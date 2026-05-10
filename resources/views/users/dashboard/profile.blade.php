@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Profil Saya</h1>
    </div>

    <!-- User Card -->
    <div class="card-premium p-8 text-center mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-12 -mt-12"></div>
        <div class="relative z-10">
            <div class="relative inline-block mb-6">
                <img src="{{ url($user->avatar ?: 'assets/media/avatars/default.png') }}" class="w-24 h-24 rounded-[32px] object-cover border-4 border-white shadow-xl" alt="">
                <button class="absolute -bottom-2 -right-2 w-8 h-8 bg-blue-600 rounded-xl text-white text-xs shadow-lg border-2 border-white flex items-center justify-center">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 mb-1">{{ $user->name }}</h2>
            <p class="text-sm font-medium text-slate-500">{{ $user->phone }}</p>
        </div>
    </div>

    <!-- Account Details -->
    <div class="space-y-4 mb-10">
        <div class="text-label ml-1">Informasi Akun</div>
        <div class="card-premium space-y-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="fas fa-envelope"></i></div>
                    <span class="text-sm font-bold text-slate-800">Email</span>
                </div>
                <span class="text-sm font-medium text-slate-500">{{ $user->email ?: '-' }}</span>
            </div>
            <div class="h-[1px] bg-slate-50"></div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="fas fa-user-friends"></i></div>
                    <span class="text-sm font-bold text-slate-800">Jumlah Santri</span>
                </div>
                <span class="text-sm font-medium text-slate-500">{{ $students->count() }} Anak</span>
            </div>
            <div class="h-[1px] bg-slate-50"></div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="fas fa-id-card"></i></div>
                    <span class="text-sm font-bold text-slate-800">Status Akun</span>
                </div>
                <span class="px-3 py-1 rounded-lg bg-emerald-100 text-emerald-600 text-[10px] font-black uppercase">Aktif Terverifikasi</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
        <button class="w-full card-premium !p-5 flex items-center justify-between group active:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-active:text-blue-600"><i class="fas fa-key"></i></div>
                <span class="text-sm font-bold text-slate-800">Ubah Password</span>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
        </button>
        
        <button class="w-full card-premium !p-5 flex items-center justify-between group active:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-active:text-blue-600"><i class="fas fa-question-circle"></i></div>
                <span class="text-sm font-bold text-slate-800">Bantuan & CS</span>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
        </button>

        <form action="{{ route('wali.logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-5 rounded-3xl border-2 border-red-50 text-red-600 font-black uppercase tracking-[0.2em] text-xs flex items-center justify-center gap-3 active:bg-red-50 transition-colors">
                <i class="fas fa-power-off text-sm"></i>
                <span>Keluar Akun</span>
            </button>
        </form>
    </div>
</div>
@endsection
