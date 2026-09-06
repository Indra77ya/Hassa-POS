@if(!empty($__is_laundry_enabled))
    @if(auth()->check() && (auth()->user()->can('superadmin') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.view')))
        <a
        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-w-auto tw-h-auto tw-py-1 tw-px-4 tw-rounded-md"
        href="{{ url('/pos/create?sub_type=laundry') }}" title="{{ __('laundry::lang.add_laundry_order') }}" data-toggle="tooltip" data-placement="bottom">
            <i class="fa fa-tshirt fa-lg !tw-text-sm"></i>
            <strong>@lang('laundry::lang.laundry')</strong>
        </a>
    @endif
@endif
