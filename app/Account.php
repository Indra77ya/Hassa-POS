<?php

namespace App;

use App\Utils\Util;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public static $is_syncing = false;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($account) {
            if (self::$is_syncing) {
                return;
            }

            if (class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
                self::$is_syncing = true;
                \Modules\Accounting\Entities\AccountingAccount::$is_syncing = true;

                try {
                    // Check if already synced or exists
                    $accounting_account = null;
                    if (!empty($account->accounting_account_id)) {
                        $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($account->accounting_account_id);
                    }

                    if (!$accounting_account) {
                        $mapped_type = self::getMappedAccountingType($account);
                        $accounting_account = \Modules\Accounting\Entities\AccountingAccount::create([
                            'name' => $account->name,
                            'business_id' => $account->business_id,
                            'created_by' => $account->created_by ?? 1,
                            'description' => $account->note,
                            'gl_code' => $account->account_number,
                            'status' => $account->is_closed ? 'inactive' : 'active',
                            'account_primary_type' => $mapped_type['primary'],
                            'account_sub_type_id' => $mapped_type['sub_type_id'],
                            'account_id' => $account->id,
                        ]);
                    } else {
                        $accounting_account->account_id = $account->id;
                        $accounting_account->save();
                    }

                    if ($accounting_account) {
                        $account->accounting_account_id = $accounting_account->id;
                        $account->save();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error syncing Payment Account to Accounting Account: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    \Modules\Accounting\Entities\AccountingAccount::$is_syncing = false;
                }
            }
        });

        static::updated(function ($account) {
            if (self::$is_syncing) {
                return;
            }

            if (class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
                self::$is_syncing = true;
                \Modules\Accounting\Entities\AccountingAccount::$is_syncing = true;

                try {
                    $accounting_account = null;
                    if (!empty($account->accounting_account_id)) {
                        $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($account->accounting_account_id);
                    }

                    if (!$accounting_account) {
                        // Fallback matching by name and business_id
                        $accounting_account = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $account->business_id)
                            ->where('name', $account->name)
                            ->first();
                    }

                    if ($accounting_account) {
                        $mapped_type = self::getMappedAccountingType($account);
                        $accounting_account->update([
                            'name' => $account->name,
                            'description' => $account->note,
                            'gl_code' => $account->account_number,
                            'status' => $account->is_closed ? 'inactive' : 'active',
                            'account_id' => $account->id,
                            'account_primary_type' => $mapped_type['primary'],
                            'account_sub_type_id' => $mapped_type['sub_type_id'],
                        ]);

                        if (empty($account->accounting_account_id)) {
                            $account->accounting_account_id = $accounting_account->id;
                            $account->save();
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating Accounting Account from Payment Account: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    \Modules\Accounting\Entities\AccountingAccount::$is_syncing = false;
                }
            }
        });

        static::deleted(function ($account) {
            if (self::$is_syncing) {
                return;
            }

            if (class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
                self::$is_syncing = true;
                \Modules\Accounting\Entities\AccountingAccount::$is_syncing = true;

                try {
                    $accounting_account = null;
                    if (!empty($account->accounting_account_id)) {
                        $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($account->accounting_account_id);
                    }

                    if ($accounting_account) {
                        $accounting_account->delete();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error deleting Accounting Account from Payment Account: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    \Modules\Accounting\Entities\AccountingAccount::$is_syncing = false;
                }
            }
        });
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'account_details' => 'array',
    ];

    public static function forDropdown($business_id, $prepend_none, $closed = false, $show_balance = false)
    {
        $query = Account::where('accounts.business_id', $business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        $account_ids = [];
        if ($permitted_locations != 'all') {
            $locations = BusinessLocation::where('business_id', $business_id)
                            ->whereIn('id', $permitted_locations)
                            ->get();

            foreach ($locations as $location) {
                if (! empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    foreach ($default_payment_accounts as $key => $account) {
                        if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }
                }
            }

            $account_ids = array_unique($account_ids);
        }

        if ($permitted_locations != 'all') {
            $query->whereIn('accounts.id', $account_ids);
        }

        $can_access_account = auth()->user()->can('account.access');
        if ($can_access_account && $show_balance) {
            $query->leftjoin('account_types as ats', 'accounts.account_type_id', '=', 'ats.id')
                ->leftjoin('account_types as pat', 'ats.parent_account_type_id', '=', 'pat.id')
                ->select(['accounts.name',
                    'accounts.id',
                    'accounts.normal_balance',
                    'ats.fixed_key',
                    'ats.name as account_type_name',
                    'pat.name as parent_account_type_name',
                    DB::raw("(SELECT SUM(CASE WHEN type='debit' THEN amount ELSE 0 END) FROM account_transactions WHERE account_id = accounts.id AND deleted_at IS NULL) as total_debit"),
                    DB::raw("(SELECT SUM(CASE WHEN type='credit' THEN amount ELSE 0 END) FROM account_transactions WHERE account_id = accounts.id AND deleted_at IS NULL) as total_credit"),
                ]);
        }

        if (! $closed) {
            $query->where('accounts.is_closed', 0);
        }

        $accounts = $query->get();

        $dropdown = [];
        if ($prepend_none) {
            $dropdown[''] = __('lang_v1.none');
        }

        $commonUtil = new Util;
        foreach ($accounts as $account) {
            $name = $account->name;

            if ($can_access_account && $show_balance) {
                $is_debit_normal = self::getBalanceTypeStatic($account->normal_balance, $account->fixed_key, $account->account_type_name, $account->parent_account_type_name) == 'debit';

                if ($is_debit_normal) {
                    $balance = $account->total_debit - $account->total_credit;
                } else {
                    $balance = $account->total_credit - $account->total_debit;
                }

                $name .= ' ('.__('lang_v1.balance').': '.$commonUtil->num_f($balance).')';
            }

            $dropdown[$account->id] = $name;
        }

        return $dropdown;
    }

    /**
     * Scope a query to only include not closed accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Scope a query to only include non capital accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeNotCapital($query)
    // {
    //     return $query->where(function ($q) {
    //         $q->where('account_type', '!=', 'capital');
    //         $q->orWhereNull('account_type');
    //     });
    // }

    public static function accountTypes()
    {
        return [
            '' => __('account.not_applicable'),
            'saving_current' => __('account.saving_current'),
            'capital' => __('account.capital'),
        ];
    }

    public function account_type()
    {
        return $this->belongsTo(\App\AccountType::class, 'account_type_id');
    }

    /**
     * Determine if the account has a debit normal balance.
     * Assets and Expenses are typically debit-normal.
     *
     * @return string (debit|credit)
     */
    public function getBalanceType()
    {
        return self::getBalanceTypeStatic($this->normal_balance, $this->account_type->fixed_key ?? '', $this->account_type->name ?? '', $this->account_type->parent_account->name ?? '');
    }

    public static function getBalanceTypeStatic($normal_balance, $fixed_key, $type_name = '', $parent_type_name = '')
    {
        if (!empty($normal_balance)) {
            return $normal_balance;
        }

        $type_name = strtolower($type_name);
        $parent_type_name = strtolower($parent_type_name);

        $debit_keys = [
            'kas_dan_bank', 'piutang_usaha', 'persediaan', 'aktiva_lancar_lainnya',
            'aktiva_tetap', 'aktiva_lainnya', 'harga_pokok_penjualan',
            'beban_operasional', 'beban_lain_lain', 'beban_pajak'
        ];

        if (in_array($fixed_key, $debit_keys)) {
            return 'debit';
        }

        // Fallback for legacy data or if fixed_key is missing
        $debit_names = [
            'aktiva lancar', 'aktiva tetap', 'current assets', 'fixed assets',
            'cogs', 'expenses', 'biaya operasional', 'harga pokok penjualan', 'beban',
            'kas', 'bank', 'cash', 'piutang', 'receivable', 'persediaan', 'inventory', 'asset'
        ];

        foreach ($debit_names as $name) {
            if (strpos($type_name, $name) !== false || strpos($parent_type_name, $name) !== false) {
                return 'debit';
            }
        }

        return 'credit';
    }

    /**
     * Map POS account type to Accounting primary and sub type ID.
     *
     * @param  \App\Account  $account
     * @return array
     */
    public static function getMappedAccountingType($account)
    {
        $fixed_key = '';
        if ($account->account_type) {
            $fixed_key = $account->account_type->fixed_key;
        } else if (!empty($account->account_type_id)) {
            $type = \App\AccountType::find($account->account_type_id);
            if ($type) {
                $fixed_key = $type->fixed_key;
            }
        }

        switch ($fixed_key) {
            case 'kas_dan_bank':
                return ['primary' => 'asset', 'sub_type_id' => 3]; // cash_and_cash_equivalents
            case 'piutang_usaha':
                return ['primary' => 'asset', 'sub_type_id' => 1]; // accounts_receivable
            case 'persediaan':
            case 'aktiva_lancar_lainnya':
                return ['primary' => 'asset', 'sub_type_id' => 2]; // current_assets
            case 'aktiva_tetap':
            case 'akumulasi_penyusutan':
                return ['primary' => 'asset', 'sub_type_id' => 4]; // fixed_assets
            case 'aktiva_lainnya':
                return ['primary' => 'asset', 'sub_type_id' => 5]; // non_current_assets
            case 'hutang_usaha':
                return ['primary' => 'liability', 'sub_type_id' => 6]; // accounts_payable
            case 'hutang_lancar_lainnya':
                return ['primary' => 'liability', 'sub_type_id' => 8]; // current_liabilities
            case 'hutang_jangka_panjang':
                return ['primary' => 'liability', 'sub_type_id' => 9]; // non_current_liabilities
            case 'ekuitas':
                return ['primary' => 'equity', 'sub_type_id' => 10]; // owners_equity
            case 'pendapatan_usaha':
                return ['primary' => 'income', 'sub_type_id' => 11]; // income
            case 'pendapatan_lainnya':
                return ['primary' => 'income', 'sub_type_id' => 12]; // other_income
            case 'harga_pokok_penjualan':
                return ['primary' => 'expenses', 'sub_type_id' => 13]; // cost_of_sale
            case 'beban_operasional':
            case 'beban_pajak':
                return ['primary' => 'expenses', 'sub_type_id' => 14]; // expenses
            case 'beban_lain_lain':
                return ['primary' => 'expenses', 'sub_type_id' => 15]; // other_expense
            default:
                return ['primary' => 'asset', 'sub_type_id' => 3]; // fallback to Cash & equivalents
        }
    }

    /**
     * Resolve POS account type ID from Accounting account primary and sub_type_id.
     *
     * @param  string  $primary
     * @param  int  $sub_type_id
     * @param  int  $business_id
     * @return int|null
     */
    public static function getPOSAccountTypeIdFromAccounting($primary, $sub_type_id, $business_id)
    {
        $fixed_key = null;
        if ($primary == 'asset') {
            if ($sub_type_id == 3) {
                $fixed_key = 'kas_dan_bank';
            } elseif ($sub_type_id == 1) {
                $fixed_key = 'piutang_usaha';
            } elseif ($sub_type_id == 2) {
                $fixed_key = 'persediaan'; // or aktiva_lancar_lainnya, choose persediaan as default
            } elseif ($sub_type_id == 4) {
                $fixed_key = 'aktiva_tetap';
            } elseif ($sub_type_id == 5) {
                $fixed_key = 'aktiva_lainnya';
            }
        } elseif ($primary == 'liability') {
            if ($sub_type_id == 6) {
                $fixed_key = 'hutang_usaha';
            } elseif ($sub_type_id == 8) {
                $fixed_key = 'hutang_lancar_lainnya';
            } elseif ($sub_type_id == 9) {
                $fixed_key = 'hutang_jangka_panjang';
            }
        } elseif ($primary == 'equity') {
            $fixed_key = 'ekuitas';
        } elseif ($primary == 'income') {
            if ($sub_type_id == 11) {
                $fixed_key = 'pendapatan_usaha';
            } elseif ($sub_type_id == 12) {
                $fixed_key = 'pendapatan_lainnya';
            }
        } elseif (in_array($primary, ['expense', 'expenses'])) {
            if ($sub_type_id == 13) {
                $fixed_key = 'harga_pokok_penjualan';
            } elseif ($sub_type_id == 14) {
                $fixed_key = 'beban_operasional';
            } elseif ($sub_type_id == 15) {
                $fixed_key = 'beban_lain_lain';
            }
        }

        if ($fixed_key) {
            $type = \App\AccountType::where('business_id', $business_id)
                ->where('fixed_key', $fixed_key)
                ->first();
            if ($type) {
                return $type->id;
            }
        }

        return null;
    }

    /**
     * Get account group category (Asset, Liability, Equity, etc.)
     *
     * @return string
     */
    public function getCategory()
    {
        return self::getCategoryStatic($this->account_type->fixed_key ?? '');
    }

    public static function getCategoryStatic($fixed_key)
    {
        if (in_array($fixed_key, ['kas_dan_bank', 'piutang_usaha', 'persediaan', 'aktiva_lancar_lainnya', 'aktiva_tetap', 'akumulasi_penyusutan', 'aktiva_lainnya'])) {
            return __('account.assets');
        } elseif (in_array($fixed_key, ['hutang_usaha', 'hutang_lancar_lainnya', 'hutang_jangka_panjang'])) {
            return __('account.liability');
        } elseif ($fixed_key == 'ekuitas') {
            return __('account.equity');
        } elseif (in_array($fixed_key, ['pendapatan_usaha', 'pendapatan_lainnya'])) {
            return __('account.income');
        } elseif (in_array($fixed_key, ['harga_pokok_penjualan', 'beban_operasional', 'beban_lain_lain', 'beban_pajak'])) {
            return __('account.expenses');
        }

        return '';
    }

    /**
     * Get transaction type to increase balance
     */
    public function getIncreaseType()
    {
        return $this->getBalanceType();
    }

    /**
     * Get transaction type to decrease balance
     */
    public function getDecreaseType()
    {
        return $this->getBalanceType() == 'debit' ? 'credit' : 'debit';
    }
}
