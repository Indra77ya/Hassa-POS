<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Account;
use App\AccountTransaction;
use App\BusinessLocation;
use App\Transaction;
use App\TransactionPayment;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use DB;

class TransactionMappingTest extends TestCase
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
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        // Create account_types table
        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fixed_key')->nullable();
            $table->integer('parent_account_type_id')->nullable();
            $table->timestamps();
        });

        // Create accounts table
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('account_type_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('normal_balance')->nullable();
            $table->integer('is_closed')->default(0);
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

        // Create products
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('enable_stock')->default(1);
            $table->timestamps();
        });

        // Create variations
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->decimal('default_purchase_price', 22, 4);
            $table->timestamps();
        });

        // Create transaction_sell_lines
        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->decimal('quantity', 22, 4);
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create purchase_lines
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('purchase_price', 22, 4);
            $table->timestamps();
        });

        // Create transaction_sell_lines_purchase_lines
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sell_line_id');
            $table->integer('purchase_line_id');
            $table->decimal('quantity', 22, 4);
            $table->decimal('qty_returned', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transactions
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('sub_status')->nullable();
            $table->integer('is_quotation')->default(0);
            $table->string('payment_status')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('final_total', 22, 4)->default(0);
            $table->timestamps();
        });

        // Create transaction_payments
        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('payment_ref_no')->nullable();
            $table->boolean('is_return')->default(0);
            $table->boolean('is_advance')->default(0);
            $table->string('method')->nullable();
            $table->string('transaction_no')->nullable();
            $table->string('card_transaction_number')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_month')->nullable();
            $table->string('card_year')->nullable();
            $table->string('card_security')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->integer('payment_for')->nullable();
            $table->integer('parent_id')->nullable();
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
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Create accounting_accounts_transactions
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

        // Create users table
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
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

        // Create contacts table
        Schema::dropIfExists('contacts');
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('supplier_business_name')->nullable();
            $table->timestamps();
        });

        // Create account_transactions table with softDeletes
        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->string('type', 100);
            $table->decimal('amount', 22, 4);
            $table->string('sub_type', 100)->nullable();
            $table->dateTime('operation_date');
            $table->integer('created_by')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->integer('transfer_transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Seed basic settings
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Mapping Test Business',
        ]);

        DB::table('accounting_accounts')->insert([
            ['id' => 10, 'name' => 'Kas', 'business_id' => 1, 'account_primary_type' => 'asset', 'account_sub_type_id' => 3],
            ['id' => 11, 'name' => 'Piutang Usaha', 'business_id' => 1, 'account_primary_type' => 'asset', 'account_sub_type_id' => 1],
            ['id' => 12, 'name' => 'Pendapatan Penjualan', 'business_id' => 1, 'account_primary_type' => 'income', 'account_sub_type_id' => 11],
            ['id' => 13, 'name' => 'Harga Pokok Penjualan', 'business_id' => 1, 'account_primary_type' => 'expenses', 'account_sub_type_id' => 13],
            ['id' => 14, 'name' => 'Persediaan Barang', 'business_id' => 1, 'account_primary_type' => 'asset', 'account_sub_type_id' => 2],
            ['id' => 15, 'name' => 'Beban Air', 'business_id' => 1, 'account_primary_type' => 'expense', 'account_sub_type_id' => 14],
            ['id' => 21, 'name' => 'Hutang Usaha', 'business_id' => 1, 'account_primary_type' => 'liability', 'account_sub_type_id' => 4],
        ]);

        DB::table('business_locations')->insert([
            'id' => 1,
            'business_id' => 1,
            'accounting_default_map' => json_encode([
                'sale' => [
                    'payment_account' => 12, // Pendapatan Penjualan
                    'deposit_to' => 11,      // Piutang Usaha
                ],
                'sell_payment' => [
                    'payment_account' => 11, // Piutang Usaha
                    'deposit_to' => 10,      // Kas
                ],
                'purchases' => [
                    'payment_account' => 21, // Hutang Usaha
                    'deposit_to' => 14,      // Persediaan Barang
                ],
                'purchase_payment' => [
                    'payment_account' => 10, // Kas
                ],
                'expense' => [
                    'payment_account' => 10, // Kas
                    'deposit_to' => 15,      // Beban Air
                ]
            ]),
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;
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
        ]);
    }

    /**
     * Test balance validation: balanced entries should commit, unbalanced should throw exception.
     */
    public function testBalanceValidationEnforced()
    {
        // 1. Balanced: Debit 5000, Credit 5000
        DB::beginTransaction();
        AccountingAccountsTransaction::create([
            'accounting_account_id' => 10,
            'transaction_id' => 99,
            'amount' => 5000,
            'type' => 'debit',
            'operation_date' => now(),
        ]);
        AccountingAccountsTransaction::create([
            'accounting_account_id' => 12,
            'transaction_id' => 99,
            'amount' => 5000,
            'type' => 'credit',
            'operation_date' => now(),
        ]);

        AccountingAccountsTransaction::validateTransactionBalance(99);
        DB::commit();

        $this->assertTrue(true); // Should pass without exception

        // 2. Unbalanced: Debit 5000, Credit 4000
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Ketidakseimbangan jurnal akuntansi terdeteksi");

        DB::beginTransaction();
        AccountingAccountsTransaction::create([
            'accounting_account_id' => 10,
            'transaction_id' => 100,
            'amount' => 5000,
            'type' => 'debit',
            'operation_date' => now(),
        ]);
        AccountingAccountsTransaction::create([
            'accounting_account_id' => 12,
            'transaction_id' => 100,
            'amount' => 4000,
            'type' => 'credit',
            'operation_date' => now(),
        ]);

        try {
            AccountingAccountsTransaction::validateTransactionBalance(100);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Test Cash Sale mapping directs straight to Kas and Pendapatan Penjualan, bypassing Piutang Usaha.
     */
    public function testCashSaleMappingDirectToKas()
    {
        // Create a fully-paid (cash) sell transaction
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'sell',
            'payment_status' => 'paid',
            'invoice_no' => 'INV-0001',
            'final_total' => 6060000,
        ]);

        $event = new \App\Events\SellCreatedOrModified($transaction);

        $listener = new \Modules\Accounting\Listeners\MapSellTransaction();
        $listener->handle($event);

        // Verify direct Debit to Kas (10) and Credit to Pendapatan Penjualan (12)
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();

        $kas_tx = $txs->where('accounting_account_id', 10)->first();
        $this->assertNotNull($kas_tx);
        $this->assertEquals('debit', $kas_tx->type);
        $this->assertEquals(6060000, $kas_tx->amount);

        $rev_tx = $txs->where('accounting_account_id', 12)->first();
        $this->assertNotNull($rev_tx);
        $this->assertEquals('credit', $rev_tx->type);
        $this->assertEquals(6060000, $rev_tx->amount);

        // Verify Piutang Usaha (11) was bypassed (0 entries)
        $this->assertNull($txs->where('accounting_account_id', 11)->first());
    }

    /**
     * Test COGS and Inventory Reduction Jounals are created for stock managed items on Sale.
     */
    public function testCogsAndInventoryReductionOnSale()
    {
        // Create stock-managed product
        DB::table('products')->insert([
            'id' => 50,
            'name' => 'Fisik Product',
            'enable_stock' => 1,
        ]);

        DB::table('variations')->insert([
            'id' => 60,
            'product_id' => 50,
            'default_purchase_price' => 1500, // Cost is 1500
        ]);

        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'sell',
            'payment_status' => 'paid',
            'invoice_no' => 'INV-0002',
            'final_total' => 5000,
        ]);

        DB::table('transaction_sell_lines')->insert([
            'transaction_id' => $transaction->id,
            'product_id' => 50,
            'variation_id' => 60,
            'quantity' => 2, // 2 items sold
            'quantity_returned' => 0,
        ]);

        $event = new \App\Events\SellCreatedOrModified($transaction);
        $listener = new \Modules\Accounting\Listeners\MapSellTransaction();
        $listener->handle($event);

        // COGS amount should be 2 * 1500 = 3000
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();

        $cogs_tx = $txs->where('accounting_account_id', 13)->first();
        $this->assertNotNull($cogs_tx);
        $this->assertEquals('debit', $cogs_tx->type);
        $this->assertEquals(3000, $cogs_tx->amount);

        $inv_tx = $txs->where('accounting_account_id', 14)->first();
        $this->assertNotNull($inv_tx);
        $this->assertEquals('credit', $inv_tx->type);
        $this->assertEquals(3000, $inv_tx->amount);
    }

    /**
     * Test that the Cash Flow report strictly retrieves and renders only transactions
     * of accounts belonging to 'kas_dan_bank' (cash_and_cash_equivalents).
     */
    public function testCashFlowFilterToKasDanBank()
    {
        // 1. Create account types
        $kasType = DB::table('account_types')->insertGetId([
            'name' => 'Kas dan Bank',
            'fixed_key' => 'kas_dan_bank',
        ]);
        $piutangType = DB::table('account_types')->insertGetId([
            'name' => 'Piutang',
            'fixed_key' => 'piutang_usaha',
        ]);

        // 2. Create accounts
        $kasAcc = DB::table('accounts')->insertGetId([
            'name' => 'Kas Bank Utama',
            'business_id' => 1,
            'account_type_id' => $kasType,
        ]);
        $piutangAcc = DB::table('accounts')->insertGetId([
            'name' => 'Piutang Utama',
            'business_id' => 1,
            'account_type_id' => $piutangType,
        ]);

        // 3. Create transactions on those accounts
        DB::table('account_transactions')->insert([
            [
                'account_id' => $kasAcc,
                'type' => 'debit',
                'amount' => 1000000,
                'operation_date' => '2026-07-31 14:36:00',
            ],
            [
                'account_id' => $piutangAcc,
                'type' => 'debit',
                'amount' => 5000000,
                'operation_date' => '2026-07-31 14:36:00',
            ],
        ]);

        // 4. Request the cash flow endpoint via ajax
        $response = $this->get('/account/cash-flow', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        // We should ONLY see the transaction on Kas Bank Utama (1 transaction)
        $this->assertCount(1, $data);
        $this->assertEquals('Kas Bank Utama', $data[0]['account_name']);
    }

    /**
     * Test overpayment (change return) mapping correctly records net flow to Kas and Revenue, bypassing Piutang.
     */
    public function testChangeReturnPaymentMapping()
    {
        // 1. Create a sell transaction for 720,000
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'sell',
            'payment_status' => 'paid',
            'invoice_no' => 'INV-0009',
            'final_total' => 720000,
        ]);

        // 2. Create the overpayment (750,000) and change return (30,000)
        DB::table('transaction_payments')->insert([
            [
                'transaction_id' => $transaction->id,
                'business_id' => 1,
                'amount' => 750000,
                'is_return' => 0,
                'created_at' => now(),
            ],
            [
                'transaction_id' => $transaction->id,
                'business_id' => 1,
                'amount' => 30000,
                'is_return' => 1,
                'created_at' => now(),
            ]
        ]);

        // 3. Trigger mapping listener
        $event = new \App\Events\SellCreatedOrModified($transaction);
        $listener = new \Modules\Accounting\Listeners\MapSellTransaction();
        $listener->handle($event);

        // 4. Verify results
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();

        // Credit to Pendapatan Penjualan (12) must be 720,000
        $rev_tx = $txs->where('accounting_account_id', 12)->first();
        $this->assertNotNull($rev_tx);
        $this->assertEquals('credit', $rev_tx->type);
        $this->assertEquals(720000, $rev_tx->amount);

        // Debit to Kas (10) must be net 720,000 (payments 750k - returns 30k = 720k)
        $kas_tx = $txs->where('accounting_account_id', 10)->first();
        $this->assertNotNull($kas_tx);
        $this->assertEquals('debit', $kas_tx->type);
        $this->assertEquals(720000, $kas_tx->amount);

        // Piutang (11) must have 0 entries
        $this->assertNull($txs->where('accounting_account_id', 11)->first());
    }

    /**
     * Test Expense mapping correctly records Debit on Beban (deposit_to) and Credit on Kas (payment_account).
     */
    public function testExpenseMappingDebitBebanCreditKas()
    {
        // 1. Create an expense transaction for 250,000
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'expense',
            'payment_status' => 'paid',
            'invoice_no' => 'EXP-0001',
            'final_total' => 250000,
        ]);

        // 2. Trigger mapping listener
        $event = new \App\Events\ExpenseCreatedOrModified($transaction);
        $listener = new \Modules\Accounting\Listeners\MapExpenseTransactions();
        $listener->handle($event);

        // 3. Verify results
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();

        // Debit to Beban Air (15) must be 250,000
        $debit_tx = $txs->where('accounting_account_id', 15)->first();
        $this->assertNotNull($debit_tx);
        $this->assertEquals('debit', $debit_tx->type);
        $this->assertEquals(250000, $debit_tx->amount);

        // Credit to Kas (10) must be 250,000
        $credit_tx = $txs->where('accounting_account_id', 10)->first();
        $this->assertNotNull($credit_tx);
        $this->assertEquals('credit', $credit_tx->type);
        $this->assertEquals(250000, $credit_tx->amount);
    }

    /**
     * Test that Quotation and Draft documents are ignored from accounting mapping.
     * Also test transitions: Draft -> Final (maps), Final -> Draft (deletes mapping), and Deletion (cleans up).
     */
    public function testQuotationAndDraftHandling()
    {
        // 1. Create a draft/quotation transaction
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'sell',
            'status' => 'draft',
            'sub_status' => 'quotation',
            'is_quotation' => 1,
            'payment_status' => 'due',
            'invoice_no' => 'QUO-0001',
            'final_total' => 4500,
        ]);

        DB::table('transaction_sell_lines')->insert([
            'transaction_id' => $transaction->id,
            'product_id' => 1,
            'variation_id' => 1,
            'quantity' => 1,
            'quantity_returned' => 0,
        ]);

        // Trigger mapping listener
        $event = new \App\Events\SellCreatedOrModified($transaction);
        $listener = new \Modules\Accounting\Listeners\MapSellTransaction();
        $listener->handle($event);

        // Verify that NO mapping exists for this transaction since it is a draft/quotation
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $this->assertCount(0, $txs, 'Quotation/Draft must not generate any accounting transactions');

        // 2. Transition: Convert Draft/Quotation to Final
        $transaction->status = 'final';
        $transaction->sub_status = null;
        $transaction->is_quotation = 0;
        $transaction->save();

        // Trigger mapping listener again
        $event = new \App\Events\SellCreatedOrModified($transaction);
        $listener->handle($event);

        // Verify that mapping WAS generated when transitioned to final
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $this->assertGreaterThan(0, $txs->count(), 'Converting Quotation/Draft to Final must generate accounting transactions');

        // 3. Transition: Convert Final back to Draft/Quotation
        $transaction->status = 'draft';
        $transaction->sub_status = 'quotation';
        $transaction->is_quotation = 1;
        $transaction->save();

        // Trigger mapping listener again
        $event = new \App\Events\SellCreatedOrModified($transaction);
        $listener->handle($event);

        // Verify that mappings are DELETED/REMOVED when transitioned back to draft/quotation
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $this->assertCount(0, $txs, 'Changing status back to draft/quotation must delete/reverse existing accounting transactions');

        // 4. Deletion: If a transaction is deleted, its mappings must be deleted too
        // Convert to final first to have mappings
        $transaction->status = 'final';
        $transaction->sub_status = null;
        $transaction->is_quotation = 0;
        $transaction->save();

        $event = new \App\Events\SellCreatedOrModified($transaction);
        $listener->handle($event);

        $this->assertGreaterThan(0, AccountingAccountsTransaction::where('transaction_id', $transaction->id)->count());

        // Now dispatch SellCreatedOrModified with isDeleted = true
        \App\Events\SellCreatedOrModified::dispatch($transaction, true);

        // Verify that mappings are fully deleted
        $txs = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $this->assertCount(0, $txs, 'Deleting the sale must cleanly remove all its accounting mappings');
    }

    public function testAccountsDropdownEndpoint()
    {
        $user = \App\User::create([
            'surname' => 'Mr',
            'first_name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'business_id' => 1,
        ]);

        $this->actingAs($user);

        // Set up session for business_id and enabled modules
        session([
            'user.business_id' => 1,
            'business' => ['enabled_modules' => ['account']]
        ]);

        // Create an account
        Account::create([
            'business_id' => 1,
            'name' => 'Kas Test POS',
            'account_number' => '10101',
            'is_closed' => 0
        ]);

        $controller = new \App\Http\Controllers\SellPosController(
            $this->app->make(\App\Utils\ContactUtil::class),
            $this->app->make(\App\Utils\ProductUtil::class),
            $this->app->make(\App\Utils\BusinessUtil::class),
            $this->app->make(\App\Utils\TransactionUtil::class),
            $this->app->make(\App\Utils\CashRegisterUtil::class),
            $this->app->make(\App\Utils\ModuleUtil::class),
            $this->app->make(\App\Utils\NotificationUtil::class)
        );

        $response = $controller->getAccountsDropdown();
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
    }

    /**
     * Test that deleting/cancelling a payment updates the mapping and balance immediately,
     * even for recently created transactions.
     */
    public function testPaymentDeletionUpdatesMappingImmediately()
    {
        // 1. Create a purchase transaction
        $transaction = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'purchase',
            'payment_status' => 'paid',
            'ref_no' => 'PO-NEW-1111',
            'final_total' => 5000000,
            'created_at' => now(), // created just now (within 30 seconds)
        ]);

        // 2. Add payment record to database
        $payment = TransactionPayment::create([
            'transaction_id' => $transaction->id,
            'business_id' => 1,
            'amount' => 5000000,
            'is_return' => 0,
            'method' => 'cash',
        ]);

        // 3. Trigger initial mapping listener
        $eventAdded = new \App\Events\PurchaseCreatedOrModified($transaction);
        $listenerPurchase = new \Modules\Accounting\Listeners\MapPurchaseTransaction();
        $listenerPurchase->handle($eventAdded);

        // Verify Cash (10) has a Credit entry of 5,000,000 (which reduced Cash)
        $txsBefore = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $cashBefore = $txsBefore->where('accounting_account_id', 10)->first();
        $this->assertNotNull($cashBefore);
        $this->assertEquals('credit', $cashBefore->type);
        $this->assertEquals(5000000, $cashBefore->amount);

        // 4. Now, delete the payment and fire TransactionPaymentDeleted
        $payment->delete();
        $eventDeleted = new \App\Events\TransactionPaymentDeleted($payment);
        $listenerPayment = new \Modules\Accounting\Listeners\MapPaymentTransaction();
        $listenerPayment->handle($eventDeleted);

        // 5. Verify Cash Credit entry is gone or updated, and the cash balance is restored
        $txsAfter = AccountingAccountsTransaction::where('transaction_id', $transaction->id)->get();
        $cashAfter = $txsAfter->where('accounting_account_id', 10)->first();

        // Since payment was deleted, net_paid is 0. So no Cash Credit entry should exist for this transaction anymore.
        $this->assertNull($cashAfter, 'Cash Credit entry must be deleted when the payment is deleted');
    }
}
