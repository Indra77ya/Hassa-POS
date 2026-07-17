
<?php $__env->startSection('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.packages')); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('superadmin::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('superadmin::lang.packages'); ?> <small><?php echo app('translator')->get('superadmin::lang.all_packages'); ?></small></h1>
        <!-- <ol class="breadcrumb">
            <a href="#"><i class="fa fa-dashboard"></i> Level</a><br/>
            <li class="active">Here<br/>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        <?php echo $__env->make('superadmin::layouts.partials.currency', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        

        <div
            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-justify-end tw-gap-2.5">
                    
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                            href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'create']), false); ?>">
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
                            <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 tw-mt-4">
                                    <div class="box box-success hvr-grow-shadow">
                                        <div class="box-header with-border text-center">
                                            <h2 class="box-title"><?php echo e($package->name, false); ?></h2>
                                            <?php if($package->mark_package_as_popular == 1): ?>
                                                <div class="pull-right">
                                                    <span class="badge bg-green">
                                                        <?php echo app('translator')->get('superadmin::lang.popular'); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row">
                                                <?php if($package->is_private): ?>
                                                    <a href="#!" class="btn btn-box-tool">
                                                        <i class="fas fa-lock fa-lg text-warning" data-toggle="tooltip"
                                                            title="<?php echo app('translator')->get('superadmin::lang.private_superadmin_only'); ?>"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if($package->is_one_time): ?>
                                                    <a href="#!" class="btn btn-box-tool">
                                                        <i class="fas fa-dot-circle fa-lg text-info" data-toggle="tooltip"
                                                            title="<?php echo app('translator')->get('superadmin::lang.one_time_only_subscription'); ?>"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if($package->is_active == 1): ?>
                                                    <span class="badge bg-green">
                                                        <?php echo app('translator')->get('superadmin::lang.active'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-red">
                                                        <?php echo app('translator')->get('superadmin::lang.inactive'); ?>
                                                    </span>
                                                <?php endif; ?>

                                                <a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'edit'], [$package->id]), false); ?>"
                                                    class="btn btn-box-tool" title="edit"><i class="fa fa-edit"></i></a>
                                                <a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy'], [$package->id]), false); ?>"
                                                    class="btn btn-box-tool link_confirmation" title="delete"><i
                                                        class="fa fa-trash"></i></a>

                                            </div>
                                        </div>
                                        <!-- /.box-header -->
                                        <div class="box-body text-center">

                                            <?php if($package->location_count == 0): ?>
                                                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                                            <?php else: ?>
                                                <?php echo e($package->location_count, false); ?>

                                            <?php endif; ?>

                                            <?php echo app('translator')->get('business.business_locations'); ?>
                                            <br />

                                            <?php if($package->user_count == 0): ?>
                                                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                                            <?php else: ?>
                                                <?php echo e($package->user_count, false); ?>

                                            <?php endif; ?>

                                            <?php echo app('translator')->get('superadmin::lang.users'); ?>
                                            <br />

                                            <?php if($package->product_count == 0): ?>
                                                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                                            <?php else: ?>
                                                <?php echo e($package->product_count, false); ?>

                                            <?php endif; ?>

                                            <?php echo app('translator')->get('superadmin::lang.products'); ?>
                                            <br />

                                            <?php if($package->invoice_count == 0): ?>
                                                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                                            <?php else: ?>
                                                <?php echo e($package->invoice_count, false); ?>

                                            <?php endif; ?>

                                            <?php echo app('translator')->get('superadmin::lang.invoices'); ?>
                                            <br />

                                            <?php if($package->trial_days != 0): ?>
                                                <?php echo e($package->trial_days, false); ?> <?php echo app('translator')->get('superadmin::lang.trial_days'); ?>
                                                <br />
                                            <?php endif; ?>

                                            <?php if(!empty($package->custom_permissions)): ?>
                                                <?php $__currentLoopData = $package->custom_permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(isset($permission_formatted[$permission])): ?>
                                                        <?php echo e($permission_formatted[$permission], false); ?>

                                                        <br />
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>

                                            <h3 class="text-center">
                                                <?php if($package->price != 0): ?>
                                                    <span class="display_currency" data-currency_symbol="true">
                                                        <?php echo e($package->price, false); ?>

                                                    </span>

                                                    <small>
                                                        / <?php echo e($package->interval_count, false); ?>

                                                        <?php echo e(__('lang_v1.' . $package->interval), false); ?>

                                                    </small>
                                                <?php else: ?>
                                                    <?php echo app('translator')->get('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . __('lang_v1.' . $package->interval)]); ?>
                                                <?php endif; ?>
                                            </h3>

                                        </div>
                                        <!-- /.box-body -->

                                        <div class="box-footer text-center">
                                            <?php echo e($package->description, false); ?>

                                        </div>
                                    </div>
                                    <!-- /.box -->
                                </div>
                                <?php if($loop->iteration % 3 == 0): ?>
                                    <div class="clearfix"></div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <div class="col-md-12">
                                <?php echo e($packages->links(), false); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/packages/index.blade.php ENDPATH**/ ?>