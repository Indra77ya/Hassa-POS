<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'laundry_bonus_per_point')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('laundry_bonus_per_point', 22, 4)->default(0)->after('essentials_salary');
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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'laundry_bonus_per_point')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('laundry_bonus_per_point');
            });
        }
    }
};
