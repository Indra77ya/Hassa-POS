<?php

namespace Tests\Feature;

use App\Business;
use App\Category;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\Repair\Entities\RepairTradeIn;
use Modules\Repair\Utils\RepairUtil;
use Tests\TestCase;

class RepairTradeInTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Modules\Repair\Providers\RepairServiceProvider::class)) {
            $this->app->register(\Modules\Repair\Providers\RepairServiceProvider::class);
        }

        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('repair_settings')->nullable();
            $table->timestamps();
        });

        \DB::table('business')->insert([
            'id' => 1,
            'name' => 'Repair TradeIn Business',
        ]);

        // Create categories table
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('short_code')->nullable();
            $table->integer('parent_id')->default(0);
            $table->string('category_type')->default('product');
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
            $table->timestamps();
        });

        Unit::create([
            'id' => 1,
            'business_id' => 1,
            'actual_name' => 'Piece',
            'short_name' => 'Pc',
        ]);

        // Create products table
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('type')->default('single');
            $table->integer('unit_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('enable_stock')->default(1);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('DUMMY');
            $table->integer('product_id');
            $table->string('sub_sku')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create variation_location_details table
        Schema::dropIfExists('variation_location_details');
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variation_id');
            $table->integer('product_id');
            $table->integer('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transactions table
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id')->default(1);
            $table->string('type');
            $table->string('status');
            $table->string('payment_status')->default('due');
            $table->integer('contact_id')->default(1);
            $table->string('ref_no')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->decimal('grand_total', 22, 4)->default(0);
            $table->integer('created_by')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();
        });

        // Create transaction_payments table
        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->dateTime('paid_on')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Create purchase_lines table
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(1);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create repair_trade_ins table
        Schema::dropIfExists('repair_trade_ins');
        Schema::create('repair_trade_ins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('transaction_id')->unsigned()->nullable()->index();
            $table->integer('job_sheet_id')->unsigned()->nullable()->index();
            $table->integer('product_id')->unsigned()->nullable()->index();
            $table->integer('purchase_transaction_id')->unsigned()->nullable()->index();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('serial_no')->nullable();
            $table->string('condition')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_creates_trade_in_product_and_purchase_transaction()
    {
        $sale = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'sell',
            'status' => 'final',
            'contact_id' => 1,
            'final_total' => 1500000,
            'created_by' => 1,
        ]);

        $repairUtil = new RepairUtil();
        $tradeInRecord = $repairUtil->saveOrUpdateTradeIn(
            1,
            1,
            [
                'amount' => 500000,
                'item_details' => [
                    'device_name' => 'iPhone 11 Bekas',
                    'serial_no' => 'SN123456789',
                    'condition' => 'Mulus 90%',
                ],
            ],
            $sale->id
        );

        $this->assertNotNull($tradeInRecord);
        $this->assertEquals(500000, $tradeInRecord->amount);
        $this->assertEquals('SN123456789', $tradeInRecord->serial_no);
        $this->assertEquals('Mulus 90%', $tradeInRecord->condition);

        // Verify registered product
        $product = Product::find($tradeInRecord->product_id);
        $this->assertNotNull($product);
        $this->assertStringContainsString('iPhone 11 Bekas', $product->name);
        $this->assertEquals(1, $product->enable_stock);

        // Verify purchase transaction
        $purchase = Transaction::find($tradeInRecord->purchase_transaction_id);
        $this->assertNotNull($purchase);
        $this->assertEquals('purchase', $purchase->type);
        $this->assertEquals('received', $purchase->status);
        $this->assertEquals('paid', $purchase->payment_status);
        $this->assertEquals(500000, $purchase->final_total);

        // Verify purchase line
        $purchaseLine = PurchaseLine::where('transaction_id', $purchase->id)->first();
        $this->assertNotNull($purchaseLine);
        $this->assertEquals($product->id, $purchaseLine->product_id);
        $this->assertEquals(1, $purchaseLine->quantity);
    }
}
