<?php

namespace Modules\AssetManagement\Console;

use App\Business;
use App\ReferenceCount;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetDepreciationLog;
use Modules\AssetManagement\Entities\AssetSetting;

class RunDepreciationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:run-depreciation {--date= : The date to run depreciation for (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate monthly asset depreciation and post manual journal entries.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dateStr = $this->option('date') ?: Carbon::now()->format('Y-m-d');
        $currentDate = Carbon::parse($dateStr);
        $year = $currentDate->year;
        $month = $currentDate->month;
        $depreciationDate = $currentDate->format('Y-m-d');

        $util = new Util();
        $businesses = Business::all();

        $this->info("Starting monthly asset depreciation calculation for {$year}-" . sprintf('%02d', $month) . "...");

        foreach ($businesses as $business) {
            $setting = AssetSetting::forBusiness($business->id);

            $expenseAccountId = $setting->depreciation_expense_account_id;
            $accumAccountId = $setting->accumulated_depreciation_account_id;

            if (!$expenseAccountId || !$accumAccountId) {
                $this->warn("Skipping Business ID {$business->id}: Depreciation accounts not properly configured.");
                continue;
            }

            // Fetch active assets for this business
            $assets = Asset::where('business_id', $business->id)
                ->where('status', 'active')
                ->where('purchase_date', '<=', $depreciationDate)
                ->get();

            foreach ($assets as $asset) {
                // Check if already processed for this asset in this year and month (Locking mechanism)
                $alreadyLogged = AssetDepreciationLog::where('asset_id', $asset->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->exists();

                if ($alreadyLogged) {
                    $this->line("Asset '{$asset->name}' (ID {$asset->id}) already depreciated for {$year}-{$month}. Skipping.");
                    continue;
                }

                $monthlyAmount = $asset->monthly_depreciation;

                if ($monthlyAmount <= 0) {
                    continue;
                }

                // Ensure total accumulated depreciation does not exceed depreciable base (purchase_price - salvage_value)
                $maxDepreciable = max(0, $asset->purchase_price - $asset->salvage_value);
                $currentAccumulated = $asset->total_accumulated_depreciation;

                if ($currentAccumulated >= $maxDepreciable) {
                    $this->line("Asset '{$asset->name}' (ID {$asset->id}) is fully depreciated. Skipping.");
                    continue;
                }

                // Cap the monthly amount to remaining depreciable balance if needed
                if ($currentAccumulated + $monthlyAmount > $maxDepreciable) {
                    $monthlyAmount = round($maxDepreciable - $currentAccumulated, 4);
                }

                if ($monthlyAmount <= 0) {
                    continue;
                }

                try {
                    DB::beginTransaction();

                    // Generate Reference Number
                    $refNo = 'DEPR-' . $business->id . '-' . $asset->id . '-' . $year . sprintf('%02d', $month);

                    // Create AccTransMapping entry for journal entry
                    $accTransMapping = new AccountingAccTransMapping();
                    $accTransMapping->business_id = $business->id;
                    $accTransMapping->ref_no = $refNo;
                    $accTransMapping->note = "Penyusutan Bulanan Aset: {$asset->name} ({$asset->asset_code}) Periode {$year}-" . sprintf('%02d', $month);
                    $accTransMapping->type = 'journal_entry';
                    $accTransMapping->created_by = 1;
                    $accTransMapping->operation_date = $depreciationDate;
                    $accTransMapping->save();

                    // Debit: Beban Penyusutan
                    $debitTrans = new AccountingAccountsTransaction();
                    $debitTrans->accounting_account_id = $expenseAccountId;
                    $debitTrans->amount = $monthlyAmount;
                    $debitTrans->type = 'debit';
                    $debitTrans->created_by = 1;
                    $debitTrans->operation_date = $depreciationDate;
                    $debitTrans->sub_type = 'journal_entry';
                    $debitTrans->acc_trans_mapping_id = $accTransMapping->id;
                    $debitTrans->save();

                    // Credit: Akumulasi Penyusutan
                    $creditTrans = new AccountingAccountsTransaction();
                    $creditTrans->accounting_account_id = $accumAccountId;
                    $creditTrans->amount = $monthlyAmount;
                    $creditTrans->type = 'credit';
                    $creditTrans->created_by = 1;
                    $creditTrans->operation_date = $depreciationDate;
                    $creditTrans->sub_type = 'journal_entry';
                    $creditTrans->acc_trans_mapping_id = $accTransMapping->id;
                    $creditTrans->save();

                    // Create log entry in asset_depreciation_logs
                    AssetDepreciationLog::create([
                        'business_id' => $business->id,
                        'asset_id' => $asset->id,
                        'depreciation_date' => $depreciationDate,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $monthlyAmount,
                        'accounting_acc_trans_mapping_id' => $accTransMapping->id,
                    ]);

                    DB::commit();

                    $this->info("Depreciated Asset '{$asset->name}' (ID {$asset->id}) by {$monthlyAmount} for {$year}-" . sprintf('%02d', $month));
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed to depreciate Asset ID {$asset->id}: " . $e->getMessage());
                    \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
                }
            }
        }

        $this->info("Monthly asset depreciation calculation completed.");
    }
}
