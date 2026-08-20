<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Business;
use Modules\AssetManagement\Entities\AssetSetting;

class SyncAssetManagementAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_settings') || !Schema::hasTable('accounting_accounts')) {
            return;
        }

        $businesses = Business::pluck('id');
        foreach ($businesses as $business_id) {
            try {
                AssetSetting::forBusiness($business_id);
            } catch (\Exception $e) {
                \Log::error('Error syncing asset management accounts for business ' . $business_id . ': ' . $e->getMessage());
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
    }
}
