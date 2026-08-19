<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Business;
use App\User;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetCategory;
use Modules\AssetManagement\Entities\AssetDepreciationLog;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use DB;

class FixedAssetManagementTest extends TestCase
{
    protected $business;
    protected $user;
    protected $expenseAccount;
    protected $accumulatedAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        $this->app->register(\Modules\AssetManagement\Providers\AssetManagementServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

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

        // Create users table
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('business_id')->nullable();
            $table->timestamps();
        });

        // Create accounting_accounts
        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
            $table->integer('account_sub_type_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Create accounting_accounts_transactions
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

        // Create accounting_acc_trans_mappings
        Schema::dropIfExists('accounting_acc_trans_mappings');
        Schema::create('accounting_acc_trans_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->string('ref_no', 100);
            $table->string('type', 100);
            $table->integer('created_by');
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Create asset_categories
        Schema::dropIfExists('asset_categories');
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('depreciation_expense_account_id')->nullable();
            $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Create assets
        Schema::dropIfExists('assets');
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->unsignedBigInteger('asset_category_id')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('historical_cost', 22, 4)->default(0);
            $table->decimal('salvage_value', 22, 4)->default(0);
            $table->date('purchase_date');
            $table->integer('useful_life_months')->default(12);
            $table->string('depreciation_method')->default('straight_line');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_disposed')->default(false);
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_amount', 22, 4)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Create asset_depreciation_logs
        Schema::dropIfExists('asset_depreciation_logs');
        Schema::create('asset_depreciation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedBigInteger('asset_id');
            $table->date('depreciation_date');
            $table->decimal('amount', 22, 4)->default(0);
            $table->unsignedBigInteger('accounting_accounts_transaction_debit_id')->nullable();
            $table->unsignedBigInteger('accounting_accounts_transaction_credit_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Seed Business & User
        $this->business = Business::create([
            'id' => 1,
            'name' => 'Asset Test Business',
        ]);

        $this->user = User::create([
            'id' => 1,
            'surname' => 'Asset',
            'first_name' => 'Admin',
            'username' => 'asset_admin',
            'email' => 'asset_admin@test.com',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id,
        ]);

        $this->actingAs($this->user);
        session(['user' => ['id' => $this->user->id, 'business_id' => $this->business->id]]);

        // Create Default Accounts for testing
        $this->expenseAccount = AccountingAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Beban Penyusutan Peralatan',
            'account_primary_type' => 'expense',
            'account_sub_type_id' => 14,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->accumulatedAccount = AccountingAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Akumulasi Penyusutan Peralatan',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 16, // Accumulated Depreciation
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_create_asset_category_and_fixed_asset()
    {
        $category = AssetCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Peralatan Kantor',
            'depreciation_expense_account_id' => $this->expenseAccount->id,
            'accumulated_depreciation_account_id' => $this->accumulatedAccount->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('asset_categories', [
            'id' => $category->id,
            'name' => 'Peralatan Kantor',
        ]);

        $asset = Asset::create([
            'business_id' => $this->business->id,
            'name' => 'Laptop Macbook Pro',
            'asset_category_id' => $category->id,
            'sku' => 'AST-001',
            'historical_cost' => 24000000,
            'salvage_value' => 2000000,
            'purchase_date' => '2026-01-01',
            'useful_life_months' => 24,
            'depreciation_method' => 'straight_line',
            'is_active' => true,
            'is_disposed' => false,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'sku' => 'AST-001',
            'historical_cost' => 24000000,
        ]);

        // Max depreciable amount = 24,000,000 - 2,000,000 = 22,000,000
        // Monthly depreciation = 22,000,000 / 24 = 916,666.67
        $this->assertEquals(22000000, $asset->max_depreciable_amount);
        $this->assertEquals(916666.67, round($asset->monthly_depreciation_amount, 2));
    }

    /** @test */
    public function it_runs_monthly_depreciation_command_and_posts_double_entry_journals()
    {
        $category = AssetCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kendaraan Operational',
            'depreciation_expense_account_id' => $this->expenseAccount->id,
            'accumulated_depreciation_account_id' => $this->accumulatedAccount->id,
            'created_by' => $this->user->id,
        ]);

        $asset = Asset::create([
            'business_id' => $this->business->id,
            'name' => 'Motor Honda Vario',
            'asset_category_id' => $category->id,
            'sku' => 'AST-MTR-01',
            'historical_cost' => 12000000,
            'salvage_value' => 0,
            'purchase_date' => '2026-01-01',
            'useful_life_months' => 12,
            'depreciation_method' => 'straight_line',
            'is_active' => true,
            'is_disposed' => false,
            'created_by' => $this->user->id,
        ]);

        // Monthly depreciation = 12,000,000 / 12 = 1,000,000
        $this->artisan('asset:run-depreciation', ['--business_id' => $this->business->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('asset_depreciation_logs', [
            'business_id' => $this->business->id,
            'asset_id' => $asset->id,
            'amount' => 1000000,
        ]);

        // Verify Journal Entries
        $this->assertDatabaseHas('accounting_accounts_transactions', [
            'accounting_account_id' => $this->expenseAccount->id,
            'amount' => 1000000,
            'type' => 'debit',
            'sub_type' => 'journal_entry',
        ]);

        $this->assertDatabaseHas('accounting_accounts_transactions', [
            'accounting_account_id' => $this->accumulatedAccount->id,
            'amount' => 1000000,
            'type' => 'credit',
            'sub_type' => 'journal_entry',
        ]);

        // Refresh asset and check net book value
        $asset->refresh();
        $this->assertEquals(1000000, $asset->accumulated_depreciation);
        $this->assertEquals(11000000, $asset->net_book_value);
    }

    /** @test */
    public function it_stops_depreciation_when_salvage_value_limit_or_disposed_status_reached()
    {
        $asset = Asset::create([
            'business_id' => $this->business->id,
            'name' => 'Mesin Cetak',
            'sku' => 'AST-MSN-01',
            'historical_cost' => 10000000,
            'salvage_value' => 9000000,
            'purchase_date' => '2026-01-01',
            'useful_life_months' => 2,
            'depreciation_method' => 'straight_line',
            'is_active' => true,
            'is_disposed' => false,
            'created_by' => $this->user->id,
        ]);

        // Max depreciable amount = 10,000,000 - 9,000,000 = 1,000,000
        // Run 1st month
        $this->artisan('asset:run-depreciation', ['--business_id' => $this->business->id])
            ->assertExitCode(0);

        $this->assertEquals(500000, $asset->fresh()->accumulated_depreciation);

        // Simulate disposed asset
        $asset->update(['is_disposed' => true]);

        // Clear log so month check passes to test disposal condition
        AssetDepreciationLog::where('asset_id', $asset->id)->delete();
        $this->artisan('asset:run-depreciation', ['--business_id' => $this->business->id])
            ->assertExitCode(0);

        // Accumulated depreciation remains 0 after clearing log because disposed asset was skipped
        $this->assertEquals(0, $asset->fresh()->accumulated_depreciation);
    }
}
