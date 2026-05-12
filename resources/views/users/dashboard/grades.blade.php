@extends('layouts.wali-pwa')

@section('content')
<div class="px-6 pt-12 pb-24">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('wali.app') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h1 class="text-xl font-black text-slate-900">Nilai Akademik</h1>
    </div>

    <!-- Empty State if no grades -->
    @if($grades->isEmpty())
    <div class="text-center py-20">
        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full mx-auto flex items-center justify-center text-3xl mb-6">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h3 class="text-lg font-black text-slate-900 mb-2">Belum Ada Nilai</h3>
        <p class="text-slate-400 text-sm font-medium px-10">Laporan nilai akademik ananda akan muncul di sini setelah diinput oleh guru.</p>
    </div>
    @endif

    <!-- Academic Cycles -->
    @foreach($grades as $yearId => $semesters)
        @foreach($semesters as $semesterId => $items)
        @php 
            $first = $items->first();
        @endphp
        <div class="mb-10">
            <div class="flex justify-between items-end mb-4 px-1">
                <div>
                    <div class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">{{ $first->academicYear->name }}</div>
                    <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">{{ $first->semester->name }}</h3>
                </div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $items->count() }} Mapel</div>
            </div>

            <div class="card-premium divide-y divide-slate-50 p-0 overflow-hidden shadow-sm">
                @foreach($items as $grade)
                <div class="flex items-center justify-between p-5 active:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 text-sm">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-900">{{ $grade->study->name }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">KKM: {{ $grade->kkm }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-black {{ $grade->grade >= $grade->kkm ? 'text-emerald-600' : 'text-red-600' }} tabular-nums">{{ $grade->grade }}</div>
                        <div class="text-[10px] font-black uppercase tracking-widest {{ $grade->grade >= $grade->kkm ? 'text-emerald-500/50' : 'text-red-500/50' }}">{{ $grade->letter_grade }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endforeach
</div>
@endsection
