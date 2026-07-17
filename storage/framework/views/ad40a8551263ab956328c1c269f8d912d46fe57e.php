<?php if(!empty($module_form_parts)): ?>
  <?php $__currentLoopData = $module_form_parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(!empty($value['template_path'])): ?>
      <?php
        $template_data = $value['template_data'] ?: [];
      ?>
      <?php echo $__env->make($value['template_path'], $template_data, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/layouts/partials/module_form_part.blade.php ENDPATH**/ ?>