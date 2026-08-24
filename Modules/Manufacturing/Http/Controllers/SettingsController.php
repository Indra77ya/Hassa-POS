<?php

namespace Modules\Manufacturing\Http\Controllers;

use App\Account;
use App\Business;
use App\System;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Manufacturing\Utils\ManufacturingUtil;

class SettingsController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $mfgUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, ManufacturingUtil $mfgUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->mfgUtil = $mfgUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $manufacturing_settings = $this->mfgUtil->getSettings($business_id);

        $version = System::getProperty('manufacturing_version');

        $accounting_accounts = AccountingAccount::forDropdown($business_id);

        $payment_accounts = Account::forDropdown($business_id, true, false);

        return view('manufacturing::settings.index')->with(compact('manufacturing_settings', 'version', 'accounting_accounts', 'payment_accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $settings = $request->only([
                'ref_no_prefix',
                'mfg_raw_material_account_id',
                'mfg_finished_goods_account_id',
                'mfg_production_cost_account_id',
                'mfg_payment_account_id',
            ]);

            $settings['disable_editing_ingredient_qty'] = ! empty($request->input('disable_editing_ingredient_qty')) ? true : false;

            $settings['enable_updating_product_price'] = ! empty($request->input('enable_updating_product_price')) ? true : false;

            $business = Business::where('id', $business_id)
                                ->update(['manufacturing_settings' => json_encode($settings)]);

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Auto map or create required manufacturing accounts.
     *
     * @return Response
     */
    public function autoMapAccounts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user_id = auth()->user()->id;

            // 1. Raw Materials Inventory Account
            $raw_mat_acc = AccountingAccount::where('business_id', $business_id)
                ->where(function($q) {
                    $q->where('name', 'like', '%Persediaan Bahan Baku%')
                      ->orWhere('name', 'like', '%Bahan Baku%')
                      ->orWhere('name', 'like', '%Raw Material%');
                })
                ->where('status', 'active')
                ->first();

            if (!$raw_mat_acc) {
                // Check for fallback inventory sub_type
                $sub_type_id = \Modules\Accounting\Entities\AccountingAccountType::where('account_primary_type', 'asset')
                    ->where('name', 'persediaan')
                    ->value('id') ?? 2;

                $raw_mat_acc = AccountingAccount::create([
                    'name' => 'Persediaan Bahan Baku',
                    'gl_code' => '1302',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => $sub_type_id,
                    'status' => 'active',
                    'created_by' => $user_id,
                ]);
            }

            // 2. Finished Goods Inventory Account
            $finished_acc = AccountingAccount::where('business_id', $business_id)
                ->where(function($q) {
                    $q->where('name', 'like', '%Persediaan Barang Jadi%')
                      ->orWhere('name', 'like', '%Barang Jadi%')
                      ->orWhere('name', 'like', '%Finished Goods%');
                })
                ->where('status', 'active')
                ->first();

            if (!$finished_acc) {
                $sub_type_id = \Modules\Accounting\Entities\AccountingAccountType::where('account_primary_type', 'asset')
                    ->where('name', 'persediaan')
                    ->value('id') ?? 2;

                $finished_acc = AccountingAccount::create([
                    'name' => 'Persediaan Barang Jadi',
                    'gl_code' => '1303',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => $sub_type_id,
                    'status' => 'active',
                    'created_by' => $user_id,
                ]);
            }

            // 3. Production Cost / Overhead Account
            $prod_cost_acc = AccountingAccount::where('business_id', $business_id)
                ->where(function($q) {
                    $q->where('name', 'like', '%Biaya Produksi%')
                      ->orWhere('name', 'like', '%Overhead%')
                      ->orWhere('name', 'like', '%Production Cost%');
                })
                ->where('status', 'active')
                ->first();

            if (!$prod_cost_acc) {
                $sub_type_id = \Modules\Accounting\Entities\AccountingAccountType::where('account_primary_type', 'expenses')
                    ->where('name', 'beban_operasional')
                    ->value('id') ?? 14;

                $prod_cost_acc = AccountingAccount::create([
                    'name' => 'Biaya Produksi / Overhead',
                    'gl_code' => '6100',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => $sub_type_id,
                    'status' => 'active',
                    'created_by' => $user_id,
                ]);
            }

            // 4. Default Payment Account (Kas / Bank)
            $payment_acc = Account::where('business_id', $business_id)
                ->where('is_closed', 0)
                ->where(function($q) {
                    $q->where('name', 'like', '%Kas%')
                      ->orWhere('name', 'like', '%Bank%');
                })
                ->first();

            if (!$payment_acc) {
                $payment_acc = Account::create([
                    'name' => 'Kas Operasional Produksi',
                    'business_id' => $business_id,
                    'created_by' => $user_id,
                    'is_closed' => 0,
                ]);
            }

            // Save auto mapped accounts in settings
            $manufacturing_settings = $this->mfgUtil->getSettings($business_id);
            $manufacturing_settings['mfg_raw_material_account_id'] = $raw_mat_acc->id;
            $manufacturing_settings['mfg_finished_goods_account_id'] = $finished_acc->id;
            $manufacturing_settings['mfg_production_cost_account_id'] = $prod_cost_acc->id;
            $manufacturing_settings['mfg_payment_account_id'] = $payment_acc->id;

            Business::where('id', $business_id)
                ->update(['manufacturing_settings' => json_encode($manufacturing_settings)]);

            $output = [
                'success' => true,
                'msg' => 'Auto Mapping Akun Manufaktur berhasil disinkronkan!',
                'data' => [
                    'mfg_raw_material_account_id' => $raw_mat_acc->id,
                    'mfg_raw_material_account_name' => $raw_mat_acc->name,
                    'mfg_finished_goods_account_id' => $finished_acc->id,
                    'mfg_finished_goods_account_name' => $finished_acc->name,
                    'mfg_production_cost_account_id' => $prod_cost_acc->id,
                    'mfg_production_cost_account_name' => $prod_cost_acc->name,
                    'mfg_payment_account_id' => $payment_acc->id,
                    'mfg_payment_account_name' => $payment_acc->name,
                ]
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($request->ajax()) {
            return response()->json($output);
        }

        return redirect()->back()->with('status', $output);
    }
}
