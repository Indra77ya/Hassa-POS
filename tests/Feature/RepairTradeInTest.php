<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\Schema;
use Modules\Repair\Entities\JobSheet;
use Modules\Repair\Entities\RepairTradeIn;
use Tests\TestCase;

class RepairTradeInTest extends TestCase
{
    protected $user;
    protected $business;
    protected $location;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('product_locations');
        Schema::dropIfExists('variation_location_details');
        Schema::dropIfExists('repair_trade_ins');
        Schema::dropIfExists('repair_job_sheets');
        Schema::dropIfExists('transaction_payments');
        Schema::dropIfExists('purchase_lines');
        Schema::dropIfExists('transaction_sell_lines');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('variations');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('business_locations');
        Schema::dropIfExists('business');
        Schema::dropIfExists('users');

        Schema::create('users', function ($table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->timestamps();
        });

        Schema::create('business', function ($table) {
            $table->id();
            $table->string('name')->default('Test');
            $table->integer('currency_id')->default(1);
            $table->string('start_date')->default('2026-01-01');
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->text('repair_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('business_locations', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('name')->default('Main Shop');
            $table->string('location_id')->nullable();
            $table->timestamps();
        });

        Schema::create('contacts', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('type')->default('customer');
            $table->string('name');
            $table->string('contact_id')->nullable();
            $table->string('mobile')->nullable();
            $table->decimal('balance', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->timestamps();
        });

        Schema::create('brands', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('categories', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->string('category_type')->default('product');
            $table->timestamps();
        });

        Schema::create('units', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('actual_name')->default('Pc');
            $table->string('short_name')->default('Pc');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->integer('unit_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(1);
            $table->string('sku');
            $table->text('product_description')->nullable();
            $table->integer('created_by');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_locations', function ($table) {
            $table->integer('product_id');
            $table->integer('location_id');
        });

        Schema::create('variation_location_details', function ($table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->integer('location_id');
            $table->integer('product_variation_id')->default(0);
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('product_variations', function ($table) {
            $table->id();
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();
        });

        Schema::create('variations', function ($table) {
            $table->id();
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->integer('product_variation_id')->default(0);
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('transactions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->default('sell');
            $table->string('status')->default('final');
            $table->string('payment_status')->default('due');
            $table->integer('contact_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_payments', function ($table) {
            $table->id();
            $table->integer('transaction_id');
            $table->integer('business_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->default('cash');
            $table->dateTime('paid_on')->nullable();
            $table->integer('created_by')->default(1);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_lines', function ($table) {
            $table->id();
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(1);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->decimal('quantity_sold', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('repair_job_sheets', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->integer('contact_id');
            $table->string('job_sheet_no')->nullable();
            $table->string('service_type')->default('carry_in');
            $table->string('serial_no')->nullable();
            $table->integer('status_id')->nullable();
            $table->decimal('estimated_cost', 22, 4)->default(0);
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::create('repair_trade_ins', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('job_sheet_id')->nullable();
            $table->integer('purchase_transaction_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->string('device_name');
            $table->integer('brand_id')->nullable();
            $table->integer('device_model_id')->nullable();
            $table->string('serial_no')->nullable();
            $table->text('condition_notes')->nullable();
            $table->integer('category_id')->nullable();
            $table->decimal('valuation_amount', 22, 4)->default(0);
            $table->decimal('selling_price', 22, 4)->default(0);
            $table->integer('created_by');
            $table->timestamps();
        });

        $this->user = \Mockery::mock(User::class)->makePartial();
        $this->user->id = 1;
        $this->user->business_id = 1;
        $this->user->surname = 'Admin';
        $this->user->first_name = 'User';
        $this->user->email = 'admin@test.com';
        $this->user->shouldReceive('permitted_locations')->andReturn('all');
        $this->user->shouldReceive('can')->andReturn(true);
        $this->user->shouldReceive('hasPermissionTo')->andReturn(true);
        $this->user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $this->business = Business::create([
            'id' => 1,
            'name' => 'Test Business TradeIn',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'time_zone' => 'Asia/Jakarta',
        ]);

        $this->location = BusinessLocation::create([
            'id' => 1,
            'business_id' => $this->business->id,
            'name' => 'Main Shop',
            'location_id' => 'LOC001',
        ]);

        $this->customer = Contact::create([
            'id' => 1,
            'business_id' => $this->business->id,
            'type' => 'customer',
            'name' => 'John Doe TradeIn',
            'contact_id' => 'CUST001',
            'mobile' => '08123456789',
        ]);

        $this->actingAs($this->user);
        session(['user.business_id' => $this->business->id, 'user.id' => $this->user->id]);
    }

    public function test_save_or_update_trade_in_creates_trade_in_record_product_and_purchase()
    {
        $repairUtil = new \Modules\Repair\Utils\RepairUtil();

        $trade_in_data = [
            'device_name' => 'iPhone 11 Second',
            'serial_no' => 'IMEI999888',
            'valuation_amount' => '3000000',
            'selling_price' => '3800000',
            'condition_notes' => 'Layar mulus, baterai 80%',
            'location_id' => $this->location->id,
            'contact_id' => $this->customer->id,
        ];

        $trade_in = $repairUtil->saveOrUpdateTradeIn($this->business->id, $this->user->id, $trade_in_data, null, 101);

        $this->assertNotNull($trade_in);
        $this->assertEquals('iPhone 11 Second', $trade_in->device_name);
        $this->assertEquals(3000000, (float)$trade_in->valuation_amount);

        // Assert product auto-created
        $product = Product::find($trade_in->product_id);
        $this->assertNotNull($product);
        $this->assertStringContainsString('iPhone 11 Second', $product->name);

        // Assert purchase transaction created
        $purchase = Transaction::find($trade_in->purchase_transaction_id);
        $this->assertNotNull($purchase);
        $this->assertEquals('purchase', $purchase->type);
        $this->assertEquals(3000000, (float)$purchase->final_total);
    }
}
