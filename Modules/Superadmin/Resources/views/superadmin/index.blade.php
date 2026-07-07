@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.packages'))

@section('content')
    @include('superadmin::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('superadmin::lang.welcome_superadmin')
        </h1>
    </section>

    <section class="content">

        @include('superadmin::layouts.partials.currency')

        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="btn-group pull-right" data-toggle="buttons">
                    <label class="btn btn-info active">
                        <input type="radio" name="date-filter" data-start="{{ date('Y-m-d') }}"
                            data-end="{{ date('Y-m-d') }}" checked> {{ __('home.today') }}
                    </label>
                    <label class="btn btn-info">
                        <input type="radio" name="date-filter" data-start="{{ $date_filters['this_week']['start'] }}"
                            data-end="{{ $date_filters['this_week']['end'] }}"> {{ __('home.this_week') }}
                    </label>
                    <label class="btn btn-info">
                        <input type="radio" name="date-filter" data-start="{{ $date_filters['this_month']['start'] }}"
                            data-end="{{ $date_filters['this_month']['end'] }}"> {{ __('home.this_month') }}
                    </label>
                    <label class="btn btn-info">
                        <input type="radio" name="date-filter" data-start="{{ $date_filters['this_yr']['start'] }}"
                            data-end="{{ $date_filters['this_yr']['end'] }}"> {{ __('superadmin::lang.this_year') }}
                    </label>
                </div>
            </div>

        </div>
        <br />
        <div class="row">
            <div class="col-lg-4 col-xs-6">
                @component('components.static', [
                    'svg' =>
                        '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-refresh"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>',
                ])
                    <h3><span class="new_subscriptions">&nbsp;</span></h3>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                        @lang('superadmin::lang.new_subscriptions')
                    </p>
                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'index']) }}"
                        class="small-box-footer">@lang('superadmin::lang.more_info') <i class="fa fa-arrow-circle-right"></i></a>
                @endcomponent
            </div>
            <!-- ./col -->

            <!-- <div class="col-lg-4 col-xs-6">
         <div class="small-box bg-green">
         <div class="inner">
         <h3>53<sup style="font-size: 20px">%</sup></h3>

         <p>Bounce Rate</p>
         </div>
         <div class="icon">
         <i class="ion ion-stats-bars"></i>
         </div>
         <a href="#" class="small-box-footer">@lang('superadmin::lang.more_info')<i class="fa fa-arrow-circle-right"></i></a>
         </div>
         </div> -->
            <!-- ./col -->

            <div class="col-lg-4 col-xs-6">

                @component('components.static', [
                    'svg' =>
                        '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-user-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>',
                ])
                    <h3><span class="new_registrations">&nbsp;</span></h3>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                        @lang('superadmin::lang.new_registrations')
                    </p>
                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}"
                        class="small-box-footer">@lang('superadmin::lang.more_info') <i class="fa fa-arrow-circle-right"></i></a>
                @endcomponent

            </div>
            <!-- ./col -->

            <div class="col-lg-4 col-xs-6">
                @component('components.static', [
                    'svg_bg' => 'tw-bg-red-200',
                    'svg_text' => 'tw-text-black-200',
                    'svg' =>
                        '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chart-pie"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 3.2a9 9 0 1 0 10.8 10.8a1 1 0 0 0 -1 -1h-6.8a2 2 0 0 1 -2 -2v-7a.9 .9 0 0 0 -1 -.8" /><path d="M15 3.5a9 9 0 0 1 5.5 5.5h-4.5a1 1 0 0 1 -1 -1v-4.5" /></svg>',
                ])
                    <h3>{{ $not_subscribed }}</h3>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                        @lang('superadmin::lang.not_subscribed')
                    </p>
                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}"
                        class="small-box-footer">@lang('superadmin::lang.more_info') <i class="fa fa-arrow-circle-right"></i></a>
                @endcomponent
            </div>
            <!-- ./col -->
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div
                    class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
                    <div class="tw-p-4 sm:tw-p-5">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M17 17h-11v-14h-2"></path>
                                <path d="M6 5l14 1l-1 7h-13"></path>
                            </svg>
                            <h3
                                class="tw-flex-1 tw-min-w-0 tw-text-base tw-font-medium tw-tracking-tight tw-text-gray-900 tw-truncate tw-whitespace-nowrap sm:tw-text-lg lg:tw-text-xl">
                                {{ __('superadmin::lang.monthly_sales_trend') }}
                            </h3>
                        </div>
                        {!! $monthly_sells_chart->container() !!}

                    </div>
                </div>
            </div>

        </div>

    </section>
@endsection

@section('javascript')
    {!! $monthly_sells_chart->script() !!}

    <script type="text/javascript">
        $(document).ready(function() {

            var start = $('input[name="date-filter"]:checked').data('start');
            var end = $('input[name="date-filter"]:checked').data('end');
            update_statistics(start, end);
            $(document).on('change', 'input[name="date-filter"]', function() {
                var start = $('input[name="date-filter"]:checked').data('start');
                var end = $('input[name="date-filter"]:checked').data('end');
                update_statistics(start, end);
            });
        });

        function update_statistics(start, end) {
            var data = {
                start: start,
                end: end
            };

            //get purchase details
            var loader = '<i class="fa fa-refresh fa-spin fa-fw"></i>';
            $('.new_subscriptions').html(loader);
            $('.new_registrations').html(loader);
            $.ajax({
                method: "GET",
                url: '/superadmin/stats',
                dataType: "json",
                data: data,
                success: function(data) {
                    $('.new_subscriptions').html(__currency_trans_from_en(data.new_subscriptions, true, true));
                    $('.new_registrations').html(data.new_registrations);
                }
            });
        }
    </script>
@endsection
