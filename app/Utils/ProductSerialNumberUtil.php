<?php

namespace App\Utils;

use App\ProductSerialNumber;
use App\TransactionSellLine;

class ProductSerialNumberUtil extends Util
{
    /**
     * Sync serial numbers on purchase line creation or update.
     */
    public function syncPurchaseSerialNumbers($business_id, $product_id, $variation_id, $purchase_line_id, array $serials, $purchase_price = 0)
    {
        // Clean serial numbers list
        $serials = array_values(array_filter(array_map('trim', $serials)));

        // Existing serials for this purchase line
        $existing = ProductSerialNumber::where('business_id', $business_id)
            ->where('purchase_line_id', $purchase_line_id)
            ->get();

        $existing_sn_list = $existing->pluck('serial_number')->toArray();

        // Remove deleted serials that are still in_stock
        foreach ($existing as $item) {
            if (!in_array($item->serial_number, $serials) && $item->status == 'in_stock') {
                $item->delete();
            }
        }

        // Add new serials
        foreach ($serials as $sn) {
            if (!empty($sn) && !in_array($sn, $existing_sn_list)) {
                ProductSerialNumber::create([
                    'business_id' => $business_id,
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'purchase_line_id' => $purchase_line_id,
                    'serial_number' => $sn,
                    'purchase_price' => $purchase_price,
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
            $record = ProductSerialNumber::where('business_id', $business_id)
                ->where(function($q) use ($sn_val) {
                    $q->where('id', $sn_val)
                      ->orWhere('serial_number', $sn_val);
                })
                ->first();

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
