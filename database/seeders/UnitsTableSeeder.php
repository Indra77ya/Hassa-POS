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

        // 1. Base Units
        $base_units = [
            ['Pieces', 'Pcs', 0],
            ['Grams', 'g', 1],
            ['Milliliters', 'ml', 1],
            ['Meters', 'm', 1],
            ['Each', 'ea', 0],
            ['Session', 'session', 0],
            ['Hour', 'hr', 1],
            ['Day', 'day', 0],
            ['Box', 'box', 0],
            ['Pack', 'pack', 0],
            ['Bottle', 'btl', 0],
            ['Can', 'can', 0],
            ['Roll', 'roll', 0],
            ['Pair', 'pair', 0],
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

        // 2. Conversion Units
        $conversion_units = [
            ['Kilograms', 'Kg', 1, 'Grams', 1000],
            ['Liters', 'L', 1, 'Milliliters', 1000],
            ['Centimeters', 'cm', 1, 'Meters', 0.01],
            ['Kilometers', 'Km', 1, 'Meters', 1000],
            ['Dozen', 'dz', 0, 'Pieces', 12],
            ['Gross', 'gs', 0, 'Pieces', 144],
            ['Carton (Large)', 'ctn-l', 0, 'Pieces', 48],
            ['Carton (Small)', 'ctn-s', 0, 'Pieces', 24],
            ['Box of 10', 'box-10', 0, 'Pieces', 10],
            ['Pack of 6', 'pack-6', 0, 'Pieces', 6],
            ['Ream', 'ream', 0, 'Pieces', 500],
            ['Pallet', 'pallet', 0, 'Box', 50],
            ['Bundle', 'bundle', 0, 'Pieces', 20],
            ['Metric Ton', 'MT', 1, 'Kilograms', 1000],
            ['Square Meter', 'sqm', 1, 'Meters', 1], // Just as a placeholder for 1:1 if needed, though usually different dimension
        ];

        foreach ($conversion_units as $cu) {
            $base_unit_name = $cu[3];
            $base_unit_id = null;

            if (isset($created_base_units[$base_unit_name])) {
                $base_unit_id = $created_base_units[$base_unit_name];
            } else {
                // Check if it's already in conversion units created just before (like Metric Ton -> Kilograms)
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
