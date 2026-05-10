<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Wali Santri - Cahaya Tasbih</title>
    
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="{{ url('manifest-wali.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Wali Santri">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            600: '#10B981',
                        },
                        slate: {
                            50: '#F8FAFC',
                            100: '#F1F5F9',
                            400: '#94A3B8',
                            500: '#64748B',
                            600: '#475569',
                            800: '#1E293B',
                            900: '#0F172A',
                        },
                        blue: {
                            50: '#eff6ff',
                            600: '#2563EB',
                            700: '#1d4ed8',
                        }
                    },
                    borderRadius: {
                        '3xl': '24px',
                        '4xl': '32px',
                    },
                    boxShadow: {
                        'premium': '0 8px 30px rgba(0,0,0,0.04)',
                        'card': '0 10px 40px rgba(0,0,0,0.06)',
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer components {
            .text-h1 { @apply text-[22px] font-extrabold text-slate-900 leading-tight; }
            .text-h2 { @apply text-[16px] font-bold text-slate-800; }
            .text-amount { @apply text-[18px] font-bold text-emerald-600; }
            .text-label { @apply text-[11px] font-extrabold uppercase tracking-[0.15em] text-slate-400; }
            .text-body { @apply text-[14px] font-medium text-slate-600; }
            .text-caption { @apply text-[12px] font-normal text-slate-500; }
            
            .card-premium { @apply bg-white rounded-3xl shadow-premium p-5 border border-slate-50; }
            .glass-nav { @apply backdrop-blur-xl bg-white/90 border-t border-slate-100; }
            
            .btn-primary { @apply bg-blue-600 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-blue-200 active:scale-95 transition-all disabled:bg-slate-300; }
            .input-premium { @apply w-full bg-slate-50 border-0 rounded-2xl py-4 px-5 text-slate-900 font-semibold placeholder:text-slate-400 focus:ring-2 focus:ring-blue-600/20 transition-all; }
        }
        
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        h1, h2, .text-h1, .text-h2 { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .hero-gradient {
            background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%);
        }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

    <main class="max-w-md mx-auto min-h-screen relative">
        @yield('content')
    </main>

    <!-- PWA Install Prompt (Premium Bottom Sheet style) -->
    <div id="pwa-install-prompt" class="fixed inset-0 z-[100] flex items-end justify-center p-5 transform translate-y-full transition-transform duration-700 ease-in-out hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="dismissInstallPrompt()"></div>
        <div class="bg-white w-full max-w-sm rounded-[40px] p-8 relative z-10 shadow-2xl">
            <div class="w-16 h-1.5 bg-slate-100 rounded-full mx-auto mb-8"></div>
            <div class="text-center mb-8">
                <div class="w-24 h-24 bg-blue-600 rounded-[32px] mx-auto flex items-center justify-center text-white text-5xl shadow-xl shadow-blue-100 mb-6 relative overflow-hidden">
                    <i class="fas fa-mosque relative z-10"></i>
                    <div class="absolute top-0 right-0 w-full h-full bg-white/10 skew-x-12 translate-x-1/2"></div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">Pasang Aplikasi</h3>
                <p class="text-slate-500 text-sm leading-relaxed px-4">Akses Portal Wali Santri lebih cepat, aman, dan praktis langsung dari layar utama HP Anda.</p>
            </div>
            
            <!-- Android / Desktop Install -->
            <div id="android-install-area" class="hidden space-y-3">
                <button id="btn-install-pwa" class="w-full btn-primary">Install Sekarang</button>
                <button onclick="dismissInstallPrompt()" class="w-full py-4 text-slate-400 font-bold text-xs uppercase tracking-[0.2em]">Nanti Saja</button>
            </div>

            <!-- iOS Manual Install Instructions -->
            <div id="ios-install-area" class="hidden">
                <div class="bg-slate-50 rounded-3xl p-5 border border-slate-100 mb-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-slate-400 shadow-sm"><i class="fas fa-share-square"></i></div>
                        <p class="text-xs text-slate-600 font-medium">1. Klik tombol <span class="font-bold text-slate-900">Share</span> di bagian bawah browser Safari.</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-slate-400 shadow-sm"><i class="fas fa-plus-square"></i></div>
                        <p class="text-xs text-slate-600 font-medium">2. Pilih menu <span class="font-bold text-slate-900">Add to Home Screen</span> (Tambah ke Layar Utama).</p>
                    </div>
                </div>
                <button onclick="dismissInstallPrompt()" class="w-full btn-primary">Saya Mengerti</button>
            </div>
        </div>
    </div>

    @if(Auth::guard('wali')->check() && !request()->routeIs('wali.login'))
    <!-- Bottom Navigation Bar (Fintech style) -->
    <nav class="fixed bottom-0 left-0 right-0 h-20 glass-nav z-50 flex items-center justify-around px-4">
        <a href="{{ route('wali.app') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('wali.app') ? 'text-blue-600' : 'text-slate-400' }}">
            <div class="relative">
                <i class="fas fa-house-chimney text-xl"></i>
                @if(request()->routeIs('wali.app'))
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                @endif
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest">Home</span>
        </a>
        <a href="{{ route('wali.bills') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('wali.bills') ? 'text-blue-600' : 'text-slate-400' }}">
            <div class="relative">
                <i class="fas fa-receipt text-xl"></i>
                @if(request()->routeIs('wali.bills'))
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                @endif
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest">Tagihan</span>
        </a>
        <a href="{{ route('wali.profile') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('wali.profile') ? 'text-blue-600' : 'text-slate-400' }}">
            <div class="relative">
                <i class="fas fa-user-gear text-xl"></i>
                @if(request()->routeIs('wali.profile'))
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                @endif
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest">Profile</span>
        </a>
    </nav>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // PWA Installation & Forced Display Logic
        let deferredPrompt;
        const promptContainer = document.getElementById('pwa-install-prompt');
        const installBtn = document.getElementById('btn-install-pwa');
        const androidArea = document.getElementById('android-install-area');
        const iosArea = document.getElementById('ios-install-area');

        // Check if already in standalone mode
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

        function showInstallPrompt() {
            if (isStandalone) return; // Don't show if already installed
            
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            
            promptContainer.classList.remove('hidden');
            setTimeout(() => {
                promptContainer.classList.remove('translate-y-full');
            }, 100);

            if (isIOS) {
                iosArea.classList.remove('hidden');
            } else {
                androidArea.classList.remove('hidden');
            }
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallPrompt();
        });

        // Forced show on /wali/app if not standalone and not firing event
        @if(request()->routeIs('wali.app'))
        window.addEventListener('load', () => {
            setTimeout(() => {
                if (!isStandalone && !deferredPrompt) {
                    showInstallPrompt();
                }
            }, 3000);
        });
        @endif

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') dismissInstallPrompt();
                deferredPrompt = null;
            }
        });

        function dismissInstallPrompt() {
            promptContainer.classList.add('translate-y-full');
            setTimeout(() => promptContainer.classList.add('hidden'), 700);
        }

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw-wali.js');
            });
        }
    </script>

    @if(session('success'))
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#2563eb', customClass: { popup: 'rounded-3xl' } });
    </script>
    @endif
</body>
</html>
