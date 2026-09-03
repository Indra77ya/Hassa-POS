@if($__is_repair_enabled)
	@can("repair.create")
		<a class="btn btn-info btn-flat btn-sm" href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=repair'}}" title="{{ __('repair::lang.add_repair') }}" data-toggle="tooltip" data-placement="bottom" style="font-weight: bold; height: 30px; display: inline-flex; align-items: center;">
			<i class="fa fa-wrench"></i> &nbsp;@lang('repair::lang.repair')
		</a>
	@endcan
@endif
