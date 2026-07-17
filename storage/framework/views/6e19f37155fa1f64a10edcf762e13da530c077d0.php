<?php $__env->startSection('title', __('essentials::lang.payroll')); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('essentials::layouts.nav_hrm', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('essentials::lang.payroll'); ?>
    </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#payroll_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fas fa-coins" aria-hidden="true"></i>
                            <?php echo app('translator')->get('essentials::lang.all_payrolls'); ?>
                        </a>
                    </li>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.view_all_payroll')): ?>
                        <li>
                            <a href="#payroll_group_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-layer-group" aria-hidden="true"></i>
                                <?php echo app('translator')->get('essentials::lang.all_payroll_groups'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(auth()->user()->can('essentials.view_allowance_and_deduction') || auth()->user()->can('essentials.add_allowance_and_deduction')): ?>
                        <li>
                            <a href="#pay_component_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fab fa-gg-circle" aria-hidden="true"></i>
                                <?php echo app('translator')->get( 'essentials::lang.pay_components' ); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="payroll_tab">
                        <div class="row">
                            <div class="col-md-12">
                                <?php $__env->startComponent('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid', 'closed' => true]); ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.view_all_payroll')): ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?php echo Form::label('user_id_filter', __('essentials::lang.employee') . ':'); ?>

                                                <?php echo Form::select('user_id_filter', $employees, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?php echo Form::label('location_id_filter',  __('purchase.business_location') . ':'); ?>


                                                <?php echo Form::select('location_id_filter', $locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); ?>

                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?php echo Form::label('department_id', __('essentials::lang.department') . ':'); ?>

                                                <?php echo Form::select('department_id', $departments, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <?php echo Form::label('designation_id', __('essentials::lang.designation') . ':'); ?>

                                                <?php echo Form::select('designation_id', $designations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); ?>

                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <?php echo Form::label('month_year_filter', __( 'essentials::lang.month_year' ) . ':'); ?>

                                            <div class="input-group">
                                                <?php echo Form::text('month_year_filter', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.month_year' ) ]); ?>

                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php echo $__env->renderComponent(); ?>
                            </div>
                        </div>
                        <div class="row">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.create_payroll')): ?>
                                <div class="col-md-12">
                                    <button type="button" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                        data-toggle="modal" data-target="#payroll_modal">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> <?php echo app('translator')->get( 'messages.add' ); ?>
                                    </button>
                                </div>
                                <br><br><br>
                            <?php endif; ?>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payrolls_table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th><?php echo app('translator')->get( 'essentials::lang.employee' ); ?></th>
                                                <th><?php echo app('translator')->get( 'essentials::lang.department' ); ?></th>
                                                <th><?php echo app('translator')->get( 'essentials::lang.designation' ); ?></th>
                                                <th><?php echo app('translator')->get( 'essentials::lang.month_year' ); ?></th>
                                                <th><?php echo app('translator')->get( 'purchase.ref_no' ); ?></th>
                                                <th><?php echo app('translator')->get( 'sale.total_amount' ); ?></th>
                                                <th><?php echo app('translator')->get( 'sale.payment_status' ); ?></th>
                                                <th><?php echo app('translator')->get( 'messages.action' ); ?></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>                                
                            </div>
                        </div>
                    </div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.view_all_payroll')): ?>
                        <div class="tab-pane" id="payroll_group_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th><?php echo app('translator')->get('essentials::lang.name'); ?></th>
                                                    <th><?php echo app('translator')->get('sale.status'); ?></th>
                                                    <th><?php echo app('translator')->get( 'sale.payment_status' ); ?></th>
                                                    <th><?php echo app('translator')->get('essentials::lang.total_gross_amount'); ?></th>
                                                    <th><?php echo app('translator')->get('lang_v1.added_by'); ?></th>
                                                    <th><?php echo app('translator')->get('business.location'); ?></th>
                                                    <th><?php echo app('translator')->get('lang_v1.created_at'); ?></th>
                                                    <th><?php echo app('translator')->get( 'messages.action' ); ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(auth()->user()->can('essentials.view_allowance_and_deduction') || auth()->user()->can('essentials.add_allowance_and_deduction')): ?>
                        <div class="tab-pane" id="pay_component_tab">
                            <div class="row">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.add_allowance_and_deduction')): ?>
                                    <div class="col-md-12">
                                        <button type="button" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right btn-modal"
                                            data-href="<?php echo e(action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'create']), false); ?>" data-container="#add_allowance_deduction_modal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 5l0 14" />
                                                <path d="M5 12l14 0" />
                                            </svg> <?php echo app('translator')->get( 'messages.add' ); ?>
                                        </button>
                                    </div><br><br><br>
                                <?php endif; ?>
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="ad_pc_table" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th><?php echo app('translator')->get( 'lang_v1.description' ); ?></th>
                                                    <th><?php echo app('translator')->get( 'lang_v1.type' ); ?></th>
                                                    <th><?php echo app('translator')->get( 'sale.amount' ); ?></th>
                                                    <th><?php echo app('translator')->get( 'essentials::lang.applicable_date' ); ?></th>
                                                    <th><?php echo app('translator')->get( 'essentials::lang.employee' ); ?></th>
                                                    <th><?php echo app('translator')->get( 'messages.action' ); ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="user_leave_summary"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.create_payroll')): ?>
        <?php if ($__env->exists('essentials::payroll.payroll_modal')) echo $__env->make('essentials::payroll.payroll_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
    <div class="modal fade" id="add_allowance_deduction_modal" tabindex="-1" role="dialog"
 aria-labelledby="gridSystemModalLabel"></div>
</section>
<!-- /.content -->
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
    <script type="text/javascript">
        $(document).ready( function(){
            payrolls_table = $('#payrolls_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: {
                    url: "<?php echo e(action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']), false); ?>",
                    data: function (d) {
                        if ($('#user_id_filter').length) {
                            d.user_id = $('#user_id_filter').val();
                        }
                        if ($('#location_id_filter').length) {
                            d.location_id = $('#location_id_filter').val();
                        }
                        d.month_year = $('#month_year_filter').val();
                        if ($('#department_id').length) {
                            d.department_id = $('#department_id').val();
                        }
                        if ($('#designation_id').length) {
                            d.designation_id = $('#designation_id').val();
                        }
                    },
                },
                columnDefs: [
                    {
                        targets: 7,
                        orderable: false,
                        searchable: false,
                    },
                ],
                aaSorting: [[4, 'desc']],
                columns: [
                    { data: 'user', name: 'user' },
                    { data: 'department', name: 'dept.name' },
                    { data: 'designation', name: 'dsgn.name' },
                    { data: 'transaction_date', name: 'transaction_date'},
                    { data: 'ref_no', name: 'ref_no'},
                    { data: 'final_total', name: 'final_total'},
                    { data: 'payment_status', name: 'payment_status'},
                    { data: 'action', name: 'action' },
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#payrolls_table'));
                },
            });

            $(document).on('change', '#user_id_filter, #month_year_filter, #department_id, #designation_id, #location_id_filter', function() {
                payrolls_table.ajax.reload();
            });

            if ($('#add_payroll_step1').length) {
                $('#add_payroll_step1').validate();
                $('#employee_id').select2({
                    dropdownParent: $('#payroll_modal')
                });
            }

            $('div.view_modal').on('shown.bs.modal', function(e) {
                __currency_convert_recursively($('.view_modal'));
            });

            $('#month_year, #month_year_filter').datepicker({
                autoclose: true,
                format: 'mm/yyyy',
                minViewMode: "months"
            });

            //pay components
            <?php if(auth()->user()->can('essentials.view_allowance_and_deduction') || auth()->user()->can('essentials.add_allowance_and_deduction')): ?>
                $('#add_allowance_deduction_modal').on('shown.bs.modal', function(e) {
                    var $p = $(this);
                    $('#add_allowance_deduction_modal .select2').select2({dropdownParent:$p});
                    $('#add_allowance_deduction_modal #applicable_date').datepicker();
                    
                });

                $(document).on('submit', 'form#add_allowance_form', function(e) {
                    e.preventDefault();
                    $(this).find('button[type="submit"]').attr('disabled', true);
                    var data = $(this).serialize();

                    $.ajax({
                        method: $(this).attr('method'),
                        url: $(this).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                $('div#add_allowance_deduction_modal').modal('hide');
                                toastr.success(result.msg);
                                ad_pc_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                });
                
                ad_pc_table = $('#ad_pc_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "<?php echo e(action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'index']), false); ?>",
                    columns: [
                        { data: 'description', name: 'description' },
                        { data: 'type', name: 'type' },
                        { data: 'amount', name: 'amount' },
                        { data: 'applicable_date', name: 'applicable_date' },
                        { data: 'employees', searchable: false, orderable: false },
                        { data: 'action', name: 'action' }
                    ],
                    fnDrawCallback: function(oSettings) {
                        __currency_convert_recursively($('#ad_pc_table'));
                    },
                });

                $(document).on('click', '.delete-allowance', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).data('href');
                            var data = $(this).serialize();

                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                data: data,
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        ad_pc_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            <?php endif; ?>
            //payroll groups
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.view_all_payroll')): ?>
                payroll_group_table = $('#payroll_group_table').DataTable({
                        processing: true,
                        serverSide: true,
                        fixedHeader:false,
                        ajax: "<?php echo e(action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'payrollGroupDatatable']), false); ?>",
                        aaSorting: [[6, 'desc']],
                        columns: [
                            { data: 'name', name: 'essentials_payroll_groups.name' },
                            { data: 'status', name: 'essentials_payroll_groups.status' },
                            { data: 'payment_status', name: 'essentials_payroll_groups.payment_status' },
                            { data: 'gross_total', name: 'essentials_payroll_groups.gross_total' },
                            { data: 'added_by', name: 'added_by' },
                            { data: 'location_name', name: 'BL.name' },
                            { data: 'created_at', name: 'essentials_payroll_groups.created_at', searchable: false},
                            { data: 'action', name: 'action', searchable: false, orderable: false}
                        ]
                    });
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.delete_payroll')): ?>
                $(document).on('click', '.delete-payroll', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            var data = $(this).serialize();

                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                data: data,
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        payroll_group_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            <?php endif; ?>

            $(document).on('change', '#primary_work_location', function () {
                let location_id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "<?php echo e(action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'getEmployeesBasedOnLocation']), false); ?>",
                    dataType: 'json',
                    data: {
                        'location_id' : location_id
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('select#employee_ids').html('');
                            $('select#employee_ids').html(result.employees_html);
                        }
                    }
                });
            });
        });
    </script>
    <script src="<?php echo e(asset('js/payment.js?v=' . $asset_v), false); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/payroll/index.blade.php ENDPATH**/ ?>