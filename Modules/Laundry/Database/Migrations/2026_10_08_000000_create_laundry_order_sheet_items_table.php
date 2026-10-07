<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaundryOrderSheetItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('laundry_order_sheet_items')) {
            Schema::create('laundry_order_sheet_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('laundry_order_sheet_id');
                $table->unsignedBigInteger('laundry_item_type_id')->nullable();
                $table->unsignedBigInteger('laundry_service_type_id')->nullable();
                $table->unsignedBigInteger('laundry_status_id')->nullable();
                $table->decimal('quantity', 15, 2)->default(1.00);
                $table->string('unit_name')->default('kg');
                $table->decimal('unit_price', 22, 4)->default(0.0000);
                $table->decimal('subtotal', 22, 4)->default(0.0000);
                $table->text('items_detail')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('laundry_order_sheet_id')->references('id')->on('laundry_order_sheets')->onDelete('cascade');
                $table->foreign('laundry_item_type_id')->references('id')->on('laundry_item_types')->onDelete('set null');
                $table->foreign('laundry_service_type_id')->references('id')->on('laundry_service_types')->onDelete('set null');
                $table->foreign('laundry_status_id')->references('id')->on('laundry_statuses')->onDelete('set null');
            });
        }

        if (Schema::hasTable('laundry_order_process_logs') && !Schema::hasColumn('laundry_order_process_logs', 'laundry_order_sheet_item_id')) {
            Schema::table('laundry_order_process_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('laundry_order_sheet_item_id')->nullable()->after('order_sheet_id');
                $table->foreign('laundry_order_sheet_item_id')->references('id')->on('laundry_order_sheet_items')->onDelete('cascade');
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
        if (Schema::hasTable('laundry_order_process_logs') && Schema::hasColumn('laundry_order_process_logs', 'laundry_order_sheet_item_id')) {
            Schema::table('laundry_order_process_logs', function (Blueprint $table) {
                $table->dropForeign(['laundry_order_sheet_item_id']);
                $table->dropColumn('laundry_order_sheet_item_id');
            });
        }

        Schema::dropIfExists('laundry_order_sheet_items');
    }
}
