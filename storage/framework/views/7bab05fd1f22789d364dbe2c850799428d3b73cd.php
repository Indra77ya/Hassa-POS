<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		      <h4 class="modal-title" id="modalTitle"><?php echo e($product->name, false); ?></h4>
	    </div>
	    <div class="modal-body">
      		<div class="row">
      			<div class="col-sm-9">
	      			<div class="col-sm-4 invoice-col">
	      				<b><?php echo app('translator')->get('product.sku'); ?>:</b>
						<?php echo e($product->sku, false); ?><br>
						<b><?php echo app('translator')->get('product.brand'); ?>: </b>
						<?php echo e($product->brand->name ?? '--', false); ?><br>
						<b><?php echo app('translator')->get('product.unit'); ?>: </b>
						<?php echo e($product->unit->short_name ?? '--', false); ?><br>
						<b><?php echo app('translator')->get('product.barcode_type'); ?>: </b>
						<?php echo e($product->barcode_type ?? '--', false); ?>

						<?php 
    						$custom_labels = json_decode(session('business.custom_labels'), true);
						?>

                        <?php for($i = 1; $i <= 20; $i++): ?>
                            <?php
                                $db_field = 'product_custom_field' . $i;
                                $label = 'custom_field_' .$i;
                            ?>

                            <?php if(!empty($product->$db_field)): ?>
                                <br/>
                                <b><?php echo e($custom_labels['product'][$label] ?? '', false); ?>: </b>
                                <?php echo e($product->$db_field, false); ?>

                            <?php endif; ?>
                        <?php endfor; ?>
						
						<br>
						<strong><?php echo app('translator')->get('lang_v1.available_in_locations'); ?>:</strong>
						<?php if(count($product->product_locations) > 0): ?>
							<?php echo e(implode(', ', $product->product_locations->pluck('name')->toArray()), false); ?>

						<?php else: ?>
							<?php echo app('translator')->get('lang_v1.none'); ?>
						<?php endif; ?>
						<?php if(!empty($product->media->first())): ?> <br>
							<strong><?php echo app('translator')->get('lang_v1.product_brochure'); ?>:</strong>
							<a href="<?php echo e($product->media->first()->display_url, false); ?>" download="<?php echo e($product->media->first()->display_name, false); ?>">
								<span class="label label-info">
									<i class="fas fa-download"></i>
									<?php echo e($product->media->first()->display_name, false); ?>

								</span>
							</a>
						<?php endif; ?>
	      			</div>

	      			<div class="col-sm-4 invoice-col">
						<b><?php echo app('translator')->get('product.category'); ?>: </b>
						<?php echo e($product->category->name ?? '--', false); ?><br>
						<b><?php echo app('translator')->get('product.sub_category'); ?>: </b>
						<?php echo e($product->sub_category->name ?? '--', false); ?><br>	
						
						<b><?php echo app('translator')->get('product.manage_stock'); ?>: </b>
						<?php if($product->enable_stock): ?>
							<?php echo app('translator')->get('messages.yes'); ?>
						<?php else: ?>
							<?php echo app('translator')->get('messages.no'); ?>
						<?php endif; ?>
						<br>
						<?php if($product->enable_stock): ?>
							<b><?php echo app('translator')->get('product.alert_quantity'); ?>: </b>
							<?php echo e($product->alert_quantity ?? '--', false); ?>

						<?php endif; ?>

						<?php if(!empty($product->warranty)): ?>
							<br>
							<b><?php echo app('translator')->get('lang_v1.warranty'); ?>: </b>
							<?php echo e($product->warranty->display_name, false); ?>

						<?php endif; ?>
	      			</div>
					
	      			<div class="col-sm-4 invoice-col">
	      				<b><?php echo app('translator')->get('product.expires_in'); ?>: </b>
	      				<?php
	  						$expiry_array = ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ];
	  					?>
	      				<?php if(!empty($product->expiry_period) && !empty($product->expiry_period_type)): ?>
							<?php echo e($product->expiry_period, false); ?> <?php echo e($expiry_array[$product->expiry_period_type], false); ?>

						<?php else: ?>
							<?php echo e($expiry_array[''], false); ?>

	      				<?php endif; ?>
	      				<br>
						<?php if($product->weight): ?>
							<b><?php echo app('translator')->get('lang_v1.weight'); ?>: </b>
							<?php echo e($product->weight, false); ?><br>
						<?php endif; ?>
						<b><?php echo app('translator')->get('product.applicable_tax'); ?>: </b>
						<?php echo e($product->product_tax->name ?? __('lang_v1.none'), false); ?><br>
						<?php
							$tax_type = ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')];
						?>
						<b><?php echo app('translator')->get('product.selling_price_tax_type'); ?>: </b>
						<?php echo e($tax_type[$product->tax_type], false); ?><br>
						<b><?php echo app('translator')->get('product.product_type'); ?>: </b>
						<?php echo app('translator')->get('lang_v1.' . $product->type); ?>
						
	      			</div>
	      			<div class="clearfix"></div>
	      			<br>
      				<div class="col-sm-12">
      					<?php echo $product->product_description; ?>

      				</div>
	      		</div>
      			<div class="col-sm-3 col-md-3 invoice-col">
      				<div class="thumbnail">
      					<img src="<?php echo e($product->image_url, false); ?>" alt="Product image">
      				</div>
      			</div>
      		</div>
      		<?php if($rack_details->count()): ?>
      		<?php if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position')): ?>
      			<div class="row">
      				<div class="col-md-12">
      					<h4><?php echo app('translator')->get('lang_v1.rack_details'); ?>:</h4>
      				</div>
      				<div class="col-md-12">
      					<div class="table-responsive">
      					<table class="table table-condensed bg-gray">
      						<tr class="bg-green">
      							<th><?php echo app('translator')->get('business.location'); ?></th>
      							<?php if(session('business.enable_racks')): ?>
      								<th><?php echo app('translator')->get('lang_v1.rack'); ?></th>
      							<?php endif; ?>
      							<?php if(session('business.enable_row')): ?>
      								<th><?php echo app('translator')->get('lang_v1.row'); ?></th>
      							<?php endif; ?>
      							<?php if(session('business.enable_position')): ?>
      								<th><?php echo app('translator')->get('lang_v1.position'); ?></th>
      							<?php endif; ?>
      							</tr>
      						<?php $__currentLoopData = $rack_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      							<tr>
	      							<td><?php echo e($rd->name, false); ?></td>
	      							<?php if(session('business.enable_racks')): ?>
	      								<td><?php echo e($rd->rack, false); ?></td>
	      							<?php endif; ?>
	      							<?php if(session('business.enable_row')): ?>
	      								<td><?php echo e($rd->row, false); ?></td>
	      							<?php endif; ?>
	      							<?php if(session('business.enable_position')): ?>
	      								<td><?php echo e($rd->position, false); ?></td>
	      							<?php endif; ?>
      							</tr>
      						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      					</table>
      					</div>
      				</div>
      			</div>
      		<?php endif; ?>
      		<?php endif; ?>
      		<?php if($product->type == 'single'): ?>
      			<?php echo $__env->make('product.partials.single_product_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      		<?php elseif($product->type == 'variable'): ?>
      			<?php echo $__env->make('product.partials.variable_product_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      		<?php elseif($product->type == 'combo'): ?>
      			<?php echo $__env->make('product.partials.combo_product_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      		<?php endif; ?>
      		<?php if($product->enable_stock == 1): ?>
	      		<div class="row">
	      			<div class="col-md-12">
	      				<strong><?php echo app('translator')->get('lang_v1.product_stock_details'); ?></strong>
	      			</div>
	      			<div class="col-md-12" id="view_product_stock_details" data-product_id="<?php echo e($product->id, false); ?>">
	      			</div>
	      		</div>
      		<?php endif; ?>
      	</div>
      	<div class="modal-footer">
      		<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" 
	        aria-label="Print" 
	          onclick="$(this).closest('div.modal').printThis();">
	        <i class="fa fa-print"></i> <?php echo app('translator')->get( 'messages.print' ); ?>
	      </button>
	      	<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
	    </div>
	</div>
</div>
<?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/view-modal.blade.php ENDPATH**/ ?>