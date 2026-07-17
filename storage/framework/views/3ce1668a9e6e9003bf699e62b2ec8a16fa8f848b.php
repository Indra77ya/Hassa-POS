<details class="tw-dw-dropdown tw-dw-dropdown-end" style="margin: 10px;">
    <summary class="tw-bg-transparent tw-text-white tw-font-medium tw-text-sm md:tw-text-base select-none">
        <?php echo e(isset($_GET['lang']) ? config('constants.langs')[$_GET['lang']]['full_name'] : config('constants.langs')[config('app.locale')]['full_name'], false); ?>

    </summary>
    <ul
        class="tw-p-2 tw-shadow tw-dw-menu tw-dw-dropdown-content tw-z-[1] tw-w-48 md:tw-w-56 tw-bg-white tw-rounded-xl tw-mt-3">
        <?php $__currentLoopData = config('constants.langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><a value="<?php echo e($key, false); ?>" class="change_lang"> <?php echo e($val['full_name'], false); ?></a>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</details>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/layouts/partials/language_btn.blade.php ENDPATH**/ ?>