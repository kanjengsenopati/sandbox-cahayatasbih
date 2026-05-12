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
    <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-8 overflow-x-auto no-scrollbar">
        <button onclick="switchTab('saldo')" id="tab-saldo" class="flex-none px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white shadow-sm text-blue-600">Saldo</button>
        <button onclick="switchTab('saving')" id="tab-saving" class="flex-none px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500">Tabungan</button>
        <button onclick="switchTab('bill')" id="tab-bill" class="flex-none px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500">Tagihan</button>
        <button onclick="switchTab('pos')" id="tab-pos" class="flex-none px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500">Belanja</button>
    </div>

    <!-- Saldo History -->
    <div id="content-saldo" class="space-y-4">
        <!-- Filter Form -->
        <form action="{{ route('wali.history') }}" method="GET" class="mb-6" id="filter-form">
            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2">
                <button type="submit" name="filter" value="today" class="flex-none px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter == 'today' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-500 border border-slate-100' }}">Hari Ini</button>
                <button type="submit" name="filter" value="this_week" class="flex-none px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter == 'this_week' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-500 border border-slate-100' }}">Minggu Ini</button>
                <button type="submit" name="filter" value="this_month" class="flex-none px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter == 'this_month' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-500 border border-slate-100' }}">Bulan Ini</button>
                <button type="button" onclick="document.getElementById('custom-filter').classList.toggle('hidden')" class="flex-none px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter == 'custom' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-500 border border-slate-100' }}">Pilih Sendiri</button>
            </div>
            
            <div id="custom-filter" class="{{ $filter == 'custom' ? '' : 'hidden' }} mt-3 card-premium p-4 border border-slate-100">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Sampai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>
                <button type="submit" name="filter" value="custom" class="w-full bg-blue-50 text-blue-600 rounded-xl py-2 text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-colors">Terapkan Filter</button>
            </div>
        </form>

        <!-- Summary -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="col-span-2 card-premium p-5 bg-gradient-to-br from-blue-600 to-blue-800 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <div class="text-[10px] font-black text-blue-200 uppercase tracking-widest mb-1">Total Saldo Saku</div>
                <div class="text-3xl font-black tabular-nums tracking-tighter">Rp{{ number_format($totalSaldo, 0, ',', '.') }}</div>
            </div>
            <div class="card-premium p-4 border border-slate-50">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-500 text-[10px]"><i class="fas fa-arrow-down"></i></div>
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pemasukan</div>
                </div>
                <div class="text-sm font-black text-slate-900 tracking-tighter">Rp{{ number_format($saldoIn, 0, ',', '.') }}</div>
            </div>
            <div class="card-premium p-4 border border-slate-50">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 bg-rose-50 rounded-lg flex items-center justify-center text-rose-500 text-[10px]"><i class="fas fa-arrow-up"></i></div>
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pengeluaran</div>
                </div>
                <div class="text-sm font-black text-slate-900 tracking-tighter">Rp{{ number_format($saldoOut, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($saldoHistories as $history)
            <div class="card-premium overflow-hidden transition-all duration-300 {{ $history->pointOfSaleTransaction ? 'cursor-pointer active:scale-[0.98]' : '' }}" {!! $history->pointOfSaleTransaction ? 'onclick="togglePanel(\'panel-'.$history->id.'\')"' : '' !!}>
                <div class="flex items-center gap-4 p-4">
                    <div class="w-12 h-12 {{ $history->type == 'IN' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} rounded-2xl flex-shrink-0 flex items-center justify-center text-lg">
                        <i class="fas {{ $history->type == 'IN' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="text-[13px] font-black text-slate-900 truncate tracking-tight">{{ $history->description ?: ($history->type == 'IN' ? 'Top Up Saldo' : 'Pengeluaran') }}</div>
                        <div class="text-[10px] text-slate-400 font-bold mt-0.5">{{ \Carbon\Carbon::parse($history->created_at)->translatedFormat('d M Y, H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black tracking-tighter {{ $history->type == 'IN' ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $history->type == 'IN' ? '+' : '-' }}Rp{{ number_format($history->amount, 0, ',', '.') }}
                        </div>
                        <div class="text-[9px] font-black uppercase tracking-widest mt-0.5 {{ $history->status == 'SUCCESS' ? 'text-emerald-500' : ($history->status == 'PENDING' ? 'text-orange-500' : 'text-rose-500') }}">
                            {{ $history->status }}
                        </div>
                    </div>
                </div>
                
                @if($history->pointOfSaleTransaction)
                <div id="panel-{{ $history->id }}" class="hidden bg-slate-50/50 border-t border-slate-100 p-4">
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail Belanja</div>
                        <div class="text-[10px] font-bold text-slate-500">Kasir: {{ $history->pointOfSaleTransaction->admins->name ?? 'Administrator' }}</div>
                    </div>
                    <div class="space-y-3">
                        @foreach($history->pointOfSaleTransaction->pointOfSaleTransactionDetails as $detail)
                        <div class="flex justify-between items-start">
                            <div class="flex gap-3">
                                <div class="w-7 h-7 bg-white rounded-lg border border-slate-100 flex items-center justify-center text-slate-400 text-[10px] font-black shadow-sm">{{ $detail->quantity }}x</div>
                                <div>
                                    <div class="text-[12px] font-black text-slate-800 tracking-tight leading-tight">{{ $detail->item->name ?? 'Produk' }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 mt-0.5">@Rp{{ number_format($detail->price, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="text-[12px] font-black text-slate-900 tracking-tighter">Rp{{ number_format($detail->total, 0, ',', '.') }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-20">
                <i class="fas fa-receipt text-4xl text-slate-200 mb-4"></i>
                <p class="text-slate-400 text-sm font-medium">Belum ada riwayat saldo saku</p>
            </div>
            @endforelse
            
            @if($saldoHistories->hasPages())
            <div class="mt-6">
                {{ $saldoHistories->links('pagination::tailwind') }}
            </div>
            @endif
        </div>
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

    <!-- Bill History (Hidden by default) -->
    <div id="content-bill" class="space-y-4 hidden">
        @forelse($billTransactions as $transaction)
        <div class="card-premium flex items-center gap-4 p-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-lg">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="flex-1 overflow-hidden">
                <div class="text-sm font-bold text-slate-900 truncate">Pembayaran Tagihan</div>
                <div class="text-[10px] text-slate-400 font-medium">{{ $transaction->payment_code }} • {{ \Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d M Y, H:i') }}</div>
            </div>
            <div class="text-right">
                <div class="text-sm font-black text-slate-900">
                    Rp{{ number_format($transaction->pay_amount, 0, ',', '.') }}
                </div>
                <div class="text-[9px] font-black uppercase tracking-widest {{ $transaction->status == 'PAID' ? 'text-emerald-600' : ($transaction->status == 'PENDING_PAYMENT' ? 'text-orange-600' : 'text-slate-400') }}">
                    {{ $transaction->status }}
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <i class="fas fa-file-invoice-dollar text-4xl text-slate-200 mb-4"></i>
            <p class="text-slate-400 text-sm font-medium">Belum ada riwayat pembayaran</p>
        </div>
        @endforelse
    </div>

    <!-- PoS History (Hidden by default) -->
    <div id="content-pos" class="space-y-6 hidden">
        @forelse($posTransactions as $pos)
        <div class="bg-white rounded-[24px] shadow-premium p-0 border border-slate-50 overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Digital Receipt</div>
                <div class="text-[10px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-500/10 px-2 py-1 rounded-md">Paid</div>
            </div>
            
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ \Carbon\Carbon::parse($pos->created_at)->translatedFormat('d M Y, H:i') }}</div>
                        <div class="text-base font-black text-slate-900 tracking-tight">{{ $pos->payment_code }}</div>
                        <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Kasir: {{ $pos->admins->name ?? 'Administrator' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Belanja</div>
                        <div class="text-xl font-black text-slate-900 tabular-nums leading-none">Rp{{ number_format($pos->pay_amount, 0, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="border-t border-dashed border-slate-200 pt-6 space-y-4">
                    @foreach($pos->pointOfSaleTransactionDetails as $detail)
                    <div class="flex justify-between items-start">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center text-slate-400 text-xs font-black">{{ $detail->quantity }}x</div>
                            <div>
                                <div class="text-[13px] font-black text-slate-800 leading-tight">{{ $detail->item->name ?? 'Produk' }}</div>
                                <div class="text-[10px] font-bold text-slate-400 mt-0.5">@Rp{{ number_format($detail->price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="text-[13px] font-black text-slate-900 tabular-nums">Rp{{ number_format($detail->total, 0, ',', '.') }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-center">
                    <div class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em]">Terima Kasih</div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full mx-auto flex items-center justify-center text-3xl mb-6">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Belum Ada Belanja</h3>
            <p class="text-slate-400 text-sm font-medium px-10">Riwayat belanja ananda di kantin atau toko sekolah akan muncul di sini.</p>
        </div>
        @endforelse
    </div>
</div>

<script>
    function switchTab(type) {
        const tabs = ['saldo', 'saving', 'bill', 'pos'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-' + t);
            const content = document.getElementById('content-' + t);
            if (t === type) {
                btn.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
                btn.classList.remove('text-slate-500');
                content.classList.remove('hidden');
            } else {
                btn.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                btn.classList.add('text-slate-500');
                content.classList.add('hidden');
            }
        });

        const url = new URL(window.location);
        url.searchParams.set('tab', type);
        window.history.pushState({}, '', url);
    }

    function togglePanel(id) {
        const panel = document.getElementById(id);
        if (panel) {
            panel.classList.toggle('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'saldo';
        switchTab(activeTab);
    });
</script>
@endsection
