@extends('layouts.wali-pwa')

@section('content')
<div class="px-5 pt-10">
    <!-- Header & Greetings -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="text-caption italic mb-1">Assalamu'alaikum,</div>
            <h1 class="text-h1">{{ auth('wali')->user()->name }}</h1>
        </div>
        <div class="relative">
            <img src="{{ url(auth('wali')->user()->avatar ?: 'assets/media/avatars/default.png') }}" class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-lg" alt="">
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-600 rounded-full border-2 border-white"></div>
        </div>
    </div>

    <!-- Wallet / Balance Card (Fintech Style) -->
    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 mb-8 text-white shadow-xl shadow-blue-100 relative overflow-hidden">
        <!-- Abstract patterns -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-400/20 rounded-full -ml-12 -mb-12 blur-xl"></div>
        
        <div class="flex items-center gap-3 mb-6 relative z-10">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="text-label text-white/70">Total Saldo Terkelola</div>
        </div>
        
        <div class="mb-6 relative z-10">
            @php
                $totalSaldo = $students->sum('saldo');
                $totalTabungan = $students->sum('saving');
            @endphp
            <div class="text-3xl font-bold mb-1">Rp {{ number_format($totalSaldo + $totalTabungan, 0, ',', '.') }}</div>
            <div class="text-xs text-white/60">Data gabungan dari {{ $students->count() }} santri</div>
        </div>

        <div class="grid grid-cols-2 gap-4 relative z-10">
            <div class="bg-white/10 rounded-2xl p-3 border border-white/10">
                <div class="text-[10px] uppercase font-bold text-white/60 mb-1">Uang Saku</div>
                <div class="font-bold text-sm">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white/10 rounded-2xl p-3 border border-white/10">
                <div class="text-[10px] uppercase font-bold text-white/60 mb-1">Tabungan</div>
                <div class="font-bold text-sm">Rp {{ number_format($totalTabungan, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Student Selector (Fintech Cards) -->
    <div class="mb-8">
        <div class="flex justify-between items-end mb-4">
            <div class="text-h2">Anak Saya</div>
            <a href="#" class="text-xs font-bold text-blue-600">Lihat Semua</a>
        </div>
        
        <div class="flex gap-4 overflow-x-auto pb-4 -mx-5 px-5 scrollbar-hide">
            @forelse($students as $student)
            <div class="min-w-[280px] card-premium flex gap-4 items-center border border-slate-50">
                <img src="{{ url($student->avatar ?: 'assets/media/avatars/default.png') }}" class="w-14 h-14 rounded-2xl object-cover" alt="">
                <div class="flex-1">
                    <div class="text-h2 truncate">{{ $student->name }}</div>
                    <div class="text-caption text-slate-500 mb-2">{{ $student->classroom->name ?? '-' }}</div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase">Rp {{ number_format($student->saldo, 0, ',', '.') }}</span>
                        @if($student->is_blocked)
                            <span class="px-2 py-0.5 rounded-lg bg-red-100 text-red-600 text-[10px] font-bold uppercase">Terblokir</span>
                        @endif
                    </div>
                </div>
                <div class="text-slate-300">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            @empty
            <div class="w-full card-premium text-center py-8">
                <div class="text-slate-300 text-4xl mb-3"><i class="fas fa-user-friends"></i></div>
                <div class="text-body">Belum ada data santri tertaut</div>
                <div class="text-caption mt-1">Silakan hubungi admin sekolah</div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Action Grid -->
    <div class="mb-8">
        <div class="text-h2 mb-4">Layanan Cepat</div>
        <div class="grid grid-cols-4 gap-4">
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-600 text-center uppercase tracking-tighter">Top Up</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-600 text-center uppercase tracking-tighter">Tagihan</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-600 text-center uppercase tracking-tighter">Atur Limit</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-book-reader"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-600 text-center uppercase tracking-tighter">Akademik</span>
            </div>
        </div>
    </div>

    <!-- Information Feed -->
    <div class="mb-8">
        <div class="text-h2 mb-4">Informasi Terbaru</div>
        <div class="space-y-4">
            @foreach($informations as $info)
            <div class="card-premium flex gap-4 p-4 items-center">
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex-shrink-0 overflow-hidden">
                    <img src="{{ asset('storage/' . $info->image) }}" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/100?text=Info'">
                </div>
                <div class="overflow-hidden">
                    <div class="text-[10px] font-bold text-blue-600 uppercase mb-0.5">{{ $info->informationCategory->name ?? 'Info' }}</div>
                    <div class="text-sm font-bold text-slate-800 truncate">{{ $info->title }}</div>
                    <div class="text-[10px] text-slate-400 italic mt-0.5">{{ \Carbon\Carbon::parse($info->created_at)->translatedFormat('d M Y') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
