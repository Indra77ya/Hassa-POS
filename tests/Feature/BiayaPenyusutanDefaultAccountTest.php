<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Business;
use App\User;
use App\Account;
use App\ExpenseCategory;
use Modules\Accounting\Entities\AccountingAccount;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require_once __DIR__ . '/../../database/migrations/2026_08_25_000000_remove_biaya_penyusutan_and_akumulasi_penyusutan_accounts.php';

class BiayaPenyusutanDefaultAccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Accounting module provider
        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

        // Create standard business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('owner_id')->nullable();
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

        // Create accounts table
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('account_type_id')->nullable();
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('note')->nullable();
            $table->string('normal_balance')->nullable();
            $table->integer('is_closed')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create account_transactions table
        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create permissions table
        Schema::dropIfExists('permissions');
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        // Create expense_categories table
        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('business_id');
            $table->integer('parent_id')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create account_types table
        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fixed_key')->nullable();
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->timestamps();
        });

        // Create accounting_account_types table
        Schema::dropIfExists('accounting_account_types');
        Schema::create('accounting_account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('account_primary_type')->nullable();
            $table->string('account_type')->nullable();
            $table->integer('parent_id')->nullable();
            $table->text('description')->nullable();
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
            $table->integer('detail_type_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('gl_code')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // Create accounting_accounts_transactions
        Schema::dropIfExists('accounting_accounts_transactions');
        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->timestamps();
        });

        // Reset sync states
        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
    }

    public function test_create_default_accounts_excludes_biaya_penyusutan_and_akumulasi_penyusutan()
    {
        $business = Business::create(['name' => 'Toko Sampel', 'owner_id' => 1]);

        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->shouldReceive('can')->with('accounting.manage_accounts')->andReturn(true);
        $user->id = 1;
        $user->business_id = $business->id;

        $this->actingAs($user);
        session(['user' => ['id' => 1, 'business_id' => $business->id]]);

        // Call CoaController createDefaultAccounts
        $controller = new \Modules\Accounting\Http\Controllers\CoaController(
            app(\Modules\Accounting\Utils\AccountingUtil::class),
            app(\App\Utils\ModuleUtil::class)
        );

        $controller->createDefaultAccounts();

        // Assert Biaya Penyusutan does NOT exist in accounting_accounts
        $biayaPenyusutanAcc = AccountingAccount::where('business_id', $business->id)
            ->where('name', 'Biaya Penyusutan')
            ->first();
        $this->assertNull($biayaPenyusutanAcc);

        // Assert Akumulasi Penyusutan does NOT exist in accounting_accounts
        $akumulasiPenyusutanAcc = AccountingAccount::where('business_id', $business->id)
            ->where('name', 'Akumulasi Penyusutan')
            ->first();
        $this->assertNull($akumulasiPenyusutanAcc);

        // Assert Biaya Penyusutan does NOT exist in POS accounts
        $biayaPenyusutanPos = Account::where('business_id', $business->id)
            ->where('name', 'Biaya Penyusutan')
            ->first();
        $this->assertNull($biayaPenyusutanPos);

        // Assert Akumulasi Penyusutan does NOT exist in POS accounts
        $akumulasiPenyusutanPos = Account::where('business_id', $business->id)
            ->where('name', 'Akumulasi Penyusutan')
            ->first();
        $this->assertNull($akumulasiPenyusutanPos);
    }

    public function test_chart_of_accounts_view_contains_create_default_accounts_button()
    {
        $business = Business::create(['name' => 'Toko Sampel View', 'owner_id' => 1]);

        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->andReturn(true);
        $user->shouldReceive('hasAnyPermission')->andReturn(true);
        $user->id = 1;
        $user->business_id = $business->id;

        $this->actingAs($user);
        session(['user' => ['id' => 1, 'business_id' => $business->id]]);

        $viewFile = file_get_contents(base_path('Modules/Accounting/Resources/views/chart_of_accounts/index.blade.php'));

        $this->assertStringContainsString("route('accounting.create-default-accounts')", $viewFile);
        $this->assertStringContainsString('btn-success', $viewFile);
    }

    public function test_migration_removes_biaya_penyusutan_and_akumulasi_penyusutan_records()
    {
        $business = Business::create(['name' => 'Toko Lama', 'owner_id' => 1]);

        // Manually create accounts to simulate existing state before migration
        AccountingAccount::create(['name' => 'Biaya Penyusutan', 'business_id' => $business->id, 'account_primary_type' => 'expenses', 'account_sub_type_id' => 15]);
        AccountingAccount::create(['name' => 'Akumulasi Penyusutan', 'business_id' => $business->id, 'account_primary_type' => 'asset', 'account_sub_type_id' => 17]);

        Account::create(['name' => 'Biaya Penyusutan', 'business_id' => $business->id]);
        Account::create(['name' => 'Akumulasi Penyusutan', 'business_id' => $business->id]);

        ExpenseCategory::create(['name' => 'Biaya Penyusutan', 'business_id' => $business->id]);

        $migration = new \RemoveBiayaPenyusutanAndAkumulasiPenyusutanAccounts();
        $migration->up();

        // Verify accounts were deleted
        $this->assertNull(Account::where('business_id', $business->id)->where('name', 'Biaya Penyusutan')->first());
        $this->assertNull(Account::where('business_id', $business->id)->where('name', 'Akumulasi Penyusutan')->first());

        $this->assertNull(AccountingAccount::where('business_id', $business->id)->where('name', 'Biaya Penyusutan')->first());
        $this->assertNull(AccountingAccount::where('business_id', $business->id)->where('name', 'Akumulasi Penyusutan')->first());

        $this->assertNull(ExpenseCategory::where('business_id', $business->id)->where('name', 'Biaya Penyusutan')->first());
    }
}
