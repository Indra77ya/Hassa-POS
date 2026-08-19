<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\AccountingAccount;

class AssetSetting extends Model
{
    protected $table = 'asset_settings';
    protected $guarded = ['id'];

    public function depreciationExpenseAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'depreciation_expense_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'accumulated_depreciation_account_id');
    }

    /**
     * Get or create default asset setting for a business and auto-seed accounts if missing
     */
    public static function forBusiness($business_id)
    {
        $setting = self::where('business_id', $business_id)->first();

        if (!$setting) {
            $setting = self::create([
                'business_id' => $business_id,
            ]);
        }

        // Auto-seed or lookup depreciation expense account ('Beban Penyusutan')
        if (empty($setting->depreciation_expense_account_id)) {
            $expenseAcc = AccountingAccount::where('business_id', $business_id)
                ->where(function ($q) {
                    $q->where('name', 'Beban Penyusutan')
                        ->orWhere('name', 'Biaya Penyusutan');
                })
                ->first();

            if (!$expenseAcc) {
                $expenseAcc = AccountingAccount::create([
                    'business_id' => $business_id,
                    'name' => 'Beban Penyusutan',
                    'account_number' => '6105',
                    'account_sub_type_id' => 14, // Operating Expense / Beban Operasional
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                ]);
            }

            $setting->depreciation_expense_account_id = $expenseAcc->id;
        }

        // Auto-seed or lookup accumulated depreciation account ('Akumulasi Penyusutan')
        if (empty($setting->accumulated_depreciation_account_id)) {
            $accumAcc = AccountingAccount::where('business_id', $business_id)
                ->where('name', 'Akumulasi Penyusutan')
                ->first();

            if (!$accumAcc) {
                $accumAcc = AccountingAccount::create([
                    'business_id' => $business_id,
                    'name' => 'Akumulasi Penyusutan',
                    'account_number' => '1701',
                    'account_sub_type_id' => 16, // Akumulasi Penyusutan (Contra Asset)
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                ]);
            }

            $setting->accumulated_depreciation_account_id = $accumAcc->id;
        }

        $setting->save();

        return $setting;
    }
}
