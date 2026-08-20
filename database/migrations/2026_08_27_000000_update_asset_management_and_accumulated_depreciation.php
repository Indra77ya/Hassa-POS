<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Entities\AccountingAccount;

class UpdateAssetManagementAndAccumulatedDepreciation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (!Schema::hasColumn('assets', 'fixed_asset_account_id')) {
                    $table->integer('fixed_asset_account_id')->nullable()->after('useful_life');
                }
                if (!Schema::hasColumn('assets', 'payment_account_id')) {
                    $table->integer('payment_account_id')->nullable()->after('fixed_asset_account_id');
                }
                if (!Schema::hasColumn('assets', 'accounting_acc_trans_mapping_id')) {
                    $table->integer('accounting_acc_trans_mapping_id')->nullable()->after('payment_account_id');
                }
            });
        }

        // Hotfix existing 'Akumulasi Penyusutan' accounts in accounting_accounts table
        if (Schema::hasTable('accounting_accounts')) {
            AccountingAccount::where('name', 'like', '%Akumulasi Penyusutan%')
                ->update([
                    'account_sub_type_id' => 17, // Akumulasi Penyusutan (Non-Current Assets / Contra-Asset)
                    'account_primary_type' => 'asset',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'fixed_asset_account_id')) {
                    $table->dropColumn('fixed_asset_account_id');
                }
                if (Schema::hasColumn('assets', 'payment_account_id')) {
                    $table->dropColumn('payment_account_id');
                }
                if (Schema::hasColumn('assets', 'accounting_acc_trans_mapping_id')) {
                    $table->dropColumn('accounting_acc_trans_mapping_id');
                }
            });
        }
    }
}
