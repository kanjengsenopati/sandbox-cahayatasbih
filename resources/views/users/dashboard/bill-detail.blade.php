@extends('layouts.wali-pwa')

@section('content')
<div class="pt-12 pb-24">
    <!-- Header -->
    <div class="px-6 flex items-center gap-4 mb-8">
        <a href="{{ route('wali.bills') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Detail Tagihan</h1>
    </div>

    <!-- Student Info Card -->
    <div class="px-6 mb-8">
        <div class="card-premium flex items-center gap-4 bg-blue-600 text-white border-0 shadow-lg shadow-blue-100 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <img src="{{ url($activeStudent->avatar ?: 'assets/media/avatars/default.png') }}" class="w-12 h-12 rounded-xl object-cover border-2 border-white/20 relative z-10" alt="">
            <div class="relative z-10">
                <div class="text-[9px] font-black uppercase tracking-widest text-blue-100/60 mb-0.5">Nama Siswa / Santri</div>
                <div class="text-sm font-bold leading-tight">{{ $activeStudent->name }}</div>
                <div class="text-[9px] font-bold text-blue-100/80">Kelas : {{ $activeStudent->classroom->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="px-6 mb-8">
        <h2 class="text-base font-black text-slate-900 uppercase tracking-tight mb-4">{{ $billType->billItem->name }} - {{ $billType->academicYear->year }}</h2>
        <div class="flex justify-between items-end mb-6">
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Total Tagihan</div>
                <div class="text-xl font-black text-slate-900">Rp{{ number_format($summary['total'], 0, ',', '.') }}</div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Status</div>
                @if($summary['unpaid'] == 0)
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-[9px] font-black uppercase">Lunas</span>
                @else
                    <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-lg text-[9px] font-black uppercase">Proses Bayar</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Sudah Bayar</div>
                <div class="text-sm font-black text-slate-900">Rp{{ number_format($summary['paid'], 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Belum Bayar</div>
                <div class="text-sm font-black text-red-600">Rp{{ number_format($summary['unpaid'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="h-2 bg-slate-100 mb-8"></div>

    <!-- Itemized List -->
    <form action="{{ route('wali.checkout') }}" method="POST">
        @csrf
        <div class="px-6 mb-4">
            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" id="check-all" class="w-5 h-5 rounded-md border-slate-300 text-blue-600 focus:ring-blue-600 transition-all">
                <span class="text-sm font-black text-slate-900 uppercase tracking-widest">Bayar Semua</span>
            </label>
        </div>

        <div class="px-6 space-y-4">
            @foreach($bills as $bill)
            <div class="card-premium !p-4 flex items-center gap-4 border-l-4 {{ $bill->status == 'PAID' ? 'border-l-emerald-500' : 'border-l-blue-600' }}">
                @if($bill->status == 'UNPAID')
                    <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="bill-checkbox w-5 h-5 rounded-md border-slate-300 text-blue-600 focus:ring-blue-600 transition-all">
                @else
                    <div class="w-5 h-5 flex items-center justify-center text-emerald-500">
                        <i class="fas fa-check-circle"></i>
                    </div>
                @endif
                
                <div class="flex-1">
                    <div class="text-[10px] font-bold text-slate-400 mb-0.5">Rp{{ number_format($bill->amount, 0, ',', '.') }}</div>
                    <div class="text-sm font-black text-slate-900">Tagihan {{ $bill->translated_month }} {{ $bill->year }}</div>
                </div>

                @if($bill->status == 'PAID')
                    <span class="bg-emerald-500 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest">Lunas</span>
                @else
                    <div class="text-right">
                        <button type="button" class="bg-blue-600 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest mb-1">Bayar</button>
                        <div class="text-[9px] font-bold text-red-600 uppercase tracking-tighter">Belum Bayar</div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Floating Footer for Checkout -->
        <div id="checkout-footer" class="fixed bottom-24 left-6 right-6 z-40 hidden transform translate-y-10 opacity-0 transition-all duration-500">
            <div class="bg-slate-900 rounded-[32px] p-6 shadow-2xl flex justify-between items-center">
                <div>
                    <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Selected Total</div>
                    <div id="selected-total" class="text-white text-lg font-black tabular-nums">Rp0</div>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-8 py-4 rounded-2xl text-sm font-black uppercase tracking-widest active:scale-95 transition-all shadow-lg shadow-blue-500/20">Lanjutkan</button>
            </div>
        </div>
    </form>
</div>

<script>
    const checkAll = document.getElementById('check-all');
    const billCheckboxes = document.querySelectorAll('.bill-checkbox');
    const checkoutFooter = document.getElementById('checkout-footer');
    const selectedTotalDisplay = document.getElementById('selected-total');

    function updateSummary() {
        let total = 0;
        let count = 0;
        billCheckboxes.forEach(cb => {
            if (cb.checked) {
                const amount = parseInt(cb.closest('.card-premium').querySelector('.font-bold.text-slate-400').innerText.replace(/[^0-9]/g, ''));
                total += amount;
                count++;
            }
        });

        selectedTotalDisplay.innerText = 'Rp' + total.toLocaleString('id-ID');

        if (count > 0) {
            checkoutFooter.classList.remove('hidden');
            setTimeout(() => {
                checkoutFooter.classList.remove('translate-y-10', 'opacity-0');
            }, 10);
        } else {
            checkoutFooter.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => {
                checkoutFooter.classList.add('hidden');
            }, 500);
        }
    }

    checkAll.addEventListener('change', () => {
        billCheckboxes.forEach(cb => cb.checked = checkAll.checked);
        updateSummary();
    });

    billCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSummary);
    });
</script>
@endsection
