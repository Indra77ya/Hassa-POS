@extends('layouts.auth')
@section('title', __('superadmin::lang.pricing'))

@section('content')
    <div class="">
        @include('superadmin::layouts.partials.currency')
        <div class="pricing">
            <div class="tw-mt-20">
                <div class="tw-flex tw-flex-col tw-items-center">

                    <div class="tw-flex tw-flex-col tw-gap-2 tw-text-center">
                        <h2 class="tw-font-bold tw-text-3xl tw-text-white">@lang('superadmin::lang.pricing')</h2>
                        <h3 class="tw-text-sm tw-font-medium tw-text-white">
                            Choose your prefered {{ config('app.name', 'ultimatePOS') }} pricing plan
                        </h3>
                    </div>
                    <!-- Montly/annual-->
                    <div class="tw-flex tw-gap-2 mt-5 md:tw-mt-5">
                        <span class="tw-text-white">Montly</span>
                        <input type="checkbox" id="durationCheck" class="tw-dw-toggle tw-dw-toggle-secondary duration_check"
                            style="margin: 0px" />

                        <span class="tw-flex tw-flex-col tw-text-white"> Annual </span>
                    </div>
                </div>

                {{-- <div class="box-body tw-mt-6"> --}}
                <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-5 md:tw-gap-0 tw-mt-5 md:tw-mt-7 tw-mb-10 tw-h-auto"
                    id="packages">
                    {{-- @include('superadmin::subscription.partials.packages', [
                            'action_type' => 'register',
                        ]) --}}
                </div>
                {{-- </div> --}}
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.change_lang').click(function() {
                window.location = "{{ route('pricing')}}?lang=" + $(this).attr('value');
            });

            $('#durationCheck').off('change').on('change', function() {
                var interval = $(this).is(':checked') ? 'years' : 'months';
                set_packages(interval);
            });

            function set_packages(interval) {
                $.ajax({
                    method: 'get',
                    url: "{{ route('package_duration_update') }}",
                    dataType: 'html',
                    data: {
                        interval: interval
                    },
                    success: function(response) {
                        $('#packages').html(response);
                        // this function use for formate currency
                        __currency_convert_recursively($('.price_card'))
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error(textStatus, errorThrown);
                    },
                });
            }
            set_packages('months');
        })
    </script>
@endsection
