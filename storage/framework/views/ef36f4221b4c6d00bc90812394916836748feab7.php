<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo e(action([\Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard']), false); ?>"><i class="fa fas fa-users"></i> <?php echo e(__('essentials::lang.hrm'), false); ?></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.crud_leave_type')): ?>
                        <li <?php if(request()->segment(2) == 'leave-type'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class, 'index']), false); ?>"><?php echo app('translator')->get('essentials::lang.leave_type'); ?></a></li>
                    <?php endif; ?>
                    <?php if(auth()->user()->can('essentials.crud_all_leave') || auth()->user()->can('essentials.crud_own_leave')): ?>
                        <li <?php if(request()->segment(2) == 'leave'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']), false); ?>"><?php echo app('translator')->get('essentials::lang.leave'); ?></a></li>
                    <?php endif; ?>
                    <?php if(auth()->user()->can('essentials.crud_all_attendance') || auth()->user()->can('essentials.view_own_attendance')): ?>
                    <li <?php if(request()->segment(2) == 'attendance'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'index']), false); ?>"><?php echo app('translator')->get('essentials::lang.attendance'); ?></a></li>
                    <?php endif; ?>
                    <li <?php if(request()->segment(2) == 'payroll'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']), false); ?>"><?php echo app('translator')->get('essentials::lang.payroll'); ?></a></li>

                    <li <?php if(request()->segment(2) == 'holiday'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'index']), false); ?>"><?php echo app('translator')->get('essentials::lang.holiday'); ?></a></li>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.crud_department')): ?>
                    <li <?php if(request()->get('type') == 'hrm_department'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=hrm_department', false); ?>"><?php echo app('translator')->get('essentials::lang.departments'); ?></a></li>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.crud_designation')): ?>
                    <li <?php if(request()->get('type') == 'hrm_designation'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=hrm_designation', false); ?>"><?php echo app('translator')->get('essentials::lang.designations'); ?></a></li>
                    <?php endif; ?>

                    <!--<?php if(auth()->user()->can('essentials.access_sales_target')): ?>-->
                    <!--    <li <?php if(request()->segment(1) == 'hrm' && request()->segment(2) == 'sales-target'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\SalesTargetController::class, 'index']), false); ?>"><?php echo app('translator')->get('essentials::lang.sales_target'); ?></a></li>-->
                    <!--<?php endif; ?>-->

                    <?php if(auth()->user()->can('edit_essentials_settings')): ?>
                        <li <?php if(request()->segment(1) == 'hrm' && request()->segment(2) == 'settings'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']), false); ?>"><?php echo app('translator')->get('business.settings'); ?></a></li>
                    <?php endif; ?>
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/layouts/nav_hrm.blade.php ENDPATH**/ ?>