<?php

namespace Modules\Laundry\Entities;

use Illuminate\Database\Eloquent\Model;

class LaundryItemType extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'process_ids' => 'array',
    ];

    public static function forDropdown($business_id)
    {
        return static::where('business_id', $business_id)
            ->where('is_active', true)
            ->pluck('name', 'id');
    }
}
