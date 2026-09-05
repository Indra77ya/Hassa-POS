<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'store']), 'method' => 'post', 'id' => 'add_item_type_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.add_item_type')</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('laundry::lang.name') . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('laundry::lang.name')]) !!}
      </div>

      <div class="form-group">
        {!! Form::label('unit_name', __('laundry::lang.unit') . ':*') !!}
        {!! Form::text('unit_name', 'kg', ['class' => 'form-control', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('default_price', __('laundry::lang.default_price') . ':') !!}
        {!! Form::number('default_price', 0, ['class' => 'form-control', 'step' => '0.01']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', __('brand.note') . ':') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
