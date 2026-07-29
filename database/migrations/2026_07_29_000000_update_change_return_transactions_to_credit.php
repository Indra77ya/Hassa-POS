<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateChangeReturnTransactionsToCredit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Get all account_transactions that represent change returns (is_return = 1) but have 'debit' type
        $transactions = DB::table('account_transactions')
            ->join('transaction_payments', 'account_transactions.transaction_payment_id', '=', 'transaction_payments.id')
            ->where('transaction_payments.is_return', 1)
            ->where('account_transactions.type', 'debit')
            ->select('account_transactions.id', 'account_transactions.accounting_accounts_transaction_id')
            ->get();

        foreach ($transactions as $tx) {
            // Update the core account transaction to 'credit'
            DB::table('account_transactions')
                ->where('id', $tx->id)
                ->update(['type' => 'credit']);

            // Update linked accounting accounts transaction if exists
            if (!empty($tx->accounting_accounts_transaction_id)) {
                DB::table('accounting_accounts_transactions')
                    ->where('id', $tx->accounting_accounts_transaction_id)
                    ->update(['type' => 'credit']);
            }

            // Also search by account_transaction_id in accounting_accounts_transactions just in case
            if (Schema::hasTable('accounting_accounts_transactions')) {
                DB::table('accounting_accounts_transactions')
                    ->where('account_transaction_id', $tx->id)
                    ->update(['type' => 'credit']);
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
        // No down migration needed since we are correcting an incorrect data state
    }
}
