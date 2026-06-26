@if ($__is_essentials_enabled && $is_employee_allowed)


    <button type="button" data-type="clock_in" data-toggle="tooltip" data-placement="bottom" title="@lang('essentials::lang.clock_in')"
        class="@if (!empty($clock_in)) hide @endif clock_in_btn  tw-inline-flex tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-transition-all tw-duration-200 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-p-1.5 tw-rounded-lg tw-ring-1 hover:tw-text-white tw-ring-white/10">
        <span class="tw-sr-only">
            Clock In
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
            <path d="M7 11l5 5l5 -5" />
            <path d="M12 4l0 12" />
        </svg>
    </button>


    <button type="button"
        class="@if (empty($clock_in)) hide @endif clock_out_btn tw-inline-flex tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-transition-all tw-duration-200 tw-bg-primary-800 hover:tw-bg-primary-700 tw-p-1.5 tw-rounded-lg tw-ring-1 tw-ring-white/10 hover:tw-text-white"
        data-type="clock_out" data-toggle="popover" data-placement="bottom" data-html="true"
        title="@lang('essentials::lang.clock_out') @if (!empty($clock_in)) <br>
				<small>
					<b>@lang('essentials::lang.clocked_in_at'):</b> {{ @format_datetime($clock_in->clock_in_time) }}
				</small>
				<br>
				<small><b>@lang('essentials::lang.shift'):</b> {{ ucfirst($clock_in->shift_name) }}</small>
				@if (!empty($clock_in->start_time) && !empty($clock_in->end_time))
					<br>
					<small>
						<b>@lang('restaurant.start_time'):</b> {{ @format_time($clock_in->start_time) }}<br>
						<b>@lang('restaurant.end_time'):</b> {{ @format_time($clock_in->end_time) }}
					</small> @endif
			@endif">
        <span class="tw-sr-only">
            Clock In
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
            <path d="M7 11l5 5l5 -5" />
            <path d="M12 4l0 12" />
        </svg>
    </button>
@endif
