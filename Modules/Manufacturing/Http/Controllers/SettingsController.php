<?php

namespace Modules\Manufacturing\Http\Controllers;

use App\Business;
use App\System;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Manufacturing\Utils\ManufacturingUtil;
use Modules\Accounting\Entities\AccountingAccount;
use App\Account;
use App\AccountType;

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

        $accounting_accounts = [];
        if (class_exists('Modules\Accounting\Entities\AccountingAccount')) {
            $accounting_accounts = AccountingAccount::forDropdown($business_id, true);
        }

        $pos_accounts = Account::forDropdown($business_id, true, false, false, true);

        return view('manufacturing::settings.index')->with(compact('manufacturing_settings', 'version', 'accounting_accounts', 'pos_accounts'));
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
     * Auto map or create default manufacturing accounts.
     *
     * @return Response
     */
    public function autoMapAccounts()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user_id = auth()->user()->id;

            // 1. Ensure Account Types exist in POS accounts table
            $type_persediaan = AccountType::where('business_id', $business_id)->where('fixed_key', 'persediaan')->first();
            if (!$type_persediaan) {
                $type_persediaan = AccountType::create(['name' => __('account.persediaan'), 'business_id' => $business_id, 'parent_account_type_id' => null, 'fixed_key' => 'persediaan']);
            }

            $type_beban = AccountType::where('business_id', $business_id)->where('fixed_key', 'beban_operasional')->first();
            if (!$type_beban) {
                $type_beban = AccountType::create(['name' => __('account.beban_operasional'), 'business_id' => $business_id, 'parent_account_type_id' => null, 'fixed_key' => 'beban_operasional']);
            }

            // 2. Ensure default POS accounts exist
            $pos_defaults = [
                'raw' => ['name' => 'Persediaan Bahan Baku', 'type_id' => $type_persediaan->id, 'number' => '1302', 'balance' => 'debit'],
                'finished' => ['name' => 'Persediaan Barang Jadi', 'type_id' => $type_persediaan->id, 'number' => '1303', 'balance' => 'debit'],
                'cost' => ['name' => 'Biaya Produksi / Overhead', 'type_id' => $type_beban->id, 'number' => '6105', 'balance' => 'debit'],
            ];

            foreach ($pos_defaults as $key => $da) {
                $exists = Account::where('business_id', $business_id)->where('name', $da['name'])->first();
                if (!$exists) {
                    Account::create([
                        'name' => $da['name'],
                        'business_id' => $business_id,
                        'account_number' => $da['number'],
                        'account_type_id' => $da['type_id'],
                        'normal_balance' => $da['balance'],
                        'created_by' => $user_id,
                    ]);
                }
            }

            // 3. Ensure default Accounting module accounts exist
            $accounting_raw_id = null;
            $accounting_finished_id = null;
            $accounting_cost_id = null;

            if (class_exists('Modules\Accounting\Entities\AccountingAccount')) {
                $raw_acc = AccountingAccount::where('business_id', $business_id)->where('name', 'like', '%Persediaan Bahan Baku%')->first();
                if (!$raw_acc) {
                    $raw_acc = AccountingAccount::create([
                        'name' => 'Persediaan Bahan Baku', 'business_id' => $business_id, 'account_primary_type' => 'asset',
                        'account_sub_type_id' => 2, 'detail_type_id' => 21, 'gl_code' => '1302', 'status' => 'active', 'created_by' => $user_id,
                    ]);
                }
                $accounting_raw_id = $raw_acc->id;

                $finished_acc = AccountingAccount::where('business_id', $business_id)->where('name', 'like', '%Persediaan Barang Jadi%')->first();
                if (!$finished_acc) {
                    $finished_acc = AccountingAccount::create([
                        'name' => 'Persediaan Barang Jadi', 'business_id' => $business_id, 'account_primary_type' => 'asset',
                        'account_sub_type_id' => 2, 'detail_type_id' => 21, 'gl_code' => '1303', 'status' => 'active', 'created_by' => $user_id,
                    ]);
                }
                $accounting_finished_id = $finished_acc->id;

                $cost_acc = AccountingAccount::where('business_id', $business_id)->where('name', 'like', '%Biaya Produksi%')->first();
                if (!$cost_acc) {
                    $cost_acc = AccountingAccount::create([
                        'name' => 'Biaya Produksi / Overhead', 'business_id' => $business_id, 'account_primary_type' => 'expenses',
                        'account_sub_type_id' => 14, 'detail_type_id' => 138, 'gl_code' => '6105', 'status' => 'active', 'created_by' => $user_id,
                    ]);
                }
                $accounting_cost_id = $cost_acc->id;
            }

            // Get Kas/Bank account for payment account setting
            $kas_pos = Account::where('business_id', $business_id)->where(function($q) {
                $q->where('name', 'like', '%Kas%')->orWhere('name', 'like', '%Bank%');
            })->first();

            // Save settings
            $settings = $this->mfgUtil->getSettings($business_id);
            $settings['mfg_raw_material_account_id'] = $accounting_raw_id;
            $settings['mfg_finished_goods_account_id'] = $accounting_finished_id;
            $settings['mfg_production_cost_account_id'] = $accounting_cost_id;
            if (!empty($kas_pos)) {
                $settings['mfg_payment_account_id'] = $kas_pos->id;
            }

            Business::where('id', $business_id)->update(['manufacturing_settings' => json_encode($settings)]);

            $output = ['success' => 1, 'msg' => __('Pemetaan akun Manufaktur berhasil disinkronkan secara otomatis.')];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->back()->with('status', $output);
    }
}
