<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'updateStatus'], [$order_sheet->id]), 'method' => 'post', 'id' => 'update_laundry_status_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.change_status') ({{ $order_sheet->order_no }})</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('laundry_status_id', __('laundry::lang.status') . ':*') !!}
        {!! Form::select('laundry_status_id', $statuses, $order_sheet->laundry_status_id, ['class' => 'form-control select2', 'required', 'style' => 'width:100%']) !!}
      </div>

      <hr>
      <h4>@lang('laundry::lang.process_staff_assignment')</h4>
      @php
          $existing_logs = $order_sheet->processLogs->pluck('staff_id', 'laundry_process_id')->toArray();
      @endphp
      @foreach($processes as $proc)
          <div class="form-group">
              <label>{{ $proc->name }} (@lang('laundry::lang.points'): {{ $proc->points }}):</label>
              {!! Form::select("process_staffs[{$proc->id}]", $staffs, isset($existing_logs[$proc->id]) ? $existing_logs[$proc->id] : null, ['class' => 'form-control select2', 'placeholder' => __('laundry::lang.select_staff'), 'style' => 'width:100%']) !!}
          </div>
      @endforeach
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
