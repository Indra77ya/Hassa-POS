<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairTradeInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repair_trade_ins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->integer('job_sheet_id')->unsigned()->nullable();
            $table->string('model_name')->nullable();
            $table->string('serial_no')->nullable();
            $table->text('condition')->nullable();
            $table->decimal('trade_in_value', 22, 4)->default(0);
            $table->decimal('resale_price', 22, 4)->default(0);
            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('purchase_transaction_id')->unsigned()->nullable();
            $table->integer('created_by')->unsigned();
            $table->timestamps();
        });
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
