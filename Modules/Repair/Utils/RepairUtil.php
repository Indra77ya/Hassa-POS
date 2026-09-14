<?php

namespace Modules\Repair\Utils;

use App\Business;
use App\Charts\CommonChart;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionPayment;
use App\Unit;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Modules\Repair\Entities\JobSheet;
use Modules\Repair\Entities\RepairTradeIn;
use Modules\Repair\Notifications\RepairStatusUpdated;
use Notification;

class RepairUtil extends Util
{
    protected $productUtil;
    protected $transactionUtil;

    public function __construct(ProductUtil $productUtil = null, TransactionUtil $transactionUtil = null)
    {
        $this->productUtil = $productUtil ?: new ProductUtil();
        $this->transactionUtil = $transactionUtil ?: new TransactionUtil();
    }

    /**
     * Save or update trade-in details for job sheet / sale transaction
     */
    public function saveOrUpdateTradeIn($business_id, $user_id, array $trade_in_data, $transaction_id = null, $job_sheet_id = null)
    {
        $trade_in_value = ! empty($trade_in_data['trade_in_value']) ? $this->num_uf($trade_in_data['trade_in_value']) : 0;
        $device_name = ! empty($trade_in_data['device_name']) ? trim($trade_in_data['device_name']) : '';

        // Existing trade-in check
        $existing = null;
        if ($job_sheet_id) {
            $existing = RepairTradeIn::where('business_id', $business_id)->where('job_sheet_id', $job_sheet_id)->first();
        } elseif ($transaction_id) {
            $existing = RepairTradeIn::where('business_id', $business_id)->where('transaction_id', $transaction_id)->first();
        }

        if ($trade_in_value <= 0 || empty($device_name)) {
            if ($existing) {
                // If trade-in value was reset to 0, remove trade-in payment line if needed
                if ($existing->transaction_id) {
                    TransactionPayment::where('transaction_id', $existing->transaction_id)
                        ->where('note', 'like', '%Tukar Tambah%')
                        ->delete();
                    $this->transactionUtil->updatePaymentStatus($existing->transaction_id);
                }
                $existing->delete();
            }
            return null;
        }

        $contact_id = ! empty($trade_in_data['contact_id']) ? $trade_in_data['contact_id'] : null;
        if (! $contact_id && $job_sheet_id) {
            $js = JobSheet::find($job_sheet_id);
            $contact_id = $js ? $js->contact_id : null;
        } elseif (! $contact_id && $transaction_id) {
            $tr = Transaction::find($transaction_id);
            $contact_id = $tr ? $tr->contact_id : null;
        }

        $location_id = ! empty($trade_in_data['location_id']) ? $trade_in_data['location_id'] : null;
        if (! $location_id && $job_sheet_id) {
            $js = JobSheet::find($job_sheet_id);
            $location_id = $js ? $js->location_id : null;
        } elseif (! $location_id && $transaction_id) {
            $tr = Transaction::find($transaction_id);
            $location_id = $tr ? $tr->location_id : null;
        }

        $unit_price = ! empty($trade_in_data['unit_price']) ? $this->num_uf($trade_in_data['unit_price']) : $trade_in_value;
        $brand = ! empty($trade_in_data['brand']) ? trim($trade_in_data['brand']) : null;
        $model = ! empty($trade_in_data['model']) ? trim($trade_in_data['model']) : null;
        $serial_no = ! empty($trade_in_data['serial_no']) ? trim($trade_in_data['serial_no']) : null;
        $condition = ! empty($trade_in_data['condition']) ? trim($trade_in_data['condition']) : null;
        $notes = ! empty($trade_in_data['notes']) ? trim($trade_in_data['notes']) : null;

        $product_id = $existing ? $existing->product_id : null;
        $purchase_transaction_id = $existing ? $existing->purchase_transaction_id : null;

        // 1. Create or Update Product for Second-Hand Device
        if (! $product_id) {
            $unit = Unit::where('business_id', $business_id)->first();
            if (! $unit) {
                $unit = Unit::create([
                    'business_id' => $business_id,
                    'actual_name' => 'Pc(s)',
                    'short_name' => 'Pc(s)',
                    'allow_decimal' => 0,
                    'created_by' => $user_id,
                ]);
            }
            $unit_id = $unit->id;
            $sku = 'TT-' . time() . rand(100, 999);
            $prod_name = '[Bekas] ' . $device_name . ($serial_no ? ' (SN: ' . $serial_no . ')' : '');

            $product = Product::create([
                'name' => $prod_name,
                'business_id' => $business_id,
                'type' => 'single',
                'unit_id' => $unit_id,
                'sku' => $sku,
                'barcode_type' => 'C128',
                'enable_stock' => 1,
                'created_by' => $user_id,
            ]);
            $product_id = $product->id;

            $this->productUtil->createSingleProductVariation(
                $product->id,
                $product->sku,
                $trade_in_value,
                $trade_in_value,
                0,
                $unit_price,
                $unit_price
            );

            if ($location_id) {
                $product->product_locations()->sync([$location_id]);
            }
        } else {
            $product = Product::find($product_id);
            if ($product) {
                $prod_name = '[Bekas] ' . $device_name . ($serial_no ? ' (SN: ' . $serial_no . ')' : '');
                $product->update(['name' => $prod_name]);
            }
        }

        // 2. Create or Update Purchase Transaction for Stock Entry
        if (! $purchase_transaction_id && $location_id && $contact_id) {
            $ref_no = 'PO-TT-' . time() . rand(10, 99);
            $purchase = Transaction::create([
                'business_id' => $business_id,
                'location_id' => $location_id,
                'type' => 'purchase',
                'status' => 'received',
                'payment_status' => 'paid',
                'contact_id' => $contact_id,
                'transaction_date' => now(),
                'total_before_tax' => $trade_in_value,
                'final_total' => $trade_in_value,
                'ref_no' => $ref_no,
                'created_by' => $user_id,
            ]);
            $purchase_transaction_id = $purchase->id;

            $variation = $product ? $product->variations()->first() : null;
            if ($variation) {
                PurchaseLine::create([
                    'transaction_id' => $purchase->id,
                    'product_id' => $product->id,
                    'variation_id' => $variation->id,
                    'product_variation_id' => $variation->product_variation_id,
                    'quantity' => 1,
                    'purchase_price' => $trade_in_value,
                    'purchase_price_inc_tax' => $trade_in_value,
                    'item_tax' => 0,
                ]);

                // Update product quantity stock in location
                $this->productUtil->updateProductQuantity($location_id, $product->id, $variation->id, 1);
            }

            // Create purchase payment line
            TransactionPayment::create([
                'transaction_id' => $purchase->id,
                'business_id' => $business_id,
                'amount' => $trade_in_value,
                'method' => 'other',
                'paid_on' => now(),
                'created_by' => $user_id,
                'payment_ref_no' => 'PAY-TT-' . time() . rand(10, 99),
                'note' => 'Pembelian Barang Tukar Tambah: ' . $device_name,
            ]);
        }

        // 3. Create or Update Payment Line on Sale/POS Transaction
        if ($transaction_id) {
            $payment = TransactionPayment::where('transaction_id', $transaction_id)
                ->where('note', 'like', '%Tukar Tambah%')
                ->first();

            if (! $payment) {
                TransactionPayment::create([
                    'transaction_id' => $transaction_id,
                    'business_id' => $business_id,
                    'amount' => $trade_in_value,
                    'method' => 'other',
                    'paid_on' => now(),
                    'created_by' => $user_id,
                    'payment_ref_no' => 'PAY-TT-SALE-' . time() . rand(10, 99),
                    'note' => 'Potongan Tukar Tambah: ' . $device_name,
                ]);
            } else {
                $payment->update([
                    'amount' => $trade_in_value,
                    'note' => 'Potongan Tukar Tambah: ' . $device_name,
                ]);
            }

            $this->transactionUtil->updatePaymentStatus($transaction_id);
        }

        // 4. Save/Update RepairTradeIn Record
        $data_to_save = [
            'business_id' => $business_id,
            'location_id' => $location_id,
            'contact_id' => $contact_id,
            'job_sheet_id' => $job_sheet_id,
            'transaction_id' => $transaction_id,
            'purchase_transaction_id' => $purchase_transaction_id,
            'product_id' => $product_id,
            'device_name' => $device_name,
            'brand' => $brand,
            'model' => $model,
            'serial_no' => $serial_no,
            'condition' => $condition,
            'notes' => $notes,
            'unit_price' => $unit_price,
            'trade_in_value' => $trade_in_value,
            'created_by' => $user_id,
        ];

        if ($existing) {
            $existing->update($data_to_save);
            return $existing;
        }

        return RepairTradeIn::create($data_to_save);
    }
    public function replaceModuleTags($business_id, $data, $job_sheet)
    {
        $id = empty($job_sheet->repair_job_sheet_id) ? $job_sheet->id : $job_sheet->repair_job_sheet_id;

        $job_sheet = JobSheet::with('customer', 'technician', 'Brand',
                        'Device', 'deviceModel', 'status')
                        ->where('business_id', $business_id)
                        ->findOrFail($id);

        $business = Business::findOrFail($business_id);

        foreach ($data as $key => $value) {
            //replace customer name
            if (strpos($value, '{customer_name}') !== false) {
                $customer_name = $job_sheet->customer->name;
                $data[$key] = str_replace('{customer_name}', $customer_name, $data[$key]);
            }

            //Replace job sheet number
            if (strpos($value, '{job_sheet_no}') !== false) {
                $job_sheet_no = $job_sheet->job_sheet_no;
                $data[$key] = str_replace('{job_sheet_no}', $job_sheet_no, $data[$key]);
            }

            //Replace status
            if (strpos($value, '{status}') !== false) {
                $data[$key] = str_replace('{status}', $job_sheet->status->name, $data[$key]);
            }

            //Replace serial number
            if (strpos($value, '{serial_number}') !== false) {
                $serial_number = $job_sheet->serial_no;
                $data[$key] = str_replace('{serial_number}', $serial_number, $data[$key]);
            }

            //replace delivery_date
            if (strpos($value, '{delivery_date}') !== false) {
                $delivery_date = $job_sheet->delivery_date;
                if (! empty($delivery_date)) {
                    $delivery_date = $this->format_date($delivery_date, true);
                }
                $data[$key] = str_replace('{delivery_date}', $delivery_date, $data[$key]);
            }

            //replace service staff name
            if (strpos($value, '{service_staff}') !== false && ! empty($job_sheet->technician)) {
                $service_staff = $job_sheet->technician->user_full_name;
                $data[$key] = str_replace('{service_staff}', $service_staff, $data[$key]);
            }

            //replace brand name
            if (strpos($value, '{brand}') !== false && ! empty($job_sheet->Brand)) {
                $brand = $job_sheet->Brand->name;
                $data[$key] = str_replace('{brand}', $brand, $data[$key]);
            }

            //replace device name
            if (strpos($value, '{device}') !== false && ! empty($job_sheet->Device)) {
                $device = $job_sheet->Device->name;
                $data[$key] = str_replace('{device}', $device, $data[$key]);
            }

            //replace device model name
            if (strpos($value, '{device_model}') !== false && ! empty($job_sheet->deviceModel)) {
                $device_model = $job_sheet->deviceModel->name;
                $data[$key] = str_replace('{device_model}', $device_model, $data[$key]);
            }

            //Replace business_name
            if (strpos($value, '{business_name}') !== false) {
                $business_name = $business->name;
                $data[$key] = str_replace('{business_name}', $business_name, $data[$key]);
            }
        }

        return $data;
    }

    public function repairWarrantyExpiresIn($repair)
    {
        $warranty = '';
        if (! empty($repair->repair_completed_on)) {
            $repair_completed_on = \Carbon::parse($repair->repair_completed_on);

            $warranty_expires_on = $repair_completed_on;
            if ($repair->duration_type == 'months') {
                $warranty_expires_on = $warranty_expires_on->addMonths($repair->duration);
            } elseif ($repair->duration_type == 'years') {
                $warranty_expires_on = $warranty_expires_on->addYears($repair->duration);
            } elseif ($repair->duration_type == 'days') {
                $warranty_expires_on = $warranty_expires_on->addDays($repair->duration);
            }

            $warranty = $warranty_expires_on->diffForHumans();
        }

        return $warranty;
    }

    public function getRepairSettings($business_id)
    {
        $repair_settings = Business::where('id', $business_id)
                                ->value('repair_settings');

        $repair_settings = ! empty($repair_settings) ? json_decode($repair_settings, true) : [];

        return $repair_settings;
    }

    public function getJobsheetPdfSettings($business_id)
    {
        $repair_jobsheet_settings = Business::where('id', $business_id)
                                ->value('repair_jobsheet_settings');

        $repair_jobsheet_settings = ! empty($repair_jobsheet_settings) ?
        json_decode($repair_jobsheet_settings, true) : [];

        return $repair_jobsheet_settings;
    }

    public function sendRepairUpdateNotification($sms_body, $transaction)
    {
        $business_id = $transaction->business_id;
        $contact = $transaction->contact;
        $notification_data['sms_body'] = $sms_body;

        $tag_replaced_data = $this->replaceTags($business_id, $notification_data, $transaction);
        $tag_replaced_data = $this->replaceModuleTags($business_id, $tag_replaced_data, $transaction);

        $business = Business::findOrFail($business_id);
        $data['sms_settings'] = $business->sms_settings;
        $data['mobile_number'] = $contact->mobile;
        $data['sms_body'] = $tag_replaced_data['sms_body'];

        //Send sms
        if (! empty($contact->mobile) && ! empty($data['sms_body'])) {
            $response = $this->sendSms($data);

            if (! empty($response) && $response->getStatusCode() == 200) {
                $is_sent = __('repair::lang.sms_sent');
            } else {
                $is_sent = __('repair::lang.sms_not_sent');
            }

            //loging if notification sent
            activity()
            ->performedOn($transaction)
            ->withProperties(['sms_body' => $data['sms_body'], 'mobile_number' => $data['mobile_number'], 'sent' => $is_sent])
            ->log('is_sent_notification');

            return $response;
        } else {
            return null;
        }
    }

    public function sendJobSheetUpdateSmsNotification($sms_body, $job_sheet)
    {
        $business_id = $job_sheet->business_id;
        $customer = $job_sheet->customer;
        $notification_data['sms_body'] = $sms_body;

        //replace tag from template
        $tag_replaced_data = $this->replaceModuleTags($business_id, $notification_data, $job_sheet);

        $business = Business::findOrFail($business_id);
        $data['sms_settings'] = $business->sms_settings;
        $data['mobile_number'] = $customer->mobile;
        $data['sms_body'] = $tag_replaced_data['sms_body'];

        //Send sms
        if (! empty($data['sms_settings']) && ! empty($customer->mobile) && ! empty($data['sms_body'])) {
            $response = $this->sendSms($data);
            if (! empty($response) && $response->getStatusCode() == 200) {
                $is_sent = __('repair::lang.sms_sent');
            } else {
                $is_sent = __('repair::lang.sms_not_sent');
            }

            //loging if notification sent
            activity()
            ->performedOn($job_sheet)
            ->withProperties(['sms_body' => $data['sms_body'], 'mobile_number' => $data['mobile_number'], 'sent' => $is_sent])
            ->log('is_sent_notification');

            return $response;
        } else {
            return null;
        }
    }

    public function sendJobSheetUpdateEmailNotification($notification_data, $job_sheet)
    {
        $business_id = $job_sheet->business_id;
        $customer = $job_sheet->customer;

        //replace tag from template
        $tag_replaced_data = $this->replaceModuleTags($business_id, $notification_data, $job_sheet);

        if (! empty($customer->email)) {
            $customer->notify(new RepairStatusUpdated($tag_replaced_data));
        }
    }

    public function getRepairStatusTemplateTags()
    {
        return  [
            'tags' => ['{customer_name}', '{job_sheet_no}', '{status}', '{serial_number}', '{delivery_date}', '{service_staff}', '{brand}', '{device}', '{device_model}', '{business_name}'],
            'help_text' => __('lang_v1.available_tags'),
        ];
    }

    public function getRepairByStatus($business_id)
    {
        $job_sheets_by_status = JobSheet::join(
                    'repair_statuses as rs',
                    'repair_job_sheets.status_id',
                    '=',
                    'rs.id'
                )
                ->where('repair_job_sheets.business_id', $business_id)
                ->select(
                    DB::raw('COUNT(repair_job_sheets.id) as total_job_sheets'),
                    'rs.name as status_name',
                    'rs.color',
                    'rs.sort_order'
                )
                ->groupBy('rs.id')
                ->orderBy('sort_order', 'asc')
                ->get();

        return $job_sheets_by_status;
    }

    public function getRepairByServiceStaff($business_id)
    {
        $job_sheets_by_service_staff = JobSheet::leftJoin(
                        'users', 'repair_job_sheets.service_staff',
                         '=',
                         'users.id'
                        )
                        ->where('repair_job_sheets.business_id', $business_id)
                        ->whereNotNull('repair_job_sheets.service_staff')
                        ->select(DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as service_staff"),
                            DB::raw('COUNT(repair_job_sheets.id) as total_job_sheets')
                        )
                        ->groupBy('repair_job_sheets.service_staff')
                        ->get();

        return $job_sheets_by_service_staff;
    }

    public function getTrendingRepairBrands($business_id)
    {
        $job_sheets = JobSheet::leftJoin('brands',
                            'repair_job_sheets.brand_id',
                            '=',
                            'brands.id')
                            ->where('repair_job_sheets.business_id', $business_id)
                            ->whereNotNull('repair_job_sheets.brand_id')
                            ->select('brands.name as brand',
                                DB::raw('COUNT(repair_job_sheets.id) as job_sheets_brands')
                            )
                            ->limit(5)
                            ->groupBy('brands.id')
                            ->orderBy('job_sheets_brands', 'desc')
                            ->get();

        $labels = [];
        $values = [];
        foreach ($job_sheets as $key => $job_sheet) {
            $labels[] = $job_sheet['brand'];
            $values[] = $job_sheet['job_sheets_brands'];
        }

        $chart = new CommonChart;
        $chart->labels($labels)
            ->options($this->__chartOptions(__('repair::lang.total_unit_repaired')))
            ->dataset(__('repair::lang.total_unit_repaired'), 'column', $values);

        return $chart;
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
            ],
        ];
    }

    public function getTrendingDevices($business_id)
    {
        $job_sheets = JobSheet::leftJoin('categories as CAT',
                            'repair_job_sheets.device_id',
                            '=',
                            'CAT.id')
                            ->where('repair_job_sheets.business_id', $business_id)
                            ->whereNotNull('repair_job_sheets.device_id')
                            ->select('CAT.name as device',
                                DB::raw('COUNT(repair_job_sheets.id) as job_sheet_devices')
                            )
                            ->limit(5)
                            ->groupBy('CAT.id')
                            ->orderBy('job_sheet_devices', 'desc')
                            ->get();

        $labels = [];
        $values = [];
        foreach ($job_sheets as $key => $job_sheet) {
            $labels[] = $job_sheet['device'];
            $values[] = $job_sheet['job_sheet_devices'];
        }

        $chart = new CommonChart;
        $chart->labels($labels)
            ->options($this->__chartOptions(__('repair::lang.total_unit_repaired')))
            ->dataset(__('repair::lang.total_unit_repaired'), 'column', $values);

        return $chart;
    }

    public function getTrendingDeviceModels($business_id)
    {
        $job_sheets = JobSheet::leftJoin('repair_device_models as RDM',
                            'repair_job_sheets.device_model_id',
                            '=',
                            'RDM.id')
                            ->leftJoin('brands', 'RDM.brand_id',
                            '=', 'brands.id')
                            ->leftJoin('categories as CAT',
                            'RDM.device_id',
                            '=',
                            'CAT.id')
                            ->where('repair_job_sheets.business_id', $business_id)
                            ->whereNotNull('repair_job_sheets.device_model_id')
                            ->select('RDM.name as device_model', 'brands.name as brand',
                                DB::raw('COUNT(repair_job_sheets.id) as job_sheet_models'),
                                'CAT.name as device'
                            )
                            ->limit(5)
                            ->groupBy('RDM.id')
                            ->orderBy('job_sheet_models', 'desc')
                            ->get();

        $labels = [];
        $values = [];
        foreach ($job_sheets as $key => $job_sheet) {
            $label = $job_sheet['device_model'];
            $brand = $job_sheet['brand'];
            $device = $job_sheet['device'];
            if (! empty($brand) && ! empty($device)) {
                $label = $job_sheet['device_model'].' ('.$brand.' / '.$device.')';
            } elseif (! empty($brand)) {
                $label = $job_sheet['device_model'].' ('.$brand.')';
            } elseif (! empty($device)) {
                $label = $job_sheet['device_model'].' ('.$device.')';
            }
            $labels[] = $label;
            $values[] = $job_sheet['job_sheet_models'];
        }

        $chart = new CommonChart;
        $chart->labels($labels)
            ->options($this->__chartOptions(__('repair::lang.total_unit_repaired')))
            ->dataset(__('repair::lang.total_unit_repaired'), 'column', $values);

        return $chart;
    }
}
