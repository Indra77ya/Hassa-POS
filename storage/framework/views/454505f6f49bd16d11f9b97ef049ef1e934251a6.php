
<?php $__env->startSection('title', __('woocommerce::lang.api_settings')); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('woocommerce::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('woocommerce::lang.api_settings'); ?></h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <?php echo Form::open([
            'action' => '\Modules\Woocommerce\Http\Controllers\WoocommerceController@updateSettings',
            'method' => 'post',
        ]); ?>

        <div class="row">
            <div class="col-xs-12">
                <!--  <pos-tab-container> -->
                
                <?php $__env->startComponent('components.widget', ['class' => 'pos-tab-container']); ?>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu">
                        <div class="list-group">
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base active"><?php echo app('translator')->get('woocommerce::lang.instructions'); ?></a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('woocommerce::lang.api_settings'); ?></a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('woocommerce::lang.product_sync_settings'); ?></a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('woocommerce::lang.order_sync_settings'); ?></a>
                            <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('woocommerce::lang.webhook_settings'); ?></a>
                        </div>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        <?php echo $__env->make('woocommerce::woocommerce.partials.api_instructions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php echo $__env->make('woocommerce::woocommerce.partials.api_settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php echo $__env->make('woocommerce::woocommerce.partials.product_sync_settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php echo $__env->make('woocommerce::woocommerce.partials.order_sync_settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php echo $__env->make('woocommerce::woocommerce.partials.webhook_settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                <?php echo $__env->renderComponent(); ?>
            </div>

            <div class="col-xs-12">
                <p class="help-block"><i><?php echo __('woocommerce::lang.version_info', ['version' => $module_version]); ?></i></p>
            </div>
            <!--  </pos-tab-container> -->
        </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group pull-right">
                    <?php echo e(Form::submit('update', ['class' => 'tw-dw-btn tw-dw-btn-error tw-text-white']), false); ?>

                </div>
            </div>
        </div>
        <?php echo Form::close(); ?>

    </section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.create_quantity').on('ifChecked', function(event) {
                $('.create_stock_settings').each(function() {
                    $(this).addClass('hide');
                });
            });
            $('.create_quantity').on('ifUnchecked', function(event) {
                $('.create_stock_settings').each(function() {
                    $(this).removeClass('hide');
                });
            });
            $('.update_quantity').on('ifChecked', function(event) {
                $('.update_stock_settings').each(function() {
                    $(this).addClass('hide');
                });
            });
            $('.update_quantity').on('ifUnchecked', function(event) {
                $('.update_stock_settings').each(function() {
                    $(this).removeClass('hide');
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Woocommerce\Providers/../Resources/views/woocommerce/api_settings.blade.php ENDPATH**/ ?>