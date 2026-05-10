@extends('layouts.wali-pwa')

@section('content')
<div class="px-8 pt-20 pb-10 flex flex-col min-h-screen">
    <!-- Brand / Logo -->
    <div class="mb-12">
        <div class="w-16 h-16 bg-blue-600 rounded-3xl flex items-center justify-center text-white text-3xl shadow-xl shadow-blue-100 mb-6">
            <i class="fas fa-mosque"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Selamat Datang</h1>
        <p class="text-slate-500 font-medium leading-relaxed">Portal Resmi Wali Santri & Orang Tua <br><span class="text-blue-600 font-bold">Cahaya Tasbih</span></p>
    </div>

    <!-- Login Form -->
    <div class="flex-1">
        <form action="{{ route('wali.authenticate') }}" method="POST" class="space-y-5">
            @csrf
            
            @if(session('error'))
                <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-bold flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="space-y-2">
                <label class="text-label ml-1">No Handphone</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-400">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <input type="number" name="phone" value="{{ old('phone') }}" class="input-premium pl-12" placeholder="08xxxxxx" required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-label ml-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" class="input-premium pl-12 pr-12" placeholder="••••••••" required>
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-400">
                        <i id="eye-icon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full btn-primary flex items-center justify-center gap-3">
                    <span>Masuk Ke Portal</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Footer Action -->
    <div class="mt-10 text-center">
        <p class="text-slate-500 text-sm font-medium">Belum punya akun?</p>
        <a href="{{ route('wali.register') }}" class="inline-block mt-2 text-blue-600 font-extrabold uppercase tracking-widest text-xs border-b-2 border-blue-600 pb-1">Daftar Sekarang</a>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection