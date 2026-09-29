<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryOrderProcessLog;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Utils\Util;

class LaundryReportController extends Controller
{
    protected $commonUtil;

    public function __construct(?Util $commonUtil = null)
    {
        $this->commonUtil = $commonUtil ?? new Util();
        $this->middleware(function ($request, $next) {
            if (! (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view_staff_points'))) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }

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
                    'u.laundry_bonus_per_point as bonus_rate',
                    DB::raw('(laundry_order_process_logs.points_earned * COALESCE(u.laundry_bonus_per_point, 0)) as total_bonus'),
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
                    return $this->commonUtil->num_f($row->quantity, false, null, true) . ' ' . e($row->unit_name);
                })
                ->editColumn('process_points', function ($row) {
                    return $this->commonUtil->num_f($row->process_points);
                })
                ->editColumn('points_earned', function ($row) {
                    return '<strong>' . $this->commonUtil->num_f($row->points_earned) . '</strong>';
                })
                ->editColumn('bonus_rate', function ($row) {
                    return $this->commonUtil->num_f($row->bonus_rate, true);
                })
                ->editColumn('total_bonus', function ($row) {
                    return '<strong class="text-success">' . $this->commonUtil->num_f($row->total_bonus, true) . '</strong>';
                })
                ->rawColumns(['points_earned', 'total_bonus'])
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
                'u.laundry_bonus_per_point as bonus_rate',
                DB::raw('SUM(laundry_order_process_logs.points_earned) as total_points'),
                DB::raw('SUM(laundry_order_process_logs.points_earned * COALESCE(u.laundry_bonus_per_point, 0)) as total_bonus'),
                DB::raw('COUNT(laundry_order_process_logs.id) as total_tasks'),
            ])
            ->groupBy('u.id', 'u.first_name', 'u.last_name', 'u.laundry_bonus_per_point')
            ->get();

        return view('laundry::reports.staff_points', compact('staffs', 'staff_summary'));
    }
}
