<?php

namespace Modules\Superadmin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Modules\Superadmin\Entities\Package;
use App\Business;
use Modules\Superadmin\Entities\SuperadminCoupon;
use App\Utils\Util;


class CouponController extends Controller
{
    protected $commonUtil;

    public function __construct(
        Util $commonUtil

    ) {
        $this->commonUtil = $commonUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $coupons = SuperadminCoupon::get();
            return Datatables::of($coupons)
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->addColumn('action', function ($row) {
                    $html = '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary " href="' . action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'edit'], ['coupon' => $row->id]) . '">'
                        . __('superadmin::lang.edit_coupon') . '</a>';

                    $html .= ' <a href="' . action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'destroy'], [$row->id]) . '"
                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_coupon_confirmation">' . __('messages.delete') . '</a>';
                    return $html;
                })
                ->editColumn('applied_on_packages', function ($row) {
                    return implode(", ", Package::whereIn('id', json_decode($row->applied_on_packages)?? [])->pluck('name')->toArray());
                })
                ->editColumn('applied_on_business', function ($row) {
                    return implode(", ", Business::whereIn('id', json_decode($row->applied_on_business)?? [])->pluck('name')->toArray());
                })
                ->editColumn('is_active', function($row){
                    if($row->is_active == 1){
                        return 'Active';
                    }else{
                       return 'Deactive';
                    }
                })
                ->rawColumns(['applied_on_packages', 'applied_on_business', 'created_at', 'action'])
                ->make(true);
        }

        return view('superadmin::coupons.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $discount_types = [
            'fixed' => 'Fixed',
            'percentage' => 'Percentage'
        ];
        $businesses = Business::get()->pluck('name', 'id');
        $packages = Package::active()->orderby('sort_order')->pluck('name', 'id');
        return view('superadmin::coupons.create')->with(compact('packages', 'discount_types', 'businesses'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $input =  $request->except(['_token']);
            $input['applied_on_packages'] = $input['applied_on_packages'] = empty($input['applied_on_packages']) ? null : json_encode($input['applied_on_packages']);
            $input['applied_on_business'] = $input['applied_on_business'] = empty($input['applied_on_business']) ? null : json_encode($input['applied_on_business']);
            if (!empty($input['expiry_date'])) {
                $input['expiry_date'] = $this->commonUtil->uf_date($input['expiry_date']);
            }

            $input['is_active'] = $request->has('is_active') ? 1 : 0;

            SuperadminCoupon::create($input);

            $output = [
                'success' => 1,
                'msg' => __('superadmin::lang.coupon_created_succesfully'),
            ];

            return redirect()
                ->action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('superadmin::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $coupon = SuperadminCoupon::findorfail($id);
        $discount_types = [
            'fixed' => 'Fixed',
            'percentage' => 'Percentage'
        ];
        $businesses = Business::get()->pluck('name', 'id');
        $packages = Package::active()->orderby('sort_order')->pluck('name', 'id');
        return view('superadmin::coupons.edit')->with(compact('coupon', 'packages', 'discount_types', 'businesses'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input =  $request->except(['_token']);

            $input['applied_on_packages'] = $input['applied_on_packages'] = empty($input['applied_on_packages']) ? null : json_encode($input['applied_on_packages']);

            $input['applied_on_business'] = $input['applied_on_business'] = empty($input['applied_on_business']) ? null : json_encode($input['applied_on_business']);
            
            if (!empty($input['expiry_date'])) {
                $input['expiry_date'] = $this->commonUtil->uf_date($input['expiry_date']);
            }

            $input['is_active'] = $request->has('is_active') ? 1 : 0;

            SuperadminCoupon::findorfail($id)->update($input);

            $output = [
                'success' => 1,
                'msg' => __('superadmin::lang.coupon_updated_succesfully'),
            ];

            return redirect()
                ->action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            SuperadminCoupon::where('id', $id)->delete();
            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
            return redirect()
                ->action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }
}
