<?php

namespace App\Http\Controllers;

use App\ProductSerialNumber;
use App\Utils\ProductSerialNumberUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductSerialNumberController extends Controller
{
    protected $snUtil;

    public function __construct(ProductSerialNumberUtil $snUtil)
    {
        $this->snUtil = $snUtil;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $serials = ProductSerialNumber::where('product_serial_numbers.business_id', $business_id)
                ->join('products', 'products.id', '=', 'product_serial_numbers.product_id')
                ->leftJoin('variations', 'variations.id', '=', 'product_serial_numbers.variation_id')
                ->leftJoin('transaction_sell_lines', 'transaction_sell_lines.id', '=', 'product_serial_numbers.transaction_sell_line_id')
                ->leftJoin('transactions as sell_tx', 'sell_tx.id', '=', 'transaction_sell_lines.transaction_id')
                ->leftJoin('purchase_lines', 'purchase_lines.id', '=', 'product_serial_numbers.purchase_line_id')
                ->leftJoin('transactions as purch_tx', 'purch_tx.id', '=', 'purchase_lines.transaction_id')
                ->select([
                    'product_serial_numbers.id',
                    'product_serial_numbers.serial_number',
                    'product_serial_numbers.purchase_price',
                    'product_serial_numbers.selling_price',
                    'product_serial_numbers.status',
                    'products.name as product_name',
                    'variations.name as variation_name',
                    'sell_tx.invoice_no as sell_invoice',
                    'purch_tx.ref_no as purchase_ref',
                    'product_serial_numbers.created_at'
                ]);

            if ($request->has('status') && !empty($request->status)) {
                $serials->where('product_serial_numbers.status', $request->status);
            }

            return Datatables::of($serials)
                ->addColumn('product', function ($row) {
                    $name = $row->product_name;
                    if (!empty($row->variation_name) && $row->variation_name != 'DUMMY') {
                        $name .= ' (' . $row->variation_name . ')';
                    }
                    return $name;
                })
                ->editColumn('purchase_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->purchase_price . '</span>';
                })
                ->editColumn('selling_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->selling_price . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $badge = 'bg-green';
                    $text = __('lang_v1.in_stock');
                    if ($row->status == 'sold') {
                        $badge = 'bg-red';
                        $text = __('lang_v1.sold');
                    } elseif ($row->status == 'used_in_repair') {
                        $badge = 'bg-yellow';
                        $text = 'Perbaikan';
                    } elseif ($row->status == 'returned') {
                        $badge = 'bg-blue';
                        $text = 'Diretur';
                    }
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return @format_datetime($row->created_at);
                })
                ->rawColumns(['status', 'purchase_price', 'selling_price'])
                ->make(true);
        }

        return view('product_serial_number.index');
    }

    public function getRegisteredSerials(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $product_id = $request->input('product_id');
        $variation_id = $request->input('variation_id');

        $serials = $this->snUtil->getAvailableSerials($business_id, $product_id, $variation_id);

        return response()->json([
            'success' => true,
            'serials' => $serials
        ]);
    }
}
