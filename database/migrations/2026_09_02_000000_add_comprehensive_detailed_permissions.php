<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

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
            // User & Roles
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.view_own',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',

            // Contacts
            'supplier.view', 'supplier.view_own', 'supplier.create', 'supplier.update', 'supplier.delete',
            'customer.view', 'customer.view_own', 'customer.create', 'customer.update', 'customer.delete',
            'customer.view_own_customers_only', 'access_all_customers',
            'sales_commission_agent.view', 'sales_commission_agent.create', 'sales_commission_agent.update', 'sales_commission_agent.delete',

            // Products & Catalog
            'product.view', 'product.create', 'product.update', 'product.delete', 'product.opening_stock',
            'view_purchase_price', 'product.import', 'product.export', 'product.update_price',
            'brand.view', 'brand.create', 'brand.update', 'brand.delete',
            'category.view', 'category.create', 'category.update', 'category.delete',
            'unit.view', 'unit.create', 'unit.update', 'unit.delete',
            'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
            'warranty.view', 'warranty.create', 'warranty.update', 'warranty.delete',
            'discount.access', 'discount.create', 'discount.edit', 'discount.delete',

            // Purchases
            'purchase.view', 'purchase.view_own', 'purchase.create', 'purchase.update', 'purchase.delete',
            'purchase.update_status', 'purchase.payments', 'edit_purchase_payment', 'delete_purchase_payment',
            'purchase_reorder.view',

            // Sales & POS
            'sell.view', 'sell.view_own', 'sell.create', 'sell.update', 'sell.delete',
            'direct_sell.access', 'direct_sell.view', 'direct_sell.create', 'direct_sell.update', 'direct_sell.delete',
            'pos.view', 'pos.create', 'pos.update', 'pos.delete',
            'view_own_sell_only', 'view_paid_sells_only', 'view_due_sells_only',
            'sell.payments', 'edit_sell_payment', 'delete_sell_payment',
            'edit_product_discount_from_pos_screen', 'edit_product_price_from_pos_screen',
            'edit_product_discount_from_sale_screen', 'edit_product_price_from_sale_screen',
            'view_cash_register', 'close_cash_register',
            'access_all_locations', 'access_own_shipping', 'access_pending_shipments_only',
            'access_commission_agent_shipping', 'access_shipping',
            'list_drafts', 'draft.view_own', 'draft.update', 'draft.delete',
            'list_quotations', 'quotation.view_own', 'quotation.update', 'quotation.delete',
            'print_invoice',

            // Stock Transfers & Adjustments
            'stock_transfer.view', 'stock_transfer.create', 'stock_transfer.update', 'stock_transfer.delete',
            'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.update', 'stock_adjustment.delete',

            // Expenses
            'expense.access', 'all_expense.access', 'view_own_expense', 'expense.add', 'expense.edit', 'expense.delete',
            'expense_category.view', 'expense_category.create', 'expense_category.update', 'expense_category.delete',

            // Accounting
            'account.access', 'edit_account_transaction', 'delete_account_transaction',
            'accounting.manage_accounts', 'accounting.manage_journal', 'accounting.view_reports',

            // Reports
            'purchase_n_sell_report.view', 'tax_report.view', 'contacts_report.view',
            'expense_report.view', 'profit_loss_report.view', 'stock_report.view',
            'trending_product_report.view', 'register_report.view', 'sales_representative.view',
            'view_product_stock_value',

            // Settings
            'business_settings.access', 'barcode_settings.access', 'invoice_settings.access',
            'access_printers', 'access_types_of_service',

            // Common / Other
            'view_export_buttons', 'send_payment_received_notification', 'send_payment_reminder_notification',
            'dashboard.data', 'crud_all_bookings', 'crud_own_bookings',
            'access_default_selling_price', 'access_tables',

            // Modules
            'manufacturing.access_recipe', 'manufacturing.add_recipe', 'manufacturing.edit_recipe', 'manufacturing.access_production',
            'repair.create', 'repair.update', 'repair.view', 'repair.delete', 'repair_status.update', 'repair_status.access',
            'laundry.view_dashboard', 'laundry.view', 'laundry.create', 'laundry.update', 'laundry.delete',
            'laundry.update_status', 'laundry.log_process', 'laundry.manage_master_data', 'laundry.view_staff_points',
            'asset.view', 'asset.create', 'asset.edit', 'asset.delete',
            'crm.view_leads', 'crm.access_sources', 'crm.access_life_stage',
            'project.create_project', 'project.edit_project', 'project.delete_project',
            'essentials.create_message', 'essentials.view_message', 'essentials.add_allowance_and_deduction',
            'essentials.approve_leave', 'essentials.assign_todos',
        ];

        $time_stamp = Carbon::now()->toDateTimeString();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web', 'created_at' => $time_stamp]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No down action required
    }
};
