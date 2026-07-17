<tbody class="product_rows" id="product_<?php echo e($product->id, false); ?>">
	<tr class="bg-green">
		<td><?php echo e($product->name, false); ?> (<?php echo e($product->sku, false); ?>)</td>
		<td>
			<?php echo Form::select('products[' . $product->id . '][category_id]', $categories, $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm category_id', 'style' => 'width: 100%;']); ?>

		</td>
		<td>
			<?php echo Form::select('products[' . $product->id . '][sub_category_id]', !empty($sub_categories[$product->category_id]) ? $sub_categories[$product->category_id] : [], $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm sub_category_id', 'style' => 'width: 100%;']); ?>

		</td>
		<td>
			<?php echo Form::select('products[' . $product->id . '][brand_id]', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm', 'style' => 'width: 100%;']); ?>

		</td>
		<td>
			<?php echo Form::select('products[' . $product->id . '][tax]', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm row_tax', 'style' => 'width: 100%;'],$tax_attributes); ?>

		</td>
		<td>
			<?php echo Form::select('products[' . $product->id . '][product_locations][]', $business_locations, $product->product_locations->pluck('id'), ['class' => 'form-control select2', 'multiple']); ?>

		</td>
	<tr>
	<tr>
		<td colspan="6">
			<table class="table">
				<thead>
					<tr>
						<th><?php echo app('translator')->get('lang_v1.variation'); ?></th>
						<th><?php echo app('translator')->get('product.default_purchase_price'); ?></th>
						<th><?php echo app('translator')->get('product.profit_percent'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.profit_percent') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?></th>
                		<th><?php echo app('translator')->get('product.default_selling_price'); ?></th>
                		<th><?php echo app('translator')->get('lang_v1.group_price'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php $__currentLoopData = $product->variations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<tr class="variation_row">
						<?php echo $__env->make('product.partials.bulk_edit_variation_row', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
					</tr>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</tbody>
			</table>
		</td>
	</tr>
</tbody><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/partials/bulk_edit_product_row.blade.php ENDPATH**/ ?>