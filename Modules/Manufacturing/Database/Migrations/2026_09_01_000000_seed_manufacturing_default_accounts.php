<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Business;
use App\AccountType;
use App\Account;
use Modules\Accounting\Entities\AccountingAccount;

class SeedManufacturingDefaultAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            $business_id = $business->id;

            // 1. Seed POS Account Types if missing
            $type_persediaan = AccountType::where('business_id', $business_id)
                ->where('fixed_key', 'persediaan')
                ->first();
            if (!$type_persediaan) {
                $type_persediaan = AccountType::create([
                    'name' => __('account.persediaan'),
                    'business_id' => $business_id,
                    'parent_account_type_id' => null,
                    'fixed_key' => 'persediaan'
                ]);
            }

            $type_beban = AccountType::where('business_id', $business_id)
                ->where('fixed_key', 'beban_operasional')
                ->first();
            if (!$type_beban) {
                $type_beban = AccountType::create([
                    'name' => __('account.beban_operasional'),
                    'business_id' => $business_id,
                    'parent_account_type_id' => null,
                    'fixed_key' => 'beban_operasional'
                ]);
            }

            // 2. Seed POS Accounts (accounts table)
            $pos_mfg_accounts = [
                ['name' => 'Persediaan Bahan Baku', 'type_id' => $type_persediaan->id, 'number' => '1302', 'balance' => 'debit'],
                ['name' => 'Persediaan Barang Jadi', 'type_id' => $type_persediaan->id, 'number' => '1303', 'balance' => 'debit'],
                ['name' => 'Biaya Produksi / Overhead', 'type_id' => $type_beban->id, 'number' => '6105', 'balance' => 'debit'],
            ];

            foreach ($pos_mfg_accounts as $da) {
                $exists = Account::where('business_id', $business_id)
                    ->where('name', $da['name'])
                    ->first();
                if (!$exists) {
                    Account::create([
                        'name' => $da['name'],
                        'business_id' => $business_id,
                        'account_number' => $da['number'],
                        'account_type_id' => $da['type_id'],
                        'normal_balance' => $da['balance'],
                        'created_by' => 1
                    ]);
                }
            }

            // 3. Seed Accounting Module Accounts (accounting_accounts table)
            if (Schema::hasTable('accounting_accounts')) {
                $accounting_mfg_accounts = [
                    [
                        'name' => 'Persediaan Bahan Baku',
                        'business_id' => $business_id,
                        'account_primary_type' => 'asset',
                        'account_sub_type_id' => 2,
                        'detail_type_id' => 21,
                        'gl_code' => '1302',
                        'status' => 'active',
                        'created_by' => 1,
                    ],
                    [
                        'name' => 'Persediaan Barang Jadi',
                        'business_id' => $business_id,
                        'account_primary_type' => 'asset',
                        'account_sub_type_id' => 2,
                        'detail_type_id' => 21,
                        'gl_code' => '1303',
                        'status' => 'active',
                        'created_by' => 1,
                    ],
                    [
                        'name' => 'Biaya Produksi / Overhead',
                        'business_id' => $business_id,
                        'account_primary_type' => 'expenses',
                        'account_sub_type_id' => 14,
                        'detail_type_id' => 138,
                        'gl_code' => '6105',
                        'status' => 'active',
                        'created_by' => 1,
                    ],
                ];

                foreach ($accounting_mfg_accounts as $account) {
                    $exists = AccountingAccount::where('business_id', $business_id)
                        ->where('name', $account['name'])
                        ->exists();
                    if (!$exists) {
                        AccountingAccount::create($account);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
