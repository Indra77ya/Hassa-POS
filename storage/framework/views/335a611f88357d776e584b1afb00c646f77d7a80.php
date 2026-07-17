<div class="row">
	<div class="col-md-4">
		<div class="input-group date">
    		<?php echo Form::text('attendance_by_shift_date_filter', \Carbon::createFromTimestamp(strtotime('today'))->format(session('business.date_format')), ['class' => 'form-control', 'id' => 'attendance_by_shift_date_filter', 'readonly' ]); ?>

    		<span class="input-group-addon"><i class="fas fa-calendar"></i></span>
    	</div>
	</div>
	<div class="col-md-12">
		<br>
		<table class="table" id="attendance_by_shift_table">
			<thead>
				<tr>
					<th>
						<?php echo app('translator')->get('essentials::lang.shift'); ?>
					</th>
					<th>
						<?php echo app('translator')->get('essentials::lang.present'); ?>
					</th>
					<th>
						<?php echo app('translator')->get('essentials::lang.absent'); ?>
					</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/attendance/attendance_by_shift.blade.php ENDPATH**/ ?>