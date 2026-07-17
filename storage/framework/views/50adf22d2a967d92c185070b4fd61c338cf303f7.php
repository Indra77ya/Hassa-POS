<td>
	<?php if($product->type == 'variable'): ?>
		<?php echo e($variation->product_variation->name, false); ?>

		- <?php echo e($variation->name, false); ?> (<?php echo e($variation->sub_sku, false); ?>)
	<?php endif; ?>
</td>
<td>
<div class="input-group">
	<span class="input-group-addon"><small><?php echo app('translator')->get('product.exc_of_tax'); ?></small></span>
	<?php echo Form::text('products[' . $product->id . '][variations][' . $variation->id . '][default_purchase_price]', number_format($variation->default_purchase_price, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['placeholder' => __('product.exc_of_tax'), 'class' => 'form-control input-sm input_number pp_exc_tax']); ?>

</div>
<div class="input-group">
	<span class="input-group-addon"><small><?php echo app('translator')->get('product.inc_of_tax'); ?></small></span>
	<?php echo Form::text('products[' . $product->id . '][variations][' . $variation->id . '][dpp_inc_tax]', number_format($variation->dpp_inc_tax, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['placeholder' => __('product.inc_of_tax'), 'class' => 'form-control input-sm input_number pp_inc_tax']); ?></td>
</div>
<td>
	<select class="form-control input-sm profit_margin_type" name="products[<?php echo e($product->id, false); ?>][variations][<?php echo e($variation->id, false); ?>][profit_margin_type]" style="width: 100%;">
		<option value="percentage" <?php if($variation->profit_margin_type == 'percentage'): ?> selected <?php endif; ?>>%</option>
		<option value="fixed" <?php if($variation->profit_margin_type == 'fixed'): ?> selected <?php endif; ?>>Fixed (Rp)</option>
	</select>
	<?php echo Form::text('products[' . $product->id . '][variations][' . $variation->id . '][profit_percent]', number_format($variation->profit_percent, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input-sm input_number profit_percent']); ?>

</td>
<td>
	<div class="input-group">
		<span class="input-group-addon"><small><?php echo app('translator')->get('product.exc_of_tax'); ?></small></span>
		<?php echo Form::text('products[' . $product->id . '][variations][' . $variation->id . '][default_sell_price]', number_format($variation->default_sell_price, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['placeholder' => __('product.exc_of_tax'), 'class' => 'form-control input-sm input_number sp_exc_tax']); ?>

	</div>

	<div class="input-group">
		<span class="input-group-addon"><small><?php echo app('translator')->get('product.inc_of_tax'); ?></small></span>
		<?php echo Form::text('products[' . $product->id . '][variations][' . $variation->id . '][sell_price_inc_tax]', number_format($variation->sell_price_inc_tax, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['placeholder' => __('product.dpp_inc_tax'), 'class' => 'form-control input-sm input_number sp_inc_tax']); ?>

	</div>
</td>
<td style="text-align: left;">
	<?php $__currentLoopData = $price_groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<?php
			$price_grp = $variation->group_prices->filter(function($item) use($k) {
			    return $item->price_group_id == $k;
			})->first();
		?>
		<div class="input-group" style="width: 100%;">
			<span class="input-group-addon"><small><?php echo e($v, false); ?> -</small></span>
			<?php echo Form::text('products[' . $product->id . '][variations][' . $variation->id . '][group_prices][' . $k . ']', !empty($price_grp) ? number_format($price_grp->price_inc_tax, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']) : 0, ['class' => 'form-control input-sm input_number']); ?>

		</div>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</td><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/partials/bulk_edit_variation_row.blade.php ENDPATH**/ ?>