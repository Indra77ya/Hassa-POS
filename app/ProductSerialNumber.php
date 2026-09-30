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

    public function purchaseLine()
    {
        return $this->belongsTo(\App\PurchaseLine::class, 'purchase_line_id');
    }

    public function sellLine()
    {
        return $this->belongsTo(\App\TransactionSellLine::class, 'transaction_sell_line_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }
}
