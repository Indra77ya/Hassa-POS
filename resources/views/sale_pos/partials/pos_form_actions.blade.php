@php
    $is_mobile = isMobile();
@endphp
<div class="row" style="margin: 0;">
    <div class="pos-form-actions" style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; z-index: 1000; background-color: #f4f5f7; border-top: 2px solid #d2d6de; padding: 8px 15px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
        <div class="row" style="margin: 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 5px;">

            <div class="pull-left" style="display: flex; align-items: center; flex-wrap: wrap; gap: 5px;">
                @if (empty($edit))
                    <button type="button" class="btn btn-danger btn-flat btn-sm js-pos-cancel">
                        <i class="fas fa-window-close"></i> @lang('sale.cancel')
                    </button>
                @else
                    <button type="button" class="btn btn-danger btn-flat btn-sm js-pos-delete" id="pos-delete" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-trash-alt"></i> @lang('messages.delete')
                    </button>
                @endif

                @if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button" class="btn bg-navy btn-flat btn-sm @if ($pos_settings['disable_draft'] != 0) hide @endif" id="pos-draft" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-edit"></i> @lang('sale.draft')
                    </button>
                @endif

                @if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button" class="btn bg-purple btn-flat btn-sm" id="pos-quotation" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-file-alt"></i> @lang('lang_v1.quotation')
                    </button>
                @endif

                @if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    @if (empty($pos_settings['disable_suspend']))
                        <button type="button" class="btn btn-warning btn-flat btn-sm no-print pos-express-finalize" data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')" @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-pause-circle" aria-hidden="true"></i> @lang('lang_v1.suspend')
                        </button>
                    @endif
                @endif

                @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    @if (empty($pos_settings['disable_credit_sale_button']))
                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                        <button type="button" class="btn bg-maroon btn-flat btn-sm no-print pos-express-finalize" data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')" @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-handshake" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
                        </button>
                    @endif
                @endif

                @if (!Gate::check('disable_card') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button" class="btn bg-orange btn-flat btn-sm no-print pos-express-finalize @if (!array_key_exists('card', $payment_types)) hide @endif" data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">
                        <i class="fas fa-credit-card" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')
                    </button>
                @endif

                @if (!empty($pos_settings['enable_midtrans']) && !empty($pos_settings['midtrans_client_key']))
                    <button type="button" class="btn btn-info btn-flat btn-sm no-print" id="pos-midtrans-pay-btn" title="Pay with Midtrans Snap">
                        <img src="{{ asset('img/midtrans_mark.png') }}" alt="Midtrans" style="height: 14px; width: auto; vertical-align: middle; display: inline-block;"> Midtrans
                    </button>
                @endif
            </div>

            <div class="pull-right" style="display: flex; align-items: center; flex-wrap: wrap; gap: 5px;">
                @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                    <button type="button" class="btn btn-default btn-flat btn-sm hidden-xs" data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions">
                        <i class="fas fa-history"></i> @lang('lang_v1.recent_transactions')
                    </button>
                @endif

                @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button" class="btn btn-primary btn-flat btn-sm pos-finalize no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif" title="@lang('lang_v1.tooltip_checkout_multi_pay')" style="font-weight: bold;">
                        <i class="fas fa-money-check-alt" aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay')
                    </button>
                @endif

                @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button" class="btn btn-success btn-flat btn-sm no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize" data-pay_method="cash" title="@lang('tooltip.express_checkout')" style="font-weight: bold;">
                        <i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')
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
