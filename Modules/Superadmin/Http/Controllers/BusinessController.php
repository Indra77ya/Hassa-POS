<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Business;
use App\Product;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\VariationLocationDetails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Superadmin\Entities\Package;
use Modules\Superadmin\Notifications\PasswordUpdateNotification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class BusinessController extends BaseController
{
    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
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
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $date_today = \Carbon::today();
            $businesses = Business::leftjoin('subscriptions AS s', function ($join) use ($date_today) {
                $join->on('business.id', '=', 's.business_id')
                                    ->whereDate('s.start_date', '<=', $date_today)
                                    ->whereDate('s.end_date', '>=', $date_today)
                                    ->where('s.status', 'approved');
            })
                            ->leftjoin('packages as p', 's.package_id', '=', 'p.id')
                            ->leftjoin('business_locations as bl', 'business.id', '=', 'bl.business_id')
                            ->leftjoin('users as u', 'u.id', '=', 'business.owner_id')
                            ->leftjoin('users as creator', 'creator.id', '=', 'business.created_by')
                            ->select(
                                    'business.id',
                                    'business.name',
                                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as owner_name"),
                                    'u.email as owner_email',
                                    'u.contact_number',
                                    'bl.mobile',
                                    'bl.alternate_number',
                                    'bl.city',
                                    'bl.state',
                                    'bl.country',
                                    'bl.landmark',
                                    'bl.zip_code',
                                    'business.is_active',
                                    's.start_date',
                                    's.end_date',
                                    'p.name as package_name',
                                    'business.created_at',
                                    DB::raw("CONCAT(COALESCE(creator.surname, ''), ' ', COALESCE(creator.first_name, ''), ' ', COALESCE(creator.last_name, '')) as biz_creator")
                                )->groupBy('business.id');

            if (! empty(request()->package_id)) {
                $businesses->where('p.id', request()->package_id);
            }

            $subscription_status = request()->subscription_status;
            if ($subscription_status == 30) {
                $businesses->whereDate('s.end_date', '<=', \Carbon::today()->addDays(30));
            } elseif ($subscription_status == 7) {
                $businesses->whereDate('s.end_date', '<=', \Carbon::today()->addDays(7));
            } elseif ($subscription_status == 3) {
                $businesses->whereDate('s.end_date', '<=', \Carbon::today()->addDays(3));
            } elseif ($subscription_status == 'expired') {
                $businesses->where(function ($q) {
                    $q->whereDate('s.end_date', '<', \Carbon::today())
                    ->orWhereNull('s.end_date');
                });
            } elseif ($subscription_status == 'subscribed') {
                $businesses->whereNotNull('s.start_date');
            }

            $is_active = request()->is_active;
            if ($is_active == 'active') {
                $businesses->where('business.is_active', 1);
            } elseif ($is_active == 'inactive') {
                $businesses->where('business.is_active', 0);
            }

            $last_transaction_date = request()->last_transaction_date;
            $query = $this->filterTransactionDate($businesses, $last_transaction_date, '>');

            $no_transaction_since = request()->no_transaction_since;

            $query = $this->filterTransactionDate($businesses, $no_transaction_since, '=');

            return Datatables::of($query)
                ->addColumn('address', function ($row) {
                    $address_parts = [];
                    if (!empty($row->landmark)) {
                        $address_parts[] = $row->landmark;
                    }
                    if (!empty($row->city)) {
                        $address_parts[] = $row->city;
                    }
                    if (!empty($row->state)) {
                        $address_parts[] = $row->state;
                    }
                    if (!empty($row->country)) {
                        $address_parts[] = $row->country;
                    }
                    if (!empty($row->zip_code)) {
                        $address_parts[] = $row->zip_code;
                    }
                    $address_str = implode(', ', $address_parts);
                    return '<div class="tw-text-xs tw-text-gray-600 tw-min-w-[250px] tw-break-words">' . e($address_str) . '</div>';
                })
                ->addColumn('business_contact_number', '{{$mobile}} @if(!empty($alternate_number)), {{$alternate_number}}@endif')
                ->editColumn('is_active', function ($row) {
                    if ($row->is_active == 1) {
                        return '<span class="tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-300">' . __("business.is_active") . '</span>';
                    } else {
                        return '<span class="tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full tw-bg-gray-100 tw-text-gray-700 tw-border tw-border-gray-300">' . __("lang_v1.inactive") . '</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'
                                    . __('messages.actions') . ' <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    $html .= '<li><a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'show'], [$row->id]) . '"><i class="fa fa-briefcase"></i> ' . __('superadmin::lang.manage') . '</a></li>';

                    $html .= '<li><a href="#" class="btn-modal" data-href="' . action([\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'create'], ['business_id' => $row->id]) . '" data-container=".view_modal"><i class="fa fa-sync"></i> ' . __('superadmin::lang.add_subscription') . '</a></li>';

                    if ($row->is_active == 1) {
                        $html .= '<li><a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'toggleActive'], [$row->id, 0]) . '" class="link_confirmation"><i class="fa fa-power-off text-warning"></i> ' . __('lang_v1.deactivate') . '</a></li>';
                    } else {
                        $html .= '<li><a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'toggleActive'], [$row->id, 1]) . '" class="link_confirmation"><i class="fa fa-power-off text-success"></i> ' . __('lang_v1.activate') . '</a></li>';
                    }

                    $html .= '<li><a href="#" class="btn-modal" data-href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'getResetModal'], [$row->id]) . '" data-container=".view_modal"><i class="fa fa-undo"></i> ' . __('superadmin::lang.reset_business_data') . '</a></li>';

                    $html .= '<li><a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'importDemoData'], [$row->id]) . '" class="import_demo_confirmation"><i class="fa fa-download"></i> ' . __('superadmin::lang.import_demo') . '</a></li>';

                    if (request()->session()->get('user.business_id') != $row->id) {
                        $html .= '<li class="divider"></li>';
                        $html .= '<li><a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'destroy'], [$row->id]) . '" class="delete_business_confirmation" style="color: #ef4444 !important;"><i class="fa fa-trash text-danger" style="color: #ef4444 !important;"></i> ' . __('messages.delete') . '</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->filterColumn('owner_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('address', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(city, ''), ', ', COALESCE(state, ''), ', ', COALESCE(country, ''), ', ', COALESCE(landmark, ''), ', ', COALESCE(zip_code, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('business_contact_number', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('bl.mobile', 'like', "%{$keyword}%")
                        ->orWhere('bl.alternate_number', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('current_subscription', function ($row) {
                    if (empty($row->package_name)) {
                        return '';
                    }
                    $html = '<div class="tw-font-semibold tw-text-gray-800 tw-text-xs">' . e($row->package_name) . '</div>';
                    if (!empty($row->start_date) && !empty($row->end_date)) {
                        $html .= '<div class="tw-text-[10px] tw-text-gray-500 tw-mt-0.5">(' . $this->businessUtil->format_date($row->start_date) . ' - ' . $this->businessUtil->format_date($row->end_date) . ')</div>';
                    }
                    return $html;
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->rawColumns(['action', 'is_active', 'created_at', 'address', 'current_subscription'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $packages = Package::listPackages()->pluck('name', 'id');

        $subscription_statuses = [
            'subscribed' => __('superadmin::lang.subscribed'),
            'expired' => __('report.expired'),
            '30' => __('superadmin::lang.expiring_in_one_month'),
            '7' => __('superadmin::lang.expiring_in_7_days'),
            '3' => __('superadmin::lang.expiring_in_3_days'),
        ];

        $last_transaction_date = [
            'today' => __('home.today'),
            'yesterday' => __('superadmin::lang.yesterday'),
            'this_week' => __('home.this_week'),
            'this_month' => __('home.this_month'),
            'last_month' => __('superadmin::lang.last_month'),
            'this_year' => __('superadmin::lang.this_year'),
            'last_year' => __('superadmin::lang.last_year'),
        ];

        return view('superadmin::business.index')
            ->with(compact('business_id', 'packages', 'subscription_statuses', 'last_transaction_date'));
    }

    private function filterTransactionDate($query, $filter, $operator)
    {
        if ($filter == 'today') {
            $today = \Carbon::today()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) = '$today') $operator 0");
        } elseif ($filter == 'yesterday') {
            $yesterday = \Carbon::yesterday()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$yesterday') $operator 0");
        } elseif ($filter == 'this_week') {
            $this_week = \Carbon::today()->subDays(7)->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$this_week') $operator 0");
        } elseif ($filter == 'this_month') {
            $this_month = \Carbon::today()->firstOfMonth()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$this_month') $operator 0");
        } elseif ($filter == 'last_month') {
            $last_month = \Carbon::today()->subDays(30)->firstOfMonth()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$last_month') $operator 0");
        } elseif ($filter == 'this_year') {
            $this_year = \Carbon::today()->firstOfYear()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$this_year') $operator 0");
        } elseif ($filter == 'last_year') {
            $last_year = \Carbon::today()->subYear()->firstOfYear()->format('Y-m-d');
            $query->whereRaw("(SELECT COUNT(id) FROM transactions as t WHERE t.business_id = business.id AND DATE(t.transaction_date) >= '$last_year') $operator 0");
        }

        return $query;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $currencies = $this->businessUtil->allCurrencies();
        $timezone_list = $this->businessUtil->allTimeZones();

        $accounting_methods = $this->businessUtil->allAccountingMethods();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = __('business.months.'.$i);
        }

        $is_admin = true;

        $packages = Package::active()->orderby('sort_order')->pluck('name', 'id');
        $gateways = $this->_payment_gateways();

        return view('superadmin::business.create')
            ->with(compact(
                'currencies',
                'timezone_list',
                'accounting_methods',
                'months',
                'is_admin',
                'packages',
                'gateways'
            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            //Create owner.
            $owner_details = $request->only(['surname', 'first_name', 'last_name', 'username', 'email', 'password']);
            $owner_details['language'] = env('APP_LOCALE');

            $user = User::create_user($owner_details);

            $business_details = $request->only(['name', 'start_date', 'currency_id', 'tax_label_1', 'tax_number_1', 'tax_label_2', 'tax_number_2', 'time_zone', 'accounting_method', 'fy_start_month']);

            $business_location = $request->only(['name', 'country', 'state', 'city', 'zip_code', 'landmark', 'website', 'mobile', 'alternate_number']);

            //Create the business
            $business_details['owner_id'] = $user->id;
            if (! empty($business_details['start_date'])) {
                $business_details['start_date'] = $this->businessUtil->uf_date($business_details['start_date']);
            }

            //upload logo
            $logo_name = $this->businessUtil->uploadFile($request, 'business_logo', 'business_logos', 'image');
            if (! empty($logo_name)) {
                $business_details['logo'] = $logo_name;
            }

            //default enabled modules
            $business_details['enabled_modules'] = ['purchases', 'add_sale', 'pos_sale', 'stock_transfers', 'stock_adjustment', 'expenses'];

            //created_by
            $business_details['created_by'] = $request->session()->get('user.id');

            $business = $this->businessUtil->createNewBusiness($business_details);

            //Update user with business id
            $user->business_id = $business->id;
            $user->save();

            $this->businessUtil->newBusinessDefaultResources($business->id, $user->id);
            $new_location = $this->businessUtil->addLocation($business->id, $business_location);

            //create new permission with the new location
            Permission::create(['name' => 'location.'.$new_location->id]);

            $subscription_details = $request->only(['package_id', 'paid_via', 'payment_transaction_id']);

            //Add subscription if present
            if (! empty($subscription_details['package_id']) && ! empty($subscription_details['paid_via'])) {
                $package = Package::find($subscription_details['package_id']);

                $subscription = $this->_add_subscription(null, $package->price, $business->id, $subscription_details['package_id'], $subscription_details['paid_via'], $subscription_details['payment_transaction_id'], $request->session()->get('user.id'), true);
            }

            DB::commit();

            //Module function to be called after after business is created
            if (config('app.env') != 'demo') {
                $this->moduleUtil->getModuleData('after_business_created', ['business' => $business]);
            }

            $output = ['success' => 1,
                'msg' => __('business.business_created_succesfully'),
            ];

            return redirect()
                ->action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show($business_id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business = Business::with(['currency', 'locations', 'subscriptions', 'owner'])->find($business_id);

        $created_id = $business->created_by;

        $created_by = ! empty($created_id) ? User::find($created_id) : null;

        return view('superadmin::business.show')
            ->with(compact('business', 'created_by'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        return view('superadmin::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            //Check if logged in busines id is same as deleted business then not allowed.
            $business_id = request()->session()->get('user.business_id');
            if ($business_id == $id) {
                $output = ['success' => 0, 'msg' => __('superadmin.lang.cannot_delete_current_business')];

                return back()->with('status', $output);
            }

            DB::beginTransaction();

            //Delete related products & transactions.
            $products_id = Product::where('business_id', $id)->pluck('id')->toArray();
            if (! empty($products_id)) {
                VariationLocationDetails::whereIn('product_id', $products_id)->delete();
            }
            Transaction::where('business_id', $id)->delete();

            Business::where('id', $id)
                ->delete();

            DB::commit();

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];

            return redirect()
                ->action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Changes the activation status of a business.
     *
     * @return Response
     */
    public function toggleActive(Request $request, $business_id, $is_active)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->businessUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        Business::where('id', $business_id)
            ->update(['is_active' => $is_active]);

        $output = ['success' => 1,
            'msg' => __('lang_v1.success'),
        ];

        return back()->with('status', $output);
    }

    /**
     * Shows user list for a particular business
     *
     * @return Response
     */
    public function usersList($business_id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $user_id = request()->session()->get('user.id');

            $users = User::where('business_id', $business_id)
                        ->where('id', '!=', $user_id)
                        ->where('is_cmmsn_agnt', 0)
                        ->select(['id', 'username',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email', ]);

            return Datatables::of($users)
                ->addColumn(
                    'role',
                    function ($row) {
                        $role_name = $this->moduleUtil->getUserRoleName($row->id);

                        return $role_name;
                    }
                )
                ->addColumn(
                    'action',
                    '@can("user.update")
                        <a href="#" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary update_user_password" data-user_id="{{$id}}" data-user_name="{{$full_name}}"><i class="glyphicon glyphicon-edit"></i> @lang("superadmin::lang.update_password")</a>
                        &nbsp;
                        @if(!empty($username))
                        <a href="{{route("sign-in-as-user", $id)}}?save_current=true" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent"><i class="fas fa-sign-in-alt"></i> @lang("lang_v1.login_as_username", ["username" => $username])</a>
                        @endif
                    @endcan'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Updates user password from superadmin
     *
     * @return Response
     */
    public function updatePassword(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            $user = User::findOrFail($request->input('user_id'));
            $user->password = Hash::make($request->input('password'));
            $user->save();

            //Send password update notification
            if ($this->moduleUtil->IsMailConfigured()) {
                $user->notify(new PasswordUpdateNotification($request->input('password')));
            }

            $output = ['success' => 1,
                'msg' => __('superadmin::lang.password_updated_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the reset business data modal.
     *
     * @param  int  $id
     * @return Response
     */
    public function getResetModal($id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business = Business::findOrFail($id);

        return view('superadmin::business.reset_data_modal')
            ->with(compact('business'));
    }

    /**
     * Resets granular data for a specific business.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function postResetData(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return response()->json($notAllowed);
            }

            $business_id = $id;

            DB::beginTransaction();

            $select_all_global = !empty($request->input('select_all_global'));

            // 1. DATA TRANSAKSI
            $select_all_transactions = $request->input('select_all_transactions');
            $reset_transactions = $request->input('reset_transactions', []);

            $reset_all_tx = $select_all_global || !empty($select_all_transactions);

            // Sub-category: sales
            if ($reset_all_tx || in_array('sales', $reset_transactions)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                    DB::table('repair_job_sheets')->where('business_id', $business_id)->delete();
                }

                $sales_ids = DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->whereIn('type', ['sell', 'sell_return', 'sales_order'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($sales_ids)) {
                    DB::table('transaction_payments')->whereIn('transaction_id', $sales_ids)->delete();

                    $sell_line_ids = DB::table('transaction_sell_lines')->whereIn('transaction_id', $sales_ids)->pluck('id')->toArray();
                    if (!empty($sell_line_ids)) {
                        DB::table('transaction_sell_lines_purchase_lines')->whereIn('sell_line_id', $sell_line_ids)->delete();
                        DB::table('transaction_sell_lines')->whereIn('id', $sell_line_ids)->delete();
                    }

                    DB::table('account_transactions')->whereIn('transaction_id', $sales_ids)->delete();
                    DB::table('transactions')->whereIn('id', $sales_ids)->delete();
                }

                // Delete customer advance payments
                $customer_advance_payment_ids = DB::table('transaction_payments')
                    ->join('contacts', 'transaction_payments.payment_for', '=', 'contacts.id')
                    ->where('transaction_payments.business_id', $business_id)
                    ->where('transaction_payments.is_advance', 1)
                    ->whereIn('contacts.type', ['customer', 'both'])
                    ->select('transaction_payments.id as id')
                    ->pluck('id')
                    ->toArray();

                if (!empty($customer_advance_payment_ids)) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                        DB::table('accounting_accounts_transactions')
                            ->whereIn('transaction_payment_id', $customer_advance_payment_ids)
                            ->delete();
                    }
                    DB::table('account_transactions')
                        ->whereIn('transaction_payment_id', $customer_advance_payment_ids)
                        ->delete();
                    DB::table('transaction_payments')
                        ->whereIn('id', $customer_advance_payment_ids)
                        ->delete();
                }

                // Also delete bookings if sales reset is requested
                DB::table('bookings')->where('business_id', $business_id)->delete();
            }

            // Sub-category: purchases
            if ($reset_all_tx || in_array('purchases', $reset_transactions)) {
                $purchase_ids = DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->whereIn('type', ['purchase', 'purchase_return', 'purchase_order'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($purchase_ids)) {
                    DB::table('transaction_payments')->whereIn('transaction_id', $purchase_ids)->delete();

                    $purchase_line_ids = DB::table('purchase_lines')->whereIn('transaction_id', $purchase_ids)->pluck('id')->toArray();
                    if (!empty($purchase_line_ids)) {
                        DB::table('transaction_sell_lines_purchase_lines')->whereIn('purchase_line_id', $purchase_line_ids)->delete();
                        DB::table('purchase_lines')->whereIn('id', $purchase_line_ids)->delete();
                    }

                    DB::table('account_transactions')->whereIn('transaction_id', $purchase_ids)->delete();
                    DB::table('transactions')->whereIn('id', $purchase_ids)->delete();
                }

                // Delete supplier advance payments
                $supplier_advance_payment_ids = DB::table('transaction_payments')
                    ->join('contacts', 'transaction_payments.payment_for', '=', 'contacts.id')
                    ->where('transaction_payments.business_id', $business_id)
                    ->where('transaction_payments.is_advance', 1)
                    ->whereIn('contacts.type', ['supplier', 'both'])
                    ->select('transaction_payments.id as id')
                    ->pluck('id')
                    ->toArray();

                if (!empty($supplier_advance_payment_ids)) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                        DB::table('accounting_accounts_transactions')
                            ->whereIn('transaction_payment_id', $supplier_advance_payment_ids)
                            ->delete();
                    }
                    DB::table('account_transactions')
                        ->whereIn('transaction_payment_id', $supplier_advance_payment_ids)
                        ->delete();
                    DB::table('transaction_payments')
                        ->whereIn('id', $supplier_advance_payment_ids)
                        ->delete();
                }
            }

            // Sub-category: expenses
            if ($reset_all_tx || in_array('expenses', $reset_transactions)) {
                $expense_ids = DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->where('type', 'expense')
                    ->pluck('id')
                    ->toArray();

                if (!empty($expense_ids)) {
                    DB::table('transaction_payments')->whereIn('transaction_id', $expense_ids)->delete();
                    DB::table('account_transactions')->whereIn('transaction_id', $expense_ids)->delete();
                    DB::table('transactions')->whereIn('id', $expense_ids)->delete();
                }
            }

            // Sub-category: registers (cash register)
            if ($reset_all_tx || in_array('registers', $reset_transactions)) {
                $register_ids = DB::table('cash_registers')
                    ->where('business_id', $business_id)
                    ->pluck('id')
                    ->toArray();

                if (!empty($register_ids)) {
                    DB::table('cash_register_transactions')->whereIn('cash_register_id', $register_ids)->delete();
                    DB::table('cash_registers')->whereIn('id', $register_ids)->delete();
                }
            }

            // Sub-category: stock_adjustments (stock adjustments and transfers)
            if ($reset_all_tx || in_array('stock_adjustments', $reset_transactions)) {
                $stock_ids = DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->whereIn('type', ['stock_adjustment', 'sell_transfer', 'purchase_transfer'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($stock_ids)) {
                    DB::table('transaction_payments')->whereIn('transaction_id', $stock_ids)->delete();
                    DB::table('stock_adjustment_lines')->whereIn('transaction_id', $stock_ids)->delete();
                    DB::table('purchase_lines')->whereIn('transaction_id', $stock_ids)->delete();
                    DB::table('transaction_sell_lines')->whereIn('transaction_id', $stock_ids)->delete();
                    DB::table('account_transactions')->whereIn('transaction_id', $stock_ids)->delete();
                    DB::table('transactions')->whereIn('id', $stock_ids)->delete();
                }
            }

            // Sub-category: finance (financial & accounting transactions and accounts)
            if ($reset_all_tx || in_array('finance', $reset_transactions)) {
                // POS module financial accounts & transactions
                $account_ids = DB::table('accounts')->where('business_id', $business_id)->pluck('id')->toArray();
                if (!empty($account_ids)) {
                    DB::table('account_transactions')->whereIn('account_id', $account_ids)->delete();
                }
                DB::table('accounts')->where('business_id', $business_id)->delete();
                DB::table('account_types')->where('business_id', $business_id)->delete();

                // Accounting module accounts & transactions
                $accounting_account_ids = DB::table('accounting_accounts')->where('business_id', $business_id)->pluck('id')->toArray();
                if (!empty($accounting_account_ids)) {
                    DB::table('accounting_accounts_transactions')->whereIn('accounting_account_id', $accounting_account_ids)->delete();
                    DB::table('accounting_budgets')->whereIn('accounting_account_id', $accounting_account_ids)->delete();
                }
                DB::table('accounting_acc_trans_mappings')->where('business_id', $business_id)->delete();
                DB::table('accounting_accounts')->where('business_id', $business_id)->delete();
                DB::table('accounting_account_types')->where('business_id', $business_id)->delete();

                // Delete all advance payments when resetting finance
                $advance_payment_ids = DB::table('transaction_payments')
                    ->where('business_id', $business_id)
                    ->where('is_advance', 1)
                    ->pluck('id')
                    ->toArray();

                if (!empty($advance_payment_ids)) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                        DB::table('accounting_accounts_transactions')
                            ->whereIn('transaction_payment_id', $advance_payment_ids)
                            ->delete();
                    }
                    DB::table('account_transactions')
                        ->whereIn('transaction_payment_id', $advance_payment_ids)
                        ->delete();
                    DB::table('transaction_payments')
                        ->whereIn('id', $advance_payment_ids)
                        ->delete();
                }

                // Set accounting_default_map in business_locations to null
                DB::table('business_locations')->where('business_id', $business_id)->update(['accounting_default_map' => null]);
            }

            // Sub-category: reset_stock
            if ($reset_all_tx || in_array('reset_stock', $reset_transactions)) {
                $stock_tx_types = [
                    'sell', 'sell_return', 'sales_order',
                    'purchase', 'purchase_return', 'purchase_order',
                    'stock_adjustment', 'sell_transfer', 'purchase_transfer',
                    'opening_stock'
                ];

                $stock_tx_ids = DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->whereIn('type', $stock_tx_types)
                    ->pluck('id')
                    ->toArray();

                if (!empty($stock_tx_ids)) {
                    $payment_ids = DB::table('transaction_payments')
                        ->whereIn('transaction_id', $stock_tx_ids)
                        ->pluck('id')
                        ->toArray();

                    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                        if (!empty($payment_ids)) {
                            DB::table('accounting_accounts_transactions')
                                ->whereIn('transaction_payment_id', $payment_ids)
                                ->delete();
                        }
                        DB::table('accounting_accounts_transactions')
                            ->whereIn('transaction_id', $stock_tx_ids)
                            ->delete();
                    }

                    DB::table('transaction_payments')->whereIn('transaction_id', $stock_tx_ids)->delete();

                    $sell_line_ids = DB::table('transaction_sell_lines')->whereIn('transaction_id', $stock_tx_ids)->pluck('id')->toArray();
                    if (!empty($sell_line_ids)) {
                        DB::table('transaction_sell_lines_purchase_lines')->whereIn('sell_line_id', $sell_line_ids)->delete();
                        DB::table('transaction_sell_lines')->whereIn('id', $sell_line_ids)->delete();
                    }

                    $purchase_line_ids = DB::table('purchase_lines')->whereIn('transaction_id', $stock_tx_ids)->pluck('id')->toArray();
                    if (!empty($purchase_line_ids)) {
                        DB::table('transaction_sell_lines_purchase_lines')->whereIn('purchase_line_id', $purchase_line_ids)->delete();
                        DB::table('purchase_lines')->whereIn('id', $purchase_line_ids)->delete();
                    }

                    DB::table('stock_adjustment_lines')->whereIn('transaction_id', $stock_tx_ids)->delete();
                    DB::table('account_transactions')->whereIn('transaction_id', $stock_tx_ids)->delete();
                    DB::table('transactions')->whereIn('id', $stock_tx_ids)->delete();
                }

                // Delete all advance payments when resetting stock
                $advance_payment_ids = DB::table('transaction_payments')
                    ->where('business_id', $business_id)
                    ->where('is_advance', 1)
                    ->pluck('id')
                    ->toArray();

                if (!empty($advance_payment_ids)) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                        DB::table('accounting_accounts_transactions')
                            ->whereIn('transaction_payment_id', $advance_payment_ids)
                            ->delete();
                    }
                    DB::table('account_transactions')
                        ->whereIn('transaction_payment_id', $advance_payment_ids)
                        ->delete();
                    DB::table('transaction_payments')
                        ->whereIn('id', $advance_payment_ids)
                        ->delete();
                }

                DB::table('bookings')->where('business_id', $business_id)->delete();
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                    DB::table('repair_job_sheets')->where('business_id', $business_id)->delete();
                }

                $product_ids = DB::table('products')->where('business_id', $business_id)->pluck('id')->toArray();
                if (!empty($product_ids)) {
                    DB::table('variation_location_details')->whereIn('product_id', $product_ids)->update(['qty_available' => 0]);
                }
            }


            // 2. DATA MASTER
            $select_all_master = $request->input('select_all_master');
            $reset_master = $request->input('reset_master', []);

            $reset_all_mst = $select_all_global || !empty($select_all_master);

            // Sub-category: products
            if ($reset_all_mst || in_array('products', $reset_master)) {
                $product_ids = DB::table('products')->where('business_id', $business_id)->pluck('id')->toArray();
                if (!empty($product_ids)) {
                    DB::table('variation_location_details')->whereIn('product_id', $product_ids)->delete();
                    DB::table('product_locations')->whereIn('product_id', $product_ids)->delete();
                    DB::table('product_racks')->whereIn('product_id', $product_ids)->delete();
                    DB::table('variations')->whereIn('product_id', $product_ids)->delete();
                    DB::table('product_variations')->whereIn('product_id', $product_ids)->delete();
                    DB::table('products')->whereIn('id', $product_ids)->delete();
                }
            }

            // Sub-category: contacts
            if ($reset_all_mst || in_array('contacts', $reset_master)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                    DB::table('repair_job_sheets')->where('business_id', $business_id)->delete();
                }
                // Do not delete default/Walk-In Customer (is_default = 1)
                DB::table('contacts')
                    ->where('business_id', $business_id)
                    ->where('is_default', '!=', 1)
                    ->delete();

                // Delete all advance payments when resetting contacts
                $advance_payment_ids = DB::table('transaction_payments')
                    ->where('business_id', $business_id)
                    ->where('is_advance', 1)
                    ->pluck('id')
                    ->toArray();

                if (!empty($advance_payment_ids)) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_accounts_transactions')) {
                        DB::table('accounting_accounts_transactions')
                            ->whereIn('transaction_payment_id', $advance_payment_ids)
                            ->delete();
                    }
                    DB::table('account_transactions')
                        ->whereIn('transaction_payment_id', $advance_payment_ids)
                        ->delete();
                    DB::table('transaction_payments')
                        ->whereIn('id', $advance_payment_ids)
                        ->delete();
                }

                // Automatically ensure Walk-In Customer exists / is re-created
                $contactUtil = new \App\Utils\ContactUtil();
                $contactUtil->getWalkInCustomer($business_id);
            }

            // Sub-category: categories
            if ($reset_all_mst || in_array('categories', $reset_master)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_device_models')) {
                    DB::table('repair_device_models')->where('business_id', $business_id)->update(['device_id' => null]);
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                    DB::table('repair_job_sheets')->where('business_id', $business_id)->update(['device_id' => null]);
                }
                DB::table('categories')->where('business_id', $business_id)->delete();
                DB::table('expense_categories')->where('business_id', $business_id)->delete();
            }

            // Sub-category: brands
            if ($reset_all_mst || in_array('brands', $reset_master)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_device_models')) {
                    DB::table('repair_device_models')->where('business_id', $business_id)->update(['brand_id' => null]);
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                    DB::table('repair_job_sheets')->where('business_id', $business_id)->update(['brand_id' => null]);
                }
                DB::table('brands')->where('business_id', $business_id)->delete();
            }

            // Sub-category: taxes (custom taxes)
            if ($reset_all_mst || in_array('taxes', $reset_master)) {
                $tax_rate_ids = DB::table('tax_rates')->where('business_id', $business_id)->pluck('id')->toArray();
                if (!empty($tax_rate_ids)) {
                    DB::table('group_sub_taxes')->whereIn('group_tax_id', $tax_rate_ids)->orWhereIn('tax_id', $tax_rate_ids)->delete();
                    DB::table('tax_rates')->whereIn('id', $tax_rate_ids)->delete();
                }
            }

            // Sub-category: units
            if ($reset_all_mst || in_array('units', $reset_master)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('units')) {
                    DB::table('units')->where('business_id', $business_id)->delete();
                }
            }

            // Sub-category: customer_groups & selling_price_groups
            if ($reset_all_mst || in_array('customer_groups', $reset_master)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('customer_groups')) {
                    DB::table('customer_groups')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('selling_price_groups')) {
                    DB::table('selling_price_groups')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('contacts')) {
                    DB::table('contacts')->where('business_id', $business_id)->update(['customer_group_id' => null]);
                }
            }

            // Sub-category: warranties
            if ($reset_all_mst || in_array('warranties', $reset_master)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('warranties')) {
                    $warranty_ids = DB::table('warranties')->where('business_id', $business_id)->pluck('id')->toArray();
                    if (!empty($warranty_ids) && \Illuminate\Support\Facades\Schema::hasTable('sell_line_warranties')) {
                        DB::table('sell_line_warranties')->whereIn('warranty_id', $warranty_ids)->delete();
                    }
                    DB::table('warranties')->where('business_id', $business_id)->delete();
                }
            }


            // 3. DATA MODUL
            $select_all_modules = $request->input('select_all_modules');
            $reset_modules = $request->input('reset_modules', []);

            $reset_all_mod = $select_all_global || !empty($select_all_modules);

            // Sub-category: asset_management
            if ($reset_all_mod || in_array('asset_management', $reset_modules)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('asset_depreciation_logs')) {
                    DB::table('asset_depreciation_logs')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('assets')) {
                    DB::table('assets')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('asset_categories')) {
                    DB::table('asset_categories')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('asset_settings')) {
                    DB::table('asset_settings')->where('business_id', $business_id)->delete();
                }
            }

            // Sub-category: manufacturing
            if ($reset_all_mod || in_array('manufacturing', $reset_modules)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('mfg_recipes')) {
                    $product_ids = DB::table('products')->where('business_id', $business_id)->pluck('id')->toArray();
                    if (!empty($product_ids)) {
                        $recipe_ids = DB::table('mfg_recipes')->whereIn('product_id', $product_ids)->pluck('id')->toArray();
                        if (!empty($recipe_ids)) {
                            if (\Illuminate\Support\Facades\Schema::hasTable('mfg_recipe_ingredients')) {
                                DB::table('mfg_recipe_ingredients')->whereIn('mfg_recipe_id', $recipe_ids)->delete();
                            }
                            DB::table('mfg_recipes')->whereIn('id', $recipe_ids)->delete();
                        }
                    }
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('mfg_ingredient_groups')) {
                    DB::table('mfg_ingredient_groups')->where('business_id', $business_id)->delete();
                }

                $prod_tx_ids = DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->whereIn('type', ['production_purchase', 'production_sell'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($prod_tx_ids)) {
                    DB::table('purchase_lines')->whereIn('transaction_id', $prod_tx_ids)->delete();
                    DB::table('transaction_sell_lines')->whereIn('transaction_id', $prod_tx_ids)->delete();
                    DB::table('transaction_payments')->whereIn('transaction_id', $prod_tx_ids)->delete();
                    DB::table('transactions')->whereIn('id', $prod_tx_ids)->delete();
                }
            }

            // Sub-category: repair
            if ($reset_all_mod || in_array('repair', $reset_modules)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                    DB::table('repair_job_sheets')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('repair_device_models')) {
                    DB::table('repair_device_models')->where('business_id', $business_id)->delete();
                }
            }

            // Sub-category: essentials
            if ($reset_all_mod || in_array('essentials', $reset_modules)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_attendances')) {
                    DB::table('essentials_attendances')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_leaves')) {
                    DB::table('essentials_leaves')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_payrolls')) {
                    $payroll_ids = DB::table('essentials_payrolls')->where('business_id', $business_id)->pluck('id')->toArray();
                    if (!empty($payroll_ids)) {
                        DB::table('transaction_payments')->whereIn('transaction_id', $payroll_ids)->delete();
                        DB::table('essentials_payrolls')->whereIn('id', $payroll_ids)->delete();
                    }
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_payroll_groups')) {
                    DB::table('essentials_payroll_groups')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_to_dos')) {
                    $todo_ids = DB::table('essentials_to_dos')->where('business_id', $business_id)->pluck('id')->toArray();
                    if (!empty($todo_ids)) {
                        if (\Illuminate\Support\Facades\Schema::hasTable('essentials_todo_comments')) {
                            DB::table('essentials_todo_comments')->whereIn('task_id', $todo_ids)->delete();
                        }
                        if (\Illuminate\Support\Facades\Schema::hasTable('essentials_todos_users')) {
                            DB::table('essentials_todos_users')->whereIn('todo_id', $todo_ids)->delete();
                        }
                        DB::table('essentials_to_dos')->whereIn('id', $todo_ids)->delete();
                    }
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_reminders')) {
                    DB::table('essentials_reminders')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_documents')) {
                    DB::table('essentials_documents')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('essentials_kb')) {
                    DB::table('essentials_kb')->where('business_id', $business_id)->delete();
                }
            }

            // Sub-category: crm
            if ($reset_all_mod || in_array('crm', $reset_modules)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('crm_schedules')) {
                    $sched_ids = DB::table('crm_schedules')->where('business_id', $business_id)->pluck('id')->toArray();
                    if (!empty($sched_ids)) {
                        if (\Illuminate\Support\Facades\Schema::hasTable('crm_schedule_logs')) {
                            DB::table('crm_schedule_logs')->whereIn('schedule_id', $sched_ids)->delete();
                        }
                        if (\Illuminate\Support\Facades\Schema::hasTable('crm_schedule_users')) {
                            DB::table('crm_schedule_users')->whereIn('schedule_id', $sched_ids)->delete();
                        }
                        DB::table('crm_schedules')->whereIn('id', $sched_ids)->delete();
                    }
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('crm_proposals')) {
                    DB::table('crm_proposals')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('crm_campaigns')) {
                    DB::table('crm_campaigns')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('crm_call_logs')) {
                    DB::table('crm_call_logs')->where('business_id', $business_id)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('crm_lead_users')) {
                    $contact_ids = DB::table('contacts')->where('business_id', $business_id)->pluck('id')->toArray();
                    if (!empty($contact_ids)) {
                        DB::table('crm_lead_users')->whereIn('contact_id', $contact_ids)->delete();
                    }
                }
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('superadmin::lang.reset_success')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong') . ' ' . $e->getMessage()
            ];
        }

        return response()->json($output);
    }

    /**
     * Resets business data and imports dummy/demo data for a specific business.
     *
     * @param  int  $id
     * @return Response
     */
    public function importDemoData($id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return response()->json($notAllowed);
            }

            $business_id = $id;

            DB::beginTransaction();

            // 1. Reset existing transactions & master data for this business
            if (\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets')) {
                DB::table('repair_job_sheets')->where('business_id', $business_id)->delete();
            }

            $tx_ids = DB::table('transactions')->where('business_id', $business_id)->pluck('id')->toArray();
            if (!empty($tx_ids)) {
                DB::table('transaction_payments')->whereIn('transaction_id', $tx_ids)->delete();

                $sell_line_ids = DB::table('transaction_sell_lines')->whereIn('transaction_id', $tx_ids)->pluck('id')->toArray();
                if (!empty($sell_line_ids)) {
                    DB::table('transaction_sell_lines_purchase_lines')->whereIn('sell_line_id', $sell_line_ids)->delete();
                    DB::table('transaction_sell_lines')->whereIn('id', $sell_line_ids)->delete();
                }

                $purchase_line_ids = DB::table('purchase_lines')->whereIn('transaction_id', $tx_ids)->pluck('id')->toArray();
                if (!empty($purchase_line_ids)) {
                    DB::table('transaction_sell_lines_purchase_lines')->whereIn('purchase_line_id', $purchase_line_ids)->delete();
                    DB::table('purchase_lines')->whereIn('id', $purchase_line_ids)->delete();
                }

                if (\Illuminate\Support\Facades\Schema::hasTable('stock_adjustment_lines')) {
                    DB::table('stock_adjustment_lines')->whereIn('transaction_id', $tx_ids)->delete();
                }

                DB::table('account_transactions')->whereIn('transaction_id', $tx_ids)->delete();
                DB::table('transactions')->whereIn('id', $tx_ids)->delete();
            }

            DB::table('transaction_payments')->where('business_id', $business_id)->delete();
            DB::table('bookings')->where('business_id', $business_id)->delete();

            // Reset products and product-related tables
            $product_ids = DB::table('products')->where('business_id', $business_id)->pluck('id')->toArray();
            if (!empty($product_ids)) {
                $pv_ids = DB::table('product_variations')->whereIn('product_id', $product_ids)->pluck('id')->toArray();
                $v_ids = DB::table('variations')->whereIn('product_id', $product_ids)->pluck('id')->toArray();

                if (!empty($v_ids)) {
                    DB::table('variation_location_details')->whereIn('variation_id', $v_ids)->delete();
                    if (\Illuminate\Support\Facades\Schema::hasTable('variation_group_prices')) {
                        DB::table('variation_group_prices')->whereIn('variation_id', $v_ids)->delete();
                    }
                    DB::table('variations')->whereIn('id', $v_ids)->delete();
                }

                if (!empty($pv_ids)) {
                    DB::table('product_variations')->whereIn('id', $pv_ids)->delete();
                }

                DB::table('product_locations')->whereIn('product_id', $product_ids)->delete();
                DB::table('products')->whereIn('id', $product_ids)->delete();
            }

            // Reset master data
            DB::table('categories')->where('business_id', $business_id)->delete();
            DB::table('expense_categories')->where('business_id', $business_id)->delete();
            DB::table('brands')->where('business_id', $business_id)->delete();
            DB::table('units')->where('business_id', $business_id)->delete();
            DB::table('warranties')->where('business_id', $business_id)->delete();
            DB::table('discounts')->where('business_id', $business_id)->delete();
            DB::table('customer_groups')->where('business_id', $business_id)->delete();
            DB::table('selling_price_groups')->where('business_id', $business_id)->delete();

            // Reset contacts except non-deletable
            DB::table('contacts')->where('business_id', $business_id)->where('is_default', 0)->delete();

            // Ensure business location exists
            $location = DB::table('business_locations')->where('business_id', $business_id)->first();
            if (!$location) {
                $location_id = DB::table('business_locations')->insertGetId([
                    'business_id' => $business_id,
                    'name' => 'Toko Utama',
                    'city' => 'Jakarta',
                    'country' => 'Indonesia',
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $location_id = $location->id;
            }

            // Find business owner/user
            $user = User::where('business_id', $business_id)->first();
            $user_id = $user ? $user->id : auth()->user()->id;

            // 2. Seed Units
            $units_data = [
                ['actual_name' => 'Pieces', 'short_name' => 'pcs', 'allow_decimal' => 0],
                ['actual_name' => 'Box', 'short_name' => 'box', 'allow_decimal' => 0],
                ['actual_name' => 'Botol', 'short_name' => 'btl', 'allow_decimal' => 0],
                ['actual_name' => 'Pak', 'short_name' => 'pak', 'allow_decimal' => 0],
                ['actual_name' => 'Dus', 'short_name' => 'dus', 'allow_decimal' => 0],
                ['actual_name' => 'Kilogram', 'short_name' => 'kg', 'allow_decimal' => 1],
                ['actual_name' => 'Liter', 'short_name' => 'ltr', 'allow_decimal' => 1],
                ['actual_name' => 'Gram', 'short_name' => 'gr', 'allow_decimal' => 1],
            ];
            $unit_ids = [];
            foreach ($units_data as $ud) {
                $unit_ids[$ud['actual_name']] = DB::table('units')->insertGetId(array_merge($ud, [
                    'business_id' => $business_id,
                    'created_by' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }

            // 3. Seed Brands
            $brands = [
                ['name' => 'Indofood', 'description' => 'Produsen Makanan & Minuman'],
                ['name' => 'Nestle', 'description' => 'Nutrisi & Produk Konsumsi'],
                ['name' => 'Unilever', 'description' => 'Perawatan & Produk Konsumen'],
                ['name' => 'Samsung', 'description' => 'Elektronik & Gadget'],
                ['name' => 'Apple', 'description' => 'Gadget & Komputer Premium'],
                ['name' => 'Sony', 'description' => 'Perangkat Elektronik & Audio'],
                ['name' => 'Wardah', 'description' => 'Kosmetik & Skincare'],
                ['name' => 'Nike', 'description' => 'Pakaian & Sepatu Olahraga'],
                ['name' => 'Uniqlo', 'description' => 'Pakaian Kasual Modern'],
            ];
            $brand_ids = [];
            foreach ($brands as $b) {
                $brand_ids[$b['name']] = DB::table('brands')->insertGetId([
                    'business_id' => $business_id,
                    'name' => $b['name'],
                    'description' => $b['description'],
                    'created_by' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 4. Seed Categories
            $categories = [
                'Makanan & Minuman',
                'Elektronik & Gadget',
                'Pakaian & Fashion',
                'Kecantikan & Perawatan',
                'Alat Tulis & Kantor',
            ];
            $category_ids = [];
            foreach ($categories as $cat_name) {
                $category_ids[$cat_name] = DB::table('categories')->insertGetId([
                    'business_id' => $business_id,
                    'name' => $cat_name,
                    'category_type' => 'product',
                    'parent_id' => 0,
                    'created_by' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 5. Seed Expense Categories
            $exp_categories = ['Listrik & Air', 'Gaji Karyawan', 'Sewa Tempat', 'Internet & Telepon', 'Promosi & Iklan'];
            foreach ($exp_categories as $exp_cat) {
                DB::table('expense_categories')->insert([
                    'business_id' => $business_id,
                    'name' => $exp_cat,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 6. Seed Contacts (Customers & Suppliers)
            $contacts_data = [
                // Customers
                ['type' => 'customer', 'name' => 'Budi Santoso', 'mobile' => '081234567890', 'contact_id' => 'CUST-0001', 'email' => 'budi@gmail.com', 'city' => 'Jakarta Selatan', 'state' => 'DKI Jakarta'],
                ['type' => 'customer', 'name' => 'Siti Aminah', 'mobile' => '081987654321', 'contact_id' => 'CUST-0002', 'email' => 'siti.aminah@yahoo.com', 'city' => 'Bandung', 'state' => 'Jawa Barat'],
                ['type' => 'customer', 'name' => 'Ahmad Hidayat', 'mobile' => '082134567891', 'contact_id' => 'CUST-0003', 'email' => 'ahmad.h@gmail.com', 'city' => 'Surabaya', 'state' => 'Jawa Timur'],
                ['type' => 'customer', 'name' => 'Dewi Lestari', 'mobile' => '081345678912', 'contact_id' => 'CUST-0004', 'email' => 'dewi.lestari@gmail.com', 'city' => 'Semarang', 'state' => 'Jawa Tengah'],
                ['type' => 'customer', 'name' => 'Rian Pratama', 'mobile' => '081567890123', 'contact_id' => 'CUST-0005', 'email' => 'rian.p@outlook.com', 'city' => 'Yogyakarta', 'state' => 'DI Yogyakarta'],

                // Suppliers (Pemasok)
                ['type' => 'supplier', 'name' => 'Hendra Setiawan', 'supplier_business_name' => 'PT Distributor Sembako Utama', 'mobile' => '085123456789', 'contact_id' => 'SUPP-0001', 'email' => 'sales@distributorutama.co.id', 'city' => 'Jakarta Barat', 'state' => 'DKI Jakarta', 'tax_number' => '01.234.567.8-012.000'],
                ['type' => 'supplier', 'name' => 'Bambang Wijaya', 'supplier_business_name' => 'CV Sumber Makmur Abadi', 'mobile' => '087890123456', 'contact_id' => 'SUPP-0002', 'email' => 'info@sumbermakmur.com', 'city' => 'Surabaya', 'state' => 'Jawa Timur', 'tax_number' => '02.345.678.9-023.000'],
                ['type' => 'supplier', 'name' => 'Agus Kurniawan', 'supplier_business_name' => 'PT Indofood Sukses Makmur Tbk', 'mobile' => '081122334455', 'contact_id' => 'SUPP-0003', 'email' => 'order@indofood.co.id', 'city' => 'Jakarta Selatan', 'state' => 'DKI Jakarta', 'tax_number' => '01.000.111.2-011.000'],
                ['type' => 'supplier', 'name' => 'Rina Gunawan', 'supplier_business_name' => 'PT Unilever Indonesia Tbk', 'mobile' => '081233445566', 'contact_id' => 'SUPP-0004', 'email' => 'supply@unilever.co.id', 'city' => 'Tangerang', 'state' => 'Banten', 'tax_number' => '01.111.222.3-012.000'],
                ['type' => 'supplier', 'name' => 'Luki Perkasa', 'supplier_business_name' => 'PT Nestle Indonesia', 'mobile' => '081344556677', 'contact_id' => 'SUPP-0005', 'email' => 'nestle.order@nestle.co.id', 'city' => 'Pasuruan', 'state' => 'Jawa Timur', 'tax_number' => '01.222.333.4-013.000'],
                ['type' => 'supplier', 'name' => 'Eko Prasetyo', 'supplier_business_name' => 'CV Mayora Distribusi Nusantara', 'mobile' => '081455667788', 'contact_id' => 'SUPP-0006', 'email' => 'distribusi@mayora.co.id', 'city' => 'Bandung', 'state' => 'Jawa Barat', 'tax_number' => '02.444.555.6-024.000'],
                ['type' => 'supplier', 'name' => 'Deni Suherman', 'supplier_business_name' => 'PT Wings Surya Indonesia', 'mobile' => '081566778899', 'contact_id' => 'SUPP-0007', 'email' => 'logistik@wingssurya.com', 'city' => 'Gresik', 'state' => 'Jawa Timur', 'tax_number' => '02.555.666.7-025.000'],
                ['type' => 'supplier', 'name' => 'Suryadi', 'supplier_business_name' => 'PT Samsung Electronics Indonesia', 'mobile' => '081677889900', 'contact_id' => 'SUPP-0008', 'email' => 'b2b@samsung.co.id', 'city' => 'Cikarang', 'state' => 'Jawa Barat', 'tax_number' => '01.666.777.8-016.000'],
                ['type' => 'supplier', 'name' => 'Tri Martono', 'supplier_business_name' => 'PT Asus Technology Indonesia', 'mobile' => '081788990011', 'contact_id' => 'SUPP-0009', 'email' => 'dealer@asus.co.id', 'city' => 'Jakarta Pusat', 'state' => 'DKI Jakarta', 'tax_number' => '01.777.888.9-017.000'],
                ['type' => 'supplier', 'name' => 'Maya Putri', 'supplier_business_name' => 'CV Wardah Cosmetics Center', 'mobile' => '081899001122', 'contact_id' => 'SUPP-0010', 'email' => 'grosir@wardahbeauty.com', 'city' => 'Tangerang Selatan', 'state' => 'Banten', 'tax_number' => '02.888.999.0-028.000'],
                ['type' => 'supplier', 'name' => 'Ferry Iskandar', 'supplier_business_name' => 'PT Mitra Adiperkasa Tbk', 'mobile' => '081900112233', 'contact_id' => 'SUPP-0011', 'email' => 'retail.supply@map.co.id', 'city' => 'Jakarta Selatan', 'state' => 'DKI Jakarta', 'tax_number' => '01.999.000.1-019.000'],
                ['type' => 'supplier', 'name' => 'Hendra Wijaya', 'supplier_business_name' => 'CV Anugerah Jaya Logistik', 'mobile' => '082011223344', 'contact_id' => 'SUPP-0012', 'email' => 'anugerahjaya@gmail.com', 'city' => 'Semarang', 'state' => 'Jawa Tengah', 'tax_number' => '02.000.111.2-020.000'],
            ];
            foreach ($contacts_data as $cd) {
                DB::table('contacts')->insert(array_merge($cd, [
                    'business_id' => $business_id,
                    'created_by' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }

            // Ensure Walk-In Customer exists
            $contactUtil = new \App\Utils\ContactUtil();
            $contactUtil->getWalkInCustomer($business_id);

            // 7. Seed Sample Products
            $products_sample = [
                ['name' => 'Beras Premium 5kg', 'cat' => 'Makanan & Minuman', 'brand' => 'Indofood', 'unit' => 'Pak', 'cost' => 55000, 'price' => 68000, 'stock' => 150],
                ['name' => 'Minyak Goreng 2L', 'cat' => 'Makanan & Minuman', 'brand' => 'Indofood', 'unit' => 'Botol', 'cost' => 28000, 'price' => 35000, 'stock' => 200],
                ['name' => 'Susu UHT 1L', 'cat' => 'Makanan & Minuman', 'brand' => 'Nestle', 'unit' => 'Dus', 'cost' => 14000, 'price' => 18000, 'stock' => 120],
                ['name' => 'Smartphone Galaxy A15', 'cat' => 'Elektronik & Gadget', 'brand' => 'Samsung', 'unit' => 'Pieces', 'cost' => 2100000, 'price' => 2499000, 'stock' => 25],
                ['name' => 'Headphone Wireless', 'cat' => 'Elektronik & Gadget', 'brand' => 'Sony', 'unit' => 'Pieces', 'cost' => 450000, 'price' => 599000, 'stock' => 40],
                ['name' => 'Kaos Polos Cotton', 'cat' => 'Pakaian & Fashion', 'brand' => 'Uniqlo', 'unit' => 'Pieces', 'cost' => 60000, 'price' => 89000, 'stock' => 100],
                ['name' => 'Sepatu Running', 'cat' => 'Pakaian & Fashion', 'brand' => 'Nike', 'unit' => 'Pieces', 'cost' => 350000, 'price' => 499000, 'stock' => 30],
                ['name' => 'Sunscreen SPF 50', 'cat' => 'Kecantikan & Perawatan', 'brand' => 'Wardah', 'unit' => 'Pieces', 'cost' => 45000, 'price' => 62000, 'stock' => 80],
                ['name' => 'Shampoo Soft 170ml', 'cat' => 'Kecantikan & Perawatan', 'brand' => 'Unilever', 'unit' => 'Botol', 'cost' => 18000, 'price' => 24000, 'stock' => 90],
            ];

            foreach ($products_sample as $idx => $ps) {
                $sku = 'DEMO-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
                $unit_id = $unit_ids[$ps['unit']] ?? reset($unit_ids);
                $brand_id = $brand_ids[$ps['brand']] ?? null;
                $cat_id = $category_ids[$ps['cat']] ?? null;

                $p_id = DB::table('products')->insertGetId([
                    'business_id' => $business_id,
                    'name' => $ps['name'],
                    'type' => 'single',
                    'unit_id' => $unit_id,
                    'brand_id' => $brand_id,
                    'category_id' => $cat_id,
                    'tax_type' => 'exclusive',
                    'barcode_type' => 'C128',
                    'enable_stock' => 1,
                    'alert_quantity' => 10,
                    'sku' => $sku,
                    'created_by' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('product_locations')->insert([
                    'product_id' => $p_id,
                    'location_id' => $location_id
                ]);

                $pv_id = DB::table('product_variations')->insertGetId([
                    'product_id' => $p_id,
                    'name' => 'DUMMY',
                    'is_dummy' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $v_id = DB::table('variations')->insertGetId([
                    'product_id' => $p_id,
                    'product_variation_id' => $pv_id,
                    'name' => 'DUMMY',
                    'sub_sku' => $sku,
                    'default_purchase_price' => $ps['cost'],
                    'dpp_inc_tax' => $ps['cost'],
                    'profit_percent' => 25,
                    'default_sell_price' => $ps['price'],
                    'sell_price_inc_tax' => $ps['price'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('variation_location_details')->insert([
                    'product_id' => $p_id,
                    'product_variation_id' => $pv_id,
                    'variation_id' => $v_id,
                    'location_id' => $location_id,
                    'qty_available' => $ps['stock']
                ]);
            }

            // 8. Seed Roles & Users (Kasir, Akuntan, Sales, Staf Gudang, Manajer Toko, Agen Komisi)
            $roles_data = [
                [
                    'name' => 'Kasir',
                    'permissions' => ['sell.view', 'sell.create', 'sell.update', 'access_all_locations', 'view_cash_register', 'close_cash_register', 'print_invoice']
                ],
                [
                    'name' => 'Akuntan',
                    'permissions' => ['purchase_n_sell_report.view', 'contacts_report.view', 'tax_report.view', 'register_report.view', 'expense_report.view', 'expense.access', 'account.access', 'dashboard.data']
                ],
                [
                    'name' => 'Sales Representative',
                    'permissions' => ['customer.view', 'customer.create', 'customer.update', 'product.view', 'sell.view', 'sell.create', 'sell.update', 'sales_representative.view', 'dashboard.data', 'print_invoice', 'view_cash_register']
                ],
                [
                    'name' => 'Staf Gudang',
                    'permissions' => ['supplier.view', 'supplier.create', 'product.view', 'product.create', 'product.update', 'purchase.view', 'purchase.create', 'purchase.update', 'stock_report.view', 'unit.view', 'category.view', 'brand.view', 'dashboard.data']
                ],
                [
                    'name' => 'Manajer Toko',
                    'permissions' => [
                        'user.view', 'supplier.view', 'customer.view', 'product.view', 'product.create', 'product.update',
                        'purchase.view', 'purchase.create', 'sell.view', 'sell.create', 'sell.update',
                        'purchase_n_sell_report.view', 'contacts_report.view', 'stock_report.view', 'expense.access', 'access_all_locations', 'dashboard.data', 'print_invoice', 'view_cash_register', 'close_cash_register'
                    ]
                ]
            ];

            $created_roles = [];
            foreach ($roles_data as $rd) {
                $role_name = $rd['name'] . '#' . $business_id;
                $role = Role::firstOrCreate([
                    'name' => $role_name,
                    'business_id' => $business_id,
                    'guard_name' => 'web'
                ]);
                $role->syncPermissions($rd['permissions']);
                $created_roles[$rd['name']] = $role;
            }

            // Clean up previous demo users (except the business owner)
            $owner_id = $user_id;
            User::withTrashed()->where('business_id', $business_id)->where('id', '!=', $owner_id)->forceDelete();

            $password = Hash::make('123456');

            $demo_users = [
                ['username' => 'kasir_' . $business_id, 'first_name' => 'Kasir', 'last_name' => 'Toko', 'email' => 'kasir' . $business_id . '@demo.com', 'role' => 'Kasir'],
                ['username' => 'akuntan_' . $business_id, 'first_name' => 'Akuntan', 'last_name' => 'Keuangan', 'email' => 'akuntan' . $business_id . '@demo.com', 'role' => 'Akuntan'],
                ['username' => 'sales_' . $business_id, 'first_name' => 'Sales', 'last_name' => 'Lapangan', 'email' => 'sales' . $business_id . '@demo.com', 'role' => 'Sales Representative'],
                ['username' => 'gudang_' . $business_id, 'first_name' => 'Staf', 'last_name' => 'Gudang', 'email' => 'gudang' . $business_id . '@demo.com', 'role' => 'Staf Gudang'],
                ['username' => 'manajer_' . $business_id, 'first_name' => 'Manajer', 'last_name' => 'Toko', 'email' => 'manajer' . $business_id . '@demo.com', 'role' => 'Manajer Toko'],
            ];

            foreach ($demo_users as $du) {
                $new_u = User::create([
                    'surname' => '',
                    'first_name' => $du['first_name'],
                    'last_name' => $du['last_name'],
                    'username' => $du['username'],
                    'email' => $du['email'],
                    'password' => $password,
                    'business_id' => $business_id,
                    'user_type' => 'user',
                    'status' => 'active',
                    'is_cmmsn_agnt' => 0,
                    'cmmsn_percent' => 0,
                ]);

                if (isset($created_roles[$du['role']])) {
                    $new_u->assignRole($created_roles[$du['role']]->name);
                }
            }

            // Seed Sales Commission Agents (Agen Komisi Penjualan)
            $commission_agents = [
                ['first_name' => 'Agen', 'last_name' => 'Rian', 'email' => 'rian' . $business_id . '@agent.com', 'contact_no' => '081299887766', 'percent' => 5],
                ['first_name' => 'Agen', 'last_name' => 'Dewi', 'email' => 'dewi' . $business_id . '@agent.com', 'contact_no' => '081399887755', 'percent' => 7.5],
            ];

            foreach ($commission_agents as $ca) {
                User::create([
                    'surname' => '',
                    'first_name' => $ca['first_name'],
                    'last_name' => $ca['last_name'],
                    'email' => $ca['email'],
                    'contact_no' => $ca['contact_no'],
                    'business_id' => $business_id,
                    'user_type' => 'user',
                    'status' => 'active',
                    'is_cmmsn_agnt' => 1,
                    'cmmsn_percent' => $ca['percent'],
                ]);
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('superadmin::lang.import_demo_success')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong') . ' ' . $e->getMessage()
            ];
        }

        return response()->json($output);
    }
}
