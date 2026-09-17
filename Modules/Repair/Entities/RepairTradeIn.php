<?php

namespace Modules\Repair\Entities;

use Illuminate\Database\Eloquent\Model;

class RepairTradeIn extends Model
{
    protected $table = 'repair_trade_ins';

    protected $guarded = ['id'];

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'purchase_transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function unit()
    {
        return $this->belongsTo(\App\Unit::class, 'unit_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Category::class, 'category_id');
    }
}
