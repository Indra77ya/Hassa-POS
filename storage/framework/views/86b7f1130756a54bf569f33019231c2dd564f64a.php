<?php
  $custom_labels = json_decode(session('business.custom_labels'), true);
  $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
  $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
  $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
  $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
?>
<table class="table table-bordered table-striped" id="stock_report_table">
    <thead>
        <tr>
            <th class="not-export"><?php echo app('translator')->get('messages.action'); ?></th>
            <th>SKU</th>
            <th><?php echo app('translator')->get('business.product'); ?></th>
            <th><?php echo app('translator')->get('lang_v1.variation'); ?></th>
            <th><?php echo app('translator')->get('product.category'); ?></th>
            <th><?php echo app('translator')->get('sale.location'); ?></th>
            <th><?php echo app('translator')->get('purchase.unit_selling_price'); ?></th>
            <th><?php echo app('translator')->get('report.current_stock'); ?></th>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_product_stock_value')): ?>
            <th class="stock_price"><?php echo app('translator')->get('lang_v1.total_stock_price'); ?> <br><small>(<?php echo app('translator')->get('lang_v1.by_purchase_price'); ?>)</small></th>
            <th><?php echo app('translator')->get('lang_v1.total_stock_price'); ?> <br><small>(<?php echo app('translator')->get('lang_v1.by_sale_price'); ?>)</small></th>
            <th><?php echo app('translator')->get('lang_v1.potential_profit'); ?></th>
            <?php endif; ?>
            <th><?php echo app('translator')->get('report.total_unit_sold'); ?></th>
            <th><?php echo app('translator')->get('lang_v1.total_unit_transfered'); ?></th>
            <th><?php echo app('translator')->get('lang_v1.total_unit_adjusted'); ?></th>
            <th><?php echo e($product_custom_field1, false); ?></th>
            <th><?php echo e($product_custom_field2, false); ?></th>
            <th><?php echo e($product_custom_field3, false); ?></th>
            <th><?php echo e($product_custom_field4, false); ?></th>
            <?php if($show_manufacturing_data): ?>
                <th class="current_stock_mfg"><?php echo app('translator')->get('manufacturing::lang.current_stock_mfg'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('manufacturing::lang.mfg_stock_tooltip') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 text-center footer-total">
            <td colspan="7"><strong><?php echo app('translator')->get('sale.total'); ?>:</strong></td>
            <td class="footer_total_stock"></td>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_product_stock_value')): ?>
            <td class="footer_total_stock_price"></td>
            <td class="footer_stock_value_by_sale_price"></td>
            <td class="footer_potential_profit"></td>
            <?php endif; ?>
            <td class="footer_total_sold"></td>
            <td class="footer_total_transfered"></td>
            <td class="footer_total_adjusted"></td>
            <td colspan="4"></td>
            <?php if($show_manufacturing_data): ?>
                <td class="footer_total_mfg_stock"></td>
            <?php endif; ?>
        </tr>
    </tfoot>
</table><?php /**PATH C:\laragon\www\Hassa POS\resources\views/report/partials/stock_report_table.blade.php ENDPATH**/ ?>