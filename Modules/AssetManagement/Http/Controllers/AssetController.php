<?php

namespace Modules\AssetManagement\Http\Controllers;

use App\BusinessLocation;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetCategory;
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

        return view('assetmanagement::assets.create', compact('categories', 'locations'));
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
            ]);

            $input['business_id'] = $business_id;
            $input['purchase_date'] = $this->commonUtil->uf_date($request->input('purchase_date'));
            $input['purchase_price'] = $this->commonUtil->num_uf($request->input('purchase_price'));
            $input['salvage_value'] = $request->filled('salvage_value') ? $this->commonUtil->num_uf($request->input('salvage_value')) : 0;
            $input['created_by'] = auth()->user()->id;

            Asset::create($input);

            // Ensure settings & accounts exist
            AssetSetting::forBusiness($business_id);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_created_successfully'),
            ];
        } catch (\Exception $e) {
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

        return view('assetmanagement::assets.edit', compact('asset', 'categories', 'locations'));
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
            ]);

            $input['purchase_date'] = $this->commonUtil->uf_date($request->input('purchase_date'));
            $input['purchase_price'] = $this->commonUtil->num_uf($request->input('purchase_price'));
            $input['salvage_value'] = $request->filled('salvage_value') ? $this->commonUtil->num_uf($request->input('salvage_value')) : 0;

            $asset->update($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_updated_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'index'])->with('status', $output);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('asset.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $asset = Asset::where('business_id', $business_id)->findOrFail($id);
            $asset->delete();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.asset_deleted_successfully'),
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
