@php
    $is_mobile = isMobile();
@endphp
<div class="row" style="margin:0;">
    <div
        class="pos-form-actions tw-fixed tw-bottom-0 tw-left-0 tw-right-0 tw-w-full tw-z-[1000] !tw-mt-0 tw-bg-white tw-border-t tw-border-slate-100 tw-shadow-[0_-8px_30px_rgb(0,0,0,0.04)] tw-rounded-t-2xl">
        <div
            class="tw-flex tw-items-center tw-justify-between tw-flex-col sm:tw-flex-row md:tw-flex-row lg:tw-flex-row xl:tw-flex-row tw-gap-2.5 tw-overflow-x-auto tw-w-full tw-px-5 tw-py-2 tw-min-h-[60px]">

            <div class="!tw-w-full md:!tw-w-none !tw-flex md:!tw-hidden !tw-flex-row !tw-items-center !tw-gap-3">
                @if (empty($edit))
                    <button type="button" class="tw-leading-none tw-whitespace-nowrap tw-font-bold tw-text-rose-600 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-rose-50 hover:tw-bg-rose-100 tw-border tw-border-rose-100 tw-p-2.5 tw-rounded-xl tw-w-[5.5rem] tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1 active:tw-scale-95 tw-transition-all js-pos-cancel"> <i
                            class="fas fa-window-close"></i> @lang('sale.cancel')</button>
                @else
                    <button type="button" class="btn-danger tw-dw-btn hide tw-dw-btn-xs js-pos-delete" id="pos-delete"
                        @if (!empty($only_payment)) disabled @endif> <i class="fas fa-trash-alt"></i>
                        @lang('messages.delete')</button>
                @endif

                @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="pos-finalize tw-leading-none tw-whitespace-nowrap tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1.5 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-slate-800 hover:tw-bg-slate-900 tw-rounded-xl tw-p-2.5 tw-w-[8.5rem] active:tw-scale-95 tw-transition-all no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                        title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                            aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay') </button>
                @endif

                @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-leading-none tw-whitespace-nowrap tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-p-2.5 tw-rounded-xl tw-w-[5.5rem] tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1 active:tw-scale-95 tw-transition-all no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize"
                        style="background-color: #10b981 !important; color: #ffffff !important;"
                        data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt"
                            aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>
                @endif
            </div>

            <div class="tw-flex tw-items-center tw-gap-4 tw-flex-row tw-overflow-x-auto pos-footer-secondary">

                {{-- Cancel: isolated on the far left (desktop only; mobile has its own Cancel above) --}}
                <div class="!tw-hidden md:!tw-flex md:tw-items-center md:tw-gap-3">
                    @if (empty($edit))
                        <button type="button"
                            class="tw-leading-none tw-whitespace-nowrap tw-font-bold tw-text-rose-600 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-rose-50 hover:tw-bg-rose-100 tw-border tw-border-rose-100 tw-py-2 tw-px-4 tw-rounded-xl tw-w-[8.5rem] tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1.5 active:tw-scale-95 tw-transition-all js-pos-cancel"> <i
                                class="fas fa-window-close"></i> @lang('sale.cancel')</button>
                    @else
                        <button type="button"
                            class="tw-leading-none tw-whitespace-nowrap tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-rose-600 hover:tw-bg-rose-700 tw-py-2 tw-px-4 tw-rounded-xl tw-w-[8.5rem] tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1.5 hide active:tw-scale-95 tw-transition-all js-pos-delete"
                            @if (!empty($only_payment)) disabled @endif> <i
                                class="fas fa-trash-alt"></i> @lang('messages.delete')</button>
                    @endif
                    <span class="pos-footer-divider tw-inline-block tw-w-px tw-h-7 tw-bg-slate-100 tw-flex-shrink-0 tw-self-center tw-rounded-[1px] tw-mx-1"></span>
                </div>

                @if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-font-bold tw-text-slate-600 hover:tw-text-indigo-600 tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 tw-whitespace-nowrap tw-transition-colors @if ($pos_settings['disable_draft'] != 0) hide @endif"
                        id="pos-draft" @if (!empty($only_payment)) disabled @endif><i
                            class="fas fa-edit tw-text-indigo-500 tw-text-base"></i> @lang('sale.draft')</button>
                @endif

                @if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-font-bold tw-text-slate-600 hover:tw-text-amber-600 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 tw-whitespace-nowrap tw-transition-colors @if ($is_mobile) col-xs-6 @endif"
                        id="pos-quotation" @if (!empty($only_payment)) disabled @endif><i
                            class="fas fa-file-alt tw-text-amber-500 tw-text-base"></i> @lang('lang_v1.quotation')</button>
                @endif

                @if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    @if (empty($pos_settings['disable_suspend']))
                        <button type="button"
                            class="tw-font-bold tw-text-slate-600 hover:tw-text-rose-600 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 tw-whitespace-nowrap tw-transition-colors no-print pos-express-finalize"
                            data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')"
                            @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-pause-circle tw-text-rose-500 tw-text-base" aria-hidden="true"></i>
                            @lang('lang_v1.suspend')
                        </button>
                    @endif
                @endif

                @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    @if (empty($pos_settings['disable_credit_sale_button']))
                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                        <button type="button"
                            class="tw-font-bold tw-text-slate-600 hover:tw-text-violet-600 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 tw-whitespace-nowrap tw-transition-colors no-print pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                            data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                            @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-handshake tw-text-violet-500 tw-text-base" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
                        </button>
                    @endif
                @endif
                @if (!Gate::check('disable_card') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-font-bold tw-text-slate-600 hover:tw-text-pink-600 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 tw-whitespace-nowrap tw-transition-colors no-print @if (!empty($pos_settings['disable_suspend']))  @endif pos-express-finalize @if (!array_key_exists('card', $payment_types)) hide @endif @if ($is_mobile) col-xs-6 @endif"
                        data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">
                        <i class="fas fa-credit-card tw-text-pink-500 tw-text-base" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')
                    </button>
                @endif

                {{-- Desktop-only primary CTAs (mobile has these in its own top row) --}}
                <div class="!tw-hidden md:!tw-flex md:tw-items-center md:tw-gap-3">
                    <span class="pos-footer-divider tw-inline-block tw-w-px tw-h-7 tw-bg-slate-100 tw-flex-shrink-0 tw-self-center tw-rounded-[1px] tw-mx-1"></span>
                    @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="pos-finalize tw-leading-none tw-whitespace-nowrap tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1.5 tw-font-extrabold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-slate-800 hover:tw-bg-slate-900 tw-py-2.5 tw-px-5 tw-rounded-xl tw-transition-all tw-duration-200 hover:-tw-translate-y-px active:tw-scale-95 tw-shadow-sm hover:tw-shadow-md no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                            style="width: 150px !important;"
                            title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                                aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay') </button>
                    @endif

                    @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="tw-leading-none tw-whitespace-nowrap tw-font-extrabold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-py-2.5 tw-px-5 tw-rounded-xl tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1.5 tw-transition-all tw-duration-200 hover:-tw-translate-y-px active:tw-scale-95 tw-shadow-sm hover:tw-shadow-md no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize"
                            style="background-color: #10b981 !important; color: #ffffff !important; width: 150px !important;"
                            data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt"
                                aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>
                    @endif
                </div>
            </div>

            <div class="tw-w-full md:tw-w-fit tw-flex tw-items-center tw-justify-end tw-hidden md:tw-flex" style="padding-right: 36px !important;">
                @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                    <button type="button"
                        class="tw-font-bold tw-bg-indigo-50 hover:tw-bg-indigo-100 tw-border tw-border-indigo-100 tw-text-indigo-600 tw-rounded-xl tw-w-full md:tw-w-fit tw-px-4 tw-h-9 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-transition-all tw-duration-200 hover:-tw-translate-y-px active:tw-scale-95"
                        data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"><i
                            class="fas fa-history"></i> @lang('lang_v1.recent_transactions')</button>
                @endif
            </div>
        </div>
    </div>
</div>
@if (isset($transaction))
    @include('sale_pos.partials.edit_discount_modal', [
        'sales_discount' => $transaction->discount_amount,
        'discount_type' => $transaction->discount_type,
        'rp_redeemed' => $transaction->rp_redeemed,
        'rp_redeemed_amount' => $transaction->rp_redeemed_amount,
        'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0,
    ])
@else
    @include('sale_pos.partials.edit_discount_modal', [
        'sales_discount' => $business_details->default_sales_discount,
        'discount_type' => 'percentage',
        'rp_redeemed' => 0,
        'rp_redeemed_amount' => 0,
        'max_available' => 0,
    ])
@endif

@if (isset($transaction))
    @include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
    @include('sale_pos.partials.edit_order_tax_modal', [
        'selected_tax' => $business_details->default_sales_tax,
    ])
@endif

@include('sale_pos.partials.edit_shipping_modal')
