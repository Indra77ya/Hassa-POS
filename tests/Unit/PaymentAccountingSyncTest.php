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
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('created_by')->nullable();
            $table->string('note')->nullable();
            $table->string('account_number')->nullable();
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
}
