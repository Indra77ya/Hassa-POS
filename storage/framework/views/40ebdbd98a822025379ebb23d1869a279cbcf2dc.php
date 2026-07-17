<div class="pos-tab-content">
    <div class="row">
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('default_tax_class',  __('woocommerce::lang.default_tax_class') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('woocommerce::lang.default_tax_class_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::text('default_tax_class',$default_settings['default_tax_class'], ['class' => 'form-control']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('product_tax_type',  __('woocommerce::lang.sync_product_price') . ':'); ?>

                <?php echo Form::select('product_tax_type', ['inc' => __('woocommerce::lang.inc_tax'), 'exc' => __('woocommerce::lang.exc_tax') ], $default_settings['product_tax_type'], ['class' => 'form-control']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('default_selling_price_group',  __('woocommerce::lang.default_selling_price_group') . ':'); ?>

                <?php echo Form::select('default_selling_price_group', $price_groups, $default_settings['default_selling_price_group'], ['class' => 'form-control select2', 'style' => 'width: 100%;']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('sync_description_as',  __('woocommerce::lang.sync_description_as') . ':'); ?>

                <?php echo Form::select('sync_description_as', ['short' => __('woocommerce::lang.short_description'), 'long' => __('woocommerce::lang.long_description'), 'both' => __('woocommerce::lang.both')], !empty($default_settings['sync_description_as']) ? $default_settings['sync_description_as'] : 'long', ['class' => 'form-control select2', 'style' => 'width: 100%;']); ?>

            </div>
        </div>
        <div class="col-xs-12">
            <hr>
        </div>
        <div class="col-xs-12">
            <div class="form-group">
                <label for="#"><?php echo app('translator')->get('woocommerce::lang.product_fields_to_be_synced_for_create'); ?>:</label><br>
                <label class="checkbox-inline">
                  <?php echo Form::checkbox('product_fields_for_create[]', 'name', true, ['class' => 'input-icheck', 'disabled'] ); ?>

                  <?php echo app('translator')->get('product.product_name'); ?>,
                </label>
                <label class="checkbox-inline">
                  <?php echo Form::checkbox('product_fields_for_create[]', 'price', true, ['class' => 'input-icheck', 'disabled'] ); ?>

                  <?php echo app('translator')->get('woocommerce::lang.price'); ?>,
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_create[]', 'category', in_array('category', $default_settings['product_fields_for_create']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('product.category'); ?>,
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_create[]', 'quantity', in_array('quantity', $default_settings['product_fields_for_create']), ['class' => 'input-icheck create_quantity'] ); ?> <?php echo app('translator')->get('sale.qty'); ?> 
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_create[]', 'weight', in_array('weight', $default_settings['product_fields_for_create']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('lang_v1.weight'); ?>
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_create[]', 'image', in_array('image', $default_settings['product_fields_for_create']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('woocommerce::lang.images'); ?>
                </label>
            </div>
            <div class="form-group">
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_create[]', 'description', in_array('description', $default_settings['product_fields_for_create']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('lang_v1.description'); ?>
                </label>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-xs-4 <?php if(in_array('quantity', $default_settings['product_fields_for_create'])): ?> hide <?php endif; ?> create_stock_settings">
            <div class="form-group">
                <?php echo Form::label('manage_stock_for_create',  __('product.manage_stock') . ':'); ?>

                <?php echo Form::select('manage_stock_for_create', ['true' => 'true', 'false' => 'false', 'none' => __('woocommerce::lang.dont_update') ], isset($default_settings['manage_stock_for_create']) ? $default_settings['manage_stock_for_create'] : 'none', ['class' => 'form-control']); ?>

            </div>
        </div>
        <div class="col-xs-4 <?php if(in_array('quantity', $default_settings['product_fields_for_create'])): ?> hide <?php endif; ?> create_stock_settings">
            <div class="form-group">
                <?php echo Form::label('in_stock_for_create',  __('woocommerce::lang.in_stock') . ':'); ?>

                <?php echo Form::select('in_stock_for_create', ['true' => 'true', 'false' => 'false', 'none' => __('woocommerce::lang.dont_update') ], isset($default_settings['in_stock_for_create']) ? $default_settings['in_stock_for_create'] : 'none', ['class' => 'form-control']); ?>

            </div>
        </div>
        <div class="col-xs-12">
            <hr>
        </div>
        <div class="col-xs-12">
            <br/>
            <div class="form-group">
                <label for="#"><?php echo app('translator')->get('woocommerce::lang.product_fields_to_be_synced_for_update'); ?>:</label><br>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'name', in_array('name', $default_settings['product_fields_for_update']), ['class' => 'input-icheck'] ); ?>

                  <?php echo app('translator')->get('product.product_name'); ?>,
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'price', in_array('price', $default_settings['product_fields_for_update']), ['class' => 'input-icheck'] ); ?>

                  <?php echo app('translator')->get('woocommerce::lang.price'); ?>,
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'category', in_array('category', $default_settings['product_fields_for_update']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('product.category'); ?>,
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'quantity', in_array('quantity', $default_settings['product_fields_for_update']), ['class' => 'input-icheck update_quantity'] ); ?> <?php echo app('translator')->get('sale.qty'); ?> 
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'weight', in_array('weight', $default_settings['product_fields_for_update']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('lang_v1.weight'); ?>
                </label>
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'image', in_array('image', $default_settings['product_fields_for_update']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('woocommerce::lang.images'); ?>
                </label>
            </div>
            <div class="form-group">
                <label class="checkbox-inline">
                    <?php echo Form::checkbox('product_fields_for_update[]', 'description', in_array('description', $default_settings['product_fields_for_create']), ['class' => 'input-icheck'] ); ?> <?php echo app('translator')->get('lang_v1.description'); ?>
                </label>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-xs-4 <?php if(in_array('quantity', $default_settings['product_fields_for_update'])): ?> hide <?php endif; ?> update_stock_settings">
            <div class="form-group">
                <?php echo Form::label('manage_stock_for_update',  __('product.manage_stock') . ':'); ?>

                <?php echo Form::select('manage_stock_for_update', ['true' => 'true', 'false' => 'false', 'none' => __('woocommerce::lang.dont_update') ], isset($default_settings['manage_stock_for_update']) ? $default_settings['manage_stock_for_update'] : 'none', ['class' => 'form-control']); ?>

            </div>
        </div>
        <div class="col-xs-4 <?php if(in_array('quantity', $default_settings['product_fields_for_update'])): ?> hide <?php endif; ?> update_stock_settings">
            <div class="form-group">
                <?php echo Form::label('in_stock_for_update',  __('woocommerce::lang.in_stock') . ':'); ?>

                <?php echo Form::select('in_stock_for_update', ['true' => 'true', 'false' => 'false', 'none' => __('woocommerce::lang.dont_update') ], isset($default_settings['in_stock_for_update']) ? $default_settings['in_stock_for_update'] : 'none', ['class' => 'form-control']); ?>

            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Woocommerce\Providers/../Resources/views/woocommerce/partials/product_sync_settings.blade.php ENDPATH**/ ?>