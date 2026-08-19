<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciationLog extends Model
{
    protected $table = 'asset_depreciation_logs';
    protected $guarded = ['id'];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
