<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\BusinessLocation;
use App\Transaction;

class MapPaymentTransaction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $payment = $event->transactionPayment;
        
        // If payment is being deleted, always hard delete any accounting mapping for this specific payment ID
        $is_deleted = ($event instanceof \App\Events\TransactionPaymentDeleted) || (isset($event->isDeleted) && $event->isDeleted);
        if ($is_deleted && !empty($payment->id)) {
            \Modules\Accounting\Entities\AccountingAccountsTransaction::where('transaction_payment_id', $payment->id)->delete();
        }

        if (empty($payment->transaction_id)) {
            return;
        }

        $transaction = Transaction::find($payment->transaction_id);
        if (!$transaction) {
            return;
        }

        // Bypass mapping inside MapPaymentTransaction if the transaction was created/updated recently (e.g., within 30 seconds),
        // as the core mapping has already been or is being processed by MapSellTransaction, MapPurchaseTransaction, or MapExpenseTransactions.
        // It is only allowed to trigger for past transactions (Pay Due).
        // However, if a payment is being deleted or updated, we must always update the mappings immediately to keep the payment accounts updated.
        $is_deletion_or_update = ($event instanceof \App\Events\TransactionPaymentDeleted) || ($event instanceof \App\Events\TransactionPaymentUpdated) || (!empty($event->isDeleted));

        if (!$is_deletion_or_update && in_array($transaction->type, ['sell', 'purchase', 'expense'])) {
            $is_past_transaction = !empty($transaction->created_at) && now()->diffInSeconds($transaction->created_at) > 30;
            if (!$is_past_transaction) {
                return;
            }
        }

        if ($transaction->type == 'sell') {
            // Re-run MapSellTransaction for this sale to update cash, receivable and revenue legs
            $mapSell = new \Modules\Accounting\Listeners\MapSellTransaction();
            $mapSell->handle(new \App\Events\SellCreatedOrModified($transaction));
            return;
        }

        if ($transaction->type == 'purchase') {
            // Re-run MapPurchaseTransaction for this purchase to update cash, payable and inventory legs
            $mapPurchase = new \Modules\Accounting\Listeners\MapPurchaseTransaction();
            $mapPurchase->handle(new \App\Events\PurchaseCreatedOrModified($transaction));
            return;
        }

        if ($transaction->type == 'expense') {
            $type = 'expense_payment';
        } else {
            return;
        }

        // if payment is deleted then delete the mapping
        if (isset($event->isDeleted) && $event->isDeleted) {
            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
            $accountingUtil->deleteMap($payment->transaction_id, null);
            return;
        }

        //get location setting
        $business_location = BusinessLocation::find($transaction->location_id);
        $accounting_default_map = json_decode($business_location->accounting_default_map, true);

        //check if default map is set or not, if set the proceed.
        $deposit_to = isset($accounting_default_map[$type]['deposit_to']) ? $accounting_default_map[$type]['deposit_to'] : null;
        $payment_account = isset($accounting_default_map[$type]['payment_account']) ? $accounting_default_map[$type]['payment_account'] : null;

        if (!isset($event->isDeleted) || !$event->isDeleted) {
            //Do the mapping
            if (!is_null($deposit_to) && !is_null($payment_account)) {
                $payment_id = $payment->id;
                $user_id = request()->session()->get('user.id') ?? 1;
                $business_id = $transaction->business_id;
                
                $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
                $accountingUtil->saveMap($type, $payment_id, $user_id, $business_id, $deposit_to, $payment_account);
            }
        }
    }
}
