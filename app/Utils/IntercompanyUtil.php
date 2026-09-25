<?php

namespace App\Utils;

use App\BusinessIntercompanyLink;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Variation;
use Illuminate\Support\Facades\DB;

class IntercompanyUtil
{
    /**
     * Synchronize a sale transaction in source business to a purchase order in target linked business.
     */
    public static function syncSellToPurchase(Transaction $sell)
    {
        if ($sell->type != 'sell' || empty($sell->contact_id)) {
            return;
        }

        $link = BusinessIntercompanyLink::where('business_id', $sell->business_id)
            ->where('contact_id', $sell->contact_id)
            ->first();

        if (!$link) {
            return;
        }

        $target_business_id = $link->linked_business_id;
        $target_contact_id = $link->linked_contact_id;

        if (!$target_contact_id) {
            // Fallback: search or pick supplier contact in target business
            $target_contact = Contact::where('business_id', $target_business_id)
                ->whereIn('type', ['supplier', 'both'])
                ->first();
            $target_contact_id = $target_contact ? $target_contact->id : null;
        }

        if (!$target_contact_id) {
            return;
        }

        $target_location = BusinessLocation::where('business_id', $target_business_id)
            ->where('is_active', 1)
            ->first();

        if (!$target_location) {
            return;
        }

        DB::transaction(function () use ($sell, $link, $target_business_id, $target_contact_id, $target_location) {
            $existing_purchase = Transaction::where('business_id', $target_business_id)
                ->where('intercompany_linked_transaction_id', $sell->id)
                ->first();

            $ref_no = 'INT-PO-' . $sell->invoice_no;

            if ($existing_purchase) {
                $purchase = $existing_purchase;
                $purchase->update([
                    'total_before_tax' => $sell->total_before_tax,
                    'tax_amount' => $sell->tax_amount ?? 0,
                    'final_total' => $sell->final_total,
                ]);
            } else {
                $purchase = Transaction::create([
                    'business_id' => $target_business_id,
                    'location_id' => $target_location->id,
                    'type' => 'purchase',
                    'status' => 'ordered', // Pending receipt confirmation
                    'payment_status' => 'due',
                    'contact_id' => $target_contact_id,
                    'ref_no' => $ref_no,
                    'transaction_date' => $sell->transaction_date,
                    'total_before_tax' => $sell->total_before_tax,
                    'tax_amount' => $sell->tax_amount ?? 0,
                    'final_total' => $sell->final_total,
                    'created_by' => $sell->created_by,
                    'intercompany_linked_transaction_id' => $sell->id,
                ]);

                $sell->update(['intercompany_linked_transaction_id' => $purchase->id]);
            }

            // Sync purchase lines matching SKU
            PurchaseLine::where('transaction_id', $purchase->id)->delete();

            $sell_lines = TransactionSellLine::where('transaction_id', $sell->id)->get();

            foreach ($sell_lines as $line) {
                $sell_variation = Variation::with('product')->find($line->variation_id);
                if (!$sell_variation) continue;

                $sku = $sell_variation->sub_sku ?: $sell_variation->product->sku;

                // Find matching variation in target business by SKU
                $target_variation = Variation::where('sub_sku', $sku)
                    ->whereHas('product', function ($q) use ($target_business_id) {
                        $q->where('business_id', $target_business_id);
                    })
                    ->first();

                if (!$target_variation) {
                    $target_product = Product::where('sku', $sku)
                        ->where('business_id', $target_business_id)
                        ->first();
                    if ($target_product) {
                        $target_variation = Variation::where('product_id', $target_product->id)->first();
                    }
                }

                if ($target_variation) {
                    PurchaseLine::create([
                        'transaction_id' => $purchase->id,
                        'product_id' => $target_variation->product_id,
                        'variation_id' => $target_variation->id,
                        'quantity' => $line->quantity,
                        'purchase_price' => $line->unit_price,
                        'purchase_price_inc_tax' => $line->unit_price_inc_tax,
                        'item_tax' => $line->item_tax ?? 0,
                    ]);
                }
            }
        });
    }

    /**
     * Synchronize a purchase transaction in source business to a sale transaction in target linked business.
     */
    public static function syncPurchaseToSell(Transaction $purchase)
    {
        if ($purchase->type != 'purchase' || empty($purchase->contact_id)) {
            return;
        }

        $link = BusinessIntercompanyLink::where('business_id', $purchase->business_id)
            ->where('contact_id', $purchase->contact_id)
            ->first();

        if (!$link) {
            return;
        }

        $target_business_id = $link->linked_business_id;
        $target_contact_id = $link->linked_contact_id;

        if (!$target_contact_id) {
            $target_contact = Contact::where('business_id', $target_business_id)
                ->whereIn('type', ['customer', 'both'])
                ->first();
            $target_contact_id = $target_contact ? $target_contact->id : null;
        }

        if (!$target_contact_id) {
            return;
        }

        $target_location = BusinessLocation::where('business_id', $target_business_id)
            ->where('is_active', 1)
            ->first();

        if (!$target_location) {
            return;
        }

        DB::transaction(function () use ($purchase, $link, $target_business_id, $target_contact_id, $target_location) {
            $existing_sell = Transaction::where('business_id', $target_business_id)
                ->where('intercompany_linked_transaction_id', $purchase->id)
                ->first();

            $invoice_no = 'INT-SO-' . ($purchase->ref_no ?: $purchase->id);

            if ($existing_sell) {
                $sell = $existing_sell;
                $sell->update([
                    'total_before_tax' => $purchase->total_before_tax,
                    'tax_amount' => $purchase->tax_amount ?? 0,
                    'final_total' => $purchase->final_total,
                ]);
            } else {
                $sell = Transaction::create([
                    'business_id' => $target_business_id,
                    'location_id' => $target_location->id,
                    'type' => 'sell',
                    'status' => 'final',
                    'payment_status' => 'due',
                    'contact_id' => $target_contact_id,
                    'invoice_no' => $invoice_no,
                    'transaction_date' => $purchase->transaction_date,
                    'total_before_tax' => $purchase->total_before_tax,
                    'tax_amount' => $purchase->tax_amount ?? 0,
                    'final_total' => $purchase->final_total,
                    'created_by' => $purchase->created_by,
                    'intercompany_linked_transaction_id' => $purchase->id,
                ]);

                $purchase->update(['intercompany_linked_transaction_id' => $sell->id]);
            }

            // Sync sell lines matching SKU
            TransactionSellLine::where('transaction_id', $sell->id)->delete();

            $purchase_lines = PurchaseLine::where('transaction_id', $purchase->id)->get();

            foreach ($purchase_lines as $line) {
                $pur_variation = Variation::with('product')->find($line->variation_id);
                if (!$pur_variation) continue;

                $sku = $pur_variation->sub_sku ?: $pur_variation->product->sku;

                $target_variation = Variation::where('sub_sku', $sku)
                    ->whereHas('product', function ($q) use ($target_business_id) {
                        $q->where('business_id', $target_business_id);
                    })
                    ->first();

                if (!$target_variation) {
                    $target_product = Product::where('sku', $sku)
                        ->where('business_id', $target_business_id)
                        ->first();
                    if ($target_product) {
                        $target_variation = Variation::where('product_id', $target_product->id)->first();
                    }
                }

                if ($target_variation) {
                    TransactionSellLine::create([
                        'transaction_id' => $sell->id,
                        'product_id' => $target_variation->product_id,
                        'variation_id' => $target_variation->id,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->purchase_price,
                        'unit_price_inc_tax' => $line->purchase_price_inc_tax,
                        'unit_price_before_discount' => $line->purchase_price,
                        'item_tax' => $line->item_tax ?? 0,
                    ]);
                }
            }
        });
    }

    /**
     * Synchronize payment status bidirectionally across intercompany linked transactions.
     */
    public static function syncPaymentStatus(Transaction $transaction)
    {
        if (empty($transaction->intercompany_linked_transaction_id)) {
            return;
        }

        $linked_trans = Transaction::find($transaction->intercompany_linked_transaction_id);
        if (!$linked_trans) {
            return;
        }

        // Calculate total paid on current transaction
        $total_paid = TransactionPayment::where('transaction_id', $transaction->id)->sum('amount');
        $payment_status = 'due';
        if ($total_paid >= $transaction->final_total) {
            $payment_status = 'paid';
        } elseif ($total_paid > 0) {
            $payment_status = 'partial';
        }

        $transaction->payment_status = $payment_status;
        $transaction->save();

        // Update linked transaction payment status
        $linked_trans->payment_status = $payment_status;
        $linked_trans->save();
    }
}
