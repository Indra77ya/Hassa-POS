<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([BarcodesTableSeeder::class,
            PermissionsTableSeeder::class,
            CurrenciesTableSeeder::class,
            AccountTypesSeeder::class,
           //RolesTableSeeder::class,
           //UsersTableSeeder::class,
           //SuppliersTableSeeder::class,
           //CustomersTableSeeder::class,
           //UnitsTableSeeder::class,
           //CategoriesTableSeeder::class,
           //BrandsTableSeeder::class,
           //WarrantiesTableSeeder::class,
           //ProductsTableSeeder::class,
           //DiscountsTableSeeder::class,
           //ExpenseCategoriesTableSeeder::class,
           //TaxRatesTableSeeder::class,
        ]);
    }
}
