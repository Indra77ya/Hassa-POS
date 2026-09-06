<?php

namespace Modules\Laundry\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Contact;
use App\BusinessLocation;
use App\User;

class LaundryOrderSheet extends Model
{
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    public function status()
    {
        return $this->belongsTo(LaundryStatus::class, 'laundry_status_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(LaundryServiceType::class, 'laundry_service_type_id');
    }

    public function itemType()
    {
        return $this->belongsTo(LaundryItemType::class, 'laundry_item_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processLogs()
    {
        return $this->hasMany(LaundryOrderProcessLog::class, 'order_sheet_id');
    }
}
