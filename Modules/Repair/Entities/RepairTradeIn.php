<?php

namespace Modules\Repair\Entities;

use Illuminate\Database\Eloquent\Model;

class RepairTradeIn extends Model
{
    protected $table = 'repair_trade_ins';

    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'purchase_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function jobSheet()
    {
        return $this->belongsTo(\Modules\Repair\Entities\JobSheet::class, 'job_sheet_id');
    }
}
