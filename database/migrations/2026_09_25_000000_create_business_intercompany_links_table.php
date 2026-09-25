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
        if (!Schema::hasTable('business_intercompany_links')) {
            Schema::create('business_intercompany_links', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');

                $table->integer('linked_business_id')->unsigned();
                $table->foreign('linked_business_id')->references('id')->on('business')->onDelete('cascade');

                $table->integer('contact_id')->unsigned()->comment('Contact ID in business_id representing linked_business_id');
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

                $table->integer('linked_contact_id')->unsigned()->nullable()->comment('Contact ID in linked_business_id representing business_id');
                $table->foreign('linked_contact_id')->references('id')->on('contacts')->onDelete('set null');

                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('transactions', 'intercompany_linked_transaction_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->integer('intercompany_linked_transaction_id')->unsigned()->nullable()->index()->after('id');
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
        if (Schema::hasColumn('transactions', 'intercompany_linked_transaction_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('intercompany_linked_transaction_id');
            });
        }
        Schema::dropIfExists('business_intercompany_links');
    }
};
