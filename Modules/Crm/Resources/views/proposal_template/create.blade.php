@extends('layouts.app')
@section('title', __('crm::lang.proposal_template'))
@section('content')
	@include('crm::layouts.nav')
	<!-- Content Header (Page header) -->
	<section class="content-header no-print">
	   <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
	   		@lang('crm::lang.proposal_template')
	   		<small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('lang_v1.create')</small>
	   </h1>
	</section>
	<!-- Main content -->
	<section class="content">
		@component('components.widget', ['class' => 'box-solid'])
			{!! Form::open(['url' => action([\Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'store']), 'method' => 'post', 'id' => 'proposal_template_form', 'files' => true]) !!}
				@includeIf('crm::proposal_template.partials.template_form', ['attachments' => true])
				<button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white ladda-button pull-right" data-style="expand-right">
                    <span class="ladda-label">@lang('messages.save')</span>
                </button>
			{!! Form::close() !!}
    	@endcomponent
	</section>
@endsection
@section('javascript')
<script type="text/javascript">
	$(function () {
		tinymce.init({
	        selector: 'textarea#proposal_email_body',
	        height: 350,
	    });

     	//initialize file input
        $('#attachments').fileinput({
            showUpload: false,
            showPreview: false,
            browseLabel: LANG.file_browse_label,
            removeLabel: LANG.remove
        });

        $('form#proposal_template_form').validate({
	        submitHandler: function(form) {
	            form.submit();
	            let ladda = Ladda.create(document.querySelector('.ladda-button'));
    			ladda.start();
	        }
	    });
	});
</script>
@endsection