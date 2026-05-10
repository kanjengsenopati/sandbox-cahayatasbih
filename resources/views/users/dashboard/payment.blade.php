@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
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
        <div class="text-4xl font-black text-slate-900 mb-2 tabular-nums">Rp{{ number_format($transaction->pay_amount, 0, ',', '.') }}</div>
        <div class="bg-blue-50 text-blue-600 inline-block px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
            Termasuk Kode Unik: {{ $transaction->unique_payment }}
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
        <div class="text-label ml-1">Transfer Ke Rekening</div>
        @foreach($banks as $b)
        <div class="card-premium flex items-center justify-between p-5 group active:bg-blue-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-blue-600 text-xl font-black">
                    @if(str_contains(strtolower($b->bank->name), 'bca')) <i class="fas fa-university"></i>
                    @elseif(str_contains(strtolower($b->bank->name), 'mandiri')) <i class="fas fa-university"></i>
                    @else <i class="fas fa-building-columns"></i> @endif
                </div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ $b->bank->name }}</div>
                    <div class="text-base font-black text-slate-900 tracking-wider">{{ $b->account_number }}</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase">A.N {{ $b->account_name }}</div>
                </div>
            </div>
            <button onclick="copyToClipboard('{{ $b->account_number }}')" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 active:text-blue-600 active:bg-blue-50 transition-all">
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
