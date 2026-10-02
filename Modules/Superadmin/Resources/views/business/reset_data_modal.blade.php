<div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.2); border: none;">
        {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'postResetData'], [$business->id]), 'method' => 'post', 'id' => 'business_reset_data_form' ]) !!}
        <div class="modal-header" style="background: linear-gradient(135deg, #d9534f 0%, #c9302c 100%); color: white; padding: 15px 20px;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8; font-size: 24px;"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight: 700; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-undo"></i> @lang('superadmin::lang.reset_business_data') &mdash; <span style="font-weight: 400; opacity: 0.95;">{{ $business->name }}</span>
            </h4>
        </div>

        <div class="modal-body" style="padding: 20px; background-color: #f8fafc;">
            <!-- Global Select All / Total Reset Banner -->
            <div class="panel" style="border-radius: 6px; border: 1.5px solid #f87171; background-color: #fef2f2; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div class="panel-body" style="padding: 12px 16px;">
                    <label style="cursor: pointer; font-size: 15px; font-weight: 700; color: #991b1b; margin-bottom: 0; display: flex; align-items: center; gap: 10px;">
                        {!! Form::checkbox('select_all_global', 1, false, ['id' => 'select_all_global', 'style' => 'width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;']) !!}
                        <i class="fa fa-exclamation-triangle text-danger" style="font-size: 18px;"></i>
                        <span>@lang('superadmin::lang.select_all_global')</span>
                    </label>
                    <div class="help-block" style="margin: 4px 0 0 28px; color: #7f1d1d; font-size: 12px; font-weight: 500;">
                        @lang('superadmin::lang.select_all_global_help')
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Column 1: Data Transaksi -->
                <div class="col-md-4">
                    <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #e2e8f0; min-height: 410px; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
                        <div class="panel-heading" style="background-color: #fef2f2; border-bottom: 2px solid #ef4444; padding: 10px 14px;">
                            <label style="cursor: pointer; font-size: 14px; font-weight: 700; color: #991b1b; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                                {!! Form::checkbox('select_all_transactions', 1, false, ['id' => 'select_all_transactions', 'class' => 'parent_category', 'style' => 'width: 16px; height: 16px; accent-color: #dc2626;']) !!}
                                <i class="fa fa-calculator text-danger"></i>
                                <span>@lang('superadmin::lang.select_all_transactions')</span>
                            </label>
                        </div>
                        <div class="panel-body transaction-children-container" style="padding: 12px 14px; flex-grow: 1;">
                            <div class="checkbox" style="margin-top: 5px; margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_transactions[]', 'sales', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_sales')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_transactions[]', 'purchases', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_purchases')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_transactions[]', 'expenses', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_expenses')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_transactions[]', 'registers', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_registers')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_transactions[]', 'stock_adjustments', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_stock_adjustments')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_transactions[]', 'finance', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_finance')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 5px; padding-top: 8px; border-top: 1px dashed #e2e8f0;">
                                <label style="font-size: 13px; cursor: pointer; color: #dc2626; font-weight: 700;">
                                    {!! Form::checkbox('reset_transactions[]', 'reset_stock', false, ['class' => 'transaction_child child_checkbox', 'style' => 'accent-color: #dc2626;']) !!}
                                    @lang('superadmin::lang.reset_stock') <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Data Master -->
                <div class="col-md-4">
                    <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #e2e8f0; min-height: 410px; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
                        <div class="panel-heading" style="background-color: #fffbe3; border-bottom: 2px solid #f59e0b; padding: 10px 14px;">
                            <label style="cursor: pointer; font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                                {!! Form::checkbox('select_all_master', 1, false, ['id' => 'select_all_master', 'class' => 'parent_category', 'style' => 'width: 16px; height: 16px; accent-color: #d97706;']) !!}
                                <i class="fa fa-database text-warning"></i>
                                <span>@lang('superadmin::lang.select_all_master')</span>
                            </label>
                        </div>
                        <div class="panel-body master-children-container" style="padding: 12px 14px; flex-grow: 1;">
                            <div class="checkbox" style="margin-top: 5px; margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'products', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_products')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'contacts', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_contacts')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'categories', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_categories')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'brands', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_brands')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'taxes', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_taxes')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'units', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_units')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'customer_groups', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_customer_groups')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 5px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_master[]', 'warranties', false, ['class' => 'master_child child_checkbox', 'style' => 'accent-color: #d97706;']) !!}
                                    @lang('superadmin::lang.reset_warranties')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Data Modul -->
                <div class="col-md-4">
                    <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #e2e8f0; min-height: 410px; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
                        <div class="panel-heading" style="background-color: #eff6ff; border-bottom: 2px solid #3b82f6; padding: 10px 14px;">
                            <label style="cursor: pointer; font-size: 14px; font-weight: 700; color: #1e40af; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                                {!! Form::checkbox('select_all_modules', 1, false, ['id' => 'select_all_modules', 'class' => 'parent_category', 'style' => 'width: 16px; height: 16px; accent-color: #2563eb;']) !!}
                                <i class="fa fa-cubes text-primary"></i>
                                <span>@lang('superadmin::lang.select_all_modules')</span>
                            </label>
                        </div>
                        <div class="panel-body module-children-container" style="padding: 12px 14px; flex-grow: 1;">
                            <div class="checkbox" style="margin-top: 5px; margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_modules[]', 'asset_management', false, ['class' => 'module_child child_checkbox', 'style' => 'accent-color: #2563eb;']) !!}
                                    @lang('superadmin::lang.reset_asset_management')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_modules[]', 'manufacturing', false, ['class' => 'module_child child_checkbox', 'style' => 'accent-color: #2563eb;']) !!}
                                    @lang('superadmin::lang.reset_manufacturing')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_modules[]', 'repair', false, ['class' => 'module_child child_checkbox', 'style' => 'accent-color: #2563eb;']) !!}
                                    @lang('superadmin::lang.reset_repair')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_modules[]', 'essentials', false, ['class' => 'module_child child_checkbox', 'style' => 'accent-color: #2563eb;']) !!}
                                    @lang('superadmin::lang.reset_essentials')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_modules[]', 'crm', false, ['class' => 'module_child child_checkbox', 'style' => 'accent-color: #2563eb;']) !!}
                                    @lang('superadmin::lang.reset_crm')
                                </label>
                            </div>
                            <div class="checkbox" style="margin-bottom: 5px;">
                                <label style="font-size: 13px; cursor: pointer; font-weight: 500; color: #334155;">
                                    {!! Form::checkbox('reset_modules[]', 'laundry', false, ['class' => 'module_child child_checkbox', 'style' => 'accent-color: #2563eb;']) !!}
                                    @lang('superadmin::lang.reset_laundry')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="background-color: #f1f5f9; padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 600; border-radius: 4px;">@lang('messages.close')</button>
            <button type="submit" class="btn btn-danger" id="btn-submit-reset" style="font-weight: 600; border-radius: 4px; background-color: #dc2626; border-color: #dc2626;">
                <i class="fa fa-refresh"></i> @lang('superadmin::lang.reset_selected')
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
                    var originalText = submitBtn.html();
                    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

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
                            toastr.error("Terjadi kesalahan saat memproses permintaan.");
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        });
    });
</script>
