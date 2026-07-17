<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-condensed bg-gray">
				<thead>
					<tr class="bg-green">
						<th>SKU</th>
		                <th><?php echo app('translator')->get('business.product'); ?></th>
		                <th><?php echo app('translator')->get('business.location'); ?></th>
		                <th><?php echo app('translator')->get('sale.unit_price'); ?></th>
		                <th><?php echo app('translator')->get('report.current_stock'); ?></th>
		                <th><?php echo app('translator')->get('lang_v1.total_stock_price'); ?></th>
		                <th><?php echo app('translator')->get('report.total_unit_sold'); ?></th>
		                <th><?php echo app('translator')->get('lang_v1.total_unit_transfered'); ?></th>
		                <th><?php echo app('translator')->get('lang_v1.total_unit_adjusted'); ?></th>
		            </tr>
	            </thead>
	            <tbody>
	            	<?php $__currentLoopData = $product_stock_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	            		<tr>
	            			<td><?php echo e($product->sku, false); ?></td>
	            			<td>
	            				<?php
	            				$name = $product->product;
			                    if ($product->type == 'variable') {
			                        $name .= ' - ' . $product->product_variation . '-' . $product->variation_name;
			                    }
			                    ?>
			                    <?php echo e($name, false); ?>

	            			</td>
	            			<td><?php echo e($product->location_name, false); ?></td>
	            			<td>
                        		<span class="display_currency"data-currency_symbol=true ><?php echo e($product->unit_price ?? 0, false); ?></span>
                        	</td>
	            			<td>
                        		<span data-is_quantity="true" class="display_currency"data-currency_symbol=false ><?php echo e($product->stock ?? 0, false); ?></span><?php echo e($product->unit, false); ?>

                        	</td>
                        	<td>
                        		<span class="display_currency"data-currency_symbol=true ><?php echo e($product->unit_price * $product->stock, false); ?></span>
                        	</td>
                        	<td>
                        		<span data-is_quantity="true" class="display_currency"data-currency_symbol=false ><?php echo e($product->total_sold ?? 0, false); ?></span><?php echo e($product->unit, false); ?>

                        	</td>
                        	<td>
                        		<span data-is_quantity="true" class="display_currency"data-currency_symbol=false ><?php echo e($product->total_transfered ?? 0, false); ?></span><?php echo e($product->unit, false); ?>

                        	</td>
                        	<td>
                        		<span data-is_quantity="true" class="display_currency"data-currency_symbol=false ><?php echo e($product->total_adjusted ?? 0, false); ?></span><?php echo e($product->unit, false); ?>

                        	</td>
	            		</tr>
	            	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	            </tbody>
	     	</table>
     	</div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/partials/product_stock_details.blade.php ENDPATH**/ ?>