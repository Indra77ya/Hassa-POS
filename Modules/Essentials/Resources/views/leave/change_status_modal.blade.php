<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'changeStatus']),
            'method' => 'post',
            'id' => 'change_status_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('essentials::lang.change_status')</h4>
        </div>
        <div class="modal-body">

            <div class="form-group">
                <input type="hidden" value="{{ $current_leave->id }}" name="leave_id" id="leave_id">
                <label for="status">@lang('sale.status'):*</label>
                <select class="form-control select2" name="status" required id="status_dropdown" style="width: 100%;">
                    @foreach ($leave_statuses as $key => $value)
                        <option value="{{ $key }}" @if ($current_leave->status == $key) selected @endif>
                            {{ $value['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status_note">@lang('brand.note'):</label>
                <textarea class="form-control" name="status_note" rows="3" id="status_note"></textarea>
            </div>
            <div class="table-responsive table-condensed">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.leave_type')</th>
                            <th> @lang('essentials::lang.max_allowed_leaves')  @if($leaveType->leave_count_interval == 'month')
								(@lang('essentials::lang.within_current_month'))
							@elseif($leaveType->leave_count_interval == 'year')
								(@lang('essentials::lang.within_current_fy'))
							@endif
						</th>
                            <th>@lang('essentials::lang.leave_taken')</th>
							<th>@lang('essentials::lang.leave_left')</th>
                            <th>@lang('essentials::lang.leave_applied')</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>{{ $leaveType->leave_type }}</td>
                            <td>{{ $leaveType->max_leave_count }} @lang('lang_v1.days')</td>
                            <td>{{ $leaveCount }} @lang('lang_v1.days')</td>
                            @php
                                $start = \Carbon\Carbon::parse($current_leave->start_date);
                                $end = \Carbon\Carbon::parse($current_leave->end_date);
                                $leaveDays = $start->diffInDays($end) + 1; // +1 to include the end date
                            @endphp
							<td class="@if(($leaveType->max_leave_count - $leaveCount) < 0) bg-danger @endif">{{ $leaveType->max_leave_count - $leaveCount }} @lang('lang_v1.days')</td>
                            <td>{{ $leaveDays }} @lang('lang_v1.days')</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($leaveType->max_leave_count - ($leaveCount + $leaveDays) < 0)
                <div class="form-check form-check-inline is_additional">
                    <input class="form-check-input" @if($current_leave->is_additional) checked @endif type="checkbox" id="is_additional" name="is_additional" value="1">
                    <label class="form-check-label" for="is_additional">@lang('essentials::lang.approve_additional_leave_text')</label>
                </div>
            @endif
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white ladda-button update-leave-status"
                data-style="expand-right">
                <span class="ladda-label">@lang('messages.update')</span>
            </button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<style>
  .is_additional {
    display: flex;
    align-items: center;
    gap: 8px; /* Adjust spacing */
    flex-wrap: nowrap; /* Prevent line break */
    max-width: 100%; /* Ensures it fits within parent */
    overflow: hidden;
}

.is_additional label {
    white-space: normal; /* Allow text to wrap inside modal */
    flex: 1; /* Take remaining space */
}

</style>