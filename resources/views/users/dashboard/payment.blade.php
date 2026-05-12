@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Progress Stepper -->
    <div class="flex items-center justify-between mb-10 px-2">
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-black"><i class="fas fa-check"></i></div>
            <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500">Pilih</span>
        </div>
        <div class="flex-1 h-[2px] bg-emerald-100 mx-4 mb-6"></div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 {{ !$proof ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-emerald-500 text-white' }} rounded-full flex items-center justify-center text-xs font-black">
                @if(!$proof) 2 @else <i class="fas fa-check"></i> @endif
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest {{ !$proof ? 'text-blue-600' : 'text-emerald-500' }}">Bayar</span>
        </div>
        <div class="flex-1 h-[2px] {{ !$proof ? 'bg-slate-100' : 'bg-emerald-100' }} mx-4 mb-6"></div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 {{ $proof ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-slate-100 text-slate-400' }} rounded-full flex items-center justify-center text-xs font-black">3</div>
            <span class="text-[9px] font-black uppercase tracking-widest {{ $proof ? 'text-blue-600' : 'text-slate-400' }}">Verifikasi</span>
        </div>
    </div>

    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.bills') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Pembayaran</h1>
    </div>

    <!-- Status Badge -->
    <div class="mb-8 text-center">
        @if(!$proof)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-100 text-orange-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                <i class="fas fa-clock"></i> Menunggu Pembayaran
            </div>
        @elseif($proof->status == 'PENDING')
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                <i class="fas fa-spinner fa-spin"></i> Menunggu Verifikasi Admin
            </div>
        @elseif($proof->status == 'APPROVED')
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                <i class="fas fa-check-circle"></i> Pembayaran Berhasil
            </div>
        @endif
    </div>

    <!-- Invoice Card -->
    <div class="card-premium !p-8 text-center mb-10 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-blue-600"></div>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Total yang harus dibayar</div>
        @php
            $formattedAmount = number_format($transaction->pay_amount, 0, ',', '.');
            $mainPart = strlen($formattedAmount) > 3 ? substr($formattedAmount, 0, -3) : '';
            $lastThree = strlen($formattedAmount) > 3 ? substr($formattedAmount, -3) : $formattedAmount;
        @endphp
        <div class="flex items-center justify-center gap-3 mb-3">
            <div class="text-4xl font-black text-slate-900 tabular-nums tracking-tighter">
                Rp{{ $mainPart }}<span class="text-blue-600">{{ $lastThree }}</span>
            </div>
            <button onclick="copyToClipboard('{{ $transaction->pay_amount }}')" class="w-10 h-10 bg-slate-50 text-slate-500 rounded-xl flex items-center justify-center active:bg-blue-600 active:text-white transition-all shadow-sm border border-slate-100">
                <i class="far fa-copy text-lg"></i>
            </button>
        </div>
        <div class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest mt-1">
            <i class="fas fa-exclamation-circle"></i> Wajib transfer hingga 3 digit terakhir
        </div>
        
        <div class="mt-8 pt-8 border-t border-dashed border-slate-200">
            <div class="flex justify-between items-center text-sm font-bold text-slate-800 mb-3">
                <span>ID Transaksi</span>
                <span class="text-slate-400">#{{ $transaction->payment_code }}</span>
            </div>
            <div class="flex justify-between items-center text-sm font-bold text-slate-800">
                <span>Batas Waktu</span>
                <span class="text-orange-500">{{ \Carbon\Carbon::parse($transaction->expiry_time)->translatedFormat('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Bank Accounts -->
    <div class="space-y-4 mb-10">
        <div class="text-[13px] font-black text-slate-900 tracking-tight ml-1">Transfer Ke Rekening</div>
        @foreach($banks as $b)
        <div class="card-premium flex items-center justify-between p-5 group active:bg-blue-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-blue-600 text-xl font-black">
                    @if(str_contains(strtolower($b->bank->name), 'bca')) <i class="fas fa-university"></i>
                    @elseif(str_contains(strtolower($b->bank->name), 'mandiri')) <i class="fas fa-university"></i>
                    @else <i class="fas fa-building-columns"></i> @endif
                </div>
                <div>
                    <div class="text-[11px] font-black text-slate-800 uppercase tracking-tight mb-0.5">{{ $b->bank->name }}</div>
                    <div class="text-base font-black text-slate-900 tracking-tighter">{{ $b->bank->account_number }}</div>
                    <div class="text-[11px] font-bold text-slate-600 capitalize tracking-tight">A.N {{ strtolower($b->bank->account_name) }}</div>
                </div>
            </div>
            <button onclick="copyToClipboard('{{ $b->bank->account_number }}')" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 active:text-blue-600 active:bg-blue-50 transition-all">
                <i class="far fa-copy"></i>
            </button>
        </div>
        @endforeach
    </div>

    <!-- Upload Proof -->
    <div class="space-y-4">
        <div class="text-label ml-1">Upload Bukti Bayar</div>
        @if($proof)
            <div class="card-premium p-4 border-2 border-slate-100">
                <img src="{{ asset('storage/' . $proof->proof_path) }}" class="w-full h-48 object-cover rounded-2xl mb-4" alt="Bukti Pembayaran">
                <div class="text-center">
                    <p class="text-[11px] font-bold text-slate-400 mb-4 italic">Bukti telah diunggah pada {{ \Carbon\Carbon::parse($proof->created_at)->translatedFormat('d M Y, H:i') }}</p>
                    @if($proof->status != 'APPROVED')
                        <button onclick="document.getElementById('proof-input').click()" class="text-blue-600 text-xs font-black uppercase tracking-widest">Ganti Bukti</button>
                    @endif
                </div>
            </div>
        @else
            <div onclick="document.getElementById('proof-input').click()" class="card-premium !p-10 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-4 active:bg-slate-50 transition-colors cursor-pointer group">
                <div class="w-16 h-16 bg-slate-50 rounded-[24px] flex items-center justify-center text-slate-300 text-2xl group-active:text-blue-600 group-active:bg-blue-50 transition-all">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="text-center">
                    <div class="text-sm font-black text-slate-800 uppercase tracking-tight">Pilih Foto Bukti</div>
                    <div class="text-[10px] font-bold text-slate-400 mt-1">JPG, PNG atau JPEG (Max 2MB)</div>
                </div>
            </div>
        @endif

        <form id="upload-form" action="{{ route('wali.upload-proof', $transaction->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" name="proof" id="proof-input" onchange="document.getElementById('upload-form').submit()">
        </form>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'Nomor rekening berhasil disalin ke clipboard.',
                confirmButtonColor: '#2563eb',
                customClass: { popup: 'rounded-3xl' }
            });
        });
    }
</script>
@endsection
