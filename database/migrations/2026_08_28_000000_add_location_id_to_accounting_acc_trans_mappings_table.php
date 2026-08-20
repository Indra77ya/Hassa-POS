<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddLocationIdToAccountingAccTransMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('accounting_acc_trans_mappings') && !Schema::hasColumn('accounting_acc_trans_mappings', 'location_id')) {
            Schema::table('accounting_acc_trans_mappings', function (Blueprint $table) {
                $table->integer('location_id')->unsigned()->nullable()->after('business_id')->index();
            });
        }

        // Backfill location_id for existing asset acquisition and depreciation journals
        if (Schema::hasTable('assets') && Schema::hasTable('accounting_acc_trans_mappings')) {
            // 1. Asset acquisition mappings
            DB::statement("
                UPDATE accounting_acc_trans_mappings ATM
                JOIN assets A ON A.accounting_acc_trans_mapping_id = ATM.id
                SET ATM.location_id = A.location_id
                WHERE A.location_id IS NOT NULL AND ATM.location_id IS NULL
            ");

            // 2. Asset depreciation mappings
            if (Schema::hasTable('asset_depreciation_logs')) {
                DB::statement("
                    UPDATE accounting_acc_trans_mappings ATM
                    JOIN asset_depreciation_logs ADL ON ADL.accounting_acc_trans_mapping_id = ATM.id
                    JOIN assets A ON ADL.asset_id = A.id
                    SET ATM.location_id = A.location_id
                    WHERE A.location_id IS NOT NULL AND ATM.location_id IS NULL
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('accounting_acc_trans_mappings') && Schema::hasColumn('accounting_acc_trans_mappings', 'location_id')) {
            Schema::table('accounting_acc_trans_mappings', function (Blueprint $table) {
                $table->dropColumn('location_id');
            });
        }
    }
}
