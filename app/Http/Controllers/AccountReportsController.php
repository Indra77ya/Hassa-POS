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

            //Get Closing stock
            $permitted_locations = auth()->user()->permitted_locations();
            
            $closing_stock = $this->transactionUtil->getOpeningClosingStock(
                $business_id,
                $end_date,
                $location_id,
                $permitted_locations
            );

            $accounts = Account::leftjoin(
                'account_transactions as AT',
                'AT.account_id',
                '=',
                'accounts.id'
            )
            ->leftjoin('account_types as ATY', 'accounts.account_type_id', '=', 'ATY.id')
            ->leftjoin('account_types as PATY', 'ATY.parent_account_type_id', '=', 'PATY.id')
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->whereDate('AT.operation_date', '<=', $end_date);

            // Removed restrictive filtering to include all accounts in Balance Sheet

            $accounts = $accounts->select([
                'accounts.name as account_name',
                'ATY.name as type_name',
                'PATY.name as parent_type_name',
                DB::raw("SUM( IF(AT.type='credit', amount, 0) ) as credit_balance"),
                DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as debit_balance"),
            ])
            ->groupBy('accounts.id', 'accounts.name', 'ATY.name', 'PATY.name')
            ->get();

            $assets = [
                'current_assets' => [],
                'fixed_assets' => [],
                'other_assets' => [],
            ];
            $liabilities = [
                'current_liabilities' => [],
                'long_term_liabilities' => [],
            ];
            $equity = [];

            foreach ($accounts as $account) {
                $type = strtolower($account->type_name . ' ' . $account->parent_type_name);
                $name = strtolower($account->account_name);

                if (str_contains($type, 'liability') || str_contains($type, 'utang') || str_contains($type, 'kewajiban') || str_contains($type, 'pasiva') || str_contains($name, 'utang') || str_contains($name, 'kewajiban')) {
                    $account->balance = $account->credit_balance - $account->debit_balance;
                    if (str_contains($type, 'long term') || str_contains($type, 'jangka panjang') || str_contains($name, 'jangka panjang')) {
                        $liabilities['long_term_liabilities'][] = $account;
                    } else {
                        $liabilities['current_liabilities'][] = $account;
                    }
                } elseif (str_contains($type, 'equity') || str_contains($type, 'modal') || str_contains($type, 'ekuitas') || str_contains($type, 'capital') || str_contains($name, 'modal') || str_contains($name, 'ekuitas')) {
                    $account->balance = $account->credit_balance - $account->debit_balance;
                    $equity[] = $account;
                } elseif (str_contains($type, 'asset') || str_contains($type, 'aktiva') || str_contains($type, 'harta') || str_contains($type, 'saving') || str_contains($type, 'current') || str_contains($name, 'kas') || str_contains($name, 'bank') || str_contains($name, 'piutang')) {
                    $account->balance = $account->debit_balance - $account->credit_balance;
                    if (str_contains($type, 'fixed') || str_contains($type, 'tetap') || str_contains($name, 'tetap') || str_contains($name, 'tanah') || str_contains($name, 'bangunan') || str_contains($name, 'mesin') || str_contains($name, 'peralatan')) {
                        $assets['fixed_assets'][] = $account;
                    } elseif (str_contains($type, 'other') || str_contains($type, 'lainnya') || str_contains($name, 'lainnya')) {
                        $assets['other_assets'][] = $account;
                    } else {
                        $assets['current_assets'][] = $account;
                    }
                } else {
                    $account->balance = $account->debit_balance - $account->credit_balance;
                    $assets['current_assets'][] = $account;
                }
            }

            // Calculate Retained Earnings
            $retained_earnings = $this->transactionUtil->getProfitLossDetails($business_id, $location_id, '1970-01-01', $end_date, null, $permitted_locations);

            $output = [
                'supplier_due' => $purchase_details['purchase_due'],
                'customer_due' => $sell_details['invoice_due'] - $sell_return_details['total_sell_return_inc_tax'],
                'closing_stock' => $closing_stock,
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equity,
                'retained_earnings' => $retained_earnings['net_profit'],
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
        $query = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
        ->leftjoin('account_types as ATY', 'accounts.account_type_id', '=', 'ATY.id')
        ->leftjoin('account_types as PATY', 'ATY.parent_account_type_id', '=', 'PATY.id')
        ->whereNull('AT.deleted_at')
        ->where('accounts.business_id', $business_id)
        ->whereDate('AT.operation_date', '<=', $end_date);

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
            'accounts.name',
            'ATY.name as type_name',
            'PATY.name as parent_type_name',
            DB::raw("SUM( IF(AT.type='credit', amount, 0) ) as total_credit"),
            DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as total_debit"),
        ])
        ->groupBy('accounts.id', 'accounts.name', 'ATY.name', 'PATY.name')
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
