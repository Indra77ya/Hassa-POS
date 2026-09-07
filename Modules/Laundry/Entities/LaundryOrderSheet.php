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

    public function transactions()
    {
        return $this->hasMany(\App\Transaction::class, 'laundry_order_sheet_id');
    }

    public function getTotalAmountAttribute()
    {
        $price = optional($this->itemType)->default_price ?? 0;
        return (float) ($this->quantity * $price);
    }

    public function getTotalPaidAttribute()
    {
        $transaction_ids = $this->transactions()->pluck('id')->toArray();
        if (empty($transaction_ids)) {
            return 0.00;
        }
        return (float) \App\TransactionPayment::whereIn('transaction_id', $transaction_ids)
            ->where('is_return', 0)
            ->sum('amount');
    }

    public function getPaymentStatusAttribute()
    {
        $total = $this->total_amount;
        $paid = $this->total_paid;

        if ($total <= 0) {
            return 'due';
        }

        if ($paid >= $total) {
            return 'paid';
        } elseif ($paid > 0) {
            return 'partial';
        } else {
            return 'due';
        }
    }
}
