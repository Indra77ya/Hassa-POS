<div class="pos_form_totals">

	<div class="pos_totals_left">

		{{-- Items --}}
		<div class="pos_totals_cell">
			<span class="pos_totals_label">@lang('sale.item')</span>
			<span class="pos_totals_value total_quantity">0</span>
		</div>

		{{-- Subtotal --}}
		<div class="pos_totals_cell">
			<span class="pos_totals_label">
				<span class="mobile-only">SUB</span>
				<span class="desktop-only">@lang('sale.subtotal')</span>
			</span>
			<span class="pos_totals_value price_total">0</span>
		</div>

		{{-- Discount --}}
		<div class="pos_totals_cell @if(Gate::check('disable_discount') && !auth()->user()->can('superadmin') && !auth()->user()->can('admin')) hide @endif">
			<span class="pos_totals_label">
				@if($is_discount_enabled)
					<span class="mobile-only">DISC(-)</span>
					<span class="desktop-only">@lang('sale.discount')(-) @show_tooltip(__('tooltip.sale_discount'))</span>
					@if($edit_discount)
						<i class="fas fa-edit pos_totals_edit" id="pos-edit-discount" title="@lang('sale.edit_discount')" aria-hidden="true" data-toggle="modal" data-target="#posEditDiscountModal"></i>
					@endif
				@endif
			</span>
			<span class="pos_totals_value pos_totals_value--danger" id="total_discount">0</span>
			<input type="hidden" name="discount_type" id="discount_type" value="@if(empty($edit)){{'percentage'}}@else{{$transaction->discount_type}}@endif" data-default="percentage">
			<input type="hidden" name="discount_amount" id="discount_amount" value="@if(empty($edit)) {{@num_format($business_details->default_sales_discount)}} @else {{@num_format($transaction->discount_amount)}} @endif" data-default="{{$business_details->default_sales_discount}}">
		</div>

		{{-- Loyalty --}}
		<div class="pos_totals_cell @if(!$is_rp_enabled) hide @endif">
			<span class="pos_totals_label">
				<span class="mobile-only">LOY</span>
				<span class="desktop-only">{{session('business.rp_name')}}</span>
			</span>
			<span class="pos_totals_value pos_totals_value--loyalty" id="loyalty_amount_display">0</span>
			<input type="hidden" name="rp_redeemed" id="rp_redeemed" value="@if(empty($edit)){{'0'}}@else{{$transaction->rp_redeemed}}@endif">
			<input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="@if(empty($edit)){{'0'}}@else {{$transaction->rp_redeemed_amount}} @endif">
		</div>

		{{-- Order Tax --}}
		<div class="pos_totals_cell @if($pos_settings['disable_order_tax'] != 0) hide @endif">
			<span class="pos_totals_label">
				<span class="mobile-only">TAX(+)</span>
				<span class="desktop-only">@lang('sale.order_tax')(+) @show_tooltip(__('tooltip.sale_tax'))</span>
				<i class="fas fa-edit pos_totals_edit" title="@lang('sale.edit_order_tax')" aria-hidden="true" data-toggle="modal" data-target="#posEditOrderTaxModal" id="pos-edit-tax"></i>
			</span>
			<span class="pos_totals_value" id="order_tax">@if(empty($edit)) 0 @else {{$transaction->tax_amount}} @endif</span>
			<input type="hidden" name="tax_rate_id" id="tax_rate_id" value="@if(empty($edit)) {{$business_details->default_sales_tax}} @else {{$transaction->tax_id}} @endif" data-default="{{$business_details->default_sales_tax}}">
			<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format($transaction->tax?->amount)}} @endif" data-default="{{$business_details->tax_calculation_amount}}">
		</div>

		{{-- Shipping --}}
		<div class="pos_totals_cell">
			<span class="pos_totals_label">
				<span class="mobile-only">SHIP(+)</span>
				<span class="desktop-only">@lang('sale.shipping')(+) @show_tooltip(__('tooltip.shipping'))</span>
				<i class="fas fa-edit pos_totals_edit" title="@lang('sale.shipping')" aria-hidden="true" data-toggle="modal" data-target="#posShippingModal"></i>
			</span>
			<span class="pos_totals_value" id="shipping_charges_amount">0</span>
			<input type="hidden" name="shipping_details" id="shipping_details" value="@if(empty($edit)){{''}}@else{{$transaction->shipping_details}}@endif" data-default="">
			<input type="hidden" name="shipping_address" id="shipping_address" value="@if(empty($edit)){{''}}@else{{$transaction->shipping_address}}@endif">
			<input type="hidden" name="shipping_status" id="shipping_status" value="@if(empty($edit)){{''}}@else{{$transaction->shipping_status}}@endif">
			<input type="hidden" name="delivered_to" id="delivered_to" value="@if(empty($edit)){{''}}@else{{$transaction->delivered_to}}@endif">
			<input type="hidden" name="delivery_person" id="delivery_person" value="@if(empty($edit)){{''}}@else{{$transaction->delivery_person}}@endif">
			<input type="hidden" name="shipping_charges" id="shipping_charges" value="@if(empty($edit)){{@num_format(0.00)}} @else{{@num_format($transaction->shipping_charges)}} @endif" data-default="0.00">
		</div>

		{{-- Packing charge --}}
		@if(in_array('types_of_service', $enabled_modules))
			<div class="pos_totals_cell">
				<span class="pos_totals_label">
					<span class="mobile-only">PACK(+)</span>
					<span class="desktop-only">@lang('lang_v1.packing_charge')(+)</span>
					<i class="fas fa-edit pos_totals_edit service_modal_btn"></i>
				</span>
				<span class="pos_totals_value" id="packing_charge_text">0</span>
			</div>
		@endif

		{{-- Round-off --}}
		@if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
			<div class="pos_totals_cell">
				<span class="pos_totals_label">
					<span class="mobile-only">RND</span>
					<span class="desktop-only">@lang('lang_v1.round_off')</span>
				</span>
				<span class="pos_totals_value" id="round_off_text">0</span>
				<input type="hidden" name="round_off_amount" id="round_off_amount" value=0>
			</div>
		@endif
	</div>

	{{-- Total Payable hero --}}
	<div class="pos_totals_right">
		<span class="pos_totals_right_label">@lang('sale.total_payable')</span>
		<span id="total_payable" class="pos_totals_right_value number">0</span>
		<input type="hidden" name="final_total" id="final_total_input" value="0.00">
	</div>
</div>

<style>
	.pos_form_totals {
		display: flex;
		flex-direction: column;
		margin: 0;
		background: #f8fafc;
		border-top: 1px solid #e2e8f0;
	}
	.pos_form_totals .pos_totals_left {
		display: flex;
		flex-wrap: wrap;
		gap: 1px;
		background: #e2e8f0;
		order: 1;
		min-width: 0;
	}
	.pos_form_totals .pos_totals_cell {
		flex: 1 1 calc(25% - 1px);
		min-width: calc(25% - 1px);
		max-width: 100%;
		background: #f8fafc;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 3px 4px;
		min-height: 30px;
		overflow: hidden;
		box-sizing: border-box;
	}
	.pos_form_totals .pos_totals_label {
		font-size: 9px;
		font-weight: 700;
		color: #94a3b8;
		text-transform: uppercase;
		letter-spacing: 0.3px;
		line-height: 1.1;
		text-align: center;
		max-width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.pos_form_totals .pos_totals_value {
		font-size: 12px;
		font-weight: 700;
		color: #0f172a;
		line-height: 1.2;
		letter-spacing: -0.2px;
		white-space: nowrap;
		font-variant-numeric: tabular-nums;
	}
	.pos_form_totals .pos_totals_value--danger { color: #dc2626; }
	.pos_form_totals .pos_totals_value--loyalty { color: #8b5cf6; }
	.pos_form_totals .pos_totals_edit {
		cursor: pointer;
		font-size: 9px;
		padding: 0 2px;
		margin-left: 1px;
		color: #3b82f6;
		vertical-align: middle;
		opacity: 0.8;
	}
	.pos_form_totals .pos_totals_edit:hover { opacity: 1; }
	.pos_form_totals .pos_totals_right {
		order: 2;
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		padding: 10px 12px;
		background: #ecfdf5;
		border-top: 1px solid #d1fae5;
	}
	.pos_form_totals .pos_totals_right_label {
		font-size: 11px;
		font-weight: 800;
		color: #065f46;
		text-transform: uppercase;
		letter-spacing: 0.8px;
		white-space: nowrap;
		line-height: 1.2;
	}
	.pos_form_totals .pos_totals_right_value {
		font-size: 19px;
		font-weight: 800;
		color: #047857;
		letter-spacing: -0.5px;
		white-space: nowrap;
		font-variant-numeric: tabular-nums;
		overflow-wrap: anywhere;
		line-height: 1.2;
	}
	.pos_form_totals .desktop-only { display: none; }
	.pos_form_totals .mobile-only { display: inline; }

	@media (max-width: 767px) {
		.pos_totals_right {
			margin-bottom: 25px !important;
		}
	}

	@media (min-width: 768px) {
		.pos_form_totals {
			flex-direction: row;
		}
		.pos_form_totals .pos_totals_left {
			flex: 1 1 70%;
		}
		.pos_form_totals .pos_totals_right {
			flex: 0 0 30%;
			flex-direction: column;
			justify-content: center;
			gap: 2px;
			padding: 6px 12px;
			min-height: 56px;
			border-top: none;
			border-left: 1px solid #d1fae5;
		}
		.pos_form_totals .pos_totals_cell {
			padding: 6px 8px;
			min-height: 44px;
		}
		.pos_form_totals .pos_totals_label {
			font-size: 10px;
			letter-spacing: 0.5px;
			line-height: 1.2;
			white-space: nowrap;
		}
		.pos_form_totals .pos_totals_value {
			font-size: 13px;
		}
		.pos_form_totals .pos_totals_right_label { font-size: 10px; }
		.pos_form_totals .pos_totals_right_value { font-size: 26px; }
		.pos_form_totals .desktop-only { display: inline; }
		.pos_form_totals .mobile-only { display: none; }
	}
</style>
