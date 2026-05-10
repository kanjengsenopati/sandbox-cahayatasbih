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
    <div class="card-premium flex items-center gap-4 mb-8 bg-blue-600 text-white border-0 shadow-lg shadow-blue-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <img src="{{ url($activeStudent->avatar ?: 'assets/media/avatars/default.png') }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white/20 relative z-10" alt="">
        <div class="relative z-10">
            <div class="text-[10px] font-black uppercase tracking-widest text-blue-100/60 mb-0.5">Nama Santri / Siswa</div>
            <div class="text-base font-bold leading-tight mb-1">{{ $activeStudent->name }}</div>
            <div class="text-[10px] font-bold text-blue-100/80">Kelas : {{ $activeStudent->classroom->name ?? '-' }}</div>
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
    <div class="space-y-5">
        @forelse($groupedBills as $group)
        <div class="card-premium p-6 border-l-4 border-l-blue-600">
            <div class="flex justify-between items-start mb-6">
                <div class="flex-1 pr-4">
                    <h3 class="text-sm font-black text-slate-900 uppercase leading-snug mb-1">{{ $group['name'] }}</h3>
                    <div class="text-lg font-black text-slate-900">Rp{{ number_format($group['total'], 0, ',', '.') }}</div>
                </div>
                <a href="{{ route('wali.bill-detail', $group['bill_type_id']) }}" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest active:scale-95 transition-transform shadow-md shadow-blue-100">Bayar</a>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-emerald-50 rounded-2xl p-3 border border-emerald-100/50">
                    <div class="text-[9px] font-black text-emerald-600/70 uppercase tracking-widest mb-1">Sudah Dibayarkan</div>
                    <div class="text-xs font-black text-emerald-600">Rp{{ number_format($group['paid'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-orange-50 rounded-2xl p-3 border border-orange-100/50">
                    <div class="text-[9px] font-black text-orange-600/70 uppercase tracking-widest mb-1">Kekurangan</div>
                    <div class="text-xs font-black text-orange-600">Rp{{ number_format($group['unpaid'], 0, ',', '.') }}</div>
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
