<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryOrderProcessLog;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaundryReportController extends Controller
{
    public function staffPointsReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $logs = LaundryOrderProcessLog::join('laundry_order_sheets as os', 'laundry_order_process_logs.order_sheet_id', '=', 'os.id')
                ->join('laundry_processes as lp', 'laundry_order_process_logs.laundry_process_id', '=', 'lp.id')
                ->join('users as u', 'laundry_order_process_logs.staff_id', '=', 'u.id')
                ->where('os.business_id', $business_id)
                ->whereNotNull('laundry_order_process_logs.staff_id')
                ->select([
                    'laundry_order_process_logs.id',
                    'os.order_no',
                    'lp.name as process_name',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as staff_name"),
                    'os.quantity',
                    'os.unit_name',
                    'lp.points as process_points',
                    'laundry_order_process_logs.points_earned',
                    'laundry_order_process_logs.completed_at',
                ]);

            if (!empty($request->staff_id)) {
                $logs->where('laundry_order_process_logs.staff_id', $request->staff_id);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                $logs->whereBetween('laundry_order_process_logs.completed_at', [$start, $end]);
            }

            return DataTables::of($logs)
                ->editColumn('completed_at', function ($row) {
                    return $row->completed_at ? Carbon::parse($row->completed_at)->format('d/m/Y H:i') : '-';
                })
                ->editColumn('quantity', function ($row) {
                    return number_format($row->quantity, 2) . ' ' . e($row->unit_name);
                })
                ->editColumn('process_points', function ($row) {
                    return number_format($row->process_points, 2);
                })
                ->editColumn('points_earned', function ($row) {
                    return '<strong>' . number_format($row->points_earned, 2) . '</strong>';
                })
                ->rawColumns(['points_earned'])
                ->make(true);
        }

        $staffs = User::forDropdown($business_id, false);

        // Calculate summary total points per staff
        $staff_summary = LaundryOrderProcessLog::join('laundry_order_sheets as os', 'laundry_order_process_logs.order_sheet_id', '=', 'os.id')
            ->join('users as u', 'laundry_order_process_logs.staff_id', '=', 'u.id')
            ->where('os.business_id', $business_id)
            ->whereNotNull('laundry_order_process_logs.staff_id')
            ->select([
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as staff_name"),
                DB::raw('SUM(laundry_order_process_logs.points_earned) as total_points'),
                DB::raw('COUNT(laundry_order_process_logs.id) as total_tasks'),
            ])
            ->groupBy('u.id', 'u.first_name', 'u.last_name')
            ->get();

        return view('laundry::reports.staff_points', compact('staffs', 'staff_summary'));
    }
}
