<div class="box box-solid box-info" id="trade_in_box">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-refresh"></i> @lang('repair::lang.trade_in_details') / Tukar Tambah</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('trade_in_device_name', __('repair::lang.trade_in_device_name') . ':') !!}
                    {!! Form::text('trade_in[device_name]', !empty($trade_in) ? $trade_in->device_name : null, ['class' => 'form-group input-sm form-control', 'id' => 'trade_in_device_name', 'placeholder' => 'misal: iPhone 11 Pro 64GB']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('trade_in_brand_id', __('product.brand') . ':') !!}
                    {!! Form::select('trade_in[brand_id]', $brands ?? [], !empty($trade_in) ? $trade_in->brand_id : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%;']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('trade_in_category_id', __('product.category') . ':') !!}
                    {!! Form::select('trade_in[category_id]', $categories ?? [], !empty($trade_in) ? $trade_in->category_id : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%;']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('trade_in_serial_no', __('repair::lang.serial_no') . ' / IMEI:') !!}
                    {!! Form::text('trade_in[serial_no]', !empty($trade_in) ? $trade_in->serial_no : null, ['class' => 'form-control input-sm', 'placeholder' => 'SN / IMEI']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('trade_in_valuation_amount', __('repair::lang.valuation_amount') . ' (Potongan Tukar Tambah):') !!}
                    {!! Form::text('trade_in[valuation_amount]', !empty($trade_in) ? @num_format($trade_in->valuation_amount) : 0, ['class' => 'form-control input-sm input_number', 'id' => 'trade_in_valuation_amount']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('trade_in_selling_price', __('repair::lang.expected_selling_price') . ' (Estimasi Harga Jual):') !!}
                    {!! Form::text('trade_in[selling_price]', !empty($trade_in) ? @num_format($trade_in->selling_price) : 0, ['class' => 'form-control input-sm input_number', 'id' => 'trade_in_selling_price']) !!}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('trade_in_condition_notes', __('repair::lang.condition_notes') . ' (Kelayakan / Kondisi Perangkat):') !!}
                    {!! Form::textarea('trade_in[condition_notes]', !empty($trade_in) ? $trade_in->condition_notes : null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'misal: Layar mulus, Baterai health 85%, casing baret halus, unit only']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
