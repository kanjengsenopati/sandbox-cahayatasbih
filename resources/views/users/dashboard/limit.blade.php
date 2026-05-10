@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Limit Jajan Harian</h1>
    </div>

    <!-- Info Card -->
    <div class="card-premium mb-8 border-0 bg-blue-50">
        <div class="flex gap-4 items-center">
            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-sm">
                <i class="fas fa-info-circle text-xl"></i>
            </div>
            <p class="text-xs text-blue-800 font-medium leading-relaxed flex-1">Gunakan fitur ini untuk membatasi pengeluaran harian santri di kantin sekolah.</p>
        </div>
    </div>

    <!-- Current Limit Display -->
    <div class="text-center mb-10">
        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Limit Saat Ini</div>
        <div class="text-4xl font-black text-slate-900 tabular-nums">Rp{{ number_format($activeStudent->daily_limit, 0, ',', '.') }}</div>
        <div class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-widest">{{ $activeStudent->name }}</div>
    </div>

    <!-- Update Form -->
    <form action="{{ route('wali.update-limit') }}" method="POST" class="space-y-6">
        @csrf
        <div class="space-y-2">
            <label class="text-label ml-1">Atur Nominal Baru</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-400 font-bold">Rp</div>
                <input type="number" name="daily_limit" value="{{ $activeStudent->daily_limit }}" class="input-premium pl-12 text-lg" placeholder="0" required>
            </div>
            <p class="text-[10px] text-slate-400 italic ml-1">*Isi 0 jika tidak ingin memberi batasan</p>
        </div>

        <!-- Quick Options -->
        <div class="grid grid-cols-3 gap-3 mb-8">
            <button type="button" onclick="setVal(10000)" class="py-3 rounded-2xl bg-white border border-slate-100 text-slate-600 text-xs font-bold active:bg-blue-600 active:text-white transition-colors shadow-sm">10.000</button>
            <button type="button" onclick="setVal(20000)" class="py-3 rounded-2xl bg-white border border-slate-100 text-slate-600 text-xs font-bold active:bg-blue-600 active:text-white transition-colors shadow-sm">20.000</button>
            <button type="button" onclick="setVal(50000)" class="py-3 rounded-2xl bg-white border border-slate-100 text-slate-600 text-xs font-bold active:bg-blue-600 active:text-white transition-colors shadow-sm">50.000</button>
        </div>

        <button type="submit" class="w-full btn-primary flex items-center justify-center gap-3">
            <span>Simpan Perubahan</span>
            <i class="fas fa-save text-xs"></i>
        </button>
    </form>
</div>

<script>
    function setVal(val) {
        document.querySelector('input[name="daily_limit"]').value = val;
    }
</script>
@endsection
