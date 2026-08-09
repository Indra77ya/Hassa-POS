<?php

use Illuminate\Database\Migrations\Migration;
use App\ExpenseCategory;
use App\BusinessLocation;
use Modules\Accounting\Entities\AccountingAccount;

class SyncExistingExpenseCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!class_exists(AccountingAccount::class)) {
            return;
        }

        // Disable syncing guards to prevent loops since we handle linking explicitly
        \App\Account::$is_syncing = true;
        AccountingAccount::$is_syncing = true;

        \DB::beginTransaction();
        try {
            $categories = ExpenseCategory::all();

            foreach ($categories as $category) {
                $business_id = $category->business_id;

                // 1. Find or create AccountingAccount
                $accountingAccount = AccountingAccount::where('business_id', $business_id)
                    ->where('name', $category->name)
                    ->where('account_primary_type', 'expenses')
                    ->first();

                if (!$accountingAccount) {
                    $accountingAccount = AccountingAccount::create([
                        'name' => $category->name,
                        'business_id' => $business_id,
                        'account_primary_type' => 'expenses',
                        'account_sub_type_id' => 14, // Beban Operasional
                        'detail_type_id' => 138, // Uncategorised Expense
                        'status' => 'active',
                        'created_by' => 1
                    ]);
                }

                // 2. Find or create core POS Account
                $posAccount = null;
                if (!empty($accountingAccount->account_id)) {
                    $posAccount = \App\Account::find($accountingAccount->account_id);
                }

                if (!$posAccount) {
                    $posAccount = \App\Account::where('business_id', $business_id)
                        ->where('name', $category->name)
                        ->first();
                }

                if (!$posAccount) {
                    $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting('expenses', 14, $business_id);

                    $posAccount = \App\Account::create([
                        'name' => $category->name,
                        'business_id' => $business_id,
                        'created_by' => 1,
                        'note' => 'Uncategorised Expense',
                        'is_closed' => 0,
                        'accounting_account_id' => $accountingAccount->id,
                        'account_type_id' => $account_type_id,
                    ]);
                } else {
                    if (empty($posAccount->accounting_account_id)) {
                        $posAccount->update(['accounting_account_id' => $accountingAccount->id]);
                    }
                }

                // Ensure bidirectional links are correct
                if ($accountingAccount->account_id !== $posAccount->id) {
                    $accountingAccount->update(['account_id' => $posAccount->id]);
                }

                // 3. Update accounting_default_map of all active business locations for this business
                $cash_account = AccountingAccount::where('business_id', $business_id)
                    ->where('account_sub_type_id', 3)
                    ->where('status', 'active')
                    ->orderBy('id', 'asc')
                    ->first();

                $cash_account_id = $cash_account ? $cash_account->id : null;

                $locations = BusinessLocation::where('business_id', $business_id)->get();
                foreach ($locations as $location) {
                    $map = json_decode($location->accounting_default_map, true) ?: [];
                    $map['expense_' . $category->id] = [
                        'deposit_to' => $accountingAccount->id,
                        'payment_account' => $cash_account_id,
                    ];
                    $location->update(['accounting_default_map' => json_encode($map)]);
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in hotfix migration SyncExistingExpenseCategories: ' . $e->getMessage());
            throw $e;
        } finally {
            // Re-enable syncing guards
            \App\Account::$is_syncing = false;
            AccountingAccount::$is_syncing = false;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback is necessary for synchronization hotfixes.
    }
}
