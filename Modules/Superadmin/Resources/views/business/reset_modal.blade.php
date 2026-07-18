<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'resetBusinessData'], [$business_id]), 'method' => 'post', 'id' => 'reset_business_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('superadmin::lang.reset_business_data') - {{ $business_name }}</h4>
        </div>

        <div class="modal-body">
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> {!! __('superadmin::lang.reset_business_desc') !!}
            </div>

            <div class="row">
                <!-- Transaction Data Column -->
                <div class="col-md-6" id="transaction_section">
                    <h4 class="tw-font-bold tw-text-black tw-mb-4">@lang('superadmin::lang.transaction_data')</h4>
                    <div class="form-group">
                        <div class="checkbox">
                            <label class="text-primary" style="font-weight: bold;">
                                {!! Form::checkbox('select_all_transactions', 1, false, ['class' => 'input-icheck', 'id' => 'select_all_transactions']) !!}
                                <i>@lang('superadmin::lang.select_all')</i>
                            </label>
                        </div>
                        <hr style="margin-top: 5px; margin-bottom: 5px;">
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_sales_pos', 1, false, ['class' => 'input-icheck transaction_checkbox']) !!}
                                @lang('superadmin::lang.reset_sales_pos')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_purchases', 1, false, ['class' => 'input-icheck transaction_checkbox']) !!}
                                @lang('superadmin::lang.reset_purchases')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_expenses', 1, false, ['class' => 'input-icheck transaction_checkbox']) !!}
                                @lang('superadmin::lang.reset_expenses')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_cash_registers', 1, false, ['class' => 'input-icheck transaction_checkbox']) !!}
                                @lang('superadmin::lang.reset_cash_registers')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_stock_adjustments', 1, false, ['class' => 'input-icheck transaction_checkbox']) !!}
                                @lang('superadmin::lang.reset_stock_adjustments')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_stock_transfers', 1, false, ['class' => 'input-icheck transaction_checkbox']) !!}
                                @lang('superadmin::lang.reset_stock_transfers')
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Master Data Column -->
                <div class="col-md-6" id="master_section">
                    <h4 class="tw-font-bold tw-text-black tw-mb-4">@lang('superadmin::lang.master_data')</h4>
                    <div class="form-group">
                        <div class="checkbox">
                            <label class="text-primary" style="font-weight: bold;">
                                {!! Form::checkbox('select_all_master', 1, false, ['class' => 'input-icheck', 'id' => 'select_all_master']) !!}
                                <i>@lang('superadmin::lang.select_all')</i>
                            </label>
                        </div>
                        <hr style="margin-top: 5px; margin-bottom: 5px;">
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_products', 1, false, ['class' => 'input-icheck master_checkbox']) !!}
                                @lang('superadmin::lang.reset_products')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_contacts', 1, false, ['class' => 'input-icheck master_checkbox']) !!}
                                @lang('superadmin::lang.reset_contacts')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_categories_brands', 1, false, ['class' => 'input-icheck master_checkbox']) !!}
                                @lang('superadmin::lang.reset_categories_brands')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_tax_rates', 1, false, ['class' => 'input-icheck master_checkbox']) !!}
                                @lang('superadmin::lang.reset_tax_rates')
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('confirm_word_input', __('superadmin::lang.type_reset_to_confirm', ['word' => 'RESET']) . ':') !!}
                        {!! Form::text('confirm_word_input', null, ['class' => 'form-control', 'id' => 'confirm_word_input', 'placeholder' => 'RESET', 'autocomplete' => 'off']) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-error tw-text-white" id="submit_reset_btn" disabled>@lang('superadmin::lang.confirm_reset')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize iCheck if applicable
        if (typeof iCheck === 'function') {
            $('.input-icheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
        }

        // Handle check confirmation function
        function checkResetConfirmation() {
            var val = $('#confirm_word_input').val();
            var anyChecked = false;

            // Check if at least one checkbox is checked (including select_all headers)
            $('form#reset_business_form input[type="checkbox"]').each(function() {
                if ($(this).is(':checked')) {
                    anyChecked = true;
                }
            });

            if (val === 'RESET' && anyChecked) {
                $('#submit_reset_btn').removeAttr('disabled');
            } else {
                $('#submit_reset_btn').attr('disabled', 'disabled');
            }
        }

        var is_programmatic = false;

        // Helper function to update Select All state based on individual checkbox changes
        function updateSelectAllState(allSelector, checkedSelector, selectAllInput) {
            if (is_programmatic) return;

            var total = $(allSelector).length;
            var checked = $(checkedSelector).length;

            is_programmatic = true;
            if (checked === total) {
                $(selectAllInput).prop('checked', true).iCheck('update');
            } else {
                $(selectAllInput).prop('checked', false).iCheck('update');
            }
            is_programmatic = false;

            checkResetConfirmation();
        }

        // Select All Transactions logic
        $('#select_all_transactions').on('ifChecked', function() {
            if (!is_programmatic) {
                is_programmatic = true;
                $('.transaction_checkbox').prop('checked', true).iCheck('update');
                is_programmatic = false;
                checkResetConfirmation();
            }
        });
        $('#select_all_transactions').on('ifUnchecked', function() {
            if (!is_programmatic) {
                is_programmatic = true;
                $('.transaction_checkbox').prop('checked', false).iCheck('update');
                is_programmatic = false;
                checkResetConfirmation();
            }
        });

        // Select All Master logic
        $('#select_all_master').on('ifChecked', function() {
            if (!is_programmatic) {
                is_programmatic = true;
                $('.master_checkbox').prop('checked', true).iCheck('update');
                is_programmatic = false;
                checkResetConfirmation();
            }
        });
        $('#select_all_master').on('ifUnchecked', function() {
            if (!is_programmatic) {
                is_programmatic = true;
                $('.master_checkbox').prop('checked', false).iCheck('update');
                is_programmatic = false;
                checkResetConfirmation();
            }
        });

        // Individual Transaction checkboxes logic
        $('.transaction_checkbox').on('ifChanged', function() {
            updateSelectAllState('.transaction_checkbox', '.transaction_checkbox:checked', '#select_all_transactions');
        });

        // Individual Master checkboxes logic
        $('.master_checkbox').on('ifChanged', function() {
            updateSelectAllState('.master_checkbox', '.master_checkbox:checked', '#select_all_master');
        });

        $(document).on('keyup change', '#confirm_word_input', function() {
            checkResetConfirmation();
        });
    });
</script>
