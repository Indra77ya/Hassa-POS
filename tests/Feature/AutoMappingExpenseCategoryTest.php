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

require_once __DIR__ . '/../../database/migrations/2026_08_01_000000_sync_existing_expense_categories.php';
require_once __DIR__ . '/../../database/migrations/2026_08_29_000000_sync_operating_expense_categories.php';

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
            $table->integer('business_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create transaction table for expense records
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id');
            $table->string('type');
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('expense_category_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->timestamps();
        });

        // Create transaction_payments table
        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->integer('account_id')->nullable();
            $table->integer('is_return')->default(0);
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

    /**
     * Test transaction atomicity rollback on failure.
     */
    public function testExpenseCategoryCreationFailureRollsBackAllTables()
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

        // Set up active user
        $user = \App\User::create([
            'id' => 1,
            'username' => 'admin',
            'business_id' => 1
        ]);
        $this->actingAs($user);
        session(['user' => ['id' => 1, 'business_id' => 1]]);

        // We make the controller request throw an exception by forcing an error in DB
        \DB::listen(function($query) {
            if (str_contains($query->sql, 'insert into `accounts`')) {
                throw new \RuntimeException("Forced Failure in Account Table Insertion");
            }
        });

        // Try to store via controller
        $response = $this->post('/expense-categories', [
            'name' => 'Gagal Total',
            'code' => 'GT01'
        ]);

        // Verify everything was rolled back completely
        $category_count = ExpenseCategory::where('name', 'Gagal Total')->count();
        $accounting_account_count = AccountingAccount::where('name', 'Gagal Total')->count();
        $pos_account_count = Account::where('name', 'Gagal Total')->count();

        $this->assertEquals(0, $category_count, "ExpenseCategory was not rolled back!");
        $this->assertEquals(0, $accounting_account_count, "AccountingAccount was not rolled back!");
        $this->assertEquals(0, $pos_account_count, "POS Account was not rolled back!");
    }

    /**
     * Test hotfix migration retroactively synchronizes old expense categories.
     */
    public function testSyncExistingExpenseCategoriesMigration()
    {
        $business_id = 1;

        // 1. Create cash account
        $cash_account = AccountingAccount::create([
            'name' => 'Kas Toko',
            'business_id' => $business_id,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'accounting_default_map' => json_encode([]),
        ]);

        // Insert records using raw SQL insert to simulate unsynced states
        \DB::table('expense_categories')->insert([
            'id' => 101,
            'name' => 'Cek System',
            'business_id' => $business_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('accounting_accounts')->insert([
            'id' => 201,
            'name' => 'Cek System',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'detail_type_id' => 138,
            'status' => 'active',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('expense_categories')->insert([
            'id' => 102,
            'name' => 'Ini Uji Coba',
            'business_id' => $business_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify pre-conditions
        $this->assertNull(Account::where('name', 'Cek System')->first());
        $this->assertNull(AccountingAccount::where('name', 'Ini Uji Coba')->first());
        $this->assertNull(Account::where('name', 'Ini Uji Coba')->first());

        // 2. Run Migration
        $migration = new \SyncExistingExpenseCategories();
        $migration->up();

        // 3. Verify 'Cek System' is now synchronized
        $posAcc1 = Account::where('name', 'Cek System')->first();
        $this->assertNotNull($posAcc1);
        $this->assertEquals(201, $posAcc1->accounting_account_id);

        $accountingAcc1 = AccountingAccount::find(201);
        $this->assertEquals($posAcc1->id, $accountingAcc1->account_id);

        // 4. Verify 'Ini Uji Coba' is now synchronized
        $accountingAcc2 = AccountingAccount::where('name', 'Ini Uji Coba')->first();
        $this->assertNotNull($accountingAcc2);

        $posAcc2 = Account::where('name', 'Ini Uji Coba')->first();
        $this->assertNotNull($posAcc2);

        $this->assertEquals($accountingAcc2->id, $posAcc2->accounting_account_id);
        $this->assertEquals($posAcc2->id, $accountingAcc2->account_id);

        // 5. Verify mappings are created under Business Location
        $location->refresh();
        $map = json_decode($location->accounting_default_map, true);

        $this->assertArrayHasKey('expense_101', $map);
        $this->assertEquals(201, $map['expense_101']['deposit_to']);
        $this->assertEquals($cash_account->id, $map['expense_101']['payment_account']);

        $this->assertArrayHasKey('expense_102', $map);
        $this->assertEquals($accountingAcc2->id, $map['expense_102']['deposit_to']);
        $this->assertEquals($cash_account->id, $map['expense_102']['payment_account']);
    }

    /**
     * Test dynamic payment account resolution for expenses from transaction_payments.
     */
    public function testExpenseCategoryDynamicPaymentAccountFromActualPayments()
    {
        $business_id = 1;

        // 1. Create Kas and Bank accounts
        $cash_account = AccountingAccount::create([
            'id' => 301,
            'name' => 'Kas Toko',
            'business_id' => $business_id,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        $bank_pos_account = Account::create([
            'name' => 'Bank Mandiri',
            'business_id' => $business_id,
            'account_number' => '1234',
        ]);

        $bank_accounting_account = AccountingAccount::find($bank_pos_account->accounting_account_id);
        $this->assertNotNull($bank_accounting_account);

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'accounting_default_map' => json_encode([]),
        ]);

        // 2. Create expense category AFTER location is created
        $category = ExpenseCategory::create([
            'name' => 'Gosend',
            'business_id' => $business_id
        ]);

        $expense_accounting_account = AccountingAccount::where('name', 'Gosend')->first();
        $this->assertNotNull($expense_accounting_account);

        // 3. Create expense transaction using Model so it can be queried via standard Eloquent methods
        $expense = \App\Transaction::create([
            'business_id' => $business_id,
            'location_id' => $location->id,
            'type' => 'expense',
            'final_total' => 25000,
            'expense_category_id' => $category->id,
        ]);

        // 4. Create transaction payment selecting "Bank Mandiri" account
        \DB::table('transaction_payments')->insert([
            'id' => 901,
            'transaction_id' => $expense->id,
            'amount' => 25000,
            'account_id' => $bank_pos_account->id, // Bank Mandiri
            'is_return' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Trigger MapExpenseTransactions
        $listener = new \Modules\Accounting\Listeners\MapExpenseTransactions();
        $event = new \stdClass();
        $event->expense = $expense;

        $listener->handle($event);

        // 6. Verify that the Double-Entry Accounting Credit leg points to Bank Mandiri (ID 401's accounting_account_id)
        // instead of the default Kas Toko (ID 301) from mapping!
        $journal_entries = AccountingAccountsTransaction::where('transaction_id', $expense->id)->get();
        $this->assertCount(2, $journal_entries);

        $debit_entry = $journal_entries->where('type', 'debit')->first();
        $credit_entry = $journal_entries->where('type', 'credit')->first();

        $this->assertEquals($expense_accounting_account->id, $debit_entry->accounting_account_id);
        $this->assertEquals($bank_accounting_account->id, $credit_entry->accounting_account_id, "The credit leg should point to Bank Mandiri account!");
        $this->assertEquals(25000, $credit_entry->amount);
    }

    /**
     * Test creating an AccountingAccount (Operating Expense) automatically creates ExpenseCategory
     * with code set from gl_code and maps it in BusinessLocation.
     */
    public function testAccountingAccountCreationSyncsToExpenseCategory()
    {
        $business_id = 1;

        $cash_account = AccountingAccount::create([
            'name' => 'Kas Toko',
            'business_id' => $business_id,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
            'status' => 'active',
        ]);

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'accounting_default_map' => json_encode([]),
        ]);

        // Create Operating Expense AccountingAccount
        $acc = AccountingAccount::create([
            'name' => 'Beban Kebersihan',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'gl_code' => '6105',
            'status' => 'active',
        ]);

        // Verify ExpenseCategory created automatically
        $category = ExpenseCategory::where('business_id', $business_id)
            ->where('name', 'Beban Kebersihan')
            ->first();

        $this->assertNotNull($category);
        $this->assertEquals('6105', $category->code);

        // Verify location default map updated
        $location->refresh();
        $map = json_decode($location->accounting_default_map, true);
        $this->assertArrayHasKey('expense_' . $category->id, $map);
        $this->assertEquals($acc->id, $map['expense_' . $category->id]['deposit_to']);
        $this->assertEquals($cash_account->id, $map['expense_' . $category->id]['payment_account']);
    }

    /**
     * Test updating AccountingAccount updates ExpenseCategory name and code.
     */
    public function testAccountingAccountUpdateSyncsToExpenseCategory()
    {
        $business_id = 1;

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'accounting_default_map' => json_encode([]),
        ]);

        $acc = AccountingAccount::create([
            'name' => 'Beban Kebersihan',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'gl_code' => '6105',
            'status' => 'active',
        ]);

        // Update name and gl_code
        $acc->update([
            'name' => 'Beban Kebersihan & K3',
            'gl_code' => '6106',
        ]);

        $category = ExpenseCategory::where('business_id', $business_id)
            ->where('name', 'Beban Kebersihan & K3')
            ->first();

        $this->assertNotNull($category);
        $this->assertEquals('6106', $category->code);
    }

    /**
     * Test deleting AccountingAccount deletes corresponding ExpenseCategory.
     */
    public function testAccountingAccountDeleteSyncsToExpenseCategory()
    {
        $business_id = 1;

        $location = BusinessLocation::create([
            'business_id' => $business_id,
            'accounting_default_map' => json_encode([]),
        ]);

        $acc = AccountingAccount::create([
            'name' => 'Beban Kebersihan',
            'business_id' => $business_id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'gl_code' => '6105',
            'status' => 'active',
        ]);

        $category = ExpenseCategory::where('business_id', $business_id)->where('name', 'Beban Kebersihan')->first();
        $this->assertNotNull($category);

        $acc->delete();

        $category_check = ExpenseCategory::find($category->id);
        $this->assertNull($category_check);
    }
}
