@extends('layouts.app')
@section('title', __('laundry::lang.staff_points_report'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.staff_points_report')</h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('staff_id', __('laundry::lang.staff') . ':') !!}
                {!! Form::select('staff_id', $staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('date_range', __('report.date_range') . ':') !!}
                {!! Form::text('date_range', null, ['class' => 'form-control', 'id' => 'staff_points_date_range', 'placeholder' => __('lang_v1.select_a_date_range'), 'readonly']) !!}
            </div>
        </div>
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-info', 'title' => __('laundry::lang.staff_points_summary')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('laundry::lang.staff_name')</th>
                                <th>@lang('laundry::lang.total_tasks_completed')</th>
                                <th>@lang('laundry::lang.total_points_earned')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff_summary as $summary)
                                <tr>
                                    <td>{{ $summary->staff_name }}</td>
                                    <td>{{ $summary->total_tasks }}</td>
                                    <td><strong>{{ number_format($summary->total_points, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">@lang('lang_v1.no_data')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

    @component('components.widget', ['class' => 'box-primary', 'title' => __('laundry::lang.detailed_points_log')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="staff_points_table">
                <thead>
                    <tr>
                        <th>@lang('laundry::lang.order_no')</th>
                        <th>@lang('laundry::lang.process_name')</th>
                        <th>@lang('laundry::lang.staff_name')</th>
                        <th>@lang('laundry::lang.quantity')</th>
                        <th>@lang('laundry::lang.process_points')</th>
                        <th>@lang('laundry::lang.points_earned')</th>
                        <th>@lang('laundry::lang.completed_at')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    $('#staff_points_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#staff_points_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            staff_points_table.ajax.reload();
        }
    );
    $('#staff_points_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#staff_points_date_range').val('');
        staff_points_table.ajax.reload();
    });

    var staff_points_table = $('#staff_points_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ action([\Modules\Laundry\Http\Controllers\LaundryReportController::class, 'staffPointsReport']) }}',
            data: function(d) {
                d.staff_id = $('#staff_id').val();
                if($('#staff_points_date_range').val()) {
                    var start = $('#staff_points_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#staff_points_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
            }
        },
        columns: [
            { data: 'order_no', name: 'os.order_no' },
            { data: 'process_name', name: 'lp.name' },
            { data: 'staff_name', name: 'u.first_name' },
            { data: 'quantity', name: 'os.quantity' },
            { data: 'process_points', name: 'lp.points' },
            { data: 'points_earned', name: 'laundry_order_process_logs.points_earned' },
            { data: 'completed_at', name: 'laundry_order_process_logs.completed_at' }
        ]
    });

    $(document).on('change', '#staff_id', function() {
        staff_points_table.ajax.reload();
    });
});
</script>
@endsection
