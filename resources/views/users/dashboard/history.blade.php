@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Riwayat Transaksi</h1>
    </div>

    <!-- Tab Selector -->
    <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-8">
        <button onclick="switchTab('saldo')" id="tab-saldo" class="flex-1 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all bg-white shadow-sm text-blue-600">Saldo Saku</button>
        <button onclick="switchTab('saving')" id="tab-saving" class="flex-1 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-500">Tabungan</button>
    </div>

    <!-- Saldo History -->
    <div id="content-saldo" class="space-y-4">
        @forelse($saldoHistories as $history)
        <div class="card-premium flex items-center gap-4 p-4">
            <div class="w-12 h-12 {{ $history->type == 'IN' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} rounded-2xl flex items-center justify-center text-lg">
                <i class="fas {{ $history->type == 'IN' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
            </div>
            <div class="flex-1 overflow-hidden">
                <div class="text-sm font-bold text-slate-900 truncate">{{ $history->description ?: ($history->type == 'IN' ? 'Top Up Saldo' : 'Pengeluaran') }}</div>
                <div class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($history->created_at)->translatedFormat('d M Y, H:i') }}</div>
            </div>
            <div class="text-right">
                <div class="text-sm font-black {{ $history->type == 'IN' ? 'text-emerald-600' : 'text-slate-900' }}">
                    {{ $history->type == 'IN' ? '+' : '-' }}Rp{{ number_format($history->amount, 0, ',', '.') }}
                </div>
                <div class="text-[9px] font-bold uppercase tracking-widest {{ $history->status == 'SUCCESS' ? 'text-emerald-500' : ($history->status == 'PENDING' ? 'text-orange-500' : 'text-red-500') }}">
                    {{ $history->status }}
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <i class="fas fa-receipt text-4xl text-slate-200 mb-4"></i>
            <p class="text-slate-400 text-sm font-medium">Belum ada riwayat saldo saku</p>
        </div>
        @endforelse
    </div>

    <!-- Saving History (Hidden by default) -->
    <div id="content-saving" class="space-y-4 hidden">
        @forelse($savingHistories as $history)
        <div class="card-premium flex items-center gap-4 p-4">
            <div class="w-12 h-12 {{ $history->type == 'IN' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} rounded-2xl flex items-center justify-center text-lg">
                <i class="fas {{ $history->type == 'IN' ? 'fa-piggy-bank' : 'fa-hand-holding-usd' }}"></i>
            </div>
            <div class="flex-1 overflow-hidden">
                <div class="text-sm font-bold text-slate-900 truncate">{{ $history->description ?: ($history->type == 'IN' ? 'Setoran Tabungan' : 'Penarikan Tabungan') }}</div>
                <div class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($history->created_at)->translatedFormat('d M Y, H:i') }}</div>
            </div>
            <div class="text-right">
                <div class="text-sm font-black {{ $history->type == 'IN' ? 'text-emerald-600' : 'text-slate-900' }}">
                    {{ $history->type == 'IN' ? '+' : '-' }}Rp{{ number_format($history->amount, 0, ',', '.') }}
                </div>
                <div class="text-[9px] font-bold uppercase tracking-widest text-emerald-500">
                    {{ $history->status }}
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <i class="fas fa-piggy-bank text-4xl text-slate-200 mb-4"></i>
            <p class="text-slate-400 text-sm font-medium">Belum ada riwayat tabungan</p>
        </div>
        @endforelse
    </div>
</div>

<script>
    function switchTab(type) {
        const saldoBtn = document.getElementById('tab-saldo');
        const savingBtn = document.getElementById('tab-saving');
        const saldoContent = document.getElementById('content-saldo');
        const savingContent = document.getElementById('content-saving');

        if (type === 'saldo') {
            saldoBtn.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
            saldoBtn.classList.remove('text-slate-500');
            savingBtn.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
            savingBtn.classList.add('text-slate-500');
            saldoContent.classList.remove('hidden');
            savingContent.classList.add('hidden');
        } else {
            savingBtn.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
            savingBtn.classList.remove('text-slate-500');
            saldoBtn.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
            saldoBtn.classList.add('text-slate-500');
            savingContent.classList.remove('hidden');
            saldoContent.classList.add('hidden');
        }
    }
</script>
@endsection
