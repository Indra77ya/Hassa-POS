<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\ProductSerialNumber;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OpeningStockSerialNumberTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::dropIfExists('activity_log');
        \Illuminate\Support\Facades\Schema::dropIfExists('users');
        \Illuminate\Support\Facades\Schema::dropIfExists('business');
        \Illuminate\Support\Facades\Schema::dropIfExists('products');
        \Illuminate\Support\Facades\Schema::dropIfExists('variations');
        \Illuminate\Support\Facades\Schema::dropIfExists('product_serial_numbers');
        \Illuminate\Support\Facades\Schema::dropIfExists('units');
        \Illuminate\Support\Facades\Schema::dropIfExists('categories');
        \Illuminate\Support\Facades\Schema::dropIfExists('business_locations');
        \Illuminate\Support\Facades\Schema::dropIfExists('transactions');
        \Illuminate\Support\Facades\Schema::dropIfExists('purchase_lines');

        \Illuminate\Support\Facades\Schema::dropIfExists('system');
        \Illuminate\Support\Facades\Schema::create('system', function ($table) {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
        });
        \DB::table('system')->insert(['key' => 'db_version', 'value' => '5.0']);

        \Illuminate\Support\Facades\Schema::create('business', function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('currency_id')->default(1);
            $table->string('start_date')->nullable();
            $table->string('tax_number_1')->nullable();
            $table->string('tax_label_1')->nullable();
            $table->string('tax_number_2')->nullable();
            $table->string('tax_label_2')->nullable();
            $table->string('default_profit_percent')->default(0);
            $table->integer('owner_id')->nullable();
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->string('fy_start_month')->default('1');
            $table->string('accounting_method')->default('fifo');
            $table->decimal('default_sales_discount', 5, 2)->nullable();
            $table->enum('sell_price_tax', ['includes', 'excludes'])->default('includes');
            $table->string('logo')->nullable();
            $table->string('sku_prefix')->nullable();
            $table->boolean('enable_tooltip')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('business_locations', function ($table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('location_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('user_type')->default('user');
            $table->string('surname')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('language')->default('en');
            $table->unsignedBigInteger('business_id');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        \Illuminate\Support\Facades\Schema::create('units', function ($table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('business_id');
            $table->string('type');
            $table->unsignedBigInteger('unit_id');
            $table->string('sku');
            $table->string('barcode_type')->default('C128');
            $table->boolean('enable_stock')->default(1);
            $table->boolean('enable_sr_no')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->decimal('alert_quantity', 22, 4)->nullable();
            $table->string('image')->nullable();
            $table->text('product_description')->nullable();
            $table->boolean('not_for_selling')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('variations', function ($table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->string('sub_sku');
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transactions', function ($table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('location_id');
            $table->string('type');
            $table->string('status')->default('received');
            $table->string('payment_status')->default('paid');
            $table->unsignedBigInteger('opening_stock_product_id')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('final_total', 22, 4)->default(0);
            $table->text('additional_notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('purchase_lines', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('quantity_used', 22, 4)->default(0);
            $table->decimal('pp_without_discount', 22, 4)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->date('exp_date')->nullable();
            $table->string('lot_number')->nullable();
            $table->decimal('secondary_unit_quantity', 22, 4)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('product_serial_numbers', function ($table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->unsignedBigInteger('purchase_line_id')->nullable();
            $table->unsignedBigInteger('transaction_sell_line_id')->nullable();
            $table->string('serial_number');
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('selling_price', 22, 4)->default(0);
            $table->string('status')->default('in_stock');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_creates_serial_numbers_when_adding_opening_stock()
    {
        $business = Business::create(['name' => 'OS Business']);
        $user = User::create([
            'first_name' => 'OS',
            'last_name' => 'Admin',
            'email' => 'os_admin@test.com',
            'business_id' => $business->id,
        ]);
        $location = BusinessLocation::create([
            'business_id' => $business->id,
            'name' => 'Main Location',
        ]);

        $product = Product::create([
            'name' => 'Laptop OS SN Test',
            'business_id' => $business->id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => 'LAP-OS-001',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => $user->id,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => $product->sku,
            'default_purchase_price' => 5000000,
            'dpp_inc_tax' => 5000000,
            'profit_percent' => 10,
            'default_sell_price' => 5500000,
            'sell_price_inc_tax' => 5500000,
        ]);

        $payload = [
            'product_id' => $product->id,
            'stocks' => [
                $location->id => [
                    $variation->id => [
                        0 => [
                            'quantity' => '2',
                            'purchase_price' => '5.000.000',
                            'serial_numbers' => ['OS-SN-101', 'OS-SN-102'],
                            'sn_details' => [
                                'OS-SN-101' => [
                                    'serial_number' => 'OS-SN-101',
                                    'purchase_price' => '5.000.000',
                                    'selling_price' => '6.000.000',
                                ],
                                'OS-SN-102' => [
                                    'serial_number' => 'OS-SN-102',
                                    'purchase_price' => '5.000.000',
                                    'selling_price' => '6.500.000',
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        session(['financial_year.start' => '2023-01-01']);
        $response = $this->actingAs($user)->post('/opening-stock/save', $payload);

        $response->assertRedirect('products');

        $this->assertDatabaseHas('product_serial_numbers', [
            'business_id' => $business->id,
            'product_id' => $product->id,
            'serial_number' => 'OS-SN-101',
            'purchase_price' => 5000000,
            'selling_price' => 6000000,
            'status' => 'in_stock',
        ]);

        $this->assertDatabaseHas('product_serial_numbers', [
            'business_id' => $business->id,
            'product_id' => $product->id,
            'serial_number' => 'OS-SN-102',
            'purchase_price' => 5000000,
            'selling_price' => 6500000,
            'status' => 'in_stock',
        ]);
    }
}
