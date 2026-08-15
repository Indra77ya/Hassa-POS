<?php

namespace App\Console\Commands;

use Database\Seeders\CreateRolesAndUsersSeeder;
use Illuminate\Console\Command;

class CreateRolesAndUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:create-roles-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default roles (Sales, Akunting, Gudang, Manufaktur, Teknisi) and users with password 12345';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating default roles and users...');

        $seeder = new CreateRolesAndUsersSeeder();
        $seeder->run();

        $this->info('Roles and users created successfully!');

        return 0;
    }
}
