<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('created_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->string('name');
            $table->string('asset_code')->nullable();
            $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->onDelete('set null');
            $table->integer('location_id')->unsigned()->nullable();
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('set null');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 22, 4);
            $table->decimal('salvage_value', 22, 4)->default(0);
            $table->integer('useful_life'); // Useful life in months
            $table->string('depreciation_method')->default('straight_line');
            $table->string('status')->default('active'); // active, sold, disposed
            $table->text('description')->nullable();
            $table->integer('created_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('asset_depreciation_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->date('depreciation_date');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('amount', 22, 4);
            $table->integer('accounting_acc_trans_mapping_id')->unsigned()->nullable();
            $table->timestamps();

            $table->unique(['asset_id', 'year', 'month'], 'asset_depr_unique_month');
        });

        Schema::create('asset_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->integer('depreciation_expense_account_id')->unsigned()->nullable();
            $table->integer('accumulated_depreciation_account_id')->unsigned()->nullable();
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
        Schema::dropIfExists('asset_settings');
        Schema::dropIfExists('asset_depreciation_logs');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
