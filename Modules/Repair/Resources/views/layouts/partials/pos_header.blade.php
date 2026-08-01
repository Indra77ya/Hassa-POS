@if($__is_repair_enabled)
	@can("repair.create")
		<a 
		class="tw-shadow-sm tw-bg-white hover:tw-bg-slate-50 tw-cursor-pointer tw-border tw-border-slate-200 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-h-9 tw-px-3.5 tw-text-xs tw-font-bold tw-text-[#009EE4] active:tw-scale-[0.97] tw-transition-all"
		href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=repair'}}" title="{{ __('repair::lang.add_repair') }}">
			<i class="fa fa-wrench tw-text-[#00a2e8] tw-text-xs tw-mr-1"></i>
			@lang('repair::lang.repair')
		</a>
	@endcan
@endif