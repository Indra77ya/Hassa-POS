<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'assets';
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function depreciationLogs()
    {
        return $this->hasMany(AssetDepreciationLog::class, 'asset_id');
    }

    /**
     * Calculate monthly depreciation amount
     */
    public function getMonthlyDepreciationAttribute()
    {
        if ($this->useful_life <= 0) {
            return 0;
        }

        $depreciable_amount = max(0, $this->purchase_price - $this->salvage_value);
        return round($depreciable_amount / $this->useful_life, 4);
    }

    /**
     * Calculate total accumulated depreciation to date
     */
    public function getTotalAccumulatedDepreciationAttribute()
    {
        return $this->depreciationLogs()->sum('amount');
    }

    /**
     * Calculate current net book value
     */
    public function getNetBookValueAttribute()
    {
        return max(0, $this->purchase_price - $this->total_accumulated_depreciation);
    }
}
