<?php

namespace App\Console\Commands;

use App\Http\Controllers\MidtransController;
use App\Transaction;
use App\Utils\TransactionUtil;
use Illuminate\Console\Command;

class SyncMidtransPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:sync-midtrans {--transaction_id= : Specific transaction ID to sync} {--business_id= : Filter by business ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalize and sync payment for Midtrans transactions recorded without payment lines';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TransactionUtil $transactionUtil)
    {
        $transactionId = $this->option('transaction_id');
        $businessId = $this->option('business_id');

        if (!$transactionId) {
            $this->error('Please specify a --transaction_id to sync.');
            return 1;
        }

        $query = Transaction::with(['business', 'sell_lines.product'])
            ->where('type', 'sell')
            ->where(function ($q) {
                $q->whereNull('payment_status')
                  ->orWhere('payment_status', '!=', 'paid');
            })
            ->where(function ($q) use ($transactionId) {
                $q->where('id', $transactionId)
                  ->orWhere('invoice_no', $transactionId)
                  ->orWhere('invoice_no', sprintf('%04d', $transactionId));
            });

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $transactions = $query->get();

        if ($transactions->isEmpty()) {
            $this->info('No unpaid transactions found to sync.');
            return 0;
        }

        $midtransController = new MidtransController($transactionUtil);
        $count = 0;

        foreach ($transactions as $transaction) {
            // Provide session context for CLI execution
            if ($transaction->created_by) {
                $user = \App\User::find($transaction->created_by);
                if ($user) {
                    auth()->login($user);
                    session(['user.business_id' => $transaction->business_id]);
                }
            }

            $midtransController->finalizeAndPayTransaction($transaction, 'MID-POS-MANUAL-SYNC-' . $transaction->id);
            $count++;
            $this->info("Transaction #{$transaction->id} (Invoice: {$transaction->invoice_no}) synced to paid.");
        }

        $this->info("Successfully synced {$count} Midtrans transaction(s).");
        return 0;
    }
}
