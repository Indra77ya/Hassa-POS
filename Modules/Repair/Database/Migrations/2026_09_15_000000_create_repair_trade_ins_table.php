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
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->integer('location_id')->unsigned()->nullable();
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->integer('job_sheet_id')->unsigned()->nullable();
            $table->integer('purchase_transaction_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('variation_id')->unsigned()->nullable();
            $table->string('device_name');
            $table->integer('brand_id')->unsigned()->nullable();
            $table->integer('device_model_id')->unsigned()->nullable();
            $table->string('serial_no')->nullable();
            $table->text('condition_notes')->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->decimal('valuation_amount', 22, 4)->default(0);
            $table->decimal('selling_price', 22, 4)->default(0);
            $table->integer('created_by')->unsigned();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
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
