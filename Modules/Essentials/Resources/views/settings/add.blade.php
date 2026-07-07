@extends('layouts.app')
@section('title', __('essentials::lang.essentials_n_hrm_settings'))

@section('content')
    @include('essentials::layouts.nav_hrm')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('essentials::lang.essentials_n_hrm_settings')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'action' => '\Modules\Essentials\Http\Controllers\EssentialsSettingsController@update',
            'method' => 'post',
            'id' => 'essentials_settings_form',
        ]) !!}
        <div class="row">
            <div class="col-xs-12">
                <!--  <pos-tab-container> -->
                {{-- <div class="col-xs-12 pos-tab-container"> --}}
                @component('components.widget', ['class' => 'pos-tab-container'])
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu">
                        <div class="list-group">
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base active">@lang('essentials::lang.leave')</a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('essentials::lang.payroll')</a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('essentials::lang.attendance')</a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('essentials::lang.sales_target')</a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('essentials::lang.essentials')</a>
                        </div>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.leave_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.payroll_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.attendance_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.sales_target_settings')
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        @include('essentials::settings.partials.essentials_settings')
                    </div>
                @endcomponent
            </div>

            <!--  </pos-tab-container> -->
        </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group text-center">
                    {{ Form::submit(__('messages.update'), ['class' => 'tw-dw-btn tw-dw-btn-error tw-text-white']) }}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
        <div class="col-xs-12">
            <p class="help-block"><i>{!! __('essentials::lang.version_info', ['version' => $module_version]) !!}</i></p>
        </div>
    </section>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            tinymce.init({
                selector: 'textarea#leave_instructions',
            });

            $('#essentials_settings_form').validate({
                ignore: [],
            });
        });
    </script>
@endsection
