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
        $query = Account::where('business_id', $business_id);

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
                    DB::raw("(SELECT SUM(IF(type='debit', amount, 0)) FROM account_transactions WHERE account_id = accounts.id AND deleted_at IS NULL) as total_debit"),
                    DB::raw("(SELECT SUM(IF(type='credit', amount, 0)) FROM account_transactions WHERE account_id = accounts.id AND deleted_at IS NULL) as total_credit"),
                ]);
        }

        if (! $closed) {
            $query->where('is_closed', 0);
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
