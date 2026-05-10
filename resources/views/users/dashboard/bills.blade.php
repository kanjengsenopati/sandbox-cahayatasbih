@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Tagihan Santri</h1>
    </div>

    <!-- Active Student Summary -->
    <div class="card-premium flex items-center gap-4 mb-6 bg-blue-600 text-white border-0 shadow-lg shadow-blue-100 relative overflow-hidden p-5">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <div class="w-12 h-12 rounded-2xl bg-white/20 border border-white/20 flex items-center justify-center text-xl relative z-10">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="relative z-10 flex-1">
            <div class="text-[9px] font-black uppercase tracking-widest text-blue-100/60 mb-0.5">Nama Santri / Siswa</div>
            <div class="text-base font-black leading-tight">{{ $activeStudent->name }}</div>
            <div class="text-[9px] font-bold text-blue-100/80 mt-0.5">Kelas : {{ $activeStudent->classroom->name ?? '-' }}</div>
        </div>
    </div>

    <!-- Tab Selector (Optional, but images show Tagihan/Riwayat) -->
    <div class="flex border-b border-slate-100 mb-6">
        <button class="flex-1 py-3 text-sm font-black text-blue-600 border-b-2 border-blue-600 uppercase tracking-widest">Tagihan</button>
        <a href="{{ route('wali.history') }}" class="flex-1 py-3 text-sm font-bold text-slate-400 uppercase tracking-widest text-center">Riwayat</a>
    </div>

    <!-- Search -->
    <div class="relative mb-8">
        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <input type="text" placeholder="Cari Data" class="w-full bg-slate-100 border-0 rounded-2xl py-4 pl-12 pr-5 text-sm font-medium focus:ring-2 focus:ring-blue-600/20 transition-all">
    </div>

    <!-- Grouped Bill List -->
    <div class="space-y-3">
        @forelse($groupedBills as $group)
        <div class="card-premium p-4 border-l-4 border-l-blue-600 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <div class="flex-1 pr-4">
                    <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-tight mb-0.5">{{ $group['name'] }}</h3>
                    <div class="text-[19px] font-black text-slate-900 tracking-tight leading-none">Rp{{ number_format($group['total'], 0, ',', '.') }}</div>
                </div>
                <a href="{{ route('wali.bill-detail', $group['bill_type_id']) }}" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-transform shadow-md shadow-blue-200">Bayar</a>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-emerald-50/50 rounded-xl p-2.5 border border-emerald-100/40">
                    <div class="text-[8px] font-black text-emerald-600/70 uppercase tracking-wider mb-1">Sudah Dibayar</div>
                    <div class="text-[11px] font-black text-emerald-600">Rp{{ number_format($group['paid'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-orange-50/50 rounded-xl p-2.5 border border-orange-100/40">
                    <div class="text-[8px] font-black text-orange-600/70 uppercase tracking-wider mb-1">Kekurangan</div>
                    <div class="text-[11px] font-black text-orange-600">Rp{{ number_format($group['unpaid'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-full mx-auto flex items-center justify-center text-3xl mb-6">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Semua Lunas!</h3>
            <p class="text-slate-400 text-sm font-medium px-10">Tidak ada tagihan tertunggak untuk santri ini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
