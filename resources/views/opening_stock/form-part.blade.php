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
									<th>@lang( 'lang_v1.selling_price' )</th>
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
			'selling_price' => $variation->sell_price_inc_tax,
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
	$selling_price = isset($var['selling_price']) ? $var['selling_price'] : $variation->sell_price_inc_tax;

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
				$unique_prefix = "stocks[{$key}][{$variation->id}][{$sub_key}]";
			@endphp
			<div class="tw-mt-2 text-left" style="background: #f8fafc; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
				<label style="font-size: 11px; margin-bottom: 4px;" class="text-primary">
					<i class="fa fa-barcode"></i> <strong>@lang('lang_v1.sn_details_per_unit')</strong>
				</label>
				<table class="table table-bordered table-condensed os_sn_table mb-0" style="background: #ffffff; font-size: 12px;" data-prefix="{{ $unique_prefix }}">
					<thead>
						<tr class="bg-gray">
							<th style="width: 40%;">@lang('lang_v1.serial_numbers') <span class="text-danger">*</span></th>
							<th style="width: 27%;">@lang('lang_v1.purchase_price') <span class="text-danger">*</span></th>
							<th style="width: 27%;">@lang('lang_v1.selling_price') <span class="text-danger">*</span></th>
							<th style="width: 6%;" class="text-center"><i class="fa fa-trash"></i></th>
						</tr>
					</thead>
					<tbody class="os_sn_tbody">
						@if($existing_sn_records->count() > 0)
							@foreach($existing_sn_records as $sn_idx => $sn_rec)
								<tr class="os_sn_row">
									<td>
										<input type="text" name="{{ $unique_prefix }}[sn_details][{{ $sn_idx }}][serial_number]" value="{{ $sn_rec->serial_number }}" class="form-control input-sm os_sn_input" placeholder="Enter Serial / IMEI" required>
									</td>
									<td>
										<input type="text" name="{{ $unique_prefix }}[sn_details][{{ $sn_idx }}][purchase_price]" value="{{ @num_format($sn_rec->purchase_price) }}" class="form-control input-sm input_number os_sn_pp_input" placeholder="@lang('lang_v1.purchase_price')" required>
									</td>
									<td>
										<input type="text" name="{{ $unique_prefix }}[sn_details][{{ $sn_idx }}][selling_price]" value="{{ @num_format($sn_rec->selling_price) }}" class="form-control input-sm input_number os_sn_sp_input" placeholder="@lang('lang_v1.selling_price')" required>
									</td>
									<td class="text-center">
										<button type="button" class="btn btn-xs btn-danger remove_os_sn_row"><i class="fa fa-trash"></i></button>
									</td>
								</tr>
							@endforeach
						@else
							<tr class="os_sn_row">
								<td>
									<input type="text" name="{{ $unique_prefix }}[sn_details][0][serial_number]" class="form-control input-sm os_sn_input" placeholder="Enter Serial / IMEI">
								</td>
								<td>
									<input type="text" name="{{ $unique_prefix }}[sn_details][0][purchase_price]" value="{{ @num_format($purcahse_price) }}" class="form-control input-sm input_number os_sn_pp_input" placeholder="@lang('lang_v1.purchase_price')">
								</td>
								<td>
									<input type="text" name="{{ $unique_prefix }}[sn_details][0][selling_price]" value="{{ @num_format($selling_price) }}" class="form-control input-sm input_number os_sn_sp_input" placeholder="@lang('lang_v1.selling_price')">
								</td>
								<td class="text-center">
									<button type="button" class="btn btn-xs btn-danger remove_os_sn_row"><i class="fa fa-trash"></i></button>
								</td>
							</tr>
						@endif
					</tbody>
				</table>
				<button type="button" class="btn btn-xs btn-primary tw-mt-1.5 add_os_sn_row">
					<i class="fa fa-plus"></i> @lang('lang_v1.add_sn_or_imei')
				</button>
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
<td>
	{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][selling_price]', @num_format($selling_price) , ['class' => 'form-control input-sm input_number selling_price']); !!}
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
	<td>&nbsp;</td>
			</tr>
		@endforeach
	@endforeach
								</tbody>
								<tfoot>
								<tr>
									<td colspan="@if($enable_expiry == 1 && $product->enable_stock == 1 && $enable_lot == 1) 6 @elseif(($enable_expiry == 1 && $product->enable_stock == 1) || $enable_lot == 1) 5 @else 4 @endif"></td>
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