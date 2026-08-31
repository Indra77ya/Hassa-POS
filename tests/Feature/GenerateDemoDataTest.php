<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Business;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Modules\Superadmin\Http\Controllers\BusinessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use DB;

class GenerateDemoDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Bypass Spatie permission DB queries
        Gate::before(function () {
            return true;
        });

        // Set up essential tables in SQLite memory for testing
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('location_id')->nullable();
            $table->integer('invoice_layout_id')->nullable();
            $table->integer('invoice_scheme_id')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('landmark')->nullable();
            $table->string('accounting_default_map')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('customer_groups');
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 5, 2)->default(0);
            $table->string('price_calculation_type')->default('percentage');
            $table->integer('selling_price_group_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('invoice_layouts');
        Schema::create('invoice_layouts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('Default');
            $table->integer('business_id');
            $table->boolean('is_default')->default(1);
            $table->timestamps();
        });

        Schema::dropIfExists('invoice_schemes');
        Schema::create('invoice_schemes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('Default');
            $table->integer('business_id');
            $table->boolean('is_default')->default(1);
            $table->timestamps();
        });

        Schema::dropIfExists('reference_counts');
        Schema::create('reference_counts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_type');
            $table->integer('ref_count')->default(1);
            $table->integer('business_id');
            $table->timestamps();
        });

        Schema::dropIfExists('units');
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category_type')->default('product');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('brands');
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('created_by')->nullable();
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

        Schema::dropIfExists('variation_templates');
        Schema::create('variation_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->timestamps();
        });

        Schema::dropIfExists('variation_value_templates');
        Schema::create('variation_value_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('variation_template_id');
            $table->timestamps();
        });

        Schema::dropIfExists('contacts');
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('type');
            $table->string('name');
            $table->string('supplier_business_name')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('customer_group_id')->nullable();
            $table->boolean('is_default')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('type');
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('tax_type')->nullable();
            $table->string('barcode_type')->nullable();
            $table->boolean('enable_stock')->default(1);
            $table->decimal('alert_quantity', 22, 4)->default(0);
            $table->string('sku')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('product_locations');
        Schema::create('product_locations', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('location_id');
        });

        Schema::dropIfExists('product_variations');
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->string('name');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();
        });

        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('product_variation_id');
            $table->string('name');
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

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

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('contact_no')->nullable();
            $table->integer('business_id')->nullable();
            $table->string('user_type')->default('user');
            $table->string('status')->default('active');
            $table->boolean('is_cmmsn_agnt')->default(0);
            $table->decimal('cmmsn_percent', 5, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->integer('contact_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4);
            $table->decimal('purchase_price', 22, 4);
            $table->decimal('purchase_price_inc_tax', 22, 4);
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4);
            $table->decimal('unit_price', 22, 4);
            $table->decimal('unit_price_inc_tax', 22, 4);
            $table->decimal('unit_price_before_discount', 22, 4);
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->nullable();
            $table->dateTime('paid_on')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('payment_for')->nullable();
            $table->boolean('is_advance')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::dropIfExists('model_has_roles');
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id');
            $table->string('model_type');
            $table->integer('model_id');
        });

        Schema::dropIfExists('role_has_permissions');
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('permission_id');
        });

        Schema::dropIfExists('permissions');
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        $this->business = Business::create([
            'id' => 1,
            'name' => 'Demo Test Business',
        ]);

        DB::table('invoice_layouts')->insert([
            'id' => 1,
            'name' => 'Default',
            'business_id' => 1,
            'is_default' => 1,
        ]);

        DB::table('invoice_schemes')->insert([
            'id' => 1,
            'name' => 'Default',
            'business_id' => 1,
            'is_default' => 1,
        ]);
    }

    /** @test */
    public function superadmin_can_generate_custom_demo_data()
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);

        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $request = new Request();
        $request->merge([
            'reset_old_data' => 1,
            'num_users' => 3,
            'num_suppliers' => 4,
            'num_customers' => 4,
            'num_products' => 5,
            'num_variations' => 2,
            'num_units' => 3,
            'num_categories' => 3,
            'num_brands' => 3,
            'num_warranties' => 2,
            'num_transactions' => 6,
        ]);

        $response = $controller->postGenerateDemo($request, 1);
        $result = $response->getData(true);

        $this->assertTrue($result['success']);

        // Assert database counts
        $this->assertEquals(3, DB::table('units')->where('business_id', 1)->count());
        $this->assertEquals(3, DB::table('categories')->where('business_id', 1)->count());
        $this->assertEquals(3, DB::table('brands')->where('business_id', 1)->count());
        $this->assertEquals(2, DB::table('warranties')->where('business_id', 1)->count());
        $this->assertEquals(4, DB::table('contacts')->where('business_id', 1)->where('type', 'supplier')->count());
        $this->assertEquals(5, DB::table('products')->where('business_id', 1)->count());
        $this->assertEquals(6, DB::table('transactions')->where('business_id', 1)->count());
    }
}
