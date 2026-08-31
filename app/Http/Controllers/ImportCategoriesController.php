<?php

namespace App\Http\Controllers;

use App\Category;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;

class ImportCategoriesController extends Controller
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
     * Display import categories screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        // Check if zip extension is loaded or not.
        if ($zip_loaded === false) {
            $output = [
                'success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import',
            ];

            return view('import_categories.index')
                ->with('notification', $output);
        } else {
            return view('import_categories.index');
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
        if (! auth()->user()->can('category.create')) {
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

            if ($request->hasFile('categories_csv')) {
                $file = $request->file('categories_csv');

                $parsed_array = Excel::toArray([], $file);

                // Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();

                // Track category names imported in this batch to avoid duplicate parent/sub categories within the CSV itself
                $batch_names = [];

                foreach ($imported_data as $key => $value) {
                    $row_no = $key + 2; // Since header is removed and index starts at 0

                    // Check if row is empty
                    if (empty(array_filter($value))) {
                        continue;
                    }

                    // Minimum 1 column: Category Name
                    if (count($value) < 1) {
                        $is_valid = false;
                        $error_msg = "Some of the columns are missing. Please, use the latest template.";
                        break;
                    }

                    $name = trim($value[0]);
                    $short_code = isset($value[1]) ? trim($value[1]) : null;
                    $description = isset($value[2]) ? trim($value[2]) : null;
                    $parent_category_name = isset($value[3]) ? trim($value[3]) : null;

                    if (empty($name)) {
                        $is_valid = false;
                        $error_msg = "Category Name is required in row no. $row_no";
                        break;
                    }

                    // Check if category already exists in database
                    $exists_db = Category::where('business_id', $business_id)
                        ->where('category_type', 'product')
                        ->where('name', $name)
                        ->exists();

                    if ($exists_db) {
                        $is_valid = false;
                        $error_msg = __('category.duplicate_error', ['name' => $name, 'row' => $row_no]);
                        break;
                    }

                    // Check if category is duplicated in the current file
                    if (in_array(strtolower($name), $batch_names)) {
                        $is_valid = false;
                        $error_msg = __('category.duplicate_error', ['name' => $name, 'row' => $row_no]);
                        break;
                    }

                    // Add to batch tracking
                    $batch_names[] = strtolower($name);

                    // Parent category logic
                    $parent_id = 0;
                    if (! empty($parent_category_name)) {
                        $parent_category = Category::where('business_id', $business_id)
                            ->where('category_type', 'product')
                            ->where('parent_id', 0)
                            ->where(function ($query) use ($parent_category_name) {
                                $query->where('name', $parent_category_name)
                                    ->orWhere('short_code', $parent_category_name);
                            })->first();

                        if (empty($parent_category)) {
                            $is_valid = false;
                            $error_msg = __('category.parent_not_found_error', ['name' => $parent_category_name, 'row' => $row_no]);
                            break;
                        }

                        $parent_id = $parent_category->id;
                    }

                    // Create Category
                    Category::create([
                        'name' => $name,
                        'business_id' => $business_id,
                        'short_code' => $short_code,
                        'parent_id' => $parent_id,
                        'description' => $description,
                        'category_type' => 'product',
                        'created_by' => $user_id,
                    ]);
                }

                if (! $is_valid) {
                    throw new \Exception($error_msg);
                }

                DB::commit();

                $output = [
                    'success' => 1,
                    'msg' => __('category.file_imported_successfully'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => 'No file uploaded',
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect('import-categories')->with('notification', $output);
        }

        return redirect()->action([\App\Http\Controllers\TaxonomyController::class, 'index'], ['type' => 'product'])->with('status', $output);
    }
}
