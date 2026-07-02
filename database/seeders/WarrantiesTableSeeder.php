<?php

namespace Database\Seeders;

use App\Business;
use App\Warranty;
use Illuminate\Database\Seeder;

class WarrantiesTableSeeder extends Seeder
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

        $warranties = [
            ['Garansi 1 Minggu', 'Garansi penggantian unit baru jika ada kerusakan pabrik dalam 7 hari.', 7, 'days'],
            ['Garansi 1 Bulan', 'Garansi servis dan suku cadang selama 30 hari.', 1, 'months'],
            ['Garansi 3 Bulan', 'Garansi terbatas untuk perbaikan komponen utama.', 3, 'months'],
            ['Garansi 6 Bulan', 'Garansi standar untuk perangkat elektronik.', 6, 'months'],
            ['Garansi 1 Tahun', 'Garansi resmi distributor untuk servis dan suku cadang.', 1, 'years'],
            ['Garansi 2 Tahun', 'Garansi extended untuk produk premium.', 2, 'years'],
            ['Garansi 5 Tahun', 'Garansi jangka panjang untuk komponen tertentu (seperti HDD/SSD).', 5, 'years'],
            ['Garansi Seumur Hidup', 'Garansi seumur hidup untuk produk tertentu (seperti memori RAM).', 99, 'years'],
        ];

        foreach ($warranties as $w) {
            Warranty::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $w[0]
                ],
                [
                    'description' => $w[1],
                    'duration' => $w[2],
                    'duration_type' => $w[3]
                ]
            );
        }
    }
}
