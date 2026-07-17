<div class="pos-tab-content">
     <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('theme_color', __('lang_v1.theme_color')); ?>

                <?php echo Form::select('theme_color', $theme_colors,   $business->theme_color, 
                    ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); ?>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php
                    $page_entries = [25 => 25, 50 => 50, 100 => 100, 200 => 200, 500 => 500, 1000 => 1000, -1 => __('lang_v1.all')];
                ?>
                <?php echo Form::label('default_datatable_page_entries', __('lang_v1.default_datatable_page_entries')); ?>

                <?php echo Form::select('common_settings[default_datatable_page_entries]', $page_entries, !empty($common_settings['default_datatable_page_entries']) ? $common_settings['default_datatable_page_entries'] : 25 , 
                    ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'default_datatable_page_entries']); ?>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                  <label>
                    <?php echo Form::checkbox('enable_tooltip', 1, $business->enable_tooltip , 
                    [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'business.show_help_text' ), false); ?>

                  </label>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/business/partials/settings_system.blade.php ENDPATH**/ ?>