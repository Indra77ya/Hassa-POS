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

        \Illuminate\Support\Facades\Schema::dropIfExists('users');
        \Illuminate\Support\Facades\Schema::dropIfExists('business');
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
            $table->integer('business_id')->nullable();
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

        \Illuminate\Support\Facades\Schema::create('business', function ($table) {
            $table->id();
            $table->string('name')->default('Test Business');
            $table->timestamps();
        });

        \App\Business::create([
            'id' => 1,
            'name' => 'Test Business',
        ]);

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
}
