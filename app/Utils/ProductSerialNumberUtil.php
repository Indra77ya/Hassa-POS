<?php

namespace App\Utils;

use App\ProductSerialNumber;
use App\TransactionSellLine;

class ProductSerialNumberUtil extends Util
{
    /**
     * Sync serial numbers on purchase line creation or update with custom HPP and Selling Price.
     */
    public function syncPurchaseSerialNumbers($business_id, $product_id, $variation_id, $purchase_line_id, array $serials, $default_purchase_price = 0, array $sn_details = [])
    {
        $serials = array_values(array_filter(array_map('trim', $serials)));

        $existing = ProductSerialNumber::where('business_id', $business_id)
            ->where('purchase_line_id', $purchase_line_id)
            ->get();

        // Remove deleted serials that are still in_stock
        foreach ($existing as $item) {
            if (!in_array($item->serial_number, $serials) && $item->status == 'in_stock') {
                $item->delete();
            }
        }

        foreach ($serials as $sn) {
            $pp = isset($sn_details[$sn]['purchase_price']) ? $this->num_uf($sn_details[$sn]['purchase_price']) : $default_purchase_price;
            $sp = isset($sn_details[$sn]['selling_price']) ? $this->num_uf($sn_details[$sn]['selling_price']) : 0;

            // Prevent registering duplicate in_stock serial numbers in same business
            $existing_instock = ProductSerialNumber::where('business_id', $business_id)
                ->where('serial_number', $sn)
                ->where('status', 'in_stock')
                ->where('purchase_line_id', '!=', $purchase_line_id)
                ->first();

            if ($existing_instock) {
                continue;
            }

            $record = ProductSerialNumber::where('business_id', $business_id)
                ->where('purchase_line_id', $purchase_line_id)
                ->where('serial_number', $sn)
                ->first();

            if ($record) {
                $record->update([
                    'purchase_price' => $pp,
                    'selling_price' => $sp
                ]);
            } else {
                ProductSerialNumber::create([
                    'business_id' => $business_id,
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'purchase_line_id' => $purchase_line_id,
                    'serial_number' => $sn,
                    'purchase_price' => $pp,
                    'selling_price' => $sp,
                    'status' => 'in_stock'
                ]);
            }
        }
    }

    /**
     * Get available in-stock serial numbers for a variation or product.
     */
    public function getAvailableSerials($business_id, $product_id, $variation_id = null)
    {
        $query = ProductSerialNumber::where('business_id', $business_id)
            ->where('product_id', $product_id)
            ->where('status', 'in_stock');

        if (!empty($variation_id)) {
            $query->where('variation_id', $variation_id);
        }

        return $query->select('id', 'serial_number', 'purchase_price', 'selling_price')->get();
    }

    /**
     * Mark serial numbers as sold for a sell line (creating new ones if scanned directly on POS).
     */
    public function syncSellSerialNumbers($business_id, $transaction_sell_line_id, array $serial_ids_or_numbers)
    {
        $serial_ids_or_numbers = array_values(array_filter(array_map('trim', $serial_ids_or_numbers)));

        $sell_line = TransactionSellLine::find($transaction_sell_line_id);

        // Reset previous sell line serials if any
        ProductSerialNumber::where('business_id', $business_id)
            ->where('transaction_sell_line_id', $transaction_sell_line_id)
            ->update([
                'transaction_sell_line_id' => null,
                'status' => 'in_stock'
            ]);

        if (empty($serial_ids_or_numbers)) {
            return;
        }

        foreach ($serial_ids_or_numbers as $sn_val) {
            $query = ProductSerialNumber::where('business_id', $business_id);
            if ($sell_line) {
                $query->where('product_id', $sell_line->product_id);
            }
            $record = $query->where(function($q) use ($sn_val) {
                $q->where('serial_number', $sn_val)
                  ->orWhere('id', $sn_val);
            })->first();

            if ($record) {
                $record->update([
                    'transaction_sell_line_id' => $transaction_sell_line_id,
                    'status' => 'sold'
                ]);
            } else {
                // Newly scanned SN on POS
                ProductSerialNumber::create([
                    'business_id' => $business_id,
                    'product_id' => $sell_line ? $sell_line->product_id : null,
                    'variation_id' => $sell_line ? $sell_line->variation_id : null,
                    'serial_number' => $sn_val,
                    'transaction_sell_line_id' => $transaction_sell_line_id,
                    'selling_price' => $sell_line ? $sell_line->unit_price_inc_tax : 0,
                    'status' => 'sold'
                ]);
            }
        }
    }
}
