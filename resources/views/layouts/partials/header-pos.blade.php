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
<div class="col-md-12 no-print pos-header">
    <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">
    <div
        class="tw-flex tw-flex-col lg:tw-flex-row tw-items-center tw-justify-between tw-shadow-[rgba(15,23,42,0.04)_0px_3px_12px] tw-bg-white tw-rounded-2xl tw-mx-0 tw-mt-1 tw-mb-0 tw-p-3.5 tw-gap-3">

        {{-- Left Location & DateTime Info Panel --}}
        <div class="tw-w-full lg:tw-w-auto tw-flex-shrink-0">
            <div class="tw-flex tw-items-center tw-gap-3">
                <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[13px] tw-text-slate-500">
                    <span class="tw-font-bold tw-text-slate-800">@lang('sale.location'):</span>
                    <span class="tw-font-semibold tw-text-slate-600">
                        @if (empty($transaction->location_id))
                            @if (count($business_locations) > 1)
                                <div style="display: inline-block; width: 140px;">
                                    {!! Form::select(
                                        'select_location_id',
                                        $business_locations,
                                        $default_location->id ?? null,
                                        ['class' => 'form-control input-sm tw-rounded-lg tw-border-slate-200 !tw-h-8 !tw-p-1', 'id' => 'select_location_id', 'required', 'autofocus'],
                                        $bl_attributes,
                                    ) !!}
                                </div>
                            @else
                                {{ $default_location->name }}
                            @endif
                        @else
                            {{ $transaction->location->name }}
                        @endif
                    </span>
                </div>

                {{-- Datetime and shortcuts trigger --}}
                <div
                    class="tw-hidden md:tw-flex tw-items-center tw-gap-1.5 tw-bg-slate-50 tw-border tw-border-slate-100 tw-py-1 tw-px-2.5 tw-rounded-lg">
                    <span class="curr_datetime tw-text-xs tw-font-bold tw-text-slate-600">{{ @format_datetime('now') }}</span>
                    <i class="fa fa-keyboard hover-q tw-text-slate-400 hover:tw-text-slate-600 tw-cursor-pointer tw-text-xs" aria-hidden="true" data-container="body"
                        data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')"
                        data-html="true" data-trigger="hover" data-original-title="" title=""></i>
                </div>

                @if (empty($pos_settings['hide_product_suggestion']))
                    <button type="button" title="{{ __('lang_v1.view_products') }}" data-placement="bottom"
                        class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-600 btn-modal pull-right tw-block md:tw-hidden"
                        data-toggle="modal" data-target="#mobile_product_suggestion_modal">
                        <strong><i class="fa fa-cubes fa-lg tw-text-slate-500 !tw-text-sm"></i></strong>
                    </button>
                @endif

                <span class="tw-block md:tw-hidden">
                    <i class="fas hamburger fa-bars tw-mx-5"
                        onclick="document.getElementById('pos_header_more_options').classList.toggle('tw-hidden')"></i>
                </span>

            </div>
        </div>

        {{-- Right Actions Row (Tightly-grouped, compact layout) --}}
        <div class="tw-w-full lg:tw-w-auto tw-flex tw-items-center tw-justify-start lg:tw-justify-end tw-gap-1.5 tw-flex-wrap md:tw-flex-nowrap"
            id="pos_header_more_options">

            {{-- Back Button --}}
            <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}"
                class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all">
                <i class="fa fa-backward tw-text-slate-500 tw-text-xs"></i>
            </a>

            {{-- Recent Transactions --}}
            @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                <button type="button"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all"
                    data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions" title="{{ __('lang_v1.recent_transactions') }}">
                    <i class="fa fa-history tw-text-slate-500 tw-text-sm"></i>
                </button>
            @endif

            {{-- Service Staff --}}
            @if (!empty($pos_settings['inline_service_staff']))
                <button type="button" id="show_service_staff_availability"
                    title="{{ __('lang_v1.service_staff_availability') }}"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all"
                    data-container=".view_modal"
                    data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">
                    <i class="fa fa-users tw-text-slate-500 tw-text-sm"></i>
                </button>
            @endif

            {{-- Close Register --}}
            @can('close_cash_register')
                <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all"
                    data-container=".close_register_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                    <i class="fa fa-times-circle tw-text-slate-500 tw-text-sm"></i>
                </button>
            @endcan

            {{-- Service Staff Replacement --}}
            @if (
                !empty($pos_settings['inline_service_staff']) ||
                    (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules)))
                <button type="button"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all popover-default"
                    id="service_staff_replacement" title="{{ __('restaurant.service_staff_replacement') }}"
                    data-toggle="popover" data-trigger="click"
                    data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_service_staff_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" id="send_for_sercice_staff_replacement">@lang('lang_v1.send')</button></div>'
                    data-html="true" data-placement="bottom">
                    <i class="fa fa-user-plus tw-text-slate-500 tw-text-sm"></i>
                </button>
            @endif

            {{-- Register Details --}}
            @can('view_cash_register')
                <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all btn-modal"
                    data-container=".register_details_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                    <i class="fa fa-briefcase tw-text-slate-500 tw-text-sm" aria-hidden="true"></i>
                </button>
            @endcan

            {{-- Calculator --}}
            <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button"
                class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all popover-default"
                data-toggle="popover" data-trigger="click" data-content='@include('layouts.partials.calculator')' data-html="true"
                data-placement="bottom">
                <i class="fa fa-calculator tw-text-slate-500 tw-text-sm" aria-hidden="true"></i>
            </button>

            {{-- Return --}}
            <button type="button"
                class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all popover-default"
                id="return_sale" title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click"
                data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm" id="send_for_sell_return">@lang('lang_v1.send')</button></div>'
                data-html="true" data-placement="bottom">
                <i class="fas fa-undo tw-text-slate-500 tw-text-sm"></i>
            </button>

            {{-- Full Screen --}}
            <button type="button" title="{{ __('lang_v1.full_screen') }}"
                class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all"
                id="full_screen">
                <i class="fa fa-window-maximize tw-text-slate-500 tw-text-sm"></i>
            </button>

            {{-- View Suspended Sales --}}
            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all btn-modal"
                data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}">
                <i class="fa fa-pause-circle tw-text-slate-400 tw-text-sm"></i>
            </button>

            {{-- Customer Display Screen --}}
            @if (!empty($pos_settings['customer_display_screen']))
                <a href="{{route('pos_display')}}" id="customer_display_screen"  onclick="window.open(this.href, 'customer_display', 'width='+screen.width+',height='+screen.height+',top=0,left=0'); return false;"   title="{{ __('lang_v1.customer_display_screen') }}"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-w-9 tw-h-9 tw-text-slate-500 active:tw-scale-[0.97] tw-transition-all">
                    <i class="fa fa-tv tw-text-slate-500 tw-text-sm"></i>
                </a>
            @endif

            {{-- Large Module Buttons (Styled as soft rounded-xl action chips) --}}
            @if (isModuleEnabled('Repair') && $transaction_sub_type != 'repair')
                @include('repair::layouts.partials.pos_header')
            @endif

            @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                @can('sell.create')
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                        title="@lang('sale.pos_sale')"
                        class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-h-9 tw-px-3.5 tw-text-xs tw-font-bold tw-text-slate-600 active:tw-scale-[0.97] tw-transition-all">
                        <i class="fa fa-th-large tw-text-slate-400 tw-text-xs tw-mr-1"></i>
                        @lang('sale.pos_sale')
                    </a>
                @endcan
            @endif

            @can('expense.add')
                <button type="button" title="{{ __('expense.add_expense') }}" data-placement="bottom"
                    class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-h-9 tw-px-3.5 tw-text-xs tw-font-bold tw-text-slate-600 active:tw-scale-[0.97] tw-transition-all btn-modal"
                    id="add_expense">
                    <i class="fa fa-minus-circle tw-text-slate-400 tw-text-xs tw-mr-1"></i> @lang('expense.add_expense')
                </button>
            @endcan

        </div>
    </div>
</div>

<div class="modal fade" id="service_staff_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
