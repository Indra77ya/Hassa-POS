<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'store']), 'method' => 'post', 'id' => 'quick_add_order_sheet_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('laundry::lang.add_order_sheet')</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
            {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('laundry_status_id', __('laundry::lang.status') . ':*') !!}
            {!! Form::select('laundry_status_id', $statuses, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('laundry_service_type_id', __('laundry::lang.service_type') . ':*') !!}
            {!! Form::select('laundry_service_type_id', $service_types, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('laundry_item_type_id', __('laundry::lang.item_type') . ':*') !!}
            {!! Form::select('laundry_item_type_id', $item_types, null, ['class' => 'form-control select2', 'id' => 'modal_laundry_item_type_id', 'required', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
          </div>
        </div>
        <div class="col-md-4">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                {!! Form::label('quantity', __('laundry::lang.quantity') . ':*') !!}
                {!! Form::number('quantity', 1, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                {!! Form::label('unit_name', __('laundry::lang.unit') . ':*') !!}
                {!! Form::text('unit_name', 'kg', ['class' => 'form-control', 'id' => 'modal_unit_name', 'required']) !!}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('delivery_type', __('laundry::lang.delivery_type') . ':') !!}
            {!! Form::select('delivery_type', ['self_service' => __('laundry::lang.self_service'), 'pickup_delivery' => __('laundry::lang.pickup_delivery')], 'self_service', ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('received_at', __('laundry::lang.received_at') . ':') !!}
            {!! Form::text('received_at', \Carbon\Carbon::now()->format('Y-m-d H:i'), ['class' => 'form-control date-time-picker']) !!}
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('items_detail', __('laundry::lang.items_detail') . ':') !!}
            {!! Form::textarea('items_detail', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Contoh: 3 Kemeja, 2 Celana Jeans, 1 Bedcover']) !!}
          </div>
        </div>
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
        <table class="table table-bordered table-striped" id="modal_process_rows_table">
          <thead>
            <tr>
              <th class="col-md-4">@lang('laundry::lang.process_name')</th>
              <th class="col-md-4">@lang('laundry::lang.staff_in_charge')</th>
              <th class="col-md-3">@lang('laundry::lang.process_status')</th>
              <th class="col-md-1 text-center">@lang('messages.action')</th>
            </tr>
          </thead>
          <tbody id="modal_process_rows_container">
            @foreach($processes as $index => $proc)
              <tr class="process-row">
                <td>
                  <select name="process_rows[{{ $index }}][process_id]" class="form-control select2 process-select" required style="width:100%">
                    <option value="">@lang('laundry::lang.select_process')</option>
                    @foreach($processes as $p)
                      <option value="{{ $p->id }}" {{ $p->id == $proc->id ? 'selected' : '' }}>
                        {{ $p->name }} (@lang('laundry::lang.points'): {{ $p->points }})
                      </option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <select name="process_rows[{{ $index }}][staff_id]" class="form-control select2 staff-select" style="width:100%">
                    <option value="">@lang('laundry::lang.select_staff')</option>
                    @foreach($staffs as $s_id => $s_name)
                      <option value="{{ $s_id }}">{{ $s_name }}</option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <select name="process_rows[{{ $index }}][status]" class="form-control select2 status-select" style="width:100%">
                    <option value="pending">@lang('laundry::lang.pending')</option>
                    <option value="in_progress">@lang('laundry::lang.in_progress')</option>
                    <option value="completed">@lang('laundry::lang.completed')</option>
                  </select>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-danger btn-xs remove-process-row"><i class="fa fa-trash"></i> @lang('laundry::lang.remove_process_row')</button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('notes', __('brand.note') . ':') !!}
            {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) !!}
          </div>
        </div>
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
$(document).ready(function() {
    $('.view_modal .select2').select2();

    if ($('.date-time-picker').length) {
        $('.date-time-picker').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });
    }

    var modal_process_options = `<option value="">{{ __('laundry::lang.select_process') }}</option>`;
    @foreach($processes as $p)
        modal_process_options += `<option value="{{ $p->id }}">{{ e($p->name) }} ({{ __('laundry::lang.points') }}: {{ $p->points }})</option>`;
    @endforeach

    var modal_staff_options = `<option value="">{{ __('laundry::lang.select_staff') }}</option>`;
    @foreach($staffs as $s_id => $s_name)
        modal_staff_options += `<option value="{{ $s_id }}">{{ e($s_name) }}</option>`;
    @endforeach

    function addModalProcessRow(selected_process_id, selected_staff_id, selected_status) {
        var idx = new Date().getTime() + Math.floor(Math.random() * 1000);
        var row_html = `<tr class="process-row">
            <td>
                <select name="process_rows[${idx}][process_id]" class="form-control select2 process-select" required style="width:100%">
                    ${modal_process_options}
                </select>
            </td>
            <td>
                <select name="process_rows[${idx}][staff_id]" class="form-control select2 staff-select" style="width:100%">
                    ${modal_staff_options}
                </select>
            </td>
            <td>
                <select name="process_rows[${idx}][status]" class="form-control select2 status-select" style="width:100%">
                    <option value="pending">{{ __('laundry::lang.pending') }}</option>
                    <option value="in_progress">{{ __('laundry::lang.in_progress') }}</option>
                    <option value="completed">{{ __('laundry::lang.completed') }}</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs remove-process-row"><i class="fa fa-trash"></i> {{ __('laundry::lang.remove_process_row') }}</button>
            </td>
        </tr>`;

        var $row = $(row_html);
        if (selected_process_id) $row.find('.process-select').val(selected_process_id);
        if (selected_staff_id) $row.find('.staff-select').val(selected_staff_id);
        if (selected_status) $row.find('.status-select').val(selected_status);

        $('#modal_process_rows_container').append($row);
        $row.find('.select2').select2();
    }

    $(document).off('click', '#modal_add_process_row_btn').on('click', '#modal_add_process_row_btn', function() {
        addModalProcessRow(null, null);
    });

    $(document).off('click', '.remove-process-row').on('click', '.remove-process-row', function() {
        $(this).closest('tr.process-row').remove();
    });

    $(document).off('change', '#modal_laundry_item_type_id').on('change', '#modal_laundry_item_type_id', function() {
        var item_type_id = $(this).val();
        if (item_type_id) {
            $.ajax({
                url: '/laundry/item-types/get-details/' + item_type_id,
                dataType: 'json',
                success: function(result) {
                    if (result && result.unit_name) {
                        $('#modal_unit_name').val(result.unit_name);
                    }
                    if (result && result.processes && result.processes.length > 0) {
                        $('#modal_process_rows_container').empty();
                        $.each(result.processes, function(i, proc) {
                            addModalProcessRow(proc.id, null);
                        });
                    }
                }
            });
        }
    });
});
</script>
