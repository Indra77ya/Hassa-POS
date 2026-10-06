<?php

namespace Modules\Accounting\Listeners;

use App\BusinessLocation;
use App\Transaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class MapPurchaseReturnTransaction
{
    /**
     * Handle mapping for purchase_return transaction
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
            // Inventory Account (Persediaan Barang) - Credit
            $inventory_account_id = isset($accounting_default_map['purchases']['deposit_to']) ? $accounting_default_map['purchases']['deposit_to'] : null;
            if (is_null($inventory_account_id)) {
                $inventory_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_primary_type', 'asset')
                    ->where(function($q) {
                        $q->where('name', 'like', '%Persediaan%')
                          ->orWhere('name', 'like', '%Inventory%')
                          ->orWhere('account_sub_type_id', 2);
                    })
                    ->value('id');
            }

            // Payable Account (Hutang Usaha) - Debit
            $payable_account_id = isset($accounting_default_map['purchases']['payment_account']) ? $accounting_default_map['purchases']['payment_account'] : null;
            if (is_null($payable_account_id)) {
                $payable_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_primary_type', 'liability')
                    ->where(function($q) {
                        $q->where('name', 'like', '%Hutang%')
                          ->orWhere('name', 'like', '%Payable%')
                          ->orWhere('account_sub_type_id', 6);
                    })
                    ->value('id');
            }

            if (is_null($inventory_account_id)) {
                return;
            }

            // 2. Delete existing mappings for this return transaction
            AccountingAccountsTransaction::where('transaction_id', $id)
                ->whereIn('map_type', ['payment_account', 'deposit_to', 'cogs_debit', 'cogs_credit', 'recovered_deposit_to', 'loss_deposit_to'])
                ->delete();

            // 3. Calculate payments received for this return (Refunds from supplier)
            $payments_sum = \DB::table('transaction_payments')
                ->where('transaction_id', $id)
                ->sum('amount');

            $final_total = $transaction->final_total;
            $received_paid = min($payments_sum, $final_total);
            $unreceived_payable_offset = $final_total - $received_paid;

            // Credit Inventory Leg (Persediaan Barang) - full return value
            $inventory_data = [
                'accounting_account_id' => $inventory_account_id,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $final_total,
                'type' => 'credit',
                'sub_type' => 'purchase_return',
                'note' => 'Retur Pembelian - ' . ($transaction->ref_no ?? $transaction->id),
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
            ];
            AccountingAccountsTransaction::updateOrCreateMapTransaction($inventory_data);

            // Debit Cash Leg (Kas/Bank) for refund received from supplier
            if ($received_paid > 0) {
                $payments = \DB::table('transaction_payments')
                    ->where('transaction_id', $id)
                    ->get();

                $scale_factor = ($payments_sum > 0) ? ($received_paid / $payments_sum) : 1.0;
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
                        $p_cash_account_id = isset($accounting_default_map['purchase_payment']['payment_account'])
                            ? $accounting_default_map['purchase_payment']['payment_account']
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
                            'type' => 'debit',
                            'sub_type' => 'purchase_return',
                            'note' => 'Pengembalian Dana Retur Pembelian - ' . ($transaction->ref_no ?? $transaction->id),
                            'map_type' => 'payment_account',
                            'created_by' => $user_id,
                            'operation_date' => $payment->paid_on ?? $transaction->transaction_date ?? \Carbon::now(),
                        ];
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
                        $total_mapped_cash += $p_amount;
                    }
                }

                $remaining_cash = $received_paid - $total_mapped_cash;
                if ($remaining_cash > 0.01) {
                    $fallback_cash_account_id = isset($accounting_default_map['purchase_payment']['payment_account'])
                        ? $accounting_default_map['purchase_payment']['payment_account']
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
                            'type' => 'debit',
                            'sub_type' => 'purchase_return',
                            'note' => 'Pengembalian Dana Retur Pembelian - ' . ($transaction->ref_no ?? $transaction->id),
                            'map_type' => 'payment_account',
                            'created_by' => $user_id,
                            'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                        ];
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
                    }
                }
            }

            // Debit Payable Leg (Hutang Usaha) for the remaining unreceived return amount (offsets/reduces outstanding purchase debt)
            if ($unreceived_payable_offset > 0 && !is_null($payable_account_id)) {
                $payable_data = [
                    'accounting_account_id' => $payable_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $unreceived_payable_offset,
                    'type' => 'debit',
                    'sub_type' => 'purchase_return',
                    'note' => 'Pemotongan Hutang Retur Pembelian - ' . ($transaction->ref_no ?? $transaction->id),
                    'map_type' => 'payment_account',
                    'created_by' => $user_id,
                    'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                ];
                AccountingAccountsTransaction::updateOrCreateMapTransaction($payable_data);
            }

            // Validate balance
            AccountingAccountsTransaction::validateTransactionBalance($id);
        });
    }
}
