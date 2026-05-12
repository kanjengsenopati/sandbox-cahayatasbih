@extends('layouts.wali-pwa')

@section('content')
<div class="pb-24">
    <!-- Progress Stepper -->
    <div class="px-6 pt-12 flex items-center justify-between mb-8">
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-black shadow-lg shadow-blue-200">1</div>
            <span class="text-[9px] font-black uppercase tracking-widest text-blue-600">Pilih</span>
        </div>
        <div class="flex-1 h-[2px] bg-slate-100 mx-4 mb-6"></div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-xs font-black">2</div>
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Bayar</span>
        </div>
        <div class="flex-1 h-[2px] bg-slate-100 mx-4 mb-6"></div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-xs font-black">3</div>
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Verifikasi</span>
        </div>
    </div>

    <!-- Header -->
    <div class="px-5 flex items-center gap-4 mb-6">
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
        <div class="card-premium overflow-hidden shadow-sm">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-4">
                <h2 class="text-[13px] font-black text-white uppercase tracking-tight leading-snug">{{ $billType->name }} {{ $billType->academicYear->name ?? '' }}</h2>
            </div>
            
            <div class="p-5">
                <!-- Total -->
                <div class="mb-3">
                    <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-0.5">Total Tagihan</div>
                    <div class="text-[28px] font-black text-slate-900 leading-none tabular-nums tracking-tight">Rp{{ number_format($summary['total'], 0, ',', '.') }}</div>
                </div>

                <!-- Paid / Unpaid - Full Width Grid -->
                <div class="grid grid-cols-2 gap-0 mb-3">
                    <div class="pr-4 border-r border-slate-100">
                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-0.5">Sudah Bayar</div>
                        <div class="text-[15px] font-black text-slate-900 tabular-nums tracking-tight">Rp{{ number_format($summary['paid'], 0, ',', '.') }}</div>
                    </div>
                    <div class="pl-4">
                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-0.5">Belum Bayar</div>
                        <div class="text-[15px] font-black text-red-600 tabular-nums tracking-tight">Rp{{ number_format($summary['unpaid'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-1">Status</div>
                    @if($summary['unpaid'] == 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm">
                            <i class="fas fa-check-circle text-[7px]"></i> Lunas
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm">
                            <i class="fas fa-clock text-[7px]"></i> Proses Bayar
                        </span>
                    @endif
                </div>
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

    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        let total = 0;
        let count = 0;
        let itemsHtml = '';

        billCheckboxes.forEach(cb => {
            if (cb.checked) {
                const card = cb.closest('.card-premium');
                const title = card.querySelector('.text-[13px].font-black').innerText;
                const amount = getAmount(card);
                total += amount;
                count++;
                itemsHtml += `
                    <div class="flex justify-between items-center text-[12px] font-black text-slate-800 tracking-tight mb-2">
                        <span class="capitalize">${title.toLowerCase()}</span>
                        <span class="tracking-tighter">Rp${amount.toLocaleString('id-ID')}</span>
                    </div>
                `;
            }
        });

        if (count === 0) return;

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `
                <div class="text-left space-y-4 p-2">
                    <div class="space-y-1 mb-4 max-h-40 overflow-y-auto no-scrollbar">
                        ${itemsHtml}
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t-2 border-slate-50">
                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Total Tagihan</span>
                        <span class="text-xl font-black text-slate-900 tracking-tighter">Rp${total.toLocaleString('id-ID')}</span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 leading-relaxed italic mt-4">*Langkah berikutnya akan menyertakan instruksi transfer dan kode unik pembayaran.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#f1f5f9',
            customClass: {
                popup: 'rounded-[32px] p-8',
                title: 'text-xl font-black text-slate-900',
                confirmButton: 'rounded-2xl px-8 py-3.5 font-black text-[11px] uppercase tracking-widest shadow-lg shadow-blue-200',
                cancelButton: 'rounded-2xl px-8 py-3.5 font-black text-[11px] uppercase tracking-widest text-slate-500'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endsection
