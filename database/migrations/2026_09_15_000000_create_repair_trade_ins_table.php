<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepairTradeInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('repair_trade_ins')) {
            Schema::create('repair_trade_ins', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('business_id')->unsigned()->index();
                $table->integer('user_id')->unsigned()->index();
                $table->integer('transaction_id')->unsigned()->nullable()->index();
                $table->integer('job_sheet_id')->unsigned()->nullable()->index();
                $table->integer('product_id')->unsigned()->nullable()->index();
                $table->integer('purchase_transaction_id')->unsigned()->nullable()->index();
                $table->decimal('amount', 22, 4)->default(0);
                $table->string('serial_no')->nullable();
                $table->string('condition')->nullable();
                $table->text('details')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('repair_trade_ins');
    }
}
