@extends('layouts.app')
@section('title', __( 'account.balance_sheet' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'account.balance_sheet')
    </h1>
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
            <h2 class="box-title tw-text-xl tw-font-bold">{{session()->get('business.name')}}</h2><br>
            <h3 class="box-title tw-text-lg">@lang( 'account.balance_sheet')</h3><br>
            <h4 class="box-title">@lang('lang_v1.as_of') <span id="hidden_date">{{@format_date('now')}}</span></h4>
        </div>
        <div class="box-body">
            <style>
                .table-balance-sheet {
                    border: 2px solid #000 !important;
                }
                .table-balance-sheet th, .table-balance-sheet td {
                    border: 1px solid #000 !important;
                    padding: 8px !important;
                }
                .table-balance-sheet .header-side {
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 1.2em;
                    background-color: #f9f9f9;
                }
                .table-balance-sheet .section-title {
                    font-weight: bold;
                    text-align: center;
                    background-color: #eeeeee;
                }
                .table-balance-sheet .total-row {
                    font-weight: bold;
                }
                .table-balance-sheet .grand-total-row {
                    font-weight: bold;
                    background-color: #e0e0e0;
                    font-size: 1.1em;
                }
                .v-align-top {
                    vertical-align: top !important;
                }
                .table-inner {
                    margin-bottom: 0;
                    width: 100%;
                }
                .table-inner td, .table-inner th {
                    border: none !important;
                    padding: 4px 8px !important;
                }
                .border-top-bold {
                    border-top: 2px solid #000 !important;
                }
            </style>
            <div class="table-responsive">
            <table class="table table-balance-sheet">
                <thead>
                    <tr>
                        <th class="header-side" width="50%">@lang( 'account.assets')</th>
                        <th class="header-side" width="50%">@lang( 'account.liability_equity')</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="v-align-top p-0">
                            <table class="table-inner">
                                <tr>
                                    <th colspan="2" class="section-title">@lang('account.current_assets')</th>
                                </tr>
                                <tr>
                                    <td>- @lang('account.customer_due')</td>
                                    <td class="text-right">
                                        <span class="remote-data" id="customer_due">
                                            <i class="fas fa-sync fa-spin fa-fw"></i>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>- @lang('report.closing_stock')</td>
                                    <td class="text-right">
                                        <span class="remote-data" id="closing_stock">
                                            <i class="fas fa-sync fa-spin fa-fw"></i>
                                        </span>
                                    </td>
                                </tr>
                                <tbody id="current_assets_accounts"></tbody>
                                <tr class="total-row">
                                    <td class="border-top-bold">@lang('account.total_current_assets')</td>
                                    <td class="text-right border-top-bold" id="total_current_assets"></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="section-title">@lang('account.fixed_assets')</th>
                                </tr>
                                <tbody id="fixed_assets_accounts"></tbody>
                                <tr class="total-row">
                                    <td class="border-top-bold">@lang('account.total_fixed_assets')</td>
                                    <td class="text-right border-top-bold" id="total_fixed_assets"></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="section-title">@lang('account.other_assets')</th>
                                </tr>
                                <tbody id="other_assets_accounts"></tbody>
                                <tr class="total-row">
                                    <td class="border-top-bold">@lang('account.total_other_assets')</td>
                                    <td class="text-right border-top-bold" id="total_other_assets"></td>
                                </tr>
                            </table>
                        </td>
                        <td class="v-align-top p-0">
                            <table class="table-inner">
                                <tr>
                                    <th colspan="2" class="section-title">@lang('account.current_liabilities')</th>
                                </tr>
                                <tr>
                                    <td>- @lang('account.supplier_due')</td>
                                    <td class="text-right">
                                        <span class="remote-data" id="supplier_due">
                                            <i class="fas fa-sync fa-spin fa-fw"></i>
                                        </span>
                                    </td>
                                </tr>
                                <tbody id="current_liabilities_accounts"></tbody>
                                <tr class="total-row">
                                    <td class="border-top-bold">@lang('account.total_current_liabilities')</td>
                                    <td class="text-right border-top-bold" id="total_current_liabilities"></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="section-title">@lang('account.long_term_liabilities')</th>
                                </tr>
                                <tbody id="long_term_liabilities_accounts"></tbody>
                                <tr class="total-row">
                                    <td class="border-top-bold">@lang('account.total_long_term_liabilities')</td>
                                    <td class="text-right border-top-bold" id="total_long_term_liabilities"></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="section-title">@lang('account.equity')</th>
                                </tr>
                                <tbody id="equity_accounts"></tbody>
                                <tr>
                                    <td>- @lang('account.retained_earnings')</td>
                                    <td class="text-right">
                                        <span class="remote-data" id="retained_earnings">
                                            <i class="fas fa-sync fa-spin fa-fw"></i>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td class="border-top-bold">@lang('account.total_equity')</td>
                                    <td class="text-right border-top-bold" id="total_equity"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="grand-total-row">
                        <th>
                            <div class="pull-left">@lang('account.total_assets')</div>
                            <div class="pull-right" id="total_assets"></div>
                        </th>
                        <th>
                            <div class="pull-left">@lang('account.total_liability_equity')</div>
                            <div class="pull-right" id="total_liability_equity"></div>
                        </th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
        <div class="box-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print pull-right"onclick="window.print()">
          <i class="fa fa-print"></i> @lang('messages.print')</button>
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
            $('#hidden_date').text($(this).val());
        });
        $('#bal_sheet_location_id').change( function() {
            update_balance_sheet();
        });
    });

    function update_balance_sheet(){
        var loader = '<i class="fas fa-sync fa-spin fa-fw"></i>';
        $('span.remote-data').each( function() {
            $(this).html(loader);
        });

        $('#current_assets_accounts, #fixed_assets_accounts, #other_assets_accounts, #current_liabilities_accounts, #long_term_liabilities_accounts, #equity_accounts').html('');

        var end_date = $('input#end_date').val();
        var location_id = $('#bal_sheet_location_id').val()
        $.ajax({
            url: "{{action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet'])}}?end_date=" + end_date + '&location_id=' + location_id, 
            dataType: "json",
            success: function(result){
                // Assets
                $('span#customer_due').text(__currency_trans_from_en(result.customer_due, true));
                __write_number($('input#hidden_customer_due'), result.customer_due);

                $('span#closing_stock').text(__currency_trans_from_en(result.closing_stock, true));
                __write_number($('input#hidden_closing_stock'), result.closing_stock);

                var total_current_assets = (parseFloat(result.customer_due) || 0) + (parseFloat(result.closing_stock) || 0);
                result.assets.current_assets.forEach(function(account){
                    var bal = parseFloat(account.balance) || 0;
                    $('#current_assets_accounts').append('<tr><td>- ' + account.account_name + '</td><td class="text-right">' + __currency_trans_from_en(bal, true) + '</td></tr>');
                    total_current_assets += bal;
                });
                $('#total_current_assets').text(__currency_trans_from_en(total_current_assets, true));

                var total_fixed_assets = 0;
                result.assets.fixed_assets.forEach(function(account){
                    var bal = parseFloat(account.balance) || 0;
                    $('#fixed_assets_accounts').append('<tr><td>- ' + account.account_name + '</td><td class="text-right">' + __currency_trans_from_en(bal, true) + '</td></tr>');
                    total_fixed_assets += bal;
                });
                $('#total_fixed_assets').text(__currency_trans_from_en(total_fixed_assets, true));

                var total_other_assets = 0;
                result.assets.other_assets.forEach(function(account){
                    var bal = parseFloat(account.balance) || 0;
                    $('#other_assets_accounts').append('<tr><td>- ' + account.account_name + '</td><td class="text-right">' + __currency_trans_from_en(bal, true) + '</td></tr>');
                    total_other_assets += bal;
                });
                $('#total_other_assets').text(__currency_trans_from_en(total_other_assets, true));

                var total_assets = total_current_assets + total_fixed_assets + total_other_assets;
                $('#total_assets').text(__currency_trans_from_en(total_assets, true));

                // Liabilities
                $('span#supplier_due').text(__currency_trans_from_en(result.supplier_due, true));
                __write_number($('input#hidden_supplier_due'), result.supplier_due);

                var total_current_liabilities = parseFloat(result.supplier_due) || 0;
                result.liabilities.current_liabilities.forEach(function(account){
                    var bal = parseFloat(account.balance) || 0;
                    $('#current_liabilities_accounts').append('<tr><td>- ' + account.account_name + '</td><td class="text-right">' + __currency_trans_from_en(bal, true) + '</td></tr>');
                    total_current_liabilities += bal;
                });
                $('#total_current_liabilities').text(__currency_trans_from_en(total_current_liabilities, true));

                var total_long_term_liabilities = 0;
                result.liabilities.long_term_liabilities.forEach(function(account){
                    var bal = parseFloat(account.balance) || 0;
                    $('#long_term_liabilities_accounts').append('<tr><td>- ' + account.account_name + '</td><td class="text-right">' + __currency_trans_from_en(bal, true) + '</td></tr>');
                    total_long_term_liabilities += bal;
                });
                $('#total_long_term_liabilities').text(__currency_trans_from_en(total_long_term_liabilities, true));

                // Equity
                var retained_earnings = parseFloat(result.retained_earnings) || 0;
                var total_equity = retained_earnings;
                $('span#retained_earnings').text(__currency_trans_from_en(retained_earnings, true));

                result.equity.forEach(function(account){
                    var bal = parseFloat(account.balance) || 0;
                    $('#equity_accounts').append('<tr><td>- ' + account.account_name + '</td><td class="text-right">' + __currency_trans_from_en(bal, true) + '</td></tr>');
                    total_equity += bal;
                });
                $('#total_equity').text(__currency_trans_from_en(total_equity, true));

                var total_liability_equity = total_current_liabilities + total_long_term_liabilities + total_equity;
                $('#total_liability_equity').text(__currency_trans_from_en(total_liability_equity, true));
            }
        });
    }
</script>

@endsection