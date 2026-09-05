<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryOrderSheet;
use Modules\Laundry\Entities\LaundryStatus;
use Modules\Laundry\Entities\LaundryProcess;

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
}
