<div class="pos-tab-content">
    <div class="row">
        <div class="col-xs-6">
            <div class="checkbox">
                <label>
                    <?php echo Form::checkbox('calculate_sales_target_commission_without_tax', 1, !empty($settings['calculate_sales_target_commission_without_tax']) ? 1 : 0, ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('essentials::lang.calculate_sales_target_commission_without_tax'); ?>
                </label>
                <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('essentials::lang.calculate_sales_target_commission_without_tax_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/settings/partials/sales_target_settings.blade.php ENDPATH**/ ?>