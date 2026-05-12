<?php if(isset($name) && Auth::user()->can('Delete ' . $name)): ?>
<div>
    <a data-id="form<?php echo e($id); ?>" type="button" id="btnDelete<?php echo e($id); ?>"
        class="btn-delete btn btn-icon btn-active-light-primary w-30px h-30px me-3">
        <i class="fas fa-trash-alt" data-id="form<?php echo e($id); ?>"></i>
    </a>
    <form id="form<?php echo e($id); ?>" action="<?php echo e($action); ?>" method="post">
        <?php echo csrf_field(); ?>
        <?php echo method_field('delete'); ?>
    </form>
</div>
<?php endif; ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/components/action/delete.blade.php ENDPATH**/ ?>