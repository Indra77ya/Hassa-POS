<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Manufacturing\Utils\ManufacturingUtil;
use Tests\TestCase;

class ManufacturingAccountingSyncTest extends TestCase
{
    use DatabaseMigrations;

    public function test_manufacturing_syncs_accounting_journal_when_finalized()
    {
        $business = Business::firstOrCreate(
            ['name' => 'Manufacturing Test Business'],
            [
                'currency_id' => 1,
                'start_date' => '2026-01-01',
                'time_zone' => 'Asia/Jakarta',
            ]
        );

        $user = User::factory()->create(['business_id' => $business->id]);
        $location = BusinessLocation::create([
            'business_id' => $business->id,
            'name' => 'Main Location',
        ]);

        $raw_mat_account = AccountingAccount::create([
            'business_id' => $business->id,
            'name' => 'Persediaan Bahan Baku Test',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 2, // persediaan
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $finished_goods_account = AccountingAccount::create([
            'business_id' => $business->id,
            'name' => 'Persediaan Barang Jadi Test',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 2, // persediaan
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $overhead_account = AccountingAccount::create([
            'business_id' => $business->id,
            'name' => 'Beban Overhead Produksi Test',
            'account_primary_type' => 'expenses',
            'account_sub_type_id' => 14, // beban_operasional
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        // Save manufacturing settings
        $settings = [
            'mfg_raw_material_account_id' => $raw_mat_account->id,
            'mfg_finished_goods_account_id' => $finished_goods_account->id,
            'mfg_production_cost_account_id' => $overhead_account->id,
        ];

        $business->manufacturing_settings = json_encode($settings);
        $business->save();

        // Create dummy production transaction
        $transaction = \App\Transaction::create([
            'business_id' => $business->id,
            'location_id' => $location->id,
            'type' => 'production_purchase',
            'status' => 'received',
            'payment_status' => 'due',
            'ref_no' => 'PRD-TEST-001',
            'transaction_date' => '2026-08-30 10:00:00',
            'total_before_tax' => 150000,
            'final_total' => 150000,
            'mfg_is_final' => 1,
            'mfg_production_cost' => 25000,
            'mfg_production_cost_type' => 'fixed',
            'created_by' => $user->id,
        ]);

        $mfgUtil = new ManufacturingUtil();
        $mfgUtil->syncAccountingJournal($transaction);

        $mapping = AccountingAccTransMapping::where('business_id', $business->id)
            ->where('ref_no', 'MFG-PRD-TEST-001')
            ->first();

        $this->assertNotNull($mapping);

        $debit_fg = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)
            ->where('accounting_account_id', $finished_goods_account->id)
            ->where('type', 'debit')
            ->first();

        $this->assertNotNull($debit_fg);
        $this->assertEquals(150000, $debit_fg->amount);

        // Delete journal
        $mfgUtil->deleteAccountingJournal($transaction);
        $mapping_deleted = AccountingAccTransMapping::where('business_id', $business->id)
            ->where('ref_no', 'MFG-PRD-TEST-001')
            ->first();

        $this->assertNull($mapping_deleted);
    }
}
