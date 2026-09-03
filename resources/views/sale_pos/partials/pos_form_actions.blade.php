<div class="row">
	<div class="col-md-12">
		<div class="box box-solid bg-orange font-12 font-bold p-10 mb-10 text-center color-white hide" id="pos_max_qty_error">
			@lang('sale.max_qty_error')
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="box box-solid font-12 font-bold p-10 mb-10 text-center text-danger hide" id="pos_error_msg">
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12 text-center pos-express-btns">
		@if(!empty($pos_settings['enable_midtrans']) && !empty($pos_settings['midtrans_client_key']))
			<button type="button" class="btn bg-blue btn-default btn-flat no-print" id="pos-midtrans-pay-btn" title="Pay with Midtrans Snap">
				<img src="{{ asset('img/midtrans_mark.png') }}" alt="Midtrans" style="height: 16px; vertical-align: middle;"> Midtrans
			</button>
		@endif

		@if(empty($edit))
			<button type="button" class="btn btn-danger btn-flat js-pos-cancel">
				<i class="fas fa-window-close"></i> @lang('sale.cancel')
			</button>
		@else
			<button type="button" class="btn btn-danger btn-flat js-pos-delete hide" id="pos-delete" @if(!empty($only_payment)) disabled @endif>
				<i class="fas fa-trash-alt"></i> @lang('messages.delete')
			</button>
		@endif

		@if(!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			<button type="button" class="btn bg-purple btn-flat @if($pos_settings['disable_draft'] != 0) hide @endif" id="pos-draft" @if(!empty($only_payment)) disabled @endif>
				<i class="fas fa-edit"></i> @lang('sale.draft')
			</button>
		@endif

		@if(!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			<button type="button" class="btn bg-yellow btn-flat" id="pos-quotation" @if(!empty($only_payment)) disabled @endif>
				<i class="fas fa-file-alt"></i> @lang('lang_v1.quotation')
			</button>
		@endif

		@if(!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			@if(empty($pos_settings['disable_suspend']))
				<button type="button" class="btn bg-red btn-flat no-print pos-express-finalize" data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')" @if(!empty($only_payment)) disabled @endif>
					<i class="fas fa-pause-circle" aria-hidden="true"></i> @lang('lang_v1.suspend')
				</button>
			@endif
		@endif

		@if(!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			@if(empty($pos_settings['disable_credit_sale_button']))
				<input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
				<button type="button" class="btn bg-purple btn-flat no-print pos-express-finalize" data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')" @if(!empty($only_payment)) disabled @endif>
					<i class="fas fa-handshake" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
				</button>
			@endif
		@endif

		@if(!Gate::check('disable_card') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			<button type="button" class="btn bg-maroon btn-flat no-print pos-express-finalize @if(!array_key_exists('card', $payment_types)) hide @endif" data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">
				<i class="fas fa-credit-card" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')
			</button>
		@endif

		@if(!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			<button type="button" class="btn bg-navy btn-flat pos-finalize no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif" title="@lang('lang_v1.tooltip_checkout_multi_pay')">
				<i class="fas fa-money-check-alt" aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay')
			</button>
		@endif

		@if(!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
			<button type="button" class="btn btn-success btn-flat no-print @if($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize" data-pay_method="cash" title="@lang('tooltip.express_checkout')">
				<i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')
			</button>
		@endif

		@if(!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
			<button type="button" class="btn bg-olive btn-flat" data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions">
				<i class="fas fa-history"></i> @lang('lang_v1.recent_transactions')
			</button>
		@endif
	</div>
</div>

@if(isset($transaction))
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

@if(isset($transaction))
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
	@include('sale_pos.partials.edit_order_tax_modal', [
		'selected_tax' => $business_details->default_sales_tax,
	])
@endif

@include('sale_pos.partials.edit_shipping_modal')
