<?php $__env->startSection('content'); ?>
<div id="root"></div>

<!-- PRODUCTION ASSETS - HARD CODED, NO ENV CHECKS -->
<?php
    $manifestPath = public_path('portalwalisantri/dist/vite-manifest.json');
    $manifestExists = file_exists($manifestPath);
    $manifest = $manifestExists ? json_decode(file_get_contents($manifestPath), true) : [];
    $entryKey = 'node_modules/@tanstack/react-start/dist/plugin/default-entry/client.tsx';
    $entry = $manifest[$entryKey] ?? null;
?>

<!-- FORCE-DEBUG v4: <?php echo e(date('Y-m-d H:i:s')); ?> | manifest=<?php echo e($manifestExists ? 'YES' : 'NO'); ?> | path=<?php echo e($manifestPath); ?> | entry=<?php echo e($entry ? 'FOUND' : 'MISSING'); ?> -->

<?php if($entry): ?>
    <?php $__currentLoopData = $entry['css'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $css): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <link rel="stylesheet" href="/portalwalisantri/dist/<?php echo e($css); ?>">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $entry['assets'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(str_ends_with($asset, '.css')): ?>
            <link rel="stylesheet" href="/portalwalisantri/dist/<?php echo e($asset); ?>">
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <script type="module" src="/portalwalisantri/dist/<?php echo e($entry['file']); ?>"></script>
<?php else: ?>
    <!-- FALLBACK: Direct asset loading without manifest -->
    <link rel="stylesheet" href="/portalwalisantri/dist/assets/styles-CUKemUB5.css">
    <script type="module" src="/portalwalisantri/dist/assets/index-CzI2t6V8.js?v=<?php echo e(time()); ?>"></script>
<?php endif; ?>

<script>
    window.addEventListener('online', () => document.body.classList.remove('offline'));
    window.addEventListener('offline', () => document.body.classList.add('offline'));
</script>
<style>
    body.offline::before {
        content: "Anda sedang offline.";
        position: fixed; top: 0; left: 0; right: 0;
        background: #DC2626; color: white; text-align: center;
        padding: 8px; z-index: 9999; font-size: 12px; font-weight: bold;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.wali-pwa', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/users/dashboard/pwa-app-fix.blade.php ENDPATH**/ ?>