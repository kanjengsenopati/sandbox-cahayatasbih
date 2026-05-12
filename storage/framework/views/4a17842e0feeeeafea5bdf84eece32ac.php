<?php $__env->startSection('content'); ?>
<div id="root"></div>

<!-- PRODUCTION ASSETS - HARD CODED, NO ENV CHECKS -->
<?php
    // Try multiple possible locations for manifest (VPS vs Local)
    $possiblePaths = [
        public_path('portalwalisantri/dist/vite-manifest.json'),
        base_path('public/portalwalisantri/dist/vite-manifest.json'),
    ];
    
    $manifestPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $manifestPath = $path;
            break;
        }
    }

    $manifest = $manifestPath ? json_decode(file_get_contents($manifestPath), true) : [];
    $entry = $manifest['index.html'] ?? null;
?>

<!-- DEPLOYMENT VERSION v5: <?php echo e(date('Y-m-d H:i:s')); ?> | manifest=<?php echo e($manifestPath ? 'YES' : 'NO'); ?> | entry=<?php echo e($entry ? 'FOUND' : 'MISSING'); ?> -->

<?php if($entry): ?>
    <?php $__currentLoopData = $entry['css'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $css): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <link rel="stylesheet" href="/portalwalisantri/dist/<?php echo e($css); ?>?v=<?php echo e(time()); ?>">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <script type="module" src="/portalwalisantri/dist/<?php echo e($entry['file']); ?>?v=<?php echo e(time()); ?>"></script>
<?php else: ?>
    <!-- FALLBACK: Direct asset loading if manifest logic fails -->
    <link rel="stylesheet" href="/portalwalisantri/dist/assets/styles-DwM8hKnt.css?v=<?php echo e(time()); ?>">
    <script type="module" src="/portalwalisantri/dist/assets/index-D6ECJnJ2.js?v=<?php echo e(time()); ?>"></script>
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