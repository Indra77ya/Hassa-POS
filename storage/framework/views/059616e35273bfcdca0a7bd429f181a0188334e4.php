<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale(), false); ?>">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token(), false); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name', 'POS'), false); ?></title>

    <?php echo $__env->make('layouts.partials.css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('layouts.partials.extracss_auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src='https://www.google.com/recaptcha/api.js'></script>

</head>

<body class="pace-done" data-new-gr-c-s-check-loaded="14.1172.0" data-gr-ext-installed="" cz-shortcut-listen="true">
    <?php $request = app('Illuminate\Http\Request'); ?>
    <?php if(session('status') && session('status.success')): ?>
        <input type="hidden" id="status_span" data-status="<?php echo e(session('status.success'), false); ?>"
            data-msg="<?php echo e(session('status.msg'), false); ?>">
    <?php endif; ?>
    <div class="container-fluid">
        <div class="row eq-height-row">
            <div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pt-20 tw-pb-10 tw-px-5">
                <div class="row">
                    

                    <div class="tw-absolute tw-top-2 md:tw-top-5 tw-left-4 md:tw-left-8 tw-flex tw-items-center tw-gap-4"
                        style="text-align: left">
                        <a href="<?php echo e(url('/'), false); ?>">
                            <div
                                class="lg:tw-w-16 md:tw-h-16 tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-overflow-hidden tw-p-0.5 tw-mb-4">
                                <img src="<?php echo e(asset('img/logo-small.png'), false); ?>" alt="lock" class="tw-object-fill" />
                            </div>
                        </a>
                        <?php if(config('constants.SHOW_REPAIR_STATUS_LOGIN_SCREEN') && Route::has('repair-status')): ?>
                            <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                href="<?php echo e(action([\Modules\Repair\Http\Controllers\CustomerRepairStatusController::class, 'index']), false); ?>">
                                <?php echo app('translator')->get('repair::lang.repair_status'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(Route::has('member_scanner')): ?>
                            <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                href="<?php echo e(action([\Modules\Gym\Http\Controllers\MemberController::class, 'member_scanner']), false); ?>">
                                <?php echo app('translator')->get('gym::lang.gym_member_profile'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="tw-absolute tw-top-5 md:tw-top-8 tw-right-5 md:tw-right-10 tw-flex tw-items-center tw-gap-4"
                        style="text-align: left">
                        <?php if(!($request->segment(1) == 'business' && $request->segment(2) == 'register')): ?>
                            <!-- Register Url -->
                            <?php if(config('constants.allow_registration')): ?>
                            

                            <div class="tw-border-2 tw-border-white tw-rounded-full tw-h-10 md:tw-h-12 tw-w-24 tw-flex tw-items-center tw-justify-center">
                             <a href="<?php echo e(route('business.getRegister'), false); ?><?php if(!empty(request()->lang)): ?><?php echo e('?lang='.request()->lang, false); ?><?php endif; ?>"
                                    class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white">
                                    <?php echo e(__('business.register'), false); ?></a>
                            </div>

                                <!-- pricing url -->
                                <?php if(Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing'): ?>
                                    &nbsp; <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                        href="<?php echo e(action([\Modules\Superadmin\Http\Controllers\PricingController::class, 'index']), false); ?>"><?php echo app('translator')->get('superadmin::lang.pricing'); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if($request->segment(1) != 'login'): ?>
                            <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                href="<?php echo e(action([\App\Http\Controllers\Auth\LoginController::class, 'login']), false); ?><?php if(!empty(request()->lang)): ?><?php echo e('?lang='.request()->lang, false); ?><?php endif; ?>"><?php echo e(__('business.sign_in'), false); ?></a>
                        <?php endif; ?>
                        <?php echo $__env->make('layouts.partials.language_btn', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                    <div class="col-md-10 col-xs-8" style="text-align: right;">

                    </div>
                </div>
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>


    <?php echo $__env->make('layouts.partials.javascripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Scripts -->
    <script src="<?php echo e(asset('js/login.js?v=' . $asset_v), false); ?>"></script>

    <?php echo $__env->yieldContent('javascript'); ?>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();

            // $('input').iCheck({
            //     checkboxClass: 'icheckbox_square-blue',
            //     radioClass: 'iradio_square-blue',
            //     increaseArea: '20%' // optional
            // });
        });
    </script>
    <style>
        .wizard>.content {
            background-color: white !important;
        }
    </style>
</body>

</html>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/layouts/auth2.blade.php ENDPATH**/ ?>