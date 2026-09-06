<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryStatus;
use Yajra\DataTables\Facades\DataTables;

class LaundryStatusController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $statuses = LaundryStatus::where('business_id', $business_id);

            return DataTables::of($statuses)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryStatusController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button> ';
                    $html .= '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryStatusController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_status_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    return $html;
                })
                ->editColumn('color', function ($row) {
                    return '<span class="badge" style="background-color: ' . e($row->color) . ';">' . e($row->color) . '</span>';
                })
                ->editColumn('is_completed_status', function ($row) {
                    return $row->is_completed_status ? '<span class="label bg-green">' . __('laundry::lang.yes') . '</span>' : '<span class="label bg-gray">' . __('laundry::lang.no') . '</span>';
                })
                ->rawColumns(['action', 'color', 'is_completed_status'])
                ->make(true);
        }

        return view('laundry::status.index');
    }

    public function create()
    {
        return view('laundry::status.create');
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'color', 'sort_order', 'is_completed_status']);
            $input['business_id'] = $business_id;
            $input['is_completed_status'] = !empty($input['is_completed_status']) ? 1 : 0;

            LaundryStatus::create($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.status_added_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $status = LaundryStatus::where('business_id', $business_id)->findOrFail($id);

        return view('laundry::status.edit', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'color', 'sort_order', 'is_completed_status']);
            $input['is_completed_status'] = !empty($input['is_completed_status']) ? 1 : 0;

            $status = LaundryStatus::where('business_id', $business_id)->findOrFail($id);
            $status->update($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.status_updated_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            LaundryStatus::where('business_id', $business_id)->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __('laundry::lang.status_deleted_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }
}
