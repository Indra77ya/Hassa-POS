<?php

use Illuminate\Database\Migrations\Migration;
use App\Business;
use App\Account;
use App\AccountType;
use Modules\Accounting\Entities\AccountingAccount;

class AddBiayaPenyusutanDefaultAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $businesses = Business::all();

        // Pairs of [duplicate_name => primary_name]
        $duplicate_pairs = [
            'Piutang Usaha (A/R)' => 'Piutang Usaha',
            'Hutang Dagang (A/P)' => 'Hutang Usaha',
            'Harga Pokok Penjualan (HPP)' => 'Harga Pokok Penjualan',
            'Persediaan' => 'Persediaan Barang',
            'Beban Utilitas' => 'Beban Listrik & Air',
            'Beban Sewa atau Kontrak' => 'Beban Sewa',
            'Beban Upah' => 'Beban Gaji',
            'Properti, Pabrik & Peralatan' => 'Peralatan',
            'Akumulasi Penyusutan Properti, Pabrik & Peralatan' => 'Akumulasi Penyusutan',
        ];

        foreach ($businesses as $business) {
            $business_id = $business->id;

            // Clean up duplicates if both primary and duplicate exist
            foreach ($duplicate_pairs as $duplicate_name => $primary_name) {
                if (class_exists(AccountingAccount::class)) {
                    $primary_acc = AccountingAccount::where('business_id', $business_id)
                        ->where('name', $primary_name)
                        ->first();

                    $duplicate_acc = AccountingAccount::where('business_id', $business_id)
                        ->where('name', $duplicate_name)
                        ->first();

                    if ($primary_acc && $duplicate_acc && $primary_acc->id != $duplicate_acc->id) {
                        \DB::table('accounting_accounts_transactions')
                            ->where('accounting_account_id', $duplicate_acc->id)
                            ->update(['accounting_account_id' => $primary_acc->id]);

                        $duplicate_acc->delete();
                    }
                }

                $primary_pos = Account::where('business_id', $business_id)
                    ->where('name', $primary_name)
                    ->first();

                $duplicate_pos = Account::where('business_id', $business_id)
                    ->where('name', $duplicate_name)
                    ->first();

                if ($primary_pos && $duplicate_pos && $primary_pos->id != $duplicate_pos->id) {
                    \DB::table('account_transactions')
                        ->where('account_id', $duplicate_pos->id)
                        ->update(['account_id' => $primary_pos->id]);

                    $duplicate_pos->delete();
                }
            }

            // 1. Ensure POS AccountType 'beban_lain_lain' exists
            $type = AccountType::where('business_id', $business_id)
                                ->where('fixed_key', 'beban_lain_lain')
                                ->first();
            if (!$type) {
                $translated_name = __('account.beban_lain_lain');
                $type = AccountType::create([
                    'name' => $translated_name ?: 'Beban Lain-lain',
                    'business_id' => $business_id,
                    'parent_account_type_id' => null,
                    'fixed_key' => 'beban_lain_lain'
                ]);
            }

            // 2. Ensure POS Account 'Biaya Penyusutan' exists
            $posAccount = Account::where('business_id', $business_id)
                                 ->where('name', 'Biaya Penyusutan')
                                 ->first();

            if (!$posAccount) {
                $posAccount = Account::create([
                    'name' => 'Biaya Penyusutan',
                    'business_id' => $business_id,
                    'account_number' => '6105',
                    'account_type_id' => $type->id,
                    'normal_balance' => 'debit',
                    'created_by' => $business->owner_id ?? 1
                ]);
            }

            // 3. Ensure AccountingAccount 'Biaya Penyusutan' exists if Accounting Module installed
            if (class_exists(AccountingAccount::class)) {
                $accountingAccount = AccountingAccount::where('business_id', $business_id)
                                                       ->where('name', 'Biaya Penyusutan')
                                                       ->first();

                if (!$accountingAccount) {
                    AccountingAccount::create([
                        'name' => 'Biaya Penyusutan',
                        'business_id' => $business_id,
                        'account_primary_type' => 'expenses',
                        'account_sub_type_id' => 15,
                        'detail_type_id' => 152,
                        'status' => 'active',
                        'gl_code' => '61005',
                        'created_by' => $business->owner_id ?? 1
                    ]);
                }
            }
        }

        // 4. Run pos:sync-payment-accounting to ensure links are synced
        try {
            \Illuminate\Support\Facades\Artisan::call('pos:sync-payment-accounting');
        } catch (\Exception $e) {
            \Log::error('Error running pos:sync-payment-accounting in AddBiayaPenyusutanDefaultAccount migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback needed for default account seeding
    }
}
