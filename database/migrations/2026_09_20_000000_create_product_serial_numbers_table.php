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
        if (!Schema::hasTable('product_serial_numbers')) {
            Schema::create('product_serial_numbers', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');

                $table->integer('product_id')->unsigned();
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

                $table->integer('variation_id')->unsigned()->nullable();
                $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');

                $table->string('serial_number')->index();

                $table->integer('purchase_line_id')->unsigned()->nullable();
                $table->foreign('purchase_line_id')->references('id')->on('purchase_lines')->onDelete('set null');

                $table->integer('transaction_sell_line_id')->unsigned()->nullable();
                $table->foreign('transaction_sell_line_id')->references('id')->on('transaction_sell_lines')->onDelete('set null');

                $table->decimal('purchase_price', 22, 4)->default(0);
                $table->decimal('selling_price', 22, 4)->default(0);

                $table->enum('status', ['in_stock', 'sold', 'used_in_repair', 'returned'])->default('in_stock')->index();

                $table->text('notes')->nullable();
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
        Schema::dropIfExists('product_serial_numbers');
    }
};
