@extends('layouts.app')

@section('title', __('crm::lang.campaigns'))

@section('content')
@include('crm::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
   <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('crm::lang.campaigns')</h1>
</section>
<section class="content no-print">
	@component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('campaign_type', __('crm::lang.campaign_type') . ':') !!}
                    {!! Form::select('campaign_type', ['sms' => __('crm::lang.sms'), 'email' => __('business.email')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'campaign_type_filter', 'placeholder' => __('messages.all')]); !!}
                </div>    
            </div>
        </div>
    @endcomponent
	@component('components.widget', ['class' => 'box-primary', 'title' => __('crm::lang.all_campaigns')])
        @slot('tool')
        	<div class="box-tools">
                <a  href="{{action([\Modules\Crm\Http\Controllers\CampaignController::class, 'create'])}}"class="tw-m-2 tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right btn-add-schedule">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </a>
            </div>
        @endslot
        <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="campaigns_table">
		        <thead>
		            <tr>
		                <th> @lang('messages.action')</th>
		                <th>@lang('crm::lang.campaign_name')</th>
		                <th>@lang('crm::lang.campaign_type')</th>
		                <th>@lang('business.created_by')</th>
                        <th>@lang('lang_v1.created_at')</th>
		            </tr>
		        </thead>
		    </table>
        </div>
    @endcomponent
    <div class="modal fade campaign_modal" tabindex="-1" role="dialog"></div>
    <div class="modal fade campaign_view_modal" tabindex="-1" role="dialog"></div>
</section>
@endsection
@section('javascript')
	<script src="{{ asset('modules/crm/js/crm.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			initializeCampaignDatatable();
		});
	</script>
@endsection