<?php $__env->startSection('title', __('business.business_settings')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('business.business_settings'); ?></h1>
    <br>
    <?php echo $__env->make('layouts.partials.search_settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</section>

<!-- Main content -->
<section class="content">
<?php echo Form::open(['url' => action([\App\Http\Controllers\BusinessController::class, 'postBusinessSettings']), 'method' => 'post', 'id' => 'bussiness_edit_form',
           'files' => true ]); ?>

    <div class="row">
        <div class="col-xs-12">
       <!--  <pos-tab-container> -->
        
        <?php $__env->startComponent('components.widget', ['class' =>  'pos-tab-container']); ?>
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu tw-rounded-lg">
                <div class="list-group">
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base  active"><?php echo app('translator')->get('business.business'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('business.tax'); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.business_tax') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('business.product'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('contact.contact'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('business.sale'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('sale.pos_sale'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.display_screen'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('purchase.purchases'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.payment'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('business.dashboard'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('business.system'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.prefixes'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.email_settings'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.sms_settings'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.reward_point_settings'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.modules'); ?></a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base"><?php echo app('translator')->get('lang_v1.custom_labels'); ?></a>
                </div>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                <!-- tab 1 start -->
                <?php echo $__env->make('business.partials.settings_business', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 1 end -->
                <!-- tab 2 start -->
                <?php echo $__env->make('business.partials.settings_tax', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 2 end -->
                <!-- tab 3 start -->
                <?php echo $__env->make('business.partials.settings_product', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <?php echo $__env->make('business.partials.settings_contact', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 3 end -->
                <!-- tab 4 start -->
                <?php echo $__env->make('business.partials.settings_sales', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php echo $__env->make('business.partials.settings_pos', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php echo $__env->make('business.partials.settings_display_pos', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 4 end -->
                <!-- tab 5 start -->
                <?php echo $__env->make('business.partials.settings_purchase', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <?php echo $__env->make('business.partials.settings_payment', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 5 end -->
                <!-- tab 6 start -->
                <?php echo $__env->make('business.partials.settings_dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 6 end -->
                <!-- tab 7 start -->
                <?php echo $__env->make('business.partials.settings_system', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 7 end -->
                <!-- tab 8 start -->
                <?php echo $__env->make('business.partials.settings_prefixes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 8 end -->
                <!-- tab 9 start -->
                <?php echo $__env->make('business.partials.settings_email', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 9 end -->
                <!-- tab 10 start -->
                <?php echo $__env->make('business.partials.settings_sms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 10 end -->
                <!-- tab 11 start -->
                <?php echo $__env->make('business.partials.settings_reward_point', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 11 end -->
                <!-- tab 12 start -->
                <?php echo $__env->make('business.partials.settings_modules', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <!-- tab 12 end -->
                <?php echo $__env->make('business.partials.settings_custom_labels', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        <?php echo $__env->renderComponent(); ?>
        
        <!--  </pos-tab-container> -->
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 text-center">
            <button class="tw-dw-btn tw-dw-btn-error tw-dw-btn-lg tw-text-white" type="submit"><?php echo app('translator')->get('business.update_settings'); ?></button>
        </div>
    </div>
<?php echo Form::close(); ?>

</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
<script type="text/javascript">
    __page_leave_confirmation('#bussiness_edit_form');
    $(document).on('ifToggled', '#use_superadmin_settings', function() {
        if ($('#use_superadmin_settings').is(':checked')) {
            $('#toggle_visibility').addClass('hide');
            $('.test_email_btn').addClass('hide');
        } else {
            $('#toggle_visibility').removeClass('hide');
            $('.test_email_btn').removeClass('hide');
        }
    });

    $(document).ready(function(){

    
        $('#test_email_btn').click( function() {
            var data = {
                mail_driver: $('#mail_driver').val(),
                mail_host: $('#mail_host').val(),
                mail_port: $('#mail_port').val(),
                mail_username: $('#mail_username').val(),
                mail_password: $('#mail_password').val(),
                mail_encryption: $('#mail_encryption').val(),
                mail_from_address: $('#mail_from_address').val(),
                mail_from_name: $('#mail_from_name').val(),
            };
            $.ajax({
                method: 'post',
                data: data,
                url: "<?php echo e(action([\App\Http\Controllers\BusinessController::class, 'testEmailConfiguration']), false); ?>",
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        swal({
                            text: result.msg,
                            icon: 'success'
                        });
                    } else {
                        swal({
                            text: result.msg,
                            icon: 'error'
                        });
                    }
                },
            });
        });

        $('#test_sms_btn').click( function() {
            var test_number = $('#test_number').val();
            if (test_number.trim() == '') {
                toastr.error('<?php echo e(__("lang_v1.test_number_is_required"), false); ?>');
                $('#test_number').focus();

                return false;
            }

            var data = {
                url: $('#sms_settings_url').val(),
                send_to_param_name: $('#send_to_param_name').val(),
                msg_param_name: $('#msg_param_name').val(),
                request_method: $('#request_method').val(),
                param_1: $('#sms_settings_param_key1').val(),
                param_2: $('#sms_settings_param_key2').val(),
                param_3: $('#sms_settings_param_key3').val(),
                param_4: $('#sms_settings_param_key4').val(),
                param_5: $('#sms_settings_param_key5').val(),
                param_6: $('#sms_settings_param_key6').val(),
                param_7: $('#sms_settings_param_key7').val(),
                param_8: $('#sms_settings_param_key8').val(),
                param_9: $('#sms_settings_param_key9').val(),
                param_10: $('#sms_settings_param_key10').val(),

                param_val_1: $('#sms_settings_param_val1').val(),
                param_val_2: $('#sms_settings_param_val2').val(),
                param_val_3: $('#sms_settings_param_val3').val(),
                param_val_4: $('#sms_settings_param_val4').val(),
                param_val_5: $('#sms_settings_param_val5').val(),
                param_val_6: $('#sms_settings_param_val6').val(),
                param_val_7: $('#sms_settings_param_val7').val(),
                param_val_8: $('#sms_settings_param_val8').val(),
                param_val_9: $('#sms_settings_param_val9').val(),
                param_val_10: $('#sms_settings_param_val10').val(),
                test_number: test_number,

                header_1: $('#sms_settings_header_key1').val(),
                header_val_1: $('#sms_settings_header_val1').val(),
                header_2: $('#sms_settings_header_key2').val(),
                header_val_2: $('#sms_settings_header_val2').val(),
                header_3: $('#sms_settings_header_key3').val(),
                header_val_3: $('#sms_settings_header_val3').val(),
                data_parameter_type: $('#data_parameter_type').val(),
            };

            $.ajax({
                method: 'post',
                data: data,
                url: "<?php echo e(action([\App\Http\Controllers\BusinessController::class, 'testSmsConfiguration']), false); ?>",
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        swal({
                            text: result.msg,
                            icon: 'success'
                        });
                    } else {
                        swal({
                            text: result.msg,
                            icon: 'error'
                        });
                    }
                },
            });

        });

        $('select.custom_labels_products').change(function(){
            value = $(this).val();
            textarea = $(this).parents('div.custom_label_product_div').find('div.custom_label_product_dropdown');
            if(value == 'dropdown'){
                textarea.removeClass('hide');
            } else{
                textarea.addClass('hide');
            }
        })

        tinymce.init({
            selector: 'textarea#display_screen_heading',
            height: 250
        });

        $('.carousel_image').fileinput({
            showUpload: true,
            showPreview: true,
            browseLabel: LANG.file_browse_label,
            removeLabel: LANG.remove,
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/business/settings.blade.php ENDPATH**/ ?>