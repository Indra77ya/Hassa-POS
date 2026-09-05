<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLaundryColumnsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'laundry_order_sheet_id')) {
                $table->unsignedBigInteger('laundry_order_sheet_id')->nullable();
                $table->foreign('laundry_order_sheet_id')->references('id')->on('laundry_order_sheets')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'laundry_order_sheet_id')) {
                $table->dropForeign(['laundry_order_sheet_id']);
                $table->dropColumn('laundry_order_sheet_id');
            }
        });
    }
}
