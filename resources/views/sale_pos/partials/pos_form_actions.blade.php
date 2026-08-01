@php
    $is_mobile = isMobile();
@endphp
<div class="row" style="margin:0;">
    <div
        class="pos-form-actions tw-fixed tw-bottom-0 tw-left-0 tw-right-0 tw-w-full tw-z-[1000] !tw-mt-0 tw-bg-slate-50 tw-border-t tw-border-slate-200/60 tw-shadow-[0_-3px_12px_rgba(15,23,42,0.04)] tw-rounded-tl-2xl tw-rounded-tr-2xl">
        <div
            class="tw-flex tw-items-center tw-justify-between tw-flex-col sm:tw-flex-row md:tw-flex-row lg:tw-flex-row xl:tw-flex-row tw-gap-2.5 tw-overflow-x-auto tw-w-full tw-px-5 tw-py-2.5 tw-min-h-[58px]">

            {{-- Mobile Actions Bar --}}
            <div class="!tw-w-full md:!tw-w-none !tw-flex md:!tw-hidden !tw-flex-row !tw-items-center !tw-gap-2">
                @if (empty($edit))
                    <button type="button"
                        class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-text-slate-600 tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-3.5 tw-text-xs tw-font-bold tw-whitespace-nowrap js-pos-cancel">
                        <i class="fas fa-times-circle tw-text-slate-400"></i> @lang('sale.cancel')
                    </button>
                @else
                    <button type="button"
                        class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-rose-50 hover:tw-bg-rose-100 tw-border tw-border-rose-200 tw-text-rose-700 tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-3.5 tw-text-xs tw-font-bold tw-whitespace-nowrap js-pos-delete"
                        id="pos-delete" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-trash-alt tw-text-rose-500"></i> @lang('messages.delete')
                    </button>
                @endif

                @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="pos-finalize tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-[var(--theme-800)] hover:tw-bg-[var(--theme-700)] active:tw-bg-[var(--theme-900)] tw-text-white tw-shadow-[0_2px_4px_rgba(0,0,0,0.06)] tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs tw-font-bold tw-whitespace-nowrap no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                        title="@lang('lang_v1.tooltip_checkout_multi_pay')">
                        <i class="fas fa-calculator" aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay')
                    </button>
                @endif

                @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="pos-express-finalize tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-emerald-600 hover:tw-bg-emerald-500 active:tw-bg-emerald-700 tw-text-white tw-shadow-[0_2px_4px_rgba(0,0,0,0.06)] tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-3.5 tw-text-xs tw-font-bold tw-whitespace-nowrap no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif"
                        data-pay_method="cash" title="@lang('tooltip.express_checkout')">
                        <i class="fas fa-bolt" aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')
                    </button>
                @endif
            </div>

            {{-- Main / Secondary Actions List --}}
            <div class="tw-flex tw-items-center tw-gap-2 tw-flex-row tw-overflow-x-auto pos-footer-secondary tw-py-0.5">

                {{-- Cancel: Desktop only --}}
                <div class="!tw-hidden md:!tw-flex md:tw-items-center md:tw-gap-2">
                    @if (empty($edit))
                        <button type="button"
                            class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-text-slate-600 tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap js-pos-cancel">
                            <i class="fas fa-times-circle tw-text-slate-400"></i> @lang('sale.cancel')
                        </button>
                    @else
                        <button type="button"
                            class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-rose-50 hover:tw-bg-rose-100 tw-border tw-border-rose-200 tw-text-rose-700 tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap hide js-pos-delete"
                            id="pos-delete" @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-trash-alt tw-text-rose-500"></i> @lang('messages.delete')
                        </button>
                    @endif
                    <span class="pos-footer-divider tw-inline-block tw-w-px tw-h-5 tw-bg-slate-200 tw-flex-shrink-0 tw-self-center tw-mx-1"></span>
                </div>

                {{-- Draft --}}
                @if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 tw-text-slate-600 tw-transition-all tw-duration-150 hover:tw-bg-slate-50 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap @if ($pos_settings['disable_draft'] != 0) hide @endif"
                        id="pos-draft" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-file-alt tw-text-slate-400"></i> @lang('sale.draft')
                    </button>
                @endif

                {{-- Quotation --}}
                @if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 tw-text-slate-600 tw-transition-all tw-duration-150 hover:tw-bg-slate-50 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap @if ($is_mobile) col-xs-6 @endif"
                        id="pos-quotation" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-file-invoice-dollar tw-text-slate-400"></i> @lang('lang_v1.quotation')
                    </button>
                @endif

                {{-- Suspend --}}
                @if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    @if (empty($pos_settings['disable_suspend']))
                        <button type="button"
                            class="pos-express-finalize tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 tw-text-slate-600 tw-transition-all tw-duration-150 hover:tw-bg-slate-50 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap no-print"
                            data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')"
                            @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-pause-circle tw-text-slate-400" aria-hidden="true"></i> @lang('lang_v1.suspend')
                        </button>
                    @endif
                @endif

                {{-- Credit Sale --}}
                @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    @if (empty($pos_settings['disable_credit_sale_button']))
                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                        <button type="button"
                            class="pos-express-finalize tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 tw-text-slate-600 tw-transition-all tw-duration-150 hover:tw-bg-slate-50 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap no-print @if ($is_mobile) col-xs-6 @endif"
                            data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                            @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-handshake tw-text-slate-400" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
                        </button>
                    @endif
                @endif

                {{-- Express Checkout Card --}}
                @if (!Gate::check('disable_card') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="pos-express-finalize tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-white tw-border tw-border-slate-200 tw-text-slate-600 tw-transition-all tw-duration-150 hover:tw-bg-slate-50 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap no-print @if (!array_key_exists('card', $payment_types)) hide @endif @if ($is_mobile) col-xs-6 @endif"
                        data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">
                        <i class="fas fa-credit-card tw-text-slate-400" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')
                    </button>
                @endif

                {{-- Desktop-only primary CTAs (Checkout & Cash) --}}
                <div class="!tw-hidden md:!tw-flex md:tw-items-center md:tw-gap-2">
                    <span class="pos-footer-divider tw-inline-block tw-w-px tw-h-5 tw-bg-slate-200 tw-flex-shrink-0 tw-self-center tw-mx-1"></span>

                    @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="pos-finalize tw-h-10 tw-min-h-[2.5rem] tw-rounded-xl tw-bg-[var(--theme-800)] hover:tw-bg-[var(--theme-700)] active:tw-bg-[var(--theme-900)] tw-text-white tw-shadow-[0_2px_4px_rgba(0,0,0,0.06)] tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-5 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                            title="@lang('lang_v1.tooltip_checkout_multi_pay')">
                            <i class="fas fa-calculator" aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay')
                        </button>
                    @endif

                    @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="pos-express-finalize tw-h-10 tw-min-h-[2.5rem] tw-rounded-xl tw-bg-emerald-600 hover:tw-bg-emerald-500 active:tw-bg-emerald-700 tw-text-white tw-shadow-[0_2px_4px_rgba(0,0,0,0.06)] tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-5 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif"
                            data-pay_method="cash" title="@lang('tooltip.express_checkout')">
                            <i class="fas fa-bolt" aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')
                        </button>
                    @endif
                </div>
            </div>

            {{-- Recent Transactions Button (Desktop) --}}
            <div class="tw-w-full md:tw-w-fit tw-flex tw-flex-col tw-items-end tw-gap-3 tw-hidden md:tw-block">
                @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                    <button type="button"
                        class="tw-h-9 tw-min-h-[2.25rem] tw-rounded-xl tw-bg-slate-200 hover:tw-bg-slate-300 active:tw-bg-slate-400 tw-text-slate-700 tw-transition-all tw-duration-150 active:tw-scale-[0.97] tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-4.5 tw-text-xs md:tw-text-[13px] tw-font-bold tw-whitespace-nowrap"
                        data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions">
                        <i class="fas fa-history tw-text-slate-500"></i> @lang('lang_v1.recent_transactions')
                    </button>
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
