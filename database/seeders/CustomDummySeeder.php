<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomDummySeeder extends Seeder
{
    public function run()
    {
        // 1. Get user & business
        $user = DB::table('users')->first();
        if (!$user) {
            $this->command->error("No user found. Please create at least one user manually.");
            return;
        }
        $user_id = $user->id;

        $business = DB::table('business')->first();
        $all_modules = '["purchases","add_sale","pos_sale","stock_transfers","stock_adjustment","expenses","account","subscription","service_staff","tables","modifiers","kitchen","booking","types_of_service","product_catalogue","repair"]';

        if (!$business) {
            $business_id = DB::table('business')->insertGetId([
                'name' => 'Hassa POS Pratama Indonesia', 'currency_id' => 54, 'start_date' => '2023-01-01', 'owner_id' => $user_id, 'time_zone' => 'Asia/Jakarta',
                'fy_start_month' => 1, 'accounting_method' => 'fifo', 'default_profit_percent' => 25, 'created_at' => now(),
                'enabled_modules' => $all_modules,
                'ref_no_prefixes' => '{"purchase":"PO","stock_transfer":"ST","stock_adjustment":"SA","sell_return":"CN","expense":"EP","contacts":"CO","purchase_payment":"PP","sell_payment":"SP","business_location":"BL"}',
                'date_format' => 'd-m-Y', 'time_format' => '24'
            ]);
        } else {
            $business_id = $business->id;
            DB::table('business')->where('id', $business_id)->update(['currency_id' => 54, 'enabled_modules' => $all_modules]);
        }

        $today = Carbon::now()->format('Y-m-d H:i:s');
        $driver = DB::getDriverName();
        if ($driver == 'mysql') { DB::statement('SET FOREIGN_KEY_CHECKS = 0'); }

        // 2. Cleanup
        $tables = [
            'brands', 'categories', 'contacts', 'products', 'product_variations', 'variations',
            'variation_location_details', 'transactions', 'transaction_payments',
            'transaction_sell_lines', 'purchase_lines', 'business_locations', 'product_locations',
            'units', 'tax_rates', 'expense_categories', 'customer_groups', 'selling_price_groups',
            'warranties', 'variation_templates', 'variation_value_templates', 'variation_group_prices',
            'discounts', 'stock_adjustment_lines', 'accounts', 'account_transactions', 'account_types',
            'res_tables', 'cash_registers', 'cash_register_transactions', 'bookings',
            'repair_statuses', 'repair_job_sheets', 'mfg_recipes', 'essentials_payrolls'
        ];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) { DB::table($table)->delete(); }
        }

        // Expense Categories (20)
        $this->command->info("Seeding 20 Expense Categories...");
        $exp_cats = ['Listrik & Air', 'Sewa Gedung', 'Gaji Karyawan', 'Internet & Telp', 'Biaya Kebersihan', 'Keamanan', 'Peralatan Kantor', 'Pajak', 'Iklan/Promosi', 'Transportasi', 'Konsumsi', 'Perbaikan Gedung', 'Peralatan POS', 'Aturan Toko', 'Operasional Harian', 'Lain-lain', 'Biaya Tak Terduga', 'Logistik', 'Packing', 'Bunga Bank'];
        $exp_cat_ids = [];
        foreach ($exp_cats as $ec) {
            $exp_cat_ids[] = DB::table('expense_categories')->insertGetId(['business_id' => $business_id, 'name' => $ec]);
        }

        // 3. Master Data
        $loc1 = DB::table('business_locations')->insertGetId([
            'business_id' => $business_id, 'name' => 'Toko Pusat Jakarta', 'city' => 'Jakarta Pusat',
            'country' => 'Indonesia', 'state' => 'DKI Jakarta', 'zip_code' => '10110',
            'is_active' => 1, 'created_at' => $today
        ]);

        // Restaurant Tables (10)
        $this->command->info("Seeding 10 Restaurant Tables...");
        $table_ids = [];
        for ($i = 1; $i <= 10; $i++) {
            $table_ids[] = DB::table('res_tables')->insertGetId([
                'business_id' => $business_id, 'location_id' => $loc1, 'name' => 'Meja #'.str_pad($i, 2, '0', STR_PAD_LEFT), 'created_by' => $user_id, 'created_at' => $today
            ]);
        }
        $loc2 = DB::table('business_locations')->insertGetId([
            'business_id' => $business_id, 'name' => 'Cabang Bandung', 'city' => 'Bandung',
            'country' => 'Indonesia', 'state' => 'Jawa Barat', 'zip_code' => '40111',
            'is_active' => 1, 'created_at' => $today
        ]);

        // Units
        $u_pcs = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Pieces', 'short_name' => 'pcs', 'allow_decimal' => 0, 'created_by' => $user_id]);
        $u_gr = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Gram', 'short_name' => 'gr', 'allow_decimal' => 1, 'created_by' => $user_id]);
        $u_ml = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Mililiter', 'short_name' => 'ml', 'allow_decimal' => 1, 'created_by' => $user_id]);
        $u_cm = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Sentimeter', 'short_name' => 'cm', 'allow_decimal' => 1, 'created_by' => $user_id]);

        $units_data = [
            ['actual_name' => 'Lusin', 'short_name' => 'lsn', 'allow_decimal' => 0, 'base_unit_id' => $u_pcs, 'base_unit_multiplier' => 12],
            ['actual_name' => 'Kodi', 'short_name' => 'kodi', 'allow_decimal' => 0, 'base_unit_id' => $u_pcs, 'base_unit_multiplier' => 20],
            ['actual_name' => 'Kilogram', 'short_name' => 'kg', 'allow_decimal' => 1, 'base_unit_id' => $u_gr, 'base_unit_multiplier' => 1000],
            ['actual_name' => 'Liter', 'short_name' => 'ltr', 'allow_decimal' => 1, 'base_unit_id' => $u_ml, 'base_unit_multiplier' => 1000],
            ['actual_name' => 'Box', 'short_name' => 'box', 'allow_decimal' => 0, 'base_unit_id' => $u_pcs, 'base_unit_multiplier' => 24],
            ['actual_name' => 'Meter', 'short_name' => 'm', 'allow_decimal' => 1, 'base_unit_id' => $u_cm, 'base_unit_multiplier' => 100],
            ['actual_name' => 'Rim', 'short_name' => 'rim', 'allow_decimal' => 0, 'base_unit_id' => $u_pcs, 'base_unit_multiplier' => 500],
            ['actual_name' => 'Dus', 'short_name' => 'dus', 'allow_decimal' => 0, 'base_unit_id' => $u_pcs, 'base_unit_multiplier' => 40],
            ['actual_name' => 'Pak', 'short_name' => 'pak', 'allow_decimal' => 0, 'base_unit_id' => $u_pcs, 'base_unit_multiplier' => 10],
            ['actual_name' => 'Karung', 'short_name' => 'krg', 'allow_decimal' => 1, 'base_unit_id' => $u_gr, 'base_unit_multiplier' => 50000],
            ['actual_name' => 'Botol', 'short_name' => 'btl', 'allow_decimal' => 0, 'base_unit_id' => null, 'base_unit_multiplier' => null],
            ['actual_name' => 'Bungkus', 'short_name' => 'bks', 'allow_decimal' => 0, 'base_unit_id' => null, 'base_unit_multiplier' => null],
            ['actual_name' => 'Sachet', 'short_name' => 'sct', 'allow_decimal' => 0, 'base_unit_id' => null, 'base_unit_multiplier' => null]
        ];
        foreach ($units_data as $ud) {
            DB::table('units')->insert(array_merge($ud, ['business_id' => $business_id, 'created_by' => $user_id]));
        }
        $all_u_ids = DB::table('units')->where('business_id', $business_id)->pluck('id')->toArray();

        // Warranties
        $warranties_data = [
            ['name' => 'Garansi Resmi 1 Tahun', 'description' => 'Garansi pabrik resmi Indonesia', 'duration' => 1, 'duration_type' => 'years'],
            ['name' => 'Garansi Toko 6 Bulan', 'description' => 'Garansi servis dan sparepart di toko', 'duration' => 6, 'duration_type' => 'months'],
            ['name' => 'Garansi Distributor 2 Tahun', 'description' => 'Garansi servis oleh pihak ketiga', 'duration' => 2, 'duration_type' => 'years'],
            ['name' => 'Garansi Ganti Baru 7 Hari', 'description' => 'Cacat pabrik langsung ganti baru', 'duration' => 7, 'duration_type' => 'days'],
            ['name' => 'Tanpa Garansi', 'description' => 'Barang tidak bergaransi', 'duration' => 0, 'duration_type' => 'days']
        ];
        $warranty_ids = [];
        foreach ($warranties_data as $wd) {
            $warranty_ids[] = DB::table('warranties')->insertGetId(array_merge($wd, ['business_id' => $business_id]));
        }

        // Customer Groups
        for ($i = 1; $i <= 50; $i++) {
            DB::table('customer_groups')->insert(['business_id' => $business_id, 'name' => 'Grup Pelanggan #'.$i, 'amount' => rand(1, 15), 'created_by' => $user_id]);
        }
        $cg_ids = DB::table('customer_groups')->where('business_id', $business_id)->pluck('id')->toArray();

        // Brands & Categories
        $brand_ids = [];
        for ($i = 1; $i <= 50; $i++) { $brand_ids[] = DB::table('brands')->insertGetId(['business_id' => $business_id, 'name' => 'Brand Hassa-'.str_pad($i, 3, '0', STR_PAD_LEFT), 'created_by' => $user_id]); }
        $cat_ids = [];
        for ($i = 1; $i <= 50; $i++) { $cat_ids[] = DB::table('categories')->insertGetId(['business_id' => $business_id, 'name' => 'Kategori-'.str_pad($i, 3, '0', STR_PAD_LEFT), 'category_type' => 'product', 'parent_id' => 0, 'created_by' => $user_id]); }

        // Modifiers (10)
        $this->command->info("Seeding 10 Modifiers...");
        $modifier_ids = [];
        for ($i = 1; $i <= 10; $i++) {
            $m_id = DB::table('products')->insertGetId([
                'name' => 'Ekstra Toping #'.$i, 'business_id' => $business_id, 'type' => 'modifier', 'unit_id' => $u_pcs,
                'tax_type' => 'exclusive', 'barcode_type' => 'C128', 'sku' => 'MOD-'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'created_by' => $user_id, 'created_at' => $today
            ]);
            $mpv_id = DB::table('product_variations')->insertGetId(['name' => 'DUMMY', 'product_id' => $m_id, 'is_dummy' => 1]);
            $modifier_ids[] = DB::table('variations')->insertGetId([
                'name' => 'DUMMY', 'product_id' => $m_id, 'sub_sku' => 'MOD-'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'product_variation_id' => $mpv_id, 'default_purchase_price' => 0, 'dpp_inc_tax' => 0,
                'profit_percent' => 0, 'default_sell_price' => rand(1, 5) * 1000, 'sell_price_inc_tax' => rand(1, 5) * 1110,
                'created_at' => $today
            ]);
        }

        $tax_id = DB::table('tax_rates')->insertGetId(['business_id' => $business_id, 'name' => 'PPN 11%', 'amount' => 11, 'created_by' => $user_id]);

        // Discounts (10)
        $this->command->info("Seeding 10 Discounts...");
        for ($i = 1; $i <= 10; $i++) {
            DB::table('discounts')->insert([
                'name' => 'Promo Hassa #'.$i,
                'business_id' => $business_id,
                'brand_id' => (rand(0, 1) ? $brand_ids[array_rand($brand_ids)] : null),
                'category_id' => (rand(0, 1) ? $cat_ids[array_rand($cat_ids)] : null),
                'location_id' => $loc1,
                'priority' => $i,
                'discount_type' => (rand(0, 1) ? 'fixed' : 'percentage'),
                'discount_amount' => rand(5, 50) * 100,
                'starts_at' => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
                'is_active' => 1,
                'created_at' => $today
            ]);
        }

        // 4. Contacts (500 Customers, 500 Suppliers)
        $fnames = ['Andi', 'Budi', 'Cici', 'Dedi', 'Eko', 'Fani', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kiki', 'Lani', 'Maya', 'Nico', 'Oki', 'Putu', 'Rina', 'Santi', 'Tono', 'Uli'];
        $lnames = ['Saputra', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Santoso', 'Gunawan', 'Lestari', 'Sari', 'Utami'];

        $contacts = [];
        for ($i = 1; $i <= 500; $i++) {
            $contacts[] = [ 'business_id' => $business_id, 'type' => 'customer', 'name' => $fnames[array_rand($fnames)].' '.$lnames[array_rand($lnames)].' '.$i, 'contact_id' => 'CUST-'.str_pad($i, 5, '0', STR_PAD_LEFT), 'customer_group_id' => $cg_ids[array_rand($cg_ids)], 'created_by' => $user_id, 'mobile' => '08'.rand(11, 59).rand(1000000, 9999999), 'created_at' => $today, 'first_name' => $fnames[array_rand($fnames)], 'last_name' => $lnames[array_rand($lnames)] ];
            $contacts[] = [ 'business_id' => $business_id, 'type' => 'supplier', 'name' => 'Supplier Hassa Utama '.$i, 'contact_id' => 'SUPP-'.str_pad($i, 5, '0', STR_PAD_LEFT), 'customer_group_id' => null, 'created_by' => $user_id, 'mobile' => '08'.rand(11, 59).rand(1000000, 9999999), 'created_at' => $today, 'first_name' => 'Supplier', 'last_name' => 'Hassa' ];
        }
        $chunks = array_chunk($contacts, 200);
        foreach ($chunks as $chunk) { DB::table('contacts')->insert($chunk); }
        $cust_ids = DB::table('contacts')->where('business_id', $business_id)->where('type', 'customer')->pluck('id')->toArray();
        $supp_ids = DB::table('contacts')->where('business_id', $business_id)->where('type', 'supplier')->pluck('id')->toArray();

        // 5. Products (1,000)
        $all_v_ids = [];
        for ($i = 1; $i <= 1000; $i++) {
            $is_alert = ($i <= 10); // 10 products with stock alerts
            $p_id = DB::table('products')->insertGetId([
                'name' => 'Produk Hassa '.$i, 'business_id' => $business_id, 'type' => 'single', 'unit_id' => $all_u_ids[array_rand($all_u_ids)],
                'brand_id' => $brand_ids[array_rand($brand_ids)], 'category_id' => $cat_ids[array_rand($cat_ids)], 'tax' => $tax_id,
                'tax_type' => 'exclusive', 'barcode_type' => 'C128',
                'enable_stock' => 1, 'sku' => 'SKU-'.str_pad($i, 5, '0', STR_PAD_LEFT), 'created_by' => $user_id, 'created_at' => $today,
                'warranty_id' => $warranty_ids[array_rand($warranty_ids)],
                'alert_quantity' => $is_alert ? 5000 : 10
            ]);
            DB::table('product_locations')->insert(['product_id' => $p_id, 'location_id' => $loc1]);
            DB::table('product_locations')->insert(['product_id' => $p_id, 'location_id' => $loc2]);

            $pv_id = DB::table('product_variations')->insertGetId(['name' => 'DUMMY', 'product_id' => $p_id, 'is_dummy' => 1]);
            $buy = rand(5, 500) * 1000; $sell = $buy * 1.25;
            $v_id = DB::table('variations')->insertGetId(['name' => 'DUMMY', 'product_id' => $p_id, 'sub_sku' => 'SKU-'.str_pad($i, 5, '0', STR_PAD_LEFT), 'product_variation_id' => $pv_id, 'default_purchase_price' => $buy, 'dpp_inc_tax' => $buy * 1.11, 'profit_percent' => 25, 'default_sell_price' => $sell, 'sell_price_inc_tax' => $sell * 1.11, 'created_at' => $today]);
            $all_v_ids[] = ['p_id' => $p_id, 'v_id' => $v_id, 'buy' => $buy, 'sell' => $sell];

            $qty1 = $is_alert ? 5 : rand(100, 5000);
            $qty2 = $is_alert ? 5 : rand(100, 5000);
            DB::table('variation_location_details')->insert(['product_id' => $p_id, 'product_variation_id' => $pv_id, 'variation_id' => $v_id, 'location_id' => $loc1, 'qty_available' => $qty1]);
            DB::table('variation_location_details')->insert(['product_id' => $p_id, 'product_variation_id' => $pv_id, 'variation_id' => $v_id, 'location_id' => $loc2, 'qty_available' => $qty2]);
        }

        // 6. Sell Transactions (All Varieties: Final, Draft, Quotation, POS)
        $this->command->info("Seeding 4,000 Sells (Final, Draft, Quotation, POS, Shipments, Subscriptions, SalesOrder)...");
        $sell_types = [
            ['status' => 'final', 'is_direct_sale' => 1, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'Sale'],
            ['status' => 'final', 'is_direct_sale' => 0, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'POS'],
            ['status' => 'draft', 'is_direct_sale' => 0, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'Draft'],
            ['status' => 'draft', 'is_direct_sale' => 0, 'is_quotation' => 1, 'sub_status' => 'quotation', 'label' => 'Quotation'],
            ['status' => 'ordered', 'is_direct_sale' => 0, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'SalesOrder']
        ];
        $shipping_statuses = ['ordered', 'packed', 'shipped', 'delivered', 'cancelled'];
        $all_sell_ids = [];
        foreach ($sell_types as $stype) {
            for ($i = 1; $i <= 800; $i++) {
                $p = $all_v_ids[array_rand($all_v_ids)];
                $dt = Carbon::now()->subDays(rand(0, 365))->format('Y-m-d H:i:s');

                $ship_status = ($stype['status'] == 'final' && rand(1, 4) == 1) ? $shipping_statuses[array_rand($shipping_statuses)] : null;
                $is_recurring = ($stype['status'] == 'final' && rand(1, 10) == 1) ? 1 : 0;

                $is_kitchen = ($stype['label'] == 'POS' && rand(0, 1));

                // For Sales Payment Due Alert: Needs payment_status != paid and pay term expiring soon
                $is_due_soon = ($stype['label'] == 'Sale' && $i <= 10);

                $tid = DB::table('transactions')->insertGetId([
                    'business_id' => $business_id, 'location_id' => $loc1, 'type' => 'sell',
                    'status' => $stype['status'], 'is_direct_sale' => $stype['is_direct_sale'],
                    'is_quotation' => $stype['is_quotation'], 'sub_status' => $stype['sub_status'],
                    'payment_status' => ($is_due_soon ? 'due' : ($stype['status'] == 'final' ? 'paid' : 'due')),
                    'contact_id' => $cust_ids[array_rand($cust_ids)],
                    'invoice_no' => 'INV-'.$stype['label'].'-'.Str::random(5).'-'.$i,
                    'transaction_date' => $is_due_soon ? Carbon::now()->subDays(5)->format('Y-m-d H:i:s') : $dt,
                    'total_before_tax' => $p['sell'],
                    'shipping_status' => $ship_status,
                    'delivery_person' => $ship_status ? $user_id : null,
                    'is_kitchen_order' => $is_kitchen ? 1 : 0,
                    'res_table_id' => $is_kitchen ? $table_ids[array_rand($table_ids)] : null,
                    'res_waiter_id' => $is_kitchen ? $user_id : null,
                    'res_order_status' => $is_kitchen ? ['received', 'cooked', 'served'][rand(0, 2)] : null,
                    'pay_term_number' => $is_due_soon ? 7 : null,
                    'pay_term_type' => $is_due_soon ? 'days' : null,
                    'is_recurring' => $is_recurring,
                    'subscription_no' => $is_recurring ? 'SUB-'.Str::random(5).'-'.$i : null,
                    'recur_interval' => $is_recurring ? 1 : null,
                    'recur_interval_type' => $is_recurring ? 'months' : null,
                    'final_total' => $p['sell'], 'created_by' => $user_id, 'created_at' => $dt
                ]);
                $all_sell_ids[] = $tid;

                $line_status = $is_kitchen ? ['received', 'cooked', 'served'][rand(0, 2)] : null;

                $line_id = DB::table('transaction_sell_lines')->insertGetId([
                    'transaction_id' => $tid, 'product_id' => $p['p_id'], 'variation_id' => $p['v_id'],
                    'quantity' => 1, 'unit_price' => $p['sell'], 'unit_price_inc_tax' => $p['sell'],
                    'item_tax' => 0, 'unit_price_before_discount' => $p['sell'],
                    'res_line_order_status' => $line_status,
                    'res_service_staff_id' => $is_kitchen ? $user_id : null,
                    'created_at' => $dt
                ]);

                // Random Modifier for POS Kitchen Order
                if ($is_kitchen && rand(0, 1)) {
                    $m_v_id = $modifier_ids[array_rand($modifier_ids)];
                    $m_v = DB::table('variations')->where('id', $m_v_id)->first();
                    DB::table('transaction_sell_lines')->insert([
                        'transaction_id' => $tid, 'product_id' => $m_v->product_id, 'variation_id' => $m_v_id,
                        'quantity' => 1, 'unit_price' => $m_v->default_sell_price, 'unit_price_inc_tax' => $m_v->sell_price_inc_tax,
                        'item_tax' => 0, 'unit_price_before_discount' => $m_v->default_sell_price,
                        'parent_sell_line_id' => $line_id, 'children_type' => 'modifier', 'created_at' => $dt
                    ]);
                }

                if ($stype['status'] == 'final' && !$is_due_soon) {
                    DB::table('transaction_payments')->insert([
                        'transaction_id' => $tid, 'business_id' => $business_id, 'amount' => $p['sell'], 'method' => 'cash', 'paid_on' => $dt, 'created_by' => $user_id, 'payment_for' => $cust_ids[array_rand($cust_ids)], 'payment_ref_no' => 'PAY-SELL-'.Str::random(5), 'created_at' => $dt
                    ]);
                }
            }
        }

        // 7. Sell Returns (1,000)
        $this->command->info("Seeding 1,000 Sell Returns...");
        for ($i = 1; $i <= 1000; $i++) {
            $p = $all_v_ids[array_rand($all_v_ids)];
            $dt = Carbon::now()->subDays(rand(0, 180))->format('Y-m-d H:i:s');
            $rtid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id, 'location_id' => $loc1, 'type' => 'sell_return', 'status' => 'final', 'payment_status' => 'paid',
                'contact_id' => $cust_ids[array_rand($cust_ids)], 'invoice_no' => 'SRET-'.Str::random(5).'-'.$i, 'transaction_date' => $dt,
                'total_before_tax' => $p['sell'],
                'final_total' => $p['sell'], 'return_parent_id' => $all_sell_ids[array_rand($all_sell_ids)], 'created_by' => $user_id, 'created_at' => $dt
            ]);
            DB::table('transaction_payments')->insert([
                'transaction_id' => $rtid, 'business_id' => $business_id, 'amount' => $p['sell'], 'method' => 'cash', 'paid_on' => $dt, 'created_by' => $user_id, 'payment_for' => $cust_ids[array_rand($cust_ids)], 'payment_ref_no' => 'PAY-SRET-'.Str::random(5), 'created_at' => $dt
            ]);
        }

        // 8. Purchase Transactions (1,000) & Purchase Returns (1,000)
        $this->command->info("Seeding 1,000 Purchases & 1,000 Purchase Returns...");
        $all_purchase_ids = [];
        for ($i = 1; $i <= 1000; $i++) {
            $p = $all_v_ids[array_rand($all_v_ids)];
            $qty = rand(10, 100);
            $total = $p['buy'] * $qty;
            $dt = Carbon::now()->subDays(rand(0, 180))->format('Y-m-d H:i:s');

            // For Purchase Payment Due Alert: 10 transactions
            $is_due_soon = ($i <= 10);

            // Purchase
            $tid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id, 'location_id' => $loc1, 'type' => 'purchase', 'status' => 'received',
                'payment_status' => $is_due_soon ? 'due' : 'paid',
                'contact_id' => $supp_ids[array_rand($supp_ids)], 'ref_no' => 'PUR-'.Str::random(5).'-'.$i,
                'transaction_date' => $is_due_soon ? Carbon::now()->subDays(5)->format('Y-m-d H:i:s') : $dt,
                'total_before_tax' => $total,
                'final_total' => $total,
                'pay_term_number' => $is_due_soon ? 7 : null,
                'pay_term_type' => $is_due_soon ? 'days' : null,
                'created_by' => $user_id, 'created_at' => $dt
            ]);
            $all_purchase_ids[] = $tid;
            DB::table('purchase_lines')->insert(['transaction_id' => $tid, 'product_id' => $p['p_id'], 'variation_id' => $p['v_id'], 'quantity' => $qty, 'purchase_price' => $p['buy'], 'purchase_price_inc_tax' => $p['buy'] * 1.11, 'item_tax' => $p['buy'] * 0.11, 'created_at' => $dt]);

            if (!$is_due_soon) {
                DB::table('transaction_payments')->insert([
                    'transaction_id' => $tid, 'business_id' => $business_id, 'amount' => $total, 'method' => 'cash', 'paid_on' => $dt, 'created_by' => $user_id, 'payment_for' => $supp_ids[array_rand($supp_ids)], 'payment_ref_no' => 'PAY-PUR-'.Str::random(5), 'created_at' => $dt
                ]);
            }

            // Purchase Return
            $prtid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id, 'location_id' => $loc1, 'type' => 'purchase_return', 'status' => 'final', 'payment_status' => 'paid',
                'contact_id' => $supp_ids[array_rand($supp_ids)], 'ref_no' => 'PRET-'.Str::random(5).'-'.$i, 'transaction_date' => $dt,
                'total_before_tax' => $p['buy'] * rand(1, 5),
                'final_total' => $p['buy'] * rand(1, 5), 'return_parent_id' => $tid, 'created_by' => $user_id, 'created_at' => $dt
            ]);
            DB::table('transaction_payments')->insert([
                'transaction_id' => $prtid, 'business_id' => $business_id, 'amount' => $p['buy'] * rand(1, 5), 'method' => 'cash', 'paid_on' => $dt, 'created_by' => $user_id, 'payment_for' => $supp_ids[array_rand($supp_ids)], 'payment_ref_no' => 'PAY-PRET-'.Str::random(5), 'created_at' => $dt
            ]);
        }

        // 9. Stock Transfers (1,000)
        $this->command->info("Seeding 1,000 Stock Transfers...");
        $st_statuses = ['pending', 'in_transit', 'final'];
        for ($i = 1; $i <= 1000; $i++) {
            $p = $all_v_ids[array_rand($all_v_ids)];
            $dt = Carbon::now()->subDays(rand(0, 180))->format('Y-m-d H:i:s');
            $qty = rand(5, 50);
            $total = $p['buy'] * $qty;

            // Sell Transfer (From Jakarta)
            $tid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id, 'location_id' => $loc1, 'type' => 'sell_transfer', 'status' => $st_statuses[array_rand($st_statuses)],
                'ref_no' => 'ST-'.Str::random(5).'-'.$i, 'transaction_date' => $dt,
                'total_before_tax' => $total, 'final_total' => $total, 'created_by' => $user_id, 'created_at' => $dt
            ]);

            // Purchase Transfer (To Bandung)
            DB::table('transactions')->insert([
                'business_id' => $business_id, 'location_id' => $loc2, 'type' => 'purchase_transfer', 'status' => 'received',
                'ref_no' => 'ST-'.Str::random(5).'-'.$i, 'transaction_date' => $dt,
                'total_before_tax' => $total, 'final_total' => $total, 'transfer_parent_id' => $tid, 'created_by' => $user_id, 'created_at' => $dt
            ]);

            DB::table('purchase_lines')->insert(['transaction_id' => $tid, 'product_id' => $p['p_id'], 'variation_id' => $p['v_id'], 'quantity' => $qty, 'purchase_price' => $p['buy'], 'purchase_price_inc_tax' => $p['buy'], 'created_at' => $dt]);
        }

        // 10. Stock Adjustments (1,000)
        $this->command->info("Seeding 1,000 Stock Adjustments...");
        $adj_types = ['normal', 'abnormal'];
        for ($i = 1; $i <= 1000; $i++) {
            $p = $all_v_ids[array_rand($all_v_ids)];
            $dt = Carbon::now()->subDays(rand(0, 180))->format('Y-m-d H:i:s');
            $qty = rand(1, 20);
            $total = $p['buy'] * $qty;
            $recovered = (rand(1, 5) == 1) ? $total * 0.5 : 0;

            $tid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id, 'location_id' => (rand(0, 1) ? $loc1 : $loc2), 'type' => 'stock_adjustment', 'status' => 'final',
                'adjustment_type' => $adj_types[array_rand($adj_types)], 'ref_no' => 'SA-'.Str::random(5).'-'.$i, 'transaction_date' => $dt,
                'total_before_tax' => $total, 'final_total' => $total, 'total_amount_recovered' => $recovered, 'created_by' => $user_id, 'created_at' => $dt
            ]);

            DB::table('stock_adjustment_lines')->insert(['transaction_id' => $tid, 'product_id' => $p['p_id'], 'variation_id' => $p['v_id'], 'quantity' => $qty, 'unit_price' => $p['buy'], 'created_at' => $dt]);
        }

        // 11. Expenses (1,000)
        $this->command->info("Seeding 1,000 Expenses...");
        for ($i = 1; $i <= 1000; $i++) {
            $dt = Carbon::now()->subDays(rand(0, 365))->format('Y-m-d H:i:s');
            $amt = rand(10, 500) * 1000;

            $tid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id, 'location_id' => (rand(0, 1) ? $loc1 : $loc2), 'type' => 'expense', 'status' => 'final', 'payment_status' => 'paid',
                'ref_no' => 'EXP-'.Str::random(5).'-'.$i, 'transaction_date' => $dt,
                'total_before_tax' => $amt, 'final_total' => $amt, 'expense_category_id' => $exp_cat_ids[array_rand($exp_cat_ids)], 'expense_for' => $user_id, 'created_by' => $user_id, 'created_at' => $dt
            ]);

            DB::table('transaction_payments')->insert([
                'transaction_id' => $tid, 'business_id' => $business_id, 'amount' => $amt, 'method' => 'cash', 'paid_on' => $dt, 'created_by' => $user_id, 'payment_ref_no' => 'PAY-EXP-'.Str::random(5), 'created_at' => $dt
            ]);
        }

        // 12. Payment Accounts (5 Accounts & Transactions)
        $this->command->info("Seeding 5 Payment Accounts & Linking Transactions...");

        $asset_type_id = DB::table('account_types')->insertGetId([
            'name' => 'Assets', 'business_id' => $business_id, 'created_at' => $today
        ]);
        $equity_type_id = DB::table('account_types')->insertGetId([
            'name' => 'Equity', 'business_id' => $business_id, 'created_at' => $today
        ]);

        $accounts_data = [
            ['name' => 'Kas Tunai Utama', 'account_number' => '100001', 'account_type_id' => $asset_type_id],
            ['name' => 'Bank BCA - 8820123xxx', 'account_number' => '200001', 'account_type_id' => $asset_type_id],
            ['name' => 'Bank Mandiri - 131001xxx', 'account_number' => '200002', 'account_type_id' => $asset_type_id],
            ['name' => 'Petty Cash', 'account_number' => '100002', 'account_type_id' => $asset_type_id],
            ['name' => 'Modal Pemilik', 'account_number' => '300001', 'account_type_id' => $equity_type_id]
        ];
        $acc_ids = [];
        foreach ($accounts_data as $ad) {
            $aid = DB::table('accounts')->insertGetId(array_merge($ad, [
                'business_id' => $business_id, 'created_by' => $user_id, 'created_at' => $today
            ]));
            $acc_ids[] = $aid;

            // Opening Balance
            DB::table('account_transactions')->insert([
                'account_id' => $aid, 'type' => 'credit', 'sub_type' => 'opening_balance',
                'amount' => rand(5000, 50000) * 1000, 'reff_no' => 'OB-'.Str::random(5),
                'operation_date' => Carbon::now()->subMonths(6)->format('Y-m-d H:i:s'),
                'created_by' => $user_id, 'created_at' => $today
            ]);
        }

        // Link existing payments to accounts
        $payments = DB::table('transaction_payments')->where('business_id', $business_id)->get();
        $account_txs = [];
        foreach ($payments as $pay) {
            $aid = $acc_ids[array_rand($acc_ids)];
            $tx = DB::table('transactions')->where('id', $pay->transaction_id)->first();

            if ($tx) {
                // System uses: sell -> credit, purchase -> debit
                $types = [
                    'sell' => 'credit', 'purchase' => 'debit', 'expense' => 'debit',
                    'purchase_return' => 'credit', 'sell_return' => 'debit', 'sell_transfer' => 'credit'
                ];
                $type = $types[$tx->type] ?? 'debit';

                $account_txs[] = [
                    'account_id' => $aid, 'type' => $type, 'amount' => $pay->amount,
                    'reff_no' => 'PAY-'.Str::random(5), 'operation_date' => $pay->created_at,
                    'created_by' => $user_id, 'transaction_id' => $tx->id,
                    'transaction_payment_id' => $pay->id, 'created_at' => $today
                ];

                // Update payment with account_id
                DB::table('transaction_payments')->where('id', $pay->id)->update(['account_id' => $aid]);
            }
        }
        $chunks = array_chunk($account_txs, 500);
        foreach ($chunks as $chunk) { DB::table('account_transactions')->insert($chunk); }

        // 12b. Balancing Trial Balance
        $this->command->info("Balancing Trial Balance...");
        $supplier_due = DB::table('transactions')->where('business_id', $business_id)->where('type', 'purchase')->sum('final_total')
                        - DB::table('transaction_payments')->where('business_id', $business_id)->whereIn('transaction_id', DB::table('transactions')->where('type', 'purchase')->pluck('id'))->sum('amount');

        $customer_due = DB::table('transactions')->where('business_id', $business_id)->where('type', 'sell')->sum('final_total')
                        - DB::table('transaction_payments')->where('business_id', $business_id)->whereIn('transaction_id', DB::table('transactions')->where('type', 'sell')->pluck('id'))->sum('amount');

        $account_sum = DB::table('account_transactions as AT')
                        ->join('accounts as A', 'AT.account_id', '=', 'A.id')
                        ->where('A.business_id', $business_id)
                        ->whereNull('AT.deleted_at')
                        ->sum(DB::raw("IF(AT.type='credit', AT.amount, -1*AT.amount)"));

        // Trial Balance Logic from view:
        // Debit Side = Customer Due + Account Balances
        // Credit Side = Supplier Due
        // To balance: We need (Customer Due + Account Balances) = Supplier Due
        $current_debit = $customer_due + $account_sum;
        $diff = $supplier_due - $current_debit;

        if ($diff != 0) {
            $balancing_account = end($acc_ids); // Modal Pemilik
            DB::table('account_transactions')->insert([
                'account_id' => $balancing_account, 'type' => ($diff > 0 ? 'credit' : 'debit'), 'sub_type' => 'opening_balance',
                'amount' => abs($diff), 'reff_no' => 'BAL-'.Str::random(5),
                'operation_date' => $today, 'created_by' => $user_id, 'created_at' => $today
            ]);
        }

        // 13. Cash Registers
        $this->command->info("Seeding Cash Register & Transactions...");
        $register_id = DB::table('cash_registers')->insertGetId([
            'business_id' => $business_id, 'location_id' => $loc1, 'user_id' => $user_id, 'status' => 'open', 'created_at' => $today
        ]);

        // Link POS transactions to register
        $pos_txs = DB::table('transactions')->where('business_id', $business_id)->where('type', 'sell')->where('is_direct_sale', 0)->get();
        $reg_txs = [];
        foreach ($pos_txs as $tx) {
            $reg_txs[] = [
                'cash_register_id' => $register_id, 'amount' => $tx->final_total, 'pay_method' => 'cash', 'type' => 'debit', 'transaction_type' => 'sell', 'transaction_id' => $tx->id, 'created_at' => $tx->created_at
            ];

            // Randomly assign table & waiter to some POS transactions
            if (rand(1, 3) == 1) {
                DB::table('transactions')->where('id', $tx->id)->update([
                    'res_table_id' => $table_ids[array_rand($table_ids)],
                    'res_waiter_id' => $user_id,
                    'res_order_status' => 'served',
                    'commission_agent' => $user_id
                ]);
            }
        }
        $chunks = array_chunk($reg_txs, 500);
        foreach ($chunks as $chunk) { DB::table('cash_register_transactions')->insert($chunk); }

        // 14. Bookings (Restaurant/Service)
        if (Schema::hasTable('bookings')) {
            $this->command->info("Seeding 100 Bookings...");
            $booking_statuses = ['booked', 'completed', 'cancelled'];
            for ($i = 1; $i <= 100; $i++) {
                $start = Carbon::now()->addDays(rand(-30, 30))->addHours(rand(0, 23));
                $end = (clone $start)->addHours(rand(1, 4));

                DB::table('bookings')->insert([
                    'business_id' => $business_id,
                    'location_id' => (rand(0, 1) ? $loc1 : $loc2),
                    'contact_id' => $cust_ids[array_rand($cust_ids)],
                    'waiter_id' => $user_id,
                    'table_id' => $table_ids[array_rand($table_ids)],
                    'booking_start' => $start->format('Y-m-d H:i:s'),
                    'booking_end' => $end->format('Y-m-d H:i:s'),
                    'created_by' => $user_id,
                    'booking_status' => $booking_statuses[array_rand($booking_statuses)],
                    'booking_note' => 'Booking dummy Hassa POS #'.$i,
                    'created_at' => $today
                ]);
            }
        }

        // 15. Repair Module (If exists)
        if (Schema::hasTable('repair_statuses')) {
            $this->command->info("Seeding Repair Statuses & Job Sheets...");
            $rep_stats = [
                ['name' => 'Pending', 'color' => '#ff0000', 'sort_order' => 1],
                ['name' => 'In Progress', 'color' => '#0000ff', 'sort_order' => 2],
                ['name' => 'Completed', 'color' => '#00ff00', 'sort_order' => 3],
                ['name' => 'Delivered', 'color' => '#000000', 'sort_order' => 4],
                ['name' => 'Cancelled', 'color' => '#808080', 'sort_order' => 5]
            ];
            $stat_ids = [];
            foreach ($rep_stats as $rs) {
                $stat_ids[] = DB::table('repair_statuses')->insertGetId(array_merge($rs, ['business_id' => $business_id]));
            }

            if (Schema::hasTable('repair_job_sheets')) {
                for ($i = 1; $i <= 50; $i++) {
                    DB::table('repair_job_sheets')->insert([
                        'business_id' => $business_id, 'location_id' => $loc1, 'contact_id' => $cust_ids[array_rand($cust_ids)],
                        'job_sheet_no' => 'JOB-'.str_pad($i, 5, '0', STR_PAD_LEFT), 'service_type' => 'carry_in',
                        'brand_id' => $brand_ids[array_rand($brand_ids)], 'serial_no' => Str::random(10),
                        'status_id' => $stat_ids[array_rand($stat_ids)], 'estimated_cost' => rand(10, 100) * 1000,
                        'created_by' => $user_id, 'created_at' => $today
                    ]);
                }
            }
        }

        // 15. Manufacturing Module (If exists)
        if (Schema::hasTable('mfg_recipes')) {
            $this->command->info("Seeding 50 Manufacturing Recipes...");
            $mfg_prods = array_slice($all_v_ids, 0, 50);
            foreach ($mfg_prods as $mp) {
                DB::table('mfg_recipes')->insert([
                    'product_id' => $mp['p_id'], 'variation_id' => $mp['v_id'], 'ingredients' => '[]',
                    'final_price' => $mp['buy'], 'created_at' => $today
                ]);
            }
        }

        // 16. Essentials/HRM Payroll (If exists)
        if (Schema::hasTable('essentials_payrolls')) {
            $this->command->info("Seeding 20 Payroll Records...");
            for ($i = 1; $i <= 20; $i++) {
                $month = rand(1, 12);
                $year = 2023;
                DB::table('essentials_payrolls')->insert([
                    'business_id' => $business_id, 'user_id' => $user_id, 'ref_no' => 'PAYROLL-'.Str::random(5),
                    'month' => $month, 'year' => $year, 'duration' => 1, 'duration_unit' => 'month',
                    'amount_per_unit_duration' => 5000000, 'gross_amount' => 5000000, 'created_by' => $user_id, 'created_at' => $today
                ]);
            }
        }

        if ($driver == 'mysql') { DB::statement('SET FOREIGN_KEY_CHECKS = 1'); }
        $this->command->info("Dummy Seeder Berhasil! 1000 Produk, 10 Diskon, 4000 Sales (inc POS, Draft, Quot, Shipment, Subs, SalesOrder), 1000 Sell Return, 1000 Purchase, 1000 Purchase Return, 1000 Stock Transfer, 1000 Stock Adjustment, 1000 Expense, & 100 Bookings.");
    }
}
