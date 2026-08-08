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
use Illuminate\Support\Facades\Auth;
use DB;

class AutoMappingExpenseCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Accounting module provider and set session
        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

        // Bypass permission checks
        Gate::before(function () {
            return true;
        });

        // Setup necessary tables
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
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

        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('account_type_id')->nullable();
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('normal_balance')->nullable();
            $table->integer('is_closed')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->dateTime('operation_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->text('accounting_default_map')->nullable();
            $table->integer('is_active')->default(1);
            $table->timestamps();
        });

        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
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
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_account_types');
        Schema::create('accounting_account_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('account_primary_type');
            $table->string('account_type');
            $table->integer('parent_id')->nullable();
            $table->integer('business_id')->nullable();
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

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('language', 15)->default('en');
            $table->char('remember_token', 100)->nullable();
            $table->timestamps();
        });

        // Initialize session mock
        $user_data = ['id' => 1, 'business_id' => 1];
        request()->session()->put('user', $user_data);

        // Mock authenticated user
        $user = \App\User::create([
            'id' => 1,
            'username' => 'testadmin',
            'password' => bcrypt('password'),
        ]);
        Auth::login($user);

        // Enable default mapping observer triggers
        AccountingAccount::$is_syncing = false;
        Account::$is_syncing = false;
    }

    private function setupCashAccount($business_id)
    {
        // Create core POS account type 'kas_dan_bank'
        $pos_cash_type = \App\AccountType::create([
            'name' => 'Kas & Bank',
            'fixed_key' => 'kas_dan_bank',
            'business_id' => $business_id
        ]);

        // Create active main cash account in both POS and Accounting
        $accounting_cash = AccountingAccount::create([
            'name' => 'Kas',
            'business_id' => $business_id,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3, // cash and cash equivalents
            'status' => 'active'
        ]);

        $pos_cash = Account::create([
            'name' => 'Kas',
            'business_id' => $business_id,
            'account_type_id' => $pos_cash_type->id,
            'accounting_account_id' => $accounting_cash->id,
            'is_closed' => 0
        ]);

        $accounting_cash->update(['account_id' => $pos_cash->id]);

        return $accounting_cash;
    }

    public function testAutoMappingOnExpenseCategoryCreation()
    {
        $business_id = 1;

        // 1. Create sub_type 14 (Beban Operasional)
        $sub_type_14 = \Modules\Accounting\Entities\AccountingAccountType::create([
            'id' => 14,
            'name' => 'Beban Operasional',
            'account_primary_type' => 'expenses',
            'account_type' => 'sub_type'
        ]);

        $accounting_cash = $this->setupCashAccount($business_id);

        // 4. Set up an active Business Location for mapping check
        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'is_active' => 1,
            'accounting_default_map' => json_encode([])
        ]);

        // 5. Create an Expense Category which triggers the Observer
        $expense_category = ExpenseCategory::create([
            'business_id' => $business_id,
            'name' => 'Beban Perjalanan Dinas'
        ]);

        // Verify:
        // A. An AccountingAccount was created with correct parameters
        $new_accounting_account = AccountingAccount::where('name', 'Beban Perjalanan Dinas')
            ->where('business_id', $business_id)
            ->first();

        $this->assertNotNull($new_accounting_account);
        $this->assertEquals('expenses', $new_accounting_account->account_primary_type);
        $this->assertEquals(14, $new_accounting_account->account_sub_type_id);

        // B. A POS core Account was created and linked dynamically
        $new_pos_account = Account::where('name', 'Beban Perjalanan Dinas')
            ->where('business_id', $business_id)
            ->first();

        $this->assertNotNull($new_pos_account);
        $this->assertEquals($new_accounting_account->id, $new_pos_account->accounting_account_id);
        $this->assertEquals($new_pos_account->id, $new_accounting_account->account_id);

        // C. The active Business Location's default map is automatically filled
        $updated_location = BusinessLocation::find($location->id);
        $map = json_decode($updated_location->accounting_default_map, true);

        $this->assertArrayHasKey('expense_' . $expense_category->id, $map);
        $this->assertEquals($accounting_cash->id, $map['expense_' . $expense_category->id]['payment_account']);
        $this->assertEquals($new_accounting_account->id, $map['expense_' . $expense_category->id]['deposit_to']);
    }

    public function testNameSynchronizationOnExpenseCategoryUpdate()
    {
        $business_id = 1;

        // Create initial setup
        \Modules\Accounting\Entities\AccountingAccountType::create([
            'id' => 14,
            'name' => 'Beban Operasional',
            'account_primary_type' => 'expenses',
            'account_type' => 'sub_type'
        ]);

        $expense_category = ExpenseCategory::create([
            'business_id' => $business_id,
            'name' => 'Beban Konsumsi'
        ]);

        // Verify initial names
        $accounting_acc = AccountingAccount::where('business_id', $business_id)->where('account_sub_type_id', 14)->first();
        $pos_acc = Account::where('business_id', $business_id)->where('accounting_account_id', $accounting_acc->id)->first();

        $this->assertEquals('Beban Konsumsi', $accounting_acc->name);
        $this->assertEquals('Beban Konsumsi', $pos_acc->name);

        // Update the Expense Category name
        $expense_category->name = 'Beban Makanan & Minuman';
        $expense_category->save();

        // Verify updated names are synchronized symmetrically
        $accounting_acc->refresh();
        $pos_acc->refresh();

        $this->assertEquals('Beban Makanan & Minuman', $accounting_acc->name);
        $this->assertEquals('Beban Makanan & Minuman', $pos_acc->name);
    }

    public function testObserverOnDeletedWithoutTransactions()
    {
        $business_id = 1;

        \Modules\Accounting\Entities\AccountingAccountType::create([
            'id' => 14,
            'name' => 'Beban Operasional',
            'account_primary_type' => 'expenses',
            'account_type' => 'sub_type'
        ]);

        $this->setupCashAccount($business_id);

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'is_active' => 1,
            'accounting_default_map' => json_encode([])
        ]);

        $expense_category = ExpenseCategory::create([
            'business_id' => $business_id,
            'name' => 'Beban ATK'
        ]);

        // Ensure mapping is generated
        $location->refresh();
        $map = json_decode($location->accounting_default_map, true);
        $this->assertArrayHasKey('expense_' . $expense_category->id, $map);

        $accounting_acc = AccountingAccount::where('name', 'Beban ATK')->first();
        $pos_acc = Account::where('name', 'Beban ATK')->first();

        $this->assertNotNull($accounting_acc);
        $this->assertNotNull($pos_acc);

        // Delete Expense Category
        $expense_category->delete();

        // Since NO transactions exist, accounts should be hard deleted and mapping entry unset
        $this->assertNull(AccountingAccount::find($accounting_acc->id));
        $this->assertNull(Account::find($pos_acc->id));

        $location->refresh();
        $map_after = json_decode($location->accounting_default_map, true);
        $this->assertArrayNotHasKey('expense_' . $expense_category->id, $map_after);
    }

    public function testObserverOnDeletedWithTransactions()
    {
        $business_id = 1;

        \Modules\Accounting\Entities\AccountingAccountType::create([
            'id' => 14,
            'name' => 'Beban Operasional',
            'account_primary_type' => 'expenses',
            'account_type' => 'sub_type'
        ]);

        $this->setupCashAccount($business_id);

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'is_active' => 1,
            'accounting_default_map' => json_encode([])
        ]);

        $expense_category = ExpenseCategory::create([
            'business_id' => $business_id,
            'name' => 'Beban ATK dengan Transaksi'
        ]);

        $accounting_acc = AccountingAccount::where('name', 'Beban ATK dengan Transaksi')->first();
        $pos_acc = Account::where('name', 'Beban ATK dengan Transaksi')->first();

        // Simulate some transaction records to mimic history
        AccountingAccountsTransaction::create([
            'accounting_account_id' => $accounting_acc->id,
            'amount' => 100000,
            'type' => 'debit',
            'operation_date' => now()
        ]);

        // Delete Expense Category
        $expense_category->delete();

        // Since transactions exist, accounts should NOT be deleted. Instead, they should be marked 'inactive'/closed.
        $accounting_acc->refresh();
        $pos_acc->refresh();

        $this->assertEquals('inactive', $accounting_acc->status);
        $this->assertEquals(1, $pos_acc->is_closed);

        // The default map mapping entry must be cleaned/unset
        $location->refresh();
        $map_after = json_decode($location->accounting_default_map, true);
        $this->assertArrayNotHasKey('expense_' . $expense_category->id, $map_after);
    }
}
