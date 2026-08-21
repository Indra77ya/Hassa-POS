<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'postResetData'], [$business->id]), 'method' => 'post', 'id' => 'business_reset_data_form' ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('superadmin::lang.reset_business_data') - {{ $business->name }}</h4>
        </div>

        <div class="modal-body">
            <!-- Global Select All / Total Reset Banner -->
            <div class="well well-sm" style="background-color: #fff0f0; border-color: #d9534f; margin-bottom: 15px;">
                <label style="cursor: pointer; font-size: 15px; font-weight: bold; color: #a94442; margin-bottom: 0;">
                    {!! Form::checkbox('select_all_global', 1, false, ['id' => 'select_all_global']) !!}
                    <i class="fa fa-warning text-danger"></i> @lang('superadmin::lang.select_all_global')
                </label>
                <div class="help-block" style="margin: 3px 0 0 20px; color: #737373; font-size: 12px;">
                    @lang('superadmin::lang.select_all_global_help')
                </div>
            </div>

            <div class="row">
                <!-- Column 1: Data Transaksi -->
                <div class="col-md-4">
                    <div class="well well-sm" style="min-height: 400px; background-color: #fcfcfc;">
                        <h4 style="margin-top: 0; color: #d9534f; font-weight: bold; border-bottom: 1px solid #d9534f; padding-bottom: 5px;">
                            <label style="cursor: pointer; font-size: 14px;">
                                {!! Form::checkbox('select_all_transactions', 1, false, ['id' => 'select_all_transactions', 'class' => 'parent_category']) !!}
                                @lang('superadmin::lang.select_all_transactions')
                            </label>
                        </h4>
                        <div class="transaction-children-container" style="margin-left: 15px;">
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_transactions[]', 'sales', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_sales')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_transactions[]', 'purchases', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_purchases')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_transactions[]', 'expenses', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_expenses')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_transactions[]', 'registers', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_registers')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_transactions[]', 'stock_adjustments', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_stock_adjustments')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_transactions[]', 'finance', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_finance')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer; color: #d9534f; font-weight: bold;">
                                    {!! Form::checkbox('reset_transactions[]', 'reset_stock', false, ['class' => 'transaction_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_stock') <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Data Master -->
                <div class="col-md-4">
                    <div class="well well-sm" style="min-height: 400px; background-color: #fcfcfc;">
                        <h4 style="margin-top: 0; color: #f0ad4e; font-weight: bold; border-bottom: 1px solid #f0ad4e; padding-bottom: 5px;">
                            <label style="cursor: pointer; font-size: 14px;">
                                {!! Form::checkbox('select_all_master', 1, false, ['id' => 'select_all_master', 'class' => 'parent_category']) !!}
                                @lang('superadmin::lang.select_all_master')
                            </label>
                        </h4>
                        <div class="master-children-container" style="margin-left: 15px;">
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'products', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_products')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'contacts', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_contacts')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'categories', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_categories')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'brands', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_brands')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'taxes', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_taxes')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'units', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_units')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'customer_groups', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_customer_groups')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_master[]', 'warranties', false, ['class' => 'master_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_warranties')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Data Modul -->
                <div class="col-md-4">
                    <div class="well well-sm" style="min-height: 400px; background-color: #fcfcfc;">
                        <h4 style="margin-top: 0; color: #337ab7; font-weight: bold; border-bottom: 1px solid #337ab7; padding-bottom: 5px;">
                            <label style="cursor: pointer; font-size: 14px;">
                                {!! Form::checkbox('select_all_modules', 1, false, ['id' => 'select_all_modules', 'class' => 'parent_category']) !!}
                                @lang('superadmin::lang.select_all_modules')
                            </label>
                        </h4>
                        <div class="module-children-container" style="margin-left: 15px;">
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_modules[]', 'asset_management', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_asset_management')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_modules[]', 'manufacturing', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_manufacturing')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_modules[]', 'repair', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_repair')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_modules[]', 'essentials', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_essentials')
                                </label>
                            </div>
                            <div class="checkbox">
                                <label style="font-size: 13px; cursor: pointer;">
                                    {!! Form::checkbox('reset_modules[]', 'crm', false, ['class' => 'module_child child_checkbox']) !!}
                                    @lang('superadmin::lang.reset_crm')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-danger" id="btn-submit-reset">@lang('superadmin::lang.reset_selected')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
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
