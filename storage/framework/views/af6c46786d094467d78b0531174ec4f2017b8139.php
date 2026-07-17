<?php if(isset($moduleAssets) && is_array($moduleAssets)): ?>
    
    <?php if(!empty($moduleAssets['css'])): ?>
        <?php $__currentLoopData = $moduleAssets['css']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $css): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <link rel="stylesheet" href="<?php echo e(Module::asset($css['path']), false); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
    
    
    <?php if(!empty($moduleAssets['js'])): ?>
        <?php $__currentLoopData = $moduleAssets['js']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $js): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <script src="<?php echo e(Module::asset($js['path']), false); ?>"></script>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
<?php endif; ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/layouts/module-assets.blade.php ENDPATH**/ ?>