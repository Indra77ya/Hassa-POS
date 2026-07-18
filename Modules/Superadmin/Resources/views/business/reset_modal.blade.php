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
                <div class="col-md-6">
                    <h4 class="tw-font-bold tw-text-black tw-mb-4">@lang('superadmin::lang.transaction_data')</h4>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_sales_pos', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_sales_pos')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_purchases', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_purchases')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_expenses', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_expenses')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_cash_registers', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_cash_registers')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_stock_adjustments', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_stock_adjustments')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_stock_transfers', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_stock_transfers')
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Master Data Column -->
                <div class="col-md-6">
                    <h4 class="tw-font-bold tw-text-black tw-mb-4">@lang('superadmin::lang.master_data')</h4>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_products', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_products')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_contacts', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_contacts')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_categories_brands', 1, false, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.reset_categories_brands')
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('reset_tax_rates', 1, false, ['class' => 'input-icheck']) !!}
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

        function checkResetConfirmation() {
            var val = $('#confirm_word_input').val();
            var anyChecked = false;

            // Check if at least one checkbox is checked
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

        $(document).on('keyup change', '#confirm_word_input', function() {
            checkResetConfirmation();
        });

        $(document).on('change', 'form#reset_business_form input[type="checkbox"]', function() {
            checkResetConfirmation();
        });

        // If iCheck is used, we listen to iCheck events
        $('form#reset_business_form input[type="checkbox"]').on('ifChanged', function() {
            checkResetConfirmation();
        });
    });
</script>
