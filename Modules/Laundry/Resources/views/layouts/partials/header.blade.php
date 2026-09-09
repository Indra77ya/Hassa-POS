@if(!empty($__is_laundry_enabled))
    @if(auth()->check() && (auth()->user()->can('superadmin') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.view')))
        <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=laundry'}}" title="{{ __('laundry::lang.add_laundry_order') }}" data-toggle="tooltip" data-placement="bottom"
            class="tw-hidden sm:tw-inline-flex tw-transition-all tw-duration-200 tw-gap-2 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-py-1.5 tw-px-3 tw-rounded-lg tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-ring-1 tw-ring-white/10 tw-text-white hover:tw-text-white">
            <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 6a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-12z" />
                <path d="M8 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M16 9h.01" />
                <path d="M16 12h.01" />
            </svg>
            @lang('laundry::lang.laundry')
        </a>
    @endif
@endif
