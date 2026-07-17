
<?php $__env->startSection('title', 'Superadmin Subscription'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('superadmin::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('superadmin::lang.subscription'); ?>
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold"><?php echo app('translator')->get('superadmin::lang.view_subscription'); ?></small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <?php echo $__env->make('superadmin::layouts.partials.currency', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php $__env->startComponent('components.filters', ['title' => __('report.filters')]); ?>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('package_id', __('superadmin::lang.packages') . ':'); ?>

                    <?php echo Form::select('package_id', $packages, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]); ?>

                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('subscription_status', __('superadmin::lang.status') . ':'); ?>

                    <?php echo Form::select('subscription_status', $subscription_statuses, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]); ?>

                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('created_at', __('lang_v1.created_at') . ':'); ?>

                    <?php echo Form::text('created_at', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'readonly',
                    ]); ?>

                </div>
            </div>
        <?php echo $__env->renderComponent(); ?>

        <div
            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('superadmin')): ?>
                                <table class="table table-bordered table-striped" id="superadmin_subscription_table">
                                    <thead>
                                        <tr>
                                            <th><?php echo app('translator')->get('superadmin::lang.business_name'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.package_name'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.status'); ?></th>
                                            <th><?php echo app('translator')->get('lang_v1.created_at'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.start_date'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.trial_end_date'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.end_date'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.coupon_code'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.original_price'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.paid_amount'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.paid_via'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.payment_transaction_id'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.action'); ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"></div>

    </section>
    <!-- /.content -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
    <script>
        $(document).ready(function() {

            $('#created_at').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#created_at').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    superadmin_subscription_table.ajax.reload();
                }
            );
            $('#created_at').on('cancel.daterangepicker', function(ev, picker) {
                $('#created_at').val('');
                superadmin_subscription_table.ajax.reload();
            });

            $('#package_id, #subscription_status').change(function() {
                superadmin_subscription_table.ajax.reload();
            });

            // superadmin_subscription_table
            var superadmin_subscription_table = $('#superadmin_subscription_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: {
                    url: '/superadmin/superadmin-subscription',
                    data: function(d) {
                        if ($('#package_id').length) {
                            d.package_id = $('#package_id').val();
                        }
                        if ($('#subscription_status').length) {
                            d.status = $('#subscription_status').val();
                        }

                        var start = '';
                        var end = '';
                        if ($('#created_at').val()) {
                            start = $('input#created_at')
                                .data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            end = $('input#created_at')
                                .data('daterangepicker')
                                .endDate.format('YYYY-MM-DD');
                        }
                        d.start_date = start;
                        d.end_date = end;

                        d = __datatable_ajax_callback(d);
                    },
                },
                columnDefs: [{
                    "targets": 12,
                    "orderable": false,
                    "searchable": false
                }],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#superadmin_subscription_table'), true);
                }
            });


            // change_status button
            $(document).on('click', 'button.change_status', function() {
                $("div#statusModal").load($(this).data('href'), function() {
                    $(this).modal('show');
                    $("form#status_change_form").submit(function(e) {
                        e.preventDefault();
                        var url = $(this).attr("action");
                        var data = $(this).serialize();
                        $.ajax({
                            method: "POST",
                            dataType: "json",
                            data: data,
                            url: url,
                            success: function(result) {
                                if (result.success == true) {
                                    $("div#statusModal").modal('hide');
                                    toastr.success(result.msg);
                                    superadmin_subscription_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    });
                });
            });

            $(document).on('shown.bs.modal', '.view_modal', function() {
                $('.edit-subscription-modal .datepicker').datepicker({
                    autoclose: true,
                    format: datepicker_date_format
                });
                $("form#edit_subscription_form").submit(function(e) {
                    e.preventDefault();
                    var url = $(this).attr("action");
                    var data = $(this).serialize();
                    $.ajax({
                        method: "POST",
                        dataType: "json",
                        data: data,
                        url: url,
                        success: function(result) {
                            if (result.success == true) {
                                $("div.view_modal").modal('hide');
                                toastr.success(result.msg);
                                superadmin_subscription_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                });
            });

        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/superadmin_subscription/index.blade.php ENDPATH**/ ?>