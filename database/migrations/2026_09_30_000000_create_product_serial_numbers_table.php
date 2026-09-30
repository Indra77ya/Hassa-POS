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
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('variation_id')->nullable();
                $table->unsignedInteger('location_id')->nullable();
                $table->unsignedInteger('purchase_line_id')->nullable();
                $table->unsignedInteger('transaction_sell_line_id')->nullable();
                $table->unsignedInteger('repair_job_sheet_id')->nullable();
                $table->string('serial_number');
                $table->decimal('purchase_price', 22, 4)->nullable();
                $table->decimal('selling_price', 22, 4)->nullable();
                $table->string('status')->default('in_stock'); // in_stock, sold, used_in_repair, returned
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'product_id']);
                $table->index(['business_id', 'serial_number']);
                $table->index('status');
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
