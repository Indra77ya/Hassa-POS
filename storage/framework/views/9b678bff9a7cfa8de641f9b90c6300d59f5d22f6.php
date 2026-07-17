<div class="modal fade" id="edit_product_location_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
	    <div class="modal-content">
	    	<?php echo Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateProductLocation']), 'method' => 'post', 'id' => 'edit_product_location_form' ]); ?>

		    	<div class="modal-header">
			    	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				      <h4 class="modal-title"><span class="add_to_location_title hide"><?php echo app('translator')->get( 'lang_v1.add_location_to_the_selected_products' ); ?></span><span class="remove_from_location_title hide"><?php echo app('translator')->get( 'lang_v1.remove_location_from_the_selected_products' ); ?></span></h4>
			    </div>
			    <div class="modal-body">
			    	<div class="form-group">
		                <?php echo Form::label('product_location',  __('purchase.business_location') . ':'); ?>

		                <?php echo Form::select('product_location[]', $business_locations, null, ['class' => 'form-control', 'style' => 'width:100%', 'required', 'multiple', 'id' => 'product_location']); ?>

		                <?php echo Form::hidden('products', null, ['id' => 'products_to_update_location']); ?>


		                <?php echo Form::hidden('update_type', null, ['id' => 'update_type']); ?>

		            </div>
			    </div>
			    <div class="modal-footer">
		      		<button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="update_product_location"><?php echo app('translator')->get( 'messages.save' ); ?></button>
		      		<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
		    	</div>
	    	<?php echo Form::close(); ?>

	    </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/partials/edit_product_location_modal.blade.php ENDPATH**/ ?>