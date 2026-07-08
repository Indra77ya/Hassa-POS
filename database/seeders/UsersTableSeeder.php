<?php

namespace Database\Seeders;

use App\Business;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $business = Business::first();
        if (!$business) {
            return;
        }

        $password = Hash::make('123456');

        $users_data = [
            // Manager
            [
                'surname' => 'Mr', 'first_name' => 'Budi', 'last_name' => 'Santoso',
                'username' => 'budi_manager', 'email' => 'budi@example.com', 'role' => 'Manager'
            ],
            [
                'surname' => 'Ms', 'first_name' => 'Siti', 'last_name' => 'Aminah',
                'username' => 'siti_manager', 'email' => 'siti@example.com', 'role' => 'Manager'
            ],
            // Supervisor
            [
                'surname' => 'Mr', 'first_name' => 'Agus', 'last_name' => 'Setiawan',
                'username' => 'agus_super', 'email' => 'agus@example.com', 'role' => 'Supervisor'
            ],
            [
                'surname' => 'Mrs', 'first_name' => 'Dewi', 'last_name' => 'Lestari',
                'username' => 'dewi_super', 'email' => 'dewi@example.com', 'role' => 'Supervisor'
            ],
            // Stock Manager
            [
                'surname' => 'Mr', 'first_name' => 'Eko', 'last_name' => 'Prasetyo',
                'username' => 'eko_stock', 'email' => 'eko@example.com', 'role' => 'Stock Manager'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Rian', 'last_name' => 'Hidayat',
                'username' => 'rian_stock', 'email' => 'rian@example.com', 'role' => 'Stock Manager'
            ],
            // Sales Representative
            [
                'surname' => 'Ms', 'first_name' => 'Maya', 'last_name' => 'Putri',
                'username' => 'maya_sales', 'email' => 'maya@example.com', 'role' => 'Sales Representative'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Dedi', 'last_name' => 'Kurniawan',
                'username' => 'dedi_sales', 'email' => 'dedi@example.com', 'role' => 'Sales Representative'
            ],
            // Accountant
            [
                'surname' => 'Mrs', 'first_name' => 'Indah', 'last_name' => 'Permata',
                'username' => 'indah_acc', 'email' => 'indah@example.com', 'role' => 'Accountant'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Fajar', 'last_name' => 'Nugraha',
                'username' => 'fajar_acc', 'email' => 'fajar@example.com', 'role' => 'Accountant'
            ],
            // Receptionist
            [
                'surname' => 'Ms', 'first_name' => 'Rina', 'last_name' => 'Sari',
                'username' => 'rina_recep', 'email' => 'rina@example.com', 'role' => 'Receptionist'
            ],
            [
                'surname' => 'Mr', 'first_name' => 'Andi', 'last_name' => 'Wijaya',
                'username' => 'andi_recep', 'email' => 'andi@example.com', 'role' => 'Receptionist'
            ],
        ];

        foreach ($users_data as $ud) {
            $user = User::firstOrCreate(
                ['username' => $ud['username']],
                [
                    'surname' => $ud['surname'],
                    'first_name' => $ud['first_name'],
                    'last_name' => $ud['last_name'],
                    'email' => $ud['email'],
                    'password' => $password,
                    'business_id' => $business->id,
                    'language' => 'en',
                    'user_type' => 'user'
                ]
            );

            $role_name = $ud['role'] . '#' . $business->id;
            $user->assignRole($role_name);
        }
    }
}
