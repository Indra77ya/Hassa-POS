<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SellCreatedOrModified;
use App\BusinessLocation;

class MapSellTransaction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SellCreatedOrModified $event)
    {
        \DB::transaction(function () use ($event) {
            //get location setting and check if default is set or not, if set the proceed.
            $business_location = BusinessLocation::find($event->transaction->location_id);
            $accounting_default_map = json_decode($business_location->accounting_default_map, true);

            $is_cash_sale = ($event->transaction->payment_status === 'paid');

            if ($is_cash_sale) {
                $deposit_to = isset($accounting_default_map['sell_payment']['deposit_to']) ? $accounting_default_map['sell_payment']['deposit_to'] : null;
            } else {
                $deposit_to = isset($accounting_default_map['sale']['deposit_to']) ? $accounting_default_map['sale']['deposit_to'] : null;
            }
            $payment_account = isset($accounting_default_map['sale']['payment_account']) ? $accounting_default_map['sale']['payment_account'] : null;

            //if purchase is deleted then delete the mapping
            if(isset($event->isDeleted) && $event->isDeleted){
                $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
                $accountingUtil->deleteMap($event->transaction->id, null);
            } else {

                if(!is_null($deposit_to) && !is_null($payment_account)){

                    $type = 'sell';
                    $id = $event->transaction->id;
                    $user_id = request()->session()->get('user.id') ?? 1;
                    $business_id = $event->transaction->business_id;

                    $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
                    $accountingUtil->saveMap($type, $id, $user_id, $business_id, $deposit_to, $payment_account);

                    // 4. Calculate COGS / HPP for stock managed products (enable_stock == 1)
                    $cogs_amount = \DB::table('transaction_sell_lines as tsl')
                        ->where('tsl.transaction_id', $id)
                        ->join('products as p', 'tsl.product_id', '=', 'p.id')
                        ->where('p.enable_stock', 1)
                        ->leftJoin('transaction_sell_lines_purchase_lines as tspl', 'tsl.id', '=', 'tspl.sell_line_id')
                        ->leftJoin('purchase_lines as pl', 'tspl.purchase_line_id', '=', 'pl.id')
                        ->select(\DB::raw('SUM(
                            (COALESCE(tspl.quantity, tsl.quantity) - COALESCE(tspl.qty_returned, tsl.quantity_returned, 0))
                            * COALESCE(pl.purchase_price, (SELECT default_purchase_price FROM variations WHERE id = tsl.variation_id))
                        ) as total_cogs'))
                        ->value('total_cogs');

                    if ($cogs_amount > 0) {
                        // Resolve COGS account (Harga Pokok Penjualan)
                        $cogs_account_id = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
                            ->where('status', 'active')
                            ->where(function($q) {
                                $q->where('name', 'like', '%Harga Pokok Penjualan%')
                                  ->orWhere('account_sub_type_id', 13);
                            })
                            ->value('id');

                        // Resolve Inventory account (Persediaan Barang)
                        $inventory_account_id = isset($accounting_default_map['purchases']['deposit_to']) ? $accounting_default_map['purchases']['deposit_to'] : null;
                        if (!$inventory_account_id) {
                            $inventory_account_id = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
                                ->where('status', 'active')
                                ->where('name', 'like', '%Persediaan%')
                                ->value('id');
                        }

                        if ($cogs_account_id && $inventory_account_id) {
                            $cogs_data = [
                                'accounting_account_id' => $cogs_account_id,
                                'transaction_id' => $id,
                                'transaction_payment_id' => null,
                                'amount' => $cogs_amount,
                                'type' => 'debit',
                                'sub_type' => 'sell',
                                'note' => 'HPP Penjualan - ' . $event->transaction->invoice_no,
                                'map_type' => 'cogs_debit',
                                'created_by' => $user_id,
                                'operation_date' => \Carbon::now(),
                            ];

                            $inventory_data = [
                                'accounting_account_id' => $inventory_account_id,
                                'transaction_id' => $id,
                                'transaction_payment_id' => null,
                                'amount' => $cogs_amount,
                                'type' => 'credit',
                                'sub_type' => 'sell',
                                'note' => 'HPP Penjualan - ' . $event->transaction->invoice_no,
                                'map_type' => 'cogs_credit',
                                'created_by' => $user_id,
                                'operation_date' => \Carbon::now(),
                            ];

                            \Modules\Accounting\Entities\AccountingAccountsTransaction::updateOrCreateMapTransaction($cogs_data);
                            \Modules\Accounting\Entities\AccountingAccountsTransaction::updateOrCreateMapTransaction($inventory_data);
                        }
                    }
                }
            }

            // Perform transaction balance validation for this sell transaction
            if (!isset($event->isDeleted) || !$event->isDeleted) {
                \Modules\Accounting\Entities\AccountingAccountsTransaction::validateTransactionBalance($event->transaction->id);
            }
        });
    }
}
