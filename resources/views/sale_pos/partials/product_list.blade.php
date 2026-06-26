@forelse($products as $product)
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-cursor-pointer tw-font-semibold tw-bg-white tw-rounded-lg tw-p-1 tw-border tw-border-[#e5e7eb] tw-shadow-[0_1px_3px_rgba(0,0,0,0.06)] tw-transition-all tw-duration-150 hover:-tw-translate-y-px hover:tw-shadow-[0_4px_12px_rgba(0,0,0,0.1)] active:tw-scale-[0.97] @if($product->enable_stock && $product->qty_available <= 0) product_out_of_stock !tw-bg-[#f3f4f6] tw-opacity-60 @endif" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}} @if(!empty($show_prices)) @lang('lang_v1.default') - @format_currency($product->selling_price) @foreach($product->group_prices as $group_price) @if(array_key_exists($group_price->price_group_id, $allowed_group_prices)) {{$allowed_group_prices[$group_price->price_group_id]}} - @format_currency($group_price->price_inc_tax) @endif @endforeach @endif">

		<div class="image-container tw-h-[58px] tw-mx-auto tw-w-full tw-mb-[3px]"
			style="background-image: url(
					@if(count($product->media) > 0)
						{{$product->media->first()->display_url}}
					@elseif(!empty($product->product_image))
						{{asset('/uploads/img/' . rawurlencode($product->product_image))}}
					@else
						{{asset('/img/default.png')}}
					@endif
				);
			background-repeat: no-repeat; background-position: center;
			background-size: contain;">

		</div>

		<div class="text_div tw-mt-0.5">
			<small class="text text-muted tw-w-full tw-line-clamp-1 !tw-leading-[13px] tw-max-h-[13px] !tw-text-[11px]">{{$product->name}}
			@if($product->type == 'variable')
				- {{$product->variation}}
			@endif
			</small>

			<small class="text-muted">
				({{$product->sub_sku}})
			</small><br>
			<small class="text-muted" style="font-size: 10px;">
				@if($product->enable_stock)
				{{ @num_format($product->qty_available) }} {{$product->unit}} @lang('lang_v1.in_stock')
				@else
					--
				@endif
			</small><br>
			@if(!empty($show_prices))
				<span class="product_price !tw-text-[11px] tw-font-bold tw-text-[#15803d] tw-leading-[13px] tw-whitespace-nowrap tw-overflow-hidden tw-text-ellipsis">@format_currency($product->selling_price)</span>
			@endif
		</div>

		</div>
	</div>
@empty
	<input type="hidden" id="no_products_found">
	<div class="col-md-12">
		<h4 class="text-center">
			@lang('lang_v1.no_products_to_display')
		</h4>
	</div>
@endforelse
