<div class="row">
    <div class="col-sm-12">
        <?php echo Form::open(['url' => action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'importAttendance']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]); ?>

            <div class="row">
                <div class="col-sm-6">
                <div class="col-sm-8">
                    <div class="form-group">
                        <?php echo Form::label('name', __( 'product.file_to_import' ) . ':'); ?>

                        <?php echo Form::file('attendance', ['accept'=> '.xls', 'required' => 'required']); ?>

                      </div>
                </div>
                <div class="col-sm-4">
                <br>
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm"><?php echo app('translator')->get('messages.submit'); ?></button>
                </div>
                </div>
            </div>

        <?php echo Form::close(); ?>

        <br><br>
        <div class="row">
            <div class="col-sm-4">
                <a href="<?php echo e(asset('modules/essentials/files/import_attendance_template.xls'), false); ?>" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm" download><i class="fa fa-download"></i> <?php echo app('translator')->get('lang_v1.download_template_file'); ?></a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table" width="100%">
                    <tr>
                        <th><?php echo app('translator')->get('lang_v1.col_no'); ?></th>
                        <th><?php echo app('translator')->get('lang_v1.col_name'); ?></th>
                        <th><?php echo app('translator')->get('lang_v1.instruction'); ?></th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td><?php echo app('translator')->get('business.email'); ?> <small class="text-muted">(<?php echo app('translator')->get('lang_v1.required'); ?>)</small></td>
                        <td><?php echo __('essentials::lang.email_ins'); ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><?php echo app('translator')->get('essentials::lang.clock_in_time'); ?> <small class="text-muted">(<?php echo app('translator')->get('lang_v1.required'); ?>)</small></td>
                        <td><?php echo __('essentials::lang.clock_in_time_ins'); ?> (<?php echo e(\Carbon::now()->toDateTimeString(), false); ?>)</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><?php echo app('translator')->get('essentials::lang.clock_out_time'); ?> <small class="text-muted">(<?php echo app('translator')->get('lang_v1.optional'); ?>)</small></td>
                        <td><?php echo __('essentials::lang.clock_out_time_ins'); ?> (<?php echo e(\Carbon::now()->toDateTimeString(), false); ?>)</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><?php echo app('translator')->get('essentials::lang.clock_in_note'); ?> <small class="text-muted">(<?php echo app('translator')->get('lang_v1.optional'); ?>)</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><?php echo app('translator')->get('essentials::lang.clock_out_note'); ?> <small class="text-muted">(<?php echo app('translator')->get('lang_v1.optional'); ?>)</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td><?php echo app('translator')->get('essentials::lang.ip_address'); ?> <small class="text-muted">(<?php echo app('translator')->get('lang_v1.optional'); ?>)</small></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Essentials\Providers/../Resources/views/attendance/import_attendance.blade.php ENDPATH**/ ?>