<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Cek Pembayaran SPP & Syahriah - PPTQ Cahaya Tasbih">
    <meta name="description" content="Pantau status pembayaran SPP dan tagihan Syahriah santri secara real-time. Mudah, transparan, dan dapat diakses kapan saja tanpa login.">
    <meta name="keywords" content="spp, pptq cahaya tasbih, cek spp online, pembayaran santri, syahriah">
    <meta name="author" content="PPTQ Cahaya Tasbih">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="🔍 Cek Pembayaran SPP Santri">
    <meta property="og:description" content="Cek tagihan dan riwayat pembayaran putra-putri Anda di PPTQ Cahaya Tasbih. Klik di sini untuk melihat rinciannya.">
    <meta property="og:image" content="{{ asset('assets/media/logos/logo.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Cek Pembayaran SPP & Syahriah - PPTQ Cahaya Tasbih">
    <meta property="twitter:description" content="Pantau status pembayaran SPP dan tagihan Syahriah santri secara real-time.">
    <meta property="twitter:image" content="{{ asset('assets/media/logos/logo.png') }}">

    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo.png') }}">
    <title>Cek Pembayaran SPP - PPTQ Cahaya Tasbih</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/media/logos/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/media/logos/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/media/logos/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/media/logos/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/media/logos/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/media/logos/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/media/logos/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/media/logos/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/media/logos/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="imageassets/media/logos/favicon/png" sizes="192x192"
        href="{{ asset('assets/media/logos/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="imageassets/media/logos/favicon/png" sizes="32x32"
        href="{{ asset('assets/media/logos/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="imageassets/media/logos/favicon/png" sizes="96x96"
        href="assets/media/logos/favicon/favicon-96x96.png">
    <link rel="icon" type="imageassets/media/logos/favicon/png" sizes="16x16"
        href="assets/media/logos/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/media/logos/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="assets/media/logos/favicon/ms-icon-144x144.png">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed', // Primary Brand Color
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Axios -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        [x-cloak] { display: none !important; }
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.2em 1.2em;
            padding-right: 2rem;
        }
        .animate-enter { animation: enter 0.3s ease-out; }
        @keyframes enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .shimmer {
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 1000px 100%; 
            animation-duration: 1.2s; 
            animation-fill-mode: forwards; 
            animation-iteration-count: infinite;
            animation-name: placeholderShimmer;
            animation-timing-function: linear;
        }
        @keyframes placeholderShimmer { 0% { background-position: -468px 0; } 100% { background-position: 468px 0; } }
        
        @media print {
            body * { visibility: hidden; }
            #invoice-modal, #invoice-modal * { visibility: visible; }
            #invoice-modal { position: absolute; left: 0; top: 0; width: 100%; height: 100%; overflow: visible; background: white; z-index: 9999; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-primary-50 min-h-screen text-slate-800 antialiased selection:bg-primary-200 selection:text-primary-900">

    <div class="max-w-4xl mx-auto px-4 py-8 md:py-12" x-data="paymentApp()">
        
        <!-- Header -->
        <header class="text-center mb-8 animate-enter">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-200 mb-4">
                <i class="ph-bold ph-mosque text-3xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight mb-1">Cek Pembayaran SPP Santri</h1>
            <p class="text-primary-700 font-medium opacity-90">PPTQ Cahaya Tasbih</p>
        </header>

        <!-- Command Center (Sticky Search & Filter) -->
        <div class="sticky top-4 z-50 mb-6 animate-enter" style="animation-delay: 0.1s;">
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary-400 to-primary-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-200"></div>
                
                <div class="relative bg-white/90 backdrop-blur-xl rounded-xl shadow-xl shadow-primary-900/5 border border-white/50 p-1.5 md:p-2">
                    <div class="flex flex-col md:flex-row md:items-center divide-y md:divide-y-0 md:divide-x divide-slate-200/60">
                        
                        <!-- Segment 1: Search -->
                        <div class="flex-grow flex items-center px-3 py-2 md:py-0">
                            <i class="ph ph-magnifying-glass text-primary-500 text-xl mr-3" :class="{'animate-pulse text-primary-300': isLoading}"></i>
                            <input 
                                type="text" 
                                x-model.debounce.750ms="searchQuery"
                                placeholder="Cari Nama atau NIS..." 
                                class="w-full text-base placeholder-slate-400 border-none outline-none focus:ring-0 text-slate-700 bg-transparent"
                            >
                            <button 
                                x-show="searchQuery.length > 0" 
                                @click="searchQuery = ''; fetchStudents()"
                                class="text-slate-300 hover:text-red-500 transition-colors p-1"
                            >
                                <i class="ph-fill ph-x-circle text-lg"></i>
                            </button>
                        </div>

                        <!-- Filters -->
                        <div class="grid grid-cols-2 divide-x divide-slate-200/60 border-t md:border-t-0 border-slate-200/60 md:w-auto w-full">
                            <div class="px-1 md:px-2 py-1 md:py-0 relative">
                                <i class="ph ph-buildings absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                <select 
                                    x-model="selectedUnit" 
                                    @change="onUnitChange()"
                                    class="w-full bg-transparent hover:bg-slate-50 text-slate-600 font-medium text-sm py-2.5 pl-9 rounded-lg border-none focus:ring-0 cursor-pointer transition-colors outline-none truncate"
                                >
                                    <option value="">Semua Unit</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ request('unit') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="px-1 md:px-2 py-1 md:py-0 relative">
                                <i class="ph ph-users-three absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                <select 
                                    x-model="selectedClass" 
                                    @change="fetchStudents()"
                                    class="w-full bg-transparent hover:bg-slate-50 text-slate-600 font-medium text-sm py-2.5 pl-9 rounded-lg border-none focus:ring-0 cursor-pointer transition-colors outline-none truncate disabled:opacity-50"
                                    :disabled="availableClasses.length === 0"
                                >
                                    <option value="">Semua Kelas</option>
                                    <template x-for="cls in availableClasses" :key="cls.id">
                                        <option :value="cls.id" x-text="cls.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <!-- Quick Loading Indicator (Overlay) -->
            <div x-show="isLoading" class="absolute top-full left-0 w-full text-center mt-2" x-transition>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-medium shadow-sm">
                    <i class="ph-bold ph-spinner animate-spin mr-1.5"></i> Memuat Data...
                </span>
            </div>
        </div>

        <!-- Student List Container -->
        <div class="space-y-4 animate-enter" style="animation-delay: 0.2s;">
            
            <!-- Shimmer Loading for New Searches (Replacing) -->
            <div x-show="isLoading && !isAppending" class="space-y-4">
                 <template x-for="i in 3">
                    <div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm animate-pulse">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-slate-200"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-1/3 bg-slate-200 rounded"></div>
                                <div class="h-3 w-1/4 bg-slate-200 rounded"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Results Container -->
            <!-- The partial is injected here. No x-html used to prevent scope issues with complex DOM (though for simple replace it's fine, innerHTML is safer for memory if referencing centralized state) -->
            <div x-ref="studentList" class="space-y-4 min-h-[200px]" x-show="!isLoading || isAppending">
                @include('public-payment._student_list', ['students' => $students])
            </div>
            
        </div>

        <!-- Footer -->
        <footer class="mt-16 pb-8 text-center">
            <div class="inline-flex items-center justify-center p-4 rounded-xl bg-white/50 backdrop-blur-sm border border-white shadow-sm">
                <p class="text-slate-400 text-sm font-medium">
                    &copy; {{ date('Y') }} PPTQ Cahaya Tasbih <span class="mx-2 text-slate-300">|</span> Sistem Informasi Pembayaran
                </p>
            </div>
        </footer>

        <!-- Invoice Modal (Centralized) -->
        <div id="invoice-modal" x-show="showInvoiceModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div x-show="showInvoiceModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm no-print" @click="showInvoiceModal = false"></div>
            <div x-show="showInvoiceModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-10 scale-95" class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-600 text-white flex items-center justify-center"><i class="ph-bold ph-mosque text-xl"></i></div>
                        <div><h3 class="font-bold text-slate-900 text-lg leading-tight">Rincian Pembayaran SPP</h3><p class="text-xs text-slate-500 font-medium">PPTQ Cahaya Tasbih</p></div>
                    </div>
                    <button @click="showInvoiceModal = false" class="text-slate-400 hover:text-slate-600 transition-colors no-print"><i class="ph-bold ph-x text-xl"></i></button>
                </div>
                <!-- Body -->
                <div class="p-6 overflow-y-auto" :class="{'overflow-visible': true}">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Nama Santri</p><p class="font-bold text-slate-800 truncate" x-text="activeStudent?.name"></p></div>
                        <div><p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">NIS / Kelas</p><p class="font-semibold text-slate-700"><span x-text="activeStudent?.nis"></span> &bull; <span x-text="activeStudent?.class"></span></p></div>
                        <div><p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Unit Sekolah</p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-primary-100 text-primary-700" x-text="activeStudent?.unit"></span></div>
                    </div>
                    <div class="border rounded-lg overflow-hidden md:overflow-visible">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500 border-b font-semibold"><tr><th class="px-4 py-3 w-10 text-center">No</th><th class="px-4 py-3">Bulan</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-right">Nominal</th><th class="px-4 py-3 text-right">Tgl Bayar</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(month, index) in activeStudent?.months" :key="index">
                                    <tr class="group hover:bg-slate-50/50">
                                        <td class="px-4 py-2.5 text-center text-slate-400" x-text="index + 1"></td>
                                        <td class="px-4 py-2.5 font-medium text-slate-700" x-text="month.name"></td>
                                        <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border" :class="month.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200'"><span x-text="month.status === 'paid' ? 'Lunas' : 'Belum'"></span></span></td>
                                        <td class="px-4 py-2.5 text-right font-medium" x-text="month.amount_formatted"></td>
                                        <td class="px-4 py-2.5 text-right text-slate-500 text-xs" x-text="month.paid_date"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 no-print">
                    <button @click="showInvoiceModal = false" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium text-sm hover:bg-white hover:text-slate-900 transition-colors">Tutup</button>
                    <button @click="window.print()" class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium text-sm hover:bg-primary-700 transition-colors shadow-lg shadow-primary-600/20 flex items-center gap-2"><i class="ph-bold ph-printer"></i> Print Invoice</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function paymentApp() {
            return {
                searchQuery: '{{ request("search") }}',
                selectedUnit: '{{ request("unit") }}',
                selectedClass: '{{ request("class_id") }}',
                showInvoiceModal: false,
                activeStudent: null,
                availableClasses: @json($classes),
                isLoading: false,
                isAppending: false,
                expandedRowId: null, // Centralized State

                init() {
                    this.$watch('searchQuery', () => {
                        this.isLoading = true;
                        this.fetchStudents();
                    });
                },

                async onUnitChange() {
                    this.selectedClass = '';
                    if (this.selectedUnit) {
                        try {
                            const response = await axios.get('{{ route("payment-check.get-classes") }}', { params: { school_id: this.selectedUnit } });
                            this.availableClasses = response.data;
                        } catch (error) { this.availableClasses = []; }
                    } else { this.availableClasses = []; }
                    this.fetchStudents();
                },

                async fetchStudents(url = null, isAppend = false) {
                    this.isLoading = true;
                    this.isAppending = isAppend;
                    
                    const params = new URLSearchParams();
                    if (this.searchQuery) params.append('search', this.searchQuery);
                    if (this.selectedUnit) params.append('unit', this.selectedUnit);
                    if (this.selectedClass) params.append('class_id', this.selectedClass);
                    
                    const targetUrl = url || '{{ route("payment-check.index") }}';
                    
                    if (!isAppend) {
                         const newUrl = `${window.location.pathname}?${params.toString()}`;
                         window.history.pushState({path: newUrl}, '', newUrl);
                    }

                    try {
                        const config = {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }, // Explicitly force AJAX header
                            params: isAppend ? {} : {
                                search: this.searchQuery,
                                unit: this.selectedUnit,
                                class_id: this.selectedClass
                            }
                        };
                        
                        const response = await axios.get(targetUrl, config);
                        
                        // IMPORTANT: Parent updates the content
                        if (isAppend) {
                            this.$refs.studentList.insertAdjacentHTML('beforeend', response.data);
                        } else {
                            this.$refs.studentList.innerHTML = response.data;
                            this.expandedRowId = null; // Reset expansion on new search
                        }

                    } catch (error) {
                        console.error('Error fetching students:', error);
                    } finally {
                        this.isLoading = false;
                        this.isAppending = false;
                    }
                },

                loadMore(url, btnEl) {
                    const wrapper = btnEl.closest('.load-more-wrapper');
                    if(wrapper) wrapper.remove();
                    this.fetchStudents(url, true);
                },

                toggleBox(id) {
                    this.expandedRowId = (this.expandedRowId === id) ? null : id;
                },

                getInitials(name) { return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase(); },
                
                openInvoice(student) {
                    this.activeStudent = student;
                    this.showInvoiceModal = true;
                }
            }
        }
    </script>
</body>
</html>
