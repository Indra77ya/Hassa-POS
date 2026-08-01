@forelse($products as $product)
	<div class="col-md-3 col-xs-4 no-print !tw-px-[4px] tw-mb-2">
		<div class="product_box tw-w-full tw-text-center tw-cursor-pointer tw-bg-white tw-rounded-xl tw-p-2 tw-border tw-border-slate-100 tw-shadow-[0_2px_8px_rgba(0,0,0,0.02)] tw-transition-all tw-duration-200 hover:-tw-translate-y-0.5 hover:tw-shadow-[0_6px_20px_rgba(99,102,241,0.08)] active:tw-scale-[0.97] @if($product->enable_stock && $product->qty_available <= 0) product_out_of_stock !tw-bg-slate-50 tw-opacity-65 @endif" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}} @if(!empty($show_prices)) @lang('lang_v1.default') - @format_currency($product->selling_price) @foreach($product->group_prices as $group_price) @if(array_key_exists($group_price->price_group_id, $allowed_group_prices)) {{$allowed_group_prices[$group_price->price_group_id]}} - @format_currency($group_price->price_inc_tax) @endif @endforeach @endif">

			<div class="image-container tw-h-16 tw-mx-auto tw-w-full tw-mb-2 tw-rounded-lg tw-overflow-hidden tw-bg-slate-50 tw-border tw-border-slate-100/50"
				style="background-image: url('{{ count($product->media) > 0 ? $product->media->first()->display_url : (!empty($product->product_image) ? asset('/uploads/img/' . rawurlencode($product->product_image)) : asset('/img/default.png')) }}');
				background-repeat: no-repeat; background-position: center;
				background-size: cover; height: 64px;">
			</div>

			<div class="text_div tw-mt-1">
				<div class="tw-text-slate-700 tw-font-bold tw-text-[12px] tw-line-clamp-1 tw-leading-tight tw-mb-0.5" style="max-height: 1.25rem;">
					{{$product->name}}
					@if($product->type == 'variable')
						- {{$product->variation}}
					@endif
				</div>

				<div class="tw-text-slate-400 tw-text-[10px] tw-font-medium tw-leading-none tw-mb-1">
					{{$product->sub_sku}}
				</div>

				<div class="tw-mb-1.5">
					@if($product->enable_stock)
						@if($product->qty_available <= 0)
							<span class="tw-inline-block tw-text-[9px] tw-font-bold tw-text-rose-600 tw-bg-rose-50 tw-px-1.5 tw-py-0.5 tw-rounded-md">@lang('lang_v1.out_of_stock')</span>
						@elseif($product->qty_available <= 5)
							<span class="tw-inline-block tw-text-[9px] tw-font-bold tw-text-amber-600 tw-bg-amber-50 tw-px-1.5 tw-py-0.5 tw-rounded-md">{{ @num_format($product->qty_available) }} {{$product->unit}}</span>
						@else
							<span class="tw-inline-block tw-text-[9px] tw-font-bold tw-text-indigo-600 tw-bg-indigo-50 tw-px-1.5 tw-py-0.5 tw-rounded-md">{{ @num_format($product->qty_available) }} {{$product->unit}}</span>
						@endif
					@else
						<span class="tw-inline-block tw-text-[9px] tw-font-bold tw-text-slate-400 tw-bg-slate-50 tw-px-1.5 tw-py-0.5 tw-rounded-md">Unlimited</span>
					@endif
				</div>

				@if(!empty($show_prices))
					<div class="product_price tw-text-[13px] tw-font-extrabold tw-text-emerald-600 tw-leading-tight">
						@format_currency($product->selling_price)
					</div>
				@endif
			</div>

		</div>
	</div>
@empty
	<input type="hidden" id="no_products_found">
	<div class="col-md-12">
		<div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-center tw-py-12 tw-px-6">
			<div class="tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-50 tw-flex tw-items-center tw-justify-center tw-text-slate-300 tw-mb-3">
				<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
			</div>
			<div class="tw-text-slate-600 tw-font-bold tw-text-sm">@lang('lang_v1.no_products_to_display')</div>
		</div>
	</div>
@endforelse
