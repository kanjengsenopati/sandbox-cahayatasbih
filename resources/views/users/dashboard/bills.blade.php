@extends('layouts.wali-pwa')

@section('content')
<div class="px-5 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
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

    <!-- Tabs -->
    <div class="flex border-b border-slate-100 mb-5">
        <button class="flex-1 py-3 text-[13px] font-black text-blue-600 border-b-2 border-blue-600 uppercase tracking-widest">Tagihan</button>
        <a href="{{ route('wali.history') }}" class="flex-1 py-3 text-[13px] font-bold text-slate-400 uppercase tracking-widest text-center">Riwayat</a>
    </div>

    <!-- Search -->
    <div class="relative mb-5">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="search-bills" placeholder="Cari Data" class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3.5 pl-11 pr-5 text-[13px] font-medium focus:ring-2 focus:ring-blue-600/20 focus:border-blue-200 transition-all" oninput="filterBills()">
    </div>

    <!-- Grouped Bill List -->
    <div class="space-y-3" id="bill-list">
        @forelse($groupedBills as $group)
        <div class="bill-card card-premium p-5 border-l-4 {{ $group['unpaid'] > 0 ? 'border-l-blue-600' : 'border-l-emerald-500' }} shadow-sm" data-name="{{ strtolower($group['bill_type_name'] . ' ' . $group['academic_year']) }}">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 pr-3">
                    <h3 class="text-[13px] font-black text-slate-900 uppercase leading-snug tracking-tight">{{ $group['bill_type_name'] }}</h3>
                    <div class="text-[10px] font-bold text-slate-400 tracking-wide mt-0.5">{{ $group['academic_year'] }}</div>
                    <div class="text-[20px] font-black text-slate-900 tracking-tight leading-none mt-2 tabular-nums">Rp{{ number_format($group['total'], 0, ',', '.') }}</div>
                </div>
                <a href="{{ route('wali.bill-detail', $group['bill_type_id']) }}" class="bg-blue-600 text-white px-5 py-2.5 rounded-[14px] text-[10px] font-black uppercase tracking-widest active:scale-95 transition-transform shadow-md shadow-blue-200 mt-1">Bayar</a>
            </div>
            
            <div class="flex gap-3 mt-3">
                <div class="flex-1 bg-emerald-50/60 rounded-[14px] py-2.5 px-3 border border-emerald-100/40">
                    <div class="text-[8px] font-black text-emerald-600/60 uppercase tracking-wider mb-0.5">Sudah Dibayarkan</div>
                    <div class="text-[12px] font-black text-emerald-600 tabular-nums">Rp{{ number_format($group['paid'], 0, ',', '.') }}</div>
                </div>
                <div class="flex-1 bg-orange-50/60 rounded-[14px] py-2.5 px-3 border border-orange-100/40">
                    <div class="text-[8px] font-black text-orange-600/60 uppercase tracking-wider mb-0.5">Kekurangan</div>
                    <div class="text-[12px] font-black text-orange-600 tabular-nums">Rp{{ number_format($group['unpaid'], 0, ',', '.') }}</div>
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

<script>
function filterBills() {
    const q = document.getElementById('search-bills').value.toLowerCase();
    document.querySelectorAll('.bill-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
