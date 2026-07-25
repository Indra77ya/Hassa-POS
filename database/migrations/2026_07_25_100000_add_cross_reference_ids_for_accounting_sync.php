<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrossReferenceIdsForAccountingSync extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('accounts') && !Schema::hasColumn('accounts', 'accounting_account_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('accounting_account_id')->nullable()->after('business_id');
            });
        }

        if (Schema::hasTable('accounting_accounts') && !Schema::hasColumn('accounting_accounts', 'account_id')) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('account_id')->nullable()->after('business_id');
            });
        }

        if (Schema::hasTable('account_transactions') && !Schema::hasColumn('account_transactions', 'accounting_accounts_transaction_id')) {
            Schema::table('account_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('accounting_accounts_transaction_id')->nullable()->after('account_id');
            });
        }

        if (Schema::hasTable('accounting_accounts_transactions') && !Schema::hasColumn('accounting_accounts_transactions', 'account_transaction_id')) {
            Schema::table('accounting_accounts_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('account_transaction_id')->nullable()->after('accounting_account_id');
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
        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'accounting_account_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn('accounting_account_id');
            });
        }

        if (Schema::hasTable('accounting_accounts') && Schema::hasColumn('accounting_accounts', 'account_id')) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->dropColumn('account_id');
            });
        }

        if (Schema::hasTable('account_transactions') && Schema::hasColumn('account_transactions', 'accounting_accounts_transaction_id')) {
            Schema::table('account_transactions', function (Blueprint $table) {
                $table->dropColumn('accounting_accounts_transaction_id');
            });
        }

        if (Schema::hasTable('accounting_accounts_transactions') && Schema::hasColumn('accounting_accounts_transactions', 'account_transaction_id')) {
            Schema::table('accounting_accounts_transactions', function (Blueprint $table) {
                $table->dropColumn('account_transaction_id');
            });
        }
    }
}
