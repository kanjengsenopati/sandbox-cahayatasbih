<?php if(isset($name) && Auth::user()->can('Edit ' . $name)): ?>
<a href="<?php echo e($action); ?>" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
    <i class="fas fa-edit"></i>
</a>
<?php endif; ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/components/action/edit.blade.php ENDPATH**/ ?>