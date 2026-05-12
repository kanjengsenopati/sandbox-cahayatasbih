<?php if($errors->any()): ?>
<div class="alert alert-danger alert-dismissible show fade">
    <ul>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e(str_replace(['The','field is required'],['Form','harus diisi'],$error)); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/components/alert/alert-validation.blade.php ENDPATH**/ ?>