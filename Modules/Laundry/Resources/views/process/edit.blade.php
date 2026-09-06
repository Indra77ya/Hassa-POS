<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'update'], [$process->id]), 'method' => 'put', 'id' => 'edit_process_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.edit_process')</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('laundry::lang.name') . ':*') !!}
        {!! Form::text('name', $process->name, ['class' => 'form-control', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('points', __('laundry::lang.points') . ':*') !!}
        {!! Form::number('points', $process->points, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('sort_order', __('laundry::lang.sort_order') . ':') !!}
        {!! Form::number('sort_order', $process->sort_order, ['class' => 'form-control']) !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', __('brand.note') . ':') !!}
        {!! Form::textarea('description', $process->description, ['class' => 'form-control', 'rows' => 2]) !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
