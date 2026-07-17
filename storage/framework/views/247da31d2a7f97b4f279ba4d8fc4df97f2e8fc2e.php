<?php
  $custom_labels = json_decode(session('business.custom_labels'), true);
  $user_custom_field1 = !empty($custom_labels['user']['custom_field_1']) ? $custom_labels['user']['custom_field_1'] : __('lang_v1.user_custom_field1');
  $user_custom_field2 = !empty($custom_labels['user']['custom_field_2']) ? $custom_labels['user']['custom_field_2'] : __('lang_v1.user_custom_field2');
  $user_custom_field3 = !empty($custom_labels['user']['custom_field_3']) ? $custom_labels['user']['custom_field_3'] : __('lang_v1.user_custom_field3');
  $user_custom_field4 = !empty($custom_labels['user']['custom_field_4']) ? $custom_labels['user']['custom_field_4'] : __('lang_v1.user_custom_field4');
?>
<div class="form-group col-md-3">
    <?php echo Form::label('user_dob', __( 'lang_v1.dob' ) . ':'); ?>

    <?php echo Form::text('dob', !empty($user->dob) ? \Carbon::createFromTimestamp(strtotime($user->dob))->format(session('business.date_format')) : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.dob'), 'readonly', 'id' => 'user_dob' ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('gender', __( 'lang_v1.gender' ) . ':'); ?>

    <?php echo Form::select('gender', ['male' => __('lang_v1.male'), 'female' => __('lang_v1.female'), 'others' => __('lang_v1.others')], !empty($user->gender) ? $user->gender : null, ['class' => 'form-control', 'id' => 'gender', 'placeholder' => __( 'messages.please_select') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('marital_status', __( 'lang_v1.marital_status' ) . ':'); ?>

    <?php echo Form::select('marital_status', ['married' => __( 'lang_v1.married'), 'unmarried' => __( 'lang_v1.unmarried' ), 'divorced' => __( 'lang_v1.divorced' )], !empty($user->marital_status) ? $user->marital_status : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.marital_status') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('blood_group', __( 'lang_v1.blood_group' ) . ':'); ?>

    <?php echo Form::text('blood_group', !empty($user->blood_group) ? $user->blood_group : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.blood_group') ]); ?>

</div>
<div class="clearfix"></div>
<div class="form-group col-md-3">
    <?php echo Form::label('contact_number', __( 'lang_v1.mobile_number' ) . ':'); ?>

    <?php echo Form::text('contact_number', !empty($user->contact_number) ? $user->contact_number : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.mobile_number') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('alt_number', __( 'business.alternate_number' ) . ':'); ?>

    <?php echo Form::text('alt_number', !empty($user->alt_number) ? $user->alt_number : null, ['class' => 'form-control', 'placeholder' => __( 'business.alternate_number') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('family_number', __( 'lang_v1.family_contact_number' ) . ':'); ?>

    <?php echo Form::text('family_number', !empty($user->family_number) ? $user->family_number : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.family_contact_number') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('fb_link', __( 'lang_v1.fb_link' ) . ':'); ?>

    <?php echo Form::text('fb_link', !empty($user->fb_link) ? $user->fb_link : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.fb_link') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('twitter_link', __( 'lang_v1.twitter_link' ) . ':'); ?>

    <?php echo Form::text('twitter_link', !empty($user->twitter_link) ? $user->twitter_link : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.twitter_link') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('social_media_1', __( 'lang_v1.social_media', ['number' => 1] ) . ':'); ?>

    <?php echo Form::text('social_media_1', !empty($user->social_media_1) ? $user->social_media_1 : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.social_media', ['number' => 1] ) ]); ?>

</div>
<div class="clearfix"></div>
<div class="form-group col-md-3">
    <?php echo Form::label('social_media_2', __( 'lang_v1.social_media', ['number' => 2] ) . ':'); ?>

    <?php echo Form::text('social_media_2', !empty($user->social_media_2) ? $user->social_media_2 : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.social_media', ['number' => 2] ) ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('custom_field_1', $user_custom_field1 . ':'); ?>

    <?php echo Form::text('custom_field_1', !empty($user->custom_field_1) ? $user->custom_field_1 : null, ['class' => 'form-control', 'placeholder' => $user_custom_field1 ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('custom_field_2', $user_custom_field2 . ':'); ?>

    <?php echo Form::text('custom_field_2', !empty($user->custom_field_2) ? $user->custom_field_2 : null, ['class' => 'form-control', 'placeholder' => $user_custom_field2 ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('custom_field_3', $user_custom_field3 . ':'); ?>

    <?php echo Form::text('custom_field_3', !empty($user->custom_field_3) ? $user->custom_field_3 : null, ['class' => 'form-control', 'placeholder' => $user_custom_field3 ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('custom_field_4', $user_custom_field4 . ':'); ?>

    <?php echo Form::text('custom_field_4', !empty($user->custom_field_4) ? $user->custom_field_4 : null, ['class' => 'form-control', 'placeholder' => $user_custom_field4 ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('guardian_name', __( 'lang_v1.guardian_name') . ':'); ?>

    <?php echo Form::text('guardian_name', !empty($user->guardian_name) ? $user->guardian_name : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.guardian_name' ) ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('id_proof_name', __( 'lang_v1.id_proof_name') . ':'); ?>

    <?php echo Form::text('id_proof_name', !empty($user->id_proof_name) ? $user->id_proof_name : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.id_proof_name' ) ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('id_proof_number', __( 'lang_v1.id_proof_number') . ':'); ?>

    <?php echo Form::text('id_proof_number', !empty($user->id_proof_number) ? $user->id_proof_number : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.id_proof_number' ) ]); ?>

</div>
<div class="clearfix"></div>
<div class="form-group col-md-6">
    <?php echo Form::label('permanent_address', __( 'lang_v1.permanent_address') . ':'); ?>

    <?php echo Form::textarea('permanent_address', !empty($user->permanent_address) ? $user->permanent_address : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.permanent_address'), 'rows' => 3 ]); ?>

</div>
<div class="form-group col-md-6">
    <?php echo Form::label('current_address', __( 'lang_v1.current_address') . ':'); ?>

    <?php echo Form::textarea('current_address', !empty($user->current_address) ? $user->current_address : null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.current_address'), 'rows' => 3 ]); ?>

</div>
<div class="col-md-12">
    <hr>
    <h4><?php echo app('translator')->get('lang_v1.bank_details'); ?>:</h4>
</div>
<div class="form-group col-md-3">
    <?php echo Form::label('account_holder_name', __( 'lang_v1.account_holder_name') . ':'); ?>

    <?php echo Form::text('bank_details[account_holder_name]', !empty($bank_details['account_holder_name']) ? $bank_details['account_holder_name'] : null , ['class' => 'form-control', 'id' => 'account_holder_name', 'placeholder' => __( 'lang_v1.account_holder_name') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('account_number', __( 'lang_v1.account_number') . ':'); ?>

    <?php echo Form::text('bank_details[account_number]', !empty($bank_details['account_number']) ? $bank_details['account_number'] : null, ['class' => 'form-control', 'id' => 'account_number', 'placeholder' => __( 'lang_v1.account_number') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('bank_name', __( 'lang_v1.bank_name') . ':'); ?>

    <?php echo Form::text('bank_details[bank_name]', !empty($bank_details['bank_name']) ? $bank_details['bank_name'] : null, ['class' => 'form-control', 'id' => 'bank_name', 'placeholder' => __( 'lang_v1.bank_name') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('bank_code', __( 'lang_v1.bank_code') . ':'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.bank_code_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
    <?php echo Form::text('bank_details[bank_code]', !empty($bank_details['bank_code']) ? $bank_details['bank_code'] : null, ['class' => 'form-control', 'id' => 'bank_code', 'placeholder' => __( 'lang_v1.bank_code') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('branch', __( 'lang_v1.branch') . ':'); ?>

    <?php echo Form::text('bank_details[branch]', !empty($bank_details['branch']) ? $bank_details['branch'] : null, ['class' => 'form-control', 'id' => 'branch', 'placeholder' => __( 'lang_v1.branch') ]); ?>

</div>
<div class="form-group col-md-3">
    <?php echo Form::label('tax_payer_id', __( 'lang_v1.tax_payer_id') . ':'); ?>

    <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.tax_payer_id_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
    <?php echo Form::text('bank_details[tax_payer_id]', !empty($bank_details['tax_payer_id']) ? $bank_details['tax_payer_id'] : null, ['class' => 'form-control', 'id' => 'tax_payer_id', 'placeholder' => __( 'lang_v1.tax_payer_id') ]); ?>

</div><?php /**PATH C:\laragon\www\Hassa POS\resources\views/user/form.blade.php ENDPATH**/ ?>