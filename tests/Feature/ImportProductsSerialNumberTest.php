<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\ProductSerialNumber;
use App\Unit;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ImportProductsSerialNumberTest extends TestCase
{
    protected $user;

    protected $business;

    protected $location;

    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';

        // Media
        Schema::dropIfExists('media');
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('model_id');
            $table->string('model_type');
            $table->string('file_name');
            $table->timestamps();
        });

        // Roles & Permissions
        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->integer('business_id')->default(1);
            $table->timestamps();
        });

        Schema::dropIfExists('permissions');
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::dropIfExists('model_has_roles');
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id');
            $table->string('model_type');
            $table->integer('model_id');
        });

        Schema::dropIfExists('model_has_permissions');
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->string('model_type');
            $table->integer('model_id');
        });

        Schema::dropIfExists('role_has_permissions');
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('role_id');
        });

        // Users
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->char('language', 7)->default('en');
            $table->integer('business_id');
            $table->string('user_type')->default('user');
            $table->boolean('allow_login')->default(1);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        // Business
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('currency_id')->default(1);
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->integer('owner_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->decimal('default_profit_percent', 5, 2)->default(20.00);
            $table->timestamps();
        });

        // Business Locations
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('location_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // Units
        Schema::dropIfExists('units');
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->integer('allow_decimal')->default(0);
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Brands
        Schema::dropIfExists('brands');
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Categories
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('short_code')->nullable();
            $table->integer('parent_id')->default(0);
            $table->string('category_type')->default('product');
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Tax Rates
        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->float('amount', 8, 2);
            $table->integer('is_tax_group')->default(0);
            $table->integer('created_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Products
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->integer('unit_id')->nullable();
            $table->integer('sub_unit_ids')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(0);
            $table->decimal('alert_quantity', 22, 4)->default(0);
            $table->string('sku');
            $table->string('barcode_type')->default('C128');
            $table->decimal('expiry_period', 4, 2)->nullable();
            $table->string('expiry_period_type')->nullable();
            $table->boolean('enable_sr_no')->default(0);
            $table->string('weight')->nullable();
            $table->string('image')->nullable();
            $table->text('product_description')->nullable();
            $table->integer('created_by')->nullable();
            $table->boolean('is_inactive')->default(0);
            $table->boolean('not_for_selling')->default(0);
            $table->string('product_custom_field1')->nullable();
            $table->string('product_custom_field2')->nullable();
            $table->string('product_custom_field3')->nullable();
            $table->string('product_custom_field4')->nullable();
            $table->string('product_custom_field5')->nullable();
            $table->string('product_custom_field6')->nullable();
            $table->string('product_custom_field7')->nullable();
            $table->string('product_custom_field8')->nullable();
            $table->string('product_custom_field9')->nullable();
            $table->string('product_custom_field10')->nullable();
            $table->string('product_custom_field11')->nullable();
            $table->string('product_custom_field12')->nullable();
            $table->string('product_custom_field13')->nullable();
            $table->string('product_custom_field14')->nullable();
            $table->string('product_custom_field15')->nullable();
            $table->string('product_custom_field16')->nullable();
            $table->string('product_custom_field17')->nullable();
            $table->string('product_custom_field18')->nullable();
            $table->string('product_custom_field19')->nullable();
            $table->string('product_custom_field20')->nullable();
            $table->timestamps();
        });

        // Product Variations
        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();
        });

        // Variations
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('product_id');
            $table->integer('product_variation_id');
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Product Locations
        Schema::dropIfExists('product_locations');
        Schema::create('product_locations', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('location_id');
        });

        // Variation Location Details
        Schema::dropIfExists('variation_location_details');
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('product_variation_id');
            $table->integer('variation_id');
            $table->integer('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

        // Product Serial Numbers
        Schema::dropIfExists('product_serial_numbers');
        Schema::create('product_serial_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('product_id');
            $table->integer('variation_id')->nullable();
            $table->integer('purchase_line_id')->nullable();
            $table->integer('transaction_sell_line_id')->nullable();
            $table->string('serial_number');
            $table->decimal('purchase_price', 22, 4)->nullable();
            $table->decimal('selling_price', 22, 4)->nullable();
            $table->string('status')->default('in_stock');
            $table->timestamps();
        });

        // Product Racks
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

        // Purchase Lines
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('pp_without_discount', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->integer('tax_id')->nullable();
            $table->date('exp_date')->nullable();
            $table->timestamps();
        });

        // Transactions
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type');
            $table->string('status')->default('received');
            $table->string('payment_status')->default('paid');
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('opening_stock_product_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create Seed Data
        $this->business = Business::create([
            'name' => 'Test Business SN Import',
            'currency_id' => 1,
            'default_profit_percent' => 20.00,
        ]);

        $this->user = User::create([
            'first_name' => 'Admin',
            'username' => 'admin_sn_test_imp',
            'email' => 'admin_sn_imp@test.com',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id,
            'user_type' => 'user',
        ]);

        $this->location = BusinessLocation::create([
            'business_id' => $this->business->id,
            'name' => 'Matahari Laptop Unnes',
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'actual_name' => 'Pcs',
            'short_name' => 'Pcs',
            'allow_decimal' => 0,
            'created_by' => $this->user->id,
        ]);

        Permission::firstOrCreate(['name' => 'product.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'product.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access_all_locations', 'guard_name' => 'web']);
        $this->user->givePermissionTo(['product.view', 'product.create', 'access_all_locations']);

        $this->actingAs($this->user);
        $this->withoutMiddleware([
            \App\Http\Middleware\AdminSidebarMenu::class,
            \App\Http\Middleware\CheckUserLogin::class,
            \App\Http\Middleware\IsInstalled::class,
        ]);

        session([
            'user.business_id' => $this->business->id,
            'user.id' => $this->user->id,
            'business.default_profit_percent' => 20,
            'financial_year.start' => date('Y') . '-01-01',
        ]);
    }

    public function test_import_products_with_serial_numbers()
    {
        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "iPhone 15 Pro,Apple,Pcs,Elektronik,Handphone,IP15P-IMP,C128,1,5,,,,exclusive,single,,,,15000000,15000000,20,18000000,1,Matahari Laptop Unnes,,1,0.2,,,,,Test,,,,,0,Matahari Laptop Unnes,percentage,SN-IMP-001,15000000,18000000\n" .
            "iPhone 15 Pro,Apple,Pcs,Elektronik,Handphone,IP15P-IMP,C128,1,5,,,,exclusive,single,,,,15000000,15000000,20,18000000,1,Matahari Laptop Unnes,,1,0.2,,,,,Test,,,,,0,Matahari Laptop Unnes,percentage,SN-IMP-002,16000000,19000000\n";

        $file = UploadedFile::fake()->createWithContent('import_products.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('status');

        // Assert 1 product created with enable_sr_no = 1
        $product = Product::where('business_id', $this->business->id)->where('sku', 'IP15P-IMP')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->enable_sr_no);

        // Assert serial numbers created
        $serials = ProductSerialNumber::where('business_id', $this->business->id)->where('product_id', $product->id)->get();
        $this->assertCount(2, $serials);

        $sn1 = $serials->where('serial_number', 'SN-IMP-001')->first();
        $this->assertNotNull($sn1);
        $this->assertEquals(15000000, $sn1->purchase_price);
        $this->assertEquals(18000000, $sn1->selling_price);

        $sn2 = $serials->where('serial_number', 'SN-IMP-002')->first();
        $this->assertNotNull($sn2);
        $this->assertEquals(16000000, $sn2->purchase_price);
        $this->assertEquals(19000000, $sn2->selling_price);

        // Assert product variation prices were calculated as average (15.5m and 18.5m)
        $variation = $product->variations()->first();
        $this->assertGreaterThan(0, (float)$variation->default_purchase_price);
        $this->assertGreaterThan(0, (float)$variation->default_sell_price);
    }

    public function test_import_products_with_zero_opening_stock_does_not_add_current_stock()
    {
        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "Laptop A,Lenovo,Pcs,Laptop,Second,KON001,C128,1,5,,,,exclusive,single,,,,1200000,1200000,20,1700000,0,Matahari Laptop Unnes,,1,0.2,,,,,Test,,,,,0,Matahari Laptop Unnes,percentage,A1,1200000,1700000\n" .
            "Laptop A,Lenovo,Pcs,Laptop,Second,KON001,C128,1,5,,,,exclusive,single,,,,1300000,1300000,20,1800000,0,Matahari Laptop Unnes,,1,0.2,,,,,Test,,,,,0,Matahari Laptop Unnes,percentage,A2,1300000,1800000\n";

        $file = UploadedFile::fake()->createWithContent('import_products_zero_stock.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('status');

        $product = Product::where('business_id', $this->business->id)->where('sku', 'KON001')->first();
        $this->assertNotNull($product);

        // Current stock should be 0 (no in_stock serials registered)
        $serials = ProductSerialNumber::where('business_id', $this->business->id)->where('product_id', $product->id)->get();
        $this->assertCount(0, $serials);
    }

    public function test_import_products_rejects_opening_stock_greater_than_one_for_serial_numbers()
    {
        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "iPhone 15 Pro Invalid,Apple,Pcs,Elektronik,Handphone,IP15P-INV,C128,1,5,,,,exclusive,single,,,,15000000,15000000,20,18000000,5,Matahari Laptop Unnes,,1,0.2,,,,,Test,,,,,0,Matahari Laptop Unnes,percentage,SN-IMP-INV01,15000000,18000000\n";

        $file = UploadedFile::fake()->createWithContent('import_products_invalid.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('notification');
        $notification = session('notification');
        $this->assertEquals(0, $notification['success']);
        $this->assertStringContainsString('Serial number hanya boleh stoknya 1 aja', $notification['msg']);
    }

    public function test_import_products_rejects_duplicate_serial_number()
    {
        $product = Product::create([
            'name' => 'Laptop Existing',
            'business_id' => $this->business->id,
            'sku' => 'LAP-EXIST',
            'enable_sr_no' => 1,
        ]);

        // Register an existing in_stock serial number
        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'serial_number' => 'SN-DUP-EXIST',
            'status' => 'in_stock',
        ]);

        $csvHeader = "NAME,BRAND,UNIT,CATEGORY,SUB-CATEGORY,SKU,BARCODE TYPE,MANAGE STOCK,ALERT QUANTITY,EXPIRES IN,EXPIRY PERIOD UNIT,APPLICABLE TAX,Selling Price Tax Type,PRODUCT TYPE,VARIATION NAME,VARIATION VALUES,VARIATION SKU,PURCHASE PRICE (Including tax),PURCHASE PRICE (Excluding tax),PROFIT MARGIN,SELLING PRICE,OPENING STOCK,LOCATION,EXPIRY DATE,ENABLE IMEI OR SERIAL NUMBER,WEIGHT,RACK,ROW,POSITION,IMAGE,PRODUCT DESCRIPTION,CUSTOM FIELD 1,CUSTOM FIELD 2,CUSTOM FIELD 3,CUSTOM FIELD 4,NOT FOR SELLING,PRODUCT LOCATIONS,PROFIT MARGIN TYPE,SERIAL NUMBER,SERIAL PURCHASE PRICE,SERIAL SELLING PRICE\n";
        $csvContent = $csvHeader .
            "Laptop Gaming,Lenovo,Pcs,Laptop,Second,LAP-001,C128,1,5,,,,exclusive,single,,,,10000000,10000000,20,12000000,1,Matahari Laptop Unnes,,1,0.2,,,,,Test,,,,,0,Matahari Laptop Unnes,percentage,SN-DUP-EXIST,10000000,12000000\n";

        $file = UploadedFile::fake()->createWithContent('import_products.csv', $csvContent);

        $response = $this->post('/import-products/store', [
            'products_csv' => $file,
        ]);

        $response->assertRedirect('import-products');
        $response->assertSessionHas('notification');
        $notification = session('notification');
        $this->assertEquals(0, $notification['success']);
        $this->assertStringContainsString('SN-DUP-EXIST', $notification['msg']);
    }
}
