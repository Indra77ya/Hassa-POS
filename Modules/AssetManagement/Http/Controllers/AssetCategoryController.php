<?php

namespace Modules\AssetManagement\Http\Controllers;

use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AssetManagement\Entities\AssetCategory;
use Yajra\DataTables\Facades\DataTables;

class AssetCategoryController extends Controller
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
            $categories = AssetCategory::where('business_id', $business_id)->select(['id', 'name', 'code', 'description']);

            return DataTables::of($categories)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' . __('messages.action') . ' <span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    if (auth()->user()->can('asset.edit')) {
                        $html .= '<li><a href="#" data-href="' . action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'update'], [$row->id]) . '" class="btn btn-modal edit_category_button" data-name="' . e($row->name) . '" data-code="' . e($row->code) . '" data-description="' . e($row->description) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }

                    if (auth()->user()->can('asset.delete')) {
                        $html .= '<li><a href="' . action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'destroy'], [$row->id]) . '" class="delete_category_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('assetmanagement::categories.index');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('asset.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $input = $request->only(['name', 'code', 'description']);
            $input['business_id'] = $business_id;
            $input['created_by'] = auth()->user()->id;

            AssetCategory::create($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.category_created_successfully'),
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

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('asset.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $category = AssetCategory::where('business_id', $business_id)->findOrFail($id);

            $input = $request->only(['name', 'code', 'description']);
            $category->update($input);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.category_updated_successfully'),
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
        if (!auth()->user()->can('asset.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $category = AssetCategory::where('business_id', $business_id)->findOrFail($id);
            $category->delete();

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.category_deleted_successfully'),
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
