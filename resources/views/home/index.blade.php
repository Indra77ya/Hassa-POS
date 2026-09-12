@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

    @php
        $now = \Carbon\Carbon::now();
        $hour = $now->hour;
        if ($hour < 11) {
            $time_greeting = 'Selamat Pagi';
        } elseif ($hour < 15) {
            $time_greeting = 'Selamat Siang';
        } elseif ($hour < 18) {
            $time_greeting = 'Selamat Sore';
        } else {
            $time_greeting = 'Selamat Malam';
        }
        $user_role_name = auth()->user()->roles->pluck('name')->first() ?? '';
        if ($user_role_name && session('user.business_id')) {
            $user_role_name = str_replace('#' . session('user.business_id'), '', $user_role_name);
        }
        $user_first_name = Session::get('user.first_name');
        $show_role_badge = !empty($user_role_name) && strtolower(trim($user_role_name)) !== strtolower(trim($user_first_name));
    @endphp

    @if (auth()->user()->can('dashboard.data'))
        <div class="tw-pb-6 theme-header-bg xl:tw-pb-0">
            <div class="tw-px-5 tw-pt-3">
                <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between sm:tw-gap-12">
                    <div class="tw-mt-2 sm:tw-w-1/2 md:tw-w-1/2">
                        <h1 class="tw-text-2xl md:tw-text-3xl tw-tracking-tight tw-font-bold tw-text-white tw-mb-1 md:tw-mb-0">
                            {{ __('home.welcome_message', ['name' => $user_first_name]) }} 👋
                        </h1>
                    </div>
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
                            <button type="button" id="dashboard_date_filter"
                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-full tw-gap-1 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-900 tw-transition-all tw-duration-200 tw-bg-white tw-rounded-lg sm:tw-w-auto hover:tw-bg-primary-50">
                                <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
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
                        </div>
                    @endif
                </div>

                @if ($is_admin)
                    <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">

                        <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0 tw-bg-sky-100 tw-text-sky-500">
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
                                        <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('home.total_sell') }}
                                        </p>
                                        <p class="total_sell tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2"></path>
                                            <path d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1"></path>
                                            <path d="M12 6v10"></path>
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('lang_v1.net') }} @show_tooltip(__('lang_v1.net_home_tooltip'))
                                        </p>
                                        <p class="net tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <path d="M9 7l1 0" />
                                            <path d="M9 13l6 0" />
                                            <path d="M13 17l2 0" />
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('home.invoice_due') }}
                                        </p>
                                        <p class="invoice_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                        <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M21 7l-18 0" />
                                            <path d="M18 10l3 -3l-3 -3" />
                                            <path d="M6 20l-3 -3l3 -3" />
                                            <path d="M3 17l18 0" />
                                        </svg>
                                    </div>

                                    <div class="tw-flex-1 tw-min-w-0">
                                        <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                            {{ __('lang_v1.total_sell_return') }}
                                            <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true" data-container="body"
                                            data-toggle="popover" data-placement="auto bottom" id="total_srp"
                                            data-value="{{ __('lang_v1.total_sell_return') }}-{{ __('lang_v1.total_sell_return_paid') }}"
                                            data-content="" data-html="true" data-trigger="hover"></i>
                                        </p>
                                        <p class="total_sell_return tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($is_admin)
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-0 tw-grid" aria-hidden="true">
                        <div class="theme-header-bg"></div>
                        <div class="tw-hidden sm:tw-block tw-bg-gray-100"></div>
                    </div>
                    <div class="tw-px-5 tw-isolate">
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-4 sm:tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0 bg-sky-100 tw-text-sky-500">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M12 3v12"></path>
                                                <path d="M16 11l-4 4l-4 -4"></path>
                                                <path d="M3 12a9 9 0 0 0 18 0"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('home.total_purchase') }}
                                            </p>
                                            <p class="total_purchase tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 9v4" />
                                                <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                                <path d="M12 16h.01" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500">
                                                {{ __('home.purchase_due') }}
                                            </p>
                                            <p class="purchase_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                                <path d="M15 14v-2a2 2 0 0 0 -2 -2h-4l2 -2m0 4l-2 -2" />
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.total_purchase_return') }}
                                                <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true" data-container="body"
                                                data-toggle="popover" data-placement="auto bottom" id="total_prp"
                                                data-value="{{ __('lang_v1.total_purchase_return') }}-{{ __('lang_v1.total_purchase_return_paid') }}"
                                                data-content="" data-html="true" data-trigger="hover"></i>
                                            </p>
                                            <p class="total_purchase_return tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2"></path>
                                                <path d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1"></path>
                                                <path d="M12 6v10"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.expense') }}
                                            </p>
                                            <p class="total_expense tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="tw-px-5 tw-py-6">
            <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    @if (!empty($all_locations))
                        <div class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
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
                                    <div class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50">
                                        <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                            {!! $sells_chart_1->container() !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
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
                                    <div class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50">
                                        <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                            {!! $sells_chart_2->container() !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
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

                            <div class="tw-flow-root tw-mt-5 tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="sales_payment_dues_table" style="width: 100%;">
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
                    <div class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v4"></path>
                                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
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
                            <div class="tw-flow-root tw-mt-5 tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="purchase_payment_dues_table" style="width: 100%;">
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
                    <div class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-w-10 tw-h-10">
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
                            <div class="tw-flow-root tw-mt-5 tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-bordered table-striped" id="stock_alert_table" style="width: 100%;">
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
                @endcan
            </div>
        </div>
    @else
        {{-- Modern Staff Dashboard View --}}
        <div class="tw-px-4 sm:tw-px-6 tw-py-6">
            <div class="tw-max-w-7xl tw-mx-auto">

                {{-- Welcome Banner Header Card --}}
                <div class="tw-bg-white tw-rounded-2xl tw-p-5 sm:tw-p-6 tw-border tw-border-gray-200 tw-shadow-sm tw-mb-6">
                    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
                        <div class="tw-flex-1">
                            <div class="tw-flex tw-items-center tw-flex-wrap tw-gap-2 tw-mb-2">
                                <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-bold tw-text-emerald-800 tw-bg-emerald-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-emerald-200">
                                    <span class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-emerald-500"></span>
                                    {{ $time_greeting }}
                                </span>
                                @if ($show_role_badge)
                                    <span class="tw-inline-flex tw-items-center tw-text-xs tw-font-bold tw-text-blue-800 tw-bg-blue-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-blue-200">
                                        <i class="fa fa-user-tag tw-mr-1.5" style="font-size: 11px;"></i>
                                        {{ $user_role_name }}
                                    </span>
                                @endif
                            </div>
                            <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-extrabold tw-text-gray-900 tw-tracking-tight">
                                Halo, {{ $user_first_name }}!
                            </h1>
                            <p class="tw-text-sm tw-text-gray-600 tw-mt-1">
                                Pilih modul kerja di bawah untuk langsung mengakses tugas & transaksi harian Anda.
                            </p>
                        </div>
                        <div class="tw-inline-flex tw-items-center tw-gap-3 tw-bg-gray-50 tw-px-4 tw-py-3 tw-rounded-xl tw-border tw-border-gray-200 tw-self-start md:tw-self-center">
                            <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-blue-600 tw-text-white tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                <i class="fa fa-calendar-alt" style="font-size: 18px;"></i>
                            </div>
                            <div>
                                <span class="tw-block tw-text-xs tw-font-semibold tw-text-gray-600">Hari ini</span>
                                <span class="tw-block tw-text-sm tw-font-bold tw-text-gray-900">{{ $now->format('d F Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Operational Shortcuts Section Header --}}
                <div class="tw-mb-5 tw-flex tw-items-center tw-justify-between">
                    <div>
                        <h2 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900 tw-flex tw-items-center tw-gap-2.5">
                            <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-blue-600 tw-text-white tw-shadow-sm">
                                <i class="fa fa-th-large" style="font-size: 16px;"></i>
                            </span>
                            Pintasan Operasional
                        </h2>
                    </div>
                </div>

                {{-- Grid Cards --}}
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-5">

                    @if (auth()->user()->can('laundry.view') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.update_status') || auth()->user()->can('laundry.log_process'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-blue-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-blue-100 tw-text-blue-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fas fa-tshirt" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-blue-700 tw-bg-blue-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-blue-100">Laundry</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-blue-600 tw-transition-colors">Pesanan Laundry</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Lihat, kelola, dan catat lembar pesanan laundry pelanggan dengan cepat.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-blue-50 tw-text-blue-700 hover:tw-bg-blue-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Lihat Pesanan</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('laundry.create'))
                                    <a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-blue-600 tw-text-white hover:tw-bg-blue-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Buat Pesanan Laundry Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('sell.create') || auth()->user()->can('pos.create'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-emerald-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-emerald-100 tw-text-emerald-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-shopping-cart" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-emerald-700 tw-bg-emerald-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-emerald-100">POS</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-emerald-600 tw-transition-colors">Layar Kasir (POS)</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Buka terminal kasir penjualan untuk melayani pembayaran transaksi secara langsung.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-emerald-50 tw-text-emerald-700 hover:tw-bg-emerald-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Buka Terminal Kasir</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('repair.view') || auth()->user()->can('job_sheet.create'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-amber-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-amber-100 tw-text-amber-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-wrench" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-amber-700 tw-bg-amber-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-amber-100">Servis</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-amber-600 tw-transition-colors">Servis & Repair</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Kelola job sheet penerimaan dan status pengerjaan servis barang.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-amber-50 tw-text-amber-700 hover:tw-bg-amber-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Job Sheet Servis</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('job_sheet.create'))
                                    <a href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-amber-600 tw-text-white hover:tw-bg-amber-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Buat Job Sheet Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('product.view') || auth()->user()->can('product.create'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-purple-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-purple-100 tw-text-purple-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-cubes" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-purple-700 tw-bg-purple-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-purple-100">Produk</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-purple-600 tw-transition-colors">Daftar Produk</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Lihat & periksa katalog barang, harga variasi, serta posisi stok terkini.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-purple-50 tw-text-purple-700 hover:tw-bg-purple-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Katalog Produk</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('product.create'))
                                    <a href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-purple-600 tw-text-white hover:tw-bg-purple-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Tambah Produk Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('customer.view') || auth()->user()->can('customer.create'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-indigo-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-indigo-100 tw-text-indigo-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-users" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-indigo-700 tw-bg-indigo-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-indigo-100">Kontak</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-indigo-600 tw-transition-colors">Data Pelanggan</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Kelola informasi data pelanggan, nomor kontak, dan riwayat transaksi.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-indigo-50 tw-text-indigo-700 hover:tw-bg-indigo-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Data Pelanggan</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('customer.create'))
                                    <button type="button" data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'customer']) }}"
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-indigo-600 tw-text-white hover:tw-bg-indigo-700 tw-text-xs tw-font-bold tw-transition-colors btn-modal"
                                            data-container=".contact_modal"
                                            title="Tambah Pelanggan Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-teal-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-teal-100 tw-text-teal-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-file-invoice" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-teal-700 tw-bg-teal-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-teal-100">Penjualan</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-teal-600 tw-transition-colors">Daftar Penjualan</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Lihat, periksa, dan cetak riwayat faktur transaksi penjualan toko.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\SellController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-teal-50 tw-text-teal-700 hover:tw-bg-teal-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Riwayat Penjualan</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('sell.create'))
                                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-teal-600 tw-text-white hover:tw-bg-teal-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Tambah Penjualan Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-sky-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-sky-100 tw-text-sky-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-truck" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-sky-700 tw-bg-sky-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-sky-100">Pembelian</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-sky-600 tw-transition-colors">Pembelian Stok</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Catat transaksi pembelian stok barang dari pemasok / supplier.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-sky-50 tw-text-sky-700 hover:tw-bg-sky-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Riwayat Pembelian</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('purchase.create'))
                                    <a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-sky-600 tw-text-white hover:tw-bg-sky-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Tambah Pembelian Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('expense.access') || auth()->user()->can('expense.add'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-rose-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-rose-100 tw-text-rose-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-receipt" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-rose-700 tw-bg-rose-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-rose-100">Pengeluaran</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-rose-600 tw-transition-colors">Biaya Operasional</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Catat dan pantau pengeluaran biaya operasional harian usaha.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\ExpenseController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-rose-50 tw-text-rose-700 hover:tw-bg-rose-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Riwayat Biaya</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('expense.add'))
                                    <a href="{{ action([\App\Http\Controllers\ExpenseController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-rose-600 tw-text-white hover:tw-bg-rose-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Catat Pengeluaran Baru">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->can('stock_adjustment.view') || auth()->user()->can('stock_adjustment.create'))
                        <div class="tw-group tw-bg-white tw-rounded-2xl tw-p-5 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md hover:tw-border-orange-400 tw-transition-all tw-duration-200 tw-flex tw-flex-col tw-justify-between">
                            <div>
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3.5">
                                    <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-orange-100 tw-text-orange-600 tw-flex tw-items-center tw-justify-center group-hover:tw-scale-105 tw-transition-transform">
                                        <i class="fa fa-sliders-h" style="font-size: 22px;"></i>
                                    </div>
                                    <span class="tw-text-xs tw-font-bold tw-text-orange-700 tw-bg-orange-50 tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-orange-100">Stok</span>
                                </div>
                                <h3 class="tw-text-base tw-font-bold tw-text-gray-900 group-hover:tw-text-orange-600 tw-transition-colors">Penyesuaian Stok</h3>
                                <p class="tw-text-xs tw-text-gray-600 tw-mt-1.5 tw-leading-relaxed">Sesuaikan fisik stok barang rusak, hilang, atau opname inventaris.</p>
                            </div>
                            <div class="tw-mt-5 tw-pt-3.5 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                                <a href="{{ action([\App\Http\Controllers\StockAdjustmentController::class, 'index']) }}"
                                   class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-rounded-xl tw-bg-orange-50 tw-text-orange-700 hover:tw-bg-orange-600 hover:tw-text-white tw-text-xs tw-font-bold tw-transition-colors">
                                    <span>Riwayat Penyesuaian</span>
                                    <i class="fa fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                                @if (auth()->user()->can('stock_adjustment.create'))
                                    <a href="{{ action([\App\Http\Controllers\StockAdjustmentController::class, 'create']) }}"
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-xl tw-bg-orange-600 tw-text-white hover:tw-bg-orange-700 tw-text-xs tw-font-bold tw-transition-colors"
                                       title="Buat Penyesuaian Stok">
                                        <i class="fa fa-plus" style="font-size: 13px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
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
