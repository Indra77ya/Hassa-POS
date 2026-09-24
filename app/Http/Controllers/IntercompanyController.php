<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessIntercompanyLink;
use App\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IntercompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('superadmin');
    }

    /**
     * Display a listing of intercompany links.
     */
    public function index()
    {
        if (request()->ajax()) {
            $links = BusinessIntercompanyLink::with(['business', 'linkedBusiness', 'contact', 'linkedContact'])
                ->select('business_intercompany_links.*');

            return Datatables::of($links)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' . action([\App\Http\Controllers\IntercompanyController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_intercompany_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $businesses = Business::where('is_active', 1)->pluck('name', 'id');

        return view('intercompany.index', compact('businesses'));
    }

    /**
     * Get contacts for a given business for selection dropdown.
     */
    public function getContacts($business_id)
    {
        $contacts = Contact::where('business_id', $business_id)
            ->select('id', 'name', 'type', 'contact_id')
            ->get();

        return response()->json($contacts);
    }

    /**
     * Store a new intercompany link.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|integer|exists:business,id',
            'linked_business_id' => 'required|integer|exists:business,id|different:business_id',
            'contact_id' => 'required|integer|exists:contacts,id',
            'linked_contact_id' => 'nullable|integer|exists:contacts,id',
        ]);

        try {
            // Create link from business_id -> linked_business_id
            BusinessIntercompanyLink::updateOrCreate(
                [
                    'business_id' => $request->business_id,
                    'linked_business_id' => $request->linked_business_id,
                ],
                [
                    'contact_id' => $request->contact_id,
                    'linked_contact_id' => $request->linked_contact_id,
                ]
            );

            // Create reciprocal link from linked_business_id -> business_id if linked_contact_id provided
            if ($request->filled('linked_contact_id')) {
                BusinessIntercompanyLink::updateOrCreate(
                    [
                        'business_id' => $request->linked_business_id,
                        'linked_business_id' => $request->business_id,
                    ],
                    [
                        'contact_id' => $request->linked_contact_id,
                        'linked_contact_id' => $request->contact_id,
                    ]
                );
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
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
     * Remove the specified intercompany link.
     */
    public function destroy($id)
    {
        try {
            $link = BusinessIntercompanyLink::findOrFail($id);

            // Also delete reverse reciprocal link if exists
            BusinessIntercompanyLink::where('business_id', $link->linked_business_id)
                ->where('linked_business_id', $link->business_id)
                ->delete();

            $link->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
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
