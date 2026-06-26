
<button target="_blank" class="tw-dw-btn tw-dw-btn-sm tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right btn-modal"
    data-href="{{action([\Modules\Repair\Http\Controllers\RepairStatusController::class, 'create'])}}" 
    data-container=".view_modal">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M12 5l0 14" />
        <path d="M5 12l14 0" />
    </svg> @lang('messages.add')
</button>
<br><br>
<table class="table table-bordered table-striped" id="status_table" style="width: 100%">
    <thead>
    <tr>
        <th>@lang( 'repair::lang.status_name' )</th>
        <th>@lang( 'repair::lang.color' )</th>
        <th>@lang( 'repair::lang.sort_order' )</th>
        <th>@lang( 'messages.action' )</th>
    </tr>
    </thead>
</table>
<div class="modal fade brands_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>