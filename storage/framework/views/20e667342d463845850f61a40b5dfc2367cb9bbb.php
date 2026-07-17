<?php $__env->startSection('title', config('app.name', 'ultimatePOS')); ?>
<?php $request = app('Illuminate\Http\Request'); ?>
<?php $__env->startSection('content'); ?>
<div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pt-20 tw-pb-10 tw-px-5 tw-flex tw-flex-col tw-items-center tw-justify-center">
    <div class="tw-text-6xl tw-font-extrabold tw-text-center tw-text-white tw-shadow-lg tw-px-4 tw-py-2 tw-bg-blue-700 tw-rounded-md">
        <?php echo e(config('app.name', 'UltimatePOS'), false); ?>

    </div>
    
    <p class="tw-text-lg tw-font-medium tw-text-center tw-text-white tw-mt-2 tw-shadow-md tw-bg-blue-600 tw-rounded-md tw-px-3 tw-py-1">
        <?php echo e(env('APP_TITLE', ''), false); ?>

    </p>
</div>

<?php $__env->stopSection(); ?>
            
<?php echo $__env->make('layouts.auth2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/welcome.blade.php ENDPATH**/ ?>