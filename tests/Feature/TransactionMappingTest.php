<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\BusinessLocation;
use App\Transaction;
use App\TransactionPayment;
use App\ExpenseCategory;
use App\Events\SellCreatedOrModified;
use App\Events\PurchaseCreatedOrModified;
use App\Events\ExpenseCreatedOrModified;
use App\Events\TransactionPaymentAdded;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use DB;

class TransactionMappingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->text('enabled_modules')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->text('accounting_default_map')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('final_total', 22, 4)->default(0.0000);
            $table->unsignedInteger('expense_category_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0.0000);
            $table->string('method', 100)->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
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
            $table->string('sub_type', 100)->nullable();
            $table->string('map_type', 100)->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Insert business
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'fy_start_month' => 1,
            'time_zone' => 'Asia/Jakarta',
            'enabled_modules' => json_encode([])
        ]);

        // Insert mock CoA accounts
        DB::table('accounting_accounts')->insert([
            ['id' => 10, 'name' => 'Pendapatan Penjualan', 'business_id' => 1, 'account_primary_type' => 'income'],
            ['id' => 20, 'name' => 'Piutang Usaha', 'business_id' => 1, 'account_primary_type' => 'asset'],
            ['id' => 30, 'name' => 'Kas', 'business_id' => 1, 'account_primary_type' => 'asset'],
            ['id' => 40, 'name' => 'Persediaan Barang', 'business_id' => 1, 'account_primary_type' => 'asset'],
            ['id' => 50, 'name' => 'Hutang Usaha', 'business_id' => 1, 'account_primary_type' => 'liability'],
            ['id' => 60, 'name' => 'Beban Listrik & Air', 'business_id' => 1, 'account_primary_type' => 'expense'],
            ['id' => 70, 'name' => 'Beban Internet (Khusus Kategori)', 'business_id' => 1, 'account_primary_type' => 'expense'],
        ]);

        // Register the Accounting service provider to activate the events and listeners
        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);

        // Put business_id, user_id, date_format, and timezone in session
        session([
            'user.business_id' => 1,
            'user.id' => 1,
            'business' => [
                'time_zone' => 'Asia/Jakarta',
                'date_format' => 'Y-m-d',
                'enabled_modules' => []
            ]
        ]);

        // Set Laravel Session Store on Request helper to prevent "Session store not set on request"
        request()->setLaravelSession($this->app['session']->driver());
    }

    /**
     * Test mapping of Sell transaction.
     */
    public function testMapSellTransaction()
    {
        // 1. Create Business Location with mapping for 'sale'
        // sale -> payment_account = 10 (Pendapatan Penjualan)
        // sale -> deposit_to = 20 (Piutang Usaha)
        $default_map = [
            'sale' => [
                'payment_account' => 10,
                'deposit_to' => 20
            ]
        ];

        $location = BusinessLocation::create([
            'business_id' => 1,
            'name' => 'Main Store',
            'accounting_default_map' => json_encode($default_map)
        ]);

        // 2. Create Transaction of type 'sell'
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => $location->id,
            'type' => 'sell',
            'final_total' => 1500000
        ]);

        // 3. Trigger the Event (MapSellTransaction Listener listens to this)
        event(new SellCreatedOrModified($transaction));

        // 4. Assertions:
        // We expect two entries in accounting_accounts_transactions for transaction_id
        $ledgerEntries = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $this->assertCount(2, $ledgerEntries);

        $paymentAccountEntry = $ledgerEntries->firstWhere('map_type', 'payment_account');
        $this->assertNotNull($paymentAccountEntry);
        $this->assertEquals(10, $paymentAccountEntry->accounting_account_id);
        $this->assertEquals(1500000, $paymentAccountEntry->amount);
        $this->assertEquals('credit', $paymentAccountEntry->type);
        $this->assertEquals('sell', $paymentAccountEntry->sub_type);

        $depositToEntry = $ledgerEntries->firstWhere('map_type', 'deposit_to');
        $this->assertNotNull($depositToEntry);
        $this->assertEquals(20, $depositToEntry->accounting_account_id);
        $this->assertEquals(1500000, $depositToEntry->amount);
        $this->assertEquals('debit', $depositToEntry->type);
        $this->assertEquals('sell', $depositToEntry->sub_type);

        // 5. Test Deletion mapping scenario
        $deleteEvent = new SellCreatedOrModified($transaction);
        $deleteEvent->isDeleted = true;
        event($deleteEvent);

        $this->assertEquals(0, AccountingAccountsTransaction::where('transaction_id', $transaction->id)->count());
    }

    /**
     * Test mapping of Purchase transaction.
     */
    public function testMapPurchaseTransaction()
    {
        // 1. Setup default map for purchases
        // purchases -> payment_account = 50 (Hutang Usaha)
        // purchases -> deposit_to = 40 (Persediaan Barang)
        $default_map = [
            'purchases' => [
                'payment_account' => 50,
                'deposit_to' => 40
            ]
        ];

        $location = BusinessLocation::create([
            'business_id' => 1,
            'name' => 'Main Store',
            'accounting_default_map' => json_encode($default_map)
        ]);

        // 2. Create Purchase transaction
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => $location->id,
            'type' => 'purchase',
            'final_total' => 3200000
        ]);

        // 3. Fire the event
        event(new PurchaseCreatedOrModified($transaction));

        // 4. Verify Accounting Ledger Entries
        $ledgerEntries = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $this->assertCount(2, $ledgerEntries);

        $paymentAccountEntry = $ledgerEntries->firstWhere('map_type', 'payment_account');
        $this->assertNotNull($paymentAccountEntry);
        $this->assertEquals(50, $paymentAccountEntry->accounting_account_id);
        $this->assertEquals(3200000, $paymentAccountEntry->amount);
        $this->assertEquals('credit', $paymentAccountEntry->type);

        $depositToEntry = $ledgerEntries->firstWhere('map_type', 'deposit_to');
        $this->assertNotNull($depositToEntry);
        $this->assertEquals(40, $depositToEntry->accounting_account_id);
        $this->assertEquals(3200000, $depositToEntry->amount);
        $this->assertEquals('debit', $depositToEntry->type);
    }

    /**
     * Test mapping of Expense transaction (both default fallback and category-specific mapping).
     */
    public function testMapExpenseTransaction()
    {
        // Create Expense Category
        $category = ExpenseCategory::create([
            'name' => 'Internet',
            'business_id' => 1
        ]);

        // 1. Setup Map with:
        // - default 'expense': payment_account = 30 (Kas), deposit_to = 60 (Beban Listrik & Air)
        // - category 'expense_{category_id}': payment_account = 30 (Kas), deposit_to = 70 (Beban Internet)
        $default_map = [
            'expense' => [
                'payment_account' => 30,
                'deposit_to' => 60
            ],
            'expense_' . $category->id => [
                'payment_account' => 30,
                'deposit_to' => 70
            ]
        ];

        $location = BusinessLocation::create([
            'business_id' => 1,
            'name' => 'Main Store',
            'accounting_default_map' => json_encode($default_map)
        ]);

        // A. Test standard/fallback expense mapping (no expense category assigned)
        $expenseStandard = Transaction::create([
            'business_id' => 1,
            'location_id' => $location->id,
            'type' => 'expense',
            'final_total' => 450000,
            'expense_category_id' => null
        ]);

        event(new ExpenseCreatedOrModified($expenseStandard));

        $ledgerStandard = AccountingAccountsTransaction::where('transaction_id', $expenseStandard->id)->get();
        $this->assertCount(2, $ledgerStandard);

        $depositToStandard = $ledgerStandard->firstWhere('map_type', 'deposit_to');
        $this->assertEquals(60, $depositToStandard->accounting_account_id); // Fallback standard: Beban Listrik & Air
        $this->assertEquals(450000, $depositToStandard->amount);

        // B. Test Category-specific expense mapping
        $expenseCategorized = Transaction::create([
            'business_id' => 1,
            'location_id' => $location->id,
            'type' => 'expense',
            'final_total' => 250000,
            'expense_category_id' => $category->id
        ]);

        event(new ExpenseCreatedOrModified($expenseCategorized));

        $ledgerCategorized = AccountingAccountsTransaction::where('transaction_id', $expenseCategorized->id)->get();
        $this->assertCount(2, $ledgerCategorized);

        $depositToCategorized = $ledgerCategorized->firstWhere('map_type', 'deposit_to');
        $this->assertEquals(70, $depositToCategorized->accounting_account_id); // Category-specific: Beban Internet
        $this->assertEquals(250000, $depositToCategorized->amount);
    }

    /**
     * Test mapping of Transaction Payments (Sales Payments & Purchase Payments).
     */
    public function testMapPaymentTransaction()
    {
        // 1. Setup Map for payments
        // sell_payment: payment_account = 20 (Piutang Usaha), deposit_to = 30 (Kas)
        // purchase_payment: payment_account = 30 (Kas), deposit_to = 50 (Hutang Usaha)
        $default_map = [
            'sell_payment' => [
                'payment_account' => 20,
                'deposit_to' => 30
            ],
            'purchase_payment' => [
                'payment_account' => 30,
                'deposit_to' => 50
            ]
        ];

        $location = BusinessLocation::create([
            'business_id' => 1,
            'name' => 'Main Store',
            'accounting_default_map' => json_encode($default_map)
        ]);

        // A. Sales Payment mapping test
        // i. Create sell transaction first
        $sellTx = Transaction::create([
            'business_id' => 1,
            'location_id' => $location->id,
            'type' => 'sell'
        ]);

        // ii. Create transaction payment
        $paymentSell = TransactionPayment::create([
            'business_id' => 1,
            'transaction_id' => $sellTx->id,
            'amount' => 500000
        ]);

        // iii. Fire payment added event
        event(new TransactionPaymentAdded($paymentSell));

        // iv. Assert ledger entries exist
        $ledgerSellPayment = AccountingAccountsTransaction::where('transaction_payment_id', $paymentSell->id)->get();
        $this->assertCount(2, $ledgerSellPayment);

        $paySellEntry = $ledgerSellPayment->firstWhere('map_type', 'payment_account');
        $this->assertNotNull($paySellEntry);
        $this->assertEquals(20, $paySellEntry->accounting_account_id); // Piutang Usaha (Credit)
        $this->assertEquals(500000, $paySellEntry->amount);

        $depSellEntry = $ledgerSellPayment->firstWhere('map_type', 'deposit_to');
        $this->assertNotNull($depSellEntry);
        $this->assertEquals(30, $depSellEntry->accounting_account_id); // Kas (Debit)
        $this->assertEquals(500000, $depSellEntry->amount);
    }
}
