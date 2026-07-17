<?php $__env->startSection('title', __('lang_v1.my_profile')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('lang_v1.my_profile'); ?></h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
<?php echo Form::open(['url' => action([\App\Http\Controllers\UserController::class, 'updatePassword']), 'method' => 'post', 'id' => 'edit_password_form',
            'class' => 'form-horizontal' ]); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="box box-solid"> <!--business info box start-->
            <div class="box-header">
                <div class="box-header">
                    <h3 class="box-title"> <?php echo app('translator')->get('user.change_password'); ?></h3>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <?php echo Form::label('current_password', __('user.current_password') . ':', ['class' => 'col-sm-3 control-label']); ?>

                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                            <?php echo Form::password('current_password', ['class' => 'form-control','placeholder' => __('user.current_password'), 'required']); ?>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo Form::label('new_password', __('user.new_password') . ':', ['class' => 'col-sm-3 control-label']); ?>

                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                            <?php echo Form::password('new_password', ['class' => 'form-control','placeholder' => __('user.new_password'), 'required']); ?>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo Form::label('confirm_password', __('user.confirm_new_password') . ':', ['class' => 'col-sm-3 control-label']); ?>

                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                            <?php echo Form::password('confirm_password', ['class' => 'form-control','placeholder' =>  __('user.confirm_new_password'), 'required']); ?>

                        </div>
                    </div>
                </div>
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right"><?php echo app('translator')->get('messages.update'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php echo Form::close(); ?>

<?php echo Form::open(['url' => action([\App\Http\Controllers\UserController::class, 'updateProfile']), 'method' => 'post', 'id' => 'edit_user_profile_form', 'files' => true ]); ?>

<div class="row">
    <div class="col-sm-8">
        <div class="box box-solid"> <!--business info box start-->
            <div class="box-header">
                <div class="box-header">
                    <h3 class="box-title"> <?php echo app('translator')->get('user.edit_profile'); ?></h3>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group col-md-2">
                    <?php echo Form::label('surname', __('business.prefix') . ':'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        <?php echo Form::text('surname', $user->surname, ['class' => 'form-control','placeholder' => __('business.prefix_placeholder')]); ?>

                    </div>
                </div>
                <div class="form-group col-md-5">
                    <?php echo Form::label('first_name', __('business.first_name') . ':'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        <?php echo Form::text('first_name', $user->first_name, ['class' => 'form-control','placeholder' => __('business.first_name'), 'required']); ?>

                    </div>
                </div>
                <div class="form-group col-md-5">
                    <?php echo Form::label('last_name', __('business.last_name') . ':'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        <?php echo Form::text('last_name', $user->last_name, ['class' => 'form-control','placeholder' => __('business.last_name')]); ?>

                    </div>
                </div>
                <div class="form-group col-md-6">
                    <?php echo Form::label('email', __('business.email') . ':'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        <?php echo Form::email('email',  $user->email, ['class' => 'form-control','placeholder' => __('business.email') ]); ?>

                    </div>
                </div>
                <div class="form-group col-md-6">
                    <?php echo Form::label('language', __('business.language') . ':'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        <?php echo Form::select('language',$languages, $user->language, ['class' => 'form-control select2']); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php $__env->startComponent('components.widget', ['title' => __('lang_v1.profile_photo')]); ?>
            <?php if(!empty($user->media)): ?>
                <div class="col-md-12 text-center">
                    <?php echo $user->media->thumbnail([150, 150], 'img-circle'); ?>

                </div>
            <?php endif; ?>
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo Form::label('profile_photo', __('lang_v1.upload_image') . ':'); ?>

                    <?php echo Form::file('profile_photo', ['id' => 'profile_photo', 'accept' => 'image/*']); ?>

                    <small><p class="help-block"><?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?></p></small>
                </div>
            </div>
        <?php echo $__env->renderComponent(); ?>
    </div>
</div>
<?php echo $__env->make('user.edit_profile_form_part', ['bank_details' => !empty($user->bank_details) ? json_decode($user->bank_details, true) : null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="row">
    <div class="col-md-12 text-center">
        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-lg"><?php echo app('translator')->get('messages.update'); ?></button>
    </div>
</div>
<?php echo Form::close(); ?>


</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/user/profile.blade.php ENDPATH**/ ?>