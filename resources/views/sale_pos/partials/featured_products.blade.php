@foreach($featured_products as $variation)
	@php
		$enable_stock = !empty($variation->product) ? $variation->product->enable_stock : 0;
		$unit_short_name = '';
		if (!empty($variation->product) && !empty($variation->product->unit)) {
			$unit_short_name = $variation->product->unit->short_name;
		}
	@endphp
	<div class="col-md-3 col-xs-4 no-print" style="padding: 2px;">
		<div class="product_box text-center cursor-pointer @if($enable_stock && $variation->qty_available <= 0) product_out_of_stock @endif" data-toggle="tooltip" data-placement="bottom" data-variation_id="{{$variation->id}}" title="{{$variation->full_name}}" style="background: #fff; border: 1px solid #f39c12; border-radius: 4px; padding: 5px; margin-bottom: 5px; min-height: 120px;">

			<div class="image-container"
				style="background-image: url('{{ count($variation->media) > 0 ? $variation->media->first()->display_url : (!empty($variation->product->image_url) ? $variation->product->image_url : asset('/img/default.png')) }}');
				background-repeat: no-repeat; background-position: center;
				background-size: contain; height: 50px; width: 100%; margin: 0 auto 3px auto;">
			</div>

			<div class="text_div">
				<small class="text-muted" style="font-weight: bold; font-size: 11px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
					{{$variation->product->name}}
					@if($variation->product->type == 'variable')
						- {{$variation->name}}
					@endif
				</small>

				<small class="text-muted" style="font-size: 10px;">
					({{$variation->sub_sku}})
				</small><br>
				<small class="text-muted" style="font-size: 10px;">
					@if($enable_stock)
						{{ @num_format($variation->qty_available) }} {{$unit_short_name}} @lang('lang_v1.in_stock')
					@else
						--
					@endif
				</small><br>
				<span class="product_price text-success" style="font-weight: bold; font-size: 11px;">@format_currency($variation->sell_price_inc_tax)</span>
			</div>

		</div>
	</div>
@endforeach
