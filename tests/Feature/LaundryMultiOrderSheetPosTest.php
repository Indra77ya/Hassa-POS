<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Business;
use App\BusinessLocation;
use App\InvoiceScheme;
use App\User;
use App\Contact;
use App\CashRegister;
use App\Transaction;
use Modules\Laundry\Entities\LaundryOrderSheet;
use Modules\Laundry\Entities\LaundryItemType;

class LaundryMultiOrderSheetPosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('reference_counts');
        Schema::dropIfExists('transaction_sell_lines');
        Schema::dropIfExists('invoice_schemes');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('users');
        Schema::dropIfExists('business');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('cash_register_transactions');
        Schema::dropIfExists('transaction_payments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('laundry_order_sheets');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('laundry_statuses');
        Schema::dropIfExists('laundry_processes');
        Schema::dropIfExists('laundry_service_types');
        Schema::dropIfExists('laundry_order_process_logs');
        Schema::dropIfExists('laundry_item_types');
        Schema::dropIfExists('products');
        Schema::dropIfExists('business_locations');
        Schema::dropIfExists('product_locations');
        Schema::dropIfExists('variations');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('units');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('notifications');

        Schema::create('notifications', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->integer('business_id')->default(1);
            $table->timestamps();
        });

        Schema::create('model_has_roles', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('laundry_item_types', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->string('unit_name')->nullable();
            $table->decimal('default_price', 22, 4)->default(0);
            $table->text('description')->nullable();
            $table->text('process_ids')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('business_id');
            $table->integer('unit_id')->default(1);
            $table->string('type')->default('single');
            $table->boolean('enable_stock')->default(0);
            $table->decimal('alert_quantity', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->string('sku');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('business_locations', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name')->default('Main Location');
            $table->integer('invoice_scheme_id')->default(1);
            $table->integer('invoice_layout_id')->default(1);
            $table->timestamps();
        });

        Schema::create('product_locations', function ($table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id');
        });

        Schema::create('variations', function ($table) {
            $table->id();
            $table->string('name')->default('DUMMY');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variation_id')->default(1);
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_variations', function ($table) {
            $table->id();
            $table->string('name')->default('DUMMY');
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('units', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('contacts', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('type')->default('customer');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->integer('customer_group_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('laundry_order_sheets', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('order_no');
            $table->integer('contact_id');
            $table->integer('location_id')->default(1);
            $table->integer('laundry_status_id')->nullable();
            $table->integer('laundry_service_type_id')->nullable();
            $table->integer('laundry_item_type_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(1);
            $table->text('item_details')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('estimate_completion_at')->nullable();
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('business', function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('currency_id')->default(1);
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->string('accounting_method')->default('fifo');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('24');
            $table->decimal('default_sales_discount', 5, 2)->default(0);
            $table->integer('default_sales_tax')->nullable();
            $table->text('keyboard_shortcuts')->nullable();
            $table->text('pos_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_groups', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 5, 2);
            $table->timestamps();
        });

        Schema::create('cash_registers', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id');
            $table->integer('user_id');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('cash_register_transactions', function ($table) {
            $table->id();
            $table->integer('cash_register_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('pay_method')->default('cash');
            $table->string('type')->default('credit');
            $table->string('transaction_type')->default('sell');
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id');
            $table->string('type')->default('sell');
            $table->string('status')->default('final');
            $table->string('payment_status')->default('due');
            $table->integer('contact_id');
            $table->integer('laundry_order_sheet_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->dateTime('transaction_date');
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->string('discount_type')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->boolean('is_direct_sale')->default(0);
            $table->boolean('is_suspend')->default(0);
            $table->string('sub_type')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_sell_lines', function ($table) {
            $table->id();
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4);
            $table->decimal('unit_price', 22, 4);
            $table->decimal('unit_price_inc_tax', 22, 4);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->integer('tax_id')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_payments', function ($table) {
            $table->id();
            $table->integer('transaction_id');
            $table->decimal('amount', 22, 4);
            $table->string('method');
            $table->string('payment_ref_no')->nullable();
            $table->dateTime('paid_on');
            $table->integer('created_by')->default(1);
            $table->boolean('is_return')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_log', function ($table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->text('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_templates', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('template_for');
            $table->text('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->string('subject')->nullable();
            $table->boolean('auto_send')->default(0);
            $table->boolean('auto_send_sms')->default(0);
            $table->boolean('auto_send_wa_notif')->default(0);
            $table->timestamps();
        });

        Schema::create('tax_rates', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4)->default(0);
            $table->boolean('is_tax_group')->default(0);
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('currencies', function ($table) {
            $table->id();
            $table->string('country');
            $table->string('currency');
            $table->string('code');
            $table->string('symbol');
            $table->string('thousand_separator');
            $table->string('decimal_separator');
            $table->timestamps();
        });

        Schema::create('reference_counts', function ($table) {
            $table->id();
            $table->string('ref_type');
            $table->integer('ref_count');
            $table->integer('business_id');
            $table->timestamps();
        });

        Schema::create('invoice_schemes', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->string('scheme_type')->default('blank');
            $table->string('number_type')->default('sequential');
            $table->integer('total_digits')->default(4);
            $table->string('prefix')->nullable();
            $table->integer('start_number')->default(1);
            $table->integer('invoice_count')->default(0);
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });

        \DB::table('currencies')->insert([
            'id' => 1,
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
        ]);

        \DB::table('units')->insert([
            'id' => 1,
            'business_id' => 1,
            'actual_name' => 'Pieces',
            'short_name' => 'Pcs',
        ]);
    }

    public function test_multi_laundry_order_sheet_pos_checkout_updates_all_order_sheets()
    {
        $business = Business::create([
            'name' => 'Laundry Multi POS Business',
            'time_zone' => 'Asia/Jakarta',
            'accounting_method' => 'fifo',
            'date_format' => 'Y-m-d',
            'time_format' => '24',
            'keyboard_shortcuts' => '{}',
            'pos_settings' => json_encode(['enable_midtrans' => 0]),
        ]);

        InvoiceScheme::create([
            'id' => 1,
            'business_id' => $business->id,
            'name' => 'Default Scheme',
            'scheme_type' => 'blank',
            'number_type' => 'sequential',
            'total_digits' => 4,
            'start_number' => 1,
            'invoice_count' => 0,
            'is_default' => 1,
        ]);

        BusinessLocation::create([
            'id' => 1,
            'business_id' => $business->id,
            'name' => 'Main Location',
            'invoice_scheme_id' => 1,
            'invoice_layout_id' => 1,
        ]);

        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->business_id = $business->id;
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasPermissionTo')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->actingAs($user);

        session([
            'user.business_id' => $business->id,
            'user.id' => $user->id,
            'business' => $business,
        ]);

        CashRegister::create([
            'business_id' => $business->id,
            'location_id' => 1,
            'user_id' => $user->id,
            'status' => 'open',
        ]);

        $contact = Contact::create([
            'business_id' => $business->id,
            'type' => 'customer',
            'name' => 'Kak Rendi Jaya',
        ]);

        $item_type_bedcover = LaundryItemType::create([
            'business_id' => $business->id,
            'name' => 'Bedcover',
            'default_price' => 40000,
        ]);

        $item_type_sepatu = LaundryItemType::create([
            'business_id' => $business->id,
            'name' => 'Sepatu',
            'default_price' => 25000,
        ]);

        // Order Sheet 1 (due 40,000)
        $os1 = LaundryOrderSheet::create([
            'business_id' => $business->id,
            'order_no' => 'LND-2026-0001',
            'contact_id' => $contact->id,
            'location_id' => 1,
            'laundry_item_type_id' => $item_type_bedcover->id,
            'quantity' => 1,
        ]);

        // Order Sheet 2 (due 25,000)
        $os2 = LaundryOrderSheet::create([
            'business_id' => $business->id,
            'order_no' => 'LND-2026-0002',
            'contact_id' => $contact->id,
            'location_id' => 1,
            'laundry_item_type_id' => $item_type_sepatu->id,
            'quantity' => 1,
        ]);

        $this->assertEquals('due', $os1->payment_status);
        $this->assertEquals('due', $os2->payment_status);

        // POS checkout containing both order sheet IDs
        $input = [
            'location_id' => 1,
            'is_direct_sale' => 0,
            'contact_id' => $contact->id,
            'laundry_order_sheet_id' => [$os1->id, $os2->id],
            'status' => 'final',
            'final_total' => 65000,
            'discount_type' => 'fixed',
            'discount_amount' => 0,
            'tax_rate_id' => null,
            'products' => [
                [
                    'product_id' => 1,
                    'variation_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 40000,
                    'unit_price_inc_tax' => 40000,
                    'item_tax' => 0,
                    'tax_id' => null,
                    'enable_stock' => 0,
                    'product_type' => 'single',
                    'laundry_order_sheet_id' => $os1->id,
                ],
                [
                    'product_id' => 2,
                    'variation_id' => 2,
                    'quantity' => 1,
                    'unit_price' => 25000,
                    'unit_price_inc_tax' => 25000,
                    'item_tax' => 0,
                    'tax_id' => null,
                    'enable_stock' => 0,
                    'product_type' => 'single',
                    'laundry_order_sheet_id' => $os2->id,
                ],
            ],
            'payment' => [
                [
                    'method' => 'cash',
                    'amount' => 65000,
                ],
            ],
        ];

        $request = \Illuminate\Http\Request::create('/pos', 'POST', $input);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $controller = app(\App\Http\Controllers\SellPosController::class);
        $res = $controller->store($request);

        $this->assertEquals(1, $res['success']);

        // Refresh Eloquent models
        $os1_fresh = LaundryOrderSheet::find($os1->id);
        $os2_fresh = LaundryOrderSheet::find($os2->id);

        $this->assertEquals(40000, $os1_fresh->total_paid);
        $this->assertEquals('paid', $os1_fresh->payment_status);

        $this->assertEquals(25000, $os2_fresh->total_paid);
        $this->assertEquals('paid', $os2_fresh->payment_status);
    }
}
