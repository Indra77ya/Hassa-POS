@extends('layouts.app')

@section('title', __('accounting::lang.balance_sheet'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'accounting::lang.balance_sheet' )</h1>
</section>

<section class="content">

    <div class="col-md-12 no-print">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, request()->input('location_id'), ['class' => 'form-control select2', 'id' => 'location_id', 'placeholder' => __('lang_v1.all')]); !!}
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

    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border text-center">
                <h2 class="box-title" style="font-weight: bold; font-size: 1.5em;">@lang( 'accounting::lang.balance_sheet')</h2>
                <p style="font-size: 1.1em; color: #555;">{{@format_date($start_date)}} ~ {{@format_date($end_date)}}</p>
            </div>

            <div class="box-body">
                
                @php
                    $sum_current_assets = 0;
                    $sum_non_current_assets = 0;
                    $sum_current_liabilities = 0;
                    $sum_non_current_liabilities = 0;
                    $total_equities = 0;
                @endphp

                <div class="row">
                    <!-- SISI KIRI: AKTIVA (ASET) -->
                    <div class="col-md-6" style="border-right: 2px solid #ddd; min-height: 400px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="info">
                                    <th colspan="2" class="text-center" style="font-size: 1.15em;"><strong>AKTIVA (ASET)</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ASET LANCAR -->
                                <tr style="background-color: #f9f9f9;">
                                    <th colspan="2" style="padding-left: 10px; color: #333;"><strong>ASET LANCAR</strong></th>
                                </tr>
                                @foreach($current_assets as $asset)
                                    @if($asset->balance != 0)
                                        @php $sum_current_assets += $asset->balance @endphp
                                        <tr>
                                            <td style="padding-left: 20px;">{{$asset->name}}</td>
                                            <td class="text-right" style="width: 35%;">@format_currency($asset->balance)</td>
                                        </tr>
                                    @endif
                                @endforeach

                                @if($sum_current_assets == 0)
                                    <tr>
                                        <td colspan="2" class="text-center text-muted" style="font-style: italic;">Tidak ada aset lancar tercatat</td>
                                    </tr>
                                @endif
                                <tr style="background-color: #fafafa; border-top: 1px solid #ddd;">
                                    <th style="padding-left: 15px;"><strong>Jumlah Aset Lancar</strong></th>
                                    <th class="text-right"><strong>@format_currency($sum_current_assets)</strong></th>
                                </tr>

                                <!-- ASET TIDAK LANCAR -->
                                <tr style="background-color: #f9f9f9;">
                                    <th colspan="2" style="padding-left: 10px; color: #333; border-top: 2px solid #ddd;"><strong>ASET TIDAK LANCAR</strong></th>
                                </tr>
                                @foreach($non_current_assets as $asset)
                                    @if($asset->balance != 0)
                                        @php $sum_non_current_assets += $asset->balance @endphp
                                        <tr>
                                            <td style="padding-left: 20px;">{{$asset->name}}</td>
                                            <td class="text-right" style="width: 35%;">@format_currency($asset->balance)</td>
                                        </tr>
                                    @endif
                                @endforeach

                                @if($sum_non_current_assets == 0)
                                    <tr>
                                        <td colspan="2" class="text-center text-muted" style="font-style: italic;">Tidak ada aset tidak lancar tercatat</td>
                                    </tr>
                                @endif
                                <tr style="background-color: #fafafa; border-top: 1px solid #ddd;">
                                    <th style="padding-left: 15px;"><strong>Jumlah Aset Tidak Lancar</strong></th>
                                    <th class="text-right"><strong>@format_currency($sum_non_current_assets)</strong></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PASIVA (LIABILITAS & EKUITAS) -->
                    <div class="col-md-6" style="min-height: 400px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="info">
                                    <th colspan="2" class="text-center" style="font-size: 1.15em;"><strong>PASIVA (LIABILITAS & EKUITAS)</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- KEWAJIBAN / LIABILITAS -->
                                <tr style="background-color: #f9f9f9;">
                                    <th colspan="2" style="padding-left: 10px; color: #333;"><strong>LIABILITAS</strong></th>
                                </tr>

                                <!-- LIABILITAS JANGKA PENDEK -->
                                <tr style="background-color: #fafafa;">
                                    <th colspan="2" style="padding-left: 15px; font-weight: normal; color: #555;"><em>LIABILITAS JANGKA PENDEK</em></th>
                                </tr>
                                @foreach($current_liabilities as $liability)
                                    @if($liability->balance != 0)
                                        @php $sum_current_liabilities += $liability->balance @endphp
                                        <tr>
                                            <td style="padding-left: 25px;">{{$liability->name}}</td>
                                            <td class="text-right" style="width: 35%;">@format_currency($liability->balance)</td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if($sum_current_liabilities == 0)
                                    <tr>
                                        <td colspan="2" class="text-center text-muted" style="font-style: italic; padding-left: 25px;">Tidak ada liabilitas jangka pendek tercatat</td>
                                    </tr>
                                @endif
                                <tr style="border-top: 1px solid #ddd;">
                                    <th style="padding-left: 20px; font-weight: normal; color: #666;">Jumlah Liabilitas Jangka Pendek</th>
                                    <th class="text-right" style="font-weight: normal; color: #666;">@format_currency($sum_current_liabilities)</th>
                                </tr>

                                <!-- LIABILITAS JANGKA PANJANG -->
                                <tr style="background-color: #fafafa; border-top: 1.5px solid #eee;">
                                    <th colspan="2" style="padding-left: 15px; font-weight: normal; color: #555;"><em>LIABILITAS JANGKA PANJANG</em></th>
                                </tr>
                                @foreach($non_current_liabilities as $liability)
                                    @if($liability->balance != 0)
                                        @php $sum_non_current_liabilities += $liability->balance @endphp
                                        <tr>
                                            <td style="padding-left: 25px;">{{$liability->name}}</td>
                                            <td class="text-right" style="width: 35%;">@format_currency($liability->balance)</td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if($sum_non_current_liabilities == 0)
                                    <tr>
                                        <td colspan="2" class="text-center text-muted" style="font-style: italic; padding-left: 25px;">Tidak ada liabilitas jangka panjang tercatat</td>
                                    </tr>
                                @endif
                                <tr style="border-top: 1px solid #ddd;">
                                    <th style="padding-left: 20px; font-weight: normal; color: #666;">Jumlah Liabilitas Jangka Panjang</th>
                                    <th class="text-right" style="font-weight: normal; color: #666;">@format_currency($sum_non_current_liabilities)</th>
                                </tr>

                                @php $total_liabilities = $sum_current_liabilities + $sum_non_current_liabilities; @endphp
                                <tr style="background-color: #f5f5f5; border-top: 2px solid #ddd;">
                                    <th style="padding-left: 15px;"><strong>Jumlah Liabilitas</strong></th>
                                    <th class="text-right"><strong>@format_currency($total_liabilities)</strong></th>
                                </tr>

                                <!-- EKUITAS / MODAL -->
                                <tr style="background-color: #f9f9f9;">
                                    <th colspan="2" style="padding-left: 10px; color: #333; border-top: 2px solid #ddd;"><strong>EKUITAS</strong></th>
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
                                    <td style="padding-left: 20px;">Laba Tahun Ini</td>
                                    <td class="text-right">@format_currency($current_period_net_profit)</td>
                                </tr>

                                <tr style="background-color: #fafafa; border-top: 1px solid #ddd;">
                                    <th style="padding-left: 15px;"><strong>Jumlah Ekuitas</strong></th>
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
                                @php $total_assets = $sum_current_assets + $sum_non_current_assets; @endphp
                                <tr class="success" style="font-size: 1.25em;">
                                    <th><strong>JUMLAH ASET (JUMLAH AKTIVA)</strong></th>
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
                                    <th><strong>JUMLAH LIABILITAS DAN EKUITAS</strong></th>
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