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

            if ($at->sub_type === 'fund_transfer') {
                self::syncFundTransferToAccounting($at);
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

            if ($at->sub_type === 'fund_transfer') {
                self::syncFundTransferToAccounting($at);
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
                    $mapping_id = $aat->acc_trans_mapping_id;
                    $aat->delete();

                    if (!empty($mapping_id)) {
                        $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::find($mapping_id);
                        if ($mapping && $mapping->type === 'transfer') {
                            $mapping->delete();
                            $partner_aat = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping_id)->first();
                            if ($partner_aat) {
                                $partner_at_id = $partner_aat->account_transaction_id;
                                $partner_aat->delete();
                                if (!empty($partner_at_id)) {
                                    $partner_at = \App\AccountTransaction::find($partner_at_id);
                                    if ($partner_at) {
                                        $partner_at->delete();
                                    }
                                }
                            }
                        }
                    }
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

    public static function syncFundTransferToAccounting($at)
    {
        // Check if mapping table exists and it's a fund transfer with partner linked
        if ($at->sub_type !== 'fund_transfer' || empty($at->transfer_transaction_id)) {
            return;
        }

        // Avoid infinite loops
        if (self::$is_syncing || (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class) && \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing)) {
            return;
        }

        try {
            self::$is_syncing = true;
            if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = true;
            }

            // Retrieve both transactions
            $tx1 = $at;
            $tx2 = \App\AccountTransaction::find($at->transfer_transaction_id);

            if (!$tx2) {
                return;
            }

            // Get business_id from account
            $account1 = \App\Account::find($tx1->account_id);
            $account2 = \App\Account::find($tx2->account_id);

            if (!$account1 || !$account2) {
                return;
            }

            $business_id = $account1->business_id;

            // Ensure both have their respective AccountingAccountsTransaction created.
            $aat1 = null;
            if (!empty($tx1->accounting_accounts_transaction_id)) {
                $aat1 = \Modules\Accounting\Entities\AccountingAccountsTransaction::find($tx1->accounting_accounts_transaction_id);
            }
            if (!$aat1) {
                $aat1 = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('account_transaction_id', $tx1->id)->first();
            }

            $aat2 = null;
            if (!empty($tx2->accounting_accounts_transaction_id)) {
                $aat2 = \Modules\Accounting\Entities\AccountingAccountsTransaction::find($tx2->accounting_accounts_transaction_id);
            }
            if (!$aat2) {
                $aat2 = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('account_transaction_id', $tx2->id)->first();
            }

            // If either of them is not created yet, create it now!
            if (!$aat1 && !empty($account1->accounting_account_id)) {
                $aat1 = \Modules\Accounting\Entities\AccountingAccountsTransaction::create([
                    'accounting_account_id' => $account1->accounting_account_id,
                    'transaction_id' => $tx1->transaction_id,
                    'transaction_payment_id' => $tx1->transaction_payment_id,
                    'amount' => $tx1->amount,
                    'type' => $tx1->type,
                    'sub_type' => 'fund_transfer',
                    'created_by' => $tx1->created_by ?? 1,
                    'operation_date' => $tx1->operation_date,
                    'note' => $tx1->note,
                    'account_transaction_id' => $tx1->id,
                ]);
                if ($aat1) {
                    $tx1->accounting_accounts_transaction_id = $aat1->id;
                    $tx1->save();
                }
            }

            if (!$aat2 && !empty($account2->accounting_account_id)) {
                $aat2 = \Modules\Accounting\Entities\AccountingAccountsTransaction::create([
                    'accounting_account_id' => $account2->accounting_account_id,
                    'transaction_id' => $tx2->transaction_id,
                    'transaction_payment_id' => $tx2->transaction_payment_id,
                    'amount' => $tx2->amount,
                    'type' => $tx2->type,
                    'sub_type' => 'fund_transfer',
                    'created_by' => $tx2->created_by ?? 1,
                    'operation_date' => $tx2->operation_date,
                    'note' => $tx2->note,
                    'account_transaction_id' => $tx2->id,
                ]);
                if ($aat2) {
                    $tx2->accounting_accounts_transaction_id = $aat2->id;
                    $tx2->save();
                }
            }

            if (!$aat1 || !$aat2) {
                return;
            }

            // Find or create the AccountingAccTransMapping
            $mapping = null;
            if (!empty($aat1->acc_trans_mapping_id)) {
                $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::find($aat1->acc_trans_mapping_id);
            }
            if (!$mapping && !empty($aat2->acc_trans_mapping_id)) {
                $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::find($aat2->acc_trans_mapping_id);
            }

            if (!$mapping) {
                $ref_no = null;
                if (class_exists(\Modules\Accounting\Utils\AccountingUtil::class) && \Illuminate\Support\Facades\Schema::hasTable('business')) {
                    $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil(new \App\Utils\Util());
                    $accounting_settings = $accountingUtil->getAccountingSettings($business_id);
                    $prefix = !empty($accounting_settings['transfer_prefix']) ? $accounting_settings['transfer_prefix'] : '';
                    $ref_count = (new \App\Utils\Util())->setAndGetReferenceCount('accounting_transfer');
                    $ref_no = (new \App\Utils\Util())->generateReferenceNumber('accounting_transfer', $ref_count, $business_id, $prefix);
                } else {
                    $ref_no = 'TRX-' . time() . '-' . rand(100, 999);
                }

                $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::create([
                    'business_id' => $business_id,
                    'ref_no' => $ref_no,
                    'note' => $tx1->note ?? $tx2->note,
                    'type' => 'transfer',
                    'created_by' => $tx1->created_by ?? 1,
                    'operation_date' => $tx1->operation_date,
                ]);
            } else {
                $mapping->update([
                    'note' => $tx1->note ?? $tx2->note,
                    'operation_date' => $tx1->operation_date,
                ]);
            }

            // Link the mapping to both transactions
            $aat1->update([
                'acc_trans_mapping_id' => $mapping->id,
                'sub_type' => 'fund_transfer',
            ]);
            $aat2->update([
                'acc_trans_mapping_id' => $mapping->id,
                'sub_type' => 'fund_transfer',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in syncFundTransferToAccounting: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
        } finally {
            self::$is_syncing = false;
            if (class_exists(\Modules\Accounting\Entities\AccountingAccountsTransaction::class)) {
                \Modules\Accounting\Entities\AccountingAccountsTransaction::$is_syncing = false;
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
