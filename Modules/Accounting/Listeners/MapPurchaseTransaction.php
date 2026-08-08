<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\PurchaseCreatedOrModified;
use App\BusinessLocation;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class MapPurchaseTransaction
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
    public function handle(PurchaseCreatedOrModified $event)
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

            // Get business location and default map
            $business_location = BusinessLocation::find($transaction->location_id);
            if (!$business_location) {
                return;
            }
            $accounting_default_map = json_decode($business_location->accounting_default_map, true);

            // Resolve accounts
            // Debit: Persediaan Barang
            $inventory_account_id = isset($accounting_default_map['purchases']['deposit_to']) ? $accounting_default_map['purchases']['deposit_to'] : null;
            if (is_null($inventory_account_id)) {
                $inventory_account_id = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_primary_type', 'asset')
                    ->where(function($q) {
                        $q->where('name', 'like', '%Persediaan%')
                          ->orWhere('name', 'like', '%Inventory%')
                          ->orWhere('account_sub_type_id', 2);
                    })
                    ->value('id');
            }

            // Credit (Tempo/Credit): Hutang Usaha
            $payable_account_id = isset($accounting_default_map['purchases']['payment_account']) ? $accounting_default_map['purchases']['payment_account'] : null;
            if (is_null($payable_account_id)) {
                $payable_account_id = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
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

            // 2. Delete existing mappings for this transaction
            AccountingAccountsTransaction::where('transaction_id', $id)
                ->whereIn('map_type', ['payment_account', 'deposit_to', 'cogs_debit', 'cogs_credit', 'recovered_deposit_to', 'loss_deposit_to'])
                ->delete();

            // 3. Calculate net paid amount (payments)
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

            if ($net_paid > $final_total) {
                $net_paid = $final_total;
            }

            $unpaid = $final_total - $net_paid;

            // Debit Inventory Leg (Persediaan Barang)
            $inventory_data = [
                'accounting_account_id' => $inventory_account_id,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $final_total,
                'type' => 'debit',
                'sub_type' => 'purchase',
                'note' => 'Pembelian - ' . $transaction->ref_no,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
            ];
            AccountingAccountsTransaction::updateOrCreateMapTransaction($inventory_data);

            // Credit Cash Leg (Kas/Bank)
            if ($net_paid > 0) {
                $payments = \DB::table('transaction_payments')
                    ->where('transaction_id', $id)
                    ->where('is_return', 0)
                    ->get();

                $scale_factor = 1.0;
                if ($payments_sum > 0) {
                    $scale_factor = $net_paid / $payments_sum;
                }

                $total_mapped_cash = 0;
                foreach ($payments as $payment) {
                    $p_amount = (float)$payment->amount * $scale_factor;
                    if ($p_amount <= 0) {
                        continue;
                    }

                    // Resolve cash account for this specific payment
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
                        $p_cash_account_id = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
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
                            'sub_type' => 'purchase',
                            'note' => 'Bayar Pembelian - ' . $transaction->ref_no,
                            'map_type' => 'payment_account',
                            'created_by' => $user_id,
                            'operation_date' => $payment->paid_on ?? $transaction->transaction_date ?? \Carbon::now(),
                        ];
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
                        $total_mapped_cash += $p_amount;
                    }
                }

                // If some cash wasn't mapped through payment records or sum of payments < net_paid
                $remaining_cash = $net_paid - $total_mapped_cash;
                if ($remaining_cash > 0.01) {
                    $fallback_cash_account_id = isset($accounting_default_map['purchase_payment']['payment_account'])
                        ? $accounting_default_map['purchase_payment']['payment_account']
                        : null;
                    if (is_null($fallback_cash_account_id)) {
                        $fallback_cash_account_id = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
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
                            'sub_type' => 'purchase',
                            'note' => 'Bayar Pembelian - ' . $transaction->ref_no,
                            'map_type' => 'payment_account',
                            'created_by' => $user_id,
                            'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                        ];
                        AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
                    }
                }
            }

            // Credit Payable Leg (Hutang Usaha)
            if ($unpaid > 0 && !is_null($payable_account_id)) {
                $payable_data = [
                    'accounting_account_id' => $payable_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $unpaid,
                    'type' => 'credit',
                    'sub_type' => 'purchase',
                    'note' => 'Hutang Pembelian - ' . $transaction->ref_no,
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
