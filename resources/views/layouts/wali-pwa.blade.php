<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Wali Santri - Cahaya Tasbih</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest-wali.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            600: '#10B981',
                        },
                        slate: {
                            400: '#94A3B8',
                            600: '#475569',
                            800: '#1E293B',
                            900: '#0F172A',
                        },
                        blue: {
                            600: '#2563EB',
                        }
                    },
                    borderRadius: {
                        '3xl': '24px',
                    },
                    boxShadow: {
                        'premium': '0 8px 30px rgba(0,0,0,0.04)',
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer components {
            .text-h1 { @apply text-[22px] font-bold text-slate-900 leading-tight; }
            .text-h2 { @apply text-[16px] font-semibold text-slate-800; }
            .text-amount { @apply text-[18px] font-bold text-emerald-600; }
            .text-label { @apply text-[11px] font-bold uppercase tracking-widest text-slate-400; }
            .text-body { @apply text-[14px] font-medium text-slate-600; }
            .text-caption { @apply text-[12px] font-normal italic text-slate-400; }
            
            .card-premium { @apply bg-white rounded-3xl shadow-premium p-5; }
            .glass-nav { @apply backdrop-blur-md bg-white/80 border-t border-slate-100; }
        }
        
        body { font-family: 'Inter', sans-serif; }
        h1, h2, .text-h1 { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] pb-24">

    @yield('content')

    <!-- Install Prompt Popup (Hidden by default) -->
    <div id="pwa-install-prompt" class="fixed bottom-24 left-5 right-5 z-[100] transform translate-y-full transition-transform duration-500 ease-in-out hidden">
        <div class="bg-white rounded-3xl shadow-2xl p-6 border border-slate-100">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl">
                    <i class="fas fa-mosque"></i>
                </div>
                <div>
                    <div class="text-h2">Install App Wali Santri</div>
                    <div class="text-caption">Akses lebih cepat & praktis</div>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="dismissInstallPrompt()" class="flex-1 py-3 px-4 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm">Nanti saja</button>
                <button id="btn-install-pwa" class="flex-1 py-3 px-4 rounded-2xl bg-blue-600 text-white font-bold text-sm shadow-lg shadow-blue-200">Install Sekarang</button>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 h-20 glass-nav z-50 flex items-center justify-around px-2">
        <a href="{{ route('wali.app') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('wali.app') ? 'text-blue-600' : 'text-slate-400' }}">
            <div class="w-6 h-6 flex items-center justify-center text-xl">
                <i class="fas fa-home-alt"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider">Beranda</span>
        </a>
        <a href="{{ route('wali.ppdb.index') }}" class="flex flex-col items-center gap-1 text-slate-400">
            <div class="w-6 h-6 flex items-center justify-center text-xl">
                <i class="fas fa-user-plus"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider">PPDB</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-1 text-slate-400">
            <div class="w-6 h-6 flex items-center justify-center text-xl">
                <i class="fas fa-history"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider">Riwayat</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-1 text-slate-400">
            <div class="w-6 h-6 flex items-center justify-center text-xl">
                <i class="fas fa-user-circle"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider">Profil</span>
        </a>
    </nav>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw-wali.js')
                    .then(reg => console.log('SW Registered'))
                    .catch(err => console.log('SW Failed', err));
            });
        }

        // PWA Install Prompt Logic
        let deferredPrompt;
        const promptEl = document.getElementById('pwa-install-prompt');
        const installBtn = document.getElementById('btn-install-pwa');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show prompt after a short delay
            setTimeout(() => {
                promptEl.classList.remove('hidden');
                setTimeout(() => {
                    promptEl.classList.remove('translate-y-full');
                }, 100);
            }, 3000);
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    dismissInstallPrompt();
                }
                deferredPrompt = null;
            }
        });

        function dismissInstallPrompt() {
            promptEl.classList.add('translate-y-full');
            setTimeout(() => {
                promptEl.classList.add('hidden');
            }, 500);
        }
    </script>
</body>
</html>
