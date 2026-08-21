<?php

namespace App\Observers;

use App\ExpenseCategory;
use App\BusinessLocation;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

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
        if (!class_exists(AccountingAccount::class)) {
            return;
        }

        if (AccountingAccount::$is_syncing) {
            return;
        }

        \DB::beginTransaction();
        try {
            $business_id = $expenseCategory->business_id;
            $created_by = request()->session()->get('user.id') ?? 1;

            // 1. Create AccountingAccount
            $accountingAccount = AccountingAccount::create([
                'name' => $expenseCategory->name,
                'business_id' => $business_id,
                'account_primary_type' => 'expenses',
                'account_sub_type_id' => 14, // Beban Operasional
                'detail_type_id' => 138, // Uncategorised Expense
                'status' => 'active',
                'created_by' => $created_by
            ]);

            // 2. Double-check if the POS Account was successfully created via AccountingAccount's observer.
            // If not, create it manually here to guarantee both exist.
            $pos_account = null;
            if (!empty($accountingAccount->account_id)) {
                $pos_account = \App\Account::find($accountingAccount->account_id);
            }

            if (!$pos_account) {
                $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting('expenses', 14, $business_id);

                $pos_account = \App\Account::create([
                    'name' => $expenseCategory->name,
                    'business_id' => $business_id,
                    'created_by' => $created_by,
                    'note' => 'Uncategorised Expense',
                    'is_closed' => 0,
                    'accounting_account_id' => $accountingAccount->id,
                    'account_type_id' => $account_type_id,
                ]);

                $accountingAccount->update(['account_id' => $pos_account->id]);
            } else {
                if (empty($pos_account->accounting_account_id)) {
                    $pos_account->update(['accounting_account_id' => $accountingAccount->id]);
                }
            }

            // Find the primary active Cash Account (sub_type_id 3)
            $cash_account = AccountingAccount::where('business_id', $business_id)
                ->where('account_sub_type_id', 3)
                ->where('status', 'active')
                ->orderBy('id', 'asc')
                ->first();

            $cash_account_id = $cash_account ? $cash_account->id : null;

            // Update accounting_default_map of all active business locations
            $locations = BusinessLocation::where('business_id', $business_id)->get();
            foreach ($locations as $location) {
                $map = json_decode($location->accounting_default_map, true) ?: [];
                $map['expense_' . $expenseCategory->id] = [
                    'deposit_to' => $accountingAccount->id,
                    'payment_account' => $cash_account_id,
                ];
                $location->update(['accounting_default_map' => json_encode($map)]);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in ExpenseCategoryObserver@created: ' . $e->getMessage());
            throw $e;
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
        if (!class_exists(AccountingAccount::class)) {
            return;
        }

        if (AccountingAccount::$is_syncing) {
            return;
        }

        \DB::beginTransaction();
        try {
            // Only synchronize if name has changed
            if ($expenseCategory->isDirty('name')) {
                $old_name = $expenseCategory->getOriginal('name');
                $business_id = $expenseCategory->business_id;

                // Find via default map first as it is the most accurate link
                $location = BusinessLocation::where('business_id', $business_id)->first();
                $account_id = null;
                if ($location) {
                    $map = json_decode($location->accounting_default_map, true) ?: [];
                    $account_id = $map['expense_' . $expenseCategory->id]['deposit_to'] ?? null;
                }

                $account = null;
                if ($account_id) {
                    $account = AccountingAccount::find($account_id);
                }

                if (!$account) {
                    // Fallback search by old name
                    $account = AccountingAccount::where('business_id', $business_id)
                        ->where('name', $old_name)
                        ->where('account_primary_type', 'expenses')
                        ->first();
                }

                if ($account) {
                    $account->update(['name' => $expenseCategory->name]);

                    if (!empty($account->account_id)) {
                        $pos_acc = \App\Account::find($account->account_id);
                        if ($pos_acc) {
                            $pos_acc->update(['name' => $expenseCategory->name]);
                        }
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in ExpenseCategoryObserver@updated: ' . $e->getMessage());
            throw $e;
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
        if (!class_exists(AccountingAccount::class)) {
            return;
        }

        if (AccountingAccount::$is_syncing) {
            return;
        }

        \DB::beginTransaction();
        try {
            $business_id = $expenseCategory->business_id;

            // Find the corresponding account from map first
            $locations = BusinessLocation::where('business_id', $business_id)->get();
            $account_id = null;
            foreach ($locations as $location) {
                $map = json_decode($location->accounting_default_map, true) ?: [];
                if (isset($map['expense_' . $expenseCategory->id])) {
                    $account_id = $map['expense_' . $expenseCategory->id]['deposit_to'] ?? $account_id;

                    // Also clean up the map entry
                    unset($map['expense_' . $expenseCategory->id]);
                    $location->update(['accounting_default_map' => json_encode($map)]);
                }
            }

            $account = null;
            if ($account_id) {
                $account = AccountingAccount::find($account_id);
            }

            if (!$account) {
                // Fallback search by name
                $account = AccountingAccount::where('business_id', $business_id)
                    ->where('name', $expenseCategory->name)
                    ->where('account_primary_type', 'expenses')
                    ->first();
            }

            if ($account) {
                // Check for transactions
                $has_accounting_tx = AccountingAccountsTransaction::where('accounting_account_id', $account->id)->exists();
                $has_pos_tx = false;
                if (!empty($account->account_id) && class_exists(\App\AccountTransaction::class)) {
                    $has_pos_tx = \App\AccountTransaction::where('account_id', $account->account_id)->exists();
                }

                if ($has_accounting_tx || $has_pos_tx) {
                    // Deactivate instead of deleting
                    $account->update(['status' => 'inactive']);
                    if (!empty($account->account_id)) {
                        $pos_acc = \App\Account::find($account->account_id);
                        if ($pos_acc) {
                            $pos_acc->update(['is_closed' => 1]);
                        }
                    }
                } else {
                    // Safely delete
                    $pos_account_id = $account->account_id;
                    $account->delete();

                    if (!empty($pos_account_id)) {
                        $pos_acc = \App\Account::find($pos_account_id);
                        if ($pos_acc) {
                            $pos_acc->delete();
                        }
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in ExpenseCategoryObserver@deleted: ' . $e->getMessage());
            throw $e;
        }
    }
}
