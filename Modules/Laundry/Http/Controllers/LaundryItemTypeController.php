<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryItemType;
use Modules\Laundry\Entities\LaundryProcess;
use Yajra\DataTables\Facades\DataTables;

class LaundryItemTypeController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $item_types = LaundryItemType::where('business_id', $business_id);

            return DataTables::of($item_types)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary edit_item_type_button"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button> ';
                    $html .= '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_item_type_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    return $html;
                })
                ->addColumn('default_processes', function ($row) {
                    if (empty($row->process_ids)) return '-';
                    $processes = LaundryProcess::whereIn('id', $row->process_ids)->pluck('name')->toArray();
                    return implode(', ', $processes);
                })
                ->rawColumns(['action', 'default_processes'])
                ->make(true);
        }

        return view('laundry::item_type.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->pluck('name', 'id');

        return view('laundry::item_type.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'unit_name', 'default_price', 'description', 'process_ids']);
            $input['business_id'] = $business_id;

            LaundryItemType::create($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.item_type_added_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $item_type = LaundryItemType::where('business_id', $business_id)->findOrFail($id);
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->pluck('name', 'id');

        return view('laundry::item_type.edit', compact('item_type', 'processes'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'unit_name', 'default_price', 'description', 'process_ids']);
            $item_type = LaundryItemType::where('business_id', $business_id)->findOrFail($id);
            $item_type->update($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.item_type_updated_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            LaundryItemType::where('business_id', $business_id)->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __('laundry::lang.item_type_deleted_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function getItemTypeDetails($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $item_type = LaundryItemType::where('business_id', $business_id)->find($id);

        $processes = [];
        if ($item_type && !empty($item_type->process_ids)) {
            $processes = LaundryProcess::where('business_id', $business_id)
                ->whereIn('id', $item_type->process_ids)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'points']);
        }

        return response()->json([
            'unit_name' => $item_type ? $item_type->unit_name : 'kg',
            'default_price' => $item_type ? $item_type->default_price : 0,
            'processes' => $processes,
        ]);
    }
}
