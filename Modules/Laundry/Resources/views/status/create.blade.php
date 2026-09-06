<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\LaundryStatusController::class, 'store']), 'method' => 'post', 'id' => 'add_status_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.add_status')</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('laundry::lang.name') . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('laundry::lang.name')]) !!}
      </div>

      <div class="form-group">
        {!! Form::label('color', __('laundry::lang.color') . ':') !!}
        {!! Form::text('color', '#3c8dbc', ['class' => 'form-control']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('sort_order', __('laundry::lang.sort_order') . ':') !!}
        {!! Form::number('sort_order', 0, ['class' => 'form-control']) !!}
      </div>

      <div class="checkbox">
        <label>
          {!! Form::checkbox('is_completed_status', 1, false) !!} @lang('laundry::lang.mark_as_completed_status')
        </label>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
