<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryProcess;
use Yajra\DataTables\Facades\DataTables;

class LaundryProcessController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $processes = LaundryProcess::where('business_id', $business_id);

            return DataTables::of($processes)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary edit_process_button"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button> ';
                    $html .= '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_process_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('laundry::process.index');
    }

    public function create()
    {
        return view('laundry::process.create');
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'points', 'sort_order', 'description']);
            $input['business_id'] = $business_id;

            LaundryProcess::create($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.process_added_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $process = LaundryProcess::where('business_id', $business_id)->findOrFail($id);

        return view('laundry::process.edit', compact('process'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'points', 'sort_order', 'description']);
            $process = LaundryProcess::where('business_id', $business_id)->findOrFail($id);
            $process->update($input);

            $output = ['success' => true, 'msg' => __('laundry::lang.process_updated_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            LaundryProcess::where('business_id', $business_id)->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __('laundry::lang.process_deleted_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }
}
