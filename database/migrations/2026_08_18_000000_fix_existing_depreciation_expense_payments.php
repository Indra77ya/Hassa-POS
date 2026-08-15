<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ExpenseController;
use App\Transaction;
use App\Utils\TransactionUtil;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transactionUtil = new TransactionUtil();

        $depreciationExpenses = Transaction::whereIn('type', ['expense', 'expense_refund'])
            ->whereNotNull('expense_category_id')
            ->get();

        foreach ($depreciationExpenses as $expense) {
            if (ExpenseController::isDepreciationCategory($expense->expense_category_id, $expense->business_id)) {
                $akumulasi_id = ExpenseController::getAccumulatedDepreciationAccountId($expense->business_id);
                if ($akumulasi_id) {
                    $paymentCount = DB::table('transaction_payments')->where('transaction_id', $expense->id)->count();
                    if ($paymentCount == 0) {
                        $payments = [[
                            'amount' => $expense->final_total,
                            'method' => 'cash',
                            'account_id' => $akumulasi_id,
                            'paid_on' => $expense->transaction_date,
                        ]];
                        $transactionUtil->createOrUpdatePaymentLines($expense, $payments, $expense->business_id);
                        $transactionUtil->updatePaymentStatus($expense->id, $expense->final_total);
                    } else {
                        DB::table('transaction_payments')->where('transaction_id', $expense->id)->update([
                            'amount' => $expense->final_total,
                            'account_id' => $akumulasi_id,
                        ]);
                        \App\AccountTransaction::where('transaction_id', $expense->id)->update(['account_id' => $akumulasi_id]);
                        $transactionUtil->updatePaymentStatus($expense->id, $expense->final_total);
                    }
                }
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
        //
    }
};
