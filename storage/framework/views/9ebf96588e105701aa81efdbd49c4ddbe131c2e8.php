<?php $__empty_1 = true; $__currentLoopData = $attendance_by_shift; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
	<td><?php echo e($data['shift'], false); ?></td>
	<td><?php echo e($data['present'], false); ?><br><small><span class="label bg-info"><?php echo implode('</span>,  <span class="label bg-info">', $data['present_users']); ?> </span></small></td>
	<td><?php echo e($data['total'] - $data['present'], false); ?> <br><small><span class="label bg-info"><?php echo implode('</span>, <span class="label bg-info">', array_diff($data['all_users'], $data['present_users'])); ?></span></small></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
	<tr>
		<td colspan="3" class="text-center"><?php echo app('translator')->get('essentials::lang.no_data_found'); ?></td>
	</tr>
<?php endif; ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/attendance/attendance_by_shift_data.blade.php ENDPATH**/ ?>