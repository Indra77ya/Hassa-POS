<?php

namespace App\Http\Controllers;

use App\AccountTransaction;
use App\BusinessLocation;
use App\PurchaseLine;
use App\Transaction;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CombinedPurchaseReturnController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $transactionUtil;

    protected $productUtil;

    /**
     * Constructor
     *
     * @param  TransactionUtil  $transactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('purchase_return.create')
            ->with(compact('business_locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->except('_token');

            $business_id = request()->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $products = $input['products'];

            if (! empty($products)) {
                $ref_count = $this->transactionUtil->setAndGetReferenceCount('purchase_return');
                $return_ref_no = $this->transactionUtil->generateReferenceNumber('purchase_return', $ref_count);

                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $this->transactionUtil->num_uf($input['discount_amount']),
                ];

                $invoice_total = $this->productUtil->calculateInvoiceTotal($products, $input['tax_id'], $discount);

                $input_data = [
                    'business_id' => $business_id,
                    'location_id' => $input['location_id'],
                    'type' => 'purchase_return',
                    'status' => 'final',
                    'contact_id' => $input['contact_id'],
                    'ref_no' => empty($input['ref_no']) ? $return_ref_no : $input['ref_no'],
                    'transaction_date' => $this->transactionUtil->uf_date($input['transaction_date'], true),
                    'total_before_tax' => $invoice_total['total_before_tax'],
                    'tax_id' => $input['tax_id'],
                    'tax_amount' => $invoice_total['tax'],
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $this->transactionUtil->num_uf($input['discount_amount']),
                    'final_total' => $invoice_total['final_total'],
                    'created_by' => $user_id,
                ];

                $product_data = [];

                foreach ($products as $product) {
                    $product_data[] = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => 0,
                        'purchase_price' => $this->productUtil->num_uf($product['unit_price']),
                        'purchase_price_inc_tax' => $this->productUtil->num_uf($product['unit_price_inc_tax']),
                        'quantity_returned' => $this->productUtil->num_uf($product['quantity']),
                    ];

                    //decrease product quantity
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input['location_id'],
                        $this->productUtil->num_uf($product['quantity'])
                    );
                }

                $purchase_return = Transaction::create($input_data);
                $purchase_return->purchase_lines()->createMany($product_data);

                //update payment status
                $this->transactionUtil->updatePaymentStatus($purchase_return->id, $purchase_return->final_total);

                // Sync accounting double-entry journal if Accounting module is installed
                if (class_exists('\Modules\Accounting\Listeners\MapPurchaseReturnTransaction')) {
                    $mapPurchaseReturn = new \Modules\Accounting\Listeners\MapPurchaseReturnTransaction();
                    $mapPurchaseReturn->handle($purchase_return);
                }
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.purchase_return_added_success'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('purchase-return')->with('status', $output);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $purchase_return = Transaction::where('business_id', $business_id)
                                    ->where('type', 'purchase_return')
                                    ->find($id);

        $location_id = $purchase_return->location_id;

        $purchase_lines = Transaction::leftJoin('purchase_lines as pl', 'transactions.id', '=', 'pl.transaction_id')
                        ->leftJoin('products as p', 'pl.product_id', '=', 'p.id')
                        ->leftJoin('variations as v', 'pl.variation_id', '=', 'v.id')
                        ->leftJoin('variation_location_details as vld', function ($join) use ($location_id) {
                            $join->on('v.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
                        ->where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'purchase_return')
                        ->where('transactions.id', $id)
                        ->select(
                            'p.name as product_name',
                            'p.type as product_type',
                            'p.id as product_id',
                            'v.id as variation_id',
                            'v.name as variation_name',
                            'v.sub_sku',
                            'vld.qty_available',
                            'u.short_name as unit',
                            'pl.id as purchase_line_id',
                            'pl.purchase_price as unit_price',
                            'pl.purchase_price_inc_tax as unit_price_inc_tax',
                            'pl.quantity_returned as quantity_returned'
                        )->get();

        foreach ($purchase_lines as $key => $value) {
            $purchase_lines[$key]->qty_available += $value->quantity_returned;
            $purchase_lines[$key]->formatted_qty_available = $this->productUtil->num_f($purchase_lines[$key]->qty_available);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $taxes = $this->transactionUtil->getTaxDetails($business_id);

        return view('purchase_return.edit')
            ->with(compact('business_locations', 'purchase_return', 'purchase_lines', 'taxes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->except('_token');

            $business_id = request()->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $purchase_return_id = $input['purchase_return_id'];

            $purchase_return = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase_return')
                                ->find($purchase_return_id);

            $products = $input['products'];

            if (! empty($products)) {
                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $this->transactionUtil->num_uf($input['discount_amount']),
                ];

                $invoice_total = $this->productUtil->calculateInvoiceTotal($products, $input['tax_id'], $discount);

                $input_data = [
                    'contact_id' => $input['contact_id'],
                    'ref_no' => $input['ref_no'],
                    'transaction_date' => $this->transactionUtil->uf_date($input['transaction_date'], true),
                    'total_before_tax' => $invoice_total['total_before_tax'],
                    'tax_id' => $input['tax_id'],
                    'tax_amount' => $invoice_total['tax'],
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $this->transactionUtil->num_uf($input['discount_amount']),
                    'final_total' => $invoice_total['final_total'],
                ];

                $product_data = [];

                $updated_purchase_lines = [];

                foreach ($products as $product) {
                    if (! empty($product['purchase_line_id'])) {
                        $return_line = PurchaseLine::find($product['purchase_line_id']);

                        $updated_purchase_lines[] = $return_line->id;

                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $purchase_return->location_id,
                            $this->productUtil->num_uf($product['quantity']),
                            $return_line->quantity_returned
                        );

                        $return_line->purchase_price = $this->productUtil->num_uf($product['unit_price']);
                        $return_line->purchase_price_inc_tax = $this->productUtil->num_uf($product['unit_price_inc_tax']);
                        $return_line->quantity_returned = $this->productUtil->num_uf($product['quantity']);
                        $return_line->save();
                    } else {
                        $product_data[] = [
                            'product_id' => $product['product_id'],
                            'variation_id' => $product['variation_id'],
                            'quantity' => 0,
                            'purchase_price' => $this->productUtil->num_uf($product['unit_price']),
                            'purchase_price_inc_tax' => $this->productUtil->num_uf($product['unit_price_inc_tax']),
                            'quantity_returned' => $this->productUtil->num_uf($product['quantity']),
                        ];

                        //decrease product quantity
                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $purchase_return->location_id,
                            $this->productUtil->num_uf($product['quantity'])
                        );
                    }
                }

                $purchase_return->update($input_data);

                //If purchase line deleted add return quantity to stock
                $deleted_purchase_lines = PurchaseLine::where('transaction_id', $purchase_return_id)
                            ->whereNotIn('id', $updated_purchase_lines)
                            ->get();

                foreach ($deleted_purchase_lines as $dpl) {
                    $this->productUtil->updateProductQuantity($purchase_return->location_id, $dpl->product_id, $dpl->variation_id, $dpl->quantity_returned, 0, null, false);
                }

                PurchaseLine::where('transaction_id', $purchase_return_id)
                            ->whereNotIn('id', $updated_purchase_lines)
                            ->delete();

                $purchase_return->purchase_lines()->saveMany($product_data);

                //update payment status
                $this->transactionUtil->updatePaymentStatus($purchase_return->id, $purchase_return->final_total);

                // Sync accounting double-entry journal if Accounting module is installed
                if (class_exists('\Modules\Accounting\Listeners\MapPurchaseReturnTransaction')) {
                    $mapPurchaseReturn = new \Modules\Accounting\Listeners\MapPurchaseReturnTransaction();
                    $mapPurchaseReturn->handle($purchase_return);
                }
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.purchase_return_updated_success'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('purchase-return')->with('status', $output);
    }

    /**
     * Return products row for combined purchase return.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductRow(Request $request)
    {
        if ($request->ajax()) {
            $row_index = $request->input('row_index');
            $variation_id = $request->input('variation_id');
            $location_id = $request->input('location_id');

            $business_id = $request->session()->get('user.business_id');

            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);

            return view('purchase_return.partials.product_table_row')
            ->with(compact('product', 'row_index'));
        }
    }
}
