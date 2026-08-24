<?php

namespace Tests\Feature;

use App\Account;
use App\AccountTransaction;
use App\Business;
use App\BusinessLocation;
use App\Product;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Manufacturing\Entities\MfgRecipe;
use Modules\Manufacturing\Entities\MfgRecipeIngredient;
use Modules\Manufacturing\Utils\ManufacturingUtil;
use Tests\TestCase;

class ManufacturingAccountingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $business;

    protected $user;

    protected $location;

    protected $rawMatAccount;

    protected $finishedGoodsAccount;

    protected $prodCostAccount;

    protected $paymentAccount;

    protected $rawMatProduct;

    protected $rawMatVariation;

    protected $finishedProduct;

    protected $finishedVariation;

    protected $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Business & User
        $this->business = Business::factory()->create([
            'manufacturing_settings' => json_encode([]),
        ]);

        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $this->actingAs($this->user);

        $this->location = BusinessLocation::factory()->create([
            'business_id' => $this->business->id,
        ]);

        // Accounting Accounts
        $this->rawMatAccount = AccountingAccount::create([
            'name' => 'Persediaan Bahan Baku Test',
            'gl_code' => '1130-TEST',
            'business_id' => $this->business->id,
            'status' => 'active',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 1,
        ]);

        $this->finishedGoodsAccount = AccountingAccount::create([
            'name' => 'Persediaan Barang Jadi Test',
            'gl_code' => '1140-TEST',
            'business_id' => $this->business->id,
            'status' => 'active',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 1,
        ]);

        $this->prodCostAccount = AccountingAccount::create([
            'name' => 'Biaya Produksi Overhead Test',
            'gl_code' => '5100-TEST',
            'business_id' => $this->business->id,
            'status' => 'active',
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14,
        ]);

        $this->paymentAccount = Account::create([
            'name' => 'Kas Operasional Produksi',
            'business_id' => $this->business->id,
            'created_by' => $this->user->id,
        ]);

        // Set manufacturing settings
        $settings = [
            'mfg_raw_material_account_id' => $this->rawMatAccount->id,
            'mfg_finished_goods_account_id' => $this->finishedGoodsAccount->id,
            'mfg_production_cost_account_id' => $this->prodCostAccount->id,
            'mfg_payment_account_id' => $this->paymentAccount->id,
        ];

        $this->business->manufacturing_settings = json_encode($settings);
        $this->business->save();
    }

    public function test_finalized_production_creates_accounting_journal_and_payment_account_transactions()
    {
        $mfgUtil = app(ManufacturingUtil::class);

        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'production_purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'ref_no' => 'PROD-TEST-001',
            'transaction_date' => now()->toDateTimeString(),
            'final_total' => 150000,
            'mfg_production_cost' => 50000,
            'mfg_production_cost_type' => 'fixed',
            'mfg_is_final' => 1,
            'created_by' => $this->user->id,
        ]);

        $mfgUtil->syncAccountingJournal($transaction);

        // Verify Double-Entry Journal Mapping created
        $accTransMapping = AccountingAccTransMapping::where('business_id', $this->business->id)
            ->where('ref_no', 'MFG-JOURNAL-PROD-TEST-001')
            ->first();

        $this->assertNotNull($accTransMapping);

        // Verify Finished Goods Debit Entry (150,000)
        $debitEntry = AccountingAccountsTransaction::where('acc_trans_mapping_id', $accTransMapping->id)
            ->where('accounting_account_id', $this->finishedGoodsAccount->id)
            ->where('type', 'debit')
            ->first();

        $this->assertNotNull($debitEntry);
        $this->assertEquals(150000, $debitEntry->amount);

        // Verify Raw Materials Credit Entry (150,000 - 50,000 = 100,000)
        $creditRawMat = AccountingAccountsTransaction::where('acc_trans_mapping_id', $accTransMapping->id)
            ->where('accounting_account_id', $this->rawMatAccount->id)
            ->where('type', 'credit')
            ->first();

        $this->assertNotNull($creditRawMat);
        $this->assertEquals(100000, $creditRawMat->amount);

        // Verify Production Cost Credit Entry (50,000)
        $creditCost = AccountingAccountsTransaction::where('acc_trans_mapping_id', $accTransMapping->id)
            ->where('accounting_account_id', $this->prodCostAccount->id)
            ->where('type', 'credit')
            ->first();

        $this->assertNotNull($creditCost);
        $this->assertEquals(50000, $creditCost->amount);

        // Verify Transaction Payment Created for Payment Account (50,000)
        $payment = TransactionPayment::where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(50000, $payment->amount);
        $this->assertEquals($this->paymentAccount->id, $payment->account_id);

        // Verify Account Transaction for Payment Account Created (50,000 credit)
        $accountTrans = AccountTransaction::where('transaction_payment_id', $payment->id)->first();
        $this->assertNotNull($accountTrans);
        $this->assertEquals(50000, $accountTrans->amount);
        $this->assertEquals('credit', $accountTrans->type);
    }

    public function test_deleting_or_unfinalizing_production_removes_journal_and_payment_transactions()
    {
        $mfgUtil = app(ManufacturingUtil::class);

        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'type' => 'production_purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'ref_no' => 'PROD-TEST-002',
            'transaction_date' => now()->toDateTimeString(),
            'final_total' => 200000,
            'mfg_production_cost' => 40000,
            'mfg_production_cost_type' => 'fixed',
            'mfg_is_final' => 1,
            'created_by' => $this->user->id,
        ]);

        $mfgUtil->syncAccountingJournal($transaction);

        $this->assertDatabaseHas('accounting_acc_trans_mappings', ['ref_no' => 'MFG-JOURNAL-PROD-TEST-002']);
        $this->assertDatabaseHas('transaction_payments', ['transaction_id' => $transaction->id]);

        // Delete Journal
        $mfgUtil->deleteAccountingJournal($transaction);

        $this->assertDatabaseMissing('accounting_acc_trans_mappings', ['ref_no' => 'MFG-JOURNAL-PROD-TEST-002']);
        $this->assertDatabaseMissing('transaction_payments', ['transaction_id' => $transaction->id]);
    }
}
