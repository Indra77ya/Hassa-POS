<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperadminCoupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
}
