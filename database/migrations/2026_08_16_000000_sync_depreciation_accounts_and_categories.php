<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AccountTypeController;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $businesses = \App\Business::pluck('id');
            foreach ($businesses as $business_id) {
                AccountTypeController::syncDepreciationForBusiness($business_id, 1);
            }
        } catch (\Exception $e) {
            \Log::error('Migration sync_depreciation_accounts_and_categories failed: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
