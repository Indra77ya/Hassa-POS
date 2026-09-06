<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVariationIdToLaundryItemTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laundry_item_types', function (Blueprint $table) {
            if (!Schema::hasColumn('laundry_item_types', 'variation_id')) {
                $table->unsignedInteger('variation_id')->nullable()->after('default_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laundry_item_types', function (Blueprint $table) {
            if (Schema::hasColumn('laundry_item_types', 'variation_id')) {
                $table->dropColumn('variation_id');
            }
        });
    }
}
