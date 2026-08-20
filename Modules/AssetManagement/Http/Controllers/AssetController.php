<?php

namespace Modules\AssetManagement\Http\Controllers;

use App\BusinessLocation;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Carbon\Carbon;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetCategory;
use Modules\AssetManagement\Entities\AssetDepreciationLog;
use Modules\AssetManagement\Entities\AssetSetting;
use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
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

        if (request()->ajax()) {
            $assets = Asset::where('assets.business_id', $business_id)
                ->leftJoin('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id')
                ->leftJoin('business_locations', 'assets.location_id', '=', 'business_locations.id')
                ->select([
                    'assets.id',
                    'assets.name',
                    'assets.asset_code',
                    'asset_categories.name as category_name',
                    'business_locations.name as location_name',
                    'assets.purchase_date',
                    'assets.purchase_price',
                    'assets.salvage_value',
                    'assets.useful_life',
                    'assets.status',
                ]);

            if (request()->has('category_id') && !empty(request()->category_id)) {
                $assets->where('assets.asset_category_id', request()->category_id);
            }

            if (request()->has('location_id') && !empty(request()->location_id)) {
                $assets->where('assets.location_id', request()->location_id);
            }

            if (request()->has('status') && !empty(request()->status)) {
                $assets->where('assets.status', request()->status);
            }

            return DataTables::of($assets)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' . __('messages.action') . ' <span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    if (auth()->user()->can('asset.view')) {
                        $html .= '<li><a href="' . action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'show'], [$row->id]) . '"><i class="glyphicon glyphicon-eye-open"></i> ' . __('messages.view') . '</a></li>';
                    }

                    if (auth()->user()->can('asset.edit')) {
                        $html .= '<li><a href="' . action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }

                    if (auth()->user()->can('asset.edit')) {
                        $html .= '<li><a href="' . action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'depreciate'], [$row->id]) . '" class="process_depreciation_button"><i class="fa fa-calculator"></i> ' . __('assetmanagement::lang.process_depreciation') . '</a></li>';
                    }

                    if (auth()->user()->can('asset.delete')) {
                        $html .= '<li><a href="' . action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'destroy'], [$row->id]) . '" class="delete_asset_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->addColumn('monthly_depreciation', function ($row) {
                    $asset = Asset::find($row->id);
                    return $this->commonUtil->num_f($asset->monthly_depreciation, true);
                })
                ->addColumn('total_accumulated_depreciation', function ($row) {
                    $asset = Asset::find($row->id);
                    return $this->commonUtil->num_f($asset->total_accumulated_depreciation, true);
                })
                ->addColumn('net_book_value', function ($row) {
                    $asset = Asset::find($row->id);
                    return $this->commonUtil->num_f($asset->net_book_value, true);
                })
                ->editColumn('purchase_price', function ($row) {
                    return $this->commonUtil->num_f($row->purchase_price, true);
                })
                ->editColumn('salvage_value', function ($row) {
                    return $this->commonUtil->num_f($row->salvage_value, true);
                })
                ->editColumn('purchase_date', function ($row) {
                    return $this->commonUtil->format_date($row->purchase_date);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $categories = AssetCategory::where('business_id', $business_id)->pluck('name', 'id');
        $locations = BusinessLocation::forDropdown($business_id);

        return view('assetmanagement::assets.index', compact('categories', 'locations'));
    }

    public function create()
    {
        if (!auth()->user()->can('asset.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $categories = AssetCategory::where('business_id', $business_id)->pluck('name', 'id');
        $locations = BusinessLocation::forDropdown($business_id);

        // Fixed Asset accounts (Debit options: sub_type_id 4 - aktiva_tetap or sub_type_id 5 - aktiva_lainnya)
        $fixed_asset_accounts = AccountingAccount::where('business_id', $business_id)
            ->where('status', 'active')
            ->whereIn('account_sub_type_id', [4, 5])
            ->pluck('name', 'id');

        // Payment accounts / Funding Sources (Credit options: Kas/Bank [3], Hutang [6,7,8,9], Modal [10])
        $payment_accounts = AccountingAccount::where('business_id', $business_id)
            ->where('status', 'active')
            ->whereIn('account_sub_type_id', [3, 6, 7, 8, 9, 10])
            ->pluck('name', 'id');

        return view('assetmanagement::assets.create', compact('categories', 'locations', 'fixed_asset_accounts', 'payment_accounts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('asset.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $request->validate([
                'name' => 'required|string|max:255',
                'purchase_date' => 'required|date',
                'purchase_price' => 'required',
                'useful_life' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $input = $request->only([
                'name',
                'asset_code',
                'asset_category_id',
                'location_id',
                'purchase_date',
                'useful_life',
                'depreciation_method',
                'status',
                'description',
                'fixed_asset_account_id',
                'payment_account_id',
            ]);

            $input['business_id'] = $business_id;
            $input['purchase_date'] = $this->commonUtil->uf_date($request->input('purchase_date'));
            $input['purchase_price'] = $this->commonUtil->num_uf($request->input('purchase_price'));
            $input['salvage_value'] = $request->filled('salvage_value') ? $this->commonUtil->num_uf($request->input('salvage_value')) : 0;
            $input['created_by'] = auth()->user()->id;

            // Ensure settings & accounts exist
            AssetSetting::forBusiness($business_id);

            // Auto-Journal for Asset Acquisition
            if (!empty($input['fixed_asset_account_id']) && !empty($input['payment_account_id']) && $input['purchase_price'] > 0) {
                $refNo = 'ASSET-ACQ-' . $business_id . '-' . time();

                $accTransMapping = new AccountingAccTransMapping();
                $accTransMapping->business_id = $business_id;
                $accTransMapping->ref_no = $refNo;
                $accTransMapping->note = "Perolehan Aset Tetap: {$input['name']}" . (!empty($input['asset_code']) ? " ({$input['asset_code']})" : '');
                $accTransMapping->type = 'journal_entry';
                $accTransMapping->created_by = auth()->user()->id;
                $accTransMapping->operation_date = $input['purchase_date'];
                $accTransMapping->save();

                // Debit: Fixed Asset Account
                $debitTrans = new AccountingAccountsTransaction();
                $debitTrans->accounting_account_id = $input['fixed_asset_account_id'];
                $debitTrans->amount = $input['purchase_price'];
                $debitTrans->type = 'debit';
                $debitTrans->created_by = auth()->user()->id;
                $debitTrans->operation_date = $input['purchase_date'];
                $debitTrans->sub_type = 'journal_entry';
                $debitTrans->acc_trans_mapping_id = $accTransMapping->id;
                $debitTrans->save();

                // Credit: Payment Account (Funding Source)
                $creditTrans = new AccountingAccountsTransaction();
                $creditTrans->accounting_account_id = $input['payment_account_id'];
                $creditTrans->amount = $input['purchase_price'];
                $creditTrans->type = 'credit';
                $creditTrans->created_by = auth()->user()->id;
                $creditTrans->operation_date = $input['purchase_date'];
                $creditTrans->sub_type = 'journal_entry';
                $creditTrans->acc_trans_mapping_id = $accTransMapping->id;
                $creditTrans->save();

                $input['accounting_acc_trans_mapping_id'] = $accTransMapping->id;
            }

            Asset::create($input);

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_created_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'index'])->with('status', $output);
    }

    public function show($id)
    {
        if (!auth()->user()->can('asset.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $asset = Asset::where('business_id', $business_id)
            ->with(['category', 'location', 'depreciationLogs'])
            ->findOrFail($id);

        return view('assetmanagement::assets.show', compact('asset'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('asset.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $asset = Asset::where('business_id', $business_id)->findOrFail($id);
        $categories = AssetCategory::where('business_id', $business_id)->pluck('name', 'id');
        $locations = BusinessLocation::forDropdown($business_id);

        $fixed_asset_accounts = AccountingAccount::where('business_id', $business_id)
            ->where('status', 'active')
            ->whereIn('account_sub_type_id', [4, 5])
            ->pluck('name', 'id');

        $payment_accounts = AccountingAccount::where('business_id', $business_id)
            ->where('status', 'active')
            ->whereIn('account_sub_type_id', [3, 6, 7, 8, 9, 10])
            ->pluck('name', 'id');

        return view('assetmanagement::assets.edit', compact('asset', 'categories', 'locations', 'fixed_asset_accounts', 'payment_accounts'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('asset.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $asset = Asset::where('business_id', $business_id)->findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'purchase_date' => 'required|date',
                'purchase_price' => 'required',
                'useful_life' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $input = $request->only([
                'name',
                'asset_code',
                'asset_category_id',
                'location_id',
                'purchase_date',
                'useful_life',
                'depreciation_method',
                'status',
                'description',
                'fixed_asset_account_id',
                'payment_account_id',
            ]);

            $input['purchase_date'] = $this->commonUtil->uf_date($request->input('purchase_date'));
            $input['purchase_price'] = $this->commonUtil->num_uf($request->input('purchase_price'));
            $input['salvage_value'] = $request->filled('salvage_value') ? $this->commonUtil->num_uf($request->input('salvage_value')) : 0;

            // Sync Acquisition Journal Entry
            if (!empty($input['fixed_asset_account_id']) && !empty($input['payment_account_id']) && $input['purchase_price'] > 0) {
                $accTransMapping = null;
                if (!empty($asset->accounting_acc_trans_mapping_id)) {
                    $accTransMapping = AccountingAccTransMapping::where('business_id', $business_id)
                        ->find($asset->accounting_acc_trans_mapping_id);
                }

                if (!$accTransMapping) {
                    $refNo = 'ASSET-ACQ-' . $business_id . '-' . time();
                    $accTransMapping = new AccountingAccTransMapping();
                    $accTransMapping->business_id = $business_id;
                    $accTransMapping->ref_no = $refNo;
                    $accTransMapping->type = 'journal_entry';
                    $accTransMapping->created_by = auth()->user()->id;
                }

                $accTransMapping->note = "Perolehan Aset Tetap: {$input['name']}" . (!empty($input['asset_code']) ? " ({$input['asset_code']})" : '');
                $accTransMapping->operation_date = $input['purchase_date'];
                $accTransMapping->save();

                // Clear previous transactions on this mapping
                AccountingAccountsTransaction::where('acc_trans_mapping_id', $accTransMapping->id)->delete();

                // Debit: Fixed Asset Account
                $debitTrans = new AccountingAccountsTransaction();
                $debitTrans->accounting_account_id = $input['fixed_asset_account_id'];
                $debitTrans->amount = $input['purchase_price'];
                $debitTrans->type = 'debit';
                $debitTrans->created_by = auth()->user()->id;
                $debitTrans->operation_date = $input['purchase_date'];
                $debitTrans->sub_type = 'journal_entry';
                $debitTrans->acc_trans_mapping_id = $accTransMapping->id;
                $debitTrans->save();

                // Credit: Payment Account (Funding Source)
                $creditTrans = new AccountingAccountsTransaction();
                $creditTrans->accounting_account_id = $input['payment_account_id'];
                $creditTrans->amount = $input['purchase_price'];
                $creditTrans->type = 'credit';
                $creditTrans->created_by = auth()->user()->id;
                $creditTrans->operation_date = $input['purchase_date'];
                $creditTrans->sub_type = 'journal_entry';
                $creditTrans->acc_trans_mapping_id = $accTransMapping->id;
                $creditTrans->save();

                $input['accounting_acc_trans_mapping_id'] = $accTransMapping->id;
            } elseif (!empty($asset->accounting_acc_trans_mapping_id)) {
                AccountingAccountsTransaction::where('acc_trans_mapping_id', $asset->accounting_acc_trans_mapping_id)->delete();
                AccountingAccTransMapping::where('id', $asset->accounting_acc_trans_mapping_id)->delete();
                $input['accounting_acc_trans_mapping_id'] = null;
            }

            $asset->update($input);

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_updated_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'index'])->with('status', $output);
    }

    public function depreciate($id)
    {
        if (!auth()->user()->can('asset.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $asset = Asset::where('business_id', $business_id)->findOrFail($id);

            $setting = AssetSetting::forBusiness($business_id);
            $expenseAccountId = $setting->depreciation_expense_account_id;
            $accumAccountId = $setting->accumulated_depreciation_account_id;

            if (!$expenseAccountId || !$accumAccountId) {
                return [
                    'success' => false,
                    'msg' => __('assetmanagement::lang.depreciation_accounts_not_configured'),
                ];
            }

            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;
            $depreciationDate = $now->format('Y-m-d');

            $alreadyLogged = AssetDepreciationLog::where('asset_id', $asset->id)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            if ($alreadyLogged) {
                return [
                    'success' => false,
                    'msg' => __('assetmanagement::lang.already_depreciated_this_month'),
                ];
            }

            $monthlyAmount = $asset->monthly_depreciation;
            $maxDepreciable = max(0, $asset->purchase_price - $asset->salvage_value);
            $currentAccumulated = $asset->total_accumulated_depreciation;

            if ($currentAccumulated >= $maxDepreciable) {
                return [
                    'success' => false,
                    'msg' => __('assetmanagement::lang.asset_fully_depreciated'),
                ];
            }

            if ($currentAccumulated + $monthlyAmount > $maxDepreciable) {
                $monthlyAmount = round($maxDepreciable - $currentAccumulated, 4);
            }

            if ($monthlyAmount <= 0) {
                return [
                    'success' => false,
                    'msg' => __('assetmanagement::lang.invalid_depreciation_amount'),
                ];
            }

            DB::beginTransaction();

            $refNo = 'DEPR-' . $business_id . '-' . $asset->id . '-' . $year . sprintf('%02d', $month);

            $accTransMapping = new AccountingAccTransMapping();
            $accTransMapping->business_id = $business_id;
            $accTransMapping->ref_no = $refNo;
            $accTransMapping->note = "Penyusutan Bulanan Manual Aset: {$asset->name}" . (!empty($asset->asset_code) ? " ({$asset->asset_code})" : '') . " Periode {$year}-" . sprintf('%02d', $month);
            $accTransMapping->type = 'journal_entry';
            $accTransMapping->created_by = auth()->user()->id;
            $accTransMapping->operation_date = $depreciationDate;
            $accTransMapping->save();

            // Debit: Beban Penyusutan
            $debitTrans = new AccountingAccountsTransaction();
            $debitTrans->accounting_account_id = $expenseAccountId;
            $debitTrans->amount = $monthlyAmount;
            $debitTrans->type = 'debit';
            $debitTrans->created_by = auth()->user()->id;
            $debitTrans->operation_date = $depreciationDate;
            $debitTrans->sub_type = 'journal_entry';
            $debitTrans->acc_trans_mapping_id = $accTransMapping->id;
            $debitTrans->save();

            // Credit: Akumulasi Penyusutan
            $creditTrans = new AccountingAccountsTransaction();
            $creditTrans->accounting_account_id = $accumAccountId;
            $creditTrans->amount = $monthlyAmount;
            $creditTrans->type = 'credit';
            $creditTrans->created_by = auth()->user()->id;
            $creditTrans->operation_date = $depreciationDate;
            $creditTrans->sub_type = 'journal_entry';
            $creditTrans->acc_trans_mapping_id = $accTransMapping->id;
            $creditTrans->save();

            // Create log entry
            AssetDepreciationLog::create([
                'business_id' => $business_id,
                'asset_id' => $asset->id,
                'depreciation_date' => $depreciationDate,
                'year' => $year,
                'month' => $month,
                'amount' => $monthlyAmount,
                'accounting_acc_trans_mapping_id' => $accTransMapping->id,
            ]);

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.depreciation_processed_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('asset.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $asset = Asset::where('business_id', $business_id)->findOrFail($id);

            DB::beginTransaction();

            if (!empty($asset->accounting_acc_trans_mapping_id)) {
                AccountingAccountsTransaction::where('acc_trans_mapping_id', $asset->accounting_acc_trans_mapping_id)->delete();
                AccountingAccTransMapping::where('id', $asset->accounting_acc_trans_mapping_id)->delete();
            }

            $asset->delete();

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_deleted_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
