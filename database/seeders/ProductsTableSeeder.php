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
use App\VariationLocationDetails;
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

        $units = Unit::where('business_id', $business->id)->pluck('id')->toArray();
        $categories = Category::where('business_id', $business->id)->where('parent_id', 0)->pluck('id')->toArray();
        $brands = Brands::where('business_id', $business->id)->pluck('id')->toArray();

        // Create Variation Template for Variable Products
        $size_template = VariationTemplate::firstOrCreate(
            ['name' => 'Ukuran', 'business_id' => $business->id]
        );
        $sizes = ['S', 'M', 'L'];
        foreach ($sizes as $s) {
            VariationValueTemplate::firstOrCreate(
                ['name' => $s, 'variation_template_id' => $size_template->id]
            );
        }

        $products_data = [
            // Single Products
            ['Beras Premium 5kg', 'Beras kualitas super', 'single'],
            ['Minyak Goreng 2L', 'Minyak sawit jernih', 'single'],
            ['Gula Pasir 1kg', 'Gula tebu pilihan', 'single'],
            ['Garam Meja 250g', 'Garam beryodium', 'single'],
            ['Kecap Manis 520ml', 'Kecap kedelai hitam', 'single'],
            ['Saus Sambal 340ml', 'Pedas mantap', 'single'],
            ['Mie Instan Goreng', 'Rasa original', 'single'],
            ['Susu UHT 1L', 'Susu sapi segar', 'single'],
            ['Kopi Bubuk 200g', 'Kopi arabika asli', 'single'],
            ['Teh Celup isi 25', 'Teh melati wangi', 'single'],
            ['Sabun Cuci Piring 700ml', 'Bersih kesat', 'single'],
            ['Deterjen Bubuk 800g', 'Putih cemerlang', 'single'],
            ['Pembersih Lantai 750ml', 'Aroma pinus', 'single'],
            ['Pasta Gigi 190g', 'Napas segar', 'single'],
            ['Sabun Mandi Batang', 'Aroma bunga', 'single'],
            ['Shampoo 170ml', 'Anti ketombe', 'single'],
            ['Tisu Wajah 250s', 'Lembut di kulit', 'single'],
            ['Air Mineral 600ml', 'Segar alami', 'single'],
            ['Biskuit Kaleng', 'Aneka rasa', 'single'],
            ['Cokelat Batang', 'Milk chocolate', 'single'],
            ['Kertas HVS A4', '70 gram', 'single'],
            ['Buku Tulis isi 38', 'Kualitas bagus', 'single'],
            ['Pulpen Hitam', 'Tinta lancar', 'single'],
            ['Lampu LED 10W', 'Hemat energi', 'single'],
            ['Baterai AA isi 4', 'Tahan lama', 'single'],

            // Variable Products
            ['Kaos Polos Cotton', 'Bahan adem', 'variable'],
            ['Celana Chino Pria', 'Model slimfit', 'variable'],
            ['Kemeja Flanel', 'Motif kotak-kotak', 'variable'],
            ['Sepatu Sneakers', 'Nyaman dipakai', 'variable'],
            ['Jaket Bomber', 'Bahan taslan', 'variable'],
        ];

        foreach ($products_data as $index => $pd) {
            $sku = 'PROD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $product = Product::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $pd[0]
                ],
                [
                    'type' => $pd[2],
                    'unit_id' => !empty($units) ? $units[array_rand($units)] : 1,
                    'brand_id' => !empty($brands) ? $brands[array_rand($brands)] : null,
                    'category_id' => !empty($categories) ? $categories[array_rand($categories)] : null,
                    'tax_type' => 'exclusive',
                    'enable_stock' => 1,
                    'alert_quantity' => 5,
                    'sku' => $sku,
                    'barcode_type' => 'C128',
                    'created_by' => $created_by
                ]
            );

            // Assign locations
            if ($location) {
                $product->product_locations()->syncWithoutDetaching([$location->id]);
            }

            if ($pd[2] == 'single') {
                $product_variation = ProductVariation::firstOrCreate(
                    ['name' => 'DUMMY', 'product_id' => $product->id, 'is_dummy' => 1]
                );

                $purchase_price = rand(5000, 200000);
                $sell_price = $purchase_price * 1.2; // 20% profit
                if ($sell_price > 300000) $sell_price = 300000;

                $variation = Variation::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'product_variation_id' => $product_variation->id,
                        'name' => 'DUMMY'
                    ],
                    [
                        'sub_sku' => $sku,
                        'default_purchase_price' => $purchase_price,
                        'dpp_inc_tax' => $purchase_price,
                        'profit_percent' => 20,
                        'default_sell_price' => $sell_price,
                        'sell_price_inc_tax' => $sell_price
                    ]
                );

                if ($location) {
                    VariationLocationDetails::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'variation_id' => $variation->id,
                            'location_id' => $location->id
                        ],
                        ['qty_available' => rand(50, 100)]
                    );
                }
            } else {
                $product_variation = ProductVariation::firstOrCreate(
                    ['name' => 'Ukuran', 'product_id' => $product->id, 'is_dummy' => 0]
                );

                foreach ($sizes as $s_index => $s_name) {
                    $purchase_price = rand(50000, 250000);
                    $sell_price = $purchase_price * 1.2;
                    if ($sell_price > 300000) $sell_price = 300000;

                    $variation = Variation::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'product_variation_id' => $product_variation->id,
                            'name' => $s_name
                        ],
                        [
                            'sub_sku' => $sku . '-' . ($s_index + 1),
                            'default_purchase_price' => $purchase_price,
                            'dpp_inc_tax' => $purchase_price,
                            'profit_percent' => 20,
                            'default_sell_price' => $sell_price,
                            'sell_price_inc_tax' => $sell_price
                        ]
                    );

                    if ($location) {
                        VariationLocationDetails::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'variation_id' => $variation->id,
                                'location_id' => $location->id
                            ],
                            ['qty_available' => rand(20, 50)]
                        );
                    }
                }
            }
        }
    }
}
