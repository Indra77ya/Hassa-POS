<div class="col-md-4 tw-mb-5 <?php echo e($package->interval, false); ?> tw-relative price_card">
    <div
        class="tw-flex tw-flex-col tw-gap-6 tw-p-6 tw-shadow bg-white tw-rounded-2xl tw-shadow-lg tw-transition-all tw-duration-700 hover:tw-scale-110 tw-cursor-pointer">

        <?php if($package->mark_package_as_popular == 1): ?>
            <div class="tw-dw-badge tw-dw-badge-primary tw-self-center tw-badge-lg">
                <?php echo app('translator')->get('superadmin::lang.popular'); ?>
            </div>
        <?php endif; ?>


        <div class="tw-flex tw-flex-col tw-text-center">
            <h2 class="md:tw-text-xl tw-text-base tw-text-[#1f1f1f]"><?php echo e($package->name, false); ?></h2>

            <h3
                class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-inline-block tw-text-transparent tw-bg-clip-text hover:tw-text-[#467BF5] tw-font-semibold tw-text-2xl md:tw-text-3xl">
                <?php
                    $interval_type = !empty($intervals[$package->interval])
                        ? $intervals[$package->interval]
                        : __('lang_v1.' . $package->interval);
                ?>
                <?php if($package->price != 0): ?>
                    <span class="display_currency" data-use_page_currency="true" data-currency_symbol="true">
                        <?php echo e($package->price, false); ?>

                    </span>

                    
                    / <?php echo e($package->interval_count, false); ?> <?php echo e($interval_type, false); ?>

                    
                <?php else: ?>
                    <?php echo app('translator')->get('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . $interval_type]); ?>
                <?php endif; ?>
            </h3>

            <span class="tw-text-sm tw-text-gray-700"><?php echo e($package->description, false); ?></span>
        </div>

        <!-- Features -->
        <div class="tw-flex tw-flex-col tw-text-white">
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                <?php if($package->location_count == 0): ?>
                    <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                <?php else: ?>
                    <?php echo e($package->location_count, false); ?>

                <?php endif; ?>

                <?php echo app('translator')->get('business.business_locations'); ?>
            </div>
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                <?php if($package->user_count == 0): ?>
                    <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                <?php else: ?>
                    <?php echo e($package->user_count, false); ?>

                <?php endif; ?>

                <?php echo app('translator')->get('superadmin::lang.users'); ?>
            </div>
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                <?php if($package->product_count == 0): ?>
                    <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                <?php else: ?>
                    <?php echo e($package->product_count, false); ?>

                <?php endif; ?>

                <?php echo app('translator')->get('superadmin::lang.products'); ?>
            </div>
            <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                <i class="fa fa-check tw-text-accent"></i>
                <?php if($package->invoice_count == 0): ?>
                    <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
                <?php else: ?>
                    <?php echo e($package->invoice_count, false); ?>

                <?php endif; ?>

                <?php echo app('translator')->get('superadmin::lang.invoices'); ?>
            </div>

            <?php if(!empty($package->custom_permissions)): ?>
                <?php $__currentLoopData = $package->custom_permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(isset($permission_formatted[$permission])): ?>
                        <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                            <i class="fa fa-check tw-text-accent"></i>
                            <?php echo e($permission_formatted[$permission], false); ?>

                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <?php if($package->trial_days != 0): ?>
                <div class="tw-flex tw-gap-2 tw-items-center tw-text-[#1f1f1f]">
                    <i class="fa fa-check tw-text-accent"></i>
                    <?php echo e($package->trial_days, false); ?> <?php echo app('translator')->get('superadmin::lang.trial_days'); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if($package->enable_custom_link == 1): ?>
            <a href="<?php echo e($package->custom_link, false); ?>"
                class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-tw-w-full tw-tw-max-w-full tw-mt-2 tw-flex tw-items-center tw-justify-center hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700"><?php echo e($package->custom_link_text, false); ?></a>
        <?php else: ?>
            <?php if(isset($action_type) && $action_type == 'register'): ?>
                <a href="<?php echo e(route('business.getRegister'), false); ?>?package=<?php echo e($package->id, false); ?>"
                    class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-tw-w-full tw-tw-max-w-full tw-mt-2 tw-flex tw-items-center tw-justify-center hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700">
                    <?php if($package->price != 0): ?>
                        <?php echo app('translator')->get('superadmin::lang.register_subscribe'); ?>
                    <?php else: ?>
                        <?php echo app('translator')->get('superadmin::lang.register_free'); ?>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pay'], [$package->id]), false); ?>"
                    class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-tw-w-full tw-tw-max-w-full tw-mt-2 tw-flex tw-items-center tw-justify-center hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700">
                    <?php if($package->price != 0): ?>
                        <?php echo app('translator')->get('superadmin::lang.pay_and_subscribe'); ?>
                    <?php else: ?>
                        <?php echo app('translator')->get('superadmin::lang.subscribe'); ?>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/subscription/partials/package_card.blade.php ENDPATH**/ ?>