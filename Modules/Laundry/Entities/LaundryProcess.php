<?php

namespace Modules\Laundry\Entities;

use Illuminate\Database\Eloquent\Model;

class LaundryProcess extends Model
{
    protected $guarded = ['id'];

    public static function forDropdown($business_id)
    {
        return static::where('business_id', $business_id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->pluck('name', 'id');
    }
}
