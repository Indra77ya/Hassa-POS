<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Account;
use App\AccountTransaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

class PaymentAccountingSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Manually create required minimal schema tables in SQLite in-memory database
        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id');
            $table->string('fixed_key')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('created_by')->nullable();
            $table->string('note')->nullable();
            $table->string('account_number')->nullable();
            $table->integer('account_type_id')->nullable();
            $table->tinyInteger('is_closed')->default(0);
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('gl_code')->nullable();
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
            $table->bigInteger('account_sub_type_id')->nullable();
            $table->bigInteger('detail_type_id')->nullable();
            $table->bigInteger('parent_account_id')->nullable();
            $table->longText('description')->nullable();
            $table->string('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

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
            $table->text('note')->nullable();
            $table->unsignedBigInteger('accounting_accounts_transaction_id')->nullable();
            $table->integer('transfer_transaction_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

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
            $table->softDeletes();
            $table->timestamps();
        });

        // Reset sync status guards
        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
        AccountTransaction::$is_syncing = false;
        AccountingAccountsTransaction::$is_syncing = false;
    }

    /**
     * Test bidirectional master account creation and linking.
     */
    public function testBidirectionalAccountCreation()
    {
        // 1. Create a Payment Account -> should create an AccountingAccount
        $paymentAccount = Account::create([
            'name' => 'Bank BCA Ter-Sync',
            'business_id' => 1,
            'created_by' => 1,
            'note' => 'BCA sync note',
            'account_number' => '123456',
            'is_closed' => 0,
        ]);

        $this->assertNotNull($paymentAccount->accounting_account_id);

        $accountingAccount = AccountingAccount::find($paymentAccount->accounting_account_id);
        $this->assertNotNull($accountingAccount);
        $this->assertEquals('Bank BCA Ter-Sync', $accountingAccount->name);
        $this->assertEquals(1, $accountingAccount->business_id);
        $this->assertEquals('BCA sync note', $accountingAccount->description);
        $this->assertEquals('123456', $accountingAccount->gl_code);
        $this->assertEquals('active', $accountingAccount->status);
        $this->assertEquals('asset', $accountingAccount->account_primary_type);
        $this->assertEquals(3, $accountingAccount->account_sub_type_id);
        $this->assertEquals($paymentAccount->id, $accountingAccount->account_id);

        // 2. Create an Accounting Account -> should create a Payment Account
        $newAccountingAccount = AccountingAccount::create([
            'name' => 'Bank Mandiri Ter-Sync',
            'business_id' => 1,
            'created_by' => 1,
            'description' => 'Mandiri sync note',
            'gl_code' => '654321',
            'status' => 'active',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3, // Cash and cash equivalents
        ]);

        $this->assertNotNull($newAccountingAccount->account_id);

        $linkedPaymentAccount = Account::find($newAccountingAccount->account_id);
        $this->assertNotNull($linkedPaymentAccount);
        $this->assertEquals('Bank Mandiri Ter-Sync', $linkedPaymentAccount->name);
        $this->assertEquals('654321', $linkedPaymentAccount->account_number);
        $this->assertEquals('Mandiri sync note', $linkedPaymentAccount->note);
        $this->assertEquals(0, $linkedPaymentAccount->is_closed);
        $this->assertEquals($newAccountingAccount->id, $linkedPaymentAccount->accounting_account_id);
    }

    /**
     * Test bidirectional master account updates.
     */
    public function testBidirectionalAccountUpdate()
    {
        $paymentAccount = Account::create([
            'name' => 'Original Name',
            'business_id' => 1,
            'created_by' => 1,
            'is_closed' => 0,
        ]);

        $accountingAccount = AccountingAccount::find($paymentAccount->accounting_account_id);
        $this->assertNotNull($accountingAccount);

        // Update Payment Account
        $paymentAccount->update([
            'name' => 'Updated Name From POS',
            'note' => 'New POS Note',
            'is_closed' => 1,
        ]);

        $accountingAccount->refresh();
        $this->assertEquals('Updated Name From POS', $accountingAccount->name);
        $this->assertEquals('New POS Note', $accountingAccount->description);
        $this->assertEquals('inactive', $accountingAccount->status);

        // Update Accounting Account
        $accountingAccount->update([
            'name' => 'Updated Name From Accounting',
            'description' => 'New Accounting Note',
            'status' => 'active',
        ]);

        $paymentAccount->refresh();
        $this->assertEquals('Updated Name From Accounting', $paymentAccount->name);
        $this->assertEquals('New Accounting Note', $paymentAccount->note);
        $this->assertEquals(0, $paymentAccount->is_closed);
    }

    /**
     * Test bidirectional master account deletions.
     */
    public function testBidirectionalAccountDeletion()
    {
        $paymentAccount = Account::create([
            'name' => 'To Be Deleted',
            'business_id' => 1,
        ]);

        $accountingAccountId = $paymentAccount->accounting_account_id;

        // Delete Payment Account
        $paymentAccount->delete();

        $this->assertNull(AccountingAccount::find($accountingAccountId));
    }

    /**
     * Test transaction synchronization.
     */
    public function testTransactionSynchronization()
    {
        $paymentAccount = Account::create([
            'name' => 'Tx Sync Account',
            'business_id' => 1,
        ]);

        // 1. Create AccountTransaction in POS -> should sync to Accounting
        $tx = AccountTransaction::create([
            'account_id' => $paymentAccount->id,
            'type' => 'debit',
            'amount' => 150000,
            'sub_type' => 'deposit',
            'operation_date' => now(),
            'note' => 'Deposit POS note',
        ]);

        $this->assertNotNull($tx->accounting_accounts_transaction_id);

        $aat = AccountingAccountsTransaction::find($tx->accounting_accounts_transaction_id);
        $this->assertNotNull($aat);
        $this->assertEquals(150000, $aat->amount);
        $this->assertEquals('debit', $aat->type);
        $this->assertEquals('deposit', $aat->sub_type);
        $this->assertEquals('Deposit POS note', $aat->note);

        // 2. Update AccountTransaction in POS -> should update Accounting
        $tx->update([
            'amount' => 180000,
            'note' => 'Updated POS note',
        ]);

        $aat->refresh();
        $this->assertEquals(180000, $aat->amount);
        $this->assertEquals('Updated POS note', $aat->note);

        // 3. Create AccountingAccountsTransaction in Accounting -> should sync to POS
        $aatNew = AccountingAccountsTransaction::create([
            'accounting_account_id' => $paymentAccount->accounting_account_id,
            'type' => 'credit',
            'amount' => 50000,
            'sub_type' => 'other',
            'operation_date' => now(),
            'note' => 'Credit note accounting',
        ]);

        $this->assertNotNull($aatNew->account_transaction_id);

        $txNew = AccountTransaction::find($aatNew->account_transaction_id);
        $this->assertNotNull($txNew);
        $this->assertEquals(50000, $txNew->amount);
        $this->assertEquals('credit', $txNew->type);
        $this->assertEquals('Credit note accounting', $txNew->note);

        // 4. Delete POS transaction -> should delete Accounting transaction
        $tx->delete();
        $this->assertNull(AccountingAccountsTransaction::find($aat->id));
    }

    /**
     * Test the console bulk sync command.
     */
    public function testConsoleSyncCommand()
    {
        // Setup unlinked accounts and transactions manually with sync turned off
        Account::$is_syncing = true;
        AccountingAccount::$is_syncing = true;
        AccountTransaction::$is_syncing = true;
        AccountingAccountsTransaction::$is_syncing = true;

        $unlinkedPaymentAccount = Account::create([
            'name' => 'Unlinked POS Bank',
            'business_id' => 1,
            'account_number' => '999999',
            'note' => 'unlinked POS',
        ]);

        $unlinkedAccountingAccount = AccountingAccount::create([
            'name' => 'Unlinked Accounting Bank',
            'business_id' => 1,
            'gl_code' => '888888',
            'description' => 'unlinked Accounting',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3,
        ]);

        $this->assertNull($unlinkedPaymentAccount->accounting_account_id);
        $this->assertNull($unlinkedAccountingAccount->account_id);

        // Create unlinked transactions for unlinkedPaymentAccount
        $at = AccountTransaction::create([
            'account_id' => $unlinkedPaymentAccount->id,
            'type' => 'debit',
            'amount' => 777000,
            'operation_date' => now(),
            'note' => 'unlinked tx',
        ]);

        // Turn sync back on for the Command
        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
        AccountTransaction::$is_syncing = false;
        AccountingAccountsTransaction::$is_syncing = false;

        // Run the artisan sync command
        Artisan::call('pos:sync-payment-accounting');

        // Verify account linkage
        $unlinkedPaymentAccount->refresh();
        $this->assertNotNull($unlinkedPaymentAccount->accounting_account_id);

        $newAccountingAccount = AccountingAccount::find($unlinkedPaymentAccount->accounting_account_id);
        $this->assertNotNull($newAccountingAccount);
        $this->assertEquals($unlinkedPaymentAccount->id, $newAccountingAccount->account_id);

        // Verify transaction synchronization
        $at->refresh();
        $this->assertNotNull($at->accounting_accounts_transaction_id);

        $aat = AccountingAccountsTransaction::find($at->accounting_accounts_transaction_id);
        $this->assertNotNull($aat);
        $this->assertEquals(777000, $aat->amount);
    }

    /**
     * Test that bulk inserting default accounts (simulating 'Create Default Accounts')
     * followed by the pos:sync-payment-accounting sync command correctly synchronizes
     * Cash and cash equivalents accounts and also verifies that POS default accounts are seeded.
     */
    public function testDefaultAccountsSync()
    {
        // Setup initial clean state
        Account::truncate();
        AccountingAccount::truncate();
        \App\AccountType::truncate();

        // Simulate session/environment variables
        $business_id = 1;
        $user_id = 1;

        // 1. Prepare default accounts mimicking CoaController@createDefaultAccounts
        $default_accounts = [
            [
                'name' => 'Cash and cash equivalents',
                'business_id' => $business_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => 3,
                'detail_type_id' => 31,
                'status' => 'active',
                'created_by' => $user_id,
            ],
            [
                'name' => 'Accounts Payable (A/P)',
                'business_id' => $business_id,
                'account_primary_type' => 'liability',
                'account_sub_type_id' => 6,
                'detail_type_id' => 58,
                'status' => 'active',
                'created_by' => $user_id,
            ]
        ];

        // 2. Run POS default account types and accounts seeding (mimicked from CoaController)
        $default_types = [
            ['key' => 'kas_dan_bank', 'parent' => null],
            ['key' => 'piutang_usaha', 'parent' => null],
        ];

        $created_types = [];
        foreach ($default_types as $at) {
            $type = \App\AccountType::create([
                'name' => 'Mock ' . $at['key'],
                'business_id' => $business_id,
                'parent_account_type_id' => null,
                'fixed_key' => $at['key']
            ]);
            $created_types[$at['key']] = $type->id;
        }

        $default_pos_accounts = [
            ['name' => 'Kas', 'type' => 'kas_dan_bank', 'number' => '1101', 'balance' => 'debit'],
            ['name' => 'Bank', 'type' => 'kas_dan_bank', 'number' => '1102', 'balance' => 'debit'],
        ];

        foreach ($default_pos_accounts as $da) {
            Account::create([
                'name' => $da['name'],
                'business_id' => $business_id,
                'account_number' => $da['number'],
                'account_type_id' => $created_types[$da['type']],
                'normal_balance' => $da['balance'],
                'created_by' => $user_id
            ]);
        }

        // Verify that POS accounts are created and they automatically triggered Accounting Account equivalents
        $this->assertEquals(2, Account::count());
        $this->assertEquals(2, AccountingAccount::count());

        // Now do bulk insert for other Accounting default accounts (which bypass model events)
        AccountingAccount::insert($default_accounts);

        // Verify total AccountingAccount count is 4 (2 from POS creation, 2 from bulk insert)
        $this->assertEquals(4, AccountingAccount::count());

        // 3. Run the bidirectional sync command (same as inside createDefaultAccounts())
        Artisan::call('pos:sync-payment-accounting');

        // 4. Verify that "Cash and cash equivalents" is now successfully synced/propagated to POS accounts list
        // and mapped to each other
        $cashAndEquivalentPOS = Account::where('name', 'Cash and cash equivalents')->first();
        $this->assertNotNull($cashAndEquivalentPOS);

        $cashAndEquivalentAccounting = AccountingAccount::where('name', 'Cash and cash equivalents')->first();
        $this->assertNotNull($cashAndEquivalentAccounting);

        $this->assertEquals($cashAndEquivalentAccounting->id, $cashAndEquivalentPOS->accounting_account_id);
        $this->assertEquals($cashAndEquivalentPOS->id, $cashAndEquivalentAccounting->account_id);
    }

    /**
     * Test POS-originating Fund Transfer synchronization and mapping.
     */
    public function testPOSFundTransferSynchronization()
    {
        // Setup table `accounting_acc_trans_mappings` in in-memory sqlite
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

        $fromAcc = Account::create([
            'name' => 'From Account',
            'business_id' => 1,
        ]);

        $toAcc = Account::create([
            'name' => 'To Account',
            'business_id' => 1,
        ]);

        // Creating the source transaction first (debit/credit according to transfer)
        $tx1 = AccountTransaction::create([
            'account_id' => $fromAcc->id,
            'type' => 'credit',
            'amount' => 5000,
            'sub_type' => 'fund_transfer',
            'operation_date' => now(),
            'note' => 'Transfer note test',
        ]);

        // Creating the destination transaction pointing back to tx1
        $tx2 = AccountTransaction::create([
            'account_id' => $toAcc->id,
            'type' => 'debit',
            'amount' => 5000,
            'sub_type' => 'fund_transfer',
            'transfer_transaction_id' => $tx1->id,
            'operation_date' => now(),
            'note' => 'Transfer note test',
        ]);

        // Update tx1 with tx2's ID to fully link them (mimicking AccountController@postFundTransfer)
        $tx1->update([
            'transfer_transaction_id' => $tx2->id,
        ]);

        // Trigger sync manually/indirectly via model event (on update)
        $tx1->refresh();
        $this->assertEquals($tx2->id, $tx1->transfer_transaction_id, "transfer_transaction_id was null. tx1 is: " . json_encode($tx1));

        // Verify that both AccountingAccountsTransactions are linked to this mapping
        $aat1 = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('account_transaction_id', $tx1->id)->first();
        $aat2 = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('account_transaction_id', $tx2->id)->first();

        // Verify that a mapping has been automatically created under Accounting
        $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::where('business_id', 1)->first();

        $this->assertNotNull($mapping);
        $this->assertEquals('transfer', $mapping->type);
        $this->assertEquals('Transfer note test', $mapping->note);

        $this->assertNotNull($aat1);
        $this->assertNotNull($aat2);
        $this->assertEquals($mapping->id, $aat1->acc_trans_mapping_id);
        $this->assertEquals($mapping->id, $aat2->acc_trans_mapping_id);
    }

    /**
     * Test Accounting-originated Transfer synchronization and POS linking.
     */
    public function testAccountingTransferSynchronization()
    {
        // Setup table `accounting_acc_trans_mappings`
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

        // 1. Create two AccountingAccounts with linked POS Accounts
        $paymentAccountFrom = Account::create(['name' => 'From Acc POS', 'business_id' => 1]);
        $paymentAccountTo = Account::create(['name' => 'To Acc POS', 'business_id' => 1]);

        $this->assertNotNull($paymentAccountFrom->accounting_account_id);
        $this->assertNotNull($paymentAccountTo->accounting_account_id);

        // 2. Create Transfer Mapping in Accounting
        $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::create([
            'business_id' => 1,
            'ref_no' => 'TRX-ACCOUNTING-001',
            'type' => 'transfer',
            'created_by' => 1,
            'operation_date' => now(),
            'note' => 'Accounting transfer note',
        ]);

        // 3. Create the two Accounting transaction legs linked to the mapping
        $aat1 = \Modules\Accounting\Entities\AccountingAccountsTransaction::create([
            'accounting_account_id' => $paymentAccountFrom->accounting_account_id,
            'acc_trans_mapping_id' => $mapping->id,
            'amount' => 3000,
            'type' => 'credit',
            'sub_type' => 'transfer',
            'created_by' => 1,
            'operation_date' => now(),
            'note' => 'Accounting transfer note',
        ]);

        $aat2 = \Modules\Accounting\Entities\AccountingAccountsTransaction::create([
            'accounting_account_id' => $paymentAccountTo->accounting_account_id,
            'acc_trans_mapping_id' => $mapping->id,
            'amount' => 3000,
            'type' => 'debit',
            'sub_type' => 'transfer',
            'created_by' => 1,
            'operation_date' => now(),
            'note' => 'Accounting transfer note',
        ]);

        // Trigger manual sync or update to trigger linkPOSFundTransfer
        \Modules\Accounting\Entities\AccountingAccountsTransaction::linkPOSFundTransfer($aat1);

        // 4. Verify that corresponding POS AccountTransactions are created, linked and have sub_type = 'fund_transfer'
        $at1 = \App\AccountTransaction::where('accounting_accounts_transaction_id', $aat1->id)->first();
        $at2 = \App\AccountTransaction::where('accounting_accounts_transaction_id', $aat2->id)->first();

        $this->assertNotNull($at1);
        $this->assertNotNull($at2);
        $this->assertEquals('fund_transfer', $at1->sub_type);
        $this->assertEquals('fund_transfer', $at2->sub_type);
        $this->assertEquals($at2->id, $at1->transfer_transaction_id);
        $this->assertEquals($at1->id, $at2->transfer_transaction_id);
    }
}
