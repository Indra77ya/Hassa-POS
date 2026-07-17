<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('default_sales_discount', __('business.default_sales_discount') . ':*'); ?>

                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-percent"></i>
                    </span>
                    <?php echo Form::text('default_sales_discount', number_format($business->default_sales_discount, session('business.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']), ['class' => 'form-control input_number']); ?>

                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('default_sales_tax', __('business.default_sales_tax') . ':'); ?>

                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    <?php echo Form::select('default_sales_tax', $tax_rates, $business->default_sales_tax, ['class' => 'form-control select2','placeholder' => __('business.default_sales_tax'), 'style' => 'width: 100%;']); ?>

                </div>
            </div>
        </div>
        <!-- <div class="clearfix"></div> -->

        
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('item_addition_method', __('lang_v1.sales_item_addition_method') . ':'); ?>

                <?php echo Form::select('item_addition_method', [ 0 => __('lang_v1.add_item_in_new_row'), 1 =>  __('lang_v1.increase_item_qty')], $business->item_addition_method, ['class' => 'form-control select2', 'style' => 'width: 100%;']); ?>

            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('amount_rounding_method', __('lang_v1.amount_rounding_method') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.amount_rounding_method_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                <?php echo Form::select('pos_settings[amount_rounding_method]', 
                [ 
                    '1' =>  __('lang_v1.round_to_nearest_whole_number'), 
                    '0.05' =>  __('lang_v1.round_to_nearest_decimal', ['multiple' => 0.05]), 
                    '0.1' =>  __('lang_v1.round_to_nearest_decimal', ['multiple' => 0.1]),
                    '0.5' =>  __('lang_v1.round_to_nearest_decimal', ['multiple' => 0.5])
                ], 
                !empty($pos_settings['amount_rounding_method']) ? $pos_settings['amount_rounding_method'] : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.none')]); ?>

            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    <?php echo Form::checkbox('pos_settings[enable_msp]', 1,  
                        !empty($pos_settings['enable_msp']) ? true : false , 
                    [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.sale_price_is_minimum_sale_price' ), false); ?> 
                  </label>
                  <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.minimum_sale_price_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    <?php echo Form::checkbox('pos_settings[allow_overselling]', 1,  
                        !empty($pos_settings['allow_overselling']) ? true : false , 
                    [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.allow_overselling' ), false); ?> 
                  </label>
                  <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.allow_overselling_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    <?php echo Form::checkbox('pos_settings[enable_sales_order]', 1, !empty($pos_settings['enable_sales_order']) , [ 'class' => 'input-icheck', 'id' => 'enable_sales_order']); ?> <?php echo e(__( 'lang_v1.enable_sales_order' ), false); ?>

                    </label>
                  <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.sales_order_help_text') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    <?php echo Form::checkbox('pos_settings[is_pay_term_required]', 1, !empty($pos_settings['is_pay_term_required']) , [ 'class' => 'input-icheck', 'id' => 'is_pay_term_required']); ?> <?php echo e(__( 'lang_v1.is_pay_term_required' ), false); ?>

                    </label>
                </div>
            </div>
        </div>

    </div>
    <hr>
    <div class="row">
        <div class="col-md-12"><h4><?php echo app('translator')->get('lang_v1.commission_agent'); ?>:</h4></div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('sales_cmsn_agnt', __('lang_v1.sales_commission_agent') . ':'); ?>

                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    <?php echo Form::select('sales_cmsn_agnt', $commission_agent_dropdown, $business->sales_cmsn_agnt, ['class' => 'form-control select2', 'style' => 'width: 100%;']); ?>

                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('cmmsn_calculation_type', __('lang_v1.cmmsn_calculation_type') . ':'); ?>

                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    <?php echo Form::select('pos_settings[cmmsn_calculation_type]', ['invoice_value' => __('lang_v1.invoice_value'), 'payment_received' => __('lang_v1.payment_received')], !empty($pos_settings['cmmsn_calculation_type']) ? $pos_settings['cmmsn_calculation_type'] : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'cmmsn_calculation_type']); ?>

                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    <?php echo Form::checkbox('pos_settings[is_commission_agent_required]', 1, !empty($pos_settings['is_commission_agent_required']) , [ 'class' => 'input-icheck', 'id' => 'is_commission_agent_required']); ?> <?php echo e(__( 'lang_v1.is_commission_agent_required' ), false); ?>

                    </label>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12"><h4><?php echo app('translator')->get('lang_v1.payment_link'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.payment_link_help_text') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>:</h4></div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    <?php echo Form::checkbox('pos_settings[enable_payment_link]', 1, !empty($pos_settings['enable_payment_link']) , [ 'class' => 'input-icheck', 'id' => 'enable_payment_link']); ?> <?php echo e(__( 'lang_v1.enable_payment_link' ), false); ?>

                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <h4>Razorpay: <small>(For INR India)</small></h4>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('razor_pay_key_id', 'Key ID:'); ?>

                <?php echo Form::text('pos_settings[razor_pay_key_id]', $pos_settings['razor_pay_key_id'] ?? '', ['class' => 'form-control', 'id' => 'razor_pay_key_id']); ?>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('razor_pay_key_secret', 'Key Secret:'); ?>

                <?php echo Form::text('pos_settings[razor_pay_key_secret]', $pos_settings['razor_pay_key_secret'] ?? '', ['class' => 'form-control', 'id' => 'razor_pay_key_secret']); ?>

            </div>
        </div>

        <div class="col-md-12">
            <h4>Stripe:</h4>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('stripe_public_key', __('lang_v1.stripe_public_key') . ':'); ?>

                <?php echo Form::text('pos_settings[stripe_public_key]', $pos_settings['stripe_public_key'] ?? '', ['class' => 'form-control', 'id' => 'stripe_public_key']); ?>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('stripe_secret_key', __('lang_v1.stripe_secret_key') . ':'); ?>

                <?php echo Form::text('pos_settings[stripe_secret_key]', $pos_settings['stripe_secret_key'] ?? '', ['class' => 'form-control', 'id' => 'stripe_secret_key']); ?>

            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/business/partials/settings_sales.blade.php ENDPATH**/ ?>