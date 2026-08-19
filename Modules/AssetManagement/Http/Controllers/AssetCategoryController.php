<?php

namespace Modules\AssetManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AssetManagement\Entities\AssetCategory;
use Modules\Accounting\Entities\AccountingAccount;
use Yajra\DataTables\Facades\DataTables;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.view'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $categories = AssetCategory::where('business_id', $business_id)
                ->with(['depreciationExpenseAccount', 'accumulatedDepreciationAccount'])
                ->select(['id', 'name', 'description', 'depreciation_expense_account_id', 'accumulated_depreciation_account_id']);

            return DataTables::of($categories)
                ->addColumn('expense_account', function ($row) {
                    return $row->depreciationExpenseAccount->name ?? '-';
                })
                ->addColumn('accumulated_account', function ($row) {
                    return $row->accumulatedDepreciationAccount->name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'
                            . __('messages.actions') . ' <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    if (auth()->user()->can('asset.edit')) {
                        $html .= '<li><a data-href="' . action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'edit'], [$row->id]) . '" class="btn-modal" data-container=".category_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }

                    if (auth()->user()->can('asset.delete')) {
                        $html .= '<li><a data-href="' . action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'destroy'], [$row->id]) . '" class="delete_category_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('assetmanagement::category.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $expense_accounts = AccountingAccount::where('business_id', $business_id)
            ->whereIn('account_primary_type', ['expense', 'expenses'])
            ->pluck('name', 'id');

        $asset_accounts = AccountingAccount::where('business_id', $business_id)
            ->whereIn('account_primary_type', ['asset'])
            ->pluck('name', 'id');

        return view('assetmanagement::category.create')
            ->with(compact('expense_accounts', 'asset_accounts'));
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.create'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description', 'depreciation_expense_account_id', 'accumulated_depreciation_account_id']);
            $input['business_id'] = $business_id;
            $input['created_by'] = request()->session()->get('user.id');

            AssetCategory::create($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.category_created_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $category = AssetCategory::where('business_id', $business_id)->findOrFail($id);

        $expense_accounts = AccountingAccount::where('business_id', $business_id)
            ->whereIn('account_primary_type', ['expense', 'expenses'])
            ->pluck('name', 'id');

        $asset_accounts = AccountingAccount::where('business_id', $business_id)
            ->whereIn('account_primary_type', ['asset'])
            ->pluck('name', 'id');

        return view('assetmanagement::category.edit')
            ->with(compact('category', 'expense_accounts', 'asset_accounts'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description', 'depreciation_expense_account_id', 'accumulated_depreciation_account_id']);
            $category = AssetCategory::where('business_id', $business_id)->findOrFail($id);

            $category->update($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.category_updated_success'),
            ];
        } catch (\Exception $e) {
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
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.delete'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $category = AssetCategory::where('business_id', $business_id)->findOrFail($id);
            $category->delete();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.category_deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
