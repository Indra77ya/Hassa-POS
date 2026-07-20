<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;

class ImportUnitsController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display import units screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        // Check if zip extension is loaded or not.
        if ($zip_loaded === false) {
            $output = ['success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import',
            ];

            return view('import_units.index')
                ->with('notification', $output);
        } else {
            return view('import_units.index');
        }
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            // Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('units_csv')) {
                $file = $request->file('units_csv');

                $parsed_array = Excel::toArray([], $file);

                // Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();

                // Track short/actual names imported in this batch to avoid duplicates within the CSV itself
                $batch_names = [];

                foreach ($imported_data as $key => $value) {
                    $row_no = $key + 2; // Since header is removed and index starts at 0

                    // Check if row is empty
                    if (empty(array_filter($value))) {
                        continue;
                    }

                    // Minimum 3 columns: Name, Short Name, Allow Decimal
                    if (count($value) < 3) {
                        $is_valid = false;
                        $error_msg = "Some of the columns are missing. Please, use the latest template.";
                        break;
                    }

                    $actual_name = trim($value[0]);
                    $short_name = trim($value[1]);
                    $allow_decimal = $this->parseBoolean($value[2]);

                    if (empty($actual_name)) {
                        $is_valid = false;
                        $error_msg = "Unit Name is required in row no. $row_no";
                        break;
                    }

                    if (empty($short_name)) {
                        $is_valid = false;
                        $error_msg = "Short Name is required in row no. $row_no";
                        break;
                    }

                    // Check if unit already exists in database
                    $exists_db = Unit::where('business_id', $business_id)
                        ->where(function ($query) use ($actual_name, $short_name) {
                            $query->where('actual_name', $actual_name)
                                  ->orWhere('short_name', $short_name);
                        })->exists();

                    if ($exists_db) {
                        $is_valid = false;
                        $error_msg = __('unit.duplicate_error', ['name' => $actual_name . '/' . $short_name, 'row' => $row_no]);
                        break;
                    }

                    // Check if unit is duplicated in the current file
                    if (in_array(strtolower($actual_name), $batch_names) || in_array(strtolower($short_name), $batch_names)) {
                        $is_valid = false;
                        $error_msg = __('unit.duplicate_error', ['name' => $actual_name . '/' . $short_name, 'row' => $row_no]);
                        break;
                    }

                    // Add to batch tracking
                    $batch_names[] = strtolower($actual_name);
                    $batch_names[] = strtolower($short_name);

                    // Sub unit logic
                    $base_unit_id = null;
                    $base_unit_multiplier = null;

                    $define_base_unit = isset($value[3]) ? $this->parseBoolean($value[3]) : 0;
                    if ($define_base_unit == 1) {
                        $multiplier = isset($value[4]) ? trim($value[4]) : null;
                        $base_unit_name = isset($value[5]) ? trim($value[5]) : null;

                        if (empty($multiplier) || ! is_numeric($multiplier)) {
                            $is_valid = false;
                            $error_msg = "Invalid multiplier in row no. $row_no";
                            break;
                        }

                        if (empty($base_unit_name)) {
                            $is_valid = false;
                            $error_msg = "Base Unit Name/Short Name is required in row no. $row_no when defining a sub-unit.";
                            break;
                        }

                        // Query base unit
                        $base_unit = Unit::where('business_id', $business_id)
                            ->where(function ($query) use ($base_unit_name) {
                                $query->where('actual_name', $base_unit_name)
                                      ->orWhere('short_name', $base_unit_name);
                            })->first();

                        if (empty($base_unit)) {
                            $is_valid = false;
                            $error_msg = __('unit.base_unit_not_found_error', ['name' => $base_unit_name, 'row' => $row_no]);
                            break;
                        }

                        $base_unit_id = $base_unit->id;
                        $base_unit_multiplier = $multiplier;
                    }

                    // Create the Unit
                    Unit::create([
                        'business_id' => $business_id,
                        'actual_name' => $actual_name,
                        'short_name' => $short_name,
                        'allow_decimal' => $allow_decimal,
                        'base_unit_id' => $base_unit_id,
                        'base_unit_multiplier' => $base_unit_multiplier,
                        'created_by' => $user_id
                    ]);
                }

                if (! $is_valid) {
                    throw new \Exception($error_msg);
                }

                DB::commit();

                $output = ['success' => 1,
                    'msg' => __('unit.file_imported_successfully'),
                ];
            } else {
                $output = ['success' => 0,
                    'msg' => 'No file uploaded',
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect('import-units')->with('notification', $output);
        }

        return redirect('units')->with('status', $output);
    }

    /**
     * Parse boolean string value (Yes, No, Ya, Tidak, 1, 0)
     *
     * @param string $value
     * @return int
     */
    private function parseBoolean($value)
    {
        $val = strtolower(trim($value));
        if (in_array($val, ['1', 'yes', 'y', 'ya', 'true'])) {
            return 1;
        }
        return 0;
    }
}
