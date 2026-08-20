<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class ProfitLossAccountingSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);

        \Illuminate\Support\Facades\Gate::before(function () {
            return true;
        });

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('status')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('shipping_charges', 22, 4)->default(0);
            $table->string('discount_type')->nullable();
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->decimal('total_amount_recovered', 22, 4)->default(0);
            $table->decimal('rp_redeemed_amount', 22, 4)->default(0);
            $table->decimal('round_off_amount', 22, 4)->default(0);
            $table->decimal('additional_expense_value_1', 22, 4)->default(0);
            $table->decimal('additional_expense_value_2', 22, 4)->default(0);
            $table->decimal('additional_expense_value_3', 22, 4)->default(0);
            $table->decimal('additional_expense_value_4', 22, 4)->default(0);
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->nullable();
            $table->boolean('is_return')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gl_code')->nullable();
            $table->string('name');
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
            $table->integer('account_sub_type_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts_transactions');
        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->integer('acc_trans_mapping_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->dateTime('operation_date');
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_account_types');
        Schema::create('accounting_account_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name')->nullable();
            $table->string('location_id')->nullable();
            $table->string('receipt_printer_type')->nullable();
            $table->integer('selling_price_group_id')->nullable();
            $table->text('default_payment_accounts')->nullable();
            $table->integer('invoice_scheme_id')->nullable();
            $table->integer('invoice_layout_id')->nullable();
            $table->integer('sale_invoice_scheme_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->text('accounting_default_map')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('permissions');
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::dropIfExists('model_has_roles');
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('model_id');
            $table->string('model_type');
        });

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('user_type')->default('user');
            $table->boolean('allow_login')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->decimal('quantity_adjusted', 22, 4)->default(0);
            $table->decimal('purchase_price', 22, 4)->default(0);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('parent_sell_line_id')->nullable();
            $table->string('children_type')->default('');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sell_line_id')->nullable();
            $table->integer('purchase_line_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('qty_returned', 22, 4)->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->string('name')->nullable();
            $table->string('type')->default('single');
            $table->boolean('enable_stock')->default(1);
            $table->timestamps();
        });

        // Register custom IF function for SQLite compatibility
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::connection()->getPdo()->sqliteCreateFunction('IF', function ($expression, $true_val, $false_val) {
                return $expression ? $true_val : $false_val;
            });
        }

        // Insert business
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'fy_start_month' => 1,
            'time_zone' => 'Asia/Jakarta',
        ]);

        DB::table('business_locations')->insert([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Main Location',
            'is_active' => 1,
        ]);
    }

    public function testProfitLossReportSyncsWithAccounting()
    {
        // 1. Seed Accounting account types
        DB::table('accounting_account_types')->insert([
            ['id' => 10, 'name' => 'Penjualan'],
            ['id' => 13, 'name' => 'Harga Pokok Penjualan'],
            ['id' => 14, 'name' => 'Beban Operasional'],
        ]);

        // 2. Seed Accounting Accounts
        DB::table('accounting_accounts')->insert([
            ['id' => 1, 'gl_code' => '4100', 'name' => 'Penjualan Produk', 'business_id' => 1, 'account_primary_type' => 'income', 'account_sub_type_id' => 10],
            ['id' => 2, 'gl_code' => '5100', 'name' => 'HPP Produk', 'business_id' => 1, 'account_primary_type' => 'expenses', 'account_sub_type_id' => 13],
            ['id' => 3, 'gl_code' => '6100', 'name' => 'Beban Gaji', 'business_id' => 1, 'account_primary_type' => 'expenses', 'account_sub_type_id' => 14],
            ['id' => 4, 'gl_code' => '6200', 'name' => 'Beban Listrik & Air', 'business_id' => 1, 'account_primary_type' => 'expenses', 'account_sub_type_id' => 14],
        ]);

        // 3. Seed Double-Entry Accounting Transactions
        // Income: Penjualan = Credit 10,000,000
        DB::table('accounting_accounts_transactions')->insert([
            'accounting_account_id' => 1,
            'amount' => 10000000,
            'type' => 'credit',
            'operation_date' => '2024-02-10 10:00:00',
        ]);

        // HPP: Debit 6,000,000
        DB::table('accounting_accounts_transactions')->insert([
            'accounting_account_id' => 2,
            'amount' => 6000000,
            'type' => 'debit',
            'operation_date' => '2024-02-10 10:00:00',
        ]);

        // Beban Gaji: Debit 1,500,000
        DB::table('accounting_accounts_transactions')->insert([
            'accounting_account_id' => 3,
            'amount' => 1500000,
            'type' => 'debit',
            'operation_date' => '2024-02-12 10:00:00',
        ]);

        // Beban Listrik & Air: Debit 500,000
        DB::table('accounting_accounts_transactions')->insert([
            'accounting_account_id' => 4,
            'amount' => 500000,
            'type' => 'debit',
            'operation_date' => '2024-02-15 10:00:00',
        ]);

        // Expected Calculations:
        // Total Income = 10,000,000
        // Total HPP = 6,000,000
        // Gross Profit = 4,000,000
        // Total Operating Expenses = 2,000,000 (1.5M + 500K)
        // Net Profit = 2,000,000

        // Create user in DB to pass auth middleware check
        DB::table('users')->insert([
            'id' => 1,
            'business_id' => 1,
            'first_name' => 'Admin',
            'email' => 'admin@example.com',
            'user_type' => 'user',
            'allow_login' => 1,
        ]);

        $user = \App\User::find(1);
        $this->actingAs($user);

        session([
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
            'business.date_format' => 'Y-m-d',
            'currency' => [
                'symbol' => 'Rp',
                'decimal_separator' => ',',
                'thousand_separator' => '.',
            ],
            'business.currency_symbol_placement' => 'before',
            'business.currency_precision' => 2,
            'asset_v' => 1,
        ]);

        $transactionUtil = new \App\Utils\TransactionUtil();
        $pnl_details = $transactionUtil->getProfitLossDetails(1, null, '2024-02-01', '2024-02-28');

        $this->assertEquals(4000000, $pnl_details['gross_profit']);
        $this->assertEquals(2000000, $pnl_details['net_profit']);
        $this->assertTrue($pnl_details['accounting_data']['is_accounting']);
        $this->assertEquals(10000000, $pnl_details['accounting_data']['total_income']);
        $this->assertEquals(6000000, $pnl_details['accounting_data']['total_cogs']);
        $this->assertEquals(2000000, $pnl_details['accounting_data']['total_operating_expense']);

        // Verify HTML endpoint response
        $response = $this->get('/reports/profit-loss?start_date=2024-02-01&end_date=2024-02-28', ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
        $response->assertSee('Laporan Laba Rugi Perusahaan');
        $response->assertSee('LABA KOTOR');
        $response->assertSee('Laba / Rugi Bersih Akhir');
    }
}
