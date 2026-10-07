<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Laundry\Entities\LaundryStatus;
use Modules\Laundry\Entities\LaundryProcess;
use Modules\Laundry\Entities\LaundryServiceType;
use Modules\Laundry\Entities\LaundryItemType;

class LaundryMultiItemOrderSheetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));

        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_process_logs');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_sheet_items');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_sheets');
        \Illuminate\Support\Facades\Schema::dropIfExists('transactions');
        \Illuminate\Support\Facades\Schema::dropIfExists('transaction_payments');
        \Illuminate\Support\Facades\Schema::dropIfExists('contacts');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_statuses');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_processes');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_service_types');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_item_types');
        \Illuminate\Support\Facades\Schema::dropIfExists('selling_price_groups');
        \Illuminate\Support\Facades\Schema::dropIfExists('products');
        \Illuminate\Support\Facades\Schema::dropIfExists('variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('business_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('units');
        \Illuminate\Support\Facades\Schema::dropIfExists('business');

        \Illuminate\Support\Facades\Schema::create('business', function ($table) {
            $table->id();
            $table->string('name')->default('Laundry Business');
            $table->text('laundry_settings')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('selling_price_groups', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->boolean('is_active')->default(1);
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

        \Illuminate\Support\Facades\Schema::create('business_locations', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name')->default('Main Location');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('contacts', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('type')->default('customer');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('contact_id')->nullable();
            $table->boolean('is_default')->default(0);
            $table->integer('customer_group_id')->nullable();
            $table->softDeletes();
            $table->integer('created_by')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_statuses', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->string('color')->default('#000000');
            $table->boolean('is_completed_status')->default(false);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_service_types', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->integer('completion_hours')->default(24);
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

        \Illuminate\Support\Facades\Schema::create('laundry_order_sheets', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id')->default(1);
            $table->string('order_no');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('laundry_status_id')->nullable();
            $table->unsignedBigInteger('laundry_service_type_id')->nullable();
            $table->unsignedBigInteger('laundry_item_type_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->string('unit_name')->default('kg');
            $table->string('delivery_type')->default('self_service');
            $table->dateTime('received_at')->nullable();
            $table->dateTime('estimated_completion_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('items_detail')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('whatsapp_sent_at')->nullable();
            $table->integer('created_by')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_order_sheet_items', function ($table) {
            $table->id();
            $table->unsignedBigInteger('laundry_order_sheet_id');
            $table->unsignedBigInteger('laundry_item_type_id')->nullable();
            $table->unsignedBigInteger('laundry_service_type_id')->nullable();
            $table->unsignedBigInteger('laundry_status_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->string('unit_name')->default('kg');
            $table->decimal('unit_price', 22, 4)->default(0.0000);
            $table->decimal('subtotal', 22, 4)->default(0.0000);
            $table->text('items_detail')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_order_process_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_sheet_id');
            $table->unsignedBigInteger('laundry_order_sheet_item_id')->nullable();
            $table->unsignedBigInteger('laundry_process_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('points_earned', 8, 2)->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->integer('created_by')->default(1);
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
            $table->boolean('is_return')->default(0);
            $table->timestamps();
        });

        \App\Business::create(['id' => 1, 'name' => 'Test Business']);
        \App\BusinessLocation::create(['id' => 1, 'business_id' => 1, 'name' => 'Main Outlet']);
        \App\Unit::create(['id' => 1, 'business_id' => 1, 'actual_name' => 'Kilogram', 'short_name' => 'kg']);
    }

    public function test_multi_item_laundry_order_sheet_creation_and_pos_details()
    {
        $status_pending = LaundryStatus::create([
            'business_id' => 1,
            'name' => 'Proses',
            'color' => '#3b82f6',
            'is_completed_status' => false,
        ]);

        $service_reg = LaundryServiceType::create([
            'business_id' => 1,
            'name' => 'Reguler',
            'completion_hours' => 24,
        ]);

        $item_bedcover = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Bed Cover',
            'unit_name' => 'Pcs',
            'default_price' => 35000,
        ]);

        $item_sepatu = LaundryItemType::create([
            'business_id' => 1,
            'name' => 'Cuci Sepatu',
            'unit_name' => 'Pasang',
            'default_price' => 25000,
        ]);

        $contact = \App\Contact::create([
            'business_id' => 1,
            'type' => 'customer',
            'name' => 'Budi Multi Item',
            'mobile' => '081299998888',
            'created_by' => 1,
        ]);

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->id = 1;
        $user->business_id = 1;
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasPermissionTo')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->actingAs($user);

        $req = \Illuminate\Http\Request::create('/dummy', 'GET');
        $req->setLaravelSession(app('session.store'));
        app()->instance('request', $req);
        session(['user.business_id' => 1, 'user.id' => 1]);

        $order_sheet = \Modules\Laundry\Entities\LaundryOrderSheet::create([
            'business_id' => 1,
            'location_id' => 1,
            'order_no' => 'LND-2026-0001',
            'contact_id' => $contact->id,
            'laundry_status_id' => $status_pending->id,
            'laundry_service_type_id' => $service_reg->id,
            'laundry_item_type_id' => $item_bedcover->id,
            'quantity' => 1,
            'unit_name' => 'Pcs',
            'delivery_type' => 'self_service',
            'items_detail' => '1 Bedcover king size, 2 Pasang sepatu kets',
            'created_by' => 1,
        ]);

        $order_sheet->items()->create([
            'laundry_item_type_id' => $item_bedcover->id,
            'laundry_service_type_id' => $service_reg->id,
            'laundry_status_id' => $status_pending->id,
            'quantity' => 1,
            'unit_name' => 'Pcs',
            'unit_price' => 35000,
            'subtotal' => 35000,
        ]);

        $order_sheet->items()->create([
            'laundry_item_type_id' => $item_sepatu->id,
            'laundry_service_type_id' => $service_reg->id,
            'laundry_status_id' => $status_pending->id,
            'quantity' => 2,
            'unit_name' => 'Pasang',
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        $this->assertNotNull($order_sheet);
        $this->assertEquals(2, $order_sheet->items()->count());

        // Total amount: (1 * 35000) + (2 * 25000) = 85,000
        $this->assertEquals(85000, $order_sheet->total_amount);

        // Test getPosDetails endpoint returning all multi-items using controller direct call
        $controller = app(\Modules\Laundry\Http\Controllers\OrderSheetController::class);
        $pos_details_res = $controller->getPosDetails($order_sheet->id);
        $data = json_decode($pos_details_res->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals(85000, $data['total_amount']);
        $this->assertEquals(85000, $data['due_amount']);
        $this->assertCount(2, $data['items']);

        // Test public tracking page search using controller direct call
        $public_controller = app(\Modules\Laundry\Http\Controllers\PublicStatusController::class);
        $req_public = \Illuminate\Http\Request::create('/laundry/status', 'POST', ['search_key' => $order_sheet->order_no]);
        $req_public->setLaravelSession(app('session.store'));
        app()->instance('request', $req_public);

        $view_public = $public_controller->search($req_public);
        $this->assertEquals('laundry::public_status.index', $view_public->name());
        $this->assertEquals($order_sheet->id, $view_public->getData()['order_sheet']->id);
    }
}
