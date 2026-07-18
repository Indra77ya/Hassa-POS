<?php

namespace Database\Seeders;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\Discount;
use App\ExpenseCategory;
use App\Product;
use App\ProductVariation;
use App\TaxRate;
use App\Unit;
use App\User;
use App\Variation;
use App\VariationTemplate;
use App\VariationValueTemplate;
use App\Warranty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BusinessTwoSeeder extends Seeder
{
    /**
     * Run the database seeds for Business ID 2.
     *
     * @return void
     */
    public function run()
    {
        // 1. Fetch Business 2
        $business = Business::find(2);
        if (!$business) {
            $this->command->error("Business dengan ID 2 tidak ditemukan!");
            return;
        }

        $location = BusinessLocation::where('business_id', $business->id)->first();
        if (!$location) {
            $this->command->warn("Business Location untuk Business ID 2 tidak ditemukan. Membuat lokasi default...");
            $location_id = \Illuminate\Support\Facades\DB::table('business_locations')->insertGetId([
                'business_id' => $business->id,
                'name' => $business->name . ' Pusat',
                'landmark' => 'Linking Street',
                'country' => 'Indonesia',
                'state' => 'DKI Jakarta',
                'city' => 'Jakarta',
                'zip_code' => '12345',
                'invoice_scheme_id' => 1,
                'invoice_layout_id' => 1,
                'sale_invoice_layout_id' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $location = BusinessLocation::find($location_id);
        }

        $user = User::where('business_id', $business->id)->first();
        $created_by = $user ? $user->id : 4; // Standard owner/admin ID for business 2

        $this->command->info("Memulai seeding data dummy umum untuk Business: {$business->name} (ID: {$business->id})...");

        // 2. Roles & Permissions for Business 2
        $this->command->info("Seeding Roles dan Permissions...");
        $roles_data = [
            [
                'name' => 'Manager',
                'permissions' => [
                    'user.view', 'user.create', 'user.update', 'user.delete',
                    'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
                    'customer.view', 'customer.create', 'customer.update', 'customer.delete',
                    'product.view', 'product.create', 'product.update', 'product.delete',
                    'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete',
                    'sell.view', 'sell.create', 'sell.update', 'sell.delete',
                    'purchase_n_sell_report.view', 'contacts_report.view', 'stock_report.view', 'tax_report.view', 'trending_product_report.view', 'register_report.view', 'sales_representative.view', 'expense_report.view',
                    'brand.view', 'brand.create', 'brand.update', 'brand.delete',
                    'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
                    'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                    'category.view', 'category.create', 'category.update', 'category.delete',
                    'expense.access', 'access_all_locations', 'dashboard.data', 'print_invoice',
                    'view_cash_register', 'close_cash_register', 'account.access', 'crud_all_bookings'
                ]
            ],
            [
                'name' => 'Supervisor',
                'permissions' => [
                    'user.view',
                    'supplier.view', 'supplier.create', 'supplier.update',
                    'customer.view', 'customer.create', 'customer.update',
                    'product.view', 'purchase.view',
                    'sell.view', 'sell.create', 'sell.update',
                    'contacts_report.view', 'stock_report.view', 'sales_representative.view',
                    'dashboard.data', 'print_invoice', 'view_cash_register', 'crud_all_bookings'
                ]
            ],
            [
                'name' => 'Stock Manager',
                'permissions' => [
                    'supplier.view', 'supplier.create', 'supplier.update',
                    'product.view', 'product.create', 'product.update', 'product.delete',
                    'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete',
                    'stock_report.view', 'trending_product_report.view',
                    'brand.view', 'brand.create', 'brand.update', 'brand.delete',
                    'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                    'category.view', 'category.create', 'category.update', 'category.delete',
                    'dashboard.data'
                ]
            ],
            [
                'name' => 'Sales Representative',
                'permissions' => [
                    'customer.view', 'customer.create', 'customer.update',
                    'product.view', 'sell.view', 'sell.create', 'sell.update',
                    'sales_representative.view', 'dashboard.data', 'print_invoice',
                    'view_cash_register', 'close_cash_register'
                ]
            ],
            [
                'name' => 'Accountant',
                'permissions' => [
                    'purchase_n_sell_report.view', 'contacts_report.view', 'tax_report.view', 'register_report.view', 'expense_report.view',
                    'expense.access', 'account.access', 'dashboard.data'
                ]
            ],
            [
                'name' => 'Receptionist',
                'permissions' => [
                    'customer.view', 'customer.create', 'customer.update',
                    'crud_all_bookings', 'dashboard.data'
                ]
            ],
        ];

        foreach ($roles_data as $rd) {
            $role_name = $rd['name'] . '#' . $business->id;

            $role = Role::firstOrCreate([
                'name' => $role_name,
                'business_id' => $business->id,
                'guard_name' => 'web'
            ]);

            $role->syncPermissions($rd['permissions']);
        }

        // 3. Additional Users for Business 2
        $this->command->info("Seeding Users...");
        $password = Hash::make('123456');
        $users_data = [
            [
                'surname' => 'Mr', 'first_name' => 'Andi', 'last_name' => 'Pratama',
                'username' => 'andi_manager_2', 'email' => 'andi.m2@example.com', 'role' => 'Manager'
            ],
            [
                'surname' => 'Ms', 'first_name' => 'Sinta', 'last_name' => 'Dewi',
                'username' => 'sinta_manager_2', 'email' => 'sinta.m2@example.com', 'role' => 'Manager'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Bambang', 'last_name' => 'Sutrisno',
                'username' => 'bambang_super_2', 'email' => 'bambang.s2@example.com', 'role' => 'Supervisor'
            ],
            [
                'surname' => 'Mrs', 'first_name' => 'Fitri', 'last_name' => 'Indriani',
                'username' => 'fitri_super_2', 'email' => 'fitri.s2@example.com', 'role' => 'Supervisor'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Hendra', 'last_name' => 'Wijaya',
                'username' => 'hendra_stock_2', 'email' => 'hendra.s2@example.com', 'role' => 'Stock Manager'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Joko', 'last_name' => 'Widodo',
                'username' => 'joko_sales_2', 'email' => 'joko.s2@example.com', 'role' => 'Sales Representative'
            ],
            [
                'surname' => 'Mrs', 'first_name' => 'Megawati', 'last_name' => 'Soekarno',
                'username' => 'mega_acc_2', 'email' => 'mega.a2@example.com', 'role' => 'Accountant'
            ],
        ];

        foreach ($users_data as $ud) {
            $u = User::firstOrCreate(
                ['username' => $ud['username']],
                [
                    'surname' => $ud['surname'],
                    'first_name' => $ud['first_name'],
                    'last_name' => $ud['last_name'],
                    'email' => $ud['email'],
                    'password' => $password,
                    'business_id' => $business->id,
                    'language' => 'en',
                    'user_type' => 'user'
                ]
            );

            $role_name = $ud['role'] . '#' . $business->id;
            $u->assignRole($role_name);
        }

        // Give location permission
        Permission::firstOrCreate(['name' => 'location.' . $location->id]);
        $owner_user = User::find($created_by);
        if ($owner_user) {
            $owner_user->givePermissionTo('location.' . $location->id);
        }

        // 4. Tax Rates for Business 2
        $this->command->info("Seeding Tax Rates...");
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

        // 5. Expense Categories for Business 2
        $this->command->info("Seeding Expense Categories...");
        $expense_categories = [
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
            ['Gaji Karyawan Tetap', 'EXP-001-01', 'Gaji & Upah'],
            ['Bonus & Insentif', 'EXP-001-02', 'Gaji & Upah'],
            ['Tagihan Listrik PLN', 'EXP-003-01', 'Utilitas (Listrik, Air, Internet)'],
            ['Tagihan Air PDAM', 'EXP-003-02', 'Utilitas (Listrik, Air, Internet)'],
            ['Internet & Telepon', 'EXP-003-03', 'Utilitas (Listrik, Air, Internet)'],
        ];
        $created_parents = [];
        foreach ($expense_categories as $ec) {
            if ($ec[2] === null) {
                $cat = ExpenseCategory::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $ec[0]
                    ],
                    ['code' => $ec[1]]
                );
                $created_parents[$ec[0]] = $cat->id;
            }
        }
        foreach ($expense_categories as $ec) {
            if ($ec[2] !== null) {
                $parent_id = $created_parents[$ec[2]] ?? null;
                ExpenseCategory::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $ec[0]
                    ],
                    [
                        'code' => $ec[1],
                        'parent_id' => $parent_id
                    ]
                );
            }
        }

        // 6. Units for Business 2
        $this->command->info("Seeding Units...");
        $units = [
            ['Pieces', 'Pc(s)', 0],
            ['Packets', 'packets', 0],
            ['Grams', 'g', 1],
            ['Liters', 'ltr', 1],
            ['Box', 'box', 0],
            ['Bottles', 'btl', 0],
            ['Sachet', 'sct', 0],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'actual_name' => $u[0]
                ],
                [
                    'short_name' => $u[1],
                    'allow_decimal' => $u[2],
                    'created_by' => $created_by
                ]
            );
        }

        // 7. Warranties for Business 2
        $this->command->info("Seeding Warranties...");
        $warranties = [
            ['Garansi 1 Minggu', 'Garansi penggantian unit baru jika ada kerusakan pabrik dalam 7 hari.', 7, 'days'],
            ['Garansi 1 Bulan', 'Garansi servis dan suku cadang selama 30 hari.', 1, 'months'],
            ['Garansi 6 Bulan', 'Garansi standar untuk perangkat elektronik.', 6, 'months'],
            ['Garansi 1 Tahun', 'Garansi resmi distributor untuk servis dan suku cadang.', 1, 'years'],
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

        // 8. Brands for Business 2
        $this->command->info("Seeding Brands...");
        $brands = [
            ['Samsung', 'Elektronik dan Gadget Terkemuka'],
            ['Sony', 'Perangkat Audio dan Video Berkualitas'],
            ['Apple', 'Gadget Premium dan Eksklusif'],
            ['Indofood', 'Produsen Makanan Olahan Terbesar'],
            ['Nestle', 'Nutrisi, Kesehatan, dan Kesejahteraan'],
            ['Unilever', 'Produk Konsumen Cepat Saji'],
            ['Mayora', 'Makanan Ringan dan Minuman Kemasan'],
            ['Zara', 'Tren Fashion Terkini'],
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

        // 9. Categories for Business 2
        $this->command->info("Seeding Categories...");
        $main_categories = [
            ['Makanan & Minuman', 'F&B'],
            ['Pakaian Pria', 'PM'],
            ['Elektronik', 'ELC'],
            ['Kecantikan & Perawatan', 'KCT'],
            ['Kesehatan', 'KSH'],
            ['Alat Tulis & Kantor', 'ATK'],
            ['Komputer & Laptop', 'CMP'],
        ];
        $created_parent_categories = [];
        foreach ($main_categories as $mc) {
            $cat = Category::firstOrCreate(
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
            $created_parent_categories[$mc[0]] = $cat->id;
        }

        $sub_categories = [
            ['Makanan Instan', 'MI', 'Makanan & Minuman'],
            ['Minuman Dingin', 'MD', 'Makanan & Minuman'],
            ['Kaos Pria', 'KP', 'Pakaian Pria'],
            ['Skincare', 'SKC', 'Kecantikan & Perawatan'],
            ['Vitamin & Suplemen', 'VIT', 'Kesehatan'],
            ['Kertas & Buku', 'KRB', 'Alat Tulis & Kantor'],
        ];
        foreach ($sub_categories as $sc) {
            $parent_id = $created_parent_categories[$sc[2]] ?? 0;
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

        // 10. Products & Variations for Business 2
        $this->command->info("Seeding Products and Variations...");
        // Create standard variation template "Ukuran" for business 2
        $size_template = VariationTemplate::firstOrCreate(
            ['name' => 'Ukuran', 'business_id' => $business->id]
        );
        $sizes = ['S', 'M', 'L'];
        foreach ($sizes as $s) {
            VariationValueTemplate::firstOrCreate(
                ['name' => $s, 'variation_template_id' => $size_template->id]
            );
        }

        $getUnit = function($name) use ($business) {
            return Unit::where('business_id', $business->id)->where('actual_name', $name)->first()->id ?? null;
        };
        $getCat = function($name) use ($business) {
            return Category::where('business_id', $business->id)->where('name', $name)->first()->id ?? null;
        };
        $getBrand = function($name) use ($business) {
            return Brands::where('business_id', $business->id)->where('name', $name)->first()->id ?? null;
        };

        $products_data = [
            ['Beras Premium 5kg', 'Makanan & Minuman', 'Indofood', 'Pieces', 'single', 65000],
            ['Minyak Goreng 2L', 'Makanan & Minuman', 'Indofood', 'Liters', 'single', 35000],
            ['Gula Pasir 1kg', 'Makanan Instan', 'Indofood', 'Grams', 'single', 15000],
            ['Mie Instan Goreng', 'Makanan Instan', 'Indofood', 'Pieces', 'single', 3000],
            ['Kopi Bubuk 200g', 'Makanan & Minuman', 'Mayora', 'Grams', 'single', 25000],
            ['Kaos Polos Cotton', 'Kaos Pria', 'Uniqlo', 'Pieces', 'variable', 75000],
            ['Lampu LED 10W', 'Elektronik', 'LG', 'Pieces', 'single', 45000],
            ['Sabun Mandi Batang', 'Kecantikan & Perawatan', 'Unilever', 'Pieces', 'single', 5000],
            ['Shampoo 170ml', 'Kecantikan & Perawatan', 'Unilever', 'Bottles', 'single', 25000],
            ['Pasta Gigi 190g', 'Kecantikan & Perawatan', 'Unilever', 'Pieces', 'single', 15000],
            ['Skincare Serum', 'Skincare', 'Wardah', 'Bottles', 'single', 120000],
            ['Vitamin C 1000mg', 'Vitamin & Suplemen', 'Nestle', 'Bottles', 'single', 50000],
            ['Buku Tulis isi 38', 'Kertas & Buku', 'Indofood', 'Pieces', 'single', 5000],
            ['Pulpen Hitam', 'Alat Tulis & Kantor', 'Mayora', 'Pieces', 'single', 3000],
        ];

        foreach ($products_data as $index => $pd) {
            $sku = 'AP-PROD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $product = Product::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $pd[0]
                ],
                [
                    'type' => $pd[4],
                    'unit_id' => $getUnit($pd[3]) ?? Unit::where('business_id', $business->id)->first()->id,
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

        // 11. Suppliers for Business 2
        $this->command->info("Seeding Suppliers...");
        $suppliers = [
            ['PT Maju Jaya Bersama', 'Budi Santoso', 'budi.mjb@example.com', '081211111111', 'Jakarta', 'DKI Jakarta', 15, 'days'],
            ['CV Sumber Rezeki Baru', 'Siti Aminah', 'siti.srb@example.com', '081211111112', 'Surabaya', 'Jawa Timur', 30, 'days'],
            ['PT Alam Sejahtera Indonesia', 'Agus Setiawan', 'agus.asi@example.com', '081211111113', 'Bandung', 'Jawa Barat', 1, 'months'],
            ['UD Berkah Jaya Abadi', 'Dewi Lestari', 'dewi.bja@example.com', '081211111114', 'Semarang', 'Jawa Tengah', 7, 'days'],
        ];

        foreach ($suppliers as $index => $s) {
            Contact::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'supplier_business_name' => $s[0],
                    'type' => 'supplier'
                ],
                [
                    'name' => $s[1],
                    'email' => $s[2],
                    'mobile' => $s[3],
                    'city' => $s[4],
                    'state' => $s[5],
                    'country' => 'Indonesia',
                    'pay_term_number' => $s[6],
                    'pay_term_type' => $s[7],
                    'created_by' => $created_by,
                    'contact_id' => 'AP-SUP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'contact_status' => 'active'
                ]
            );
        }

        // 12. Customers for Business 2
        $this->command->info("Seeding Customers...");
        $customers = [
            ['Bambang Gunawan', 'bambang.g2@example.com', '081311111111', 'Jakarta', 'DKI Jakarta'],
            ['Siti Rahmawati', 'siti.r2@example.com', '081311111112', 'Surabaya', 'Jawa Timur'],
            ['Ahmad Fauzi', 'ahmad.f2@example.com', '081311111113', 'Bandung', 'Jawa Barat'],
            ['Dewi Sartika', 'dewi.s2@example.com', '081311111114', 'Semarang', 'Jawa Tengah'],
        ];

        foreach ($customers as $index => $c) {
            Contact::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'email' => $c[1],
                    'type' => 'customer'
                ],
                [
                    'name' => $c[0],
                    'mobile' => $c[2],
                    'city' => $c[3],
                    'state' => $c[4],
                    'country' => 'Indonesia',
                    'created_by' => $created_by,
                    'contact_id' => 'AP-CUST' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'contact_status' => 'active'
                ]
            );
        }

        // 13. Discounts for Business 2
        $this->command->info("Seeding Discounts...");
        $now = Carbon::now();
        $discounts = [
            [
                'name' => 'Promo Awal Bulan AP',
                'discount_type' => 'percentage',
                'discount_amount' => 10,
                'starts_at' => $now->copy()->startOfMonth(),
                'ends_at' => $now->copy()->startOfMonth()->addDays(7),
            ],
            [
                'name' => 'Diskon Akhir Pekan AP',
                'discount_type' => 'percentage',
                'discount_amount' => 5,
                'starts_at' => $now->copy()->next(Carbon::SATURDAY),
                'ends_at' => $now->copy()->next(Carbon::SUNDAY)->endOfDay(),
            ],
            [
                'name' => 'Diskon Member Loyal AP',
                'discount_type' => 'percentage',
                'discount_amount' => 15,
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

        $this->command->info("Seeding data dummy Business ID 2 berhasil diselesaikan!");
    }
}
