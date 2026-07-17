<?php if($__is_essentials_enabled && $is_employee_allowed): ?>


    <button type="button" data-type="clock_in" data-toggle="tooltip" data-placement="bottom" title="<?php echo app('translator')->get('essentials::lang.clock_in'); ?>"
        class="<?php if(!empty($clock_in)): ?> hide <?php endif; ?> clock_in_btn  tw-inline-flex tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-transition-all tw-duration-200 tw-bg-<?php if(!empty(session('business.theme_color'))): ?><?php echo e(session('business.theme_color'), false); ?><?php else: ?><?php echo e('primary', false); ?><?php endif; ?>-800 hover:tw-bg-<?php if(!empty(session('business.theme_color'))): ?><?php echo e(session('business.theme_color'), false); ?><?php else: ?><?php echo e('primary', false); ?><?php endif; ?>-700 tw-p-1.5 tw-rounded-lg tw-ring-1 hover:tw-text-white tw-ring-white/10">
        <span class="tw-sr-only">
            Clock In
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
            <path d="M7 11l5 5l5 -5" />
            <path d="M12 4l0 12" />
        </svg>
    </button>


    <button type="button"
        class="<?php if(empty($clock_in)): ?> hide <?php endif; ?> clock_out_btn tw-inline-flex tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-transition-all tw-duration-200 tw-bg-primary-800 hover:tw-bg-primary-700 tw-p-1.5 tw-rounded-lg tw-ring-1 tw-ring-white/10 hover:tw-text-white"
        data-type="clock_out" data-toggle="popover" data-placement="bottom" data-html="true"
        title="<?php echo app('translator')->get('essentials::lang.clock_out'); ?> <?php if(!empty($clock_in)): ?> <br>
				<small>
					<b><?php echo app('translator')->get('essentials::lang.clocked_in_at'); ?>:</b> <?php echo e(\Carbon::createFromTimestamp(strtotime($clock_in->clock_in_time))->format(session('business.date_format') . ' ' . 'H:i'), false); ?>

				</small>
				<br>
				<small><b><?php echo app('translator')->get('essentials::lang.shift'); ?>:</b> <?php echo e(ucfirst($clock_in->shift_name), false); ?></small>
				<?php if(!empty($clock_in->start_time) && !empty($clock_in->end_time)): ?>
					<br>
					<small>
						<b><?php echo app('translator')->get('restaurant.start_time'); ?>:</b> <?php echo e(\Carbon::createFromTimestamp(strtotime($clock_in->start_time))->format('H:i'), false); ?><br>
						<b><?php echo app('translator')->get('restaurant.end_time'); ?>:</b> <?php echo e(\Carbon::createFromTimestamp(strtotime($clock_in->end_time))->format('H:i'), false); ?>

					</small> <?php endif; ?>
			<?php endif; ?>">
        <span class="tw-sr-only">
            Clock In
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
            <path d="M7 11l5 5l5 -5" />
            <path d="M12 4l0 12" />
        </svg>
    </button>
<?php endif; ?>
<?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/layouts/partials/header_part.blade.php ENDPATH**/ ?>