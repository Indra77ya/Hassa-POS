<?php

namespace Modules\AssetManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetCategory;
use App\Utils\Util;
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
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.view'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $assets = Asset::where('business_id', $business_id)
                ->with(['category', 'depreciationLogs'])
                ->select(['id', 'business_id', 'name', 'asset_category_id', 'sku', 'historical_cost', 'salvage_value', 'purchase_date', 'useful_life_months', 'is_active', 'is_disposed']);

            return DataTables::of($assets)
                ->addColumn('category_name', function ($row) {
                    return $row->category->name ?? '-';
                })
                ->addColumn('accumulated_depreciation', function ($row) {
                    return $this->commonUtil->num_f($row->accumulated_depreciation, true);
                })
                ->addColumn('net_book_value', function ($row) {
                    return $this->commonUtil->num_f($row->net_book_value, true);
                })
                ->editColumn('historical_cost', function ($row) {
                    return $this->commonUtil->num_f($row->historical_cost, true);
                })
                ->editColumn('salvage_value', function ($row) {
                    return $this->commonUtil->num_f($row->salvage_value, true);
                })
                ->editColumn('purchase_date', function ($row) {
                    return $this->commonUtil->format_date($row->purchase_date);
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_disposed) {
                        return '<span class="label bg-red">' . __('assetmanagement::lang.disposed') . '</span>';
                    } elseif ($row->is_active) {
                        return '<span class="label bg-green">' . __('assetmanagement::lang.active') . '</span>';
                    } else {
                        return '<span class="label bg-gray">' . __('assetmanagement::lang.inactive') . '</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'
                            . __('messages.actions') . ' <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    if (auth()->user()->can('asset.edit')) {
                        $html .= '<li><a data-href="' . action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'edit'], [$row->id]) . '" class="btn-modal" data-container=".asset_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }

                    if (auth()->user()->can('asset.delete')) {
                        $html .= '<li><a data-href="' . action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'destroy'], [$row->id]) . '" class="delete_asset_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('assetmanagement::asset.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $categories = AssetCategory::where('business_id', $business_id)->pluck('name', 'id');

        return view('assetmanagement::asset.create')->with(compact('categories'));
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.create'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'name', 'asset_category_id', 'sku', 'historical_cost',
                'salvage_value', 'purchase_date', 'useful_life_months',
                'depreciation_method', 'notes'
            ]);

            $input['business_id'] = $business_id;
            $input['historical_cost'] = $this->commonUtil->num_uf($input['historical_cost'] ?? 0);
            $input['salvage_value'] = $this->commonUtil->num_uf($input['salvage_value'] ?? 0);
            $input['purchase_date'] = $this->commonUtil->uf_date($input['purchase_date']);
            $input['useful_life_months'] = (int) ($input['useful_life_months'] ?? 12);
            $input['depreciation_method'] = $input['depreciation_method'] ?? 'straight_line';
            $input['is_active'] = $request->has('is_active') ? 1 : 0;
            $input['created_by'] = request()->session()->get('user.id');

            Asset::create($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_created_success'),
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

        $asset = Asset::where('business_id', $business_id)->findOrFail($id);
        $categories = AssetCategory::where('business_id', $business_id)->pluck('name', 'id');

        return view('assetmanagement::asset.edit')->with(compact('asset', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'name', 'asset_category_id', 'sku', 'historical_cost',
                'salvage_value', 'purchase_date', 'useful_life_months',
                'depreciation_method', 'notes'
            ]);

            $asset = Asset::where('business_id', $business_id)->findOrFail($id);

            $input['historical_cost'] = $this->commonUtil->num_uf($input['historical_cost'] ?? 0);
            $input['salvage_value'] = $this->commonUtil->num_uf($input['salvage_value'] ?? 0);
            $input['purchase_date'] = $this->commonUtil->uf_date($input['purchase_date']);
            $input['useful_life_months'] = (int) ($input['useful_life_months'] ?? 12);
            $input['is_active'] = $request->has('is_active') ? 1 : 0;
            $input['is_disposed'] = $request->has('is_disposed') ? 1 : 0;

            if ($input['is_disposed'] && empty($asset->disposal_date)) {
                $input['disposal_date'] = \Carbon\Carbon::now()->format('Y-m-d');
            }

            $asset->update($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_updated_success'),
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
            $asset = Asset::where('business_id', $business_id)->findOrFail($id);
            $asset->delete();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_deleted_success'),
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
