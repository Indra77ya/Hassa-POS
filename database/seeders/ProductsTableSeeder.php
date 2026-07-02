<?php

namespace Database\Seeders;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\ProductVariation;
use App\Unit;
use App\User;
use App\Variation;
use App\VariationTemplate;
use App\VariationValueTemplate;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
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

        $location = BusinessLocation::where('business_id', $business->id)->first();
        $user = User::where('business_id', $business->id)->first();
        $created_by = $user ? $user->id : 1;

        // Create Variation Template
        $size_template = VariationTemplate::firstOrCreate(
            ['name' => 'Ukuran', 'business_id' => $business->id]
        );
        $sizes = ['S', 'M', 'L'];
        foreach ($sizes as $s) {
            VariationValueTemplate::firstOrCreate(
                ['name' => $s, 'variation_template_id' => $size_template->id]
            );
        }

        // Helper to get ID
        $getUnit = function($name) use ($business) {
            return Unit::where('business_id', $business->id)->where('actual_name', $name)->first()->id ?? 1;
        };
        $getCat = function($name) use ($business) {
            return Category::where('business_id', $business->id)->where('name', $name)->first()->id ?? null;
        };
        $getBrand = function($name) use ($business) {
            return Brands::where('business_id', $business->id)->where('name', $name)->first()->id ?? null;
        };

        $products_data = [
            // [Nama Produk, Kategori, Brand, Unit, Tipe, Harga Jual]

            // F&B
            ['Beras Premium 5kg', 'Makanan & Minuman', 'Indofood', 'Pak', 'single', 65000],
            ['Minyak Goreng 2L', 'Makanan & Minuman', 'Indofood', 'Liter', 'single', 35000],
            ['Gula Pasir 1kg', 'Makanan Instan', 'Indofood', 'Kilogram', 'single', 15000],
            ['Mie Instan Goreng', 'Makanan Instan', 'Indofood', 'Pak', 'single', 3000],
            ['Kopi Bubuk 200g', 'Makanan & Minuman', 'Mayora', 'Gram', 'single', 25000],
            ['Biskuit Kaleng', 'Makanan & Minuman', 'Mayora', 'Kaleng', 'single', 45000],
            ['Susu UHT 1L', 'Minuman Dingin', 'Nestle', 'Kotak', 'single', 18000],

            // Pakaian
            ['Kaos Polos Cotton', 'Kaos Pria', 'Uniqlo', 'Buah', 'variable', 75000],
            ['Celana Chino Pria', 'Celana Jeans Pria', 'Uniqlo', 'Buah', 'variable', 199000],
            ['Kemeja Flanel', 'Pakaian Pria', 'Uniqlo', 'Buah', 'variable', 250000],
            ['Gamis Wanita Modern', 'Gamis Wanita', 'Batik Keris', 'Buah', 'variable', 299000],
            ['Jaket Bomber', 'Pakaian Pria', 'Zara', 'Buah', 'variable', 299000],

            // Elektronik & Gadget
            ['Lampu LED 10W', 'Elektronik', 'LG', 'Buah', 'single', 45000],
            ['Baterai AA isi 4', 'Elektronik', 'Sony', 'Pak', 'single', 25000],
            ['Smartphone Case', 'Handphone & Aksesoris', 'Apple', 'Buah', 'single', 150000],
            ['Kabel Data Type-C', 'Handphone & Aksesoris', 'Samsung', 'Buah', 'single', 85000],
            ['Mouse Wireless', 'Komputer & Laptop', 'Asus', 'Buah', 'single', 125000],
            ['Earphone Wired', 'Handphone & Aksesoris', 'Sony', 'Buah', 'single', 199000],

            // Kecantikan & Kesehatan
            ['Sabun Mandi Batang', 'Kecantikan & Perawatan', 'Unilever', 'Buah', 'single', 5000],
            ['Shampoo 170ml', 'Kecantikan & Perawatan', 'Unilever', 'Botol', 'single', 25000],
            ['Pasta Gigi 190g', 'Kecantikan & Perawatan', 'Unilever', 'Buah', 'single', 15000],
            ['Skincare Serum', 'Skincare', 'Wardah', 'Botol', 'single', 120000],
            ['Vitamin C 1000mg', 'Vitamin & Suplemen', 'Nestle', 'Botol', 'single', 50000],

            // Otomotif
            ['Oli Mesin Matic', 'Oli Mesin', 'Yamaha', 'Botol', 'single', 65000],
            ['Busi Motor', 'Otomotif', 'Honda', 'Buah', 'single', 25000],
            ['Lap Chamois', 'Otomotif', 'Toyota', 'Buah', 'single', 35000],

            // ATK & Lainnya
            ['Buku Tulis isi 38', 'Kertas & Buku', 'Indofood', 'Buah', 'single', 5000],
            ['Pulpen Hitam', 'Alat Tulis & Kantor', 'Mayora', 'Buah', 'single', 3000],
            ['Sepatu Sneakers', 'Sepatu Olahraga', 'Nike', 'Pasang', 'variable', 299000],
            ['Pembersih Lantai 750ml', 'Alat Kebersihan', 'Unilever', 'Botol', 'single', 15000],
        ];

        foreach ($products_data as $index => $pd) {
            $sku = 'PROD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $product = Product::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $pd[0]
                ],
                [
                    'type' => $pd[4],
                    'unit_id' => $getUnit($pd[3]),
                    'brand_id' => $getBrand($pd[2]),
                    'category_id' => $getCat($pd[1]),
                    'tax_type' => 'exclusive',
                    'enable_stock' => 1,
                    'alert_quantity' => 5,
                    'sku' => $sku,
                    'barcode_type' => 'C128',
                    'created_by' => $created_by
                ]
            );

            if ($location) {
                $product->product_locations()->syncWithoutDetaching([$location->id]);
            }

            if ($pd[4] == 'single') {
                $product_variation = ProductVariation::firstOrCreate(
                    ['name' => 'DUMMY', 'product_id' => $product->id, 'is_dummy' => 1]
                );

                $purchase_price = $pd[5] * 0.8;
                $sell_price = $pd[5];

                Variation::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'product_variation_id' => $product_variation->id,
                        'name' => 'DUMMY'
                    ],
                    [
                        'sub_sku' => $sku,
                        'default_purchase_price' => $purchase_price,
                        'dpp_inc_tax' => $purchase_price,
                        'profit_percent' => 25,
                        'default_sell_price' => $sell_price,
                        'sell_price_inc_tax' => $sell_price
                    ]
                );
            } else {
                $product_variation = ProductVariation::firstOrCreate(
                    ['name' => 'Ukuran', 'product_id' => $product->id, 'is_dummy' => 0]
                );

                foreach ($sizes as $s_index => $s_name) {
                    $purchase_price = $pd[5] * 0.8;
                    $sell_price = $pd[5];

                    Variation::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'product_variation_id' => $product_variation->id,
                            'name' => $s_name
                        ],
                        [
                            'sub_sku' => $sku . '-' . ($s_index + 1),
                            'default_purchase_price' => $purchase_price,
                            'dpp_inc_tax' => $purchase_price,
                            'profit_percent' => 25,
                            'default_sell_price' => $sell_price,
                            'sell_price_inc_tax' => $sell_price
                        ]
                    );
                }
            }
        }
    }
}
