<?php

namespace Modules\Repair\Utils;

use App\Business;
use App\Charts\CommonChart;
use App\Utils\Util;
use DB;
use Modules\Repair\Entities\JobSheet;
use Modules\Repair\Notifications\RepairStatusUpdated;
use Notification;

class RepairUtil extends Util
{
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

    /**
     * Saves or updates trade-in details for a transaction.
     * Registers product, creates purchase transaction and payment, and links trade-in record.
     */
    public function saveOrUpdateTradeIn($business_id, $user_id, array $trade_in_data, $transaction_id = null, $job_sheet_id = null)
    {
        if (empty($trade_in_data['model_name']) || empty($trade_in_data['trade_in_value'])) {
            return null;
        }

        $trade_in_amount = is_numeric($trade_in_data['trade_in_value']) ? floatval($trade_in_data['trade_in_value']) : $this->num_uf($trade_in_data['trade_in_value']);
        if ($trade_in_amount <= 0) {
            return null;
        }

        $resale_price_raw = !empty($trade_in_data['resale_price']) ? $trade_in_data['resale_price'] : $trade_in_amount;
        $resale_price_val = is_numeric($resale_price_raw) ? floatval($resale_price_raw) : $this->num_uf($resale_price_raw);
        $resale_price = $resale_price_val > 0 ? $resale_price_val : $trade_in_amount;

        $existing = null;
        if (!empty($transaction_id)) {
            $existing = \Modules\Repair\Entities\RepairTradeIn::where('transaction_id', $transaction_id)->first();
        } elseif (!empty($job_sheet_id)) {
            $existing = \Modules\Repair\Entities\RepairTradeIn::where('job_sheet_id', $job_sheet_id)->first();
        }

        $product_id = $existing ? $existing->product_id : null;
        $purchase_transaction_id = $existing ? $existing->purchase_transaction_id : null;

        $location_id = null;
        $contact_id = null;

        if (!empty($transaction_id)) {
            $sale_trans = \App\Transaction::find($transaction_id);
            if ($sale_trans) {
                $location_id = $sale_trans->location_id;
                $contact_id = $sale_trans->contact_id;
            }
        }

        if (empty($location_id)) {
            $loc = \App\BusinessLocation::where('business_id', $business_id)->first();
            $location_id = $loc ? $loc->id : null;
        }

        if (empty($contact_id)) {
            $contact = \App\Contact::where('business_id', $business_id)->where('type', 'supplier')->first();
            if (!$contact) {
                $contact = \App\Contact::where('business_id', $business_id)->first();
            }
            $contact_id = $contact ? $contact->id : null;
        }

        // Determine unit_id and category_id
        $unit_id = !empty($trade_in_data['unit_id']) ? $trade_in_data['unit_id'] : null;
        if (empty($unit_id)) {
            $unit = \App\Unit::where('business_id', $business_id)->first();
            $unit_id = $unit ? $unit->id : 1;
        }

        $category_id = !empty($trade_in_data['category_id']) ? $trade_in_data['category_id'] : null;

        // Determine payment account for method 'other' from location default_payment_accounts
        $account_id = null;
        if (!empty($location_id)) {
            $location = \App\BusinessLocation::find($location_id);
            if ($location && !empty($location->default_payment_accounts)) {
                $defaultPaymentAccounts = json_decode($location->default_payment_accounts, true);
                $account_id = $defaultPaymentAccounts['other']['account']
                    ?? $defaultPaymentAccounts['custom_pay_1']['account']
                    ?? $defaultPaymentAccounts['cash']['account']
                    ?? null;
            }
        }

        // 1. Create or update Product for the trade-in item
        if (empty($product_id)) {
            $product_name = '[BEKAS/SECOND] ' . $trade_in_data['model_name'];
            if (!empty($trade_in_data['serial_no'])) {
                $product_name .= ' (' . $trade_in_data['serial_no'] . ')';
            }

            $product = \App\Product::create([
                'name' => $product_name,
                'business_id' => $business_id,
                'type' => 'single',
                'unit_id' => $unit_id,
                'category_id' => $category_id,
                'sku' => 'TRD-' . strtoupper(\Str::random(6)),
                'enable_stock' => 1,
                'created_by' => $user_id,
            ]);
            $product_id = $product->id;

            // Link product location
            if (!empty($location_id)) {
                $product->product_locations()->sync([$location_id]);
            }

            // Create Variation
            $profit_percent = $trade_in_amount > 0 ? (($resale_price - $trade_in_amount) / $trade_in_amount) * 100 : 0;
            $productUtil = new \App\Utils\ProductUtil();
            $productUtil->createSingleProductVariation(
                $product->id,
                $product->sku,
                $trade_in_amount,
                $trade_in_amount,
                $profit_percent,
                $resale_price,
                $resale_price
            );
        } else {
            // Update product unit and category if provided
            $product_obj = \App\Product::find($product_id);
            if ($product_obj) {
                if (!empty($unit_id)) {
                    $product_obj->unit_id = $unit_id;
                }
                if (!empty($category_id)) {
                    $product_obj->category_id = $category_id;
                }
                $product_obj->save();
            }

            // Update variation prices if exists
            $variation = \App\Variation::where('product_id', $product_id)->first();
            if ($variation) {
                $variation->default_purchase_price = $trade_in_amount;
                $variation->dpp_inc_tax = $trade_in_amount;
                $variation->profit_percent = $trade_in_amount > 0 ? (($resale_price - $trade_in_amount) / $trade_in_amount) * 100 : 0;
                $variation->default_sell_price = $resale_price;
                $variation->sell_price_inc_tax = $resale_price;
                $variation->save();
            }
        }

        $variation = \App\Variation::where('product_id', $product_id)->first();
        $variation_id = $variation ? $variation->id : null;

        // 2. Create Purchase Transaction for stock in
        if (empty($purchase_transaction_id) && !empty($variation_id)) {
            $ref_count = $this->setAndGetReferenceCount('purchase', $business_id);
            $ref_no = $this->generateReferenceNumber('purchase', $ref_count, $business_id);

            $purchase = \App\Transaction::create([
                'business_id' => $business_id,
                'location_id' => $location_id,
                'type' => 'purchase',
                'status' => 'received',
                'payment_status' => 'paid',
                'contact_id' => $contact_id,
                'transaction_date' => \Carbon::now()->toDateTimeString(),
                'total_before_tax' => $trade_in_amount,
                'final_total' => $trade_in_amount,
                'created_by' => $user_id,
                'ref_no' => $ref_no,
            ]);

            $purchase_transaction_id = $purchase->id;

            // Create Purchase Line
            \App\PurchaseLine::create([
                'transaction_id' => $purchase->id,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => 1,
                'purchase_price' => $trade_in_amount,
                'purchase_price_inc_tax' => $trade_in_amount,
                'item_tax' => 0,
                'quantity_sold' => 0,
            ]);

            // Adjust stock
            $productUtil = new \App\Utils\ProductUtil();
            $productUtil->updateProductQuantity($location_id, $product_id, $variation_id, 1, 0);

            // Create Purchase Payment Line
            \App\TransactionPayment::create([
                'transaction_id' => $purchase->id,
                'business_id' => $business_id,
                'amount' => $trade_in_amount,
                'method' => 'other',
                'paid_on' => \Carbon::now()->toDateTimeString(),
                'created_by' => $user_id,
                'account_id' => $account_id,
                'payment_ref_no' => 'TRD-PAY-' . strtoupper(\Str::random(6)),
                'note' => 'Pembayaran Otomatis Tukar Tambah',
            ]);

            // Dispatch PurchaseCreatedOrModified event to sync accounting double-entry journal (inventory debit)
            event(new \App\Events\PurchaseCreatedOrModified($purchase));
        }

        // 3. Save or update RepairTradeIn model record
        $trade_in = \Modules\Repair\Entities\RepairTradeIn::updateOrCreate(
            [
                'transaction_id' => $transaction_id,
                'job_sheet_id' => $job_sheet_id,
            ],
            [
                'business_id' => $business_id,
                'transaction_id' => $transaction_id,
                'job_sheet_id' => $job_sheet_id,
                'model_name' => $trade_in_data['model_name'],
                'serial_no' => isset($trade_in_data['serial_no']) ? $trade_in_data['serial_no'] : null,
                'condition' => isset($trade_in_data['condition']) ? $trade_in_data['condition'] : null,
                'unit_id' => $unit_id,
                'category_id' => $category_id,
                'trade_in_value' => $trade_in_amount,
                'resale_price' => $resale_price,
                'product_id' => $product_id,
                'purchase_transaction_id' => $purchase_transaction_id,
                'created_by' => $user_id,
            ]
        );

        // 4. Create a payment line on the sale transaction for trade_in deduction if transaction_id exists
        if (!empty($transaction_id)) {
            // Check if trade-in payment line exists
            $trade_in_payment = \App\TransactionPayment::where('transaction_id', $transaction_id)
                ->where('note', 'like', '%Tukar Tambah%')
                ->first();

            if (!$trade_in_payment) {
                \App\TransactionPayment::create([
                    'transaction_id' => $transaction_id,
                    'business_id' => $business_id,
                    'amount' => $trade_in_amount,
                    'method' => 'other',
                    'paid_on' => \Carbon::now()->toDateTimeString(),
                    'created_by' => $user_id,
                    'account_id' => $account_id,
                    'payment_ref_no' => 'TRD-DED-' . strtoupper(\Str::random(6)),
                    'note' => 'Tukar Tambah (Trade-In)',
                ]);
            } else {
                $trade_in_payment->amount = $trade_in_amount;
                if (!empty($account_id) && empty($trade_in_payment->account_id)) {
                    $trade_in_payment->account_id = $account_id;
                }
                $trade_in_payment->note = 'Tukar Tambah (Trade-In)';
                $trade_in_payment->save();
            }

            // Recalculate transaction payment status
            $transactionUtil = new \App\Utils\TransactionUtil();
            $transactionUtil->updatePaymentStatus($transaction_id);
        }

        return $trade_in;
    }
}
