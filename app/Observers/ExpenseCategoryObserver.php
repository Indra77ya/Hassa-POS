<?php

namespace App\Observers;

use App\ExpenseCategory;
use App\BusinessLocation;
use App\Account;
use App\AccountTransaction;
use Illuminate\Support\Facades\Log;

class ExpenseCategoryObserver
{
    /**
     * Handle the ExpenseCategory "created" event.
     *
     * @param  \App\ExpenseCategory  $expenseCategory
     * @return void
     */
    public function created(ExpenseCategory $expenseCategory)
    {
        if (!class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
            return;
        }

        try {
            $business_id = $expenseCategory->business_id;

            // 1. Resolve sub_type_id and account_primary_type for Operating Expenses (Beban Operasional)
            $sub_type_id = 14;
            $account_primary_type = 'expenses';

            if (class_exists(\Modules\Accounting\Entities\AccountingAccountType::class)) {
                $sub_type = \Modules\Accounting\Entities\AccountingAccountType::find(14);
                if ($sub_type) {
                    $sub_type_id = $sub_type->id;
                    $account_primary_type = $sub_type->account_primary_type;
                }
            }

            // 2. Auto-Create Accounting Account
            // Temporarily disable syncing to avoid double triggers if any, but default static observer in AccountingAccount handles it.
            $accounting_account = \Modules\Accounting\Entities\AccountingAccount::create([
                'name' => $expenseCategory->name,
                'business_id' => $business_id,
                'account_primary_type' => $account_primary_type,
                'account_sub_type_id' => $sub_type_id,
                'status' => 'active',
                'created_by' => auth()->user()->id ?? 1
            ]);

            // 3. Ensure core POS Payment Account is also created and properly linked
            $pos_account = Account::where('accounting_account_id', $accounting_account->id)->first();
            if (!$pos_account) {
                $account_type_id = Account::getPOSAccountTypeIdFromAccounting(
                    $accounting_account->account_primary_type,
                    $accounting_account->account_sub_type_id,
                    $accounting_account->business_id
                );

                $pos_account = Account::create([
                    'name' => $accounting_account->name,
                    'business_id' => $accounting_account->business_id,
                    'created_by' => $accounting_account->created_by ?? 1,
                    'note' => $accounting_account->description,
                    'account_number' => $accounting_account->gl_code,
                    'is_closed' => $accounting_account->status == 'active' ? 0 : 1,
                    'accounting_account_id' => $accounting_account->id,
                    'account_type_id' => $account_type_id,
                ]);

                if ($pos_account) {
                    $accounting_account->account_id = $pos_account->id;
                    $accounting_account->save();
                }
            }

            // 4. Resolve the primary active Cash Account (Kas Utama Aktif)
            $cash_account_id = null;

            // Step A: Search in core POS accounts under 'kas_dan_bank'
            $pos_cash_accounts = Account::join('account_types', 'accounts.account_type_id', '=', 'account_types.id')
                ->where('accounts.business_id', $business_id)
                ->where('accounts.is_closed', 0)
                ->where('account_types.fixed_key', 'kas_dan_bank')
                ->select('accounts.*')
                ->get();

            if ($pos_cash_accounts->isNotEmpty()) {
                // Prioritize exact match with 'Kas' or 'Cash' (case-insensitive)
                $cash_acc = $pos_cash_accounts->first(function($acc) {
                    return strcasecmp($acc->name, 'Kas') === 0 || strcasecmp($acc->name, 'Cash') === 0;
                });

                if (!$cash_acc) {
                    $cash_acc = $pos_cash_accounts->first();
                }

                if ($cash_acc) {
                    $cash_account_id = $cash_acc->accounting_account_id;
                }
            }

            // Step B: Fallback directly to accounting_accounts with sub_type_id = 3 (Cash and cash equivalents)
            if (!$cash_account_id) {
                $accounting_cash_accounts = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
                    ->where('status', 'active')
                    ->where('account_sub_type_id', 3)
                    ->get();

                if ($accounting_cash_accounts->isNotEmpty()) {
                    $acc = $accounting_cash_accounts->first(function($a) {
                        return strcasecmp($a->name, 'Kas') === 0 || strcasecmp($a->name, 'Cash') === 0;
                    });

                    if (!$acc) {
                        $acc = $accounting_cash_accounts->first();
                    }

                    if ($acc) {
                        $cash_account_id = $acc->id;
                    }
                }
            }

            // 5. Auto-Fill mapping in BusinessLocation for all active locations
            if ($cash_account_id) {
                $locations = BusinessLocation::where('business_id', $business_id)
                    ->where('is_active', 1)
                    ->get();

                foreach ($locations as $location) {
                    $map = json_decode($location->accounting_default_map, true) ?: [];
                    $map['expense_' . $expenseCategory->id] = [
                        'payment_account' => $cash_account_id,
                        'deposit_to' => $accounting_account->id
                    ];
                    $location->accounting_default_map = json_encode($map);
                    $location->save();
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in ExpenseCategoryObserver@created: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        }
    }

    /**
     * Handle the ExpenseCategory "updated" event.
     *
     * @param  \App\ExpenseCategory  $expenseCategory
     * @return void
     */
    public function updated(ExpenseCategory $expenseCategory)
    {
        if (!class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
            return;
        }

        try {
            $business_id = $expenseCategory->business_id;

            // Find related AccountingAccount
            $accounting_account = null;

            // Check from mapping table if we have mapping
            $location = BusinessLocation::where('business_id', $business_id)->whereNotNull('accounting_default_map')->first();
            if ($location) {
                $map = json_decode($location->accounting_default_map, true) ?: [];
                if (isset($map['expense_' . $expenseCategory->id]['deposit_to'])) {
                    $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($map['expense_' . $expenseCategory->id]['deposit_to']);
                }
            }

            // Fallback: search by original name and business_id
            if (!$accounting_account) {
                $original_name = $expenseCategory->getOriginal('name');
                $accounting_account = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
                    ->where('name', $original_name)
                    ->first();
            }

            if ($accounting_account) {
                // Update the name
                $accounting_account->name = $expenseCategory->name;
                $accounting_account->save();

                // Automatically updates POS core Account name through AccountingAccount's updated boot observer
            }

        } catch (\Exception $e) {
            Log::error('Error in ExpenseCategoryObserver@updated: ' . $e->getMessage());
        }
    }

    /**
     * Handle the ExpenseCategory "deleted" event.
     *
     * @param  \App\ExpenseCategory  $expenseCategory
     * @return void
     */
    public function deleted(ExpenseCategory $expenseCategory)
    {
        if (!class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
            return;
        }

        try {
            $business_id = $expenseCategory->business_id;

            // Find related AccountingAccount
            $accounting_account = null;
            $location = BusinessLocation::where('business_id', $business_id)->whereNotNull('accounting_default_map')->first();
            if ($location) {
                $map = json_decode($location->accounting_default_map, true) ?: [];
                if (isset($map['expense_' . $expenseCategory->id]['deposit_to'])) {
                    $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($map['expense_' . $expenseCategory->id]['deposit_to']);
                }
            }

            if (!$accounting_account) {
                $accounting_account = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
                    ->where('name', $expenseCategory->name)
                    ->first();
            }

            if ($accounting_account) {
                $has_transactions = false;

                // Check accounting transactions
                if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                    $has_transactions = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('accounting_account_id', $accounting_account->id)->exists();
                }

                // Check POS account transactions
                if (!$has_transactions && !empty($accounting_account->account_id)) {
                    $has_transactions = AccountTransaction::where('account_id', $accounting_account->account_id)
                        ->whereNull('deleted_at')
                        ->exists();
                }

                if ($has_transactions) {
                    // a. If transaction history exists, set status to inactive / is_closed = 1
                    $accounting_account->status = 'inactive';
                    $accounting_account->save();

                    if (!empty($accounting_account->account_id)) {
                        $pos_acc = Account::find($accounting_account->account_id);
                        if ($pos_acc) {
                            $pos_acc->is_closed = 1;
                            $pos_acc->save();
                        }
                    }
                } else {
                    // b. If NO transaction history exists, safely delete the accounts
                    // Disable model syncing to avoid deletion loops/errors, then delete both
                    $prev_sync_accounting = \Modules\Accounting\Entities\AccountingAccount::$is_syncing;
                    $prev_sync_pos = Account::$is_syncing;

                    \Modules\Accounting\Entities\AccountingAccount::$is_syncing = true;
                    Account::$is_syncing = true;

                    if (!empty($accounting_account->account_id)) {
                        $pos_acc = Account::find($accounting_account->account_id);
                        if ($pos_acc) {
                            $pos_acc->delete();
                        }
                    }

                    $accounting_account->delete();

                    \Modules\Accounting\Entities\AccountingAccount::$is_syncing = $prev_sync_accounting;
                    Account::$is_syncing = $prev_sync_pos;
                }
            }

            // 3. Clear mapping from all business locations of this business
            $locations = BusinessLocation::where('business_id', $business_id)->get();
            foreach ($locations as $loc) {
                $map = json_decode($loc->accounting_default_map, true) ?: [];
                if (isset($map['expense_' . $expenseCategory->id])) {
                    unset($map['expense_' . $expenseCategory->id]);
                    $loc->accounting_default_map = json_encode($map);
                    $loc->save();
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in ExpenseCategoryObserver@deleted: ' . $e->getMessage());
        }
    }
}
