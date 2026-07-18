<div class="modal fade" id="reset_business_data_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">
            {!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'reset_business_data_form']) !!}
            <div class="modal-header" style="background-color: #f5f5f5; border-bottom: 1px solid #e5e5e5;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="font-size: 24px;">&times;</span></button>
                <h4 class="modal-title" style="font-weight: bold; color: #333333;">@lang('superadmin::lang.reset_business_data')</h4>
            </div>

            <div class="modal-body" style="padding: 25px;">
                <!-- Warning Callout / Alert -->
                <div class="row">
                    <div class="col-xs-12" style="margin-bottom: 20px;">
                        <div class="callout callout-danger" style="margin: 0; border-left: 5px solid #d9534f; background-color: #fdf7f7 !important; color: #a94442 !important; padding: 15px; border-radius: 4px;">
                            <h4 style="font-weight: bold; margin-top: 0; color: #d9534f !important;">PENTING / WARNING!</h4>
                            <p style="font-size: 14px; margin-bottom: 0;">@lang('superadmin::lang.reset_data_confirmation')</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Transactions Data Column -->
                    <div class="col-md-6">
                        <div class="box box-solid box-primary" style="border: 1px solid #3c8dbc; border-radius: 4px; overflow: hidden;">
                            <div class="box-header with-border" style="background-color: #3c8dbc !important; padding: 12px 15px;">
                                <h3 class="box-title" style="color: #ffffff !important; font-weight: bold; font-size: 16px;">@lang('superadmin::lang.transactions_data')</h3>
                            </div>
                            <div class="box-body" style="padding: 20px;">
                                <div class="checkbox" style="margin-top: 0; margin-bottom: 15px;">
                                    <label class="tw-font-bold text-primary" style="font-size: 15px; cursor: pointer; color: #3c8dbc;">
                                        <input type="checkbox" id="select_all_transactions" name="reset_options[]" value="select_all_transactions" style="transform: scale(1.15); margin-right: 8px;">
                                        <strong>@lang('superadmin::lang.select_all')</strong>
                                    </label>
                                </div>
                                <hr style="margin-top: 10px; margin-bottom: 15px; border-top: 1px solid #eeeeee;">

                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_sales" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_sales')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_purchases" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_purchases')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_expenses" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_expenses')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_registers" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_registers')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_stock_adjustments" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_stock_adjustments')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Master Data Column -->
                    <div class="col-md-6">
                        <div class="box box-solid box-success" style="border: 1px solid #00a65a; border-radius: 4px; overflow: hidden;">
                            <div class="box-header with-border" style="background-color: #00a65a !important; padding: 12px 15px;">
                                <h3 class="box-title" style="color: #ffffff !important; font-weight: bold; font-size: 16px;">@lang('superadmin::lang.master_data')</h3>
                            </div>
                            <div class="box-body" style="padding: 20px;">
                                <div class="checkbox" style="margin-top: 0; margin-bottom: 15px;">
                                    <label class="tw-font-bold text-success" style="font-size: 15px; cursor: pointer; color: #00a65a;">
                                        <input type="checkbox" id="select_all_master" name="reset_options[]" value="select_all_master" style="transform: scale(1.15); margin-right: 8px;">
                                        <strong>@lang('superadmin::lang.select_all')</strong>
                                    </label>
                                </div>
                                <hr style="margin-top: 10px; margin-bottom: 15px; border-top: 1px solid #eeeeee;">

                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_products" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_products')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_contacts" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_contacts')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_categories" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_categories')
                                        </label>
                                    </div>
                                    <div class="checkbox" style="margin: 0;">
                                        <label style="font-size: 14px; cursor: pointer; color: #555555;">
                                            <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_taxes" style="transform: scale(1.1); margin-right: 8px;">
                                            @lang('superadmin::lang.reset_taxes')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background-color: #f5f5f5; border-top: 1px solid #e5e5e5; padding: 15px 25px; display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                <button type="button" class="tw-m-0.5 tw-dw-btn tw-dw-btn-error tw-text-white" id="confirm_reset_business_btn" style="font-weight: bold; height: 36px; min-height: 36px; margin: 0; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; line-height: 1;">@lang('superadmin::lang.reset_data')</button>
                <button type="button" class="tw-m-0.5 tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal" style="font-weight: bold; height: 36px; min-height: 36px; margin: 0; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; line-height: 1;">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        $(document).ready(function() {
            // Toggle Select All Transactions
            $(document).on('change', '#select_all_transactions', function() {
                var checked = $(this).is(':checked');
                $('.transaction_reset_option').each(function() {
                    $(this).prop('checked', checked);
                    $(this).prop('disabled', checked);
                });
            });

            // Toggle Select All Master Data
            $(document).on('change', '#select_all_master', function() {
                var checked = $(this).is(':checked');
                $('.master_reset_option').each(function() {
                    $(this).prop('checked', checked);
                    $(this).prop('disabled', checked);
                });
            });

            // Click Reset Button in Table or view
            $(document).on('click', '.reset_business_data_btn', function(e) {
                e.preventDefault();
                var business_id = $(this).data('business_id');
                var business_name = $(this).data('business_name');

                // Reset form
                $('#reset_business_data_form')[0].reset();
                $('.transaction_reset_option, .master_reset_option').prop('disabled', false).prop('checked', false);

                var is_superadmin = window.location.pathname.includes('/superadmin');
                var url = is_superadmin ? '/superadmin/business/' + business_id + '/reset-data' : '/business/reset-data';
                $('#reset_business_data_form').attr('action', url);

                $('#reset_business_data_modal').modal('show');
            });

            // Confirm Reset
            $(document).on('click', '#confirm_reset_business_btn', function(e) {
                e.preventDefault();

                var selected_options = [];
                // Check normal checked checkboxes
                $('#reset_business_data_form input[name="reset_options[]"]:checked').each(function() {
                    selected_options.push($(this).val());
                });

                // Also collect checked checkboxes that are disabled due to Select All
                if ($('#select_all_transactions').is(':checked')) {
                    selected_options.push('select_all_transactions');
                }
                if ($('#select_all_master').is(':checked')) {
                    selected_options.push('select_all_master');
                }

                if (selected_options.length === 0) {
                    toastr.error("@lang('superadmin::lang.please_select_at_least_one')");
                    return false;
                }

                swal({
                    title: LANG.sure,
                    text: "@lang('superadmin::lang.reset_data_confirmation')",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        var url = $('#reset_business_data_form').attr('action');
                        var data = {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            reset_options: selected_options
                        };

                        $.ajax({
                            method: 'POST',
                            url: url,
                            data: data,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success) {
                                    $('#reset_business_data_modal').modal('hide');
                                    swal({
                                        title: "Success",
                                        text: result.msg,
                                        icon: "success"
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                } else {
                                    swal("Error", result.msg, "error");
                                }
                            },
                            error: function(xhr) {
                                swal("Error", "@lang('messages.something_went_wrong')", "error");
                            }
                        });
                    }
                });
            });
        });
    });
</script>
