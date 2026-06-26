<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Flip all existing account transaction types to match the new logic
        // Old: sell=credit, purchase=debit, expense=debit
        // New: sell=debit, purchase=credit, expense=credit

        DB::table('account_transactions')->where('type', 'debit')->update(['type' => 'temp']);
        DB::table('account_transactions')->where('type', 'credit')->update(['type' => 'debit']);
        DB::table('account_transactions')->where('type', 'temp')->update(['type' => 'credit']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('account_transactions')->where('type', 'debit')->update(['type' => 'temp']);
        DB::table('account_transactions')->where('type', 'credit')->update(['type' => 'debit']);
        DB::table('account_transactions')->where('type', 'temp')->update(['type' => 'credit']);
    }
};
