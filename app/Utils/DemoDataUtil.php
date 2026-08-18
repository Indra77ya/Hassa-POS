<?php

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoDataUtil extends Util
{
    /**
     * Generates comprehensive demo data for a given business ID.
     *
     * @param int $business_id
     * @return bool
     */
    public function generateDemoData($business_id)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $business = DB::table('business')->where('id', $business_id)->first();
        if (!$business) {
            return false;
        }

        $user = DB::table('users')->where('business_id', $business_id)->first();
        if (!$user) {
            $user = DB::table('users')->where('id', $business->owner_id)->first();
        }
        if (!$user) {
            $user = DB::table('users')->first();
        }
        $user_id = $user ? $user->id : 1;

        $all_modules = '["purchases","add_sale","pos_sale","stock_transfers","stock_adjustment","expenses","account","subscription","service_staff","tables","modifiers","kitchen","booking","types_of_service","product_catalogue","repair"]';

        DB::table('business')->where('id', $business_id)->update([
            'enabled_modules' => $all_modules,
            'ref_no_prefixes' => json_encode([
                'purchase' => 'PO',
                'stock_transfer' => 'ST',
                'stock_adjustment' => 'SA',
                'sell_return' => 'CN',
                'expense' => 'EP',
                'contacts' => 'CO',
                'purchase_payment' => 'PP',
                'sell_payment' => 'SP',
                'business_location' => 'BL'
            ]),
            'date_format' => 'd-m-Y',
            'time_format' => '24'
        ]);

        $today = Carbon::now()->format('Y-m-d H:i:s');
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        // 1. Clean existing business data safely
        $this->cleanBusinessData($business_id);

        // 2. Expense Categories (10)
        $exp_cats = ['Listrik & Air', 'Sewa Gedung', 'Gaji Karyawan', 'Internet & Telp', 'Biaya Kebersihan', 'Keamanan', 'Peralatan Kantor', 'Iklan/Promosi', 'Transportasi', 'Operasional Harian'];
        $exp_cat_ids = [];
        foreach ($exp_cats as $ec) {
            $exp_cat_ids[] = DB::table('expense_categories')->insertGetId([
                'business_id' => $business_id,
                'name' => $ec,
                'code' => 'EXP-' . Str::upper(Str::random(4))
            ]);
        }

        // 3. Business Locations
        $loc1 = DB::table('business_locations')->insertGetId([
            'business_id' => $business_id,
            'name' => $business->name . ' - Pusat',
            'city' => 'Jakarta Pusat',
            'country' => 'Indonesia',
            'state' => 'DKI Jakarta',
            'zip_code' => '10110',
            'is_active' => 1,
            'created_at' => $today
        ]);

        $loc2 = DB::table('business_locations')->insertGetId([
            'business_id' => $business_id,
            'name' => $business->name . ' - Cabang Bandung',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'state' => 'Jawa Barat',
            'zip_code' => '40111',
            'is_active' => 1,
            'created_at' => $today
        ]);

        // Restaurant Tables (5)
        $table_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $table_ids[] = DB::table('res_tables')->insertGetId([
                'business_id' => $business_id,
                'location_id' => $loc1,
                'name' => 'Meja #' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'created_by' => $user_id,
                'created_at' => $today
            ]);
        }

        // Units
        $u_pcs = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Pieces', 'short_name' => 'pcs', 'allow_decimal' => 0, 'created_by' => $user_id]);
        $u_gr = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Gram', 'short_name' => 'gr', 'allow_decimal' => 1, 'created_by' => $user_id]);
        $u_box = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Box', 'short_name' => 'box', 'allow_decimal' => 0, 'created_by' => $user_id]);
        $u_pack = DB::table('units')->insertGetId(['business_id' => $business_id, 'actual_name' => 'Pak', 'short_name' => 'pak', 'allow_decimal' => 0, 'created_by' => $user_id]);

        $all_u_ids = [$u_pcs, $u_gr, $u_box, $u_pack];

        // Warranties
        $warranties_data = [
            ['name' => 'Garansi Resmi 1 Tahun', 'description' => 'Garansi pabrik resmi Indonesia', 'duration' => 1, 'duration_type' => 'years'],
            ['name' => 'Garansi Toko 6 Bulan', 'description' => 'Garansi servis dan sparepart di toko', 'duration' => 6, 'duration_type' => 'months'],
            ['name' => 'Tanpa Garansi', 'description' => 'Barang tidak bergaransi', 'duration' => 0, 'duration_type' => 'days']
        ];
        $warranty_ids = [];
        foreach ($warranties_data as $wd) {
            $warranty_ids[] = DB::table('warranties')->insertGetId(array_merge($wd, ['business_id' => $business_id]));
        }

        // Customer Groups
        $cg_ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $cg_ids[] = DB::table('customer_groups')->insertGetId([
                'business_id' => $business_id,
                'name' => 'Grup Pelanggan VIP #' . $i,
                'amount' => rand(5, 15),
                'created_by' => $user_id
            ]);
        }

        // Brands & Categories
        $brand_ids = [];
        for ($i = 1; $i <= 10; $i++) {
            $brand_ids[] = DB::table('brands')->insertGetId([
                'business_id' => $business_id,
                'name' => 'Brand Demo-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'created_by' => $user_id
            ]);
        }

        $cat_ids = [];
        for ($i = 1; $i <= 10; $i++) {
            $cat_ids[] = DB::table('categories')->insertGetId([
                'business_id' => $business_id,
                'name' => 'Kategori-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'category_type' => 'product',
                'parent_id' => 0,
                'created_by' => $user_id
            ]);
        }

        // Modifiers (5)
        $modifier_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $m_id = DB::table('products')->insertGetId([
                'name' => 'Ekstra Toping #' . $i,
                'business_id' => $business_id,
                'type' => 'modifier',
                'unit_id' => $u_pcs,
                'tax_type' => 'exclusive',
                'barcode_type' => 'C128',
                'sku' => 'MOD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'created_by' => $user_id,
                'created_at' => $today
            ]);
            $mpv_id = DB::table('product_variations')->insertGetId(['name' => 'DUMMY', 'product_id' => $m_id, 'is_dummy' => 1]);
            $modifier_ids[] = DB::table('variations')->insertGetId([
                'name' => 'DUMMY',
                'product_id' => $m_id,
                'sub_sku' => 'MOD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'product_variation_id' => $mpv_id,
                'default_purchase_price' => 0,
                'dpp_inc_tax' => 0,
                'profit_percent' => 0,
                'default_sell_price' => rand(2, 5) * 1000,
                'sell_price_inc_tax' => rand(2, 5) * 1000,
                'created_at' => $today
            ]);
        }

        $tax_id = DB::table('tax_rates')->insertGetId([
            'business_id' => $business_id,
            'name' => 'PPN 11%',
            'amount' => 11,
            'created_by' => $user_id
        ]);

        // Discounts
        for ($i = 1; $i <= 3; $i++) {
            DB::table('discounts')->insert([
                'name' => 'Promo Diskon #' . $i,
                'business_id' => $business_id,
                'brand_id' => $brand_ids[array_rand($brand_ids)],
                'category_id' => $cat_ids[array_rand($cat_ids)],
                'location_id' => $loc1,
                'priority' => $i,
                'discount_type' => 'percentage',
                'discount_amount' => rand(5, 20),
                'starts_at' => Carbon::now()->subDays(15)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
                'is_active' => 1,
                'created_at' => $today
            ]);
        }

        // 4. Contacts (50 Customers, 20 Suppliers)
        $fnames = ['Andi', 'Budi', 'Cici', 'Dedi', 'Eko', 'Fani', 'Gita', 'Hadi', 'Indah', 'Joko'];
        $lnames = ['Saputra', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Santoso'];

        // Ensure default Walk-In Customer exists
        $contactUtil = new ContactUtil();
        $walkin = $contactUtil->getWalkInCustomer($business_id);

        $cust_ids = [$walkin['id'] ?? null];
        $cust_ids = array_filter($cust_ids);

        for ($i = 1; $i <= 50; $i++) {
            $fn = $fnames[array_rand($fnames)];
            $ln = $lnames[array_rand($lnames)];
            $cust_ids[] = DB::table('contacts')->insertGetId([
                'business_id' => $business_id,
                'type' => 'customer',
                'name' => $fn . ' ' . $ln . ' ' . $i,
                'contact_id' => 'CUST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'customer_group_id' => $cg_ids[array_rand($cg_ids)],
                'created_by' => $user_id,
                'mobile' => '08' . rand(11, 59) . rand(1000000, 9999999),
                'created_at' => $today,
                'first_name' => $fn,
                'last_name' => $ln
            ]);
        }

        $supp_ids = [];
        for ($i = 1; $i <= 20; $i++) {
            $supp_ids[] = DB::table('contacts')->insertGetId([
                'business_id' => $business_id,
                'type' => 'supplier',
                'name' => 'Supplier Utama Demo ' . $i,
                'contact_id' => 'SUPP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'created_by' => $user_id,
                'mobile' => '08' . rand(11, 59) . rand(1000000, 9999999),
                'created_at' => $today,
                'first_name' => 'Supplier',
                'last_name' => 'Demo'
            ]);
        }

        // 5. Products (100)
        $all_v_ids = [];
        for ($i = 1; $i <= 100; $i++) {
            $is_alert = ($i <= 5);
            $p_id = DB::table('products')->insertGetId([
                'name' => 'Produk Demo ' . $i,
                'business_id' => $business_id,
                'type' => 'single',
                'unit_id' => $all_u_ids[array_rand($all_u_ids)],
                'brand_id' => $brand_ids[array_rand($brand_ids)],
                'category_id' => $cat_ids[array_rand($cat_ids)],
                'tax' => $tax_id,
                'tax_type' => 'exclusive',
                'barcode_type' => 'C128',
                'enable_stock' => 1,
                'sku' => 'SKU-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'created_by' => $user_id,
                'created_at' => $today,
                'warranty_id' => $warranty_ids[array_rand($warranty_ids)],
                'alert_quantity' => $is_alert ? 500 : 10
            ]);

            DB::table('product_locations')->insert(['product_id' => $p_id, 'location_id' => $loc1]);
            DB::table('product_locations')->insert(['product_id' => $p_id, 'location_id' => $loc2]);

            $pv_id = DB::table('product_variations')->insertGetId(['name' => 'DUMMY', 'product_id' => $p_id, 'is_dummy' => 1]);
            $buy = rand(10, 200) * 1000;
            $sell = $buy * 1.3;

            $v_id = DB::table('variations')->insertGetId([
                'name' => 'DUMMY',
                'product_id' => $p_id,
                'sub_sku' => 'SKU-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'product_variation_id' => $pv_id,
                'default_purchase_price' => $buy,
                'dpp_inc_tax' => $buy * 1.11,
                'profit_percent' => 30,
                'default_sell_price' => $sell,
                'sell_price_inc_tax' => $sell * 1.11,
                'created_at' => $today
            ]);

            $all_v_ids[] = ['p_id' => $p_id, 'v_id' => $v_id, 'buy' => $buy, 'sell' => $sell];

            $qty1 = $is_alert ? 3 : rand(50, 500);
            $qty2 = $is_alert ? 3 : rand(50, 500);
            DB::table('variation_location_details')->insert(['product_id' => $p_id, 'product_variation_id' => $pv_id, 'variation_id' => $v_id, 'location_id' => $loc1, 'qty_available' => $qty1]);
            DB::table('variation_location_details')->insert(['product_id' => $p_id, 'product_variation_id' => $pv_id, 'variation_id' => $v_id, 'location_id' => $loc2, 'qty_available' => $qty2]);
        }

        // 6. Sell Transactions (200)
        $sell_types = [
            ['status' => 'final', 'is_direct_sale' => 1, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'Sale'],
            ['status' => 'final', 'is_direct_sale' => 0, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'POS'],
            ['status' => 'draft', 'is_direct_sale' => 0, 'is_quotation' => 0, 'sub_status' => null, 'label' => 'Draft'],
            ['status' => 'draft', 'is_direct_sale' => 0, 'is_quotation' => 1, 'sub_status' => 'quotation', 'label' => 'Quotation']
        ];
        $all_sell_ids = [];

        foreach ($sell_types as $stype) {
            for ($i = 1; $i <= 50; $i++) {
                $p = $all_v_ids[array_rand($all_v_ids)];
                $dt = Carbon::now()->subDays(rand(0, 90))->format('Y-m-d H:i:s');
                $is_kitchen = ($stype['label'] == 'POS' && rand(0, 1));

                $tid = DB::table('transactions')->insertGetId([
                    'business_id' => $business_id,
                    'location_id' => $loc1,
                    'type' => 'sell',
                    'status' => $stype['status'],
                    'is_direct_sale' => $stype['is_direct_sale'],
                    'is_quotation' => $stype['is_quotation'],
                    'sub_status' => $stype['sub_status'],
                    'payment_status' => ($stype['status'] == 'final' ? 'paid' : 'due'),
                    'contact_id' => $cust_ids[array_rand($cust_ids)],
                    'invoice_no' => 'INV-' . $stype['label'] . '-' . Str::random(4) . '-' . $i,
                    'transaction_date' => $dt,
                    'total_before_tax' => $p['sell'],
                    'is_kitchen_order' => $is_kitchen ? 1 : 0,
                    'res_table_id' => $is_kitchen ? $table_ids[array_rand($table_ids)] : null,
                    'res_waiter_id' => $is_kitchen ? $user_id : null,
                    'res_order_status' => $is_kitchen ? ['received', 'cooked', 'served'][rand(0, 2)] : null,
                    'final_total' => $p['sell'],
                    'created_by' => $user_id,
                    'created_at' => $dt
                ]);
                $all_sell_ids[] = $tid;

                $line_id = DB::table('transaction_sell_lines')->insertGetId([
                    'transaction_id' => $tid,
                    'product_id' => $p['p_id'],
                    'variation_id' => $p['v_id'],
                    'quantity' => 1,
                    'unit_price' => $p['sell'],
                    'unit_price_inc_tax' => $p['sell'],
                    'item_tax' => 0,
                    'unit_price_before_discount' => $p['sell'],
                    'res_line_order_status' => $is_kitchen ? ['received', 'cooked', 'served'][rand(0, 2)] : null,
                    'res_service_staff_id' => $is_kitchen ? $user_id : null,
                    'created_at' => $dt
                ]);

                if ($is_kitchen && rand(0, 1)) {
                    $m_v_id = $modifier_ids[array_rand($modifier_ids)];
                    $m_v = DB::table('variations')->where('id', $m_v_id)->first();
                    if ($m_v) {
                        DB::table('transaction_sell_lines')->insert([
                            'transaction_id' => $tid,
                            'product_id' => $m_v->product_id,
                            'variation_id' => $m_v_id,
                            'quantity' => 1,
                            'unit_price' => $m_v->default_sell_price,
                            'unit_price_inc_tax' => $m_v->sell_price_inc_tax,
                            'item_tax' => 0,
                            'unit_price_before_discount' => $m_v->default_sell_price,
                            'parent_sell_line_id' => $line_id,
                            'children_type' => 'modifier',
                            'created_at' => $dt
                        ]);
                    }
                }

                if ($stype['status'] == 'final') {
                    DB::table('transaction_payments')->insert([
                        'transaction_id' => $tid,
                        'business_id' => $business_id,
                        'amount' => $p['sell'],
                        'method' => 'cash',
                        'paid_on' => $dt,
                        'created_by' => $user_id,
                        'payment_for' => $cust_ids[array_rand($cust_ids)],
                        'payment_ref_no' => 'PAY-SELL-' . Str::random(5),
                        'created_at' => $dt
                    ]);
                }
            }
        }

        // 7. Purchases & Purchase Returns (50 each)
        for ($i = 1; $i <= 50; $i++) {
            $p = $all_v_ids[array_rand($all_v_ids)];
            $qty = rand(5, 30);
            $total = $p['buy'] * $qty;
            $dt = Carbon::now()->subDays(rand(0, 90))->format('Y-m-d H:i:s');

            $tid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id,
                'location_id' => $loc1,
                'type' => 'purchase',
                'status' => 'received',
                'payment_status' => 'paid',
                'contact_id' => $supp_ids[array_rand($supp_ids)],
                'ref_no' => 'PUR-' . Str::random(4) . '-' . $i,
                'transaction_date' => $dt,
                'total_before_tax' => $total,
                'final_total' => $total,
                'created_by' => $user_id,
                'created_at' => $dt
            ]);

            DB::table('purchase_lines')->insert([
                'transaction_id' => $tid,
                'product_id' => $p['p_id'],
                'variation_id' => $p['v_id'],
                'quantity' => $qty,
                'purchase_price' => $p['buy'],
                'purchase_price_inc_tax' => $p['buy'] * 1.11,
                'item_tax' => $p['buy'] * 0.11,
                'created_at' => $dt
            ]);

            DB::table('transaction_payments')->insert([
                'transaction_id' => $tid,
                'business_id' => $business_id,
                'amount' => $total,
                'method' => 'cash',
                'paid_on' => $dt,
                'created_by' => $user_id,
                'payment_for' => $supp_ids[array_rand($supp_ids)],
                'payment_ref_no' => 'PAY-PUR-' . Str::random(5),
                'created_at' => $dt
            ]);
        }

        // 8. Expenses (50)
        for ($i = 1; $i <= 50; $i++) {
            $dt = Carbon::now()->subDays(rand(0, 90))->format('Y-m-d H:i:s');
            $amt = rand(10, 200) * 1000;

            $tid = DB::table('transactions')->insertGetId([
                'business_id' => $business_id,
                'location_id' => (rand(0, 1) ? $loc1 : $loc2),
                'type' => 'expense',
                'status' => 'final',
                'payment_status' => 'paid',
                'ref_no' => 'EXP-' . Str::random(4) . '-' . $i,
                'transaction_date' => $dt,
                'total_before_tax' => $amt,
                'final_total' => $amt,
                'expense_category_id' => $exp_cat_ids[array_rand($exp_cat_ids)],
                'expense_for' => $user_id,
                'created_by' => $user_id,
                'created_at' => $dt
            ]);

            DB::table('transaction_payments')->insert([
                'transaction_id' => $tid,
                'business_id' => $business_id,
                'amount' => $amt,
                'method' => 'cash',
                'paid_on' => $dt,
                'created_by' => $user_id,
                'payment_ref_no' => 'PAY-EXP-' . Str::random(5),
                'created_at' => $dt
            ]);
        }

        // 9. Accounts & Financial Setup
        $asset_lancar_type_id = DB::table('account_types')->insertGetId(['name' => 'Aktiva Lancar', 'business_id' => $business_id, 'created_at' => $today]);
        $asset_tetap_type_id = DB::table('account_types')->insertGetId(['name' => 'Aktiva Tetap', 'business_id' => $business_id, 'created_at' => $today]);
        $equity_type_id = DB::table('account_types')->insertGetId(['name' => 'Ekuitas', 'business_id' => $business_id, 'created_at' => $today]);

        $accounts_data = [
            ['name' => 'Kas Tunai Utama', 'account_number' => '101001', 'account_type_id' => $asset_lancar_type_id],
            ['name' => 'Bank BCA - 8820123xxx', 'account_number' => '101002', 'account_type_id' => $asset_lancar_type_id],
            ['name' => 'Bank Mandiri - 131001xxx', 'account_number' => '101003', 'account_type_id' => $asset_lancar_type_id],
            ['name' => 'Peralatan Kantor', 'account_number' => '102001', 'account_type_id' => $asset_tetap_type_id],
            ['name' => 'Modal Pemilik', 'account_number' => '301001', 'account_type_id' => $equity_type_id]
        ];

        $acc_ids = [];
        foreach ($accounts_data as $ad) {
            $aid = DB::table('accounts')->insertGetId(array_merge($ad, [
                'business_id' => $business_id,
                'created_by' => $user_id,
                'created_at' => $today
            ]));
            $acc_ids[] = $aid;

            DB::table('account_transactions')->insert([
                'account_id' => $aid,
                'type' => 'credit',
                'sub_type' => 'opening_balance',
                'amount' => rand(10000, 50000) * 1000,
                'reff_no' => 'OB-' . Str::random(5),
                'operation_date' => Carbon::now()->subMonths(3)->format('Y-m-d H:i:s'),
                'created_by' => $user_id,
                'created_at' => $today
            ]);
        }

        // Link payments to accounts
        $payments = DB::table('transaction_payments')->where('business_id', $business_id)->get();
        foreach ($payments as $pay) {
            $aid = $acc_ids[array_rand($acc_ids)];
            $tx = DB::table('transactions')->where('id', $pay->transaction_id)->first();
            if ($tx) {
                $type = in_array($tx->type, ['sell', 'purchase_return']) ? 'credit' : 'debit';
                DB::table('account_transactions')->insert([
                    'account_id' => $aid,
                    'type' => $type,
                    'amount' => $pay->amount,
                    'reff_no' => 'PAY-' . Str::random(5),
                    'operation_date' => $pay->created_at,
                    'created_by' => $user_id,
                    'transaction_id' => $tx->id,
                    'transaction_payment_id' => $pay->id,
                    'created_at' => $today
                ]);
                DB::table('transaction_payments')->where('id', $pay->id)->update(['account_id' => $aid]);
            }
        }

        // 10. Cash Registers
        $register_id = DB::table('cash_registers')->insertGetId([
            'business_id' => $business_id,
            'location_id' => $loc1,
            'user_id' => $user_id,
            'status' => 'open',
            'created_at' => $today
        ]);

        $pos_txs = DB::table('transactions')->where('business_id', $business_id)->where('type', 'sell')->where('is_direct_sale', 0)->get();
        foreach ($pos_txs as $tx) {
            DB::table('cash_register_transactions')->insert([
                'cash_register_id' => $register_id,
                'amount' => $tx->final_total,
                'pay_method' => 'cash',
                'type' => 'debit',
                'transaction_type' => 'sell',
                'transaction_id' => $tx->id,
                'created_at' => $tx->created_at
            ]);
        }

        // 11. Bookings (10)
        if (Schema::hasTable('bookings')) {
            for ($i = 1; $i <= 10; $i++) {
                $start = Carbon::now()->addDays(rand(-10, 10))->addHours(rand(0, 12));
                $end = (clone $start)->addHours(2);

                DB::table('bookings')->insert([
                    'business_id' => $business_id,
                    'location_id' => $loc1,
                    'contact_id' => $cust_ids[array_rand($cust_ids)],
                    'waiter_id' => $user_id,
                    'table_id' => $table_ids[array_rand($table_ids)],
                    'booking_start' => $start->format('Y-m-d H:i:s'),
                    'booking_end' => $end->format('Y-m-d H:i:s'),
                    'created_by' => $user_id,
                    'booking_status' => 'booked',
                    'booking_note' => 'Booking demo #' . $i,
                    'created_at' => $today
                ]);
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        return true;
    }

    /**
     * Cleans up business data across all modules before seeding demo data.
     */
    protected function cleanBusinessData($business_id)
    {
        // Delete all transactions and lines
        $tx_ids = DB::table('transactions')->where('business_id', $business_id)->pluck('id')->toArray();
        if (!empty($tx_ids)) {
            DB::table('transaction_payments')->whereIn('transaction_id', $tx_ids)->delete();
            DB::table('transaction_sell_lines')->whereIn('transaction_id', $tx_ids)->delete();
            DB::table('purchase_lines')->whereIn('transaction_id', $tx_ids)->delete();
            DB::table('stock_adjustment_lines')->whereIn('transaction_id', $tx_ids)->delete();
            DB::table('account_transactions')->whereIn('transaction_id', $tx_ids)->delete();
            DB::table('transactions')->whereIn('id', $tx_ids)->delete();
        }

        // Delete cash registers
        $reg_ids = DB::table('cash_registers')->where('business_id', $business_id)->pluck('id')->toArray();
        if (!empty($reg_ids)) {
            DB::table('cash_register_transactions')->whereIn('cash_register_id', $reg_ids)->delete();
            DB::table('cash_registers')->whereIn('id', $reg_ids)->delete();
        }

        // Delete accounts
        $acc_ids = DB::table('accounts')->where('business_id', $business_id)->pluck('id')->toArray();
        if (!empty($acc_ids)) {
            DB::table('account_transactions')->whereIn('account_id', $acc_ids)->delete();
            DB::table('accounts')->whereIn('id', $acc_ids)->delete();
        }
        DB::table('account_types')->where('business_id', $business_id)->delete();

        // Accounting module accounts & transactions
        if (Schema::hasTable('accounting_accounts')) {
            $acc_ac_ids = DB::table('accounting_accounts')->where('business_id', $business_id)->pluck('id')->toArray();
            if (!empty($acc_ac_ids)) {
                DB::table('accounting_accounts_transactions')->whereIn('accounting_account_id', $acc_ac_ids)->delete();
                DB::table('accounting_accounts')->whereIn('id', $acc_ac_ids)->delete();
            }
            DB::table('accounting_acc_trans_mappings')->where('business_id', $business_id)->delete();
            DB::table('accounting_account_types')->where('business_id', $business_id)->delete();
        }

        // Delete products & variations
        $p_ids = DB::table('products')->where('business_id', $business_id)->pluck('id')->toArray();
        if (!empty($p_ids)) {
            DB::table('variation_location_details')->whereIn('product_id', $p_ids)->delete();
            DB::table('product_locations')->whereIn('product_id', $p_ids)->delete();
            DB::table('variations')->whereIn('product_id', $p_ids)->delete();
            DB::table('product_variations')->whereIn('product_id', $p_ids)->delete();
            DB::table('products')->whereIn('id', $p_ids)->delete();
        }

        // Delete master data
        DB::table('contacts')->where('business_id', $business_id)->where('is_default', '!=', 1)->delete();
        DB::table('categories')->where('business_id', $business_id)->delete();
        DB::table('expense_categories')->where('business_id', $business_id)->delete();
        DB::table('brands')->where('business_id', $business_id)->delete();
        DB::table('units')->where('business_id', $business_id)->delete();
        DB::table('warranties')->where('business_id', $business_id)->delete();
        DB::table('customer_groups')->where('business_id', $business_id)->delete();
        DB::table('discounts')->where('business_id', $business_id)->delete();
        DB::table('tax_rates')->where('business_id', $business_id)->delete();
        DB::table('res_tables')->where('business_id', $business_id)->delete();

        if (Schema::hasTable('bookings')) {
            DB::table('bookings')->where('business_id', $business_id)->delete();
        }

        if (Schema::hasTable('repair_job_sheets')) {
            DB::table('repair_job_sheets')->where('business_id', $business_id)->delete();
        }
    }
}
