<div class="<?php echo e($class ?? '', false); ?> tw-mb-4 tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200"
    <?php if(!empty($id)): ?> id="<?php echo e($id, false); ?>" <?php endif; ?>>
    <div class="tw-p-2 sm:tw-p-3">
        <?php if(empty($header)): ?>
            <?php if(!empty($title) || !empty($tool)): ?>
                <div class="box-header">
                    <?php echo $icon ?? ''; ?>

                    <h3 class="box-title"><?php echo e($title ?? '', false); ?></h3>
                    <?php echo $tool ?? ''; ?>


                    <?php if(isset($help_text)): ?>
                        <br />
                        <small><?php echo $help_text; ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="box-header">
                <?php echo $header; ?>

            </div>
        <?php endif; ?>
        <div class="tw-flow-root tw-border-gray-200">
            <div class="">
                <div class="tw-py-2 tw-align-middle sm:tw-px-5">
                    <?php echo e($slot, false); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/components/widget.blade.php ENDPATH**/ ?>