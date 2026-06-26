<?php

namespace Database\Seeders;

use App\AccountType;
use App\Business;
use Illuminate\Database\Seeder;

class AccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $businesses = Business::all();

        $default_types = [
            ['key' => 'kas_dan_bank', 'parent' => null],
            ['key' => 'piutang_usaha', 'parent' => null],
            ['key' => 'persediaan', 'parent' => null],
            ['key' => 'aktiva_lancar_lainnya', 'parent' => null],
            ['key' => 'aktiva_tetap', 'parent' => null],
            ['key' => 'akumulasi_penyusutan', 'parent' => null],
            ['key' => 'aktiva_lainnya', 'parent' => null],
            ['key' => 'hutang_usaha', 'parent' => null],
            ['key' => 'hutang_lancar_lainnya', 'parent' => null],
            ['key' => 'hutang_jangka_panjang', 'parent' => null],
            ['key' => 'ekuitas', 'parent' => null],
            ['key' => 'pendapatan_usaha', 'parent' => null],
            ['key' => 'pendapatan_lainnya', 'parent' => null],
            ['key' => 'harga_pokok_penjualan', 'parent' => null],
            ['key' => 'beban_operasional', 'parent' => null],
            ['key' => 'beban_lain_lain', 'parent' => null],
            ['key' => 'beban_pajak', 'parent' => null],
        ];

        foreach ($businesses as $business) {
            foreach ($default_types as $at) {
                $translated_name = __('account.' . $at['key'], [], 'id');

                $exists = AccountType::where('business_id', $business->id)
                                     ->where('fixed_key', $at['key'])
                                     ->first();

                if (! $exists) {
                    AccountType::create([
                        'name' => $translated_name,
                        'business_id' => $business->id,
                        'parent_account_type_id' => null,
                        'fixed_key' => $at['key']
                    ]);
                } else {
                    $exists->update(['name' => $translated_name]);
                }
            }
        }
    }
}
