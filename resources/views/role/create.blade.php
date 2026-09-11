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
    @endphp
    @component('components.widget', ['class' => 'box-primary'])
        {!! Form::open(['url' => action([\App\Http\Controllers\RoleController::class, 'store']), 'method' => 'post', 'id' => 'role_add_form' ]) !!}
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('name', __( 'user.role_name' ) . ':*') !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'user.role_name' ) ]); !!}
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('preset_role', __( 'role.preset_role' ) . ':') !!}
              <select name="preset_role" class="form-control select2" id="preset_role_select">
                <option value="">@lang('messages.please_select')</option>
                <optgroup label="@lang('role.system_presets')">
                  <option value="cashier">@lang('role.preset_cashier')</option>
                  <option value="accountant">@lang('role.preset_accountant')</option>
                  <option value="warehouse">@lang('role.preset_warehouse')</option>
                  <option value="sales_supervisor">@lang('role.preset_sales_supervisor')</option>
                  <option value="store_manager">@lang('role.preset_store_manager')</option>
                  <option value="cs">@lang('role.preset_cs')</option>
                  <option value="laundry_admin">@lang('role.preset_laundry_admin')</option>
                  <option value="repair_tech">@lang('role.preset_repair_tech')</option>
                  <option value="mfg_supervisor">@lang('role.preset_mfg_supervisor')</option>
                  <option value="procurement">@lang('role.preset_procurement')</option>
                  <option value="hrm_admin">@lang('role.preset_hrm_admin')</option>
                  <option value="asset_manager">@lang('role.preset_asset_manager')</option>
                </optgroup>
                @if(!empty($custom_templates) && count($custom_templates) > 0)
                  <optgroup label="@lang('role.custom_templates')">
                    @foreach($custom_templates as $tmpl)
                      <option value="custom_{{ $tmpl->id }}">⭐ {{ $tmpl->name }}</option>
                    @endforeach
                  </optgroup>
                @endif
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('description', __( 'role.description' ) . ':') !!}
              {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 1, 'placeholder' => __( 'role.description_placeholder' ) ]); !!}
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 tw-mb-4">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('save_as_template', 1, false, ['class' => 'input-icheck', 'id' => 'save_as_template_chk']) !!}
                <strong>@lang('role.save_as_custom_template')</strong>
              </label>
            </div>
            <div id="template_name_box" class="tw-mt-2" style="display: none;">
              <div class="form-group col-md-4 tw-pl-0">
                {!! Form::label('template_name', __('role.template_name') . ':') !!}
                {!! Form::text('template_name', null, ['class' => 'form-control', 'placeholder' => __('role.template_name_placeholder')]) !!}
              </div>
            </div>
          </div>
        </div>
        <div class="row tw-mb-3">
          <div class="col-md-12">
            <h3 class="tw-font-bold text-primary"><i class="fa fa-key"></i> @lang( 'user.permissions' )</h3>
          </div>
        </div>

        {{-- User & Role Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.user') . ' & ' . __('user.roles'),
          'icon' => 'fa-users',
          'items' => ['user.view', 'user.create', 'user.update', 'user.delete', 'roles.view', 'roles.create', 'roles.update', 'roles.delete']
        ])

        {{-- Supplier & Customer Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.supplier') . ' & ' . __('role.customer'),
          'icon' => 'fa-address-book',
          'items' => [
            ['value' => 'supplier.view', 'label' => __('lang_v1.view_all_supplier'), 'is_radio' => true, 'radio_group' => 'supplier_view'],
            ['value' => 'supplier.view_own', 'label' => __('lang_v1.view_own_supplier'), 'is_radio' => true, 'radio_group' => 'supplier_view'],
            'supplier.create', 'supplier.update', 'supplier.delete',
            ['value' => 'customer.view', 'label' => __('lang_v1.view_all_customer'), 'is_radio' => true, 'radio_group' => 'customer_view'],
            ['value' => 'customer.view_own', 'label' => __('lang_v1.view_own_customer'), 'is_radio' => true, 'radio_group' => 'customer_view'],
            'customer.create', 'customer.update', 'customer.delete'
          ]
        ])

        {{-- Product & Inventory Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('business.product') . ', ' . __('role.brand') . ', ' . __('category.category') . ' & ' . __('role.unit'),
          'icon' => 'fa-cubes',
          'items' => [
            'product.view', 'product.create', 'product.update', 'product.delete', 'product.opening_stock', 'view_purchase_price',
            'brand.view', 'brand.create', 'brand.update', 'brand.delete',
            'category.view', 'category.create', 'category.update', 'category.delete',
            'unit.view', 'unit.create', 'unit.update', 'unit.delete',
            'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete'
          ]
        ])

        {{-- Purchase Permissions --}}
        @if(in_array('purchases', $enabled_modules))
          @include('role.partials.permission_group', [
            'title' => __('role.purchase'),
            'icon' => 'fa-shopping-cart',
            'items' => [
              ['value' => 'purchase.view', 'label' => __('lang_v1.view_all_purchase'), 'is_radio' => true, 'radio_group' => 'purchase_view'],
              ['value' => 'view_own_purchase', 'label' => __('lang_v1.view_own_purchase'), 'is_radio' => true, 'radio_group' => 'purchase_view'],
              'purchase.create', 'purchase.update', 'purchase.delete', 'purchase.payments', 'edit_purchase_payment', 'delete_purchase_payment', 'purchase.update_status'
            ]
          ])
        @endif

        {{-- Sales & POS Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('sale.pos_sale') . ' & ' . __('sale.sale'),
          'icon' => 'fa-line-chart',
          'items' => [
            'sell.view', 'sell.create', 'sell.update', 'sell.delete', 'sell.print', 'sell.send_whatsapp',
            'direct_sell.access', 'direct_sell.update', 'direct_sell.delete', 'sell.payments', 'edit_sell_payment', 'delete_sell_payment',
            'edit_product_price_from_pos_screen', 'edit_product_discount_from_pos_screen', 'edit_pos_payment', 'print_invoice',
            'disable_pay_checkout', 'disable_draft', 'disable_express_checkout', 'disable_discount', 'disable_suspend_sale', 'disable_credit_sale', 'disable_quotation', 'disable_card',
            'discount.access', 'access_sell_return', 'access_own_sell_return', 'edit_invoice_number'
          ]
        ])

        {{-- Stock Adjustment & Transfer --}}
        @include('role.partials.permission_group', [
          'title' => __('role.stock_adjustment') . ' & ' . __('role.stock_transfer'),
          'icon' => 'fa-truck',
          'items' => [
            ['value' => 'stock_adjustment.view', 'label' => __('role.stock_adjustment.view'), 'is_radio' => true, 'radio_group' => 'stock_adjustment_view'],
            ['value' => 'view_own_stock_adjustment', 'label' => __('role.stock_adjustment.view_own'), 'is_radio' => true, 'radio_group' => 'stock_adjustment_view'],
            'stock_adjustment.create', 'stock_adjustment.update', 'stock_adjustment.delete',
            ['value' => 'stock_transfer.view', 'label' => __('role.stock_transfer.view'), 'is_radio' => true, 'radio_group' => 'stock_transfer_view'],
            ['value' => 'stock_transfer.view_own', 'label' => __('role.stock_transfer.view_own'), 'is_radio' => true, 'radio_group' => 'stock_transfer_view'],
            'stock_transfer.create', 'stock_transfer.update', 'stock_transfer.delete'
          ]
        ])

        {{-- Laundry Module Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.laundry'),
          'icon' => 'fa-washing-machine',
          'items' => [
            'laundry.view_dashboard', 'laundry.view', 'laundry.create', 'laundry.update', 'laundry.delete',
            'laundry.update_status', 'laundry.log_process', 'laundry.manage_master_data', 'laundry.view_staff_points',
            'laundry.add_payment', 'laundry.print', 'laundry.send_whatsapp'
          ]
        ])

        {{-- Repair Module Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.repair'),
          'icon' => 'fa-wrench',
          'items' => [
            'repair.view', 'repair.create', 'repair.update', 'repair.delete',
            'repair_status.update', 'repair_status.access', 'repair.add_payment',
            'repair.print', 'repair.send_whatsapp'
          ]
        ])

        {{-- Manufacturing Module Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.manufacturing'),
          'icon' => 'fa-industry',
          'items' => [
            'manufacturing.access_recipe', 'manufacturing.add_recipe', 'manufacturing.edit_recipe', 'manufacturing.delete_recipe',
            'manufacturing.access_production', 'manufacturing.add_production', 'manufacturing.edit_production', 'manufacturing.delete_production'
          ]
        ])

        {{-- Asset Management Module Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.asset'),
          'icon' => 'fa-building',
          'items' => ['asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.revoke', 'asset.maintenance']
        ])

        {{-- HRM & Essentials Module Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.essentials'),
          'icon' => 'fa-id-badge',
          'items' => [
            'essentials.view_all_attendance', 'essentials.view_own_attendance', 'essentials.crud_all_attendance', 'essentials.crud_own_attendance',
            'essentials.approve_leave', 'essentials.create_message', 'essentials.view_message', 'essentials.assign_todos', 'essentials.crud_payroll'
          ]
        ])

        {{-- CRM & Project Module Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.crm') . ' & ' . __('role.project'),
          'icon' => 'fa-handshake-o',
          'items' => [
            'crm.view_all_leads', 'crm.view_own_leads', 'crm.access_all_schedule', 'crm.access_own_schedule', 'crm.access_all_campaigns',
            'project.view_project', 'project.create_project', 'project.edit_project', 'project.delete_project'
          ]
        ])

        {{-- Expense, Account & Reports Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('lang_v1.expense') . ', ' . __('account.account') . ' & ' . __('role.report'),
          'icon' => 'fa-calculator',
          'items' => [
            ['value' => 'all_expense.access', 'label' => __('lang_v1.access_all_expense'), 'is_radio' => true, 'radio_group' => 'expense_view'],
            ['value' => 'view_own_expense', 'label' => __('lang_v1.view_own_expense'), 'is_radio' => true, 'radio_group' => 'expense_view'],
            'expense.add', 'expense.edit', 'expense.delete',
            'account.access', 'edit_account_transaction', 'delete_account_transaction',
            'purchase_n_sell_report.view', 'tax_report.view', 'contacts_report.view', 'expense_report.view',
            'profit_loss_report.view', 'stock_report.view', 'trending_product_report.view', 'register_report.view',
            'sales_representative.view', 'view_product_stock_value', 'dashboard.data'
          ]
        ])

        {{-- Settings Permissions --}}
        @include('role.partials.permission_group', [
          'title' => __('role.settings'),
          'icon' => 'fa-cogs',
          'items' => [
            'business_settings.access', 'barcode_settings.access', 'invoice_settings.access', 'access_printers', 'access_default_selling_price', 'view_export_buttons', 'send_payment_received_notification', 'send_payment_reminder_notification'
          ]
        ])

        @include('role.partials.module_permissions')

        <div class="row tw-mt-6">
        <div class="col-md-12 text-center">
           <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang( 'messages.save' )</button>
        </div>
        </div>

        {!! Form::close() !!}
    @endcomponent
</section>
<!-- /.content -->
@endsection

@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
    var presetPermissions = {
      'cashier': [
        'sell.view', 'sell.create', 'sell.update', 'direct_sell.access', 'view_cash_register', 'close_cash_register',
        'access_all_locations', 'print_invoice', 'sell.print'
      ],
      'accountant': [
        'account.access', 'view_purchase_price', 'sell.view', 'purchase.view', 'all_expense.access', 'profit_loss_report.view',
        'access_all_locations'
      ],
      'warehouse': [
        'product.view', 'product.create', 'product.update', 'purchase.view', 'purchase.create', 'purchase.update',
        'stock_adjustment.view', 'stock_adjustment.create', 'stock_transfer.view', 'stock_transfer.create', 'access_all_locations'
      ],
      'sales_supervisor': [
        'sell.view', 'sell.create', 'sell.update', 'sell.delete', 'edit_sell_price', 'discount.access',
        'view_own_sell_only', 'access_all_locations', 'sell.print', 'sell.send_whatsapp'
      ],
      'store_manager': [
        'user.view', 'user.create', 'user.update', 'supplier.view', 'supplier.create', 'customer.view', 'customer.create',
        'product.view', 'product.create', 'product.update', 'purchase.view', 'purchase.create', 'purchase.update',
        'sell.view', 'sell.create', 'sell.update', 'sell.delete', 'access_all_locations'
      ],
      'cs': [
        'customer.view', 'customer.create', 'customer.update', 'sell.view', 'view_own_sell_only', 'crm.view_own_leads'
      ],
      'laundry_admin': [
        'laundry.view_dashboard', 'laundry.view', 'laundry.create', 'laundry.update', 'laundry.delete', 'laundry.update_status',
        'laundry.log_process', 'laundry.manage_master_data', 'laundry.view_staff_points', 'laundry.add_payment', 'laundry.print', 'laundry.send_whatsapp'
      ],
      'repair_tech': [
        'repair.view', 'repair.create', 'repair.update', 'repair.delete', 'repair_status.update', 'repair_status.access',
        'repair.add_payment', 'repair.print', 'repair.send_whatsapp'
      ],
      'mfg_supervisor': [
        'manufacturing.access_recipe', 'manufacturing.add_recipe', 'manufacturing.edit_recipe', 'manufacturing.delete_recipe',
        'manufacturing.access_production', 'manufacturing.add_production', 'manufacturing.edit_production', 'manufacturing.delete_production'
      ],
      'procurement': [
        'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete', 'purchase.payments', 'supplier.view', 'supplier.create', 'supplier.update'
      ],
      'hrm_admin': [
        'user.view', 'user.create', 'user.update', 'user.delete', 'essentials.view_all_attendance', 'essentials.crud_all_attendance',
        'essentials.approve_leave', 'essentials.assign_todos', 'essentials.crud_payroll'
      ],
      'asset_manager': [
        'asset.view', 'asset.create', 'asset.update', 'asset.delete', 'asset.revoke', 'asset.maintenance'
      ]
    };

    var customTemplates = {
      @if(!empty($custom_templates))
        @foreach($custom_templates as $tmpl)
          'custom_{{ $tmpl->id }}': {!! json_encode($tmpl->permissions ?? []) !!},
        @endforeach
      @endif
    };

    $('#preset_role_select').on('change', function(){
      var val = $(this).val();
      var perms = null;

      if (val && presetPermissions[val]) {
        perms = presetPermissions[val];
      } else if (val && customTemplates[val]) {
        perms = customTemplates[val];
      }

      if (perms) {
        $('input.input-icheck').each(function(){
          var permName = $(this).val();
          if (perms.includes(permName)) {
            $(this).iCheck('check');
          } else if ($(this).attr('name') === 'permissions[]') {
            $(this).iCheck('uncheck');
          }
        });
      }
    });

    $('#save_as_template_chk').on('ifChanged', function(event){
      if(event.target.checked) {
        $('#template_name_box').slideDown();
      } else {
        $('#template_name_box').slideUp();
      }
    });
  });
</script>
@endsection
