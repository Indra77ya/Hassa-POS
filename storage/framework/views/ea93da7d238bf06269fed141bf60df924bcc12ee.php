<!--payment related settings -->
<div class="pos-tab-content">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php echo Form::label('cash_denominations', __('lang_v1.cash_denominations') . ':'); ?>

                 <?php echo Form::text('pos_settings[cash_denominations]', isset($pos_settings['cash_denominations']) ? $pos_settings['cash_denominations'] : null, ['class' => 'form-control', 'id' => 'cash_denominations']); ?>

                 <p class="help-block"><?php echo e(__('lang_v1.cash_denominations_help'), false); ?></p>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('enable_cash_denomination_on', __('lang_v1.enable_cash_denomination_on') . ':'); ?>

                <?php echo Form::select('pos_settings[enable_cash_denomination_on]', ['pos_screen' => __('lang_v1.pos_screen'), 'all_screens' => __('lang_v1.all_screen')], isset($pos_settings['enable_cash_denomination_on']) ? $pos_settings['enable_cash_denomination_on'] : 'pos_screen', ['class' => 'form-control', 'style' => 'width: 100%;' ]); ?>

            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('enable_cash_denomination_for_payment_methods', __('lang_v1.enable_cash_denomination_for_payment_methods') . ':'); ?>

                <?php echo Form::select('pos_settings[enable_cash_denomination_for_payment_methods][]', $payment_types, isset($pos_settings['enable_cash_denomination_for_payment_methods']) ? $pos_settings['enable_cash_denomination_for_payment_methods'] : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'multiple' ]); ?>

            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    <?php echo Form::checkbox('pos_settings[cash_denomination_strict_check]', 1,  
                        !empty($pos_settings['cash_denomination_strict_check']) , 
                    [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.strict_check' ), false); ?>

                  </label>
                  <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.strict_check_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/business/partials/settings_payment.blade.php ENDPATH**/ ?>