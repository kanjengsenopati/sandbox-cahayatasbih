@extends('layouts.wali-pwa')

@section('content')
<!-- Hero Section (Fintech Style) -->
<div class="hero-gradient pt-16 pb-32 px-6 rounded-b-[48px] relative overflow-hidden">
    <!-- Abstract Decoration -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-400/20 rounded-full -ml-24 -mb-24 blur-2xl"></div>

    <div class="relative z-10">
        <!-- Top Row: Greetings & Profile -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <div class="text-blue-100 text-xs font-bold uppercase tracking-widest mb-1 opacity-80">Assalamu'alaikum,</div>
                <h1 class="text-white text-2xl font-extrabold tracking-tight">{{ explode(' ', auth('wali')->user()->name)[0] }}</h1>
            </div>
            <div class="flex gap-3">
                <button class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white backdrop-blur-md">
                    <i class="fas fa-bell"></i>
                </button>
                <img src="{{ url(auth('wali')->user()->avatar ?: 'assets/media/avatars/default.png') }}" class="w-10 h-10 rounded-xl object-cover border-2 border-white/20" alt="">
            </div>
        </div>

        <!-- Hero Card: Active Student -->
        @if($activeStudent)
        <div class="bg-white/10 backdrop-blur-md rounded-4xl p-6 border border-white/20 shadow-2xl">
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img src="{{ url($activeStudent->avatar ?: 'assets/media/avatars/default.png') }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white" alt="">
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center text-[10px] text-white">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-white font-extrabold text-lg leading-tight">{{ $activeStudent->name }}</div>
                        <div class="text-blue-100/70 text-xs font-bold">{{ $activeStudent->classroom->name ?? 'Tanpa Kelas' }}</div>
                    </div>
                </div>
                <button onclick="toggleStudentSelector()" class="bg-white/20 px-3 py-2 rounded-xl text-white text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                    <span>Ganti</span>
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>

            <div class="h-[1px] bg-white/10 mb-6"></div>

            <div class="flex justify-between items-end">
                <div>
                    <div class="text-blue-100/60 text-[10px] font-black uppercase tracking-[0.2em] mb-1">Sisa Saldo Saku</div>
                    <div class="text-white text-2xl font-black tabular-nums">Rp {{ number_format($activeStudent->saldo, 0, ',', '.') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-blue-100/60 text-[10px] font-black uppercase tracking-[0.2em] mb-1">Tabungan</div>
                    <div class="text-white text-lg font-bold tabular-nums">Rp {{ number_format($activeStudent->saving, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white/10 backdrop-blur-md rounded-4xl p-8 border border-white/20 text-center text-white">
            <i class="fas fa-user-slash text-3xl mb-4 opacity-40"></i>
            <p class="font-bold">Belum ada santri tertaut</p>
        </div>
        @endif
    </div>
</div>

<!-- Quick Actions (Fintech Native Layout) -->
<div class="px-6 -mt-12 relative z-20 mb-8">
    <div class="bg-white rounded-[32px] shadow-card p-6 grid grid-cols-3 gap-y-6 justify-items-center border border-slate-50">
        <a href="{{ route('wali.topup') }}" class="flex flex-col items-center gap-2.5">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-emerald-100/50 active:scale-90 transition-transform">
                <i class="fas fa-plus-circle"></i>
            </div>
            <span class="text-[10px] font-extrabold text-slate-800 uppercase tracking-tighter">Topup</span>
        </a>
        <a href="{{ route('wali.history') }}" class="flex flex-col items-center gap-2.5">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-blue-100/50 active:scale-90 transition-transform">
                <i class="fas fa-history"></i>
            </div>
            <span class="text-[10px] font-extrabold text-slate-800 uppercase tracking-tighter">Riwayat</span>
        </a>
        <a href="{{ route('wali.bills') }}" class="flex flex-col items-center gap-2.5">
            <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-orange-100/50 active:scale-90 transition-transform">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <span class="text-[10px] font-extrabold text-slate-800 uppercase tracking-tighter">Tagihan</span>
        </a>
        <a href="{{ route('wali.schedule') }}" class="flex flex-col items-center gap-2.5">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-indigo-100/50 active:scale-90 transition-transform">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <span class="text-[10px] font-extrabold text-slate-800 uppercase tracking-tighter">Agenda</span>
        </a>
        <a href="{{ route('wali.limit') }}" class="flex flex-col items-center gap-2.5">
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-purple-100/50 active:scale-90 transition-transform">
                <i class="fas fa-shield-halved"></i>
            </div>
            <span class="text-[10px] font-extrabold text-slate-800 uppercase tracking-tighter">Limit</span>
        </a>
        <a href="{{ route('wali.ppdb.index') }}" class="flex flex-col items-center gap-2.5">
            <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-rose-100/50 active:scale-90 transition-transform">
                <i class="fas fa-user-plus"></i>
            </div>
            <span class="text-[10px] font-extrabold text-slate-800 uppercase tracking-tighter">PPDB</span>
        </a>
    </div>
</div>

<!-- Main Content Area -->
<div class="px-6 pb-20">
    <!-- Academic Snapshot -->
    <div class="mb-10">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-h2">Informasi Akademik</h2>
            <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('wali.grades') }}" class="card-premium p-4 active:scale-95 transition-transform block">
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm mb-3">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="text-[10px] font-black uppercase text-slate-400 mb-1">Mata Pelajaran</div>
                <div class="text-h2">{{ $studyCount }} Aktif</div>
            </a>
            <a href="{{ route('wali.tahfidz') }}" class="card-premium p-4 active:scale-95 transition-transform block">
                <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-sm mb-3">
                    <i class="fas fa-star"></i>
                </div>
                <div class="text-[10px] font-black uppercase text-slate-400 mb-1">Halaman Tahfidz</div>
                <div class="text-h2">{{ $tahfidzCount }} Hal</div>
            </a>
        </div>
    </div>

    <!-- Feed / Information -->
    <div>
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-h2">Berita Sekolah</h2>
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">Lihat Semua</span>
        </div>
        <div class="space-y-4">
            @foreach($informations as $info)
            <a href="{{ route('wali.news-detail', $info->id) }}" class="card-premium flex gap-4 p-4 items-center active:bg-slate-50 transition-colors block">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex-shrink-0 overflow-hidden">
                    <img src="{{ asset('storage/' . $info->image) }}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 150 150\'%3E%3Crect width=\'150\' height=\'150\' fill=\'%23f1f5f9\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-family=\'sans-serif\' font-size=\'20\' font-weight=\'bold\' fill=\'%2394a3b8\'%3ENews%3C/text%3E%3C/svg%3E'">
                </div>
                <div class="overflow-hidden">
                    <div class="text-[9px] font-black text-blue-600 uppercase tracking-wider mb-1">{{ $info->informationCategory->name ?? 'Info' }}</div>
                    <div class="text-sm font-bold text-slate-900 leading-tight mb-2">{{ $info->title }}</div>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <i class="far fa-clock text-[10px]"></i>
                        <span class="text-[10px] font-medium">{{ \Carbon\Carbon::parse($info->created_at)->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Student Selector Bottom Sheet (Hidden) -->
<div id="student-selector" class="fixed inset-0 z-[100] flex items-end justify-center p-5 transform translate-y-full transition-transform duration-500 ease-in-out hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleStudentSelector()"></div>
    <div class="bg-white w-full max-w-sm rounded-[40px] p-8 relative z-10 shadow-2xl">
        <div class="w-16 h-1.5 bg-slate-100 rounded-full mx-auto mb-8"></div>
        <h3 class="text-xl font-black text-slate-900 mb-6 px-2">Pilih Santri</h3>
        <div class="space-y-3 max-h-[60vh] overflow-y-auto no-scrollbar pb-6">
            @foreach($students as $student)
            <a href="{{ route('wali.switch-student', $student->id) }}" class="flex items-center gap-4 p-4 rounded-3xl border-2 {{ $activeStudent && $activeStudent->id == $student->id ? 'border-blue-600 bg-blue-50' : 'border-slate-50 bg-slate-50/50' }} active:scale-[0.98] transition-all">
                <img src="{{ url($student->avatar ?: 'assets/media/avatars/default.png') }}" class="w-14 h-14 rounded-2xl object-cover" alt="">
                <div class="flex-1">
                    <div class="text-slate-900 font-bold leading-tight">{{ $student->name }}</div>
                    <div class="text-slate-500 text-xs font-medium">{{ $student->classroom->name ?? 'Tanpa Kelas' }}</div>
                </div>
                @if($activeStudent && $activeStudent->id == $student->id)
                    <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-[10px]">
                        <i class="fas fa-check"></i>
                    </div>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</div>

<script>
    function toggleStudentSelector() {
        const sheet = document.getElementById('student-selector');
        if (sheet.classList.contains('hidden')) {
            sheet.classList.remove('hidden');
            setTimeout(() => sheet.classList.remove('translate-y-full'), 50);
        } else {
            sheet.classList.add('translate-y-full');
            setTimeout(() => sheet.classList.add('hidden'), 500);
        }
    }
</script>
@endsection
