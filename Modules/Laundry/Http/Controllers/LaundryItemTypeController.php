<?php

namespace Modules\Laundry\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Laundry\Entities\LaundryItemType;
use Modules\Laundry\Entities\LaundryProcess;
use Yajra\DataTables\Facades\DataTables;

class LaundryItemTypeController extends Controller
{
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $item_types = LaundryItemType::where('business_id', $business_id);

            return DataTables::of($item_types)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button> ';
                    $html .= '<button data-href="' . action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_item_type_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    return $html;
                })
                ->addColumn('default_processes', function ($row) {
                    if (empty($row->process_ids)) return '-';
                    $processes = LaundryProcess::whereIn('id', $row->process_ids)->pluck('name')->toArray();
                    return implode(', ', $processes);
                })
                ->rawColumns(['action', 'default_processes'])
                ->make(true);
        }

        return view('laundry::item_type.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->pluck('name', 'id');

        return view('laundry::item_type.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'unit_name', 'default_price', 'description', 'process_ids']);
            $input['business_id'] = $business_id;

            $item_type = LaundryItemType::create($input);

            // Auto-create service product in Products table for this item type
            $user_id = $request->session()->get('user.id') ?? 1;
            $variation_id = $this->_createOrSyncProductForItemType($business_id, $item_type, $user_id);

            if ($variation_id && \Illuminate\Support\Facades\Schema::hasColumn('laundry_item_types', 'variation_id')) {
                $item_type->variation_id = $variation_id;
                $item_type->save();
            }

            $output = ['success' => true, 'msg' => __('laundry::lang.item_type_added_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $item_type = LaundryItemType::where('business_id', $business_id)->findOrFail($id);
        $processes = LaundryProcess::where('business_id', $business_id)->where('is_active', true)->orderBy('sort_order', 'asc')->pluck('name', 'id');

        return view('laundry::item_type.edit', compact('item_type', 'processes'));
    }

    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'unit_name', 'default_price', 'description', 'process_ids']);
            $item_type = LaundryItemType::where('business_id', $business_id)->findOrFail($id);
            $item_type->update($input);

            // Sync updated details to linked product/variation
            $user_id = $request->session()->get('user.id') ?? 1;
            $variation_id = $this->_createOrSyncProductForItemType($business_id, $item_type, $user_id);

            if ($variation_id && \Illuminate\Support\Facades\Schema::hasColumn('laundry_item_types', 'variation_id')) {
                $item_type->variation_id = $variation_id;
                $item_type->save();
            }

            $output = ['success' => true, 'msg' => __('laundry::lang.item_type_updated_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    private function _createOrSyncProductForItemType($business_id, $item_type, $user_id = 1)
    {
        if (empty($item_type)) return null;

        try {
            $unit_name = $item_type->unit_name ?? 'kg';
            $unit = \App\Unit::where('business_id', $business_id)->where('actual_name', 'LIKE', '%' . $unit_name . '%')->first();
            if (!$unit) {
                $unit = \App\Unit::where('business_id', $business_id)->first();
            }

            $price = $item_type->default_price ?? 0;
            $variation_id = \Illuminate\Support\Facades\Schema::hasColumn('laundry_item_types', 'variation_id') ? $item_type->variation_id : null;

            if (!empty($variation_id)) {
                $variation = \App\Variation::find($variation_id);
                if ($variation) {
                    $product = \App\Product::find($variation->product_id);
                    if ($product) {
                        $product->name = $item_type->name;
                        if ($unit) $product->unit_id = $unit->id;
                        $product->save();

                        $variation->default_purchase_price = $price;
                        $variation->dpp_inc_tax = $price;
                        $variation->default_sell_price = $price;
                        $variation->sell_price_inc_tax = $price;
                        $variation->save();

                        return $variation->id;
                    }
                }
            }

            // Check existing product by exact name
            $variation = \App\Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                ->where('p.business_id', $business_id)
                ->where('p.name', $item_type->name)
                ->select('variations.*')
                ->first();

            if ($variation) {
                $variation->default_purchase_price = $price;
                $variation->dpp_inc_tax = $price;
                $variation->default_sell_price = $price;
                $variation->sell_price_inc_tax = $price;
                $variation->save();

                return $variation->id;
            }

            // Create single product & variation using ProductUtil
            $product_data = [
                'name' => $item_type->name,
                'business_id' => $business_id,
                'unit_id' => $unit ? $unit->id : 1,
                'type' => 'single',
                'enable_stock' => 0,
                'alert_quantity' => 0,
                'created_by' => $user_id,
                'sku' => 'LND-ITM-' . $item_type->id . '-' . time(),
            ];

            $product = \App\Product::create($product_data);

            if (\Illuminate\Support\Facades\Schema::hasTable('business_locations') && \Illuminate\Support\Facades\Schema::hasTable('product_locations')) {
                $locations = \App\BusinessLocation::where('business_id', $business_id)->pluck('id')->toArray();
                if (!empty($locations)) {
                    $product->product_locations()->sync($locations);
                }
            }

            $productUtil = new \App\Utils\ProductUtil();
            $variation = $productUtil->createSingleProductVariation(
                $product,
                $product->sku,
                $price,
                $price,
                0,
                $price,
                $price
            );

            if (is_object($variation) && isset($variation->id)) {
                return $variation->id;
            }

            if (is_numeric($variation)) {
                return $variation;
            }

            $first_var = \App\Variation::where('product_id', $product->id)->first();
            return $first_var ? $first_var->id : null;
        } catch (\Exception $e) {
            \Log::error('Error creating/syncing product for laundry item type: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            LaundryItemType::where('business_id', $business_id)->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __('laundry::lang.item_type_deleted_success')];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function getItemTypeDetails($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $item_type = LaundryItemType::where('business_id', $business_id)->find($id);

        $processes = [];
        if ($item_type && !empty($item_type->process_ids)) {
            $processes = LaundryProcess::where('business_id', $business_id)
                ->whereIn('id', $item_type->process_ids)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'points']);
        }

        return response()->json([
            'unit_name' => $item_type ? $item_type->unit_name : 'kg',
            'default_price' => $item_type ? $item_type->default_price : 0,
            'processes' => $processes,
        ]);
    }
}
