<?php

namespace Tests\Feature;

use App\BusinessLocation;
use App\Transaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Listeners\MapPurchaseReturnTransaction;
use Modules\Accounting\Listeners\MapSellReturnTransaction;
use Tests\TestCase;

class AccountingReturnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Accounting module provider
        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);

        // Bypass Gate permissions
        Gate::before(function () {
            return true;
        });

        // Create business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
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
            $table->text('accounting_default_map')->nullable();
            $table->timestamps();
        });

        // Create contacts table
        Schema::dropIfExists('contacts');
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('type');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->timestamps();
        });

        // Create transactions table
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id');
            $table->string('type');
            $table->string('status');
            $table->string('payment_status')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('return_parent_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->dateTime('transaction_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create transaction_sell_lines table
        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create products table
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->boolean('enable_stock')->default(1);
            $table->timestamps();
        });

        // Create variations table
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->decimal('default_purchase_price', 22, 4)->default(0);
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->default(0);
            $table->decimal('sell_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transaction_sell_lines_purchase_lines table
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sell_line_id');
            $table->integer('purchase_line_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('qty_returned', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create purchase_lines table
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transaction_payments table
        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->integer('account_id')->nullable();
            $table->string('method')->nullable();
            $table->dateTime('paid_on')->nullable();
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
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('status')->default('active');
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

        // Insert business
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Return Test Business',
        ]);
    }

    public function test_purchase_return_accounting_mapping()
    {
        $business_id = 1;

        // Create Location
        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'name' => 'Main Location',
        ]);

        // Create Accounts
        $inventory_acc = AccountingAccount::create([
            'business_id' => $business_id,
            'name' => 'Persediaan Barang',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 2,
            'status' => 'active',
        ]);

        $payable_acc = AccountingAccount::create([
            'business_id' => $business_id,
            'name' => 'Hutang Usaha',
            'account_primary_type' => 'liability',
            'account_sub_type_id' => 6,
            'status' => 'active',
        ]);

        // Create Purchase Transaction
        $purchase = Transaction::create([
            'business_id' => $business_id,
            'location_id' => $location->id,
            'type' => 'purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'final_total' => 7250000,
            'transaction_date' => now(),
            'created_by' => 1,
        ]);

        // Create Purchase Return Transaction
        $purchase_return = Transaction::create([
            'business_id' => $business_id,
            'location_id' => $location->id,
            'type' => 'purchase_return',
            'status' => 'final',
            'payment_status' => 'due',
            'return_parent_id' => $purchase->id,
            'final_total' => 7250000,
            'ref_no' => 'PR2026/0001',
            'transaction_date' => now(),
            'created_by' => 1,
        ]);

        // Invoke Listener
        $listener = new MapPurchaseReturnTransaction();
        $listener->handle($purchase_return);

        // Verify Journal Entries
        // Inventory (Persediaan Barang) should be CREDITED for 7,250,000
        $inventory_entry = AccountingAccountsTransaction::where('transaction_id', $purchase_return->id)
            ->where('accounting_account_id', $inventory_acc->id)
            ->first();

        $this->assertNotNull($inventory_entry);
        $this->assertEquals('credit', $inventory_entry->type);
        $this->assertEquals(7250000, $inventory_entry->amount);

        // Payable (Hutang Usaha) should be DEBITED for 7,250,000
        $payable_entry = AccountingAccountsTransaction::where('transaction_id', $purchase_return->id)
            ->where('accounting_account_id', $payable_acc->id)
            ->first();

        $this->assertNotNull($payable_entry);
        $this->assertEquals('debit', $payable_entry->type);
        $this->assertEquals(7250000, $payable_entry->amount);
    }

    public function test_sell_return_accounting_mapping()
    {
        $business_id = 1;

        // Create Location
        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'name' => 'Main Location',
        ]);

        // Create Accounts
        $revenue_acc = AccountingAccount::create([
            'business_id' => $business_id,
            'name' => 'Pendapatan Penjualan',
            'account_primary_type' => 'income',
            'account_sub_type_id' => 11,
            'status' => 'active',
        ]);

        $receivable_acc = AccountingAccount::create([
            'business_id' => $business_id,
            'name' => 'Piutang Usaha',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 1,
            'status' => 'active',
        ]);

        // Create Sale Transaction
        $sale = Transaction::create([
            'business_id' => $business_id,
            'location_id' => $location->id,
            'type' => 'sell',
            'status' => 'final',
            'payment_status' => 'due',
            'final_total' => 5000000,
            'transaction_date' => now(),
            'created_by' => 1,
        ]);

        // Create Sales Return Transaction
        $sell_return = Transaction::create([
            'business_id' => $business_id,
            'location_id' => $location->id,
            'type' => 'sell_return',
            'status' => 'final',
            'payment_status' => 'due',
            'return_parent_id' => $sale->id,
            'final_total' => 5000000,
            'invoice_no' => 'SR2026/0001',
            'transaction_date' => now(),
            'created_by' => 1,
        ]);

        // Invoke Listener
        $listener = new MapSellReturnTransaction();
        $listener->handle($sell_return);

        // Verify Journal Entries
        // Revenue (Pendapatan Penjualan) should be DEBITED for 5,000,000
        $revenue_entry = AccountingAccountsTransaction::where('transaction_id', $sell_return->id)
            ->where('accounting_account_id', $revenue_acc->id)
            ->first();

        $this->assertNotNull($revenue_entry);
        $this->assertEquals('debit', $revenue_entry->type);
        $this->assertEquals(5000000, $revenue_entry->amount);

        // Receivable (Piutang Usaha) should be CREDITED for 5,000,000
        $receivable_entry = AccountingAccountsTransaction::where('transaction_id', $sell_return->id)
            ->where('accounting_account_id', $receivable_acc->id)
            ->first();

        $this->assertNotNull($receivable_entry);
        $this->assertEquals('credit', $receivable_entry->type);
        $this->assertEquals(5000000, $receivable_entry->amount);
    }
}
