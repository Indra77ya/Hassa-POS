<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'store']), 'method' => 'post', 'id' => 'add_process_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.add_process')</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('laundry::lang.name') . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('laundry::lang.name')]) !!}
      </div>

      <div class="form-group">
        {!! Form::label('points', __('laundry::lang.points') . ':*') !!}
        {!! Form::number('points', 0, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('sort_order', __('laundry::lang.sort_order') . ':') !!}
        {!! Form::number('sort_order', 0, ['class' => 'form-control']) !!}
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
