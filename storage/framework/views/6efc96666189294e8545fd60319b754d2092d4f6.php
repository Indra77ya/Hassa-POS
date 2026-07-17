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
                <a class="navbar-brand" href="<?php echo e(action([\Modules\Repair\Http\Controllers\DashboardController::class, 'index']), false); ?>"><i class="fas fa-wrench"></i> <?php echo e(__('repair::lang.repair'), false); ?></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <?php if(auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all')): ?>
                        <li <?php if(request()->segment(2) == 'job-sheet' && empty(request()->segment(3))): ?> class="active" <?php endif; ?>>
                            <a href="<?php echo e(action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'index']), false); ?>">
                                <?php echo app('translator')->get('repair::lang.job_sheets'); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('job_sheet.create')): ?>
                        <li <?php if(request()->segment(2) == 'job-sheet' && request()->segment(3) == 'create'): ?> class="active" <?php endif; ?>>
                            <a href="<?php echo e(action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create']), false); ?>">
                                <?php echo app('translator')->get('repair::lang.add_job_sheet'); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(auth()->user()->can('repair.view') || auth()->user()->can('repair.view_own')): ?>
                        <li <?php if(request()->segment(2) == 'repair' && empty(request()->segment(3))): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Repair\Http\Controllers\RepairController::class, 'index']), false); ?>"><?php echo app('translator')->get('repair::lang.list_invoices'); ?></a></li>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('repair.create')): ?>
                        <li <?php if(request()->segment(2) == 'repair' && request()->segment(3) == 'create'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=repair', false); ?>"><?php echo app('translator')->get('repair::lang.add_invoice'); ?></a></li>
                    <?php endif; ?>

                    <?php if(auth()->user()->can('brand.view') || auth()->user()->can('brand.create')): ?>
                        <li <?php if(request()->segment(1) == 'brands'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\App\Http\Controllers\BrandController::class, 'index']), false); ?>"><?php echo app('translator')->get('brand.brands'); ?></a></li>
                    <?php endif; ?>

                    <?php if(auth()->user()->can('edit_repair_settings')): ?>
                        <li <?php if(request()->segment(1) == 'repair' && request()->segment(2) == 'repair-settings'): ?> class="active" <?php endif; ?>><a href="<?php echo e(action([\Modules\Repair\Http\Controllers\RepairSettingsController::class, 'index']), false); ?>"><?php echo app('translator')->get('messages.settings'); ?></a></li>
                    <?php endif; ?>
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section><?php /**PATH C:\laragon\www\Hassa POS\Modules\Repair\Providers/../Resources/views/layouts/nav.blade.php ENDPATH**/ ?>