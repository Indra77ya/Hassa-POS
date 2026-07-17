
<?php
    $heading = !empty($module_category_data['heading']) ? $module_category_data['heading'] : __('category.categories');
    $navbar = !empty($module_category_data['navbar']) ? $module_category_data['navbar'] : null;
?>
<?php $__env->startSection('title', $heading); ?>

<?php $__env->startSection('content'); ?>
    <?php if(!empty($navbar)): ?>
        <?php echo $__env->make($navbar, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black" ><?php echo e($heading, false); ?>

            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold" >
                <?php echo e($module_category_data['sub_heading'] ?? __('category.manage_your_categories'), false); ?>

            </small>
            <?php if(isset($module_category_data['heading_tooltip'])): ?>
                <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . $module_category_data['heading_tooltip'] . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
            <?php endif; ?>
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        <?php
            $cat_code_enabled =
                isset($module_category_data['enable_taxonomy_code']) && !$module_category_data['enable_taxonomy_code']
                    ? false
                    : true;
        ?>
        <input type="hidden" id="category_type" value="<?php echo e(request()->get('type'), false); ?>">
        <?php
            $can_add = true;
            if (request()->get('type') == 'product' && !auth()->user()->can('category.create')) {
                $can_add = false;
            }
        ?>
        <?php $__env->startComponent('components.widget', ['class' => 'box-solid', 'can_add' => $can_add]); ?>
            <?php if($can_add): ?>
                <?php $__env->slot('tool'); ?>
                    <div class="box-tools">
                        
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full btn-modal"
                            data-href="<?php echo e(action([\App\Http\Controllers\TaxonomyController::class, 'create']), false); ?>?type=<?php echo e(request()->get('type'), false); ?>" 
                            data-container=".category_modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> <?php echo app('translator')->get('messages.add'); ?>
                        </a>
                    </div>
                <?php $__env->endSlot(); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="category_table">
                    <thead>
                        <tr>
                            <th>
                                <?php if(!empty($module_category_data['taxonomy_label'])): ?>
                                    <?php echo e($module_category_data['taxonomy_label'], false); ?>

                                <?php else: ?>
                                    <?php echo app('translator')->get('category.category'); ?>
                                <?php endif; ?>
                            </th>
                            <?php if($cat_code_enabled): ?>
                                <th><?php echo e($module_category_data['taxonomy_code_label'] ?? __('category.code'), false); ?></th>
                            <?php endif; ?>
                            <th><?php echo app('translator')->get('lang_v1.description'); ?></th>
                            <th class="not-export"><?php echo app('translator')->get('messages.action'); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        <?php echo $__env->renderComponent(); ?>

        <div class="modal fade category_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <?php if ($__env->exists('taxonomy.taxonomies_js')) echo $__env->make('taxonomy.taxonomies_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/taxonomy/index.blade.php ENDPATH**/ ?>