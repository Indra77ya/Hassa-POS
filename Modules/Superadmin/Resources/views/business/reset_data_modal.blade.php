<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
        {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'postResetData'], [$business->id]), 'method' => 'post', 'id' => 'business_reset_data_form' ]) !!}

        <!-- Modal Header -->
        <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 20px; color: #64748b; opacity: 0.7;"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight: 600; font-size: 16px; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-undo" style="color: #64748b;"></i>
                <span>@lang('superadmin::lang.reset_business_data')</span>
                <span style="color: #94a3b8; font-weight: 400;">&mdash;</span>
                <span style="color: #334155; font-weight: 500;">{{ $business->name }}</span>
            </h4>
        </div>

        <!-- Modal Body -->
        <div class="modal-body" style="padding: 20px; background-color: #f8fafc;">

            <!-- Global Select All Banner -->
            <div style="background-color: #ffffff; border: 1px solid #fca5a5; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                <label style="cursor: pointer; font-size: 14px; font-weight: 600; color: #991b1b; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                    {!! Form::checkbox('select_all_global', 1, false, ['id' => 'select_all_global', 'style' => 'width: 16px; height: 16px; cursor: pointer; margin: 0;']) !!}
                    <i class="fa fa-exclamation-triangle" style="color: #dc2626;"></i>
                    <span>@lang('superadmin::lang.select_all_global')</span>
                </label>
                <div style="margin-top: 4px; margin-left: 24px; color: #64748b; font-size: 12px; font-weight: 400;">
                    @lang('superadmin::lang.select_all_global_help')
                </div>
            </div>

            <!-- Categories Columns Grid -->
            <div class="row" style="margin-left: -8px; margin-right: -8px;">

                <!-- Column 1: Data Transaksi -->
                <div class="col-md-4" style="padding-left: 8px; padding-right: 8px;">
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; min-height: 380px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
                        <div style="padding: 12px 14px; border-bottom: 1px solid #f1f5f9; background-color: #fafafa; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                            <label style="cursor: pointer; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                                {!! Form::checkbox('select_all_transactions', 1, false, ['id' => 'select_all_transactions', 'class' => 'parent_category', 'style' => 'margin: 0;']) !!}
                                <i class="fa fa-calculator" style="color: #64748b;"></i>
                                <span>@lang('superadmin::lang.select_all_transactions')</span>
                            </label>
                        </div>
                        <div class="transaction-children-container" style="padding: 14px; flex-grow: 1;">
                            <div class="checkbox" style="margin-top: 0; margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_transactions[]', 'sales', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_sales')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_transactions[]', 'purchases', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_purchases')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_transactions[]', 'expenses', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_expenses')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_transactions[]', 'registers', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_registers')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_transactions[]', 'stock_adjustments', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_stock_adjustments')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_transactions[]', 'finance', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_finance')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 0; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                                <label style="font-size: 13px; cursor: pointer; color: #dc2626; font-weight: 600;">
                                    {!! Form::checkbox('reset_transactions[]', 'reset_stock', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_stock') <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Data Master -->
                <div class="col-md-4" style="padding-left: 8px; padding-right: 8px;">
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; min-height: 380px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
                        <div style="padding: 12px 14px; border-bottom: 1px solid #f1f5f9; background-color: #fafafa; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                            <label style="cursor: pointer; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                                {!! Form::checkbox('select_all_master', 1, false, ['id' => 'select_all_master', 'class' => 'parent_category', 'style' => 'margin: 0;']) !!}
                                <i class="fa fa-database" style="color: #64748b;"></i>
                                <span>@lang('superadmin::lang.select_all_master')</span>
                            </label>
                        </div>
                        <div class="master-children-container" style="padding: 14px; flex-grow: 1;">
                            <div class="checkbox" style="margin-top: 0; margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'products', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_products')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'contacts', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_contacts')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'categories', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_categories')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'brands', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_brands')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'taxes', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_taxes')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'units', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_units')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'customer_groups', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_customer_groups')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 0;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_master[]', 'warranties', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_warranties')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Data Modul -->
                <div class="col-md-4" style="padding-left: 8px; padding-right: 8px;">
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; min-height: 380px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
                        <div style="padding: 12px 14px; border-bottom: 1px solid #f1f5f9; background-color: #fafafa; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                            <label style="cursor: pointer; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                                {!! Form::checkbox('select_all_modules', 1, false, ['id' => 'select_all_modules', 'class' => 'parent_category', 'style' => 'margin: 0;']) !!}
                                <i class="fa fa-cubes" style="color: #64748b;"></i>
                                <span>@lang('superadmin::lang.select_all_modules')</span>
                            </label>
                        </div>
                        <div class="module-children-container" style="padding: 14px; flex-grow: 1;">
                            <div class="checkbox" style="margin-top: 0; margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_modules[]', 'asset_management', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_asset_management')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_modules[]', 'manufacturing', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_manufacturing')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_modules[]', 'repair', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_repair')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_modules[]', 'essentials', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_essentials')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_modules[]', 'crm', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_crm')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 0;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 400;">
                                    {!! Form::checkbox('reset_modules[]', 'laundry', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_laundry')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid #e2e8f0; background-color: #ffffff; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 500; font-size: 13px; color: #475569; border-color: #cbd5e1; border-radius: 4px; padding: 6px 16px;">
                @lang('messages.close')
            </button>
            <button type="submit" class="btn btn-danger" id="btn-submit-reset" style="font-weight: 500; font-size: 13px; background-color: #dc2626; border-color: #dc2626; border-radius: 4px; padding: 6px 18px;">
                <i class="fa fa-undo" style="margin-right: 4px;"></i> @lang('superadmin::lang.reset_selected')
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Global Select All Checkbox Handler
        $(document).off('change', '#select_all_global').on('change', '#select_all_global', function() {
            var isChecked = $(this).is(':checked');
            $('.parent_category').prop('checked', isChecked);
            $('.child_checkbox').each(function() {
                $(this).prop('checked', isChecked);
                $(this).prop('disabled', isChecked);
            });
        });

        // Category Select All Handlers
        function setupSelectAllCategory(triggerId, childrenClass) {
            $(document).off('change', triggerId).on('change', triggerId, function() {
                var isChecked = $(this).is(':checked');
                $(childrenClass).each(function() {
                    $(this).prop('checked', isChecked);
                    $(this).prop('disabled', isChecked);
                });
            });
        }

        setupSelectAllCategory('#select_all_transactions', '.transaction_child');
        setupSelectAllCategory('#select_all_master', '.master_child');
        setupSelectAllCategory('#select_all_modules', '.module_child');

        // Form Submission Interceptor with SweetAlert and AJAX
        $(document).off('submit', 'form#business_reset_data_form').on('submit', 'form#business_reset_data_form', function(e) {
            e.preventDefault();
            var form = $(this);

            // Check if at least one checkbox is checked
            var hasChecked = false;
            form.find('input[type="checkbox"]').each(function() {
                if ($(this).is(':checked')) {
                    hasChecked = true;
                }
            });

            if (!hasChecked) {
                toastr.error("{{ __('superadmin::lang.select_at_least_one_category') }}");
                return false;
            }

            swal({
                title: LANG.sure,
                text: "{{ __('superadmin::lang.reset_confirmation_text') }}",
                icon: "warning",
                buttons: [(LANG.cancel ? LANG.cancel : "{{ __('messages.cancel') }}"), "{{ __('superadmin::lang.yes_reset') }}"],
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    // Temporarily enable any disabled fields so they serialize properly
                    var disabledFields = form.find('input[type="checkbox"]:disabled');
                    disabledFields.prop('disabled', false);

                    var data = form.serialize();

                    // Restore the disabled status
                    disabledFields.prop('disabled', true);

                    // Add submit button spinner or disable to prevent double submit
                    var submitBtn = form.find('#btn-submit-reset');
                    var originalText = submitBtn.html();
                    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("superadmin::lang.processing") }}');

                    $.ajax({
                        method: 'POST',
                        url: form.attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                $('.view_modal').modal('hide');
                                toastr.success(result.msg);
                                if (typeof superadmin_business_table !== 'undefined') {
                                    superadmin_business_table.ajax.reload();
                                } else {
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 1000);
                                }
                            } else {
                                toastr.error(result.msg);
                                submitBtn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function() {
                            toastr.error("{{ __('superadmin::lang.error_processing_request') }}");
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        });
    });
</script>
