<?php

namespace Modules\Repair\Entities;

use Illuminate\Database\Eloquent\Model;

class RepairTradeIn extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'repair_trade_ins';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Relationship with Business
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    /**
     * Relationship with Location
     */
    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    /**
     * Relationship with Contact
     */
    public function customer()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    /**
     * Relationship with Job Sheet
     */
    public function jobSheet()
    {
        return $this->belongsTo(JobSheet::class, 'job_sheet_id');
    }

    /**
     * Relationship with Sale Transaction
     */
    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }

    /**
     * Relationship with Purchase Transaction (for second-hand item stock entry)
     */
    public function purchaseTransaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'purchase_transaction_id');
    }

    /**
     * Relationship with Product
     */
    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    /**
     * Relationship with User creator
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
