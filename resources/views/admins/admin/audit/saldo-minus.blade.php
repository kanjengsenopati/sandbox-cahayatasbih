@extends('layouts.master', ['title' => 'Audit Saldo Minus'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Audit Saldo Minus Santri</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.audit.sync') }}" class="text-muted text-hover-primary">Audit dan Sinkron</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Daftar & Forensik Saldo Minus</li>
                </ul>
            </div>
            <!--end::Page title-->

            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.audit.saldo-minus', ['refresh' => 1]) }}"
                   class="btn btn-sm btn-light-primary fw-bolder">
                    <i class="fas fa-sync-alt me-1 fs-7"></i> Refresh Ringkasan
                </a>
                <a href="{{ route('admin.audit.saldo-minus.export') }}"
                   class="btn btn-sm btn-primary fw-bolder">
                    <i class="fas fa-file-excel me-1 fs-7"></i> Export CSV / Excel
                </a>
            </div>
            <!--end::Actions-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">

            <!--begin::Alert Card: Ringkasan Forensik-->
            <div class="card mb-6 border border-primary border-dashed bg-light-primary rounded-3">
                <div class="card-body p-5">
                    <div class="d-flex align-items-start">
                        <div class="symbol symbol-45px symbol-circle bg-primary text-white d-flex align-items-center justify-content-center me-4 mt-1">
                            <i class="fas fa-shield-alt text-white fs-2"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h4 class="text-gray-900 fw-bolder mb-0">Fakta Audit Forensik & Akar Masalah Saldo Minus</h4>
                                <span class="badge badge-light-success fw-bolder fs-8 px-3 py-1">
                                    <i class="fas fa-lock me-1 text-success"></i> Sistem Baru Sudah Diproteksi (Atomic Guard Aktif)
                                </span>
                            </div>
                            <p class="text-gray-700 fs-7 mb-2">
                                Saldo minus santri di sistem lama terjadi terutama akibat <strong>transaksi belanja santri lewat fitur Kasir (PoS keranjang belanja)</strong> tanpa batasan kasbon (mencakup 73% santri), serta <strong>pemotongan tagihan</strong> yang mengeksekusi pelunasan tanpa memvalidasi kecukupan saldo. Di aplikasi HP wali santri, kartu saldo dinonaktifkan sehingga orang tua tidak melihat angka minus.
                            </p>
                            <div class="d-flex flex-wrap gap-3 fs-8 text-gray-600">
                                <span><i class="fas fa-database text-primary me-1"></i> Sumber Audit: <strong class="text-primary">{{ $connName }}</strong></span>
                                <span><i class="fas fa-users text-danger me-1"></i> Total Korban Minus: <strong class="text-danger">{{ number_format($summary['total_count']) }} Santri</strong></span>
                                <span><i class="fas fa-shopping-basket text-warning me-1"></i> Belanja Kasir (PoS) Murni: <strong class="text-warning">{{ number_format($summary['pos_only_count']) }} Santri ({{ round(($summary['pos_only_count'] / max(1, $summary['total_count'])) * 100) }}%)</strong></span>
                                <span><i class="fas fa-file-invoice-dollar text-primary me-1"></i> Terpotong Tagihan: <strong class="text-primary">{{ number_format($summary['spp_victims_count']) }} Santri ({{ round(($summary['spp_victims_count'] / max(1, $summary['total_count'])) * 100) }}%)</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Alert Card-->

            <!--begin::KPI Stats Cards-->
            <div class="row g-4 mb-6">
                <!--Card 1: Total Santri Minus-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-danger">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Total Santri Bersaldo Minus</div>
                                    <div class="text-danger fs-2x fw-bolder font-mono">{{ number_format($summary['total_count']) }}</div>
                                    <div class="text-muted fs-8 mt-1">Santri tercatat saldo defisit</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-danger">
                                    <span class="symbol-label">
                                        <i class="fas fa-user-minus text-danger fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Card 2: Total Defisit Kumulatif-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Total Akumulasi Defisit</div>
                                    <div class="text-warning fs-2x fw-bolder font-mono">Rp {{ number_format($summary['total_deficit'], 0, ',', '.') }}</div>
                                    <div class="text-muted fs-8 mt-1">Total saldo di bawah nol</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-warning">
                                    <span class="symbol-label">
                                        <i class="fas fa-wallet text-warning fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Card 3: Belanja Kasir PoS Murni-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-success">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Kasir (PoS) Belanja Murni</div>
                                    <div class="text-success fs-2x fw-bolder font-mono">{{ number_format($summary['pos_only_count']) }}</div>
                                    <div class="text-muted fs-8 mt-1">{{ round(($summary['pos_only_count'] / max(1, $summary['total_count'])) * 100) }}% jajan kasir keranjang tanpa tagihan</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-success">
                                    <span class="symbol-label">
                                        <i class="fas fa-shopping-basket text-success fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Card 4: Korban Pemotongan Tagihan-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Pemotongan Tagihan</div>
                                    <div class="text-primary fs-2x fw-bolder font-mono">{{ number_format($summary['spp_victims_count']) }}</div>
                                    <div class="text-muted fs-8 mt-1">{{ round(($summary['spp_victims_count'] / max(1, $summary['total_count'])) * 100) }}% ada pemotongan tagihan</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-primary">
                                    <span class="symbol-label">
                                        <i class="fas fa-file-invoice-dollar text-primary fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::KPI Stats Cards-->

            <!--begin::Card Table-->
            <div class="card shadow-sm border-0 mb-6">
                <!--begin::Card Header with Filters-->
                <div class="card-header border-0 pt-6 pb-4">
                    <div class="card-title w-100 d-flex flex-column">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light-danger fw-bolder me-2 px-3 py-1.5 fs-7">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i> {{ number_format($summary['total_count']) }} Santri
                                </span>
                                <h3 class="fw-bolder text-gray-800 m-0 fs-5">Tabel Data Lengkap Saldo Minus & Log Bukti</h3>
                            </div>
                        </div>

                        <!--begin::Filter Bar-->
                        <div class="d-flex flex-wrap gap-3 align-items-end p-4 bg-light rounded-3 border border-gray-200">
                            <!--Filter Lembaga-->
                            <div style="min-width: 170px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Lembaga</label>
                                <select id="filter_school" class="form-select form-select-sm filter-control">
                                    <option value="">Semua Lembaga</option>
                                    @foreach($schools as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!--Filter Kelas-->
                            <div style="min-width: 150px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Kelas</label>
                                <div class="dropdown" id="audit_classroom_dropdown_container" style="position: relative !important;">
                                    <input type="hidden" id="filter_classroom" value="">
                                    <button class="btn btn-light border bg-white fs-8 d-flex justify-content-between align-items-center" type="button" id="filter_classroom_btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-width: 140px; height: 32px; cursor: pointer;">
                                        <span id="filter_classroom_btn_text" class="text-truncate me-2" style="pointer-events: none;">Semua Kelas</span>
                                        <i class="fas fa-chevron-down fs-9 text-gray-500 filter-classroom-arrow" style="pointer-events: none; transition: transform 0.2s ease;"></i>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-lg border-0" style="min-width: 260px; width: 500px; max-width: calc(100vw - 32px); max-height: 400px; overflow-y: auto; border-radius: 16px; position: absolute !important; top: 100% !important; margin-top: 6px !important; z-index: 9999 !important;" aria-labelledby="filter_classroom_btn" id="audit_classroom_mega_menu">
                                    </div>
                                </div>
                            </div>

                            <!--Filter Rentang Minus-->
                            <div style="min-width: 170px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Kategori Nominal Minus</label>
                                <select id="filter_minus_range" class="form-select form-select-sm filter-control">
                                    <option value="">Semua Kategori</option>
                                    <option value="ringan">Minus Ringan (&lt; Rp 50.000)</option>
                                    <option value="sedang">Minus Sedang (Rp 50rb - 200rb)</option>
                                    <option value="berat">Minus Berat (&gt; Rp 200.000)</option>
                                </select>
                            </div>

                            <!--Filter Search Box-->
                            <div class="flex-grow-1" style="min-width: 200px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Cari Nama / NIS</label>
                                <div class="position-relative d-flex align-items-center filter-search-box" style="cursor: text;">
                                    <span class="position-absolute ms-3 text-gray-400" style="pointer-events: none;">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           id="search_keyword" 
                                           class="form-control form-control-sm form-control-solid ps-9" 
                                           placeholder="Ketik Nama Santri / NIS..." />
                                </div>
                            </div>

                            <!--Action Buttons-->
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-apply-filter" class="btn btn-sm btn-primary d-flex align-items-center">
                                    <i class="fas fa-filter me-1 fs-7"></i> Tampilkan
                                </button>
                                <button type="button" id="btn-reset-filter" class="btn btn-sm btn-light d-flex align-items-center text-gray-700 border">
                                    <i class="fas fa-undo me-1 fs-7"></i> Reset
                                </button>
                            </div>
                        </div>
                        <!--end::Filter Bar-->
                    </div>
                </div>
                <!--end::Card Header-->

                <!--begin::Card Body-->
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-saldo-minus" class="table table-row-bordered table-row-dashed align-middle gy-3 gs-3 border rounded w-100">
                            <thead>
                                <tr class="fw-bolder fs-7 text-gray-700 text-uppercase gs-0 bg-light">
                                    <th class="w-35px text-center">No</th>
                                    <th style="min-width: 170px;">Nama Siswa & NIS</th>
                                    <th style="min-width: 120px;">Lembaga & Kelas</th>
                                    <th style="min-width: 120px;">Riwayat Saldo</th>
                                    <th style="min-width: 130px;">Mulai Minus Sejak</th>
                                    <th class="text-end" style="min-width: 110px;">Minus Berapa</th>
                                    <th style="min-width: 170px;">Pemicu & Nominal</th>
                                    <th class="text-center w-90px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600 fs-7">
                                <!-- DataTables will populate here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Card Body-->
            </div>
            <!--end::Card Table-->

        </div>
    </div>
    <!--end::Post-->
</div>

<!--begin::Modal Detail Log Kronologis (3 Kolom & 2 Kolom)-->
<div class="modal fade" id="modal-log-timeline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-audit-dialog modal-fullscreen-lg-down">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <!--Modal Header-->
            <div class="modal-header border-bottom py-4 px-6 bg-light">
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center gap-2">
                        <span class="symbol symbol-35px symbol-circle bg-light-primary text-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-invoice-dollar fs-5 text-primary"></i>
                        </span>
                        <h3 class="modal-title fw-bolder text-gray-900 fs-4 mb-0">
                            Bukti & Diagnosa Saldo Minus Santri
                        </h3>
                    </div>
                    <div class="text-muted fs-7 mt-1 ms-10">
                        Santri: <strong class="text-dark fs-6" id="modal-student-name">-</strong> &bull; 
                        NIS: <strong class="text-dark font-mono" id="modal-student-nis">-</strong> &bull; 
                        Lembaga: <strong class="text-dark" id="modal-student-school">-</strong> &bull; 
                        Kelas: <strong class="text-dark" id="modal-student-class">-</strong>
                    </div>
                </div>

                <!--View Switcher & Close-->
                <div class="d-flex align-items-center gap-3">
                    <div class="btn-group btn-group-sm bg-white p-1 rounded-3 border" role="group" aria-label="Pilih Mode Tampilan">
                        <button type="button" class="btn btn-sm btn-primary fw-bolder btn-switch-view" data-view="3col" id="btn-view-3col">
                            <i class="fas fa-columns me-1.5 fs-7"></i> 3 Kolom (Aliran Dana)
                        </button>
                        <button type="button" class="btn btn-sm btn-light fw-bolder text-gray-700 btn-switch-view" data-view="2col" id="btn-view-2col">
                            <i class="fas fa-list-ol me-1.5 fs-7"></i> 2 Kolom (Kronologis)
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-icon btn-light-dark" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-4"></i>
                    </button>
                </div>
            </div>

            <!--Modal Body-->
            <div class="modal-body px-6 py-5">
                <!--Loading Spinner-->
                <div id="modal-loading" class="text-center py-12">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <div class="text-gray-800 fs-6 fw-bold">Membedah aliran dana dan jejak mutasi saldo santri...</div>
                    <div class="text-muted fs-8 mt-1">Mengelompokkan top up, pemotongan tagihan, dan transaksi kantin.</div>
                </div>

                <!--Content Container-->
                <div id="modal-content" class="d-none">

                    <!--begin::Hero Spotlight: 2 Hal Pokok (Kapan Mulai Minus & Pemicu/Petugas)-->
                    <div id="hero-spotlight-card" class="card mb-4 border-0 rounded-3 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #1e1e2d 0%, #252538 100%);">
                        <div class="card-body p-4 text-white">
                            <div class="row align-items-center g-3">
                                <!-- Box A: Sejak Kapan Minus Muncul -->
                                <div class="col-md-6 border-end-md border-gray-700 pe-md-4">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge badge-danger p-1.5 px-2.5 fw-bolder fs-9 text-uppercase">
                                            <i class="fas fa-clock text-white me-1"></i> 1. Kapan Saldo Mulai Minus?
                                        </span>
                                    </div>
                                    <div class="text-white fs-4 fw-bolder font-mono" id="hero-first-minus-date">-</div>
                                    <div class="text-gray-300 fs-8 mt-1" id="hero-first-minus-shift">
                                        Pergeseran Saldo: <span class="font-mono text-gray-400" id="hero-prev-saldo">-</span> &rarr; <span class="font-mono text-danger fw-bolder" id="hero-after-saldo">-</span>
                                    </div>
                                </div>

                                <!-- Box B: Disebabkan Oleh Apa & Berapa Nominalnya & Siapa Petugasnya -->
                                <div class="col-md-6 ps-md-4">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge badge-warning text-dark p-1.5 px-2.5 fw-bolder fs-9 text-uppercase">
                                            <i class="fas fa-exclamation-triangle text-dark me-1"></i> 2. Transaksi Pemicu & Petugas
                                        </span>
                                        <span class="badge badge-light-danger font-mono fs-8 fw-bolder" id="hero-trigger-amount">- Rp 0</span>
                                    </div>
                                    <div class="fs-6 fw-bolder text-white text-truncate" id="hero-trigger-title" title="-">
                                        -
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mt-1 fs-8 text-gray-300">
                                        <div id="hero-trigger-officer-wrap">
                                            <i class="fas fa-user-check text-info me-1"></i> Petugas: <strong class="text-white" id="hero-trigger-officer">-</strong>
                                        </div>
                                        <div class="font-mono text-gray-400 fs-9" id="hero-trigger-code-wrap">
                                            <i class="fas fa-receipt text-gray-500 me-1"></i> Ref: <span class="text-gray-300" id="hero-trigger-code">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Hero Spotlight-->

                    <!--Top Equation Formula Strip: Masuk - Tagihan - Jajan = Saldo Minus-->
                    <div class="card mb-4 border border-gray-200 rounded-3 p-4 bg-light shadow-xs">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-center mb-3">
                            <!-- Box 1: Pemasukan Top Up -->
                            <div class="flex-grow-1 p-3 bg-white rounded-3 border border-gray-200 shadow-xs" style="min-width: 180px;">
                                <div class="text-success fs-8 fw-bolder text-uppercase mb-1">
                                    <i class="fas fa-arrow-down text-success me-1"></i> Total Top Up Masuk
                                </div>
                                <div class="text-gray-900 fs-3 fw-bolder font-mono" id="stat-topup">Rp 0</div>
                                <div class="text-muted fs-8 mt-0.5" id="stat-topup-count">0 transaksi</div>
                            </div>

                            <div class="fs-2 fw-normal text-gray-400 px-1">−</div>

                            <!-- Box 2: Potongan Tagihan -->
                            <div class="flex-grow-1 p-3 bg-white rounded-3 border border-gray-200 shadow-xs" style="min-width: 180px;">
                                <div class="text-danger fs-8 fw-bolder text-uppercase mb-1">
                                    <i class="fas fa-file-invoice-dollar text-danger me-1"></i> Potong Tagihan
                                </div>
                                <div class="text-gray-900 fs-3 fw-bolder font-mono" id="stat-bill-deductions">Rp 0</div>
                                <div class="text-muted fs-8 mt-0.5" id="stat-bill-count">0 transaksi</div>
                            </div>

                            <div class="fs-2 fw-normal text-gray-400 px-1">−</div>

                            <!-- Box 3: Belanja POS -->
                            <div class="flex-grow-1 p-3 bg-white rounded-3 border border-gray-200 shadow-xs" style="min-width: 180px;">
                                <div class="text-warning fs-8 fw-bolder text-uppercase mb-1">
                                    <i class="fas fa-shopping-basket text-warning me-1"></i> Belanja Jajan POS
                                </div>
                                <div class="text-gray-900 fs-3 fw-bolder font-mono" id="stat-pos-deductions">Rp 0</div>
                                <div class="text-muted fs-8 mt-0.5" id="stat-pos-count">0 transaksi</div>
                            </div>

                            <div class="fs-2 fw-normal text-gray-400 px-1">=</div>

                            <!-- Box 4: Saldo Akhir (Defisit) -->
                            <div class="flex-grow-1 p-3 bg-light-danger rounded-3 border border-danger border-opacity-30 shadow-xs" style="min-width: 200px;">
                                <div class="text-danger fs-8 fw-bolder text-uppercase mb-1">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i> Saldo Akhir Tercatat
                                </div>
                                <div class="text-danger fs-3 fw-bolder font-mono" id="stat-final-saldo">- Rp 0</div>
                                <div class="text-danger fs-9 fw-semibold mt-0.5">Defisit Mutasi</div>
                            </div>
                        </div>

                        <!-- Diagnosis Alert Box -->
                        <div class="alert alert-light-secondary d-flex align-items-center p-3 mb-0 border border-gray-200 rounded-3 bg-white">
                            <i class="fas fa-info-circle text-primary fs-3 me-3 flex-shrink-0"></i>
                            <div class="fs-7 text-gray-800 lh-base" id="stat-diagnosis-text">
                                Memuat analisa keuangan santri...
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- VIEW MODE 1: TAMPILAN 3 KOLOM (ALIRAN DANA)                     -->
                    <!-- ============================================================== -->
                    <div id="view-mode-3col">
                        <div class="row g-4">
                            <!-- Kolom 1: Pemasukan Top Up (Hijau) -->
                            <div class="col-lg-4 col-md-12">
                                <div class="card h-100 border border-gray-200 shadow-xs">
                                    <div class="card-header border-bottom border-gray-100 py-3 px-4 bg-white d-flex align-items-center justify-content-between min-h-auto">
                                        <div>
                                            <span class="fs-7 fw-bolder text-gray-900 d-flex align-items-center">
                                                <i class="fas fa-arrow-circle-down text-success me-2 fs-6"></i> 1. Uang Masuk (Top Up)
                                            </span>
                                            <span class="text-muted fs-9" id="col-topup-count-label">0 Transaksi</span>
                                        </div>
                                        <span class="badge badge-light-success text-success font-mono fs-8 fw-bolder px-2.5 py-1" id="col-topup-badge">Rp 0</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm form-control-solid search-col" data-target="#list-col-topup" placeholder="Cari tanggal / nominal top up..." style="font-size: 0.8rem;">
                                        </div>
                                        <div id="list-col-topup" class="column-scroll-container pe-1" style="max-height: 520px; overflow-y: auto;">
                                            <!-- List populated via JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom 2: Potongan Tagihan (Merah) -->
                            <div class="col-lg-4 col-md-12">
                                <div class="card h-100 border border-gray-200 shadow-xs">
                                    <div class="card-header border-bottom border-gray-100 py-3 px-4 bg-white d-flex align-items-center justify-content-between min-h-auto">
                                        <div>
                                            <span class="fs-7 fw-bolder text-gray-900 d-flex align-items-center">
                                                <i class="fas fa-file-invoice-dollar text-danger me-2 fs-6"></i> 2. Potong Tagihan
                                            </span>
                                            <span class="text-muted fs-9" id="col-spp-count-label">0 Pemotongan Tagihan</span>
                                        </div>
                                        <span class="badge badge-light-danger text-danger font-mono fs-8 fw-bolder px-2.5 py-1" id="col-spp-badge">Rp 0</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm form-control-solid search-col" data-target="#list-col-spp" placeholder="Cari nama tagihan / bulan..." style="font-size: 0.8rem;">
                                        </div>
                                        <div id="list-col-spp" class="column-scroll-container pe-1" style="max-height: 520px; overflow-y: auto;">
                                            <!-- List populated via JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom 3: Belanja Kasir / Kantin (Oranye/Kuning) -->
                            <div class="col-lg-4 col-md-12">
                                <div class="card h-100 border border-gray-200 shadow-xs">
                                    <div class="card-header border-bottom border-gray-100 py-3 px-4 bg-white d-flex align-items-center justify-content-between min-h-auto">
                                        <div>
                                            <span class="fs-7 fw-bolder text-gray-900 d-flex align-items-center">
                                                <i class="fas fa-shopping-basket text-warning me-2 fs-6"></i> 3. Jajan Kasir (PoS) Keranjang Belanja
                                            </span>
                                            <span class="text-muted fs-9" id="col-pos-count-label">0 Belanja Kasir</span>
                                        </div>
                                        <span class="badge badge-light text-gray-800 border border-gray-200 font-mono fs-8 fw-bolder px-2.5 py-1" id="col-pos-badge">Rp 0</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm form-control-solid search-col" data-target="#list-col-pos" placeholder="Cari nama barang / jajan..." style="font-size: 0.8rem;">
                                        </div>
                                        <div id="list-col-pos" class="column-scroll-container pe-1" style="max-height: 520px; overflow-y: auto;">
                                            <!-- List populated via JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- VIEW MODE 2: TAMPILAN 2 KOLOM (DIAGNOSA & KRONOLOGIS)           -->
                    <!-- ============================================================== -->
                    <div id="view-mode-2col" class="d-none">
                        <div class="row g-4">
                            <!-- Kolom Kiri: Diagnosa & Breakdown Forensik (col-lg-4) -->
                            <div class="col-lg-4">
                                <div class="card border rounded-3 p-4 bg-white mb-4 shadow-xs">
                                    <h5 class="fw-bolder text-gray-900 mb-3 d-flex align-items-center">
                                        <i class="fas fa-chart-pie text-primary me-2"></i> Komposisi Pengeluaran
                                    </h5>

                                    <!-- Progress Bar Tagihan vs POS -->
                                    <div class="d-flex align-items-center justify-content-between fs-8 mb-1">
                                        <span class="text-danger fw-bolder" id="label-percent-spp">Tagihan: 0%</span>
                                        <span class="text-warning fw-bolder" id="label-percent-pos">Kasir (PoS): 0%</span>
                                    </div>
                                    <div class="progress h-8px mb-4">
                                        <div id="progress-bar-spp" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                        <div id="progress-bar-pos" class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                    </div>

                                    <!-- Ringkasan Nilai -->
                                    <div class="d-flex flex-column gap-2 fs-7 mb-4">
                                        <div class="d-flex justify-content-between p-2.5 rounded-2 bg-light-success border border-success border-opacity-20">
                                            <span class="text-success fw-bold"><i class="fas fa-plus-circle me-1 text-success"></i> Uang Masuk</span>
                                            <span class="font-mono fw-bolder text-gray-900" id="val-side-topup">Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between p-2.5 rounded-2 bg-light-danger border border-danger border-opacity-20">
                                            <span class="text-danger fw-bold"><i class="fas fa-file-invoice-dollar me-1 text-danger"></i> Potongan Tagihan</span>
                                            <span class="font-mono fw-bolder text-gray-900" id="val-side-spp">Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between p-2.5 rounded-2 bg-light-warning border border-warning border-opacity-20">
                                            <span class="text-gray-800 fw-bold"><i class="fas fa-shopping-basket me-1 text-warning"></i> Jajan Kasir (PoS)</span>
                                            <span class="font-mono fw-bolder text-gray-900" id="val-side-pos">Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between p-2.5 rounded-2 bg-danger bg-opacity-10 border border-danger border-opacity-30">
                                            <span class="text-danger fw-bolder"><i class="fas fa-exclamation-circle me-1 text-danger"></i> Selisih Minus</span>
                                            <span class="font-mono fw-bolder text-danger" id="val-side-deficit">- Rp 0</span>
                                        </div>
                                    </div>

                                    <!-- Titik Awal Saldo Jebol Minus -->
                                    <div id="box-first-negative-2col" class="p-3 rounded-2 border border-danger border-dashed bg-light-danger">
                                        <div class="fw-bolder text-danger fs-8 mb-1 d-flex align-items-center">
                                            <i class="fas fa-bolt text-danger me-1.5"></i> Titik Balik Saldo Pertama Kali Minus:
                                        </div>
                                        <div class="fs-8 text-gray-800" id="text-first-negative-2col">
                                            -
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Tabel Kronologis Interaktif (col-lg-8) -->
                            <div class="col-lg-8">
                                <div class="card border rounded-3 p-4 bg-white shadow-xs">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                        <!-- Filter Kategori Tabs -->
                                        <ul class="nav nav-pills nav-pills-sm" id="timeline-filter-pills">
                                            <li class="nav-item">
                                                <button class="nav-link active btn-sm py-1.5 px-3 fs-8 fw-bolder timeline-filter-btn" data-filter="all">Semua</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link btn-sm py-1.5 px-3 fs-8 fw-bolder timeline-filter-btn text-danger" data-filter="negative">🔴 Saat Saldo Minus</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link btn-sm py-1.5 px-3 fs-8 fw-bolder timeline-filter-btn" data-filter="pos">🛒 Kasir (PoS) Jajan</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link btn-sm py-1.5 px-3 fs-8 fw-bolder timeline-filter-btn" data-filter="spp">💳 Tagihan</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link btn-sm py-1.5 px-3 fs-8 fw-bolder timeline-filter-btn" data-filter="topup">📥 Top Up</button>
                                            </li>
                                        </ul>

                                        <!-- Quick search within timeline -->
                                        <div style="min-width: 200px;">
                                            <input type="text" id="search-timeline-input" class="form-control form-control-sm form-control-solid" placeholder="Cari keterangan..." style="font-size: 0.8rem;">
                                        </div>
                                    </div>

                                    <!-- Timeline Table -->
                                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                                        <table class="table table-sm table-row-bordered align-middle gs-3 gy-2 fs-8 mb-0">
                                            <thead class="sticky-top bg-light" style="z-index: 2;">
                                                <tr class="fw-bolder fs-9 text-uppercase text-gray-700 bg-light border-bottom border-gray-200">
                                                    <th class="w-35px text-center py-2.5 bg-light">No</th>
                                                    <th class="py-2.5 bg-light">Tanggal & Waktu</th>
                                                    <th class="py-2.5 bg-light">Tipe</th>
                                                    <th class="text-end py-2.5 bg-light">Nominal</th>
                                                    <th class="py-2.5 bg-light">Keterangan / Transaksi</th>
                                                    <th class="text-end py-2.5 pe-3 bg-light">Saldo Akhir</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modal-timeline-body">
                                                <!-- Populated dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!--Modal Footer-->
            <div class="modal-footer border-top py-3 px-6 bg-light d-flex justify-content-between align-items-center">
                <span class="text-muted fs-8">
                    <i class="fas fa-shield-alt text-primary me-1"></i> Data dihitung langsung dari riwayat transaksi mutasi buku besar.
                </span>
                <button type="button" class="btn btn-sm btn-secondary fw-bolder" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal Detail Log Kronologis-->

<style>
    /* ============================================================== */
    /* LEGACY TYPOGRAPHY (METRONIC POPPINS STACK)                     */
    /* ============================================================== */
    body, 
    #kt_content, 
    #kt_content_container,
    .card, 
    .table, 
    .modal, 
    .btn, 
    .form-control, 
    .form-select, 
    .badge,
    .toolbar,
    .page-title,
    .alert {
        font-family: Poppins, Helvetica, "sans-serif" !important;
    }

    /* Pastikan FontAwesome icons tidak ter-override font text */
    .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands,
    [class^="fa-"], [class*=" fa-"] {
        font-family: "Font Awesome 6 Free", "FontAwesome" !important;
    }

    .font-mono {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
    }

    /* ============================================================== */
    /* CLEAN TABLE LAYOUT (NO HORIZONTAL SCROLL & NO CLIPPING)        */
    /* ============================================================== */
    #table-saldo-minus {
        width: 100% !important;
        table-layout: auto !important;
        margin-bottom: 0 !important;
    }

    #table-saldo-minus thead th {
        background-color: #f5f8fa !important;
        color: #5e6278 !important;
        font-weight: 700 !important;
        font-size: 0.725rem !important;
        letter-spacing: 0.03em !important;
        text-transform: uppercase !important;
        border-bottom: 1px solid #eff2f5 !important;
        padding: 9px 10px !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    #table-saldo-minus tbody td {
        padding: 9px 10px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #eff2f5 !important;
    }

    #table-saldo-minus tbody tr:hover td {
        background-color: #f9f9fc !important;
    }

    /* Kontainer tabel responsif tanpa pemotongan brutal */
    .table-responsive {
        overflow-x: auto;
    }

    /* ============================================================== */
    /* FORM CONTROLS & FILTER BAR                                     */
    /* ============================================================== */
    .filter-control {
        cursor: pointer !important;
        height: 38px !important;
        min-height: 38px !important;
        border: 1px solid #e4e6ef !important;
        border-radius: 0.475rem !important;
        font-size: 0.85rem !important;
        color: #3f4254 !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    .filter-control:focus {
        border-color: #009ef7 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.15) !important;
        outline: none !important;
    }
    #search_keyword {
        height: 38px !important;
        min-height: 38px !important;
        border: 1px solid #e4e6ef !important;
        border-radius: 0.475rem !important;
        font-size: 0.85rem !important;
        color: #3f4254 !important;
        background-color: #ffffff !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    #search_keyword:focus {
        border-color: #009ef7 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.15) !important;
        outline: none !important;
    }
    #btn-apply-filter, #btn-reset-filter {
        height: 38px !important;
        min-height: 38px !important;
    }

    /* Modal Tweaks */
    .modal-audit-dialog {
        max-width: 95vw !important;
        width: 95vw !important;
    }
    @media (min-width: 1600px) {
        .modal-audit-dialog {
            max-width: 1560px !important;
        }
    }
    .col-item {
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .col-item:hover {
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
    }
    .border-top-4 {
        border-top-width: 4px !important;
    }
    .column-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    .column-scroll-container::-webkit-scrollbar-thumb {
        background: #d5d5d5;
        border-radius: 4px;
    }
</style>
@endsection

@push('js')
<script>
    var saldoMinusTable;
    var cachedLogData = null;

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    $(document).ready(function() {
        // Initialize DataTables tanpa scroll samping & tanpa child row collapse
        saldoMinusTable = $('#table-saldo-minus').DataTable({
            processing: true,
            serverSide: true,
            responsive: false, // Tidak collapse ke child row
            scrollX: false,    // Tidak ada scroll samping
            autoWidth: false,
            ajax: {
                url: "{{ route('admin.audit.saldo-minus.data') }}",
                data: function(d) {
                    d.school_id = $('#filter_school').val();
                    d.classroom_id = $('#filter_classroom').val();
                    d.minus_range = $('#filter_minus_range').val();
                    d.search_keyword = $('#search_keyword').val();
                }
            },
            columns: [
                { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center font-mono text-muted fs-8 w-35px' },
                { data: 'student', name: 'student', orderable: true, searchable: true },
                { data: 'school_class', name: 'school_class', orderable: true, searchable: true },
                { data: 'saldo_status', name: 'saldo_status', orderable: false, searchable: false },
                { data: 'last_trans_date', name: 'last_trans_date', orderable: true, searchable: false },
                { data: 'minus_amount', name: 'minus_amount', orderable: true, searchable: false, className: 'text-end' },
                { data: 'root_cause', name: 'root_cause', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center w-90px' }
            ],
            order: [[5, 'asc']], // Order by minus_amount asc (most negative first)
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                emptyTable: "Tidak ada santri dengan saldo minus",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ santri minus",
                infoEmpty: "Menampilkan 0 data",
                lengthMenu: "Tampilkan _MENU_ santri",
                loadingRecords: "Memuat...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: "Pencarian:",
                zeroRecords: "Data santri minus tidak ditemukan"
            }
        });

        // Filter trigger
        $('#btn-apply-filter').on('click', function(e) {
            e.preventDefault();
            saldoMinusTable.ajax.reload();
        });

        // Search on Enter key
        $('#search_keyword').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                saldoMinusTable.ajax.reload();
            }
        });

        // Search container click
        $('.filter-search-box').on('click', function() {
            $('#search_keyword').focus();
        });

        const auditAllClasses = @json($classrooms ?? []);

        function renderAuditClassroomMegaMenu(selectedSchoolId) {
            const container = $('#audit_classroom_mega_menu');
            container.empty();

            let filteredClasses = auditAllClasses;
            if (selectedSchoolId) {
                filteredClasses = auditAllClasses.filter(c => c.school_id == selectedSchoolId);
            }

            if (!filteredClasses || filteredClasses.length === 0) {
                container.html('<div class="text-muted fs-8 p-3 text-center">Tidak ada kelas ditemukan</div>');
                return;
            }

            const groups = {};
            filteredClasses.forEach(c => {
                let match = (c.name || '').match(/^(\d+)/);
                let key = match ? match[1] : 'Lainnya';
                if (!groups[key]) groups[key] = [];
                groups[key].push(c);
            });

            const resetBtn = $('<button type="button" class="btn btn-sm btn-light-primary w-100 fw-bold mb-3 audit-classroom-item text-center rounded-2 py-1.5 fs-8" data-id="" data-name="Semua Kelas" style="cursor: pointer;"><i class="fas fa-layer-group me-1" style="pointer-events: none;"></i><span style="pointer-events: none;">Semua Kelas</span></button>');
            container.append(resetBtn);

            const sortedKeys = Object.keys(groups).sort((a,b) => {
                let numA = parseInt(a);
                let numB = parseInt(b);
                if (isNaN(numA)) return 1;
                if (isNaN(numB)) return -1;
                return numA - numB;
            });

            const colCount = sortedKeys.length;
            let colClass = 'col-12';
            let menuWidth = '260px';
            if (colCount === 2) {
                colClass = 'col-6';
                menuWidth = '380px';
            } else if (colCount === 3) {
                colClass = 'col-4';
                menuWidth = '500px';
            } else if (colCount >= 4) {
                colClass = 'col-3';
                menuWidth = '620px';
            }
            container.css({ 'width': menuWidth });

            const row = $('<div class="row g-2"></div>');
            sortedKeys.forEach(key => {
                const col = $(`<div class="${colClass}"></div>`);
                const headerTitle = isNaN(parseInt(key)) ? key : 'Kelas ' + key;
                col.append(`<h6 class="dropdown-header text-uppercase text-muted fw-bolder px-1 mb-2 fs-9 border-bottom pb-1">${headerTitle}</h6>`);
                const list = $('<div class="d-flex flex-column gap-1"></div>');
                groups[key].forEach(c => {
                    list.append(`<button type="button" class="btn btn-sm btn-light btn-active-light-primary text-start w-100 py-1 px-2 mb-1 rounded-2 audit-classroom-item fs-8 fw-semibold d-flex align-items-center justify-content-between text-truncate" data-id="${c.id}" data-name="${c.name}" style="cursor: pointer; transition: all 0.15s ease-in-out; min-height: 28px;">
                        <span class="text-truncate" style="pointer-events: none;">${c.name}</span>
                        <i class="fas fa-check text-primary fs-9 d-none class-check-icon" style="pointer-events: none;"></i>
                    </button>`);
                });
                col.append(list);
                row.append(col);
            });
            container.append(row);
        }

        renderAuditClassroomMegaMenu('');

        $(document).on('click', '#audit_classroom_mega_menu .audit-classroom-item', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#filter_classroom').val(id);
            if (id) {
                $('#filter_classroom_btn_text').html(`<i class="fas fa-chalkboard-user me-1 text-primary"></i> <span class="fw-bold">${name}</span>`);
            } else {
                $('#filter_classroom_btn_text').text('Semua Kelas');
            }

            $('#audit_classroom_mega_menu .class-check-icon').addClass('d-none');
            $('#audit_classroom_mega_menu .audit-classroom-item').removeClass('active btn-primary text-white').addClass('btn-light');
            if (id) {
                $(this).addClass('active btn-primary text-white').removeClass('btn-light');
                $(this).find('.class-check-icon').removeClass('d-none');
            }

            const dropdownEl = document.getElementById('audit_classroom_mega_menu');
            if (dropdownEl) {
                const bsDropdown = bootstrap.Dropdown.getInstance(document.getElementById('filter_classroom_btn'));
                if (bsDropdown) bsDropdown.hide();
            }

            saldoMinusTable.ajax.reload();
        });

        $('#audit_classroom_dropdown_container').on('show.bs.dropdown', function () {
            const $btn = $('#filter_classroom_btn');
            const $menu = $('#audit_classroom_mega_menu');
            const btnOffset = $btn.offset();
            const menuWidth = $menu.outerWidth() || 500;
            const winWidth = $(window).width();
            if (btnOffset && (btnOffset.left + menuWidth > winWidth - 20)) {
                $menu.css({ 'left': 'auto', 'right': '0' });
            } else {
                $menu.css({ 'left': '0', 'right': 'auto' });
            }
        });

        // Reset Filter
        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#filter_school').val('');
            $('#filter_classroom').val('');
            $('#filter_classroom_btn_text').text('Semua Kelas');
            $('#filter_minus_range').val('');
            $('#search_keyword').val('');
            renderAuditClassroomMegaMenu('');
            saldoMinusTable.ajax.reload();
        });

        // School change filter for classrooms
        $('#filter_school').on('change', function() {
            var schoolId = $(this).val();
            $('#filter_classroom').val('');
            $('#filter_classroom_btn_text').text('Semua Kelas');
            renderAuditClassroomMegaMenu(schoolId);
            saldoMinusTable.ajax.reload();
        });

        // View Switcher (3 Kolom vs 2 Kolom)
        $('.btn-switch-view').on('click', function() {
            var targetView = $(this).data('view');
            $('.btn-switch-view').removeClass('btn-primary active').addClass('btn-light text-gray-700');
            $(this).addClass('btn-primary active').removeClass('btn-light text-gray-700');

            if (targetView === '3col') {
                $('#view-mode-3col').removeClass('d-none');
                $('#view-mode-2col').addClass('d-none');
            } else {
                $('#view-mode-3col').addClass('d-none');
                $('#view-mode-2col').removeClass('d-none');
            }
        });

        // Live Search within 3 Columns
        $(document).on('keyup', '.search-col', function() {
            var targetList = $(this).data('target');
            var val = $(this).val().toLowerCase().trim();
            $(targetList + ' .col-item').each(function() {
                var text = $(this).data('search').toLowerCase();
                if (!val || text.indexOf(val) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Live Search within Timeline (2 Kolom)
        $('#search-timeline-input').on('keyup', function() {
            filterTimelineTable();
        });

        // Timeline Filter Tabs (2 Kolom)
        $('.timeline-filter-btn').on('click', function(e) {
            e.preventDefault();
            $('.timeline-filter-btn').removeClass('active');
            $(this).addClass('active');
            filterTimelineTable();
        });

        function filterTimelineTable() {
            var activeFilter = $('#timeline-filter-pills .timeline-filter-btn.active').data('filter') || 'all';
            var searchVal = ($('#search-timeline-input').val() || '').toLowerCase().trim();

            $('#modal-timeline-body tr').each(function() {
                var rowCategory = $(this).data('category');
                var isNegative = $(this).data('negative') === true || $(this).data('negative') === 'true';
                var searchContent = ($(this).data('search') || '').toLowerCase();

                var matchCategory = true;
                if (activeFilter === 'negative') {
                    matchCategory = isNegative;
                } else if (activeFilter !== 'all') {
                    matchCategory = (rowCategory === activeFilter);
                }

                var matchSearch = (!searchVal || searchContent.indexOf(searchVal) > -1);

                if (matchCategory && matchSearch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Render cardlet for 3-column view
        function renderCardlet(item, type) {
            var searchData = (item.created_at + ' ' + item.description + ' ' + (item.friendly_desc || '') + ' ' + (item.officer_name || '') + ' ' + item.amount).toLowerCase();
            var badgeClass = 'badge-light-success text-success';
            var borderClass = 'border-gray-200';

            if (type === 'spp') {
                badgeClass = 'badge-light-danger text-danger';
            } else if (type === 'pos') {
                badgeClass = 'badge-light-warning text-dark border border-warning border-opacity-30';
            }

            var balanceBadge = item.is_negative
                ? '<span class="text-danger font-mono fw-bold fs-9">' + item.balance_after_formatted + '</span>'
                : '<span class="text-gray-600 font-mono fs-9">' + item.balance_after_formatted + '</span>';

            var titleDesc = item.friendly_desc || item.description;
            var rawNote = (item.friendly_desc && item.friendly_desc !== item.description)
                ? '<div class="text-muted fs-9 font-mono mt-0.5" title="' + item.description + '">Tercatat: ' + item.description + '</div>'
                : '';

            var officerHtml = '';
            if (item.officer_name) {
                var officerIcon = (type === 'spp') ? 'fa-user-check text-primary' : 'fa-cash-register text-warning';
                var officerLabel = (type === 'spp') ? 'Petugas' : 'Kasir';
                var refCode = item.payment_code ? ' &bull; <span class="font-mono text-muted fs-9">' + item.payment_code + '</span>' : '';
                officerHtml = `<div class="text-gray-800 fs-9 mt-1 fw-semibold"><i class="fas ${officerIcon} me-1"></i>${officerLabel}: <strong class="text-primary">${item.officer_name}</strong>${refCode}</div>`;
            }

            return `
                <div class="card mb-2 border ${borderClass} rounded-2 p-2.5 shadow-none bg-white col-item" data-search="${searchData}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fs-9 font-mono">${item.created_at}</span>
                        <span class="badge ${badgeClass} font-mono fs-8 fw-bolder">${item.amount_formatted}</span>
                    </div>
                    <div class="text-gray-900 fs-8 fw-bolder text-truncate" title="${titleDesc}">
                        ${titleDesc}
                    </div>
                    ${officerHtml}
                    ${rawNote}
                    <div class="d-flex align-items-center justify-content-between fs-9 text-muted pt-1 mt-1 border-top border-gray-100">
                        <span class="text-gray-500">Saldo berjalan:</span>
                        ${balanceBadge}
                    </div>
                </div>
            `;
        }

        // Modal View Logs
        $(document).on('click', '.btn-view-logs', function(e) {
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            var name = btn.data('name');
            var nis = btn.data('nis');
            var school = btn.data('school');
            var className = btn.data('class');

            $('#modal-student-name').text(name);
            $('#modal-student-nis').text(nis);
            $('#modal-student-school').text(school);
            $('#modal-student-class').text(className);

            // Default ke tampilan 3 Kolom
            $('#btn-view-3col').trigger('click');

            $('#modal-loading').removeClass('d-none');
            $('#modal-content').addClass('d-none');
            $('#modal-log-timeline').modal('show');

            $.ajax({
                url: "{{ url('admin/audit/saldo-minus') }}/" + id + "/logs",
                type: "GET",
                success: function(res) {
                    cachedLogData = res;
                    $('#modal-loading').addClass('d-none');
                    $('#modal-content').removeClass('d-none');

                    var summary = res.summary;

                    // 0. Hero Spotlight Card (2 Hal Pokok: Timestamp Kapan Mulai Minus & Pemicu/Petugas)
                    if (summary.first_negative_event) {
                        var fn = summary.first_negative_event;
                        $('#hero-spotlight-card').removeClass('d-none');
                        $('#hero-first-minus-date').text(fn.date);
                        $('#hero-prev-saldo').text(fn.prev_formatted);
                        $('#hero-after-saldo').text(fn.after_formatted);
                        $('#hero-trigger-amount').text('- ' + fn.amount_formatted);
                        $('#hero-trigger-title').text(fn.friendly_desc || fn.desc).attr('title', fn.desc);
                        var defaultRole = (fn.category === 'spp') ? 'Petugas Keuangan' : 'Petugas Kasir';
                        $('#hero-trigger-officer').text(fn.officer_name || defaultRole);
                        if (fn.payment_code) {
                            $('#hero-trigger-code').text(fn.payment_code);
                            $('#hero-trigger-code-wrap').removeClass('d-none');
                        } else {
                            $('#hero-trigger-code-wrap').addClass('d-none');
                        }
                    } else {
                        $('#hero-spotlight-card').addClass('d-none');
                    }

                    // 1. Header Formula Strip
                    $('#stat-topup').text(summary.total_in_formatted);
                    $('#stat-topup-count').text(summary.topup_count + ' transaksi');

                    $('#stat-bill-deductions').text(summary.total_bill_formatted);
                    $('#stat-bill-count').text(summary.spp_count + ' pemotongan');

                    $('#stat-pos-deductions').text(summary.total_pos_formatted);
                    $('#stat-pos-count').text(summary.pos_count + ' transaksi');

                    $('#stat-final-saldo').text(summary.final_saldo_formatted);
                    $('#stat-diagnosis-text').html(
                        '<strong>Kesimpulan Audit:</strong> Total pengeluaran santri (' + summary.total_out_formatted + ') ' +
                        'melebihi seluruh saldo masuk (' + summary.total_in_formatted + ') sehingga tekor/minus sebesar ' +
                        '<strong class="text-danger">' + summary.deficit_formatted + '</strong>. ' +
                        summary.diagnosis
                    );

                    // 2. Populasi Tampilan 3 Kolom
                    $('#col-topup-badge').text(summary.total_in_formatted);
                    $('#col-topup-count-label').text(summary.topup_count + ' Transaksi');
                    var listTopup = $('#list-col-topup').empty();
                    if (res.items_topup && res.items_topup.length > 0) {
                        $.each(res.items_topup, function(i, it) {
                            listTopup.append(renderCardlet(it, 'topup'));
                        });
                    } else {
                        listTopup.html('<div class="text-center py-6 text-muted fs-8 fst-italic">Tidak ada riwayat top up.</div>');
                    }

                    $('#col-spp-badge').text(summary.total_bill_formatted);
                    $('#col-spp-count-label').text(summary.spp_count + ' Pemotongan Tagihan');
                    var listSpp = $('#list-col-spp').empty();
                    if (res.items_spp && res.items_spp.length > 0) {
                        $.each(res.items_spp, function(i, it) {
                            listSpp.append(renderCardlet(it, 'spp'));
                        });
                    } else {
                        listSpp.html('<div class="text-center py-6 text-muted fs-8 fst-italic">Tidak ada pemotongan tagihan.</div>');
                    }

                    $('#col-pos-badge').text(summary.total_pos_formatted);
                    $('#col-pos-count-label').text(summary.pos_count + ' Belanja Kasir');
                    var listPos = $('#list-col-pos').empty();
                    if (res.items_pos && res.items_pos.length > 0) {
                        $.each(res.items_pos, function(i, it) {
                            listPos.append(renderCardlet(it, 'pos'));
                        });
                    } else {
                        listPos.html('<div class="text-center py-6 text-muted fs-8 fst-italic">Tidak ada transaksi jajan kasir PoS.</div>');
                    }

                    // 3. Populasi Tampilan 2 Kolom (Side panel)
                    $('#val-side-topup').text(summary.total_in_formatted);
                    $('#val-side-spp').text(summary.total_bill_formatted);
                    $('#val-side-pos').text(summary.total_pos_formatted);
                    $('#val-side-deficit').text(summary.final_saldo_formatted);

                    $('#label-percent-spp').text('Tagihan: ' + summary.spp_percent + '%');
                    $('#label-percent-pos').text('Kasir (PoS): ' + summary.pos_percent + '%');
                    $('#progress-bar-spp').css('width', summary.spp_percent + '%');
                    $('#progress-bar-pos').css('width', summary.pos_percent + '%');

                    if (summary.first_negative_event) {
                        var fn = summary.first_negative_event;
                        $('#box-first-negative-2col').removeClass('d-none');
                        var officerNote = fn.officer_name ? ' oleh petugas <strong>' + fn.officer_name + '</strong>' : '';
                        $('#text-first-negative-2col').html(
                            'Pada <strong>' + fn.date + '</strong>, saldo santri (' + fn.prev_formatted + ') ' +
                            'dipotong <strong>' + fn.amount_formatted + '</strong> (' + (fn.friendly_desc || fn.desc) + ')' + officerNote + ', ' +
                            'mengakibatkan saldo pertama kali anjlok ke <strong class="text-danger">' + fn.after_formatted + '</strong>.'
                        );
                    } else {
                        $('#box-first-negative-2col').addClass('d-none');
                    }

                    // 4. Populasi Tabel Kronologis (2 Kolom)
                    var tbody = $('#modal-timeline-body').empty();
                    if (res.timeline && res.timeline.length > 0) {
                        $.each(res.timeline, function(i, item) {
                            var typeBadge = '';
                            if (item.type === 'IN') {
                                typeBadge = '<span class="badge badge-light-success fw-bolder fs-8"><i class="fas fa-arrow-down text-success me-1"></i> Top Up</span>';
                            } else if (item.category === 'spp') {
                                typeBadge = '<span class="badge badge-light-danger fw-bolder fs-8"><i class="fas fa-file-invoice-dollar text-danger me-1"></i> Potong Tagihan</span>';
                            } else if (item.category === 'adjustment') {
                                typeBadge = '<span class="badge badge-light-secondary text-gray-700 fw-bolder fs-8"><i class="fas fa-tools text-gray-500 me-1"></i> Penyesuaian</span>';
                            } else {
                                typeBadge = '<span class="badge badge-light-warning text-dark fw-bolder fs-8"><i class="fas fa-shopping-basket text-warning me-1"></i> Kasir (PoS)</span>';
                            }

                            var rowClass = item.is_first_negative ? 'table-danger' : '';
                            var balanceBadge = item.is_negative 
                                ? '<span class="text-danger font-mono fw-bolder fs-8">' + item.balance_after_formatted + '</span>'
                                : '<span class="text-success font-mono fw-bold fs-8">' + item.balance_after_formatted + '</span>';

                            var indicator = item.is_first_negative ? '<span class="badge badge-danger ms-1 fs-9">Awal Minus!</span>' : '';
                            var searchStr = (item.created_at + ' ' + item.description + ' ' + (item.friendly_desc || '') + ' ' + (item.officer_name || '') + ' ' + item.amount_formatted + ' ' + item.category).toLowerCase();

                            var displayTitle = item.friendly_desc || item.description;
                            var rawDescNote = (item.friendly_desc && item.friendly_desc !== item.description)
                                ? '<div class="text-muted fs-9 font-mono mt-0.5"><i class="fas fa-info-circle text-gray-400 me-1"></i>Tercatat: <span class="text-gray-700">' + item.description + '</span></div>'
                                : '';

                            var officerInfo = item.officer_name 
                                ? `<div class="text-primary fs-9 mt-0.5 fw-semibold"><i class="fas fa-user-check me-1"></i>Petugas: <strong>${item.officer_name}</strong> ${item.payment_code ? '&bull; <span class="font-mono text-muted fs-9">(' + item.payment_code + ')</span>' : ''}</div>`
                                : '';

                            var tr = `
                                <tr class="${rowClass}" data-category="${item.category}" data-negative="${item.is_negative}" data-search="${searchStr}">
                                    <td class="text-center font-mono text-muted">${item.index}</td>
                                    <td class="font-mono text-nowrap">${item.created_at}</td>
                                    <td>${typeBadge}</td>
                                    <td class="text-end font-mono fw-bolder">${item.amount_formatted}</td>
                                    <td>
                                        <div class="fw-bold text-gray-900">${displayTitle} ${indicator}</div>
                                        ${officerInfo}
                                        ${rawDescNote}
                                    </td>
                                    <td class="text-end">${balanceBadge}</td>
                                </tr>
                            `;
                            tbody.append(tr);
                        });
                    } else {
                        tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data transaksi.</td></tr>');
                    }
                },
                error: function(err) {
                    $('#modal-loading').addClass('d-none');
                    $('#modal-content').removeClass('d-none');
                    $('#stat-diagnosis-text').html('<span class="text-danger">Gagal memuat rekap log forensik transaksi santri.</span>');
                }
            });
        });
    });
</script>
@endpush
