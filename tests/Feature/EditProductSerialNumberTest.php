<?php

namespace Tests\Feature;

use App\Business;
use App\Product;
use App\ProductSerialNumber;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EditProductSerialNumberTest extends TestCase
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
    public function it_can_render_in_stock_serial_numbers_on_edit_product_page()
    {
        $business = Business::create(['name' => 'Test Business']);
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'business_id' => $business->id,
        ]);

        $business_id = $user->business_id;

        $product = Product::create([
            'name' => 'Test Laptop SN Edit',
            'business_id' => $business_id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => 'LAP-EDIT-001',
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

        ProductSerialNumber::create([
            'business_id' => $business_id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'serial_number' => 'SN-EDIT-01',
            'purchase_price' => 5000000,
            'selling_price' => 5500000,
            'status' => 'in_stock',
        ]);

        ProductSerialNumber::create([
            'business_id' => $business_id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'serial_number' => 'SN-SOLD-99',
            'purchase_price' => 5000000,
            'selling_price' => 5500000,
            'status' => 'sold',
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('SN-EDIT-01');
        $response->assertDontSee('SN-SOLD-99');
    }

    /** @test */
    public function it_updates_product_details_without_modifying_serials_on_product_update()
    {
        $business = Business::create(['name' => 'Test Business']);
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin2@test.com',
            'business_id' => $business->id,
        ]);

        $business_id = $business->id;

        $product = Product::create([
            'name' => 'Test Phone SN Sync',
            'business_id' => $business_id,
            'type' => 'single',
            'unit_id' => 1,
            'sku' => 'PHONE-SYNC-001',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => $user->id,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => $product->sku,
            'default_purchase_price' => 4000000,
            'dpp_inc_tax' => 4000000,
            'profit_percent' => 0,
            'default_sell_price' => 5000000,
            'sell_price_inc_tax' => 5000000,
        ]);

        ProductSerialNumber::create([
            'business_id' => $business_id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'serial_number' => 'SN-OLD-1',
            'purchase_price' => 4000000,
            'selling_price' => 5000000,
            'status' => 'in_stock',
        ]);

        $payload = [
            'name' => 'Test Phone SN Sync Updated Name',
            'sku' => $product->sku,
            'barcode_type' => 'C128',
            'unit_id' => $product->unit_id,
            'category_id' => $product->category_id,
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'type' => 'single',
            'single_variation_id' => $variation->id,
            'single_dpp' => 4000000,
            'single_dpp_inc_tax' => 4000000,
            'profit_percent' => 0,
            'single_dsp' => 5000000,
            'single_dsp_inc_tax' => 5000000,
            'submit_type' => 'submit',
        ];

        $response = $this->actingAs($user)->put("/products/{$product->id}", $payload);

        $response->assertRedirect('products');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Phone SN Sync Updated Name',
            'enable_sr_no' => 1,
        ]);

        // Existing serial number remains unchanged
        $this->assertDatabaseHas('product_serial_numbers', [
            'product_id' => $product->id,
            'serial_number' => 'SN-OLD-1',
            'status' => 'in_stock',
        ]);
    }
}
