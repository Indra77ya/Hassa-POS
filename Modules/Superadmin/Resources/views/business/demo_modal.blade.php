<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'postGenerateDemo'], [$business->id]), 'method' => 'post', 'id' => 'business_generate_demo_form' ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('superadmin::lang.generate_demo_data_heading') - {{ $business->name }}</h4>
        </div>

        <div class="modal-body">
            <div class="checkbox" style="margin-bottom: 20px;">
                <label style="font-weight: bold; color: #d9534f; cursor: pointer;">
                    {!! Form::checkbox('reset_old_data', 1, true, ['id' => 'reset_old_data']) !!}
                    @lang('superadmin::lang.reset_old_data_first')
                </label>
                <div class="help-block" style="margin-left: 20px; font-size: 12px; color: #737373;">
                    @lang('superadmin::lang.reset_old_data_help')
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_users', __('superadmin::lang.num_users') . ':') !!}
                        {!! Form::number('num_users', 5, ['class' => 'form-control', 'min' => 0, 'max' => 50, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_suppliers', __('superadmin::lang.num_suppliers') . ':') !!}
                        {!! Form::number('num_suppliers', 10, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_customers', __('superadmin::lang.num_customers') . ':') !!}
                        {!! Form::number('num_customers', 10, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_products', __('superadmin::lang.num_products') . ':') !!}
                        {!! Form::number('num_products', 20, ['class' => 'form-control', 'min' => 0, 'max' => 20000, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_variations', __('superadmin::lang.num_variations') . ':') !!}
                        {!! Form::number('num_variations', 3, ['class' => 'form-control', 'min' => 0, 'max' => 20, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_units', __('superadmin::lang.num_units') . ':') !!}
                        {!! Form::number('num_units', 5, ['class' => 'form-control', 'min' => 0, 'max' => 20, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_categories', __('superadmin::lang.num_categories') . ':') !!}
                        {!! Form::number('num_categories', 5, ['class' => 'form-control', 'min' => 0, 'max' => 30, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_brands', __('superadmin::lang.num_brands') . ':') !!}
                        {!! Form::number('num_brands', 5, ['class' => 'form-control', 'min' => 0, 'max' => 30, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_warranties', __('superadmin::lang.num_warranties') . ':') !!}
                        {!! Form::number('num_warranties', 3, ['class' => 'form-control', 'min' => 0, 'max' => 10, 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('num_transactions', __('superadmin::lang.num_transactions') . ':') !!}
                        {!! Form::number('num_transactions', 15, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'required']) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="btn-submit-demo">@lang('superadmin::lang.generate_demo_data')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).off('submit', 'form#business_generate_demo_form').on('submit', 'form#business_generate_demo_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('#btn-submit-demo');
            submitBtn.prop('disabled', true);

            var data = form.serialize();

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
        });
    });
</script>
