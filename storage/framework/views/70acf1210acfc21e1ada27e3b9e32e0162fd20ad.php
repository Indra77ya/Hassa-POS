
<?php $__env->startSection('title', __('superadmin::lang.pricing')); ?>

<?php $__env->startSection('content'); ?>
    <div class="">
        <?php echo $__env->make('superadmin::layouts.partials.currency', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="pricing">
            <div class="tw-mt-20">
                <div class="tw-flex tw-flex-col tw-items-center">

                    <div class="tw-flex tw-flex-col tw-gap-2 tw-text-center">
                        <h2 class="tw-font-bold tw-text-3xl tw-text-white"><?php echo app('translator')->get('superadmin::lang.pricing'); ?></h2>
                        <h3 class="tw-text-sm tw-font-medium tw-text-white">
                            <?php echo app('translator')->get('superadmin::lang.choose_your_pricing_plan', ['app_name' => config('app.name', 'ultimatePOS')]); ?>
                        </h3>
                    </div>
                    <!-- <?php echo app('translator')->get('superadmin::lang.monthly'); ?>/annual-->
                    <div class="tw-flex tw-gap-2 mt-5 md:tw-mt-5">
                        <span class="tw-text-white"><?php echo app('translator')->get('superadmin::lang.monthly'); ?></span>
                        <input type="checkbox" id="durationCheck" class="tw-dw-toggle tw-dw-toggle-secondary duration_check"
                            style="margin: 0px" />

                        <span class="tw-flex tw-flex-col tw-text-white"> <?php echo app('translator')->get('superadmin::lang.annual'); ?> </span>
                    </div>
                </div>

                
                <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-5 md:tw-gap-0 tw-mt-5 md:tw-mt-7 tw-mb-10 tw-h-auto"
                    id="packages">
                    
                </div>
                
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.change_lang').click(function() {
                window.location = "<?php echo e(route('pricing'), false); ?>?lang=" + $(this).attr('value');
            });

            $('#durationCheck').off('change').on('change', function() {
                var interval = $(this).is(':checked') ? 'years' : 'months';
                set_packages(interval);
            });

            function set_packages(interval) {
                $.ajax({
                    method: 'get',
                    url: "<?php echo e(route('package_duration_update'), false); ?>",
                    dataType: 'html',
                    data: {
                        interval: interval
                    },
                    success: function(response) {
                        $('#packages').html(response);
                        // this function use for formate currency
                        __currency_convert_recursively($('.price_card'))
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error(textStatus, errorThrown);
                    },
                });
            }
            set_packages('months');
        })
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/pricing/index.blade.php ENDPATH**/ ?>