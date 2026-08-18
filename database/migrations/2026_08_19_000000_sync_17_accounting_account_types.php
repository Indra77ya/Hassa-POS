<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Entities\AccountingAccountType;

class Sync17AccountingAccountTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accounting_account_types')) {
            return;
        }

        $sub_types = [
            1 => [
                'name' => 'piutang_usaha',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            2 => [
                'name' => 'persediaan',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            3 => [
                'name' => 'kas_dan_bank',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            4 => [
                'name' => 'aktiva_tetap',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            5 => [
                'name' => 'aktiva_lainnya',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            6 => [
                'name' => 'hutang_usaha',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'liability',
                'parent_id' => null,
            ],
            7 => [
                'name' => 'credit_card',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'liability',
                'parent_id' => null,
            ],
            8 => [
                'name' => 'hutang_lancar_lainnya',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'liability',
                'parent_id' => null,
            ],
            9 => [
                'name' => 'hutang_jangka_panjang',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'liability',
                'parent_id' => null,
            ],
            10 => [
                'name' => 'ekuitas',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'equity',
                'parent_id' => null,
            ],
            11 => [
                'name' => 'pendapatan_usaha',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'income',
                'parent_id' => null,
            ],
            12 => [
                'name' => 'pendapatan_lainnya',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'income',
                'parent_id' => null,
            ],
            13 => [
                'name' => 'harga_pokok_penjualan',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'expenses',
                'parent_id' => null,
            ],
            14 => [
                'name' => 'beban_operasional',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'expenses',
                'parent_id' => null,
            ],
            15 => [
                'name' => 'beban_lain_lain',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'expenses',
                'parent_id' => null,
            ],
            16 => [
                'name' => 'aktiva_lancar_lainnya',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            17 => [
                'name' => 'akumulasi_penyusutan',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null,
            ],
            18 => [
                'name' => 'beban_pajak',
                'show_balance' => 0,
                'account_type' => 'sub_type',
                'account_primary_type' => 'expenses',
                'parent_id' => null,
            ],
        ];

        foreach ($sub_types as $id => $data) {
            $existing = AccountingAccountType::where('id', $id)
                ->where('account_type', 'sub_type')
                ->whereNull('business_id')
                ->first();

            if ($existing) {
                $existing->update([
                    'name' => $data['name'],
                    'show_balance' => $data['show_balance'],
                    'account_primary_type' => $data['account_primary_type'],
                ]);
            } else {
                AccountingAccountType::create([
                    'id' => $id,
                    'name' => $data['name'],
                    'show_balance' => $data['show_balance'],
                    'account_type' => 'sub_type',
                    'account_primary_type' => $data['account_primary_type'],
                    'parent_id' => null,
                    'business_id' => null,
                ]);
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
