<?php

namespace Database\Seeders;

use App\Business;
use App\Unit;
use App\User;
use Illuminate\Database\Seeder;

class UnitsTableSeeder extends Seeder
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

        // 1. Base Units (Satuan Dasar)
        $base_units = [
            ['Buah', 'bh', 0],
            ['Gram', 'g', 1],
            ['Mililiter', 'ml', 1],
            ['Meter', 'm', 1],
            ['Pcs', 'pcs', 0],
            ['Sesi', 'sesi', 0],
            ['Jam', 'jam', 1],
            ['Hari', 'hari', 0],
            ['Kotak', 'kotak', 0],
            ['Pak', 'pak', 0],
            ['Botol', 'btl', 0],
            ['Kaleng', 'klng', 0],
            ['Gulung', 'roll', 0],
            ['Pasang', 'psg', 0],
            ['Set', 'set', 0],
        ];

        $created_base_units = [];

        foreach ($base_units as $bu) {
            $unit = Unit::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'actual_name' => $bu[0]
                ],
                [
                    'short_name' => $bu[1],
                    'allow_decimal' => $bu[2],
                    'created_by' => $created_by
                ]
            );
            $created_base_units[$bu[0]] = $unit->id;
        }

        // 2. Conversion Units (Satuan Konversi)
        $conversion_units = [
            ['Kilogram', 'Kg', 1, 'Gram', 1000],
            ['Liter', 'L', 1, 'Mililiter', 1000],
            ['Sentimeter', 'cm', 1, 'Meter', 0.01],
            ['Kilometer', 'Km', 1, 'Meter', 1000],
            ['Lusin', 'lsn', 0, 'Buah', 12],
            ['Kodi', 'kodi', 0, 'Buah', 20],
            ['Gross', 'grs', 0, 'Buah', 144],
            ['Rim', 'ream', 0, 'Buah', 500],
            ['Karton (Besar)', 'ktn-b', 0, 'Buah', 48],
            ['Karton (Kecil)', 'ktn-k', 0, 'Buah', 24],
            ['Box isi 10', 'box-10', 0, 'Buah', 10],
            ['Pak isi 6', 'pak-6', 0, 'Buah', 6],
            ['Palet', 'pallet', 0, 'Kotak', 50],
            ['Ikat', 'ikat', 0, 'Buah', 20],
            ['Ton Metrik', 'Ton', 1, 'Kilogram', 1000],
        ];

        foreach ($conversion_units as $cu) {
            $base_unit_name = $cu[3];
            $base_unit_id = null;

            if (isset($created_base_units[$base_unit_name])) {
                $base_unit_id = $created_base_units[$base_unit_name];
            } else {
                $existing = Unit::where('business_id', $business->id)
                                ->where('actual_name', $base_unit_name)
                                ->first();
                if ($existing) {
                    $base_unit_id = $existing->id;
                }
            }

            Unit::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'actual_name' => $cu[0]
                ],
                [
                    'short_name' => $cu[1],
                    'allow_decimal' => $cu[2],
                    'base_unit_id' => $base_unit_id,
                    'base_unit_multiplier' => $cu[4],
                    'created_by' => $created_by
                ]
            );
        }
    }
}
