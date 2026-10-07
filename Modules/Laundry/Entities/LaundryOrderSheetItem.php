<?php

namespace Modules\Laundry\Entities;

use Illuminate\Database\Eloquent\Model;

class LaundryOrderSheetItem extends Model
{
    protected $guarded = ['id'];

    public function orderSheet()
    {
        return $this->belongsTo(LaundryOrderSheet::class, 'laundry_order_sheet_id');
    }

    public function itemType()
    {
        return $this->belongsTo(LaundryItemType::class, 'laundry_item_type_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(LaundryServiceType::class, 'laundry_service_type_id');
    }

    public function status()
    {
        return $this->belongsTo(LaundryStatus::class, 'laundry_status_id');
    }

    public function processLogs()
    {
        return $this->hasMany(LaundryOrderProcessLog::class, 'laundry_order_sheet_item_id');
    }
}
