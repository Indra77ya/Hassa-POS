<div class="modal-dialog" role="document">
  <div class="modal-content">

    <?php echo Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'store']), 'method' => 'post', 'id' => 'superadmin_add_subscription' ]); ?>


    <?php echo Form::hidden('business_id', $business_id); ?>

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title"><?php echo app('translator')->get( 'superadmin::lang.add_subscription' ); ?></h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        <?php echo Form::label('package_id', __( 'superadmin::lang.subscription_packages' ) . ':*'); ?>

          <?php echo Form::select('package_id', $packages, null, ['class' => 'form-control', 'required', 'placeholder' => __( 'messages.please_select' ) ]); ?>

      </div>
      <div class="form-group">
        <?php echo Form::label('paid_via', __( 'superadmin::lang.paid_via' ) . ':*'); ?>

          <?php echo Form::select('paid_via', $gateways, null, ['class' => 'form-control', 'required', 'placeholder' => __( 'messages.please_select' ) ]); ?>

      </div>
      <div class="form-group">
        <?php echo Form::label('payment_transaction_id', __( 'superadmin::lang.payment_transaction_id' ) . ':'); ?>

          <?php echo Form::text('payment_transaction_id', null, ['class' => 'form-control', 'placeholder' => __( 'superadmin::lang.payment_transaction_id' ) ]); ?>

      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"><?php echo app('translator')->get( 'messages.save' ); ?></button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
    </div>

    <?php echo Form::close(); ?>


  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog --><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/superadmin_subscription/add_subscription.blade.php ENDPATH**/ ?>