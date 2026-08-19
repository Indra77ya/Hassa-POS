<?php

use Illuminate\Database\Migrations\Migration;
use App\Account;
use App\ExpenseCategory;
use Modules\Accounting\Entities\AccountingAccount;

class RemoveBiayaPenyusutanAndAkumulasiPenyusutanAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $target_names = [
            'Biaya Penyusutan',
            'Akumulasi Penyusutan',
            'Depreciation expense',
            'Accumulated depreciation on property, plant and equipment'
        ];

        // 1. Delete POS Accounts and their transactions
        $pos_accounts = Account::whereIn('name', $target_names)->get();
        foreach ($pos_accounts as $account) {
            \DB::table('account_transactions')->where('account_id', $account->id)->delete();
            $account->delete();
        }

        // 2. Delete Accounting Accounts and their transactions
        if (class_exists(AccountingAccount::class)) {
            $accounting_accounts = AccountingAccount::whereIn('name', $target_names)->get();
            foreach ($accounting_accounts as $account) {
                \DB::table('accounting_accounts_transactions')->where('accounting_account_id', $account->id)->delete();
                $account->delete();
            }
        }

        // 3. Delete Expense Categories
        ExpenseCategory::whereIn('name', ['Biaya Penyusutan', 'Depreciation expense'])->delete();

        // 4. Run pos:sync-payment-accounting
        try {
            \Illuminate\Support\Facades\Artisan::call('pos:sync-payment-accounting');
        } catch (\Exception $e) {
            \Log::error('Error running pos:sync-payment-accounting in RemoveBiayaPenyusutanAndAkumulasiPenyusutanAccounts migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback needed
    }
}
