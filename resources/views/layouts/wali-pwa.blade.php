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
            
            .btn-primary { @apply bg-blue-600 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-blue-200 active:scale-95 transition-all; }
            .input-premium { @apply w-full bg-slate-50 border-0 rounded-2xl py-4 px-5 text-slate-900 font-semibold placeholder:text-slate-400 focus:ring-2 focus:ring-blue-600/20 transition-all; }
        }
        
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        h1, h2, .text-h1, .text-h2 { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .hero-gradient {
            background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%);
        }
        
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-900 selection:bg-blue-100 selection:text-blue-700">

    <main class="max-w-md mx-auto min-h-screen relative overflow-x-hidden">
        @yield('content')
    </main>

    <!-- PWA Install Prompt (Premium Bottom Sheet style) -->
    <div id="pwa-install-prompt" class="fixed inset-0 z-[100] flex items-end justify-center p-5 transform translate-y-full transition-transform duration-700 ease-in-out hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="dismissInstallPrompt()"></div>
        <div class="bg-white w-full max-w-sm rounded-[32px] p-8 relative z-10 shadow-2xl">
            <div class="w-16 h-1 bg-slate-200 rounded-full mx-auto mb-8"></div>
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-600 rounded-3xl mx-auto flex items-center justify-center text-white text-4xl shadow-xl shadow-blue-100 mb-6">
                    <i class="fas fa-mosque"></i>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 mb-2">Pasang Aplikasi Wali</h3>
                <p class="text-slate-500 text-sm leading-relaxed px-4">Nikmati kemudahan akses portal santri langsung dari layar utama Anda.</p>
            </div>
            <div class="space-y-3">
                <button id="btn-install-pwa" class="w-full btn-primary">Install Sekarang</button>
                <button onclick="dismissInstallPrompt()" class="w-full py-4 text-slate-400 font-bold text-sm uppercase tracking-widest">Nanti Saja</button>
            </div>
        </div>
    </div>

    @if(Auth::guard('wali')->check() && !request()->routeIs('wali.login'))
    <!-- Bottom Navigation Bar (Fintech style) -->
    <nav class="fixed bottom-0 left-0 right-0 h-20 glass-nav z-50 flex items-center justify-around px-4">
        <a href="{{ route('wali.app') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('wali.app') ? 'text-blue-600' : 'text-slate-400' }}">
            <div class="relative">
                <i class="fas fa-home-alt text-xl"></i>
                @if(request()->routeIs('wali.app'))
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-1 h-1 bg-blue-600 rounded-full"></div>
                @endif
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-widest">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-1.5 text-slate-400">
            <i class="fas fa-file-invoice-dollar text-xl"></i>
            <span class="text-[10px] font-extrabold uppercase tracking-widest">Tagihan</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-1.5 text-slate-400">
            <i class="fas fa-user-circle text-xl"></i>
            <span class="text-[10px] font-extrabold uppercase tracking-widest">Profile</span>
        </a>
    </nav>
    @endif

    <script>
        // SW Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw-wali.js');
            });
        }

        // Install Prompt Logic
        let deferredPrompt;
        const promptContainer = document.getElementById('pwa-install-prompt');
        const installBtn = document.getElementById('btn-install-pwa');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Auto show after delay if not dismissed
            if (!localStorage.getItem('pwa_prompt_dismissed')) {
                setTimeout(() => {
                    promptContainer.classList.remove('hidden');
                    setTimeout(() => {
                        promptContainer.classList.remove('translate-y-full');
                    }, 50);
                }, 5000);
            }
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
            promptContainer.classList.add('translate-y-full');
            localStorage.setItem('pwa_prompt_dismissed', 'true');
            setTimeout(() => {
                promptContainer.classList.add('hidden');
            }, 700);
        }
    </script>
</body>
</html>
