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
        if (Schema::hasTable('laundry_order_sheets') && !Schema::hasColumn('laundry_order_sheets', 'whatsapp_sent_at')) {
            Schema::table('laundry_order_sheets', function (Blueprint $table) {
                $table->dateTime('whatsapp_sent_at')->nullable()->after('completed_at');
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
        if (Schema::hasTable('laundry_order_sheets') && Schema::hasColumn('laundry_order_sheets', 'whatsapp_sent_at')) {
            Schema::table('laundry_order_sheets', function (Blueprint $table) {
                $table->dropColumn('whatsapp_sent_at');
            });
        }
    }
};
