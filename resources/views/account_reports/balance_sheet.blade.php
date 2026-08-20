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
                                <th><strong>JUMLAH ASET (JUMLAH AKTIVA)</strong></th>
                                <th class="text-right" style="width: 35%;" id="total_assets"><strong>Rp 0.00</strong></th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr class="success" style="font-size: 1.25em;">
                                <th><strong>JUMLAH LIABILITAS DAN EKUITAS</strong></th>
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

                // Group Assets: Current vs Non-Current
                var current_assets = [];
                var non_current_assets = [];
                var sum_current_assets = 0;
                var sum_non_current_assets = 0;

                if (result.assets && result.assets.length > 0) {
                    result.assets.forEach(function(asset) {
                        var balance = parseFloat(asset.balance) || 0;
                        if (balance != 0) {
                            var sub_type = asset.sub_type;
                            if (['accounts_receivable', 'current_assets', 'cash_and_cash_equivalents', 'piutang_usaha', 'persediaan', 'kas_dan_bank', 'aktiva_lancar_lainnya'].indexOf(sub_type) !== -1) {
                                current_assets.push(asset);
                                sum_current_assets += balance;
                            } else if (['fixed_assets', 'non_current_assets', 'aktiva_tetap', 'aktiva_lainnya', 'akumulasi_penyusutan'].indexOf(sub_type) !== -1) {
                                non_current_assets.push(asset);
                                sum_non_current_assets += balance;
                            } else {
                                non_current_assets.push(asset);
                                sum_non_current_assets += balance;
                            }
                        }
                    });
                }

                // 1. Render Left Side: Assets
                var assets_tbody = $('#assets_table tbody');
                assets_tbody.empty();

                // Group Aset Lancar
                assets_tbody.append(
                    '<tr style="background-color: #f9f9f9;">' +
                    '    <th colspan="2" style="padding-left: 10px; color: #333;"><strong>ASET LANCAR</strong></th>' +
                    '</tr>'
                );
                if (current_assets.length > 0) {
                    current_assets.forEach(function(asset) {
                        assets_tbody.append(
                            '<tr>' +
                            '    <td style="padding-left: 20px;">' + asset.name + '</td>' +
                            '    <td class="text-right" style="width: 35%;">' + __currency_trans_from_en(asset.balance, true) + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    assets_tbody.append(
                        '<tr>' +
                        '    <td colspan="2" class="text-center text-muted" style="font-style: italic;">Tidak ada aset lancar tercatat</td>' +
                        '</tr>'
                    );
                }
                assets_tbody.append(
                    '<tr style="background-color: #fafafa; border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 15px;"><strong>Jumlah Aset Lancar</strong></th>' +
                    '    <th class="text-right"><strong>' + __currency_trans_from_en(sum_current_assets, true) + '</strong></th>' +
                    '</tr>'
                );

                // Group Aset Tidak Lancar (Sort Akumulasi Penyusutan to the bottom)
                non_current_assets.sort(function(a, b) {
                    var isAccA = a.sub_type === 'akumulasi_penyusutan' || a.name.toLowerCase().indexOf('akumulasi penyusutan') !== -1 ? 1 : 0;
                    var isAccB = b.sub_type === 'akumulasi_penyusutan' || b.name.toLowerCase().indexOf('akumulasi penyusutan') !== -1 ? 1 : 0;
                    return isAccA - isAccB;
                });

                assets_tbody.append(
                    '<tr style="background-color: #f9f9f9;">' +
                    '    <th colspan="2" style="padding-left: 10px; color: #333; border-top: 2px solid #ddd;"><strong>ASET TIDAK LANCAR</strong></th>' +
                    '</tr>'
                );
                if (non_current_assets.length > 0) {
                    non_current_assets.forEach(function(asset) {
                        assets_tbody.append(
                            '<tr>' +
                            '    <td style="padding-left: 20px;">' + asset.name + '</td>' +
                            '    <td class="text-right" style="width: 35%;">' + __currency_trans_from_en(asset.balance, true) + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    assets_tbody.append(
                        '<tr>' +
                        '    <td colspan="2" class="text-center text-muted" style="font-style: italic;">Tidak ada aset tidak lancar tercatat</td>' +
                        '</tr>'
                    );
                }
                assets_tbody.append(
                    '<tr style="background-color: #fafafa; border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 15px;"><strong>Jumlah Aset Tidak Lancar</strong></th>' +
                    '    <th class="text-right"><strong>' + __currency_trans_from_en(sum_non_current_assets, true) + '</strong></th>' +
                    '</tr>'
                );

                total_assets = sum_current_assets + sum_non_current_assets;

                // Group Liabilities: Current vs Non-Current
                var current_liabilities = [];
                var non_current_liabilities = [];
                var sum_current_liabilities = 0;
                var sum_non_current_liabilities = 0;

                if (result.liabilities && result.liabilities.length > 0) {
                    result.liabilities.forEach(function(liability) {
                        var balance = parseFloat(liability.balance) || 0;
                        if (balance != 0) {
                            var sub_type = liability.sub_type;
                            if (['accounts_payable', 'credit_card', 'current_liabilities'].indexOf(sub_type) !== -1) {
                                current_liabilities.push(liability);
                                sum_current_liabilities += balance;
                            } else if (sub_type === 'non_current_liabilities') {
                                non_current_liabilities.push(liability);
                                sum_non_current_liabilities += balance;
                            } else {
                                // fallback to current liabilities
                                current_liabilities.push(liability);
                                sum_current_liabilities += balance;
                            }
                        }
                    });
                }

                // 2. Render Right Side: Pasiva
                var pasiva_tbody = $('#pasiva_table tbody');
                pasiva_tbody.empty();

                // Header LIABILITAS
                pasiva_tbody.append(
                    '<tr style="background-color: #f9f9f9;">' +
                    '    <th colspan="2" style="padding-left: 10px; color: #333;"><strong>LIABILITAS</strong></th>' +
                    '</tr>'
                );

                // Subsection LIABILITAS JANGKA PENDEK
                pasiva_tbody.append(
                    '<tr style="background-color: #fafafa;">' +
                    '    <th colspan="2" style="padding-left: 15px; font-weight: normal; color: #555;"><em>LIABILITAS JANGKA PENDEK</em></th>' +
                    '</tr>'
                );
                if (current_liabilities.length > 0) {
                    current_liabilities.forEach(function(liability) {
                        pasiva_tbody.append(
                            '<tr>' +
                            '    <td style="padding-left: 25px;">' + liability.name + '</td>' +
                            '    <td class="text-right" style="width: 35%;">' + __currency_trans_from_en(liability.balance, true) + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    pasiva_tbody.append(
                        '<tr>' +
                        '    <td colspan="2" class="text-center text-muted" style="font-style: italic; padding-left: 25px;">Tidak ada liabilitas jangka pendek tercatat</td>' +
                        '</tr>'
                    );
                }
                pasiva_tbody.append(
                    '<tr style="border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 20px; font-weight: normal; color: #666;">Jumlah Liabilitas Jangka Pendek</th>' +
                    '    <th class="text-right" style="font-weight: normal; color: #666;">' + __currency_trans_from_en(sum_current_liabilities, true) + '</th>' +
                    '</tr>'
                );

                // Subsection LIABILITAS JANGKA PANJANG
                pasiva_tbody.append(
                    '<tr style="background-color: #fafafa; border-top: 1.5px solid #eee;">' +
                    '    <th colspan="2" style="padding-left: 15px; font-weight: normal; color: #555;"><em>LIABILITAS JANGKA PANJANG</em></th>' +
                    '</tr>'
                );
                if (non_current_liabilities.length > 0) {
                    non_current_liabilities.forEach(function(liability) {
                        pasiva_tbody.append(
                            '<tr>' +
                            '    <td style="padding-left: 25px;">' + liability.name + '</td>' +
                            '    <td class="text-right" style="width: 35%;">' + __currency_trans_from_en(liability.balance, true) + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    pasiva_tbody.append(
                        '<tr>' +
                        '    <td colspan="2" class="text-center text-muted" style="font-style: italic; padding-left: 25px;">Tidak ada liabilitas jangka panjang tercatat</td>' +
                        '</tr>'
                    );
                }
                pasiva_tbody.append(
                    '<tr style="border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 20px; font-weight: normal; color: #666;">Jumlah Liabilitas Jangka Panjang</th>' +
                    '    <th class="text-right" style="font-weight: normal; color: #666;">' + __currency_trans_from_en(sum_non_current_liabilities, true) + '</th>' +
                    '</tr>'
                );

                total_liabilities = sum_current_liabilities + sum_non_current_liabilities;
                pasiva_tbody.append(
                    '<tr style="background-color: #f5f5f5; border-top: 2px solid #ddd;">' +
                    '    <th style="padding-left: 15px;"><strong>Jumlah Liabilitas</strong></th>' +
                    '    <th class="text-right"><strong>' + __currency_trans_from_en(total_liabilities, true) + '</strong></th>' +
                    '</tr>'
                );

                // 2c. EKUITAS (MODAL) header
                pasiva_tbody.append(
                    '<tr style="background-color: #f9f9f9;">' +
                    '    <th colspan="2" style="padding-left: 10px; color: #333; border-top: 2px solid #ddd;"><strong>EKUITAS</strong></th>' +
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
                    '    <td style="padding-left: 20px;">Laba Tahun Ini</td>' +
                    '    <td class="text-right">' + __currency_trans_from_en(net_profit, true) + '</td>' +
                    '</tr>'
                );

                pasiva_tbody.append(
                    '<tr style="background-color: #fafafa; border-top: 1px solid #ddd;">' +
                    '    <th style="padding-left: 15px;"><strong>Jumlah Ekuitas</strong></th>' +
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
