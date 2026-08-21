@extends('layouts.app')
@section('title', __( 'account.trial_balance' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'account.trial_balance')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-sm-12">
            @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('trial_bal_location_id',  __('purchase.business_location') . ':') !!}
                    {!! Form::select('trial_bal_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('date_range_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range_filter', null,
                        ['placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control', 'readonly', 'id' => 'date_range_filter']); !!}
                </div>
            </div>
            @endcomponent
        </div>
    </div>
    <br>
    <div class="box box-solid">
        <div class="box-header print_section text-center">
            <h2 class="box-title" style="font-weight: bold;">@lang( 'account.trial_balance')</h2>
            <p id="trial_balance_period_text">{{@format_date($start_date)}} ~ {{@format_date($end_date)}}</p>
        </div>
        <div class="box-body">
            <table class="table table-bordered table-pl-12" id="trial_balance_table">
                <thead>
                    <tr class="bg-gray">
                        <th rowspan="2" class="text-center" style="vertical-align: middle;">@lang('account.account')</th>
                        <th colspan="2" class="text-center">@lang('account.opening_balance')</th>
                        <th colspan="2" class="text-center">@lang('account.current_period')</th>
                        <th colspan="2" class="text-center">@lang('account.ending_balance')</th>
                    </tr>
                    <tr class="bg-gray">
                        <th class="text-center">@lang('account.debit')</th>
                        <th class="text-center">@lang('account.credit')</th>
                        <th class="text-center">@lang('account.debit')</th>
                        <th class="text-center">@lang('account.credit')</th>
                        <th class="text-center">@lang('account.debit')</th>
                        <th class="text-center">@lang('account.credit')</th>
                    </tr>
                </thead>
                <tbody id="trial_balance_details">
                </tbody>
                <tfoot>
                    <tr class="bg-gray">
                        <th class="text-right">@lang('sale.total')</th>
                        <td class="text-right" id="total_opening_debit"></td>
                        <td class="text-right" id="total_opening_credit"></td>
                        <td class="text-right" id="total_debit"></td>
                        <td class="text-right" id="total_credit"></td>
                        <td class="text-right" id="total_balance_debit"></td>
                        <td class="text-right" id="total_balance_credit"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="box-footer">
            <button type="button" class="btn btn-primary no-print pull-right"onclick="window.print()">
          <i class="fa fa-print"></i> @lang('messages.print')</button>
        </div>
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')

<script type="text/javascript">
    $(document).ready( function(){
        dateRangeSettings.startDate = moment('{{$start_date}}');
        dateRangeSettings.endDate = moment('{{$end_date}}');

        $('#date_range_filter').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_range_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                update_trial_balance();
            }
        );
        $('#date_range_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#date_range_filter').val('');
            update_trial_balance();
        });

        // Set initial val of the input to match the configured daterangepicker start & end dates
        $('#date_range_filter').val(dateRangeSettings.startDate.format(moment_date_format) + ' ~ ' + dateRangeSettings.endDate.format(moment_date_format));

        update_trial_balance();

        $('#trial_bal_location_id').change( function() {
            update_trial_balance();
        });
    });

    function update_trial_balance(){
        $('#trial_balance_details').html('<tr><td colspan="7" class="text-center"><i class="fas fa-sync fa-spin fa-fw"></i></td></tr>');

        var start = '';
        var end = '';

        if ($('#date_range_filter').val()) {
            start = $('input#date_range_filter')
                .data('daterangepicker')
                .startDate.format('YYYY-MM-DD');
            end = $('input#date_range_filter')
                .data('daterangepicker')
                .endDate.format('YYYY-MM-DD');
        }

        var location_id = $('#trial_bal_location_id').val()
        $.ajax({
            url: "{{action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance'])}}?start_date=" + start + "&end_date=" + end + '&location_id=' + location_id,
            dataType: "json",
            success: function(result){
                var total_opening_debit = 0;
                var total_opening_credit = 0;
                var total_current_debit = 0;
                var total_current_credit = 0;
                var total_ending_debit = 0;
                var total_ending_credit = 0;
                var rows = '';

                // Update period text in the header
                if (result.start_date && result.end_date) {
                    var formatted_start = moment(result.start_date).format(moment_date_format);
                    var formatted_end = moment(result.end_date).format(moment_date_format);
                    $('#trial_balance_period_text').text(formatted_start + ' ~ ' + formatted_end);
                }

                var accounts = result.accounts;

                accounts.forEach(function(account) {
                    var opening_debit = parseFloat(account.opening_debit) || 0;
                    var opening_credit = parseFloat(account.opening_credit) || 0;
                    var current_debit = parseFloat(account.current_debit) || 0;
                    var current_credit = parseFloat(account.current_credit) || 0;
                    var ending_debit = parseFloat(account.ending_debit) || 0;
                    var ending_credit = parseFloat(account.ending_credit) || 0;

                    rows += '<tr>' +
                        '<td>' + account.name + '</td>' +
                        '<td class="text-right">' + (opening_debit > 0 ? __currency_trans_from_en(opening_debit, true) : '') + '</td>' +
                        '<td class="text-right">' + (opening_credit > 0 ? __currency_trans_from_en(opening_credit, true) : '') + '</td>' +
                        '<td class="text-right">' + (current_debit > 0 ? __currency_trans_from_en(current_debit, true) : '') + '</td>' +
                        '<td class="text-right">' + (current_credit > 0 ? __currency_trans_from_en(current_credit, true) : '') + '</td>' +
                        '<td class="text-right">' + (ending_debit > 0 ? __currency_trans_from_en(ending_debit, true) : '') + '</td>' +
                        '<td class="text-right">' + (ending_credit > 0 ? __currency_trans_from_en(ending_credit, true) : '') + '</td>' +
                    '</tr>';

                    total_opening_debit += opening_debit;
                    total_opening_credit += opening_credit;
                    total_current_debit += current_debit;
                    total_current_credit += current_credit;
                    total_ending_debit += ending_debit;
                    total_ending_credit += ending_credit;
                });

                $('#trial_balance_details').html(rows);
                $('#total_opening_debit').text(__currency_trans_from_en(total_opening_debit, true));
                $('#total_opening_credit').text(__currency_trans_from_en(total_opening_credit, true));
                $('#total_debit').text(__currency_trans_from_en(total_current_debit, true));
                $('#total_credit').text(__currency_trans_from_en(total_current_credit, true));
                $('#total_balance_debit').text(__currency_trans_from_en(total_ending_debit, true));
                $('#total_balance_credit').text(__currency_trans_from_en(total_ending_credit, true));
            }
        });
    }
</script>

@endsection