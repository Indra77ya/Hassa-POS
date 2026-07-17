<div class="table-responsive">
    <table class="table table-bordered table-striped table-text-center" id="profit_by_categories_table">
        <thead>
            <tr>
                <th><?php echo app('translator')->get('product.category'); ?></th>
                <th><?php echo app('translator')->get('lang_v1.gross_profit'); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr class="bg-gray font-17 footer-total">
                <td><strong><?php echo app('translator')->get('sale.total'); ?>:</strong></td>
                <td class="footer_total"></td>
            </tr>
        </tfoot>
    </table>

    <p class="text-muted">
        <?php echo app('translator')->get('lang_v1.profit_note'); ?>
    </p>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/report/partials/profit_by_categories.blade.php ENDPATH**/ ?>