@extends('layouts.app')

@section('title', __('accounting::lang.trial_balance'))

@section('content')

@include('accounting::layouts.nav')

<section class="content">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, request()->input('location_id'), ['class' => 'form-control select2', 'id' => 'location_id']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('date_range_filter', __('report.date_range') . ':') !!}
                {!! Form::text('date_range_filter', null,
                    ['placeholder' => __('lang_v1.select_a_date_range'),
                    'class' => 'form-control', 'readonly', 'id' => 'date_range_filter']); !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="box box-warning">
                <div class="box-header with-border text-center">
                    <h2 class="box-title">@lang( 'accounting::lang.trial_balance')</h2>
                    <p>{{@format_date($start_date)}} ~ {{@format_date($end_date)}}</p>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                                <tr class="bg-gray">
                                    <th rowspan="2" class="text-center" style="vertical-align: middle;">@lang('accounting::lang.account')</th>
                                    <th colspan="2" class="text-center">@lang('accounting::lang.opening_balance')</th>
                                    <th colspan="2" class="text-center">@lang('accounting::lang.current_period')</th>
                                    <th colspan="2" class="text-center">@lang('accounting::lang.ending_balance')</th>
                                </tr>
                                <tr class="bg-gray">
                                    <th class="text-center">@lang('accounting::lang.debit')</th>
                                    <th class="text-center">@lang('accounting::lang.credit')</th>
                                    <th class="text-center">@lang('accounting::lang.debit')</th>
                                    <th class="text-center">@lang('accounting::lang.credit')</th>
                                    <th class="text-center">@lang('accounting::lang.debit')</th>
                                    <th class="text-center">@lang('accounting::lang.credit')</th>
                                </tr>
                            </thead>

                            @php
                                $total_opening_debit = 0;
                                $total_opening_credit = 0;
                                $total_current_debit = 0;
                                $total_current_credit = 0;
                                $total_ending_debit = 0;
                                $total_ending_credit = 0;
                            @endphp

                            <tbody>
                                @foreach($accounts as $account)
                                    @php
                                        $total_opening_debit += $account->opening_debit;
                                        $total_opening_credit += $account->opening_credit;
                                        $total_current_debit += $account->current_debit;
                                        $total_current_credit += $account->current_credit;
                                        $total_ending_debit += $account->ending_debit;
                                        $total_ending_credit += $account->ending_credit;
                                    @endphp

                                    <tr>
                                        <td>{{$account->name}}</td>
                                        <td class="text-right">
                                            @if($account->opening_debit != 0)
                                                @format_currency($account->opening_debit)
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($account->opening_credit != 0)
                                                @format_currency($account->opening_credit)
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($account->current_debit != 0)
                                                @format_currency($account->current_debit)
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($account->current_credit != 0)
                                                @format_currency($account->current_credit)
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($account->ending_debit != 0)
                                                @format_currency($account->ending_debit)
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($account->ending_credit != 0)
                                                @format_currency($account->ending_credit)
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="bg-gray font-weight-bold">
                                    <th class="text-center">@lang('sale.total')</th>
                                    <th class="text-right">@format_currency($total_opening_debit)</th>
                                    <th class="text-right">@format_currency($total_opening_credit)</th>
                                    <th class="text-right">@format_currency($total_current_debit)</th>
                                    <th class="text-right">@format_currency($total_current_credit)</th>
                                    <th class="text-right">@format_currency($total_ending_debit)</th>
                                    <th class="text-right">@format_currency($total_ending_credit)</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


@stop

@section('javascript')

<script type="text/javascript">
    $(document).ready(function(){

        dateRangeSettings.startDate = moment('{{$start_date}}');
        dateRangeSettings.endDate = moment('{{$end_date}}');

        $('#date_range_filter').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_range_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                apply_filter();
            }
        );
        $('#date_range_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#date_range_filter').val('');
            apply_filter();
        });

        $('#location_id').change(function(){
            apply_filter();
        });

        function apply_filter(){
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

            var location_id = $('#location_id').val();

            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('start_date', start);
            urlParams.set('end_date', end);
            if (location_id) {
                urlParams.set('location_id', location_id);
            } else {
                urlParams.delete('location_id');
            }
            window.location.search = urlParams;
        }
    });

</script>

@stop