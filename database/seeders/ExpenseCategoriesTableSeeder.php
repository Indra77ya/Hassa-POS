<?php

namespace Database\Seeders;

use App\Business;
use App\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategoriesTableSeeder extends Seeder
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

        $categories = [
            // [Nama, Kode, Parent Name]
            ['Gaji & Upah', 'EXP-001', null],
            ['Sewa Kantor', 'EXP-002', null],
            ['Utilitas (Listrik, Air, Internet)', 'EXP-003', null],
            ['Pemasaran & Iklan', 'EXP-004', null],
            ['Biaya Transportasi', 'EXP-005', null],
            ['Perlengkapan Kantor', 'EXP-006', null],
            ['Pemeliharaan & Perbaikan', 'EXP-007', null],
            ['Pajak & Perizinan', 'EXP-008', null],
            ['Biaya Administrasi Bank', 'EXP-009', null],
            ['Biaya Operasional Lainnya', 'EXP-010', null],

            // Sub-categories
            ['Gaji Karyawan Tetap', 'EXP-001-01', 'Gaji & Upah'],
            ['Bonus & Insentif', 'EXP-001-02', 'Gaji & Upah'],
            ['Tagihan Listrik PLN', 'EXP-003-01', 'Utilitas (Listrik, Air, Internet)'],
            ['Tagihan Air PDAM', 'EXP-003-02', 'Utilitas (Listrik, Air, Internet)'],
            ['Internet & Telepon', 'EXP-003-03', 'Utilitas (Listrik, Air, Internet)'],
            ['Iklan Media Sosial', 'EXP-004-01', 'Pemasaran & Iklan'],
            ['Bensin & Parkir', 'EXP-005-01', 'Biaya Transportasi'],
            ['Servis Kendaraan', 'EXP-007-01', 'Pemeliharaan & Perbaikan'],
        ];

        $created_parents = [];

        foreach ($categories as $c) {
            if ($c[2] === null) {
                $category = ExpenseCategory::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $c[0]
                    ],
                    ['code' => $c[1]]
                );
                $created_parents[$c[0]] = $category->id;
            }
        }

        foreach ($categories as $c) {
            if ($c[2] !== null) {
                $parent_id = $created_parents[$c[2]] ?? null;
                ExpenseCategory::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $c[0]
                    ],
                    [
                        'code' => $c[1],
                        'parent_id' => $parent_id
                    ]
                );
            }
        }
    }
}
