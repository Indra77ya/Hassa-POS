<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccountType extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function getAccountTypeNameAttribute()
    {
        if (! empty($this->business_id)) {
            return $this->name;
        }

        if (\Lang::has('accounting::lang.'.$this->name)) {
            return __('accounting::lang.'.$this->name);
        }

        if (\Lang::has('account.'.$this->name)) {
            return __('account.'.$this->name);
        }

        return $this->name;
    }

    public function getAccountTypeDescriptionAttribute()
    {
        if (empty($this->descriptiion)) {
            return '';
        }

        $descriptiion = ! empty($this->business_id) ?
        $this->descriptiion : __('accounting::lang.'.$this->descriptiion);

        return $descriptiion;
    }

    /**
     * Get the parent of the type
     */
    public function parent()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccountType', 'parent_id', 'id');
    }

    public static function accounting_primary_type()
    {
        $accounting_primary_type = [
            'asset' => ['label' => __('accounting::lang.asset')],
            'expenses' => ['label' => __('accounting::lang.expenses')],
            'income' => ['label' => __('accounting::lang.income')],
            'equity' => ['label' => __('accounting::lang.equity')],
            'liability' => ['label' => __('accounting::lang.liability')],
        ];

        return $accounting_primary_type;
    }
}
