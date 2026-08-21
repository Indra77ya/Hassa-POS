<div class="modal-dialog modal-lg" style="width: 92%; max-width: 1050px;" role="document">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
        {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'postResetData'], [$business->id]), 'method' => 'post', 'id' => 'business_reset_data_form' ]) !!}
        <div class="modal-header" style="background-color: #1e293b; color: #ffffff; padding: 15px 20px;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight: 600; font-size: 16px; margin: 0; color: #ffffff;">
                <i class="fa fa-undo" style="margin-right: 8px; color: #f87171;"></i>@lang('superadmin::lang.reset_business_data') - <span style="color: #cbd5e1;">{{ $business->name }}</span>
            </h4>
        </div>

        <div class="modal-body" style="padding: 20px; background-color: #f8fafc;">
            <!-- Global Select All / Total Reset Banner -->
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px;">
                <label style="cursor: pointer; font-size: 15px; font-weight: 700; color: #991b1b; margin-bottom: 2px; display: flex; align-items: center; gap: 8px;">
                    {!! Form::checkbox('select_all_global', 1, false, ['id' => 'select_all_global', 'style' => 'width: 16px; height: 16px; cursor: pointer; accent-color: #dc2626;']) !!}
                    <span><i class="fa fa-exclamation-triangle" style="margin-right: 4px; color: #dc2626;"></i> @lang('superadmin::lang.select_all_global')</span>
                </label>
                <div style="font-size: 12px; margin-left: 24px; color: #7f1d1d; opacity: 0.85;">
                    @lang('superadmin::lang.select_all_global_help')
                </div>
            </div>

            <div class="row">
                <!-- Column 1: Data Transaksi -->
                <div class="col-md-4">
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; min-height: 420px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 700; border-bottom: 2px solid #ef4444; padding-bottom: 8px;">
                            <label style="cursor: pointer; font-size: 14px; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                                {!! Form::checkbox('select_all_transactions', 1, false, ['id' => 'select_all_transactions', 'class' => 'parent_category', 'style' => 'cursor: pointer; accent-color: #ef4444;']) !!}
                                <span style="color: #dc2626;">@lang('superadmin::lang.select_all_transactions')</span>
                            </label>
                        </h4>
                        <div class="transaction-children-container" style="margin-left: 12px;">
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_transactions[]', 'sales', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_sales')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_transactions[]', 'purchases', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_purchases')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_transactions[]', 'expenses', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_expenses')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_transactions[]', 'registers', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_registers')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_transactions[]', 'stock_adjustments', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_stock_adjustments')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_transactions[]', 'finance', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_finance')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #dc2626; font-weight: 600;">
                                    {!! Form::checkbox('reset_transactions[]', 'reset_stock', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_stock') <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Data Master -->
                <div class="col-md-4">
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; min-height: 420px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 700; border-bottom: 2px solid #f59e0b; padding-bottom: 8px;">
                            <label style="cursor: pointer; font-size: 14px; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                                {!! Form::checkbox('select_all_master', 1, false, ['id' => 'select_all_master', 'class' => 'parent_category', 'style' => 'cursor: pointer; accent-color: #f59e0b;']) !!}
                                <span style="color: #d97706;">@lang('superadmin::lang.select_all_master')</span>
                            </label>
                        </h4>
                        <div class="master-children-container" style="margin-left: 12px;">
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'products', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_products')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'contacts', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_contacts')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'categories', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_categories')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'brands', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_brands')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'taxes', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_taxes')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'units', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_units')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'customer_groups', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_customer_groups')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_master[]', 'warranties', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_warranties')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Data Modul -->
                <div class="col-md-4">
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; min-height: 420px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 700; border-bottom: 2px solid #2563eb; padding-bottom: 8px;">
                            <label style="cursor: pointer; font-size: 14px; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                                {!! Form::checkbox('select_all_modules', 1, false, ['id' => 'select_all_modules', 'class' => 'parent_category', 'style' => 'cursor: pointer; accent-color: #2563eb;']) !!}
                                <span style="color: #2563eb;">@lang('superadmin::lang.select_all_modules')</span>
                            </label>
                        </h4>
                        <div class="module-children-container" style="margin-left: 12px;">
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_modules[]', 'asset_management', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_asset_management')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_modules[]', 'manufacturing', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_manufacturing')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_modules[]', 'repair', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_repair')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_modules[]', 'essentials', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_essentials')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="font-size: 13px; cursor: pointer; color: #334155; font-weight: 500;">
                                    {!! Form::checkbox('reset_modules[]', 'crm', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_crm')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="background-color: #f1f5f9; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <button type="submit" class="btn btn-danger" id="btn-submit-reset" style="font-weight: 600; padding: 6px 16px; border-radius: 4px;">
                <i class="fa fa-refresh" style="margin-right: 4px;"></i> @lang('superadmin::lang.reset_selected')
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 600; padding: 6px 16px; border-radius: 4px;">
                @lang('messages.close')
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
                toastr.error('Silakan pilih setidaknya satu kategori data untuk disetel ulang.');
                return false;
            }

            swal({
                title: LANG.sure,
                text: "Data yang terpilih akan dihapus secara permanen dari sistem!",
                icon: "warning",
                buttons: ["Batal", "Ya, Setel Ulang"],
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
                    submitBtn.prop('disabled', true);

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
                                submitBtn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            toastr.error("Terjadi kesalahan saat memproses permintaan.");
                            submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
