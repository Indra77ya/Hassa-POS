<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Account;
use App\AccountType;
use App\ExpenseCategory;
use Modules\Accounting\Entities\AccountingAccount;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;

require_once __DIR__ . '/../../database/migrations/2026_08_17_000000_sync_operating_expense_categories.php';

class OperatingExpenseCategorySyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

        Gate::before(function () {
            return true;
        });

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

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

        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fixed_key')->nullable();
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->text('accounting_default_map')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id')->unsigned();
            $table->string('code')->nullable();
            $table->integer('parent_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

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

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->integer('business_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
    }

    /**
     * Test creating an Operating Expense AccountingAccount creates ExpenseCategory.
     */
    public function testOperatingExpenseAccountCreationCreatesExpenseCategory()
    {
        $business_id = 1;

        $acc = AccountingAccount::create([
            'name' => 'Beban Kebersihan',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14, // Operating Expense
            'gl_code' => '6110',
            'status' => 'active',
        ]);

        $exp_cat = ExpenseCategory::where('business_id', $business_id)
            ->where('name', 'Beban Kebersihan')
            ->first();

        $this->assertNotNull($exp_cat);
        $this->assertEquals('6110', $exp_cat->code);
    }

    /**
     * Test updating an Operating Expense AccountingAccount updates ExpenseCategory.
     */
    public function testOperatingExpenseAccountUpdateUpdatesExpenseCategory()
    {
        $business_id = 1;

        $acc = AccountingAccount::create([
            'name' => 'Beban Kebersihan',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'gl_code' => '6110',
            'status' => 'active',
        ]);

        $acc->update([
            'name' => 'Beban Kebersihan & Keamanan',
            'gl_code' => '6111',
        ]);

        $exp_cat = ExpenseCategory::where('business_id', $business_id)
            ->where('name', 'Beban Kebersihan & Keamanan')
            ->first();

        $this->assertNotNull($exp_cat);
        $this->assertEquals('6111', $exp_cat->code);
    }

    /**
     * Test deleting an Operating Expense AccountingAccount deletes ExpenseCategory.
     */
    public function testOperatingExpenseAccountDeletionDeletesExpenseCategory()
    {
        $business_id = 1;

        $acc = AccountingAccount::create([
            'name' => 'Beban Kebersihan',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'gl_code' => '6110',
            'status' => 'active',
        ]);

        $this->assertNotNull(ExpenseCategory::where('name', 'Beban Kebersihan')->first());

        $acc->delete();

        $this->assertNull(ExpenseCategory::where('name', 'Beban Kebersihan')->first());
    }

    /**
     * Test hotfix migration SyncOperatingExpenseCategories.
     */
    public function testHotfixMigrationSyncsOperatingExpenseCategories()
    {
        $business_id = 1;

        // Insert unsynced Operating Expense account
        \DB::table('accounting_accounts')->insert([
            'id' => 888,
            'name' => 'Beban Transportasi',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'gl_code' => '6120',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNull(ExpenseCategory::where('name', 'Beban Transportasi')->first());

        $migration = new \SyncOperatingExpenseCategories();
        $migration->up();

        $exp_cat = ExpenseCategory::where('name', 'Beban Transportasi')->first();
        $this->assertNotNull($exp_cat);
        $this->assertEquals('6120', $exp_cat->code);
    }

    /**
     * Test hotfix migration cleans up duplicate accounts with same name.
     */
    public function testHotfixMigrationCleansUpDuplicateAccounts()
    {
        $business_id = 1;

        // Insert duplicate POS accounts
        \DB::table('accounts')->insert([
            'id' => 10,
            'name' => 'Beban Gaji',
            'business_id' => $business_id,
            'account_number' => '6101',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('accounts')->insert([
            'id' => 11,
            'name' => 'Beban Gaji',
            'business_id' => $business_id,
            'account_number' => null,
            'note' => 'Uncategorised Expense',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertEquals(2, Account::where('name', 'Beban Gaji')->count());

        $migration = new \SyncOperatingExpenseCategories();
        $migration->up();

        // Verify duplicate was merged and deleted
        $this->assertEquals(1, Account::where('name', 'Beban Gaji')->count());
        $remaining = Account::where('name', 'Beban Gaji')->first();
        $this->assertEquals('6101', $remaining->account_number);
    }
}
