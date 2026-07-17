<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header no-print">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title"></h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-xs-6">
          <div class="well well-sm">
            <strong><?php echo app('translator')->get('business.business_name'); ?>: </strong> <?php echo e($system["invoice_business_name"], false); ?> <br>
            <strong><?php echo app('translator')->get('business.email'); ?>: </strong> <?php echo e($system["email"], false); ?> <br>
            <strong><?php echo app('translator')->get('business.landmark'); ?>: </strong> <?php echo e($system["invoice_business_landmark"], false); ?> <br>
            <strong><?php echo app('translator')->get('business.city'); ?>: </strong> <?php echo e($system["invoice_business_city"], false); ?>

            <strong><?php echo app('translator')->get('business.zip_code'); ?>: </strong> <?php echo e($system["invoice_business_zip"], false); ?> <br>
            <strong><?php echo app('translator')->get('business.state'); ?>: </strong> <?php echo e($system["invoice_business_state"], false); ?>

            <strong><?php echo app('translator')->get('business.country'); ?>: </strong> <?php echo e($system["invoice_business_country"], false); ?>

          </div>
        </div>
        <div class="col-xs-6">
          <div class="well well-sm">
            <strong><?php echo app('translator')->get('business.business_name'); ?>: </strong> <?php echo e($subscription->business->name, false); ?> <br>
            <?php if(!empty($subscription->business->tax_number_1) && !empty($subscription->business->tax_label_1)): ?>
              <strong><?php echo e($subscription->business->tax_label_1, false); ?>: </strong> <?php echo e($subscription->business->tax_number_1, false); ?> <br>
            <?php endif; ?>
            
            <?php if(!empty($subscription->business->tax_number_2) && !empty($subscription->business->tax_label_2)): ?>
              <strong><?php echo e($subscription->business->tax_label_2, false); ?>: </strong> <?php echo e($subscription->business->tax_number_2, false); ?> <br>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <table class="table subscription-details">
            <thead>
              <tr>
                <th><?php echo app('translator')->get('superadmin::lang.package_name'); ?></th>
                <th><?php echo app('translator')->get('lang_v1.quantity'); ?></th>
                <th><?php echo app('translator')->get('lang_v1.price'); ?></th>
              </tr>
            </thead>
            <body>
              <tr>
                <td><?php echo e($subscription->package->name, false); ?></td>
                <td>1</td>
                <td>
                  <?php if(empty($subscription->coupon_code)): ?>
                      <span class="display_currency" data-currency_symbol="true" data-use_page_currency="true"><?php echo e($subscription->package_price, false); ?></span>
                  <?php else: ?>
                      <span class="display_currency" data-currency_symbol="true" data-use_page_currency="true"><?php echo e($subscription->original_price, false); ?></span> <br>
                      
                     - <span class="display_currency" data-currency_symbol="true" data-use_page_currency="true"> <?php echo e($subscription->original_price - $subscription->package_price, false); ?></span>  <small class="badge bg-info"><?php echo e($subscription->coupon_code, false); ?></small> <br>

                      <span class="display_currency" data-currency_symbol="true" data-use_page_currency="true"><?php echo e($subscription->package_price, false); ?></span> <br>

                  <?php endif; ?>
                
                </td>
              </tr>
            </body>
          </table>
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-xs-12">
          <table class="table">
            <tr>
              <th><?php echo app('translator')->get('lang_v1.created_at'); ?>:</th>
              <td><?php echo e(\Carbon::createFromTimestamp(strtotime($subscription->created_at))->format(session('business.date_format')), false); ?></td>
              <th> <?php echo app('translator')->get('superadmin::lang.payment_transaction_id'); ?>:</th>
              <td><?php echo e($subscription->payment_transaction_id, false); ?></td>
            </tr>
            <tr>
              <th><?php echo app('translator')->get('business.created_by'); ?>:</th>
              <td><?php echo e($subscription->created_user->user_full_name, false); ?></td>
              <th><?php echo app('translator')->get('superadmin::lang.paid_via'); ?>:</th>
              <td><?php echo e($subscription->paid_via, false); ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="modal-footer no-print">
      <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" aria-label="Print" 
      onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> <?php echo app('translator')->get( 'messages.print' ); ?>
      </button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script type="text/javascript">
  $(document).ready(function(){
    __currency_convert_recursively($('.subscription-details'));
  })
</script><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/subscription/show_subscription_modal.blade.php ENDPATH**/ ?>