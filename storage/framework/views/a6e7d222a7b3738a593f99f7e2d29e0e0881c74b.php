<section class="no-print">
    <nav class="navbar-default tw-transition-all tw-duration-5000 tw-shrink-0 tw-rounded-2xl tw-m-[16px] tw-border-2 !tw-bg-white">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false" style="margin-top: 3px; margin-right: 3px;">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']), false); ?>"><i class="fa fas fa-users-cog"></i> <?php echo e(__('superadmin::lang.superadmin'), false); ?></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li <?php if(request()->segment(1) == 'superadmin' && request()->segment(2) == 'business'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']), false); ?>"><?php echo app('translator')->get('superadmin::lang.all_business'); ?></a></li>

                    <li <?php if(request()->segment(1) == 'superadmin' && request()->segment(2) == 'superadmin-subscription'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'index']), false); ?>"><?php echo app('translator')->get('superadmin::lang.subscription'); ?></a></li>

                    <li <?php if(request()->segment(1) == 'superadmin' && request()->segment(2) == 'packages'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'index']), false); ?>"><?php echo app('translator')->get('superadmin::lang.subscription_packages'); ?></a></li>

                    <li <?php if(request()->segment(1) == 'superadmin' && request()->segment(2) == 'coupons'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'index']), false); ?>"><?php echo app('translator')->get('superadmin::lang.all_coupons'); ?></a></li>

                    <li <?php if(request()->segment(1) == 'superadmin' && request()->segment(2) == 'settings'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\SuperadminSettingsController::class, 'edit']), false); ?>"><?php echo app('translator')->get('superadmin::lang.super_admin_settings'); ?></a></li>

                    <li <?php if(request()->segment(1) == 'superadmin' && request()->segment(2) == 'communicator'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'index']), false); ?>"><?php echo app('translator')->get('superadmin::lang.communicator'); ?></a></li>
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/layouts/nav.blade.php ENDPATH**/ ?>