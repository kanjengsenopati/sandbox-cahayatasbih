@forelse($students as $student)
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:scale-[1.01] transition-all duration-300 hover:shadow-lg hover:shadow-primary-900/5 group"
         :class="{'ring-2 ring-primary-500/20 scale-[1.01]': expandedRowId === '{{ $student['id'] }}'}">
        
        <!-- Accordion Header -->
        <button 
            @click="toggleBox('{{ $student['id'] }}')" 
            class="w-full text-left px-5 py-4 flex flex-col md:flex-row md:items-center justify-between bg-white relative overflow-hidden gap-4 md:gap-0"
        >
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-primary-400 to-primary-600 rounded-r-full"></div>
            
            <div class="flex items-center gap-4 pl-2 md:pl-3">
                <div class="w-12 h-12 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center flex-shrink-0 font-bold text-sm border border-primary-100 shadow-inner">
                    <span>{{ substr(strtoupper($student['name']), 0, 2) }}</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base md:text-lg leading-tight group-hover:text-primary-700 transition-colors">{{ $student['name'] }}</h3>
                    <div class="flex items-center flex-wrap gap-2 mt-1.5 text-sm text-slate-500">
                        <span class="flex items-center gap-1 bg-slate-50 px-2 py-0.5 rounded text-xs font-medium border border-slate-100">
                            <i class="ph-fill ph-identification-card text-slate-400"></i>
                            <span>{{ $student['nis'] }}</span>
                        </span>
                        
                        <!-- Unit & Class Badges -->
                        <div class="flex items-center gap-1">
                            <span class="flex items-center gap-1 bg-violet-50 px-2 py-0.5 rounded-l text-xs font-semibold border-y border-l border-violet-100 text-violet-700">
                                <span>{{ $student['unit'] }}</span>
                            </span>
                            <span class="flex items-center gap-1 bg-primary-50 px-2 py-0.5 rounded-r text-xs font-medium border border-primary-100 text-primary-700 -ml-1 z-10">
                                <i class="ph-fill ph-chalkboard-teacher text-primary-400"></i>
                                <span>{{ $student['class'] }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between md:justify-end gap-2 md:gap-3 md:pl-0 pl-14 w-full md:w-auto">
                <!-- Status Badge -->
                <span 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 md:px-3 md:py-1.5 rounded-full text-[10px] md:text-xs font-bold border shadow-sm {{ $student['status'] === 'Lunas' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' }}"
                >
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $student['status'] === 'Lunas' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 {{ $student['status'] === 'Lunas' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    </span>
                    <span>{{ $student['status'] }}</span>
                </span>

                <!-- SYAHRIAH Label -->
                <span class="bg-purple-600 text-white font-bold text-[10px] px-2 py-0.5 md:px-3 md:py-1 rounded-full tracking-wider shadow-sm">
                    SYAHRIAH
                </span>

                <!-- Changed Icon Logic: Plus/Minus -->
                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 transform shadow-md"
                     :class="expandedRowId === '{{ $student['id'] }}' ? 'bg-purple-800 text-white rotate-180' : 'bg-purple-600 text-white hover:bg-purple-700 hover:scale-105'">
                     
                     <!-- Minus Icon (Shown when open) -->
                    <i class="ph-bold ph-minus text-lg" x-show="expandedRowId === '{{ $student['id'] }}'"></i>
                    
                    <!-- Plus Icon (Shown when closed) -->
                    <i class="ph-bold ph-plus text-lg" x-show="expandedRowId !== '{{ $student['id'] }}'"></i>
                </div>
            </div>
        </button>

        <!-- Accordion Body -->
        <div x-show="expandedRowId === '{{ $student['id'] }}'" x-collapse x-cloak class="bg-slate-50/50 border-t border-slate-100">
            <div class="p-5 md:p-6">
                
                <!-- Summary Grid of Months -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
                    @foreach($student['months'] as $month)
                        <div class="relative rounded-xl p-3 text-center border transition-all duration-200 group/month {{ $month['status'] === 'paid' ? 'bg-emerald-500 border-emerald-500 shadow-md shadow-emerald-500/20' : ($month['status'] === 'unpaid' && $month['isPast'] ? 'bg-red-600 border-red-600 text-white shadow-md shadow-red-500/20' : 'bg-white border-rose-100') }}">
                            <div class="text-[10px] font-bold uppercase tracking-wider mb-2 {{ $month['status'] === 'paid' ? 'text-white/90' : ($month['status'] === 'unpaid' && $month['isPast'] ? 'text-white/90' : 'text-slate-400') }}">
                                {{ $month['name'] }}
                            </div>
                            
                            <div class="flex items-center justify-center mb-1">
                                @if($month['status'] === 'paid')
                                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/20 text-white shadow-inner group-hover/month:scale-110 transition-transform">
                                        <i class="ph-bold ph-check text-lg"></i>
                                    </div>
                                @else
                                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-full shadow-inner {{ ($month['status'] === 'unpaid' && $month['isPast']) ? 'bg-white/20 text-white' : ($month['isPast'] ? 'bg-rose-50 text-rose-300' : 'bg-slate-100 text-slate-300') }}">
                                        @if($month['status'] === 'unpaid' && $month['isPast'])
                                            <i class="ph-bold ph-x text-lg"></i>
                                        @else
                                            <i class="ph-bold ph-x text-lg"></i>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($month['status'] === 'paid')
                                <div class="text-[10px] text-white font-bold bg-white/20 inline-block px-2 py-0.5 rounded-full mt-1">LUNAS</div>
                            @elseif($month['status'] === 'unpaid' && $month['isPast'])
                                <div class="text-[10px] text-white font-bold bg-white/20 inline-block px-2 py-0.5 rounded-full mt-1">BELUM</div>
                            @else
                                <div class="text-[10px] text-slate-400 font-medium mt-1">Tagihan</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Payment Summary Footer -->
                <div class="bg-gradient-to-br from-primary-900 to-primary-800 rounded-2xl p-5 text-white shadow-xl shadow-primary-900/20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-500/20 rounded-full blur-xl -ml-5 -mb-5"></div>
                    
                    <div class="relative flex flex-col md:flex-row items-center justify-between gap-6">
                        <!-- Left Section: Sudah Terbayar -->
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="p-3.5 bg-emerald-500/20 rounded-xl backdrop-blur-sm border border-emerald-400/20 shadow-lg">
                                <i class="ph-fill ph-check-circle text-2xl text-emerald-300"></i>
                            </div>
                            <div>
                                <p class="text-emerald-100/80 text-xs font-bold uppercase tracking-wider mb-0.5">Sudah Terbayar</p>
                                <p class="text-xl font-bold tracking-tight text-white">{{ $student['summary']['paid_formatted'] }}</p>
                            </div>
                        </div>
                        
                        <div class="hidden md:block h-12 w-px bg-white/10"></div>
                        
                        <!-- Middle Section: Tagihan Berjalan -->
                         <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="p-3.5 bg-orange-500/20 rounded-xl backdrop-blur-sm border border-orange-400/20 shadow-lg">
                                <i class="ph-fill ph-clock-countdown text-2xl text-orange-200"></i>
                            </div>
                            <div>
                                <p class="text-orange-200 text-xs font-bold uppercase tracking-wider mb-0.5">Tagihan Berjalan</p>
                                <p class="text-xl font-bold tracking-tight text-white">{{ $student['summary']['current_due_formatted'] }}</p>
                            </div>
                        </div>

                        <div class="hidden md:block h-12 w-px bg-white/10"></div>

                        <!-- Right Section: Sisa Tagihan -->
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="p-3.5 bg-rose-500/20 rounded-xl backdrop-blur-sm border border-rose-400/20 shadow-lg">
                                <i class="ph-fill ph-warning-circle text-2xl text-rose-300"></i>
                            </div>
                            <div>
                                <p class="text-rose-200 text-xs font-bold uppercase tracking-wider mb-0.5">Total Sisa Tagihan</p>
                                <p class="text-xl font-bold tracking-tight text-white">{{ $student['summary']['remaining_formatted'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
                    <!-- WA Button -->
                    <a 
                        href="{{ $student['wa_link'] ?? '#' }}" 
                        target="{{ $student['wa_link'] ? '_blank' : '' }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full font-semibold text-sm transition-all focus:ring-4 focus:ring-green-500/20 {{ $student['wa_link'] ? 'bg-green-500 hover:bg-green-600 text-white shadow-lg shadow-green-500/30 hover:-translate-y-0.5' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}"
                        title="{{ !$student['wa_link'] ? 'Nomor HP tidak tersedia' : 'Kirim Tagihan via WhatsApp' }}"
                        onclick="{{ !$student['wa_link'] ? 'event.preventDefault()' : '' }}"
                    >
                        <i class="ph-fill ph-whatsapp-logo text-lg"></i>
                        <span>Kirim Tagihan</span>
                    </a>

                    <!-- Invoice Button -->
                    <button 
                        @click="openInvoice(@js($student))"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm shadow-lg shadow-primary-600/30 transition-all hover:-translate-y-0.5 focus:ring-4 focus:ring-primary-600/20"
                    >
                        <i class="ph-fill ph-receipt text-lg"></i>
                        <span>Lihat Rincian</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
@empty
    <!-- Empty State -->
    @if($students->currentPage() === 1)
    <div class="text-center py-16">
        <div class="relative inline-block mb-4">
            <div class="absolute inset-0 bg-primary-200 blur-xl opacity-20 rounded-full"></div>
            <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-full bg-white border border-slate-100 shadow-sm text-slate-300">
                <i class="ph ph-magnifying-glass text-4xl"></i>
            </div>
        </div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Data Tidak Ditemukan</h3>
        <p class="text-slate-500 max-w-xs mx-auto text-sm">
            Tidak ada data yang cocok dengan filter Anda.
        </p>
    </div>
    @endif
@endforelse

<!-- LOAD MORE BUTTON -->
@if($students->hasMorePages())
    <div class="py-8 text-center load-more-wrapper">
        <button 
            @click="loadMore('{{ $students->nextPageUrl() }}', $el)"
            class="group inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-primary-100 shadow-sm text-primary-600 font-semibold text-sm hover:bg-primary-50 hover:shadow-md transition-all duration-300"
        >
            <span>Muat Lebih Banyak</span>
            <i class="ph-bold ph-arrow-down group-hover:translate-y-1 transition-transform"></i>
        </button>
    </div>
@endif
