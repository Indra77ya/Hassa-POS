<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductSerialNumber extends Model
{
    protected $table = 'product_serial_numbers';

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function purchase_line()
    {
        return $this->belongsTo(\App\PurchaseLine::class, 'purchase_line_id');
    }

    public function sell_line()
    {
        return $this->belongsTo(\App\TransactionSellLine::class, 'transaction_sell_line_id');
    }

    public function job_sheet()
    {
        return $this->belongsTo(\Modules\Repair\Entities\JobSheet::class, 'repair_job_sheet_id');
    }
}
