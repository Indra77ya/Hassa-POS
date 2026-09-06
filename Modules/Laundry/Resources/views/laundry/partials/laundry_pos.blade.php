<div class="col-md-4">
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-shopping-basket"></i>
            </span>
            {!! Form::select('laundry_order_sheet_id', $order_sheets, null, ['class' => 'form-control select2', 'placeholder' => __('laundry::lang.select_order_sheet'), 'id' => 'laundry_order_sheet_id']) !!}
        </div>
    </div>
</div>
