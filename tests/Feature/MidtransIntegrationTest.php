<?php

namespace Tests\Feature;

use App\Business;
use App\Contact;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable middleware for tests where needed
        $this->withoutMiddleware(\App\Http\Middleware\AdminSidebarMenu::class);
    }

    /** @test */
    public function business_settings_can_save_midtrans_credentials()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'owner_id' => $user->id,
            'pos_settings' => json_encode([]),
        ]);
        $user->business_id = $business->id;
        $user->save();

        $this->actingAs($user);

        $posSettingsData = [
            'pos_settings' => [
                'enable_midtrans' => '1',
                'midtrans_server_key' => 'SB-Mid-server-test123',
                'midtrans_client_key' => 'SB-Mid-client-test123',
                'midtrans_mode' => 'sandbox',
            ],
        ];

        $response = $this->post(action([\App\Http\Controllers\BusinessController::class, 'postBusinessSettings']), $posSettingsData);

        $business->refresh();
        $savedSettings = json_decode($business->pos_settings, true);

        $this->assertEquals('1', $savedSettings['enable_midtrans'] ?? null);
        $this->assertEquals('SB-Mid-server-test123', $savedSettings['midtrans_server_key'] ?? null);
        $this->assertEquals('SB-Mid-client-test123', $savedSettings['midtrans_client_key'] ?? null);
        $this->assertEquals('sandbox', $savedSettings['midtrans_mode'] ?? null);
    }

    /** @test */
    public function midtrans_controller_creates_snap_token()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'owner_id' => $user->id,
            'pos_settings' => json_encode([
                'enable_midtrans' => '1',
                'midtrans_server_key' => 'SB-Mid-server-dummy',
                'midtrans_client_key' => 'SB-Mid-client-dummy',
                'midtrans_mode' => 'sandbox',
            ]),
        ]);
        $user->business_id = $business->id;
        $user->save();

        $contact = Contact::create([
            'business_id' => $business->id,
            'type' => 'customer',
            'name' => 'John Doe',
            'mobile' => '08123456789',
            'created_by' => $user->id,
        ]);

        $transaction = Transaction::create([
            'business_id' => $business->id,
            'location_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'due',
            'contact_id' => $contact->id,
            'invoice_no' => 'INV-001',
            'transaction_date' => now(),
            'total_before_tax' => 100000,
            'final_total' => 100000,
            'created_by' => $user->id,
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'mock-snap-token-12345',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345',
            ], 200),
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('midtrans.create_snap_token', [$transaction->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'token' => 'mock-snap-token-12345',
            'client_key' => 'SB-Mid-client-dummy',
            'is_production' => false,
        ]);
    }

    /** @test */
    public function midtrans_webhook_notification_updates_transaction_payment()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'owner_id' => $user->id,
            'pos_settings' => json_encode([
                'enable_midtrans' => '1',
                'midtrans_server_key' => 'SB-Mid-server-secret',
                'midtrans_client_key' => 'SB-Mid-client-secret',
                'midtrans_mode' => 'sandbox',
            ]),
        ]);
        $user->business_id = $business->id;
        $user->save();

        $contact = Contact::create([
            'business_id' => $business->id,
            'type' => 'customer',
            'name' => 'Jane Doe',
            'created_by' => $user->id,
        ]);

        $transaction = Transaction::create([
            'business_id' => $business->id,
            'location_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'due',
            'contact_id' => $contact->id,
            'invoice_no' => 'INV-002',
            'transaction_date' => now(),
            'total_before_tax' => 150000,
            'final_total' => 150000,
            'created_by' => $user->id,
        ]);

        $orderId = 'MID-POS-' . $transaction->id . '-1600000000';
        $statusCode = '200';
        $grossAmount = '150000.00';
        $serverKey = 'SB-Mid-server-secret';
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $notificationPayload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'custom_field1' => (string) $transaction->id,
        ];

        $response = $this->postJson(route('midtrans.notification'), $notificationPayload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $transaction->refresh();
        $this->assertEquals('paid', $transaction->payment_status);
    }
}
