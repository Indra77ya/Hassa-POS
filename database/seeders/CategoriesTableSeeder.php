<?php

namespace Database\Seeders;

use App\Business;
use App\Category;
use App\User;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
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

        // 1. Main Categories (15 items)
        $main_categories = [
            ['Makanan & Minuman', 'F&B'],
            ['Pakaian Pria', 'PM'],
            ['Pakaian Wanita', 'PW'],
            ['Elektronik', 'ELC'],
            ['Kecantikan & Perawatan', 'KCT'],
            ['Kesehatan', 'KSH'],
            ['Perlengkapan Rumah', 'PRT'],
            ['Otomotif', 'OTM'],
            ['Olahraga & Outdoor', 'OLR'],
            ['Mainan & Hobi', 'MNN'],
            ['Alat Tulis & Kantor', 'ATK'],
            ['Bahan Bangunan', 'BHN'],
            ['Perhiasan & Aksesoris', 'PHS'],
            ['Komputer & Laptop', 'CMP'],
            ['Handphone & Aksesoris', 'HP'],
        ];

        $created_parents = [];

        foreach ($main_categories as $mc) {
            $category = Category::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $mc[0],
                    'parent_id' => 0
                ],
                [
                    'short_code' => $mc[1],
                    'created_by' => $created_by,
                    'category_type' => 'product'
                ]
            );
            $created_parents[$mc[0]] = $category->id;
        }

        // 2. Sub Categories (15 items)
        $sub_categories = [
            ['Makanan Instan', 'MI', 'Makanan & Minuman'],
            ['Minuman Dingin', 'MD', 'Makanan & Minuman'],
            ['Kaos Pria', 'KP', 'Pakaian Pria'],
            ['Celana Jeans Pria', 'CJP', 'Pakaian Pria'],
            ['Gamis Wanita', 'GW', 'Pakaian Wanita'],
            ['Smartphone', 'SP', 'Handphone & Aksesoris'],
            ['Laptop Gaming', 'LG', 'Komputer & Laptop'],
            ['Oli Mesin', 'OLI', 'Otomotif'],
            ['Skincare', 'SKC', 'Kecantikan & Perawatan'],
            ['Vitamin & Suplemen', 'VIT', 'Kesehatan'],
            ['Alat Kebersihan', 'ALB', 'Perlengkapan Rumah'],
            ['Sepatu Olahraga', 'SPO', 'Olahraga & Outdoor'],
            ['Action Figure', 'AF', 'Mainan & Hobi'],
            ['Kertas & Buku', 'KRB', 'Alat Tulis & Kantor'],
            ['Cat Tembok', 'CT', 'Bahan Bangunan'],
        ];

        foreach ($sub_categories as $sc) {
            $parent_id = $created_parents[$sc[2]] ?? 0;
            Category::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $sc[0],
                    'parent_id' => $parent_id
                ],
                [
                    'short_code' => $sc[1],
                    'created_by' => $created_by,
                    'category_type' => 'product'
                ]
            );
        }
    }
}
