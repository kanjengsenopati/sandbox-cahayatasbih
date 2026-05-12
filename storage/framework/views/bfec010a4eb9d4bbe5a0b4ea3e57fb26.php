<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Wali Santri - Cahaya Tasbih</title>
    
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="<?php echo e(url('manifest-wali.json')); ?>">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Wali Santri">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; background: #F8FAFC; color: #0F172A; overflow-x: hidden; }
        h1, h2 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body>
    <main class="max-w-md mx-auto min-h-screen relative">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if(session('success')): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "<?php echo e(session('success')); ?>", confirmButtonColor: '#2563eb' });
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/layouts/wali-pwa.blade.php ENDPATH**/ ?>