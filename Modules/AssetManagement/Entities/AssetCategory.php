<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $table = 'asset_categories';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'depreciation_expense_account_id',
        'accumulated_depreciation_account_id',
        'created_by',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_category_id');
    }

    public function depreciationExpenseAccount()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccount::class, 'depreciation_expense_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccount::class, 'accumulated_depreciation_account_id');
    }
}
