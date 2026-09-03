<div class="row">
	<div class="col-md-12">
		<p class="label label-info" style="font-size: 13px; margin-bottom: 10px; display: inline-block;"><strong>@lang('sale.invoice_no'):</strong> {{$transaction->invoice_no}}</p>
	</div>
	<div class="col-md-4 col-sm-6">
		<div class="form-group" style="margin-bottom: 10px;">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user"></i>
				</span>
				<input type="hidden" id="default_customer_id" 
				value="{{ $transaction->contact->id }}" >
				<input type="hidden" id="default_customer_name" 
				value="{{ $transaction->contact->name }}" >
				<input type="hidden" id="default_customer_balance" 
				value="{{$transaction->contact->balance}}" >
				{!! Form::select('contact_id', 
					[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
				<span class="input-group-btn">
					<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name="" @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
				</span>
			</div>
			<small class="text-danger @if(empty($customer_due)) hide @endif contact_due_text" style="font-weight: bold; margin-top: 3px; display: block;"><strong>@lang('account.customer_due'):</strong> <span>{{$customer_due ?? ''}}</span></small>
		</div>
	</div>
	<div class="col-md-8 col-sm-6">
		<div class="form-group" style="margin-bottom: 10px;">
			<div class="input-group">
				<span class="input-group-btn">
					<button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
				</span>
				{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
				'autofocus' => true,
				]); !!}
				<span class="input-group-btn">
					<!-- Show button for weighing scale modal -->
					@if(isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
						<button type="button" class="btn btn-default bg-white btn-flat" id="weighing_scale_btn" data-toggle="modal" data-target="#weighing_scale_modal"
						title="@lang('lang_v1.weighing_scale')"><i class="fa fa-digital-tachograph fa-lg"></i></button>
					@endif

					<button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="row">
	@if(!empty($pos_settings['show_invoice_layout']))
	<div class="col-md-4 col-xs-6">
		<div class="form-group" style="margin-bottom: 10px;">
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
			<div class="form-group" style="margin-bottom: 10px;">
			{!! Form::select('commission_agent', 
						$commission_agent, $transaction->commission_agent, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent'), 'id' => 'commission_agent', 'required' => $is_commission_agent_required]); !!}
			</div>
		</div>
	@endif
	@if(!empty($pos_settings['enable_transaction_date']))
		<div class="col-md-4 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px;">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</span>
					{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required', 'id' => 'transaction_date']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(config('constants.enable_sell_in_diff_currency') == true)
		<div class="col-md-4 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px;">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-exchange-alt"></i>
					</span>
					{!! Form::text('exchange_rate', @num_format($transaction->exchange_rate), ['class' => 'form-control input-sm input_number', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(!empty($price_groups) && is_array($price_groups) && count($price_groups) > 1)
		<div class="col-md-4 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px;">
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
			$price_group_key = '';
			if (is_array($price_groups) && !empty($price_groups)) {
				reset($price_groups);
				$price_group_key = key($price_groups);
			}
		@endphp
		{!! Form::hidden('price_group', $price_group_key, ['id' => 'price_group']) !!}
	@endif
	@if(!empty($default_price_group_id))
		{!! Form::hidden('default_price_group', $default_price_group_id, ['id' => 'default_price_group']) !!}
	@endif

	@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
		<div class="col-md-4 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px;">
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
			<label style="cursor: pointer;">
              {!! Form::checkbox('is_recurring', 1, $transaction->is_recurring, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} &nbsp;@lang('lang_v1.subscribe')?
            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link" style="padding: 0 5px;"><i class="fa fa-external-link-square-alt text-info"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
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
			<div class="form-group" style="margin-bottom: 10px;">
				<div class="checkbox">
				<label style="cursor: pointer;">
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
<div class="row">
	<div class="col-sm-12 pos_product_div" style="min-height: 220px;">
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
		<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
			<thead>
				<tr>
					<th class="text-center @if(!empty($pos_settings['inline_service_staff'])) col-md-3 @else col-md-4 @endif">
						@lang('sale.product') @show_tooltip(__('lang_v1.tooltip_sell_product_column'))
					</th>
					<th class="text-center col-md-3">
						@lang('sale.qty')
					</th>
					@if(!empty($pos_settings['inline_service_staff']))
						<th class="text-center col-md-2">
							@lang('restaurant.service_staff')
						</th>
					@endif
					<th class="text-center col-md-2 {{$hide_tax}}">
						@lang('sale.price_inc_tax')
					</th>
					<th class="text-center col-md-2">
						@lang('sale.subtotal')
					</th>
					<th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
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
		</style>
	</div>
</div>
