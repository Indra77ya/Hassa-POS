@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

    <div class="tw-pb-6 theme-header-bg xl:tw-pb-0">
        <div class="tw-px-5 tw-pt-3">
                    <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between sm:tw-gap-12">
                        <div class="tw-mt-2 sm:tw-w-1/2 md:tw-w-1/2">
                            <h1
                                class="tw-text-2xl md:tw-text-4xl tw-tracking-tight tw-font-bold tw-text-white tw-mb-2 md:tw-mb-0">
                                {{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}
                            </h1>
                        </div>
    
                        @if (auth()->user()->can('dashboard.data'))
                            @if ($is_admin)
                                <div class="tw-mt-2 sm:tw-w-1/3 md:tw-w-1/4 ">
                                    @if (count($all_locations) > 1)
                                        {!! Form::select('dashboard_location', $all_locations, null, [
                                            'class' => 'form-control select2',
                                            'placeholder' => __('lang_v1.select_location'),
                                            'id' => 'dashboard_location',
                                        ]) !!}
                                    @endif
                                </div>
            
                                <div class="tw-mt-2 sm:tw-w-1/3 md:tw-w-1/4 tw-text-right">
                                    @if ($is_admin)
                                        <button type="button" id="dashboard_date_filter"
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-full tw-gap-1 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-900 tw-transition-all tw-duration-200 tw-bg-white tw-rounded-lg sm:tw-w-auto hover:tw-bg-primary-50">
                                            <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                <path d="M16 3v4" />
                                                <path d="M8 3v4" />
                                                <path d="M4 11h16" />
                                                <path d="M7 14h.013" />
                                                <path d="M10.01 14h.005" />
                                                <path d="M13.01 14h.005" />
                                                <path d="M16.015 14h.005" />
                                                <path d="M13.015 17h.005" />
                                                <path d="M7.01 17h.005" />
                                                <path d="M10.01 17h.005" />
                                            </svg>
                                            <span>
                                                {{ __('messages.filter_by_date') }}
                                            </span>
                                            <svg aria-hidden="true" class="tw-size-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M6 9l6 6l6 -6" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="tw-mt-3 sm:tw-mt-0 tw-inline-flex tw-items-center tw-gap-2 tw-bg-white/10 tw-backdrop-blur-md tw-px-4 tw-py-2 tw-rounded-xl tw-text-white tw-text-sm tw-font-medium">
                                <svg class="tw-w-5 tw-h-5 tw-text-white/80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span>{{ \Carbon\Carbon::now()->format('d F Y') }}</span>
                            </div>
                        @endif
                    </div>
                    @if (auth()->user()->can('dashboard.data'))
                        @if ($is_admin)
                            <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            
                                <div
                                    class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl  tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0 tw-bg-sky-100 tw-text-sky-500">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 17h-11v-14h-2" />
                                                    <path d="M6 5l14 1l-1 7h-13" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                    {{ __('home.total_sell') }}
                                                </p>
                                                <p
                                                    class="total_sell tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path
                                                        d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                                    </path>
                                                    <path
                                                        d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                                    </path>
                                                    <path d="M12 6v10"></path>
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                    {{ __('lang_v1.net') }} @show_tooltip(__('lang_v1.net_home_tooltip'))
                                                </p>
                                                <p
                                                    class="net tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                    <path d="M9 7l1 0" />
                                                    <path d="M9 13l6 0" />
                                                    <path d="M13 17l2 0" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                    {{ __('home.invoice_due') }}
                                                </p>
                                                <p
                                                    class="invoice_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M21 7l-18 0" />
                                                    <path d="M18 10l3 -3l-3 -3" />
                                                    <path d="M6 20l-3 -3l3 -3" />
                                                    <path d="M3 17l18 0" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                    {{ __('lang_v1.total_sell_return') }}
                                                    <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true" data-container="body"
                                                    data-toggle="popover" data-placement="auto bottom" id="total_srp"
                                                    data-value="{{ __('lang_v1.total_sell_return') }}-{{ __('lang_v1.total_sell_return_paid') }}"
                                                    data-content="" data-html="true" data-trigger="hover"></i>
                                                </p>
                                                <p
                                                    class="total_sell_return tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                                </p>
                                                {{-- <p class="mb-0 text-muted fs-10 mt-5">{{ __('lang_v1.total_sell_return') }}: <span
                                                        class="total_sr"></span><br>
                                                    {{ __('lang_v1.total_sell_return_paid') }}<span class="total_srp"></span></p> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
              
        </div>
        @if (auth()->user()->can('dashboard.data'))
            @if ($is_admin)
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-0 tw-grid" aria-hidden="true">
                        <div class="theme-header-bg"></div>
                        <div class="tw-hidden sm:tw-block tw-bg-gray-100"></div>
                    </div>
                    <div class="tw-px-5 tw-isolate">
                        <div
                            class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-4 sm:tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0 bg-sky-100 tw-text-sky-500">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M12 3v12"></path>
                                                <path d="M16 11l-4 4l-4 -4"></path>
                                                <path d="M3 12a9 9 0 0 0 18 0"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('home.total_purchase') }}
                                            </p>
                                            <p
                                                class="total_purchase tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 9v4" />
                                                <path
                                                    d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                                <path d="M12 16h.01" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500">
                                                {{ __('home.purchase_due') }}
                                            </p>
                                            <p
                                                class="purchase_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                                <path d="M15 14v-2a2 2 0 0 0 -2 -2h-4l2 -2m0 4l-2 -2" />
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.total_purchase_return') }}
                                                <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true" data-container="body"
                                                data-toggle="popover" data-placement="auto bottom" id="total_prp"
                                                data-value="{{ __('lang_v1.total_purchase_return') }}-{{ __('lang_v1.total_purchase_return_paid') }}"
                                                data-content="" data-html="true" data-trigger="hover"></i>
                                            </p>
                                            <p
                                                class="total_purchase_return tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                            {{-- <p class="mb-0 text-muted fs-10 mt-5">
                                                {{ __('lang_v1.total_purchase_return') }}: <span
                                                    class="total_pr"></span><br>
                                                {{ __('lang_v1.total_purchase_return_paid') }}<span
                                                    class="total_prp"></span></p> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                                </path>
                                                <path
                                                    d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                                </path>
                                                <path d="M12 6v10"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.expense') }}
                                            </p>
                                            <p
                                                class="total_expense tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- @if (!empty($widgets['after_sale_purchase_totals']))
                    @foreach ($widgets['after_sale_purchase_totals'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            @endif
        @endif
    </div>
    @if (auth()->user()->can('dashboard.data'))
        <div class="tw-px-5 tw-py-6">
            <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    @if (!empty($all_locations))
                        <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                        <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17h-11v-14h-2"></path>
                                            <path d="M6 5l14 1l-1 7h-13"></path>
                                        </svg>
                                    </div>

                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        {{ __('home.sells_last_30_days') }}
                                    </h3>
                                </div>
                                <div class="tw-mt-5">
                                    <div
                                        class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                        <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                            {!! $sells_chart_1->container() !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- @if (!empty($widgets['after_sales_last_30_days']))
                        @foreach ($widgets['after_sales_last_30_days'] as $widget)
                            {!! $widget !!}
                        @endforeach
                    @endif --}}
                    @if (!empty($all_locations))
                        <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                        <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17h-11v-14h-2"></path>
                                            <path d="M6 5l14 1l-1 7h-13"></path>
                                        </svg>
                                    </div>
                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        {{ __('home.sells_current_fy') }}
                                    </h3>
                                </div>
                                <div class="tw-mt-5">
                                    <div
                                        class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                        <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                            {!! $sells_chart_2->container() !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                {{-- @if (!empty($widgets['after_sales_current_fy']))
                    @foreach ($widgets['after_sales_current_fy'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.sales_payment_dues') }}
                                            @show_tooltip(__('lang_v1.tooltip_sales_payment_dues'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        {!! Form::select('sales_payment_dues_location', $all_locations, null, [
                                            'class' => 'form-control select2',
                                            'placeholder' => __('lang_v1.select_location'),
                                            'id' => 'sales_payment_dues_location',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="sales_payment_dues_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th style="white-space: nowrap !important;">@lang('contact.customer')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.invoice_no')</th>
                                                    <th style="white-space: nowrap !important;">@lang('home.due_amount')</th>
                                                    <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @can('purchase.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.purchase_payment_dues') }}
                                            @show_tooltip(__('tooltip.payment_dues'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('purchase_payment_dues_location', $all_locations, null, [
                                                'class' => 'form-control select2 ',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'purchase_payment_dues_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="purchase_payment_dues_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th style="white-space: nowrap !important;">@lang('purchase.supplier')</th>
                                                    <th style="white-space: nowrap !important;">@lang('purchase.ref_no')</th>
                                                    <th style="white-space: nowrap !important;">@lang('home.due_amount')</th>
                                                    <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('stock_report.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('home.product_stock_alert') }}
                                            @show_tooltip(__('tooltip.product_stock_alert'))
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('stock_alert_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'stock_alert_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="stock_alert_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th style="white-space: nowrap !important;">@lang('sale.product')</th>
                                                    <th style="white-space: nowrap !important;">@lang('business.location')</th>
                                                    <th style="white-space: nowrap !important;">@lang('report.current_stock')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (session('business.enable_product_expiry') == 1)
                        <div
                            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                        <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M12 9v4"></path>
                                            <path
                                                d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                            </path>
                                            <path d="M12 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                        <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                            <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                                {{ __('home.stock_expiry_alert') }}
                                                @show_tooltip(
                                                __('tooltip.stock_expiry_alert', [
                                                'days'
                                                =>session('business.stock_expiry_alert_days', 30) ]) )
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <input type="hidden" id="stock_expiry_alert_days"
                                                value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
                                            <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('business.product')</th>
                                                        <th>@lang('business.location')</th>
                                                        <th>@lang('report.stock_left')</th>
                                                        <th>@lang('product.expires_in')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
                @if (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            {{ __('lang_v1.sales_order') }}
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('so_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'so_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        @php
                                            $so_custom_labels = json_decode(session('business.custom_labels'), true);
                                        @endphp
                                        <table class="table table-bordered table-striped ajax_view"
                                            id="sales_order_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
                                                    <th style="white-space: nowrap !important;">@lang('messages.date')</th>
                                                    <th style="white-space: nowrap !important;">@lang('restaurant.order_no')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.customer_name')</th>
                                                    <th style="white-space: nowrap !important;">@lang('lang_v1.contact_no')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.location')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.status')</th>
                                                    <th style="white-space: nowrap !important;">@lang('lang_v1.shipping_status')</th>
                                                    <th style="white-space: nowrap !important;">@lang('lang_v1.quantity_remaining')</th>
                                                    <th style="white-space: nowrap !important;">{{ $so_custom_labels['sell']['custom_field_1'] ?? '' }}</th>
                                                    <th style="white-space: nowrap !important;">{{ $so_custom_labels['sell']['custom_field_2'] ?? '' }}</th>
                                                    <th style="white-space: nowrap !important;">{{ $so_custom_labels['sell']['custom_field_3'] ?? '' }}</th>
                                                    <th style="white-space: nowrap !important;">{{ $so_custom_labels['sell']['custom_field_4'] ?? '' }}</th>
                                                    <th style="white-space: nowrap !important;">@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($common_settings['enable_purchase_requisition']) &&
                        (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own')))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M10 10v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1 -1v-4"></path>
                                        <path d="M9 6h6"></path>
                                        <path d="M10 6v-2a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2"></path>
                                        <circle cx="12" cy="16" r="2"></circle>
                                        <path d="M5 20h14a2 2 0 0 0 2 -2v-10"></path>
                                        <path d="M15 16v4"></path>
                                        <path d="M9 20v-4"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.purchase_requisition')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            @if (count($all_locations) > 1)
                                                {!! Form::select('pr_location', $all_locations, null, [
                                                    'class' => 'form-control select2',
                                                    'placeholder' => __('lang_v1.select_location'),
                                                    'id' => 'pr_location',
                                                ]) !!}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped ajax_view"
                                            id="purchase_requisition_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="not-export">@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('purchase.location')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.required_by_date')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (
                    !empty($common_settings['enable_purchase_order']) &&
                        (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')))

                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <rect x="4" y="4" width="16" height="16" rx="2" />
                                        <line x1="4" y1="10" x2="20" y2="10" />
                                        <line x1="12" y1="4" x2="12" y2="20" />
                                        <line x1="12" y1="10" x2="16" y2="10" />
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.purchase_order')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('po_location', $all_locations, null, [
                                                'class' => 'form-control select2',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'po_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped ajax_view"
                                            id="purchase_order_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="not-export">@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('purchase.location')</th>
                                                    <th>@lang('purchase.supplier')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.quantity_remaining')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
                @if (auth()->user()->can('access_pending_shipments_only') ||
                        auth()->user()->can('access_shipping') ||
                        auth()->user()->can('access_own_shipping'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                                        <path d="M3 9l4 0"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.pending_shipments')
                                        </h3>
                                    </div>
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('pending_shipments_location', $all_locations, null, [
                                                'class' => 'form-control select2 ',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'pending_shipments_location',
                                            ]) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped ajax_view" id="shipments_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
                                                    <th style="white-space: nowrap !important;">@lang('messages.date')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.invoice_no')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.customer_name')</th>
                                                    <th style="white-space: nowrap !important;">@lang('lang_v1.contact_no')</th>
                                                    <th style="white-space: nowrap !important;">@lang('sale.location')</th>
                                                    <th style="white-space: nowrap !important;">@lang('lang_v1.shipping_status')</th>
                                                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                                                        <th style="white-space: nowrap !important;">
                                                            {{ $custom_labels['shipping']['custom_field_1'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                                                        <th style="white-space: nowrap !important;">
                                                            {{ $custom_labels['shipping']['custom_field_2'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                                                        <th style="white-space: nowrap !important;">
                                                            {{ $custom_labels['shipping']['custom_field_3'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                                                        <th style="white-space: nowrap !important;">
                                                            {{ $custom_labels['shipping']['custom_field_4'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                                                        <th style="white-space: nowrap !important;">
                                                            {{ $custom_labels['shipping']['custom_field_5'] }}
                                                        </th>
                                                    @endif
                                                    <th style="white-space: nowrap !important;">@lang('sale.payment_status')</th>
                                                    <th style="white-space: nowrap !important;">@lang('restaurant.service_staff')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div
                                    class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z">
                                        </path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                        <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                            @lang('lang_v1.payment_recovered_today')
                                        </h3>
                                    </div>

                                </div>
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="cash_flow_table">
                                            <thead>
                                                <tr>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('account.account')</th>
                                                    <th>@lang('lang_v1.description')</th>
                                                    <th>@lang('lang_v1.payment_method')</th>
                                                    <th>@lang('lang_v1.payment_details')</th>
                                                    <th>@lang('account.credit')</th>
                                                    <th>@lang('lang_v1.account_balance')
                                                        @show_tooltip(__('lang_v1.account_balance_tooltip'))</th>
                                                    <th>@lang('lang_v1.total_balance')
                                                        @show_tooltip(__('lang_v1.total_balance_tooltip'))</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr class="bg-gray font-17 footer-total text-center">
                                                    <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                                    <td class="footer_total_credit"></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- @if (!empty($widgets['after_dashboard_reports']))
                    @foreach ($widgets['after_dashboard_reports'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            </div>
        </div>
    @else
        <div class="tw-px-5 tw-pt-8 tw-pb-12">
            <div class="tw-max-w-6xl tw-mx-auto">
                {{-- Operational Shortcuts Section --}}
                <div class="tw-mb-6 tw-flex tw-items-center tw-justify-between">
                    <div>
                        <h2 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-flex tw-items-center tw-gap-2.5">
                            <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-primary-500 tw-text-white tw-shadow-sm">
                                <svg class="tw-w-5 tw-h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                            </span>
                            Pintasan Operasional
                        </h2>
                        <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-1">
                            Pilih modul kerja di bawah ini untuk langsung mengakses tugas harian Anda
                        </p>
                    </div>
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
                    @if (auth()->user()->can('laundry.view') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.update_status') || auth()->user()->can('laundry.log_process'))
                        <a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index']) }}"
                           class="tw-group tw-relative tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-p-6 tw-ring-1 tw-ring-gray-200 dark:tw-ring-slate-700 tw-shadow-sm hover:tw-shadow-xl hover:tw-ring-blue-400 hover:-tw-translate-y-1 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-blue-100 dark:tw-bg-blue-900/50 tw-text-blue-600 dark:tw-text-blue-300 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <svg class="tw-w-7 tw-h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-blue-700 dark:tw-text-blue-300 tw-bg-blue-100/80 dark:tw-bg-blue-900/50 tw-px-3 tw-py-1 tw-rounded-full">Laundry</span>
                                </div>
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 dark:tw-text-white group-hover:tw-text-blue-600 tw-transition-colors">Pesanan Laundry</h3>
                                <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-2 tw-leading-relaxed">Lihat, kelola, dan catat lembar pesanan laundry pelanggan dengan cepat.</p>
                            </div>
                            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-items-center tw-justify-between tw-text-sm tw-font-bold tw-text-blue-600 dark:tw-text-blue-400">
                                <span>Buka Pesanan</span>
                                <svg class="tw-w-5 tw-h-5 group-hover:tw-translate-x-1.5 tw-transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </a>
                    @endif

                    @if (auth()->user()->can('sell.create') || auth()->user()->can('pos.create'))
                        <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                           class="tw-group tw-relative tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-p-6 tw-ring-1 tw-ring-gray-200 dark:tw-ring-slate-700 tw-shadow-sm hover:tw-shadow-xl hover:tw-ring-emerald-400 hover:-tw-translate-y-1 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-emerald-100 dark:tw-bg-emerald-900/50 tw-text-emerald-600 dark:tw-text-emerald-300 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <svg class="tw-w-7 tw-h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                        </svg>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-emerald-700 dark:tw-text-emerald-300 tw-bg-emerald-100/80 dark:tw-bg-emerald-900/50 tw-px-3 tw-py-1 tw-rounded-full">POS</span>
                                </div>
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 dark:tw-text-white group-hover:tw-text-emerald-600 tw-transition-colors">Layar Kasir (POS)</h3>
                                <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-2 tw-leading-relaxed">Buka terminal kasir penjualan untuk melayani pembayaran transaksi secara langsung.</p>
                            </div>
                            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-items-center tw-justify-between tw-text-sm tw-font-bold tw-text-emerald-600 dark:tw-text-emerald-400">
                                <span>Buka Kasir</span>
                                <svg class="tw-w-5 tw-h-5 group-hover:tw-translate-x-1.5 tw-transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </a>
                    @endif

                    @if (auth()->user()->can('repair.view') || auth()->user()->can('job_sheet.create'))
                        <a href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'index']) }}"
                           class="tw-group tw-relative tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-p-6 tw-ring-1 tw-ring-gray-200 dark:tw-ring-slate-700 tw-shadow-sm hover:tw-shadow-xl hover:tw-ring-amber-400 hover:-tw-translate-y-1 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-amber-100 dark:tw-bg-amber-900/50 tw-text-amber-600 dark:tw-text-amber-300 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <svg class="tw-w-7 tw-h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.83-5.83M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l5.654-4.654m0 0l3.03-2.496c.384-.317.626-.74.766-1.208" />
                                        </svg>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-amber-700 dark:tw-text-amber-300 tw-bg-amber-100/80 dark:tw-bg-amber-900/50 tw-px-3 tw-py-1 tw-rounded-full">Servis</span>
                                </div>
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 dark:tw-text-white group-hover:tw-text-amber-600 tw-transition-colors">Servis & Repair</h3>
                                <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-2 tw-leading-relaxed">Kelola job sheet penerimaan dan status pengerjaan servis barang.</p>
                            </div>
                            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-items-center tw-justify-between tw-text-sm tw-font-bold tw-text-amber-600 dark:tw-text-amber-400">
                                <span>Buka Modul</span>
                                <svg class="tw-w-5 tw-h-5 group-hover:tw-translate-x-1.5 tw-transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </a>
                    @endif

                    @if (auth()->user()->can('product.view') || auth()->user()->can('product.create'))
                        <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}"
                           class="tw-group tw-relative tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-p-6 tw-ring-1 tw-ring-gray-200 dark:tw-ring-slate-700 tw-shadow-sm hover:tw-shadow-xl hover:tw-ring-purple-400 hover:-tw-translate-y-1 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-purple-100 dark:tw-bg-purple-900/50 tw-text-purple-600 dark:tw-text-purple-300 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <svg class="tw-w-7 tw-h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25" />
                                        </svg>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-purple-700 dark:tw-text-purple-300 tw-bg-purple-100/80 dark:tw-bg-purple-900/50 tw-px-3 tw-py-1 tw-rounded-full">Produk</span>
                                </div>
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 dark:tw-text-white group-hover:tw-text-purple-600 tw-transition-colors">Daftar Produk</h3>
                                <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-2 tw-leading-relaxed">Lihat & periksa katalog barang, harga variasi, serta posisi stok terkini.</p>
                            </div>
                            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-items-center tw-justify-between tw-text-sm tw-font-bold tw-text-purple-600 dark:tw-text-purple-400">
                                <span>Buka Produk</span>
                                <svg class="tw-w-5 tw-h-5 group-hover:tw-translate-x-1.5 tw-transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </a>
                    @endif

                    @if (auth()->user()->can('customer.view') || auth()->user()->can('customer.create'))
                        <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}"
                           class="tw-group tw-relative tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-p-6 tw-ring-1 tw-ring-gray-200 dark:tw-ring-slate-700 tw-shadow-sm hover:tw-shadow-xl hover:tw-ring-indigo-400 hover:-tw-translate-y-1 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-indigo-100 dark:tw-bg-indigo-900/50 tw-text-indigo-600 dark:tw-text-indigo-300 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <svg class="tw-w-7 tw-h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-indigo-700 dark:tw-text-indigo-300 tw-bg-indigo-100/80 dark:tw-bg-indigo-900/50 tw-px-3 tw-py-1 tw-rounded-full">Kontak</span>
                                </div>
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 dark:tw-text-white group-hover:tw-text-indigo-600 tw-transition-colors">Data Pelanggan</h3>
                                <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-2 tw-leading-relaxed">Kelola informasi data pelanggan, nomor kontak, dan riwayat transaksi.</p>
                            </div>
                            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-items-center tw-justify-between tw-text-sm tw-font-bold tw-text-indigo-600 dark:tw-text-indigo-400">
                                <span>Buka Pelanggan</span>
                                <svg class="tw-w-5 tw-h-5 group-hover:tw-translate-x-1.5 tw-transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </a>
                    @endif

                    @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                        <a href="{{ action([\App\Http\Controllers\SellController::class, 'index']) }}"
                           class="tw-group tw-relative tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-p-6 tw-ring-1 tw-ring-gray-200 dark:tw-ring-slate-700 tw-shadow-sm hover:tw-shadow-xl hover:tw-ring-teal-400 hover:-tw-translate-y-1 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                    <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-teal-100 dark:tw-bg-teal-900/50 tw-text-teal-600 dark:tw-text-teal-300 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <svg class="tw-w-7 tw-h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-teal-700 dark:tw-text-teal-300 tw-bg-teal-100/80 dark:tw-bg-teal-900/50 tw-px-3 tw-py-1 tw-rounded-full">Penjualan</span>
                                </div>
                                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 dark:tw-text-white group-hover:tw-text-teal-600 tw-transition-colors">Daftar Penjualan</h3>
                                <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300 tw-mt-2 tw-leading-relaxed">Lihat, periksa, dan cetak riwayat faktur transaksi penjualan toko.</p>
                            </div>
                            <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-items-center tw-justify-between tw-text-sm tw-font-bold tw-text-teal-600 dark:tw-text-teal-400">
                                <span>Buka Penjualan</span>
                                <svg class="tw-w-5 tw-h-5 group-hover:tw-translate-x-1.5 tw-transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

@endsection


<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@section('css')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            sales_order_table = $('#sales_order_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}?sale_type=sales_order',
                    "data": function(d) {
                        d.for_dashboard_sales_order = true;

                        if ($('#so_location').length > 0) {
                            d.location_id = $('#so_location').val();
                        }
                    }
                },
                columnDefs: [{
                    "targets": 7,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'so_qty_remaining',
                        name: 'so_qty_remaining',
                        "searchable": false
                    },
                    {
                        data: 'custom_field_1',
                        name: 'transactions.custom_field_1',
                        @if (empty($so_custom_labels['sell']['custom_field_1']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_2',
                        name: 'transactions.custom_field_2',
                        @if (empty($so_custom_labels['sell']['custom_field_2']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_3',
                        name: 'transactions.custom_field_3',
                        @if (empty($so_custom_labels['sell']['custom_field_3']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_4',
                        name: 'transactions.custom_field_4',
                        @if (empty($so_custom_labels['sell']['custom_field_4']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                ]
            });

            @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)

                // Cash Flow Table
                cash_flow_table = $('#cash_flow_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    "ajax": {
                        "url": "{{ action([\App\Http\Controllers\AccountController::class, 'cashFlow']) }}",
                        "data": function(d) {
                            d.type = 'credit';
                            d.only_payment_recovered = true;
                        }
                    },
                    "ordering": false,
                    "searching": false,
                    columns: [{
                            data: 'operation_date',
                            name: 'operation_date'
                        },
                        {
                            data: 'account_name',
                            name: 'account_name'
                        },
                        {
                            data: 'sub_type',
                            name: 'sub_type'
                        },
                        {
                            data: 'method',
                            name: 'TP.method'
                        },
                        {
                            data: 'payment_details',
                            name: 'payment_details',
                            searchable: false
                        },
                        {
                            data: 'credit',
                            name: 'amount'
                        },
                        {
                            data: 'balance',
                            name: 'balance'
                        },
                        {
                            data: 'total_balance',
                            name: 'total_balance'
                        },
                    ],
                    "fnDrawCallback": function(oSettings) {
                        __currency_convert_recursively($('#cash_flow_table'));
                    },
                    "footerCallback": function(row, data, start, end, display) {
                        var footer_total_credit = 0;

                        for (var r in data) {
                            footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(
                                data[r].credit).data('orig-value')) : 0;
                        }
                        $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                    }
                });
            @endif

            $('#so_location').change(function() {
                sales_order_table.ajax.reload();
            });
            @if (!empty($common_settings['enable_purchase_order']))
                //Purchase table
                purchase_order_table = $('#purchase_order_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseOrderController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#po_location').length > 0) {
                                d.location_id = $('#po_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'name',
                            name: 'contacts.name'
                        },
                        {
                            data: 'status',
                            name: 'transactions.status'
                        },
                        {
                            data: 'po_qty_remaining',
                            name: 'po_qty_remaining',
                            "searchable": false
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        }
                    ]
                })

                $('#po_location').change(function() {
                    purchase_order_table.ajax.reload();
                });
            @endif

            @if (!empty($common_settings['enable_purchase_requisition']))
                //Purchase table
                purchase_requisition_table = $('#purchase_requisition_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#pr_location').length > 0) {
                                d.location_id = $('#pr_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'delivery_date',
                            name: 'delivery_date'
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        },
                    ]
                })

                $('#pr_location').change(function() {
                    purchase_requisition_table.ajax.reload();
                });

                $(document).on('click', 'a.delete-purchase-requisition', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        purchase_requisition_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            @endif

            sell_table = $('#shipments_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                aaSorting: [
                    [1, 'desc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}',
                    "data": function(d) {
                        d.only_pending_shipments = true;
                        if ($('#pending_shipments_location').length > 0) {
                            d.location_id = $('#pending_shipments_location').val();
                        }
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                        {
                            data: 'shipping_custom_field_1',
                            name: 'shipping_custom_field_1'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                        {
                            data: 'shipping_custom_field_2',
                            name: 'shipping_custom_field_2'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                        {
                            data: 'shipping_custom_field_3',
                            name: 'shipping_custom_field_3'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                        {
                            data: 'shipping_custom_field_4',
                            name: 'shipping_custom_field_4'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                        {
                            data: 'shipping_custom_field_5',
                            name: 'shipping_custom_field_5'
                        },
                    @endif {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(4)').attr('class', 'clickable_td');
                }
            });

            $('#pending_shipments_location').change(function() {
                sell_table.ajax.reload();
            });
        });
    </script>
    
@endsection
