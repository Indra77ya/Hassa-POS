<table class="table table-striped">
    <thead>
        <tr>
            <th colspan="2"
                style="background-color:#f4f4f4; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:#555; border-bottom:2px solid #ddd;">
                <i class="fa fa-arrow-circle-down text-red"></i>&nbsp; <?php echo app('translator')->get('lang_v1.costs_and_deductions'); ?>
            </th>
        </tr>
    </thead>

    
    <tr>
        <th><?php echo e(__('report.opening_stock'), false); ?> <br><small class="text-muted">(<?php echo app('translator')->get('lang_v1.by_purchase_price'); ?>)</small>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['opening_stock'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('report.opening_stock'), false); ?> <br><small class="text-muted">(<?php echo app('translator')->get('lang_v1.by_sale_price'); ?>)</small>:</th>
        <td>
            <?php if(isset($stocks['opening_stock_by_sp'])): ?>
                <span class="display_currency" data-currency_symbol="true"><?php echo e($stocks['opening_stock_by_sp'], false); ?></span>
            <?php else: ?>
                <span id="opening_stock_by_sp"><i class="fa fa-sync fa-spin fa-fw"></i></span>
            <?php endif; ?>
        </td>
    </tr>

    
    <tr style="background-color:#fff8e1;">
        <th style="font-size:14px;"><?php echo e(__('home.total_purchase'), false); ?>:<br><small class="text-muted">(<?php echo app('translator')->get('product.exc_of_tax'); ?>, <?php echo app('translator')->get('sale.discount'); ?>)</small></th>
        <td style="font-size:14px; font-weight:700;"><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_purchase'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('report.total_stock_adjustment'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_adjustment'], false); ?></span></td>
    </tr>

    
    <tr>
        <th>
            <?php echo e(__('report.total_expense'), false); ?>:
            <?php if(!empty($data['expenses_by_category']) && count($data['expenses_by_category']) > 0): ?>
                <a href="#" class="btn-link" style="font-size:11px; margin-left:5px;"
                   onclick="event.preventDefault(); $('.expense-category-row').toggle();">
                    <i class="fa fa-eye"></i> <?php echo app('translator')->get('lang_v1.details'); ?>
                </a>
            <?php endif; ?>
        </th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_expense'], false); ?></span></td>
    </tr>
    <?php if(!empty($data['expenses_by_category']) && count($data['expenses_by_category']) > 0): ?>
        <?php $__currentLoopData = $data['expenses_by_category']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense_cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="expense-category-row" style="display:none; background-color:#fafafa;">
            <th style="padding-left:30px; font-weight:normal; font-size:12px;">
                <i class="fa fa-caret-right text-muted"></i> <?php echo e($expense_cat->category_name, false); ?>

            </th>
            <td style="font-size:12px;"><span class="display_currency" data-currency_symbol="true"><?php echo e($expense_cat->category_total, false); ?></span></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    
    <tr>
        <th><?php echo e(__('lang_v1.total_purchase_shipping_charge'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_purchase_shipping_charge'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('lang_v1.purchase_additional_expense'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_purchase_additional_expense'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('lang_v1.total_transfer_shipping_charge'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_transfer_shipping_charges'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('lang_v1.total_sell_discount'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_sell_discount'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('lang_v1.total_reward_amount'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_reward_amount'], false); ?></span></td>
    </tr>

    
    <tr>
        <th><?php echo e(__('lang_v1.total_sell_return'), false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($data['total_sell_return'], false); ?></span></td>
    </tr>

    
    <?php $__currentLoopData = $data['left_side_module_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $module_data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <th><?php echo e($module_data['label'], false); ?>:</th>
        <td><span class="display_currency" data-currency_symbol="true"><?php echo e($module_data['value'], false); ?></span></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/report/partials/opening_stock_report_table.blade.php ENDPATH**/ ?>