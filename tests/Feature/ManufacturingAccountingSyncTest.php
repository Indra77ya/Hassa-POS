<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\Account;
use App\AccountType;
use App\Product;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionPayment;
use App\AccountTransaction;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Manufacturing\Entities\MfgRecipe;
use Tests\TestCase;

class ManufacturingAccountingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $business;
    protected $user;
    protected $location;
    protected $rawAccount;
    protected $finishedAccount;
    protected $costAccount;
    protected $paymentAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Business and User
        $this->business = Business::factory()->create([
            'manufacturing_settings' => json_encode([]),
        ]);

        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $this->actingAs($this->user);

        $this->location = BusinessLocation::create([
            'business_id' => $this->business->id,
            'name' => 'Main Location',
        ]);

        // 2. Seed Account Types and POS Payment Account
        $type_persediaan = AccountType::create([
            'name' => 'Persediaan',
            'business_id' => $this->business->id,
            'fixed_key' => 'persediaan'
        ]);

        $type_kas = AccountType::create([
            'name' => 'Kas & Bank',
            'business_id' => $this->business->id,
            'fixed_key' => 'kas_dan_bank'
        ]);

        $this->paymentAccount = Account::create([
            'name' => 'Kas Utamanya',
            'business_id' => $this->business->id,
            'account_number' => '1101',
            'account_type_id' => $type_kas->id,
            'normal_balance' => 'debit',
            'created_by' => $this->user->id,
        ]);

        // 3. Seed Accounting Module Accounts
        $this->rawAccount = AccountingAccount::create([
            'name' => 'Persediaan Bahan Baku',
            'business_id' => $this->business->id,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 2,
            'detail_type_id' => 21,
            'gl_code' => '1302',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->finishedAccount = AccountingAccount::create([
            'name' => 'Persediaan Barang Jadi',
            'business_id' => $this->business->id,
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 2,
            'detail_type_id' => 21,
            'gl_code' => '1303',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->costAccount = AccountingAccount::create([
            'name' => 'Biaya Produksi / Overhead',
            'business_id' => $this->business->id,
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
            'detail_type_id' => 138,
            'gl_code' => '6105',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        // 4. Save settings
        $settings = [
            'mfg_raw_material_account_id' => $this->rawAccount->id,
            'mfg_finished_goods_account_id' => $this->finishedAccount->id,
            'mfg_production_cost_account_id' => $this->costAccount->id,
            'mfg_payment_account_id' => $this->paymentAccount->id,
        ];
        $this->business->update(['manufacturing_settings' => json_encode($settings)]);
    }

    /** @test */
    public function it_creates_double_entry_journal_and_payment_line_when_production_is_finalized()
    {
        // 1. Create Production Purchase (Finalized)
        $production_purchase = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'production_purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'ref_no' => 'MFG-TEST-001',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'final_total' => 150000,
            'mfg_production_cost' => 20000,
            'mfg_production_cost_type' => 'fixed',
            'mfg_is_final' => 1,
            'created_by' => $this->user->id,
        ]);

        $production_sell = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'production_sell',
            'status' => 'final',
            'mfg_parent_production_purchase_id' => $production_purchase->id,
            'final_total' => 150000,
            'created_by' => $this->user->id,
        ]);

        $mfgUtil = new \Modules\Manufacturing\Utils\ManufacturingUtil();
        $mfgUtil->syncAccountingJournal($production_purchase, $production_sell);

        // 2. Verify Payment Line Created
        $payment = TransactionPayment::where('transaction_id', $production_purchase->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(20000, $payment->amount);
        $this->assertEquals($this->paymentAccount->id, $payment->account_id);

        $accTrans = AccountTransaction::where('transaction_payment_id', $payment->id)->first();
        $this->assertNotNull($accTrans);

        // 3. Verify Journal Entry
        $mapping = AccountingAccTransMapping::where('business_id', $this->business->id)
            ->where('ref_no', 'MFG-' . $production_purchase->id)
            ->first();
        $this->assertNotNull($mapping);

        // Debit Finished Goods = 150,000
        $debitFinished = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)
            ->where('accounting_account_id', $this->finishedAccount->id)
            ->where('type', 'debit')
            ->first();
        $this->assertNotNull($debitFinished);
        $this->assertEquals(150000, $debitFinished->amount);

        // Credit Raw Materials = 130,000 (150,000 - 20,000)
        $creditRaw = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)
            ->where('accounting_account_id', $this->rawAccount->id)
            ->where('type', 'credit')
            ->first();
        $this->assertNotNull($creditRaw);
        $this->assertEquals(130000, $creditRaw->amount);

        // Credit Kas = 20,000
        $creditKas = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)
            ->where('type', 'credit')
            ->where('accounting_account_id', '!=', $this->rawAccount->id)
            ->first();
        $this->assertNotNull($creditKas);
        $this->assertEquals(20000, $creditKas->amount);
    }

    /** @test */
    public function it_deletes_journal_and_payment_line_on_production_delete()
    {
        $production_purchase = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'production_purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'ref_no' => 'MFG-TEST-002',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'final_total' => 100000,
            'mfg_production_cost' => 10000,
            'mfg_production_cost_type' => 'fixed',
            'mfg_is_final' => 1,
            'created_by' => $this->user->id,
        ]);

        $production_sell = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'production_sell',
            'status' => 'final',
            'mfg_parent_production_purchase_id' => $production_purchase->id,
            'final_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        $mfgUtil = new \Modules\Manufacturing\Utils\ManufacturingUtil();
        $mfgUtil->syncAccountingJournal($production_purchase, $production_sell);

        // Delete Journal & Payment
        $mfgUtil->deleteAccountingJournal($production_purchase);

        $this->assertDatabaseMissing('transaction_payments', ['transaction_id' => $production_purchase->id]);
        $this->assertDatabaseMissing('accounting_acc_trans_mappings', ['ref_no' => 'MFG-' . $production_purchase->id]);
    }
}
