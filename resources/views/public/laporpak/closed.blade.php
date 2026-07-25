<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Lapor Pak — Layanan Ditutup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 text-slate-800">
    <div class="w-full max-w-md bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 text-center space-y-5 border border-slate-200/60">
        <!-- Icon Banner -->
        <div class="w-16 h-16 bg-amber-500/10 text-amber-600 rounded-[24px] flex items-center justify-center mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Layanan Pengaduan Publik</span>
            <h1 class="text-[22px] font-bold text-slate-900 leading-snug">Form Lapor Pak Sedang Ditutup</h1>
        </div>

        <div class="bg-slate-50 rounded-[24px] p-4 text-left border border-slate-200/80 space-y-2 text-xs">
            <p class="text-slate-600 leading-relaxed">
                {{ $setting->closed_message ?: 'Form pengaduan Lapor Pak saat ini belum dibuka atau telah ditutup oleh pengelola.' }}
            </p>

            @if($setting->start_datetime || $setting->end_datetime)
                <div class="pt-2 border-t border-slate-200/80 space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Jadwal Periode Akses:</span>
                    @if($setting->start_datetime)
                        <div class="flex justify-between items-center text-slate-700 font-medium">
                            <span>Waktu Buka:</span>
                            <span class="font-bold font-mono">{{ $setting->start_datetime->format('d M Y, H:i') }} WIB</span>
                        </div>
                    @endif
                    @if($setting->end_datetime)
                        <div class="flex justify-between items-center text-slate-700 font-medium">
                            <span>Waktu Tutup:</span>
                            <span class="font-bold font-mono">{{ $setting->end_datetime->format('d M Y, H:i') }} WIB</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-2">
            <a href="/" class="inline-block w-full py-3.5 rounded-[24px] bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition active:scale-95">
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>
</body>
</html>
