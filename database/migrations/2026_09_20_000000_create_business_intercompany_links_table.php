<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessIntercompanyLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_intercompany_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->integer('contact_id')->unsigned();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->integer('target_business_id')->unsigned();
            $table->foreign('target_business_id')->references('id')->on('business')->onDelete('cascade');
            $table->timestamps();

            $table->index(['business_id', 'contact_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'intercompany_linked_transaction_id')) {
                $table->integer('intercompany_linked_transaction_id')->unsigned()->nullable()->after('business_id')->index();
            }
            if (!Schema::hasColumn('transactions', 'is_intercompany')) {
                $table->boolean('is_intercompany')->default(0)->after('intercompany_linked_transaction_id');
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
        Schema::dropIfExists('business_intercompany_links');

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'intercompany_linked_transaction_id')) {
                $table->dropColumn('intercompany_linked_transaction_id');
            }
            if (Schema::hasColumn('transactions', 'is_intercompany')) {
                $table->dropColumn('is_intercompany');
            }
        });
    }
}
