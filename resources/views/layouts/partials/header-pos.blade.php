<!-- default value -->
@php
    $go_back_url = action([\App\Http\Controllers\SellPosController::class, 'index']);
    $transaction_sub_type = '';
    $view_suspended_sell_url = action([\App\Http\Controllers\SellController::class, 'index']) . '?suspended=1';
    $pos_redirect_url = action([\App\Http\Controllers\SellPosController::class, 'create']);
@endphp

@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @php
            if (!empty($value['go_back_url'])) {
                $go_back_url = $value['go_back_url'];
            }

            if (!empty($value['transaction_sub_type'])) {
                $transaction_sub_type = $value['transaction_sub_type'];
                $view_suspended_sell_url .= '&transaction_sub_type=' . $transaction_sub_type;
                $pos_redirect_url .= '?sub_type=' . $transaction_sub_type;
            }
        @endphp
    @endforeach
@endif
<input type="hidden" name="transaction_sub_type" id="transaction_sub_type" value="{{ $transaction_sub_type }}">
@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header" style="padding: 10px 15px; background-color: #f4f5f7; border-bottom: 1px solid #d2d6de;">
    <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="pull-left" style="margin-top: 2px;">
                <span class="label bg-blue" style="font-size: 13px; padding: 6px 10px; display: inline-block;">
                    <i class="fa fa-map-marker"></i> &nbsp;
                    @if (empty($transaction->location_id))
                        @if (count($business_locations) > 1)
                            {!! Form::select(
                                'select_location_id',
                                $business_locations,
                                $default_location->id ?? null,
                                ['class' => 'input-sm', 'id' => 'select_location_id', 'required', 'autofocus', 'style' => 'background: transparent; color: #fff; border: none; outline: none; font-weight: bold;'],
                                $bl_attributes,
                            ) !!}
                        @else
                            {{ $default_location->name }}
                        @endif
                    @else
                        {{ $transaction->location->name }}
                    @endif
                </span>
                <span class="label label-default hidden-xs" style="font-size: 13px; padding: 6px 10px; margin-left: 5px; display: inline-block;">
                    <i class="fa fa-clock-o"></i> <span class="curr_datetime">{{ @format_datetime('now') }}</span>
                    <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body"
                        data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')"
                        data-html="true" data-trigger="hover" data-original-title="" title="" style="margin-left: 5px; cursor: pointer;"></i>
                </span>
            </div>
            @if (empty($pos_settings['hide_product_suggestion']))
                <button type="button" title="{{ __('lang_v1.view_products') }}" data-placement="bottom"
                    class="btn btn-default btn-sm pull-right visible-xs btn-modal"
                    data-toggle="modal" data-target="#mobile_product_suggestion_modal">
                    <strong><i class="fa fa-cubes text-green"></i></strong>
                </button>
            @endif
        </div>

        <div class="col-md-8 col-sm-12 text-right">
            <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}" class="btn btn-info btn-flat btn-sm" style="margin-right: 3px;">
                <strong><i class="fa fa-backward"></i> <span class="hidden-xs">{{ __('lang_v1.go_back') }}</span></strong>
            </a>

            @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                <button type="button" class="btn btn-primary btn-flat btn-sm visible-xs-inline-block" data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions" style="margin-right: 3px;">
                    <strong><i class="fa fa-clock-o"></i></strong>
                </button>
            @endif

            @if (!empty($pos_settings['inline_service_staff']))
                <button type="button" id="show_service_staff_availability" title="{{ __('lang_v1.service_staff_availability') }}"
                    class="btn btn-default btn-flat btn-sm" data-container=".view_modal" style="margin-right: 3px;"
                    data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">
                    <strong><i class="fa fa-users text-purple"></i></strong>
                </button>
            @endif

            @can('close_cash_register')
                <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                    class="btn btn-danger btn-flat btn-sm btn-modal" data-container=".close_register_modal" style="margin-right: 3px;"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                    <strong><i class="fa fa-window-close"></i> <span class="hidden-xs">{{ __('cash_register.close_register') }}</span></strong>
                </button>
            @endcan

            @if (!empty($pos_settings['inline_service_staff']) || (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules)))
                <button type="button" class="btn btn-default btn-flat btn-sm popover-default" style="margin-right: 3px;"
                    id="service_staff_replacement" title="{{ __('restaurant.service_staff_replacement') }}"
                    data-toggle="popover" data-trigger="click"
                    data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_service_staff_invoice_no"></div><div class="w-100 text-center"><button type="button" class="btn btn-danger btn-xs" id="send_for_sercice_staff_replacement">@lang('lang_v1.send')</button></div>'
                    data-html="true" data-placement="bottom">
                    <strong><i class="fa fa-user-plus text-purple"></i></strong>
                </button>
            @endif

            @can('view_cash_register')
                <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                    class="btn btn-success btn-flat btn-sm btn-modal" data-container=".register_details_modal" style="margin-right: 3px;"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                    <strong><i class="fa fa-briefcase"></i> <span class="hidden-xs">{{ __('cash_register.register_details') }}</span></strong>
                </button>
            @endcan

            <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button" class="btn btn-default btn-flat btn-sm popover-default" style="margin-right: 3px;"
                data-toggle="popover" data-trigger="click" data-content='@include('layouts.partials.calculator')' data-html="true" data-placement="bottom">
                <strong><i class="fa fa-calculator text-teal"></i></strong>
            </button>

            <button type="button" class="btn btn-warning btn-flat btn-sm popover-default" style="margin-right: 3px;"
                id="return_sale" title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click"
                data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="btn btn-danger btn-xs" id="send_for_sell_return">@lang('lang_v1.send')</button></div>'
                data-html="true" data-placement="bottom">
                <strong><i class="fa fa-undo"></i> <span class="hidden-xs">@lang('lang_v1.sell_return')</span></strong>
            </button>

            <button type="button" id="toggle-dark-mode" title="Toggle Dark Mode" class="btn btn-default btn-flat btn-sm" style="margin-right: 3px;">
                <i class="fa fa-moon-o" id="dark-mode-icon-moon"></i>
                <i class="fa fa-sun-o hide" id="dark-mode-icon-sun"></i>
            </button>

            <button type="button" title="{{ __('lang_v1.full_screen') }}" class="btn btn-default btn-flat btn-sm" id="full_screen" style="margin-right: 3px;">
                <strong><i class="fa fa-window-maximize"></i></strong>
            </button>

            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                class="btn btn-warning btn-flat btn-sm btn-modal" data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}" style="margin-right: 3px;">
                <strong><i class="fa fa-pause-circle"></i> <span class="hidden-xs">{{ __('lang_v1.view_suspended_sales') }}</span></strong>
            </button>

            @if (!empty($pos_settings['customer_display_screen']))
                <a href="{{route('pos_display')}}" id="customer_display_screen" onclick="window.open(this.href, 'customer_display', 'width='+screen.width+',height='+screen.height+',top=0,left=0'); return false;" title="{{ __('lang_v1.customer_display_screen') }}"
                    class="btn btn-default btn-flat btn-sm" style="margin-right: 3px;">
                    <strong><i class="fa fa-tv text-blue"></i></strong>
                </a>
            @endif

            @if (isModuleEnabled('Repair') && $transaction_sub_type != 'repair')
                @include('repair::layouts.partials.pos_header')
            @endif

            @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                @can('sell.create')
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                        title="@lang('sale.pos_sale')" class="btn btn-success btn-flat btn-sm" style="margin-right: 3px;">
                        <strong><i class="fa fa-th-large"></i> @lang('sale.pos_sale')</strong>
                    </a>
                @endcan
            @endif

            @can('expense.add')
                <button type="button" title="{{ __('expense.add_expense') }}" data-placement="bottom"
                    class="btn btn-danger btn-flat btn-sm btn-modal" id="add_expense" style="margin-right: 3px;">
                    <strong><i class="fa fa-minus-circle"></i> @lang('expense.add_expense')</strong>
                </button>
            @endcan
        </div>
    </div>
</div>

<div class="modal fade" id="service_staff_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
