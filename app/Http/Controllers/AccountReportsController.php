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
            $end_date = ! empty(request()->input('end_date')) ? $this->transactionUtil->uf_date(request()->input('end_date')) : \Carbon::now()->format('Y-m-d');
            $location_id = ! empty(request()->input('location_id')) ? request()->input('location_id') : null;

            $purchase_details = $this->transactionUtil->getPurchaseTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );
            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );

            $transaction_types = ['sell_return'];
            $sell_return_details = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                null,
                $end_date,
                $location_id
            );

            $account_details = $this->getAccountBalance($business_id, $end_date, 'others', $location_id);

            $permitted_locations = auth()->user()->permitted_locations();
            $pl_details = $this->transactionUtil->getProfitLossDetails($business_id, $location_id, '1970-01-01', $end_date, null, $permitted_locations);

            $output = [
                'supplier_due' => $purchase_details['purchase_due'],
                'customer_due' => $sell_details['invoice_due'] - $sell_return_details['total_sell_return_inc_tax'],
                'account_balances' => $account_details,
                'total_sell' => $pl_details['total_sell'],
                'total_purchase' => $pl_details['total_purchase'],
                'total_expense' => $pl_details['total_expense'],
                'total_adjustment' => $pl_details['total_adjustment'],
                'total_recovered' => $pl_details['total_recovered'],
                'total_purchase_return' => $pl_details['total_purchase_return'],
                'total_sell_return' => $pl_details['total_sell_return'],
                'opening_stock' => $pl_details['opening_stock'],
                'total_sell_discount' => $pl_details['total_sell_discount'],
                'total_purchase_discount' => $pl_details['total_purchase_discount'],
                'total_reward_amount' => $pl_details['total_reward_amount'],
                'total_sell_round_off' => $pl_details['total_sell_round_off'],
                'total_sell_tax' => $pl_details['total_sell_tax'] ?? 0,
                'total_purchase_tax' => $pl_details['total_purchase_tax'] ?? 0,
                'total_sell_shipping_charge' => $pl_details['total_sell_shipping_charge'] ?? 0,
                'total_sell_additional_expense' => $pl_details['total_sell_additional_expense'] ?? 0,
            ];

            return $output;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('account_reports.trial_balance')->with(compact('business_locations'));
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
