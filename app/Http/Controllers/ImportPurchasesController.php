<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\ProductSerialNumber;
use App\PurchaseLine;
use App\TaxRate;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use DB;
use Excel;
use Illuminate\Http\Request;

class ImportPurchasesController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtil  $productUtil
     * @param  BusinessUtil  $businessUtil
     * @param  TransactionUtil  $transactionUtil
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display import purchase screen and history.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $imported_purchases = Transaction::where('business_id', $business_id)
                            ->where('type', 'purchase')
                            ->whereNotNull('import_batch')
                            ->with(['sales_person'])
                            ->select('id', 'import_batch', 'import_time', 'ref_no', 'created_by')
                            ->orderBy('import_batch', 'desc')
                            ->get();

        $imported_purchases_array = [];
        foreach ($imported_purchases as $purchase) {
            $imported_purchases_array[$purchase->import_batch]['import_time'] = $purchase->import_time;
            $imported_purchases_array[$purchase->import_batch]['created_by'] = ! empty($purchase->sales_person) ? $purchase->sales_person->user_full_name : '';
            $imported_purchases_array[$purchase->import_batch]['ref_nos'][] = $purchase->ref_no;
        }

        $import_fields = $this->__importFields();

        return view('import_purchases.index')->with(compact('imported_purchases_array', 'import_fields'));
    }

    /**
     * Preview imported data and map columns with purchase fields
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->businessUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        $business_id = request()->session()->get('user.business_id');

        if ($request->hasFile('purchases')) {
            $file_name = time().'_'.$request->purchases->getClientOriginalName();
            if (! file_exists(public_path('uploads/temp'))) {
                mkdir(public_path('uploads/temp'), 0777, true);
            }
            $request->purchases->move(public_path('uploads/temp'), $file_name);

            $parsed_array = $this->__parseData($file_name);

            $import_fields = $this->__importFields();
            foreach ($import_fields as $key => $value) {
                $import_fields[$key] = $value['label'];
            }

            // Evaluate highest matching field with the header to pre select from dropdown
            $headers = $parsed_array[0];
            $match_array = [];
            foreach ($headers as $key => $value) {
                $match_percentage = [];
                foreach ($import_fields as $k => $v) {
                    similar_text((string)$value, (string)$v, $percentage);
                    $match_percentage[$k] = $percentage;
                }
                $max_key = array_keys($match_percentage, max($match_percentage))[0];

                // If match percentage is greater than 50% then pre select the value
                $match_array[$key] = $match_percentage[$max_key] >= 50 ? $max_key : null;
            }

            $business_locations = BusinessLocation::forDropdown($business_id);

            return view('import_purchases.preview')->with(compact('parsed_array', 'import_fields', 'file_name', 'business_locations', 'match_array'));
        }

        return redirect()->back();
    }

    public function __parseData($file_name)
    {
        $array = Excel::toArray([], public_path('uploads/temp/'.$file_name))[0];

        // remove blank columns from headers
        $headers = array_filter($array[0]);

        // Remove header row
        unset($array[0]);
        $parsed_array[] = $headers;
        foreach ($array as $row) {
            $temp = [];
            foreach ($row as $k => $v) {
                if (array_key_exists($k, $headers)) {
                    $temp[] = $v;
                }
            }
            $parsed_array[] = $temp;
        }

        return $parsed_array;
    }

    /**
     * Import purchases to database
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $file_name = $request->input('file_name');
            $import_fields = $request->input('import_fields');
            $group_by = $request->input('group_by');
            $location_id = $request->input('location_id');
            $business_id = $request->session()->get('user.business_id');

            $file_path = public_path('uploads/temp/'.$file_name);
            $parsed_array = $this->__parseData($file_name);
            // Remove header row
            unset($parsed_array[0]);
            $formatted_purchases_data = $this->__formatPurchaseData($parsed_array, $import_fields, $group_by);

            // Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            $this->__importPurchases($formatted_purchases_data, $business_id, $location_id);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.purchases_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];

            @unlink($file_path);

            return redirect('import-purchases')->with('notification', $output);
        }

        @unlink($file_path);

        return redirect('import-purchases')->with('status', $output);
    }

    private function __importPurchases($formatted_data, $business_id, $location_id)
    {
        $import_batch = Transaction::where('business_id', $business_id)->max('import_batch');

        if (empty($import_batch)) {
            $import_batch = 1;
        } else {
            $import_batch = $import_batch + 1;
        }

        $now = \Carbon::now()->toDateTimeString();
        $row_index = 2;
        $user_id = auth()->user()->id;
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        foreach ($formatted_data as $data) {
            $first_line = $data[0];

            // Resolve supplier contact
            $supplier_id = null;
            if (! empty($first_line['supplier_id'])) {
                $supplier = Contact::where('business_id', $business_id)
                    ->where('contact_id', $first_line['supplier_id'])
                    ->where('type', 'supplier')
                    ->first();
                if (! empty($supplier)) {
                    $supplier_id = $supplier->id;
                }
            }
            if (empty($supplier_id) && ! empty($first_line['supplier_name'])) {
                $supplier = Contact::where('business_id', $business_id)
                    ->where('name', $first_line['supplier_name'])
                    ->where('type', 'supplier')
                    ->first();
                if (! empty($supplier)) {
                    $supplier_id = $supplier->id;
                }
            }
            if (empty($supplier_id) && ! empty($first_line['supplier_phone'])) {
                $supplier = Contact::where('business_id', $business_id)
                    ->where('mobile', $first_line['supplier_phone'])
                    ->where('type', 'supplier')
                    ->first();
                if (! empty($supplier)) {
                    $supplier_id = $supplier->id;
                }
            }

            if (empty($supplier_id)) {
                // Get or create default supplier
                $supplier = Contact::where('business_id', $business_id)
                    ->where('type', 'supplier')
                    ->first();
                if (! empty($supplier)) {
                    $supplier_id = $supplier->id;
                } else {
                    $supplier = Contact::create([
                        'business_id' => $business_id,
                        'type' => 'supplier',
                        'name' => 'Default Supplier',
                        'created_by' => $user_id,
                    ]);
                    $supplier_id = $supplier->id;
                }
            }

            // Purchase Status
            $status = ! empty($first_line['status']) ? strtolower(trim($first_line['status'])) : 'received';
            if (! in_array($status, ['received', 'pending', 'ordered'])) {
                $status = 'received';
            }

            // Purchase Date
            $transaction_date = $now;
            if (! empty($first_line['date'])) {
                try {
                    $transaction_date = \Carbon::parse($first_line['date'])->toDateTimeString();
                } catch (\Exception $e) {
                    throw new \Exception(__('lang_v1.invalid_date_format_at', ['row' => $row_index]));
                }
            }

            // Reference No
            $ref_no = ! empty($first_line['ref_no']) ? $first_line['ref_no'] : null;

            $total_before_tax = 0;
            $purchase_lines_formatted = [];

            foreach ($data as $line_data) {
                if (! empty($line_data['sku'])) {
                    $variation = Variation::where('sub_sku', $line_data['sku'])
                        ->whereHas('product', function ($query) use ($business_id) {
                            $query->where('business_id', $business_id);
                        })
                        ->with(['product'])
                        ->first();
                    $product = ! empty($variation) ? $variation->product : null;
                } else {
                    $product = Product::where('business_id', $business_id)
                        ->where('name', $line_data['product'])
                        ->with(['variations'])
                        ->first();
                    $variation = ! empty($product) ? $product->variations->first() : null;
                }

                if (empty($variation)) {
                    throw new \Exception(__('lang_v1.import_sale_product_not_found', ['row' => $row_index, 'product_name' => $line_data['product'] ?? '', 'sku' => $line_data['sku'] ?? '']));
                }

                $qty = ! empty($line_data['quantity']) ? (float)$line_data['quantity'] : 1;
                $unit_cost_before_discount = ! empty($line_data['unit_cost_before_discount']) ? (float)$line_data['unit_cost_before_discount'] : (float)$variation->default_purchase_price;
                $discount_percent = ! empty($line_data['discount_percent']) ? (float)$line_data['discount_percent'] : 0;

                $unit_cost_before_tax = $unit_cost_before_discount - ($unit_cost_before_discount * $discount_percent / 100);

                $tax_id = null;
                $item_tax = 0;
                $purchase_price_inc_tax = $unit_cost_before_tax;

                if (! empty($line_data['item_tax'])) {
                    $tax = TaxRate::where('business_id', $business_id)
                        ->where('name', $line_data['item_tax'])
                        ->first();

                    if (! empty($tax)) {
                        $tax_id = $tax->id;
                        $item_tax = $this->transactionUtil->calc_percentage($unit_cost_before_tax, $tax->amount);
                        $purchase_price_inc_tax = $unit_cost_before_tax + $item_tax;
                    }
                }

                $default_sell_price = ! empty($line_data['selling_price']) ? (float)$line_data['selling_price'] : (float)$variation->sell_price_inc_tax;

                $line_total = $unit_cost_before_tax * $qty;
                $total_before_tax += $line_total;

                $serials = [];
                if (! empty($line_data['serial_numbers'])) {
                    $raw_serials = explode(',', $line_data['serial_numbers']);
                    foreach ($raw_serials as $s) {
                        $s_trimmed = trim($s);
                        if (! empty($s_trimmed)) {
                            $serials[] = $s_trimmed;
                        }
                    }
                }

                $purchase_lines_formatted[] = [
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'quantity' => $qty,
                    'pp_without_discount' => $unit_cost_before_discount,
                    'discount_percent' => $discount_percent,
                    'purchase_price' => $unit_cost_before_tax,
                    'purchase_price_inc_tax' => $purchase_price_inc_tax,
                    'item_tax' => $item_tax,
                    'purchase_line_tax_id' => $tax_id,
                    'default_sell_price' => $default_sell_price,
                    'product_unit_id' => $product->unit_id,
                    'serials' => $serials,
                ];

                $row_index++;
            }

            // Create Purchase Transaction
            $transaction_data = [
                'business_id' => $business_id,
                'location_id' => $location_id,
                'type' => 'purchase',
                'status' => $status,
                'payment_status' => 'due',
                'contact_id' => $supplier_id,
                'transaction_date' => $transaction_date,
                'total_before_tax' => $total_before_tax,
                'final_total' => $total_before_tax,
                'created_by' => $user_id,
                'import_batch' => $import_batch,
                'import_time' => $now,
            ];

            if (! empty($ref_no)) {
                $transaction_data['ref_no'] = $ref_no;
            } else {
                $ref_count = $this->transactionUtil->setAndGetReferenceCount('purchase', $business_id);
                $transaction_data['ref_no'] = $this->transactionUtil->generateReferenceNumber('purchase', $ref_count, $business_id);
            }

            $transaction = Transaction::create($transaction_data);

            // Create purchase lines & update stock
            foreach ($purchase_lines_formatted as $p_line) {
                $line_input = [
                    'product_id' => $p_line['product_id'],
                    'variation_id' => $p_line['variation_id'],
                    'quantity' => $p_line['quantity'],
                    'pp_without_discount' => $p_line['pp_without_discount'],
                    'discount_percent' => $p_line['discount_percent'],
                    'purchase_price' => $p_line['purchase_price'],
                    'purchase_price_inc_tax' => $p_line['purchase_price_inc_tax'],
                    'item_tax' => $p_line['item_tax'],
                    'purchase_line_tax_id' => $p_line['purchase_line_tax_id'],
                    'default_sell_price' => $p_line['default_sell_price'],
                    'product_unit_id' => $p_line['product_unit_id'],
                ];

                $this->productUtil->createOrUpdatePurchaseLines($transaction, [$line_input], $currency_details, true);

                // Attach serial numbers if present
                if (! empty($p_line['serials'])) {
                    $created_line = PurchaseLine::where('transaction_id', $transaction->id)
                        ->where('variation_id', $p_line['variation_id'])
                        ->latest('id')
                        ->first();

                    if (! empty($created_line)) {
                        foreach ($p_line['serials'] as $sn_code) {
                            ProductSerialNumber::create([
                                'business_id' => $business_id,
                                'product_id' => $p_line['product_id'],
                                'variation_id' => $p_line['variation_id'],
                                'purchase_line_id' => $created_line->id,
                                'serial_number' => $sn_code,
                                'status' => $status == 'received' ? 'in_stock' : 'in_stock',
                                'purchase_price' => $p_line['purchase_price'],
                                'selling_price' => $p_line['default_sell_price'],
                                'created_by' => $user_id,
                            ]);
                        }
                    }
                }
            }

            // Adjust payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        }
    }

    private function __formatPurchaseData($parsed_array, $import_fields, $group_by)
    {
        $formatted_array = [];
        unset($parsed_array[0]);

        foreach ($parsed_array as $row) {
            $temp = [];
            foreach ($row as $key => $value) {
                if (isset($import_fields[$key])) {
                    $temp[$import_fields[$key]] = $value;
                }
            }

            $group_key = isset($row[$group_by]) ? $row[$group_by] : (! empty($temp['ref_no']) ? $temp['ref_no'] : uniqid());
            $formatted_array[$group_key][] = $temp;
        }

        return array_values($formatted_array);
    }

    private function __importFields()
    {
        $fields = [
            'ref_no' => ['label' => __('purchase.ref_no')],
            'supplier_id' => ['label' => __('lang_v1.supplier_id')],
            'supplier_name' => ['label' => __('purchase.supplier')],
            'supplier_phone' => ['label' => __('lang_v1.supplier_phone_number')],
            'date' => ['label' => __('purchase.purchase_date'), 'instruction' => __('lang_v1.date_format_instruction')],
            'status' => ['label' => __('purchase.purchase_status'), 'instruction' => 'received, pending, ordered'],
            'product' => ['label' => __('product.product_name'), 'instruction' => __('lang_v1.either_product_name_or_sku_required')],
            'sku' => ['label' => __('lang_v1.product_sku'), 'instruction' => __('lang_v1.either_product_name_or_sku_required')],
            'quantity' => ['label' => __('lang_v1.quantity'), 'instruction' => __('lang_v1.required')],
            'unit_cost_before_discount' => ['label' => __('lang_v1.unit_cost_before_discount')],
            'discount_percent' => ['label' => __('lang_v1.discount_percent')],
            'item_tax' => ['label' => __('lang_v1.item_tax')],
            'selling_price' => ['label' => __('purchase.unit_selling_price')],
            'serial_numbers' => ['label' => __('lang_v1.serial_number'), 'instruction' => __('lang_v1.comma_separated')],
        ];

        return $fields;
    }

    /**
     * Deletes all purchases from a batch
     *
     * @return \Illuminate\Http\Response
     */
    public function revertPurchaseImport($batch)
    {
        if (! auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $purchases = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase')
                                ->where('import_batch', $batch)
                                ->get();

            DB::beginTransaction();

            foreach ($purchases as $purchase) {
                // Delete purchase lines & decrease product quantity
                foreach ($purchase->purchase_lines as $line) {
                    if ($purchase->status == 'received') {
                        $this->productUtil->decreaseProductQuantity(
                            $line->product_id,
                            $line->variation_id,
                            $purchase->location_id,
                            $line->quantity
                        );
                    }
                    ProductSerialNumber::where('purchase_line_id', $line->id)->delete();
                    $line->delete();
                }

                $purchase->delete();
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.batch_deleted_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('import-purchases')->with('status', $output);
    }
}
