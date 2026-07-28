@extends('layouts.app')

@section('title', __('accounting::lang.balance_sheet'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'accounting::lang.balance_sheet' )</h1>
</section>

<section class="content">

    <div class="col-md-3 no-print">
        <div class="form-group">
            {!! Form::label('date_range_filter', __('report.date_range') . ':') !!}
            {!! Form::text('date_range_filter', null, 
                ['placeholder' => __('lang_v1.select_a_date_range'), 
                'class' => 'form-control', 'readonly', 'id' => 'date_range_filter']); !!}
        </div>
    </div>

    <div class="col-md-10 col-md-offset-1">
        <div class="box box-warning">
            <div class="box-header with-border text-center">
                <h2 class="box-title">@lang( 'accounting::lang.balance_sheet')</h2>
                <p>{{@format_date($start_date)}} ~ {{@format_date($end_date)}}</p>
            </div>

            <div class="box-body">
                
                @php
                    $total_assets = 0;
                    $total_liabilities = 0;
                    $total_equities = 0;
                @endphp

                <table class="table table-bordered table-striped">
                    <tbody>
                        <!-- ASET (AKTIVA) -->
                        <tr class="info" style="font-size: 1.1em;">
                            <th colspan="2"><strong>ASET (AKTIVA)</strong></th>
                        </tr>
                        @foreach($assets as $asset)
                            @if($asset->balance != 0)
                                @php $total_assets += $asset->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$asset->name}}</td>
                                    <td class="text-right">@format_currency($asset->balance)</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="success" style="font-size: 1.1em; border-top: 2px solid #000;">
                            <th><strong>TOTAL ASET</strong></th>
                            <th class="text-right"><strong>@format_currency($total_assets)</strong></th>
                        </tr>

                        <!-- SPACING -->
                        <tr><td colspan="2" style="background-color: #f5f5f5; height: 15px; padding: 0;"></td></tr>

                        <!-- KEWAJIBAN (LIABILITAS) -->
                        <tr class="info" style="font-size: 1.1em;">
                            <th colspan="2"><strong>LIABILITAS (KEWAJIBAN / HUTANG)</strong></th>
                        </tr>
                        @foreach($liabilities as $liability)
                            @if($liability->balance != 0)
                                @php $total_liabilities += $liability->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$liability->name}}</td>
                                    <td class="text-right">@format_currency($liability->balance)</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="warning" style="font-size: 1.1em; border-top: 1px solid #ddd;">
                            <th><strong>TOTAL LIABILITAS</strong></th>
                            <th class="text-right"><strong>@format_currency($total_liabilities)</strong></th>
                        </tr>

                        <!-- SPACING -->
                        <tr><td colspan="2" style="background-color: #f5f5f5; height: 15px; padding: 0;"></td></tr>

                        <!-- EKUITAS (MODAL) -->
                        <tr class="info" style="font-size: 1.1em;">
                            <th colspan="2"><strong>EKUITAS (MODAL)</strong></th>
                        </tr>
                        @foreach($equities as $equity)
                            @if($equity->balance != 0)
                                @php $total_equities += $equity->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$equity->name}}</td>
                                    <td class="text-right">@format_currency($equity->balance)</td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Laba Bersih Tahun Berjalan -->
                        @php $total_equities += $current_period_net_profit; @endphp
                        <tr>
                            <td style="padding-left: 30px;"><strong>Laba Bersih Tahun Berjalan</strong></td>
                            <td class="text-right">@format_currency($current_period_net_profit)</td>
                        </tr>

                        <tr class="warning" style="font-size: 1.1em; border-top: 1px solid #ddd;">
                            <th><strong>TOTAL EKUITAS</strong></th>
                            <th class="text-right"><strong>@format_currency($total_equities)</strong></th>
                        </tr>

                        <!-- SPACING -->
                        <tr><td colspan="2" style="background-color: #f5f5f5; height: 15px; padding: 0;"></td></tr>

                        <!-- TOTAL PASIVA (LIABILITAS + EKUITAS) -->
                        @php $total_liab_owners = $total_liabilities + $total_equities; @endphp
                        <tr class="success" style="font-size: 1.2em; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                            <th><strong>TOTAL LIABILITAS & EKUITAS (PASIVA)</strong></th>
                            <th class="text-right"><strong>@format_currency($total_liab_owners)</strong></th>
                        </tr>

                    </tbody>
                </table>
                
                <div class="row no-print" style="margin-top: 20px;">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-primary pull-right" aria-label="Print Report" onclick="window.print();">
                            <i class="fa fa-print"></i> @lang('messages.print')
                        </button>
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

            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('start_date', start);
            urlParams.set('end_date', end);
            window.location.search = urlParams;
        }
    });

</script>

@stop