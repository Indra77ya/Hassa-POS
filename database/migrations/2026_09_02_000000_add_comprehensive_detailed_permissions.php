<?php

use Illuminate\Database\Migrations\Migration;
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
            // User & Role
            'user.view', 'user.create', 'user.update', 'user.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',

            // Contacts
            'supplier.view', 'supplier.view_own', 'supplier.create', 'supplier.update', 'supplier.delete',
            'customer.view', 'customer.view_own', 'customer.create', 'customer.update', 'customer.delete',

            // Products, Brands, Categories, Tax, Units
            'product.view', 'product.create', 'product.update', 'product.delete', 'product.opening_stock', 'view_purchase_price',
            'brand.view', 'brand.create', 'brand.update', 'brand.delete',
            'category.view', 'category.create', 'category.update', 'category.delete',
            'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
            'unit.view', 'unit.create', 'unit.update', 'unit.delete',

            // Purchases
            'purchase.view', 'view_own_purchase', 'purchase.create', 'purchase.update', 'purchase.delete',
            'purchase.payments', 'edit_purchase_payment', 'delete_purchase_payment', 'purchase.update_status',

            // Sells & POS
            'sell.view', 'sell.create', 'sell.update', 'sell.delete', 'sell.print', 'sell.send_whatsapp',
            'direct_sell.view', 'view_own_sell_only', 'view_paid_sells_only', 'view_due_sells_only', 'view_partial_sells_only', 'view_overdue_sells_only',
            'direct_sell.access', 'direct_sell.update', 'direct_sell.delete', 'view_commission_agent_sell',
            'sell.payments', 'edit_sell_payment', 'delete_sell_payment', 'edit_product_price_from_sale_screen', 'edit_product_discount_from_sale_screen',
            'edit_product_price_from_pos_screen', 'edit_product_discount_from_pos_screen', 'edit_pos_payment', 'print_invoice',
            'disable_pay_checkout', 'disable_draft', 'disable_express_checkout', 'disable_discount', 'disable_suspend_sale', 'disable_credit_sale', 'disable_quotation', 'disable_card',
            'discount.access', 'access_types_of_service', 'access_sell_return', 'access_own_sell_return', 'edit_invoice_number',

            // Drafts, Quotations, Orders, Shipments, Register
            'draft.view_all', 'draft.view_own', 'draft.update', 'draft.delete',
            'quotation.view_all', 'quotation.view_own', 'quotation.update', 'quotation.delete',
            'so.view_all', 'so.view_own', 'so.create', 'so.update', 'so.delete',
            'purchase_requisition.view_all', 'purchase_requisition.view_own', 'purchase_requisition.create', 'purchase_requisition.delete',
            'purchase_order.view_all', 'purchase_order.view_own', 'purchase_order.create', 'purchase_order.update', 'purchase_order.delete',
            'access_shipping', 'access_own_shipping', 'access_pending_shipments_only', 'access_commission_agent_shipping',
            'view_cash_register', 'close_cash_register',

            // Stock
            'stock_adjustment.view', 'view_own_stock_adjustment', 'stock_adjustment.create', 'stock_adjustment.update', 'stock_adjustment.delete',
            'stock_transfer.view', 'stock_transfer.view_own', 'stock_transfer.create', 'stock_transfer.update', 'stock_transfer.delete',

            // Expenses & Accounts
            'all_expense.access', 'view_own_expense', 'expense.add', 'expense.edit', 'expense.delete',
            'account.access', 'edit_account_transaction', 'delete_account_transaction',

            // Reports
            'purchase_n_sell_report.view', 'tax_report.view', 'contacts_report.view', 'expense_report.view',
            'profit_loss_report.view', 'stock_report.view', 'trending_product_report.view', 'register_report.view',
            'sales_representative.view', 'view_product_stock_value', 'dashboard.data',

            // Settings
            'business_settings.access', 'barcode_settings.access', 'invoice_settings.access', 'access_printers', 'access_default_selling_price', 'view_export_buttons', 'send_payment_received_notification', 'send_payment_reminder_notification',

            // Laundry Module
            'laundry.view_dashboard', 'laundry.view', 'laundry.create', 'laundry.update', 'laundry.delete', 'laundry.update_status', 'laundry.log_process', 'laundry.manage_master_data', 'laundry.view_staff_points', 'laundry.add_payment', 'laundry.print', 'laundry.send_whatsapp',

            // Repair Module
            'repair.view', 'repair.create', 'repair.update', 'repair.delete', 'repair_status.update', 'repair_status.access', 'repair.add_payment', 'repair.print', 'repair.send_whatsapp',

            // Manufacturing Module
            'manufacturing.access_recipe', 'manufacturing.access_production', 'manufacturing.add_recipe', 'manufacturing.edit_recipe', 'manufacturing.delete_recipe', 'manufacturing.add_production', 'manufacturing.edit_production', 'manufacturing.delete_production',

            // Asset Management Module
            'asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.revoke', 'asset.maintenance',

            // Essentials / HRM Module
            'essentials.view_work_duration', 'essentials.crud_work_duration', 'essentials.view_allowance_and_deduction', 'essentials.add_allowance_and_deduction', 'essentials.crud_department', 'essentials.crud_designation', 'essentials.view_all_attendance', 'essentials.view_own_attendance', 'essentials.crud_all_attendance', 'essentials.crud_own_attendance', 'essentials.approve_leave', 'essentials.create_message', 'essentials.view_message', 'essentials.assign_todos', 'essentials.crud_payroll',

            // CRM Module
            'crm.view_all_leads', 'crm.view_own_leads', 'crm.access_all_schedule', 'crm.access_own_schedule', 'crm.access_all_campaigns', 'crm.access_own_campaigns', 'crm.access_contact_login', 'crm.access_sources', 'crm.access_life_stage',

            // Project Module
            'project.create_project', 'project.edit_project', 'project.delete_project', 'project.view_project'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm],
                ['guard_name' => 'web']
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
        // No destruct needed
    }
};
