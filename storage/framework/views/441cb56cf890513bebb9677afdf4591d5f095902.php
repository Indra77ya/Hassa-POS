
<?php $__env->startSection('title', __('lang_v1.backup')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('lang_v1.backup'); ?>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
  <?php if(session('notification') || !empty($notification)): ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php if(!empty($notification['msg'])): ?>
                    <?php echo e($notification['msg'], false); ?>

                <?php elseif(session('notification.msg')): ?>
                    <?php echo e(session('notification.msg'), false); ?>

                <?php endif; ?>
              </div>
          </div>  
      </div>     
  <?php endif; ?>

  <div class="row">
    <div class="col-sm-12">
      <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
        <?php $__env->slot('tool'); ?>
          <div class="box-tools">
            <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right btn-modal-coupon"
                    href="<?php echo e(url('backup/create'), false); ?>" id="create-new-backup-button" style="margin-bottom:2em;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> <?php echo app('translator')->get('messages.add'); ?>
              </a>
          </div>
        <?php $__env->endSlot(); ?>
        <?php if(count($backups)): ?>
                <table class="table table-striped table-bordered">
                  <thead>
                  <tr>
                      <th><?php echo app('translator')->get('lang_v1.file'); ?></th>
                      <th><?php echo app('translator')->get('lang_v1.size'); ?></th>
                      <th><?php echo app('translator')->get('lang_v1.date'); ?></th>
                      <th><?php echo app('translator')->get('lang_v1.age'); ?></th>
                      <th class="not-export"><?php echo app('translator')->get('messages.actions'); ?></th>
                  </tr>
                  </thead>
                    <tbody>
                    <?php $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($backup['file_name'], false); ?></td>
                            <td><?php echo e(humanFilesize($backup['file_size']), false); ?></td>
                            <td>
                                <?php echo e(Carbon::createFromTimestamp($backup['last_modified'])->toDateTimeString(), false); ?>

                            </td>
                            <td>
                                <?php echo e(Carbon::createFromTimestamp($backup['last_modified'])->diffForHumans(Carbon::now()), false); ?>

                            </td>
                            <td>
                              <a class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent"
                                   href="<?php echo e(action([\App\Http\Controllers\BackUpController::class, 'download'], [$backup['file_name']]), false); ?>"><i
                                        class="fa fa-cloud-download"></i> <?php echo app('translator')->get('lang_v1.download'); ?></a>
                                <a class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error link_confirmation" data-button-type="delete"
                                   href="<?php echo e(route('delete_backup', $backup['file_name']), false); ?>"><i class="fa fa-trash-o"></i>
                                    <?php echo app('translator')->get('messages.delete'); ?> </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
              </table>
            <?php else: ?>
                <div class="well">
                    <h4>There are no backups</h4>
                </div>
            <?php endif; ?>
            <br>
            <strong><?php echo app('translator')->get('lang_v1.auto_backup_instruction'); ?>:</strong><br>
            <code><?php echo e($cron_job_command, false); ?></code> <br>
      <?php echo $__env->renderComponent(); ?>
    </div>
  </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/backup/index.blade.php ENDPATH**/ ?>