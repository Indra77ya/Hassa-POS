<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccTransMapping extends Model
{
    protected $guarded = ['id'];

    public function accounts_transactions()
    {
        return $this->hasMany(\Modules\Accounting\Entities\AccountingAccountsTransaction::class, 'acc_trans_mapping_id');
    }
}
