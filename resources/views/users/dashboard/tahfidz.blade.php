@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Progres Tahfidz</h1>
    </div>

    <!-- Summary Card -->
    <div class="card-premium p-6 mb-8 bg-emerald-600 text-white border-0 shadow-lg shadow-emerald-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <div class="relative z-10">
            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100/60 mb-2">Total Hafalan</div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black tabular-nums">{{ $totalPage }}</span>
                <span class="text-emerald-100/80 font-bold uppercase text-xs tracking-widest">Halaman</span>
            </div>
            
            <div class="mt-6 flex gap-4">
                <div class="bg-white/10 rounded-2xl px-4 py-3 flex-1 backdrop-blur-md border border-white/10">
                    <div class="text-[9px] font-black text-emerald-100/50 uppercase tracking-wider mb-1">Setoran Terakhir</div>
                    <div class="text-sm font-bold">{{ $tahfidzHistory->first()?->deposit_date ? \Carbon\Carbon::parse($tahfidzHistory->first()->deposit_date)->translatedFormat('d M Y') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- History List -->
    <div class="space-y-4">
        <div class="flex justify-between items-center mb-4 px-1">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Riwayat Setoran</h3>
            <span class="text-[10px] font-bold text-slate-400">{{ $tahfidzHistory->count() }} Data</span>
        </div>

        @forelse($tahfidzHistory as $item)
        <div class="card-premium p-5 flex items-center gap-5 border-l-4 border-l-emerald-500 shadow-sm active:scale-[0.98] transition-transform">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-star"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ \Carbon\Carbon::parse($item->deposit_date)->translatedFormat('l, d F Y') }}</div>
                <div class="text-sm font-black text-slate-900 truncate">Selesai Setoran Hafalan</div>
                <div class="text-[11px] font-medium text-slate-500 mt-1 italic">{{ $item->note ?: 'Tidak ada catatan' }}</div>
            </div>
            <div class="text-right flex-shrink-0">
                <div class="text-lg font-black text-emerald-600 tabular-nums">+{{ $item->number_of_pages }}</div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Hal</div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full mx-auto flex items-center justify-center text-2xl mb-5">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 mb-1">Belum Ada Setoran</h3>
            <p class="text-slate-400 text-[12px] font-medium px-8">Data setoran tahfidz ananda akan muncul di sini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
