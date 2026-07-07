@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Superadmin Settings')

@section('content')
@include('superadmin::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('superadmin::lang.super_admin_settings')<small>@lang('superadmin::lang.edit_super_admin_settings')</small></h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @include('layouts.partials.search_settings')
    <br>
    {!! Form::open(['action' => '\Modules\Superadmin\Http\Controllers\SuperadminSettingsController@update', 'method' => 'put']) !!}
    <div class="row">

        <div class="col-xs-12">
           <!--  <pos-tab-container> -->
            {{-- <div class="col-xs-12 pos-tab-container"> --}}
                @component('components.widget', ['class' => 'pos-tab-container'])
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu tw-rounded-lg">
                    <div class="list-group">
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base active">@lang('superadmin::lang.super_admin_settings')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.application_settings')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.email_smtp_settings')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.payment_gateways')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.backup')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.cron')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.pusher_settings')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('superadmin::lang.additional_js_css')</a>
                    </div>
                </div>
                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                    @include('superadmin::superadmin_settings.partials.super_admin_settings')
                    @include('superadmin::superadmin_settings.partials.application_settings')
                    @include('superadmin::superadmin_settings.partials.email_smtp_settings')
                    @include('superadmin::superadmin_settings.partials.payment_gateways')
                    @include('superadmin::superadmin_settings.partials.backup')
                    @include('superadmin::superadmin_settings.partials.cron')
                    @include('superadmin::superadmin_settings.partials.pusher_setting')
                    @include('superadmin::superadmin_settings.partials.additional_js_css')
                </div>
                @endcomponent
            {{-- </div> --}}
            <!--  </pos-tab-container> -->
        </div>
       
    </div>
    <div class="row">
        <div class="col-xs-12 text-center">
            <div class="form-group">
            {{Form::submit(__('messages.update'), ['class'=>"tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-lg"])}}
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).on('change', '#BACKUP_DISK', function() {
        if($(this).val() == 'dropbox'){
            $('div#dropbox_access_token_div').removeClass('hide');
        } else {
            $('div#dropbox_access_token_div').addClass('hide');
        }
    });

    $(document).ready( function(){
        if ($('#welcome_email_body').length) {
            tinymce.init({
                selector: 'textarea#welcome_email_body',
            });
        }

        if ($('#superadmin_register_tc').length) {
            tinymce.init({
                selector: 'textarea#superadmin_register_tc'
            });
        }
    });
</script>
@endsection