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
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\AdminSidebarMenu::class);
        $this->withoutMiddleware(\App\Http\Middleware\IsInstalled::class);
        $this->withoutMiddleware(\App\Http\Middleware\SetSessionData::class);

        \Illuminate\Support\Facades\Schema::dropIfExists('system');
        \Illuminate\Support\Facades\Schema::dropIfExists('users');
        \Illuminate\Support\Facades\Schema::dropIfExists('business');
        \Illuminate\Support\Facades\Schema::dropIfExists('business_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('contacts');
        \Illuminate\Support\Facades\Schema::dropIfExists('transaction_sell_lines');
        \Illuminate\Support\Facades\Schema::dropIfExists('transactions');
        \Illuminate\Support\Facades\Schema::dropIfExists('transaction_payments');
        \Illuminate\Support\Facades\Schema::dropIfExists('reference_counts');
        \Illuminate\Support\Facades\Schema::dropIfExists('invoice_schemes');

        \Illuminate\Support\Facades\Schema::create('system', function ($table) {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        \App\System::create([
            'key' => 'db_version',
            'value' => config('author.app_version'),
        ]);

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('business', function ($table) {
            $table->id();
            $table->integer('owner_id')->nullable();
            $table->string('name')->default('Test');
            $table->text('pos_settings')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('business_locations', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('name')->default('Main Shop');
            $table->string('location_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('contacts', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('type')->default('customer');
            $table->string('name');
            $table->decimal('balance', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transaction_sell_lines', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(1);
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->decimal('unit_price_before_discount', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transactions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->default('sell');
            $table->string('status')->default('final');
            $table->string('payment_status')->default('due');
            $table->integer('contact_id')->nullable();
            $table->unsignedBigInteger('laundry_order_sheet_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->string('sub_type')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transaction_payments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->default('cash');
            $table->boolean('is_return')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('reference_counts', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('ref_type');
            $table->integer('ref_count')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('invoice_schemes', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('name')->default('Default');
            $table->string('scheme_type')->default('blank');
            $table->string('prefix')->default('INV');
            $table->string('number_type')->default('sequential');
            $table->integer('start_number')->default(1);
            $table->integer('invoice_count')->default(0);
            $table->integer('total_digits')->default(4);
            $table->boolean('is_default')->default(1);
            $table->timestamps();
        });

        \App\InvoiceScheme::create([
            'business_id' => 1,
            'name' => 'Default Scheme',
            'is_default' => 1,
            'number_type' => 'sequential',
            'total_digits' => 4,
            'start_number' => 1,
            'invoice_count' => 0,
        ]);
    }

    /** @test */
    public function client_side_sync_payment_finalizes_transaction_and_creates_payment_line()
    {
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->status = 'active';
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasPermissionTo')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $business = Business::create([
            'id' => 1,
            'owner_id' => $user->id,
            'pos_settings' => json_encode([
                'enable_midtrans' => '1',
                'midtrans_server_key' => 'SB-Mid-server-test',
                'midtrans_client_key' => 'SB-Mid-client-test',
                'midtrans_mode' => 'sandbox',
            ]),
        ]);

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
        session(['user.business_id' => $business->id, 'user.id' => $user->id]);

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
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->status = 'active';
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasPermissionTo')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $business = Business::create(['id' => 1, 'owner_id' => $user->id]);

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
        session(['user.business_id' => $business->id, 'user.id' => $user->id]);

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
