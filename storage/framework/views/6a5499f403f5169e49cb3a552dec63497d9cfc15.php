<?php if($__is_repair_enabled): ?>
	<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("repair.create")): ?>
		
		<a href="<?php echo e(action([\App\Http\Controllers\SellPosController::class, 'create']). '?sub_type=repair', false); ?>" title="<?php echo e(__('repair::lang.add_repair'), false); ?>" data-toggle="tooltip" data-placement="bottom"
		class="tw-hidden sm:tw-inline-flex tw-transition-all tw-duration-200 tw-gap-2 tw-bg-<?php if(!empty(session('business.theme_color'))): ?><?php echo e(session('business.theme_color'), false); ?><?php else: ?><?php echo e('primary', false); ?><?php endif; ?>-800 hover:tw-bg-<?php if(!empty(session('business.theme_color'))): ?><?php echo e(session('business.theme_color'), false); ?><?php else: ?><?php echo e('primary', false); ?><?php endif; ?>-700 tw-py-1.5 tw-px-3 tw-rounded-lg tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-ring-1 tw-ring-white/10 tw-text-white hover:tw-text-white">
		<svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
			stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
			stroke-linejoin="round">
			<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
			<path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5">
			</path>
		</svg>
		<?php echo app('translator')->get('repair::lang.repair'); ?>
	</a>
	<?php endif; ?>
<?php endif; ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Repair\Providers/../Resources/views/layouts/partials/header.blade.php ENDPATH**/ ?>