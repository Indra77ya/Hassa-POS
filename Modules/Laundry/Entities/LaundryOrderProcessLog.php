<?php

namespace Modules\Laundry\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class LaundryOrderProcessLog extends Model
{
    protected $guarded = ['id'];

    public function orderSheet()
    {
        return $this->belongsTo(LaundryOrderSheet::class, 'order_sheet_id');
    }

    public function process()
    {
        return $this->belongsTo(LaundryProcess::class, 'laundry_process_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
