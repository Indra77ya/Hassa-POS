<?php

namespace App\Http\Controllers;

use App\SellingPriceGroup;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class SellingPriceGroupController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $price_groups = SellingPriceGroup::where('business_id', $business_id)
                        ->select(['name', 'description', 'id', 'is_active']);

            return Datatables::of($price_groups)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            @lang("messages.actions") <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                            <li>
                                <button type="button" data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@edit\', [$id])}}" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100 hover:tw-text-gray-900 tw-bg-transparent tw-border-none tw-outline-none btn-modal" data-container=".view_modal">
                                    <i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")
                                </button>
                            </li>
                            <li>
                                <button type="button" data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@destroy\', [$id])}}" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100 hover:tw-text-gray-900 tw-bg-transparent tw-border-none tw-outline-none delete_spg_button">
                                    <i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")
                                </button>
                            </li>
                            <li>
                                <button type="button" data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@activateDeactivate\', [$id])}}" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100 hover:tw-text-gray-900 tw-bg-transparent tw-border-none tw-outline-none activate_deactivate_spg">
                                    <i class="fas fa-power-off"></i> @if($is_active) @lang("messages.deactivate") @else @lang("messages.activate") @endif
                                </button>
                            </li>
                        </ul>
                    </div>'
                )
                ->removeColumn('is_active')
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('selling_price_group.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $spg = SellingPriceGroup::create($input);

            //Create a new permission related to the created selling price group
            Permission::create(['name' => 'selling_price_group.'.$spg->id]);

            $output = ['success' => true,
                'data' => $spg,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);

            return view('selling_price_group.edit')
                ->with(compact('spg'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->name = $input['name'];
                $spg->description = $input['description'];
                $spg->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show interface to download product price excel file.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProductPrice(){
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.update_product_price');
    }

    /**
     * Exports selling price group prices for all the products in xls format
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        if (! auth()->user()->can('product.create') && ! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->user()->business_id;
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

        $variations = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
                            ->leftJoin('categories as sub_c', 'p.sub_category_id', '=', 'sub_c.id')
                            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
                            ->leftJoin('tax_rates as t', 'p.tax', '=', 't.id')
                            ->where('p.business_id', $business_id)
                            ->whereIn('p.type', ['single', 'variable'])
                            ->select(
                                'variations.sub_sku',
                                'p.name as product_name',
                                'variations.name as variation_name',
                                'p.type',
                                'variations.id',
                                'pv.name as product_variation_name',
                                'c.name as category_name',
                                'sub_c.name as sub_category_name',
                                'b.name as brand_name',
                                't.name as tax_name',
                                'variations.default_purchase_price',
                                'variations.dpp_inc_tax',
                                'variations.profit_percent',
                                'variations.default_sell_price',
                                'variations.sell_price_inc_tax'
                            )
                            ->with(['group_prices', 'product.product_locations'])
                            ->get();

        $export_data = [];
        foreach ($variations as $variation) {
            $temp = [];
            $temp['Product'] = $variation->type == 'single' ? $variation->product_name : $variation->product_name.' - '.$variation->product_variation_name.' - '.$variation->variation_name;
            $temp['SKU'] = $variation->sub_sku;
            $temp['Category'] = $variation->category_name ?? '';
            $temp['Sub Category'] = $variation->sub_category_name ?? '';
            $temp['Brand'] = $variation->brand_name ?? '';
            $temp['Tax'] = $variation->tax_name ?? '';

            $locations = $variation->product && $variation->product->product_locations ? $variation->product->product_locations->pluck('name')->toArray() : [];
            $temp['Business Locations'] = implode(',', $locations);

            $temp['Default Purchase Price Exc. Tax'] = $variation->default_purchase_price;
            $temp['Default Purchase Price Inc. Tax'] = $variation->dpp_inc_tax;
            $temp['Margin (%)'] = $variation->profit_percent;
            $temp['Default Selling Price Exc. Tax'] = $variation->default_sell_price;
            $temp['Default Selling Price Inc. Tax'] = $variation->sell_price_inc_tax;

            foreach ($price_groups as $price_group) {
                $price_group_id = $price_group->id;
                $variation_pg = $variation->group_prices->filter(function ($item) use ($price_group_id) {
                    return $item->price_group_id == $price_group_id;
                });

                $temp[$price_group->name] = $variation_pg->isNotEmpty() ? $variation_pg->first()->price_inc_tax : '';
            }
            $export_data[] = $temp;
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }
        ob_start();

        return collect($export_data)->downloadExcel(
            'update_products.xlsx',
            null,
            true
        );
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('product_group_prices')) {
                $file = $request->file('product_group_prices');

                $parsed_array = Excel::toArray([], $file);

                $headers = $parsed_array[0][0];

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

                // Map header columns dynamically
                $sku_col = null;
                $cat_col = null;
                $sub_cat_col = null;
                $brand_col = null;
                $tax_col = null;
                $locations_col = null;
                $dpp_exc_col = null;
                $dpp_inc_col = null;
                $margin_col = null;
                $dsp_exc_col = null;
                $dsp_inc_col = null;
                $imported_pgs = [];

                foreach ($headers as $key => $header_value) {
                    $header_lower = strtolower(trim($header_value));

                    if (empty($header_lower)) {
                        continue;
                    }

                    if ($header_lower == 'sku') {
                        $sku_col = $key;
                    } elseif ($header_lower == 'category') {
                        $cat_col = $key;
                    } elseif ($header_lower == 'sub category' || $header_lower == 'sub_category') {
                        $sub_cat_col = $key;
                    } elseif ($header_lower == 'brand') {
                        $brand_col = $key;
                    } elseif ($header_lower == 'tax') {
                        $tax_col = $key;
                    } elseif ($header_lower == 'business locations' || $header_lower == 'locations') {
                        $locations_col = $key;
                    } elseif (in_array($header_lower, ['default purchase price exc. tax', 'purchase price (excluding tax)', 'purchase price exc. tax'])) {
                        $dpp_exc_col = $key;
                    } elseif (in_array($header_lower, ['default purchase price inc. tax', 'purchase price (including tax)', 'purchase price inc. tax'])) {
                        $dpp_inc_col = $key;
                    } elseif (in_array($header_lower, ['margin (%)', 'margin', 'profit margin'])) {
                        $margin_col = $key;
                    } elseif (in_array($header_lower, ['default selling price exc. tax', 'selling price (excluding tax)', 'selling price exc. tax'])) {
                        $dsp_exc_col = $key;
                    } elseif (in_array($header_lower, ['default selling price inc. tax', 'selling price (including tax)', 'selling price inc. tax', 'selling price including tax'])) {
                        $dsp_inc_col = $key;
                    } else {
                        // Check if header matches an active price group
                        $pg_match = $price_groups->filter(function ($item) use ($header_value) {
                            return strtolower($item->name) == strtolower(trim($header_value));
                        });
                        if ($pg_match->isNotEmpty()) {
                            $imported_pgs[$key] = $header_value;
                        }
                    }
                }

                // Fallback column positions for legacy or fixed format if headers were not explicitly matched
                if (is_null($sku_col)) {
                    $sku_col = 1;
                }
                if (is_null($dsp_inc_col) && count($headers) <= 3) {
                    $dsp_inc_col = 2;
                }

                $error_msg = '';
                $user_id = $request->session()->get('user.id');
                DB::beginTransaction();

                foreach ($imported_data as $key => $value) {
                    $sku_val = isset($value[$sku_col]) ? trim($value[$sku_col]) : '';
                    if (empty($sku_val)) {
                        continue;
                    }

                    $variation = Variation::where('sub_sku', $sku_val)
                                        ->join('products', 'products.id', '=', 'variations.product_id')
                                        ->where('products.business_id', $business_id)
                                        ->select('variations.*')
                                        ->first();
                    if (empty($variation)) {
                        $row = $key + 1;
                        $error_msg = __('lang_v1.product_not_found_exception', ['sku' => $sku_val, 'row' => $row]);

                        throw new \Exception($error_msg);
                    }

                    $product = \App\Product::find($variation->product_id);

                    // Update Category & Sub-Category
                    if (! is_null($cat_col) && isset($value[$cat_col])) {
                        $category_name = trim($value[$cat_col]);
                        if (! empty($category_name)) {
                            $category = \App\Category::firstOrCreate(
                                ['business_id' => $business_id, 'name' => $category_name, 'category_type' => 'product'],
                                ['created_by' => $user_id, 'parent_id' => 0]
                            );
                            $product->category_id = $category->id;

                            if (! is_null($sub_cat_col) && isset($value[$sub_cat_col])) {
                                $sub_category_name = trim($value[$sub_cat_col]);
                                if (! empty($sub_category_name)) {
                                    $sub_category = \App\Category::firstOrCreate(
                                        ['business_id' => $business_id, 'name' => $sub_category_name, 'category_type' => 'product'],
                                        ['created_by' => $user_id, 'parent_id' => $category->id]
                                    );
                                    $product->sub_category_id = $sub_category->id;
                                } else {
                                    $product->sub_category_id = null;
                                }
                            }
                        } else {
                            $product->category_id = null;
                            $product->sub_category_id = null;
                        }
                    }

                    // Update Brand
                    if (! is_null($brand_col) && isset($value[$brand_col])) {
                        $brand_name = trim($value[$brand_col]);
                        if (! empty($brand_name)) {
                            $brand = \App\Brands::firstOrCreate(
                                ['business_id' => $business_id, 'name' => $brand_name],
                                ['created_by' => $user_id]
                            );
                            $product->brand_id = $brand->id;
                        } else {
                            $product->brand_id = null;
                        }
                    }

                    // Update Tax
                    if (! is_null($tax_col) && isset($value[$tax_col])) {
                        $tax_name = trim($value[$tax_col]);
                        if (! empty($tax_name) && strtolower($tax_name) != 'none') {
                            $tax = \App\TaxRate::where('business_id', $business_id)
                                                ->where('name', $tax_name)
                                                ->first();
                            if (! empty($tax)) {
                                $product->tax = $tax->id;
                            }
                        } else {
                            $product->tax = null;
                        }
                    }

                    // Update Business Locations
                    if (! is_null($locations_col) && isset($value[$locations_col])) {
                        $locations_str = trim($value[$locations_col]);
                        if (! empty($locations_str)) {
                            $locations_array = explode(',', $locations_str);
                            $location_ids = [];
                            $business_locations = \App\BusinessLocation::where('business_id', $business_id)->get();
                            foreach ($locations_array as $loc_name) {
                                foreach ($business_locations as $loc) {
                                    if (strtolower($loc->name) == strtolower(trim($loc_name))) {
                                        $location_ids[] = $loc->id;
                                    }
                                }
                            }
                            if (! empty($location_ids)) {
                                $product->product_locations()->sync($location_ids);
                            }
                        }
                    }

                    $product->save();

                    // Price & Margin Calculations
                    $dpp_exc = ! is_null($dpp_exc_col) && isset($value[$dpp_exc_col]) && $value[$dpp_exc_col] !== '' ? $this->commonUtil->num_uf($value[$dpp_exc_col]) : null;
                    $dpp_inc = ! is_null($dpp_inc_col) && isset($value[$dpp_inc_col]) && $value[$dpp_inc_col] !== '' ? $this->commonUtil->num_uf($value[$dpp_inc_col]) : null;
                    $margin = ! is_null($margin_col) && isset($value[$margin_col]) && $value[$margin_col] !== '' ? $this->commonUtil->num_uf($value[$margin_col]) : null;
                    $dsp_exc = ! is_null($dsp_exc_col) && isset($value[$dsp_exc_col]) && $value[$dsp_exc_col] !== '' ? $this->commonUtil->num_uf($value[$dsp_exc_col]) : null;
                    $dsp_inc = ! is_null($dsp_inc_col) && isset($value[$dsp_inc_col]) && $value[$dsp_inc_col] !== '' ? $this->commonUtil->num_uf($value[$dsp_inc_col]) : null;

                    $tax_percent = 0;
                    if (! empty($product->tax)) {
                        $tax_rate = \App\TaxRate::find($product->tax);
                        $tax_percent = ! empty($tax_rate) ? $tax_rate->amount : 0;
                    }

                    if (! is_null($dpp_exc)) {
                        $variation->default_purchase_price = $dpp_exc;
                        $variation->dpp_inc_tax = ! is_null($dpp_inc) ? $dpp_inc : $this->commonUtil->calc_percentage($dpp_exc, $tax_percent, $dpp_exc);
                    } elseif (! is_null($dpp_inc)) {
                        $variation->dpp_inc_tax = $dpp_inc;
                        $variation->default_purchase_price = $this->commonUtil->calc_percentage_base($dpp_inc, $tax_percent);
                    }

                    if (! is_null($margin)) {
                        $variation->profit_percent = $margin;
                    }

                    if (! is_null($dsp_inc)) {
                        $variation->sell_price_inc_tax = $dsp_inc;
                        $variation->default_sell_price = ! is_null($dsp_exc) ? $dsp_exc : $this->commonUtil->calc_percentage_base($dsp_inc, $tax_percent);
                        if (is_null($margin) && $variation->default_purchase_price > 0) {
                            $variation->profit_percent = $this->commonUtil->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                        }
                    } elseif (! is_null($dsp_exc)) {
                        $variation->default_sell_price = $dsp_exc;
                        $variation->sell_price_inc_tax = $this->commonUtil->calc_percentage($dsp_exc, $tax_percent, $dsp_exc);
                        if (is_null($margin) && $variation->default_purchase_price > 0) {
                            $variation->profit_percent = $this->commonUtil->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                        }
                    } elseif (! is_null($margin) && $variation->default_purchase_price > 0) {
                        $variation->default_sell_price = $this->commonUtil->calc_percentage($variation->default_purchase_price, $margin, $variation->default_purchase_price);
                        $variation->sell_price_inc_tax = $this->commonUtil->calc_percentage($variation->default_sell_price, $tax_percent, $variation->default_sell_price);
                    }

                    $variation->update();

                    // Update Selling Price Groups
                    foreach ($imported_pgs as $k => $v) {
                        $price_group = $price_groups->filter(function ($item) use ($v) {
                            return strtolower($item->name) == strtolower(trim($v));
                        });

                        if ($price_group->isNotEmpty()) {
                            if (isset($value[$k]) && ! is_null($value[$k]) && $value[$k] !== '') {
                                $pg_val = $this->commonUtil->num_uf($value[$k]);
                                VariationGroupPrice::updateOrCreate(
                                    [
                                        'variation_id' => $variation->id,
                                        'price_group_id' => $price_group->first()->id,
                                    ],
                                    [
                                        'price_inc_tax' => $pg_val,
                                    ]
                                );
                            }
                        }
                    }
                }
                DB::commit();
            }
            $output = ['success' => 1,
                'msg' => __('lang_v1.product_prices_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect('update-product-price')->with('notification', $output);
        }

        return redirect('update-product-price')->with('status', $output);
    }

    /**
     * Activate/deactivate selling price group.
     */
    public function activateDeactivate($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);
            $spg->is_active = $spg->is_active == 1 ? 0 : 1;
            $spg->save();

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];

            return $output;
        }
    }
}
