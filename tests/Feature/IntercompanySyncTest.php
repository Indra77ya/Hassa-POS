<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessIntercompanyLink;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\User;
use App\Utils\IntercompanyUtil;
use App\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntercompanySyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed currency if needed
        \DB::table('currencies')->insertOrIgnore([
            'id' => 1,
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function it_creates_intercompany_link_and_syncs_sales_to_purchase()
    {
        // Business A
        $business_a = Business::create([
            'name' => 'Business A',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'owner_id' => 1,
        ]);
        $location_a = BusinessLocation::create([
            'business_id' => $business_a->id,
            'name' => 'Location A',
            'is_active' => 1,
        ]);

        // Business B
        $business_b = Business::create([
            'name' => 'Business B',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'owner_id' => 2,
        ]);
        $location_b = BusinessLocation::create([
            'business_id' => $business_b->id,
            'name' => 'Location B',
            'is_active' => 1,
        ]);

        // Customer in A representing B
        $contact_in_a = Contact::create([
            'business_id' => $business_a->id,
            'type' => 'customer',
            'name' => 'Business B Customer',
            'contact_id' => 'CNT-B',
            'created_by' => 1,
        ]);

        // Supplier in B representing A
        $contact_in_b = Contact::create([
            'business_id' => $business_b->id,
            'type' => 'supplier',
            'name' => 'Business A Supplier',
            'contact_id' => 'CNT-A',
            'created_by' => 2,
        ]);

        // Link A and B
        $link = BusinessIntercompanyLink::create([
            'business_id' => $business_a->id,
            'linked_business_id' => $business_b->id,
            'contact_id' => $contact_in_a->id,
            'linked_contact_id' => $contact_in_b->id,
        ]);

        // Create matching product in both businesses by SKU
        $sku = 'PROD-IC-001';

        $product_a = Product::create([
            'name' => 'Item Alpha',
            'business_id' => $business_a->id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => $sku,
            'created_by' => 1,
        ]);
        $variation_a = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product_a->id,
            'sub_sku' => $sku,
            'default_sell_price' => 100000,
            'sell_price_inc_tax' => 100000,
        ]);

        $product_b = Product::create([
            'name' => 'Item Alpha',
            'business_id' => $business_b->id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => $sku,
            'created_by' => 2,
        ]);
        $variation_b = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product_b->id,
            'sub_sku' => $sku,
            'default_purchase_price' => 0,
            'dpp_inc_tax' => 0,
        ]);

        // Create sale transaction in Business A selling to Contact in A (Business B)
        $sell = Transaction::create([
            'business_id' => $business_a->id,
            'location_id' => $location_a->id,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'due',
            'contact_id' => $contact_in_a->id,
            'invoice_no' => 'INV-A-1001',
            'transaction_date' => '2026-09-25 10:00:00',
            'total_before_tax' => 200000,
            'final_total' => 200000,
            'created_by' => 1,
        ]);

        TransactionSellLine::create([
            'transaction_id' => $sell->id,
            'product_id' => $product_a->id,
            'variation_id' => $variation_a->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'unit_price_inc_tax' => 100000,
            'unit_price_before_discount' => 100000,
        ]);

        // Execute Intercompany sync
        IntercompanyUtil::syncSellToPurchase($sell);

        // Assert Purchase in Business B was created automatically
        $purchase = Transaction::where('business_id', $business_b->id)
            ->where('intercompany_linked_transaction_id', $sell->id)
            ->first();

        $this->assertNotNull($purchase);
        $this->assertEquals('purchase', $purchase->type);
        $this->assertEquals('ordered', $purchase->status);
        $this->assertEquals(200000, $purchase->final_total);

        $purchase_line = PurchaseLine::where('transaction_id', $purchase->id)->first();
        $this->assertNotNull($purchase_line);
        $this->assertEquals($product_b->id, $purchase_line->product_id);
        $this->assertEquals(2, $purchase_line->quantity);
        $this->assertEquals(100000, $purchase_line->purchase_price);
    }

    /** @test */
    public function it_allows_authorized_users_and_admin_to_switch_business()
    {
        $admin_user = User::create([
            'surname' => 'Super',
            'first_name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => \Hash::make('secret'),
            'user_type' => 'user',
            'business_id' => 1,
        ]);

        $business_1 = Business::create([
            'name' => 'Company One',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'owner_id' => $admin_user->id,
        ]);

        $business_2 = Business::create([
            'name' => 'Company Two',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'owner_id' => $admin_user->id,
        ]);

        config(['constants.administrator_usernames' => 'admin']);

        $this->actingAs($admin_user);

        $response = $this->get(route('user.switchBusiness', $business_2->id));
        $response->assertRedirect('/home');
        $this->assertEquals($business_2->id, session('business.id'));
    }
}
