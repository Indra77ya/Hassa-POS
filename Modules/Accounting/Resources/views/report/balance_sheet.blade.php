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

    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border text-center">
                <h2 class="box-title" style="font-weight: bold; font-size: 1.5em;">@lang( 'accounting::lang.balance_sheet')</h2>
                <p style="font-size: 1.1em; color: #555;">{{@format_date($start_date)}} ~ {{@format_date($end_date)}}</p>
            </div>

            <div class="box-body">
                
                @php
                    $total_assets = 0;
                    $total_liabilities = 0;
                    $total_equities = 0;
                @endphp

                <div class="row">
                    <!-- SISI KIRI: AKTIVA (ASET) -->
                    <div class="col-md-6" style="border-right: 2px solid #ddd; min-height: 400px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="info">
                                    <th colspan="2" class="text-center" style="font-size: 1.15em;"><strong>SISI KIRI: AKTIVA (ASET)</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                    @if($asset->balance != 0)
                                        @php $total_assets += $asset->balance @endphp
                                        <tr>
                                            <td style="padding-left: 20px;">{{$asset->name}}</td>
                                            <td class="text-right" style="width: 35%;">@format_currency($asset->balance)</td>
                                        </tr>
                                    @endif
                                    @endforeach

                                    @if($total_assets == 0)
                                        <tr>
                                            <td colspan="2" class="text-center text-muted"><em>Tidak ada aset tercatat</em></td>
                                        </tr>
                                    @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- SISI KANAN: PASIVA (LIABILITAS & EKUITAS) -->
                    <div class="col-md-6" style="min-height: 400px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="info">
                                    <th colspan="2" class="text-center" style="font-size: 1.15em;"><strong>SISI KANAN: PASIVA (LIABILITAS & EKUITAS)</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- KEWAJIBAN / LIABILITAS -->
                                <tr style="background-color: #f5f5f5;">
                                    <th colspan="2" style="padding-left: 10px; color: #444;"><strong>LIABILITAS (KEWAJIBAN / HUTANG)</strong></th>
                                </tr>
                                @foreach($liabilities as $liability)
                                    @if($liability->balance != 0)
                                        @php $total_liabilities += $liability->balance @endphp
                                        <tr>
                                            <td style="padding-left: 20px;">{{$liability->name}}</td>
                                            <td class="text-right" style="width: 35%;">@format_currency($liability->balance)</td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if($total_liabilities == 0)
                                    <tr>
                                        <td colspan="2" class="text-center text-muted"><em>Tidak ada liabilitas tercatat</em></td>
                                    </tr>
                                @endif
                                <tr style="background-color: #fafafa; border-top: 1px solid #ddd;">
                                    <th style="padding-left: 15px;"><strong>TOTAL LIABILITAS</strong></th>
                                    <th class="text-right"><strong>@format_currency($total_liabilities)</strong></th>
                                </tr>

                                <!-- EKUITAS / MODAL -->
                                <tr style="background-color: #f5f5f5;">
                                    <th colspan="2" style="padding-left: 10px; color: #444; border-top: 2px solid #ddd;"><strong>EKUITAS (MODAL)</strong></th>
                                </tr>
                                @foreach($equities as $equity)
                                    @if($equity->balance != 0)
                                        @php $total_equities += $equity->balance @endphp
                                        <tr>
                                            <td style="padding-left: 20px;">{{$equity->name}}</td>
                                            <td class="text-right">@format_currency($equity->balance)</td>
                                        </tr>
                                    @endif
                                @endforeach

                                <!-- Laba Bersih Tahun Berjalan -->
                                @php $total_equities += $current_period_net_profit; @endphp
                                <tr>
                                    <td style="padding-left: 20px;"><strong>Laba Bersih Tahun Berjalan</strong></td>
                                    <td class="text-right">@format_currency($current_period_net_profit)</td>
                                </tr>

                                <tr style="background-color: #fafafa; border-top: 1px solid #ddd;">
                                    <th style="padding-left: 15px;"><strong>TOTAL EKUITAS</strong></th>
                                    <th class="text-right"><strong>@format_currency($total_equities)</strong></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- GRAND TOTAL BAR DI BAGIAN BAWAH YANG SEJAJAR DAN SEIMBANG -->
                <div class="row" style="margin-top: 20px; border-top: 3px double #aaa; padding-top: 15px;">
                    <div class="col-md-6" style="border-right: 2px solid #ddd;">
                        <table class="table table-bordered">
                            <tbody>
                                <tr class="success" style="font-size: 1.25em;">
                                    <th><strong>TOTAL AKTIVA (ASET)</strong></th>
                                    <th class="text-right" style="width: 35%;"><strong>@format_currency($total_assets)</strong></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tbody>
                                @php $total_pasiva = $total_liabilities + $total_equities; @endphp
                                <tr class="success" style="font-size: 1.25em;">
                                    <th><strong>TOTAL PASIVA (LIABILITAS & EKUITAS)</strong></th>
                                    <th class="text-right" style="width: 35%;"><strong>@format_currency($total_pasiva)</strong></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
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