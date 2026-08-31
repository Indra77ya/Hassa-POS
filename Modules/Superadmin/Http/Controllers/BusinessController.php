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

                    $html .= '<li><a href="#" class="btn-modal" data-href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'getDemoModal'], [$row->id]) . '" data-container=".view_modal"><i class="fa fa-magic"></i> ' . __('superadmin::lang.generate_demo_data') . '</a></li>';

                    $html .= '<li><a href="#" class="btn-modal" data-href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'getResetModal'], [$row->id]) . '" data-container=".view_modal"><i class="fa fa-undo"></i> ' . __('superadmin::lang.reset_business_data') . '</a></li>';

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
     * Display the generate demo data modal.
     *
     * @param  int  $id
     * @return Response
     */
    public function getDemoModal($id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $business = Business::findOrFail($id);

        return view('superadmin::business.demo_modal')
            ->with(compact('business'));
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
     * Generates customizable demo data for a specific business.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function postGenerateDemo(Request $request, $id)
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
            $user_id = auth()->user()->id;

            // 1. Reset old data if requested
            if (!empty($request->input('reset_old_data'))) {
                $resetRequest = new Request(['select_all_global' => 1]);
                $this->postResetData($resetRequest, $business_id);
            }

            DB::beginTransaction();

            $business = Business::findOrFail($business_id);
            $location = DB::table('business_locations')->where('business_id', $business_id)->first();
            if (!$location) {
                $location_obj = $this->businessUtil->addLocation($business_id, [
                    'name' => 'Cabang Utama',
                    'country' => 'Indonesia',
                    'state' => 'Jawa Tengah',
                    'city' => 'Semarang',
                    'zip_code' => '50142',
                    'landmark' => 'Jl. Anjasmoro',
                ]);
                $location_id = is_object($location_obj) ? $location_obj->id : (is_array($location_obj) ? $location_obj['id'] : $location_obj);
            } else {
                $location_id = $location->id;
            }

            // Quantities from request
            $num_units = max(0, (int)$request->input('num_units', 5));
            $num_categories = max(0, (int)$request->input('num_categories', 5));
            $num_brands = max(0, (int)$request->input('num_brands', 5));
            $num_warranties = max(0, (int)$request->input('num_warranties', 3));
            $num_variations = max(0, (int)$request->input('num_variations', 3));
            $num_suppliers = max(0, (int)$request->input('num_suppliers', 10));
            $num_customers = max(0, (int)$request->input('num_customers', 10));
            $num_products = max(0, (int)$request->input('num_products', 20));
            $num_users = max(0, (int)$request->input('num_users', 5));
            $num_transactions = max(0, (int)$request->input('num_transactions', 15));

            // 2. Seed Units
            $unit_pool = [
                ['actual_name' => 'Pieces', 'short_name' => 'Pcs', 'allow_decimal' => 0],
                ['actual_name' => 'Box', 'short_name' => 'Box', 'allow_decimal' => 0],
                ['actual_name' => 'Paket', 'short_name' => 'Pak', 'allow_decimal' => 0],
                ['actual_name' => 'Botol', 'short_name' => 'Btl', 'allow_decimal' => 0],
                ['actual_name' => 'Kilogram', 'short_name' => 'Kg', 'allow_decimal' => 1],
                ['actual_name' => 'Karton', 'short_name' => 'Ktn', 'allow_decimal' => 0],
                ['actual_name' => 'Liter', 'short_name' => 'Ltr', 'allow_decimal' => 1],
                ['actual_name' => 'Meter', 'short_name' => 'Mtr', 'allow_decimal' => 1],
                ['actual_name' => 'Set', 'short_name' => 'Set', 'allow_decimal' => 0],
                ['actual_name' => 'Pasang', 'short_name' => 'Psg', 'allow_decimal' => 0],
            ];

            $created_unit_ids = [];
            for ($i = 0; $i < $num_units; $i++) {
                if (isset($unit_pool[$i])) {
                    $u_data = $unit_pool[$i];
                } else {
                    $u_data = ['actual_name' => 'Satuan ' . ($i + 1), 'short_name' => 'Sat' . ($i + 1), 'allow_decimal' => 0];
                }

                $existing_unit = DB::table('units')->where('business_id', $business_id)->where('actual_name', $u_data['actual_name'])->first();
                if ($existing_unit) {
                    $created_unit_ids[] = $existing_unit->id;
                } else {
                    $u_id = DB::table('units')->insertGetId([
                        'business_id' => $business_id,
                        'actual_name' => $u_data['actual_name'],
                        'short_name' => $u_data['short_name'],
                        'allow_decimal' => $u_data['allow_decimal'],
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created_unit_ids[] = $u_id;
                }
            }

            // 3. Seed Categories
            $category_pool = [
                'Makanan & Minuman', 'Kecantikan & Perawatan', 'Alat Tulis & Kantor', 'Elektronik & Gadget',
                'Pakaian & Aksesoris', 'Perlengkapan Rumah', 'Kesehatan & Farmasi', 'Otomotif & Aksesoris',
                'Bahan Bangunan', 'Olahraga & Hobi'
            ];

            $created_category_ids = [];
            for ($i = 0; $i < $num_categories; $i++) {
                $cat_name = $category_pool[$i] ?? ('Kategori Demo ' . ($i + 1));
                $existing_cat = DB::table('categories')->where('business_id', $business_id)->where('name', $cat_name)->first();
                if ($existing_cat) {
                    $created_category_ids[] = $existing_cat->id;
                } else {
                    $c_id = DB::table('categories')->insertGetId([
                        'business_id' => $business_id,
                        'name' => $cat_name,
                        'short_code' => 'CAT-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                        'category_type' => 'product',
                        'parent_id' => 0,
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created_category_ids[] = $c_id;
                }
            }

            // 4. Seed Brands
            $brand_pool = [
                'Indofood', 'Unilever', 'Wardah', 'Samsung', 'LG',
                'Philips', 'Maspion', 'Polygon', 'Toyota', 'Honda'
            ];

            $created_brand_ids = [];
            for ($i = 0; $i < $num_brands; $i++) {
                $b_name = $brand_pool[$i] ?? ('Merek Demo ' . ($i + 1));
                $existing_brand = DB::table('brands')->where('business_id', $business_id)->where('name', $b_name)->first();
                if ($existing_brand) {
                    $created_brand_ids[] = $existing_brand->id;
                } else {
                    $b_id = DB::table('brands')->insertGetId([
                        'business_id' => $business_id,
                        'name' => $b_name,
                        'description' => 'Merek resmi ' . $b_name,
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created_brand_ids[] = $b_id;
                }
            }

            // 5. Seed Warranties
            $warranty_pool = [
                ['name' => 'Garansi Toko 1 Bulan', 'duration' => 1, 'duration_type' => 'months'],
                ['name' => 'Garansi Resmi 6 Bulan', 'duration' => 6, 'duration_type' => 'months'],
                ['name' => 'Garansi Resmi 1 Tahun', 'duration' => 1, 'duration_type' => 'years'],
                ['name' => 'Garansi Distro 2 Tahun', 'duration' => 2, 'duration_type' => 'years'],
            ];

            $created_warranty_ids = [];
            for ($i = 0; $i < $num_warranties; $i++) {
                if (isset($warranty_pool[$i])) {
                    $w_data = $warranty_pool[$i];
                } else {
                    $w_data = ['name' => 'Garansi Demo ' . ($i + 1) . ' Bulan', 'duration' => ($i + 1), 'duration_type' => 'months'];
                }

                $existing_w = DB::table('warranties')->where('business_id', $business_id)->where('name', $w_data['name'])->first();
                if ($existing_w) {
                    $created_warranty_ids[] = $existing_w->id;
                } else {
                    $w_id = DB::table('warranties')->insertGetId([
                        'business_id' => $business_id,
                        'name' => $w_data['name'],
                        'description' => $w_data['name'],
                        'duration' => $w_data['duration'],
                        'duration_type' => $w_data['duration_type'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created_warranty_ids[] = $w_id;
                }
            }

            // 6. Seed Variation Templates
            $variation_template_pool = [
                ['name' => 'Ukuran', 'values' => ['S', 'M', 'L', 'XL', 'XXL']],
                ['name' => 'Warna', 'values' => ['Merah', 'Biru', 'Hitam', 'Putih', 'Hijau']],
                ['name' => 'Kemasan', 'values' => ['Small (250ml)', 'Medium (500ml)', 'Large (1L)']],
            ];

            for ($i = 0; $i < $num_variations; $i++) {
                if (isset($variation_template_pool[$i])) {
                    $v_tmpl = $variation_template_pool[$i];
                } else {
                    $v_tmpl = ['name' => 'Varian Demo ' . ($i + 1), 'values' => ['Opsi A', 'Opsi B', 'Opsi C']];
                }

                $existing_vt = DB::table('variation_templates')->where('business_id', $business_id)->where('name', $v_tmpl['name'])->first();
                if (!$existing_vt) {
                    $vt_id = DB::table('variation_templates')->insertGetId([
                        'business_id' => $business_id,
                        'name' => $v_tmpl['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    foreach ($v_tmpl['values'] as $val) {
                        DB::table('variation_value_templates')->insert([
                            'name' => $val,
                            'variation_template_id' => $vt_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 7. Seed Suppliers
            $supplier_pool = [
                'PT Indofood Sukses Makmur', 'PT Unilever Indonesia Tbk', 'PT Paragon Technology (Wardah)',
                'PT Samsung Electronics', 'PT Wings Surya', 'PT Mayora Indah Tbk',
                'PT Santos Jaya Abadi', 'PT Garudafood Putra Putri Jaya', 'PT Nutrifood Indonesia', 'PT Lion Wings'
            ];

            $created_supplier_ids = [];
            for ($i = 0; $i < $num_suppliers; $i++) {
                $s_name = $supplier_pool[$i] ?? ('Pemasok Demo ' . ($i + 1));
                $s_id = DB::table('contacts')->insertGetId([
                    'business_id' => $business_id,
                    'type' => 'supplier',
                    'supplier_business_name' => $s_name,
                    'name' => $s_name,
                    'contact_id' => 'SUP-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'mobile' => '0812' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                    'email' => 'supplier' . ($i + 1) . '_' . $business_id . '@demo.com',
                    'created_by' => $user_id,
                    'is_default' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created_supplier_ids[] = $s_id;
            }

            // 8. Seed Customers
            $contactUtil = new \App\Utils\ContactUtil();
            $walkin_customer = $contactUtil->getWalkInCustomer($business_id);
            $walkin_customer_id = is_object($walkin_customer) ? $walkin_customer->id : (is_array($walkin_customer) ? $walkin_customer['id'] : $walkin_customer);
            $created_customer_ids = [$walkin_customer_id];

            $customer_pool = [
                'Budi Santoso', 'Siti Aminah', 'Rudi Hermawan', 'Dewi Lestari', 'Agus Setiawan',
                'Rina Wijaya', 'Eko Prasetyo', 'Nur Hidayah', 'Dedi Kurniawan', 'Lia Rahmawati'
            ];

            for ($i = 0; $i < $num_customers; $i++) {
                $c_name = $customer_pool[$i] ?? ('Pelanggan Demo ' . ($i + 1));
                $c_id = DB::table('contacts')->insertGetId([
                    'business_id' => $business_id,
                    'type' => 'customer',
                    'name' => $c_name,
                    'contact_id' => 'CUST-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'mobile' => '0857' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                    'email' => 'customer' . ($i + 1) . '_' . $business_id . '@demo.com',
                    'created_by' => $user_id,
                    'is_default' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created_customer_ids[] = $c_id;
            }

            // 9. Seed Products & Variations
            $product_sample_pool = [
                ['name' => 'Indomie Goreng Original 85g', 'cost' => 2800, 'price' => 3500, 'stock' => 200],
                ['name' => 'Minyak Goreng Filma 2 Litre', 'cost' => 32000, 'price' => 38000, 'stock' => 150],
                ['name' => 'Kopi Kapal Api Special 165g', 'cost' => 12500, 'price' => 15500, 'stock' => 180],
                ['name' => 'Susu UHT Full Cream 1L', 'cost' => 16000, 'price' => 20000, 'stock' => 120],
                ['name' => 'Gula Pasir Kristal Premium 1kg', 'cost' => 13500, 'price' => 16500, 'stock' => 250],
                ['name' => 'Teh Celup Sosri Box 25', 'cost' => 6500, 'price' => 8500, 'stock' => 100],
                ['name' => 'Air Mineral Aqua 600ml (Karton)', 'cost' => 45000, 'price' => 55000, 'stock' => 80],
                ['name' => 'Biskuit Khong Guan Red Can 1600g', 'cost' => 85000, 'price' => 105000, 'stock' => 50],
                ['name' => 'Sabun Cuci Piring Liquid 780ml', 'cost' => 14000, 'price' => 18000, 'stock' => 140],
                ['name' => 'Deterjen Bubuk Attack 800g', 'cost' => 19500, 'price' => 24500, 'stock' => 110],
                ['name' => 'Pembersih Lantai Pine 800ml Refill', 'cost' => 11000, 'price' => 14500, 'stock' => 95],
                ['name' => 'Pasta Gigi Whitening 190g', 'cost' => 12500, 'price' => 16500, 'stock' => 160],
                ['name' => 'Bedak Tabur Two Way Cake 14g', 'cost' => 45000, 'price' => 62000, 'stock' => 80],
                ['name' => 'Shampoo Soft & Smooth 170ml', 'cost' => 18000, 'price' => 24000, 'stock' => 90],
                ['name' => 'Sabun Mandi Cair 400ml Refill', 'cost' => 22000, 'price' => 29000, 'stock' => 130],
                ['name' => 'Serum Glowing Brightening 30ml', 'cost' => 85000, 'price' => 119000, 'stock' => 70],
                ['name' => 'Micellar Water Cleanser 240ml', 'cost' => 32000, 'price' => 45000, 'stock' => 95],
                ['name' => 'Buku Tulis A5 Isi 58 (Box 10)', 'cost' => 35000, 'price' => 48000, 'stock' => 75],
                ['name' => 'Pulpen Gel Hitam 0.5mm (Pak 12)', 'cost' => 24000, 'price' => 36000, 'stock' => 110],
                ['name' => 'Kertas HVS A4 75gsm (Rim)', 'cost' => 42000, 'price' => 52000, 'stock' => 140],
            ];

            $created_products = [];
            for ($i = 0; $i < $num_products; $i++) {
                if (isset($product_sample_pool[$i])) {
                    $ps = $product_sample_pool[$i];
                } else {
                    $ps = [
                        'name' => 'Produk Demo #' . ($i + 1),
                        'cost' => rand(10, 100) * 1000,
                        'price' => rand(12, 150) * 1000,
                        'stock' => rand(50, 200)
                    ];
                }

                $sku = 'DEMO-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                $unit_id = !empty($created_unit_ids) ? $created_unit_ids[$i % count($created_unit_ids)] : null;
                $cat_id = !empty($created_category_ids) ? $created_category_ids[$i % count($created_category_ids)] : null;
                $brand_id = !empty($created_brand_ids) ? $created_brand_ids[$i % count($created_brand_ids)] : null;

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

                $profit_percent = $ps['cost'] > 0 ? round((($ps['price'] - $ps['cost']) / $ps['cost']) * 100, 2) : 25;

                $v_id = DB::table('variations')->insertGetId([
                    'product_id' => $p_id,
                    'product_variation_id' => $pv_id,
                    'name' => 'DUMMY',
                    'sub_sku' => $sku,
                    'default_purchase_price' => $ps['cost'],
                    'dpp_inc_tax' => $ps['cost'],
                    'profit_percent' => $profit_percent,
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

                $created_products[] = [
                    'product_id' => $p_id,
                    'variation_id' => $v_id,
                    'cost' => $ps['cost'],
                    'price' => $ps['price'],
                    'stock' => $ps['stock']
                ];
            }

            // 10. Seed Roles & Users
            if ($num_users > 0) {
                $roles_data = [
                    ['name' => 'Kasir', 'permissions' => ['sell.view', 'sell.create', 'sell.update', 'access_all_locations', 'view_cash_register', 'close_cash_register', 'print_invoice']],
                    ['name' => 'Akuntan', 'permissions' => ['purchase_n_sell_report.view', 'contacts_report.view', 'tax_report.view', 'register_report.view', 'expense_report.view', 'expense.access', 'account.access', 'dashboard.data']],
                    ['name' => 'Sales Representative', 'permissions' => ['customer.view', 'customer.create', 'customer.update', 'product.view', 'sell.view', 'sell.create', 'sell.update', 'sales_representative.view', 'dashboard.data', 'print_invoice', 'view_cash_register']],
                    ['name' => 'Staf Gudang', 'permissions' => ['supplier.view', 'supplier.create', 'product.view', 'product.create', 'product.update', 'purchase.view', 'purchase.create', 'purchase.update', 'stock_report.view', 'unit.view', 'category.view', 'brand.view', 'dashboard.data']],
                    ['name' => 'Manajer Toko', 'permissions' => ['user.view', 'supplier.view', 'customer.view', 'product.view', 'product.create', 'product.update', 'purchase.view', 'purchase.create', 'sell.view', 'sell.create', 'sell.update', 'purchase_n_sell_report.view', 'contacts_report.view', 'stock_report.view', 'expense.access', 'access_all_locations', 'dashboard.data', 'print_invoice', 'view_cash_register', 'close_cash_register']],
                ];

                $created_roles = [];
                foreach ($roles_data as $rd) {
                    $role_name = $rd['name'] . '#' . $business_id;
                    $role = \Spatie\Permission\Models\Role::firstOrCreate([
                        'name' => $role_name,
                        'business_id' => $business_id,
                        'guard_name' => 'web'
                    ]);

                    foreach ($rd['permissions'] as $p_name) {
                        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p_name, 'guard_name' => 'web']);
                    }

                    $role->syncPermissions($rd['permissions']);
                    $created_roles[] = $role;
                }

                $password = \Illuminate\Support\Facades\Hash::make('123456');

                for ($i = 0; $i < $num_users; $i++) {
                    $assigned_role = $created_roles[$i % count($created_roles)];
                    $role_clean = explode('#', $assigned_role->name)[0];
                    $username = strtolower(str_replace(' ', '_', $role_clean)) . '_' . ($i + 1) . '_' . $business_id;

                    $new_u = User::create([
                        'surname' => '',
                        'first_name' => $role_clean,
                        'last_name' => 'User ' . ($i + 1),
                        'username' => $username,
                        'email' => $username . '@demo.com',
                        'password' => $password,
                        'business_id' => $business_id,
                        'user_type' => 'user',
                        'status' => 'active',
                        'is_cmmsn_agnt' => 0,
                        'cmmsn_percent' => 0,
                    ]);

                    $new_u->assignRole($assigned_role->name);
                }

                // Seed 1 Sales Commission Agent
                User::create([
                    'surname' => '',
                    'first_name' => 'Agen Sales',
                    'last_name' => '(Komisi)',
                    'username' => 'agent_' . $business_id,
                    'email' => 'agent_' . $business_id . '@demo.com',
                    'password' => $password,
                    'contact_no' => '081299887766',
                    'business_id' => $business_id,
                    'user_type' => 'user',
                    'status' => 'active',
                    'is_cmmsn_agnt' => 1,
                    'cmmsn_percent' => 5,
                ]);
            }

            // 11. Seed Transactions (Purchases & Sales)
            if ($num_transactions > 0 && !empty($created_products)) {
                $num_purchases = (int)ceil($num_transactions * 0.3);
                $num_sales = $num_transactions - $num_purchases;

                // Create Purchases
                for ($i = 0; $i < $num_purchases; $i++) {
                    $supplier_id = !empty($created_supplier_ids) ? $created_supplier_ids[$i % count($created_supplier_ids)] : null;
                    $p_item = $created_products[$i % count($created_products)];
                    $qty = rand(10, 50);
                    $line_total = $p_item['cost'] * $qty;

                    $purchase_id = DB::table('transactions')->insertGetId([
                        'business_id' => $business_id,
                        'location_id' => $location_id,
                        'type' => 'purchase',
                        'status' => 'received',
                        'payment_status' => 'paid',
                        'contact_id' => $supplier_id,
                        'ref_no' => 'PO-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                        'transaction_date' => \Carbon::now()->subDays(rand(1, 30))->format('Y-m-d H:i:s'),
                        'total_before_tax' => $line_total,
                        'final_total' => $line_total,
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $pl_id = DB::table('purchase_lines')->insertGetId([
                        'transaction_id' => $purchase_id,
                        'product_id' => $p_item['product_id'],
                        'variation_id' => $p_item['variation_id'],
                        'quantity' => $qty,
                        'purchase_price' => $p_item['cost'],
                        'purchase_price_inc_tax' => $p_item['cost'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('transaction_payments')->insert([
                        'business_id' => $business_id,
                        'transaction_id' => $purchase_id,
                        'amount' => $line_total,
                        'method' => 'cash',
                        'paid_on' => now(),
                        'created_by' => $user_id,
                        'payment_for' => $supplier_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Create Sales
                for ($i = 0; $i < $num_sales; $i++) {
                    $customer_id = !empty($created_customer_ids) ? $created_customer_ids[$i % count($created_customer_ids)] : $walkin_customer_id;
                    $p_item = $created_products[$i % count($created_products)];
                    $qty = rand(1, 5);
                    $line_total = $p_item['price'] * $qty;

                    $sale_id = DB::table('transactions')->insertGetId([
                        'business_id' => $business_id,
                        'location_id' => $location_id,
                        'type' => 'sell',
                        'status' => 'final',
                        'payment_status' => 'paid',
                        'contact_id' => $customer_id,
                        'invoice_no' => 'SELL-DEMO-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                        'transaction_date' => \Carbon::now()->subDays(rand(0, 15))->format('Y-m-d H:i:s'),
                        'total_before_tax' => $line_total,
                        'final_total' => $line_total,
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $sl_id = DB::table('transaction_sell_lines')->insertGetId([
                        'transaction_id' => $sale_id,
                        'product_id' => $p_item['product_id'],
                        'variation_id' => $p_item['variation_id'],
                        'quantity' => $qty,
                        'unit_price' => $p_item['price'],
                        'unit_price_inc_tax' => $p_item['price'],
                        'unit_price_before_discount' => $p_item['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('transaction_payments')->insert([
                        'business_id' => $business_id,
                        'transaction_id' => $sale_id,
                        'amount' => $line_total,
                        'method' => 'cash',
                        'paid_on' => now(),
                        'created_by' => $user_id,
                        'payment_for' => $customer_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('superadmin::lang.generate_demo_success')
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
