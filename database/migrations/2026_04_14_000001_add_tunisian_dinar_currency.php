<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // On a fresh install (migrate:fresh + db:seed), the table is empty at
        // migration time and the seeder already includes TND. Inserting here
        // would claim id=1, which then conflicts with the seeder's explicit ids.
        // Skip the insert when the table is empty — the seeder will handle it.
        if (DB::table('currencies')->count() === 0) {
            return;
        }

        $exists = DB::table('currencies')->where('code', 'TND')->exists();

        if (! $exists) {
            DB::table('currencies')->insert([
                'country'            => 'Tunisia',
                'currency'           => 'Tunisian Dinar',
                'code'               => 'TND',
                'symbol'             => 'د.ت',
                'thousand_separator' => ',',
                'decimal_separator'  => '.',
                'created_at'         => null,
                'updated_at'         => null,
            ]);
        }
    }

    public function down()
    {
        DB::table('currencies')->where('code', 'TND')->delete();
    }
};
