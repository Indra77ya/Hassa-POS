<?php

namespace Modules\Essentials\Http\Controllers;

use App\BusinessLocation;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsHoliday;
use Yajra\DataTables\Facades\DataTables;

class EssentialsHolidayController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
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

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $essentialsUtil = new \Modules\Essentials\Utils\EssentialsUtil;

        if (request()->ajax()) {
            
            $permitted_locations = auth()->user()->permitted_locations();

            $holidays = $essentialsUtil->Gettotalholiday($business_id, request()->input('location_id'), request()->start_date, request()->end_date, $permitted_locations);

            return Datatables::of($holidays)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            $html .= '<button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container="#add_holiday_modal" data-href="'.action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'edit'], [$row->id]).'"><i class="fa fa-edit"></i> '.__('messages.edit').'</button>
                            &nbsp;
                            <button class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete-holiday" data-href="'.action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'destroy'], [$row->id]).'"><i class="fa fa-trash"></i> '.__('messages.delete').'</button>
                            ';
                        }

                        return $html;
                    }
                )
                ->editColumn('location', '{{$location ?? __("lang_v1.all")}}')
                ->editColumn('start_date', function ($row) {
                    $start_date = \Carbon::parse($row->start_date);
                    $end_date = \Carbon::parse($row->end_date);

                    $diff = $start_date->diffInDays($end_date);
                    $diff += 1;
                    $start_date_formated = $this->moduleUtil->format_date($start_date);
                    $end_date_formated = $this->moduleUtil->format_date($end_date);

                    return $start_date_formated.' - '.$end_date_formated.' ('.$diff.\Str::plural(__('lang_v1.day'), $diff).')';
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        $locations = BusinessLocation::forDropdown($business_id);

        return view('essentials::holiday.index')->with(compact('locations', 'is_admin'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $locations = BusinessLocation::forDropdown($business_id);

        return view('essentials::holiday.create')->with(compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'start_date', 'end_date', 'location_id', 'note']);

            $input['start_date'] = $this->moduleUtil->uf_date($input['start_date']);
            $input['end_date'] = $this->moduleUtil->uf_date($input['end_date']);
            $input['business_id'] = $business_id;

            EssentialsHoliday::create($input);
            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $holiday = EssentialsHoliday::where('business_id', $business_id)
                                    ->findOrFail($id);

        $locations = BusinessLocation::forDropdown($business_id);

        return view('essentials::holiday.edit')->with(compact('locations', 'holiday'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'start_date', 'end_date', 'location_id', 'note']);

            $input['start_date'] = $this->moduleUtil->uf_date($input['start_date']);
            $input['end_date'] = $this->moduleUtil->uf_date($input['end_date']);

            EssentialsHoliday::where('business_id', $business_id)
                        ->where('id', $id)
                        ->update($input);

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) && ! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        try {
            EssentialsHoliday::where('business_id', $business_id)
                        ->where('id', $id)
                        ->delete();

            $output = ['success' => true,
                'msg' => __('lang_v1.deleted_success'),
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
