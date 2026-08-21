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

            // Sync Operating Expenses (account_sub_type_id = 14) to POS ExpenseCategory
            if (self::shouldSyncToExpenseCategory($accountingAccount)) {
                self::syncToExpenseCategory($accountingAccount);
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

            // Sync Operating Expenses to POS ExpenseCategory upon update
            if (self::shouldSyncToExpenseCategory($accountingAccount)) {
                self::syncToExpenseCategory($accountingAccount);
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

            if (self::shouldSyncToExpenseCategory($accountingAccount)) {
                self::deleteExpenseCategorySync($accountingAccount);
            }
        });
    }

    /**
     * Determine if the Accounting account should sync to ExpenseCategory.
     *
     * @param  \Modules\Accounting\Entities\AccountingAccount  $accountingAccount
     * @return bool
     */
    public static function shouldSyncToExpenseCategory($accountingAccount)
    {
        return $accountingAccount->account_sub_type_id == 14 && class_exists(\App\ExpenseCategory::class);
    }

    /**
     * Synchronize AccountingAccount with ExpenseCategory and default location maps.
     *
     * @param  \Modules\Accounting\Entities\AccountingAccount  $accountingAccount
     * @return void
     */
    public static function syncToExpenseCategory($accountingAccount)
    {
        if (self::$is_syncing) {
            return;
        }

        self::$is_syncing = true;
        try {
            $business_id = $accountingAccount->business_id;

            // Find existing matching ExpenseCategory by name or via default map
            $category = \App\ExpenseCategory::where('business_id', $business_id)
                ->where('name', $accountingAccount->name)
                ->first();

            if (!$category) {
                // Search via BusinessLocation default map
                $location = \App\BusinessLocation::where('business_id', $business_id)->first();
                if ($location) {
                    $map = json_decode($location->accounting_default_map, true) ?: [];
                    foreach ($map as $key => $val) {
                        if (str_starts_with($key, 'expense_') && isset($val['deposit_to']) && $val['deposit_to'] == $accountingAccount->id) {
                            $cat_id = str_replace('expense_', '', $key);
                            $category = \App\ExpenseCategory::where('business_id', $business_id)->find($cat_id);
                            if ($category) {
                                break;
                            }
                        }
                    }
                }
            }

            if ($category) {
                $category->update([
                    'name' => $accountingAccount->name,
                    'code' => $accountingAccount->gl_code,
                ]);
            } else {
                $category = \App\ExpenseCategory::create([
                    'name' => $accountingAccount->name,
                    'business_id' => $business_id,
                    'code' => $accountingAccount->gl_code,
                ]);
            }

            // Find primary active Cash Account (sub_type_id 3)
            $cash_account = AccountingAccount::where('business_id', $business_id)
                ->where('account_sub_type_id', 3)
                ->where('status', 'active')
                ->orderBy('id', 'asc')
                ->first();

            $cash_account_id = $cash_account ? $cash_account->id : null;

            // Update accounting_default_map in all BusinessLocations for this business
            $locations = \App\BusinessLocation::where('business_id', $business_id)->get();
            foreach ($locations as $loc) {
                $map = json_decode($loc->accounting_default_map, true) ?: [];
                $map['expense_' . $category->id] = [
                    'deposit_to' => $accountingAccount->id,
                    'payment_account' => $cash_account_id,
                ];
                $loc->update(['accounting_default_map' => json_encode($map)]);
            }
        } catch (\Exception $e) {
            \Log::error('Error in syncToExpenseCategory: ' . $e->getMessage());
        } finally {
            self::$is_syncing = false;
        }
    }

    /**
     * Handle deletion sync of AccountingAccount to ExpenseCategory.
     *
     * @param  \Modules\Accounting\Entities\AccountingAccount  $accountingAccount
     * @return void
     */
    public static function deleteExpenseCategorySync($accountingAccount)
    {
        if (self::$is_syncing) {
            return;
        }

        self::$is_syncing = true;
        try {
            $business_id = $accountingAccount->business_id;

            $category = \App\ExpenseCategory::where('business_id', $business_id)
                ->where('name', $accountingAccount->name)
                ->first();

            if ($category) {
                $category->delete();
            }
        } catch (\Exception $e) {
            \Log::error('Error in deleteExpenseCategorySync: ' . $e->getMessage());
        } finally {
            self::$is_syncing = false;
        }
    }

    /**
     * Determine if the Accounting account should sync to POS.
     *
     * @param  \Modules\Accounting\Entities\AccountingAccount  $accountingAccount
     * @return bool
     */
    public static function shouldSyncToPOSStatic($accountingAccount)
    {
        return true;
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

                $sub_type_label = $v->sub_type;
                if (! empty($v->sub_type)) {
                    if (\Lang::has('accounting::lang.'.$v->sub_type)) {
                        $sub_type_label = __('accounting::lang.'.$v->sub_type);
                    } elseif (\Lang::has('account.'.$v->sub_type)) {
                        $sub_type_label = __('account.'.$v->sub_type);
                    }
                }

                $accounts[$k]->sub_type = ! empty($v->sub_type_business_id) ? $v->sub_type : $sub_type_label;
            }

            return $accounts;
        } else {
            return $query->pluck('name', 'id');
        }
    }
}
