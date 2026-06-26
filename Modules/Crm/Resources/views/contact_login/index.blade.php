
<a  
	data-href="{{action([\Modules\Crm\Http\Controllers\ContactLoginController::class, 'create'])}}"
	class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full tw-dw-btn-sm pull-right contact-login-add">
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
		stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
		class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
		<path stroke="none" d="M0 0h24v24H0z" fill="none" />
		<path d="M12 5l0 14" />
		<path d="M5 12l14 0" />
	</svg> @lang('messages.add')
</a>
<br><br>
<div class="table-responsive">
	<table class="table table-bordered table-striped" id="contact_login_table" style="width: 100%;">
		<thead>
			<tr>
				<th>@lang('messages.action')</th>
				<th>@lang('business.username')</th>
                <th>@lang('user.name')</th>
                <th>@lang( 'business.email' )</th>
                <th>@lang( 'lang_v1.department' )</th>
                <th>@lang( 'lang_v1.designation' )</th>
			</tr>
		</thead>
	</table>
</div>
<!-- modal -->
<div class="modal fade contact_login_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>