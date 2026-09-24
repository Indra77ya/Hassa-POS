<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessIntercompanyLink extends Model
{
    protected $table = 'business_intercompany_links';

    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    public function linkedBusiness()
    {
        return $this->belongsTo(\App\Business::class, 'linked_business_id');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function linkedContact()
    {
        return $this->belongsTo(\App\Contact::class, 'linked_contact_id');
    }
}
