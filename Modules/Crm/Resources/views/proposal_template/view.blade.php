@extends('layouts.app')
@section('title', __('crm::lang.proposal_template'))
@section('content')
	@include('crm::layouts.nav')
	<!-- Content Header (Page header) -->
	<section class="content-header no-print">
	   <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
	   	@lang('crm::lang.proposal_template')
	   	<small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('messages.view')</small>
	   </h1>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="box box-info">
			<div class="box-header with-border">
				<div class="box-tools pull-right">
					@if(auth()->user()->can('crm.add_proposal_template'))
    					<a href="{{action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'getEdit'])}}" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm">
    						@lang('messages.edit')
    					</a>
    				@endif
    				@can('crm.access_proposal')
    					<a href="{{action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'send'])}}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm">
    						@lang('crm::lang.send')
    					</a>
    				@endcan
				</div>
            </div>
            <div class="box-body">
            	<div class="row">
            		<div class="col-md-12">
						<p>
							<strong>CC:</strong> {{$proposal_template->cc}}
						</p>
					</div>
					<div class="col-md-12">
						<p>
							<strong>BCC:</strong> {{$proposal_template->bcc}}
						</p>
					</div>
            		<div class="col-md-12">
						<p>
							<strong>{{__('crm::lang.subject')}}:</strong> {{$proposal_template->subject}}
						</p>
					</div>
				</div>
				<div class="row mt-10">
					<div class="col-md-12">
						<p>
							<strong>{{__('crm::lang.email_body')}}:</strong> {!!$proposal_template->body!!}
						</p>
					</div>
				</div>
				@if($proposal_template->media->count() > 0)
					<hr>
					<div class="row">
						<div class="col-md-6">
							<h4>
								{{__('crm::lang.attachments')}}
							</h4>
							@includeIf('crm::proposal_template.partials.attachment', ['medias' => $proposal_template->media])
						</div>
					</div>
				@endif
			</div>
		</div>
	</section>
@endsection
@section('javascript')
<script type="text/javascript">
	$(function () {
	    $(document).on('click', 'a.delete_attachment', function (e) {
            e.preventDefault();
            var url = $(this).data('href');
            var this_btn = $(this);
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        method: 'DELETE',
                        url: url,
                        dataType: 'json',
                        success: function(result) {
                            if(result.success == true){
			                    this_btn.closest('tr').remove();
			                    toastr.success(result.msg);
			                } else {
			                    toastr.error(result.msg);
			                }
                        }
                    });
                }
            });
        });
	});
</script>
@endsection