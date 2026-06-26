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
        Schema::table('cash_register_transactions', function (Blueprint $table) {
            $table->unsignedInteger('transaction_payment_id')->nullable()->after('transaction_id');
            $table->foreign('transaction_payment_id')
                  ->references('id')
                  ->on('transaction_payments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_register_transactions', function (Blueprint $table) {
            $table->dropForeign(['transaction_payment_id']);
            $table->dropColumn('transaction_payment_id');
        });
    }
};
