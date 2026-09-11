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
use App\Utils\Util;

class OrderSheetController extends Controller
{
    protected $commonUtil;

    public function __construct(?Util $commonUtil = null)
    {
        $this->commonUtil = $commonUtil ?? new Util();
    }

    public function index(Request $request)
    {
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.update') || auth()->user()->can('laundry.delete') || auth()->user()->can('laundry.update_status') || auth()->user()->can('laundry.log_process'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $orders = LaundryOrderSheet::where('laundry_order_sheets.business_id', $business_id)
                ->select('laundry_order_sheets.*')
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
                        $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.add_payment') . '</a></li>';
                    }
                    if ($row->total_paid > 0) {
                        $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'viewPayments'], [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.view_payments') . '</a></li>';
                    }

                    $wa_action_label = !empty($row->whatsapp_sent_at) ? 'Kirim Ulang WA' : 'Kirim WhatsApp';
                    $html .= '<li><a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'print'], [$row->id]) . '" target="_blank"><i class="fa fa-print"></i> ' . __('messages.print') . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'getWhatsappLink'], [$row->id]) . '" data-id="' . $row->id . '" class="send_laundry_whatsapp"><i class="fab fa-whatsapp fa-fw text-success"></i> ' . e($wa_action_label) . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'destroy'], [$row->id]) . '" class="delete_order_sheet_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a></li>';
                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('order_no', function ($row) {
                    $html = '<a href="' . action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'show'], [$row->id]) . '">' . e($row->order_no) . '</a>';
                    if (!empty($row->whatsapp_sent_at)) {
                        $formatted_time = Carbon::parse($row->whatsapp_sent_at)->format('d/m/Y H:i');
                        $html .= ' <span class="label bg-green" title="WA Terkirim: ' . e($formatted_time) . '"><i class="fab fa-whatsapp"></i> WA Terkirim</span>';
                    }
                    return $html;
                })
                ->addColumn('wa_status', function ($row) {
                    if (!empty($row->whatsapp_sent_at)) {
                        $formatted_time = Carbon::parse($row->whatsapp_sent_at)->format('d/m/Y H:i');
                        return '<span class="label bg-green" title="Terkirim pada ' . e($formatted_time) . '"><i class="fab fa-whatsapp"></i> Terkirim</span><br><small class="text-muted">' . e($formatted_time) . '</small>';
                    } else {
                        return '<span class="label bg-gray"><i class="fab fa-whatsapp"></i> Belum Dikirim</span>';
                    }
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
                    $html .= '<br><small>' . __('sale.total') . ': ' . $this->commonUtil->num_f($total) . '</small>';
                    if ($status != 'paid') {
                        $html .= '<br><small>' . __('purchase.payment_due') . ': ' . $this->commonUtil->num_f($due) . '</small>';
                    }
                    return $html;
                })
                ->editColumn('quantity', function ($row) {
                    return $this->commonUtil->num_f($row->quantity, false, null, true) . ' ' . e($row->unit_name);
                })
                ->editColumn('received_at', function ($row) {
                    return $row->received_at ? Carbon::parse($row->received_at)->format('d/m/Y H:i') : '-';
                })
                ->editColumn('estimated_completion_at', function ($row) {
                    return $row->estimated_completion_at ? Carbon::parse($row->estimated_completion_at)->format('d/m/Y H:i') : '-';
                })
                ->rawColumns(['action', 'order_no', 'status', 'payment_status', 'wa_status'])
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
                $default_customer = Contact::where('business_id', $business_id)
                    ->where('is_default', 1)
                    ->first();
                $default_customer_id = $default_customer ? $default_customer->id : null;

                if ($contact_id != $default_customer_id) {
                    $query->where('contact_id', $contact_id);
                }
            }

            $order_sheets_list = $query->with(['itemType', 'transactions'])->get();
            $order_sheets = [];
            foreach ($order_sheets_list as $os) {
                $status_label = '';
                if ($os->payment_status == 'partial') {
                    $due = $os->total_amount - $os->total_paid;
                    if ($due < 0) $due = 0;
                    $status_label = ' (' . __('lang_v1.partial') . ' - ' . __('purchase.payment_due') . ': ' . $this->commonUtil->num_f($due) . ')';
                } elseif ($os->payment_status == 'due') {
                    $status_label = ' (' . __('lang_v1.due') . ')';
                } elseif ($os->payment_status == 'paid') {
                    $status_label = ' (' . __('lang_v1.paid') . ')';
                }
                $order_sheets[$os->id] = $os->order_no . $status_label;
            }

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
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.create'))) {
            abort(403, 'Unauthorized action.');
        }

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
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.create'))) {
            abort(403, 'Unauthorized action.');
        }

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
                $status_label = '';
                if ($order_sheet->payment_status == 'partial') {
                    $due = max(0, $order_sheet->total_amount - $order_sheet->total_paid);
                    $status_label = ' (' . __('lang_v1.partial') . ' - ' . __('purchase.payment_due') . ': ' . $this->commonUtil->num_f($due) . ')';
                } elseif ($order_sheet->payment_status == 'due') {
                    $status_label = ' (' . __('lang_v1.due') . ')';
                } elseif ($order_sheet->payment_status == 'paid') {
                    $status_label = ' (' . __('lang_v1.paid') . ')';
                }

                return response()->json([
                    'success' => true,
                    'msg' => __('laundry::lang.order_sheet_added_success'),
                    'data' => [
                        'id' => $order_sheet->id,
                        'order_no' => $order_sheet->order_no,
                        'display_order_no' => $order_sheet->order_no . $status_label,
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
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.update'))) {
            abort(403, 'Unauthorized action.');
        }

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
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.update'))) {
            abort(403, 'Unauthorized action.');
        }

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
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.delete'))) {
            abort(403, 'Unauthorized action.');
        }

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
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.update_status') || auth()->user()->can('laundry.update') || auth()->user()->can('laundry.log_process') || auth()->user()->can('laundry.create'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)->with(['status', 'processLogs'])->findOrFail($id);

        $statuses = LaundryStatus::forDropdown($business_id);
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $staffs = User::forDropdown($business_id, false);

        return view('laundry::order_sheet.status_modal', compact('order_sheet', 'statuses', 'processes', 'staffs'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.update_status') || auth()->user()->can('laundry.update') || auth()->user()->can('laundry.log_process') || auth()->user()->can('laundry.create'))) {
            abort(403, 'Unauthorized action.');
        }

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

        $business = \App\Business::find($business_id);
        $laundry_logo = null;
        if ($business && !empty($business->laundry_settings)) {
            $laundry_settings = json_decode($business->laundry_settings, true);
            if (!empty($laundry_settings['laundry_logo']) && file_exists(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo']))) {
                $laundry_logo = asset('uploads/laundry_logos/' . $laundry_settings['laundry_logo']);
            }
        }

        return view('laundry::order_sheet.print', compact('order_sheet', 'laundry_logo'));
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

            $total_amount = $order_sheet->total_amount;
            $total_paid = $order_sheet->total_paid;
            $payment_status = $order_sheet->payment_status;
            $due_amount = max(0, $total_amount - $total_paid);

            return response()->json([
                'success' => true,
                'order_sheet_id' => $order_sheet->id,
                'contact_id' => $order_sheet->contact_id,
                'customer_name' => optional($order_sheet->customer)->name,
                'quantity' => $order_sheet->quantity,
                'variation_id' => $variation_id,
                'item_type_name' => $item_type_name,
                'payment_status' => $payment_status,
                'total_amount' => $total_amount,
                'total_paid' => $total_paid,
                'due_amount' => $due_amount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getPosDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    public function getWhatsappLink($id)
    {
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.update'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)
            ->with(['customer', 'location', 'status', 'serviceType', 'itemType'])
            ->findOrFail($id);

        $customer = $order_sheet->customer;
        $mobile = $customer ? trim($customer->mobile) : '';

        if (empty($mobile)) {
            return response()->json([
                'success' => true,
                'has_mobile' => false,
                'contact_id' => $order_sheet->contact_id,
                'customer_name' => optional($customer)->name ?? '-',
                'order_no' => $order_sheet->order_no,
                'mobile' => $mobile,
                'msg' => __('Pelanggan belum memiliki nomor telepon/WhatsApp.'),
            ]);
        }

        $order_sheet->whatsapp_sent_at = Carbon::now();
        $order_sheet->save();

        $text = $this->_buildWhatsappText($order_sheet);
        $whatsapp_link = $this->commonUtil->getWhatsappNotificationLink([
            'mobile_number' => $mobile,
            'whatsapp_text' => $text,
        ]);

        return response()->json([
            'success' => true,
            'has_mobile' => true,
            'mobile' => $mobile,
            'whatsapp_link' => $whatsapp_link,
            'whatsapp_sent_at' => Carbon::parse($order_sheet->whatsapp_sent_at)->format('d/m/Y H:i'),
        ]);
    }

    public function sendWhatsappMobile(Request $request, $id)
    {
        if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.update'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'mobile' => 'required|string',
        ]);

        $business_id = request()->session()->get('user.business_id');
        $order_sheet = LaundryOrderSheet::where('business_id', $business_id)
            ->with(['customer', 'location', 'status', 'serviceType', 'itemType'])
            ->findOrFail($id);

        $mobile = trim($request->mobile);

        if ($request->get('save_to_contact', 0) == 1 && !empty($order_sheet->contact_id)) {
            Contact::where('business_id', $business_id)
                ->where('id', $order_sheet->contact_id)
                ->update(['mobile' => $mobile]);
        }

        $order_sheet->whatsapp_sent_at = Carbon::now();
        $order_sheet->save();

        $text = $this->_buildWhatsappText($order_sheet);
        $whatsapp_link = $this->commonUtil->getWhatsappNotificationLink([
            'mobile_number' => $mobile,
            'whatsapp_text' => $text,
        ]);

        return response()->json([
            'success' => true,
            'has_mobile' => true,
            'whatsapp_link' => $whatsapp_link,
            'whatsapp_sent_at' => Carbon::parse($order_sheet->whatsapp_sent_at)->format('d/m/Y H:i'),
            'msg' => __('Link WhatsApp berhasil dibuat.'),
        ]);
    }

    private function _buildWhatsappText($order_sheet)
    {
        $business = \App\Business::find($order_sheet->business_id);
        $business_name = $business ? $business->name : '';

        $customer_name = optional($order_sheet->customer)->name ?? '-';
        $service_name = optional($order_sheet->serviceType)->name ?? '-';
        $item_name = optional($order_sheet->itemType)->name ?? '-';
        $status_name = optional($order_sheet->status)->name ?? '-';

        $quantity = $this->commonUtil->num_f($order_sheet->quantity, false, null, true) . ' ' . $order_sheet->unit_name;

        $total = $order_sheet->total_amount;
        $paid = $order_sheet->total_paid;
        $due = max(0, $total - $paid);

        $payment_status_label = 'Belum Dibayar';
        if ($order_sheet->payment_status == 'paid') {
            $payment_status_label = 'Lunas';
        } elseif ($order_sheet->payment_status == 'partial') {
            $payment_status_label = 'Sebagian';
        }

        $tracking_url = url('/laundry/status/' . $order_sheet->order_no);

        $text = "*{$business_name}*\n";
        $text .= "*NOTA LAUNDRY*\n";
        $text .= "----------------------------------------\n";
        $text .= "No. Order : {$order_sheet->order_no}\n";
        $text .= "Pelanggan : {$customer_name}\n";
        $text .= "Tanggal   : " . ($order_sheet->received_at ? Carbon::parse($order_sheet->received_at)->format('d/m/Y H:i') : '-') . "\n";
        $text .= "Estimasi  : " . ($order_sheet->estimated_completion_at ? Carbon::parse($order_sheet->estimated_completion_at)->format('d/m/Y H:i') : '-') . "\n";
        $text .= "----------------------------------------\n";
        $text .= "Layanan  : {$service_name}\n";
        $text .= "Jenis    : {$item_name}\n";
        $text .= "Jumlah   : {$quantity}\n";
        if (!empty($order_sheet->items_detail)) {
            $text .= "Rincian  : {$order_sheet->items_detail}\n";
        }
        $text .= "Status   : {$status_name}\n";
        $text .= "----------------------------------------\n";
        $text .= "Total Tagihan : Rp " . $this->commonUtil->num_f($total) . "\n";
        $text .= "Sudah Dibayar : Rp " . $this->commonUtil->num_f($paid) . "\n";
        $text .= "Sisa Tagihan  : Rp " . $this->commonUtil->num_f($due) . "\n";
        $text .= "Status Bayar  : {$payment_status_label}\n";
        $text .= "----------------------------------------\n";
        $text .= "Lacak Status Laundry Anda:\n";
        $text .= "{$tracking_url}\n\n";
        $text .= "Terima kasih telah menggunakan jasa laundry kami!";

        return $text;
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
