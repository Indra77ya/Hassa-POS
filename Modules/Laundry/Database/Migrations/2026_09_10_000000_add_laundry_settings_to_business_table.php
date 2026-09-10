<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLaundrySettingsToBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('business', 'laundry_settings')) {
            Schema::table('business', function (Blueprint $table) {
                $table->text('laundry_settings')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('business', 'laundry_settings')) {
            Schema::table('business', function (Blueprint $table) {
                $table->dropColumn('laundry_settings');
            });
        }
    }
}
