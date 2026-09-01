<?php

namespace Tests\Feature;

use App\Business;
use App\Product;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportProductsUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('default_profit_percent', 5, 2)->default(10);
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->string('currency_precision')->default('2');
            $table->string('quantity_precision')->default('2');
            $table->timestamps();
        });

        \DB::table('business')->insert([
            'id' => 1,
            'name' => 'Import Test Business',
            'default_profit_percent' => 10,
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        // Create system table
        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->string('value')->nullable();
        });
        \DB::table('system')->insert(['key' => 'db_version', 'value' => config('author.app_version')]);
        \DB::table('system')->insert(['key' => 'system_version', 'value' => config('author.app_version')]);

        // Create users table
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_type')->default('user');
            $table->boolean('allow_login')->default(1);
            $table->integer('business_id')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        // Create roles and permission tables
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('name');
            $table->string('guard_name');
            $table->boolean('is_default')->default(0);
            $table->boolean('is_service_staff')->default(0);
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->string('model_type');
            $table->integer('model_id');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id');
            $table->string('model_type');
            $table->integer('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('role_id');
        });

        // Create units table
        Schema::dropIfExists('units');
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create brands table
        Schema::dropIfExists('brands');
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create categories table
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('short_code')->nullable();
            $table->integer('parent_id')->default(0);
            $table->integer('created_by')->nullable();
            $table->string('category_type')->default('product');
            $table->softDeletes();
            $table->timestamps();
        });

        // Create tax_rates table
        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 5, 2)->default(0);
            $table->boolean('is_tax_group')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create business_locations table
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('location_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
        \DB::table('business_locations')->insert([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Main Location',
        ]);

        // Create product_locations table
        Schema::dropIfExists('product_locations');
        Schema::create('product_locations', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('location_id');
        });

        // Create product_racks table
        Schema::dropIfExists('product_racks');
        Schema::create('product_racks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('product_id');
            $table->integer('location_id');
            $table->string('rack')->nullable();
            $table->string('row')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();
        });

        // Create products table
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(0);
            $table->decimal('alert_quantity', 22, 4)->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode_type')->default('C128');
            $table->decimal('expiry_period', 4, 2)->nullable();
            $table->string('expiry_period_type')->nullable();
            $table->boolean('enable_sr_no')->default(0);
            $table->string('weight')->nullable();
            $table->string('image')->nullable();
            $table->text('product_description')->nullable();
            $table->string('product_custom_field1')->nullable();
            $table->string('product_custom_field2')->nullable();
            $table->string('product_custom_field3')->nullable();
            $table->string('product_custom_field4')->nullable();
            $table->boolean('not_for_selling')->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create product_variations table
        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->integer('variation_template_id')->nullable();
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->integer('product_variation_id')->nullable();
            $table->integer('variation_value_id')->nullable();
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->string('profit_margin_type')->default('percentage');
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        $this->user = \Mockery::mock(User::class)->makePartial();
        $this->user->shouldReceive('can')->andReturn(true);
        $this->user->shouldReceive('hasRole')->andReturn(true);
        $this->user->shouldReceive('hasAnyPermission')->andReturn(true);
        $this->user->id = 1;
        $this->user->business_id = 1;
        $this->user->user_type = 'user';

        $this->actingAs($this->user);

        session([
            'user.id' => 1,
            'user.business_id' => 1,
            'business.id' => 1,
            'business.default_profit_percent' => 10,
            'business.currency_precision' => 2,
            'business.quantity_precision' => 2,
            'business.time_zone' => 'Asia/Jakarta',
            'currency' => [
                'id' => 1,
                'code' => 'USD',
                'symbol' => '$',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
            ],
        ]);
    }

    /** @test */
    public function it_exports_products_with_product_id_as_first_column()
    {
        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'allow_decimal' => 0,
            'created_by' => 1,
        ]);

        $product = Product::create([
            'name' => 'Export Sample Product',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'EXP-001',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $pv = \App\ProductVariation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv->id,
            'sub_sku' => 'EXP-001',
            'default_purchase_price' => 10,
            'dpp_inc_tax' => 10,
            'profit_percent' => 10,
            'default_sell_price' => 11,
            'sell_price_inc_tax' => 11,
        ]);

        session(['user.business_id' => 1]);
        request()->setLaravelSession(session());

        $export = new \App\Exports\ProductsExport();
        $array = $export->array();

        // Test headers
        $this->assertEquals('PRODUCT ID', $array[0][0]);
        $this->assertEquals('NAME', $array[0][1]);

        // Test first product row
        $this->assertEquals($product->id, $array[1][0]);
        $this->assertEquals('Export Sample Product', $array[1][1]);
    }

    /** @test */
    public function it_updates_existing_product_via_import_when_product_id_is_provided()
    {
        Gate::before(function () {
            return true;
        });

        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'allow_decimal' => 0,
            'created_by' => 1,
        ]);

        $product = Product::create([
            'name' => 'Original Name',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'UPD-001',
            'type' => 'single',
            'enable_stock' => 0,
            'created_by' => 1,
        ]);

        $pv = \App\ProductVariation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv->id,
            'sub_sku' => 'UPD-001',
            'default_purchase_price' => 10,
            'dpp_inc_tax' => 10,
            'profit_percent' => 10,
            'default_sell_price' => 11,
            'sell_price_inc_tax' => 11,
        ]);

        $header = [
            'PRODUCT ID', 'NAME', 'BRAND', 'UNIT', 'CATEGORY', 'SUB-CATEGORY', 'SKU', 'BARCODE TYPE',
            'MANAGE STOCK', 'ALERT QUANTITY', 'EXPIRES IN', 'EXPIRY PERIOD UNIT', 'APPLICABLE TAX',
            'Selling Price Tax Type', 'PRODUCT TYPE', 'VARIATION NAME', 'VARIATION VALUES', 'VARIATION SKUs',
            'PURCHASE PRICE (Including tax)', 'PURCHASE PRICE (Excluding tax)', 'PROFIT MARGIN', 'SELLING PRICE',
            'OPENING STOCK', 'OPENING STOCK LOCATION', 'EXPIRY DATE', 'ENABLE IMEI OR SERIAL NUMBER',
            'WEIGHT', 'RACK', 'ROW', 'POSITION', 'IMAGE', 'PRODUCT DESCRIPTION', 'CUSTOM FIELD 1',
            'CUSTOM FIELD 2', 'CUSTOM FIELD 3', 'CUSTOM FIELD 4', 'NOT FOR SELLING', 'PRODUCT LOCATIONS',
            'PROFIT MARGIN TYPE'
        ];

        $row = [
            $product->id, // PRODUCT ID
            'Updated Name', // NAME
            '', // BRAND
            'Pc', // UNIT
            '', // CATEGORY
            '', // SUB-CATEGORY
            'UPD-001', // SKU
            'C128', // BARCODE TYPE
            0, // MANAGE STOCK
            '', // ALERT QUANTITY
            '', // EXPIRES IN
            '', // EXPIRY PERIOD UNIT
            '', // APPLICABLE TAX
            'exclusive', // Selling Price Tax Type
            'single', // PRODUCT TYPE
            '', // VARIATION NAME
            '', // VARIATION VALUES
            '', // VARIATION SKUs
            50, // PURCHASE PRICE (Inc)
            50, // PURCHASE PRICE (Ex)
            10, // PROFIT MARGIN
            60, // SELLING PRICE
            '', // OPENING STOCK
            '', // OPENING STOCK LOCATION
            '', // EXPIRY DATE
            0, // ENABLE IMEI
            '', // WEIGHT
            '', // RACK
            '', // ROW
            '', // POSITION
            '', // IMAGE
            '', // PRODUCT DESCRIPTION
            '', // CF1
            '', // CF2
            '', // CF3
            '', // CF4
            0, // NOT FOR SELLING
            '', // PRODUCT LOCATIONS
            'percentage' // PROFIT MARGIN TYPE
        ];

        $csvContent = implode(',', $header) . "\n" . implode(',', $row) . "\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'products_update.csv',
            'text/csv',
            null,
            true
        );

        $user = User::create([
            'id' => 1,
            'user_type' => 'user',
            'allow_login' => 1,
            'business_id' => 1,
            'surname' => 'Admin',
            'first_name' => 'User',
        ]);

        $permission = \Spatie\Permission\Models\Permission::create([
            'name' => 'product.create',
            'guard_name' => 'web',
        ]);
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->withSession([
            'user.id' => 1,
            'user.business_id' => 1,
            'business.id' => 1,
            'business.default_profit_percent' => 10,
            'business.currency_precision' => 2,
            'business.quantity_precision' => 2,
            'currency' => [
                'id' => 1,
                'code' => 'USD',
                'symbol' => '$',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
            ],
        ])->post('/import-products/store', [
            'products_csv' => $uploadedFile,
        ]);

        $response->assertRedirect('import-products');

        $updatedProduct = Product::find($product->id);
        $this->assertEquals('Updated Name', $updatedProduct->name);

        $updatedVariation = Variation::where('product_id', $product->id)->first();
        $this->assertEquals(50, $updatedVariation->default_purchase_price);
        $this->assertEquals(60, $updatedVariation->default_sell_price);
    }
}
