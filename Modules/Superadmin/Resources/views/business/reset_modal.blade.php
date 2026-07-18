<div class="modal fade" id="reset_business_data_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            {!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'reset_business_data_form']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('superadmin::lang.reset_business_data')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Transactions Data Column -->
                    <div class="col-md-6">
                        <div class="box box-solid box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">@lang('superadmin::lang.transactions_data')</h3>
                            </div>
                            <div class="box-body" style="padding-left: 20px;">
                                <div class="checkbox">
                                    <label class="tw-font-bold text-primary">
                                        <input type="checkbox" id="select_all_transactions" name="reset_options[]" value="select_all_transactions">
                                        <strong>@lang('superadmin::lang.select_all')</strong>
                                    </label>
                                </div>
                                <hr style="margin-top: 5px; margin-bottom: 5px;">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_sales">
                                        @lang('superadmin::lang.reset_sales')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_purchases">
                                        @lang('superadmin::lang.reset_purchases')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_expenses">
                                        @lang('superadmin::lang.reset_expenses')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_registers">
                                        @lang('superadmin::lang.reset_registers')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction_reset_option" name="reset_options[]" value="reset_stock_adjustments">
                                        @lang('superadmin::lang.reset_stock_adjustments')
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Master Data Column -->
                    <div class="col-md-6">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">@lang('superadmin::lang.master_data')</h3>
                            </div>
                            <div class="box-body" style="padding-left: 20px;">
                                <div class="checkbox">
                                    <label class="tw-font-bold text-success">
                                        <input type="checkbox" id="select_all_master" name="reset_options[]" value="select_all_master">
                                        <strong>@lang('superadmin::lang.select_all')</strong>
                                    </label>
                                </div>
                                <hr style="margin-top: 5px; margin-bottom: 5px;">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_products">
                                        @lang('superadmin::lang.reset_products')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_contacts">
                                        @lang('superadmin::lang.reset_contacts')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_categories">
                                        @lang('superadmin::lang.reset_categories')
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master_reset_option" name="reset_options[]" value="reset_taxes">
                                        @lang('superadmin::lang.reset_taxes')
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-error tw-text-white" id="confirm_reset_business_btn">@lang('superadmin::lang.reset_data')</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
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
