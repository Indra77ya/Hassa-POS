<?php

use Illuminate\Database\Migrations\Migration;
use App\Business;
use Modules\Accounting\Entities\AccountingAccount;

class SeedManufacturingDefaultAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $businesses = Business::all();

            foreach ($businesses as $business) {
                $business_id = $business->id;

                // 1. Raw Materials Inventory Account
                $raw_mat_acc = AccountingAccount::where('business_id', $business_id)
                    ->where(function($q) {
                        $q->where('name', 'like', '%Persediaan Bahan Baku%')
                          ->orWhere('name', 'like', '%Raw Material%');
                    })
                    ->first();

                if (!$raw_mat_acc) {
                    AccountingAccount::create([
                        'name' => 'Persediaan Bahan Baku',
                        'gl_code' => '1130',
                        'business_id' => $business_id,
                        'account_primary_type' => 'asset',
                        'account_sub_type_id' => 2,
                        'detail_type_id' => 21,
                        'status' => 'active',
                        'created_by' => 1,
                    ]);
                }

                // 2. Finished Goods Inventory Account
                $finished_acc = AccountingAccount::where('business_id', $business_id)
                    ->where(function($q) {
                        $q->where('name', 'like', '%Persediaan Barang Jadi%')
                          ->orWhere('name', 'like', '%Finished Goods%');
                    })
                    ->first();

                if (!$finished_acc) {
                    AccountingAccount::create([
                        'name' => 'Persediaan Barang Jadi',
                        'gl_code' => '1140',
                        'business_id' => $business_id,
                        'account_primary_type' => 'asset',
                        'account_sub_type_id' => 2,
                        'detail_type_id' => 21,
                        'status' => 'active',
                        'created_by' => 1,
                    ]);
                }

                // 3. Production Cost / Overhead Account
                $prod_cost_acc = AccountingAccount::where('business_id', $business_id)
                    ->where(function($q) {
                        $q->where('name', 'like', '%Biaya Produksi%')
                          ->orWhere('name', 'like', '%Overhead%')
                          ->orWhere('name', 'like', '%Production Cost%');
                    })
                    ->first();

                if (!$prod_cost_acc) {
                    AccountingAccount::create([
                        'name' => 'Biaya Produksi / Overhead',
                        'gl_code' => '5100',
                        'business_id' => $business_id,
                        'account_primary_type' => 'expenses',
                        'account_sub_type_id' => 14,
                        'detail_type_id' => 138,
                        'status' => 'active',
                        'created_by' => 1,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in SeedManufacturingDefaultAccounts migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
