@extends('layouts.app')

@section('title', __('accounting::lang.profit_loss'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-text-black">@lang( 'accounting::lang.profit_loss' )</h1>
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
                <h2 class="box-title">@lang( 'accounting::lang.profit_loss')</h2>
                <p>{{@format_date($start_date)}} ~ {{@format_date($end_date)}}</p>
            </div>

            <div class="box-body">

                @php
                    $total_income = 0;
                    $total_cos = 0;
                    $total_opex = 0;
                    $total_other = 0;
                @endphp

                <table class="table table-bordered table-striped">
                    <tbody>
                        <!-- 1. PENDAPATAN -->
                        <tr class="info">
                            <th colspan="2"><strong>1. PENDAPATAN</strong></th>
                        </tr>
                        @foreach($incomes as $income)
                            @if($income->balance != 0)
                                @php $total_income += $income->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$income->name}}</td>
                                    <td class="text-right">@format_currency($income->balance)</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr style="background-color: #f9f9f9;">
                            <th><strong>TOTAL PENDAPATAN</strong></th>
                            <th class="text-right"><strong>@format_currency($total_income)</strong></th>
                        </tr>

                        <!-- 2. HARGA POKOK PENJUALAN (HPP) -->
                        <tr class="info">
                            <th colspan="2"><strong>2. HARGA POKOK PENJUALAN (HPP)</strong></th>
                        </tr>
                        @foreach($cost_of_sales as $cos)
                            @if($cos->balance != 0)
                                @php $total_cos += $cos->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$cos->name}}</td>
                                    <td class="text-right">@format_currency($cos->balance)</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr style="background-color: #f9f9f9;">
                            <th><strong>TOTAL HARGA POKOK PENJUALAN (HPP)</strong></th>
                            <th class="text-right"><strong>@format_currency($total_cos)</strong></th>
                        </tr>

                        <!-- LABA KOTOR -->
                        @php $gross_profit = $total_income - $total_cos; @endphp
                        <tr class="success" style="font-size: 1.1em;">
                            <th><strong>LABA KOTOR</strong></th>
                            <th class="text-right"><strong>@format_currency($gross_profit)</strong></th>
                        </tr>

                        <!-- 3. BEBAN OPERASIONAL -->
                        <tr class="info">
                            <th colspan="2"><strong>3. BEBAN OPERASIONAL</strong></th>
                        </tr>
                        @foreach($operating_expenses as $opex)
                            @if($opex->balance != 0)
                                @php $total_opex += $opex->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$opex->name}}</td>
                                    <td class="text-right">@format_currency($opex->balance)</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr style="background-color: #f9f9f9;">
                            <th><strong>@lang('accounting::lang.total_operating_expenses')</strong></th>
                            <th class="text-right"><strong>@format_currency($total_opex)</strong></th>
                        </tr>

                        <!-- LABA OPERASIONAL -->
                        @php $operating_profit = $gross_profit - $total_opex; @endphp
                        <tr class="success" style="font-size: 1.1em;">
                            <th><strong>LABA OPERASIONAL</strong></th>
                            <th class="text-right"><strong>@format_currency($operating_profit)</strong></th>
                        </tr>

                        <!-- 4. PENDAPATAN & BEBAN NON-OPERASIONAL -->
                        <tr class="info">
                            <th colspan="2"><strong>4. @lang('accounting::lang.other_income_expense')</strong></th>
                        </tr>
                        @foreach($other_incomes as $other)
                            @if($other->balance != 0)
                                @php $total_other += $other->balance @endphp
                                <tr>
                                    <td style="padding-left: 30px;">{{$other->name}}</td>
                                    <td class="text-right">@format_currency($other->balance)</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr style="background-color: #f9f9f9;">
                            <th><strong>TOTAL PENDAPATAN & BEBAN LAIN-LAIN (NETO)</strong></th>
                            <th class="text-right"><strong>@format_currency($total_other)</strong></th>
                        </tr>

                        <!-- LABA BERSIH TAHUN BERJALAN -->
                        @php $net_profit = $operating_profit + $total_other; @endphp
                        <tr class="warning" style="font-size: 1.2em; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                            <th><strong>@lang('accounting::lang.net_profit')</strong></th>
                            <th class="text-right"><strong>@format_currency($net_profit)</strong></th>
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