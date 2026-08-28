<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\BusinessLocation;

class MapExpenseTransactions
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
        $business_location = BusinessLocation::find($event->expense->location_id);
        if (!$business_location) {
            return;
        }
        $accounting_default_map = json_decode($business_location->accounting_default_map, true);

        // 1. Resolve Deposit To (Expense Account / Category Account)
        if (!empty($event->expense->expense_category_id)) {
            $deposit_to = isset($accounting_default_map['expense_'.$event->expense->expense_category_id]['deposit_to']) ? $accounting_default_map['expense_'.$event->expense->expense_category_id]['deposit_to'] : null;

            if (is_null($deposit_to)) {
                $deposit_to = isset($accounting_default_map['expense']['deposit_to']) ? $accounting_default_map['expense']['deposit_to'] : null;
            }
        } else {
            $deposit_to = isset($accounting_default_map['expense']['deposit_to']) ? $accounting_default_map['expense']['deposit_to'] : null;
        }

        // 2. Resolve Payment Account dynamically from actual transaction payments if available
        $payment_account = null;
        $payments = \DB::table('transaction_payments')
            ->where('transaction_id', $event->expense->id)
            ->where('is_return', 0)
            ->get();

        foreach ($payments as $payment) {
            if (!empty($payment->account_id)) {
                $payment_account = \DB::table('accounts')
                    ->where('id', $payment->account_id)
                    ->value('accounting_account_id');
                if ($payment_account) {
                    break;
                }
            }
        }

        // Fallback to default mapped payment account if none resolved from payments
        if (is_null($payment_account)) {
            if (!empty($event->expense->expense_category_id)) {
                $payment_account = isset($accounting_default_map['expense_'.$event->expense->expense_category_id]['payment_account']) ? $accounting_default_map['expense_'.$event->expense->expense_category_id]['payment_account'] : null;
                if (is_null($payment_account)) {
                    $payment_account = isset($accounting_default_map['expense']['payment_account']) ? $accounting_default_map['expense']['payment_account'] : null;
                }
            } else {
                $payment_account = isset($accounting_default_map['expense']['payment_account']) ? $accounting_default_map['expense']['payment_account'] : null;
            }
        }

        // if expense is deleted then delete the mapping
        if (isset($event->isDeleted) && $event->isDeleted) {
            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
            $accountingUtil->deleteMap($event->expense->id, null);
        } else {
            if (!is_null($deposit_to) && !is_null($payment_account)) {
                $type = 'expense';
                $id = $event->expense->id;
                $user_id = auth()->id() ?? (request()->hasSession() ? request()->session()->get('user.id') : null) ?? $event->expense->created_by ?? 1;
                $business_id = $event->expense->business_id;
                $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
                $accountingUtil->saveMap($type, $id, $user_id, $business_id, $deposit_to, $payment_account);
            }
        }
    }
}
