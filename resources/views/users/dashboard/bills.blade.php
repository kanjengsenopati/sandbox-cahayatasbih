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
        <button id="tab-tagihan" onclick="switchTab('tagihan')" class="flex-1 py-3 text-[13px] font-black uppercase tracking-widest text-center transition-all border-b-2 text-blue-600 border-blue-600">
            Tagihan
            @if($unpaidBills->count() > 0)
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-[9px] font-black rounded-full">{{ $unpaidBills->count() }}</span>
            @endif
        </button>
        <button id="tab-lunas" onclick="switchTab('lunas')" class="flex-1 py-3 text-[13px] font-bold uppercase tracking-widest text-center transition-all border-b-2 text-slate-400 border-transparent">
            Lunas
            @if($paidBills->count() > 0)
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 bg-emerald-500 text-white text-[9px] font-black rounded-full">{{ $paidBills->count() }}</span>
            @endif
        </button>
    </div>

    <!-- Search -->
    <div class="relative mb-5">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="search-bills" placeholder="Cari Data" class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3.5 pl-11 pr-5 text-[13px] font-medium focus:ring-2 focus:ring-blue-600/20 focus:border-blue-200 transition-all" oninput="filterBills()">
    </div>

    <!-- ========== TAB TAGIHAN (Belum Lunas) ========== -->
    <div id="panel-tagihan" class="space-y-3">
        @forelse($unpaidBills as $group)
        <div class="bill-card card-premium p-5 border-l-4 border-l-blue-600 shadow-sm" data-name="{{ strtolower($group['bill_type_name'] . ' ' . $group['academic_year']) }}">
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
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full mx-auto flex items-center justify-center text-2xl mb-5">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 mb-1">Semua Lunas!</h3>
            <p class="text-slate-400 text-[12px] font-medium px-8">Tidak ada tagihan tertunggak untuk santri ini.</p>
        </div>
        @endforelse
    </div>

    <!-- ========== TAB LUNAS ========== -->
    <div id="panel-lunas" class="space-y-3 hidden">
        @forelse($paidBills as $group)
        <div class="bill-card card-premium p-5 border-l-4 border-l-emerald-500 shadow-sm" data-name="{{ strtolower($group['bill_type_name'] . ' ' . $group['academic_year']) }}">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 pr-3">
                    <h3 class="text-[13px] font-black text-slate-900 uppercase leading-snug tracking-tight">{{ $group['bill_type_name'] }}</h3>
                    <div class="text-[10px] font-bold text-slate-400 tracking-wide mt-0.5">{{ $group['academic_year'] }}</div>
                    <div class="text-[20px] font-black text-slate-900 tracking-tight leading-none mt-2 tabular-nums">Rp{{ number_format($group['total'], 0, ',', '.') }}</div>
                </div>
                <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 px-4 py-2 rounded-[14px] text-[10px] font-black uppercase tracking-widest mt-1">
                    <i class="fas fa-check-circle text-[8px]"></i> Lunas
                </span>
            </div>
            
            <div class="flex gap-3 mt-3">
                <div class="flex-1 bg-emerald-50/60 rounded-[14px] py-2.5 px-3 border border-emerald-100/40">
                    <div class="text-[8px] font-black text-emerald-600/60 uppercase tracking-wider mb-0.5">Total Dibayar</div>
                    <div class="text-[12px] font-black text-emerald-600 tabular-nums">Rp{{ number_format($group['paid'], 0, ',', '.') }}</div>
                </div>
                <div class="flex-1 bg-slate-50/60 rounded-[14px] py-2.5 px-3 border border-slate-100/40">
                    <div class="text-[8px] font-black text-slate-400/60 uppercase tracking-wider mb-0.5">Kekurangan</div>
                    <div class="text-[12px] font-black text-slate-400 tabular-nums">Rp0</div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full mx-auto flex items-center justify-center text-2xl mb-5">
                <i class="fas fa-receipt"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 mb-1">Belum Ada Tagihan Lunas</h3>
            <p class="text-slate-400 text-[12px] font-medium px-8">Tagihan yang sudah lunas akan muncul di sini.</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function switchTab(tab) {
    const tabTagihan = document.getElementById('tab-tagihan');
    const tabLunas = document.getElementById('tab-lunas');
    const panelTagihan = document.getElementById('panel-tagihan');
    const panelLunas = document.getElementById('panel-lunas');

    if (tab === 'tagihan') {
        tabTagihan.className = 'flex-1 py-3 text-[13px] font-black uppercase tracking-widest text-center transition-all border-b-2 text-blue-600 border-blue-600';
        tabLunas.className = 'flex-1 py-3 text-[13px] font-bold uppercase tracking-widest text-center transition-all border-b-2 text-slate-400 border-transparent';
        panelTagihan.classList.remove('hidden');
        panelLunas.classList.add('hidden');
    } else {
        tabLunas.className = 'flex-1 py-3 text-[13px] font-black uppercase tracking-widest text-center transition-all border-b-2 text-emerald-600 border-emerald-600';
        tabTagihan.className = 'flex-1 py-3 text-[13px] font-bold uppercase tracking-widest text-center transition-all border-b-2 text-slate-400 border-transparent';
        panelLunas.classList.remove('hidden');
        panelTagihan.classList.add('hidden');
    }

    // Reset search
    document.getElementById('search-bills').value = '';
    filterBills();
}

function filterBills() {
    const q = document.getElementById('search-bills').value.toLowerCase();
    document.querySelectorAll('.bill-card').forEach(card => {
        if (card.closest('.hidden')) return;
        card.style.display = card.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
