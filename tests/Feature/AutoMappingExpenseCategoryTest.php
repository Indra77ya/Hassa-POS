<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Account;
use App\AccountTransaction;
use App\BusinessLocation;
use App\ExpenseCategory;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use DB;

class AutoMappingExpenseCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Accounting module provider
        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

        // Bypass Spatie permission DB queries by defining a global Gate::before rule
        Gate::before(function () {
            return true;
        });

        // Create standard business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
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

        // Create account_transactions table
        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->unsignedBigInteger('accounting_accounts_transaction_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create business location
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->text('accounting_default_map')->nullable();
            $table->timestamps();
        });

        // Create expense_categories table
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
            $table->integer('acc_trans_mapping_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->string('sub_type', 100)->nullable();
            $table->string('map_type', 100)->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('account_transaction_id')->nullable();
            $table->timestamps();
        });

        // Create users table
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->timestamps();
        });

        // Reset sync states
        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
        AccountTransaction::$is_syncing = false;
        AccountingAccountsTransaction::$is_syncing = false;
    }

    /**
     * Test Expense Category creation automatically creates an AccountingAccount
     * and maps it in accounting_default_map inside BusinessLocation.
     */
    public function testExpenseCategoryCreationAutoMapping()
    {
        // 1. Setup active Cash Account (sub_type_id 3)
        $cash_account = AccountingAccount::create([
            'name' => 'Kas Toko',
            'business_id' => 1,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        // 2. Setup Business Location with empty default map
        $location = BusinessLocation::create([
            'business_id' => 1,
            'accounting_default_map' => json_encode([]),
        ]);

        // 3. Create ExpenseCategory
        $category = ExpenseCategory::create([
            'name' => 'Uang Makan',
            'business_id' => 1,
        ]);

        // 4. Verify AccountingAccount was created
        $acc = AccountingAccount::where('business_id', 1)
            ->where('name', 'Uang Makan')
            ->first();

        $this->assertNotNull($acc);
        $this->assertEquals('expenses', $acc->account_primary_type);
        $this->assertEquals(14, $acc->account_sub_type_id);
        $this->assertEquals(138, $acc->detail_type_id);
        $this->assertEquals('active', $acc->status);

        // 5. Verify corresponding core POS Account was synced
        $this->assertNotNull($acc->account_id);
        $pos_acc = Account::find($acc->account_id);
        $this->assertNotNull($pos_acc);
        $this->assertEquals('Uang Makan', $pos_acc->name);

        // 6. Verify default map was updated in BusinessLocation
        $location->refresh();
        $map = json_decode($location->accounting_default_map, true);

        $this->assertArrayHasKey('expense_' . $category->id, $map);
        $this->assertEquals($acc->id, $map['expense_' . $category->id]['deposit_to']);
        $this->assertEquals($cash_account->id, $map['expense_' . $category->id]['payment_account']);
    }

    /**
     * Test renaming Expense Category updates both AccountingAccount and core POS Account.
     */
    public function testExpenseCategoryRenamingUpdatesAccounts()
    {
        $cash_account = AccountingAccount::create([
            'name' => 'Kas Toko',
            'business_id' => 1,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        $location = BusinessLocation::create([
            'business_id' => 1,
            'accounting_default_map' => json_encode([]),
        ]);

        // Create category
        $category = ExpenseCategory::create([
            'name' => 'Uang Makan',
            'business_id' => 1,
        ]);

        // Rename category
        $category->update(['name' => 'Konsumsi Rapat']);

        // Verify name changed in AccountingAccount
        $acc = AccountingAccount::where('business_id', 1)
            ->where('account_primary_type', 'expenses')
            ->first();

        $this->assertNotNull($acc);
        $this->assertEquals('Konsumsi Rapat', $acc->name);

        // Verify name changed in core POS Account
        $pos_acc = Account::find($acc->account_id);
        $this->assertNotNull($pos_acc);
        $this->assertEquals('Konsumsi Rapat', $pos_acc->name);
    }

    /**
     * Test deleting Expense Category with no transaction history deletes the accounts and clears mapping.
     */
    public function testExpenseCategoryDeletionWithoutTransactionsDeletesAccount()
    {
        $cash_account = AccountingAccount::create([
            'name' => 'Kas Toko',
            'business_id' => 1,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        $location = BusinessLocation::create([
            'business_id' => 1,
            'accounting_default_map' => json_encode([]),
        ]);

        // Create category
        $category = ExpenseCategory::create([
            'name' => 'Uang Makan',
            'business_id' => 1,
        ]);

        $acc = AccountingAccount::where('name', 'Uang Makan')->first();
        $this->assertNotNull($acc);

        // Delete category
        $category->delete();

        // Verify AccountingAccount is deleted
        $acc_check = AccountingAccount::find($acc->id);
        $this->assertNull($acc_check);

        // Verify core POS Account is deleted
        $pos_check = Account::find($acc->account_id);
        $this->assertNull($pos_check);

        // Verify mapping was cleaned up
        $location->refresh();
        $map = json_decode($location->accounting_default_map, true);
        $this->assertArrayNotHasKey('expense_' . $category->id, $map);
    }

    /**
     * Test deleting Expense Category with transaction history deactivates the account instead of deleting.
     */
    public function testExpenseCategoryDeletionWithTransactionsDeactivatesAccount()
    {
        $cash_account = AccountingAccount::create([
            'name' => 'Kas Toko',
            'business_id' => 1,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        $location = BusinessLocation::create([
            'business_id' => 1,
            'accounting_default_map' => json_encode([]),
        ]);

        // Create category
        $category = ExpenseCategory::create([
            'name' => 'Uang Makan',
            'business_id' => 1,
        ]);

        $acc = AccountingAccount::where('name', 'Uang Makan')->first();

        // Add a dummy transaction inside accounting_accounts_transactions
        AccountingAccountsTransaction::create([
            'accounting_account_id' => $acc->id,
            'amount' => 10000,
            'type' => 'debit',
            'operation_date' => \Carbon::now(),
            'created_by' => 1,
            'sub_type' => 'expense',
        ]);

        // Delete category
        $category->delete();

        // Verify AccountingAccount still exists but is marked inactive
        $acc_check = AccountingAccount::find($acc->id);
        $this->assertNotNull($acc_check);
        $this->assertEquals('inactive', $acc_check->status);

        // Verify mapping was cleaned up
        $location->refresh();
        $map = json_decode($location->accounting_default_map, true);
        $this->assertArrayNotHasKey('expense_' . $category->id, $map);
    }
}
