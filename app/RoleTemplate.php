<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
