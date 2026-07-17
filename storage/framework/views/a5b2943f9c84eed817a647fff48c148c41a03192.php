
<?php $__env->startSection('title', __('superadmin::lang.superadmin') . ' | Business'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('superadmin::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('superadmin::lang.all_business'); ?>
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold"><?php echo app('translator')->get('superadmin::lang.manage_business'); ?></small>
        </h1>
        <!-- <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                    <li class="active">Here</li>
                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
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
                    <?php echo Form::label('subscription_status', __('superadmin::lang.subscription_status') . ':'); ?>

                    <?php echo Form::select('subscription_status', $subscription_statuses, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]); ?>

                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('is_active', __('sale.status') . ':'); ?>

                    <?php echo Form::select(
                        'is_active',
                        ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                        null,
                        ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')],
                    ); ?>

                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('last_transaction_date', __('superadmin::lang.last_transaction_date') . ':'); ?>

                    <?php echo Form::select('last_transaction_date', $last_transaction_date, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('messages.please_select'),
                    ]); ?>

                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('no_transaction_since', __('superadmin::lang.no_transaction_since') . ':'); ?>

                    <?php echo Form::select('no_transaction_since', $last_transaction_date, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('messages.please_select'),
                    ]); ?>

                </div>
            </div>
        <?php echo $__env->renderComponent(); ?>


        <div
            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-justify-end tw-gap-2.5">
                    
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                        href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'create']), false); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg> <?php echo app('translator')->get('messages.add'); ?>
                    </a>
                </div>


                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('superadmin')): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="superadmin_business_table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <?php echo app('translator')->get('superadmin::lang.registered_on'); ?>
                                            </th>
                                            <th><?php echo app('translator')->get('superadmin::lang.business_name'); ?></th>
                                            <th><?php echo app('translator')->get('business.owner'); ?></th>
                                            <th><?php echo app('translator')->get('business.email'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.owner_number'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.business_contact_number'); ?></th>
                                            <th><?php echo app('translator')->get('business.address'); ?></th>
                                            <th><?php echo app('translator')->get('sale.status'); ?></th>
                                            <th><?php echo app('translator')->get('superadmin::lang.current_subscription'); ?></th>
                                            <th><?php echo app('translator')->get('business.created_by'); ?></th>
                                            <th class="tw-w-full"><?php echo app('translator')->get('superadmin::lang.action'); ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </section>
    <!-- /.content -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>

    <script type="text/javascript">
        $(document).ready(function() {
            superadmin_business_table = $('#superadmin_business_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: {
                    url: "<?php echo e(action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']), false); ?>",
                    data: function(d) {
                        d.package_id = $('#package_id').val();
                        d.subscription_status = $('#subscription_status').val();
                        d.is_active = $('#is_active').val();
                        d.last_transaction_date = $('#last_transaction_date').val();
                        d.no_transaction_since = $('#no_transaction_since').val();
                    },
                },
                aaSorting: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'created_at',
                        name: 'business.created_at'
                    },
                    {
                        data: 'name',
                        name: 'business.name'
                    },
                    {
                        data: 'owner_name',
                        name: 'owner_name',
                        searchable: false
                    },
                    {
                        data: 'owner_email',
                        name: 'u.email'
                    },
                    {
                        data: 'contact_number',
                        name: 'u.contact_number'
                    },
                    {
                        data: 'business_contact_number',
                        name: 'business_contact_number'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        searchable: false
                    },
                    {
                        data: 'current_subscription',
                        name: 'p.name'
                    },
                    {
                        data: 'biz_creator',
                        name: 'biz_creator',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $('#package_id, #subscription_status, #is_active, #last_transaction_date, #no_transaction_since')
                .change(function() {
                    superadmin_business_table.ajax.reload();
                });
        });
        $(document).on('click', 'a.delete_business_confirmation', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: "Once deleted, you will not be able to recover this business!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    window.location.href = $(this).attr('href');
                }
            });
        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/business/index.blade.php ENDPATH**/ ?>