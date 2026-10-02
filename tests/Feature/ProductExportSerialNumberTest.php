<?php

namespace Tests\Feature;

use App\Business;
use App\User;
use App\Product;
use App\Unit;
use App\ProductSerialNumber;
use App\Exports\ProductsExport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductExportSerialNumberTest extends TestCase
{
    protected $user;
    protected $business;

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

        // Selling Price Groups
        Schema::dropIfExists('selling_price_groups');
        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('business_id');
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
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
            $table->text('keyboard_shortcuts')->nullable();
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

        // Create Seed Data
        $this->business = Business::create([
            'name' => 'Test Business',
            'currency_id' => 1,
        ]);

        $this->user = User::create([
            'first_name' => 'Admin',
            'username' => 'admin_sn_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id,
            'user_type' => 'user',
        ]);

        Permission::firstOrCreate(['name' => 'product.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'product.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access_all_locations', 'guard_name' => 'web']);
        $this->user->givePermissionTo(['product.view', 'product.create', 'access_all_locations']);

        $this->actingAs($this->user);
        $this->withoutMiddleware([
            \App\Http\Middleware\AdminSidebarMenu::class,
            \App\Http\Middleware\CheckUserLogin::class,
            \App\Http\Middleware\IsInstalled::class
        ]);
    }

    public function test_products_export_array_includes_serial_numbers()
    {
        $product = Product::create([
            'name' => 'Test Laptop Export',
            'business_id' => $this->business->id,
            'type' => 'single',
            'sku' => 'TEST-SN-EXP-01',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => $this->user->id,
        ]);

        $pv = \App\ProductVariation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        \App\Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv->id,
            'sub_sku' => 'TEST-SN-EXP-01',
            'default_purchase_price' => 100,
            'dpp_inc_tax' => 100,
            'profit_percent' => 10,
            'default_sell_price' => 110,
            'sell_price_inc_tax' => 110,
        ]);

        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'serial_number' => 'SN-EXPORT-101',
            'status' => 'in_stock',
        ]);

        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'serial_number' => 'SN-EXPORT-102',
            'status' => 'in_stock',
        ]);

        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'serial_number' => 'SN-SOLD-999',
            'status' => 'sold',
        ]);

        request()->setLaravelSession(app('session')->driver());
        request()->session()->put('user.business_id', $this->business->id);

        $export = new ProductsExport();
        $array = $export->array();

        $headers = $array[0];
        $this->assertContains('SERIAL NUMBER / IMEI', $headers);

        $sn_index = array_search('SERIAL NUMBER / IMEI', $headers);
        $this->assertNotFalse($sn_index);

        $exported_sns = [];
        foreach ($array as $row) {
            if (isset($row[0]) && $row[0] === 'Test Laptop Export') {
                $exported_sns[] = $row[$sn_index];
            }
        }

        $this->assertContains('SN-EXPORT-101', $exported_sns);
        $this->assertContains('SN-EXPORT-102', $exported_sns);
        $this->assertNotContains('SN-SOLD-999', $exported_sns);
    }

    public function test_product_datatables_includes_serial_numbers()
    {
        $unit = Unit::create([
            'business_id' => $this->business->id,
            'actual_name' => 'Pieces',
            'short_name' => 'Pc',
        ]);

        $product = Product::create([
            'name' => 'Test Phone DT',
            'business_id' => $this->business->id,
            'type' => 'single',
            'unit_id' => $unit->id,
            'sku' => 'TEST-SN-DT-01',
            'enable_stock' => 1,
            'enable_sr_no' => 1,
            'created_by' => $this->user->id,
        ]);

        $pv = \App\ProductVariation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'is_dummy' => 1,
        ]);

        \App\Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'product_variation_id' => $pv->id,
            'sub_sku' => 'TEST-SN-DT-01',
            'default_purchase_price' => 100,
            'dpp_inc_tax' => 100,
            'profit_percent' => 10,
            'default_sell_price' => 110,
            'sell_price_inc_tax' => 110,
        ]);

        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'serial_number' => 'IMEI-DT-001',
            'status' => 'in_stock',
        ]);

        ProductSerialNumber::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'serial_number' => 'IMEI-DT-002',
            'status' => 'in_stock',
        ]);

        $response = $this->withSession(['user.business_id' => $this->business->id])
            ->getJson('/products', [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);

        $json = $response->json();
        $data = $json['data'] ?? [];

        $found = false;
        foreach ($data as $item) {
            if (isset($item['sku']) && $item['sku'] === 'TEST-SN-DT-01') {
                $this->assertStringContainsString('IMEI-DT-001, IMEI-DT-002', $item['serial_numbers']);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Product with serial numbers was not found in datatables response');
    }
}
