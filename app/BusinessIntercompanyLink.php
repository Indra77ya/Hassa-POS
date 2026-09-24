<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessIntercompanyLink extends Model
{
    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function target_business()
    {
        return $this->belongsTo(\App\Business::class, 'target_business_id');
    }
}
