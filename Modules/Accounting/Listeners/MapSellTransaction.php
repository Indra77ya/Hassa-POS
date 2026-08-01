<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SellCreatedOrModified;
use App\BusinessLocation;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

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
            $transaction = $event->transaction;
            $id = $transaction->id;
            $business_id = $transaction->business_id;
            $user_id = request()->session()->get('user.id') ?? 1;

            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();

            // 1. If deleted, delete all mappings and return
            if (isset($event->isDeleted) && $event->isDeleted) {
                $accountingUtil->deleteMap($id, null);
                return;
            }

            // If draft or quotation, delete existing mappings and return (completely ignore drafts/quotations)
            if ($transaction->status == 'draft') {
                $accountingUtil->deleteMap($id, null);
                return;
            }

            // Get business location and default map
            $business_location = BusinessLocation::find($transaction->location_id);
            if (!$business_location) {
                return;
            }
            $accounting_default_map = json_decode($business_location->accounting_default_map, true);

            // Resolve accounts
            // Credit: Pendapatan Penjualan
            $revenue_account_id = isset($accounting_default_map['sale']['payment_account']) ? $accounting_default_map['sale']['payment_account'] : null;
            // Debit (Cash): Kas/Bank
            $cash_account_id = isset($accounting_default_map['sell_payment']['deposit_to']) ? $accounting_default_map['sell_payment']['deposit_to'] : null;
            // Debit (Credit): Piutang Usaha
            $receivable_account_id = isset($accounting_default_map['sale']['deposit_to']) ? $accounting_default_map['sale']['deposit_to'] : null;

            if (is_null($revenue_account_id)) {
                return;
            }

            // 2. Delete existing mappings for this transaction first to prevent duplicates or stale records
            $accountingUtil->deleteMap($id, null);

            // 3. Calculate net paid amount (payments - change returns)
            $payments_sum = \DB::table('transaction_payments')
                ->where('transaction_id', $id)
                ->where('is_return', 0)
                ->sum('amount');
            $returns_sum = \DB::table('transaction_payments')
                ->where('transaction_id', $id)
                ->where('is_return', 1)
                ->sum('amount');
            $net_paid = $payments_sum - $returns_sum;
            if ($net_paid < 0) {
                $net_paid = 0;
            }

            $final_total = $transaction->final_total;

            // Fallback for mock/legacy tests or transactions marked as paid but without payments database records yet
            if ($transaction->payment_status === 'paid' && $net_paid <= 0) {
                $net_paid = $final_total;
            }

            // If net_paid exceeds final_total, cap it (the rest was returned as change, so net cash added is exactly final_total)
            if ($net_paid > $final_total) {
                $net_paid = $final_total;
            }

            $unpaid = $final_total - $net_paid;

            // Debit Cash Leg (Kas/Bank)
            if ($net_paid > 0 && !is_null($cash_account_id)) {
                $cash_data = [
                    'accounting_account_id' => $cash_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $net_paid,
                    'type' => 'debit',
                    'sub_type' => 'sell',
                    'note' => 'Penjualan - ' . $transaction->invoice_no,
                    'map_type' => 'deposit_to',
                    'created_by' => $user_id,
                    'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                ];
                AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
            }

            // Debit Receivable Leg (Piutang Usaha)
            if ($unpaid > 0 && !is_null($receivable_account_id)) {
                $receivable_data = [
                    'accounting_account_id' => $receivable_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $unpaid,
                    'type' => 'debit',
                    'sub_type' => 'sell',
                    'note' => 'Piutang Penjualan - ' . $transaction->invoice_no,
                    'map_type' => 'deposit_to',
                    'created_by' => $user_id,
                    'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                ];
                AccountingAccountsTransaction::updateOrCreateMapTransaction($receivable_data);
            }

            // Credit Revenue Leg (Pendapatan Penjualan)
            $revenue_data = [
                'accounting_account_id' => $revenue_account_id,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $final_total,
                'type' => 'credit',
                'sub_type' => 'sell',
                'note' => 'Pendapatan Penjualan - ' . $transaction->invoice_no,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
            ];
            AccountingAccountsTransaction::updateOrCreateMapTransaction($revenue_data);

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
                        'note' => 'HPP Penjualan - ' . $transaction->invoice_no,
                        'map_type' => 'cogs_debit',
                        'created_by' => $user_id,
                        'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                    ];

                    $inventory_data = [
                        'accounting_account_id' => $inventory_account_id,
                        'transaction_id' => $id,
                        'transaction_payment_id' => null,
                        'amount' => $cogs_amount,
                        'type' => 'credit',
                        'sub_type' => 'sell',
                        'note' => 'HPP Penjualan - ' . $transaction->invoice_no,
                        'map_type' => 'cogs_credit',
                        'created_by' => $user_id,
                        'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                    ];

                    AccountingAccountsTransaction::updateOrCreateMapTransaction($cogs_data);
                    AccountingAccountsTransaction::updateOrCreateMapTransaction($inventory_data);
                }
            }

            // Validate balance
            AccountingAccountsTransaction::validateTransactionBalance($id);
        });
    }
}
