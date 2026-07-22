@extends('layouts.app')
@section('title', __('business.business_locations'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'business.business_locations' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'business.manage_your_business_locations' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'business.all_your_business_locations' )])
        @slot('tool')
            <div class="box-tools">
               
                <button class="tw-dw-btn theme-btn-bg tw-font-bold tw-text-white tw-border-none tw-rounded-xl tw-transition-all tw-duration-200 hover:tw-scale-[1.02] active:tw-scale-[0.98] pull-right tw-mb-2 btn-modal"
                    data-href="{{action([\App\Http\Controllers\BusinessLocationController::class, 'create'])}}" 
                    data-container=".location_add_modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="business_location_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="white-space: nowrap !important;">@lang( 'invoice.name' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'lang_v1.location_id' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'business.landmark' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'business.city' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'business.zip_code' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'business.state' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'business.country' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'lang_v1.price_group' )</th>
                        <th style="white-space: nowrap !important;">@lang( 'invoice.invoice_scheme' )</th>
                        <th style="white-space: nowrap !important;">@lang('lang_v1.invoice_layout_for_pos')</th>
                        <th style="white-space: nowrap !important;">@lang('lang_v1.invoice_layout_for_sale')</th>
                        <th class="not-export" style="white-space: nowrap !important;">@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade location_add_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade location_edit_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
