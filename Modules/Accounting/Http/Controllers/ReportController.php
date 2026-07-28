<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use DB;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Utils\AccountingUtil;

class ReportController extends Controller
{
    protected $accountingUtil;

    protected $businessUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(AccountingUtil $accountingUtil, BusinessUtil $businessUtil,
    ModuleUtil $moduleUtil)
    {
        $this->accountingUtil = $accountingUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        $first_account = AccountingAccount::where('business_id', $business_id)
                            ->where('status', 'active')
                            ->first();
        $ledger_url = null;
        if (! empty($first_account)) {
            $ledger_url = route('accounting.ledger', $first_account);
        }

        return view('accounting::report.index')
            ->with(compact('ledger_url'));
    }

    /**
     * Profit & Loss Statement (Laporan Laba Rugi)
     *
     * @return Response
     */
    public function profitLoss()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        if (! empty(request()->start_date) && ! empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        // Laba Rugi: Calculates balances of Income, Cost of Sales, Expenses within the date range.
        $balance_formula = $this->accountingUtil->balanceFormula();

        // 1. Pendapatan (Income)
        $incomes = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where('accounting_accounts.account_primary_type', 'income')
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        // 2. Harga Pokok Penjualan (Cost of Sale / HPP - sub_type_id = 13)
        $cost_of_sales = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where('accounting_accounts.account_primary_type', 'expenses')
                    ->where('accounting_accounts.account_sub_type_id', 13)
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        // 3. Beban Operasional (Expenses - sub_type_id = 14)
        $operating_expenses = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where('accounting_accounts.account_primary_type', 'expenses')
                    ->where('accounting_accounts.account_sub_type_id', 14)
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        // 4. Pendapatan/Beban Non-Operasional / Lain-lain (other_income / other_expense - sub_type_id = 12 / 15)
        $other_incomes = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where(function($q) {
                        $q->where(function($sub) {
                            $sub->where('accounting_accounts.account_primary_type', 'income')
                                ->where('accounting_accounts.account_sub_type_id', 12);
                        })->orWhere(function($sub) {
                            $sub->where('accounting_accounts.account_primary_type', 'expenses')
                                ->where('accounting_accounts.account_sub_type_id', 15);
                        });
                    })
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        return view('accounting::report.profit_loss')
            ->with(compact('incomes', 'cost_of_sales', 'operating_expenses', 'other_incomes', 'start_date', 'end_date'));
    }

    /**
     * Trial Balance
     *
     * @return Response
     */
    public function trialBalance()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        if (! empty(request()->start_date) && ! empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        // Standard Trial Balance retrieves all transactions up to $end_date to calculate net balances.
        $raw_accounts = AccountingAccount::leftJoin('accounting_accounts_transactions as AAT', function($join) use ($end_date) {
                                $join->on('AAT.accounting_account_id', '=', 'accounting_accounts.id')
                                     ->whereDate('AAT.operation_date', '<=', $end_date);
                            })
                            ->where('accounting_accounts.business_id', $business_id)
                            ->select(
                                'accounting_accounts.name',
                                'accounting_accounts.account_primary_type',
                                DB::raw("SUM(IF(AAT.type = 'credit', AAT.amount, 0)) as total_credit"),
                                DB::raw("SUM(IF(AAT.type = 'debit', AAT.amount, 0)) as total_debit")
                            )
                            ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'accounting_accounts.account_primary_type')
                            ->get();

        $accounts = [];
        foreach ($raw_accounts as $act) {
            $total_debit = $act->total_debit ?? 0;
            $total_credit = $act->total_credit ?? 0;

            if ($total_debit == 0 && $total_credit == 0) {
                continue;
            }

            $debit_balance = 0;
            $credit_balance = 0;

            if (in_array($act->account_primary_type, ['asset', 'expenses'])) {
                $net = $total_debit - $total_credit;
                if ($net >= 0) {
                    $debit_balance = $net;
                } else {
                    $credit_balance = abs($net);
                }
            } else {
                $net = $total_credit - $total_debit;
                if ($net >= 0) {
                    $credit_balance = $net;
                } else {
                    $debit_balance = abs($net);
                }
            }

            $accounts[] = (object)[
                'name' => $act->name,
                'debit_balance' => $debit_balance,
                'credit_balance' => $credit_balance,
            ];
        }

        return view('accounting::report.trial_balance')
            ->with(compact('accounts', 'start_date', 'end_date'));
    }

    /**
     * Trial Balance
     *
     * @return Response
     */
    public function balanceSheet()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        if (! empty(request()->start_date) && ! empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        $balance_formula = $this->accountingUtil->balanceFormula();

        // Neraca / Balance Sheet calculates cumulative assets, liabilities, equities up to $end_date
        $assets = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereDate('AAT.operation_date', '<=', $end_date)
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->whereIn('accounting_accounts.account_primary_type', ['asset'])
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        $liabilities = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereDate('AAT.operation_date', '<=', $end_date)
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->whereIn('accounting_accounts.account_primary_type', ['liability'])
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        $equities = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->join('accounting_account_types as AATP',
                                'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                    ->whereDate('AAT.operation_date', '<=', $end_date)
                    ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                    ->where('accounting_accounts.business_id', $business_id)
                    ->whereIn('accounting_accounts.account_primary_type', ['equity'])
                    ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                    ->get();

        // Calculate Net Profit of the current period up to $end_date to balance the Balance Sheet dynamically
        // Profit = Income - Expenses for the period.
        $total_income = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where('accounting_accounts.account_primary_type', 'income')
                    ->select(DB::raw($balance_formula))
                    ->first()->balance ?? 0;

        $total_expenses = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where('accounting_accounts.account_primary_type', 'expenses')
                    ->select(DB::raw($balance_formula))
                    ->first()->balance ?? 0;

        $current_period_net_profit = $total_income - $total_expenses;

        return view('accounting::report.balance_sheet')
            ->with(compact('assets', 'liabilities', 'equities', 'current_period_net_profit', 'start_date', 'end_date'));
    }

    public function accountReceivableAgeingReport()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = request()->input('location_id', null);

        $report_details = $this->accountingUtil->getAgeingReport($business_id, 'sell', 'contact', $location_id);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('accounting::report.account_receivable_ageing_report')
        ->with(compact('report_details', 'business_locations'));
    }

    public function accountPayableAgeingReport()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = request()->input('location_id', null);
        $report_details = $this->accountingUtil->getAgeingReport($business_id, 'purchase', 'contact',
        $location_id);
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('accounting::report.account_payable_ageing_report')
        ->with(compact('report_details', 'business_locations'));
    }

    public function accountReceivableAgeingDetails()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = request()->input('location_id', null);

        $report_details = $this->accountingUtil->getAgeingReport($business_id, 'sell', 'due_date',
        $location_id);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('accounting::report.account_receivable_ageing_details')
        ->with(compact('business_locations', 'report_details'));
    }

    public function accountPayableAgeingDetails()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_reports'))) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = request()->input('location_id', null);

        $report_details = $this->accountingUtil->getAgeingReport($business_id, 'purchase', 'due_date',
        $location_id);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('accounting::report.account_payable_ageing_details')
        ->with(compact('business_locations', 'report_details'));
    }
}
