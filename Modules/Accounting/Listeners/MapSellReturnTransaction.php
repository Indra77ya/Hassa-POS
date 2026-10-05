<?php

namespace Modules\Accounting\Listeners;

use App\BusinessLocation;
use App\Transaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class MapSellReturnTransaction
{
    /**
     * Handle mapping for sell_return transaction
     *
     * @param Transaction $transaction
     * @param bool $isDeleted
     * @return void
     */
    public function handle(Transaction $transaction, $isDeleted = false)
    {
        \DB::transaction(function () use ($transaction, $isDeleted) {
            $id = $transaction->id;
            $business_id = $transaction->business_id;
            $user_id = auth()->id() ?? (request()->hasSession() ? request()->session()->get('user.id') : null) ?? $transaction->created_by ?? 1;

            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();

            // 1. If deleted or final_total <= 0, delete mapping and return
            if ($isDeleted || $transaction->final_total <= 0) {
                $accountingUtil->deleteMap($id, null);
                return;
            }

            // Get business location and default map
            $business_location = BusinessLocation::find($transaction->location_id);
            if (!$business_location) {
                return;
            }
            $accounting_default_map = json_decode($business_location->accounting_default_map, true);

            // Resolve accounts:
            // Revenue/Sales Return Account (Pendapatan Penjualan) - Debit
            $revenue_account_id = isset($accounting_default_map['sale']['payment_account']) ? $accounting_default_map['sale']['payment_account'] : null;
            if (is_null($revenue_account_id)) {
                $revenue_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_primary_type', 'income')
                    ->where(function($q) {
                        $q->where('name', 'like', '%Pendapatan%')
                          ->orWhere('name', 'like', '%Revenue%')
                          ->orWhere('name', 'like', '%Sales%')
                          ->orWhere('account_sub_type_id', 11);
                    })
                    ->value('id');
            }

            // Receivable Account (Piutang Usaha) - Credit
            $receivable_account_id = isset($accounting_default_map['sale']['deposit_to']) ? $accounting_default_map['sale']['deposit_to'] : null;
            if (is_null($receivable_account_id)) {
                $receivable_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_primary_type', 'asset')
                    ->where(function($q) {
                        $q->where('name', 'like', '%Piutang%')
                          ->orWhere('name', 'like', '%Receivable%')
                          ->orWhere('account_sub_type_id', 1);
                    })
                    ->value('id');
            }

            if (is_null($revenue_account_id)) {
                return;
            }

            // 2. Delete existing mappings for this return transaction
            AccountingAccountsTransaction::where('transaction_id', $id)
                ->whereIn('map_type', ['payment_account', 'deposit_to', 'cogs_debit', 'cogs_credit', 'recovered_deposit_to', 'loss_deposit_to'])
                ->delete();

            // 3. Calculate payments refunded for this return (Payments to customer)
            $payments_sum = \DB::table('transaction_payments')
                ->where('transaction_id', $id)
                ->sum('amount');

            $final_total = $transaction->final_total;
            $refunded_paid = min($payments_sum, $final_total);
            $unrefunded_receivable_offset = $final_total - $refunded_paid;

            // Debit Revenue Leg (Pendapatan Penjualan) - reduces total gross revenue
            $revenue_data = [
                'accounting_account_id' => $revenue_account_id,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $final_total,
                'type' => 'debit',
                'sub_type' => 'sell_return',
                'note' => 'Retur Penjualan - ' . ($transaction->invoice_no ?? $transaction->id),
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
            ];
            AccountingAccountsTransaction::updateOrCreateMapTransaction($revenue_data);

            // Credit Cash Leg (Kas/Bank) for refund paid to customer
            if ($refunded_paid > 0) {
                $payments = \DB::table('transaction_payments')
                    ->where('transaction_id', $id)
                    ->get();

                $scale_factor = ($payments_sum > 0) ? ($refunded_paid / $payments_sum) : 1.0;
                $total_mapped_cash = 0;

                foreach ($payments as $payment) {
                    $p_amount = (float)$payment->amount * $scale_factor;
                    if ($p_amount <= 0) {
                        continue;
                    }

                    $p_cash_account_id = null;
                    if (!empty($payment->account_id)) {
                        $p_cash_account_id = \DB::table('accounts')
                            ->where('id', $payment->account_id)
                            ->value('accounting_account_id');
                    }
                    if (is_null($p_cash_account_id)) {
                        $p_cash_account_id = isset($accounting_default_map['sell_payment']['deposit_to'])
                            ? $accounting_default_map['sell_payment']['deposit_to']
                            : null;
                    }
                    if (is_null($p_cash_account_id)) {
                        $p_cash_account_id = AccountingAccount::where('business_id', $business_id)
                            ->where('status', 'active')
                            ->where('account_primary_type', 'asset')
                            ->where(function($q) {
                                $q->where('name', 'like', '%Kas%')
                                  ->orWhere('name', 'like', '%Bank%')
                                  ->orWhere('account_sub_type_id', 3);
                            })
                            ->value('id');
                    }

                    if (!is_null($p_cash_account_id)) {
                        $cash_data = [
                            'accounting_account_id' => $p_cash_account_id,
                            'transaction_id' => $id,
                            'transaction_payment_id' => $payment->id,
                            'amount' => $p_amount,
                            'type' => 'credit',
                            'sub_type' => 'sell_return',
                            'note' => 'Pengembalian Dana Retur Penjualan - ' . ($transaction->invoice_no ?? $transaction->id),
                            'map_type' => 'deposit_to',
                            'created_by' => $user_id,
                            'operation_date' => $payment->paid_on ?? $transaction->transaction_date ?? \Carbon::now(),
                        ];
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
                        $total_mapped_cash += $p_amount;
                    }
                }

                $remaining_cash = $refunded_paid - $total_mapped_cash;
                if ($remaining_cash > 0.01) {
                    $fallback_cash_account_id = isset($accounting_default_map['sell_payment']['deposit_to'])
                        ? $accounting_default_map['sell_payment']['deposit_to']
                        : null;
                    if (is_null($fallback_cash_account_id)) {
                        $fallback_cash_account_id = AccountingAccount::where('business_id', $business_id)
                            ->where('status', 'active')
                            ->where('account_primary_type', 'asset')
                            ->where(function($q) {
                                $q->where('name', 'like', '%Kas%')
                                  ->orWhere('name', 'like', '%Bank%')
                                  ->orWhere('account_sub_type_id', 3);
                            })
                            ->value('id');
                    }

                    if (!is_null($fallback_cash_account_id)) {
                        $cash_data = [
                            'accounting_account_id' => $fallback_cash_account_id,
                            'transaction_id' => $id,
                            'transaction_payment_id' => null,
                            'amount' => $remaining_cash,
                            'type' => 'credit',
                            'sub_type' => 'sell_return',
                            'note' => 'Pengembalian Dana Retur Penjualan - ' . ($transaction->invoice_no ?? $transaction->id),
                            'map_type' => 'deposit_to',
                            'created_by' => $user_id,
                            'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                        ];
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
                    }
                }
            }

            // Credit Receivable Leg (Piutang Usaha) for remaining unrefunded return amount (offsets/reduces outstanding customer debt)
            if ($unrefunded_receivable_offset > 0 && !is_null($receivable_account_id)) {
                $receivable_data = [
                    'accounting_account_id' => $receivable_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $unrefunded_receivable_offset,
                    'type' => 'credit',
                    'sub_type' => 'sell_return',
                    'note' => 'Pemotongan Piutang Retur Penjualan - ' . ($transaction->invoice_no ?? $transaction->id),
                    'map_type' => 'deposit_to',
                    'created_by' => $user_id,
                    'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                ];
                AccountingAccountsTransaction::updateOrCreateMapTransaction($receivable_data);
            }

            // 4. Calculate COGS / HPP reduction and Inventory restoration for returned items
            $parent_sale_id = $transaction->return_parent_id;
            if ($parent_sale_id) {
                $cogs_amount = \DB::table('transaction_sell_lines as tsl')
                    ->where('tsl.transaction_id', $parent_sale_id)
                    ->where('tsl.quantity_returned', '>', 0)
                    ->join('products as p', 'tsl.product_id', '=', 'p.id')
                    ->where('p.enable_stock', 1)
                    ->leftJoin('transaction_sell_lines_purchase_lines as tspl', 'tsl.id', '=', 'tspl.sell_line_id')
                    ->leftJoin('purchase_lines as pl', 'tspl.purchase_line_id', '=', 'pl.id')
                    ->select(\DB::raw('SUM(
                        tsl.quantity_returned
                        * COALESCE(pl.purchase_price, (SELECT default_purchase_price FROM variations WHERE id = tsl.variation_id))
                    ) as total_cogs'))
                    ->value('total_cogs');

                if ($cogs_amount > 0) {
                    $cogs_account_id = AccountingAccount::where('business_id', $business_id)
                        ->where('status', 'active')
                        ->where(function($q) {
                            $q->where('name', 'like', '%Harga Pokok Penjualan%')
                              ->orWhere('account_sub_type_id', 13);
                        })
                        ->value('id');

                    $inventory_account_id = isset($accounting_default_map['purchases']['deposit_to']) ? $accounting_default_map['purchases']['deposit_to'] : null;
                    if (!$inventory_account_id) {
                        $inventory_account_id = AccountingAccount::where('business_id', $business_id)
                            ->where('status', 'active')
                            ->where('name', 'like', '%Persediaan%')
                            ->value('id');
                    }

                    if ($cogs_account_id && $inventory_account_id) {
                        // Debit Inventory Leg (Persediaan Barang) - restores returned stock value
                        $inventory_data = [
                            'accounting_account_id' => $inventory_account_id,
                            'transaction_id' => $id,
                            'transaction_payment_id' => null,
                            'amount' => $cogs_amount,
                            'type' => 'debit',
                            'sub_type' => 'sell_return',
                            'note' => 'Pemulihan Persediaan Retur Penjualan - ' . ($transaction->invoice_no ?? $transaction->id),
                            'map_type' => 'cogs_debit',
                            'created_by' => $user_id,
                            'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                        ];

                        // Credit COGS Leg (HPP) - reduces cost of goods sold
                        $cogs_data = [
                            'accounting_account_id' => $cogs_account_id,
                            'transaction_id' => $id,
                            'transaction_payment_id' => null,
                            'amount' => $cogs_amount,
                            'type' => 'credit',
                            'sub_type' => 'sell_return',
                            'note' => 'Pengurangan HPP Retur Penjualan - ' . ($transaction->invoice_no ?? $transaction->id),
                            'map_type' => 'cogs_credit',
                            'created_by' => $user_id,
                            'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                        ];

                        AccountingAccountsTransaction::updateOrCreateMapTransaction($inventory_data);
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cogs_data);
                    }
                }
            }

            // Validate balance
            AccountingAccountsTransaction::validateTransactionBalance($id);
        });
    }
}
