<?php

namespace App\Http\Controllers;

use App\ProductSerialNumber;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductSerialNumberController extends Controller
{
    protected $productUtil;

    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    /**
     * Display a listing of product serial numbers.
     */
    public function index()
    {
        if (! auth()->user()->can('product.view') && ! auth()->user()->can('repair.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session() ? request()->session()->get('user.business_id') : auth()->user()->business_id;

        if (request()->ajax()) {
            $query = ProductSerialNumber::where('product_serial_numbers.business_id', $business_id)
                ->leftJoin('products', 'product_serial_numbers.product_id', '=', 'products.id')
                ->leftJoin('variations', 'product_serial_numbers.variation_id', '=', 'variations.id')
                ->leftJoin('business_locations', 'product_serial_numbers.location_id', '=', 'business_locations.id')
                ->select([
                    'product_serial_numbers.id',
                    'product_serial_numbers.serial_number',
                    'product_serial_numbers.purchase_price',
                    'product_serial_numbers.selling_price',
                    'product_serial_numbers.status',
                    'product_serial_numbers.notes',
                    'products.name as product_name',
                    'variations.sub_sku as sub_sku',
                    'business_locations.name as location_name',
                    'product_serial_numbers.created_at'
                ]);

            if (! empty(request()->input('product_id'))) {
                $query->where('product_serial_numbers.product_id', request()->input('product_id'));
            }

            if (! empty(request()->input('location_id'))) {
                $query->where('product_serial_numbers.location_id', request()->input('location_id'));
            }

            if (! empty(request()->input('status'))) {
                $query->where('product_serial_numbers.status', request()->input('status'));
            }

            return DataTables::of($query)
                ->editColumn('purchase_price', function ($row) {
                    return $row->purchase_price !== null ? $this->productUtil->num_f($row->purchase_price, true) : '-';
                })
                ->editColumn('selling_price', function ($row) {
                    return $row->selling_price !== null ? $this->productUtil->num_f($row->selling_price, true) : '-';
                })
                ->editColumn('status', function ($row) {
                    $badges = [
                        'in_stock' => '<span class="label bg-green">Tersedia (In Stock)</span>',
                        'sold' => '<span class="label bg-blue">Terjual (Sold)</span>',
                        'used_in_repair' => '<span class="label bg-yellow">Digunakan di Repair</span>',
                        'returned' => '<span class="label bg-red">Retur</span>'
                    ];

                    return $badges[$row->status] ?? $row->status;
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' . __('messages.action') . ' <span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    if (auth()->user()->can('product.update')) {
                        $html .= '<li><a href="#" data-href="' . action([\App\Http\Controllers\ProductSerialNumberController::class, 'edit'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('product_serial_number.index');
    }

    /**
     * Show form to edit serial number.
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session() ? request()->session()->get('user.business_id') : auth()->user()->business_id;
        $serial = ProductSerialNumber::where('business_id', $business_id)->findOrFail($id);

        return view('product_serial_number.edit', compact('serial'));
    }

    /**
     * Update serial number details.
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session() ? request()->session()->get('user.business_id') : auth()->user()->business_id;
            $serial = ProductSerialNumber::where('business_id', $business_id)->findOrFail($id);

            $serial->serial_number = $request->input('serial_number');
            $serial->purchase_price = $request->filled('purchase_price') ? $this->productUtil->num_uf($request->input('purchase_price')) : null;
            $serial->selling_price = $request->filled('selling_price') ? $this->productUtil->num_uf($request->input('selling_price')) : null;
            $serial->status = $request->input('status');
            $serial->notes = $request->input('notes');
            $serial->save();

            $output = ['success' => true, 'msg' => 'Serial Number berhasil diperbarui.'];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    /**
     * AJAX endpoint to get active serial numbers for product & location.
     */
    public function getSerialNumbers(Request $request)
    {
        $business_id = $request->hasSession() && $request->session()->has('user.business_id')
            ? $request->session()->get('user.business_id')
            : (auth()->check() ? auth()->user()->business_id : null);

        $product_id = $request->input('product_id');
        $variation_id = $request->input('variation_id');
        $location_id = $request->input('location_id');

        $query = ProductSerialNumber::where('business_id', $business_id)
            ->where('status', 'in_stock');

        if (! empty($product_id)) {
            $query->where('product_id', $product_id);
        }

        if (! empty($variation_id)) {
            $query->where('variation_id', $variation_id);
        }

        if (! empty($location_id)) {
            $query->where(function ($q) use ($location_id) {
                $q->where('location_id', $location_id)->orWhereNull('location_id');
            });
        }

        $serials = $query->get(['id', 'serial_number', 'purchase_price', 'selling_price', 'notes']);

        return response()->json([
            'success' => true,
            'serials' => $serials
        ]);
    }

    /**
     * AJAX endpoint to search registered serial numbers for autocomplete/lookup in repair module.
     */
    public function getRegisteredSerials(Request $request)
    {
        $business_id = $request->hasSession() && $request->session()->has('user.business_id')
            ? $request->session()->get('user.business_id')
            : (auth()->check() ? auth()->user()->business_id : null);

        $term = $request->input('term');

        $query = ProductSerialNumber::where('product_serial_numbers.business_id', $business_id)
            ->leftJoin('products', 'product_serial_numbers.product_id', '=', 'products.id')
            ->select([
                'product_serial_numbers.id',
                'product_serial_numbers.serial_number',
                'product_serial_numbers.status',
                'products.name as product_name'
            ]);

        if (! empty($term)) {
            $query->where('product_serial_numbers.serial_number', 'like', '%' . $term . '%');
        }

        $serials = $query->limit(20)->get();

        return response()->json($serials);
    }
}
