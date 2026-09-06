<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class, 'update'], [$service_type->id]), 'method' => 'put', 'id' => 'edit_service_type_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.edit_service_type')</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('laundry::lang.name') . ':*') !!}
        {!! Form::text('name', $service_type->name, ['class' => 'form-control', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('completion_hours', __('laundry::lang.completion_hours') . ':*') !!}
        {!! Form::number('completion_hours', $service_type->completion_hours, ['class' => 'form-control', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', __('brand.note') . ':') !!}
        {!! Form::textarea('description', $service_type->description, ['class' => 'form-control', 'rows' => 2]) !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
