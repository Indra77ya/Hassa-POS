<?php

namespace Database\Seeders;

use App\Business;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $businesses = Business::all();

        if ($businesses->isEmpty()) {
            return;
        }

        $roles_permissions = [
            'Sales' => [
                'sell.view',
                'sell.create',
                'sell.update',
                'sell.delete',
                'view_own_sell_only',
                'list_drafts',
                'list_quotations',
                'customer.view',
                'customer.create',
                'customer.update',
                'view_cash_register',
                'close_cash_register',
                'print_invoice',
                'access_all_locations',
                'dashboard.data',
                'discount.access',
                'edit_product_discount_from_pos_screen',
                'edit_product_price_from_pos_screen',
                'edit_product_discount_from_sale_screen',
                'edit_product_price_from_sale_screen',
            ],
            'Akunting' => [
                'account.access',
                'accounting.access_accounting_module',
                'accounting.manage_accounts',
                'accounting.view_journal',
                'accounting.add_journal',
                'accounting.edit_journal',
                'accounting.delete_journal',
                'accounting.map_transactions',
                'accounting.view_transfer',
                'accounting.add_transfer',
                'accounting.edit_transfer',
                'accounting.delete_transfer',
                'accounting.manage_budget',
                'accounting.view_reports',
                'expense.access',
                'expense_report.view',
                'purchase_n_sell_report.view',
                'tax_report.view',
                'contacts_report.view',
                'access_all_locations',
                'dashboard.data',
            ],
            'Gudang' => [
                'product.view',
                'product.create',
                'product.update',
                'product.delete',
                'category.view',
                'category.create',
                'category.update',
                'category.delete',
                'brand.view',
                'brand.create',
                'brand.update',
                'brand.delete',
                'unit.view',
                'unit.create',
                'unit.update',
                'unit.delete',
                'purchase.view',
                'purchase.create',
                'purchase.update',
                'purchase.delete',
                'purchase.update_status',
                'purchase.payments',
                'stock_report.view',
                'access_all_locations',
                'dashboard.data',
            ],
            'Manufaktur' => [
                'manufacturing.access_recipe',
                'manufacturing.add_recipe',
                'manufacturing.edit_recipe',
                'manufacturing.access_production',
                'product.view',
                'stock_report.view',
                'access_all_locations',
                'dashboard.data',
            ],
            'Teknisi' => [
                'repair.view',
                'repair.create',
                'repair.update',
                'repair.delete',
                'repair_status.access',
                'repair_status.update',
                'product.view',
                'access_all_locations',
                'dashboard.data',
            ],
        ];

        // Ensure all required permissions exist
        $all_permissions = [];
        foreach ($roles_permissions as $perms) {
            foreach ($perms as $perm) {
                $all_permissions[$perm] = true;
            }
        }

        foreach (array_keys($all_permissions) as $perm_name) {
            Permission::firstOrCreate(
                ['name' => $perm_name],
                ['guard_name' => 'web']
            );
        }

        $user_definitions = [
            'Sales' => [
                'username' => 'sales_user',
                'email' => 'sales@example.com',
                'first_name' => 'Sales',
                'last_name' => 'Staff',
            ],
            'Akunting' => [
                'username' => 'akunting_user',
                'email' => 'akunting@example.com',
                'first_name' => 'Akunting',
                'last_name' => 'Staff',
            ],
            'Gudang' => [
                'username' => 'gudang_user',
                'email' => 'gudang@example.com',
                'first_name' => 'Gudang',
                'last_name' => 'Staff',
            ],
            'Manufaktur' => [
                'username' => 'manufaktur_user',
                'email' => 'manufaktur@example.com',
                'first_name' => 'Manufaktur',
                'last_name' => 'Staff',
            ],
            'Teknisi' => [
                'username' => 'teknisi_user',
                'email' => 'teknisi@example.com',
                'first_name' => 'Teknisi',
                'last_name' => 'Repair',
            ],
        ];

        foreach ($businesses as $business) {
            $business_id = $business->id;

            foreach ($roles_permissions as $role_key => $permissions) {
                $role_name = $role_key . '#' . $business_id;

                $role = Role::firstOrCreate(
                    [
                        'name' => $role_name,
                        'business_id' => $business_id,
                    ],
                    [
                        'guard_name' => 'web',
                        'is_default' => 0,
                    ]
                );

                $role->syncPermissions($permissions);

                $user_info = $user_definitions[$role_key];
                $username = ($businesses->count() > 1 && $business_id > 1)
                    ? $user_info['username'] . '_' . $business_id
                    : $user_info['username'];
                $email = ($businesses->count() > 1 && $business_id > 1)
                    ? str_replace('@', '_' . $business_id . '@', $user_info['email'])
                    : $user_info['email'];

                $user = User::firstOrCreate(
                    [
                        'username' => $username,
                        'business_id' => $business_id,
                    ],
                    [
                        'surname' => 'Sdr',
                        'first_name' => $user_info['first_name'],
                        'last_name' => $user_info['last_name'],
                        'email' => $email,
                        'password' => Hash::make('12345'),
                        'language' => 'id',
                        'is_cmmsn_agnt' => 0,
                        'cmmsn_percent' => '0.00',
                    ]
                );

                // Update password if existing user to guarantee '12345'
                $user->password = Hash::make('12345');
                $user->save();

                $user->syncRoles([$role->name]);
            }
        }
    }
}
