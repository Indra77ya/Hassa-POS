<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryOrderSheet;

class PublicStatusController extends Controller
{
    public function index($order_no = null)
    {
        $order_sheet = null;
        if (!empty($order_no)) {
            $order_sheet = LaundryOrderSheet::with(['customer', 'status', 'serviceType', 'itemType', 'processLogs.process', 'processLogs.staff'])
                ->where('order_no', $order_no)
                ->first();
        }

        return view('laundry::public_status.index', compact('order_sheet', 'order_no'));
    }

    public function search(Request $request)
    {
        $search = trim($request->input('search_key'));

        $order_sheet = LaundryOrderSheet::with(['customer', 'status', 'serviceType', 'itemType', 'processLogs.process', 'processLogs.staff'])
            ->where(function ($q) use ($search) {
                $q->where('order_no', $search)
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('mobile', $search)->orWhere('contact_id', $search);
                  });
            })
            ->latest()
            ->first();

        return view('laundry::public_status.index', compact('order_sheet', 'search'));
    }
}
