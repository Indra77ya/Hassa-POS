<div class="modal fade" id="clock_in_clock_out_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
	  <div class="modal-content">

	    <?php echo Form::open(['url' => action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'clockInClockOut']), 'method' => 'post', 'id' => 'clock_in_clock_out_form' ]); ?>

	    <div class="modal-header">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      	<h4 class="modal-title"><span id="clock_in_text"><?php echo app('translator')->get( 'essentials::lang.clock_in' ); ?></span>
	      	<span id="clock_out_text"><?php echo app('translator')->get( 'essentials::lang.clock_out' ); ?></span></h4>
	    </div>
	    <div class="modal-body">
	    	<div class="row">
	    		<input type="hidden" name="type" id="type">
		      	<div class="form-group col-md-12">
		      		<strong><?php echo app('translator')->get( 'essentials::lang.ip_address' ); ?>: <?php echo e($ip_address, false); ?></strong>
		      	</div>
		      	<div class="form-group col-md-12 clock_in_note <?php if(!empty($clock_in)): ?> hide <?php endif; ?>">
		        	<?php echo Form::label('clock_in_note', __( 'essentials::lang.clock_in_note' ) . ':'); ?>

		        	<?php echo Form::textarea('clock_in_note', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.clock_in_note'), 'rows' => 3 ]); ?>

		      	</div>
		      	<div class="form-group col-md-12 clock_out_note <?php if(empty($clock_in)): ?> hide <?php endif; ?>">
		        	<?php echo Form::label('clock_out_note', __( 'essentials::lang.clock_out_note' ) . ':'); ?>

		        	<?php echo Form::textarea('clock_out_note', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.clock_out_note'), 'rows' => 3 ]); ?>

		      	</div>
		      	<input type="hidden" name="clock_in_out_location" id="clock_in_out_location" value="">
	    	</div>
	    	<?php if($is_location_required): ?>
		    	<div class="row">
		    		<div class="col-md-12">
		    			<b><?php echo app('translator')->get('messages.location'); ?>:</b> <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary" id="get_current_location"> <i class="fas fa-map-marker-alt"></i> <?php echo app('translator')->get('essentials::lang.get_current_location'); ?></button>
		    			<br><span class="clock_in_out_location"></span>
		    		</div>
		    		<div class="col-md-12 ask_location" style="display: none;">
		    			<span class="location_required error"></span>
		    			
		    		</div>
		    	</div>
		    <?php endif; ?>
	    </div>

	    <div class="modal-footer">
	      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"><?php echo app('translator')->get( 'messages.submit' ); ?></button>
	      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
	    </div>

	    <?php echo Form::close(); ?>


	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
        	
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/attendance/clock_in_clock_out_modal.blade.php ENDPATH**/ ?>