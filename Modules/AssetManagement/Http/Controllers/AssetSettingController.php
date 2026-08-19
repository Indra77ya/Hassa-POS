<?php

namespace Modules\AssetManagement\Http\Controllers;

use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\AssetManagement\Entities\AssetSetting;

class AssetSettingController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    public function index()
    {
        if (!auth()->user()->can('asset.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $setting = AssetSetting::forBusiness($business_id);

        $accounts = AccountingAccount::where('business_id', $business_id)
            ->where('status', 'active')
            ->pluck('name', 'id');

        return view('assetmanagement::settings.index', compact('setting', 'accounts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('asset.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $setting = AssetSetting::forBusiness($business_id);

            $setting->depreciation_expense_account_id = $request->input('depreciation_expense_account_id');
            $setting->accumulated_depreciation_account_id = $request->input('accumulated_depreciation_account_id');
            $setting->save();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.settings_updated_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }
}
