<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class ResetBusinessFinanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Superadmin service provider to load routes and resources
        $this->app->register(\Modules\Superadmin\Providers\SuperadminServiceProvider::class);

        // Recreate all tables needed by postResetData to prevent query errors in SQLite
        $tables = [
            'repair_job_sheets', 'transactions', 'transaction_payments', 'transaction_sell_lines',
            'transaction_sell_lines_purchase_lines', 'bookings', 'purchase_lines', 'cash_registers',
            'cash_register_transactions', 'stock_adjustment_lines', 'products', 'variation_location_details',
            'product_locations', 'product_racks', 'variations', 'product_variations', 'contacts',
            'categories', 'brands', 'tax_rates', 'group_sub_taxes', 'accounts', 'account_transactions',
            'account_types', 'accounting_accounts', 'accounting_accounts_transactions', 'accounting_budgets',
            'accounting_acc_trans_mappings', 'accounting_account_types', 'business_locations', 'business',
            'currencies', 'customer_groups', 'reference_counts'
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('symbol');
            $table->string('thousand_separator')->nullable();
            $table->string('decimal_separator')->nullable();
            $table->timestamps();
        });

        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('currency_id')->nullable();
            $table->integer('fy_start_month')->default(1);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('type')->nullable();
        });

        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->nullable();
        });

        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
        });

        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->integer('sell_line_id');
            $table->integer('purchase_line_id');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
        });

        Schema::create('cash_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cash_register_id');
        });

        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->integer('product_id');
            $table->decimal('qty_available', 22, 4)->default(0.0000);
        });

        Schema::create('product_locations', function (Blueprint $table) {
            $table->integer('product_id');
        });

        Schema::create('product_racks', function (Blueprint $table) {
            $table->integer('product_id');
        });

        Schema::create('variations', function (Blueprint $table) {
            $table->integer('product_id');
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->integer('product_id');
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_default')->default(0);
            $table->string('contact_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->decimal('credit_limit', 22, 4)->default(0);
            $table->integer('customer_group_id')->nullable();
            $table->softDeletes();
            $table->string('contact_status')->default('active');
            $table->timestamps();
        });

        Schema::create('customer_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->string('name');
            $table->float('amount', 5, 2)->default(0);
            $table->string('price_calculation_type')->default('percentage')->nullable();
            $table->integer('selling_price_group_id')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('reference_counts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_type');
            $table->integer('ref_count');
            $table->integer('business_id');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('group_sub_taxes', function (Blueprint $table) {
            $table->integer('group_tax_id');
            $table->integer('tax_id');
        });

        // POS Accounts & Transactions
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->integer('transaction_id')->nullable();
        });

        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
        });

        // Accounting Module Accounts & Transactions
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
        });

        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('accounting_account_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0.0000);
            $table->string('type', 100)->nullable();
            $table->string('sub_type', 100)->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('operation_date')->nullable();
        });

        Schema::create('accounting_budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('accounting_account_id');
        });

        Schema::create('accounting_acc_trans_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
        });

        Schema::create('accounting_account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('name');
        });

        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->text('accounting_default_map')->nullable();
        });

        // Seed initial data
        DB::table('currencies')->insert([
            'id' => 1,
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ','
        ]);

        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'currency_id' => 1,
            'fy_start_month' => 1
        ]);
    }

    public function testResetBusinessFinance()
    {
        // 1. Prepare data for the target business (ID 1)
        DB::table('accounts')->insert([
            ['id' => 10, 'business_id' => 1, 'name' => 'Bank Mandiri'],
            ['id' => 11, 'business_id' => 1, 'name' => 'Kas Toko']
        ]);

        DB::table('account_transactions')->insert([
            ['id' => 100, 'account_id' => 10],
            ['id' => 101, 'account_id' => 11]
        ]);

        DB::table('account_types')->insert([
            ['id' => 1, 'business_id' => 1, 'name' => 'Kas & Setara Kas']
        ]);

        DB::table('accounting_accounts')->insert([
            ['id' => 20, 'business_id' => 1, 'name' => 'Bank Mandiri (Accounting)'],
            ['id' => 21, 'business_id' => 1, 'name' => 'Kas Toko (Accounting)']
        ]);

        DB::table('accounting_accounts_transactions')->insert([
            ['id' => 200, 'accounting_account_id' => 20],
            ['id' => 201, 'accounting_account_id' => 21]
        ]);

        DB::table('accounting_budgets')->insert([
            ['id' => 300, 'accounting_account_id' => 20]
        ]);

        DB::table('accounting_acc_trans_mappings')->insert([
            ['id' => 400, 'business_id' => 1]
        ]);

        // Custom account type for business 1, and system default type (business_id is null)
        DB::table('accounting_account_types')->insert([
            ['id' => 500, 'business_id' => 1, 'name' => 'Custom Type'],
            ['id' => 501, 'business_id' => null, 'name' => 'System Type']
        ]);

        DB::table('business_locations')->insert([
            ['id' => 1, 'business_id' => 1, 'accounting_default_map' => '{"sale":{"deposit_to":20}}']
        ]);

        // 2. Prepare data for another business (ID 2) to ensure it's not affected
        DB::table('accounts')->insert([
            ['id' => 12, 'business_id' => 2, 'name' => 'Other Business Account']
        ]);
        DB::table('account_transactions')->insert([
            ['id' => 102, 'account_id' => 12]
        ]);
        DB::table('accounting_accounts')->insert([
            ['id' => 22, 'business_id' => 2, 'name' => 'Other Business Accounting Account']
        ]);
        DB::table('accounting_accounts_transactions')->insert([
            ['id' => 202, 'accounting_account_id' => 22]
        ]);

        // Mock login as superadmin
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->surname = 'Mr';
        $user->first_name = 'Admin';
        $user->last_name = 'Super';
        $user->email = 'admin@example.com';
        $user->language = 'en';
        $user->user_type = 'superadmin';
        $user->allow_login = 1;

        $this->actingAs($user);

        // Let's seed contacts (some default and some non-default) for business 1
        DB::table('contacts')->insert([
            ['id' => 1000, 'business_id' => 1, 'name' => 'Walk-In Customer', 'is_default' => 1, 'type' => 'customer'],
            ['id' => 1001, 'business_id' => 1, 'name' => 'Regular Customer', 'is_default' => 0, 'type' => 'customer']
        ]);

        // Call postResetData with both finance reset AND contacts master data reset
        $response = $this->post('/superadmin/business/1/reset-data', [
            'reset_transactions' => ['finance'],
            'reset_master' => ['contacts']
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 3. Assertions: Walk-In Customer (is_default = 1) must still exist, regular customer deleted
        $remainingContacts = DB::table('contacts')->where('business_id', 1)->get();
        $this->assertCount(1, $remainingContacts);
        $this->assertEquals(1000, $remainingContacts->first()->id);
        $this->assertEquals(1, $remainingContacts->first()->is_default);

        // Assertions: Business 1 finance data should be deleted
        $this->assertEmpty(DB::table('accounts')->where('business_id', 1)->get());
        $this->assertEmpty(DB::table('account_transactions')->whereIn('account_id', [10, 11])->get());
        $this->assertEmpty(DB::table('account_types')->where('business_id', 1)->get());

        $this->assertEmpty(DB::table('accounting_accounts')->where('business_id', 1)->get());
        $this->assertEmpty(DB::table('accounting_accounts_transactions')->whereIn('accounting_account_id', [20, 21])->get());
        $this->assertEmpty(DB::table('accounting_budgets')->whereIn('accounting_account_id', [20, 21])->get());
        $this->assertEmpty(DB::table('accounting_acc_trans_mappings')->where('business_id', 1)->get());

        // Custom accounting account type should be deleted, but system default type should NOT
        $this->assertEmpty(DB::table('accounting_account_types')->where('business_id', 1)->get());
        $this->assertNotEmpty(DB::table('accounting_account_types')->whereNull('business_id')->get());

        // accounting_default_map in business locations should be cleared/null
        $location = DB::table('business_locations')->where('id', 1)->first();
        $this->assertNull($location->accounting_default_map);

        // 4. Assertions: Business 2 data should remain intact
        $this->assertNotEmpty(DB::table('accounts')->where('business_id', 2)->get());
        $this->assertNotEmpty(DB::table('account_transactions')->where('account_id', 12)->get());
        $this->assertNotEmpty(DB::table('accounting_accounts')->where('business_id', 2)->get());
        $this->assertNotEmpty(DB::table('accounting_accounts_transactions')->where('accounting_account_id', 22)->get());
    }

    public function testResetBusinessStock()
    {
        // 1. Prepare products and stock levels
        DB::table('products')->insert([
            ['id' => 100, 'business_id' => 1],
            ['id' => 101, 'business_id' => 2] // Other business product
        ]);

        DB::table('variation_location_details')->insert([
            ['product_id' => 100, 'qty_available' => 25],
            ['product_id' => 101, 'qty_available' => 50]
        ]);

        // 2. Prepare stock-affecting transactions
        DB::table('transactions')->insert([
            ['id' => 500, 'business_id' => 1, 'type' => 'purchase'],
            ['id' => 501, 'business_id' => 1, 'type' => 'opening_stock'],
            ['id' => 502, 'business_id' => 1, 'type' => 'expense'], // Expense should not be deleted by reset_stock
            ['id' => 503, 'business_id' => 2, 'type' => 'purchase'] // Other business purchase
        ]);

        DB::table('transaction_payments')->insert([
            ['id' => 600, 'transaction_id' => 500],
            ['id' => 601, 'transaction_id' => 502],
            ['id' => 602, 'transaction_id' => 503]
        ]);

        DB::table('purchase_lines')->insert([
            ['id' => 700, 'transaction_id' => 500],
            ['id' => 701, 'transaction_id' => 501],
            ['id' => 702, 'transaction_id' => 503]
        ]);

        DB::table('accounting_accounts')->insert([
            ['id' => 30, 'business_id' => 1, 'name' => 'Stock Account']
        ]);

        DB::table('accounting_accounts_transactions')->insert([
            ['id' => 800, 'accounting_account_id' => 30, 'transaction_id' => 500, 'transaction_payment_id' => 600, 'amount' => 100, 'type' => 'debit', 'sub_type' => 'journal', 'created_by' => 1, 'operation_date' => '2023-01-01 10:00:00'],
            ['id' => 801, 'accounting_account_id' => 30, 'transaction_id' => 502, 'transaction_payment_id' => 601, 'amount' => 50, 'type' => 'credit', 'sub_type' => 'journal', 'created_by' => 1, 'operation_date' => '2023-01-01 10:00:00']
        ]);

        // Mock login as superadmin
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->surname = 'Mr';
        $user->first_name = 'Admin';
        $user->last_name = 'Super';
        $user->email = 'admin@example.com';
        $user->language = 'en';
        $user->user_type = 'superadmin';
        $user->allow_login = 1;

        $this->actingAs($user);

        // Call postResetData with reset_stock
        $response = $this->post('/superadmin/business/1/reset-data', [
            'reset_transactions' => ['reset_stock']
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 3. Assertions
        // Stock of business 1 product should be set to 0
        $this->assertEquals(0, DB::table('variation_location_details')->where('product_id', 100)->value('qty_available'));

        // Stock of other business should remain intact
        $this->assertEquals(50, DB::table('variation_location_details')->where('product_id', 101)->value('qty_available'));

        // Stock-affecting transactions of business 1 should be deleted
        $this->assertNull(DB::table('transactions')->where('id', 500)->first());
        $this->assertNull(DB::table('transactions')->where('id', 501)->first());

        // Expense transaction should remain
        $this->assertNotNull(DB::table('transactions')->where('id', 502)->first());

        // Other business transaction should remain
        $this->assertNotNull(DB::table('transactions')->where('id', 503)->first());

        // Purchase lines and payments of deleted transactions should be deleted
        $this->assertNull(DB::table('purchase_lines')->where('id', 700)->first());
        $this->assertNull(DB::table('purchase_lines')->where('id', 701)->first());
        $this->assertNotNull(DB::table('purchase_lines')->where('id', 702)->first());

        $this->assertNull(DB::table('transaction_payments')->where('id', 600)->first());
        $this->assertNotNull(DB::table('transaction_payments')->where('id', 601)->first());

        // Accounting transaction logs for deleted transactions/payments should be deleted
        $this->assertNull(DB::table('accounting_accounts_transactions')->where('id', 800)->first());
        $this->assertNotNull(DB::table('accounting_accounts_transactions')->where('id', 801)->first());
    }

    public function testWalkInCustomerCannotBeDeletedAndIsAutoRegenerated()
    {
        // 1. Create a Walk-In Customer for business 1
        $contact = \App\Contact::create([
            'business_id' => 1,
            'type' => 'customer',
            'name' => 'Walk-In Customer',
            'is_default' => 1,
            'contact_id' => 'WI-0001',
            'credit_limit' => 0
        ]);

        $this->assertEquals(1, $contact->is_default);

        // 2. Try to delete it via Eloquent. It should return false or not be deleted.
        $deleted = $contact->delete();
        $this->assertFalse($deleted);

        // Check it still exists in the database
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'is_default' => 1
        ]);

        // 3. To test the automatic regeneration/restoration:
        // We force-delete it at the DB level (bypassing Eloquent events) to simulate it somehow being completely gone.
        DB::table('contacts')->where('id', $contact->id)->delete();
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id
        ]);

        // Now call the ContactUtil helper to retrieve it. It should dynamically re-create it on the fly!
        $contactUtil = new \App\Utils\ContactUtil();
        $walkIn = $contactUtil->getWalkInCustomer(1, false);

        $this->assertNotNull($walkIn);
        $this->assertEquals('Walk-In Customer', $walkIn->name);
        $this->assertEquals(1, $walkIn->is_default);

        $this->assertDatabaseHas('contacts', [
            'business_id' => 1,
            'name' => 'Walk-In Customer',
            'is_default' => 1
        ]);
    }
}
