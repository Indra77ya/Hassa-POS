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
                    ->whereIn('accounting_accounts.account_primary_type', ['expense', 'expenses'])
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
                    ->whereIn('accounting_accounts.account_primary_type', ['expense', 'expenses'])
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
                            $sub->whereIn('accounting_accounts.account_primary_type', ['expense', 'expenses'])
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

        // Query with support for Opening Balance, Current Period mutation, and Ending Balance
        $raw_accounts = AccountingAccount::leftJoin('accounting_accounts_transactions as AAT', function($join) use ($end_date) {
                                $join->on('AAT.accounting_account_id', '=', 'accounting_accounts.id')
                                     ->whereDate('AAT.operation_date', '<=', $end_date);
                            })
                            ->where('accounting_accounts.business_id', $business_id)
                            ->select(
                                'accounting_accounts.id',
                                'accounting_accounts.name',
                                'accounting_accounts.account_primary_type',
                                DB::raw("SUM(CASE WHEN AAT.type = 'debit' AND DATE(AAT.operation_date) < '{$start_date}' THEN AAT.amount ELSE 0 END) as opening_debit_raw"),
                                DB::raw("SUM(CASE WHEN AAT.type = 'credit' AND DATE(AAT.operation_date) < '{$start_date}' THEN AAT.amount ELSE 0 END) as opening_credit_raw"),
                                DB::raw("SUM(CASE WHEN AAT.type = 'debit' AND DATE(AAT.operation_date) >= '{$start_date}' AND DATE(AAT.operation_date) <= '{$end_date}' THEN AAT.amount ELSE 0 END) as current_debit_raw"),
                                DB::raw("SUM(CASE WHEN AAT.type = 'credit' AND DATE(AAT.operation_date) >= '{$start_date}' AND DATE(AAT.operation_date) <= '{$end_date}' THEN AAT.amount ELSE 0 END) as current_credit_raw")
                            )
                            ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'accounting_accounts.account_primary_type')
                            ->get();

        $accounts = [];
        foreach ($raw_accounts as $act) {
            $op_deb = floatval($act->opening_debit_raw ?? 0);
            $op_crd = floatval($act->opening_credit_raw ?? 0);
            $cur_deb = floatval($act->current_debit_raw ?? 0);
            $cur_crd = floatval($act->current_credit_raw ?? 0);

            // Skip if no activity at all (all are 0)
            if ($op_deb == 0 && $op_crd == 0 && $cur_deb == 0 && $cur_crd == 0) {
                continue;
            }

            // Calculations
            $opening_debit = 0;
            $opening_credit = 0;
            $ending_debit = 0;
            $ending_credit = 0;

            // Debit-Normal accounts
            if (in_array($act->account_primary_type, ['asset', 'expense', 'expenses'])) {
                $op_net = $op_deb - $op_crd;
                if ($op_net >= 0) {
                    $opening_debit = $op_net;
                } else {
                    $opening_credit = abs($op_net);
                }

                $end_net = $op_net + $cur_deb - $cur_crd;
                if ($end_net >= 0) {
                    $ending_debit = $end_net;
                } else {
                    $ending_credit = abs($end_net);
                }
            }
            // Credit-Normal accounts
            else {
                $op_net = $op_crd - $op_deb;
                if ($op_net >= 0) {
                    $opening_credit = $op_net;
                } else {
                    $opening_debit = abs($op_net);
                }

                $end_net = $op_net + $cur_crd - $cur_deb;
                if ($end_net >= 0) {
                    $ending_credit = $end_net;
                } else {
                    $ending_debit = abs($end_net);
                }
            }

            $accounts[] = (object)[
                'name' => $act->name,
                'opening_debit' => $opening_debit,
                'opening_credit' => $opening_credit,
                'current_debit' => $cur_deb,
                'current_credit' => $cur_crd,
                'ending_debit' => $ending_debit,
                'ending_credit' => $ending_credit,
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
        // Profit = Income - Expenses for the period (cumulative up to end_date).
        $total_income = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->whereDate('AAT.operation_date', '<=', $end_date)
                    ->where('accounting_accounts.business_id', $business_id)
                    ->where('accounting_accounts.account_primary_type', 'income')
                    ->select(DB::raw($balance_formula))
                    ->first()->balance ?? 0;

        $total_expenses = AccountingAccount::join('accounting_accounts_transactions as AAT',
                                'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                    ->whereDate('AAT.operation_date', '<=', $end_date)
                    ->where('accounting_accounts.business_id', $business_id)
                    ->whereIn('accounting_accounts.account_primary_type', ['expense', 'expenses'])
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
