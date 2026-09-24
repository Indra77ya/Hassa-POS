<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessIntercompanyLink;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Utils\IntercompanyUtil;
use App\Variation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IntercompanySyncTest extends TestCase
{
    use DatabaseTransactions;

    public function test_intercompany_link_and_sell_to_purchase_sync()
    {
        // 1. Create Business A and Business B
        $businessA = Business::create([
            'name' => 'Business A Trading',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'time_zone' => 'Asia/Jakarta',
        ]);

        $businessB = Business::create([
            'name' => 'Business B Retail',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'time_zone' => 'Asia/Jakarta',
        ]);

        // Create Location for B
        $locationB = BusinessLocation::create([
            'business_id' => $businessB->id,
            'name' => 'Store B',
            'is_active' => 1,
        ]);

        // Create User for B
        $userB = User::create([
            'surname' => 'Admin',
            'first_name' => 'UserB',
            'email' => 'userb@test.com',
            'username' => 'userb_test',
            'password' => bcrypt('secret'),
            'business_id' => $businessB->id,
        ]);

        // 2. Create Contact in Business A representing Business B
        $contactInA = Contact::create([
            'business_id' => $businessA->id,
            'type' => 'customer',
            'name' => 'PT Business B',
            'created_by' => 1,
        ]);

        // 3. Create Inter-Company Link
        BusinessIntercompanyLink::create([
            'business_id' => $businessA->id,
            'contact_id' => $contactInA->id,
            'target_business_id' => $businessB->id,
        ]);

        // 4. Create Product with SKU "ITEM-001" in both Business A and Business B
        $productA = Product::create([
            'name' => 'Widget A',
            'business_id' => $businessA->id,
            'unit_id' => 1,
            'category_id' => 1,
            'sku' => 'ITEM-001',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $variationA = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $productA->id,
            'sub_sku' => 'ITEM-001',
            'default_sell_price' => 100000,
            'sell_price_inc_tax' => 100000,
        ]);

        $productB = Product::create([
            'name' => 'Widget B',
            'business_id' => $businessB->id,
            'unit_id' => 1,
            'category_id' => 1,
            'sku' => 'ITEM-001',
            'type' => 'single',
            'created_by' => $userB->id,
        ]);

        $variationB = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $productB->id,
            'sub_sku' => 'ITEM-001',
            'default_sell_price' => 150000,
            'sell_price_inc_tax' => 150000,
        ]);

        // 5. Create Sell Transaction in Business A
        $sellTransaction = Transaction::create([
            'business_id' => $businessA->id,
            'location_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'contact_id' => $contactInA->id,
            'invoice_no' => 'INV-0001',
            'total_before_tax' => 200000,
            'final_total' => 200000,
            'transaction_date' => now(),
            'created_by' => 1,
        ]);

        TransactionSellLine::create([
            'transaction_id' => $sellTransaction->id,
            'product_id' => $productA->id,
            'variation_id' => $variationA->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'unit_price_inc_tax' => 100000,
        ]);

        // 6. Execute Intercompany Sync
        $intercompanyUtil = new IntercompanyUtil();
        $purchaseTransaction = $intercompanyUtil->syncSellToPurchase($sellTransaction);

        // Assert purchase transaction created in Business B
        $this->assertNotNull($purchaseTransaction);
        $this->assertEquals($businessB->id, $purchaseTransaction->business_id);
        $this->assertEquals('purchase', $purchaseTransaction->type);
        $this->assertEquals('ordered', $purchaseTransaction->status); // Draft / Pending until confirmed
        $this->assertEquals(200000, $purchaseTransaction->final_total);

        // Assert purchase line created with unit purchase price = 100,000
        $purchaseLine = PurchaseLine::where('transaction_id', $purchaseTransaction->id)->first();
        $this->assertNotNull($purchaseLine);
        $this->assertEquals($variationB->id, $purchaseLine->variation_id);
        $this->assertEquals(2, $purchaseLine->quantity);
        $this->assertEquals(100000, $purchaseLine->purchase_price);
    }
}
