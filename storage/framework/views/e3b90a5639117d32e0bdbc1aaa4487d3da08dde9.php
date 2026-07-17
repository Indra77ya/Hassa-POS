
<?php $__env->startSection('title', __('product.add_new_product')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('product.add_new_product'); ?></h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <?php
    $form_class = empty($duplicate_product) ? 'create' : '';
    $is_image_required = !empty($common_settings['is_product_image_required']);
    ?>
    <?php echo Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'store']), 'method' => 'post',
    'id' => 'product_add_form','class' => 'product_form ' . $form_class, 'files' => true ]); ?>

    <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('name', __('product.product_name') . ':*'); ?>

                <?php echo Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, ['class' => 'form-control', 'required',
                'placeholder' => __('product.product_name')]); ?>

            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('sku', __('product.sku') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.sku') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::text('sku', null, ['class' => 'form-control',
                'placeholder' => __('product.sku')]); ?>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('barcode_type', __('product.barcode_type') . ':*'); ?>

                <?php echo Form::select('barcode_type', $barcode_types, !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default, ['class' => 'form-control select2', 'required']); ?>

            </div>
        </div>

        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('unit_id', __('product.unit') . ':*'); ?>

                <div class="input-group">
                    <?php echo Form::select('unit_id', $units, !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : session('business.default_unit'), ['class' => 'form-control select2', 'required']); ?>

                    <span class="input-group-btn">
                        <button type="button" <?php if(!auth()->user()->can('unit.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat btn-modal" data-href="<?php echo e(action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('unit.add_unit'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
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

                <?php echo Form::select('sub_unit_ids[]', [], !empty($duplicate_product->sub_unit_ids) ? $duplicate_product->sub_unit_ids : null, ['class' => 'form-control select2', 'multiple', 'id' => 'sub_unit_ids']); ?>

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
                <?php echo Form::select('secondary_unit_id', $units, !empty($duplicate_product->secondary_unit_id) ? $duplicate_product->secondary_unit_id : null, ['class' => 'form-control select2']); ?>

            </div>
        </div>
        <?php endif; ?>

        <div class="col-sm-4 <?php if(!session('business.enable_brand')): ?> hide <?php endif; ?>">
            <div class="form-group">
                <?php echo Form::label('brand_id', __('product.brand') . ':'); ?>

                <div class="input-group">
                    <?php echo Form::select('brand_id', $brands, !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

                    <span class="input-group-btn">
                        <button type="button" <?php if(!auth()->user()->can('brand.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat btn-modal" data-href="<?php echo e(action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('brand.add_brand'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-4 <?php if(!session('business.enable_category')): ?> hide <?php endif; ?>">
            <div class="form-group">
                <?php echo Form::label('category_id', __('product.category') . ':'); ?>

                <?php echo Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

            </div>
        </div>

        <div class="col-sm-4 <?php if(!(session('business.enable_category') && session('business.enable_sub_category'))): ?> hide <?php endif; ?>">
            <div class="form-group">
                <?php echo Form::label('sub_category_id', __('product.sub_category') . ':'); ?>

                <?php echo Form::select('sub_category_id', $sub_categories, !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

            </div>
        </div>

        <?php
        $default_location = null;
        if(count($business_locations) == 1){
        $default_location = array_key_first($business_locations->toArray());
        }
        ?>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('product_locations', __('business.business_locations') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.product_location_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::select('product_locations[]', $business_locations, $default_location, ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); ?>

            </div>
        </div>


        <div class="clearfix"></div>

        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <label>
                    <?php echo Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : true, ['class' => 'input-icheck', 'id' => 'enable_stock']); ?> <strong><?php echo app('translator')->get('product.manage_stock'); ?></strong>
                </label><?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.enable_stock') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?> <p class="help-block"><i><?php echo app('translator')->get('product.enable_stock_help'); ?></i></p>
            </div>
        </div>
        <div class="col-sm-4 <?php if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0): ?> hide <?php endif; ?>" id="alert_quantity_div">
            <div class="form-group">
                <?php echo Form::label('alert_quantity', __('product.alert_quantity') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.alert_quantity') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::text('alert_quantity', !empty($duplicate_product->alert_quantity) ? number_format($duplicate_product->alert_quantity, session('business.quantity_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']) : null , ['class' => 'form-control input_number',
                'placeholder' => __('product.alert_quantity'), 'min' => '0']); ?>

            </div>
        </div>
        <?php if(!empty($common_settings['enable_product_warranty'])): ?>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('warranty_id', __('lang_v1.warranty') . ':'); ?>

                <?php echo Form::select('warranty_id', $warranties, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); ?>

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
        <div class="col-sm-8 mb-5">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-8 product-description-label">
                        <?php echo Form::label('product_description', __('lang_v1.product_description') . ':'); ?>

                    </div> 
                </div>
                <?php echo Form::textarea('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null, ['class' => 'form-control']); ?>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
               
                <div class="row">
                    <div class="col-sm-6 image-label">
                    <?php echo Form::label('image', __('lang_v1.product_image') . ':'); ?>

                    </div> 
                </div>
                <?php echo Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*',
                'required' => $is_image_required, 'class' => 'upload-element']); ?>

                <small>
                    <p class="help-block"><?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?> <br> <?php echo app('translator')->get('lang_v1.aspect_ratio_should_be_1_1'); ?></p>
                </small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <?php echo Form::label('product_brochure', __('lang_v1.product_brochure') . ':'); ?>

            <?php echo Form::file('product_brochure', ['id' => 'product_brochure', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); ?>

            <small>
                <p class="help-block">
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
                    <?php echo Form::label('expiry_period', __('product.expires_in') . ':'); ?><br>
                    <?php echo Form::text('expiry_period', !empty($duplicate_product->expiry_period) ? number_format($duplicate_product->expiry_period, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']) : $expiry_period, ['class' => 'form-control pull-left input_number',
                    'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;']); ?>

                    <?php echo Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months', ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type']); ?>

                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <label>
                    <?php echo Form::checkbox('enable_sr_no', 1, !(empty($duplicate_product)) ? $duplicate_product->enable_sr_no : false, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.enable_imei_or_sr_no'); ?></strong>
                </label> <?php
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
                    <?php echo Form::checkbox('not_for_selling', 1, !(empty($duplicate_product)) ? $duplicate_product->not_for_selling : false, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.not_for_selling'); ?></strong>
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
                <?php echo Form::label('rack_' . $id, $location . ':'); ?>


                <?php if(session('business.enable_racks')): ?>
                <?php echo Form::text('product_racks[' . $id . '][rack]', !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null, ['class' => 'form-control', 'id' => 'rack_' . $id,
                'placeholder' => __('lang_v1.rack')]); ?>

                <?php endif; ?>

                <?php if(session('business.enable_row')): ?>
                <?php echo Form::text('product_racks[' . $id . '][row]', !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); ?>

                <?php endif; ?>

                <?php if(session('business.enable_position')): ?>
                <?php echo Form::text('product_racks[' . $id . '][position]', !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); ?>

                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('weight', __('lang_v1.weight') . ':'); ?>

                <?php echo Form::text('weight', !empty($duplicate_product->weight) ? $duplicate_product->weight : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight')]); ?>

            </div>
        </div>
        <?php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $product_custom_fields = !empty($custom_labels['product']) ? $custom_labels['product'] : [];
        $product_cf_details = !empty($custom_labels['product_cf_details']) ? $custom_labels['product_cf_details'] : [];

        ?>
        <!--custom fields-->
        <div class="clearfix"></div>

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
                        
                            <input type="<?php echo e($cf_type, false); ?>" name="<?php echo e($db_field_name, false); ?>" id="<?php echo e($db_field_name, false); ?>" value="<?php echo e(!empty($duplicate_product->$db_field_name) ? $duplicate_product->$db_field_name : null, false); ?>" class="form-control" placeholder="<?php echo e($cf, false); ?>">

                        <?php elseif($cf_type == 'dropdown'): ?>
                            <!-- <?php echo Form::select($db_field_name, $dropdown, !empty($duplicate_product->$db_field_name) ? $duplicate_product->$db_field_name : null, ['placeholder' => $cf, 'class' => 'form-control select2']); ?> -->
                            <select name="<?php echo e($db_field_name, false); ?>" id="<?php echo e($db_field_name, false); ?>" class="form-control select2">
                                <option value=""><?php echo e($cf, false); ?></option>
                                <?php $__currentLoopData = $dropdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option, false); ?>" <?php if(!empty($duplicate_product->$db_field_name) && $option == $duplicate_product->$db_field_name): ?> selected <?php endif; ?>>
                                        <?php echo e($option, false); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="col-sm-3">
            <div class="form-group">
                <?php echo Form::label('preparation_time_in_minutes', __('lang_v1.preparation_time_in_minutes') . ':'); ?>

                <?php echo Form::number('preparation_time_in_minutes', !empty($duplicate_product->preparation_time_in_minutes) ? $duplicate_product->preparation_time_in_minutes : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')]); ?>

            </div>
        </div>
        <!--custom fields-->
        <div class="clearfix"></div>
        <?php echo $__env->make('layouts.partials.module_form_part', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <?php echo $__env->renderComponent(); ?>

    <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
    <div class="row">

        <div class="col-sm-4 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
            <div class="form-group">
                <?php echo Form::label('tax', __('product.applicable_tax') . ':'); ?>

                <?php echo Form::select('tax', $taxes, !empty($duplicate_product->tax) ? $duplicate_product->tax : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); ?>

            </div>
        </div>

        <div class="col-sm-4 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
            <div class="form-group">
                <?php echo Form::label('tax_type', __('product.selling_price_tax_type') . ':*'); ?>

                <?php echo Form::select('tax_type', ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
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
                <?php echo Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, ['class' => 'form-control select2',
                'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

            </div>
        </div>

        <div class="form-group col-sm-12" id="product_form_part">
            <?php echo $__env->make('product.partials.single_product_form_part', ['profit_percent' => $default_profit_percent, 'profit_margin_type' => session('business.default_profit_margin_type')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <input type="hidden" id="variation_counter" value="1">
        <input type="hidden" id="default_profit_percent" value="<?php echo e($default_profit_percent, false); ?>">
        <input type="hidden" id="default_profit_margin_type" value="<?php echo e(session('business.default_profit_margin_type'), false); ?>">

    </div>
    <?php echo $__env->renderComponent(); ?>
    <div class="row">
        <div class="col-sm-12">
            <input type="hidden" name="submit_type" id="submit_type">
            <div class="text-center">
                <div class="btn-group">
                    <?php if($selling_price_group_count): ?>
                    <button type="submit" value="submit_n_add_selling_prices" class="tw-dw-btn tw-dw-btn-warning tw-dw-btn-lg tw-text-white submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_selling_price_group_prices'); ?></button>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.opening_stock')): ?>
                    <button id="opening_stock_button" <?php if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0): ?> disabled <?php endif; ?> type="submit" value="submit_n_add_opening_stock" class="tw-dw-btn tw-dw-btn-lg tw-text-white bg-purple submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_opening_stock'); ?></button>
                    <?php endif; ?>

                    <button type="submit" value="save_n_add_another" class="tw-dw-btn tw-dw-btn-lg bg-maroon submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_another'); ?></button>

                    <button type="submit" value="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white submit_product_form"><?php echo app('translator')->get('messages.save'); ?></button>
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
    $(document).ready(function() {
        __page_leave_confirmation('#product_add_form');
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function(sCode, iQty) {
                $('input#sku').val(sCode);
            },
            onScanError: function(oDebug) {
                console.log(oDebug);
            },
            minLength: 2,
            ignoreIfFocusOn: ['input', '.form-control']
            // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
            //     console.log('Pressed: ' + iKeyCode);
            // }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/product/create.blade.php ENDPATH**/ ?>