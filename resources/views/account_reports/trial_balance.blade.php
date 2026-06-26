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
        <div class="box-header print_section">
            <h3 class="box-title">{{session()->get('business.name')}} - @lang( 'account.trial_balance') - <span id="hidden_date">{{@format_date('now')}}</span></h3>
        </div>
        <div class="box-body">
            <table class="table table-bordered table-pl-12" id="trial_balance_table">
                <thead>
                    <tr class="bg-gray">
                        <th rowspan="2" class="text-center" style="vertical-align: middle;">@lang('account.account')</th>
                        <th colspan="2" class="text-center">@lang('account.balance')</th>
                    </tr>
                    <tr class="bg-gray">
                        <th class="text-center">@lang('account.debit')</th>
                        <th class="text-center">@lang('account.credit')</th>
                    </tr>
                </thead>
                <tbody id="trial_balance_details">
                </tbody>
                <tfoot>
                    <tr class="bg-gray">
                        <th class="text-right">@lang('sale.total')</th>
                        <td class="text-center">
                            <span class="remote-data" id="total_debit">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="remote-data" id="total_credit">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
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
        update_trial_balance();

        $('#end_date').change( function() {
            update_trial_balance();
            $('#hidden_date').text($(this).val());
        });
        $('#trial_bal_location_id').change( function() {
            update_trial_balance();
        });
    });

    function update_trial_balance(){
        var loader = '<i class="fas fa-sync fa-spin fa-fw"></i>';
        $('span.remote-data').each( function() {
            $(this).html(loader);
        });

        $('#trial_balance_details').html('<tr><td colspan="3" class="text-center"><i class="fas fa-sync fa-spin fa-fw"></i></td></tr>');

        var end_date = $('input#end_date').val();
        var location_id = $('#trial_bal_location_id').val()
        $.ajax({
            url: "{{action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance'])}}?end_date=" + end_date + '&location_id=' + location_id,
            dataType: "json",
            success: function(result){
                var total_debit = 0;
                var total_credit = 0;
                var rows = '';

                // 1. Assets (Debit nature)
                // Opening Stock
                if (result.opening_stock > 0) {
                    rows += render_row("{{__('report.opening_stock')}}", result.opening_stock, 0);
                    total_debit += parseFloat(result.opening_stock);
                }

                // Customer Due (Piutang)
                if (result.customer_due > 0) {
                    rows += render_row("{{__('account.customer_due')}}", result.customer_due, 0);
                    total_debit += parseFloat(result.customer_due);
                } else if (result.customer_due < 0) {
                    rows += render_row("{{__('account.customer_due')}}", 0, Math.abs(result.customer_due));
                    total_credit += Math.abs(parseFloat(result.customer_due));
                }

                // Payment Accounts
                result.account_balances.forEach(function(account) {
                    var type = (account.type_name || '') + ' ' + (account.parent_type_name || '');
                    type = type.toLowerCase();
                    var name = account.name.toLowerCase();

                    var balance = 0;
                    var is_debit = true;

                    if (type.includes('liability') || type.includes('utang') || type.includes('kewajiban') || type.includes('pasiva') || name.includes('utang') || name.includes('kewajiban')) {
                        balance = account.total_credit - account.total_debit;
                        is_debit = false;
                    } else if (type.includes('equity') || type.includes('modal') || type.includes('ekuitas') || type.includes('capital') || name.includes('modal') || name.includes('ekuitas')) {
                        balance = account.total_credit - account.total_debit;
                        is_debit = false;
                    } else {
                        balance = account.total_debit - account.total_credit;
                        is_debit = true;
                    }

                    if (balance > 0) {
                        if (is_debit) {
                            rows += render_row(account.name, balance, 0);
                            total_debit += balance;
                        } else {
                            rows += render_row(account.name, 0, balance);
                            total_credit += balance;
                        }
                    } else if (balance < 0) {
                        if (is_debit) {
                            rows += render_row(account.name, 0, Math.abs(balance));
                            total_credit += Math.abs(balance);
                        } else {
                            rows += render_row(account.name, Math.abs(balance), 0);
                            total_debit += Math.abs(balance);
                        }
                    }
                });

                // 2. Liabilities (Credit nature)
                // Supplier Due (Utang)
                if (result.supplier_due > 0) {
                    rows += render_row("{{__('account.supplier_due')}}", 0, result.supplier_due);
                    total_credit += parseFloat(result.supplier_due);
                } else if (result.supplier_due < 0) {
                    rows += render_row("{{__('account.supplier_due')}}", Math.abs(result.supplier_due), 0);
                    total_debit += Math.abs(parseFloat(result.supplier_due));
                }

                // 3. Income (Credit nature)
                if (result.total_sell > 0) {
                    rows += render_row("{{__('report.total_sell')}}", 0, result.total_sell);
                    total_credit += parseFloat(result.total_sell);
                }
                if (result.total_purchase_return > 0) {
                    rows += render_row("{{__('lang_v1.total_purchase_return')}}", 0, result.total_purchase_return);
                    total_credit += parseFloat(result.total_purchase_return);
                }
                if (result.total_recovered > 0) {
                    rows += render_row("{{__('report.total_recovered')}}", 0, result.total_recovered);
                    total_credit += parseFloat(result.total_recovered);
                }
                if (result.total_purchase_discount > 0) {
                    rows += render_row("{{__('lang_v1.total_purchase_discount')}}", 0, result.total_purchase_discount);
                    total_credit += parseFloat(result.total_purchase_discount);
                }

                // 4. Expenses (Debit nature)
                if (result.total_purchase > 0) {
                    rows += render_row("{{__('report.total_purchase')}}", result.total_purchase, 0);
                    total_debit += parseFloat(result.total_purchase);
                }
                if (result.total_expense > 0) {
                    rows += render_row("{{__('report.total_expense')}}", result.total_expense, 0);
                    total_debit += parseFloat(result.total_expense);
                }
                if (result.total_sell_return > 0) {
                    rows += render_row("{{__('lang_v1.total_sell_return')}}", result.total_sell_return, 0);
                    total_debit += parseFloat(result.total_sell_return);
                }
                if (result.total_adjustment > 0) {
                    rows += render_row("{{__('report.total_stock_adjustment')}}", result.total_adjustment, 0);
                    total_debit += parseFloat(result.total_adjustment);
                }
                if (result.total_sell_discount > 0) {
                    rows += render_row("{{__('lang_v1.total_sell_discount')}}", result.total_sell_discount, 0);
                    total_debit += parseFloat(result.total_sell_discount);
                }
                if (result.total_reward_amount > 0) {
                    rows += render_row("{{__('lang_v1.total_reward_amount')}}", result.total_reward_amount, 0);
                    total_debit += parseFloat(result.total_reward_amount);
                }
                if (result.total_sell_round_off != 0) {
                    if (result.total_sell_round_off > 0) {
                        rows += render_row("{{__('lang_v1.round_off')}}", result.total_sell_round_off, 0);
                        total_debit += parseFloat(result.total_sell_round_off);
                    } else {
                        rows += render_row("{{__('lang_v1.round_off')}}", 0, Math.abs(result.total_sell_round_off));
                        total_credit += Math.abs(parseFloat(result.total_sell_round_off));
                    }
                }

                $('#trial_balance_details').html(rows);
                $('span#total_debit').text(__currency_trans_from_en(total_debit, true));
                $('span#total_credit').text(__currency_trans_from_en(total_credit, true));
            }
        });
    }

    function render_row(name, debit, credit) {
        var debit_text = debit > 0 ? __currency_trans_from_en(debit, true) : '';
        var credit_text = credit > 0 ? __currency_trans_from_en(credit, true) : '';
        return '<tr><td>' + name + '</td><td class="text-right">' + debit_text + '</td><td class="text-right">' + credit_text + '</td></tr>';
    }
</script>

@endsection