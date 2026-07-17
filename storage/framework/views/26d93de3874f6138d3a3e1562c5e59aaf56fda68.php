

<?php $__env->startSection('title', __('repair::lang.job_sheets')); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('repair::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
    	<?php echo app('translator')->get('repair::lang.job_sheets'); ?>
    </h1>
</section>
<!-- Main content -->
<section class="content no-print">
    <?php $__env->startComponent('components.filters', ['title' => __('report.filters'), 'closed' => false]); ?>
        <div class="col-md-3">
            <div class="form-group">
                <?php echo Form::label('location_id',  __('purchase.business_location') . ':'); ?>

                <?php echo Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <?php echo Form::label('contact_id',  __('role.customer') . ':'); ?>

                <?php echo Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

            </div>
        </div>
        <?php if(in_array('service_staff' ,$enabled_modules) && !$is_user_service_staff): ?>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('technician',  __('repair::lang.technician') . ':'); ?>

                    <?php echo Form::select('technician', $service_staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

                </div>
            </div>
        <?php endif; ?>
        <div class="col-md-3">
            <div class="form-group">
                <?php echo Form::label('status_id',  __('sale.status') . ':'); ?>

                <?php echo Form::select('status_id', $status_dropdown['statuses'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <?php echo Form::label('sell_list_filter_date_range', __('report.date_range') . ':'); ?>

                <?php echo Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); ?>

            </div>
        </div>
    <?php echo $__env->renderComponent(); ?>
    
	<div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#pending_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fas fa-exclamation-circle text-orange"></i>
                            <?php echo app('translator')->get('repair::lang.pending'); ?>
                            <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('repair::lang.common_pending_status_tooltip') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                        </a>
                    </li>
                    <li>
                        <a href="#completed_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fa fas fa-check-circle text-success"></i>
                            <?php echo app('translator')->get('repair::lang.completed'); ?>
                            <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('repair::lang.common_completed_status_tooltip') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="pending_job_sheet_tab">
                        <div class="row">
                            <div class="col-md-12 mb-12">
                                <a type="button" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                    href="<?php echo e(action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create']), false); ?>" id="add_job_sheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg> <?php echo app('translator')->get('messages.add'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="pending_job_sheets_table">
                                <thead>
                                    <tr>
                                        <th><?php echo app('translator')->get('messages.action'); ?></th>
                                        <th>
                                            <?php echo app('translator')->get('repair::lang.service_type'); ?>
                                        </th>
                                        <th>
                                            <?php echo app('translator')->get('lang_v1.due_date'); ?>
                                        </th>
                                        <th>
                                            <?php echo app('translator')->get('repair::lang.job_sheet_no'); ?>
                                        </th>
                                        <th><?php echo app('translator')->get('sale.invoice_no'); ?></th>
                                        <th><?php echo app('translator')->get('sale.status'); ?></th>
                                        <?php if(in_array('service_staff' ,$enabled_modules)): ?>
                                            <th><?php echo app('translator')->get('repair::lang.technician'); ?></th>
                                        <?php endif; ?>
                                        <th>
                                            <?php echo app('translator')->get('role.customer'); ?>
                                        </th>
                                        <th><?php echo app('translator')->get('lang_v1.contact_id'); ?></th>
                                        <th> <?php echo app('translator')->get('repair::lang.customer_phone'); ?></th>
                                        <th><?php echo app('translator')->get('business.location'); ?></th>
                                        <th><?php echo app('translator')->get('product.brand'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.device'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.device_model'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.serial_no'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.estimated_cost'); ?></th>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_1'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_1'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_2'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_2'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_3'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_3'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_4'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_4'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_5'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_5'], false); ?></th>
                                        <?php endif; ?>
                                        <th><?php echo app('translator')->get('lang_v1.added_by'); ?></th>
                                        <th><?php echo app('translator')->get('lang_v1.created_at'); ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="completed_job_sheet_tab">
                        <div class="row">
                            <div class="col-md-12 mb-12">
                                <a type="button" class="tw-dw-btn tw-dw-btn-sm tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                    href="<?php echo e(action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create']), false); ?>" id="add_job_sheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg> <?php echo app('translator')->get('messages.add'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="completed_job_sheets_table">
                                <thead>
                                    <tr>
                                        <th><?php echo app('translator')->get('messages.action'); ?></th>
                                        <th>
                                            <?php echo app('translator')->get('repair::lang.service_type'); ?>
                                        </th>
                                        <th>
                                            <?php echo app('translator')->get('lang_v1.due_date'); ?>
                                        </th>
                                        <th>
                                            <?php echo app('translator')->get('repair::lang.job_sheet_no'); ?>
                                        </th>
                                        <th><?php echo app('translator')->get('sale.invoice_no'); ?></th>
                                        <th><?php echo app('translator')->get('sale.status'); ?></th>
                                        
                                        <?php if(in_array('service_staff' ,$enabled_modules)): ?>
                                            <th><?php echo app('translator')->get('repair::lang.technician'); ?></th>
                                        <?php endif; ?>
                                        <th>
                                            <?php echo app('translator')->get('role.customer'); ?>
                                        </th>
                                        <th><?php echo app('translator')->get('lang_v1.contact_id'); ?></th>
                                        <th> <?php echo app('translator')->get('repair::lang.customer_phone'); ?></th>
                                        <th><?php echo app('translator')->get('business.location'); ?></th>
                                        <th><?php echo app('translator')->get('product.brand'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.device'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.device_model'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.serial_no'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.estimated_cost'); ?></th>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_1'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_1'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_2'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_2'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_3'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_3'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_4'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_4'], false); ?></th>
                                        <?php endif; ?>
                                        <?php if(!empty($repair_settings['job_sheet_custom_field_5'])): ?>
                                            <th><?php echo e($repair_settings['job_sheet_custom_field_5'], false); ?></th>
                                        <?php endif; ?>
                                        <th><?php echo app('translator')->get('lang_v1.added_by'); ?></th>
                                        <th><?php echo app('translator')->get('lang_v1.created_at'); ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <script type="text/javascript">
        $(document).ready(function () {
            pending_job_sheets_datatable = $("#pending_job_sheets_table").DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax:{
                        url: '/repair/job-sheet',
                        "data": function ( d ) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                            d.location_id = $('#location_id').val();
                            d.contact_id = $('#contact_id').val();
                            d.status_id = $('#status_id').val();
                            d.is_completed_status = 0;
                            <?php if(in_array('service_staff' ,$enabled_modules)): ?>
                                d.technician = $('#technician').val();
                            <?php endif; ?>
                        }
                    },
                    columnDefs: [{
                        targets: [0, 4],
                        orderable: false,
                        searchable: false
                    }],
                    aaSorting:[[2, 'asc']],
                    columns:[
                        { data: 'action', name: 'action' },
                        { data: 'service_type', name: 'service_type'},
                        {
                            data: 'delivery_date', name: 'delivery_date'
                        },
                        {
                            data: 'job_sheet_no', name: 'job_sheet_no'
                        },
                        {
                            data: 'repair_no', name: 'repair_no'
                        },
                        { data:'status', name: 'rs.name' },
                       
                        <?php if(in_array('service_staff' ,$enabled_modules)): ?>
                            { data: 'technecian', name: 'technecian', searchable: false},
                        <?php endif; ?>
                        { data: 'customer', name : 'contacts.name'},
                        { data: 'contact_id', name: 'contacts.contact_id'},
                        { data:'mobile', name: 'contacts.mobile' },
                        { data: 'location', name: 'bl.name' },
                        { data: 'brand', name: 'b.name' },
                        { data: 'device', name: 'device.name' },
                        { data: 'device_model', name: 'rdm.name' },
                        {
                            data: 'serial_no', name: 'serial_no'
                        },
                        {
                            data: 'estimated_cost', name: 'estimated_cost'
                        },
                        <?php if(!empty($repair_settings['job_sheet_custom_field_1'])): ?>
                            {
                                data: 'custom_field_1', name: 'repair_job_sheets.custom_field_1'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_2'])): ?>
                            {
                                data: 'custom_field_2', name: 'repair_job_sheets.custom_field_2'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_3'])): ?>
                            {
                                data: 'custom_field_3', name: 'repair_job_sheets.custom_field_3'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_4'])): ?>
                            {
                                data: 'custom_field_4', name: 'repair_job_sheets.custom_field_4'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_5'])): ?>
                            {
                                data: 'custom_field_5', name: 'repair_job_sheets.custom_field_5'
                            },
                        <?php endif; ?>
                        { data: 'added_by', name: 'added_by', searchable: false},
                        { data: 'created_at',
                            name: 'repair_job_sheets.created_at'
                        }
                    ],
                    "fnDrawCallback": function (oSettings) {
                        __currency_convert_recursively($('#pending_job_sheets_table'));
                    }
            });

            completed_job_sheets_datatable = $("#completed_job_sheets_table").DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax:{
                        url: '/repair/job-sheet',
                        "data": function ( d ) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                            d.location_id = $('#location_id').val();
                            d.contact_id = $('#contact_id').val();
                            d.status_id = $('#status_id').val();
                            d.is_completed_status = 1;
                            <?php if(in_array('service_staff' ,$enabled_modules)): ?>
                                d.technician = $('#technician').val();
                            <?php endif; ?>
                        }
                    },
                    columnDefs: [{
                        targets: [0, 4],
                        orderable: false,
                        searchable: false
                    }],
                    aaSorting:[[2, 'asc']],
                    columns:[
                        { data: 'action', name: 'action' },
                        { data: 'service_type', name: 'service_type'},
                        {
                            data: 'delivery_date', name: 'delivery_date'
                        },
                        {
                            data: 'job_sheet_no', name: 'job_sheet_no'
                        },
                        {
                            data: 'repair_no', name: 'repair_no'
                        },
                        { data:'status', name: 'rs.name' },
                        <?php if(in_array('service_staff' ,$enabled_modules)): ?>
                            { data: 'technecian', name: 'technecian', searchable: false},
                        <?php endif; ?>
                        { data: 'customer', name : 'contacts.name'},
                        { data: 'contact_id', name: 'contacts.contact_id'},
                        { data:'customer', name: 'contacts.mobile' },
                        { data: 'location', name: 'bl.name' },
                        { data: 'brand', name: 'b.name' },
                        { data: 'device', name: 'device.name' },
                        { data: 'device_model', name: 'rdm.name' },
                        {
                            data: 'serial_no', name: 'serial_no'
                        },
                        {
                            data: 'estimated_cost', name: 'estimated_cost'
                        },
                        <?php if(!empty($repair_settings['job_sheet_custom_field_1'])): ?>
                            {
                                data: 'custom_field_1', name: 'repair_job_sheets.custom_field_1'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_2'])): ?>
                            {
                                data: 'custom_field_2', name: 'repair_job_sheets.custom_field_2'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_3'])): ?>
                            {
                                data: 'custom_field_3', name: 'repair_job_sheets.custom_field_3'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_4'])): ?>
                            {
                                data: 'custom_field_4', name: 'repair_job_sheets.custom_field_4'
                            },
                        <?php endif; ?>
                        <?php if(!empty($repair_settings['job_sheet_custom_field_5'])): ?>
                            {
                                data: 'custom_field_5', name: 'repair_job_sheets.custom_field_5'
                            },
                        <?php endif; ?>
                        { data: 'added_by', name: 'added_by', searchable: false},
                        { data: 'created_at',
                            name: 'repair_job_sheets.created_at'
                        }
                    ],
                    "fnDrawCallback": function (oSettings) {
                        __currency_convert_recursively($('#completed_job_sheets_table'));
                    }
            });

            $(document).on('click', '#delete_job_sheet', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
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
                                if (result.success) {
                                    toastr.success(result.msg);
                                    pending_job_sheets_datatable.ajax.reload();
                                    completed_job_sheets_datatable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            <?php if(auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit')): ?>
                $(document).on('click', '.edit_job_sheet_status', function () {
                    var url = $(this).data('href');
                    $.ajax({
                        method: 'GET',
                        url: url,
                        dataType: 'html',
                        success: function(result) {
                            $('#status_modal').html(result).modal('show');
                        }
                    });
                });
            <?php endif; ?>

            $('#status_modal').on('shown.bs.modal', function (e) {

                //initialize editor
                tinymce.init({
                    selector: 'textarea#email_body',
                });

                $('#send_sms').change(function() {
                    if ($(this). is(":checked")) {
                        $('div.sms_body').fadeIn();
                    } else {
                        $('div.sms_body').fadeOut();
                    }
                });

                $('#send_email').change(function() {
                    if ($(this). is(":checked")) {
                        $('div.email_template').fadeIn();
                    } else {
                        $('div.email_template').fadeOut();
                    }
                });

                if ($('#status_id_modal').length) {
                    ;
                    $("#sms_body").val($("#status_id_modal :selected").data('sms_template'));
                    $("#email_subject").val($("#status_id_modal :selected").data('email_subject'));
                    tinymce.activeEditor.setContent($("#status_id_modal :selected").data('email_body'));  
                }

                $('#status_id_modal').on('change', function() {
                    var sms_template = $(this).find(':selected').data('sms_template');
                    var email_subject = $(this).find(':selected').data('email_subject');
                    var email_body = $(this).find(':selected').data('email_body');

                    $("#sms_body").val(sms_template);
                    $("#email_subject").val(email_subject);
                    tinymce.activeEditor.setContent(email_body);

                    if ($('#status_modal .mark-as-complete-btn').length) {
                        if ($(this).find(':selected').data('is_completed_status') == 1) 
                        {
                            $('#status_modal').find('.mark-as-complete-btn').removeClass('hide');
                            $('#status_modal').find('.mark-as-incomplete-btn').addClass('hide');
                        } else {
                            $('#status_modal').find('.mark-as-complete-btn').addClass('hide');
                            $('#status_modal').find('.mark-as-incomplete-btn').removeClass('hide');
                        }
                    }
                });
            });
            
            $('#status_modal').on('hidden.bs.modal', function(){
                tinymce.remove("textarea#email_body");
            });
            
            $(document).on('click', '.update_status_button', function(){
                $('#status_form_redirect').val($(this).data('href'));
            })
            $(document).on('submit', 'form#update_status_form', function(e){
                e.preventDefault();
                var data = $(this).serialize();
                var ladda = Ladda.create(document.querySelector('.ladda-button'));
                ladda.start();
                $.ajax({
                    method: $(this).attr("method"),
                    url: $(this).attr("action"),
                    dataType: "json",
                    data: data,
                    success: function(result){
                        ladda.stop();
                        if(result.success == true){
                            $('#status_modal').modal('hide');
                            if (result.msg) {
                                toastr.success(result.msg);
                            }

                            if ($('#status_form_redirect').val()) {
                                window.location = $('#status_form_redirect').val();
                            }
                            pending_job_sheets_datatable.ajax.reload();
                            completed_job_sheets_datatable.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change', '#location_id, #contact_id, #status_id, #technician',  function() {
                pending_job_sheets_datatable.ajax.reload();
                completed_job_sheets_datatable.ajax.reload();
            });

            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                        pending_job_sheets_datatable.ajax.reload();
                        completed_job_sheets_datatable.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                pending_job_sheets_datatable.ajax.reload();
                completed_job_sheets_datatable.ajax.reload();
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Repair\Providers/../Resources/views/job_sheet/index.blade.php ENDPATH**/ ?>