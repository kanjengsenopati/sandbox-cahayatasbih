<?php if(isset($name) && Auth::user()->can('Create ' . $name)): ?>
<div class="">
    <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission" href="<?php echo e($action); ?>"><i class="fas fa-plus"></i>
        <?php echo e($label ?? $name); ?></a>
    <!--end::Primary button-->
</div>
<?php endif; ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/components/action/create.blade.php ENDPATH**/ ?>