<?php if(!empty($__subscription) && env('APP_ENV') != 'demo'): ?>
<i class="fas fa-info-circle pull-left cursor-pointer" style= "color:white" aria-hidden="true" data-toggle="popover" data-html="true" title="<?php echo app('translator')->get('superadmin::lang.active_package_description'); ?>" data-placement="right" data-trigger="hover" data-content="
    <table class='table table-condensed'>
     <tr class='text-center'> 
        <td colspan='2'>
            <?php echo e($__subscription->package_details['name'], false); ?>

        </td>
     </tr>
     <tr class='text-center'>
        <td colspan='2'>
            <?php echo e(\Carbon::createFromTimestamp(strtotime($__subscription->start_date))->format(session('business.date_format')), false); ?> - <?php echo e(\Carbon::createFromTimestamp(strtotime($__subscription->end_date))->format(session('business.date_format')), false); ?>

        </td>
     </tr>
     <tr> 
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            <?php if($__subscription->package_details['location_count'] == 0): ?>
                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
            <?php else: ?>
                <?php echo e($__subscription->package_details['location_count'], false); ?>

            <?php endif; ?>

            <?php echo app('translator')->get('business.business_locations'); ?>
        </td>
     </tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            <?php if($__subscription->package_details['user_count'] == 0): ?>
                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
            <?php else: ?>
                <?php echo e($__subscription->package_details['user_count'], false); ?>

            <?php endif; ?>

            <?php echo app('translator')->get('superadmin::lang.users'); ?>
        </td>
     <tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            <?php if($__subscription->package_details['product_count'] == 0): ?>
                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
            <?php else: ?>
                <?php echo e($__subscription->package_details['product_count'], false); ?>

            <?php endif; ?>

            <?php echo app('translator')->get('superadmin::lang.products'); ?>
        </td>
     </tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            <?php if($__subscription->package_details['invoice_count'] == 0): ?>
                <?php echo app('translator')->get('superadmin::lang.unlimited'); ?>
            <?php else: ?>
                <?php echo e($__subscription->package_details['invoice_count'], false); ?>

            <?php endif; ?>

            <?php echo app('translator')->get('superadmin::lang.invoices'); ?>
        </td>
     </tr>
     
    </table>                     
">
</i>
<?php endif; ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/layouts/partials/active_subscription.blade.php ENDPATH**/ ?>