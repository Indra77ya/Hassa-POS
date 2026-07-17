<div class="modal fade" id="payroll_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">

	    <?php echo Form::open(['url' => action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'create']), 'method' => 'get', 'id' => 'add_payroll_step1' ]); ?>


	    <div class="modal-body">
	    	<div class="form-group">
	        	<?php echo Form::label('primary_work_location', __( 'business.location' ) . ':*'); ?>

	          	<?php echo Form::select('primary_work_location', $locations, null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'primary_work_location']); ?>

	      	</div>
	      	<div class="form-group">
	        	<?php echo Form::label('employee_ids', __( 'essentials::lang.employee' ) . ':*'); ?>

	        	<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary select-all">
                    <?php echo app('translator')->get('lang_v1.select_all'); ?>
                </button>
                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary deselect-all">
                    <?php echo app('translator')->get('lang_v1.deselect_all'); ?>
                </button>
	          	<?php echo Form::select('employee_ids[]', $employees, null, ['class' => 'form-control select2', 'required', 'style' => 'width: 100%;', 'multiple', 'id' => 'employee_ids']); ?>

	      	</div>

	      	<div class="form-group">
	      		<?php echo Form::label('month_year', __( 'essentials::lang.month_year' ) . ':*'); ?>

	      		<div class="input-group">
	          		<?php echo Form::text('month_year', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.month_year' ), 'required', 'readonly' ]); ?>

	          		<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	      		</div>
	      	</div>
	    </div>

	    <div class="modal-footer">
	      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"><?php echo app('translator')->get( 'essentials::lang.proceed' ); ?></button>
	      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
	    </div>

	    <?php echo Form::close(); ?>


	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/payroll/payroll_modal.blade.php ENDPATH**/ ?>