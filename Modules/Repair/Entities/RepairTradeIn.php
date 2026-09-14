<?php

namespace Modules\Repair\Entities;

use Illuminate\Database\Eloquent\Model;

class RepairTradeIn extends Model
{
    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }

    public function jobSheet()
    {
        return $this->belongsTo(\Modules\Repair\Entities\JobSheet::class, 'job_sheet_id');
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'purchase_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }

    public function brand()
    {
        return $this->belongsTo(\App\Brands::class, 'brand_id');
    }

    public function deviceModel()
    {
        return $this->belongsTo(\Modules\Repair\Entities\DeviceModel::class, 'device_model_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Category::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
