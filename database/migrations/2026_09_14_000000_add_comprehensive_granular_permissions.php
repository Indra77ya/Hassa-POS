<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            // User & Role Management
            'user.view', 'user.create', 'user.update', 'user.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',

            // Contacts (Supplier & Customer)
            'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'customer.add_payment', 'customer.view_ledger',

            // Products & Inventory
            'product.view', 'product.create', 'product.update', 'product.delete',
            'product.export', 'product.import',
            'brand.view', 'brand.create', 'brand.update', 'brand.delete',
            'unit.view', 'unit.create', 'unit.update', 'unit.delete',
            'category.view', 'category.create', 'category.update', 'category.delete',
            'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
            'opening_stock.add', 'view_purchase_price',

            // Purchases
            'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete',
            'purchase.update_status', 'purchase.payments', 'purchase.add_payment',
            'purchase.print', 'purchase.export',

            // Sales & POS
            'sell.view', 'sell.create', 'sell.update', 'sell.delete',
            'sell.payments', 'sell.add_payment', 'sell.print', 'sell.export',
            'view_own_sell_only', 'list_drafts', 'list_quotations',
            'access_default_selling_price', 'discount.access',
            'edit_product_discount_from_pos_screen', 'edit_product_price_from_pos_screen',
            'edit_product_discount_from_sale_screen', 'edit_product_price_from_sale_screen',
            'access_shipping', 'print_invoice',
            'view_cash_register', 'close_cash_register',

            // Expenses
            'expense.access', 'expense.view', 'expense.create', 'expense.update', 'expense.delete',
            'expense.add_payment',

            // Accounting & Payment Accounts
            'account.access', 'accounting.access_accounting_module',
            'accounting.manage_accounts', 'accounting.view_journal',
            'accounting.add_journal', 'accounting.edit_journal',
            'accounting.delete_journal', 'accounting.map_transactions',
            'accounting.view_transfer', 'accounting.add_transfer',
            'accounting.edit_transfer', 'accounting.delete_transfer',
            'accounting.manage_budget', 'accounting.view_reports',

            // Reports
            'purchase_n_sell_report.view', 'contacts_report.view',
            'stock_report.view', 'tax_report.view',
            'trending_product_report.view', 'register_report.view',
            'sales_representative.view', 'expense_report.view',
            'profit_loss_report.view',

            // System Settings & General
            'business_settings.access', 'barcode_settings.access',
            'invoice_settings.access', 'access_all_locations', 'dashboard.data',

            // Laundry Module
            'laundry.view_dashboard', 'laundry.view', 'laundry.create',
            'laundry.update', 'laundry.delete', 'laundry.update_status',
            'laundry.log_process', 'laundry.manage_master_data',
            'laundry.view_staff_points',

            // Manufacturing Module
            'manufacturing.access_recipe', 'manufacturing.add_recipe',
            'manufacturing.edit_recipe', 'manufacturing.delete_recipe',
            'manufacturing.access_production', 'manufacturing.add_production',
            'manufacturing.edit_production', 'manufacturing.delete_production',

            // Repair Module
            'repair.view', 'repair.create', 'repair.update', 'repair.delete',
            'repair_status.access', 'repair_status.update',

            // Asset Management
            'asset.view', 'asset.create', 'asset.update', 'asset.delete',

            // Essentials / HRM
            'essentials.view_work_experience', 'essentials.create_message',
            'essentials.view_message', 'essentials.add_allowance_and_deduction',
            'essentials.approve_leave', 'essentials.assign_todos',

            // CRM
            'crm.view', 'crm.create', 'crm.update', 'crm.delete',

            // Project
            'project.create_project', 'project.edit_project', 'project.delete_project',

            // Woocommerce
            'woocommerce.syc_categories', 'woocommerce.sync_products',
            'woocommerce.sync_orders', 'woocommerce.map_tax_rates',
            'woocommerce.access_woocommerce_api_settings',
        ];

        foreach ($permissions as $perm_name) {
            Permission::firstOrCreate(
                ['name' => $perm_name],
                ['guard_name' => 'web']
            );
        }

        // Auto-assign all permissions to Admin roles across businesses
        $admin_roles = Role::where('name', 'like', 'Admin#%')
            ->orWhere('name', 'Admin')
            ->get();

        $all_perm_names = Permission::pluck('name')->toArray();

        foreach ($admin_roles as $admin_role) {
            $admin_role->syncPermissions($all_perm_names);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Keep permissions intact
    }
};
