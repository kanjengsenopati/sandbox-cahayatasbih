@extends('layouts.wali-pwa')

@section('content')
<div class="pb-24">
    <!-- Header -->
    <div class="px-5 pt-12 flex items-center gap-4 mb-6">
        <a href="{{ route('wali.bills') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-lg font-black text-slate-900">Detail {{ $billType->name }}</h1>
    </div>

    <!-- Student Info Card -->
    <div class="px-5 mb-6">
        <div class="card-premium flex items-center gap-4 bg-blue-600 text-white border-0 shadow-lg shadow-blue-100 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="w-10 h-10 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center text-lg relative z-10">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="relative z-10 flex-1">
                <div class="text-[9px] font-black uppercase tracking-widest text-blue-100/60 mb-0.5">Nama Siswa / Santri</div>
                <div class="text-sm font-black leading-tight">{{ $activeStudent->name }}</div>
                <div class="text-[9px] font-bold text-blue-100/80 mt-0.5">Kelas : {{ $activeStudent->classroom->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="px-5 mb-6">
        <div class="card-premium p-5 shadow-sm">
            <h2 class="text-[14px] font-black text-slate-900 uppercase tracking-tight leading-snug mb-4">{{ $billType->name }} {{ $billType->academicYear->name ?? '' }}</h2>
            
            <div class="mb-4">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Total Tagihan</div>
                <div class="text-[26px] font-black text-slate-900 leading-none tabular-nums">Rp{{ number_format($summary['total'], 0, ',', '.') }}</div>
            </div>

            <div class="flex gap-6 mb-4">
                <div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Sudah Bayar</div>
                    <div class="text-[14px] font-black text-slate-900 tabular-nums">Rp{{ number_format($summary['paid'], 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Belum Bayar</div>
                    <div class="text-[14px] font-black text-red-600 tabular-nums">Rp{{ number_format($summary['unpaid'], 0, ',', '.') }}</div>
                </div>
            </div>

            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Status</div>
                @if($summary['unpaid'] == 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-[10px] font-black uppercase tracking-widest">
                        <i class="fas fa-check-circle text-[8px]"></i> Lunas
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg text-[10px] font-black uppercase tracking-widest">
                        <i class="fas fa-clock text-[8px]"></i> Proses Bayar
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="h-2 bg-slate-50 mb-5"></div>

    <!-- Itemized List -->
    <form action="{{ route('wali.checkout') }}" method="POST">
        @csrf
        <div class="px-5 mb-4">
            <h3 class="text-[14px] font-black text-slate-900 mb-4">Detail {{ $billType->name }}</h3>
            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" id="check-all" class="w-[18px] h-[18px] rounded border-2 border-slate-300 text-blue-600 focus:ring-blue-600 focus:ring-offset-0 transition-all">
                <span class="text-[13px] font-black text-slate-900">Bayar Semua</span>
            </label>
        </div>

        <div class="px-5 space-y-2.5">
            @foreach($bills as $bill)
            <div class="card-premium !rounded-[18px] !p-4 flex items-center gap-3.5 border-l-4 {{ $bill->status == 'PAID' ? 'border-l-emerald-500' : 'border-l-blue-600' }} shadow-sm">
                {{-- Checkbox / Check icon --}}
                @if($bill->status == 'UNPAID')
                    <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="bill-checkbox w-[18px] h-[18px] rounded border-2 border-slate-300 text-blue-600 focus:ring-blue-600 focus:ring-offset-0 transition-all flex-shrink-0">
                @else
                    <div class="w-[18px] h-[18px] flex items-center justify-center text-emerald-500 flex-shrink-0">
                        <i class="fas fa-check-circle text-sm"></i>
                    </div>
                @endif
                
                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] font-black text-slate-400 tracking-wider tabular-nums">Rp{{ number_format($bill->amount, 0, ',', '.') }}</div>
                    <div class="text-[13px] font-black text-blue-600 leading-tight mt-0.5">Tagihan {{ $bill->translated_month }} {{ $bill->year }}</div>
                </div>

                {{-- Status Badge --}}
                @if($bill->status == 'PAID')
                    <span class="bg-emerald-500 text-white px-3.5 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest flex-shrink-0">Lunas</span>
                @else
                    <div class="text-right flex flex-col items-end gap-0.5 flex-shrink-0">
                        <span class="bg-blue-600 text-white px-3.5 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest">Bayar</span>
                        <span class="text-[8px] font-black text-red-500 uppercase tracking-tight">Belum Bayar</span>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Floating Checkout Footer -->
        <div id="checkout-footer" class="fixed bottom-24 left-5 right-5 z-40 hidden transform translate-y-10 opacity-0 transition-all duration-500 ease-out">
            <div class="bg-slate-900 rounded-[24px] p-5 shadow-2xl flex justify-between items-center">
                <div>
                    <div class="text-slate-400 text-[9px] font-black uppercase tracking-widest mb-1">Total Dipilih</div>
                    <div id="selected-total" class="text-white text-lg font-black tabular-nums">Rp0</div>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-7 py-3.5 rounded-[16px] text-[11px] font-black uppercase tracking-widest active:scale-95 transition-all shadow-lg shadow-blue-500/30">Lanjutkan</button>
            </div>
        </div>
    </form>
</div>

<script>
    const checkAll = document.getElementById('check-all');
    const billCheckboxes = document.querySelectorAll('.bill-checkbox');
    const checkoutFooter = document.getElementById('checkout-footer');
    const selectedTotalDisplay = document.getElementById('selected-total');

    function getAmount(card) {
        const el = card.querySelector('.text-slate-400.tracking-wider');
        if (!el) return 0;
        return parseInt(el.innerText.replace(/[^0-9]/g, '')) || 0;
    }

    function updateSummary() {
        let total = 0;
        let count = 0;
        billCheckboxes.forEach(cb => {
            if (cb.checked) {
                total += getAmount(cb.closest('.card-premium'));
                count++;
            }
        });

        selectedTotalDisplay.innerText = 'Rp' + total.toLocaleString('id-ID');

        if (count > 0) {
            checkoutFooter.classList.remove('hidden');
            requestAnimationFrame(() => {
                checkoutFooter.classList.remove('translate-y-10', 'opacity-0');
            });
        } else {
            checkoutFooter.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => checkoutFooter.classList.add('hidden'), 500);
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            billCheckboxes.forEach(cb => cb.checked = checkAll.checked);
            updateSummary();
        });
    }

    billCheckboxes.forEach(cb => cb.addEventListener('change', updateSummary));
</script>
@endsection
