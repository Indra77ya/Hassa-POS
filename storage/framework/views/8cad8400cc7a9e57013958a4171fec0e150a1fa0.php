<?php $__env->startSection('title', __('product.edit_product')); ?>

<?php $__env->startSection('content'); ?>

<?php
  $is_image_required = !empty($common_settings['is_product_image_required']) && empty($product->image);
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('product.edit_product'); ?></h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
<?php echo Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'update'] , [$product->id] ), 'method' => 'PUT', 'id' => 'product_add_form',
        'class' => 'product_form', 'files' => true ]); ?>

    <input type="hidden" id="product_id" value="<?php echo e($product->id, false); ?>">

    <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
        <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('name', __('product.product_name') . ':*'); ?>

                  <?php echo Form::text('name', $product->name, ['class' => 'form-control', 'required',
                  'placeholder' => __('product.product_name')]); ?>

              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('sku', __('product.sku')  . ':*'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.sku') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::text('sku', $product->sku, ['class' => 'form-control',
                'placeholder' => __('product.sku'), 'required']); ?>

              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('barcode_type', __('product.barcode_type') . ':*'); ?>

                  <?php echo Form::select('barcode_type', $barcode_types, $product->barcode_type, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); ?>

              </div>
            </div>

            <div class="clearfix"></div>
            
            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('unit_id', __('product.unit') . ':*'); ?>

                <div class="input-group">
                  <?php echo Form::select('unit_id', $units, $product->unit_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); ?>

                  <span class="input-group-btn">
                    <button type="button" <?php if(!auth()->user()->can('unit.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat quick_add_unit btn-modal" data-href="<?php echo e(action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('unit.add_unit'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                  </span>
                </div>
              </div>
            </div>

            <div class="col-sm-4 <?php if(!session('business.enable_sub_units')): ?> hide <?php endif; ?>">
              <div class="form-group">
                <?php echo Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.sub_units_tooltip') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>

                <select name="sub_unit_ids[]" class="form-control select2" multiple id="sub_unit_ids">
                  <?php $__currentLoopData = $sub_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub_unit_id => $sub_unit_value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sub_unit_id, false); ?>" 
                      <?php if(is_array($product->sub_unit_ids) &&in_array($sub_unit_id, $product->sub_unit_ids)): ?>   selected 
                      <?php endif; ?>><?php echo e($sub_unit_value['name'], false); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>

            <?php if(!empty($common_settings['enable_secondary_unit'])): ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <?php echo Form::label('secondary_unit_id', __('lang_v1.secondary_unit') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.secondary_unit_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                        <?php echo Form::select('secondary_unit_id', $units, $product->secondary_unit_id, ['class' => 'form-control select2']); ?>

                    </div>
                </div>
            <?php endif; ?>

            <div class="col-sm-4 <?php if(!session('business.enable_brand')): ?> hide <?php endif; ?>">
              <div class="form-group">
                <?php echo Form::label('brand_id', __('product.brand') . ':'); ?>

                <div class="input-group">
                  <?php echo Form::select('brand_id', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

                  <span class="input-group-btn">
                    <button type="button" <?php if(!auth()->user()->can('brand.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat btn-modal" data-href="<?php echo e(action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('brand.add_brand'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                  </span>
                </div>
              </div>
            </div>
            <div class="col-sm-4 <?php if(!session('business.enable_category')): ?> hide <?php endif; ?>">
              <div class="form-group">
                <?php echo Form::label('category_id', __('product.category') . ':'); ?>

                  <?php echo Form::select('category_id', $categories, $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

              </div>
            </div>

            <div class="col-sm-4 <?php if(!(session('business.enable_category') && session('business.enable_sub_category'))): ?> hide <?php endif; ?>">
              <div class="form-group">
                <?php echo Form::label('sub_category_id', __('product.sub_category')  . ':'); ?>

                  <?php echo Form::select('sub_category_id', $sub_categories, $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('product_locations', __('business.business_locations') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.product_location_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                  <?php echo Form::select('product_locations[]', $business_locations, $product->product_locations->pluck('id'), ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); ?>

              </div>
            </div>

            <div class="clearfix"></div>
            
            <div class="col-sm-4">
              <div class="form-group">
              <br>
                <label>
                  <?php echo Form::checkbox('enable_stock', 1, $product->enable_stock, ['class' => 'input-icheck', 'id' => 'enable_stock']); ?> <strong><?php echo app('translator')->get('product.manage_stock'); ?></strong>
                </label><?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.enable_stock') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?> <p class="help-block"><i><?php echo app('translator')->get('product.enable_stock_help'); ?></i></p>
              </div>
            </div>
            <div class="col-sm-4" id="alert_quantity_div" <?php if(!$product->enable_stock): ?> style="display:none" <?php endif; ?>>
              <div class="form-group">
                <?php echo Form::label('alert_quantity', __('product.alert_quantity') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.alert_quantity') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::text('alert_quantity', $alert_quantity, ['class' => 'form-control input_number',
                'placeholder' => __('product.alert_quantity') , 'min' => '0']); ?>

              </div>
            </div>
            <?php if(!empty($common_settings['enable_product_warranty'])): ?>
            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('warranty_id', __('lang_v1.warranty') . ':'); ?>

                <?php echo Form::select('warranty_id', $warranties, $product->warranty_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); ?>

              </div>
            </div>
            <?php endif; ?>
            <!-- include module fields -->
            <?php if(!empty($pos_module_data)): ?>
                <?php $__currentLoopData = $pos_module_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!empty($value['view_path'])): ?>
                        <?php if ($__env->exists($value['view_path'], ['view_data' => $value['view_data']])) echo $__env->make($value['view_path'], ['view_data' => $value['view_data']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <div class="clearfix"></div>
            <div class="col-sm-8">
              <div class="form-group">
                <?php echo Form::label('product_description', __('lang_v1.product_description') . ':'); ?>

                  <?php echo Form::textarea('product_description', $product->product_description, ['class' => 'form-control']); ?>

              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('image', __('lang_v1.product_image') . ':'); ?>

                <?php echo Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*', 'required' => $is_image_required]); ?>

                <small><p class="help-block"><?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?>. <?php echo app('translator')->get('lang_v1.aspect_ratio_should_be_1_1'); ?> <?php if(!empty($product->image)): ?> <br> <?php echo app('translator')->get('lang_v1.previous_image_will_be_replaced'); ?> <?php endif; ?></p></small>
              </div>
            </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('product_brochure', __('lang_v1.product_brochure') . ':'); ?>

                <?php echo Form::file('product_brochure', ['id' => 'product_brochure', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); ?>

                <small>
                    <p class="help-block">
                        <?php echo app('translator')->get('lang_v1.previous_file_will_be_replaced'); ?><br>
                        <?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?>
                        <?php if ($__env->exists('components.document_help_text')) echo $__env->make('components.document_help_text', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </p>
                </small>
              </div>
            </div>
    <?php echo $__env->renderComponent(); ?>

    <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
        <div class="row">
        <?php if(session('business.enable_product_expiry')): ?>

          <?php if(session('business.expiry_type') == 'add_expiry'): ?>
            <?php
              $expiry_period = 12;
              $hide = true;
            ?>
          <?php else: ?>
            <?php
              $expiry_period = null;
              $hide = false;
            ?>
          <?php endif; ?>
          <div class="col-sm-4 <?php if($hide): ?> hide <?php endif; ?>">
            <div class="form-group">
              <div class="multi-input">
                <?php
                  $disabled = false;
                  $disabled_period = false;
                  if( empty($product->expiry_period_type) || empty($product->enable_stock) ){
                    $disabled = true;
                  }
                  if( empty($product->enable_stock) ){
                    $disabled_period = true;
                  }
                ?>
                  <?php echo Form::label('expiry_period', __('product.expires_in') . ':'); ?><br>
                  <?php echo Form::text('expiry_period', number_format($product->expiry_period, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control pull-left input_number',
                    'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;', 'disabled' => $disabled]); ?>

                  <?php echo Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], $product->expiry_period_type, ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type', 'disabled' => $disabled_period]); ?>

              </div>
            </div>
          </div>
          <?php endif; ?>
          <div class="col-sm-4">
            <div class="checkbox">
              <label>
                <?php echo Form::checkbox('enable_sr_no', 1, $product->enable_sr_no, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.enable_imei_or_sr_no'); ?></strong>
              </label>
              <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.tooltip_sr_no') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
            </div>
          </div>

          <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              <?php echo Form::checkbox('not_for_selling', 1, $product->not_for_selling, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.not_for_selling'); ?></strong>
            </label> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.tooltip_not_for_selling') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
          </div>
        </div>

        <div class="clearfix"></div>

        <!-- Rack, Row & position number -->
        <?php if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position')): ?>
          <div class="col-md-12">
            <h4><?php echo app('translator')->get('lang_v1.rack_details'); ?>:
              <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.tooltip_rack_details') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
            </h4>
          </div>
          <?php $__currentLoopData = $business_locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-sm-3">
              <div class="form-group">
                <?php echo Form::label('rack_' . $id,  $location . ':'); ?>


                
                  <?php if(!empty($rack_details[$id])): ?>
                    <?php if(session('business.enable_racks')): ?>
                      <?php echo Form::text('product_racks_update[' . $id . '][rack]', $rack_details[$id]['rack'], ['class' => 'form-control', 'id' => 'rack_' . $id]); ?>

                    <?php endif; ?>

                    <?php if(session('business.enable_row')): ?>
                      <?php echo Form::text('product_racks_update[' . $id . '][row]', $rack_details[$id]['row'], ['class' => 'form-control']); ?>

                    <?php endif; ?>

                    <?php if(session('business.enable_position')): ?>
                      <?php echo Form::text('product_racks_update[' . $id . '][position]', $rack_details[$id]['position'], ['class' => 'form-control']); ?>

                    <?php endif; ?>
                  <?php else: ?>
                    <?php echo Form::text('product_racks[' . $id . '][rack]', null, ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')]); ?>


                    <?php echo Form::text('product_racks[' . $id . '][row]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); ?>


                    <?php echo Form::text('product_racks[' . $id . '][position]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); ?>

                  <?php endif; ?>

              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>


        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('weight',  __('lang_v1.weight') . ':'); ?>

            <?php echo Form::text('weight', $product->weight, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight')]); ?>

          </div>
        </div>
        <div class="clearfix"></div>
        
        <?php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $product_custom_fields = !empty($custom_labels['product']) ? $custom_labels['product'] : [];
            $product_cf_details = !empty($custom_labels['product_cf_details']) ? $custom_labels['product_cf_details'] : [];
        ?>
        <!--custom fields-->

        <?php $__currentLoopData = $product_custom_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!empty($cf)): ?>
                <?php
                    $db_field_name = 'product_custom_field' . $loop->iteration;
                    $cf_type = !empty($product_cf_details[$loop->iteration]['type']) ? $product_cf_details[$loop->iteration]['type'] : 'text';
                    $dropdown = !empty($product_cf_details[$loop->iteration]['dropdown_options']) ? explode(PHP_EOL, $product_cf_details[$loop->iteration]['dropdown_options']) : [];
                ?>

                <div class="col-sm-3">
                    <div class="form-group">
                        <?php echo Form::label($db_field_name, $cf . ':'); ?>

                        <?php if(in_array($cf_type, ['text', 'date'])): ?>
                            <input type="<?php echo e($cf_type, false); ?>" name="<?php echo e($db_field_name, false); ?>" id="<?php echo e($db_field_name, false); ?>" 
                            value="<?php echo e($product->$db_field_name, false); ?>" class="form-control" placeholder="<?php echo e($cf, false); ?>">
                        <?php elseif($cf_type == 'dropdown'): ?>
                            <!-- <?php echo Form::select($db_field_name, $dropdown, $product->$db_field_name, ['placeholder' => $cf, 'class' => 'form-control select2']); ?> -->
                             <select name="<?php echo e($db_field_name, false); ?>" id="<?php echo e($db_field_name, false); ?>" class="form-control select2">
                                <?php $__currentLoopData = $dropdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option, false); ?>" <?php if($option == $product->$db_field_name): ?> selected <?php endif; ?>><?php echo e($option, false); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                             </select>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('preparation_time_in_minutes',  __('lang_v1.preparation_time_in_minutes') . ':'); ?>

            <?php echo Form::number('preparation_time_in_minutes', $product->preparation_time_in_minutes, ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')]); ?>

          </div>
        </div>
        <!--custom fields-->
        <?php echo $__env->make('layouts.partials.module_form_part', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    <?php echo $__env->renderComponent(); ?>

    <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
        <div class="row">
            <div class="col-sm-4 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
              <div class="form-group">
                <?php echo Form::label('tax', __('product.applicable_tax') . ':'); ?>

                  <?php echo Form::select('tax', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); ?>

              </div>
            </div>

            <div class="col-sm-4 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
              <div class="form-group">
                <?php echo Form::label('tax_type', __('product.selling_price_tax_type') . ':*'); ?>

                  <?php echo Form::select('tax_type',['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], $product->tax_type,
                  ['class' => 'form-control select2', 'required']); ?>

              </div>
            </div>

            <div class="clearfix"></div>
            <div class="col-sm-4">
              <div class="form-group">
                <?php echo Form::label('type', __('product.product_type') . ':*'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.product_type') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::select('type', $product_types, $product->type, ['class' => 'form-control select2',
                  'required','disabled', 'data-action' => 'edit', 'data-product_id' => $product->id ]); ?>

              </div>
            </div>

            <div class="form-group col-sm-12" id="product_form_part"></div>
            <input type="hidden" id="variation_counter" value="0">
            <input type="hidden" id="default_profit_percent" value="<?php echo e($default_profit_percent, false); ?>">
            </div>
    <?php echo $__env->renderComponent(); ?>

  <div class="row">
    <input type="hidden" name="submit_type" id="submit_type">
        <div class="col-sm-12">
          <div class="text-center">
            <div class="btn-group">
              <?php if($selling_price_group_count): ?>
                <button type="submit" value="submit_n_add_selling_prices" class="tw-dw-btn tw-dw-btn-warning tw-text-white tw-dw-btn-lg submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_selling_price_group_prices'); ?></button>
              <?php endif; ?>

              <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.opening_stock')): ?>
              <button type="submit" <?php if(empty($product->enable_stock)): ?> disabled="true" <?php endif; ?> id="opening_stock_button"  value="update_n_edit_opening_stock" class="tw-dw-btn tw-text-white tw-dw-btn-lg bg-purple submit_product_form"><?php echo app('translator')->get('lang_v1.update_n_edit_opening_stock'); ?></button>
              <?php endif; ?>

              <button type="submit" value="save_n_add_another" class="tw-dw-btn tw-text-white tw-dw-btn-lg bg-maroon submit_product_form"><?php echo app('translator')->get('lang_v1.update_n_add_another'); ?></button>

              <button type="submit" value="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-lg submit_product_form"><?php echo app('translator')->get('messages.update'); ?></button>
            </div>
          </div>
        </div>
  </div>
<?php echo Form::close(); ?>

</section>
<!-- /.content -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
  <script src="<?php echo e(asset('js/product.js?v=' . $asset_v), false); ?>"></script>
  <script type="text/javascript">
    $(document).ready( function(){
      __page_leave_confirmation('#product_add_form');
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/edit.blade.php ENDPATH**/ ?>