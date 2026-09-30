<?php

namespace Modules\Accounting\Listeners;

use App\BusinessLocation;
use App\Transaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Utils\AccountingUtil;

class MapPayrollTransaction
{
    /**
     * Handle the event or call directly to map payroll transaction.
     *
     * @param  mixed  $event_or_transaction
     * @return void
     */
    public function handle($event_or_transaction)
    {
        if ($event_or_transaction instanceof Transaction) {
            $transaction = $event_or_transaction;
        } elseif (isset($event_or_transaction->transaction)) {
            $transaction = $event_or_transaction->transaction;
        } else {
            return;
        }

        if (!$transaction || $transaction->type !== 'payroll') {
            return;
        }

        $business_id = $transaction->business_id;
        $location_id = $transaction->location_id;

        // 1. Resolve Deposit To (Payroll Expense Account)
        $deposit_to = null;
        if (!empty($location_id)) {
            $business_location = BusinessLocation::find($location_id);
            if ($business_location && !empty($business_location->accounting_default_map)) {
                $accounting_default_map = json_decode($business_location->accounting_default_map, true);
                if (!empty($accounting_default_map['payroll']['deposit_to'])) {
                    $deposit_to = $accounting_default_map['payroll']['deposit_to'];
                } elseif (!empty($accounting_default_map['expense']['deposit_to'])) {
                    $deposit_to = $accounting_default_map['expense']['deposit_to'];
                }
            }
        }

        // Fallback: search for "Payroll Expenses" or "Expenses" account
        if (empty($deposit_to)) {
            $payroll_account = AccountingAccount::where('business_id', $business_id)
                ->where(function ($q) {
                    $q->where('name', 'like', '%Payroll%')
                      ->orWhere('name', 'like', '%Gaji%')
                      ->orWhere('name', 'like', '%Expense%');
                })
                ->where('account_sub_type_id', '!=', null)
                ->first();

            if ($payroll_account) {
                $deposit_to = $payroll_account->id;
            }
        }

        // 2. Check if payment has actually been made
        $payments = \DB::table('transaction_payments')
            ->where('transaction_id', $transaction->id)
            ->where('is_return', 0)
            ->get();

        $paid_amount = $payments->sum('amount');

        // If no payment has been made yet (payment_status is 'due' and paid_amount is 0),
        // delete any existing mapping so Cash/Bank balance is NOT deducted prematurely!
        if ($paid_amount <= 0 && $transaction->payment_status == 'due') {
            $accountingUtil = new AccountingUtil();
            $accountingUtil->deleteMap($transaction->id, null);
            return;
        }

        // Resolve Payment Account dynamically from actual transaction payments
        $payment_account = null;
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

        // Fallback payment account from location map or Cash/Bank account if payment made without specific account
        if (is_null($payment_account) && !empty($location_id)) {
            $business_location = BusinessLocation::find($location_id);
            if ($business_location && !empty($business_location->accounting_default_map)) {
                $accounting_default_map = json_decode($business_location->accounting_default_map, true);
                if (!empty($accounting_default_map['payroll']['payment_account'])) {
                    $payment_account = $accounting_default_map['payroll']['payment_account'];
                } elseif (!empty($accounting_default_map['expense']['payment_account'])) {
                    $payment_account = $accounting_default_map['expense']['payment_account'];
                }
            }
        }

        if (is_null($payment_account)) {
            $cash_account = AccountingAccount::where('business_id', $business_id)
                ->where(function ($q) {
                    $q->where('name', 'like', '%Cash%')
                      ->orWhere('name', 'like', '%Kas%')
                      ->orWhere('name', 'like', '%Bank%');
                })
                ->first();
            if ($cash_account) {
                $payment_account = $cash_account->id;
            }
        }

        // Save accounting mapping only when actual payment exists
        if (!is_null($deposit_to) && !is_null($payment_account) && $paid_amount > 0) {
            $user_id = auth()->id() ?? (request()->hasSession() ? request()->session()->get('user.id') : null) ?? $transaction->created_by ?? 1;
            $accountingUtil = new AccountingUtil();
            $accountingUtil->saveMap('payroll', $transaction->id, $user_id, $business_id, $deposit_to, $payment_account, $transaction->staff_note ?? 'HRM Payroll');
        }
    }
}
