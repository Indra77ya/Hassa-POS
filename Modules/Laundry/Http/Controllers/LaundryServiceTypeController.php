<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryServiceType;
use Yajra\DataTables\Facades\DataTables;

class LaundryServiceTypeController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $service_types = LaundryServiceType::where('business_id', $business_id);

            return DataTables::of($service_types)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button> ';
                    $html .= '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_service_type_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('laundry::service_type.index');
    }

    public function create()
    {
        return view('laundry::service_type.create');
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'completion_hours', 'description']);
            $input['business_id'] = $business_id;

            LaundryServiceType::create($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.service_type_added_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $service_type = LaundryServiceType::where('business_id', $business_id)->findOrFail($id);

        return view('laundry::service_type.edit', compact('service_type'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'completion_hours', 'description']);
            $service_type = LaundryServiceType::where('business_id', $business_id)->findOrFail($id);
            $service_type->update($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.service_type_updated_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            LaundryServiceType::where('business_id', $business_id)->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __('laundry::lang.service_type_deleted_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }
}
