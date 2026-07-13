<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL to avoid doctrine/dbal dependency for column modification
        DB::statement("ALTER TABLE variations MODIFY COLUMN profit_percent DECIMAL(22, 4) DEFAULT 0.0000");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting to float(5,2) which was likely the original size for percentage
        DB::statement("ALTER TABLE variations MODIFY COLUMN profit_percent FLOAT(5, 2) DEFAULT 0.00");
    }
};
