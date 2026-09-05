<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcessIdsToLaundryItemTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laundry_item_types', function (Blueprint $table) {
            if (!Schema::hasColumn('laundry_item_types', 'process_ids')) {
                $table->text('process_ids')->nullable()->after('description');
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
            if (Schema::hasColumn('laundry_item_types', 'process_ids')) {
                $table->dropColumn('process_ids');
            }
        });
    }
}
