@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Berita Sekolah</h1>
    </div>

    <!-- Feature Image -->
    <div class="relative w-full aspect-video rounded-[32px] overflow-hidden shadow-2xl mb-8">
        <img src="{{ asset('storage/' . $information->image) }}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 800 450\'%3E%3Crect width=\'800\' height=\'450\' fill=\'%23f1f5f9\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-family=\'sans-serif\' font-size=\'40\' font-weight=\'bold\' fill=\'%2394a3b8\'%3ENews%3C/text%3E%3C/svg%3E'">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
        <div class="absolute bottom-6 left-6">
            <span class="px-3 py-1 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg">
                {{ $information->informationCategory->name ?? 'Info' }}
            </span>
        </div>
    </div>

    <!-- Content -->
    <div class="px-2">
        <h2 class="text-2xl font-black text-slate-900 leading-tight mb-4">{{ $information->title }}</h2>
        
        <div class="flex items-center gap-4 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 text-xs">
                    <i class="fas fa-user"></i>
                </div>
                <span class="text-xs font-bold text-slate-600">Admin Sekolah</span>
            </div>
            <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
            <div class="flex items-center gap-2 text-slate-400">
                <i class="far fa-clock text-xs"></i>
                <span class="text-xs font-bold">{{ \Carbon\Carbon::parse($information->created_at)->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        <div class="prose prose-slate max-w-none text-slate-600 font-medium leading-relaxed">
            {!! $information->content !!}
        </div>
    </div>
</div>
@endsection
