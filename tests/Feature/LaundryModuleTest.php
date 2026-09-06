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

        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_item_types');
        \Illuminate\Support\Facades\Schema::dropIfExists('products');
        \Illuminate\Support\Facades\Schema::dropIfExists('business_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('units');

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

        \Illuminate\Support\Facades\Schema::create('units', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->softDeletes();
            $table->timestamps();
        });

        \App\Unit::create([
            'id' => 1,
            'business_id' => 1,
            'actual_name' => 'kg',
            'short_name' => 'kg',
        ]);

        \App\BusinessLocation::create([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Main Branch',
        ]);
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
        $this->assertStringContainsString('fa-plus-circle', $view);
        $this->assertStringContainsString('edit_laundry_order_sheet_btn', $view);
        $this->assertStringContainsString('show_laundry_order_sheet_btn', $view);
        $this->assertStringContainsString('add_laundry_to_cart_btn', $view);
    }
}
