<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#7c3aed">
    <meta name="title" content="Cek Saldo Kartu Santri — PPTQ Cahaya Tasbih">
    <meta name="description" content="Layanan cek saldo mandiri Kartu Santri PPTQ Cahaya Tasbih dengan pemindaian barcode cepat dan real-time.">
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo.png') }}">
    <title>Cek Saldo Santri — PPTQ Cahaya Tasbih</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        /* Typography System Wrappers */
        .text-h1 { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1.25; }
        .text-h2 { font-size: 16px; font-weight: 600; color: #1e293b; line-height: 1.35; }
        .text-amount { font-size: 18px; font-weight: 700; color: #7c3aed; }
        .text-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; }
        .text-body { font-size: 14px; font-weight: 500; color: #475569; }
        .text-caption { font-size: 12px; font-weight: 400; font-style: italic; color: #94a3b8; }

        /* Prominent Display Scales for Kiosk Readability */
        .prominent-student-name {
            font-size: clamp(1.65rem, 3.2vw, 2.45rem);
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
            letter-spacing: -0.02em;
        }
        .prominent-subidentity {
            font-size: clamp(1.05rem, 1.8vw, 1.35rem);
            line-height: 1.35;
        }
        .prominent-saldo-amount {
            font-size: clamp(2.75rem, 6.2vw, 4.75rem);
            font-weight: 900;
            color: #7c3aed;
            line-height: 1.05;
            letter-spacing: -0.03em;
        }
        .prominent-limit-amount {
            font-size: clamp(1.35rem, 2.4vw, 1.85rem);
            font-weight: 800;
            line-height: 1.2;
        }

        @keyframes scanLine {
            0%, 100% { transform: translateY(-18px); opacity: 0.35; }
            50% { transform: translateY(18px); opacity: 1; }
        }
        .animate-scan-line {
            animation: scanLine 2.2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased selection:bg-violet-600 selection:text-white flex flex-col">

    <div
        x-data="cekSaldoKiosk()"
        x-init="initKiosk()"
        @click="focusScanner()"
        class="w-full min-h-screen flex flex-col justify-between"
    >
        <!-- STICKY HEADER GLASSMORPHISM -->
        <header class="sticky top-0 z-30 backdrop-blur-md bg-white/80 px-5 py-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-[24px] bg-violet-600/10 flex items-center justify-center shrink-0">
                        <img
                            src="{{ asset('assets/media/logos/logo.png') }}"
                            alt="Logo PPTQ Cahaya Tasbih"
                            class="w-8 h-8 object-contain"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                        >
                        <!-- Fallback Icon -->
                        <svg style="display:none;" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-label block mb-0.5">Layanan Mandiri Kiosk POS</span>
                        <h1 class="text-h1">Cek Saldo Kartu Santri</h1>
                    </div>
                </div>

                <!-- Top-Right Action Cluster: Scanner Active Badge + Full Screen Toggle -->
                <div class="flex items-center gap-2.5">
                    <span class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-[24px] bg-emerald-600/10 text-emerald-600 text-label">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                        <span>Scanner Aktif</span>
                    </span>

                    <button
                        type="button"
                        @click.stop="toggleFullscreen()"
                        :title="isFullscreen ? 'Keluar dari Mode Layar Penuh (Esc)' : 'Aktifkan Mode Layar Penuh Kiosk (Sembunyikan Address Bar)'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-[24px] transition shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
                        :class="isFullscreen ? 'bg-violet-600 text-white' : 'bg-slate-100 hover:bg-violet-600/10 text-slate-700 hover:text-violet-600'"
                    >
                        <!-- Icon Maximize (when not fullscreen) -->
                        <svg x-show="!isFullscreen" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
                            <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
                            <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
                            <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
                        </svg>
                        <!-- Icon Minimize (when fullscreen) -->
                        <svg x-show="isFullscreen" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                            <path d="M8 3v3a2 2 0 0 1-2 2H3"/>
                            <path d="M21 8h-3a2 2 0 0 1-2-2V3"/>
                            <path d="M3 16h3a2 2 0 0 1 2 2v3"/>
                            <path d="M16 21v-3a2 2 0 0 1 2-2h3"/>
                        </svg>
                        <span class="text-xs font-bold tracking-wide" x-text="isFullscreen ? 'Keluar Fullscreen' : 'Full Screen'"></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- MAIN CONTAINER (SAFE AREA px-5) -->
        <main class="flex-1 flex flex-col justify-center px-5 py-6 md:py-10">
            <div class="w-full max-w-4xl mx-auto space-y-5">

                <!-- BARCODE SCANNER INPUT CARD (ALWAYS ACTIVE & READY LIKE POS KASIR) -->
                <div class="rounded-[24px] bg-white p-5 md:p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <form @submit.prevent="submitScan()" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <!-- Lucide ScanBarcode Icon (18px, stroke 2px, text-slate-400) -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                                    <path d="M3 7V5a2 2 0 0 1 2-2h2"/>
                                    <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                                    <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                                    <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                                    <path d="M8 7v10"/>
                                    <path d="M12 7v10"/>
                                    <path d="M17 7v10"/>
                                </svg>
                            </div>
                            <input
                                x-ref="scannerInput"
                                id="scan-card-input"
                                type="password"
                                x-model="barcodeInput"
                                @keydown.enter.prevent="submitScan()"
                                @blur="handleBlur()"
                                autocomplete="off"
                                autofocus
                                placeholder="Tempelkan / Scan Barcode Kartu Santri pada alat scanner..."
                                class="w-full pl-11 pr-10 py-3.5 rounded-[24px] bg-slate-50 text-body text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-600/30 focus:bg-white transition"
                            >
                            <!-- Loading Spinner inside Input -->
                            <div x-show="isLoading" x-cloak class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <svg class="animate-spin h-5 w-5 text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Top-Right Action Cluster / Reset Button when student is shown -->
                        <div x-show="student || errorMessage" x-cloak class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                @click.stop="resetKiosk()"
                                class="w-full sm:w-auto px-5 py-3.5 rounded-[24px] bg-slate-100 hover:bg-slate-200/80 text-body flex items-center justify-center gap-2 transition"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                </svg>
                                <span>Tutup (<span x-text="countdown"></span>d)</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ERROR / NOT FOUND BANNER -->
                <div
                    x-show="errorMessage"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-[24px] bg-red-600/10 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col sm:flex-row items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-4 text-center sm:text-left">
                        <div class="w-14 h-14 rounded-[24px] bg-red-600/10 flex items-center justify-center shrink-0 mx-auto sm:mx-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" x2="12" y1="8" y2="12"/>
                                <line x1="12" x2="12.01" y1="16" y2="16"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-label !text-red-600 block mb-0.5">Pemindaian Gagal</span>
                            <h2 class="text-h1 !text-red-600" x-text="errorMessage"></h2>
                            <p class="text-body mt-1">Silakan tempelkan kembali Kartu Santri yang terdaftar pada alat pemindai barcode.</p>
                        </div>
                    </div>
                </div>

                <!-- STATE 1: STANDBY / READY TO SCAN SCREEN -->
                <div
                    x-show="!student && !isLoading"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="rounded-[24px] bg-white p-8 md:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.04)] text-center"
                >
                    <div class="relative w-28 h-28 mx-auto mb-6 rounded-[24px] bg-violet-600/10 flex items-center justify-center overflow-hidden">
                        <!-- Animated Laser Line -->
                        <div class="w-20 h-0.5 bg-violet-600 rounded-full absolute animate-scan-line"></div>
                        <!-- Barcode Graphic -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7V5a2 2 0 0 1 2-2h2"/>
                            <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                            <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                            <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                            <path d="M8 7v10"/>
                            <path d="M12 7v10"/>
                            <path d="M17 7v10"/>
                        </svg>
                    </div>

                    <span class="text-label !text-violet-600 block mb-2">Siap Memindai Kartu</span>
                    <h2 class="prominent-student-name mb-3">Silakan Scan Kartu Santri Anda</h2>
                    <p class="text-body max-w-lg mx-auto">
                        Arahkan barcode pada <strong>Kartu Santri</strong> ke alat scanner. Informasi <strong>Nama</strong>, <strong>NIS</strong>, <strong>Kelas</strong>, dan <strong>Nominal Saldo</strong> akan tampil secara otomatis.
                    </p>

                    <div class="mt-8 inline-flex items-center gap-2.5 px-4 py-2 rounded-[24px] bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                        </svg>
                        <span class="text-caption !not-italic">Kode kartu terenkripsi &bull; Layar otomatis di-reset setelah 15 detik</span>
                    </div>
                </div>

                <!-- STATE 2: PROMINENT STUDENT BALANCE RESULT -->
                <template x-if="student">
                    <div
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="space-y-5"
                    >
                        <!-- CARD PERINGATAN JIKA KARTU DIBLOKIR / DILAPORKAN KENDALA -->
                        <template x-if="student.is_card_reported || student.is_blocked">
                            <div class="rounded-[24px] bg-red-600/10 p-5 md:p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-[24px] bg-red-600 text-white flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-label !text-red-600 block mb-1">Perhatian &bull; Kartu Terkunci untuk Transaksi</span>
                                        <h3 class="text-h1 !text-red-600" x-text="student.is_card_reported ? ('Kartu Sedang Dilaporkan: ' + (student.card_report?.issue_label || 'Kendala Kartu')) : 'Kartu Sedang Diblokir oleh Wali Santri'"></h3>
                                        <p class="text-body !text-red-600 mt-1" x-text="student.is_card_reported ? 'Kartu ini sementara tidak dapat digunakan berbelanja di kasir hingga laporan kendala diselesaikan oleh Admin.' : 'Kartu fisik ini dinonaktifkan sementara oleh Wali Santri melalui aplikasi.'"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- MAIN PROMINENT RESULT CARD -->
                        <div class="rounded-[24px] bg-white p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                            <!-- Top-Right Action & Status Cluster -->
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                                <div class="flex items-center gap-2">
                                    <span class="text-label">Identitas Santri Terverifikasi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <template x-if="!student.is_card_reported && !student.is_blocked">
                                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[24px] bg-emerald-600/10 text-emerald-600 text-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5"/>
                                            </svg>
                                            <span>Kartu Aktif &bull; Verified</span>
                                        </span>
                                    </template>
                                    <template x-if="student.is_card_reported || student.is_blocked">
                                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[24px] bg-red-600/10 text-red-600 text-label">
                                            <span>Kartu Terkunci</span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- STUDENT IDENTITY SECTION: ALIGNED VERTICALLY WITH AVATAR -->
                            <div class="flex flex-col sm:flex-row items-center gap-5 md:gap-6 pb-6 border-b border-slate-100 text-center sm:text-left">
                                <!-- Avatar Siswa -->
                                <div class="w-20 h-20 md:w-24 md:h-24 rounded-[24px] bg-violet-600/10 flex items-center justify-center overflow-hidden shrink-0">
                                    <img
                                        :src="student.avatar_url"
                                        :alt="student.name"
                                        class="w-full h-full object-cover"
                                        x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex';"
                                    >
                                    <div style="display:none;" class="w-full h-full items-center justify-center text-h1 !text-violet-600" x-text="getInitials(student.name)"></div>
                                </div>

                                <!-- Nama Siswa + Baris NIS & Kelas Tepat di Bawah Nama (Sejajar dengan Avatar) -->
                                <div class="flex-1 min-w-0 w-full flex flex-col justify-center">
                                    <span class="text-label block mb-1">Nama Lengkap Santri</span>
                                    <h2 class="prominent-student-name break-words" x-text="student.name"></h2>

                                    <!-- Satu Baris Teks Tegas NIS & Kelas Tepat di Bawah Nama -->
                                    <div class="mt-2 flex flex-wrap items-center justify-center sm:justify-start gap-x-3.5 gap-y-1 prominent-subidentity">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-slate-400 font-semibold">NIS:</span>
                                            <span class="text-slate-800 font-extrabold tracking-tight" x-text="student.nis"></span>
                                        </div>
                                        <span class="text-slate-300 font-bold select-none">&bull;</span>
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-slate-400 font-semibold">Kelas:</span>
                                            <span class="text-slate-800 font-extrabold" x-text="student.class_name + (student.school_name ? ' — ' + student.school_name : '')"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTTOM DENSITY: HERO NOMINAL SALDO (CENTERED & MODERN PURPLE) -->
                            <div class="pt-6">
                                <div class="rounded-[24px] bg-violet-600/10 px-6 py-8 md:py-11 flex flex-col items-center justify-center text-center">
                                    <span class="text-label !text-violet-600 block mb-2">Nominal Saldo Saat Ini</span>
                                    <div class="text-amount prominent-saldo-amount" x-text="student.saldo_formatted"></div>
                                </div>

                                <!-- DAILY LIMIT QUOTA SECTION (IF STUDENT HAS ACTIVE DAILY LIMIT) -->
                                <template x-if="student.effective_daily_limit > 0">
                                    <div class="mt-4 rounded-[24px] bg-slate-50 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                                        <div class="flex items-center gap-3.5 text-center sm:text-left">
                                            <div class="w-10 h-10 rounded-[24px] bg-violet-600/10 flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="text-label block">Sisa Kuota Belanja Hari Ini</span>
                                                <div
                                                    class="prominent-limit-amount"
                                                    :class="student.remaining_limit > 0 ? 'text-violet-600' : 'text-red-600'"
                                                    x-text="student.remaining_limit_formatted"
                                                ></div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-6 text-center sm:text-right">
                                            <div>
                                                <span class="text-label block">Terpakai Hari Ini</span>
                                                <span class="text-h2" x-text="student.total_this_day_formatted"></span>
                                            </div>
                                            <div class="h-8 w-px bg-slate-200"></div>
                                            <div>
                                                <span class="text-label block">Batas Limit Harian</span>
                                                <span class="text-h2" x-text="student.effective_daily_limit_formatted"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- AUTO-RESET PROGRESS BAR (15 SECONDS) -->
                                <div class="mt-5">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-caption !not-italic">Layar otomatis kembali ke mode siap scan dalam <strong class="text-slate-600" x-text="countdown + ' detik'"></strong></span>
                                        <span class="text-caption">Scan kartu lain untuk langsung mengganti</span>
                                    </div>
                                    <div class="w-full h-2 rounded-[24px] bg-slate-100 overflow-hidden">
                                        <div
                                            class="h-full bg-violet-600 transition-all duration-1000 ease-linear rounded-[24px]"
                                            :style="'width: ' + ((countdown / maxCountdown) * 100) + '%'"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </main>

        <!-- FOOTER -->
        <footer class="px-5 py-4 text-center">
            <span class="text-caption !not-italic">&copy; {{ date('Y') }} PPTQ Cahaya Tasbih &bull; Sistem Informasi Manajemen &amp; POS Santri</span>
        </footer>
    </div>

    <script>
        function cekSaldoKiosk() {
            return {
                barcodeInput: '',
                isLoading: false,
                student: null,
                errorMessage: '',
                maxCountdown: 15,
                countdown: 15,
                countdownTimer: null,
                isFullscreen: false,

                initKiosk() {
                    this.focusScanner();

                    const syncFullscreenState = () => {
                        this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
                        this.focusScanner();
                    };

                    document.addEventListener('fullscreenchange', syncFullscreenState);
                    document.addEventListener('webkitfullscreenchange', syncFullscreenState);

                    // Global hardware barcode scanner listener:
                    // Ensures that even if the input loses focus, any barcode scan is captured cleanly.
                    window.addEventListener('keydown', (e) => {
                        const inputEl = this.$refs.scannerInput;
                        if (!inputEl) return;

                        if (document.activeElement !== inputEl) {
                            if (e.key === 'Enter' && this.barcodeInput.trim().length > 0) {
                                e.preventDefault();
                                this.submitScan();
                            } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                                inputEl.focus();
                            }
                        }
                    });
                },

                async toggleFullscreen() {
                    try {
                        const docEl = document.documentElement;
                        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                            if (docEl.requestFullscreen) {
                                await docEl.requestFullscreen();
                            } else if (docEl.webkitRequestFullscreen) {
                                docEl.webkitRequestFullscreen();
                            }
                        } else {
                            if (document.exitFullscreen) {
                                await document.exitFullscreen();
                            } else if (document.webkitExitFullscreen) {
                                document.webkitExitFullscreen();
                            }
                        }
                    } catch (err) {
                        console.warn('Fullscreen toggle gagal:', err);
                    } finally {
                        this.focusScanner();
                    }
                },

                focusScanner() {
                    this.$nextTick(() => {
                        if (this.$refs.scannerInput) {
                            this.$refs.scannerInput.focus({ preventScroll: true });
                        }
                    });
                },

                handleBlur() {
                    setTimeout(() => {
                        this.focusScanner();
                    }, 150);
                },

                getInitials(name) {
                    if (!name) return 'S';
                    const parts = name.trim().split(/\s+/);
                    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
                },

                playFeedbackTone(isSuccess) {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);

                        if (isSuccess) {
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(880, ctx.currentTime);
                            gain.gain.setValueAtTime(0.08, ctx.currentTime);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.12);
                        } else {
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(240, ctx.currentTime);
                            gain.gain.setValueAtTime(0.1, ctx.currentTime);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.22);
                        }
                    } catch (_) {}
                },

                startCountdown() {
                    this.stopCountdown();
                    this.countdown = this.maxCountdown;
                    this.countdownTimer = setInterval(() => {
                        if (this.countdown > 1) {
                            this.countdown -= 1;
                        } else {
                            this.resetKiosk();
                        }
                    }, 1000);
                },

                stopCountdown() {
                    if (this.countdownTimer) {
                        clearInterval(this.countdownTimer);
                        this.countdownTimer = null;
                    }
                },

                resetKiosk() {
                    this.stopCountdown();
                    this.student = null;
                    this.errorMessage = '';
                    this.barcodeInput = '';
                    this.focusScanner();
                },

                async submitScan() {
                    const code = (this.barcodeInput || '').trim();
                    this.barcodeInput = '';
                    this.focusScanner();

                    if (!code || this.isLoading) {
                        return;
                    }

                    this.isLoading = true;
                    this.errorMessage = '';

                    try {
                        const response = await axios.post("{{ route('public.cek-saldo.scan') }}", {
                            barcode: code
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                'Accept': 'application/json'
                            }
                        });

                        const res = response.data;
                        if (res && res.success && res.data) {
                            this.student = res.data;
                            this.errorMessage = '';
                            this.playFeedbackTone(true);
                            this.startCountdown();
                        } else {
                            this.student = null;
                            this.errorMessage = (res && res.message) ? res.message : 'Data santri dengan kartu tersebut tidak ditemukan.';
                            this.playFeedbackTone(false);
                            this.startCountdown();
                        }
                    } catch (err) {
                        this.student = null;
                        if (err.response && err.response.status === 429) {
                            this.errorMessage = 'Terlalu banyak pemindaian dalam waktu singkat. Mohon tunggu beberapa detik.';
                        } else {
                            this.errorMessage = err.response?.data?.message || 'Gagal menghubungi server. Silakan scan ulang kartu Anda.';
                        }
                        this.playFeedbackTone(false);
                        this.startCountdown();
                    } finally {
                        this.isLoading = false;
                        this.focusScanner();
                    }
                }
            };
        }
    </script>
</body>
</html>
