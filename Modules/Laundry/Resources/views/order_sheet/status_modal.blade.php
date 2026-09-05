<div class="modal-dialog modal-lg" role="document">
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
      <div class="row">
        <div class="col-md-12">
            <h4 class="text-primary pull-left">@lang('laundry::lang.process_staff_assignment')</h4>
            <button type="button" class="btn btn-success btn-xs pull-right" id="modal_add_process_row_btn">
                <i class="fa fa-plus"></i> @lang('laundry::lang.add_process_row')
            </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="col-md-5">@lang('laundry::lang.process_name')</th>
                    <th class="col-md-5">@lang('laundry::lang.staff_in_charge')</th>
                    <th class="col-md-2 text-center">@lang('messages.action')</th>
                </tr>
            </thead>
            <tbody id="modal_process_rows_container">
                @foreach($order_sheet->processLogs as $index => $log)
                    <tr class="modal-process-row">
                        <td>
                            <select name="process_rows[{{ $index }}][process_id]" class="form-control select2 modal-process-select" required style="width:100%">
                                <option value="">@lang('laundry::lang.select_process')</option>
                                @foreach($processes as $p)
                                    <option value="{{ $p->id }}" {{ $p->id == $log->laundry_process_id ? 'selected' : '' }}>
                                        {{ $p->name }} (@lang('laundry::lang.points'): {{ $p->points }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="process_rows[{{ $index }}][staff_id]" class="form-control select2 modal-staff-select" style="width:100%">
                                <option value="">@lang('laundry::lang.select_staff')</option>
                                @foreach($staffs as $s_id => $s_name)
                                    <option value="{{ $s_id }}" {{ $s_id == $log->staff_id ? 'selected' : '' }}>{{ $s_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs remove-modal-process-row"><i class="fa fa-trash"></i> @lang('laundry::lang.remove_process_row')</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var modal_process_options_html = `<option value="">{{ __('laundry::lang.select_process') }}</option>`;
    @foreach($processes as $p)
        modal_process_options_html += `<option value="{{ $p->id }}">{{ e($p->name) }} ({{ __('laundry::lang.points') }}: {{ $p->points }})</option>`;
    @endforeach

    var modal_staff_options_html = `<option value="">{{ __('laundry::lang.select_staff') }}</option>`;
    @foreach($staffs as $s_id => $s_name)
        modal_staff_options_html += `<option value="{{ $s_id }}">{{ e($s_name) }}</option>`;
    @endforeach

    $(document).on('click', '#modal_add_process_row_btn', function() {
        var idx = new Date().getTime() + Math.floor(Math.random() * 1000);
        var row_html = `<tr class="modal-process-row">
            <td>
                <select name="process_rows[${idx}][process_id]" class="form-control select2 modal-process-select" required style="width:100%">
                    ${modal_process_options_html}
                </select>
            </td>
            <td>
                <select name="process_rows[${idx}][staff_id]" class="form-control select2 modal-staff-select" style="width:100%">
                    ${modal_staff_options_html}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs remove-modal-process-row"><i class="fa fa-trash"></i> {{ __('laundry::lang.remove_process_row') }}</button>
            </td>
        </tr>`;

        var $row = $(row_html);
        $('#modal_process_rows_container').append($row);
        $row.find('.select2').select2();
    });

    $(document).on('click', '.remove-modal-process-row', function() {
        $(this).closest('tr.modal-process-row').remove();
    });
});
</script>
