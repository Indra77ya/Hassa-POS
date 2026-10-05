<?php

namespace Tests\Feature;

use App\Account;
use App\AccountTransaction;
use App\Business;
use App\Contact;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReturnPaymentAccountTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;
    protected $bankAccount;
    protected $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::firstOrCreate(
            ['name' => 'Test Business Return'],
            ['currency_id' => 1, 'start_date' => '2020-01-01', 'time_zone' => 'Asia/Jakarta', 'tax_number_1' => '123', 'owner_id' => 1]
        );

        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
            'surname' => 'Test',
            'first_name' => 'SupplierReturn',
            'email' => 'returnuser_' . uniqid() . '@example.com',
        ]);

        $this->bankAccount = Account::create([
            'business_id' => $this->business->id,
            'name' => 'Bank Test',
            'account_number' => '1002',
            'created_by' => $this->user->id,
        ]);

        $this->supplier = Contact::create([
            'business_id' => $this->business->id,
            'type' => 'supplier',
            'name' => 'Pahlawan Laptop',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_purchase_return_payment_creates_debit_account_transaction()
    {
        // 1. Create a purchase_return transaction
        $purchaseReturn = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'purchase_return',
            'status' => 'final',
            'payment_status' => 'due',
            'contact_id' => $this->supplier->id,
            'transaction_date' => now(),
            'total_before_tax' => 13975000,
            'final_total' => 13975000,
            'created_by' => $this->user->id,
        ]);

        // Give permissions
        \Permission::firstOrCreate(['name' => 'purchase.payments', 'guard_name' => 'web']);
        $this->user->givePermissionTo('purchase.payments');

        // 2. Add payment on purchase_return transaction
        $response = $this->withSession([
            'user.business_id' => $this->business->id,
            'business.id' => $this->business->id,
        ])->post(action([\App\Http\Controllers\TransactionPaymentController::class, 'store']), [
            'transaction_id' => $purchaseReturn->id,
            'amount' => '13,975,000',
            'method' => 'bank_transfer',
            'account_id' => $this->bankAccount->id,
            'paid_on' => date('m/d/Y H:i'),
        ]);

        $response->assertJson([
            'success' => true,
        ]);

        // 3. Verify that AccountTransaction created is DEBIT (money coming into bank)
        $accountTx = AccountTransaction::where('transaction_id', $purchaseReturn->id)->first();
        $this->assertNotNull($accountTx);
        $this->assertEquals('debit', $accountTx->type);
        $this->assertEquals(13975000, $accountTx->amount);
        $this->assertEquals($this->bankAccount->id, $accountTx->account_id);
    }

    public function test_migration_fixes_existing_credit_purchase_return_account_transactions()
    {
        // Create a purchase return transaction
        $purchaseReturn = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'purchase_return',
            'status' => 'final',
            'payment_status' => 'paid',
            'contact_id' => $this->supplier->id,
            'transaction_date' => now(),
            'total_before_tax' => 5000000,
            'final_total' => 5000000,
            'created_by' => $this->user->id,
        ]);

        $payment = TransactionPayment::create([
            'transaction_id' => $purchaseReturn->id,
            'business_id' => $this->business->id,
            'amount' => 5000000,
            'method' => 'cash',
            'account_id' => $this->bankAccount->id,
            'paid_on' => now(),
            'created_by' => $this->user->id,
        ]);

        // Manually insert an old incorrect 'credit' account transaction
        $accountTx = AccountTransaction::create([
            'amount' => 5000000,
            'account_id' => $this->bankAccount->id,
            'type' => 'credit', // Incorrect status before fix
            'operation_date' => now(),
            'created_by' => $this->user->id,
            'transaction_id' => $purchaseReturn->id,
            'transaction_payment_id' => $payment->id,
        ]);

        $this->assertEquals('credit', $accountTx->type);

        // Run the migration logic
        $migration = new \FixPurchaseReturnAccountTransactionsToDebit();
        $migration->up();

        $accountTx->refresh();
        $this->assertEquals('debit', $accountTx->type);
    }
}
