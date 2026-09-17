<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitAndCategoryToRepairTradeInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repair_trade_ins', function (Blueprint $table) {
            $table->integer('unit_id')->unsigned()->nullable()->after('condition');
            $table->integer('category_id')->unsigned()->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repair_trade_ins', function (Blueprint $table) {
            $table->dropColumn(['unit_id', 'category_id']);
        });
    }
}
