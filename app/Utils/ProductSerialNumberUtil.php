<?php

namespace App\Utils;

use App\ProductSerialNumber;

class ProductSerialNumberUtil extends Util
{
    /**
     * Save or update serial numbers from purchase lines input.
     *
     * @param \App\Transaction $transaction
     * @param array $purchases_input
     * @return void
     */
    public function saveOrUpdatePurchaseSerialNumbers($transaction, $purchases_input)
    {
        $business_id = $transaction->business_id;
        $location_id = $transaction->location_id;

        // Fetch saved purchase lines for this transaction
        $transaction->load(['purchase_lines', 'purchase_lines.product']);

        foreach ($transaction->purchase_lines as $index => $purchase_line) {
            $matching_input = null;

            // Match by array index or purchase_line_id
            foreach ($purchases_input as $key => $p_input) {
                if (isset($p_input['purchase_line_id']) && $p_input['purchase_line_id'] == $purchase_line->id) {
                    $matching_input = $p_input;
                    break;
                }
            }

            if (!$matching_input && isset($purchases_input[$index])) {
                $matching_input = $purchases_input[$index];
            }

            if ($matching_input && !empty($matching_input['serial_numbers'])) {
                $raw_serials = $matching_input['serial_numbers'];

                // Split by line breaks or commas
                $serial_list = preg_split('/[\n\r,]+/', $raw_serials);

                $processed_ids = [];

                foreach ($serial_list as $sn) {
                    $sn = trim($sn);
                    if (empty($sn)) {
                        continue;
                    }

                    $serial_record = ProductSerialNumber::where('purchase_line_id', $purchase_line->id)
                        ->where('serial_number', $sn)
                        ->first();

                    if (!$serial_record) {
                        $serial_record = new ProductSerialNumber();
                        $serial_record->business_id = $business_id;
                        $serial_record->product_id = $purchase_line->product_id;
                        $serial_record->variation_id = $purchase_line->variation_id;
                        $serial_record->location_id = $location_id;
                        $serial_record->purchase_line_id = $purchase_line->id;
                        $serial_record->serial_number = $sn;
                        $serial_record->purchase_price = $purchase_line->purchase_price_inc_tax;
                        $serial_record->selling_price = null; // Default to standard product price unless overridden
                        $serial_record->status = 'in_stock';
                        $serial_record->save();
                    } else {
                        $serial_record->location_id = $location_id;
                        $serial_record->purchase_price = $purchase_line->purchase_price_inc_tax;
                        $serial_record->save();
                    }

                    $processed_ids[] = $serial_record->id;
                }

                // Delete removed serial numbers that are still in_stock
                ProductSerialNumber::where('purchase_line_id', $purchase_line->id)
                    ->where('status', 'in_stock')
                    ->whereNotIn('id', $processed_ids)
                    ->delete();
            }
        }
    }

    /**
     * Link selected serial numbers to transaction sell lines and update status.
     *
     * @param \App\Transaction $transaction
     * @param array $products_input
     * @return void
     */
    public function linkSellSerialNumbers($transaction, $products_input)
    {
        if ($transaction->status !== 'final') {
            return;
        }

        $transaction->load('sell_lines');

        foreach ($transaction->sell_lines as $index => $sell_line) {
            $matching_product = null;

            foreach ($products_input as $p_input) {
                if (isset($p_input['transaction_sell_lines_id']) && $p_input['transaction_sell_lines_id'] == $sell_line->id) {
                    $matching_product = $p_input;
                    break;
                }
            }

            if (!$matching_product && isset($products_input[$index])) {
                $matching_product = $products_input[$index];
            }

            if ($matching_product && !empty($matching_product['product_serial_number_id'])) {
                $serial_id = $matching_product['product_serial_number_id'];
                $serial = ProductSerialNumber::find($serial_id);

                if ($serial && $serial->status == 'in_stock') {
                    $serial->transaction_sell_line_id = $sell_line->id;
                    $serial->status = 'sold';
                    $serial->save();
                }
            }
        }
    }
}
