<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryOrderSheet;
use Modules\Laundry\Entities\LaundryStatus;
use Modules\Laundry\Entities\LaundryProcess;
use Modules\Laundry\Entities\LaundryServiceType;
use Modules\Laundry\Entities\LaundryItemType;
use Modules\Laundry\Entities\LaundryOrderProcessLog;
use App\BusinessLocation;
use App\Contact;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderSheetController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $orders = LaundryOrderSheet::where('business_id', $business_id)
                ->with(['customer', 'location', 'status', 'serviceType', 'itemType']);

            if (!empty($request->location_id)) {
                $orders->where('location_id', $request->location_id);
            }
            if (!empty($request->laundry_status_id)) {
                $orders->where('laundry_status_id', $request->laundry_status_id);
            }
            if (!empty($request->laundry_service_type_id)) {
                $orders->where('laundry_service_type_id', $request->laundry_service_type_id);
            }

            return DataTables::of($orders)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') . ' <span class="caret"></span></button>';
                    $html .= '<ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'show'], [$row->id]) . '"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                    $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'getStatusModal'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-edit"></i> ' . __('laundry::lang.change_status') . '</a></li>';
                    $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'print'], [$row->id]) . '" target="_blank"><i class="fa fa-print"></i> ' . __('messages.print') . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'destroy'], [$row->id]) . '" class="delete_order_sheet_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a></li>';
                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('order_no', function ($row) {
                    return '<a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'show'], [$row->id]) . '">' . e($row->order_no) . '</a>';
                })
                ->editColumn('status', function ($row) {
                    if (!$row->status) return '-';
                    return '<span class="label" style="background-color: ' . e($row->status->color) . ';">' . e($row->status->name) . '</span>';
                })
                ->editColumn('quantity', function ($row) {
                    return number_format($row->quantity, 2) . ' ' . e($row->unit_name);
                })
                ->editColumn('received_at', function ($row) {
                    return $row->received_at ? Carbon::parse($row->received_at)->format('d/m/Y H:i') : '-';
                })
                ->editColumn('estimated_completion_at', function ($row) {
                    return $row->estimated_completion_at ? Carbon::parse($row->estimated_completion_at)->format('d/m/Y H:i') : '-';
                })
                ->rawColumns(['action', 'order_no', 'status'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $statuses = LaundryStatus::forDropdown($business_id);
        $service_types = LaundryServiceType::forDropdown($business_id);

        return view('laundry::order_sheet.index', compact('business_locations', 'statuses', 'service_types'));
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::where('business_id', $business_id)->whereIn('type', ['customer', 'both'])->pluck('name', 'id');
        $statuses = LaundryStatus::forDropdown($business_id);
        $service_types = LaundryServiceType::forDropdown($business_id);
        $item_types = LaundryItemType::forDropdown($business_id);
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $staffs = User::forDropdown($business_id, false);

        return view('laundry::order_sheet.create', compact('business_locations', 'customers', 'statuses', 'service_types', 'item_types', 'processes', 'staffs'));
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        try {
            DB::beginTransaction();

            $ref_count = LaundryOrderSheet::where('business_id', $business_id)->count() + 1;
            $order_no = 'LND-' . date('Y') . '-' . str_pad($ref_count, 4, '0', STR_PAD_LEFT);

            $received_at = $request->received_at ? Carbon::parse($request->received_at) : Carbon::now();
            $service_type = LaundryServiceType::find($request->laundry_service_type_id);
            $completion_hours = $service_type ? $service_type->completion_hours : 24;
            $estimated_completion_at = (clone $received_at)->addHours($completion_hours);

            $order_sheet = LaundryOrderSheet::create([
                'business_id' => $business_id,
                'location_id' => $request->location_id,
                'order_no' => $order_no,
                'contact_id' => $request->contact_id,
                'laundry_status_id' => $request->laundry_status_id,
                'laundry_service_type_id' => $request->laundry_service_type_id,
                'laundry_item_type_id' => $request->laundry_item_type_id,
                'quantity' => $request->quantity ?? 1,
                'unit_name' => $request->unit_name ?? 'kg',
                'delivery_type' => $request->delivery_type ?? 'self_service',
                'received_at' => $received_at,
                'estimated_completion_at' => $estimated_completion_at,
                'items_detail' => $request->items_detail,
                'notes' => $request->notes,
                'created_by' => $user_id,
            ]);

            $this->_syncProcessLogs($order_sheet, $request, $user_id);

            DB::commit();

            $output = ['success' => true, 'msg' => __('laundry::lang.order_sheet_added_success')];
            return redirect()->action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index'])->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('status', ['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)
            ->with(['customer', 'location', 'status', 'serviceType', 'itemType', 'createdBy', 'processLogs.process', 'processLogs.staff'])
            ->findOrFail($id);

        return view('laundry::order_sheet.show', compact('order_sheet'));
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->with('processLogs')->findOrFail($id);

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::where('business_id', $business_id)->whereIn('type', ['customer', 'both'])->pluck('name', 'id');
        $statuses = LaundryStatus::forDropdown($business_id);
        $service_types = LaundryServiceType::forDropdown($business_id);
        $item_types = LaundryItemType::forDropdown($business_id);
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $staffs = User::forDropdown($business_id, false);

        return view('laundry::order_sheet.edit', compact('order_sheet', 'business_locations', 'customers', 'statuses', 'service_types', 'item_types', 'processes', 'staffs'));
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        try {
            DB::beginTransaction();

            $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->findOrFail($id);

            $received_at = $request->received_at ? Carbon::parse($request->received_at) : $order_sheet->received_at;
            $service_type = LaundryServiceType::find($request->laundry_service_type_id);
            $completion_hours = $service_type ? $service_type->completion_hours : 24;
            $estimated_completion_at = (clone Carbon::parse($received_at))->addHours($completion_hours);

            $order_sheet->update([
                'location_id' => $request->location_id,
                'contact_id' => $request->contact_id,
                'laundry_status_id' => $request->laundry_status_id,
                'laundry_service_type_id' => $request->laundry_service_type_id,
                'laundry_item_type_id' => $request->laundry_item_type_id,
                'quantity' => $request->quantity ?? 1,
                'unit_name' => $request->unit_name ?? 'kg',
                'delivery_type' => $request->delivery_type ?? 'self_service',
                'received_at' => $received_at,
                'estimated_completion_at' => $estimated_completion_at,
                'items_detail' => $request->items_detail,
                'notes' => $request->notes,
            ]);

            $this->_syncProcessLogs($order_sheet, $request, $user_id);

            DB::commit();

            $output = ['success' => true, 'msg' => __('laundry::lang.order_sheet_updated_success')];
            return redirect()->action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index'])->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('status', ['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            LaundryOrderSheet::where('business_id', $business_id)->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __('laundry::lang.order_sheet_deleted_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function getStatusModal($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->with(['status', 'processLogs'])->findOrFail($id);

        $statuses = LaundryStatus::forDropdown($business_id);
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $staffs = User::forDropdown($business_id, false);

        return view('laundry::order_sheet.status_modal', compact('order_sheet', 'statuses', 'processes', 'staffs'));
    }

    public function updateStatus(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        try {
            DB::beginTransaction();

            $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->findOrFail($id);
            $order_sheet->laundry_status_id = $request->laundry_status_id;

            $status = LaundryStatus::find($request->laundry_status_id);
            if ($status && $status->is_completed_status) {
                $order_sheet->completed_at = Carbon::now();
            }

            $order_sheet->save();

            $this->_syncProcessLogs($order_sheet, $request, $user_id);

            DB::commit();

            $output = ['success' => true, 'msg' => __('laundry::lang.status_updated_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    private function _syncProcessLogs($order_sheet, Request $request, $user_id)
    {
        $process_rows = $request->process_rows;
        $kept_process_ids = [];

        if (!empty($process_rows) && is_array($process_rows)) {
            foreach ($process_rows as $row) {
                if (empty($row['process_id'])) continue;

                $process_id = $row['process_id'];
                $raw_staff_id = !empty($row['staff_id']) ? $row['staff_id'] : null;
                $staff_id = !empty($raw_staff_id) ? $raw_staff_id : null;
                $process = LaundryProcess::find($process_id);

                $status = $staff_id ? 'completed' : 'skipped';
                $points_earned = ($staff_id && $process) ? ($process->points * $order_sheet->quantity) : 0;

                $existing_log = LaundryOrderProcessLog::where('order_sheet_id', $order_sheet->id)
                    ->where('laundry_process_id', $process_id)
                    ->first();

                $completed_at = $staff_id ? ($existing_log && $existing_log->completed_at ? $existing_log->completed_at : Carbon::now()) : null;

                LaundryOrderProcessLog::updateOrCreate(
                    [
                        'order_sheet_id' => $order_sheet->id,
                        'laundry_process_id' => $process_id,
                    ],
                    [
                        'staff_id' => $staff_id,
                        'status' => $status,
                        'points_earned' => $points_earned,
                        'completed_at' => $completed_at,
                        'created_by' => $user_id,
                    ]
                );

                $kept_process_ids[] = $process_id;
            }
        } elseif (!empty($request->process_staffs) && is_array($request->process_staffs)) {
            // Fallback for legacy process_staffs
            foreach ($request->process_staffs as $process_id => $raw_staff_id) {
                $staff_id = !empty($raw_staff_id) ? $raw_staff_id : null;
                $process = LaundryProcess::find($process_id);
                $status = $staff_id ? 'completed' : 'skipped';
                $points_earned = ($staff_id && $process) ? ($process->points * $order_sheet->quantity) : 0;

                $existing_log = LaundryOrderProcessLog::where('order_sheet_id', $order_sheet->id)
                    ->where('laundry_process_id', $process_id)
                    ->first();

                $completed_at = $staff_id ? ($existing_log && $existing_log->completed_at ? $existing_log->completed_at : Carbon::now()) : null;

                LaundryOrderProcessLog::updateOrCreate(
                    [
                        'order_sheet_id' => $order_sheet->id,
                        'laundry_process_id' => $process_id,
                    ],
                    [
                        'staff_id' => $staff_id,
                        'status' => $status,
                        'points_earned' => $points_earned,
                        'completed_at' => $completed_at,
                        'created_by' => $user_id,
                    ]
                );

                $kept_process_ids[] = $process_id;
            }
        }

        // Delete logs for processes removed from the dynamic rows
        if (!empty($kept_process_ids)) {
            LaundryOrderProcessLog::where('order_sheet_id', $order_sheet->id)
                ->whereNotIn('laundry_process_id', $kept_process_ids)
                ->delete();
        }
    }

    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)
            ->with(['customer', 'location', 'status', 'serviceType', 'itemType', 'createdBy', 'processLogs.process', 'processLogs.staff'])
            ->findOrFail($id);

        return view('laundry::order_sheet.print', compact('order_sheet'));
    }
}
