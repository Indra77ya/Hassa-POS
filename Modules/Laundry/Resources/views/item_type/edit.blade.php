<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'update'], [$item_type->id]), 'method' => 'put', 'id' => 'edit_item_type_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.edit_item_type')</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('laundry::lang.name') . ':*') !!}
        {!! Form::text('name', $item_type->name, ['class' => 'form-control', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('unit_name', __('laundry::lang.unit') . ':*') !!}
        {!! Form::text('unit_name', $item_type->unit_name, ['class' => 'form-control', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('default_price', __('laundry::lang.default_price') . ':') !!}
        {!! Form::number('default_price', $item_type->default_price, ['class' => 'form-control', 'step' => '0.01']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', __('brand.note') . ':') !!}
        {!! Form::textarea('description', $item_type->description, ['class' => 'form-control', 'rows' => 2]) !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
