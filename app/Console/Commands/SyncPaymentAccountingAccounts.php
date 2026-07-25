<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Account;
use App\AccountTransaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use DB;

class SyncPaymentAccountingAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:sync-payment-accounting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all existing Payment Accounts and their transactions with the Accounting module bidirectionally.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting Payment Accounts & Accounting module synchronization...');

        try {
            DB::beginTransaction();

            // Disable model listener syncing to prevent loop interference during bulk migration
            Account::$is_syncing = true;
            if (class_exists(AccountingAccount::class)) {
                AccountingAccount::$is_syncing = true;
            }
            AccountTransaction::$is_syncing = true;
            if (class_exists(AccountingAccountsTransaction::class)) {
                AccountingAccountsTransaction::$is_syncing = true;
            }

            // 1. Sync from Payment Accounts to Accounting Accounts
            $accounts = Account::all();
            $this->info('Processing ' . $accounts->count() . ' payment accounts...');

            foreach ($accounts as $account) {
                if (!class_exists(AccountingAccount::class)) {
                    continue;
                }

                $accounting_account = null;
                if (!empty($account->accounting_account_id)) {
                    $accounting_account = AccountingAccount::find($account->accounting_account_id);
                }

                if (!$accounting_account) {
                    // Match by business_id and name
                    $accounting_account = AccountingAccount::where('business_id', $account->business_id)
                        ->where('name', $account->name)
                        ->first();
                }

                if (!$accounting_account) {
                    $this->info("Creating Accounting Account for: {$account->name} (Business ID: {$account->business_id})");
                    $accounting_account = AccountingAccount::create([
                        'name' => $account->name,
                        'business_id' => $account->business_id,
                        'created_by' => $account->created_by ?? 1,
                        'description' => $account->note,
                        'gl_code' => $account->account_number,
                        'status' => $account->is_closed ? 'inactive' : 'active',
                        'account_primary_type' => 'asset',
                        'account_sub_type_id' => 3, // Cash and cash equivalents
                        'account_id' => $account->id,
                    ]);
                } else {
                    $this->info("Linking existing Accounting Account for: {$account->name}");
                    $accounting_account->update([
                        'account_id' => $account->id,
                    ]);
                }

                $account->accounting_account_id = $accounting_account->id;
                $account->save();
            }

            // 2. Sync from Accounting Accounts to Payment Accounts (for Cash and Cash equivalents)
            if (class_exists(AccountingAccount::class)) {
                $accounting_accounts = AccountingAccount::where('account_primary_type', 'asset')
                    ->where('account_sub_type_id', 3)
                    ->get();

                $this->info('Processing ' . $accounting_accounts->count() . ' cash and cash equivalent accounting accounts...');

                foreach ($accounting_accounts as $aa) {
                    $account = null;
                    if (!empty($aa->account_id)) {
                        $account = Account::find($aa->account_id);
                    }

                    if (!$account) {
                        $account = Account::where('business_id', $aa->business_id)
                            ->where('name', $aa->name)
                            ->first();
                    }

                    if (!$account) {
                        $this->info("Creating Payment Account for: {$aa->name} (Business ID: {$aa->business_id})");
                        $account = Account::create([
                            'name' => $aa->name,
                            'business_id' => $aa->business_id,
                            'created_by' => $aa->created_by ?? 1,
                            'note' => $aa->description,
                            'account_number' => $aa->gl_code,
                            'is_closed' => $aa->status == 'active' ? 0 : 1,
                            'accounting_account_id' => $aa->id,
                        ]);
                    } else {
                        $this->info("Linking existing Payment Account for: {$aa->name}");
                        $account->update([
                            'accounting_account_id' => $aa->id,
                        ]);
                    }

                    $aa->account_id = $account->id;
                    $aa->save();
                }
            }

            // 3. Sync Historical Transactions
            if (class_exists(AccountingAccountsTransaction::class)) {
                $this->info('Syncing transactions...');

                // Sync from AccountTransaction to AccountingAccountsTransaction
                $transactions = AccountTransaction::all();
                foreach ($transactions as $at) {
                    $account = Account::find($at->account_id);
                    if ($account && !empty($account->accounting_account_id)) {
                        $aat = null;
                        if (!empty($at->accounting_accounts_transaction_id)) {
                            $aat = AccountingAccountsTransaction::find($at->accounting_accounts_transaction_id);
                        }

                        if (!$aat) {
                            // Find matching transaction
                            $aatQuery = AccountingAccountsTransaction::where('accounting_account_id', $account->accounting_account_id)
                                ->where('amount', $at->amount)
                                ->where('type', $at->type);

                            if (!empty($at->transaction_id)) {
                                $aatQuery->where('transaction_id', $at->transaction_id);
                            }
                            if (!empty($at->transaction_payment_id)) {
                                $aatQuery->where('transaction_payment_id', $at->transaction_payment_id);
                            }

                            $aat = $aatQuery->first();
                        }

                        if (!$aat) {
                            $aat = AccountingAccountsTransaction::create([
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
                        } else {
                            $aat->update([
                                'account_transaction_id' => $at->id,
                            ]);
                        }

                        $at->accounting_accounts_transaction_id = $aat->id;
                        $at->save();
                    }
                }

                // Sync from AccountingAccountsTransaction to AccountTransaction
                $accounting_txs = AccountingAccountsTransaction::all();
                foreach ($accounting_txs as $aat) {
                    $aa = AccountingAccount::find($aat->accounting_account_id);
                    if ($aa && !empty($aa->account_id)) {
                        $at = null;
                        if (!empty($aat->account_transaction_id)) {
                            $at = AccountTransaction::find($aat->account_transaction_id);
                        }

                        if (!$at) {
                            $atQuery = AccountTransaction::where('account_id', $aa->account_id)
                                ->where('amount', $aat->amount)
                                ->where('type', $aat->type);

                            if (!empty($aat->transaction_id)) {
                                $atQuery->where('transaction_id', $aat->transaction_id);
                            }
                            if (!empty($aat->transaction_payment_id)) {
                                $atQuery->where('transaction_payment_id', $aat->transaction_payment_id);
                            }

                            $at = $atQuery->first();
                        }

                        if (!$at) {
                            $at = AccountTransaction::create([
                                'account_id' => $aa->account_id,
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
                        } else {
                            $at->update([
                                'accounting_accounts_transaction_id' => $aat->id,
                            ]);
                        }

                        $aat->account_transaction_id = $at->id;
                        $aat->save();
                    }
                }
            }

            DB::commit();
            $this->info('Bidirectional Payment Accounts & Accounting module synchronization completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Sync failed: ' . $e->getMessage());
            \Log::error('Sync error: ' . $e->getTraceAsString());
        } finally {
            // Re-enable model syncing
            Account::$is_syncing = false;
            if (class_exists(AccountingAccount::class)) {
                AccountingAccount::$is_syncing = false;
            }
            AccountTransaction::$is_syncing = false;
            if (class_exists(AccountingAccountsTransaction::class)) {
                AccountingAccountsTransaction::$is_syncing = false;
            }
        }
    }
}
