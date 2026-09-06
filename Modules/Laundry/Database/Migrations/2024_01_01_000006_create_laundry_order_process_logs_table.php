<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaundryOrderProcessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laundry_order_process_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_sheet_id');
            $table->unsignedBigInteger('laundry_process_id');
            $table->integer('staff_id')->unsigned()->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->decimal('points_earned', 10, 2)->default(0.00);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('order_sheet_id')->references('id')->on('laundry_order_sheets')->onDelete('cascade');
            $table->foreign('laundry_process_id')->references('id')->on('laundry_processes')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laundry_order_process_logs');
    }
}
