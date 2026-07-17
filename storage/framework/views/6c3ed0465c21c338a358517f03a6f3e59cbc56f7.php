<div
    class="tw-mb-4 tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl  tw-ring-1 tw-ring-gray-200">
    <div class="tw-p-4 sm:tw-p-5">
        <div class="tw-flex tw-items-center tw-gap-4">
            <?php if(!empty($svg)): ?>
                <div
                    class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0 <?php echo e($svg_bg ?? 'tw-bg-sky-100', false); ?> <?php echo e($svg_text ?? 'tw-text-sky-500', false); ?> ">

                    <?php echo $svg; ?>

                </div>
            <?php endif; ?>

            <div class="tw-flex-1 tw-min-w-0">
                <?php echo e($slot, false); ?>

            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\laragon\www\Hassa POS\resources\views/components/static.blade.php ENDPATH**/ ?>