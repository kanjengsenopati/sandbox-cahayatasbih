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
    <div class="card-premium flex items-center gap-4 mb-8 bg-blue-600 text-white border-0 shadow-lg shadow-blue-100">
        <img src="{{ url($activeStudent->avatar ?: 'assets/media/avatars/default.png') }}" class="w-12 h-12 rounded-xl object-cover border-2 border-white/20" alt="">
        <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-blue-100/60 mb-0.5">Menampilkan Tagihan</div>
            <div class="text-base font-bold">{{ $activeStudent->name }}</div>
        </div>
    </div>

    <!-- Bill List -->
    <div class="space-y-4">
        @forelse($bills as $bill)
        <div class="card-premium p-5 border-l-4 border-l-orange-400">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1 overflow-hidden">
                    <div class="text-[9px] font-black uppercase tracking-widest text-blue-600 mb-1">{{ $bill->billType->billItem->name }}</div>
                    <div class="text-base font-extrabold text-slate-900 truncate">{{ $bill->translated_month }} {{ $bill->year }}</div>
                    <div class="text-[10px] text-slate-400 font-bold mt-1">TA: {{ $bill->billType->academicYear->year }}</div>
                </div>
                <div class="text-right">
                    <div class="text-base font-black text-slate-900">Rp{{ number_format($bill->amount, 0, ',', '.') }}</div>
                    <div class="text-[9px] font-bold uppercase tracking-widest text-orange-500 mt-1">Belum Lunas</div>
                </div>
            </div>
            
            <div class="h-[1px] bg-slate-50 mb-4"></div>
            
            <div class="flex justify-between items-center">
                <div class="text-[10px] text-slate-400 font-medium">
                    <i class="far fa-calendar-alt mr-1"></i>
                    Periode: {{ $bill->translated_month }} {{ $bill->year }}
                </div>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-transform">Bayar</button>
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

    <!-- Total Summary Floating Card -->
    @if($bills->count() > 0)
    <div class="fixed bottom-24 left-6 right-6 z-40">
        <div class="bg-slate-900 rounded-3xl p-5 shadow-2xl flex justify-between items-center">
            <div>
                <div class="text-slate-400 text-[9px] font-black uppercase tracking-widest mb-1">Total Belum Bayar</div>
                <div class="text-white text-lg font-black">Rp{{ number_format($bills->sum('amount'), 0, ',', '.') }}</div>
            </div>
            <button class="btn-primary py-3 !shadow-none">Bayar Semua</button>
        </div>
    </div>
    @endif
</div>
@endsection
