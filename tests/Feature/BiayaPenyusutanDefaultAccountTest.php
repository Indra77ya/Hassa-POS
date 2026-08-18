<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Business;
use App\User;
use App\Account;
use Modules\Accounting\Entities\AccountingAccount;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require_once __DIR__ . '/../../database/migrations/2026_08_20_000000_add_biaya_penyusutan_default_account.php';

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

        // Reset sync states
        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
    }

    public function test_create_default_accounts_includes_biaya_penyusutan()
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

        $response = $controller->createDefaultAccounts();

        // Assert Biaya Penyusutan exists in accounting_accounts
        $accountingAccount = AccountingAccount::where('business_id', $business->id)
            ->where('name', 'Biaya Penyusutan')
            ->first();

        $this->assertNotNull($accountingAccount);
        $this->assertEquals('expenses', $accountingAccount->account_primary_type);
        $this->assertEquals(15, $accountingAccount->account_sub_type_id);

        // Assert Biaya Penyusutan exists in POS accounts
        $posAccount = Account::where('business_id', $business->id)
            ->where('name', 'Biaya Penyusutan')
            ->first();

        $this->assertNotNull($posAccount);
        $this->assertEquals('6105', $posAccount->account_number);
        $this->assertEquals('debit', $posAccount->normal_balance);
    }

    public function test_migration_seeds_biaya_penyusutan_for_existing_businesses()
    {
        $business = Business::create(['name' => 'Toko Lama', 'owner_id' => 1]);

        $migration = new \AddBiayaPenyusutanDefaultAccount();
        $migration->up();

        $posAccount = Account::where('business_id', $business->id)
            ->where('name', 'Biaya Penyusutan')
            ->first();

        $this->assertNotNull($posAccount);
        $this->assertEquals('6105', $posAccount->account_number);

        $accountingAccount = AccountingAccount::where('business_id', $business->id)
            ->where('name', 'Biaya Penyusutan')
            ->first();

        $this->assertNotNull($accountingAccount);
        $this->assertEquals('expenses', $accountingAccount->account_primary_type);
    }
}
