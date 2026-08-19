<?php

namespace Modules\AssetManagement\Console;

use Illuminate\Console\Command;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetCategory;
use Modules\AssetManagement\Entities\AssetDepreciationLog;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Carbon\Carbon;
use DB;

class RunMonthlyDepreciationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:run-depreciation {--business_id= : Run depreciation for a specific business}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates monthly depreciation for active fixed assets and posts non-cash journal entries.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $specific_business_id = $this->option('business_id');

        $query = Asset::where('is_active', true)
            ->where('is_disposed', false);

        if (!empty($specific_business_id)) {
            $query->where('business_id', $specific_business_id);
        }

        $assets = $query->get();

        $depreciation_date = Carbon::now()->endOfMonth()->format('Y-m-d');
        $processed_count = 0;

        foreach ($assets as $asset) {
            // Check if already depreciated for this month
            $existing_log = AssetDepreciationLog::where('asset_id', $asset->id)
                ->whereYear('depreciation_date', Carbon::parse($depreciation_date)->year)
                ->whereMonth('depreciation_date', Carbon::parse($depreciation_date)->month)
                ->first();

            if ($existing_log) {
                continue;
            }

            // Check depreciation limits
            $accumulated = $asset->accumulated_depreciation;
            $max_depreciable = $asset->max_depreciable_amount;

            if ($accumulated >= $max_depreciable) {
                continue;
            }

            // Calculate monthly amount using Straight-Line algorithm
            $monthly_amount = $asset->monthly_depreciation_amount;

            if ($monthly_amount <= 0) {
                continue;
            }

            // Cap amount so accumulated doesn't exceed max_depreciable
            if (($accumulated + $monthly_amount) > $max_depreciable) {
                $monthly_amount = $max_depreciable - $accumulated;
            }

            if ($monthly_amount <= 0) {
                continue;
            }

            DB::beginTransaction();
            try {
                // Determine Debit Account (Beban Penyusutan)
                $debit_account_id = null;
                $credit_account_id = null;

                if ($asset->category) {
                    $debit_account_id = $asset->category->depreciation_expense_account_id;
                    $credit_account_id = $asset->category->accumulated_depreciation_account_id;
                }

                // Fallback to default business accounts if category accounts are missing
                if (empty($debit_account_id)) {
                    $debit_acc = AccountingAccount::where('business_id', $asset->business_id)
                        ->whereIn('account_primary_type', ['expense', 'expenses'])
                        ->where('name', 'like', '%Beban Penyusutan%')
                        ->first();
                    $debit_account_id = $debit_acc->id ?? null;
                }

                if (empty($credit_account_id)) {
                    $credit_acc = AccountingAccount::where('business_id', $asset->business_id)
                        ->where('account_primary_type', 'asset')
                        ->where('name', 'like', '%Akumulasi Penyusutan%')
                        ->first();
                    $credit_account_id = $credit_acc->id ?? null;
                }

                if (empty($debit_account_id) || empty($credit_account_id)) {
                    $this->error("Asset ID {$asset->id} ('{$asset->name}'): Missing depreciation expense or accumulated depreciation account.");
                    DB::rollBack();
                    continue;
                }

                // Verify accounts are non-cash (cannot be kas_dan_bank / sub_type_id 3)
                $debit_acc_obj = AccountingAccount::find($debit_account_id);
                $credit_acc_obj = AccountingAccount::find($credit_account_id);

                if ($debit_acc_obj->account_sub_type_id == 3 || $credit_acc_obj->account_sub_type_id == 3) {
                    $this->error("Asset ID {$asset->id}: Journal entry restricted. Depreciation accounts cannot be Cash or Bank accounts.");
                    DB::rollBack();
                    continue;
                }

                // Create Journal Entry
                $mapping = AccountingAccTransMapping::create([
                    'business_id' => $asset->business_id,
                    'ref_no' => 'DEP-' . $asset->id . '-' . Carbon::parse($depreciation_date)->format('Ym'),
                    'operation_date' => $depreciation_date,
                    'type' => 'depreciation',
                    'created_by' => $asset->created_by ?? 1,
                    'note' => 'Depresiasi Bulanan Aset Tetap: ' . $asset->name,
                ]);

                // Debit entry
                $debit_tx = AccountingAccountsTransaction::create([
                    'accounting_account_id' => $debit_account_id,
                    'amount' => $monthly_amount,
                    'type' => 'debit',
                    'sub_type' => 'journal_entry',
                    'operation_date' => $depreciation_date,
                    'created_by' => $asset->created_by ?? 1,
                    'note' => 'Beban Penyusutan Aset Tetap: ' . $asset->name,
                    'acc_trans_mapping_id' => $mapping->id,
                ]);

                // Credit entry
                $credit_tx = AccountingAccountsTransaction::create([
                    'accounting_account_id' => $credit_account_id,
                    'amount' => $monthly_amount,
                    'type' => 'credit',
                    'sub_type' => 'journal_entry',
                    'operation_date' => $depreciation_date,
                    'created_by' => $asset->created_by ?? 1,
                    'note' => 'Akumulasi Penyusutan Aset Tetap: ' . $asset->name,
                    'acc_trans_mapping_id' => $mapping->id,
                ]);

                // Create depreciation log record
                AssetDepreciationLog::create([
                    'business_id' => $asset->business_id,
                    'asset_id' => $asset->id,
                    'depreciation_date' => $depreciation_date,
                    'amount' => $monthly_amount,
                    'accounting_accounts_transaction_debit_id' => $debit_tx->id,
                    'accounting_accounts_transaction_credit_id' => $credit_tx->id,
                    'created_by' => $asset->created_by ?? 1,
                ]);

                DB::commit();
                $processed_count++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error processing asset ID {$asset->id}: " . $e->getMessage());
            }
        }

        $this->info("Monthly depreciation run completed. Processed {$processed_count} assets.");
        return 0;
    }
}
