<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Superadmin\Entities\Package;
use Tests\TestCase;

class MidtransItemDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\AdminSidebarMenu::class);
    }

    /** @test */
    public function pos_create_snap_token_includes_item_details_and_handles_adjustments()
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'dummy-snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/dummy-snap-token-123',
            ], 200),
        ]);

        $user = User::factory()->create();
        $business = Business::factory()->create([
            'owner_id' => $user->id,
            'pos_settings' => json_encode([
                'enable_midtrans' => '1',
                'midtrans_server_key' => 'SB-Mid-server-test',
                'midtrans_client_key' => 'SB-Mid-client-test',
                'midtrans_mode' => 'sandbox',
            ]),
        ]);
        $user->business_id = $business->id;
        $user->save();

        $location = BusinessLocation::create([
            'business_id' => $business->id,
            'name' => 'Main Shop',
            'location_id' => 'LOC01',
        ]);

        $contact = Contact::create([
            'business_id' => $business->id,
            'type' => 'customer',
            'name' => 'Walk-In Customer',
            'created_by' => $user->id,
        ]);

        $product = Product::create([
            'name' => 'Kaos Polos',
            'business_id' => $business->id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => 'SKU-KAOS-001',
            'created_by' => $user->id,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => 'SKU-KAOS-001-RED',
            'default_sell_price' => 50000,
            'sell_price_inc_tax' => 50000,
        ]);

        $transaction = Transaction::create([
            'business_id' => $business->id,
            'location_id' => $location->id,
            'type' => 'sell',
            'status' => 'draft',
            'payment_status' => 'due',
            'contact_id' => $contact->id,
            'invoice_no' => 'DRAFT-1001',
            'transaction_date' => now(),
            'total_before_tax' => 100000,
            'discount_amount' => 10000, // 10k discount
            'final_total' => 90000, // Gross amount = 90k
            'created_by' => $user->id,
        ]);

        TransactionSellLine::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'unit_price_inc_tax' => 50000,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('midtrans.create_snap_token', [$transaction->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'token' => 'dummy-snap-token-123',
        ]);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $this->assertEquals(90000, $payload['transaction_details']['gross_amount']);
            $this->assertArrayHasKey('item_details', $payload);
            $this->assertCount(2, $payload['item_details']);

            // First item (Kaos Polos)
            $this->assertEquals('SKU-KAOS-001-RED', $payload['item_details'][0]['id']);
            $this->assertEquals('Kaos Polos', $payload['item_details'][0]['name']);
            $this->assertEquals(50000, $payload['item_details'][0]['price']);
            $this->assertEquals(2, $payload['item_details'][0]['quantity']);

            // Second item (Discount adjustment line)
            $this->assertEquals('ADJUSTMENT', $payload['item_details'][1]['id']);
            $this->assertEquals(-10000, $payload['item_details'][1]['price']);
            $this->assertEquals(1, $payload['item_details'][1]['quantity']);

            // Verify total item sum equals gross amount
            $sum = 0;
            foreach ($payload['item_details'] as $item) {
                $sum += ($item['price'] * $item['quantity']);
            }
            $this->assertEquals(90000, $sum);

            return true;
        });
    }

    /** @test */
    public function superadmin_create_snap_token_includes_package_item_details()
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'dummy-sub-snap-token-456',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/dummy-sub-snap-token-456',
            ], 200),
        ]);

        putenv('MIDTRANS_SERVER_KEY=SB-Mid-server-test');
        putenv('MIDTRANS_CLIENT_KEY=SB-Mid-client-test');
        putenv('MIDTRANS_MODE=sandbox');

        $user = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $user->id]);
        $user->business_id = $business->id;
        $user->save();

        $package = Package::create([
            'name' => 'Paket Premium Gold',
            'description' => 'Akses penuh',
            'location_count' => 5,
            'user_count' => 10,
            'product_count' => 100,
            'invoice_count' => 1000,
            'interval' => 'months',
            'interval_count' => 1,
            'trial_days' => 0,
            'price' => 250000,
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->withSession([
            'user' => ['business_id' => $business->id, 'id' => $user->id, 'first_name' => 'John'],
        ])->postJson('/subscription/' . $package->id . '/midtrans-create-snap-token');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'token' => 'dummy-sub-snap-token-456',
        ]);

        Http::assertSent(function ($request) use ($package) {
            $payload = $request->data();
            $this->assertEquals(250000, $payload['transaction_details']['gross_amount']);
            $this->assertArrayHasKey('item_details', $payload);
            $this->assertCount(1, $payload['item_details']);

            $this->assertEquals('PKG-' . $package->id, $payload['item_details'][0]['id']);
            $this->assertEquals('Paket: Paket Premium Gold', $payload['item_details'][0]['name']);
            $this->assertEquals(250000, $payload['item_details'][0]['price']);
            $this->assertEquals(1, $payload['item_details'][0]['quantity']);

            return true;
        });
    }
}
