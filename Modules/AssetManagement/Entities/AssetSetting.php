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
                    'gl_code' => '6105',
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 14, // Operating Expense / Beban Operasional
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                ]);
            } else {
                $needsSave = false;
                if (empty($expenseAcc->account_primary_type)) {
                    $expenseAcc->account_primary_type = 'expenses';
                    $needsSave = true;
                }
                if (empty($expenseAcc->account_sub_type_id)) {
                    $expenseAcc->account_sub_type_id = 14;
                    $needsSave = true;
                }
                if (empty($expenseAcc->gl_code)) {
                    $expenseAcc->gl_code = '6105';
                    $needsSave = true;
                }
                if (empty($expenseAcc->account_id) || (class_exists(\App\Account::class) && !\App\Account::find($expenseAcc->account_id))) {
                    $needsSave = true;
                }
                if ($needsSave) {
                    $expenseAcc->save();
                }
            }

            $setting->depreciation_expense_account_id = $expenseAcc->id;
        } else {
            $expenseAcc = AccountingAccount::find($setting->depreciation_expense_account_id);
            if ($expenseAcc) {
                $needsSave = false;
                if (empty($expenseAcc->account_primary_type)) {
                    $expenseAcc->account_primary_type = 'expenses';
                    $needsSave = true;
                }
                if (empty($expenseAcc->account_sub_type_id)) {
                    $expenseAcc->account_sub_type_id = 14;
                    $needsSave = true;
                }
                if (empty($expenseAcc->gl_code)) {
                    $expenseAcc->gl_code = '6105';
                    $needsSave = true;
                }
                if (empty($expenseAcc->account_id) || (class_exists(\App\Account::class) && !\App\Account::find($expenseAcc->account_id))) {
                    $needsSave = true;
                }
                if ($needsSave) {
                    $expenseAcc->save();
                }
            }
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
                    'gl_code' => '1701',
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 17, // Akumulasi Penyusutan (Contra Asset)
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                ]);
            } else {
                $needsSave = false;
                if (empty($accumAcc->account_primary_type)) {
                    $accumAcc->account_primary_type = 'asset';
                    $needsSave = true;
                }
                if (empty($accumAcc->account_sub_type_id)) {
                    $accumAcc->account_sub_type_id = 17;
                    $needsSave = true;
                }
                if (empty($accumAcc->gl_code)) {
                    $accumAcc->gl_code = '1701';
                    $needsSave = true;
                }
                if (empty($accumAcc->account_id) || (class_exists(\App\Account::class) && !\App\Account::find($accumAcc->account_id))) {
                    $needsSave = true;
                }
                if ($needsSave) {
                    $accumAcc->save();
                }
            }

            $setting->accumulated_depreciation_account_id = $accumAcc->id;
        } else {
            $accumAcc = AccountingAccount::find($setting->accumulated_depreciation_account_id);
            if ($accumAcc) {
                $needsSave = false;
                if (empty($accumAcc->account_primary_type)) {
                    $accumAcc->account_primary_type = 'asset';
                    $needsSave = true;
                }
                if (empty($accumAcc->account_sub_type_id)) {
                    $accumAcc->account_sub_type_id = 17;
                    $needsSave = true;
                }
                if (empty($accumAcc->gl_code)) {
                    $accumAcc->gl_code = '1701';
                    $needsSave = true;
                }
                if (empty($accumAcc->account_id) || (class_exists(\App\Account::class) && !\App\Account::find($accumAcc->account_id))) {
                    $needsSave = true;
                }
                if ($needsSave) {
                    $accumAcc->save();
                }
            }
        }

        $setting->save();

        return $setting;
    }
}
