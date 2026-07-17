<?php
	$count = 0;
?>
<?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	<?php if($package->is_private == 1 && !auth()->user()->can('superadmin')): ?>
		<?php
			continue;
		?>
	<?php endif; ?>

	<?php
		$businesses_ids = json_decode($package->businesses);
	?>

	<?php if(Route::current()->getName() == 'subscription.index' && (is_array($businesses_ids) && in_array(auth()->user()->business_id, $businesses_ids) || is_null($package->businesses))): ?>
		<?php
			$count++;
		?>
		<?php echo $__env->make('superadmin::subscription.partials.package_card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
	<?php elseif(Route::current()->getName() == 'package_duration_update' && is_null($package->businesses)): ?>
		<?php
			$count++;
		?>
		<?php echo $__env->make('superadmin::subscription.partials.package_card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
	<?php endif; ?>
	
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Superadmin\Providers/../Resources/views/subscription/partials/packages.blade.php ENDPATH**/ ?>