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

        $this->withoutMiddleware([
            \App\Http\Middleware\IsInstalled::class,
            \App\Http\Middleware\AdminSidebarMenu::class,
        ]);

        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('default_profit_percent', 5, 2)->default(25.00);
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        \DB::table('business')->insert([
            'id' => 1,
            'name' => 'Update Product Test Business',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
            'default_profit_percent' => 25.00,
        ]);

        // Create system table
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
            $table->string('user_type')->default('user');
            $table->boolean('allow_login')->default(1);
            $table->timestamps();
        });

        // Create permissions tables
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
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
            $table->string('name');
            $table->string('guard_name');
            $table->integer('business_id')->unsigned();
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->string('model_type');
            $table->integer('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->string('model_type');
            $table->integer('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->primary(['permission_id', 'role_id']);
        });

        \Spatie\Permission\Models\Permission::create(['name' => 'product.create', 'guard_name' => 'web']);

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
            $table->string('category_type')->default('product');
            $table->integer('parent_id')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create tax_rates table
        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4)->default(0);
            $table->boolean('is_tax_group')->default(0);
            $table->integer('created_by')->nullable();
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
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(1);
            $table->decimal('alert_quantity', 22, 4)->default(0);
            $table->string('sku')->nullable();
            $table->string('barcode_type')->default('C128');
            $table->decimal('expiry_period', 4, 2)->nullable();
            $table->string('expiry_period_type')->nullable();
            $table->boolean('enable_sr_no')->default(0);
            $table->string('weight')->nullable();
            $table->text('product_description')->nullable();
            $table->string('product_custom_field1')->nullable();
            $table->string('product_custom_field2')->nullable();
            $table->string('product_custom_field3')->nullable();
            $table->string('product_custom_field4')->nullable();
            $table->string('image')->nullable();
            $table->boolean('not_for_selling')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create product_variations table
        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->integer('product_variation_id')->nullable();
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
            'name' => 'Main Location',
        ]);

        $user = User::create([
            'id' => 1,
            'business_id' => 1,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'user_type' => 'user',
            'allow_login' => 1,
        ]);
        $user->givePermissionTo('product.create');
        $this->actingAs($user);

        session([
            'user.id' => 1,
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
            'business.default_profit_percent' => 25.00,
            'business.currency_precision' => 2,
            'business.quantity_precision' => 2,
            'currency' => [
                'id' => 1,
                'code' => 'IDR',
                'symbol' => 'Rp',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
            ],
        ]);
    }

    /** @test */
    public function it_can_export_products_with_product_id()
    {
        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'created_by' => 1,
        ]);

        $product = Product::create([
            'name' => 'Original Product Name',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'SKU-ORIGINAL',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $pv = \DB::table('product_variations')->insertGetId([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv,
            'sub_sku' => 'SKU-ORIGINAL',
            'default_purchase_price' => 100,
            'dpp_inc_tax' => 100,
            'profit_percent' => 25,
            'default_sell_price' => 125,
            'sell_price_inc_tax' => 125,
        ]);

        $response = $this->get('/update-products/export');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_update_product_via_csv_import()
    {
        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'created_by' => 1,
        ]);

        $product = Product::create([
            'name' => 'Old Product Name',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'sku' => 'SKU-1001',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $pv = \DB::table('product_variations')->insertGetId([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        $variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv,
            'sub_sku' => 'SKU-1001',
            'default_purchase_price' => 100,
            'dpp_inc_tax' => 100,
            'profit_percent' => 20,
            'default_sell_price' => 120,
            'sell_price_inc_tax' => 120,
        ]);

        // CSV content with 38 columns (PRODUCT ID as col 0)
        $csvHeader = "PRODUCT ID,NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU (Leave blank to auto generate sku),BARCODE TYPE,MANAGE STOCK (1=yes 0=No),ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT (months/days),APPLICABLE TAX,Selling Price Tax Type (inclusive or exclusive),PRODUCT TYPE (single or variable),VARIATION NAME (Keep blank if product type is single),VARIATION VALUES (| seperated values & blank if product type if single),VARIATION SKUs (| seperated values & blank if product type if single),PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,OPENING STOCK LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER(1=yes 0=No),WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING(1=yes 0=No),PRODUCT LOCATIONS\n";

        $csvRowArray = [
            $product->id,             // 0: PRODUCT ID
            'Updated Product Name',   // 1: NAME
            '',                       // 2: BRAND
            'Pc',                     // 3: UNIT
            '',                       // 4: CATEGORY
            '',                       // 5: SUB-CATEGORY
            'SKU-1001-UPDATED',       // 6: SKU
            'C128',                   // 7: BARCODE TYPE
            1,                        // 8: MANAGE STOCK
            10,                       // 9: ALERT QUANTITY
            '',                       // 10: EXPIRES IN
            '',                       // 11: EXPIRY PERIOD UNIT
            '',                       // 12: APPLICABLE TAX
            'exclusive',              // 13: Selling Price Tax Type
            'single',                 // 14: PRODUCT TYPE
            '',                       // 15: VARIATION NAME
            '',                       // 16: VARIATION VALUES
            '',                       // 17: VARIATION SKUs
            150,                      // 18: PURCHASE PRICE Inc Tax
            150,                      // 19: PURCHASE PRICE Exc Tax
            20,                       // 20: PROFIT MARGIN
            180,                      // 21: SELLING PRICE
            '',                       // 22: OPENING STOCK
            '',                       // 23: OPENING STOCK LOCATION
            '',                       // 24: EXPIRY DATE
            0,                        // 25: ENABLE IMEI
            0.5,                      // 26: WEIGHT
            '',                       // 27: RACK
            '',                       // 28: ROW
            '',                       // 29: POSITION
            'product_img.png',        // 30: IMAGE
            'Updated description',    // 31: PRODUCT DESCRIPTION
            'CF1',                    // 32: CUSTOM FIELD 1
            'CF2',                    // 33: CUSTOM FIELD 2
            'CF3',                    // 34: CUSTOM FIELD 3
            'CF4',                    // 35: CUSTOM FIELD 4
            0,                        // 36: NOT FOR SELLING
            'Main Location',          // 37: PRODUCT LOCATIONS
        ];
        $csvRow = implode(',', $csvRowArray) . "\n";

        $csvContent = $csvHeader . $csvRow;

        $tempFile = sys_get_temp_dir() . '/update_products_' . uniqid() . '.csv';
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'update_products.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->post('/update-products/store', [
            'products_csv' => $uploadedFile,
        ]);

        $response->assertRedirect('update-products');

        $updatedProduct = Product::find($product->id);
        $this->assertEquals('Updated Product Name', $updatedProduct->name);
        $this->assertEquals('SKU-1001-UPDATED', $updatedProduct->sku);
        $this->assertEquals('Updated description', $updatedProduct->product_description);
        $this->assertEquals(10, $updatedProduct->alert_quantity);

        $updatedVariation = Variation::where('product_id', $product->id)->first();
        $this->assertEquals('SKU-1001-UPDATED', $updatedVariation->sub_sku);
        $this->assertEquals(150, (float)$updatedVariation->default_purchase_price);
        $this->assertEquals(180, (float)$updatedVariation->default_sell_price);
    }

    /** @test */
    public function it_can_update_category_and_price_via_csv_import()
    {
        $unit = Unit::create([
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
            'created_by' => 1,
        ]);

        $oldCategory = \App\Category::create([
            'name' => 'Old Category',
            'business_id' => 1,
            'category_type' => 'product',
            'parent_id' => 0,
            'created_by' => 1,
        ]);

        $newCategory = \App\Category::create([
            'name' => 'New Category',
            'business_id' => 1,
            'category_type' => 'product',
            'parent_id' => 0,
            'created_by' => 1,
        ]);

        $product = Product::create([
            'name' => 'Test Product Price Cat',
            'business_id' => 1,
            'unit_id' => $unit->id,
            'category_id' => $oldCategory->id,
            'sku' => 'SKU-CAT-PRICE',
            'type' => 'single',
            'created_by' => 1,
        ]);

        $pv = \DB::table('product_variations')->insertGetId([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv,
            'sub_sku' => 'SKU-CAT-PRICE',
            'default_purchase_price' => 10000,
            'dpp_inc_tax' => 10000,
            'profit_percent' => 25,
            'default_sell_price' => 12500,
            'sell_price_inc_tax' => 12500,
        ]);

        $csvHeader = "PRODUCT ID,NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU (Leave blank to auto generate sku),BARCODE TYPE,MANAGE STOCK (1=yes 0=No),ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT (months/days),APPLICABLE TAX,Selling Price Tax Type (inclusive or exclusive),PRODUCT TYPE (single or variable),VARIATION NAME (Keep blank if product type is single),VARIATION VALUES (| seperated values & blank if product type if single),VARIATION SKUs (| seperated values & blank if product type if single),PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,OPENING STOCK LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER(1=yes 0=No),WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING(1=yes 0=No),PRODUCT LOCATIONS\n";

        $csvRowArray = [
            $product->id,             // 0: PRODUCT ID
            'Test Product Price Cat', // 1: NAME
            '',                       // 2: BRAND
            'Pc',                     // 3: UNIT
            'New Category',           // 4: CATEGORY
            '',                       // 5: SUB-CATEGORY
            'SKU-CAT-PRICE',          // 6: SKU
            'C128',                   // 7: BARCODE TYPE
            1,                        // 8: MANAGE STOCK
            10,                       // 9: ALERT QUANTITY
            '',                       // 10: EXPIRES IN
            '',                       // 11: EXPIRY PERIOD UNIT
            '',                       // 12: APPLICABLE TAX
            'exclusive',              // 13: Selling Price Tax Type
            'single',                 // 14: PRODUCT TYPE
            '',                       // 15: VARIATION NAME
            '',                       // 16: VARIATION VALUES
            '',                       // 17: VARIATION SKUs
            10000,                    // 18: PURCHASE PRICE Inc Tax
            10000,                    // 19: PURCHASE PRICE Exc Tax
            25,                       // 20: PROFIT MARGIN
            25000,                    // 21: SELLING PRICE
            '',                       // 22: OPENING STOCK
            '',                       // 23: OPENING STOCK LOCATION
            '',                       // 24: EXPIRY DATE
            0,                        // 25: ENABLE IMEI
            '',                       // 26: WEIGHT
            '',                       // 27: RACK
            '',                       // 28: ROW
            '',                       // 29: POSITION
            '',                       // 30: IMAGE
            '',                       // 31: PRODUCT DESCRIPTION
            '',                       // 32: CUSTOM FIELD 1
            '',                       // 33: CUSTOM FIELD 2
            '',                       // 34: CUSTOM FIELD 3
            '',                       // 35: CUSTOM FIELD 4
            0,                        // 36: NOT FOR SELLING
            'Main Location',          // 37: PRODUCT LOCATIONS
        ];
        $csvRow = implode(',', $csvRowArray) . "\n";

        $csvContent = $csvHeader . $csvRow;

        $tempFile = sys_get_temp_dir() . '/update_products_' . uniqid() . '.csv';
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'update_products.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->post('/update-products/store', [
            'products_csv' => $uploadedFile,
        ]);

        $response->assertRedirect('update-products');

        $updatedProduct = Product::find($product->id);
        $this->assertEquals($newCategory->id, $updatedProduct->category_id);

        $updatedVariation = Variation::where('product_id', $product->id)->first();
        $this->assertEquals(25000, (float)$updatedVariation->default_sell_price);
    }
}
