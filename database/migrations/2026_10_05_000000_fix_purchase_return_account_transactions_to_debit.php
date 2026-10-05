<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixPurchaseReturnAccountTransactionsToDebit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Find all account_transactions linked to purchase_return transactions or payments on purchase_return transactions where type is 'credit'
        $transactions = DB::table('account_transactions')
            ->leftJoin('transactions', 'account_transactions.transaction_id', '=', 'transactions.id')
            ->leftJoin('transaction_payments', 'account_transactions.transaction_payment_id', '=', 'transaction_payments.id')
            ->leftJoin('transactions as payment_transactions', 'transaction_payments.transaction_id', '=', 'payment_transactions.id')
            ->where(function ($query) {
                $query->where('transactions.type', 'purchase_return')
                      ->orWhere('payment_transactions.type', 'purchase_return');
            })
            ->where('account_transactions.type', 'credit')
            ->select('account_transactions.id', 'account_transactions.accounting_accounts_transaction_id')
            ->get();

        foreach ($transactions as $tx) {
            // Update the core account transaction to 'debit'
            DB::table('account_transactions')
                ->where('id', $tx->id)
                ->update(['type' => 'debit']);

            // Update linked accounting_accounts_transaction if ID is stored on account_transactions
            if (!empty($tx->accounting_accounts_transaction_id) && Schema::hasTable('accounting_accounts_transactions')) {
                DB::table('accounting_accounts_transactions')
                    ->where('id', $tx->accounting_accounts_transaction_id)
                    ->update(['type' => 'debit']);
            }

            // Also update by foreign key account_transaction_id in accounting_accounts_transactions if present
            if (Schema::hasTable('accounting_accounts_transactions')) {
                DB::table('accounting_accounts_transactions')
                    ->where('account_transaction_id', $tx->id)
                    ->update(['type' => 'debit']);
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
