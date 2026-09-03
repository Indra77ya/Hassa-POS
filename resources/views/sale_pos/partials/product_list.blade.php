@forelse($products as $product)
	<div class="col-md-3 col-xs-4 no-print" style="padding: 2px;">
		<div class="product_box text-center cursor-pointer @if($product->enable_stock && $product->qty_available <= 0) product_out_of_stock @endif" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}} @if(!empty($show_prices)) @lang('lang_v1.default') - @format_currency($product->selling_price) @foreach($product->group_prices as $group_price) @if(array_key_exists($group_price->price_group_id, $allowed_group_prices)) {{$allowed_group_prices[$group_price->price_group_id]}} - @format_currency($group_price->price_inc_tax) @endif @endforeach @endif" style="background: #fff; border: 1px solid #d2d6de; border-radius: 4px; padding: 5px; margin-bottom: 5px; min-height: 130px; transition: all 0.2s;">

			<div class="image-container"
				style="background-image: url('{{ count($product->media) > 0 ? $product->media->first()->display_url : (!empty($product->product_image) ? asset('/uploads/img/' . rawurlencode($product->product_image)) : asset('/img/default.png')) }}');
				background-repeat: no-repeat; background-position: center;
				background-size: {{ count($product->media) > 0 || !empty($product->product_image) ? 'cover' : 'contain' }}; height: 60px; width: 100%; margin: 0 auto 5px auto;">
			</div>

			<div class="text_div">
				<small class="text-muted" style="font-weight: bold; font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
					{{$product->name}}
					@if($product->type == 'variable')
						- {{$product->variation}}
					@endif
				</small>

				<small class="text-muted" style="font-size: 10px;">
					{{$product->sub_sku}}
				</small>

				<div>
					@if($product->enable_stock)
						@if($product->qty_available <= 0)
							<span class="label label-danger" style="font-size: 9px;">@lang('lang_v1.out_of_stock')</span>
						@elseif($product->qty_available <= 5)
							<span class="label label-warning" style="font-size: 9px;">{{ @num_format($product->qty_available) }} {{$product->unit}}</span>
						@else
							<span class="label label-success" style="font-size: 9px;">{{ @num_format($product->qty_available) }} {{$product->unit}}</span>
						@endif
					@else
						<span class="label label-default" style="font-size: 9px;">Unlimited</span>
					@endif
				</div>

				@if(!empty($show_prices))
					<div class="product_price text-success" style="font-weight: bold; font-size: 12px; margin-top: 2px;">
						@format_currency($product->selling_price)
					</div>
				@endif
			</div>

		</div>
	</div>
@empty
	<input type="hidden" id="no_products_found">
	<div class="col-md-12 text-center text-muted" style="padding: 30px 10px;">
		<i class="fa fa-info-circle fa-2x" style="color: #ccc; margin-bottom: 5px;"></i>
		<p style="font-weight: bold; font-size: 13px;">@lang('lang_v1.no_products_to_display')</p>
	</div>
@endforelse
