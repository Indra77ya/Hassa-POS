
<?php $__env->startSection('title', __('essentials::lang.hrm')); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('essentials::layouts.nav_hrm', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- Main content -->
<section class="content">
	<div class="row row-custom">
		<div class="col-md-4 col-sm-6 col-xs-12 col-custom">
			<div class="box box-solid">
				<div class="box-header with-border">
	                <i class="fas fa-sign-out-alt"></i>
	                <h3 class="box-title"><?php echo app('translator')->get('essentials::lang.my_leaves'); ?></h3>
	            </div>
                <div class="box-body p-10">
                    <table class="table no-margin">
                    	<thead>
                    		<?php $__empty_1 = true; $__currentLoopData = $users_leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user_leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    			<tr>
                    				<td>
                    					<?php echo e(\Carbon::createFromTimestamp(strtotime($user_leave->start_date))->format(session('business.date_format')), false); ?>

                    					- <?php echo e(\Carbon::createFromTimestamp(strtotime($user_leave->end_date))->format(session('business.date_format')), false); ?>

                    				</td>
                    				<td>
                    					<?php echo e($user_leave->leave_type->leave_type, false); ?>

                    				</td>
                    			</tr>
                    		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    			<tr>
	                    			<td colspan="2" class="text-center">
	                    				<?php echo app('translator')->get('lang_v1.no_data'); ?>
	                    			</td>
	                    		</tr>
                    		<?php endif; ?>
                    	</thead>
                    </table>
                </div>
	        </div>
		</div>
		<div class="col-md-4 col-sm-6 col-xs-12 col-custom">
			<div class="box box-solid">
				<div class="box-header with-border">
	                <i class="fas fa-bullseye"></i>
	                <h3 class="box-title"><?php echo app('translator')->get('essentials::lang.my_sales_targets'); ?></h3>
	            </div>
                <div class="box-body p-10">
                    <table class="table no-margin">
                    	<thead>
                    		<tr>
                    			<td>
                    				<strong><?php echo app('translator')->get('essentials::lang.target_achieved_last_month'); ?>:
                    				</strong>
                    				<h4 class="text-success"><?php 
            $formated_number = "";
            if (session("business.currency_symbol_placement") == "before") {
                $formated_number .= session("currency")["symbol"] . " ";
            } 
            $formated_number .= number_format((float) $target_achieved_last_month, session("business.currency_precision", 2) , session("currency")["decimal_separator"], session("currency")["thousand_separator"]);

            if (session("business.currency_symbol_placement") == "after") {
                $formated_number .= " " . session("currency")["symbol"];
            }
            echo $formated_number; ?></h4>
                    			</td>
                    			<td>
                    				<strong><?php echo app('translator')->get('essentials::lang.target_achieved_this_month'); ?>:
                    				</strong>
                    				<h4 class="text-success"><?php 
            $formated_number = "";
            if (session("business.currency_symbol_placement") == "before") {
                $formated_number .= session("currency")["symbol"] . " ";
            } 
            $formated_number .= number_format((float) $target_achieved_this_month, session("business.currency_precision", 2) , session("currency")["decimal_separator"], session("currency")["thousand_separator"]);

            if (session("business.currency_symbol_placement") == "after") {
                $formated_number .= " " . session("currency")["symbol"];
            }
            echo $formated_number; ?></h4>
                    			</td>
                    		</tr>
                    		<tr>
                    			<th>
                    				<?php echo app('translator')->get('essentials::lang.targets'); ?>
                    			</th>
                    			<th>
                    				<?php echo app('translator')->get('essentials::lang.commission_percent'); ?>
                    			</th>
                    		</tr>
                    		<?php $__empty_1 = true; $__currentLoopData = $sales_targets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $target): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    			<tr>
                    				<td>
                    					<?php 
            $formated_number = "";
            if (session("business.currency_symbol_placement") == "before") {
                $formated_number .= session("currency")["symbol"] . " ";
            } 
            $formated_number .= number_format((float) $target->target_start, session("business.currency_precision", 2) , session("currency")["decimal_separator"], session("currency")["thousand_separator"]);

            if (session("business.currency_symbol_placement") == "after") {
                $formated_number .= " " . session("currency")["symbol"];
            }
            echo $formated_number; ?>
                    					- <?php 
            $formated_number = "";
            if (session("business.currency_symbol_placement") == "before") {
                $formated_number .= session("currency")["symbol"] . " ";
            } 
            $formated_number .= number_format((float) $target->target_end, session("business.currency_precision", 2) , session("currency")["decimal_separator"], session("currency")["thousand_separator"]);

            if (session("business.currency_symbol_placement") == "after") {
                $formated_number .= " " . session("currency")["symbol"];
            }
            echo $formated_number; ?>
                    				</td>
                    				<td>
                    					<?php echo e(number_format($target->commission_percent, 2), false); ?>%
                    				</td>
                    			</tr>
                    		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    			<tr>
	                    			<td colspan="2" class="text-center">
	                    				<?php echo app('translator')->get('lang_v1.no_data'); ?>
	                    			</td>
	                    		</tr>
                    		<?php endif; ?>
                    	</thead>
                    </table>
                </div>
	        </div>
		</div>
		<?php if(!$is_admin): ?>
        	<?php echo $__env->make('essentials::dashboard.holidays', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
     	<?php endif; ?>
        <div class="col-md-4 col-sm-6 col-xs-12 text-center">
            <a href="<?php echo e(action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'getMyPayrolls']), false); ?>"
                class="btn btn-lg btn-success">
                <i class="fas fa-coins"></i>
                <?php echo app('translator')->get('essentials::lang.my_payrolls'); ?>
            </a>
        </div>
	</div>
	<?php if($is_admin): ?>
	<hr>
	<?php endif; ?>
	<div class="row row-custom">
		<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('user.view')): ?>
	    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
	        <div class="box box-solid">
	            <div class="box-body p-10">
                	<div class="info-box info-box-new-style">
		            	<span class="info-box-icon bg-aqua"><i class="fas fa-users"></i></span>

		            	<div class="info-box-content">
		              		<span class="info-box-text"><?php echo e(__('user.users'), false); ?></span>
		              		<span class="info-box-number"><?php echo e($users->count(), false); ?></span>
		            	</div>
		            	<!-- /.info-box-content -->
		          	</div>
	                <table class="table no-margin">
	                    <thead>
	                        <tr>
	                            <th><?php echo e(__('essentials::lang.department'), false); ?></th>
	                            <th><?php echo e(__('sale.total'), false); ?></th>
	                        </tr>
	                    </thead>
	                    <tbody>
	                        <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
	                            <tr>
	                                <td><?php echo e($department->name, false); ?></td>
	                                <td><?php if(!empty($users_by_dept[$department->id])): ?><?php echo e(count($users_by_dept[$department->id]), false); ?> <?php else: ?> 0 <?php endif; ?></td>
	                            </tr>
	                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
	                            <tr>
	                                <td colspan="2" class="text-center"><?php echo app('translator')->get('lang_v1.no_data'); ?></td>
	                            </tr>
	                        <?php endif; ?>
	                    </tbody>
	                </table>
            	</div>
        	</div>
    	</div>
    	<?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('essentials.approve_leave')): ?>
    	<div class="col-md-4 col-sm-6 col-xs-12 col-custom">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fas fa-user-times"></i>
                    <h3 class="box-title"><?php echo app('translator')->get('essentials::lang.leaves'); ?></h3>
                </div>
                <div class="box-body p-10">
                    <table class="table no-margin">
                        <tr>
                            <th class="bg-light-gray" colspan="2"><?php echo app('translator')->get('home.today'); ?></th>
                        </tr>
                        <?php $__empty_1 = true; $__currentLoopData = $todays_leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                			<tr>
                				<td>
                					<?php echo e(\Carbon::createFromTimestamp(strtotime($leave->start_date))->format(session('business.date_format')), false); ?>

                					- <?php echo e(\Carbon::createFromTimestamp(strtotime($leave->end_date))->format(session('business.date_format')), false); ?>

                				</td>
                				<td>
                					<?php echo e($leave->leave_type->leave_type, false); ?>

                				</td>
                			</tr>
                		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                			<tr>
                    			<td colspan="2" class="text-center">
                    				<?php echo app('translator')->get('lang_v1.no_data'); ?>
                    			</td>
                    		</tr>
                		<?php endif; ?>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th class="bg-light-gray" colspan="2"><?php echo app('translator')->get('lang_v1.upcoming'); ?></th>
                        </tr>
                        <?php $__empty_1 = true; $__currentLoopData = $upcoming_leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                			<tr>
                				<td>
                					<?php echo e(\Carbon::createFromTimestamp(strtotime($leave->start_date))->format(session('business.date_format')), false); ?>

                					- <?php echo e(\Carbon::createFromTimestamp(strtotime($leave->end_date))->format(session('business.date_format')), false); ?>

                				</td>
                				<td>
                					<?php echo e($leave->leave_type->leave_type, false); ?>

                				</td>
                			</tr>
                		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                			<tr>
                    			<td colspan="2" class="text-center">
                    				<?php echo app('translator')->get('lang_v1.no_data'); ?>
                    			</td>
                    		</tr>
                		<?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if($is_admin): ?>
        	<?php echo $__env->make('essentials::dashboard.holidays', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
     	<?php endif; ?>
    </div>
    <div class="row row-custom">
    	<?php if($is_admin): ?>
    		<div class="col-md-4 col-sm-6 col-xs-12 col-custom">
	        	<div class="box box-solid">
	        		<div class="box-header with-border">
	                    <i class="fas fa-user-check"></i>
	                    <h3 class="box-title"><?php echo app('translator')->get('essentials::lang.todays_attendance'); ?></h3>
	                </div>
	                <div class="box-body p-10">
	                    <table class="table no-margin">
	                    	<thead>
	                    		<tr>
	                    			<th>
	                    				<?php echo app('translator')->get('essentials::lang.employee'); ?>
	                    			</th>
	                    			<th>
	                    				<?php echo app('translator')->get('essentials::lang.clock_in'); ?>
	                    			</th>
	                    			<th>
	                    				<?php echo app('translator')->get('essentials::lang.clock_out'); ?>
	                    			</th>
	                    		</tr>
	                    	</thead>
	                        <tbody>
		                        <?php $__empty_1 = true; $__currentLoopData = $todays_attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
	                                <tr>
	                                    <td><?php echo e($attendance->employee->user_full_name, false); ?></td>
	                                    <td>
	                                    	<?php echo e(\Carbon::createFromTimestamp(strtotime($attendance->clock_in_time))->format(session('business.date_format') . ' ' . 'H:i'), false); ?>


	                                    	<?php if(!empty($attendance->clock_in_note)): ?>
	                                    		<br><small><?php echo e($attendance->clock_in_note, false); ?></small>
	                                    	<?php endif; ?>
	                                    </td>
	                                    <td>
	                                    	<?php if(!empty($attendance->clock_out_time)): ?>
	                                    		<?php echo e(\Carbon::createFromTimestamp(strtotime($attendance->clock_out_time))->format(session('business.date_format') . ' ' . 'H:i'), false); ?>

	                                    	<?php endif; ?>

	                                    	<?php if(!empty($attendance->clock_out_note)): ?>
	                                    		<br><small><?php echo e($attendance->clock_out_note, false); ?></small>
	                                    	<?php endif; ?>
	                                   	</td>
	                                </tr>
	                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
	                                <tr>
	                                    <td colspan="3" class="text-center"><?php echo app('translator')->get('lang_v1.no_data'); ?></td>
	                                </tr>
	                            <?php endif; ?>
	                        </tbody>
	                    </table>
	                </div>
	            </div>
	        </div>

	        <div class="col-md-8 col-sm-12 col-xs-12">
	        	<div class="box box-solid">
	        		<div class="box-header with-border">
	                    <i class="fas fa-bullseye"></i>
	                    <h3 class="box-title"><?php echo app('translator')->get('essentials::lang.sales_targets'); ?></h3>
	                </div>
	                <div class="box-body">
	                	<table class="table" id="sales_targets_table" style="width: 100%;">
	                		<thead>
	                			<tr>
	                				<th><?php echo app('translator')->get('report.user'); ?></th>
	                				<th><?php echo app('translator')->get('essentials::lang.target_achieved_last_month'); ?></th>
	                				<th><?php echo app('translator')->get('essentials::lang.target_achieved_this_month'); ?></th>
	                			</tr>
	                		</thead>
	                	</table>
	                </div>
	            </div>
	        </div>
    	<?php endif; ?>
    </div>
    
</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
<script type="text/javascript">
	$(document).ready( function(){
		if ($('#sales_targets_table').length) {
			var sales_targets_table = $('#sales_targets_table').DataTable({
		        processing: true,
		        serverSide: true,
		        searching: false,
		        scrollY:        "75vh",
		        scrollX:        true,
		        scrollCollapse: true,
		        dom: 'Btirp',
		        fixedHeader: false,
		        ajax: "<?php echo e(action([\Modules\Essentials\Http\Controllers\DashboardController::class, 'getUserSalesTargets']), false); ?>"
		    });
		}
	});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/dashboard/hrm_dashboard.blade.php ENDPATH**/ ?>