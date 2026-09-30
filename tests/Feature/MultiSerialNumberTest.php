<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\ProductSerialNumber;
use App\PurchaseLine;
use App\Transaction;
use App\User;
use App\Utils\ProductSerialNumberUtil;
use App\Variation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class MultiSerialNumberTest extends TestCase
{
    protected $user;
    protected $business;
    protected $location;
    protected $product;
    protected $variation;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('surname')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('username')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->unsignedInteger('business_id')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('business')) {
            Schema::create('business', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('business_locations')) {
            Schema::create('business_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('business_id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('business_id');
                $table->string('type')->default('single');
                $table->unsignedInteger('unit_id')->default(1);
                $table->boolean('enable_sr_no')->default(0);
                $table->boolean('enable_stock')->default(1);
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('variations')) {
            Schema::create('variations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('product_variation_id')->default(1);
                $table->string('name')->default('DUMMY');
                $table->string('sub_sku')->nullable();
                $table->decimal('default_purchase_price', 22, 4)->default(0);
                $table->decimal('dpp_inc_tax', 22, 4)->default(0);
                $table->decimal('profit_percent', 22, 4)->default(0);
                $table->decimal('default_sell_price', 22, 4)->default(0);
                $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('location_id')->nullable();
                $table->string('type');
                $table->string('status');
                $table->string('payment_status')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->dateTime('transaction_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_lines')) {
            Schema::create('purchase_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('transaction_id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('variation_id');
                $table->decimal('quantity', 22, 4)->default(1);
                $table->decimal('purchase_price', 22, 4)->default(0);
                $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_serial_numbers')) {
            Schema::create('product_serial_numbers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('variation_id')->nullable();
                $table->unsignedInteger('location_id')->nullable();
                $table->unsignedInteger('purchase_line_id')->nullable();
                $table->unsignedInteger('transaction_sell_line_id')->nullable();
                $table->unsignedInteger('repair_job_sheet_id')->nullable();
                $table->string('serial_number');
                $table->decimal('purchase_price', 22, 4)->nullable();
                $table->decimal('selling_price', 22, 4)->nullable();
                $table->string('status')->default('in_stock');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        $this->withoutMiddleware();

        $this->business = Business::create(['name' => 'Test Business']);
        $this->user = User::create(['first_name' => 'Admin', 'username' => 'admin', 'business_id' => $this->business->id]);
        $this->actingAs($this->user);

        $this->location = BusinessLocation::create(['business_id' => $this->business->id, 'name' => 'Main Location']);

        $this->product = Product::create([
            'name' => 'Laptop ASUS ROG',
            'business_id' => $this->business->id,
            'type' => 'single',
            'unit_id' => 1,
            'enable_sr_no' => 1,
            'enable_stock' => 1,
            'created_by' => $this->user->id
        ]);

        $this->variation = Variation::create([
            'product_id' => $this->product->id,
            'product_variation_id' => 1,
            'name' => 'DUMMY',
            'sub_sku' => 'LAP-001',
            'default_purchase_price' => 10000000,
            'dpp_inc_tax' => 10000000,
            'profit_percent' => 20,
            'default_sell_price' => 12000000,
            'sell_price_inc_tax' => 12000000
        ]);
    }

    public function test_purchase_creates_individual_serial_numbers()
    {
        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'purchase',
            'status' => 'received',
            'payment_status' => 'paid',
            'created_by' => $this->user->id,
            'transaction_date' => now()
        ]);

        $purchase_line = PurchaseLine::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'quantity' => 2,
            'purchase_price' => 10000000,
            'purchase_price_inc_tax' => 10000000
        ]);

        $purchases_input = [
            [
                'purchase_line_id' => $purchase_line->id,
                'serial_numbers' => "SN-ROG-001\nSN-ROG-002"
            ]
        ];

        $util = new \App\Utils\ProductSerialNumberUtil();
        $util->saveOrUpdatePurchaseSerialNumbers($transaction, $purchases_input);

        $this->assertDatabaseHas('product_serial_numbers', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'serial_number' => 'SN-ROG-001',
            'status' => 'in_stock'
        ]);

        $this->assertDatabaseHas('product_serial_numbers', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'serial_number' => 'SN-ROG-002',
            'status' => 'in_stock'
        ]);
    }

    public function test_get_serial_numbers_ajax_endpoint()
    {
        \App\ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'location_id' => $this->location->id,
            'serial_number' => 'SN-AJAX-001',
            'purchase_price' => 10000000,
            'selling_price' => 12500000,
            'status' => 'in_stock'
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['user' => ['business_id' => $this->business->id]])
            ->getJson('/get-product-serial-numbers?product_id=' . $this->product->id . '&location_id=' . $this->location->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertStringContainsString('SN-AJAX-001', $response->getContent());
    }
}
