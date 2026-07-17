<div class="modal fade" id="add_leave_type_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">

	    <?php echo Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class, 'store']), 'method' => 'post', 'id' => 'add_leave_type_form' ]); ?>


	    <div class="modal-header">
	      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      <h4 class="modal-title"><?php echo app('translator')->get( 'essentials::lang.add_leave_type' ); ?></h4>
	    </div>

	    <div class="modal-body">
	      	<div class="form-group">
	        	<?php echo Form::label('leave_type', __( 'essentials::lang.leave_type' ) . ':*'); ?>

	          	<?php echo Form::text('leave_type', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'essentials::lang.leave_type' ) ]); ?>

	      	</div>

	      	<div class="form-group">
	        	<?php echo Form::label('max_leave_count', __( 'essentials::lang.max_leave_count' ) . ':'); ?>

	          	<?php echo Form::number('max_leave_count', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.max_leave_count' ) ]); ?>

	      	</div>

	      	<div class="form-group">
	      		<strong><?php echo app('translator')->get('essentials::lang.leave_count_interval'); ?></strong><br>
	      		<label class="radio-inline">
	      			<?php echo Form::radio('leave_count_interval', 'month', false); ?> <?php echo app('translator')->get('essentials::lang.current_month'); ?>
	      		</label>
	      		<label class="radio-inline">
	      			<?php echo Form::radio('leave_count_interval', 'year', false); ?> <?php echo app('translator')->get('essentials::lang.current_fy'); ?>
	      		</label>
	      		<label class="radio-inline">
	      			<?php echo Form::radio('leave_count_interval', null, false); ?> <?php echo app('translator')->get('lang_v1.none'); ?>
	      		</label>
	      	</div>
	    </div>

	    <div class="modal-footer">
	      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"><?php echo app('translator')->get( 'messages.save' ); ?></button>
	      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
	    </div>

	    <?php echo Form::close(); ?>


	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/leave_type/create.blade.php ENDPATH**/ ?>