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
            $orders = LaundryOrderSheet::where('laundry_order_sheets.business_id', $business_id)
                ->with(['customer', 'location', 'status', 'serviceType', 'itemType', 'transactions']);

            if (!empty($request->location_id)) {
                $orders->where('laundry_order_sheets.location_id', $request->location_id);
            }
            if (!empty($request->laundry_status_id)) {
                $orders->where('laundry_order_sheets.laundry_status_id', $request->laundry_status_id);
            }
            if (!empty($request->laundry_service_type_id)) {
                $orders->where('laundry_order_sheets.laundry_service_type_id', $request->laundry_service_type_id);
            }
            if (!empty($request->payment_status)) {
                if ($request->payment_status == 'paid') {
                    $orders->where(function($q) {
                        $q->whereRaw("(SELECT COALESCE(SUM(tp.amount), 0) FROM transaction_payments tp JOIN transactions t ON t.id = tp.transaction_id WHERE t.laundry_order_sheet_id = laundry_order_sheets.id AND tp.is_return = 0) >= (laundry_order_sheets.quantity * COALESCE((SELECT it.default_price FROM laundry_item_types it WHERE it.id = laundry_order_sheets.laundry_item_type_id), 0))");
                    });
                } elseif ($request->payment_status == 'due') {
                    $orders->where(function($q) {
                        $q->whereRaw("(SELECT COALESCE(SUM(tp.amount), 0) FROM transaction_payments tp JOIN transactions t ON t.id = tp.transaction_id WHERE t.laundry_order_sheet_id = laundry_order_sheets.id AND tp.is_return = 0) = 0")
                          ->whereRaw("(laundry_order_sheets.quantity * COALESCE((SELECT it.default_price FROM laundry_item_types it WHERE it.id = laundry_order_sheets.laundry_item_type_id), 0)) > 0");
                    });
                } elseif ($request->payment_status == 'partial') {
                    $orders->where(function($q) {
                        $q->whereRaw("(SELECT COALESCE(SUM(tp.amount), 0) FROM transaction_payments tp JOIN transactions t ON t.id = tp.transaction_id WHERE t.laundry_order_sheet_id = laundry_order_sheets.id AND tp.is_return = 0) > 0")
                          ->whereRaw("(SELECT COALESCE(SUM(tp.amount), 0) FROM transaction_payments tp JOIN transactions t ON t.id = tp.transaction_id WHERE t.laundry_order_sheet_id = laundry_order_sheets.id AND tp.is_return = 0) < (laundry_order_sheets.quantity * COALESCE((SELECT it.default_price FROM laundry_item_types it WHERE it.id = laundry_order_sheets.laundry_item_type_id), 0))");
                    });
                }
            }

            return DataTables::of($orders)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') . ' <span class="caret"></span></button>';
                    $html .= '<ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'show'], [$row->id]) . '"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                    $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'getStatusModal'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-edit"></i> ' . __('laundry::lang.change_status') . '</a></li>';

                    if ($row->payment_status != 'paid') {
                        $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'addPayment'], [$row->id]) . '" class="btn-modal" data-container=".payment_modal"><i class="fa fa-money"></i> ' . __('purchase.add_payment') . '</a></li>';
                    }
                    if ($row->total_paid > 0) {
                        $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'viewPayments'], [$row->id]) . '" class="btn-modal" data-container=".payment_modal"><i class="fa fa-money"></i> ' . __('purchase.view_payments') . '</a></li>';
                    }

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
                ->addColumn('payment_status', function ($row) {
                    $status = $row->payment_status;
                    $total = $row->total_amount;
                    $paid = $row->total_paid;
                    $due = $total - $paid;
                    if ($due < 0) $due = 0;

                    $bg_class = 'bg-red';
                    $text = __('lang_v1.due');
                    if ($status == 'paid') {
                        $bg_class = 'bg-green';
                        $text = __('lang_v1.paid');
                    } elseif ($status == 'partial') {
                        $bg_class = 'bg-yellow';
                        $text = __('lang_v1.partial');
                    }

                    $html = '<span class="label ' . $bg_class . '">' . e($text) . '</span>';
                    $html .= '<br><small>' . __('sale.total') . ': ' . number_format($total, 2) . '</small>';
                    if ($status != 'paid') {
                        $html .= '<br><small>' . __('purchase.payment_due') . ': ' . number_format($due, 2) . '</small>';
                    }
                    return $html;
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
                ->rawColumns(['action', 'order_no', 'status', 'payment_status'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $statuses = LaundryStatus::forDropdown($business_id);
        $service_types = LaundryServiceType::forDropdown($business_id);
        $payment_statuses = [
            'due' => __('lang_v1.due'),
            'partial' => __('lang_v1.partial'),
            'paid' => __('lang_v1.paid'),
        ];

        return view('laundry::order_sheet.index', compact('business_locations', 'statuses', 'service_types', 'payment_statuses'));
    }

    public function getOrderSheets(Request $request)
    {
        try {
            $business_id = session()->get('user.business_id') ?? request()->session()->get('user.business_id');
            $query = LaundryOrderSheet::where('business_id', $business_id);

            $contact_id = $request->get('contact_id');
            if (!empty($contact_id)) {
                $query->where('contact_id', $contact_id);
            }

            // Exclude fully paid order sheets from POS dropdown selection
            $query->whereRaw("(SELECT COALESCE(SUM(tp.amount), 0) FROM transaction_payments tp JOIN transactions t ON t.id = tp.transaction_id WHERE t.laundry_order_sheet_id = laundry_order_sheets.id AND tp.is_return = 0) < (laundry_order_sheets.quantity * COALESCE((SELECT it.default_price FROM laundry_item_types it WHERE it.id = laundry_order_sheets.laundry_item_type_id), 0))");

            $order_sheets = $query->pluck('order_no', 'id');

            return response()->json([
                'success' => true,
                'order_sheets' => $order_sheets,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getOrderSheets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPayment($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->findOrFail($id);
        $transaction = $this->_getOrCreateTransaction($order_sheet);

        $transactionPaymentController = app(\App\Http\Controllers\TransactionPaymentController::class);
        return $transactionPaymentController->addPayment($transaction->id);
    }

    public function viewPayments($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->findOrFail($id);
        $transaction = $this->_getOrCreateTransaction($order_sheet);

        $transactionPaymentController = app(\App\Http\Controllers\TransactionPaymentController::class);
        return $transactionPaymentController->show($transaction->id);
    }

    private function _getOrCreateTransaction($order_sheet)
    {
        $transaction = \App\Transaction::where('business_id', $order_sheet->business_id)
            ->where('laundry_order_sheet_id', $order_sheet->id)
            ->where('type', 'sell')
            ->first();

        if (!$transaction) {
            $ref_count = \App\Transaction::where('business_id', $order_sheet->business_id)->where('type', 'sell')->count() + 1;
            $invoice_no = 'LND-INV-' . str_pad($ref_count, 4, '0', STR_PAD_LEFT);

            $transaction = \App\Transaction::create([
                'business_id' => $order_sheet->business_id,
                'location_id' => $order_sheet->location_id,
                'type' => 'sell',
                'status' => 'final',
                'payment_status' => 'due',
                'contact_id' => $order_sheet->contact_id,
                'laundry_order_sheet_id' => $order_sheet->id,
                'invoice_no' => $invoice_no,
                'transaction_date' => $order_sheet->received_at ?? \Carbon\Carbon::now(),
                'total_before_tax' => $order_sheet->total_amount,
                'final_total' => $order_sheet->total_amount,
                'created_by' => auth()->user()->id ?? $order_sheet->created_by,
                'sub_type' => 'laundry',
            ]);
        } else {
            if ($transaction->final_total != $order_sheet->total_amount) {
                $transaction->total_before_tax = $order_sheet->total_amount;
                $transaction->final_total = $order_sheet->total_amount;
                $transaction->save();
            }
        }

        return $transaction;
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

        $quick_add = request()->get('quick_add', false);
        $contact_id = request()->get('contact_id', null);

        if ($quick_add || request()->ajax()) {
            return view('laundry::order_sheet.quick_add_modal', compact('business_locations', 'customers', 'statuses', 'service_types', 'item_types', 'processes', 'staffs', 'quick_add', 'contact_id'));
        }

        return view('laundry::order_sheet.create', compact('business_locations', 'customers', 'statuses', 'service_types', 'item_types', 'processes', 'staffs', 'contact_id'));
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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'msg' => __('laundry::lang.order_sheet_added_success'),
                    'data' => [
                        'id' => $order_sheet->id,
                        'order_no' => $order_sheet->order_no,
                        'contact_id' => $order_sheet->contact_id,
                        'customer_name' => optional($order_sheet->customer)->name,
                    ],
                ]);
            }

            $output = ['success' => true, 'msg' => __('laundry::lang.order_sheet_added_success')];
            return redirect()->action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index'])->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'msg' => $e->getMessage()]);
            }
            return redirect()->back()->with('status', ['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)
            ->with(['customer', 'location', 'status', 'serviceType', 'itemType', 'createdBy', 'processLogs.process', 'processLogs.staff'])
            ->findOrFail($id);

        if (request()->ajax()) {
            return view('laundry::order_sheet.show_modal', compact('order_sheet'));
        }

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

        if (request()->ajax()) {
            return view('laundry::order_sheet.edit_modal', compact('order_sheet', 'business_locations', 'customers', 'statuses', 'service_types', 'item_types', 'processes', 'staffs'));
        }

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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'msg' => __('laundry::lang.order_sheet_updated_success'),
                    'data' => [
                        'id' => $order_sheet->id,
                        'order_no' => $order_sheet->order_no,
                        'contact_id' => $order_sheet->contact_id,
                        'customer_name' => optional($order_sheet->customer)->name,
                    ],
                ]);
            }

            $output = ['success' => true, 'msg' => __('laundry::lang.order_sheet_updated_success')];
            return redirect()->action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index'])->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'msg' => $e->getMessage()]);
            }
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

                $status = !empty($row['status']) ? $row['status'] : ($staff_id ? 'completed' : 'pending');
                $is_completed = ($status === 'completed');
                $points_earned = ($is_completed && $process) ? ($process->points * $order_sheet->quantity) : 0;

                $existing_log = LaundryOrderProcessLog::where('order_sheet_id', $order_sheet->id)
                    ->where('laundry_process_id', $process_id)
                    ->first();

                $completed_at = $is_completed ? ($existing_log && $existing_log->completed_at ? $existing_log->completed_at : Carbon::now()) : null;

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
                $status = $staff_id ? 'completed' : 'pending';
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

    public function getPosDetails($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $order_sheet = LaundryOrderSheet::where('business_id', $business_id)
                ->with(['customer', 'itemType'])
                ->findOrFail($id);

            $variation_id = null;
            $item_type = $order_sheet->itemType;
            $item_type_name = optional($item_type)->name;

            if ($item_type && \Illuminate\Support\Facades\Schema::hasColumn('laundry_item_types', 'variation_id') && !empty($item_type->variation_id)) {
                $variation_id = $item_type->variation_id;
            }

            if (!$variation_id && !empty($item_type_name)) {
                // 1. Check exact match by product name
                $variation = \App\Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                    ->where('p.business_id', $business_id)
                    ->where('p.name', $item_type_name)
                    ->select('variations.id')
                    ->first();

                if ($variation) {
                    $variation_id = $variation->id;
                } else {
                    // 2. Auto-create exact service product for this Laundry Item Type
                    $variation_id = $this->_createProductForItemType($business_id, $item_type);
                }

                if ($variation_id && $item_type && \Illuminate\Support\Facades\Schema::hasColumn('laundry_item_types', 'variation_id')) {
                    $item_type->variation_id = $variation_id;
                    $item_type->save();
                }
            }

            return response()->json([
                'success' => true,
                'order_sheet_id' => $order_sheet->id,
                'contact_id' => $order_sheet->contact_id,
                'customer_name' => optional($order_sheet->customer)->name,
                'quantity' => $order_sheet->quantity,
                'variation_id' => $variation_id,
                'item_type_name' => $item_type_name,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getPosDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    private function _createProductForItemType($business_id, $item_type)
    {
        if (empty($item_type)) return null;

        try {
            $user_id = request()->session()->get('user.id') ?? 1;
            $unit_name = $item_type->unit_name ?? 'kg';
            $unit = \App\Unit::where('business_id', $business_id)->where('actual_name', 'LIKE', '%' . $unit_name . '%')->first();
            if (!$unit) {
                $unit = \App\Unit::where('business_id', $business_id)->first();
            }

            $product_data = [
                'name' => $item_type->name,
                'business_id' => $business_id,
                'unit_id' => $unit ? $unit->id : 1,
                'type' => 'single',
                'enable_stock' => 0,
                'alert_quantity' => 0,
                'created_by' => $user_id,
                'sku' => 'LND-ITM-' . $item_type->id . '-' . time(),
            ];

            $product = \App\Product::create($product_data);

            // Sync product locations
            $locations = \App\BusinessLocation::forDropdown($business_id);
            if (!empty($locations)) {
                $product->product_locations()->sync(array_keys($locations->toArray()));
            }

            // Create single product variation using ProductUtil
            $productUtil = new \App\Utils\ProductUtil();
            $price = $item_type->default_price ?? 0;
            $variation = $productUtil->createSingleProductVariation(
                $product,
                $product->sku,
                $price,
                $price,
                0,
                $price,
                $price
            );

            return $variation ? $variation->id : null;
        } catch (\Exception $e) {
            \Log::error('Error creating product for laundry item type: ' . $e->getMessage());
            return null;
        }
    }
}
