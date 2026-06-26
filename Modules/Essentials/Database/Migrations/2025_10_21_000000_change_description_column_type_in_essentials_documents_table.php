<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
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
        // Using raw SQL to change column type without requiring doctrine/dbal
        DB::statement('ALTER TABLE essentials_documents MODIFY description TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to VARCHAR(255)
        DB::statement('ALTER TABLE essentials_documents MODIFY description VARCHAR(255) NULL');
    }
};

