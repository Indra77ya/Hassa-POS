<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccountsTransaction extends Model
{
    protected $guarded = [];

    public static $is_syncing = false;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($aat) {
            if (self::$is_syncing || (class_exists(\App\AccountTransaction::class) && \App\AccountTransaction::$is_syncing)) {
                return;
            }

            $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($aat->accounting_account_id);
            if ($accounting_account && !empty($accounting_account->account_id)) {
                self::$is_syncing = true;
                if (class_exists(\App\AccountTransaction::class)) {
                    \App\AccountTransaction::$is_syncing = true;
                }

                try {
                    $at = \App\AccountTransaction::create([
                        'account_id' => $accounting_account->account_id,
                        'transaction_id' => $aat->transaction_id,
                        'transaction_payment_id' => $aat->transaction_payment_id,
                        'amount' => $aat->amount,
                        'type' => $aat->type,
                        'sub_type' => $aat->sub_type ?? 'other',
                        'created_by' => $aat->created_by ?? 1,
                        'operation_date' => $aat->operation_date,
                        'note' => $aat->note,
                        'accounting_accounts_transaction_id' => $aat->id,
                    ]);

                    if ($at) {
                        $aat->account_transaction_id = $at->id;
                        $aat->save();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error syncing AccountingAccountsTransaction to AccountTransaction: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    if (class_exists(\App\AccountTransaction::class)) {
                        \App\AccountTransaction::$is_syncing = false;
                    }
                }
            }

            if ($aat->sub_type === 'transfer' || !empty($aat->acc_trans_mapping_id)) {
                self::linkPOSFundTransfer($aat);
            }
        });

        static::updated(function ($aat) {
            if (self::$is_syncing || (class_exists(\App\AccountTransaction::class) && \App\AccountTransaction::$is_syncing)) {
                return;
            }

            $accounting_account = \Modules\Accounting\Entities\AccountingAccount::find($aat->accounting_account_id);
            if ($accounting_account && !empty($accounting_account->account_id)) {
                self::$is_syncing = true;
                if (class_exists(\App\AccountTransaction::class)) {
                    \App\AccountTransaction::$is_syncing = true;
                }

                try {
                    $at = null;
                    if (!empty($aat->account_transaction_id)) {
                        $at = \App\AccountTransaction::find($aat->account_transaction_id);
                    }

                    if (!$at) {
                        $at = \App\AccountTransaction::where('accounting_accounts_transaction_id', $aat->id)->first();
                    }

                    if ($at) {
                        $at->update([
                            'account_id' => $accounting_account->account_id,
                            'transaction_id' => $aat->transaction_id,
                            'transaction_payment_id' => $aat->transaction_payment_id,
                            'amount' => $aat->amount,
                            'type' => $aat->type,
                            'sub_type' => $aat->sub_type ?? 'other',
                            'operation_date' => $aat->operation_date,
                            'note' => $aat->note,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating AccountTransaction from AccountingAccountsTransaction: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    if (class_exists(\App\AccountTransaction::class)) {
                        \App\AccountTransaction::$is_syncing = false;
                    }
                }
            }

            if ($aat->sub_type === 'transfer' || !empty($aat->acc_trans_mapping_id)) {
                self::linkPOSFundTransfer($aat);
            }
        });

        static::deleted(function ($aat) {
            if (self::$is_syncing || (class_exists(\App\AccountTransaction::class) && \App\AccountTransaction::$is_syncing)) {
                return;
            }

            self::$is_syncing = true;
            if (class_exists(\App\AccountTransaction::class)) {
                \App\AccountTransaction::$is_syncing = true;
            }

            try {
                $at = null;
                if (!empty($aat->account_transaction_id)) {
                    $at = \App\AccountTransaction::find($aat->account_transaction_id);
                }

                if (!$at) {
                    $at = \App\AccountTransaction::where('accounting_accounts_transaction_id', $aat->id)->first();
                }

                if ($at) {
                    $at->delete();
                }
            } catch (\Exception $e) {
                \Log::error('Error deleting AccountTransaction from AccountingAccountsTransaction: ' . $e->getMessage());
            } finally {
                self::$is_syncing = false;
                if (class_exists(\App\AccountTransaction::class)) {
                    \App\AccountTransaction::$is_syncing = false;
                }
            }
        });
    }

    public function account()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccount', 'accounting_account_id');
    }

    /**
     * Creates new account transaction
     *
     * @return obj
     */
    public static function createTransaction($data)
    {
        $transaction = new AccountingAccountsTransaction();

        $transaction->amount = $data['amount'];
        $transaction->accounting_account_id = $data['accounting_account_id'];
        $transaction->transaction_id = ! empty($data['transaction_id']) ? $data['transaction_id'] : null;
        $transaction->type = $data['type'];
        $transaction->sub_type = ! empty($data['sub_type']) ? $data['sub_type'] : null;
        $transaction->map_type = ! empty($data['map_type']) ? $data['map_type'] : null;
        $transaction->operation_date = ! empty($data['operation_date']) ? $data['operation_date'] : \Carbon::now();
        $transaction->created_by = $data['created_by'];
        $transaction->note = ! empty($data['note']) ? $data['note'] : null;

        return $transaction->save();
    }

    /**
     * Creates/updates account transaction
     *
     * @return obj
     */
    public static function updateOrCreateMapTransaction($data)
    {
        $transaction = AccountingAccountsTransaction::updateOrCreate(
            ['transaction_id' => $data['transaction_id'],
                'map_type' => $data['map_type'],
                'transaction_payment_id' => $data['transaction_payment_id'],
            ],
            ['accounting_account_id' => $data['accounting_account_id'], 'amount' => $data['amount'],
                'type' => $data['type'], 'sub_type' => $data['sub_type'], 'created_by' => $data['created_by'], 'operation_date' => $data['operation_date'], 'note' => $data['note']
            ]
        );
    }

    public static function linkPOSFundTransfer($aat)
    {
        if (empty($aat->acc_trans_mapping_id)) {
            return;
        }

        if (self::$is_syncing || (class_exists(\App\AccountTransaction::class) && \App\AccountTransaction::$is_syncing)) {
            return;
        }

        try {
            $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::find($aat->acc_trans_mapping_id);
            if (!$mapping || $mapping->type !== 'transfer') {
                return;
            }

            $txs = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->get();
            if ($txs->count() < 2) {
                return;
            }

            $aat1 = $txs->first();
            $aat2 = $txs->last();

            $at1 = null;
            if (!empty($aat1->account_transaction_id)) {
                $at1 = \App\AccountTransaction::find($aat1->account_transaction_id);
            }
            if (!$at1) {
                $at1 = \App\AccountTransaction::where('accounting_accounts_transaction_id', $aat1->id)->first();
            }

            $at2 = null;
            if (!empty($aat2->account_transaction_id)) {
                $at2 = \App\AccountTransaction::find($aat2->account_transaction_id);
            }
            if (!$at2) {
                $at2 = \App\AccountTransaction::where('accounting_accounts_transaction_id', $aat2->id)->first();
            }

            if ($at1 && $at2) {
                self::$is_syncing = true;
                if (class_exists(\App\AccountTransaction::class)) {
                    \App\AccountTransaction::$is_syncing = true;
                }

                $at1->update([
                    'sub_type' => 'fund_transfer',
                    'transfer_transaction_id' => $at2->id,
                ]);

                $at2->update([
                    'sub_type' => 'fund_transfer',
                    'transfer_transaction_id' => $at1->id,
                ]);

                $aat1->update(['sub_type' => 'fund_transfer']);
                $aat2->update(['sub_type' => 'fund_transfer']);
            }

        } catch (\Exception $e) {
            \Log::error('Error in linkPOSFundTransfer: ' . $e->getMessage());
        } finally {
            self::$is_syncing = false;
            if (class_exists(\App\AccountTransaction::class)) {
                \App\AccountTransaction::$is_syncing = false;
            }
        }
    }
}
