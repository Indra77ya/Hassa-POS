<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Laundry\Entities\LaundryStatus;
use Modules\Laundry\Entities\LaundryProcess;
use Modules\Laundry\Entities\LaundryServiceType;
use Modules\Laundry\Entities\LaundryItemType;

class LaundryModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::dropIfExists('activity_log');
        \Illuminate\Support\Facades\Schema::dropIfExists('notification_templates');
        \Illuminate\Support\Facades\Schema::dropIfExists('tax_rates');
        \Illuminate\Support\Facades\Schema::dropIfExists('currencies');
        \Illuminate\Support\Facades\Schema::dropIfExists('reference_counts');
        \Illuminate\Support\Facades\Schema::dropIfExists('transaction_sell_lines');
        \Illuminate\Support\Facades\Schema::dropIfExists('invoice_schemes');
        \Illuminate\Support\Facades\Schema::dropIfExists('customer_groups');
        \Illuminate\Support\Facades\Schema::dropIfExists('users');
        \Illuminate\Support\Facades\Schema::dropIfExists('business');
        \Illuminate\Support\Facades\Schema::dropIfExists('cash_registers');
        \Illuminate\Support\Facades\Schema::dropIfExists('cash_register_transactions');
        \Illuminate\Support\Facades\Schema::dropIfExists('transaction_payments');
        \Illuminate\Support\Facades\Schema::dropIfExists('transactions');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_sheets');
        \Illuminate\Support\Facades\Schema::dropIfExists('contacts');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_statuses');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_processes');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_service_types');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_process_logs');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_item_types');
        \Illuminate\Support\Facades\Schema::dropIfExists('products');
        \Illuminate\Support\Facades\Schema::dropIfExists('business_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('units');
        \Illuminate\Support\Facades\Schema::dropIfExists('permissions');
        \Illuminate\Support\Facades\Schema::dropIfExists('roles');
        \Illuminate\Support\Facades\Schema::dropIfExists('model_has_roles');
        \Illuminate\Support\Facades\Schema::dropIfExists('notifications');

        \Illuminate\Support\Facades\Schema::create('notifications', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->integer('business_id')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('model_has_roles', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        \Illuminate\Support\Facades\Schema::create('model_has_permissions', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        \Illuminate\Support\Facades\Schema::create('role_has_permissions', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        \Illuminate\Support\Facades\Schema::create('laundry_statuses', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->string('color')->default('#3c8dbc');
            $table->integer('sort_order')->default(1);
            $table->boolean('is_completed_status')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_processes', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('points', 8, 2)->default(0);
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_service_types', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->integer('completion_hours')->default(24);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_order_process_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_sheet_id');
            $table->unsignedBigInteger('laundry_process_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('points_earned', 8, 2)->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_item_types', function ($table) {
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

        \Illuminate\Support\Facades\Schema::create('products', function ($table) {
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

        \Illuminate\Support\Facades\Schema::create('business_locations', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name')->default('Main Location');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('product_locations', function ($table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id');
        });

        \Illuminate\Support\Facades\Schema::create('variations', function ($table) {
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

        \Illuminate\Support\Facades\Schema::create('product_variations', function ($table) {
            $table->id();
            $table->string('name')->default('DUMMY');
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();
        });


        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('units', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('contacts', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('type')->default('customer');
            $table->string('name');
            $table->integer('customer_group_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_order_sheets', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('order_no');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('laundry_item_type_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(1.00);
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

        \Illuminate\Support\Facades\Schema::create('transaction_payments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->default('cash');
            $table->boolean('is_return')->default(0);
            $table->timestamps();
        });

        \App\Unit::create([
            'id' => 1,
            'business_id' => 1,
            'actual_name' => 'kg',
            'short_name' => 'kg',
        ]);

        \Illuminate\Support\Facades\Schema::create('currencies', function ($table) {
            $table->id();
            $table->string('country')->default('Indonesia');
            $table->string('currency')->default('Rupiah');
            $table->string('code')->default('IDR');
            $table->string('symbol')->default('Rp');
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('subscriptions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('package_id')->default(1);
            $table->text('package_details');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('approved');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('currencies')->insert([
            'id' => 1,
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
        ]);

        \Illuminate\Support\Facades\Schema::create('business', function ($table) {
            $table->id();
            $table->string('name')->default('Test Business');
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->string('accounting_method')->default('fifo');
            $table->text('keyboard_shortcuts')->nullable();
            $table->text('pos_settings')->nullable();
            $table->text('laundry_settings')->nullable();
            $table->boolean('enable_rp')->default(0);
            $table->string('sales_cmsn_agnt')->nullable();
            $table->decimal('default_sales_discount', 5, 2)->nullable();
            $table->integer('default_sales_tax')->nullable();
            $table->integer('currency_id')->nullable();
            $table->timestamps();
        });

        \App\Business::create([
            'id' => 1,
            'name' => 'Test Business',
            'currency_id' => 1,
        ]);

        \Illuminate\Support\Facades\Schema::create('tax_rates', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('name')->default('VAT');
            $table->decimal('amount', 22, 4)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('notification_templates', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('template_for');
            $table->text('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->text('whatsapp_text')->nullable();
            $table->boolean('auto_send')->default(0);
            $table->boolean('auto_send_sms')->default(0);
            $table->boolean('auto_send_wa_notif')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('activity_log', function ($table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->text('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('customer_groups', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('name')->default('Default');
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

        \Illuminate\Support\Facades\Schema::create('cash_registers', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->integer('location_id')->default(1);
            $table->integer('user_id')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('cash_register_transactions', function ($table) {
            $table->id();
            $table->integer('cash_register_id');
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('pay_method')->default('cash');
            $table->string('type')->default('credit');
            $table->string('transaction_type')->default('sell');
            $table->timestamps();
        });

        \App\BusinessLocation::create([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Main Branch',
        ]);

        app()->register(\Modules\Laundry\Providers\RouteServiceProvider::class);
    }

    public function test_laundry_entities_and_points_calculation()
    {
        $status = new LaundryStatus([
            'business_id' => 1,
            'name' => 'Diterima',
            'color' => '#3c8dbc',
            'sort_order' => 1,
            'is_completed_status' => false,
        ]);

        $process = new LaundryProcess([
            'business_id' => 1,
            'name' => 'Pencucian',
            'points' => 2.5,
            'sort_order' => 1,
        ]);

        $service_type = new LaundryServiceType([
            'business_id' => 1,
            'name' => 'Express 1 Hari',
            'completion_hours' => 24,
        ]);

        $item_type = new LaundryItemType([
            'business_id' => 1,
            'name' => 'Pakaian Kiloan',
            'unit_name' => 'kg',
            'default_price' => 10000,
        ]);

        $this->assertEquals('Diterima', $status->name);
        $this->assertEquals(2.5, $process->points);
        $this->assertEquals(24, $service_type->completion_hours);
        $this->assertEquals('Pakaian Kiloan', $item_type->name);

        // Test staff points formula: Points = Process Points * Quantity
        $quantity = 4.0; // 4 kg
        $points_earned = $process->points * $quantity;
        $this->assertEquals(10.0, $points_earned);
    }

    public function test_laundry_item_type_auto_creates_and_syncs_product()
    {
        $business_id = 1;

        $session_mock = \Mockery::mock();
        $session_mock->shouldReceive('get')->with('user.business_id')->andReturn($business_id);
        $session_mock->shouldReceive('get')->with('user.id')->andReturn(1);

        $request_mock = \Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
        $request_mock->shouldReceive('session')->andReturn($session_mock);
        $request_mock->shouldReceive('only')
            ->with(['name', 'unit_name', 'default_price', 'description', 'process_ids'])
            ->andReturn([
                'name' => 'Cuci Karpet Spesial',
                'unit_name' => 'm2',
                'default_price' => 25000,
                'description' => 'Layanan Cuci Karpet',
                'process_ids' => [],
            ]);

        $controller = new \Modules\Laundry\Http\Controllers\LaundryItemTypeController();
        $response = $controller->store($request_mock);

        if (!$response['success']) {
            $this->fail('Store failed: ' . ($response['msg'] ?? ''));
        }

        $this->assertTrue($response['success']);

        $item_type = LaundryItemType::where('business_id', $business_id)
            ->where('name', 'Cuci Karpet Spesial')
            ->first();

        $this->assertNotNull($item_type);

        $product = \App\Product::where('business_id', $business_id)
            ->where('name', 'Cuci Karpet Spesial')
            ->first();

        $this->assertNotNull($product);

        $variation = \App\Variation::where('product_id', $product->id)->first();
        $this->assertNotNull($variation);
        $this->assertEquals(25000, $variation->default_sell_price);

        $item_type = LaundryItemType::where('business_id', $business_id)
            ->where('name', 'Cuci Karpet Spesial')
            ->first();

        $this->assertNotNull($item_type);

        // Test update sync
        $update_request_mock = \Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
        $update_request_mock->shouldReceive('session')->andReturn($session_mock);
        $update_request_mock->shouldReceive('only')
            ->with(['name', 'unit_name', 'default_price', 'description', 'process_ids'])
            ->andReturn([
                'name' => 'Cuci Karpet Spesial Updated',
                'unit_name' => 'm2',
                'default_price' => 30000,
                'description' => 'Layanan Cuci Karpet Premium',
                'process_ids' => [],
            ]);

        $update_response = $controller->update($update_request_mock, $item_type->id);
        $this->assertTrue($update_response['success']);

        $product->refresh();
        $variation->refresh();

        $this->assertEquals('Cuci Karpet Spesial Updated', $product->name);
        $this->assertEquals(30000, $variation->default_sell_price);
    }

    public function test_laundry_pos_header_view_rendering()
    {
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $this->actingAs($user);

        $view = view('laundry::layouts.partials.pos_header', [
            '__is_laundry_enabled' => true,
            'transaction_sub_type' => '',
        ])->render();

        $this->assertStringContainsString('sub_type=laundry', $view);
        $this->assertStringContainsString('fa-tshirt', $view);
    }

    public function test_laundry_pos_input_group_view_rendering()
    {
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        $view = view('laundry::laundry.partials.laundry_pos', [
            'order_sheets' => [1 => 'LND-2026-0001'],
        ])->render();

        $this->assertStringContainsString('laundry_order_sheet_id', $view);
        $this->assertStringContainsString('add_laundry_order_sheet_quick_btn', $view);
        $this->assertStringContainsString('edit_laundry_order_sheet_btn', $view);
        $this->assertStringContainsString('show_laundry_order_sheet_btn', $view);
        $this->assertStringContainsString('add_laundry_to_cart_btn', $view);
    }

    public function test_get_order_sheets_endpoint_by_customer()
    {
        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Lipat Kiloan',
            'default_price' => 15000,
        ]);

        \App\Contact::create([
            'id' => 10,
            'business_id' => 1,
            'type' => 'customer',
            'name' => 'Budi Santoso',
        ]);

        $os1 = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-2026-0001',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 2,
        ]);

        $os2 = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-2026-0002',
            'contact_id' => 20,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 2,
        ]);

        session(['user.business_id' => 1, 'user.id' => 1]);

        $request = \Illuminate\Http\Request::create('/laundry/order-sheet/get-order-sheets', 'GET', ['contact_id' => 10]);
        $request->merge(['contact_id' => 10]);
        $request->setLaravelSession(app('session.store'));

        $controller = new \Modules\Laundry\Http\Controllers\OrderSheetController();
        $response = $controller->getOrderSheets($request);

        $data = $response->getData(true);
        if (empty($data['success'])) {
            $this->fail('getOrderSheets failed: ' . ($data['msg'] ?? ''));
        }
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey($os1->id, $data['order_sheets']);
        $this->assertArrayNotHasKey($os2->id, $data['order_sheets']);
    }

    public function test_order_sheet_payment_status_and_totals()
    {
        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Lipat',
            'default_price' => 20000,
        ]);

        $os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-2026-0005',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 2, // Total = 40,000
        ]);

        $this->assertEquals(40000, $os->total_amount);
        $this->assertEquals(0, $os->total_paid);
        $this->assertEquals('due', $os->payment_status);

        // Add partial payment of 15,000
        $tx = \App\Transaction::create([
            'business_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'laundry_order_sheet_id' => $os->id,
            'final_total' => 40000,
        ]);

        \App\TransactionPayment::create([
            'transaction_id' => $tx->id,
            'amount' => 15000,
            'method' => 'cash',
        ]);

        $os->refresh();
        $this->assertEquals(15000, $os->total_paid);
        $this->assertEquals('partial', $os->payment_status);

        // Add remaining payment of 25,000
        \App\TransactionPayment::create([
            'transaction_id' => $tx->id,
            'amount' => 25000,
            'method' => 'cash',
        ]);

        $os->refresh();
        $this->assertEquals(40000, $os->total_paid);
        $this->assertEquals('paid', $os->payment_status);
    }

    public function test_get_order_sheets_includes_paid_and_unpaid_sheets()
    {
        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Setrika',
            'default_price' => 10000,
        ]);

        $unpaid_os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-UNPAID',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 1,
        ]);

        $paid_os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-PAID',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 1,
        ]);

        $tx = \App\Transaction::create([
            'business_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'laundry_order_sheet_id' => $paid_os->id,
            'final_total' => 10000,
        ]);

        \App\TransactionPayment::create([
            'transaction_id' => $tx->id,
            'amount' => 10000,
            'method' => 'cash',
        ]);

        session(['user.business_id' => 1, 'user.id' => 1]);

        $request = \Illuminate\Http\Request::create('/laundry/order-sheet/get-order-sheets', 'GET', ['contact_id' => 10]);
        $request->merge(['contact_id' => 10]);
        $request->setLaravelSession(app('session.store'));

        $controller = new \Modules\Laundry\Http\Controllers\OrderSheetController();
        $response = $controller->getOrderSheets($request);

        $data = $response->getData(true);
        $this->assertTrue($data['success'], $data['msg'] ?? 'no msg');
        $this->assertArrayHasKey($unpaid_os->id, $data['order_sheets']);
        $this->assertArrayHasKey($paid_os->id, $data['order_sheets']);
    }

    public function test_get_pos_details_returns_payment_status_and_due_amount()
    {
        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Sepatu / Sneaker',
            'default_price' => 30000,
        ]);

        $os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-DEMO-0003',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 2, // Total = 60,000
        ]);

        $tx = \App\Transaction::create([
            'business_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'partial',
            'laundry_order_sheet_id' => $os->id,
            'final_total' => 60000,
        ]);

        // Partial payment of 30,000
        \App\TransactionPayment::create([
            'transaction_id' => $tx->id,
            'amount' => 30000,
            'method' => 'cash',
        ]);

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $this->actingAs($user);

        session(['user.business_id' => 1, 'user.id' => 1]);

        $request = \Illuminate\Http\Request::create('/laundry/order-sheet/' . $os->id . '/get-pos-details', 'GET');
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $controller = new \Modules\Laundry\Http\Controllers\OrderSheetController();
        $response = $controller->getPosDetails($os->id);

        $data = $response->getData(true);
        $this->assertTrue($data['success'], $data['msg'] ?? 'no msg');
        $this->assertEquals('partial', $data['payment_status']);
        $this->assertEquals(60000, $data['total_amount']);
        $this->assertEquals(30000, $data['total_paid']);
        $this->assertEquals(30000, $data['due_amount']);
    }

    public function test_view_payments_returns_transaction_payments()
    {
        $contact = \App\Contact::create([
            'business_id' => 1,
            'type' => 'customer',
            'name' => 'Test Customer',
            'created_by' => 1,
        ]);

        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Gorden',
            'default_price' => 40000,
        ]);

        $os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-MULTITX-01',
            'contact_id' => $contact->id,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 1, // Total = 40,000
        ]);

        // Transaction 1: Direct payment (Cash Rp 10,000)
        $tx1 = \App\Transaction::create([
            'business_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'partial',
            'contact_id' => $contact->id,
            'laundry_order_sheet_id' => $os->id,
            'final_total' => 40000,
        ]);

        $p1 = \App\TransactionPayment::create([
            'transaction_id' => $tx1->id,
            'amount' => 10000,
            'method' => 'cash',
        ]);

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->andReturn(true);
        $this->actingAs($user);

        session(['user.business_id' => 1, 'user.id' => 1]);

        $request = \Illuminate\Http\Request::create('/laundry/order-sheet/' . $os->id . '/view-payments', 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $controller = new \Modules\Laundry\Http\Controllers\OrderSheetController();
        $response = $controller->viewPayments($os->id);

        $view_data = $response->getData();
        $this->assertArrayHasKey('payments', $view_data);
        $payments = $view_data['payments'];
        $this->assertCount(1, $payments);
    }

    public function test_public_status_tracking_page_accessible_by_guest()
    {
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Express',
            'unit_name' => 'kg',
            'default_price' => 15000,
        ]);

        $os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-2026-0004',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 3.5,
            'unit_name' => 'kg',
        ]);

        // Test GET status with valid order_no without session/auth
        $response = $this->get('/laundry/status/LND-2026-0004');
        $response->assertStatus(200);
        $response->assertSee('LND-2026-0004');
        $response->assertSee('3.50 kg');

        // Test POST search
        $searchResponse = $this->post('/laundry/status/search', [
            'search_key' => 'LND-2026-0004',
        ]);
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('LND-2026-0004');

        // Test GET status with non-existent order_no
        $notFoundResponse = $this->get('/laundry/status/NONEXISTENT');
        $notFoundResponse->assertStatus(200);
    }

    public function test_laundry_pos_multiple_payments_reuses_single_transaction_and_invoice()
    {
        $contact = \App\Contact::create([
            'business_id' => 1,
            'type' => 'customer',
            'name' => 'Rudi',
            'created_by' => 1,
        ]);

        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Laundry Kiloan Rudi',
            'default_price' => 60000,
        ]);

        $os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-RUDI-01',
            'contact_id' => $contact->id,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 1, // Total = 60,000
        ]);

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->id = 1;
        $user->business_id = 1;
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasPermissionTo')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->actingAs($user);

        $biz = \App\Business::find(1);
        $biz->accounting_method = 'fifo';
        $biz->keyboard_shortcuts = '{}';
        $biz->pos_settings = json_encode(['enable_midtrans' => 1, 'midtrans_server_key' => 'SB-Mid-server-123', 'midtrans_client_key' => 'SB-Mid-client-123']);
        $biz->save();

        session([
            'user.business_id' => 1,
            'user.id' => 1,
            'business' => $biz,
        ]);

        \App\CashRegister::create([
            'business_id' => 1,
            'location_id' => 1,
            'user_id' => $user->id,
            'status' => 'open',
        ]);

        // Payment 1: Rudi pays Rp 30,000 Cash via POS store
        $input1 = [
            'location_id' => 1,
            'is_direct_sale' => 0,
            'contact_id' => $contact->id,
            'laundry_order_sheet_id' => $os->id,
            'status' => 'final',
            'final_total' => 60000,
            'discount_type' => 'fixed',
            'discount_amount' => 0,
            'tax_rate_id' => null,
            'products' => [
                [
                    'product_id' => 1,
                    'variation_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 60000,
                    'unit_price_inc_tax' => 60000,
                    'item_tax' => 0,
                    'tax_id' => null,
                    'enable_stock' => 0,
                    'product_type' => 'single',
                ],
            ],
            'payment' => [
                [
                    'method' => 'cash',
                    'amount' => 30000,
                ],
            ],
        ];

        $request1 = \Illuminate\Http\Request::create('/pos', 'POST', $input1);
        $request1->setLaravelSession(app('session.store'));
        app()->instance('request', $request1);

        $controller = app(\App\Http\Controllers\SellPosController::class);
        $res1 = $controller->store($request1);

        $this->assertEquals(1, $res1['success']);
        $tx_id1 = $res1['transaction_id'];

        // Verify only 1 transaction created for this order sheet
        $tx_count1 = \App\Transaction::where('laundry_order_sheet_id', $os->id)->where('type', 'sell')->count();
        $this->assertEquals(1, $tx_count1);

        $tx1 = \App\Transaction::find($tx_id1);
        $this->assertEquals('partial', $tx1->payment_status);
        $this->assertEquals(60000, $tx1->final_total);

        // Payment 2: Rudi pays remaining Rp 30,000 via Midtrans
        // Step A: Midtrans Token request (creates/reuses draft transaction or existing transaction)
        $input2 = [
            'location_id' => 1,
            'is_direct_sale' => 0,
            'contact_id' => $contact->id,
            'laundry_order_sheet_id' => $os->id,
            'status' => 'draft',
            'final_total' => 30000,
            'discount_type' => 'fixed',
            'discount_amount' => 0,
            'tax_rate_id' => null,
            'products' => [
                [
                    'product_id' => 1,
                    'variation_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 30000,
                    'unit_price_inc_tax' => 30000,
                    'item_tax' => 0,
                    'tax_id' => null,
                    'enable_stock' => 0,
                    'product_type' => 'single',
                ],
            ],
            'payment' => [],
        ];

        $request2 = \Illuminate\Http\Request::create('/pos', 'POST', $input2);
        $request2->setLaravelSession(app('session.store'));
        app()->instance('request', $request2);

        $res2 = $controller->store($request2);

        $this->assertEquals(1, $res2['success']);
        $tx_id2 = $res2['transaction_id'];

        // MUST be the exact same transaction ID (Invoice #1)
        $this->assertEquals($tx_id1, $tx_id2);

        // Still 1 single transaction/invoice for this order sheet
        $tx_count2 = \App\Transaction::where('laundry_order_sheet_id', $os->id)->where('type', 'sell')->count();
        $this->assertEquals(1, $tx_count2);

        // Step B: Finalize Midtrans Payment for remaining Rp 30,000
        $midtransController = app(\App\Http\Controllers\MidtransController::class);
        $midtransController->finalizeAndPayTransaction($tx1, 'MID-POS-TEST-RUDI');

        $tx1->refresh();
        $this->assertEquals('paid', $tx1->payment_status);
        $this->assertEquals(60000, $tx1->final_total);

        // Verify total payments on Invoice = 60,000 (30,000 cash + 30,000 midtrans)
        $total_paid = \App\TransactionPayment::where('transaction_id', $tx1->id)->sum('amount');
        $this->assertEquals(60000, $total_paid);

        // Order Sheet payment status MUST be 'paid'
        $os->refresh();
        $this->assertEquals('paid', $os->payment_status);
    }

    public function test_laundry_dashboard_view_renders_correctly()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        $user = \App\User::where('id', 1)->first();
        if (!$user) {
            $user = \App\User::create([
                'id' => 1,
                'business_id' => 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'email' => 'admin@test.com',
            ]);
        }
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user->givePermissionTo('superadmin');
        $this->actingAs($user);

        \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
            'business_id' => 1,
            'package_id' => 1,
            'package_details' => json_encode(['laundry_module' => 1]),
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $status = LaundryStatus::create([
            'business_id' => 1,
            'name' => 'Sedang Pencucian',
            'color' => '#00c0ef',
        ]);

        $service_type = LaundryServiceType::create([
            'business_id' => 1,
            'name' => 'Express (1 Hari)',
        ]);

        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Kemeja',
            'unit_name' => 'pcs',
            'default_price' => 12000,
        ]);

        $order = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-TEST-0001',
            'contact_id' => 10,
            'laundry_service_type_id' => $service_type->id,
            'laundry_status_id' => $status->id,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 3,
            'unit_name' => 'pcs',
        ]);

        $recent_orders = collect([$order]);

        $view = view('laundry::dashboard.index', [
            'total_orders' => 5,
            'pending_orders' => 3,
            'completed_orders' => 2,
            'recent_orders' => $recent_orders,
        ])->render();

        $this->assertStringContainsString('info-box-new-style', $view);
        $this->assertStringContainsString('LND-TEST-0001', $view);
        $this->assertStringContainsString('bg-aqua', $view);
        $this->assertStringContainsString('bg-yellow', $view);
        $this->assertStringContainsString('bg-green', $view);
    }

    public function test_laundry_granular_permissions_and_sidebar_visibility()
    {
        $user = \App\User::where('id', 2)->first();
        if (!$user) {
            $user = \App\User::create([
                'id' => 2,
                'business_id' => 1,
                'first_name' => 'Laundry',
                'last_name' => 'Staff',
                'username' => 'laundry_staff',
                'email' => 'laundry_staff@test.com',
            ]);
        }

        // User without master data, dashboard, or report permissions should be forbidden
        $response1 = $this->actingAs($user)->get('/laundry/dashboard');
        $response1->assertStatus(403);

        $response2 = $this->actingAs($user)->get('/laundry/statuses');
        $response2->assertStatus(403);

        $response3 = $this->actingAs($user)->get('/laundry/reports/staff-points');
        $response3->assertStatus(403);
    }

    public function test_laundry_settings_logo_upload_and_rendering()
    {
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        $user = \App\User::where('id', 1)->first();
        if (!$user) {
            $user = \App\User::create([
                'id' => 1,
                'business_id' => 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'email' => 'admin@test.com',
            ]);
        }
        $this->actingAs($user);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'laundry.manage_master_data', 'guard_name' => 'web']);
        $user->givePermissionTo('superadmin');
        $user->givePermissionTo('laundry.manage_master_data');

        // Test settings page GET
        $response = $this->actingAs($user)
            ->withSession(['user.business_id' => 1, 'user.id' => 1])
            ->get(route('laundry.settings'));

        $response->assertStatus(200);

        // Test updating settings with laundry logo filename
        $logo_filename = 'test_logo_' . time() . '.png';
        if (!file_exists(public_path('uploads/laundry_logos'))) {
            mkdir(public_path('uploads/laundry_logos'), 0777, true);
        }
        file_put_contents(public_path('uploads/laundry_logos/' . $logo_filename), 'test logo content');

        $business = \App\Business::find(1);
        $business->laundry_settings = json_encode(['laundry_logo' => $logo_filename]);
        $business->save();

        // Test rendering on public status page
        $item_type = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Jas',
            'unit_name' => 'pcs',
            'default_price' => 50000,
        ]);

        $os = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'order_no' => 'LND-LOGO-TEST',
            'contact_id' => 10,
            'laundry_item_type_id' => $item_type->id,
            'quantity' => 1,
        ]);

        $statusResponse = $this->get(route('laundry.public_status', [$os->order_no]));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('uploads/laundry_logos/' . $logo_filename);

        // Test rendering on print order sheet
        $printResponse = $this->actingAs($user)
            ->withSession(['user.business_id' => 1, 'user.id' => 1])
            ->get(route('laundry.order_sheet.print', [$os->id]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('uploads/laundry_logos/' . $logo_filename);

        // Test removing logo
        $removeResponse = $this->actingAs($user)
            ->withSession(['user.business_id' => 1, 'user.id' => 1])
            ->post(route('laundry.settings.store'), [
                'remove_laundry_logo' => 1,
            ]);

        $removeResponse->assertRedirect();

        $business->refresh();
        $settings = json_decode($business->laundry_settings, true);
        $this->assertNull($settings['laundry_logo']);
        $this->assertFalse(file_exists(public_path('uploads/laundry_logos/' . $logo_filename)));
    }
}
