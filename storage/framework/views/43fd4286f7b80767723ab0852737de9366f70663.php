<div class="pos-tab-content">
    <div class="row">
        <?php if(!empty($allow_superadmin_email_settings)): ?>
        <div class="col-xs-12">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    <?php echo Form::checkbox('email_settings[use_superadmin_settings]', 1, !empty($email_settings['use_superadmin_settings']) , 
                    [ 'class' => 'input-icheck', 'id' => 'use_superadmin_settings']); ?> <?php echo e(__( 'lang_v1.use_superadmin_email_settings' ), false); ?>

                  </label>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div id="toggle_visibility" <?php if(!empty($email_settings['use_superadmin_settings'])): ?> class="hide" <?php endif; ?>>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('mail_driver', __('lang_v1.mail_driver') . ':'); ?>

                <?php echo Form::select('email_settings[mail_driver]', $mail_drivers, !empty($email_settings['mail_driver']) ? $email_settings['mail_driver'] : 'smtp', ['class' => 'form-control', 'id' => 'mail_driver']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
            	<?php echo Form::label('mail_host', __('lang_v1.mail_host') . ':'); ?>

            	<?php echo Form::text('email_settings[mail_host]', $email_settings['mail_host'], ['class' => 'form-control','placeholder' => __('lang_v1.mail_host'), 'id' => 'mail_host']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
            	<?php echo Form::label('mail_port' , __('lang_v1.mail_port') . ':'); ?>

            	<?php echo Form::text('email_settings[mail_port]', $email_settings['mail_port'], ['class' => 'form-control','placeholder' => __('lang_v1.mail_port'), 'id' => 'mail_port']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('mail_username', __('lang_v1.mail_username') . ':'); ?>

                <?php echo Form::text('email_settings[mail_username]', $email_settings['mail_username'], ['class' => 'form-control','placeholder' => __('lang_v1.mail_username'), 'id' => 'mail_username']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('mail_password', __('lang_v1.mail_password') . ':'); ?>

                <input type="password" name="email_settings[mail_password]" value="<?php echo e($email_settings['mail_password'], false); ?>" class="form-control" placeholder="<?php echo e(__('lang_v1.mail_password'), false); ?>", id="mail_password">
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('mail_encryption', __('lang_v1.mail_encryption') . ':'); ?>

                <?php echo Form::text('email_settings[mail_encryption]', $email_settings['mail_encryption'], ['class' => 'form-control','placeholder' => __('lang_v1.mail_encryption_place'), 'id' => 'mail_encryption']); ?>

            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('mail_from_address', __('lang_v1.mail_from_address') . ':'); ?>

                <?php echo Form::email('email_settings[mail_from_address]', $email_settings['mail_from_address'], ['class' => 'form-control','placeholder' => __('lang_v1.mail_from_address'), 'id' => 'mail_from_address' ]); ?>

            </div>
        </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <?php echo Form::label('mail_from_name', __('lang_v1.mail_from_name') . ':'); ?>

                <?php echo Form::text('email_settings[mail_from_name]', $email_settings['mail_from_name'], ['class' => 'form-control','placeholder' => __('lang_v1.mail_from_name'), 'id' => 'mail_from_name']); ?>

            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-xs-12 test_email_btn <?php if(!empty($email_settings['use_superadmin_settings'])): ?> hide <?php endif; ?>">
            <button type="button" class="tw-dw-btn tw-dw-btn-success tw-text-white  pull-right" id="test_email_btn"><?php echo app('translator')->get('lang_v1.test_email_configuration'); ?></button>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/business/partials/settings_email.blade.php ENDPATH**/ ?>