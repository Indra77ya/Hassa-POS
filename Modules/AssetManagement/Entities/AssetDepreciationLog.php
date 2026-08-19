<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciationLog extends Model
{
    protected $table = 'asset_depreciation_logs';

    protected $fillable = [
        'business_id',
        'asset_id',
        'depreciation_date',
        'amount',
        'accounting_accounts_transaction_debit_id',
        'accounting_accounts_transaction_credit_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'depreciation_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
