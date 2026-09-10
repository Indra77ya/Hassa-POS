<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Business;
use Modules\Laundry\Entities\LaundryOrderSheet;

class PublicStatusController extends Controller
{
    private function getLaundryLogo($business_id)
    {
        if (empty($business_id)) {
            return null;
        }
        $business = Business::find($business_id);
        if (!$business || empty($business->laundry_settings)) {
            return null;
        }
        $laundry_settings = json_decode($business->laundry_settings, true);
        if (!empty($laundry_settings['laundry_logo']) && file_exists(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo']))) {
            return asset('uploads/laundry_logos/' . $laundry_settings['laundry_logo']);
        }
        return null;
    }

    public function index($order_no = null)
    {
        $order_sheet = null;
        if (!empty($order_no)) {
            $order_sheet = LaundryOrderSheet::with(['customer', 'status', 'serviceType', 'itemType', 'processLogs.process', 'processLogs.staff'])
                ->where('order_no', $order_no)
                ->first();
        }

        $laundry_logo = !empty($order_sheet) ? $this->getLaundryLogo($order_sheet->business_id) : null;

        return view('laundry::public_status.index', compact('order_sheet', 'order_no', 'laundry_logo'));
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

        $laundry_logo = !empty($order_sheet) ? $this->getLaundryLogo($order_sheet->business_id) : null;

        return view('laundry::public_status.index', compact('order_sheet', 'search', 'laundry_logo'));
    }
}
