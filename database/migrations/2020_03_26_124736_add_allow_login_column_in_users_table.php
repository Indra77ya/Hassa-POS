<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('allow_login')->default(1)->after('business_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE users CHANGE username username VARCHAR(191) NULL;');
        }
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE users CHANGE password password VARCHAR(191) NULL;');
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
};
