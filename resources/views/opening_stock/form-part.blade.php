<div class="row">
	<div class="col-sm-12">
		@forelse($locations as $key => $value)
		<div class="box box-solid">
			<div class="box-header">
	            <h3 class="box-title">@lang('sale.location'): {{$value}}</h3>
	        </div>
			<div class="box-body">
				<div class="row tw-overflow-scroll">
					<div class="col-sm-12">
						<table class="table table-condensed table-bordered text-center table-responsive table-striped add_opening_stock_table">
								<thead>
								<tr class="bg-green">
									<th>@lang( 'product.product_name' )</th>
									<th>@lang( 'lang_v1.quantity_left' )</th>
									<th>@lang( 'purchase.unit_cost_before_tax' )</th>
									@if($enable_expiry == 1 && $product->enable_stock == 1)
										<th>Exp. Date</th>
									@endif
									@if($enable_lot == 1)
										<th>@lang( 'lang_v1.lot_number' )</th>
									@endif
									<th>@lang( 'purchase.subtotal_before_tax' )</th>
									<th>@lang( 'lang_v1.date' )</th>
									<th>@lang( 'brand.note' )</th>
									<th>&nbsp;</th>
								</tr>
								</thead>
								<tbody>
@php
	$subtotal = 0;
@endphp
@foreach($product->variations as $variation)
	@if(empty($purchases[$key][$variation->id]))
		@php
			$purchases[$key][$variation->id][] = ['quantity' => 0, 
			'purchase_price' => $variation->default_purchase_price,
			'purchase_line_id' => null,
			'lot_number' => null,
			'transaction_date' => null,
			'purchase_line_note' => null,
			'secondary_unit_quantity' => 0
			]
		@endphp
	@endif

@foreach($purchases[$key][$variation->id] as $sub_key => $var)
	@php

	$purchase_line_id = $var['purchase_line_id'];

	$qty = $var['quantity'];

	$purcahse_price = $var['purchase_price'];

	$row_total = $qty * $purcahse_price;

	$subtotal += $row_total;
	$lot_number = $var['lot_number'];
	$transaction_date = $var['transaction_date'];
	$purchase_line_note = $var['purchase_line_note'];
	@endphp

<tr>
	<td>
		{{ $product->name }} @if( $product->type == 'variable' ) (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }}) @endif

		@if(!empty($purchase_line_id))
			{!! Form::hidden('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_line_id]', $purchase_line_id); !!}
		@endif

		@php
			$has_sr_no = (!empty($product->enable_sr_no) && $product->enable_sr_no == 1);
		@endphp
		@if($has_sr_no)
			@php
				$p_id = $product->id;
				$existing_sn_records = \App\ProductSerialNumber::where('business_id', session('user.business_id'))
					->where('product_id', $p_id)
					->where(function($q) use ($purchase_line_id) {
						$q->where('status', 'in_stock');
						if (!empty($purchase_line_id)) {
							$q->orWhere('purchase_line_id', $purchase_line_id);
						}
					})
					->get();
				$unique_row_id = $key . '_' . $variation->id . '_' . $sub_key;
			@endphp
			<br>
			<small class="text-primary"><strong>@lang('lang_v1.sn_input_help')</strong></small>
			<select name="stocks[{{$key}}][{{$variation->id}}][{{$sub_key}}][serial_numbers][]" class="form-control input-sm os_sn_select" multiple="multiple" style="width: 100%;" data-row_index="{{$unique_row_id}}">
				@foreach($existing_sn_records as $sn_rec)
					@php
						$is_this_line = !empty($purchase_line_id) && $sn_rec->purchase_line_id == $purchase_line_id;
						$is_unlinked = empty($sn_rec->purchase_line_id);
						$is_selected = $is_this_line || $is_unlinked;
					@endphp
					<option value="{{$sn_rec->serial_number}}"
							data-status="{{$sn_rec->status}}"
							data-purchase_price="{{@num_format($sn_rec->purchase_price)}}"
							data-purchase-price="{{@num_format($sn_rec->purchase_price)}}"
							data-selling_price="{{@num_format($sn_rec->selling_price)}}"
							data-selling-price="{{@num_format($sn_rec->selling_price)}}"
							@if($is_selected) selected="selected" @endif>
						{{$sn_rec->serial_number}}
					</option>
				@endforeach
			</select>
			<button type="button" class="btn btn-xs btn-default tw-mt-1.5 btn_sn_price_details" data-toggle="modal" data-target="#sn_price_modal_{{$unique_row_id}}">
				<i class="fa fa-cog text-info"></i> @lang('lang_v1.sn_price_details')
			</button>

			<!-- Modal for SN Price Details -->
			<div class="modal fade sn_price_modal" id="sn_price_modal_{{$unique_row_id}}" data-row_index="{{$unique_row_id}}" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">@lang('lang_v1.sn_price_modal_title') &ndash; <span class="text-primary">{{ $product->name }}@if($product->type == 'variable') - {{ $variation->product_variation->name }} : {{ $variation->name }}@endif</span></h4>
						</div>
						<div class="modal-body">
							<p class="help-block"><small>@lang('lang_v1.sn_price_modal_help')</small></p>
							<table class="table table-bordered table-condensed sn_price_table">
								<thead>
									<tr>
										<th>Serial Number / IMEI</th>
										<th>@lang('lang_v1.sn_purchase_price')</th>
										<th>@lang('lang_v1.sn_selling_price')</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">@lang('messages.save') & @lang('messages.close')</button>
						</div>
					</div>
				</div>
			</div>
		@endif
	</td>
	<td>
		<div class="input-group">
		  {!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][quantity]', @format_quantity($qty) , array_merge(['class' => 'form-control input-sm input_number purchase_quantity input_quantity', 'required'], $has_sr_no ? ['readonly' => 'readonly'] : [])); !!}
		  <span class="input-group-addon">
		    {{ $product->unit->short_name }}
		  </span>
		</div>
		@if(!empty($product->second_unit))
			<br>
            <span>
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit->short_name])*:</span><br>
            {!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][secondary_unit_quantity]', @format_quantity($var['secondary_unit_quantity']) , ['class' => 'form-control input-sm input_number input_quantity', 'required']); !!}
		@endif
	</td>
<td>
	{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_price]', @num_format($purcahse_price) , ['class' => 'form-control input-sm input_number unit_price', 'required']); !!}
</td>

@if($enable_expiry == 1 && $product->enable_stock == 1)
	<td>
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][exp_date]', !empty($var['exp_date']) ? @format_date($var['exp_date']) : null , ['class' => 'form-control input-sm os_exp_date', 'readonly']); !!}
	</td>
@endif

@if($enable_lot == 1)
	<td>
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][lot_number]', $lot_number , ['class' => 'form-control input-sm']); !!}
	</td>
@endif
	<td>
		<span class="row_subtotal_before_tax">{{@num_format($row_total)}}</span>
	</td>
	<td>
		<div class="input-group date">
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][transaction_date]', $transaction_date , ['class' => 'form-control input-sm os_date', 'readonly']); !!}
		</div>
	</td>
	<td>
		{!! Form::textarea('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_line_note]', $purchase_line_note , ['class' => 'form-control input-sm', 'rows' => 3 ]); !!}
	</td>
	<td>
		@if($loop->index == 0)
			<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary add_stock_row" data-sub-key="{{ count($purchases[$key][$variation->id])}}" 
				data-row-html='<tr>
					<td>
						{{ $product->name }} @if( $product->type == "variable" ) (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }}) @endif
					</td>
					<td>
					<div class="input-group">
	              		<input class="form-control input-sm input_number purchase_quantity" required="" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][quantity]" type="text" value="0">
			              <span class="input-group-addon">
			                {{ $product->unit->short_name }}
			              </span>
	        			</div>
					</td>
	<td>
		<input class="form-control input-sm input_number unit_price" required="" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][purchase_price]" type="text" value="{{@num_format($purcahse_price)}}">
	</td>

	@if($enable_expiry == 1 && $product->enable_stock == 1)
	<td>
		<input class="form-control input-sm os_exp_date" required="" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][exp_date]" type="text" readonly>
	</td>
	@endif

	@if($enable_lot == 1)
	<td>
		<input class="form-control input-sm" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][lot_number]" type="text">
	</td>
	@endif
	<td>
		<span class="row_subtotal_before_tax">
			0.00
		</span>
	</td>
	<td>
		<div class="input-group date">
			<input class="form-control input-sm os_date" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][transaction_date]" type="text" readonly>
		</div>
	</td>
	<td>
		<textarea rows="3" class="form-control input-sm" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][purchase_line_note]"></textarea>
	</td>
	<td>&nbsp;</td></tr>'
	><i class="fa fa-plus"></i></button>
	@else
		&nbsp;
	@endif
			</td>
			</tr>
		@endforeach
	@endforeach
								</tbody>
								<tfoot>
								<tr>
									<td colspan="@if($enable_expiry == 1 && $product->enable_stock == 1 && $enable_lot == 1) 5 @elseif(($enable_expiry == 1 && $product->enable_stock == 1) || $enable_lot == 1) @else 3 @endif"></td>
									<td><strong>@lang( 'lang_v1.total_amount_exc_tax' ): </strong> <span id="total_subtotal">{{@num_format($subtotal)}}</span>
									<input type="hidden" id="total_subtotal_hidden" value=0>
									</td>
								</tr>
								</tfoot>
						</table>
						
					</div>
				</div>
			</div>
		</div> <!--box end-->
		@empty
    		<h3>@lang( 'lang_v1.product_not_assigned_to_any_location' )</h3>
		@endforelse
	</div>
</div>