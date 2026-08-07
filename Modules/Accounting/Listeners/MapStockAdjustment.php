<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\StockAdjustmentCreatedOrModified;
use App\BusinessLocation;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountType;

class MapStockAdjustment
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
    public function handle($event)
    {
        \DB::transaction(function () use ($event) {
            $transaction = $event->stockAdjustment ?? ($event->transaction ?? null);
            if (!$transaction) {
                return;
            }

            $id = $transaction->id;
            $business_id = $transaction->business_id;
            $user_id = request()->session()->get('user.id') ?? 1;

            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();

            // 1. If deleted, delete all mappings and return
            $is_deleted = (isset($event->isDeleted) && $event->isDeleted) || (isset($event->action) && $event->action == 'deleted');
            if ($is_deleted) {
                $accountingUtil->deleteMap($id, null);
                return;
            }

            // Get business location and default map
            $business_location = BusinessLocation::find($transaction->location_id);
            if (!$business_location) {
                return;
            }
            $accounting_default_map = json_decode($business_location->accounting_default_map, true);

            // 2. Delete existing mappings for this transaction
            $accountingUtil->deleteMap($id, null);

            $final_total = (float)$transaction->final_total;
            if ($final_total <= 0) {
                return;
            }

            // Resolve Inventory account (Persediaan Barang) to CREDIT
            $inventory_account_id = isset($accounting_default_map['purchases']['deposit_to']) ? $accounting_default_map['purchases']['deposit_to'] : null;
            if (!$inventory_account_id) {
                $inventory_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('name', 'like', '%Persediaan%')
                    ->value('id');
            }

            if (!$inventory_account_id) {
                // If still not found, let's look for any active asset account
                $inventory_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_primary_type', 'asset')
                    ->value('id');
            }

            if (!$inventory_account_id) {
                return; // cannot proceed without inventory account
            }

            // Resolve Expense / Loss account to DEBIT
            $expense_account_id = $transaction->expense_account_id;
            if ($expense_account_id) {
                // Verify it exists and is active
                $exists = AccountingAccount::where('id', $expense_account_id)
                    ->where('business_id', $business_id)
                    ->where('status', 'active')
                    ->exists();
                if (!$exists) {
                    $expense_account_id = null;
                }
            }

            // Fallback search: look for active expense account with "Kerusakan" or "Kerugian"
            if (!$expense_account_id) {
                $expense_account_id = AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->whereIn('account_primary_type', ['expense', 'expenses'])
                    ->where(function($q) {
                        $q->where('name', 'like', '%Kerusakan%')
                          ->orWhere('name', 'like', '%Kerugian%');
                    })
                    ->value('id');
            }

            // If still not found, auto-create the new account 'Beban Kerusakan/Kehilangan'
            if (!$expense_account_id) {
                $sub_type_id = 14;
                $account_primary_type = 'expense';

                if (\Schema::hasTable('accounting_account_types')) {
                    $sub_type = AccountingAccountType::where('name', 'like', '%Beban%')
                        ->orWhere('name', 'like', '%Expense%')
                        ->first();
                    if ($sub_type) {
                        $sub_type_id = $sub_type->id;
                        $account_primary_type = $sub_type->account_primary_type;
                    }
                }

                $expense_account = AccountingAccount::create([
                    'name' => 'Beban Kerusakan/Kehilangan',
                    'business_id' => $business_id,
                    'account_primary_type' => $account_primary_type,
                    'account_sub_type_id' => $sub_type_id,
                    'status' => 'active',
                    'created_by' => $user_id
                ]);

                $expense_account_id = $expense_account->id;

                // Explicitly sync to POS accounts table (Payment accounts) to ensure 100% symmetry
                if (class_exists(\App\Account::class)) {
                    try {
                        $pos_account = null;
                        if (!empty($expense_account->account_id)) {
                            $pos_account = \App\Account::find($expense_account->account_id);
                        }

                        if (!$pos_account) {
                            $pos_account = \App\Account::where('accounting_account_id', $expense_account->id)->first();
                        }

                        if (!$pos_account) {
                            $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting(
                                $expense_account->account_primary_type,
                                $expense_account->account_sub_type_id,
                                $expense_account->business_id
                            );

                            $prev_is_syncing_accounting = AccountingAccount::$is_syncing;
                            $prev_is_syncing_pos = \App\Account::$is_syncing;

                            AccountingAccount::$is_syncing = true;
                            \App\Account::$is_syncing = true;

                            $pos_account = \App\Account::create([
                                'name' => $expense_account->name,
                                'business_id' => $expense_account->business_id,
                                'created_by' => $expense_account->created_by ?? 1,
                                'note' => $expense_account->description,
                                'account_number' => $expense_account->gl_code,
                                'is_closed' => $expense_account->status == 'active' ? 0 : 1,
                                'accounting_account_id' => $expense_account->id,
                                'account_type_id' => $account_type_id,
                            ]);

                            if ($pos_account) {
                                $expense_account->account_id = $pos_account->id;
                                $expense_account->save();
                            }

                            AccountingAccount::$is_syncing = $prev_is_syncing_accounting;
                            \App\Account::$is_syncing = $prev_is_syncing_pos;
                        }

                    } catch (\Exception $e) {
                        \Log::error('Error explicitly syncing auto-created Accounting Account to POS Account: ' . $e->getMessage());
                    }
                }

                // Save this expense_account_id on the transaction for future reference
                $transaction->expense_account_id = $expense_account_id;
                $transaction->save();
            }

            // Resolve Kas / Bank account to DEBIT (for total_amount_recovered > 0)
            $total_amount_recovered = (float)$transaction->total_amount_recovered;
            $cash_account_id = null;
            if ($total_amount_recovered > 0) {
                $cash_account_id = isset($accounting_default_map['sell_payment']['deposit_to']) ? $accounting_default_map['sell_payment']['deposit_to'] : null;
                if (!$cash_account_id) {
                    $cash_account_id = isset($accounting_default_map['purchase_payment']['payment_account']) ? $accounting_default_map['purchase_payment']['payment_account'] : null;
                }
                if (!$cash_account_id) {
                    $cash_account_id = AccountingAccount::where('business_id', $business_id)
                        ->where('status', 'active')
                        ->where('account_primary_type', 'asset')
                        ->where(function($q) {
                            $q->where('name', 'like', '%Kas%')
                              ->orWhere('name', 'like', '%Bank%')
                              ->orWhere('account_sub_type_id', 3);
                        })
                        ->value('id');
                }
            }

            // Calculate debit allocations
            $recovered_amount = min($total_amount_recovered, $final_total);
            $loss_amount = $final_total - $recovered_amount;

            // Credit Inventory Leg (Persediaan Barang)
            $inventory_data = [
                'accounting_account_id' => $inventory_account_id,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $final_total,
                'type' => 'credit',
                'sub_type' => 'stock_adjustment',
                'note' => 'Penyesuaian Stok - ' . $transaction->ref_no,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
            ];
            AccountingAccountsTransaction::updateOrCreateMapTransaction($inventory_data);

            // Debit Cash Leg (Kas/Bank) if any amount is recovered
            if ($recovered_amount > 0 && $cash_account_id) {
                $cash_data = [
                    'accounting_account_id' => $cash_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $recovered_amount,
                    'type' => 'debit',
                    'sub_type' => 'stock_adjustment',
                    'note' => 'Ganti Rugi Penyesuaian Stok - ' . $transaction->ref_no,
                    'map_type' => 'recovered_deposit_to',
                    'created_by' => $user_id,
                    'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                ];
                AccountingAccountsTransaction::updateOrCreateMapTransaction($cash_data);
            }

            // Debit Expense Leg (Beban Kerusakan/Kehilangan)
            if ($loss_amount > 0) {
                $expense_data = [
                    'accounting_account_id' => $expense_account_id,
                    'transaction_id' => $id,
                    'transaction_payment_id' => null,
                    'amount' => $loss_amount,
                    'type' => 'debit',
                    'sub_type' => 'stock_adjustment',
                    'note' => 'Beban Penyesuaian Stok - ' . $transaction->ref_no,
                    'map_type' => 'loss_deposit_to',
                    'created_by' => $user_id,
                    'operation_date' => $transaction->transaction_date ?? \Carbon::now(),
                ];
                AccountingAccountsTransaction::updateOrCreateMapTransaction($expense_data);
            }

            // Validate balance
            AccountingAccountsTransaction::validateTransactionBalance($id);
        });
    }
}
