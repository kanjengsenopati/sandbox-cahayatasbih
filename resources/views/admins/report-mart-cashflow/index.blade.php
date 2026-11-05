@extends('layouts.master', ['title' => 'Laporan Arus Kas Mart'])

@section('content')
<style>
    .premium-card {
        border-radius: 24px !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
        border: none !important;
    }
    .safe-padding {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    .summary-card-smooth {
        border-radius: 24px !important;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent !important;
    }
    .summary-card-smooth:hover {
        transform: translateY(-3px);
    }
    .summary-card-total {
        background: linear-gradient(135deg, #eff6ff 0%, #f8faff 50%, #ffffff 100%) !important;
        border-color: rgba(37, 99, 235, 0.18) !important;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.07), 0 8px 10px -6px rgba(37, 99, 235, 0.04) !important;
    }
    .summary-card-outlet {
        background: linear-gradient(135deg, #fffbeb 0%, #fefdf7 50%, #ffffff 100%) !important;
        border-color: rgba(245, 158, 11, 0.2) !important;
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.07), 0 8px 10px -6px rgba(245, 158, 11, 0.04) !important;
    }
    .summary-card-koperasi {
        background: linear-gradient(135deg, #ecfdf5 0%, #f7fdfa 50%, #ffffff 100%) !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.07), 0 8px 10px -6px rgba(16, 185, 129, 0.04) !important;
    }
    .summary-card-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fffafa 50%, #ffffff 100%) !important;
        border-color: rgba(220, 38, 38, 0.18) !important;
        box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.07), 0 8px 10px -6px rgba(220, 38, 38, 0.04) !important;
    }
    .summary-card-count {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%) !important;
        border-color: rgba(100, 116, 139, 0.15) !important;
        box-shadow: 0 10px 25px -5px rgba(100, 116, 139, 0.07) !important;
    }
    .summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .summary-watermark {
        position: absolute;
        right: -8px;
        bottom: -12px;
        font-size: 56px;
        opacity: 0.04;
        pointer-events: none;
        line-height: 1;
    }
    .pos-item-stack {
        line-height: 1.25;
    }
    .btn-source-filter.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }
    /* Sembunyikan kotak pencarian Select2 pada dropdown serah terima dana (kedua sisi) */
    .select2-container--hide-search .select2-search,
    .select2-dropdown:has([id*="handover-sender-id"]) .select2-search,
    .select2-dropdown:has([id*="handover-recipient-id"]) .select2-search {
        display: none !important;
    }
    /* Top Navigation Pill Tabs (PakRT Native Vite Glassmorphism) */
    .mart-top-tabs {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 6px;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
    }
    .mart-tab-btn {
        border-radius: 15px !important;
        padding: 9px 18px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #64748b !important;
        background: transparent !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .mart-tab-btn:hover {
        color: #1e293b !important;
        background: rgba(241, 245, 249, 0.7) !important;
    }
    .mart-tab-btn.active {
        background: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
    }
    .mart-tab-btn.active .tab-pill-badge {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
    }
    .upload-dropzone-area {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background-color: #f8fafc;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .upload-dropzone-area:hover {
        border-color: #2563eb;
        background-color: rgba(37, 99, 235, 0.03);
    }
</style>

<div class="content d-flex flex-column flex-column-fluid safe-padding" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar py-4" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-3 mb-lg-0">
                <x-text.h1 class="my-1">Arus Kas Mart</x-text.h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Laporan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Arus Kas Mart & Serah Terima Dana Outlet</li>
                </ul>
            </div>

            <!-- Top-Right Action Cluster -->
            <div class="d-flex items-center gap-2">
                <button type="button" class="btn btn-sm btn-light-primary fw-bold" id="btn-refresh" title="Segarkan Data">
                    <i class="fa-solid fa-arrows-rotate me-1 fs-7"></i> Refresh
                </button>
                <button type="button" class="btn btn-sm btn-success fw-bold" id="btn-export-excel" title="Download Excel">
                    <i class="fa-solid fa-file-excel me-1 fs-7"></i> Export Excel
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-fluid px-0">

            @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-4 mb-5 rounded-3">
                <i class="fa-solid fa-circle-check fs-2hx text-success me-3"></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-success">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-4 mb-5 rounded-3">
                <i class="fa-solid fa-triangle-exclamation fs-2hx text-danger me-3"></i>
                <div class="d-flex flex-column">
                    <h5 class="mb-1 text-danger">Peringatan</h5>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            <!-- Scope Notice for Restricted Users -->
            @if($isRestricted)
            <div class="alert alert-light-warning d-flex align-items-center p-4 mb-5 rounded-3" style="border: 1px solid rgba(245, 158, 11, 0.3);">
                <i class="fa-solid fa-shield-halved fs-3 text-warning me-3"></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-gray-800 fs-7">Scope Hak Akses Terkunci (Outlet Assigned):</span>
                    <span class="text-muted fs-8">
                        Anda terhubung dengan hak akses outlet: 
                        @foreach($outlets as $o)
                            <strong class="text-warning">{{ $o->name }}</strong>{{ !$loop->last ? ', ' : '' }}
                        @endforeach.
                        Data transaksi yang ditampilkan dikhususkan untuk unit outlet penugasan Anda guna rekonsiliasi penagihan saldo ke Bank Saldo (Pondok Mart).
                    </span>
                </div>
            </div>
            @endif

            <!-- TOP NAVIGATION TABS (Riwayat Belanja Saldo vs Serah Terima Dana / Handover Outlet) -->
            @php
                $activeTab = request('tab', 'riwayat') === 'handover' ? 'handover' : 'riwayat';
                $initialPendingRaw = (float) ($handoverSummary['handover_pending_raw'] ?? 0);
                $initialPendingText = $handoverSummary['handover_pending_amount'] ?? 'Rp 0';
            @endphp
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-5">
                <div class="mart-top-tabs" role="tablist">
                    <button type="button"
                            class="mart-tab-btn {{ $activeTab === 'riwayat' ? 'active' : '' }}"
                            id="btn-tab-riwayat"
                            data-tab="riwayat">
                        <i class="fa-solid fa-receipt fs-7"></i>
                        <span>Riwayat Belanja Saldo</span>
                    </button>
                    <button type="button"
                            class="mart-tab-btn {{ $activeTab === 'handover' ? 'active' : '' }}"
                            id="btn-tab-handover"
                            data-tab="handover">
                        <i class="fa-solid fa-hand-holding-dollar fs-7"></i>
                        <span>Serah Terima Dana (Handover Outlet)</span>
                        <span id="tab-badge-pending"
                              class="badge tab-pill-badge rounded-pill px-2.5 py-1 fs-9 fw-bolder"
                              style="{{ $initialPendingRaw > 0 ? 'background-color: rgba(245, 158, 11, 0.14); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);' : 'background-color: rgba(16, 185, 129, 0.14); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);' }}">
                            {{ $initialPendingRaw > 0 ? 'Belum Diserahkan: ' . $initialPendingText : 'Lunas Diserahkan' }}
                        </span>
                    </button>
                </div>

                <div class="d-none d-xl-flex align-items-center gap-2 text-muted fs-8 fw-semibold">
                    <i class="fa-solid fa-building-columns text-primary"></i>
                    <span>Bank Saldo Utama: <strong class="text-gray-800">Pondok Mart / Koperasi</strong></span>
                    <i class="fa-solid fa-arrow-right mx-1 text-gray-400"></i>
                    <i class="fa-solid fa-shop text-warning"></i>
                    <span>Penerima Klaim: <strong class="text-gray-800">Unit Outlet</strong></span>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 1: RIWAYAT BELANJA SALDO                                              -->
            <!-- ========================================================================= -->
            <div id="pane-riwayat" class="{{ $activeTab === 'riwayat' ? '' : 'd-none' }}">
                <!-- GRID SUMMARY CARDS -->
                <div class="row g-4 mb-5">
                    <!-- 1. Total Belanja Saldo -->
                    <div class="col-md-3">
                        <div class="card premium-card summary-card-smooth summary-card-total px-5 py-3 h-100">
                            <i class="fa-solid fa-wallet summary-watermark text-primary"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Total Belanja Saldo</x-text.label>
                                    <x-text.amount id="stat-total-amount" class="fs-2 text-primary lh-1 mt-0.5">Rp 0</x-text.amount>
                                </div>
                                <div class="summary-icon bg-primary text-white shadow-sm flex-shrink-0">
                                    <i class="fa-solid fa-money-bill-wave fs-6 text-white"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500" id="stat-total-count">0 Transaksi</x-text.caption>
                                <span class="badge bg-light-primary text-primary px-2 py-0.5 fs-9 fw-bold rounded-pill">Total Terfilter</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Tagihan Outlet ke Bank Saldo -->
                    <div class="col-md-3">
                        <div class="card premium-card summary-card-smooth summary-card-outlet px-5 py-3 h-100">
                            <i class="fa-solid fa-shop summary-watermark text-warning"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Tagihan Saldo Outlet</x-text.label>
                                    <x-text.amount id="stat-outlet-amount" class="fs-2 lh-1 mt-0.5" style="color: #d97706 !important;">Rp 0</x-text.amount>
                                </div>
                                <div class="summary-icon bg-warning text-white shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;">
                                    <i class="fa-solid fa-file-invoice-dollar fs-6 text-white"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500" id="stat-outlet-count">0 Transaksi</x-text.caption>
                                <span class="badge bg-light-warning text-warning px-2 py-0.5 fs-9 fw-bold rounded-pill">Ditagihkan ke Bank</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Belanja di Pondok Mart / Koperasi -->
                    <div class="col-md-3">
                        <div class="card premium-card summary-card-smooth summary-card-koperasi px-5 py-3 h-100">
                            <i class="fa-solid fa-store summary-watermark text-success"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Pondok Mart / Koperasi</x-text.label>
                                    <x-text.amount id="stat-koperasi-amount" class="fs-2 lh-1 mt-0.5" style="color: #10b981 !important;">Rp 0</x-text.amount>
                                </div>
                                <div class="summary-icon bg-success text-white shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                                    <i class="fa-solid fa-building-columns fs-6 text-white"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500" id="stat-koperasi-count">0 Transaksi</x-text.caption>
                                <span class="badge bg-light-success text-success px-2 py-0.5 fs-9 fw-bold rounded-pill">Bank Saldo Utama</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Rata-rata Belanja -->
                    <div class="col-md-3">
                        <div class="card premium-card summary-card-smooth summary-card-count px-5 py-3 h-100">
                            <i class="fa-solid fa-receipt summary-watermark text-slate-400"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Rata-rata Belanja</x-text.label>
                                    <x-text.amount id="stat-avg-amount" class="fs-2 text-dark lh-1 mt-0.5">Rp 0</x-text.amount>
                                </div>
                                <div class="summary-icon bg-secondary text-gray-700 shadow-sm flex-shrink-0">
                                    <i class="fa-solid fa-calculator fs-6 text-gray-700"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500">Nominal per Siswa</x-text.caption>
                                <span class="badge bg-light-secondary text-gray-700 px-2 py-0.5 fs-9 fw-bold rounded-pill">Per Struk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILTER CARD -->
                <div class="card premium-card bg-white px-5 py-4 mb-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                        
                        <!-- Filter Sumber Transaksi (Pondok Mart / Koperasi vs Outlet) -->
                        <div class="d-flex flex-column gap-2">
                            <x-text.label class="text-slate-400">Sumber Transaksi</x-text.label>
                            @if(!$isRestricted)
                            <div class="btn-group" role="group" id="source-filter-group">
                                <button type="button" class="btn btn-sm btn-outline btn-outline-primary btn-source-filter active" data-source="all">
                                    <i class="fa-solid fa-layer-group me-1 fs-8"></i> Semua Unit
                                </button>
                                <button type="button" class="btn btn-sm btn-outline btn-outline-primary btn-source-filter" data-source="koperasi">
                                    <i class="fa-solid fa-store me-1 fs-8"></i> Pondok Mart / Koperasi
                                </button>
                                <button type="button" class="btn btn-sm btn-outline btn-outline-primary btn-source-filter" data-source="outlet">
                                    <i class="fa-solid fa-shop me-1 fs-8"></i> Unit Outlet
                                </button>
                            </div>
                            @else
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light-warning text-warning fw-bold px-3 py-2 fs-7" style="border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px;">
                                    <i class="fa-solid fa-lock me-1 fs-8"></i> Scope Khusus Outlet
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Filter Rentang Tanggal -->
                        <div class="d-flex flex-column gap-2 flex-grow-1" style="min-width: 240px; max-width: 340px;">
                            <x-text.label class="text-slate-400">Rentang Tanggal</x-text.label>
                            <div class="position-relative cursor-pointer" id="filter-daterange-wrapper">
                                <input class="form-control form-control-sm form-control-solid fw-bold ps-9 cursor-pointer" placeholder="Pilih Periode Tanggal" value="Semua Tanggal" id="filter-daterange" readonly style="cursor: pointer !important;" />
                                <i class="fa-solid fa-calendar-days position-absolute top-50 translate-middle-y ms-3 text-muted fs-7" style="pointer-events: none;"></i>
                            </div>
                        </div>

                        <!-- Quick Period Buttons -->
                        <div class="d-flex flex-column gap-2">
                            <x-text.label class="text-slate-400">Preset Periode</x-text.label>
                            <div class="btn-group" role="group" id="preset-period-group">
                                <button type="button" class="btn btn-sm btn-light btn-period" data-period="today">Hari Ini</button>
                                <button type="button" class="btn btn-sm btn-light btn-period" data-period="week">Minggu Ini</button>
                                <button type="button" class="btn btn-sm btn-light btn-period" data-period="month">Bulan Ini</button>
                                <button type="button" class="btn btn-sm btn-primary text-white btn-period active" data-period="all">Semua</button>
                            </div>
                        </div>

                        <!-- Tombol Reset -->
                        <div class="d-flex flex-column justify-content-end gap-2">
                            <x-text.label class="text-transparent d-none d-lg-block">-</x-text.label>
                            <button type="button" class="btn btn-sm btn-light-danger fw-bold" id="btn-reset-filter" title="Reset Filter">
                                <i class="fa-solid fa-rotate-left me-1 fs-8"></i> Reset
                            </button>
                        </div>

                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card premium-card bg-white p-6">
                    <div class="card-header border-0 px-0 pt-0 pb-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                        <div>
                            <x-text.h2>Riwayat Transaksi Belanja Siswa (Saldo)</x-text.h2>
                            <x-text.caption class="text-slate-400">Menampilkan rekap seluruh transaksi belanja siswa menggunakan metode pembayaran saldo pada unit yang Anda kelola</x-text.caption>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-warning fw-bold align-self-start align-self-sm-center" id="btn-goto-handover">
                            <i class="fa-solid fa-hand-holding-dollar me-1 fs-7"></i> Buka Serah Terima Dana Outlet
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-3 gs-4" id="table-mart-cashflow">
                            <thead>
                                <tr class="text-start text-muted fw-bolder fs-8 text-uppercase gs-0" style="background-color: #f8fafc; border-radius: 12px;">
                                    <th class="w-40px ps-4">No</th>
                                    <th class="min-w-120px">No. Transaksi</th>
                                    <th class="min-w-140px">Tanggal & Waktu</th>
                                    <th class="min-w-180px">Identitas Siswa</th>
                                    <th class="min-w-160px">Unit / Outlet</th>
                                    <th class="min-w-200px">Rincian Belanja</th>
                                    <th class="min-w-130px">Nominal Belanja</th>
                                    <th class="min-w-90px text-center">Status</th>
                                    <th class="min-w-80px text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                                <!-- Populated via AJAX DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 2: SERAH TERIMA DANA (HANDOVER DANA SALDO KOPERASI -> OUTLET)         -->
            <!-- ========================================================================= -->
            <div id="pane-handover" class="{{ $activeTab === 'handover' ? '' : 'd-none' }}">
                
                <!-- INFO BANNER ALUR PENCAIRAN SALDO OUTLET -->
                <div class="card premium-card px-5 py-3 mb-5" style="background: linear-gradient(90deg, rgba(37, 99, 235, 0.06) 0%, rgba(16, 185, 129, 0.06) 100%) !important; border: 1px solid rgba(37, 99, 235, 0.14) !important;">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="summary-icon bg-primary text-white shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-money-bill-transfer fs-6 text-white"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-900 fs-7">Alur Pencairan & Serah Terima Dana Belanja Saldo Outlet</div>
                                <x-text.body class="fs-8 text-muted mb-0">
                                    Seluruh dana top-up saldo santri tersimpan di <strong>Bank Saldo (Pondok Mart / Koperasi)</strong>. Gunakan modul ini untuk mencatat penyerahan dana hasil transaksi belanja santri di <strong>Unit Outlet</strong> kepada Pengelola Outlet.
                                </x-text.body>
                            </div>
                        </div>
                        <span class="badge bg-white text-gray-800 fw-bold px-3 py-2 shadow-xs align-self-start align-self-md-center" style="border-radius: 10px;">
                            <i class="fa-solid fa-building-columns text-primary me-1"></i> Koperasi <i class="fa-solid fa-arrow-right mx-1 text-muted"></i> <i class="fa-solid fa-shop text-warning me-1"></i> Pengelola Outlet
                        </span>
                    </div>
                </div>

                <!-- 3 COMPACT SUMMARY CARDS FOR HANDOVER -->
                <div class="row g-4 mb-5">
                    <!-- Card 1: Total Transaksi Outlet -->
                    <div class="col-md-4">
                        <div class="card premium-card summary-card-smooth summary-card-total px-5 py-3 h-100">
                            <i class="fa-solid fa-shop summary-watermark text-primary"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Total Transaksi Outlet</x-text.label>
                                    <x-text.amount id="handover-total-amount" class="fs-2 text-primary lh-1 mt-0.5">
                                        {{ $handoverSummary['handover_total_amount'] ?? 'Rp 0' }}
                                    </x-text.amount>
                                </div>
                                <div class="summary-icon bg-primary text-white shadow-sm flex-shrink-0">
                                    <i class="fa-solid fa-cash-register fs-6 text-white"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500" id="handover-total-count">
                                    {{ $handoverSummary['handover_total_count'] ?? '0 Transaksi' }}
                                </x-text.caption>
                                <span class="badge bg-light-primary text-primary px-2 py-0.5 fs-9 fw-bold rounded-pill">Hak Klaim Outlet</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Dana Sudah Diserahkan -->
                    <div class="col-md-4">
                        <div class="card premium-card summary-card-smooth summary-card-koperasi px-5 py-3 h-100">
                            <i class="fa-solid fa-circle-check summary-watermark text-success"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Dana Sudah Diserahkan</x-text.label>
                                    <x-text.amount id="handover-submitted-amount" class="fs-2 lh-1 mt-0.5" style="color: #10b981 !important;">
                                        {{ $handoverSummary['handover_submitted_amount'] ?? 'Rp 0' }}
                                    </x-text.amount>
                                </div>
                                <div class="summary-icon bg-success text-white shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                                    <i class="fa-solid fa-file-circle-check fs-6 text-white"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500" id="handover-submitted-count">
                                    {{ $handoverSummary['handover_submitted_count'] ?? '0x Penyerahan' }}
                                </x-text.caption>
                                <span class="badge bg-light-success text-success px-2 py-0.5 fs-9 fw-bold rounded-pill">Sudah Diterima Outlet</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Dana Belum Diserahkan -->
                    <div class="col-md-4">
                        <div class="card premium-card summary-card-smooth {{ $initialPendingRaw > 0 ? 'summary-card-danger' : 'summary-card-koperasi' }} px-5 py-3 h-100" id="card-handover-pending">
                            <i class="fa-solid fa-clock-rotate-left summary-watermark {{ $initialPendingRaw > 0 ? 'text-danger' : 'text-success' }}"></i>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="d-flex flex-column">
                                    <x-text.label class="text-slate-400 lh-sm mb-0.5">Dana Belum Diserahkan</x-text.label>
                                    <x-text.amount id="handover-pending-amount" class="fs-2 lh-1 mt-0.5" style="color: {{ $initialPendingRaw > 0 ? '#dc2626' : '#10b981' }} !important;">
                                        {{ $initialPendingText }}
                                    </x-text.amount>
                                </div>
                                <div class="summary-icon text-white shadow-sm flex-shrink-0" id="icon-handover-pending" style="background: {{ $initialPendingRaw > 0 ? 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)' }} !important;">
                                    <i class="fa-solid fa-hand-holding-dollar fs-6 text-white"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                                <x-text.caption class="text-slate-500" id="handover-pending-caption">
                                    {{ $initialPendingRaw > 0 ? 'Sisa Tagihan di Bank Saldo' : 'Seluruh Dana Telah Diserahkan' }}
                                </x-text.caption>
                                <span id="handover-pending-badge" class="badge {{ $initialPendingRaw > 0 ? 'bg-light-danger text-danger' : 'bg-light-success text-success' }} px-2 py-0.5 fs-9 fw-bold rounded-pill">
                                    {{ $initialPendingRaw > 0 ? 'Perlu Diserahkan' : 'Lunas (Rp 0)' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN LAYOUT: FORMULIR SERAH TERIMA (LEFT) & TABEL RIWAYAT (RIGHT) -->
                <div class="row g-5">
                    <!-- Left Column: Formulir Serah Terima Dana -->
                    <div class="col-lg-5">
                        <div class="card premium-card bg-white p-6 h-100">
                            <div class="d-flex align-items-center justify-content-between pb-4 mb-4 border-bottom border-gray-200">
                                <div>
                                    <x-text.h2>{{ ($isOutletStaff ?? false) ? 'Pengajuan Penarikan Dana Outlet' : 'Formulir Serah Terima Dana' }}</x-text.h2>
                                    <x-text.caption class="text-slate-400">{{ ($isOutletStaff ?? false) ? 'Ajukan pencairan dana transaksi saldo santri ke Pengelola Koperasi' : 'Input pencairan dana transaksi saldo dari Koperasi ke Pengelola Outlet' }}</x-text.caption>
                                </div>
                                <div class="summary-icon bg-light-primary text-primary">
                                    <i class="fa-solid {{ ($isOutletStaff ?? false) ? 'fa-paper-plane' : 'fa-hand-holding-dollar' }} fs-6 text-primary"></i>
                                </div>
                            </div>

                            <form id="form-mart-handover" action="{{ ($isOutletStaff ?? false) ? route('report-mart-cashflow.handover.request') : route('report-mart-cashflow.handover.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="recipient_outlet_id" value="{{ $defaultRecipientOutletId }}">

                                <!-- 1. Tanggal Serah Terima -->
                                <div class="mb-4">
                                    <label class="form-label mb-1.5">
                                        <x-text.label class="text-slate-500">Tanggal Serah Terima <span class="text-danger">*</span></x-text.label>
                                    </label>
                                    <input type="date"
                                           name="handover_date"
                                           id="handover-date"
                                           class="form-control form-control-solid fw-bold"
                                           value="{{ now()->format('Y-m-d') }}"
                                           required>
                                </div>

                                <!-- 2. Nominal Diserahkan -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <x-text.label class="text-slate-500">Nominal Dana Diserahkan (Rp) <span class="text-danger">*</span></x-text.label>
                                        <button type="button"
                                                class="btn btn-link btn-color-primary btn-active-color-primary p-0 fs-8 fw-bolder text-decoration-none"
                                                id="btn-fill-pending">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1 fs-9"></i> Isi Sisa Belum Diserahkan
                                        </button>
                                    </div>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text fw-bolder text-success ps-4 pe-2" style="color: #10b981 !important;">Rp</span>
                                        <input type="text"
                                               id="handover-amount-display"
                                               class="form-control form-control-solid fw-bolder fs-5"
                                               style="color: #10b981;"
                                               placeholder="0"
                                               value="{{ $initialPendingRaw > 0 ? number_format($initialPendingRaw, 0, ',', '.') : '' }}"
                                               autocomplete="off"
                                               required>
                                        <input type="hidden"
                                               name="amount"
                                               id="handover-amount-raw"
                                               value="{{ $initialPendingRaw > 0 ? (int) $initialPendingRaw : '' }}">
                                    </div>
                                </div>

                                <!-- 3. Nama Pemberi & Nama Penerima (Select User) -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label mb-1.5">
                                            <x-text.label class="text-slate-500">Nama Pemberi (Koperasi) <span class="text-danger">*</span></x-text.label>
                                        </label>
                                        <select name="sender_id"
                                                id="handover-sender-id"
                                                class="form-select form-select-solid fw-semibold"
                                                data-control="select2"
                                                data-hide-search="true"
                                                data-placeholder="Pilih User Pemberi"
                                                required>
                                            <option value="">-- Pilih User Pemberi --</option>
                                            @foreach($koperasiAdmins ?? $admins as $adm)
                                                <option value="{{ $adm->id }}" {{ $defaultSenderId === $adm->id ? 'selected' : '' }}>
                                                    {{ $adm->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label mb-1.5">
                                            <x-text.label class="text-slate-500">Nama Penerima (Outlet) <span class="text-danger">*</span></x-text.label>
                                        </label>
                                        <select name="recipient_id"
                                                id="handover-recipient-id"
                                                class="form-select form-select-solid fw-semibold"
                                                data-control="select2"
                                                data-hide-search="true"
                                                data-placeholder="Pilih User Penerima"
                                                required>
                                            <option value="">-- Pilih User Penerima --</option>
                                            @foreach($outletAdmins ?? $admins as $adm)
                                                <option value="{{ $adm->id }}" {{ $defaultRecipientId === $adm->id ? 'selected' : '' }}>
                                                    {{ $adm->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- 4. Upload Bukti Transfer -->
                                <div class="mb-4">
                                    <label class="form-label mb-1.5">
                                        <x-text.label class="text-slate-500">Upload Bukti Transfer / Serah Terima</x-text.label>
                                    </label>
                                    <div class="upload-dropzone-area p-3 text-center position-relative" id="upload-trigger-box">
                                        <input type="file"
                                               name="evidence"
                                               id="handover-evidence"
                                               class="d-none"
                                               accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf">
                                        <div id="evidence-placeholder">
                                            <i class="fa-solid fa-cloud-arrow-up fs-2 text-primary mb-1"></i>
                                            <div class="fw-bold text-gray-800 fs-7">Klik untuk pilih foto bukti transfer / struk</div>
                                            <x-text.caption class="text-slate-400">Format: JPG, PNG, WEBP, atau PDF (Maks. 3MB)</x-text.caption>
                                        </div>
                                        <div id="evidence-preview-box" class="d-none">
                                            <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded-3 shadow-xs">
                                                <div class="d-flex align-items-center gap-2 text-start overflow-hidden">
                                                    <i class="fa-solid fa-file-circle-check fs-3 text-success ms-1"></i>
                                                    <div class="text-truncate">
                                                        <div class="fw-bold text-gray-800 fs-8 text-truncate" id="evidence-filename">-</div>
                                                        <div class="text-success fs-9 fw-semibold">Siap diunggah</div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-icon btn-sm btn-light-danger w-25px h-25px rounded-circle flex-shrink-0" id="btn-clear-evidence" title="Hapus File">
                                                    <i class="fa-solid fa-xmark fs-8"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Keterangan -->
                                <div class="mb-5">
                                    <label class="form-label mb-1.5">
                                        <x-text.label class="text-slate-500">Keterangan / Catatan</x-text.label>
                                    </label>
                                    <textarea name="notes"
                                              id="handover-notes"
                                              rows="2"
                                              class="form-control form-control-solid fw-semibold fs-7"
                                              placeholder="Contoh: Pencairan dana transaksi saldo santri di Outlet..."></textarea>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100 fw-bolder py-3 shadow-sm" id="btn-submit-handover" style="border-radius: 14px;">
                                    @if($isOutletStaff ?? false)
                                        <i class="fa-solid fa-paper-plane me-2"></i> Kirim Pengajuan Penarikan ke Koperasi
                                    @else
                                        <i class="fa-solid fa-check-double me-2"></i> Simpan & Serahkan Dana ke Outlet
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Tabel Riwayat Serah Terima Dana -->
                    <div class="col-lg-7">
                        <div class="card premium-card bg-white p-6 h-100">
                            <div class="card-header border-0 px-0 pt-0 pb-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                <div>
                                    <x-text.h2>Riwayat Serah Terima Dana (Koperasi ➔ Outlet)</x-text.h2>
                                    <x-text.caption class="text-slate-400">Daftar rekapitulasi penyerahan dana transaksi saldo dari Bank Saldo ke Pengelola Outlet</x-text.caption>
                                </div>
                                <button type="button" class="btn btn-sm btn-light-primary fw-bold align-self-start align-self-sm-center" id="btn-view-outlet-trx">
                                    <i class="fa-solid fa-list-check me-1 fs-8"></i> Lihat Rincian Transaksi Outlet
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-7 gy-3 gs-3" id="table-mart-handover">
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-8 text-uppercase gs-0" style="background-color: #f8fafc; border-radius: 12px;">
                                            <th class="w-35px ps-3">No</th>
                                            <th class="min-w-100px">Tanggal</th>
                                            <th class="min-w-130px">Pemberi (Koperasi)</th>
                                            <th class="min-w-130px">Penerima (Outlet)</th>
                                            <th class="min-w-120px">Nominal</th>
                                            <th class="min-w-130px">Keterangan</th>
                                            <th class="min-w-100px text-center">Bukti Transfer</th>
                                            <th class="min-w-110px text-center">Status</th>
                                            <th class="min-w-90px text-center pe-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 fw-semibold">
                                        <!-- Populated via AJAX DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!--end::Post-->
</div>

<!-- Modal Preview Bukti Transfer -->
<div class="modal fade" id="modal-preview-evidence" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content premium-card overflow-hidden">
            <div class="modal-header border-0 pb-2 pt-5 px-6 d-flex align-items-center justify-content-between">
                <div>
                    <x-text.h2>Bukti Transfer Serah Terima Dana</x-text.h2>
                    <x-text.caption class="text-slate-400" id="modal-evidence-caption">Nominal: -</x-text.caption>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-light-danger rounded-circle" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark fs-6"></i>
                </button>
            </div>
            <div class="modal-body px-6 py-4 text-center">
                <div class="bg-light rounded-3 p-2 mb-3">
                    <img src="" id="modal-evidence-img" alt="Bukti Transfer" class="img-fluid rounded-3" style="max-height: 420px; object-fit: contain;">
                </div>
                <a href="#" id="modal-evidence-download" target="_blank" class="btn btn-sm btn-light-primary fw-bold">
                    <i class="fa-solid fa-up-right-from-square me-1 fs-8"></i> Buka Ukuran Penuh di Tab Baru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Serahkan Dana ke Outlet (Upload Bukti Transfer) -->
<div class="modal fade" id="modal-accept-handover" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content premium-card overflow-hidden">
            <div class="modal-header border-0 pb-2 pt-5 px-6 d-flex align-items-center justify-content-between">
                <div>
                    <x-text.h2>Serahkan Dana ke Outlet</x-text.h2>
                    <x-text.caption class="text-slate-400">Unggah foto bukti transfer / struk untuk menyelesaikan serah terima</x-text.caption>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-light-secondary rounded-circle" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark fs-6"></i>
                </button>
            </div>
            <form id="form-accept-handover" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-6 py-4">
                    <div class="p-3 mb-4 rounded-3" style="background-color: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-gray-600 fs-7 fw-semibold">Nominal Diserahkan:</span>
                            <span class="text-success fw-bolder fs-4" id="accept-modal-amount">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-gray-600 fs-8 fw-semibold">Penerima (Outlet):</span>
                            <span class="text-gray-900 fw-bold fs-7" id="accept-modal-recipient">-</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-1.5 fs-8 fw-bold text-gray-700">Foto Bukti Transfer / Struk Penyerahan <span class="text-danger">*</span></label>
                        <input type="file" name="evidence" id="accept-handover-evidence" class="form-control form-control-solid fs-7" accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf" required>
                        <span class="text-muted fs-9 mt-1 d-block">Format: JPG, PNG, WEBP, atau PDF (Maks. 3MB)</span>
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1.5 fs-8 fw-bold text-gray-700">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" id="accept-handover-notes" rows="2" class="form-control form-control-solid fs-7" placeholder="Contoh: Transfer via BCA / Mandiri Rekening Outlet..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-5 px-6 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                    <button type="submit" class="btn btn-success fw-bolder px-5 py-2" id="btn-submit-accept-handover" style="border-radius: 12px;">
                        <i class="fa-solid fa-paper-plane me-1"></i> Transfer & Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        let currentSource = '{{ $defaultSource }}';
        let currentPeriod = 'all';
        let startDate = '';
        let endDate = '';
        let currentPendingRaw = {{ (int) ($handoverSummary['handover_pending_raw'] ?? 0) }};
        let userManuallyEditedAmount = false;
        const isRestricted = {{ $isRestricted ? 'true' : 'false' }};

        // Format angka ke ribuan Indonesia
        function formatRupiahNumber(num) {
            const clean = String(num || '').replace(/\D/g, '');
            if (!clean) return '';
            return parseInt(clean, 10).toLocaleString('id-ID');
        }

        // Switching Top Tabs (Riwayat Belanja Saldo vs Serah Terima Dana)
        function switchMartTab(tabName) {
            $('.mart-tab-btn').removeClass('active');
            if (tabName === 'handover') {
                $('#btn-tab-handover').addClass('active');
                $('#pane-riwayat').addClass('d-none');
                $('#pane-handover').removeClass('d-none');
                if (typeof handoverTable !== 'undefined') {
                    handoverTable.columns.adjust();
                }
            } else {
                $('#btn-tab-riwayat').addClass('active');
                $('#pane-handover').addClass('d-none');
                $('#pane-riwayat').removeClass('d-none');
                if (typeof table !== 'undefined') {
                    table.columns.adjust();
                }
            }
        }

        $('.mart-tab-btn').on('click', function() {
            switchMartTab($(this).data('tab'));
        });

        $('#btn-goto-handover').on('click', function() {
            switchMartTab('handover');
        });

        $('#btn-view-outlet-trx').on('click', function() {
            if (!isRestricted) {
                $('.btn-source-filter').removeClass('active');
                $('.btn-source-filter[data-source="outlet"]').addClass('active');
                currentSource = 'outlet';
                loadSummary();
                table.ajax.reload();
            }
            switchMartTab('riwayat');
        });

        function setActivePeriodBtn(period) {
            $('.btn-period').removeClass('active btn-primary text-white').addClass('btn-light');
            if (period) {
                $('.btn-period[data-period="' + period + '"]').removeClass('btn-light').addClass('active btn-primary text-white');
            }
        }

        function applyCustomDateRange(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');

            if (start.isSame(end, 'day')) {
                $('#filter-daterange').val(start.format('DD MMM YYYY'));
            } else {
                $('#filter-daterange').val(startDate + ' s/d ' + endDate);
            }

            // Sinkronisasi highlight tombol Preset Periode jika rentang tanggal cocok
            if (start.isSame(moment(), 'day') && end.isSame(moment(), 'day')) {
                currentPeriod = 'today';
                setActivePeriodBtn('today');
            } else if (start.isSame(moment().startOf('isoWeek'), 'day') && end.isSame(moment().endOf('isoWeek'), 'day')) {
                currentPeriod = 'week';
                setActivePeriodBtn('week');
            } else if (start.isSame(moment().startOf('month'), 'day') && end.isSame(moment().endOf('month'), 'day')) {
                currentPeriod = 'month';
                setActivePeriodBtn('month');
            } else {
                currentPeriod = 'custom';
                setActivePeriodBtn(null);
            }

            loadSummary();
            table.ajax.reload();
            handoverTable.ajax.reload();
        }

        // Inisialisasi Date Range Picker
        $('#filter-daterange').daterangepicker({
            autoUpdateInput: false,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            showDropdowns: true,
            alwaysShowCalendars: true,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                'Minggu Ini': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
            },
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' s/d ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Semua Tanggal',
                customRangeLabel: 'Pilih Rentang Bebas',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                firstDay: 1
            }
        }, function(start, end) {
            applyCustomDateRange(start, end);
        });

        $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
            applyCustomDateRange(picker.startDate, picker.endDate);
        });

        $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
            $('#filter-daterange').val('Semua Tanggal');
            startDate = '';
            endDate = '';
            currentPeriod = 'all';
            setActivePeriodBtn('all');
            loadSummary();
            table.ajax.reload();
            handoverTable.ajax.reload();
        });

        // Filter Sumber Transaksi (Pondok Mart / Koperasi vs Outlet)
        $('.btn-source-filter').on('click', function() {
            $('.btn-source-filter').removeClass('active');
            $(this).addClass('active');
            currentSource = $(this).data('source');

            loadSummary();
            table.ajax.reload();
        });

        // Quick Period Buttons
        $('.btn-period').on('click', function() {
            const period = $(this).data('period');
            currentPeriod = period;
            setActivePeriodBtn(period);

            const picker = $('#filter-daterange').data('daterangepicker');

            if (period === 'today') {
                const today = moment();
                startDate = today.format('YYYY-MM-DD');
                endDate = today.format('YYYY-MM-DD');
                $('#filter-daterange').val(today.format('DD MMM YYYY'));
                if (picker) {
                    picker.setStartDate(today);
                    picker.setEndDate(today);
                }
            } else if (period === 'week') {
                const startWeek = moment().startOf('isoWeek');
                const endWeek = moment().endOf('isoWeek');
                startDate = startWeek.format('YYYY-MM-DD');
                endDate = endWeek.format('YYYY-MM-DD');
                $('#filter-daterange').val(startDate + ' s/d ' + endDate);
                if (picker) {
                    picker.setStartDate(startWeek);
                    picker.setEndDate(endWeek);
                }
            } else if (period === 'month') {
                const startMonth = moment().startOf('month');
                const endMonth = moment().endOf('month');
                startDate = startMonth.format('YYYY-MM-DD');
                endDate = endMonth.format('YYYY-MM-DD');
                $('#filter-daterange').val(startDate + ' s/d ' + endDate);
                if (picker) {
                    picker.setStartDate(startMonth);
                    picker.setEndDate(endMonth);
                }
            } else {
                startDate = '';
                endDate = '';
                $('#filter-daterange').val('Semua Tanggal');
            }

            loadSummary();
            table.ajax.reload();
            handoverTable.ajax.reload();
        });

        // Reset Filter Button
        $('#btn-reset-filter').on('click', function() {
            userManuallyEditedAmount = false;
            currentSource = isRestricted ? 'outlet' : 'all';
            currentPeriod = 'all';
            startDate = '';
            endDate = '';

            $('#filter-daterange').val('Semua Tanggal');

            $('.btn-source-filter').removeClass('active');
            $('.btn-source-filter[data-source="' + currentSource + '"]').addClass('active');

            setActivePeriodBtn('all');

            loadSummary();
            table.ajax.reload();
            handoverTable.ajax.reload();
        });

        // Refresh Button
        $('#btn-refresh').on('click', function() {
            loadSummary();
            table.ajax.reload();
            handoverTable.ajax.reload();
        });

        // DataTables 1: Riwayat Belanja Saldo
        const table = $('#table-mart-cashflow').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report-mart-cashflow.index') }}",
                data: function(d) {
                    d.source = currentSource;
                    d.period = currentPeriod;
                    d.start_date = startDate;
                    d.end_date = endDate;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4' },
                { data: 'payment_code', name: 'payment_code' },
                { data: 'date', name: 'created_at' },
                { data: 'student', name: 'student.name' },
                { data: 'outlet', name: 'outlet.name' },
                { data: 'details', name: 'details', orderable: false, searchable: false },
                { data: 'pay_amount', name: 'pay_amount' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-4' }
            ],
            order: [[2, 'desc']],
            pageLength: 25,
            language: {
                search: "Cari Siswa/No. Trx:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ transaksi",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Tidak ada transaksi belanja siswa ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });

        // Inisialisasi Select2 tanpa kotak pencarian untuk kedua dropdown (Pemberi & Penerima)
        if ($.fn.select2) {
            $('#handover-sender-id, #handover-recipient-id').select2({
                minimumResultsForSearch: Infinity
            });
        }

        // DataTables 2: Riwayat Serah Terima Dana (Handover Koperasi -> Outlet)
        const handoverTable = $('#table-mart-handover').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: "{{ route('report-mart-cashflow.index') }}",
                data: function(d) {
                    d.data = 'handover_table';
                    d.period = currentPeriod;
                    d.start_date = startDate;
                    d.end_date = endDate;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-3' },
                { data: 'date', name: 'handover_date' },
                { data: 'sender', name: 'sender', orderable: false },
                { data: 'recipient', name: 'recipient_name' },
                { data: 'amount', name: 'amount' },
                { data: 'notes', name: 'notes' },
                { data: 'evidence', name: 'evidence', orderable: false, searchable: false, className: 'text-center' },
                { data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-3' }
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            language: {
                search: "Cari Penerima/Catatan:",
                lengthMenu: "_MENU_ baris",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ penyerahan",
                infoEmpty: "Belum ada data penyerahan",
                zeroRecords: "Belum ada riwayat serah terima dana ke Outlet",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });

        // Update UI Kartu Handover & Badge Tab
        function updateHandoverSummaryUI(res) {
            if (typeof res.handover_total_amount === 'undefined') return;

            $('#handover-total-amount').text(res.handover_total_amount || 'Rp 0');
            $('#handover-total-count').text(res.handover_total_count || '0 Transaksi');
            $('#handover-submitted-amount').text(res.handover_submitted_amount || 'Rp 0');
            $('#handover-submitted-count').text(res.handover_submitted_count || '0x Penyerahan');
            $('#handover-pending-amount').text(res.handover_pending_amount || 'Rp 0');

            currentPendingRaw = parseInt(res.handover_pending_raw || 0, 10);

            if (currentPendingRaw > 0) {
                $('#handover-pending-amount').attr('style', 'color: #dc2626 !important;');
                $('#card-handover-pending').removeClass('summary-card-koperasi').addClass('summary-card-danger');
                $('#icon-handover-pending').attr('style', 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;');
                $('#handover-pending-caption').text('Sisa Tagihan di Bank Saldo');
                $('#handover-pending-badge')
                    .removeClass('bg-light-success text-success')
                    .addClass('bg-light-danger text-danger')
                    .text('Perlu Diserahkan');
                $('#tab-badge-pending')
                    .attr('style', 'background-color: rgba(245, 158, 11, 0.14); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);')
                    .text('Belum Diserahkan: ' + res.handover_pending_amount);
            } else {
                $('#handover-pending-amount').attr('style', 'color: #10b981 !important;');
                $('#card-handover-pending').removeClass('summary-card-danger').addClass('summary-card-koperasi');
                $('#icon-handover-pending').attr('style', 'background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;');
                $('#handover-pending-caption').text('Seluruh Dana Telah Diserahkan');
                $('#handover-pending-badge')
                    .removeClass('bg-light-danger text-danger')
                    .addClass('bg-light-success text-success')
                    .text('Lunas (Rp 0)');
                $('#tab-badge-pending')
                    .attr('style', 'background-color: rgba(16, 185, 129, 0.14); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);')
                    .text('Lunas Diserahkan');
            }

            // Tampilkan default nominal sebesar sisa dana existing yang ada bila belum diedit manual oleh user
            if (!userManuallyEditedAmount) {
                const defaultNominal = currentPendingRaw > 0 ? currentPendingRaw : 0;
                $('#handover-amount-raw').val(defaultNominal > 0 ? defaultNominal : '');
                $('#handover-amount-display').val(defaultNominal > 0 ? formatRupiahNumber(defaultNominal) : '');
            }
        }

        // Fungsi Load Summary Cards (Tab 1 & Tab 2)
        function loadSummary() {
            $.ajax({
                url: "{{ route('report-mart-cashflow.index') }}",
                type: "GET",
                data: {
                    data: 'summary',
                    source: currentSource,
                    period: currentPeriod,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(res) {
                    $('#stat-total-amount').text(res.total_amount || 'Rp 0');
                    $('#stat-total-count').text(res.total_count || '0 Transaksi');
                    $('#stat-outlet-amount').text(res.outlet_amount || 'Rp 0');
                    $('#stat-outlet-count').text(res.outlet_count || '0 Transaksi');
                    $('#stat-koperasi-amount').text(res.koperasi_amount || 'Rp 0');
                    $('#stat-koperasi-count').text(res.koperasi_count || '0 Transaksi');
                    $('#stat-avg-amount').text(res.avg_amount || 'Rp 0');

                    updateHandoverSummaryUI(res);
                },
                error: function() {
                    console.error('Gagal memuat ringkasan arus kas mart');
                }
            });
        }

        // Format Input Nominal Serah Terima
        $('#handover-amount-display').on('input', function() {
            userManuallyEditedAmount = true;
            const rawDigits = $(this).val().replace(/\D/g, '');
            $('#handover-amount-raw').val(rawDigits);
            $(this).val(formatRupiahNumber(rawDigits));
        });

        // Tombol "Isi Sisa Belum Diserahkan"
        $('#btn-fill-pending').on('click', function() {
            userManuallyEditedAmount = true;
            const targetAmount = currentPendingRaw > 0 ? currentPendingRaw : 0;
            $('#handover-amount-raw').val(targetAmount > 0 ? targetAmount : '');
            $('#handover-amount-display').val(targetAmount > 0 ? formatRupiahNumber(targetAmount) : '0');
        });

        // Upload Bukti Transfer Interaksi
        $('#upload-trigger-box').on('click', function(e) {
            if ($(e.target).closest('#btn-clear-evidence').length) return;
            $('#handover-evidence')[0].click();
        });

        $('#handover-evidence').on('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                $('#evidence-filename').text(file.name);
                $('#evidence-placeholder').addClass('d-none');
                $('#evidence-preview-box').removeClass('d-none');
            } else {
                $('#evidence-placeholder').removeClass('d-none');
                $('#evidence-preview-box').addClass('d-none');
            }
        });

        $('#btn-clear-evidence').on('click', function(e) {
            e.stopPropagation();
            $('#handover-evidence').val('');
            $('#evidence-placeholder').removeClass('d-none');
            $('#evidence-preview-box').addClass('d-none');
        });

        // Submit Form Serah Terima Dana via AJAX
        $('#form-mart-handover').on('submit', function(e) {
            e.preventDefault();

            const rawVal = parseInt($('#handover-amount-raw').val() || '0', 10);
            if (!rawVal || rawVal <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Nominal Kosong', text: 'Masukkan nominal dana yang diserahkan lebih dari Rp 0.' });
                } else {
                    alert('Masukkan nominal dana yang diserahkan lebih dari Rp 0.');
                }
                return;
            }

            const formData = new FormData(this);
            const $btn = $('#btn-submit-handover');
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan Serah Terima...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(res) {
                    $btn.prop('disabled', false).html(originalHtml);
                    userManuallyEditedAmount = false;

                    // Reset bukti & catatan, lalu muat ulang metrik dan tabel
                    $('#handover-evidence').val('');
                    $('#evidence-placeholder').removeClass('d-none');
                    $('#evidence-preview-box').addClass('d-none');
                    $('#handover-notes').val('');

                    loadSummary();
                    handoverTable.ajax.reload();

                    setTimeout(function() {
                        const updatedPending = currentPendingRaw > 0 ? currentPendingRaw : '';
                        $('#handover-amount-raw').val(updatedPending);
                        $('#handover-amount-display').val(updatedPending ? formatRupiahNumber(updatedPending) : '');
                    }, 350);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Serah Terima Tercatat!',
                            text: res.message || 'Dana berhasil diserahkan ke Pengelola Outlet.',
                            timer: 2200,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalHtml);
                    const msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Gagal menyimpan data serah terima dana.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: msg });
                    } else {
                        alert(msg);
                    }
                }
            });
        });

        // Preview Bukti Transfer Modal
        $(document).on('click', '.btn-preview-evidence', function() {
            const url = $(this).data('url');
            const amount = $(this).data('amount');
            $('#modal-evidence-img').attr('src', url);
            $('#modal-evidence-download').attr('href', url);
            $('#modal-evidence-caption').text('Nominal Diserahkan: ' + amount);
            $('#modal-preview-evidence').modal('show');
        });

        // Buka Modal Terima Pengajuan / Transfer Dana ke Outlet
        $(document).on('click', '.btn-accept-handover', function() {
            const acceptUrl = $(this).data('url');
            const amount = $(this).data('amount');
            const recipient = $(this).data('recipient');

            $('#form-accept-handover').attr('action', acceptUrl);
            $('#accept-modal-amount').text(amount);
            $('#accept-modal-recipient').text(recipient);
            $('#accept-handover-evidence').val('');
            $('#accept-handover-notes').val('');
            $('#modal-accept-handover').modal('show');
        });

        // Submit Form Accept Handover (Upload Bukti Transfer)
        $('#form-accept-handover').on('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('accept-handover-evidence');
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Bukti Belum Dipilih', text: 'Wajib mengunggah foto bukti transfer / struk penyerahan.' });
                } else {
                    alert('Wajib mengunggah foto bukti transfer / struk penyerahan.');
                }
                return;
            }

            const formData = new FormData(this);
            const $btn = $('#btn-submit-accept-handover');
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Mengunggah...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(res) {
                    $btn.prop('disabled', false).html(originalHtml);
                    $('#modal-accept-handover').modal('hide');
                    loadSummary();
                    handoverTable.ajax.reload();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Serah Terima Selesai!',
                            text: res.message || 'Dana berhasil diserahkan ke Outlet.',
                            timer: 2200,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalHtml);
                    const msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Gagal menyelesaikan serah terima dana.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    } else {
                        alert(msg);
                    }
                }
            });
        });

        // Revisi Nominal Pengajuan oleh Koperasi
        $(document).on('click', '.btn-revise-handover', function() {
            const reviseUrl = $(this).data('url');
            const currentAmountFormatted = $(this).data('amount');
            const currentAmountRaw = $(this).data('amount-raw') || 0;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Revisi Nominal Penarikan',
                    html: '<div class="text-start fs-7 text-gray-700">' +
                          '<p class="mb-2">Nominal Pengajuan Semula: <strong class="text-primary">' + currentAmountFormatted + '</strong></p>' +
                          '<div class="mb-3">' +
                          '  <label class="form-label fw-bold text-gray-800 fs-8 mb-1">Nominal Revisi Baru (Rp) <span class="text-danger">*</span>:</label>' +
                          '  <input type="number" id="swal-revise-amount" class="form-control form-control-solid fs-7 fw-bold" placeholder="Contoh: 500000" min="1" value="' + currentAmountRaw + '">' +
                          '</div>' +
                          '<div>' +
                          '  <label class="form-label fw-bold text-gray-800 fs-8 mb-1">Alasan Penyesuaian / Revisi <span class="text-danger">*</span>:</label>' +
                          '  <textarea id="swal-revise-reason" rows="2" class="form-control form-control-solid fs-7" placeholder="Contoh: Saldo kas tunai yang tersedia saat ini Rp 500.000, sisanya besok..."></textarea>' +
                          '</div>' +
                          '<p class="mb-0 text-muted fst-italic fs-9 mt-2">*Pengajuan revisi akan dikirim ke Pengelola Outlet untuk disetujui terlebih dahulu.</p>' +
                          '</div>',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> Ajukan Revisi',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const newAmount = parseInt(document.getElementById('swal-revise-amount').value || '0', 10);
                        const reason = (document.getElementById('swal-revise-reason').value || '').trim();

                        if (!newAmount || newAmount <= 0) {
                            Swal.showValidationMessage('Nominal revisi harus lebih dari Rp 0!');
                            return false;
                        }
                        if (!reason || reason.length < 3) {
                            Swal.showValidationMessage('Alasan revisi wajib diisi (minimal 3 karakter)!');
                            return false;
                        }

                        return { amount: newAmount, reason: reason };
                    }
                }).then(function(result) {
                    if (result.isConfirmed && result.value) {
                        $.ajax({
                            url: reviseUrl,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                amount: result.value.amount,
                                reason: result.value.reason
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            success: function(res) {
                                loadSummary();
                                handoverTable.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Revisi Terkirim!',
                                    text: res.message || 'Nominal revisi telah diajukan ke Outlet.',
                                    timer: 2200,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                    ? xhr.responseJSON.message
                                    : 'Gagal mengajukan revisi nominal.';
                                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                            }
                        });
                    }
                });
            }
        });

        // Setujui Revisi Nominal oleh Outlet
        $(document).on('click', '.btn-accept-revision', function() {
            const acceptRevisionUrl = $(this).data('url');
            const amount = $(this).data('amount');

            const executeAcceptRevision = function() {
                $.ajax({
                    url: acceptRevisionUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(res) {
                        loadSummary();
                        handoverTable.ajax.reload();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Revisi Disetujui!',
                                text: res.message || 'Persetujuan revisi berhasil disimpan.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Gagal menyetujui revisi nominal.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                        } else {
                            alert(msg);
                        }
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Setujui Revisi Nominal?',
                    html: '<div class="text-start fs-7 text-gray-700">' +
                          '<p class="mb-2">Anda akan menyetujui penyesuaian nominal penarikan dana menjadi:</p>' +
                          '<div class="p-3 rounded mb-2 text-center" style="background-color: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2);">' +
                          '<span class="text-success fw-bolder fs-4">' + amount + '</span>' +
                          '</div>' +
                          '<p class="mb-0 text-muted fst-italic fs-8">*Setelah disetujui, Koperasi dapat segera melakukan proses transfer dan serah terima dana.</p>' +
                          '</div>',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-thumbs-up me-1"></i> Ya, Setujui',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        executeAcceptRevision();
                    }
                });
            } else if (confirm('Setujui revisi nominal sebesar ' + amount + '?')) {
                executeAcceptRevision();
            }
        });

        // Konfirmasi Penerimaan Serah Terima Dana
        $(document).on('click', '.btn-confirm-handover', function() {
            const confirmUrl = $(this).data('url');
            const amount = $(this).data('amount');
            const sender = $(this).data('sender');

            const executeConfirm = function() {
                $.ajax({
                    url: confirmUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(res) {
                        loadSummary();
                        handoverTable.ajax.reload();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dikonfirmasi!',
                                text: res.message || 'Serah terima dana berhasil dikonfirmasi.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Gagal mengonfirmasi serah terima dana.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                        } else {
                            alert(msg);
                        }
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Penerimaan Dana?',
                    html: '<div class="text-start fs-7 text-gray-700">' +
                          '<p class="mb-2">Pastikan Anda telah memeriksa mutasi transfer atau dana tunai yang diserahkan:</p>' +
                          '<div class="p-3 rounded mb-2" style="background-color: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2);">' +
                          '<div><strong>Nominal Diserahkan:</strong> <span class="text-success fw-bolder fs-6">' + amount + '</span></div>' +
                          '<div class="text-muted fs-8 mt-1"><strong>Pemberi (Koperasi):</strong> ' + sender + '</div>' +
                          '</div>' +
                          '<p class="mb-0 text-muted fst-italic fs-8">*Setelah dikonfirmasi, transaksi ini resmi selesai dan tercatat aktif pada arus kas penerima.</p>' +
                          '</div>',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Ya, Konfirmasi Terima',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        executeConfirm();
                    }
                });
            } else if (confirm('Konfirmasi penerimaan dana sebesar ' + amount + '?')) {
                executeConfirm();
            }
        });

        // Tolak / Batalkan Serah Terima Dana (Rollback)
        $(document).on('click', '.btn-reject-handover', function() {
            const rejectUrl = $(this).data('url');
            const amount = $(this).data('amount');
            const actionLabel = $(this).data('action-label') || 'Batalkan';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: actionLabel + ' Serah Terima Dana?',
                    html: '<div class="text-start fs-7 text-gray-700">' +
                          '<p class="mb-2">Transaksi sebesar <strong class="text-danger">' + amount + '</strong> akan di-rollback kembali utuh ke tagihan dana belum diserahkan.</p>' +
                          '<label class="form-label fw-bold text-gray-800 fs-8 mt-2">Alasan Pembatalan / Masalah Transfer <span class="text-danger">*</span>:</label>' +
                          '</div>',
                    input: 'textarea',
                    inputPlaceholder: 'Tuliskan alasan spesifik di sini (minimal 3 karakter)...',
                    inputAttributes: {
                        'aria-label': 'Tuliskan alasan di sini',
                        'style': 'height: 90px; font-size: 13px; border-radius: 10px;'
                    },
                    inputValidator: (value) => {
                        if (!value || value.trim().length < 3) {
                            return 'Alasan pembatalan / penolakan wajib diisi (minimal 3 karakter)!';
                        }
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-ban me-1"></i> Ya, ' + actionLabel,
                    cancelButtonText: 'Kembali'
                }).then(function(result) {
                    if (result.isConfirmed && result.value) {
                        $.ajax({
                            url: rejectUrl,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                reason: result.value
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            success: function(res) {
                                loadSummary();
                                handoverTable.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil ' + actionLabel,
                                    text: res.message || 'Transaksi telah dibatalkan dan saldo di-rollback.',
                                    timer: 2200,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                    ? xhr.responseJSON.message
                                    : 'Gagal memproses pembatalan serah terima.';
                                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                            }
                        });
                    }
                });
            } else {
                const reason = prompt('Tuliskan alasan pembatalan / penolakan serah terima ' + amount + ':');
                if (reason && reason.trim().length >= 3) {
                    $.ajax({
                        url: rejectUrl,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: reason
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function(res) {
                            loadSummary();
                            handoverTable.ajax.reload();
                            alert(res.message || 'Transaksi dibatalkan.');
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON ? xhr.responseJSON.message : 'Gagal.');
                        }
                    });
                }
            }
        });

        // Hapus Riwayat Serah Terima Dana
        $(document).on('click', '.btn-delete-handover', function() {
            const deleteUrl = $(this).data('url');
            const amount = $(this).data('amount');

            const executeDelete = function() {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(res) {
                        loadSummary();
                        handoverTable.ajax.reload();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message || 'Riwayat serah terima dana berhasil dihapus.',
                                timer: 1800,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Gagal menghapus riwayat serah terima dana.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                        } else {
                            alert(msg);
                        }
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Serah Terima Dana?',
                    text: 'Data penyerahan sebesar ' + amount + ' beserta pencatatan arus kas terkait akan dihapus.',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        executeDelete();
                    }
                });
            } else if (confirm('Hapus data serah terima sebesar ' + amount + '?')) {
                executeDelete();
            }
        });

        // Export Excel Action
        $('#btn-export-excel').on('click', function() {
            const params = new URLSearchParams({
                source: currentSource,
                period: currentPeriod,
                start_date: startDate,
                end_date: endDate
            });
            window.location.href = "{{ route('report-mart-cashflow.export') }}?" + params.toString();
        });

        // Initial Load
        loadSummary();
    });
</script>
@endpush
