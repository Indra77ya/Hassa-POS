<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportPurchaseLinesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function () {
            return true;
        });

        // Create currencies table
        Schema::dropIfExists('currencies');
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country');
            $table->string('currency');
            $table->string('code');
            $table->string('symbol');
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
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

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('currency_id')->default(1);
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        \DB::table('business')->insert([
            'id' => 1,
            'name' => 'Purchase Test Business',
            'currency_id' => 1,
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        // Create system table for APP_VERSION
        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->string('value')->nullable();
        });
        \DB::table('system')->insert(['key' => 'db_version', 'value' => config('author.app_version')]);

        // Create users table
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        // Create units table
        Schema::dropIfExists('units');
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->integer('base_unit_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create business_locations table
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        \DB::table('business_locations')->insert([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Toko Utama',
        ]);

        // Create tax_rates table
        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4)->default(0);
            $table->boolean('is_tax_group')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create products table
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->string('sku')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('secondary_unit_id')->nullable();
            $table->integer('tax')->nullable();
            $table->boolean('enable_stock')->default(1);
            $table->boolean('enable_sr_no')->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create product_variations table
        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->integer('product_variation_id')->default(1);
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create variation_location_details table
        Schema::dropIfExists('variation_location_details');
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variation_id');
            $table->integer('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transactions table
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id');
            $table->string('type');
            $table->string('status');
            $table->string('payment_status')->nullable();
            $table->integer('contact_id')->nullable();
            $table->string('transaction_date')->nullable();
            $table->timestamps();
        });

        // Create purchase_lines table
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('pp_without_discount', 22, 4)->default(0);
            $table->decimal('discount_percent', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create product_serial_numbers table
        Schema::dropIfExists('product_serial_numbers');
        Schema::create('product_serial_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('product_id');
            $table->integer('purchase_line_id')->nullable();
            $table->string('serial_number');
            $table->decimal('purchase_price', 22, 4)->nullable();
            $table->decimal('selling_price', 22, 4)->nullable();
            $table->string('status')->default('in_stock');
            $table->timestamps();
        });

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasRole')->andReturn(true);
        $user->shouldReceive('hasAnyPermission')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);
        $this->withoutMiddleware([
            \App\Http\Middleware\AdminSidebarMenu::class,
            \App\Http\Middleware\CheckUserLogin::class,
            \App\Http\Middleware\IsInstalled::class,
        ]);
    }

    /** @test */
    public function it_can_load_import_purchase_lines_modal()
    {
        $response = $this->withSession([
            'user' => ['id' => 1, 'business_id' => 1],
            'business' => ['id' => 1, 'currency_id' => 1, 'time_zone' => 'Asia/Jakarta'],
        ])->get('/purchases/get_import_purchase_lines_modal');

        $response->assertStatus(200);
        $response->assertSee('Impor Produk Pembelian (CSV / Excel)');
    }

    /** @test */
    public function it_can_parse_valid_purchase_lines_csv_file()
    {
        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'allow_decimal' => 0,
            'created_by' => 1,
        ]);

        $p1 = Product::create([
            'name' => 'Kabel USB',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'SK-001',
            'type' => 'single',
            'enable_stock' => 1,
            'enable_sr_no' => 0,
            'created_by' => 1,
        ]);

        $v1 = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $p1->id,
            'product_variation_id' => 1,
            'sub_sku' => 'SK-001',
            'default_purchase_price' => 150000,
            'dpp_inc_tax' => 150000,
            'profit_percent' => 33.33,
            'default_sell_price' => 200000,
            'sell_price_inc_tax' => 200000,
        ]);

        $p2 = Product::create([
            'name' => 'iPhone 13',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'IPH-13',
            'type' => 'single',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => 1,
        ]);

        $v2 = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $p2->id,
            'product_variation_id' => 1,
            'sub_sku' => 'IPH-13',
            'default_purchase_price' => 10000000,
            'dpp_inc_tax' => 10000000,
            'profit_percent' => 20,
            'default_sell_price' => 12000000,
            'sell_price_inc_tax' => 12000000,
        ]);

        $csvContent = "SKU,QUANTITY,UNIT COST (BEFORE DISCOUNT),DISCOUNT PERCENT,SELLING PRICE,SERIAL NUMBER\n";
        $csvContent .= "SK-001,5,150000,0,200000,\n";
        $csvContent .= "IPH-13,1,10000000,0,12000000,SN10001\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'purchase_lines.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->withSession([
            'user' => ['id' => 1, 'business_id' => 1],
            'business' => ['id' => 1, 'currency_id' => 1, 'time_zone' => 'Asia/Jakarta'],
        ])->postJson('/purchases/parse_import_purchase_lines', [
            'purchase_lines_csv' => $uploadedFile,
            'location_id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'row_count' => 2,
        ]);

        $json = $response->json();
        $this->assertStringContainsString('Kabel USB', $json['html']);
        $this->assertStringContainsString('iPhone 13', $json['html']);
        $this->assertStringContainsString('SN10001', $json['html']);
    }

    /** @test */
    public function it_returns_error_when_sku_is_not_found()
    {
        $csvContent = "SKU,QUANTITY,UNIT COST (BEFORE DISCOUNT),DISCOUNT PERCENT,SELLING PRICE,SERIAL NUMBER\n";
        $csvContent .= "UNKNOWN-SKU-999,1,10000,0,15000,\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'purchase_lines.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->withSession([
            'user' => ['id' => 1, 'business_id' => 1],
            'business' => ['id' => 1, 'currency_id' => 1, 'time_zone' => 'Asia/Jakarta'],
        ])->postJson('/purchases/parse_import_purchase_lines', [
            'purchase_lines_csv' => $uploadedFile,
            'location_id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'msg' => __('lang_v1.import_unfound_skus', ['skus' => 'UNKNOWN-SKU-999']),
        ]);
    }

    /** @test */
    public function it_returns_error_when_serial_number_row_has_quantity_greater_than_one()
    {
        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'allow_decimal' => 0,
            'created_by' => 1,
        ]);

        $p1 = Product::create([
            'name' => 'Kabel USB',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'SK-001',
            'type' => 'single',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => 1,
        ]);

        $v1 = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $p1->id,
            'product_variation_id' => 1,
            'sub_sku' => 'SK-001',
            'default_purchase_price' => 150000,
            'dpp_inc_tax' => 150000,
            'profit_percent' => 33.33,
            'default_sell_price' => 200000,
            'sell_price_inc_tax' => 200000,
        ]);

        $csvContent = "SKU,QUANTITY,UNIT COST (BEFORE DISCOUNT),DISCOUNT PERCENT,SELLING PRICE,SERIAL NUMBER\n";
        $csvContent .= "SK-001,5,1200000,0,5000000,24546FG\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'purchase_lines.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->withSession([
            'user' => ['id' => 1, 'business_id' => 1],
            'business' => ['id' => 1, 'currency_id' => 1, 'time_zone' => 'Asia/Jakarta'],
        ])->postJson('/purchases/parse_import_purchase_lines', [
            'purchase_lines_csv' => $uploadedFile,
            'location_id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'msg' => __('lang_v1.import_sn_qty_error', [
                'row' => 2,
                'sku' => 'SK-001',
                'serial_no' => '24546FG',
                'max_stock_msg' => __('lang_v1.serial_number_max_stock_one'),
                'qty' => 5,
            ]),
        ]);
    }
}
