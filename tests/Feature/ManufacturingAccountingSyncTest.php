<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Business;
use App\BusinessLocation;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\Unit;
use App\User;
use App\Variation;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Manufacturing\Entities\MfgRecipe;
use Modules\Manufacturing\Entities\MfgRecipeIngredient;
use Modules\Manufacturing\Utils\ManufacturingUtil;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use DB;

class ManufacturingAccountingSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Accounting module provider
        if (class_exists(\Modules\Accounting\Providers\AccountingServiceProvider::class)) {
            $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        }
        request()->setLaravelSession($this->app['session']->driver());

        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('manufacturing_settings')->nullable();
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
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
            $table->integer('created_by')->nullable();
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

        // Create purchase_lines table
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transactions table
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('ref_no')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->boolean('mfg_is_final')->default(0);
            $table->decimal('mfg_production_cost', 22, 4)->default(0);
            $table->string('mfg_production_cost_type')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create accounting_accounts table
        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
            $table->integer('account_sub_type_id')->nullable();
            $table->integer('detail_type_id')->nullable();
            $table->string('gl_code')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('status')->default('active');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create accounting_accounts_transactions table
        Schema::dropIfExists('accounting_accounts_transactions');
        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->unsignedBigInteger('acc_trans_mapping_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->string('sub_type', 100)->nullable();
            $table->string('map_type', 100)->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Seed basic settings
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Manufacturing Test Business',
        ]);

        DB::table('business_locations')->insert([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Main Factory',
        ]);

        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('hasRole')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;
        $this->actingAs($user);

        session([
            'user.id' => 1,
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
            'business.date_format' => 'Y-m-d',
        ]);
    }

    public function test_auto_mapping_manufacturing_accounts()
    {
        $mfgUtil = new ManufacturingUtil();
        $settings = $mfgUtil->autoMapManufacturingAccounts(1);

        $this->assertNotEmpty($settings['mfg_raw_material_account_id']);
        $this->assertNotEmpty($settings['mfg_finished_goods_account_id']);
        $this->assertNotEmpty($settings['mfg_production_cost_account_id']);

        $rawAcc = AccountingAccount::find($settings['mfg_raw_material_account_id']);
        $fgAcc = AccountingAccount::find($settings['mfg_finished_goods_account_id']);
        $pcAcc = AccountingAccount::find($settings['mfg_production_cost_account_id']);

        $this->assertEquals('Persediaan Bahan Baku', $rawAcc->name);
        $this->assertEquals('Persediaan Barang Jadi', $fgAcc->name);
        $this->assertEquals('Hutang Biaya Produksi', $pcAcc->name);
        $this->assertEquals('liability', $pcAcc->account_primary_type);
    }

    public function test_auto_mapping_ignores_hpp_expense_account()
    {
        // Seed an expense account with "Bahan Baku - HPP"
        AccountingAccount::create([
            'name' => 'Bahan Baku - HPP',
            'business_id' => 1,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 13,
            'status' => 'active',
        ]);

        $mfgUtil = new ManufacturingUtil();
        $settings = $mfgUtil->autoMapManufacturingAccounts(1);

        $rawAcc = AccountingAccount::find($settings['mfg_raw_material_account_id']);

        // Must select/create an Asset account, NOT the expense account
        $this->assertEquals('asset', $rawAcc->account_primary_type);
        $this->assertNotEquals('Bahan Baku - HPP', $rawAcc->name);
    }

    public function test_sync_accounting_journal_on_finalized_production()
    {
        $mfgUtil = new ManufacturingUtil();
        $settings = $mfgUtil->autoMapManufacturingAccounts(1);

        $finished_product = Product::create([
            'name' => 'Produk A',
            'business_id' => 1,
            'type' => 'single',
            'enable_stock' => 1,
            'created_by' => 1,
        ]);

        $finished_variation = Variation::create([
            'name' => 'DUMMY',
            'product_id' => $finished_product->id,
            'sub_sku' => 'PROD-A',
            'default_purchase_price' => 10000,
            'dpp_inc_tax' => 10000,
            'profit_percent' => 10,
            'default_sell_price' => 11000,
            'sell_price_inc_tax' => 11000,
        ]);

        // Create finalized production transaction
        $production_purchase = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'production_purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'ref_no' => 'PRD-001',
            'transaction_date' => now(),
            'final_total' => 150000,
            'mfg_is_final' => 1,
            'mfg_production_cost' => 30000,
            'mfg_production_cost_type' => 'fixed',
            'created_by' => 1,
        ]);

        PurchaseLine::create([
            'transaction_id' => $production_purchase->id,
            'product_id' => $finished_product->id,
            'variation_id' => $finished_variation->id,
            'quantity' => 1,
            'purchase_price' => 150000,
            'purchase_price_inc_tax' => 150000,
        ]);

        // Trigger sync
        $mfgUtil->syncAccountingJournal($production_purchase);

        $journal_entries = AccountingAccountsTransaction::where('transaction_id', $production_purchase->id)->get();
        $this->assertEquals(3, $journal_entries->count());

        $debits = $journal_entries->where('type', 'debit')->sum('amount');
        $credits = $journal_entries->where('type', 'credit')->sum('amount');

        $this->assertEquals(150000, $debits);
        $this->assertEquals(150000, $credits);

        $finished_goods_entry = $journal_entries->where('map_type', 'mfg_finished_goods')->first();
        $raw_materials_entry = $journal_entries->where('map_type', 'mfg_raw_materials')->first();
        $production_cost_entry = $journal_entries->where('map_type', 'mfg_production_cost')->first();

        $this->assertEquals($settings['mfg_finished_goods_account_id'], $finished_goods_entry->accounting_account_id);
        $this->assertEquals(150000, $finished_goods_entry->amount);
        $this->assertEquals('debit', $finished_goods_entry->type);

        $this->assertEquals($settings['mfg_raw_material_account_id'], $raw_materials_entry->accounting_account_id);
        $this->assertEquals(120000, $raw_materials_entry->amount);
        $this->assertEquals('credit', $raw_materials_entry->type);

        $this->assertEquals($settings['mfg_production_cost_account_id'], $production_cost_entry->accounting_account_id);
        $this->assertEquals(30000, $production_cost_entry->amount);
        $this->assertEquals('credit', $production_cost_entry->type);
    }
}
