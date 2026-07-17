
<?php $__env->startSection('title', __('repair::lang.repair') . ' ' . __('business.dashboard')); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('repair::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black" >
            <?php echo app('translator')->get('repair::lang.repair'); ?>
            <small><?php echo app('translator')->get('business.dashboard'); ?></small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content no-print">
        <div class="row">
            <div class="col-md-12">
                <?php $__env->startComponent('components.widget', ['title' => __('repair::lang.job_sheets_by_status') ]); ?>
                    <?php $__empty_1 = true; $__currentLoopData = $job_sheets_by_status; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job_sheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="small-box" style="background-color: <?php echo e($job_sheet->color, false); ?>;color: #fff;">
                                <div class="inner">
                                    <p><?php echo e($job_sheet->status_name, false); ?></p>
                                    <h3><?php echo e($job_sheet->total_job_sheets, false); ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h4><?php echo app('translator')->get('repair::lang.no_report_found'); ?></h4>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php echo $__env->renderComponent(); ?>
            </div>
        </div>
        <?php if(in_array('service_staff', $enabled_modules)): ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php $__env->startComponent('components.widget'); ?>
                        <?php $__env->slot('title'); ?>
                            <?php echo app('translator')->get('repair::lang.job_sheets_by_service_staff'); ?>
                        <?php $__env->endSlot(); ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo app('translator')->get('restaurant.service_staff'); ?></th>
                                        <th><?php echo app('translator')->get('repair::lang.total_job_sheets'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $job_sheets_by_service_staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job_sheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration, false); ?></td>
                                            <td><?php echo e($job_sheet->service_staff, false); ?></td>
                                            <td><?php echo e($job_sheet->total_job_sheets, false); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php echo $__env->renderComponent(); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-xs-12">
                <?php $__env->startComponent('components.widget'); ?>
                    <?php $__env->slot('title'); ?>
                        <?php echo app('translator')->get('repair::lang.trending_brands'); ?>
                    <?php $__env->endSlot(); ?>
                    <?php echo $trending_brand_chart->container(); ?>

                <?php echo $__env->renderComponent(); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?php $__env->startComponent('components.widget'); ?>
                    <?php $__env->slot('title'); ?>
                        <?php echo app('translator')->get('repair::lang.trending_devices'); ?>
                    <?php $__env->endSlot(); ?>
                    <?php echo $trending_devices_chart->container(); ?>

                <?php echo $__env->renderComponent(); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?php $__env->startComponent('components.widget'); ?>
                    <?php $__env->slot('title'); ?>
                        <?php echo app('translator')->get('repair::lang.trending_device_models'); ?>
                    <?php $__env->endSlot(); ?>
                    <?php echo $trending_dm_chart->container(); ?>

                <?php echo $__env->renderComponent(); ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <?php echo $trending_devices_chart->script(); ?>

    <?php echo $trending_dm_chart->script(); ?>

    <?php echo $trending_brand_chart->script(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Repair\Providers/../Resources/views/dashboard/index.blade.php ENDPATH**/ ?>