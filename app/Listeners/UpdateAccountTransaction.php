<?php

namespace App\Listeners;

use App\AccountTransaction;
use App\Utils\ModuleUtil;

class UpdateAccountTransaction
{
    protected $moduleUtil;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (! $this->moduleUtil->isModuleEnabled('account')) {
            return true;
        }

        $transaction_id = $event->transactionPayment->transaction_id;
        if (!empty($transaction_id)) {
            $transaction = \App\Transaction::find($transaction_id);
            if ($transaction && in_array($transaction->type, ['sell', 'purchase', 'expense'])) {
                return true;
            }
        }

        AccountTransaction::updateAccountTransaction($event->transactionPayment, $event->transactionType);
    }
}
