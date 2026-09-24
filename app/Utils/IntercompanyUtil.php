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
use App\User;
use DB;

class IntercompanyUtil extends Util
{
    /**
     * Synchronize a Sell transaction to a target business as a pending Purchase transaction.
     *
     * @param Transaction $sellTransaction
     * @return Transaction|null
     */
    public function syncSellToPurchase(Transaction $sellTransaction)
    {
        // Only process Sell transactions that are final, draft, or quotation
        if (! in_array($sellTransaction->type, ['sell', 'pos'])) {
            return null;
        }

        // Check if there is an inter-company link for this business and contact
        $link = BusinessIntercompanyLink::where('business_id', $sellTransaction->business_id)
            ->where('contact_id', $sellTransaction->contact_id)
            ->first();

        if (! $link) {
            return null;
        }

        $targetBusinessId = $link->target_business_id;

        // Find or create a Supplier contact in target business representing source business
        $sourceBusiness = $sellTransaction->business;
        $supplierContact = Contact::where('business_id', $targetBusinessId)
            ->where(function ($q) use ($sourceBusiness, $sellTransaction) {
                $q->where('custom_field1', 'INTERCOMPANY_' . $sellTransaction->business_id)
                  ->orWhere('name', 'LIKE', '%' . $sourceBusiness->name . '%');
            })->first();

        if (! $supplierContact) {
            $supplierContact = Contact::create([
                'business_id' => $targetBusinessId,
                'type' => 'supplier',
                'name' => $sourceBusiness->name . ' (Inter-Company Partner)',
                'contact_id' => 'IC-SUPP-' . $sellTransaction->business_id,
                'custom_field1' => 'INTERCOMPANY_' . $sellTransaction->business_id,
                'created_by' => $sellTransaction->created_by,
            ]);
        }

        // Find first business location in target business
        $targetLocation = BusinessLocation::where('business_id', $targetBusinessId)->Active()->first();
        if (! $targetLocation) {
            return null;
        }

        // Find system user in target business
        $targetUser = User::where('business_id', $targetBusinessId)->first();
        $targetUserId = $targetUser ? $targetUser->id : $sellTransaction->created_by;

        // Retrieve sell lines
        $sellLines = TransactionSellLine::where('transaction_id', $sellTransaction->id)
            ->with(['product', 'variations'])
            ->get();

        // Check if linked purchase transaction already exists
        $purchaseTransaction = null;
        if (! empty($sellTransaction->intercompany_linked_transaction_id)) {
            $purchaseTransaction = Transaction::where('business_id', $targetBusinessId)
                ->where('id', $sellTransaction->intercompany_linked_transaction_id)
                ->first();
        }

        if (! $purchaseTransaction) {
            // Generate purchase ref no
            $refCount = $this->setAndGetReferenceCount('purchase', $targetBusinessId);
            $refNo = $this->generateReferenceNumber('purchase', $refCount, $targetBusinessId);

            $purchaseTransaction = Transaction::create([
                'business_id' => $targetBusinessId,
                'location_id' => $targetLocation->id,
                'type' => 'purchase',
                'status' => 'ordered', // Draft / Pending until confirmed
                'payment_status' => 'due',
                'contact_id' => $supplierContact->id,
                'transaction_date' => $sellTransaction->transaction_date,
                'ref_no' => $refNo,
                'total_before_tax' => $sellTransaction->total_before_tax,
                'tax_amount' => $sellTransaction->tax_amount ?? 0,
                'discount_type' => $sellTransaction->discount_type,
                'discount_amount' => $sellTransaction->discount_amount ?? 0,
                'final_total' => $sellTransaction->final_total,
                'created_by' => $targetUserId,
                'intercompany_linked_transaction_id' => $sellTransaction->id,
                'is_intercompany' => 1,
            ]);

            // Link back on sell transaction
            $sellTransaction->intercompany_linked_transaction_id = $purchaseTransaction->id;
            $sellTransaction->is_intercompany = 1;
            $sellTransaction->save();
        } else {
            // Update purchase transaction totals
            $purchaseTransaction->update([
                'total_before_tax' => $sellTransaction->total_before_tax,
                'tax_amount' => $sellTransaction->tax_amount ?? 0,
                'discount_type' => $sellTransaction->discount_type,
                'discount_amount' => $sellTransaction->discount_amount ?? 0,
                'final_total' => $sellTransaction->final_total,
            ]);

            // Delete old purchase lines for update
            PurchaseLine::where('transaction_id', $purchaseTransaction->id)->delete();
        }

        // Build purchase lines by SKU matching
        foreach ($sellLines as $sellLine) {
            $variation = $sellLine->variations;
            if (! $variation) {
                continue;
            }

            $sku = $variation->sub_sku;

            // Find matching variation in target business by SKU
            $targetVariation = Variation::whereHas('product', function ($q) use ($targetBusinessId) {
                $q->where('business_id', $targetBusinessId);
            })->where('sub_sku', $sku)->first();

            if (! $targetVariation) {
                // If not found by sub_sku, search product SKU
                $sourceProduct = $sellLine->product;
                if ($sourceProduct && ! empty($sourceProduct->sku)) {
                    $targetProduct = Product::where('business_id', $targetBusinessId)
                        ->where('sku', $sourceProduct->sku)
                        ->first();

                    if ($targetProduct) {
                        $targetVariation = Variation::where('product_id', $targetProduct->id)->first();
                    }
                }
            }

            if ($targetVariation) {
                // Selling price from Business A becomes purchase unit price in Business B
                $unitPurchasePrice = $sellLine->unit_price;

                PurchaseLine::create([
                    'transaction_id' => $purchaseTransaction->id,
                    'product_id' => $targetVariation->product_id,
                    'variation_id' => $targetVariation->id,
                    'quantity' => $sellLine->quantity,
                    'purchase_price' => $unitPurchasePrice,
                    'purchase_price_inc_tax' => $sellLine->unit_price_inc_tax ?? $unitPurchasePrice,
                    'item_tax' => $sellLine->item_tax ?? 0,
                    'tax_id' => $sellLine->tax_id,
                    'quantity_sold' => 0,
                ]);
            }
        }

        // Sync initial payment status if source transaction has payments
        $this->syncPaymentStatus($sellTransaction);

        return $purchaseTransaction;
    }

    /**
     * Synchronize payment status and payment lines bidirectionally between inter-company linked transactions.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function syncPaymentStatus(Transaction $transaction)
    {
        if (empty($transaction->intercompany_linked_transaction_id)) {
            return;
        }

        $linkedTransaction = Transaction::find($transaction->intercompany_linked_transaction_id);
        if (! $linkedTransaction) {
            return;
        }

        // Calculate total paid on current transaction
        $totalPaid = TransactionPayment::where('transaction_id', $transaction->id)->sum('amount');

        // Check total paid on linked transaction
        $linkedTotalPaid = TransactionPayment::where('transaction_id', $linkedTransaction->id)->sum('amount');

        // If payment amounts differ, mirror payment on linked transaction
        if (abs($totalPaid - $linkedTotalPaid) > 0.01) {
            // Delete existing automated inter-company payment lines on linked transaction
            TransactionPayment::where('transaction_id', $linkedTransaction->id)
                ->where('note', 'LIKE', '%Inter-Company Sync%')
                ->delete();

            $newLinkedPaid = TransactionPayment::where('transaction_id', $linkedTransaction->id)->sum('amount');
            $difference = $totalPaid - $newLinkedPaid;

            if ($difference > 0) {
                $prefixType = $linkedTransaction->type == 'purchase' ? 'purchase_payment' : 'sell_payment';
                $refCount = $this->setAndGetReferenceCount($prefixType, $linkedTransaction->business_id);
                $paymentRefNo = $this->generateReferenceNumber($prefixType, $refCount, $linkedTransaction->business_id);

                TransactionPayment::create([
                    'transaction_id' => $linkedTransaction->id,
                    'business_id' => $linkedTransaction->business_id,
                    'amount' => $difference,
                    'method' => 'other',
                    'payment_ref_no' => $paymentRefNo,
                    'paid_on' => now()->toDateTimeString(),
                    'created_by' => $linkedTransaction->created_by,
                    'note' => 'Inter-Company Sync Payment',
                    'payment_for' => $linkedTransaction->contact_id,
                ]);
            }
        }

        // Update payment status on both transactions
        $paymentUtil = new TransactionUtil();
        $paymentUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        $paymentUtil->updatePaymentStatus($linkedTransaction->id, $linkedTransaction->final_total);
    }
}
