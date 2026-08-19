<?php

namespace Modules\AssetManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $table = 'asset_categories';
    protected $guarded = ['id'];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_category_id');
    }
}
