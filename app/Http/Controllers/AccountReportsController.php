<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\BusinessLocation;
use App\TransactionPayment;
use App\Utils\TransactionUtil;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AccountReportsController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $transactionUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function balanceSheet()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $end_date = ! empty(request()->input('end_date')) ? $this->transactionUtil->uf_date(request()->input('end_date')) : \Carbon::now()->format('Y-m-d');
            $location_id = ! empty(request()->input('location_id')) ? request()->input('location_id') : null;

            // Calculate start_date (beginning of financial year of the given end_date)
            $business = \App\Business::where('id', $business_id)->first();
            $start_month = $business ? $business->fy_start_month : 1;

            $end_time = strtotime($end_date);
            $end_year = date('Y', $end_time);
            $end_month_num = date('n', $end_time);

            if ($end_month_num < $start_month) {
                $start_year = $end_year - 1;
            } else {
                $start_year = $end_year;
            }
            $start_date = $start_year.'-'.str_pad($start_month, 2, '0', STR_PAD_LEFT).'-01';

            // Balance formula compatible with both 'expenses' and 'expense' primary types
            $balance_formula = "SUM( IF(
                (accounting_accounts.account_primary_type='asset' AND AAT.type='debit')
                OR (accounting_accounts.account_primary_type='expenses' AND AAT.type='debit')
                OR (accounting_accounts.account_primary_type='expense' AND AAT.type='debit')
                OR (accounting_accounts.account_primary_type='income' AND AAT.type='credit')
                OR (accounting_accounts.account_primary_type='equity' AND AAT.type='credit')
                OR (accounting_accounts.account_primary_type='liability' AND AAT.type='credit'),
                amount, -1*amount)) as balance";

            // Assets query
            $assets_query = \Modules\Accounting\Entities\AccountingAccount::join('accounting_accounts_transactions as AAT',
                                    'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                        ->join('accounting_account_types as AATP',
                                    'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                        ->whereDate('AAT.operation_date', '<=', $end_date)
                        ->where('accounting_accounts.business_id', $business_id)
                        ->whereIn('accounting_accounts.account_primary_type', ['asset']);
            
            if (!empty($location_id)) {
                $assets_query->where(function ($q) use ($location_id) {
                    $q->whereIn('AAT.transaction_id', function($subQuery) use ($location_id) {
                        $subQuery->select('id')->from('transactions')->where('location_id', $location_id);
                    })
                    ->orWhereIn('AAT.transaction_payment_id', function($subQuery) use ($location_id) {
                        $subQuery->select('TP.id')
                            ->from('transaction_payments as TP')
                            ->join('transactions as T', 'TP.transaction_id', '=', 'T.id')
                            ->where('T.location_id', $location_id);
                    });
                });
            }

            $assets = $assets_query->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                        ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                        ->get();

            // Liabilities query
            $liabilities_query = \Modules\Accounting\Entities\AccountingAccount::join('accounting_accounts_transactions as AAT',
                                    'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                        ->join('accounting_account_types as AATP',
                                    'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                        ->whereDate('AAT.operation_date', '<=', $end_date)
                        ->where('accounting_accounts.business_id', $business_id)
                        ->whereIn('accounting_accounts.account_primary_type', ['liability']);

            if (!empty($location_id)) {
                $liabilities_query->where(function ($q) use ($location_id) {
                    $q->whereIn('AAT.transaction_id', function($subQuery) use ($location_id) {
                        $subQuery->select('id')->from('transactions')->where('location_id', $location_id);
                    })
                    ->orWhereIn('AAT.transaction_payment_id', function($subQuery) use ($location_id) {
                        $subQuery->select('TP.id')
                            ->from('transaction_payments as TP')
                            ->join('transactions as T', 'TP.transaction_id', '=', 'T.id')
                            ->where('T.location_id', $location_id);
                    });
                });
            }

            $liabilities = $liabilities_query->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                        ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                        ->get();

            // Equities query
            $equities_query = \Modules\Accounting\Entities\AccountingAccount::join('accounting_accounts_transactions as AAT',
                                    'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                        ->join('accounting_account_types as AATP',
                                    'AATP.id', '=', 'accounting_accounts.account_sub_type_id')
                        ->whereDate('AAT.operation_date', '<=', $end_date)
                        ->where('accounting_accounts.business_id', $business_id)
                        ->whereIn('accounting_accounts.account_primary_type', ['equity']);

            if (!empty($location_id)) {
                $equities_query->where(function ($q) use ($location_id) {
                    $q->whereIn('AAT.transaction_id', function($subQuery) use ($location_id) {
                        $subQuery->select('id')->from('transactions')->where('location_id', $location_id);
                    })
                    ->orWhereIn('AAT.transaction_payment_id', function($subQuery) use ($location_id) {
                        $subQuery->select('TP.id')
                            ->from('transaction_payments as TP')
                            ->join('transactions as T', 'TP.transaction_id', '=', 'T.id')
                            ->where('T.location_id', $location_id);
                    });
                });
            }

            $equities = $equities_query->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
                        ->groupBy('accounting_accounts.id', 'accounting_accounts.name', 'AATP.name')
                        ->get();

            // Income query for current period net profit
            $total_income_query = \Modules\Accounting\Entities\AccountingAccount::join('accounting_accounts_transactions as AAT',
                                    'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                        ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                        ->where('accounting_accounts.business_id', $business_id)
                        ->where('accounting_accounts.account_primary_type', 'income');

            if (!empty($location_id)) {
                $total_income_query->where(function ($q) use ($location_id) {
                    $q->whereIn('AAT.transaction_id', function($subQuery) use ($location_id) {
                        $subQuery->select('id')->from('transactions')->where('location_id', $location_id);
                    })
                    ->orWhereIn('AAT.transaction_payment_id', function($subQuery) use ($location_id) {
                        $subQuery->select('TP.id')
                            ->from('transaction_payments as TP')
                            ->join('transactions as T', 'TP.transaction_id', '=', 'T.id')
                            ->where('T.location_id', $location_id);
                    });
                });
            }

            $total_income = $total_income_query->select(DB::raw($balance_formula))
                        ->first()->balance ?? 0;

            // Expenses query for current period net profit
            $total_expenses_query = \Modules\Accounting\Entities\AccountingAccount::join('accounting_accounts_transactions as AAT',
                                    'AAT.accounting_account_id', '=', 'accounting_accounts.id')
                        ->whereBetween('AAT.operation_date', [$start_date, $end_date])
                        ->where('accounting_accounts.business_id', $business_id)
                        ->where('accounting_accounts.account_primary_type', 'expenses');

            if (!empty($location_id)) {
                $total_expenses_query->where(function ($q) use ($location_id) {
                    $q->whereIn('AAT.transaction_id', function($subQuery) use ($location_id) {
                        $subQuery->select('id')->from('transactions')->where('location_id', $location_id);
                    })
                    ->orWhereIn('AAT.transaction_payment_id', function($subQuery) use ($location_id) {
                        $subQuery->select('TP.id')
                            ->from('transaction_payments as TP')
                            ->join('transactions as T', 'TP.transaction_id', '=', 'T.id')
                            ->where('T.location_id', $location_id);
                    });
                });
            }

            $total_expenses = $total_expenses_query->select(DB::raw($balance_formula))
                        ->first()->balance ?? 0;

            $current_period_net_profit = $total_income - $total_expenses;

            $output = [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equities' => $equities,
                'current_period_net_profit' => $current_period_net_profit,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];

            return $output;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('account_reports.balance_sheet')->with(compact('business_locations'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function trialBalance()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        if (request()->ajax()) {
            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start_date = $this->transactionUtil->uf_date(request()->start_date);
                $end_date = $this->transactionUtil->uf_date(request()->end_date);
            } else {
                $business_util = new \App\Utils\BusinessUtil();
                $fy = $business_util->getCurrentFinancialYear($business_id);
                $start_date = $fy['start'];
                $end_date = $fy['end'];
            }
            $location_id = ! empty(request()->input('location_id')) ? request()->input('location_id') : null;

            // Query to fetch the trial balance details of accounting accounts
            $raw_accounts_query = \Modules\Accounting\Entities\AccountingAccount::leftJoin('accounting_accounts_transactions as AAT', function($join) use ($end_date) {
                                $join->on('AAT.accounting_account_id', '=', 'accounting_accounts.id')
                                     ->whereRaw('DATE(AAT.operation_date) <= ?', [$end_date]);
                            })
                            ->where('accounting_accounts.business_id', $business_id);

            // Filter by location_id (business location) if provided
            if (! empty($location_id)) {
                $raw_accounts_query->where(function ($q) use ($location_id) {
                    $q->whereIn('AAT.transaction_id', function($subQuery) use ($location_id) {
                        $subQuery->select('id')->from('transactions')->where('location_id', $location_id);
                    })
                    ->orWhereIn('AAT.transaction_payment_id', function($subQuery) use ($location_id) {
                        $subQuery->select('TP.id')
                            ->from('transaction_payments as TP')
                            ->join('transactions as T', 'TP.transaction_id', '=', 'T.id')
                            ->where('T.location_id', $location_id);
                    });
                });
            }

            $raw_accounts = $raw_accounts_query->select(
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
                if (in_array($act->account_primary_type, ['asset', 'expenses'])) {
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

            return response()->json([
                'accounts' => $accounts,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        // Get default start & end dates
        $business_util = new \App\Utils\BusinessUtil();
        $fy = $business_util->getCurrentFinancialYear($business_id);
        $start_date = $fy['start'];
        $end_date = $fy['end'];

        return view('account_reports.trial_balance')->with(compact('business_locations', 'start_date', 'end_date'));
    }

    /**
     * Retrives account balances.
     *
     * @return Obj
     */
    private function getAccountBalance($business_id, $end_date, $account_type = 'others', $location_id = null)
    {
        $start_date = ! empty(request()->input('start_date')) ? $this->transactionUtil->uf_date(request()->input('start_date')) : \Carbon::now()->startOfMonth()->format('Y-m-d');

        $query = Account::leftjoin('account_types as ATY', 'accounts.account_type_id', '=', 'ATY.id')
            ->leftjoin('account_types as PATY', 'ATY.parent_account_type_id', '=', 'PATY.id')
            ->where('accounts.business_id', $business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        $account_ids = [];
        if ($permitted_locations != 'all' || ! empty($location_id)) {
            $locations_to_check = ($location_id) ? [$location_id] : $permitted_locations;
            $locations = BusinessLocation::where('business_id', $business_id)
                            ->whereIn('id', $locations_to_check)
                            ->get();

            foreach ($locations as $location) {
                if (! empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    foreach ($default_payment_accounts as $key => $account) {
                        if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }
                }
            }
            $account_ids = array_unique($account_ids);
            $query->whereIn('accounts.id', $account_ids);
        }

        $account_details = $query->select([
            'accounts.id',
            'accounts.name',
            'accounts.normal_balance',
            'ATY.name as type_name',
            'ATY.fixed_key as fixed_key',
            'PATY.name as parent_type_name',
            DB::raw("(SELECT SUM(IF(type='debit', amount, 0)) FROM account_transactions WHERE account_id = accounts.id AND deleted_at IS NULL AND DATE(operation_date) < ?) as opening_debit"),
            DB::raw("(SELECT SUM(IF(type='credit', amount, 0)) FROM account_transactions WHERE account_id = accounts.id AND deleted_at IS NULL AND DATE(operation_date) < ?) as opening_credit"),
            DB::raw("(SELECT SUM(amount) FROM account_transactions WHERE account_id = accounts.id AND type='debit' AND deleted_at IS NULL AND DATE(operation_date) >= ? AND DATE(operation_date) <= ?) as total_debit"),
            DB::raw("(SELECT SUM(amount) FROM account_transactions WHERE account_id = accounts.id AND type='credit' AND deleted_at IS NULL AND DATE(operation_date) >= ? AND DATE(operation_date) <= ?) as total_credit"),
        ])
        ->addBinding($start_date, 'select')
        ->addBinding($start_date, 'select')
        ->addBinding($start_date, 'select')
        ->addBinding($end_date, 'select')
        ->addBinding($start_date, 'select')
        ->addBinding($end_date, 'select')
        ->get();

        return $account_details;
    }

    /**
     * Displays payment account report.
     *
     * @return Response
     */
    public function paymentAccountReport()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        if (request()->ajax()) {
            $query = TransactionPayment::leftjoin(
                'transactions as T',
                'transaction_payments.transaction_id',
                '=',
                'T.id'
            )
                                    ->leftjoin('accounts as A', 'transaction_payments.account_id', '=', 'A.id')
                                    ->where('transaction_payments.business_id', $business_id)
                                    ->whereNull('transaction_payments.parent_id')
                                    ->where('transaction_payments.method', '!=', 'advance')
                                    ->leftjoin('contacts as c', 'transaction_payments.payment_for', '=', 'c.id')
                                    ->select([
                                        'paid_on',
                                        'payment_ref_no',
                                        'T.ref_no',
                                        'T.invoice_no',
                                        'T.type',
                                        'T.id as transaction_id',
                                        'A.name as account_name',
                                        'A.account_number',
                                        'transaction_payments.id as payment_id',
                                        'transaction_payments.account_id',
                                        'c.name as contact_name',
                                        'c.type as contact_type',
                                        'transaction_payments.is_advance',
                                        'transaction_payments.amount',
                                    ]);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('T.location_id', $permitted_locations);
            }

            $start_date = ! empty(request()->input('start_date')) ? request()->input('start_date') : '';
            $end_date = ! empty(request()->input('end_date')) ? request()->input('end_date') : '';

            if (! empty($start_date) && ! empty($end_date)) {
                $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
            }

            $account_id = ! empty(request()->input('account_id')) ? request()->input('account_id') : '';

            if ($account_id == 'none') {
                $query->whereNull('account_id');
            } elseif (! empty($account_id)) {
                $query->where('account_id', $account_id);
            }

            return DataTables::of($query)
                    ->editColumn('paid_on', function ($row) {
                        return $this->transactionUtil->format_date($row->paid_on, true);
                    })
                    ->editColumn('amount', function ($row) {
                        return $this->transactionUtil->num_f($row->amount, true);
                    })
                    ->addColumn('details', function ($row) {
                        $details = '';

                        if ($row->contact_type == 'supplier') {
                            $details = '<b>'.__('role.supplier').':</b> '.$row->contact_name;
                        } else {
                            $details = '<b>'.__('role.customer').':</b> '.$row->contact_name;
                        }

                        return $details;
                    })
                    ->addColumn('action', function ($row) {
                        $action = '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info
                        tw-dw-btn-xs btn-modal"
                        data-container=".view_modal" 
                        data-href="'.action([\App\Http\Controllers\AccountReportsController::class, 'getLinkAccount'], [$row->payment_id]).'">'.__('account.link_account').'</button>';

                        return $action;
                    })
                    ->addColumn('account', function ($row) {
                        $account = '';
                        if (! empty($row->account_id)) {
                            $account = $row->account_name.' - '.$row->account_number;
                        }

                        return $account;
                    })
                    ->addColumn('transaction_number', function ($row) {
                        $html = $row->ref_no;
                        if ($row->type == 'sell') {
                            $html = '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info btn-modal"
                                    data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction_id]).'" data-container=".view_modal">'.$row->invoice_no.'</button>';
                        } elseif ($row->type == 'purchase') {
                            $html = '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info btn-modal"
                                    data-href="'.action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->transaction_id]).'" data-container=".view_modal">'.$row->ref_no.'</button>';
                        }

                        return $html;
                    })
                    ->editColumn('type', function ($row) {
                        $type = $row->type;
                        if ($row->type == 'sell') {
                            $type = __('sale.sale');
                        } elseif ($row->type == 'purchase') {
                            $type = __('lang_v1.purchase');
                        } elseif ($row->type == 'expense') {
                            $type = __('lang_v1.expense');
                        } elseif ($row->is_advance == 1) {
                            $type = __('lang_v1.advance');
                        }

                        return $type;
                    })
                    ->filterColumn('account', function ($query, $keyword) {
                        $query->where('A.name', 'like', ["%{$keyword}%"])
                            ->orWhere('account_number', 'like', ["%{$keyword}%"]);
                    })
                    ->filterColumn('transaction_number', function ($query, $keyword) {
                        $query->where('T.invoice_no', 'like', ["%{$keyword}%"])
                            ->orWhere('T.ref_no', 'like', ["%{$keyword}%"]);
                    })
                    ->rawColumns(['action', 'transaction_number', 'details'])
                    ->make(true);
        }

        $accounts = Account::forDropdown($business_id, false);
        $accounts = ['' => __('messages.all'), 'none' => __('lang_v1.none')] + $accounts;

        return view('account_reports.payment_account_report')
                ->with(compact('accounts'));
    }

    /**
     * Shows form to link account with a payment.
     *
     * @return Response
     */
    public function getLinkAccount($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $payment = TransactionPayment::where('business_id', $business_id)->findOrFail($id);
            $accounts = Account::forDropdown($business_id, false);

            return view('account_reports.link_account_modal')
                ->with(compact('accounts', 'payment'));
        }
    }

    /**
     * Links account with a payment.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postLinkAccount(Request $request)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');
            if (request()->ajax()) {
                $payment_id = $request->input('transaction_payment_id');
                $account_id = $request->input('account_id');

                $payment = TransactionPayment::with(['transaction'])->where('business_id', $business_id)->findOrFail($payment_id);
                $payment->account_id = $account_id;
                $payment->save();

                $payment_type = ! empty($payment->transaction->type) ? $payment->transaction->type : null;
                if (empty($payment_type)) {
                    $child_payment = TransactionPayment::where('parent_id', $payment->id)->first();
                    $payment_type = ! empty($child_payment->transaction->type) ? $child_payment->transaction->type : null;
                }

                AccountTransaction::updateAccountTransaction($payment, $payment_type);
            }
            $output = ['success' => true,
                'msg' => __('account.account_linked_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
