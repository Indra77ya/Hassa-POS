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
<div class="col-md-12 no-print pos-header tw-sticky tw-top-0 tw-z-50 tw-bg-gray-100 !tw-py-2">
    <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">
    <div
        class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-shadow-[0_4px_20px_rgba(0,0,0,0.03)] tw-bg-white tw-border tw-border-slate-100 tw-rounded-2xl tw-mx-0 tw-mt-2 tw-mb-0 md:tw-mb-0 tw-p-3">
        <div class="tw-w-full md:tw-w-1/3">
            <div class="tw-flex tw-items-center tw-gap-3">
                <div class="tw-flex tw-items-center tw-gap-2 tw-bg-slate-50 tw-border tw-border-slate-100 tw-py-1 tw-px-3 tw-rounded-full">
                    <span class="tw-text-[10px] tw-text-slate-400 tw-uppercase tw-tracking-wider">@lang('sale.location')</span>
                    <span class="tw-text-xs tw-text-slate-700">
                        @if (empty($transaction->location_id))
                            @if (count($business_locations) > 1)
                                {!! Form::select(
                                    'select_location_id',
                                    $business_locations,
                                    $default_location->id ?? null,
                                    ['class' => 'form-control input-sm !tw-h-6 !tw-py-0 tw-text-xs tw-border-0 tw-bg-transparent focus:tw-ring-0', 'id' => 'select_location_id', 'required', 'autofocus'],
                                    $bl_attributes,
                                ) !!}
                            @else
                                {{ $default_location->name }}
                            @endif
                        @else
                            {{ $transaction->location->name }}
                        @endif
                    </span>
                </div>

                <div
                    class="tw-hidden md:tw-flex tw-items-center tw-gap-1.5 tw-bg-indigo-50/70 tw-border tw-border-indigo-100/80 tw-py-1 tw-px-3 tw-rounded-full">
                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-indigo-500 tw-animate-pulse"></span>
                    <span class="curr_datetime tw-text-indigo-700 tw-text-xs tw-font-semibold">{{ @format_datetime('now') }}</span>
                    <i class="fa fa-keyboard hover-q tw-text-indigo-400 hover:tw-text-indigo-600 tw-cursor-pointer tw-text-xs tw-ml-1" aria-hidden="true" data-container="body"
                        data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')"
                        data-html="true" data-trigger="hover" data-original-title="" title=""></i>
                </div>

                @if (empty($pos_settings['hide_product_suggestion']))
                    <button type="button" title="{{ __('lang_v1.view_products') }}" data-placement="bottom"
                        class="btn tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 btn-modal pull-right tw-block md:tw-hidden"
                        data-toggle="modal" data-target="#mobile_product_suggestion_modal">
                        <strong><i class="fa fa-cubes fa-lg tw-text-emerald-500 !tw-text-sm"></i></strong>
                    </button>
                @endif

                <span class="tw-block md:tw-hidden tw-ml-auto">
                    <i class="fas hamburger fa-bars tw-text-slate-600 tw-text-lg tw-cursor-pointer"
                        onclick="document.getElementById('pos_header_more_options').classList.toggle('tw-hidden')"></i>
                </span>

            </div>
        </div>

        <div class="tw-w-full md:tw-w-2/3 !tw-p-0 tw-flex tw-items-center tw-justify-end tw-gap-2.5 tw-flex-wrap md:tw-flex-nowrap tw-hidden md:tw-flex"
            id="pos_header_more_options">
            <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}"
                class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 hover:-tw-translate-y-px active:tw-scale-95">
                <strong>
                    <i class="fa fa-backward tw-text-blue-500 !tw-text-sm"></i>
                    <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.go_back') }}</span>
                </strong>
            </a>

            @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                <button type="button"
                    class="md:tw-hidden tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 hover:-tw-translate-y-px active:tw-scale-95"
                    data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions">
                        <strong>
                            <i class="fa fa-clock tw-text-indigo-500 !tw-text-sm"></i>
                            <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.recent_transactions') }}</span>
                        </strong>
                </button>
            @endif

            @if (!empty($pos_settings['inline_service_staff']))
                <button type="button" id="show_service_staff_availability"
                    title="{{ __('lang_v1.service_staff_availability') }}"
                    class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 hover:-tw-translate-y-px active:tw-scale-95"
                    data-container=".view_modal"
                    data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">
                    <strong>
                        <i class="fa fa-users tw-text-violet-500 !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.service_staff_availability') }}</span>
                    </strong>
                </button>
            @endif

            @can('close_cash_register')
                <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                    class="btn tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 btn-modal hover:-tw-translate-y-px active:tw-scale-95"
                    data-container=".close_register_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                    <strong>
                        <i class="fa fa-window-close tw-text-rose-500 !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('cash_register.close_register') }}</span>
                    </strong>
                </button>
            @endcan

            @if (
                !empty($pos_settings['inline_service_staff']) ||
                    (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules)))
                <button type="button"
                    class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 popover-default hover:-tw-translate-y-px active:tw-scale-95"
                    id="service_staff_replacement" title="{{ __('restaurant.service_staff_replacement') }}"
                    data-toggle="popover" data-trigger="click"
                    data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_service_staff_invoice_no"></div><div class="w-100 text-center"><button type="button" class="btn btn-xs btn-danger" id="send_for_sercice_staff_replacement">@lang('lang_v1.send')</button></div>'
                    data-html="true" data-placement="bottom">
                    <strong>
                        <i class="fa fa-user-plus tw-text-violet-500 !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('restaurant.service_staff_replacement') }}</span>
                    </strong>
                </button>
            @endif

            @can('view_cash_register')
                <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                    class="btn tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 btn-modal hover:-tw-translate-y-px active:tw-scale-95"
                    data-container=".register_details_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                    <strong>
                        <i class="fa fa-briefcase tw-text-emerald-500 !tw-text-sm" aria-hidden="true"></i>
                        <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('cash_register.register_details') }}</span>
                    </strong>
                </button>
            @endcan

            <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button"
                class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 popover-default hover:-tw-translate-y-px active:tw-scale-95"
                data-toggle="popover" data-trigger="click" data-content='@include('layouts.partials.calculator')' data-html="true"
                data-placement="bottom">
                <strong>
                    <i class="fa fa-calculator tw-text-teal-500 !tw-text-sm" aria-hidden="true"></i>
                    <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.calculator') }}</span>
                </strong>
            </button>

            <button type="button"
                class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 popover-default hover:-tw-translate-y-px active:tw-scale-95"
                id="return_sale" title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click"
                data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="btn btn-danger btn-sm" id="send_for_sell_return">@lang('lang_v1.send')</button></div>'
                data-html="true" data-placement="bottom">
                <strong>
                    <i class="fas fa-undo tw-text-rose-500 !tw-text-sm"></i>
                    <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.sell_return') }}</span>
                </strong>
            </button>

            <button type="button" title="{{ __('lang_v1.full_screen') }}"
                class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 hover:-tw-translate-y-px active:tw-scale-95"
                id="full_screen">
                <strong>
                    <i class="fa fa-window-maximize tw-text-slate-500 !tw-text-sm"></i>
                    <span class="tw-inline md:tw-hidden tw-ml-1">Full Screen</span>
                </strong>
            </button>

            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                class="btn tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 btn-modal hover:-tw-translate-y-px active:tw-scale-95"
                data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}">
                <strong>
                    <i class="fa fa-pause-circle tw-text-amber-500 !tw-text-sm"></i>
                    <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.view_suspended_sales') }}</span>
                </strong>
            </button>

            @if (!empty($pos_settings['customer_display_screen']))
                <a href="{{route('pos_display')}}" id="customer_display_screen" onclick="window.open(this.href, 'customer_display', 'width='+screen.width+',height='+screen.height+',top=0,left=0'); return false;" title="{{ __('lang_v1.customer_display_screen') }}"
                    class="tw-shadow-sm tw-bg-slate-50 hover:tw-bg-slate-100 tw-cursor-pointer tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-text-gray-600 hover:-tw-translate-y-px active:tw-scale-95">
                    <strong>
                        <i class="fa fa-tv tw-text-blue-500 !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden tw-ml-1">{{ __('lang_v1.customer_display_screen') }}</span>
                    </strong>
                </a>
            @endif

            @if (isModuleEnabled('Repair') && $transaction_sub_type != 'repair')
                @include('repair::layouts.partials.pos_header')
            @endif

            @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                @can('sell.create')
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                        title="@lang('sale.pos_sale')"
                        class="tw-shadow-sm tw-bg-emerald-50 hover:tw-bg-emerald-100 tw-border tw-border-emerald-100 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-py-2 tw-px-4 hover:-tw-translate-y-px active:tw-scale-95">
                        <strong class="tw-text-xs tw-text-emerald-700"><i class="fa fa-th-large !tw-text-sm"></i> &nbsp;
                            @lang('sale.pos_sale')</strong>
                    </a>
                @endcan
            @endif
            @can('expense.add')
                <button type="button" title="{{ __('expense.add_expense') }}" data-placement="bottom"
                    class="btn tw-shadow-sm tw-bg-rose-50 hover:tw-bg-rose-100 tw-border tw-border-rose-100 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-py-2 tw-px-4 btn-modal hover:-tw-translate-y-px active:tw-scale-95"
                    id="add_expense">
                    <strong class="tw-text-xs tw-text-rose-700"><i class="fa fas fa-minus-circle"></i> &nbsp;@lang('expense.add_expense')</strong>
                </button>
            @endcan

        </div>
    </div>
</div>

<div class="modal fade" id="service_staff_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
