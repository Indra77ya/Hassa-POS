<div class="col-md-4 tw-mb-5 {{ $package->interval }} tw-relative price_card">
    <div
        class="tw-flex tw-flex-col tw-gap-6 tw-p-6 tw-shadow bg-white tw-rounded-2xl tw-shadow-lg tw-transition-all tw-duration-700 hover:tw-scale-110 tw-cursor-pointer">

        @if ($package->mark_package_as_popular == 1)
            <div class="tw-dw-badge tw-dw-badge-primary tw-self-center tw-badge-lg">
                @lang('superadmin::lang.popular')
            </div>
        @endif


        <div class="tw-flex tw-flex-col tw-text-center">
            <h2 class="md:tw-text-xl tw-text-base tw-text-[#1f1f1f]">{{ $package->name }}</h2>

            <h3
                class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-inline-block tw-text-transparent tw-bg-clip-text hover:tw-text-[#467BF5] tw-font-semibold tw-text-2xl md:tw-text-3xl">
                @php
                    $interval_type = !empty($intervals[$package->interval])
                        ? $intervals[$package->interval]
                        : __('lang_v1.' . $package->interval);
                @endphp
                @if ($package->price != 0)
                    <span class="display_currency" data-use_page_currency="true" data-currency_symbol="true">
                        {{ $package->price }}
                    </span>

                    {{-- <small class="text-gray-700"> --}}
                    / {{ $package->interval_count }} {{ $interval_type }}
                    {{-- </small> --}}
                @else
                    @lang('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . $interval_type])
                @endif
            </h3>

            <span class="tw-text-sm tw-text-gray-700">{{ $package->description }}</span>
        </div>

        <!-- Features -->
        <div class="tw-flex tw-flex-col tw-text-white">
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                @if ($package->location_count == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{ $package->location_count }}
                @endif

                @lang('business.business_locations')
            </div>
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                @if ($package->user_count == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{ $package->user_count }}
                @endif

                @lang('superadmin::lang.users')
            </div>
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                @if ($package->product_count == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{ $package->product_count }}
                @endif

                @lang('superadmin::lang.products')
            </div>
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                @if ($package->invoice_count == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{ $package->invoice_count }}
                @endif

                @lang('superadmin::lang.invoices')
            </div>

            @if (!empty($package->custom_permissions))
                @foreach ($package->custom_permissions as $permission => $value)
                    @isset($permission_formatted[$permission])
                        <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                            <i class="fa fa-check tw-text-accent"></i>
                            {{ $permission_formatted[$permission] }}
                        </div>
                    @endisset
                @endforeach
            @endif

            @if ($package->trial_days != 0)
                <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                    <i class="fa fa-check tw-text-accent"></i>
                    {{ $package->trial_days }} @lang('superadmin::lang.trial_days')
                </div>
            @endif
        </div>

        @if ($package->enable_custom_link == 1)
            <a href="{{ $package->custom_link }}"
                class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-tw-w-full tw-tw-max-w-full tw-mt-2 tw-flex tw-items-center tw-justify-center hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700">{{ $package->custom_link_text }}</a>
        @else
            @if (isset($action_type) && $action_type == 'register')
                <a href="{{ route('business.getRegister') }}?package={{ $package->id }}"
                    class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-tw-w-full tw-tw-max-w-full tw-mt-2 tw-flex tw-items-center tw-justify-center hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700">
                    @if ($package->price != 0)
                        @lang('superadmin::lang.register_subscribe')
                    @else
                        @lang('superadmin::lang.register_free')
                    @endif
                </a>
            @else
                <a href="{{ action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pay'], [$package->id]) }}"
                    class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-tw-w-full tw-tw-max-w-full tw-mt-2 tw-flex tw-items-center tw-justify-center hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700">
                    @if ($package->price != 0)
                        @lang('superadmin::lang.pay_and_subscribe')
                    @else
                        @lang('superadmin::lang.subscribe')
                    @endif
                </a>
            @endif
        @endif
    </div>
</div>
