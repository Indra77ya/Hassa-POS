@if($__is_repair_enabled)
	@can("repair.create")
		{{-- <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=repair'}}" title="{{ __('repair::lang.add_repair') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-8 btn-sm mt-10 pull-left">
			<i class="fa fa-wrench fa-lg"></i>
			<strong>@lang('repair::lang.repair')</strong>
		</a> --}}
		<a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=repair'}}" title="{{ __('repair::lang.add_repair') }}" data-toggle="tooltip" data-placement="bottom"
		class="tw-hidden sm:tw-inline-flex tw-transition-all tw-duration-200 tw-gap-2 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-py-1.5 tw-px-3 tw-rounded-lg tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-ring-1 tw-ring-white/10 tw-text-white hover:tw-text-white">
		<svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
			stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
			stroke-linejoin="round">
			<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
			<path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5">
			</path>
		</svg>
		@lang('repair::lang.repair')
	</a>
	@endcan
@endif