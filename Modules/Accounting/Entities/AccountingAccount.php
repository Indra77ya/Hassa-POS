<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public static $is_syncing = false;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($accountingAccount) {
            if (self::$is_syncing) {
                return;
            }

            if (self::shouldSyncToPOSStatic($accountingAccount)) {
                if (class_exists(\App\Account::class)) {
                    self::$is_syncing = true;
                    \App\Account::$is_syncing = true;

                    try {
                        $account = null;
                        if (!empty($accountingAccount->account_id)) {
                            $account = \App\Account::find($accountingAccount->account_id);
                        }

                        if (!$account) {
                            $account = \App\Account::where('business_id', $accountingAccount->business_id)
                                ->where('name', $accountingAccount->name)
                                ->first();
                        }

                        if (!$account) {
                            $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting(
                                $accountingAccount->account_primary_type,
                                $accountingAccount->account_sub_type_id,
                                $accountingAccount->business_id
                            );

                            $account = \App\Account::create([
                                'name' => $accountingAccount->name,
                                'business_id' => $accountingAccount->business_id,
                                'created_by' => $accountingAccount->created_by ?? 1,
                                'note' => $accountingAccount->description,
                                'account_number' => $accountingAccount->gl_code,
                                'is_closed' => $accountingAccount->status == 'active' ? 0 : 1,
                                'accounting_account_id' => $accountingAccount->id,
                                'account_type_id' => $account_type_id,
                            ]);
                        } else {
                            $account->accounting_account_id = $accountingAccount->id;
                            $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting(
                                $accountingAccount->account_primary_type,
                                $accountingAccount->account_sub_type_id,
                                $accountingAccount->business_id
                            );
                            $account->account_type_id = $account_type_id;
                            if (empty($account->account_number) && !empty($accountingAccount->gl_code)) {
                                $account->account_number = $accountingAccount->gl_code;
                            }
                            $account->save();
                        }

                        if ($account) {
                            $accountingAccount->account_id = $account->id;
                            $accountingAccount->save();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error syncing Accounting Account to Payment Account: ' . $e->getMessage());
                    } finally {
                        self::$is_syncing = false;
                        \App\Account::$is_syncing = false;
                    }
                }
            }

            // Sync Operating Expense (sub_type_id 14) to ExpenseCategory
            if (in_array($accountingAccount->account_primary_type, ['expense', 'expenses']) && $accountingAccount->account_sub_type_id == 14) {
                if (class_exists(\App\ExpenseCategory::class)) {
                    try {
                        $exp_cat = \App\ExpenseCategory::where('business_id', $accountingAccount->business_id)
                            ->where('name', $accountingAccount->name)
                            ->first();
                        if (!$exp_cat) {
                            \App\ExpenseCategory::create([
                                'name' => $accountingAccount->name,
                                'business_id' => $accountingAccount->business_id,
                                'code' => $accountingAccount->gl_code,
                            ]);
                        } elseif (empty($exp_cat->code) && !empty($accountingAccount->gl_code)) {
                            $exp_cat->update(['code' => $accountingAccount->gl_code]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error syncing Operating Expense Accounting Account to ExpenseCategory: ' . $e->getMessage());
                    }
                }
            }
        });

        static::updated(function ($accountingAccount) {
            if (self::$is_syncing) {
                return;
            }

            $should_sync = self::shouldSyncToPOSStatic($accountingAccount);

            if ($should_sync || !empty($accountingAccount->account_id)) {
                if (class_exists(\App\Account::class)) {
                    self::$is_syncing = true;
                    \App\Account::$is_syncing = true;

                    try {
                        $account = null;
                        if (!empty($accountingAccount->account_id)) {
                            $account = \App\Account::find($accountingAccount->account_id);
                        }

                        if (!$account && $should_sync) {
                            // Fallback matching by name and business_id
                            $account = \App\Account::where('business_id', $accountingAccount->business_id)
                                ->where('name', $accountingAccount->name)
                                ->first();
                        }

                        if ($account) {
                            if (!$should_sync) {
                                $account->delete();
                                $accountingAccount->account_id = null;
                                $accountingAccount->save();
                            } else {
                                $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting(
                                    $accountingAccount->account_primary_type,
                                    $accountingAccount->account_sub_type_id,
                                    $accountingAccount->business_id
                                );

                                $account->update([
                                    'name' => $accountingAccount->name,
                                    'note' => $accountingAccount->description,
                                    'account_number' => $accountingAccount->gl_code,
                                    'is_closed' => $accountingAccount->status == 'active' ? 0 : 1,
                                    'accounting_account_id' => $accountingAccount->id,
                                    'account_type_id' => $account_type_id,
                                ]);

                                if (empty($accountingAccount->account_id)) {
                                    $accountingAccount->account_id = $account->id;
                                    $accountingAccount->save();
                                }
                            }
                        } elseif ($should_sync) {
                            $account_type_id = \App\Account::getPOSAccountTypeIdFromAccounting(
                                $accountingAccount->account_primary_type,
                                $accountingAccount->account_sub_type_id,
                                $accountingAccount->business_id
                            );

                            $account = \App\Account::create([
                                'name' => $accountingAccount->name,
                                'business_id' => $accountingAccount->business_id,
                                'created_by' => $accountingAccount->created_by ?? 1,
                                'note' => $accountingAccount->description,
                                'account_number' => $accountingAccount->gl_code,
                                'is_closed' => $accountingAccount->status == 'active' ? 0 : 1,
                                'accounting_account_id' => $accountingAccount->id,
                                'account_type_id' => $account_type_id,
                            ]);

                            if ($account) {
                                $accountingAccount->account_id = $account->id;
                                $accountingAccount->save();
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error updating Payment Account from Accounting Account: ' . $e->getMessage());
                    } finally {
                        self::$is_syncing = false;
                        \App\Account::$is_syncing = false;
                    }
                }
            }

            // Sync Operating Expense (sub_type_id 14) to ExpenseCategory
            if (in_array($accountingAccount->account_primary_type, ['expense', 'expenses']) && $accountingAccount->account_sub_type_id == 14) {
                if (class_exists(\App\ExpenseCategory::class)) {
                    try {
                        $old_name = $accountingAccount->getOriginal('name') ?? $accountingAccount->name;
                        $exp_cat = \App\ExpenseCategory::where('business_id', $accountingAccount->business_id)
                            ->where(function($q) use ($old_name, $accountingAccount) {
                                $q->where('name', $old_name)->orWhere('name', $accountingAccount->name);
                            })->first();
                        if ($exp_cat) {
                            $exp_cat->update([
                                'name' => $accountingAccount->name,
                                'code' => $accountingAccount->gl_code,
                            ]);
                        } else {
                            \App\ExpenseCategory::create([
                                'name' => $accountingAccount->name,
                                'business_id' => $accountingAccount->business_id,
                                'code' => $accountingAccount->gl_code,
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error updating ExpenseCategory from Operating Expense Accounting Account: ' . $e->getMessage());
                    }
                }
            }
        });

        static::deleted(function ($accountingAccount) {
            if (self::$is_syncing) {
                return;
            }

            if (class_exists(\App\Account::class)) {
                self::$is_syncing = true;
                \App\Account::$is_syncing = true;

                try {
                    $account = null;
                    if (!empty($accountingAccount->account_id)) {
                        $account = \App\Account::find($accountingAccount->account_id);
                    }

                    if ($account) {
                        $account->delete();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error deleting Payment Account from Accounting Account: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    \App\Account::$is_syncing = false;
                }
            }

            // Sync Operating Expense (sub_type_id 14) deletion to ExpenseCategory
            if (in_array($accountingAccount->account_primary_type, ['expense', 'expenses']) && $accountingAccount->account_sub_type_id == 14) {
                if (class_exists(\App\ExpenseCategory::class)) {
                    try {
                        $exp_cat = \App\ExpenseCategory::where('business_id', $accountingAccount->business_id)
                            ->where('name', $accountingAccount->name)
                            ->first();
                        if ($exp_cat) {
                            $exp_cat->delete();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error deleting ExpenseCategory from Operating Expense Accounting Account: ' . $e->getMessage());
                    }
                }
            }
        });
    }

    /**
     * Determine if the Accounting account should sync to POS.
     *
     * @param  \Modules\Accounting\Entities\AccountingAccount  $accountingAccount
     * @return bool
     */
    public static function shouldSyncToPOSStatic($accountingAccount)
    {
        $primary = $accountingAccount->account_primary_type;
        $sub = $accountingAccount->account_sub_type_id;

        if ($primary == 'asset') {
            // a. Akun Kas & Bank (cash_and_cash_equivalents)
            if ($sub == 3) {
                return true;
            }
            // b. Akun Piutang Usaha (accounts_receivable)
            if ($sub == 1) {
                return true;
            }
            // d. Akun Persediaan (current_assets khusus inventory)
            if ($sub == 2) {
                $detail_type_name = \Modules\Accounting\Entities\AccountingAccountType::where('id', $accountingAccount->detail_type_id)->value('name');
                if ($detail_type_name == 'inventory') {
                    return true;
                }
            }
        } elseif ($primary == 'liability') {
            // c. Akun Hutang Usaha (accounts_payable)
            if ($sub == 6) {
                return true;
            }
        } elseif ($primary == 'income') {
            // e. Akun Pendapatan Usaha (income)
            if ($sub == 11) {
                return true;
            }
        } elseif (in_array($primary, ['expense', 'expenses'])) {
            // f. Akun HPP (cost_of_sale)
            if ($sub == 13) {
                return true;
            }
            // g. Akun Beban Operasional (expenses)
            if ($sub == 14) {
                return true;
            }
        }

        return false;
    }

    public function child_accounts()
    {
        return $this->hasMany(\Modules\Accounting\Entities\AccountingAccount::class, 'parent_account_id');
    }

    // public function account_type()
    // {
    //     return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccountType::class, 'account_type_id');
    // }

    public function account_sub_type()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccountType::class, 'account_sub_type_id');
    }

    public function detail_type()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccountType::class, 'detail_type_id');
    }

    /**
     * Accounts Dropdown
     *
     * @param  int  $business_id
     * @return array
     */
    public static function forDropdown($business_id, $with_data = false, $q = '')
    {
        $query = AccountingAccount::where('accounting_accounts.business_id', $business_id)
                        ->where('status', 'active');
        if ($with_data) {
            $account_types = AccountingAccountType::accounting_primary_type();

            if (! empty($q)) {
                $query->where('accounting_accounts.name', 'like', "%{$q}%");
            }
            $accounts = $query->leftJoin('accounting_account_types as at', 'at.id', '=', 'accounting_accounts.account_sub_type_id')
                ->select('accounting_accounts.name', 'accounting_accounts.id', 'at.name as sub_type',
                 'accounting_accounts.account_primary_type', 'at.business_id as sub_type_business_id')
                ->get();

            foreach ($accounts as $k => $v) {
                $accounts[$k]->account_primary_type = ! empty($account_types[$v->account_primary_type]) ?
                $account_types[$v->account_primary_type]['label'] : $v->account_primary_type;

                $accounts[$k]->sub_type = ! empty($v->sub_type_business_id) ? $v->sub_type : __('accounting::lang.'.$v->sub_type);
            }

            return $accounts;
        } else {
            return $query->pluck('name', 'id');
        }
    }
}
