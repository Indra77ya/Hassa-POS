@extends('layouts.app')
@section('title', __( 'account.balance_sheet' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'account.balance_sheet')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-sm-12">
            @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('bal_sheet_location_id',  __('purchase.business_location') . ':') !!}
                    {!! Form::select('bal_sheet_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <label for="end_date">@lang('messages.filter_by_date'):</label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <input type="text" id="end_date" value="{{@format_date('now')}}" class="form-control" readonly>
                </div>
            </div>
            @endcomponent
        </div>
    </div>
    <br>
    <div class="box box-solid">
        <div class="box-header print_section text-center">
            <h2 class="box-title" style="font-weight: bold; font-size: 1.5em; margin-bottom: 5px;">@lang( 'account.balance_sheet')</h2>
            <p id="hidden_date_parent" style="font-size: 1.1em; color: #555;">
                <span id="hidden_date"></span>
            </p>
        </div>
        <div class="box-body">

            <div class="row">
                <!-- SISI KIRI: AKTIVA (ASET) -->
                <div class="col-md-6" style="border-right: 2px solid #ddd; min-height: 400px;">
                    <table class="table table-bordered table-striped" id="assets_table">
                        <thead>
                            <tr class="info">
                                <th colspan="2" class="text-center" style="font-size: 1.15em;"><strong>AKTIVA (ASET)</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- PASIVA (LIABILITAS & EKUITAS) -->
                <div class="col-md-6" style="min-height: 400px;">
                    <table class="table table-bordered table-striped" id="pasiva_table">
                        <thead>
                            <tr class="info">
                                <th colspan="2" class="text-center" style="font-size: 1.15em;"><strong>PASIVA (LIABILITAS & EKUITAS)</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
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
                                <th class="text-right" style="width: 35%;" id="total_assets"><strong>Rp 0.00</strong></th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr class="success" style="font-size: 1.25em;">
                                <th><strong>TOTAL PASIVA (LIABILITAS & EKUITAS)</strong></th>
                                <th class="text-right" style="width: 35%;" id="total_pasiva"><strong>Rp 0.00</strong></th>
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

</section>
<!-- /.content -->
@stop
@section('javascript')

<script type="text/javascript">
    $(document).ready( function(){
        //Date picker
        $('#end_date').datepicker({
            autoclose: true,
            format: datepicker_date_format
        });
        update_balance_sheet();

        $('#end_date').change( function() {
            update_balance_sheet();
        });
        $('#bal_sheet_location_id').change( function() {
            update_balance_sheet();
        });
    });

    function update_balance_sheet(){
        var loader = '<tr><td colspan="2" class="text-center"><i class="fas fa-sync fa-spin fa-fw"></i> Loading...</td></tr>';
        $('#assets_table tbody').html(loader);
        $('#pasiva_table tbody').html(loader);

        var end_date = $('input#end_date').val();
        var location_id = $('#bal_sheet_location_id').val();
        $.ajax({
            url: "{{action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet'])}}?end_date=" + end_date + '&location_id=' + location_id, 
            dataType: "json",
            success: function(result){
                var total_assets = 0;
                var total_liabilities = 0;
                var total_equities = 0;

                // Format hidden dates (start_date ~ end_date) in the sub-header
                var start_fmt = moment(result.start_date).format(moment_date_format);
                var end_fmt = moment(result.end_date).format(moment_date_format);
                $('#hidden_date').text(start_fmt + ' ~ ' + end_fmt);

                // 1. Render Left Side: Assets
                var assets_tbody = $('#assets_table tbody');
                assets_tbody.empty();

                if (result.assets && result.assets.length > 0) {
                    result.assets.forEach(function(asset) {
                        var balance = parseFloat(asset.balance) || 0;
                        if (balance != 0) {
                            total_assets += balance;
                            assets_tbody.append(
                                '<tr>' +
                                '    <td style="padding-left: 20px;">' + asset.name + '</td>' +
                                '    <td class="text-right" style="width: 35%;">' + __currency_trans_from_en(balance, true) + '</td>' +
                                '</tr>'
                            );
                        }
                    });
                }

                if (total_assets == 0) {
                    assets_tbody.append(
                        '<tr>' +
                        '    <td colspan="2" class="text-center text-muted"><em>Tidak ada aset tercatat</em></td>' +
                        '</tr>'
                    );
                }

                // 2. Render Right Side: Pasiva
                var pasiva_tbody = $('#pasiva_table tbody');
                pasiva_tbody.empty();

                // 2a. LIABILITAS (KEWAJIBAN / HUTANG) header
                pasiva_tbody.append(
                    '<tr style="background-color: #f5f5f5;">' +
                    '    <th colspan="2" style="padding-left: 10px; color: #444;"><strong>LIABILITAS (KEWAJIBAN / HUTANG)</strong></th>' +
                    '</tr>'
                );

                var liabilities_count = 0;
                if (result.liabilities && result.liabilities.length > 0) {
                    result.liabilities.forEach(function(liability) {
                        var balance = parseFloat(liability.balance) || 0;
                        if (balance != 0) {
                            total_liabilities += balance;
                            liabilities_count++;
                            pasiva_tbody.append(
                                '<tr>' +
                                '    <td style="padding-left: 20px;">' + liability.name + '</td>' +
                                '    <td class="text-right" style="width: 35%;">' + __currency_trans_from_en(balance, true) + '</td>' +
                                '</tr>'
                            );
                        }
                    });
                }

                if (liabilities_count == 0) {
                    pasiva_tbody.append(
                        '<tr>' +
                        '    <td colspan="2" class="text-center text-muted"><em>Tidak ada liabilitas tercatat</em></td>' +
                        '</tr>'
                    );
                }

                pasiva_tbody.append(
                    '<tr style="background-color: #fafafa; border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 15px;"><strong>TOTAL LIABILITAS</strong></th>' +
                    '    <th class="text-right"><strong>' + __currency_trans_from_en(total_liabilities, true) + '</strong></th>' +
                    '</tr>'
                );

                // 2b. EKUITAS (MODAL) header
                pasiva_tbody.append(
                    '<tr style="background-color: #f5f5f5;">' +
                    '    <th colspan="2" style="padding-left: 10px; color: #444; border-top: 2px solid #ddd;"><strong>EKUITAS (MODAL)</strong></th>' +
                    '</tr>'
                );

                var equities_count = 0;
                if (result.equities && result.equities.length > 0) {
                    result.equities.forEach(function(equity) {
                        var balance = parseFloat(equity.balance) || 0;
                        if (balance != 0) {
                            total_equities += balance;
                            equities_count++;
                            pasiva_tbody.append(
                                '<tr>' +
                                '    <td style="padding-left: 20px;">' + equity.name + '</td>' +
                                '    <td class="text-right">' + __currency_trans_from_en(balance, true) + '</td>' +
                                '</tr>'
                            );
                        }
                    });
                }

                // Laba Bersih Tahun Berjalan
                var net_profit = parseFloat(result.current_period_net_profit) || 0;
                total_equities += net_profit;

                pasiva_tbody.append(
                    '<tr>' +
                    '    <td style="padding-left: 20px;"><strong>Laba Bersih Tahun Berjalan</strong></td>' +
                    '    <td class="text-right">' + __currency_trans_from_en(net_profit, true) + '</td>' +
                    '</tr>'
                );

                pasiva_tbody.append(
                    '<tr style="background-color: #fafafa; border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 15px;"><strong>TOTAL EKUITAS</strong></th>' +
                    '    <th class="text-right"><strong>' + __currency_trans_from_en(total_equities, true) + '</strong></th>' +
                    '</tr>'
                );

                // 3. Render Bottom Grand Totals
                var total_pasiva = total_liabilities + total_equities;
                $('#total_assets').html('<strong>' + __currency_trans_from_en(total_assets, true) + '</strong>');
                $('#total_pasiva').html('<strong>' + __currency_trans_from_en(total_pasiva, true) + '</strong>');
            }
        });
    }
</script>

@endsection
