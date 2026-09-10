@extends('layouts.app')
@section('title', __('role.add_role'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>@lang( 'role.add_role' )</h1>
</section>

<!-- Main content -->
<section class="content">
    @php
      $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
      $role_permissions = [];

      $permission_groups = [
          'user_role' => [
              'title' => __('role.user'),
              'icon' => 'fas fa-users-cog',
              'permissions' => [
                  'user.view', 'user.create', 'user.update', 'user.delete', 'user.view_own',
                  'roles.view', 'roles.create', 'roles.update', 'roles.delete'
              ]
          ],
          'contacts' => [
              'title' => __('contact.contact'),
              'icon' => 'fas fa-address-book',
              'permissions' => [
                  'supplier.view', 'supplier.view_own', 'supplier.create', 'supplier.update', 'supplier.delete',
                  'customer.view', 'customer.view_own', 'customer.create', 'customer.update', 'customer.delete',
                  'customer.view_own_customers_only', 'access_all_customers',
                  'sales_commission_agent.view', 'sales_commission_agent.create', 'sales_commission_agent.update', 'sales_commission_agent.delete'
              ]
          ],
          'product_catalog' => [
              'title' => __('role.product_catalog'),
              'icon' => 'fas fa-boxes',
              'permissions' => [
                  'product.view', 'product.create', 'product.update', 'product.delete', 'product.opening_stock',
                  'view_purchase_price', 'product.import', 'product.export', 'product.update_price',
                  'brand.view', 'brand.create', 'brand.update', 'brand.delete',
                  'category.view', 'category.create', 'category.update', 'category.delete',
                  'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                  'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
                  'warranty.view', 'warranty.create', 'warranty.update', 'warranty.delete',
                  'discount.access', 'discount.create', 'discount.edit', 'discount.delete'
              ]
          ],
          'purchase' => [
              'title' => __('role.purchase'),
              'icon' => 'fas fa-shopping-cart',
              'permissions' => [
                  'purchase.view', 'purchase.view_own', 'purchase.create', 'purchase.update', 'purchase.delete',
                  'purchase.update_status', 'purchase.payments', 'edit_purchase_payment', 'delete_purchase_payment',
                  'purchase_reorder.view'
              ]
          ],
          'sell_pos' => [
              'title' => __('role.sell'),
              'icon' => 'fas fa-cash-register',
              'permissions' => [
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
                  'print_invoice'
              ]
          ],
          'stock_transfer_adjustment' => [
              'title' => __('role.stock_transfer') . ' & ' . __('role.stock_adjustment'),
              'icon' => 'fas fa-exchange-alt',
              'permissions' => [
                  'stock_transfer.view', 'stock_transfer.create', 'stock_transfer.update', 'stock_transfer.delete',
                  'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.update', 'stock_adjustment.delete'
              ]
          ],
          'expenses' => [
              'title' => __('role.expense'),
              'icon' => 'fas fa-money-bill-wave',
              'permissions' => [
                  'expense.access', 'all_expense.access', 'view_own_expense', 'expense.add', 'expense.edit', 'expense.delete',
                  'expense_category.view', 'expense_category.create', 'expense_category.update', 'expense_category.delete'
              ]
          ],
          'accounting' => [
              'title' => __('role.accounting'),
              'icon' => 'fas fa-calculator',
              'permissions' => [
                  'account.access', 'edit_account_transaction', 'delete_account_transaction',
                  'accounting.manage_accounts', 'accounting.manage_journal', 'accounting.view_reports'
              ]
          ],
          'reports' => [
              'title' => __('role.report'),
              'icon' => 'fas fa-chart-line',
              'permissions' => [
                  'purchase_n_sell_report.view', 'tax_report.view', 'contacts_report.view',
                  'expense_report.view', 'profit_loss_report.view', 'stock_report.view',
                  'trending_product_report.view', 'register_report.view', 'sales_representative.view',
                  'view_product_stock_value'
              ]
          ],
          'settings' => [
              'title' => __('role.settings'),
              'icon' => 'fas fa-cogs',
              'permissions' => [
                  'business_settings.access', 'barcode_settings.access', 'invoice_settings.access',
                  'access_printers', 'access_types_of_service',
                  'view_export_buttons', 'send_payment_received_notification', 'send_payment_reminder_notification',
                  'dashboard.data', 'crud_all_bookings', 'crud_own_bookings',
                  'access_default_selling_price', 'access_tables'
              ]
          ]
      ];
    @endphp

    @component('components.widget', ['class' => 'box-primary'])
        {!! Form::open(['url' => action([\App\Http\Controllers\RoleController::class, 'store']), 'method' => 'post', 'id' => 'role_add_form' ]) !!}

        <div class="row" style="margin-bottom: 20px;">
          <div class="col-md-6">
            <div class="form-group">
              {!! Form::label('name', __( 'user.role_name' ) . ':*') !!}
              {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'user.role_name' ) ]); !!}
            </div>
          </div>
          @if(in_array('service_staff', $enabled_modules))
            <div class="col-md-6" style="margin-top: 25px;">
              <div class="checkbox">
                <label style="font-weight: 600;">
                  {!! Form::checkbox('is_service_staff', 1, false, ['class' => 'input-icheck']); !!} {{ __( 'restaurant.service_staff' ) }}
                </label>
                @show_tooltip(__('restaurant.tooltip_service_staff'))
              </div>
            </div>
          @endif
        </div>

        <hr style="border-top: 1px solid #e2e8f0; margin-bottom: 25px;">

        <h3 style="margin-top: 0; margin-bottom: 20px; font-weight: 700; color: #0f172a;">
          <i class="fas fa-user-shield" style="margin-right: 10px; color: #2563eb;"></i>
          @lang( 'user.permissions' )
        </h3>

        @include('role.partials.permission_group', ['groups' => $permission_groups, 'role_permissions' => $role_permissions])

        @if(!empty($selling_price_groups) && count($selling_price_groups) > 0)
          <div class="panel panel-default" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <div class="panel-heading" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 18px;">
              <h4 style="margin: 0; font-weight: 600; color: #1e293b;">
                <i class="fas fa-tags" style="margin-right: 8px; color: #3b82f6;"></i>
                @lang('lang_v1.selling_price_group_permission')
              </h4>
            </div>
            <div class="panel-body" style="padding: 18px 20px;">
              <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                  <div class="checkbox">
                    <label style="font-weight: 500;">
                      {!! Form::checkbox('permissions[]', 'access_default_selling_price', false, ['class' => 'input-icheck']); !!}
                      {{ __('lang_v1.default_selling_price') }}
                    </label>
                  </div>
                </div>
                @foreach($selling_price_groups as $selling_price_group)
                  <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="checkbox">
                      <label style="font-weight: 500;">
                        {!! Form::checkbox('spg_permissions[]', 'selling_price_group.' . $selling_price_group->id, false, ['class' => 'input-icheck']); !!}
                        {{ $selling_price_group->name }}
                      </label>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        @include('role.partials.module_permissions')

        <div class="row" style="margin-top: 30px;">
          <div class="col-md-12 text-center">
             <button type="submit" class="btn btn-primary btn-lg" style="padding: 10px 40px; font-weight: 600;">@lang( 'messages.save' )</button>
          </div>
        </div>

        {!! Form::close() !!}
    @endcomponent

</section>
<!-- /.content -->
@endsection
