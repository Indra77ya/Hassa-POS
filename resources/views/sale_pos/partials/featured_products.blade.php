@foreach($featured_products as $variation)
	@php
		$enable_stock = !empty($variation->product) ? $variation->product->enable_stock : 0;
		$unit_short_name = '';
		if (!empty($variation->product) && !empty($variation->product->unit)) {
			$unit_short_name = $variation->product->unit->short_name;
		}
	@endphp
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-cursor-pointer tw-font-semibold tw-bg-white tw-rounded-lg tw-p-1 tw-border tw-border-[#fde68a] tw-shadow-[0_1px_3px_rgba(0,0,0,0.06)] tw-transition-all tw-duration-150 hover:-tw-translate-y-px hover:tw-shadow-[0_4px_12px_rgba(0,0,0,0.1)] active:tw-scale-[0.97] @if($enable_stock && $variation->qty_available <= 0) product_out_of_stock !tw-bg-[#f3f4f6] tw-opacity-60 @endif" data-toggle="tooltip" data-placement="bottom" data-variation_id="{{$variation->id}}" title="{{$variation->full_name}}">

		<div class="image-container tw-h-[58px] tw-mx-auto tw-w-full tw-mb-[3px]"
			style="background-image: url(
					@if(count($variation->media) > 0)
						{{$variation->media->first()->display_url}}
					@elseif(!empty($variation->product->image_url))
						{{$variation->product->image_url}}
					@else
						{{asset('/img/default.png')}}
					@endif
				);
			background-repeat: no-repeat; background-position: center;
			background-size: contain;">

		</div>

		<div class="text_div tw-mt-0.5">
			<small class="text text-muted tw-w-full tw-line-clamp-1 !tw-leading-[13px] tw-max-h-[13px] !tw-text-[11px]">{{$variation->product->name}}
			@if($variation->product->type == 'variable')
				- {{$variation->name}}
			@endif
			</small>

			<small class="text-muted">
				({{$variation->sub_sku}})
			</small><br>
			<small class="text-muted" style="font-size: 10px;">
				@if($enable_stock)
					{{ @num_format($variation->qty_available) }} {{$unit_short_name}} @lang('lang_v1.in_stock')
				@else
					--
				@endif
			</small><br>
			<span class="product_price !tw-text-[11px] tw-font-bold tw-text-[#15803d] tw-leading-[13px] tw-whitespace-nowrap tw-overflow-hidden tw-text-ellipsis">@format_currency($variation->sell_price_inc_tax)</span>
		</div>

		</div>
	</div>
@endforeach
