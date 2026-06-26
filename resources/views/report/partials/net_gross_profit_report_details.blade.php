<div style="border-top:3px solid #ddd; padding-top:12px;">

    {{-- COGS --}}
    @php
        $cogs_cur = ($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock'];
    @endphp
    <div style="margin-bottom:10px;">
        <h4 class="text-muted" style="margin:0; font-weight:600;">
            @lang('lang_v1.cogs')
            <span class="display_currency" data-currency_symbol="true">{{ $cogs_cur }}</span>
        </h4>
        <small class="help-block" style="margin:2px 0 0 0;">@lang('lang_v1.cogs_help_text')</small>
    </div>

    {{-- Gross Profit --}}
    <div style="border-top:1px solid #eee; padding-top:10px; margin-bottom:10px;">
        <h3 class="{{ $data['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}" style="margin:0; font-weight:700;">
            {{ __('lang_v1.gross_profit') }}:
            <span class="display_currency" data-currency_symbol="true">{{$data['gross_profit']}}</span>
            @if(!empty($data['total_sell']) && $data['total_sell'] != 0)
                <small>({{ number_format(($data['gross_profit'] / $data['total_sell']) * 100, 2) }}%)</small>
            @endif
        </h3>
        <small class="help-block" style="margin:2px 0 0 0;">
            (@lang('lang_v1.total_sell_price') - @lang('lang_v1.total_purchase_price'))
            @foreach ($data['gross_profit_label'] as $val)
                + {{$val}}
            @endforeach
        </small>
    </div>

    {{-- Net Profit --}}
    <div style="border-top:1px solid #eee; padding-top:10px;">
        <h2 class="{{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}" style="margin:0; font-weight:700;">
            {{ __('report.net_profit') }}:
            <span class="display_currency" data-currency_symbol="true">{{$data['net_profit']}}</span>
            @if(!empty($data['total_sell']) && $data['total_sell'] != 0)
                <small style="font-size:60%;">({{ number_format(($data['net_profit'] / $data['total_sell']) * 100, 2) }}%)</small>
            @endif
        </h2>
        <small class="help-block" style="margin:2px 0 0 0;">
            {{-- total_sell_return_discount: discount recovered when customer returns a discounted sale --}}
            @lang('lang_v1.gross_profit') + (@lang('lang_v1.total_sell_shipping_charge') + @lang('lang_v1.sell_additional_expense') + @lang('report.total_stock_recovered') + @lang('lang_v1.total_purchase_discount') + @lang('lang_v1.total_sell_round_off') + @lang('lang_v1.total_sell_return_discount')
            @foreach($data['right_side_module_data'] as $module_data)
                @if(!empty($module_data['add_to_net_profit']))
                    + {{$module_data['label']}}
                @endif
            @endforeach
            ) <br> - (@lang('report.total_stock_adjustment') + @lang('report.total_expense') + @lang('lang_v1.total_purchase_shipping_charge') + @lang('lang_v1.total_transfer_shipping_charge') + @lang('lang_v1.purchase_additional_expense') + @lang('lang_v1.total_sell_discount') + @lang('lang_v1.total_reward_amount')
            @foreach($data['left_side_module_data'] as $module_data)
                @if(!empty($module_data['add_to_net_profit']))
                    + {{$module_data['label']}}
                @endif
            @endforeach
            )
        </small>
    </div>

    <!-- Tax Summary: informational only, not part of P&L calculation (report uses exc. tax values) -->
    @if(!empty($data['total_sell_tax']) || !empty($data['total_purchase_tax']))
    <div style="border-top:2px solid #ddd; padding-top:10px; margin-top:12px;">
        <h4 class="text-muted" style="margin:0 0 8px 0; font-weight:600;">
            <i class="fa fa-calculator"></i>&nbsp; @lang('lang_v1.tax_summary')
        </h4>
        <table class="table table-condensed" style="margin-bottom:0; width:auto;">
            <tr>
                <th>@lang('lang_v1.tax_collected_on_sales'):</th>
                <td><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_tax'] }}</span></td>
            </tr>
            <tr>
                <th>@lang('lang_v1.tax_paid_on_purchases'):</th>
                <td><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase_tax'] }}</span></td>
            </tr>
            <tr style="border-top:1px solid #ddd;">
                @php $net_tax = $data['total_sell_tax'] - $data['total_purchase_tax']; @endphp
                <th>@lang('lang_v1.net_tax_liability'):</th>
                <td><strong class="{{ $net_tax >= 0 ? 'text-danger' : 'text-success' }}">
                    <span class="display_currency" data-currency_symbol="true">{{ $net_tax }}</span>
                </strong></td>
            </tr>
        </table>
        <small class="help-block" style="margin:2px 0 0 0;">@lang('lang_v1.tax_summary_help')</small>
    </div>
    @endif

</div>
