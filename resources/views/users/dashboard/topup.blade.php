@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Progress Stepper -->
    <div class="flex items-center justify-between mb-10 px-2">
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-black shadow-lg shadow-blue-200">1</div>
            <span class="text-[9px] font-black uppercase tracking-widest text-blue-600">Nominal</span>
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
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Top Up Saldo Saku</h1>
    </div>

    <!-- Active Student Summary -->
    <div class="card-premium flex items-center gap-4 mb-8">
        <img src="{{ url($activeStudent->avatar ?: 'assets/media/avatars/default.png') }}" class="w-12 h-12 rounded-xl object-cover border-2 border-slate-50" alt="">
        <div>
            <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Top Up Untuk</div>
            <div class="text-base font-bold text-slate-900">{{ $activeStudent->name }}</div>
        </div>
    </div>

    <!-- Topup Form -->
    <form action="{{ route('wali.store-topup') }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="student_id" value="{{ $activeStudent->id }}">
        
        <div class="space-y-4">
            <label class="text-label ml-1">Nominal Top Up</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-400 font-bold">Rp</div>
                <input type="text" id="topup_amount_display" class="input-premium pl-12 text-xl font-bold" placeholder="0" required autocomplete="off">
                <input type="hidden" name="amount" id="topup_amount" required>
            </div>
            
            <!-- Quick Amounts -->
            <div class="grid grid-cols-3 gap-3">
                <button type="button" onclick="setAmount(50000)" class="py-3 rounded-2xl bg-white border border-slate-100 text-slate-600 text-xs font-bold active:bg-blue-600 active:text-white transition-colors shadow-sm">50rb</button>
                <button type="button" onclick="setAmount(100000)" class="py-3 rounded-2xl bg-white border border-slate-100 text-slate-600 text-xs font-bold active:bg-blue-600 active:text-white transition-colors shadow-sm">100rb</button>
                <button type="button" onclick="setAmount(200000)" class="py-3 rounded-2xl bg-white border border-slate-100 text-slate-600 text-xs font-bold active:bg-blue-600 active:text-white transition-colors shadow-sm">200rb</button>
            </div>
        </div>

        <div class="space-y-4">
            <label class="text-label ml-1">Metode Pembayaran</label>
            <div class="space-y-3">
                @foreach($paymentMethods as $method)
                <label class="block relative group">
                    <input type="radio" name="payment_method_id" value="{{ $method->id }}" class="peer hidden" required>
                    <div class="card-premium flex items-center justify-between p-4 border-2 border-slate-50 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-blue-600">
                                <i class="fas {{ $method->type == 'XENDIT' ? 'fa-credit-card' : 'fa-university' }}"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900">{{ $method->name }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">Proses Instan</div>
                            </div>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-blue-600 flex items-center justify-center transition-all">
                            <div class="w-3 h-3 bg-blue-600 rounded-full scale-0 peer-checked:scale-100 transition-transform"></div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full btn-primary flex items-center justify-center gap-3">
                <span>Lanjutkan Pembayaran</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </button>
        </div>
    </form>
</div>

<script>
    const displayInput = document.getElementById('topup_amount_display');
    const hiddenInput = document.getElementById('topup_amount');

    displayInput.addEventListener('input', function(e) {
        // Remove non-numeric characters
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            // Update hidden input with raw value
            hiddenInput.value = value;
            // Format display value with dots
            this.value = parseInt(value, 10).toLocaleString('id-ID');
        } else {
            hiddenInput.value = '';
            this.value = '';
        }
    });

    function setAmount(val) {
        hiddenInput.value = val;
        displayInput.value = parseInt(val, 10).toLocaleString('id-ID');
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const amount = hiddenInput.value;
        const methodEl = document.querySelector('input[name="payment_method_id"]:checked');
        
        if (!amount || amount < 10000) {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Minimal top up adalah Rp 10.000', confirmButtonColor: '#2563eb', customClass: { popup: 'rounded-3xl' } });
            return;
        }

        if (!methodEl) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih metode pembayaran terlebih dahulu', confirmButtonColor: '#2563eb', customClass: { popup: 'rounded-3xl' } });
            return;
        }

        const methodName = methodEl.closest('label').querySelector('.text-sm.font-bold').innerText;

        Swal.fire({
            title: 'Konfirmasi Top Up',
            html: `
                <div class="text-left space-y-4 p-2">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <span class="text-[12px] font-black text-slate-800 tracking-tight">Nominal Top Up</span>
                        <span class="text-[13px] font-black text-slate-900 tracking-tighter">Rp${parseInt(amount).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <span class="text-[12px] font-black text-slate-800 tracking-tight">Metode Pembayaran</span>
                        <span class="text-[13px] font-black text-blue-600 capitalize tracking-tight">${methodName.toLowerCase()}</span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-500 leading-relaxed italic mt-4">*Total yang harus dibayar akan menyertakan kode unik di langkah berikutnya.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
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
