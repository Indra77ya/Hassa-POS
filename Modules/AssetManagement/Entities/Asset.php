<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'business_id',
        'name',
        'asset_category_id',
        'sku',
        'historical_cost',
        'salvage_value',
        'purchase_date',
        'useful_life_months',
        'depreciation_method',
        'is_active',
        'is_disposed',
        'disposal_date',
        'disposal_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_disposed' => 'boolean',
        'historical_cost' => 'float',
        'salvage_value' => 'float',
        'useful_life_months' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function depreciationLogs()
    {
        return $this->hasMany(AssetDepreciationLog::class, 'asset_id');
    }

    /**
     * Get total accumulated depreciation so far
     */
    public function getAccumulatedDepreciationAttribute()
    {
        return (float) $this->depreciationLogs()->sum('amount');
    }

    /**
     * Get maximum total depreciable amount (Historical Cost - Salvage Value)
     */
    public function getMaxDepreciableAmountAttribute()
    {
        return max(0, $this->historical_cost - $this->salvage_value);
    }

    /**
     * Get Net Book Value (Historical Cost - Accumulated Depreciation)
     */
    public function getNetBookValueAttribute()
    {
        return max($this->salvage_value, $this->historical_cost - $this->accumulated_depreciation);
    }

    /**
     * Calculate monthly depreciation amount using straight-line algorithm
     */
    public function getMonthlyDepreciationAmountAttribute()
    {
        if ($this->useful_life_months <= 0) {
            return 0;
        }

        $depreciable_total = $this->max_depreciable_amount;
        return round($depreciable_total / $this->useful_life_months, 2);
    }
}
