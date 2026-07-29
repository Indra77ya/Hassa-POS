<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public static $is_syncing = false;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($at) {
            if (self::$is_syncing || (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class) && \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing)) {
                return;
            }

            $account = \App\Account::find($at->account_id);
            if ($account && !empty($account->accounting_account_id)) {
                self::$is_syncing = true;
                if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                    \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = true;
                }

                try {
                    $aat = \Modules\Accounting\Entities\AccountingAccountsTransaction::create([
                        'accounting_account_id' => $account->accounting_account_id,
                        'transaction_id' => $at->transaction_id,
                        'transaction_payment_id' => $at->transaction_payment_id,
                        'amount' => $at->amount,
                        'type' => $at->type,
                        'sub_type' => $at->sub_type ?? 'other',
                        'created_by' => $at->created_by ?? 1,
                        'operation_date' => $at->operation_date,
                        'note' => $at->note,
                        'account_transaction_id' => $at->id,
                    ]);

                    if ($aat) {
                        $at->accounting_accounts_transaction_id = $aat->id;
                        $at->save();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error syncing AccountTransaction to Accounting: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                        \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = false;
                    }
                }
            }
        });

        static::updated(function ($at) {
            if (self::$is_syncing || (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class) && \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing)) {
                return;
            }

            $account = \App\Account::find($at->account_id);
            if ($account && !empty($account->accounting_account_id)) {
                self::$is_syncing = true;
                if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                    \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = true;
                }

                try {
                    $aat = null;
                    if (!empty($at->accounting_accounts_transaction_id)) {
                        $aat = \Modules\Accounting\Entities\AccountingAccountsTransaction::find($at->accounting_accounts_transaction_id);
                    }

                    if (!$aat) {
                        $aat = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('account_transaction_id', $at->id)->first();
                    }

                    if ($aat) {
                        $aat->update([
                            'accounting_account_id' => $account->accounting_account_id,
                            'transaction_id' => $at->transaction_id,
                            'transaction_payment_id' => $at->transaction_payment_id,
                            'amount' => $at->amount,
                            'type' => $at->type,
                            'sub_type' => $at->sub_type ?? 'other',
                            'operation_date' => $at->operation_date,
                            'note' => $at->note,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating Accounting Transaction from AccountTransaction: ' . $e->getMessage());
                } finally {
                    self::$is_syncing = false;
                    if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                        \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = false;
                    }
                }
            }
        });

        static::deleted(function ($at) {
            if (self::$is_syncing || (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class) && \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing)) {
                return;
            }

            self::$is_syncing = true;
            if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = true;
            }

            try {
                $aat = null;
                if (!empty($at->accounting_accounts_transaction_id)) {
                    $aat = \Modules\Accounting\Entities\AccountingAccountsTransaction::find($at->accounting_accounts_transaction_id);
                }

                if (!$aat) {
                    $aat = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('account_transaction_id', $at->id)->first();
                }

                if ($aat) {
                    $aat->delete();
                }
            } catch (\Exception $e) {
                \Log::error('Error deleting Accounting Transaction from AccountTransaction: ' . $e->getMessage());
            } finally {
                self::$is_syncing = false;
                if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                    \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = false;
                }
            }
        });
    }

    protected $casts = [
        'operation_date' => 'datetime',
    ];

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }

    /**
     * Gives account transaction type from payment transaction type
     *
     * @param  string  $payment_transaction_type
     * @return string
     */
    public static function getAccountTransactionType($tansaction_type)
    {
        $account_transaction_types = [
            'sell' => 'debit',
            'purchase' => 'credit',
            'expense' => 'credit',
            'purchase_return' => 'debit',
            'sell_return' => 'credit',
            'payroll' => 'credit',
            'expense_refund' => 'debit',
            'hms_booking' => 'debit',
            'gym_subscription' => 'debit',
        ];

        return $account_transaction_types[$tansaction_type];
    }

    /**
     * Creates new account transaction
     *
     * @return obj
     */
    public static function createAccountTransaction($data)
    {
        $transaction_data = [
            'amount' => $data['amount'],
            'account_id' => $data['account_id'],
            'type' => $data['type'],
            'sub_type' => ! empty($data['sub_type']) ? $data['sub_type'] : null,
            'operation_date' => ! empty($data['operation_date']) ? $data['operation_date'] : \Carbon::now(),
            'created_by' => $data['created_by'],
            'transaction_id' => ! empty($data['transaction_id']) ? $data['transaction_id'] : null,
            'transaction_payment_id' => ! empty($data['transaction_payment_id']) ? $data['transaction_payment_id'] : null,
            'note' => ! empty($data['note']) ? $data['note'] : null,
            'transfer_transaction_id' => ! empty($data['transfer_transaction_id']) ? $data['transfer_transaction_id'] : null,
        ];

        $account_transaction = AccountTransaction::create($transaction_data);

        return $account_transaction;
    }

    /**
     * Updates transaction payment from transaction payment
     *
     * @param  obj  $transaction_payment
     * @param  array  $inputs
     * @param  string  $transaction_type
     * @return string
     */
    public static function updateAccountTransaction($transaction_payment, $transaction_type)
    {
        if (! empty($transaction_payment->account_id)) {
            $account_transaction = AccountTransaction::where(
                'transaction_payment_id',
                $transaction_payment->id
            )
                    ->first();
            if (! empty($account_transaction)) {
                $account_transaction->amount = $transaction_payment->amount;
                $account_transaction->account_id = $transaction_payment->account_id;
                $account_transaction->operation_date = $transaction_payment->paid_on;
                $account_transaction->save();

                return $account_transaction;
            } else {
                $accnt_trans_data = [
                    'amount' => $transaction_payment->amount,
                    'account_id' => $transaction_payment->account_id,
                    'type' => empty($transaction_type) ? $transaction_payment->payment_type : self::getAccountTransactionType($transaction_type),
                    'operation_date' => $transaction_payment->paid_on,
                    'created_by' => $transaction_payment->created_by,
                    'transaction_id' => $transaction_payment->transaction_id,
                    'transaction_payment_id' => $transaction_payment->id,
                ];

                //If change return then set type as credit
                if (!empty($transaction_payment->transaction) && in_array($transaction_payment->transaction->type, ['sell', 'hms_booking', 'gym_subscription']) && $transaction_payment->is_return == 1) {
                    $accnt_trans_data['type'] = 'credit';
                }

                self::createAccountTransaction($accnt_trans_data);
            }
        }
    }

    public function transfer_transaction()
    {
        return $this->belongsTo(\App\AccountTransaction::class, 'transfer_transaction_id');
    }

    public function account()
    {
        return $this->belongsTo(\App\Account::class, 'account_id');
    }
}
