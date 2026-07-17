<div class="pos-tab-content">
     <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('stock_expiry_alert_days', __('business.view_stock_expiry_alert_for') . ':*'); ?>

                <div class="input-group">
                <span class="input-group-addon">
                    <i class="fas fa-calendar-times"></i>
                </span>
                <?php echo Form::number('stock_expiry_alert_days', $business->stock_expiry_alert_days, ['class' => 'form-control','required']); ?>

                <span class="input-group-addon">
                    <?php echo app('translator')->get('business.days'); ?>
                </span>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/business/partials/settings_dashboard.blade.php ENDPATH**/ ?>