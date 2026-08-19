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
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('depreciation_expense_account_id')->nullable();
            $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->unsignedBigInteger('asset_category_id')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('historical_cost', 22, 4)->default(0);
            $table->decimal('salvage_value', 22, 4)->default(0);
            $table->date('purchase_date');
            $table->integer('useful_life_months')->default(12);
            $table->string('depreciation_method')->default('straight_line');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_disposed')->default(false);
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_amount', 22, 4)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('asset_category_id')->references('id')->on('asset_categories')->onDelete('set null');
        });

        Schema::create('asset_depreciation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedBigInteger('asset_id');
            $table->date('depreciation_date');
            $table->decimal('amount', 22, 4)->default(0);
            $table->unsignedBigInteger('accounting_accounts_transaction_debit_id')->nullable();
            $table->unsignedBigInteger('accounting_accounts_transaction_credit_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_depreciation_logs');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
