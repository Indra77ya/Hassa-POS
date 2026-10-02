<?php

namespace Tests\Feature;

use App\Business;
use App\Category;
use App\Product;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DeleteProductFixTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';

        Schema::dropIfExists('media');
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('model_id');
            $table->string('model_type');
            $table->string('file_name');
            $table->timestamps();
        });

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

        Schema::dropIfExists('notifications');
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('currency_id')->default('1');
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->integer('fy_start_month')->default(1);
            $table->string('accounting_method')->default('fifo');
            $table->decimal('default_profit_percent', 5, 2)->default(25.00);
            $table->integer('owner_id')->nullable();
            $table->string('time_format')->default('24');
            $table->string('date_format')->default('m/d/Y');
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('location_id')->nullable();
            $table->string('name');
            $table->string('landmark')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('receipt_printer_type')->default('browser');
            $table->integer('selling_price_group_id')->nullable();
            $table->text('default_payment_accounts')->nullable();
            $table->integer('invoice_scheme_id')->nullable();
            $table->integer('invoice_layout_id')->nullable();
            $table->integer('sale_invoice_scheme_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('short_code')->nullable();
            $table->integer('parent_id')->default(0);
            $table->string('category_type')->default('product');
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('units');
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('brands');
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->decimal('amount', 22, 4);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('warranties');
        Schema::create('warranties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->string('duration_type');
            $table->timestamps();
        });

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

        Schema::dropIfExists('product_serial_numbers');
        Schema::create('product_serial_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('product_id');
            $table->integer('variation_id')->nullable();
            $table->string('serial_number');
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('selling_price', 22, 4)->default(0);
            $table->string('status')->default('in_stock');
            $table->timestamps();
        });

        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->string('sku')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(1);
            $table->boolean('enable_sr_no')->default(0);
            $table->boolean('is_inactive')->default(0);
            $table->boolean('not_for_selling')->default(0);
            $table->string('image')->nullable();
            $table->decimal('alert_quantity', 22, 4)->nullable();
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
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->timestamps();
        });

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

        Schema::dropIfExists('variation_group_prices');
        Schema::create('variation_group_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variation_id');
            $table->integer('price_group_id');
            $table->decimal('price_inc_tax', 22, 4);
            $table->string('price_type')->default('fixed');
            $table->timestamps();
        });

        Schema::dropIfExists('variation_location_details');
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->integer('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

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

        Schema::dropIfExists('product_locations');
        Schema::create('product_locations', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('location_id');
        });

        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('quantity_sold', 22, 4)->default(0);
            $table->decimal('quantity_adjusted', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('type');
            $table->string('status');
            $table->timestamps();
        });

        $user = User::create([
            'id' => 1,
            'first_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'business_id' => 1,
            'user_type' => 'user',
            'allow_login' => 1,
        ]);

        Permission::create(['name' => 'product.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.delete', 'guard_name' => 'web']);
        Permission::create(['name' => 'access_all_locations', 'guard_name' => 'web']);
        $user->givePermissionTo('product.view');
        $user->givePermissionTo('product.create');
        $user->givePermissionTo('product.delete');
        $user->givePermissionTo('access_all_locations');

        $this->actingAs($user);
        $this->withoutMiddleware([
            \App\Http\Middleware\AdminSidebarMenu::class,
            \App\Http\Middleware\CheckUserLogin::class,
            \App\Http\Middleware\IsInstalled::class
        ]);
    }

    public function test_delete_product_action_link_and_ajax_destroy()
    {
        $business_id = 1;

        $business = Business::create([
            'id' => $business_id,
            'name' => 'Delete Test Business',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        $unit = Unit::create([
            'business_id' => $business_id,
            'actual_name' => 'Unit',
            'short_name' => 'Unit',
        ]);

        $product = Product::create([
            'name' => 'Surface Laptop 3',
            'business_id' => $business_id,
            'type' => 'single',
            'unit_id' => $unit->id,
            'sku' => 'KON-001',
            'enable_stock' => 1,
            'created_by' => 1,
        ]);

        Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => 'KON-001',
            'product_variation_id' => 1,
            'default_purchase_price' => 4000000,
            'dpp_inc_tax' => 4000000,
            'profit_percent' => 25,
            'default_sell_price' => 5000000,
            'sell_price_inc_tax' => 5000000,
        ]);

        $sessionData = [
            'user' => ['id' => 1, 'business_id' => $business_id],
            'business' => $business,
            'currency' => ['symbol' => 'Rp', 'decimal_separator' => '.', 'thousand_separator' => ','],
            'financial_year' => ['start' => '2026-01-01', 'end' => '2026-12-31'],
        ];

        // 1. Check DataTable action column contains href="#" and data-href="..."
        $response = $this->withSession($sessionData)->getJson('/products', ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);

        $json = $response->json();
        $actionHtml = $json['data'][0]['action'];

        $this->assertStringContainsString('href="#"', $actionHtml);
        $this->assertStringContainsString('data-href="', $actionHtml);
        $this->assertStringContainsString('/products/' . $product->id, $actionHtml);

        // 2. Perform AJAX DELETE request
        $deleteResponse = $this->withSession($sessionData)->delete('/products/' . $product->id, [], ['X-Requested-With' => 'XMLHttpRequest']);
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_show_product_endpoint_renders_view_modal()
    {
        $business_id = 1;

        $business = Business::create([
            'id' => $business_id,
            'name' => 'Show Test Business',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
        ]);

        $unit = Unit::create([
            'business_id' => $business_id,
            'actual_name' => 'Unit',
            'short_name' => 'Unit',
        ]);

        $product = Product::create([
            'name' => 'Test Laptop',
            'business_id' => $business_id,
            'type' => 'single',
            'unit_id' => $unit->id,
            'sku' => 'LAP-123',
            'enable_stock' => 1,
            'created_by' => 1,
        ]);

        Variation::create([
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => 'LAP-123',
            'product_variation_id' => 1,
            'default_purchase_price' => 1000,
            'dpp_inc_tax' => 1000,
            'profit_percent' => 10,
            'default_sell_price' => 1100,
            'sell_price_inc_tax' => 1100,
        ]);

        $sessionData = [
            'user' => ['id' => 1, 'business_id' => $business_id],
            'business' => $business,
            'currency' => ['symbol' => 'Rp', 'decimal_separator' => '.', 'thousand_separator' => ','],
            'financial_year' => ['start' => '2026-01-01', 'end' => '2026-12-31'],
        ];

        // GET /products/{id} should render product modal details
        $response = $this->withSession($sessionData)->get('/products/' . $product->id);
        $response->assertStatus(200);
        $response->assertSee('Test Laptop');
        $response->assertSee('LAP-123');
    }
}
