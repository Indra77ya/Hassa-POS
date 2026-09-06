<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaundryOrderSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laundry_order_sheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->string('order_no');
            $table->integer('contact_id')->unsigned();
            $table->unsignedBigInteger('laundry_status_id')->nullable();
            $table->unsignedBigInteger('laundry_service_type_id')->nullable();
            $table->unsignedBigInteger('laundry_item_type_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->string('unit_name')->default('kg');
            $table->string('delivery_type')->default('self_service'); // self_service, pickup_delivery
            $table->dateTime('received_at')->nullable();
            $table->dateTime('estimated_completion_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('items_detail')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->unsigned();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('laundry_status_id')->references('id')->on('laundry_statuses')->onDelete('set null');
            $table->foreign('laundry_service_type_id')->references('id')->on('laundry_service_types')->onDelete('set null');
            $table->foreign('laundry_item_type_id')->references('id')->on('laundry_item_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laundry_order_sheets');
    }
}
