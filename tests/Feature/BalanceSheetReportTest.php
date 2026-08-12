<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class BalanceSheetReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);

        // Bypass Spatie permission DB queries by defining a global Gate::before rule
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
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
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

        // Business Locations Table
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

        // Selling Price Groups Table
        Schema::dropIfExists('selling_price_groups');
        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Spatie Role/Permission Tables for SQLite test environment
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

        // Create notifications table
        Schema::dropIfExists('notifications');
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Create system table
        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->text('value')->nullable();
        });

        // Insert business
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'fy_start_month' => 1,
            'time_zone' => 'Asia/Jakarta',
        ]);

        // Insert business location
        DB::table('business_locations')->insert([
            'id' => 1,
            'business_id' => 1,
            'name' => 'Main Location',
            'is_active' => 1,
        ]);

        // Register custom IF function for SQLite compatibility
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::connection()->getPdo()->sqliteCreateFunction('IF', function ($expression, $true_val, $false_val) {
                return $expression ? $true_val : $false_val;
            });
        }
    }

    public function testBalanceSheetPageLoadsCorrectly()
    {
        // Insert account types
        DB::table('accounting_account_types')->insert([
            ['id' => 1, 'name' => 'accounts_receivable'],
            ['id' => 2, 'name' => 'current_assets'],
            ['id' => 3, 'name' => 'cash_and_cash_equivalents'],
            ['id' => 4, 'name' => 'fixed_assets'],
            ['id' => 5, 'name' => 'non_current_assets'],
            ['id' => 6, 'name' => 'accounts_payable'],
            ['id' => 7, 'name' => 'credit_card'],
            ['id' => 8, 'name' => 'current_liabilities'],
            ['id' => 9, 'name' => 'non_current_liabilities'],
            ['id' => 10, 'name' => 'owners_equity'],
        ]);

        // Insert mock accounts
        DB::table('accounting_accounts')->insert([
            ['id' => 1, 'name' => 'Kas', 'business_id' => 1, 'account_primary_type' => 'asset', 'account_sub_type_id' => 3],
            ['id' => 2, 'name' => 'Peralatan', 'business_id' => 1, 'account_primary_type' => 'asset', 'account_sub_type_id' => 4],
            ['id' => 3, 'name' => 'Utang Usaha', 'business_id' => 1, 'account_primary_type' => 'liability', 'account_sub_type_id' => 6],
            ['id' => 4, 'name' => 'Utang Jangka Panjang', 'business_id' => 1, 'account_primary_type' => 'liability', 'account_sub_type_id' => 9],
            ['id' => 5, 'name' => 'Modal Usaha', 'business_id' => 1, 'account_primary_type' => 'equity', 'account_sub_type_id' => 10],
        ]);

        DB::table('accounting_accounts_transactions')->insert([
            // Kas: Debit 1000
            ['accounting_account_id' => 1, 'amount' => 1000, 'type' => 'debit', 'operation_date' => '2024-01-15 10:00:00'],
            // Peralatan: Debit 500
            ['accounting_account_id' => 2, 'amount' => 500, 'type' => 'debit', 'operation_date' => '2024-01-15 10:00:00'],
            // Utang Usaha: Credit 300
            ['accounting_account_id' => 3, 'amount' => 300, 'type' => 'credit', 'operation_date' => '2024-01-15 10:00:00'],
            // Utang Jangka Panjang: Credit 400
            ['accounting_account_id' => 4, 'amount' => 400, 'type' => 'credit', 'operation_date' => '2024-01-15 10:00:00'],
            // Modal Usaha: Credit 800
            ['accounting_account_id' => 5, 'amount' => 800, 'type' => 'credit', 'operation_date' => '2024-01-15 10:00:00'],
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->shouldReceive('can')->with('accounting.view_reports')->andReturn(true);
        $user->shouldReceive('hasRole')->andReturn(true);
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);

        // Put business_id in session
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
        ]);

        // Mock ModuleUtil to bypass subscription permission check
        $moduleUtil = \Mockery::mock(\App\Utils\ModuleUtil::class);
        $moduleUtil->shouldReceive('hasThePermissionInSubscription')->andReturn(true);
        $this->app->instance(\App\Utils\ModuleUtil::class, $moduleUtil);

        // 1. Verify accounting balance sheet route
        $response = $this->get('/accounting/reports/balance-sheet?start_date=2024-01-01&end_date=2024-01-31');

        $response->assertStatus(200);
        $response->assertViewHasAll([
            'current_assets',
            'non_current_assets',
            'current_liabilities',
            'non_current_liabilities',
            'equities',
            'current_period_net_profit',
        ]);

        $response->assertSee('ASET LANCAR');
        $response->assertSee('ASET TIDAK LANCAR');
        $response->assertSee('LIABILITAS JANGKA PENDEK');
        $response->assertSee('LIABILITAS JANGKA PANJANG');

        // 2. Verify core payment account balance sheet html page
        $responseHtml = $this->get('/account/balance-sheet');
        $responseHtml->assertStatus(200);
        $responseHtml->assertSee('JUMLAH ASET (JUMLAH AKTIVA)');
        $responseHtml->assertSee('JUMLAH LIABILITAS DAN EKUITAS');

        // 3. Verify core payment account balance sheet ajax json endpoint
        $responseAjax = $this->get('/account/balance-sheet', ['X-Requested-With' => 'XMLHttpRequest']);
        $responseAjax->assertStatus(200);
        $json = $responseAjax->json();
        $this->assertArrayHasKey('assets', $json);
        $this->assertArrayHasKey('liabilities', $json);
        $this->assertArrayHasKey('equities', $json);
    }
}
