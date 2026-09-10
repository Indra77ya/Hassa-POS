<?php

namespace Modules\Laundry\Http\Controllers;

use App\Business;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LaundrySettingsController extends Controller
{
    protected $moduleUtil;
    protected $commonUtil;

    public function __construct(ModuleUtil $moduleUtil, Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * Show laundry settings form
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'laundry_module') && auth()->user()->can('laundry.manage_master_data')))) {
            abort(403, 'Unauthorized action.');
        }

        $business = Business::find($business_id);
        $laundry_settings = !empty($business->laundry_settings) ? json_decode($business->laundry_settings, true) : [];

        return view('laundry::settings.index', compact('laundry_settings'));
    }

    /**
     * Save laundry settings
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'laundry_module') && auth()->user()->can('laundry.manage_master_data')))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business = Business::find($business_id);
            $laundry_settings = !empty($business->laundry_settings) ? json_decode($business->laundry_settings, true) : [];

            // Remove logo if requested
            if ($request->has('remove_laundry_logo') && $request->input('remove_laundry_logo') == 1) {
                if (!empty($laundry_settings['laundry_logo']) && file_exists(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo']))) {
                    unlink(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo']));
                }
                $laundry_settings['laundry_logo'] = null;
            }

            // Upload new logo if provided
            if ($request->hasFile('laundry_logo')) {
                $logo_name = $this->commonUtil->uploadFile($request, 'laundry_logo', 'laundry_logos', 'image');
                if (!empty($logo_name)) {
                    // Remove old logo file if exists
                    if (!empty($laundry_settings['laundry_logo']) && file_exists(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo']))) {
                        unlink(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo']));
                    }
                    $laundry_settings['laundry_logo'] = $logo_name;
                }
            }

            $business->laundry_settings = json_encode($laundry_settings);
            $business->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }
}
