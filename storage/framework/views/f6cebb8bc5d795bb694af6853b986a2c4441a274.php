<?php if(!session('business.enable_price_tax')): ?> 
  <?php
    $default = 0;
    $class = 'hide';
  ?>
<?php else: ?>
  <?php
    $default = null;
    $class = '';
  ?>
<?php endif; ?>
<?php
    $common_settings = session()->get('business.common_settings');
?>

<div class="col-sm-12"><br>
    <div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed <?php echo e($class, false); ?>">
        <tr>
          <th><?php echo app('translator')->get('product.default_purchase_price'); ?></th>
          <th>
            <?php echo app('translator')->get('product.profit_percent'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.profit_percent') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
            <br/>
            <select class="form-control input-sm" id="profit_margin_type" name="profit_margin_type" style="width: 100%;">
                <option value="percentage" <?php if($product_deatails->variations->first()->profit_margin_type == 'percentage'): ?> selected <?php endif; ?>>%</option>
                <option value="fixed" <?php if($product_deatails->variations->first()->profit_margin_type == 'fixed'): ?> selected <?php endif; ?>>Fixed (Rp)</option>
            </select>
          </th>
          <th><?php echo app('translator')->get('product.default_selling_price'); ?></th>
          <th><?php echo app('translator')->get('lang_v1.product_image'); ?></th>
        </tr>
        <?php $__currentLoopData = $product_deatails->variations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $is_image_required = !empty($common_settings['is_product_image_required']) && count($variation->media) == 0;
            ?>
            <?php if($loop->first): ?>
                <tr>
                    <td>
                        <input type="hidden" name="single_variation_id" value="<?php echo e($variation->id, false); ?>">

                        <div class="col-sm-6">
                          <?php echo Form::label('single_dpp', trans('product.exc_of_tax') . ':*'); ?>


                          <?php echo Form::text('single_dpp', number_format($variation->default_purchase_price, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); ?>

                        </div>

                        <div class="col-sm-6">
                          <?php echo Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*'); ?>

                        
                          <?php echo Form::text('single_dpp_inc_tax', number_format($variation->dpp_inc_tax, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); ?>

                        </div>
                    </td>

                    <td>
                        <br/>
                        <?php echo Form::text('profit_percent', number_format($variation->profit_percent, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); ?>

                    </td>

                    <td>
                        <label><span class="dsp_label"></span></label>
                        <?php echo Form::text('single_dsp', number_format($variation->default_sell_price, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); ?>


                        <?php echo Form::text('single_dsp_inc_tax', number_format($variation->sell_price_inc_tax, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); ?>

                    </td>
                    <td>
                        <?php 
                            $action = !empty($action) ? $action : '';
                        ?>
                        <?php if($action !== 'duplicate'): ?>
                            <?php $__currentLoopData = $variation->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="img-thumbnail">
                                    <span class="badge bg-red delete-media" data-href="<?php echo e(action([\App\Http\Controllers\ProductController::class, 'deleteMedia'], ['media_id' => $media->id]), false); ?>"><i class="fas fa-times"></i></span>
                                    <?php echo $media->thumbnail(); ?>

                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                        <div class="form-group">
                            <?php echo Form::label('variation_images', __('lang_v1.product_image') . ':'); ?>

                            <?php echo Form::file('variation_images[]', ['class' => 'variation_images', 
                                'accept' => 'image/*', 'multiple', 'required' => $is_image_required]); ?>

                            <small><p class="help-block"><?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?> <br> <?php echo app('translator')->get('lang_v1.aspect_ratio_should_be_1_1'); ?></p></small>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/partials/edit_single_product_form_part.blade.php ENDPATH**/ ?>