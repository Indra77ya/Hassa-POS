<?php

namespace Database\Seeders;

use App\Business;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $businesses = Business::all();

        $roles_data = [
            [
                'name' => 'Manager',
                'permissions' => [
                    'user.view', 'user.create', 'user.update', 'user.delete',
                    'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
                    'customer.view', 'customer.create', 'customer.update', 'customer.delete',
                    'product.view', 'product.create', 'product.update', 'product.delete',
                    'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete',
                    'sell.view', 'sell.create', 'sell.update', 'sell.delete',
                    'purchase_n_sell_report.view', 'contacts_report.view', 'stock_report.view', 'tax_report.view', 'trending_product_report.view', 'register_report.view', 'sales_representative.view', 'expense_report.view',
                    'brand.view', 'brand.create', 'brand.update', 'brand.delete',
                    'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
                    'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                    'category.view', 'category.create', 'category.update', 'category.delete',
                    'expense.access', 'access_all_locations', 'dashboard.data', 'print_invoice',
                    'view_cash_register', 'close_cash_register', 'account.access', 'crud_all_bookings'
                ]
            ],
            [
                'name' => 'Supervisor',
                'permissions' => [
                    'user.view',
                    'supplier.view', 'supplier.create', 'supplier.update',
                    'customer.view', 'customer.create', 'customer.update',
                    'product.view', 'purchase.view',
                    'sell.view', 'sell.create', 'sell.update',
                    'contacts_report.view', 'stock_report.view', 'sales_representative.view',
                    'dashboard.data', 'print_invoice', 'view_cash_register', 'crud_all_bookings'
                ]
            ],
            [
                'name' => 'Stock Manager',
                'permissions' => [
                    'supplier.view', 'supplier.create', 'supplier.update',
                    'product.view', 'product.create', 'product.update', 'product.delete',
                    'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete',
                    'stock_report.view', 'trending_product_report.view',
                    'brand.view', 'brand.create', 'brand.update', 'brand.delete',
                    'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                    'category.view', 'category.create', 'category.update', 'category.delete',
                    'dashboard.data'
                ]
            ],
            [
                'name' => 'Sales Representative',
                'permissions' => [
                    'customer.view', 'customer.create', 'customer.update',
                    'product.view', 'sell.view', 'sell.create', 'sell.update',
                    'sales_representative.view', 'dashboard.data', 'print_invoice',
                    'view_cash_register', 'close_cash_register'
                ]
            ],
            [
                'name' => 'Accountant',
                'permissions' => [
                    'purchase_n_sell_report.view', 'contacts_report.view', 'tax_report.view', 'register_report.view', 'expense_report.view',
                    'expense.access', 'account.access', 'dashboard.data'
                ]
            ],
            [
                'name' => 'Receptionist',
                'permissions' => [
                    'customer.view', 'customer.create', 'customer.update',
                    'crud_all_bookings', 'dashboard.data'
                ]
            ],
        ];

        foreach ($businesses as $business) {
            foreach ($roles_data as $rd) {
                $role_name = $rd['name'] . '#' . $business->id;

                $role = Role::firstOrCreate([
                    'name' => $role_name,
                    'business_id' => $business->id,
                    'guard_name' => 'web'
                ]);

                $role->syncPermissions($rd['permissions']);
            }
        }
    }
}
