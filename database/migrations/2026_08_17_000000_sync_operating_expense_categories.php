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

        // Disable model observers during migration cleanup to prevent recursion loops
        Account::$is_syncing = true;
        if (class_exists(AccountingAccount::class)) {
            AccountingAccount::$is_syncing = true;
        }

        \DB::beginTransaction();
        try {
            // 0. Clean up duplicate accounts with same name & business_id
            if (class_exists(Account::class)) {
                $duplicate_names = \DB::table('accounts')
                    ->whereNull('deleted_at')
                    ->select('business_id', 'name', \DB::raw('COUNT(*) as count'))
                    ->groupBy('business_id', 'name')
                    ->having('count', '>', 1)
                    ->get();

                foreach ($duplicate_names as $dup) {
                    $all_accs = Account::where('business_id', $dup->business_id)
                        ->where('name', $dup->name)
                        ->orderByRaw("CASE WHEN account_number IS NOT NULL AND account_number != '' THEN 0 ELSE 1 END")
                        ->orderBy('id', 'asc')
                        ->get();

                    $primary = $all_accs->first();
                    $duplicates = $all_accs->slice(1);

                    foreach ($duplicates as $dup_acc) {
                        if (\Illuminate\Support\Facades\Schema::hasTable('account_transactions')) {
                            \DB::table('account_transactions')
                                ->where('account_id', $dup_acc->id)
                                ->update(['account_id' => $primary->id]);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts')) {
                            \DB::table('accounting_accounts')
                                ->where('account_id', $dup_acc->id)
                                ->update(['account_id' => $primary->id]);
                        }
                        $dup_acc->forceDelete();
                    }
                }
            }

            if (class_exists(AccountingAccount::class)) {
                $duplicate_acc_names = \DB::table('accounting_accounts')
                    ->select('business_id', 'name', \DB::raw('COUNT(*) as count'))
                    ->groupBy('business_id', 'name')
                    ->having('count', '>', 1)
                    ->get();

                foreach ($duplicate_acc_names as $dup) {
                    $all_accs = AccountingAccount::where('business_id', $dup->business_id)
                        ->where('name', $dup->name)
                        ->orderByRaw("CASE WHEN gl_code IS NOT NULL AND gl_code != '' THEN 0 ELSE 1 END")
                        ->orderBy('id', 'asc')
                        ->get();

                    $primary = $all_accs->first();
                    $duplicates = $all_accs->slice(1);

                    foreach ($duplicates as $dup_acc) {
                        if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                            \DB::table('accounting_accounts_transactions')
                                ->where('accounting_account_id', $dup_acc->id)
                                ->update(['accounting_account_id' => $primary->id]);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasTable('accounts')) {
                            \DB::table('accounts')
                                ->where('accounting_account_id', $dup_acc->id)
                                ->update(['accounting_account_id' => $primary->id]);
                        }
                        $dup_acc->delete();
                    }
                }
            }

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
        } finally {
            Account::$is_syncing = false;
            if (class_exists(AccountingAccount::class)) {
                AccountingAccount::$is_syncing = false;
            }
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
