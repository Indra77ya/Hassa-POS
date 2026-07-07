@extends('layouts.app')
@section('title', __('crm::lang.proposal_template'))
@section('content')
	@include('crm::layouts.nav')
	<!-- Content Header (Page header) -->
	<section class="content-header no-print">
	   <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('crm::lang.proposal_template')</h1>
	</section>
	<!-- Main content -->
	<section class="content">
		@component('components.widget', ['class' => 'box-solid'])
			@if(empty($proposal_template) && auth()->user()->can('crm.add_proposal_template'))
		        @slot('tool')
		            <div class="box-tools">
				<a href="{{action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'create'])}}"
					class="tw-m-2 tw-dw-btn  tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right contact-login-add">
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
	        @endif
	        @if(!empty($proposal_template))
		        <div class="row">
		        	<div class="col-md-4 col-md-offset-4">
		        		<div class="box box-info box-solid">
		        			<div class="box-body">
		        				<strong>
		        					{{$proposal_template->subject}}
		        				</strong>
		        			</div>
		        			<div class="box-footer clearfix">
		        				<div class="row">
		        					@if(auth()->user()->can('crm.add_proposal_template'))
			        					<div class="col-md-4">
			        						<a href="{{action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'getEdit'])}}" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm pull-left">
			        							@lang('messages.edit')
			        						</a>
			        					</div>
			        				@endif
			        				@can('crm.access_proposal')
			        					<div class="col-md-4">
			        						<a href="{{action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'getView'])}}" class="tw-dw-btn tw-dw-btn-info tw-text-white tw-dw-btn-sm">
			        							@lang('messages.view')
			        						</a>
			        					</div>
			        					<div class="col-md-4">
			        						<a href="{{action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'send'])}}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm pull-right">
			        							@lang('crm::lang.send')
			        						</a>
			        					</div>
			        				@endcan
		        				</div>
		        			</div>
		        		</div>
		        	</div>
		        </div>
		    @else
		    	<div class="callout callout-info">
		            <h4>
		            	{{__('crm::lang.no_template_found')}}
		            </h4>
		        </div>
		    @endif
    	@endcomponent
	</section>
@endsection