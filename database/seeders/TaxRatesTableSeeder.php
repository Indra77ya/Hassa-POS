<?php

namespace Database\Seeders;

use App\Business;
use App\TaxRate;
use App\User;
use Illuminate\Database\Seeder;

class TaxRatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $business = Business::first();
        if (!$business) {
            return;
        }

        $user = User::where('business_id', $business->id)->first();
        $created_by = $user ? $user->id : 1;

        // 1. Individual Tax Rates
        $taxes = [
            ['PPN 11%', 11, 0],
            ['PPh 23 (2%)', 2, 0],
            ['Pajak Restoran (PB1) 10%', 10, 0],
            ['Service Charge 5%', 5, 0],
            ['PPh 21 (5%)', 5, 0],
        ];

        $created_taxes = [];

        foreach ($taxes as $t) {
            $tax = TaxRate::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $t[0]
                ],
                [
                    'amount' => $t[1],
                    'is_tax_group' => 0,
                    'created_by' => $created_by,
                    'for_tax_group' => $t[2]
                ]
            );
            $created_taxes[$t[0]] = $tax;
        }

        // 2. Tax Groups
        $tax_groups = [
            [
                'name' => 'PPN & Service (16%)',
                'sub_taxes' => ['PPN 11%', 'Service Charge 5%']
            ],
            [
                'name' => 'Pajak & Layanan Resto (15%)',
                'sub_taxes' => ['Pajak Restoran (PB1) 10%', 'Service Charge 5%']
            ]
        ];

        foreach ($tax_groups as $tg) {
            $total_amount = 0;
            $sub_tax_ids = [];

            foreach ($tg['sub_taxes'] as $st_name) {
                if (isset($created_taxes[$st_name])) {
                    $total_amount += $created_taxes[$st_name]->amount;
                    $sub_tax_ids[] = $created_taxes[$st_name]->id;
                }
            }

            $group = TaxRate::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $tg['name']
                ],
                [
                    'amount' => $total_amount,
                    'is_tax_group' => 1,
                    'created_by' => $created_by
                ]
            );

            if (!empty($sub_tax_ids)) {
                $group->sub_taxes()->sync($sub_tax_ids);
            }
        }
    }
}
