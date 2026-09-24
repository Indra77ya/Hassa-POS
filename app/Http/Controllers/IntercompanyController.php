<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessIntercompanyLink;
use App\Contact;
use Illuminate\Http\Request;

class IntercompanyController extends Controller
{
    /**
     * Display a listing of inter-company links for the active business.
     */
    public function index(Request $request)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $links = BusinessIntercompanyLink::where('business_id', $business_id)
                ->with(['contact', 'target_business'])
                ->get();

            return datatables()->of($links)
                ->addColumn('action', function ($row) {
                    return '<button data-href="' . action([\App\Http\Controllers\IntercompanyController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_intercompany_link_btn"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                })
                ->editColumn('contact_name', function ($row) {
                    return $row->contact ? $row->contact->name . ' (' . ($row->contact->supplier_business_name ?? $row->contact->type) . ')' : '';
                })
                ->editColumn('target_business_name', function ($row) {
                    return $row->target_business ? $row->target_business->name : '';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $contacts = Contact::contactDropdown($business_id, false, false);
        $businesses = Business::where('id', '!=', $business_id)->pluck('name', 'id');

        return view('intercompany.index', compact('contacts', 'businesses'));
    }

    /**
     * Store a new inter-company link.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'contact_id' => 'required|integer',
                'target_business_id' => 'required|integer',
            ]);

            $business_id = $request->session()->get('user.business_id');

            BusinessIntercompanyLink::updateOrCreate(
                [
                    'business_id' => $business_id,
                    'contact_id' => $request->input('contact_id'),
                ],
                [
                    'target_business_id' => $request->input('target_business_id'),
                ]
            );

            $output = [
                'success' => true,
                'msg' => __('Inter-Company link updated successfully.'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Delete an inter-company link.
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            BusinessIntercompanyLink::where('business_id', $business_id)->where('id', $id)->delete();

            $output = [
                'success' => true,
                'msg' => __('Inter-Company link deleted successfully.'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
