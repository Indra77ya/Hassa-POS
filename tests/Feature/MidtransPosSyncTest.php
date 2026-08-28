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
use Tests\TestCase;

class MidtransPosSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\AdminSidebarMenu::class);
    }

    /** @test */
    public function client_side_sync_payment_finalizes_transaction_and_creates_payment_line()
    {
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

        $transaction = Transaction::create([
            'business_id' => $business->id,
            'location_id' => $location->id,
            'type' => 'sell',
            'status' => 'draft',
            'payment_status' => 'due',
            'contact_id' => $contact->id,
            'invoice_no' => 'DRAFT-001',
            'transaction_date' => now(),
            'total_before_tax' => 450000,
            'final_total' => 450000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('midtrans.sync_payment', [$transaction->id]), [
            'order_id' => 'MID-POS-17-1787936711',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_status' => 'paid',
        ]);

        $transaction->refresh();
        $this->assertEquals('final', $transaction->status);
        $this->assertEquals('paid', $transaction->payment_status);
        $this->assertCount(1, $transaction->payment_lines);
        $this->assertEquals('midtrans', $transaction->payment_lines->first()->method);
        $this->assertEquals(450000, $transaction->payment_lines->first()->amount);
    }

    /** @test */
    public function sync_payment_is_idempotent()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $user->id]);
        $user->business_id = $business->id;
        $user->save();

        $contact = Contact::create([
            'business_id' => $business->id,
            'type' => 'customer',
            'name' => 'Test Customer',
            'created_by' => $user->id,
        ]);

        $transaction = Transaction::create([
            'business_id' => $business->id,
            'location_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'due',
            'contact_id' => $contact->id,
            'invoice_no' => '0004',
            'transaction_date' => now(),
            'total_before_tax' => 100000,
            'final_total' => 100000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        // First call
        $this->postJson(route('midtrans.sync_payment', [$transaction->id]));
        $transaction->refresh();
        $this->assertCount(1, $transaction->payment_lines);

        // Second duplicate call (simulating simultaneous webhook or refresh)
        $this->postJson(route('midtrans.sync_payment', [$transaction->id]));
        $transaction->refresh();
        $this->assertCount(1, $transaction->payment_lines);
    }
}
