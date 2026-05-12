@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Agenda Sekolah</h1>
    </div>

    @if($schedules->isEmpty())
    <div class="text-center py-20">
        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full mx-auto flex items-center justify-center text-3xl mb-6">
            <i class="fas fa-calendar-day"></i>
        </div>
        <h3 class="text-lg font-black text-slate-900 mb-2">Belum Ada Agenda</h3>
        <p class="text-slate-400 text-sm font-medium px-10">Agenda kegiatan sekolah mendatang akan muncul di sini.</p>
    </div>
    @endif

    <div class="relative pl-8 border-l-2 border-slate-100 space-y-10 ml-2">
        @foreach($schedules as $item)
        <div class="relative">
            <!-- Timeline dot -->
            <div class="absolute -left-[41px] top-1 w-4 h-4 bg-white border-4 {{ $item->date->isPast() ? 'border-slate-200' : 'border-blue-600' }} rounded-full z-10"></div>
            
            <div class="mb-2">
                <div class="text-[10px] font-black uppercase tracking-widest {{ $item->date->isPast() ? 'text-slate-400' : 'text-blue-600' }} mb-1">
                    {{ $item->date->translatedFormat('d F Y') }}
                </div>
                <h3 class="text-base font-black text-slate-900 leading-tight">{{ $item->name }}</h3>
            </div>
            
            <div class="card-premium p-4 {{ $item->date->isPast() ? 'opacity-60' : '' }}">
                <p class="text-sm font-medium text-slate-500 leading-relaxed">{{ $item->description ?: 'Tidak ada deskripsi kegiatan.' }}</p>
                <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">
                        <i class="fas fa-tag mr-1"></i> {{ $item->type == 'ALL' ? 'Semua Unit' : ($item->school->name ?? 'Unit') }}
                    </span>
                    @if(!$item->date->isPast())
                        <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-1 rounded-md">Mendatang</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
