<div class="col-md-6">
    <?php $__env->startComponent('components.widget'); ?>
        <?php echo $__env->make('report.partials.opening_stock_report_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->renderComponent(); ?>
</div>

<div class="col-md-6">
    <?php $__env->startComponent('components.widget'); ?>
        <?php echo $__env->make('report.partials.clossing_stock_report_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->renderComponent(); ?>
</div>
<br>
<div class="col-xs-12">
    <?php $__env->startComponent('components.widget'); ?>
        <?php echo $__env->make('report.partials.net_gross_profit_report_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->renderComponent(); ?>
</div>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/report/partials/profit_loss_details.blade.php ENDPATH**/ ?>