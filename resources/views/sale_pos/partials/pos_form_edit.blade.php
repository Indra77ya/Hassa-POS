<div class="row">
	<div class="col-md-12">
		<p class="tw-text-xs tw-font-bold tw-text-indigo-600 tw-bg-indigo-50 tw-inline-block tw-py-1 tw-px-3 tw-rounded-full tw-mb-3"><strong>@lang('sale.invoice_no'):</strong> {{$transaction->invoice_no}}</p>
	</div>
	<div class="col-md-4">
		<div class="form-group tw-mb-3" style="width: 100% !important">
			<div class="input-group tw-border tw-border-slate-200 tw-rounded-xl tw-overflow-hidden tw-shadow-sm tw-transition-all focus-within:tw-border-indigo-400 focus-within:tw-ring-1 focus-within:tw-ring-indigo-400/20" style="display: table; width: 100%;">
				<span class="input-group-addon !tw-bg-slate-50 !tw-border-0 !tw-text-slate-400 !tw-px-3">
					<i class="fa fa-user"></i>
				</span>
				<input type="hidden" id="default_customer_id" 
				value="{{ $transaction->contact->id }}" >
				<input type="hidden" id="default_customer_name" 
				value="{{ $transaction->contact->name }}" >
				<input type="hidden" id="default_customer_balance" 
				value="{{$transaction->contact->balance}}" >
				{!! Form::select('contact_id', 
					[], null, ['class' => 'form-control mousetrap !tw-border-0 !tw-shadow-none !tw-h-9 !tw-text-xs !tw-bg-transparent focus:tw-outline-none', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'height: 36px; width: 100%;']); !!}
				<span class="input-group-btn" style="width: auto;">
					<button type="button" class="btn btn-default bg-white btn-flat add_new_customer !tw-border-0 !tw-h-9 !tw-text-indigo-600 hover:!tw-bg-indigo-50/50 active:tw-scale-95 tw-transition-transform" data-name="" style="border: 0;" @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle fa-lg"></i></button>
				</span>
			</div>
			<small class="text-danger @if(empty($customer_due)) hide @endif contact_due_text tw-text-[11px] tw-font-bold tw-mt-1 tw-block"><strong>@lang('account.customer_due'):</strong> <span>{{$customer_due ?? ''}}</span></small>
		</div>
	</div>
	<div class="col-md-8">
		<div class="form-group tw-mb-3">
			<div class="input-group tw-border tw-border-slate-200 tw-rounded-xl tw-overflow-hidden tw-shadow-sm tw-transition-all focus-within:tw-border-indigo-400 focus-within:tw-ring-1 focus-within:tw-ring-indigo-400/20" style="display: table; width: 100%;">
				<div class="input-group-btn" style="width: auto;">
					<button type="button" class="btn btn-default bg-white btn-flat !tw-border-0 !tw-h-9 !tw-text-slate-400 hover:!tw-bg-slate-50" style="border: 0;" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
				</div>
                {{-- Removed mousetrap class as it was causing issue with barcode scanning --}}
				{!! Form::text('search_product', null, ['class' => 'form-control !tw-border-0 !tw-shadow-none !tw-h-9 !tw-text-xs !tw-bg-transparent focus:tw-outline-none', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
				'autofocus' => true,
				'style' => 'height: 36px;'
				]); !!}
				<span class="input-group-btn" style="width: auto;">

					<!-- Show button for weighing scale modal -->
					@if(isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
						<button type="button" class="btn btn-default bg-white btn-flat !tw-border-0 !tw-h-9 !tw-text-indigo-600 hover:!tw-bg-indigo-50/50 active:tw-scale-95 tw-transition-transform" style="border: 0;" id="weighing_scale_btn" data-toggle="modal" data-target="#weighing_scale_modal"
						title="@lang('lang_v1.weighing_scale')"><i class="fa fa-digital-tachograph fa-lg"></i></button>
					@endif

					<button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product !tw-border-0 !tw-h-9 !tw-text-indigo-600 hover:!tw-bg-indigo-50/50 active:tw-scale-95 tw-transition-transform" style="border: 0;" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle fa-lg"></i></button>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="row">
	@if(!empty($pos_settings['show_invoice_layout']))
	<div class="col-md-4 col-xs-6">
		<div class="form-group tw-mb-3">
		{!! Form::select('invoice_layout_id', 
					$invoice_layouts, $transaction->location->invoice_layout_id, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_invoice_layout'), 'id' => 'invoice_layout_id']); !!}
		</div>
	</div>
	@endif
	<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$transaction->pay_term_number}}">
	<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$transaction->pay_term_type}}">
	
	@if(!empty($commission_agent))
		@php
			$is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
		@endphp
		<div class="col-md-4 col-xs-6">
			<div class="form-group tw-mb-3">
			{!! Form::select('commission_agent', 
						$commission_agent, $transaction->commission_agent, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent'), 'id' => 'commission_agent', 'required' => $is_commission_agent_required]); !!}
			</div>
		</div>
	@endif
	@if(!empty($pos_settings['enable_transaction_date']))
		<div class="col-md-4 col-sm-6">
			<div class="form-group tw-mb-3">
				<div class="input-group tw-border tw-border-slate-200 tw-rounded-xl tw-overflow-hidden tw-shadow-sm tw-transition-all focus-within:tw-border-indigo-400" style="display: table; width: 100%;">
					<span class="input-group-addon !tw-bg-slate-50 !tw-border-0 !tw-text-slate-400 !tw-px-3">
						<i class="fa fa-calendar"></i>
					</span>
					{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control !tw-border-0 !tw-shadow-none !tw-h-9 !tw-text-xs !tw-bg-transparent focus:tw-outline-none', 'readonly', 'required', 'id' => 'transaction_date', 'style' => 'height: 36px;']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(config('constants.enable_sell_in_diff_currency') == true)
		<div class="col-md-4 col-sm-6">
			<div class="form-group tw-mb-3">
				<div class="input-group tw-border tw-border-slate-200 tw-rounded-xl tw-overflow-hidden tw-shadow-sm" style="display: table; width: 100%;">
					<span class="input-group-addon !tw-bg-slate-50 !tw-border-0 !tw-text-slate-400 !tw-px-3">
						<i class="fas fa-exchange-alt"></i>
					</span>
					{!! Form::text('exchange_rate', @num_format($transaction->exchange_rate), ['class' => 'form-control input-sm input_number !tw-border-0 !tw-shadow-none !tw-h-9 !tw-text-xs', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate', 'style' => 'height: 36px;']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(!empty($price_groups) && count($price_groups) > 1)
		<div class="col-md-4 col-sm-6">
			<div class="form-group tw-mb-3">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-money-bill-alt"></i>
					</span>
					@php
						reset($price_groups);
						$selected_price_group = !empty($transaction->price_group_id) && array_key_exists($transaction->price_group_id, $price_groups) ? $transaction->price_group_id : null;
					@endphp
					{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
					{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.price_group_help_text'))
					</span>
				</div>
			</div>
		</div>
	@else
		@php
			reset($price_groups);
		@endphp
		{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
	@endif
	@if(!empty($default_price_group_id))
		{!! Form::hidden('default_price_group', $default_price_group_id, ['id' => 'default_price_group']) !!}
	@endif

	@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
		<div class="col-md-4 col-sm-6">
			<div class="form-group tw-mb-3">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
					</span>
					{!! Form::select('types_of_service_id', $types_of_service, $transaction->types_of_service_id, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.select_types_of_service')]); !!}

					{!! Form::hidden('types_of_service_price_group', $transaction->types_of_service_price_group, ['id' => 'types_of_service_price_group']) !!}

					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.types_of_service_help'))
					</span> 
				</div>
				<small><p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p></small>
			</div>
		</div>
		<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
	@endif

	@if(in_array('subscription', $enabled_modules))
		<div class="col-md-4 col-sm-6">
			<label class="tw-text-xs tw-text-slate-600 tw-font-bold tw-cursor-pointer">
              {!! Form::checkbox('is_recurring', 1, $transaction->is_recurring, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} &nbsp;@lang('lang_v1.subscribe')?
            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link !tw-p-0 tw-ml-1"><i class="fa fa-external-link-square-alt tw-text-indigo-500"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
		</div>
	@endif

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
	<span id="restaurant_module_span">
      		<div class="col-md-3"></div>
    	</span>
    @endif

	@if(in_array('kitchen' ,$enabled_modules))
		<div class="col-md-3">
			<div class="form-group tw-mb-3">
				<div class="checkbox">
				<label class="tw-text-xs tw-text-slate-600 tw-font-bold tw-cursor-pointer">
						{!! Form::checkbox('is_kitchen_order', 1, $transaction->is_kitchen_order, ['class' => 'input-icheck status', 'id' => 'is_kitchen_order']); !!} &nbsp;{{ __('lang_v1.kitchen_order') }}
				</label>
				@show_tooltip(__('lang_v1.kitchen_order_tooltip'))
				</div>
			</div>
		</div>
    @endif

</div>
<!-- include module fields -->
@if(!empty($pos_module_data))
    @foreach($pos_module_data as $key => $value)
        @if(!empty($value['view_path']))
            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
        @endif
    @endforeach
@endif
<div class="row" style="margin:0;">
	<div class="col-sm-12 pos_product_div" style="padding:4px 0 0 0;">
		<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

		<!-- Keeps count of product rows -->
		<input type="hidden" id="product_row_count" 
			value="{{count($sell_details)}}">
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0){
				$hide_tax = 'hide';
			}
		@endphp
		<table class="table table-condensed table-responsive tw-border-0 tw-table-fixed" id="pos_table">
			<thead>
				<tr>
					<th class="text-left pos-th-product tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#64748b] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-4 !tw-py-2 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[40%]">
						@lang('sale.product') @show_tooltip(__('lang_v1.tooltip_sell_product_column'))
					</th>
					<th class="text-center pos-th-qty tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#64748b] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-4 !tw-py-2 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[22%]">
						@lang('sale.qty')
					</th>
					@if(!empty($pos_settings['inline_service_staff']))
						<th class="text-center pos-th-staff tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#64748b] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-4 !tw-py-2 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle">
							@lang('restaurant.service_staff')
						</th>
					@endif
					<th class="text-center pos-th-price tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#64748b] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-4 !tw-py-2 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-auto tw-min-w-[15%] {{$hide_tax}}">
						@lang('sale.price_inc_tax')
					</th>
					<th class="text-right pos-th-subtotal tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#64748b] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-4 !tw-py-2 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-auto tw-min-w-[15%]">
						@lang('sale.subtotal')
					</th>
					<th class="pos-th-action tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-py-2 !tw-pr-4 !tw-pl-0 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[56px] !tw-text-center"></th>
				</tr>
			</thead>
			<tbody>
				@foreach($sell_details as $sell_line)
					@include('sale_pos.product_row',
						['product' => $sell_line,
						'row_count' => $loop->index,
						'tax_dropdown' => $taxes,
						'sub_units' => !empty($sell_line->unit_details) ? $sell_line->unit_details : [],
						'action' => 'edit'
					])
				@endforeach
			</tbody>
		</table>
		<style>
			#pos_table:not(.pos-has-rows) thead { display: none !important; }
			#pos_table.pos-has-rows .pos-empty-state-row { display: none !important; }
			#add_pos_sell_form:not(.pos-has-rows) .pos_form_totals,
			#edit_pos_sell_form:not(.pos-has-rows) .pos_form_totals { display: none !important; }

			#pos_table:not(.pos-has-rows) {
				height: 100% !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
				width: 100% !important;
			}
			#pos_table:not(.pos-has-rows) tbody {
				height: 100% !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
				width: 100% !important;
				border: 0 !important;
			}
			#pos_table:not(.pos-has-rows) .pos-empty-state-row {
				height: 100% !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
				width: 100% !important;
				background: transparent !important;
			}
			#pos_table:not(.pos-has-rows) .pos-empty-state-row td {
				width: 100% !important;
				height: 100% !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
				background: transparent !important;
				border: 0 !important;
			}
			#pos_table .pos-empty-state-row:hover,
			#pos_table .pos-empty-state-row td:hover,
			#pos_table .pos-empty-state-row:hover td {
				background-color: transparent !important;
				background: transparent !important;
			}
		</style>
	</div>
</div>