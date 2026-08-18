<?php

use Illuminate\Database\Migrations\Migration;
use App\Business;
use App\Account;
use App\AccountType;
use Modules\Accounting\Entities\AccountingAccount;

class AddBiayaPenyusutanDefaultAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            $business_id = $business->id;

            // 1. Ensure POS AccountType 'beban_operasional' exists
            $type = AccountType::where('business_id', $business_id)
                                ->where('fixed_key', 'beban_operasional')
                                ->first();
            if (!$type) {
                $translated_name = __('account.beban_operasional');
                $type = AccountType::create([
                    'name' => $translated_name ?: 'Beban Operasional',
                    'business_id' => $business_id,
                    'parent_account_type_id' => null,
                    'fixed_key' => 'beban_operasional'
                ]);
            }

            // 2. Ensure POS Account 'Biaya Penyusutan' exists
            $posAccount = Account::where('business_id', $business_id)
                                 ->where('name', 'Biaya Penyusutan')
                                 ->first();

            if (!$posAccount) {
                $posAccount = Account::create([
                    'name' => 'Biaya Penyusutan',
                    'business_id' => $business_id,
                    'account_number' => '6105',
                    'account_type_id' => $type->id,
                    'normal_balance' => 'debit',
                    'created_by' => $business->owner_id ?? 1
                ]);
            }

            // 3. Ensure AccountingAccount 'Biaya Penyusutan' exists if Accounting Module installed
            if (class_exists(AccountingAccount::class)) {
                $accountingAccount = AccountingAccount::where('business_id', $business_id)
                                                       ->where('name', 'Biaya Penyusutan')
                                                       ->first();

                if (!$accountingAccount) {
                    AccountingAccount::create([
                        'name' => 'Biaya Penyusutan',
                        'business_id' => $business_id,
                        'account_primary_type' => 'expenses',
                        'account_sub_type_id' => 14,
                        'detail_type_id' => 126,
                        'status' => 'active',
                        'gl_code' => '61005',
                        'created_by' => $business->owner_id ?? 1
                    ]);
                }
            }
        }

        // 4. Run pos:sync-payment-accounting to ensure links are synced
        try {
            \Illuminate\Support\Facades\Artisan::call('pos:sync-payment-accounting');
        } catch (\Exception $e) {
            \Log::error('Error running pos:sync-payment-accounting in AddBiayaPenyusutanDefaultAccount migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback needed for default account seeding
    }
}
