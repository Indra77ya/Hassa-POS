<div style="border-top:3px solid #ddd; padding-top:12px;">

    
    <?php
        $cogs_cur = ($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock'];
    ?>
    <div style="margin-bottom:10px;">
        <h4 class="text-muted" style="margin:0; font-weight:600;">
            <?php echo app('translator')->get('lang_v1.cogs'); ?>
            <span class="display_currency" data-currency_symbol="true"><?php echo e($cogs_cur, false); ?></span>
        </h4>
        <small class="help-block" style="margin:2px 0 0 0;"><?php echo app('translator')->get('lang_v1.cogs_help_text'); ?></small>
    </div>

    
    <div style="border-top:1px solid #eee; padding-top:10px; margin-bottom:10px;">
        <h3 class="<?php echo e($data['gross_profit'] >= 0 ? 'text-success' : 'text-danger', false); ?>" style="margin:0; font-weight:700;">
            <?php echo e(__('lang_v1.gross_profit'), false); ?>:
            <span class="display_currency" data-currency_symbol="true"><?php echo e($data['gross_profit'], false); ?></span>
            <?php if(!empty($data['total_sell']) && $data['total_sell'] != 0): ?>
                <small>(<?php echo e(number_format(($data['gross_profit'] / $data['total_sell']) * 100, 2), false); ?>%)</small>
            <?php endif; ?>
        </h3>
        <small class="help-block" style="margin:2px 0 0 0;">
            (<?php echo app('translator')->get('lang_v1.total_sell_price'); ?> - <?php echo app('translator')->get('lang_v1.total_purchase_price'); ?>)
            <?php $__currentLoopData = $data['gross_profit_label']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                + <?php echo e($val, false); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </small>
    </div>

    
    <div style="border-top:1px solid #eee; padding-top:10px;">
        <h2 class="<?php echo e($data['net_profit'] >= 0 ? 'text-success' : 'text-danger', false); ?>" style="margin:0; font-weight:700;">
            <?php echo e(__('report.net_profit'), false); ?>:
            <span class="display_currency" data-currency_symbol="true"><?php echo e($data['net_profit'], false); ?></span>
            <?php if(!empty($data['total_sell']) && $data['total_sell'] != 0): ?>
                <small style="font-size:60%;">(<?php echo e(number_format(($data['net_profit'] / $data['total_sell']) * 100, 2), false); ?>%)</small>
            <?php endif; ?>
        </h2>
        <small class="help-block" style="margin:2px 0 0 0;">
            
            <?php echo app('translator')->get('lang_v1.gross_profit'); ?> + (<?php echo app('translator')->get('lang_v1.total_sell_shipping_charge'); ?> + <?php echo app('translator')->get('lang_v1.sell_additional_expense'); ?> + <?php echo app('translator')->get('report.total_stock_recovered'); ?> + <?php echo app('translator')->get('lang_v1.total_purchase_discount'); ?> + <?php echo app('translator')->get('lang_v1.total_sell_round_off'); ?> + <?php echo app('translator')->get('lang_v1.total_sell_return_discount'); ?>
            <?php $__currentLoopData = $data['right_side_module_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module_data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($module_data['add_to_net_profit'])): ?>
                    + <?php echo e($module_data['label'], false); ?>

                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ) <br> - (<?php echo app('translator')->get('report.total_stock_adjustment'); ?> + <?php echo app('translator')->get('report.total_expense'); ?> + <?php echo app('translator')->get('lang_v1.total_purchase_shipping_charge'); ?> + <?php echo app('translator')->get('lang_v1.total_transfer_shipping_charge'); ?> + <?php echo app('translator')->get('lang_v1.purchase_additional_expense'); ?> + <?php echo app('translator')->get('lang_v1.total_sell_discount'); ?> + <?php echo app('translator')->get('lang_v1.total_reward_amount'); ?>
            <?php $__currentLoopData = $data['left_side_module_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module_data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($module_data['add_to_net_profit'])): ?>
                    + <?php echo e($module_data['label'], false); ?>

                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            )
        </small>
    </div>

    <!-- Tax Summary: informational only, not part of P&L calculation (report uses exc. tax values) -->
    <?php if(!empty($data['total_sell_tax']) || !empty($data['total_purchase_tax'])): ?>
    <div style="border-top:2px solid #ddd; padding-top:10px; margin-top:12px;">
        <h4 class="text-muted" style="margin:0 0 8px 0; font-weight:600;">
            <i class="fa fa-calculator"></i>&nbsp; <?php echo app('translator')->get('lang_v1.tax_summary'); ?>
        </h4>
        <table class="table table-condensed" style="margin-bottom:0; width:auto;">
            <tr>
                <th><?php echo app('translator')->get('lang_v1.tax_collected_on_sales'); ?>:</th>
                <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_sell_tax'], false); ?></span></td>
            </tr>
            <tr>
                <th><?php echo app('translator')->get('lang_v1.tax_paid_on_purchases'); ?>:</th>
                <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_purchase_tax'], false); ?></span></td>
            </tr>
            <tr style="border-top:1px solid #ddd;">
                <?php $net_tax = $data['total_sell_tax'] - $data['total_purchase_tax']; ?>
                <th><?php echo app('translator')->get('lang_v1.net_tax_liability'); ?>:</th>
                <td><strong class="<?php echo e($net_tax >= 0 ? 'text-danger' : 'text-success', false); ?>">
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($net_tax, false); ?></span>
                </strong></td>
            </tr>
        </table>
        <small class="help-block" style="margin:2px 0 0 0;"><?php echo app('translator')->get('lang_v1.tax_summary_help'); ?></small>
    </div>
    <?php endif; ?>

</div>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/report/partials/net_gross_profit_report_details.blade.php ENDPATH**/ ?>