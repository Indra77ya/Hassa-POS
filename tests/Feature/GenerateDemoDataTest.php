<?php

namespace Tests\Feature;

use App\Business;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\DemoDataUtil;
use App\Utils\ModuleUtil;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\Superadmin\Http\Controllers\BusinessController;
use Tests\TestCase;

class GenerateDemoDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function () {
            return true;
        });

        // Reference counts
        Schema::dropIfExists('reference_counts');
        Schema::create('reference_counts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('ref_type');
            $table->integer('ref_count')->default(1);
            $table->timestamps();
        });

        // Business
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('owner_id')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('start_date')->nullable();
            $table->string('time_zone')->nullable();
            $table->text('enabled_modules')->nullable();
            $table->text('ref_no_prefixes')->nullable();
            $table->string('date_format')->nullable();
            $table->string('time_format')->nullable();
            $table->timestamps();
        });

        // Users
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        // Contacts
        Schema::dropIfExists('contacts');
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('contact_id')->nullable();
            $table->integer('customer_group_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('mobile')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->boolean('is_default')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Expense categories
        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Business locations
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // Res tables
        Schema::dropIfExists('res_tables');
        Schema::create('res_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('name');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Units
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

        // Warranties
        Schema::dropIfExists('warranties');
        Schema::create('warranties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration')->default(0);
            $table->string('duration_type')->nullable();
            $table->timestamps();
        });

        // Customer groups
        Schema::dropIfExists('customer_groups');
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('price_calculation_type')->nullable();
            $table->integer('selling_price_group_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Brands
        Schema::dropIfExists('brands');
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Categories
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('category_type')->default('product');
            $table->integer('parent_id')->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Products
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('type')->default('single');
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->string('barcode_type')->default('C128');
            $table->boolean('enable_stock')->default(1);
            $table->string('sku')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('warranty_id')->nullable();
            $table->decimal('alert_quantity', 22, 4)->default(0);
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
            $table->string('sub_sku')->nullable();
            $table->integer('product_variation_id')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Product Locations
        Schema::dropIfExists('product_locations');
        Schema::create('product_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('location_id');
            $table->timestamps();
        });

        // Variation location details
        Schema::dropIfExists('variation_location_details');
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('product_variation_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

        // Tax rates
        Schema::dropIfExists('tax_rates');
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4)->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Discounts
        Schema::dropIfExists('discounts');
        Schema::create('discounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->integer('priority')->default(1);
            $table->string('discount_type')->nullable();
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // Transactions
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_direct_sale')->default(0);
            $table->boolean('is_quotation')->default(0);
            $table->string('sub_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->integer('contact_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('ref_no')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->boolean('is_kitchen_order')->default(0);
            $table->integer('res_table_id')->nullable();
            $table->integer('res_waiter_id')->nullable();
            $table->string('res_order_status')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('expense_category_id')->nullable();
            $table->integer('expense_for')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Transaction sell lines
        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->decimal('unit_price_before_discount', 22, 4)->default(0);
            $table->string('res_line_order_status')->nullable();
            $table->integer('res_service_staff_id')->nullable();
            $table->integer('parent_sell_line_id')->nullable();
            $table->string('children_type')->nullable();
            $table->timestamps();
        });

        // Purchase lines
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Stock adjustment lines
        Schema::dropIfExists('stock_adjustment_lines');
        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        // Transaction payments
        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->nullable();
            $table->timestamp('paid_on')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('payment_for')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->integer('account_id')->nullable();
            $table->timestamps();
        });

        // Accounts
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->integer('account_type_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Account types
        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        // Account transactions
        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('reff_no')->nullable();
            $table->timestamp('operation_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->timestamps();
        });

        // Cash registers
        Schema::dropIfExists('cash_registers');
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        // Cash register transactions
        Schema::dropIfExists('cash_register_transactions');
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cash_register_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('pay_method')->nullable();
            $table->string('type')->nullable();
            $table->string('transaction_type')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        // Bookings
        Schema::dropIfExists('bookings');
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('waiter_id')->nullable();
            $table->integer('table_id')->nullable();
            $table->timestamp('booking_start')->nullable();
            $table->timestamp('booking_end')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('booking_status')->nullable();
            $table->text('booking_note')->nullable();
            $table->timestamps();
        });
    }

    public function test_generate_demo_data_util_and_controller()
    {
        $business = Business::create([
            'id' => 1,
            'name' => 'Test Business',
        ]);

        $user = User::create([
            'id' => 1,
            'surname' => 'Admin',
            'first_name' => 'Demo',
            'username' => 'demoadmin',
            'email' => 'demoadmin@example.com',
            'business_id' => 1,
        ]);

        $demoUtil = new DemoDataUtil();
        $result = $demoUtil->generateDemoData($business->id);

        $this->assertTrue($result);

        // Assert database records created
        $this->assertDatabaseHas('products', ['business_id' => $business->id]);
        $this->assertDatabaseHas('contacts', ['business_id' => $business->id]);
        $this->assertDatabaseHas('transactions', ['business_id' => $business->id]);
        $this->assertDatabaseHas('accounts', ['business_id' => $business->id]);
        $this->assertDatabaseHas('cash_registers', ['business_id' => $business->id]);

        // Mock superadmin user and test controller action
        $mockUser = \Mockery::mock(\App\User::class)->makePartial();
        $mockUser->shouldReceive('can')->with('superadmin')->andReturn(true);
        $mockUser->id = 1;
        $mockUser->business_id = 1;
        $this->actingAs($mockUser);

        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $response = $controller->generateDemoData(1);
        $resData = $response->getData(true);

        $this->assertTrue($resData['success']);
    }
}
