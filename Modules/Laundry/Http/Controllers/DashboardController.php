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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $total_orders = LaundryOrderSheet::where('business_id', $business_id)->count();
        $pending_orders = LaundryOrderSheet::where('business_id', $business_id)
            ->whereHas('status', function ($q) {
                $q->where('is_completed_status', false);
            })->count();
        $completed_orders = LaundryOrderSheet::where('business_id', $business_id)
            ->whereHas('status', function ($q) {
                $q->where('is_completed_status', true);
            })->count();

        $statuses = LaundryStatus::where('business_id', $business_id)->orderBy('sort_order', 'asc')->get();
        $recent_orders = LaundryOrderSheet::where('business_id', $business_id)
            ->with(['customer', 'status', 'serviceType', 'itemType'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('laundry::dashboard.index', compact('total_orders', 'pending_orders', 'completed_orders', 'statuses', 'recent_orders'));
    }

    public function importDemoData(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        try {
            DB::beginTransaction();

            $location = BusinessLocation::where('business_id', $business_id)->first();
            $location_id = $location ? $location->id : 1;

            $customer = Contact::where('business_id', $business_id)->whereIn('type', ['customer', 'both'])->first();
            if (!$customer) {
                $customer = Contact::create([
                    'business_id' => $business_id,
                    'type' => 'customer',
                    'name' => 'Pelanggan Demo Laundry',
                    'mobile' => '081234567890',
                    'created_by' => $user_id,
                ]);
            }

            // 1. Seed Statuses
            $status_data = [
                ['name' => 'Diterima', 'color' => '#3c8dbc', 'sort_order' => 1, 'is_completed_status' => 0],
                ['name' => 'Sedang Pencucian', 'color' => '#00c0ef', 'sort_order' => 2, 'is_completed_status' => 0],
                ['name' => 'Sedang Penyetrikaan', 'color' => '#f39c12', 'sort_order' => 3, 'is_completed_status' => 0],
                ['name' => 'Selesai & Siap Diambil', 'color' => '#00a65a', 'sort_order' => 4, 'is_completed_status' => 1],
                ['name' => 'Sudah Diambil', 'color' => '#605ca8', 'sort_order' => 5, 'is_completed_status' => 1],
            ];

            $created_statuses = [];
            foreach ($status_data as $s) {
                $created_statuses[] = LaundryStatus::firstOrCreate(
                    ['business_id' => $business_id, 'name' => $s['name']],
                    array_merge($s, ['business_id' => $business_id])
                );
            }

            // 2. Seed Processes
            $process_data = [
                ['name' => 'Penerimaan & Sortir', 'points' => 1.0, 'sort_order' => 1],
                ['name' => 'Pencucian', 'points' => 2.5, 'sort_order' => 2],
                ['name' => 'Pengeringan', 'points' => 1.5, 'sort_order' => 3],
                ['name' => 'Penyetrikaan & Pelipatan', 'points' => 3.0, 'sort_order' => 4],
                ['name' => 'Packing & Quality Control', 'points' => 1.0, 'sort_order' => 5],
            ];

            $created_processes = [];
            $all_proc_ids = [];
            foreach ($process_data as $p) {
                $proc = LaundryProcess::firstOrCreate(
                    ['business_id' => $business_id, 'name' => $p['name']],
                    array_merge($p, ['business_id' => $business_id])
                );
                $created_processes[] = $proc;
                $all_proc_ids[] = $proc->id;
            }

            // 3. Seed Service Types
            $service_data = [
                ['name' => 'Reguler (3 Hari)', 'completion_hours' => 72],
                ['name' => 'Express (1 Hari)', 'completion_hours' => 24],
                ['name' => 'Kilat (6 Jam)', 'completion_hours' => 6],
            ];

            $created_services = [];
            foreach ($service_data as $sv) {
                $created_services[] = LaundryServiceType::firstOrCreate(
                    ['business_id' => $business_id, 'name' => $sv['name']],
                    array_merge($sv, ['business_id' => $business_id])
                );
            }

            // 4. Seed Item Types with mapped process_ids
            $item_data = [
                ['name' => 'Cuci Lipat Kiloan', 'unit_name' => 'kg', 'default_price' => 8000, 'process_ids' => $all_proc_ids],
                ['name' => 'Cuci Setrika Kiloan', 'unit_name' => 'kg', 'default_price' => 10000, 'process_ids' => $all_proc_ids],
                ['name' => 'Bed Cover Besar', 'unit_name' => 'pcs', 'default_price' => 35000, 'process_ids' => [$all_proc_ids[0], $all_proc_ids[1], $all_proc_ids[2], $all_proc_ids[4]]],
                ['name' => 'Jas / Gaun', 'unit_name' => 'pcs', 'default_price' => 25000, 'process_ids' => [$all_proc_ids[0], $all_proc_ids[3], $all_proc_ids[4]]],
                ['name' => 'Sepatu / Sneaker', 'unit_name' => 'pasang', 'default_price' => 30000, 'process_ids' => [$all_proc_ids[0], $all_proc_ids[1], $all_proc_ids[2], $all_proc_ids[4]]],
            ];

            $created_items = [];
            foreach ($item_data as $it) {
                $created_items[] = LaundryItemType::firstOrCreate(
                    ['business_id' => $business_id, 'name' => $it['name']],
                    array_merge($it, ['business_id' => $business_id])
                );
            }

            // 5. Seed 3 Sample Order Sheets
            $demo_orders = [
                ['qty' => 5.0, 'item_idx' => 0, 'service_idx' => 1, 'status_idx' => 1, 'items_detail' => '5 kg Pakaian Harian (Kemeja, Celana)'],
                ['qty' => 1.0, 'item_idx' => 2, 'service_idx' => 0, 'status_idx' => 3, 'items_detail' => '1 Pcs Bed Cover King Size'],
                ['qty' => 2.0, 'item_idx' => 4, 'service_idx' => 1, 'status_idx' => 0, 'items_detail' => '2 Pasang Sepatu Nike & Adidas'],
            ];

            foreach ($demo_orders as $idx => $ord) {
                $ref_count = LaundryOrderSheet::where('business_id', $business_id)->count() + 1;
                $order_no = 'LND-DEMO-' . str_pad($ref_count, 4, '0', STR_PAD_LEFT);

                $srv = $created_services[$ord['service_idx']];
                $itm = $created_items[$ord['item_idx']];
                $st = $created_statuses[$ord['status_idx']];

                $received_at = Carbon::now()->subHours(rand(1, 12));
                $est_completion = (clone $received_at)->addHours($srv->completion_hours);

                $order_sheet = LaundryOrderSheet::create([
                    'business_id' => $business_id,
                    'location_id' => $location_id,
                    'order_no' => $order_no,
                    'contact_id' => $customer->id,
                    'laundry_status_id' => $st->id,
                    'laundry_service_type_id' => $srv->id,
                    'laundry_item_type_id' => $itm->id,
                    'quantity' => $ord['qty'],
                    'unit_name' => $itm->unit_name,
                    'delivery_type' => 'self_service',
                    'received_at' => $received_at,
                    'estimated_completion_at' => $est_completion,
                    'items_detail' => $ord['items_detail'],
                    'notes' => 'Data Demo Laundry System',
                    'created_by' => $user_id,
                ]);

                $assigned_proc_ids = $itm->process_ids ?? $all_proc_ids;
                foreach ($created_processes as $proc) {
                    if (!in_array($proc->id, $assigned_proc_ids)) continue;

                    $is_done = ($st->is_completed_status || $proc->sort_order <= $st->sort_order);
                    LaundryOrderProcessLog::create([
                        'order_sheet_id' => $order_sheet->id,
                        'laundry_process_id' => $proc->id,
                        'staff_id' => $is_done ? $user_id : null,
                        'status' => $is_done ? 'completed' : 'pending',
                        'points_earned' => $is_done ? ($proc->points * $ord['qty']) : 0,
                        'completed_at' => $is_done ? Carbon::now() : null,
                        'created_by' => $user_id,
                    ]);
                }
            }

            DB::commit();

            $output = ['success' => true, 'msg' => 'Data demo laundry berhasil dimasukkan!'];
        } catch (\Exception $e) {
            DB::rollBack();
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return redirect()->back()->with('status', $output);
    }
}
