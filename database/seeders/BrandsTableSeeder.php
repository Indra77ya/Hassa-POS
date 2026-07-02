<?php

namespace Database\Seeders;

use App\Brands;
use App\Business;
use App\User;
use Illuminate\Database\Seeder;

class BrandsTableSeeder extends Seeder
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

        $brands = [
            // Electronics
            ['Samsung', 'Elektronik dan Gadget Terkemuka'],
            ['Sony', 'Perangkat Audio dan Video Berkualitas'],
            ['Apple', 'Gadget Premium dan Eksklusif'],
            ['LG', 'Peralatan Rumah Tangga dan Panel TV'],
            ['Asus', 'Laptop dan Komponen Komputer'],

            // F&B
            ['Indofood', 'Produsen Makanan Olahan Terbesar'],
            ['Nestle', 'Nutrisi, Kesehatan, dan Kesejahteraan'],
            ['Unilever', 'Produk Konsumen Cepat Saji'],
            ['Mayora', 'Makanan Ringan dan Minuman Kemasan'],
            ['Wings', 'Produk Kebersihan dan Makanan'],

            // Fashion
            ['Nike', 'Pakaian dan Sepatu Olahraga'],
            ['Adidas', 'Peralatan Olahraga Global'],
            ['Uniqlo', 'Pakaian Kasual Modern'],
            ['Zara', 'Tren Fashion Terkini'],
            ['Batik Keris', 'Warisan Budaya Indonesia'],

            // Automotive
            ['Toyota', 'Kendaraan Roda Empat Terpercaya'],
            ['Honda', 'Sepeda Motor dan Mobil Inovatif'],
            ['Yamaha', 'Kualitas Mesin Sepeda Motor Unggul'],

            // Healthcare / Beauty
            ['Wardah', 'Kosmetik Halal Indonesia'],
            ['Biore', 'Perawatan Kulit dan Wajah'],
        ];

        foreach ($brands as $b) {
            Brands::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $b[0]
                ],
                [
                    'description' => $b[1],
                    'created_by' => $created_by
                ]
            );
        }
    }
}
