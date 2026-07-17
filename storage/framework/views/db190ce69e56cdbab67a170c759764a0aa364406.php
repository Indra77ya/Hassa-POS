
<?php $__env->startSection('title', __('manufacturing::lang.recipe')); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('manufacturing::layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('manufacturing::lang.recipe'); ?></h1>
</section>

<!-- Main content -->
<section class="content">
    <?php $__env->startComponent('components.widget', ['class' => 'box-solid']); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("manufacturing.add_recipe")): ?>
        <?php $__env->slot('tool'); ?>
            <div class="box-tools">
            <button class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right btn-modal"
                data-container="#recipe_modal"
                data-href="<?php echo e(action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'create']), false); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> <?php echo app('translator')->get('messages.add'); ?>
            </button>
            </div>
        <?php $__env->endSlot(); ?>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="recipe_table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-row" data-table-id="recipe_table"></th>
                        <th><?php echo app('translator')->get( 'manufacturing::lang.recipe' ); ?></th>
                        <th><?php echo app('translator')->get( 'product.category' ); ?></th>
                        <th><?php echo app('translator')->get( 'product.sub_category' ); ?></th>
                        <th><?php echo app('translator')->get( 'lang_v1.quantity' ); ?></th>
                        <th><?php echo app('translator')->get( 'lang_v1.price' ); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('manufacturing::lang.price_updated_live') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?></th>
                        <th><?php echo app('translator')->get( 'sale.unit_price' ); ?></th>
                        <th><?php echo app('translator')->get( 'messages.action' ); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <button type="button" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error" id="mass_update_product_price" ><?php echo app('translator')->get('manufacturing::lang.update_product_price'); ?></button> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('manufacturing::lang.update_product_price_help') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php echo $__env->renderComponent(); ?>
</section>
<!-- /.content -->
<div class="modal fade" id="recipe_modal" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <?php echo $__env->make('manufacturing::layouts.partials.common_script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\Modules\Manufacturing\Providers/../Resources/views/recipe/index.blade.php ENDPATH**/ ?>