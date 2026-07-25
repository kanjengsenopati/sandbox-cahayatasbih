<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lapor Pak — Layanan Pengaduan Wali Santri</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex justify-center text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
    <div x-data="laporPakApp()" class="relative w-full max-w-lg min-h-screen bg-slate-50 pb-16 shadow-2xl flex flex-col">
        
        <!-- STICKY HEADER GLASSMORPHISM -->
        <header class="sticky top-0 z-30 backdrop-blur-md bg-white/85 border-b border-slate-200/60 px-5 pt-6 pb-3">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[24px] bg-blue-600/10 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-0.5">Layanan Publik</span>
                        <h1 class="text-[22px] font-bold text-slate-900 leading-tight">Lapor Pak</h1>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 text-[10px] font-bold border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
            </div>

            <!-- DUAL TABS SEGMENTED SWITCHER -->
            <div class="grid grid-cols-2 bg-slate-100 p-1 rounded-[24px] border border-slate-200/80">
                <button 
                    type="button"
                    @click="activeTab = 'form'" 
                    :class="activeTab === 'form' ? 'bg-white text-blue-600 shadow-[0_8px_30px_rgb(0,0,0,0.04)] font-bold' : 'text-slate-500 font-semibold hover:text-slate-700'"
                    class="py-2.5 rounded-[24px] text-xs transition duration-200 flex items-center justify-center gap-1.5"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Form Lapor</span>
                </button>
                
                <button 
                    type="button"
                    @click="activeTab = 'progress'" 
                    :class="activeTab === 'progress' ? 'bg-white text-blue-600 shadow-[0_8px_30px_rgb(0,0,0,0.04)] font-bold' : 'text-slate-500 font-semibold hover:text-slate-700'"
                    class="py-2.5 rounded-[24px] text-xs transition duration-200 flex items-center justify-center gap-1.5 relative"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Progress Laporan</span>
                </button>
            </div>
        </header>

        <!-- ERROR ALERT BANNER -->
        <div x-show="errorMessage" x-transition class="px-5 pt-4">
            <div class="rounded-[24px] bg-red-500/10 border border-red-500/20 p-4 flex items-center justify-between gap-3 text-red-700">
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700 text-xs font-bold">×</button>
            </div>
        </div>

        <!-- CONTENT TAB 1: FORM LAPOR -->
        <main x-show="activeTab === 'form'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pt-4 space-y-5 flex-1">
            <!-- Banner Info Periode Pelayanan -->
            <div class="rounded-[24px] bg-slate-50 border border-slate-200/80 p-3.5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block">Periode Pelayanan</span>
                        <span class="text-xs font-semibold text-slate-700">
                            @if($setting->start_datetime && $setting->end_datetime)
                                {{ $setting->start_datetime->format('d M Y, H:i') }} — {{ $setting->end_datetime->format('d M Y, H:i') }} WIB
                            @elseif($setting->start_datetime)
                                Mulai {{ $setting->start_datetime->format('d M Y, H:i') }} WIB
                            @elseif($setting->end_datetime)
                                Sampai {{ $setting->end_datetime->format('d M Y, H:i') }} WIB
                            @else
                                24/7 (Setiap Hari)
                            @endif
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    @if($setting->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 me-1.5 animate-pulse"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-600 border border-red-200/60">
                            Tutup
                        </span>
                    @endif
                </div>
            </div>

            <!-- Banner Informatif -->
            <div class="rounded-[24px] bg-blue-600 text-white p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-[16px] font-semibold text-white mb-1">Pusat Pengaduan Kendala Wali</h2>
                    <p class="text-blue-100 text-xs leading-relaxed">
                        Sampaikan masalah login, aplikasi, tagihan, atau nominal saldo Anda. Tim petugas kami akan segera menindaklanjuti.
                    </p>
                </div>
            </div>

            <!-- Form Utama (AJAX Submitted) -->
            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- TOP-LEVEL HIDDEN INPUTS (TIDAK BOLEH DI DALAM <TEMPLATE>) -->
                <input type="hidden" name="student_id" :value="selectedStudent ? selectedStudent.id : ''">
                <input type="hidden" name="student_name" :value="selectedStudent ? selectedStudent.name : ''">
                <input type="hidden" name="school" :value="selectedStudent ? selectedStudent.school : ''">
                <input type="hidden" name="class_name" :value="selectedStudent ? selectedStudent.class : ''">
                <input type="hidden" name="parent_name" :value="parentName">
                <input type="hidden" name="parent_phone" :value="parentPhone">
                <input type="hidden" name="is_parent_updated" :value="isEditParent ? '1' : '0'">

                <!-- LANGKAH 1: PILIH JENIS KENDALA -->
                <div class="rounded-[24px] bg-white p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-3 border border-slate-200/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-blue-600 block mb-0.5">Langkah 1</span>
                            <h2 class="text-[16px] font-semibold text-slate-800">Pilih Jenis Kendala</h2>
                        </div>
                        <span class="w-7 h-7 rounded-full bg-blue-600/10 text-blue-600 text-xs font-bold flex items-center justify-center">1</span>
                    </div>

                    <div class="space-y-2 pt-1">
                        <template x-for="(opt, idx) in kendalaOptions" :key="opt">
                            <label 
                                @click="selectedKendala = opt"
                                :class="selectedKendala === opt ? 'border-blue-600 bg-blue-600/5 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'"
                                class="w-full text-left p-3.5 rounded-[24px] border transition cursor-pointer flex items-center justify-between select-none"
                            >
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="kendala" :value="opt" x-model="selectedKendala" class="sr-only">
                                    <span 
                                        :class="selectedKendala === opt ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500'"
                                        class="w-6 h-6 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0"
                                        x-text="idx + 1"
                                    ></span>
                                    <span :class="selectedKendala === opt ? 'text-blue-600 font-bold' : 'text-slate-700 font-medium'" class="text-xs" x-text="opt"></span>
                                </div>
                                <svg x-show="selectedKendala === opt" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </label>
                        </template>
                    </div>

                    <!-- Input Keterangan Tambahan -->
                    <div x-show="selectedKendala" x-transition class="pt-2">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Keterangan Detail (Opsional)</span>
                        <textarea 
                            name="keterangan" 
                            rows="3" 
                            x-model="keterangan"
                            placeholder="Jelaskan detail kendala yang Anda alami secara singkat..."
                            class="w-full rounded-[24px] bg-slate-50 border border-slate-200 p-3.5 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 resize-none"
                        ></textarea>
                    </div>
                </div>

                <!-- LANGKAH 2: PENCARIAN NAMA SISWA -->
                <div class="rounded-[24px] bg-white p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-3 relative border border-slate-200/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-blue-600 block mb-0.5">Langkah 2</span>
                            <h2 class="text-[16px] font-semibold text-slate-800">Cari Data Siswa</h2>
                        </div>
                        <span class="w-7 h-7 rounded-full bg-blue-600/10 text-blue-600 text-xs font-bold flex items-center justify-center">2</span>
                    </div>

                    <div class="relative">
                        <div class="relative flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3.5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input 
                                type="text" 
                                x-model="studentQuery" 
                                @input.debounce.300ms="fetchStudents"
                                @focus="isDropdownOpen = true"
                                placeholder="Ketik nama siswa..." 
                                class="w-full pl-9 pr-4 py-3 rounded-[24px] bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                            >
                        </div>

                        <!-- Dropdown Hasil AJAX Pencarian Siswa -->
                        <div 
                            x-show="isDropdownOpen && filteredStudents.length > 0" 
                            @click.away="isDropdownOpen = false"
                            class="absolute left-0 right-0 top-full mt-2 bg-white border border-slate-200 rounded-[24px] shadow-lg z-30 max-h-48 overflow-y-auto divide-y divide-slate-100"
                        >
                            <template x-for="s in filteredStudents" :key="s.id">
                                <button 
                                    type="button" 
                                    @click="selectStudent(s)" 
                                    class="w-full text-left p-3 hover:bg-blue-50 transition flex items-center justify-between group"
                                >
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600" x-text="s.name"></div>
                                        <div class="text-[11px] text-slate-400 italic" x-text="s.school + ' — ' + s.class"></div>
                                    </div>
                                    <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-mono">Pilih</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Badge Siswa Terpilih -->
                    <div x-show="selectedStudent" class="p-3 rounded-[24px] bg-emerald-500/10 border border-emerald-500/20 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="min-w-0 flex-1">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-600 block mb-0.5">Siswa Terpilih</span>
                            <div class="font-bold text-slate-800 text-xs truncate" x-text="selectedStudent ? selectedStudent.displayLabel : ''"></div>
                        </div>
                    </div>
                </div>

                <!-- LANGKAH 3: DATA WALI -->
                <div x-show="selectedStudent" x-transition class="rounded-[24px] bg-white p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-3 border border-slate-200/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-blue-600 block mb-0.5">Langkah 3</span>
                            <h2 class="text-[16px] font-semibold text-slate-800">Data Wali Siswa</h2>
                        </div>
                        <button 
                            type="button" 
                            @click="isEditParent = !isEditParent" 
                            class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                        >
                            <span x-text="isEditParent ? 'Batal Update' : 'Opsi Update'"></span>
                        </button>
                    </div>

                    <div x-show="!isEditParent" class="bg-slate-50 rounded-[24px] p-4 space-y-2 border border-slate-200/80">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Nama Wali</span>
                            <span class="font-bold text-slate-800" x-text="parentName"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">No. WhatsApp</span>
                            <span class="font-bold text-slate-800 font-mono" x-text="parentPhone"></span>
                        </div>
                        <p class="text-[11px] text-slate-400 italic pt-1">*Data wali terhubung otomatis dari sistem perwalian.</p>
                    </div>

                    <div x-show="isEditParent" class="space-y-3 bg-blue-50/50 p-4 rounded-[24px] border border-blue-200">
                        <div>
                            <label class="text-[11px] font-bold uppercase tracking-widest text-slate-600 block mb-1">Nama Lengkap Wali</label>
                            <input 
                                type="text" 
                                x-model="parentName" 
                                class="w-full px-3.5 py-2.5 rounded-[24px] bg-white border border-slate-200 text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-600"
                                placeholder="Nama Wali"
                            >
                        </div>
                        <div>
                            <label class="text-[11px] font-bold uppercase tracking-widest text-slate-600 block mb-1">No. WhatsApp Aktif</label>
                            <input 
                                type="text" 
                                x-model="parentPhone" 
                                class="w-full px-3.5 py-2.5 rounded-[24px] bg-white border border-slate-200 text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-600 font-mono"
                                placeholder="Contoh: 081234567890"
                            >
                        </div>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        :disabled="isSubmitting || !selectedKendala || !selectedStudent"
                        class="w-full py-3.5 rounded-[24px] bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-[0_8px_30px_rgb(37,99,235,0.25)] transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Mengirimkan Pengaduan...</span>
                            </span>
                        </template>
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span>Kirim Pengaduan Lapor Pak</span>
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </main>

        <!-- CONTENT TAB 2: PROGRESS LAPORAN (TANPA ID TIKET) -->
        <main x-show="activeTab === 'progress'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pt-4 space-y-4 flex-1">
            
            <!-- Filter Search Bar Progress (Berdasarkan Nama Wali / Nama Siswa) -->
            <div class="bg-white p-4 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/60 space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block">Cari Status Pengaduan</span>
                <form action="{{ route('public.laporpak.index') }}" method="GET" class="relative flex items-center">
                    <input type="hidden" name="tab" value="progress">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3.5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari Nama Wali atau Nama Siswa..." 
                        class="w-full pl-9 pr-20 py-2.5 rounded-[24px] bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600"
                    >
                    <button type="submit" class="absolute right-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-[24px] text-[11px] font-bold hover:bg-blue-700 transition">
                        Cari
                    </button>
                </form>
            </div>

            <!-- List Progress Table / Mobile Cards (TANPA ID TIKET) -->
            <div class="space-y-3">
                @forelse($reports as $index => $rep)
                    <div class="bg-white p-4 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/60 space-y-2.5">
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block">Wali Santri</span>
                                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                    {{ $rep->parent_name }}
                                    @if($rep->is_parent_updated)
                                        <span class="text-[9px] bg-blue-500/10 text-blue-600 px-1.5 py-0.2 rounded font-semibold">Diupdate</span>
                                    @endif
                                </h3>
                            </div>
                            <!-- Status Milestone Badge -->
                            <div>
                                @if($rep->status === 'Selesai' || $rep->status === 'Teratasi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 text-[10px] font-bold border border-emerald-500/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Selesai
                                    </span>
                                @elseif($rep->status === 'Sedang Ditangani')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-600 text-[10px] font-bold border border-blue-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        Sedang Ditangani
                                    </span>
                                @elseif($rep->status === 'Diterima')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 text-[10px] font-bold border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Diterima
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-600 text-[10px] font-bold border border-slate-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Laporan Masuk
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Siswa:</span>
                                <span class="font-bold text-slate-800">{{ $rep->student_name }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-500 text-[11px]">
                                <span>Unit/Kelas:</span>
                                <span>{{ $rep->school }} — {{ $rep->class_name }}</span>
                            </div>
                            <div class="pt-1">
                                <span class="inline-block px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 text-[10px] font-bold mb-0.5">
                                    {{ $rep->kendala }}
                                </span>
                                @if($rep->keterangan)
                                    <p class="text-slate-600 text-xs bg-slate-50 p-2.5 rounded-[24px] border border-slate-100 leading-relaxed mt-1">
                                        {{ $rep->keterangan }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- VISUAL MILESTONE PROGRESS TRACKER -->
                        @php
                            $statusStepMap = [
                                'Laporan Masuk' => 1,
                                'Diterima' => 2,
                                'Sedang Ditangani' => 3,
                                'Selesai' => 4,
                                'Kendala' => 1,
                                'Teratasi' => 4,
                            ];
                            $currentStep = $statusStepMap[$rep->status] ?? 1;
                            $progressPct = match($currentStep) {
                                1 => '18%',
                                2 => '46%',
                                3 => '75%',
                                4 => '100%',
                                default => '18%',
                            };
                        @endphp
                        <div class="mt-3 pt-3 border-t border-slate-100/80">
                            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                                <span>Pantau Tindak Lanjut</span>
                                <span class="font-semibold text-blue-600 font-mono">{{ $rep->status }}</span>
                            </div>

                            <div class="relative py-2 px-2">
                                <!-- Background Connecting Line -->
                                <div class="absolute top-[18px] left-6 right-6 h-1 bg-slate-200/80 rounded-full z-0"></div>
                                <!-- Active Progress Line -->
                                <div class="absolute top-[18px] left-6 h-1 bg-gradient-to-r from-blue-600 via-blue-500 to-emerald-500 rounded-full z-0 transition-all duration-500" style="width: calc({{ $progressPct }} - 24px);"></div>

                                <!-- 4 Milestone Step Nodes -->
                                <div class="relative z-10 flex items-center justify-between">
                                    <!-- Node 1: Laporan Masuk -->
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold transition-all duration-300 {{ $currentStep >= 1 ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-slate-200 text-slate-400' }}">
                                            @if($currentStep > 1)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                1
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-center leading-tight {{ $currentStep >= 1 ? 'text-blue-600' : 'text-slate-400' }}">Laporan<br>Masuk</span>
                                    </div>

                                    <!-- Node 2: Diterima -->
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold transition-all duration-300 {{ $currentStep >= 2 ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-slate-200 text-slate-400' }}">
                                            @if($currentStep > 2)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                2
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-center leading-tight {{ $currentStep >= 2 ? 'text-blue-600' : 'text-slate-400' }}">Diterima</span>
                                    </div>

                                    <!-- Node 3: Sedang Ditangani -->
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold transition-all duration-300 {{ $currentStep >= 3 ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-slate-200 text-slate-400' }}">
                                            @if($currentStep > 3)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                3
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-center leading-tight {{ $currentStep >= 3 ? 'text-blue-600' : 'text-slate-400' }}">Sedang<br>Ditangani</span>
                                    </div>

                                    <!-- Node 4: Selesai -->
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold transition-all duration-300 {{ $currentStep >= 4 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-200 text-slate-400' }}">
                                            @if($currentStep >= 4)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                4
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-center leading-tight {{ $currentStep >= 4 ? 'text-emerald-600' : 'text-slate-400' }}">Selesai</span>
                                </div>
                            </div>

                            <!-- Catatan Petugas (Jika ada admin_note) -->
                            @if(!empty($rep->admin_note))
                                <div class="mt-3 p-3 rounded-[16px] bg-blue-50/80 border border-blue-200/60 text-left space-y-1">
                                    <div class="flex items-center gap-1.5 text-blue-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-blue-600">Catatan Petugas</span>
                                    </div>
                                    <p class="text-xs text-slate-700 font-medium leading-relaxed">
                                        {{ $rep->admin_note }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Timestamp Bottom -->
                        <div class="pt-1 text-[10px] text-slate-400 italic font-mono flex justify-between items-center">
                            <span>Lapor: {{ $rep->created_at ? $rep->created_at->format('d M Y, H:i') : '-' }} WIB</span>
                            <span class="text-slate-300">#{{ $index + 1 }}</span>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-8 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/60 text-center space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xs text-slate-500 font-medium">Belum ada data pengaduan yang sesuai.</p>
                    </div>
                @endforelse
            </div>
        </main>

        <!-- MODAL RESI SUKSES INSTAN -->
        <div x-show="isSuccessModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-sm p-6 text-center space-y-4 border border-slate-200">
                <div class="w-14 h-14 bg-emerald-500/10 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-600 block mb-1">Berhasil Terkirim</span>
                    <h2 class="text-[18px] font-bold text-slate-900 leading-snug">Pengaduan Berhasil Terdaftar</h2>
                    <p class="text-xs text-slate-500 mt-1">Laporan Anda telah berhasil masuk ke sistem petugas Lapor Pak.</p>
                </div>

                <div class="bg-slate-50 rounded-[24px] p-4 text-left space-y-2 text-xs border border-slate-200/80" x-show="submittedData">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Nama Wali</span>
                        <span class="font-bold text-slate-800" x-text="submittedData ? submittedData.parent_name : ''"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Nama Siswa</span>
                        <span class="font-bold text-slate-800" x-text="submittedData ? submittedData.student_name : ''"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Kendala</span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 font-bold text-[10px]" x-text="submittedData ? submittedData.kendala : ''"></span>
                    </div>
                </div>

                <div class="pt-2">
                    <button 
                        type="button" 
                        @click="isSuccessModalOpen = false; activeTab = 'progress';"
                        class="w-full py-3 rounded-[24px] bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition active:scale-95"
                    >
                        Lihat Progress Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ALPINE JS LOGIC -->
    <script>
        function laporPakApp() {
            return {
                activeTab: "{{ $tab }}" || "form",
                kendalaOptions: [
                    "Kendala Login",
                    "Kendala Download dan Install",
                    "Kendala Tagihan Belum Sesuai",
                    "Kendala Nominal Saldo Belum Sesuai",
                    "Kendala Lainnya"
                ],
                selectedKendala: "",
                keterangan: "",
                studentQuery: "",
                filteredStudents: [],
                selectedStudent: null,
                isDropdownOpen: false,
                parentName: "",
                parentPhone: "",
                isEditParent: false,
                isSubmitting: false,
                errorMessage: "",
                isSuccessModalOpen: false,
                submittedData: null,

                fetchStudents() {
                    if (this.studentQuery.trim().length === 0) {
                        this.filteredStudents = [];
                        return;
                    }

                    fetch(`/laporpak/search-students?q=${encodeURIComponent(this.studentQuery)}`)
                        .then(res => res.json())
                        .then(data => {
                            this.filteredStudents = data;
                            this.isDropdownOpen = true;
                        })
                        .catch(err => console.error("Error fetching students:", err));
                },

                selectStudent(s) {
                    this.selectedStudent = s;
                    this.studentQuery = s.displayLabel;
                    this.parentName = s.parentName;
                    this.parentPhone = s.parentPhone;
                    this.isDropdownOpen = false;
                    this.isEditParent = false;
                },

                submitForm() {
                    if (!this.selectedKendala) {
                        alert("Harap pilih salah satu jenis kendala.");
                        return;
                    }
                    if (!this.selectedStudent) {
                        alert("Harap pilih data siswa dari hasil pencarian.");
                        return;
                    }
                    if (!this.parentName.trim() || !this.parentPhone.trim()) {
                        alert("Harap lengkapi nama wali dan nomor WhatsApp.");
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = "";

                    const formData = new FormData();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    formData.append('_token', csrfToken);
                    formData.append('kendala', this.selectedKendala);
                    formData.append('keterangan', this.keterangan || '');
                    formData.append('student_id', this.selectedStudent ? this.selectedStudent.id : '');
                    formData.append('student_name', this.selectedStudent ? this.selectedStudent.name : '');
                    formData.append('school', this.selectedStudent ? this.selectedStudent.school : '');
                    formData.append('class_name', this.selectedStudent ? this.selectedStudent.class : '');
                    formData.append('parent_name', this.parentName.trim());
                    formData.append('parent_phone', this.parentPhone.trim());
                    if (this.isEditParent) {
                        formData.append('is_parent_updated', '1');
                    }

                    fetch('{{ route("public.laporpak.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(async (res) => {
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Gagal mengirim pengaduan. Silakan periksa kembali data Anda.');
                        }
                        return data;
                    })
                    .then(data => {
                        this.isSubmitting = false;
                        this.submittedData = data.data;
                        this.isSuccessModalOpen = true;

                        // Reset form
                        this.selectedKendala = "";
                        this.keterangan = "";
                        this.studentQuery = "";
                        this.selectedStudent = null;
                        this.parentName = "";
                        this.parentPhone = "";
                        this.isEditParent = false;
                    })
                    .catch(err => {
                        this.isSubmitting = false;
                        this.errorMessage = err.message || "Terjadi kesalahan saat menghubungi server. Silakan coba lagi.";
                    });
                }
            }
        }
    </script>
</body>
</html>
