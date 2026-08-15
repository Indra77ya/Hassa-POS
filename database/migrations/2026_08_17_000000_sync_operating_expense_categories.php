<?php

use Illuminate\Database\Migrations\Migration;
use App\ExpenseCategory;
use App\Account;
use Modules\Accounting\Entities\AccountingAccount;

class SyncOperatingExpenseCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!class_exists(ExpenseCategory::class)) {
            return;
        }

        \DB::beginTransaction();
        try {
            // 1. Sync from AccountingAccount (primary: expense/expenses, sub_type_id: 14)
            if (class_exists(AccountingAccount::class)) {
                $accountingAccounts = AccountingAccount::whereIn('account_primary_type', ['expense', 'expenses'])
                    ->where('account_sub_type_id', 14)
                    ->get();

                foreach ($accountingAccounts as $aa) {
                    $exp_cat = ExpenseCategory::where('business_id', $aa->business_id)
                        ->where('name', $aa->name)
                        ->first();

                    if (!$exp_cat) {
                        ExpenseCategory::create([
                            'name' => $aa->name,
                            'business_id' => $aa->business_id,
                            'code' => $aa->gl_code,
                        ]);
                    } else {
                        if (empty($exp_cat->code) && !empty($aa->gl_code)) {
                            $exp_cat->update(['code' => $aa->gl_code]);
                        }
                    }
                }
            }

            // 2. Sync from POS Account (beban_operasional, biaya_penyusutan)
            if (class_exists(Account::class)) {
                $posAccounts = Account::whereHas('account_type', function ($q) {
                    $q->whereIn('fixed_key', ['beban_operasional', 'biaya_penyusutan']);
                })->get();

                foreach ($posAccounts as $pa) {
                    $exp_cat = ExpenseCategory::where('business_id', $pa->business_id)
                        ->where('name', $pa->name)
                        ->first();

                    if (!$exp_cat) {
                        ExpenseCategory::create([
                            'name' => $pa->name,
                            'business_id' => $pa->business_id,
                            'code' => $pa->account_number,
                        ]);
                    } else {
                        if (empty($exp_cat->code) && !empty($pa->account_number)) {
                            $exp_cat->update(['code' => $pa->account_number]);
                        }
                    }
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in hotfix migration SyncOperatingExpenseCategories: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback required for hotfix synchronization migrations.
    }
}
