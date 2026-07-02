<?php

namespace Database\Seeders;

use App\Business;
use App\Discount;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DiscountsTableSeeder extends Seeder
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

        $now = Carbon::now();

        $discounts = [
            // Limited Time Discounts
            [
                'name' => 'Promo Awal Bulan',
                'discount_type' => 'percentage',
                'discount_amount' => 10,
                'starts_at' => $now->copy()->startOfMonth(),
                'ends_at' => $now->copy()->startOfMonth()->addDays(7),
            ],
            [
                'name' => 'Cuci Gudang Elektronik',
                'discount_type' => 'fixed',
                'discount_amount' => 50000,
                'starts_at' => $now->copy()->subDays(5),
                'ends_at' => $now->copy()->addDays(10),
            ],
            [
                'name' => 'Flash Sale 12.12',
                'discount_type' => 'percentage',
                'discount_amount' => 50,
                'starts_at' => Carbon::create($now->year, 12, 12, 0, 0, 0),
                'ends_at' => Carbon::create($now->year, 12, 12, 23, 59, 59),
            ],
            [
                'name' => 'Promo Gajian',
                'discount_type' => 'fixed',
                'discount_amount' => 25000,
                'starts_at' => $now->copy()->day(25),
                'ends_at' => $now->copy()->day(25)->endOfMonth(),
            ],
            [
                'name' => 'Diskon Akhir Pekan',
                'discount_type' => 'percentage',
                'discount_amount' => 5,
                'starts_at' => $now->copy()->next(Carbon::SATURDAY),
                'ends_at' => $now->copy()->next(Carbon::SUNDAY)->endOfDay(),
            ],

            // Forever / No Expiry Discounts
            [
                'name' => 'Diskon Member Loyal',
                'discount_type' => 'percentage',
                'discount_amount' => 15,
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'name' => 'Potongan Harga Grosir',
                'discount_type' => 'fixed',
                'discount_amount' => 10000,
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'name' => 'Promo Launching Toko',
                'discount_type' => 'percentage',
                'discount_amount' => 20,
                'starts_at' => $now->copy()->subMonths(1),
                'ends_at' => null,
            ],
            [
                'name' => 'Diskon Karyawan',
                'discount_type' => 'percentage',
                'discount_amount' => 30,
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'name' => 'Subsidi Ongkir',
                'discount_type' => 'fixed',
                'discount_amount' => 15000,
                'starts_at' => null,
                'ends_at' => null,
            ],
        ];

        foreach ($discounts as $d) {
            Discount::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $d['name']
                ],
                [
                    'discount_type' => $d['discount_type'],
                    'discount_amount' => $d['discount_amount'],
                    'starts_at' => $d['starts_at'],
                    'ends_at' => $d['ends_at'],
                    'is_active' => 1,
                    'location_id' => null, // Applicable to all locations
                    'priority' => rand(1, 10)
                ]
            );
        }
    }
}
