@extends('layouts.master', ['title' => 'Data Arus Kas'])

@push('css')
<!-- Include Lightbox2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet" />

    <style>
        .premium-card {
            border-radius: 24px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
            border: none !important;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.06) !important;
        }
        .loading-overlay-filters {
            display: none !important;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 100;
            border-radius: 24px;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .loading-overlay-filters.active {
            display: flex !important;
        }
        .loading-overlay-filters-content {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #ffffff;
            padding: 12px 28px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
            animation: bounce-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .loading-overlay-filters-text {
            font-family: 'Outfit', 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 850;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            animation: pulse-text-prominent 1.5s ease-in-out infinite;
        }
        .loading-overlay-filters-text.text-primary {
            color: #2563EB !important; /* Accent primary */
        }
        .loading-overlay-filters-text.text-warning {
            color: #D97706 !important; /* Orange/Warning Accent */
        }
        @keyframes bounce-in {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes pulse-text-prominent {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(0.98); }
        }
        .premium-shadow {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
        }
        .typography-h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-h2 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-amount {
            font-size: 18px;
            font-weight: 700;
            color: #059669; /* Emerald 600 */
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-body {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            font-family: 'Inter', sans-serif;
        }
        .typography-caption {
            font-size: 12px;
            font-weight: 400;
            font-style: italic;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
        }
        .pipeline-step {
            position: relative;
            flex: 1;
            text-align: center;
            padding: 20px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
        }
        .pipeline-step.active {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid #10b981;
        }
        .pipeline-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 24px;
            padding: 0 15px;
        }
    </style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Arus Kas</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('cashflow.index') }}" class="text-muted text-hover-primary">Arus Kas</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Arus Kas</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Tabs Navigation-->
            <ul class="nav nav-tabs nav-line-tabs mb-6 fs-6" role="tablist" style="border-bottom: 2px solid #e2e8f0;">
                <li class="nav-item">
                    <a class="nav-link active fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_pemasukan" role="tab" style="font-family: 'Outfit', sans-serif;">Laporan Pemasukan Siswa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_mutasi" role="tab" style="font-family: 'Outfit', sans-serif;">Alur & Mutasi Kas Internal</a>
                </li>
            </ul>
            <!--end::Tabs Navigation-->

            <!--begin::Tab Content-->
            <div class="tab-content">
                <!--begin::Tab 1: Pemasukan Siswa-->
                <div class="tab-pane fade show active position-relative" id="tab_pemasukan" role="tabpanel">

                    <!--begin::Filters ABOVE Cards-->
                    <div class="premium-card p-6 mb-6 position-relative" style="overflow: hidden;">
                        <!-- Loading Overlay Tab 1 -->
                        <div id="loading-overlay-tab1" class="loading-overlay-filters">
                            <div class="loading-overlay-filters-content">
                                <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem; border-width: 0.2em; color: #2563EB !important;"></div>
                                <span class="loading-overlay-filters-text text-primary">PROSES MEMUAT DATA PEMASUKAN</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                            <form action="#" id="form-filter-tab1" method="get" class="d-flex align-items-center gap-4 flex-wrap">
                                <!-- Filter Tahun Ajaran -->
                                <div>
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Tahun Ajaran</label>
                                    <select id="academic_year_tab1" name="academic_year_id" class="form-select" style="border-radius: 12px; min-width: 180px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                                        <option value="">Semua Tahun Ajaran</option>
                                        @if(isset($academicYears))
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Filter Periode -->
                                <div>
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Periode</label>
                                    <select id="period_tab1" class="form-select" style="border-radius: 12px; min-width: 140px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                                        <option value="semua_periode" selected>Semua Periode</option>
                                        <option value="hari_ini">Hari Ini</option>
                                        <option value="minggu_ini">Minggu Ini</option>
                                        <option value="bulan_ini">Bulan Ini</option>
                                        <option value="pilih_sendiri">Pilih Sendiri</option>
                                    </select>
                                </div>

                                <!-- Filter Jenis Tagihan -->
                                <div>
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Jenis Tagihan</label>
                                    <select id="bill_type_tab1" name="bill_type_name" class="form-select" style="border-radius: 12px; min-width: 180px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                                        <option value="">Semua Jenis Tagihan</option>
                                    </select>
                                </div>

                                <!-- Custom Date Range Picker -->
                                <div id="wrapper_date_tab1" style="display: none;">
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Pilih Rentang Tanggal</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <div id="dateRange_tab1" class="pull-right"
                                            style="background: #fff; cursor: pointer; padding: 7px 14px; border: 1px solid #ccc; border-radius: 12px; color: #475569; font-weight: 500;">
                                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                            <span></span> <b class="caret"></b>
                                        </div>
                                        <input type="text" id="start_date_tab1" name="start_date" hidden>
                                        <input type="text" id="end_date_tab1" name="end_date" hidden>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::Filters-->

                    <!--begin::Cards-->
                    <div class="row mb-6 g-5">
                        <!-- Target Total Pemasukan -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Target Pemasukan</x-text.label>
                                    <div class="bg-light-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-wallet2 text-primary fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="total-cashflow" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Seluruh Tagihan Aktif</x-text.caption>
                            </div>
                        </div>

                        <!-- Realisasi Pemasukan -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Realisasi Pemasukan</x-text.label>
                                    <div class="bg-light-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-check-circle text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="total-payment" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Tagihan Lunas (PAID)</x-text.caption>
                            </div>
                        </div>

                        <!-- Status Pemasukan (Realisasi - Target) -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Status Pemasukan</x-text.label>
                                    <div id="status-pemasukan-icon-bg" class="bg-light-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i id="status-pemasukan-icon" class="bi bi-graph-down text-danger fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="status-pemasukan-diff" class="d-block text-danger">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption id="status-pemasukan-desc" class="text-muted d-block">Defisit Selisih Target</x-text.caption>
                            </div>
                        </div>

                        <!-- Persentase Realisasi -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Persentase Pemasukan</x-text.label>
                                    <div class="bg-light-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-percent text-info fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="percentage-realisasi" class="d-block text-primary">0%</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Rasio Realisasi Pemasukan</x-text.caption>
                            </div>
                        </div>
                    </div>
                    <!--end::Cards-->

                    <!--begin::BI Grid Breakdown-->
                    <div class="row mb-6 g-5">
                        <!-- Breakdown Pemasukan per Jenis Tagihan -->
                        <div class="col-12 col-md-8" style="width: 70%; flex: 0 0 70%; max-width: 70%;">
                            <div class="premium-card">
                                <div class="card-header border-0 pt-6 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <span class="typography-h2">Breakdown per Jenis Tagihan</span>
                                    <div class="position-relative">
                                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-4 text-slate-400" style="font-size: 14px;"></i>
                                        <input type="text" id="search-jenis-tagihan" class="form-control form-control-solid ps-10 py-2 fs-7" placeholder="Cari jenis tagihan..." style="border-radius: 12px; width: 220px; font-weight: 500; border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;" />
                                    </div>
                                </div>
                                <div class="card-body pt-2" style="max-height: 280px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed table-sm">
                                            <thead>
                                                <tr class="text-start text-gray-800 fw-bolder fs-7 text-uppercase">
                                                    <th style="color: #1e293b;">Nama Pembayaran</th>
                                                    <th class="text-end text-nowrap" style="color: #1e293b;">Target Pemasukan</th>
                                                    <th class="text-end text-nowrap" style="color: #1e293b;">Total Pemasukan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="breakdown-bills-tbody" class="fw-bold text-gray-600">
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">Memuat data breakdown...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sumber Pemasukan -->
                        <div class="col-12 col-md-4" style="width: 30%; flex: 0 0 30%; max-width: 30%;">
                            <div class="premium-card">
                                <div class="card-header border-0 pt-6">
                                    <span class="typography-h2">Sumber Pemasukan</span>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-around" style="height: 280px;">
                                    <!-- Tunai -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="typography-body fw-bold">Tunai</span>
                                            <span class="typography-body fw-bolder" id="source-tunai-amount">Rp 0</span>
                                        </div>
                                        <div class="progress" style="height: 10px; border-radius: 6px;">
                                            <div class="progress-bar bg-primary" id="source-tunai-bar" role="progressbar" style="width: 0%; border-radius: 6px;"></div>
                                        </div>
                                    </div>
                                    <!-- Debit Saldo -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="typography-body fw-bold">Debit Saldo</span>
                                            <span class="typography-body fw-bolder text-emerald-600" id="source-saldo-amount">Rp 0</span>
                                        </div>
                                        <div class="progress" style="height: 10px; border-radius: 6px;">
                                            <div class="progress-bar bg-success" id="source-saldo-bar" role="progressbar" style="width: 0%; border-radius: 6px;"></div>
                                        </div>
                                    </div>
                                    <!-- Transfer Aplikasi -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="typography-body fw-bold">Transfer Aplikasi</span>
                                            <span class="typography-body fw-bolder" id="source-transfer-amount">Rp 0</span>
                                        </div>
                                        <div class="progress" style="height: 10px; border-radius: 6px;">
                                            <div class="progress-bar bg-info" id="source-transfer-bar" role="progressbar" style="width: 0%; border-radius: 6px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::BI Grid Breakdown-->

                    <!--begin::BI Detailed Breakdown Table-->
                    <div class="row mb-6">
                        <div class="col-12">
                            <div class="premium-card">
                                <div class="card-header border-0 pt-6">
                                    <span class="typography-h2">Detil Breakdown per Jenis Tagihan</span>
                                </div>
                                <div class="card-body pt-2" style="max-height: 400px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed table-sm">
                                            <thead>
                                                <tr class="text-start text-gray-800 fw-bolder fs-7 text-uppercase">
                                                     <th style="color: #1e293b; width: 30%;">Nama Pembayaran</th>
                                                     <th class="text-end" style="color: #1e293b; width: 15%;">Target Pemasukan</th>
                                                     <th class="text-end" style="color: #1e293b; width: 15%;">Total Pemasukan</th>
                                                     <th class="text-end text-primary" style="color: #2563EB; width: 13%;">Tunai</th>
                                                     <th class="text-end text-success" style="color: #10B981; width: 13%;">Debit Saldo</th>
                                                     <th class="text-end text-info" style="color: #0EA5E9; width: 14%;">Transfer Aplikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="breakdown-detail-bills-tbody" class="fw-bold text-gray-600">
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">Memuat data detil breakdown...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::BI Detailed Breakdown Table-->
                </div>
                <!--end::Tab 1-->

                <!--begin::Tab 2: Alur & Mutasi Kas Internal-->
                <div class="tab-pane fade position-relative" id="tab_mutasi" role="tabpanel">

                    <!--begin::Filters ABOVE Cards-->
                    <div class="premium-card p-6 mb-6 position-relative" style="overflow: hidden;">
                        <!-- Loading Overlay Tab 2 -->
                        <div id="loading-overlay-tab2" class="loading-overlay-filters">
                            <div class="loading-overlay-filters-content">
                                <div class="spinner-border text-warning" role="status" style="width: 1.5rem; height: 1.5rem; border-width: 0.2em; color: #D97706 !important;"></div>
                                <span class="loading-overlay-filters-text text-warning">PROSES MEMUAT DATA MUTASI KAS</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                            <form action="#" id="form-filter-tab2" method="get" class="d-flex align-items-center gap-4 flex-wrap">
                                <!-- Filter Tahun Ajaran -->
                                <div>
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Tahun Ajaran</label>
                                    <select id="academic_year_tab2" name="academic_year_id" class="form-select" style="border-radius: 12px; min-width: 180px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                                        <option value="">Semua Tahun Ajaran</option>
                                        @if(isset($academicYears))
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Filter Periode -->
                                <div>
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Periode</label>
                                    <select id="period_tab2" class="form-select" style="border-radius: 12px; min-width: 150px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                                        <option value="semua_periode" selected>Semua Periode</option>
                                        <option value="bulan_ini">Bulan Ini</option>
                                        <option value="3_bulan">3 Bulan</option>
                                        <option value="6_bulan">6 Bulan</option>
                                        <option value="tahun_ini">Tahun Ini</option>
                                        <option value="pilih_sendiri">Pilih Sendiri</option>
                                    </select>
                                </div>

                                @if(!auth()->user()->outlet_id)
                                <div>
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Outlet</label>
                                    <select id="filter_outlet_id_tab2" name="outlet_id" class="form-select" style="border-radius: 12px; min-width: 180px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                                        <option value="">Semua Outlet</option>
                                        @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Custom Date Range Picker -->
                                <div id="wrapper_date_tab2" style="display: none;">
                                    <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Pilih Rentang Tanggal</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <div id="dateRange_tab2" class="pull-right"
                                            style="background: #fff; cursor: pointer; padding: 7px 14px; border: 1px solid #ccc; border-radius: 12px; color: #475569; font-weight: 500;">
                                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                            <span></span> <b class="caret"></b>
                                        </div>
                                        <input type="text" id="start_date_tab2" name="start_date" hidden>
                                        <input type="text" id="end_date_tab2" name="end_date" hidden>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::Filters-->

                    <!--begin::Cards-->
                    <div class="row mb-6 g-5">
                        <!-- Total Pemasukan -->
                        <div class="col-md-4">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Total Pemasukan (Tunai)</x-text.label>
                                    <div class="bg-light-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-wallet2 text-primary fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="total-pemasukan-tab2" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Pemasukan Tunai Terkumpul di Piket</x-text.caption>
                            </div>
                        </div>

                        <!-- Mutasi ke Bendahara -->
                        <div class="col-md-4">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Mutasi ke Bendahara</x-text.label>
                                    <div class="bg-light-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-arrow-down-up text-warning fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="mutasi-bendahara-tab2" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Dana Diserahkan ke Bendahara</x-text.caption>
                            </div>
                        </div>

                        <!-- Mutasi ke Yayasan -->
                        <div class="col-md-4">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Mutasi ke Yayasan</x-text.label>
                                    <div class="bg-light-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-bank text-danger fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="mutasi-yayasan-tab2" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Dana Diterima Pengurus Yayasan</x-text.caption>
                            </div>
                        </div>
                    </div>
                    <!--end::Cards-->

                    <!--begin::Piping Tracing Pipeline-->
                    <div class="premium-card mb-6">
                        <div class="card-header border-0 pt-6">
                            <span class="typography-h2">Visual Pipeline Alur Penyerahan Dana Tunai (End-to-End)</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch gap-4">
                                <!-- Step 1: Petugas Piket -->
                                <div class="pipeline-step active">
                                    <span class="typography-label d-block mb-1 text-primary">Level 1: Petugas Piket</span>
                                    <span class="typography-caption d-block mb-3 text-muted">Kas Tunai Terkumpul di Piket</span>
                                    <span class="fs-4 fw-bolder text-primary d-block" id="pipe-piket-cash">Rp 0</span>
                                </div>
                                <div class="pipeline-arrow"><i class="bi bi-arrow-right fs-1"></i></div>
                                <!-- Step 2: Bendahara -->
                                <div class="pipeline-step active">
                                    <span class="typography-label d-block mb-1 text-warning">Level 2: Bendahara</span>
                                    <span class="typography-caption d-block mb-3 text-muted">Dana Diserahkan ke Bendahara</span>
                                    <span class="fs-4 fw-bolder text-warning d-block" id="pipe-bendahara-cash">Rp 0</span>
                                </div>
                                <div class="pipeline-arrow"><i class="bi bi-arrow-right fs-1"></i></div>
                                <!-- Step 3: Yayasan -->
                                <div class="pipeline-step active">
                                    <span class="typography-label d-block mb-1 text-danger">Level 3: Pengurus Yayasan</span>
                                    <span class="typography-caption d-block mb-3 text-muted">Dana Diterima Pengurus Yayasan</span>
                                    <span class="fs-4 fw-bolder text-danger d-block" id="pipe-yayasan-cash">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--begin::Piket Officers Tracing-->
                    <div class="premium-card mb-6">
                        <div class="card-header border-0 pt-6">
                            <span class="typography-h2">Tracing Kas Petugas Piket (Transaksi Tunai)</span>
                        </div>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase">
                                            <th>Nama Petugas Piket</th>
                                            <th class="text-center">Jumlah Transaksi</th>
                                            <th class="text-end">Total Uang Masuk</th>
                                            <th class="text-end">Sudah Diserahkan</th>
                                            <th class="text-end text-danger">Kas di Tangan (Belum Diserahkan)</th>
                                            <th class="text-center">Aksi Serah Terima</th>
                                        </tr>
                                    </thead>
                                    <tbody id="piket-officers-tbody" class="fw-bold text-gray-600">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Memuat petugas piket...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!--begin::Card Datatable-->
                    <div class="premium-card">
                        <div class="card-header d-flex justify-content-between align-items-center mb-5 border-0 pt-6">
                            <!-- Title di Kiri -->
                            <span class="typography-h2">Riwayat Transaksi Mutasi</span>

                            <!-- Button Tambah di Kanan -->
                            <div class="d-flex align-items-center gap-3">
                                <button class="btn btn-success d-flex align-items-center gap-2" id="btn-serah-terima" style="border-radius: 14px;">
                                    <i class="bi bi-send-check fs-5"></i> Ajukan Serah Terima Dana
                                </button>
                                <x-action.create name="Arus Kas" action="{{ route('cashflow.create') }}" />
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="table-cashflow" class="table align-middle table-row-dashed ">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th style="width: 10%">Tanggal</th>
                                            <th style="width: 12%">Kode</th>
                                            <th style="width: 10%">Tipe</th>
                                            <th style="width: 15%">Kategori</th>
                                            <th style="width: 25%">Dari/Kepada</th>
                                            <th style="width: 10%">Jumlah</th>
                                            <th style="width: 10%">Status</th>
                                            <th style="width: 15%">Keterangan</th>
                                            <th style="width: 10%">Bukti</th>
                                            <th class="text-center min-w-100px" style="width: 15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Tab Content-->
        </div>
    </div>
    <!--end::Post-->
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true" class="rounded-[24px]">
    <div class="modal-dialog">
        <div class="modal-content premium-shadow" style="border-radius: 24px;">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Alasan Penolakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="reject_cashflow_id" name="cashflow_id">
                    <div class="mb-3">
                        <label for="status_reason" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" id="status_reason" name="status_reason" required style="border-radius: 12px;"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-danger" style="border-radius: 12px;">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Serah Terima Dana -->
<div class="modal fade" id="serahTerimaModal" tabindex="-1" aria-labelledby="serahTerimaModalLabel" aria-hidden="true" class="rounded-[24px]">
    <div class="modal-dialog modal-lg">
        <div class="modal-content premium-shadow" style="border-radius: 24px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="serahTerimaModalLabel">Form Serah Terima Dana Arus Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serahTerimaForm" action="{{ route('cashflow.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="INCOME">
                    <input type="hidden" name="payment_method" value="CASH">

                    <div class="row g-4">
                        <!-- Kategori Serah Terima -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kategori Serah Terima</label>
                            <select class="form-select" id="handover_category" name="cash_flow_category_id" required style="border-radius: 12px;">
                                <option value="" disabled selected>Pilih Alur Kategori</option>
                                <!-- Categories will be populated dynamically -->
                            </select>
                        </div>

                        <!-- Penerima Dana -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Penerima Dana (Bendahara/Yayasan)</label>
                            <select class="form-select" id="handover_receiver" name="receiver_id" required style="border-radius: 12px;">
                                <option value="" disabled selected>Pilih Penerima</option>
                                <!-- Admins will be populated dynamically -->
                            </select>
                        </div>

                        <!-- Nominal Serah Terima -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nominal Penyerahan (Rp)</label>
                            <input type="text" class="form-control" id="handover_amount" name="amount" required placeholder="Contoh: 50.000" style="border-radius: 12px;">
                        </div>

                        <!-- Tanggal Penyerahan -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Penyerahan</label>
                            <input type="date" class="form-control" name="date" required value="{{ date('Y-m-d') }}" style="border-radius: 12px;">
                        </div>

                        <!-- Bukti Serah Terima -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Bukti Penyerahan (Foto/PDF)</label>
                            <input type="file" class="form-control" name="proof_of_payment" accept="image/*,application/pdf" style="border-radius: 12px;">
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Keterangan / Catatan Tambahan</label>
                            <textarea class="form-control" name="description" placeholder="Tuliskan keterangan detail di sini..." style="border-radius: 12px; height: 80px;"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-6">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <button type="submit" class="btn btn-success" style="border-radius: 12px;">Kirim Pengajuan Serah Terima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi Piket -->
<div class="modal fade" id="detailTransaksiModal" tabindex="-1" aria-labelledby="detailTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content premium-shadow" style="border-radius: 24px; border: none; background: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-6 px-8 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title typography-h1" id="detailTransaksiModalLabel" style="font-size: 22px; font-weight: 700; color: #0f172a; font-family: 'Outfit', sans-serif;">Detail Transaksi Tunai</h5>
                    <span class="typography-caption text-muted" id="detailTransaksiOfficer" style="font-size: 12px; color: #94a3b8; font-style: italic; display: block; margin-top: 4px;">Petugas: -</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-8 pb-8 pt-4">
                <!-- Search & Entries Control -->
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-4 mb-5">
                    <!-- Page Size Selector -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="typography-body text-slate-600" style="font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;">Tampilkan</span>
                        <select id="modal-page-size" class="form-select form-select-solid py-2 px-3" style="border-radius: 12px; width: 85px; font-weight: 600; border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </select>
                        <span class="typography-body text-slate-600" style="font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;">data</span>
                    </div>

                    <!-- Search Input -->
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-4 text-slate-400" style="font-size: 14px;"></i>
                        <input type="text" id="modal-search" class="form-control form-control-solid ps-10 py-2 fs-7" placeholder="Cari tagihan atau UPT..." style="border-radius: 12px; width: 260px; font-weight: 500; border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;" />
                    </div>
                </div>

                <!-- Table Container -->
                <div class="table-responsive" style="border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <table class="table align-middle table-row-dashed table-hover mb-0">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase" style="border-bottom: 2px solid #e2e8f0; background: #f8fafc;">
                                <th class="ps-5 py-4" style="width: 60px; color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em;">No</th>
                                <th class="py-4 cursor-pointer text-hover-primary modal-sortable-column" data-sort="date_raw" style="color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; user-select: none; white-space: nowrap;">
                                    Tgl Transaksi <i class="bi bi-arrow-down-up ms-1 text-slate-400 modal-sort-icon" style="font-size: 10px;"></i>
                                </th>
                                <th class="py-4 cursor-pointer text-hover-primary modal-sortable-column" data-sort="bill_type" style="color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; user-select: none; white-space: nowrap;">
                                    Nama Tagihan <i class="bi bi-arrow-down-up ms-1 text-slate-400 modal-sort-icon" style="font-size: 10px;"></i>
                                </th>
                                <th class="py-4 text-end cursor-pointer text-hover-primary modal-sortable-column" data-sort="amount" style="color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; user-select: none; white-space: nowrap;">
                                    Nominal <i class="bi bi-arrow-down-up ms-1 text-slate-400 modal-sort-icon" style="font-size: 10px;"></i>
                                </th>
                                <th class="py-4 cursor-pointer text-hover-primary modal-sortable-column" data-sort="student_name" style="color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; user-select: none; white-space: nowrap;">
                                    Nama Siswa <i class="bi bi-arrow-down-up ms-1 text-slate-400 modal-sort-icon" style="font-size: 10px;"></i>
                                </th>
                                <th class="pe-5 py-4 cursor-pointer text-hover-primary modal-sortable-column" data-sort="classroom" style="color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; user-select: none; white-space: nowrap;">
                                    Kelas <i class="bi bi-arrow-down-up ms-1 text-slate-400 modal-sort-icon" style="font-size: 10px;"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="modal-transactions-tbody" class="fw-bold text-gray-700">
                            <!-- Rows will be injected dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination & Info -->
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-4 mt-5 px-2">
                    <div class="typography-caption text-slate-400" id="modal-table-info" style="font-size: 12px; color: #94a3b8; font-family: 'Inter', sans-serif;">
                        Menampilkan 0 sampai 0 dari 0 data
                    </div>
                    <ul class="pagination pagination-outline justify-content-end mb-0" id="modal-pagination" style="gap: 6px;">
                        <!-- Pagination buttons injected dynamically -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
<script>
    var globalCategories = {};
    var globalAdmins = [];

    // Helper to format currency
    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    $(document).ready(() => {
        var table = $('#table-cashflow').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            responsive: true,
            ajax: {
                url: '{{ route('cashflow.index') }}',
                data: function(d) {
                    d.type = 'data';
                    d.start_date = $('#start_date_tab2').val();
                    d.end_date = $('#end_date_tab2').val();
                    d.academic_year_id = $('#academic_year_tab2').val();
                    d.outlet_id = $('#filter_outlet_id_tab2').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    responsivePriority: -1
                },
               {
                    data: 'date',
                    name: 'date',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'payment_code',
                    name: 'payment_code',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'type',
                    name: 'type',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'category',
                    name: 'category',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'from_to',
                    name: 'from_to',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'description',
                    name: 'description',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'proof',
                    name: 'proof',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
            ]
        });

        // Format handover amount input as currency on type
        $('#handover_amount').on('keyup', function() {
            var val = this.value.replace(/\D/g, '');
            if (val) {
                this.value = Number(val).toLocaleString('id-ID');
            }
        });

        // Approve action
        $(document).on('click', '.approve-btn', function() {
            var cashflowId = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda akan menyetujui arus kas ini!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terima!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('/cashflow/approve/' + cashflowId, {
                            _token: "{{ csrf_token() }}",
                            status: 'APPROVED'
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.data.message,
                            confirmButtonText: 'Ok'
                        });
                        location.reload(); 
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan!',
                            text: 'Tidak dapat menyetujui Arus Kas.',
                            confirmButtonText: 'Ok'
                        });
                    }
                }
            });
        });

        // Reject action
        $(document).on('click', '.reject-btn', function() {
            var cashflowId = $(this).data('id');
            $('#reject_cashflow_id').val(cashflowId);
        });

        // Reject form submission
        $('#rejectForm').on('submit', async function(e) {
            e.preventDefault();
            var cashflowId = $('#reject_cashflow_id').val();
            var statusReason = $('#status_reason').val();
            try {
                const response = await axios.post('/cashflow/reject/' + cashflowId, {
                    _token: "{{ csrf_token() }}",
                    status: 'REJECTED',
                    reason: statusReason
                });
                Swal.fire({
                    icon: 'success',
                    title: 'Ditolak!',
                    text: response.data.message,
                    confirmButtonText: 'Ok'
                });
                location.reload();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan!',
                    text: 'Tidak dapat menolak Arus Kas.',
                    confirmButtonText: 'Ok'
                });
            }
        });

        // Click on serah terima dana button
        $('#btn-serah-terima').on('click', function() {
            populateHandoverForm(null, null);
        });

        // Quick action: serah terima from officer list
        $(document).on('click', '.btn-piket-serah', function() {
            var officerId = $(this).data('id');
            var cash = $(this).data('cash');
            populateHandoverForm(officerId, cash);
        });

        function populateHandoverForm(officerId, cash) {
            // Fill Categories Dropdown
            var catSelect = $('#handover_category');
            catSelect.empty().append('<option value="" disabled selected>Pilih Alur Kategori</option>');
            
            if (globalCategories.piket_to_bendahara) {
                catSelect.append('<option value="'+globalCategories.piket_to_bendahara+'">Serah Terima Piket ke Bendahara</option>');
            }
            if (globalCategories.bendahara_to_yayasan) {
                catSelect.append('<option value="'+globalCategories.bendahara_to_yayasan+'">Serah Terima Bendahara ke Yayasan</option>');
            }

            // Fill Receivers Dropdown
            var recSelect = $('#handover_receiver');
            recSelect.empty().append('<option value="" disabled selected>Pilih Penerima</option>');
            globalAdmins.forEach(function(admin) {
                recSelect.append('<option value="'+admin.id+'">'+admin.name+'</option>');
            });

            // Set Category default if pre-filled from officer cash
            if (cash !== null && cash > 0) {
                catSelect.val(globalCategories.piket_to_bendahara);
                $('#handover_amount').val(Number(cash).toLocaleString('id-ID'));
            } else {
                $('#handover_amount').val('');
            }

            var modal = new bootstrap.Modal(document.getElementById('serahTerimaModal'));
            modal.show();
        }

        // --- Detail Transaksi Piket Modal JS Engine ---
        let currentModalData = [];
        let modalPage = 1;
        let modalPageSize = 10;
        let modalSearchQuery = "";
        let modalSortColumn = null;
        let modalSortDir = 'asc';

        $(document).on('click', '.btn-detail-transaksi', function(e) {
            e.preventDefault();
            var officerId = $(this).data('id');
            var officerName = $(this).data('name');

            $('#detailTransaksiOfficer').text('Petugas: ' + officerName);
            $('#modal-transactions-tbody').html('<tr><td colspan="6" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat detail transaksi...</td></tr>');
            $('#modal-table-info').text('Menampilkan 0 sampai 0 dari 0 data');
            $('#modal-pagination').empty();

            $('#modal-search').val('');
            modalSearchQuery = "";
            $('#modal-page-size').val(10);
            modalPageSize = 10;

            modalSortColumn = null;
            modalSortDir = 'asc';
            $('.modal-sort-icon').removeClass('bi-arrow-down bi-arrow-up').addClass('bi-arrow-down-up');

            var modalEl = document.getElementById('detailTransaksiModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            var params = {
                type: 'piket_transactions',
                admin_id: officerId
            };
            var sd = $('#start_date_tab2').val();
            var ed = $('#end_date_tab2').val();
            var ay = $('#academic_year_tab2').val();
            var ot = $('#filter_outlet_id_tab2').val();
            if (sd) params.start_date = sd;
            if (ed) params.end_date = ed;
            if (ay) params.academic_year_id = ay;
            if (ot) params.outlet_id = ot;

            axios.get("{{ route('cashflow.index') }}", {
                params: params
            })
            .then(function(response) {
                if (response.data && response.data.success) {
                    currentModalData = response.data.data;
                    modalPage = 1;
                    renderModalTable();
                } else {
                    $('#modal-transactions-tbody').html('<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data detail transaksi.</td></tr>');
                }
            })
            .catch(function(error) {
                console.error("Error loading piket transactions:", error);
                $('#modal-transactions-tbody').html('<tr><td colspan="6" class="text-center text-danger py-4">Terjadi kesalahan saat menghubungi server.</td></tr>');
            });
        });

        function renderModalTable() {
            var tbody = $('#modal-transactions-tbody');
            tbody.empty();

            var filtered = currentModalData.filter(function(item) {
                if (!modalSearchQuery) return true;
                var query = modalSearchQuery.toLowerCase().trim();
                var billType = (item.bill_type || '').toLowerCase();
                var student = (item.student_name || '').toLowerCase();
                var classroom = (item.classroom || '').toLowerCase();
                var date = (item.date || '').toLowerCase();
                return billType.indexOf(query) !== -1 || 
                       student.indexOf(query) !== -1 || 
                       classroom.indexOf(query) !== -1 ||
                       date.indexOf(query) !== -1;
            });

            if (modalSortColumn) {
                filtered.sort(function(a, b) {
                    var valA = a[modalSortColumn];
                    var valB = b[modalSortColumn];

                    if (modalSortColumn === 'amount') {
                        valA = Number(valA) || 0;
                        valB = Number(valB) || 0;
                    } else {
                        valA = String(valA || '').toLowerCase();
                        valB = String(valB || '').toLowerCase();
                    }

                    if (valA < valB) return modalSortDir === 'asc' ? -1 : 1;
                    if (valA > valB) return modalSortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            var total = filtered.length;
            var startIdx = (modalPage - 1) * modalPageSize;
            var endIdx = startIdx + modalPageSize;
            var pageData = filtered.slice(startIdx, endIdx);

            if (total === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data transaksi yang cocok</td></tr>');
                $('#modal-table-info').text('Menampilkan 0 sampai 0 dari 0 data');
                $('#modal-pagination').empty();
                return;
            }

            pageData.forEach(function(item, idx) {
                var globalIdx = startIdx + idx + 1;
                
                var isDemo = item.is_demo || (item.upt && item.upt.toLowerCase().includes('demo'));
                var billTypeDisplay = item.bill_type;
                if (isDemo) {
                    billTypeDisplay = item.bill_type + ' <span class="badge bg-light-danger text-danger ms-2" style="border-radius: 6px; font-size: 10px; padding: 2px 6px; font-weight: 700; text-transform: uppercase;">Demo</span>';
                }

                tbody.append(
                    '<tr>' +
                    '<td class="ps-5 py-3 text-muted" style="font-family: \'Outfit\', sans-serif;">' + globalIdx + '</td>' +
                    '<td class="py-3 text-slate-800">' + item.date + '</td>' +
                    '<td class="py-3 text-slate-800">' + billTypeDisplay + '</td>' +
                    '<td class="py-3 text-end text-emerald-600 font-weight-bold" style="font-family: \'Outfit\', sans-serif;">' + item.amount_formatted + '</td>' +
                    '<td class="py-3 text-slate-800">' + item.student_name + '</td>' +
                    '<td class="pe-5 py-3 text-slate-800">' + item.classroom + '</td>' +
                    '</tr>'
                );
            });

            var showStart = startIdx + 1;
            var showEnd = Math.min(endIdx, total);
            $('#modal-table-info').text('Menampilkan ' + showStart + ' sampai ' + showEnd + ' dari ' + total + ' data');

            renderModalPagination(total);
        }

        function renderModalPagination(total) {
            var paginationUl = $('#modal-pagination');
            paginationUl.empty();

            var totalPages = Math.ceil(total / modalPageSize);
            if (totalPages <= 1) return;

            var prevClass = modalPage === 1 ? 'disabled' : '';
            paginationUl.append(
                '<li class="page-item ' + prevClass + '">' +
                '<a class="page-link modal-page-btn" href="#" data-page="' + (modalPage - 1) + '" style="border-radius: 8px; border: 1px solid #cbd5e1; padding: 6px 12px;"><i class="fa fa-angle-left"></i></a>' +
                '</li>'
            );

            for (var p = 1; p <= totalPages; p++) {
                var activeClass = modalPage === p ? 'active' : '';
                var activeStyle = modalPage === p ? 'background-color: #2563EB !important; border-color: #2563EB !important; color: white !important;' : 'border: 1px solid #cbd5e1;';
                paginationUl.append(
                    '<li class="page-item ' + activeClass + '">' +
                    '<a class="page-link modal-page-btn" href="#" data-page="' + p + '" style="border-radius: 8px; padding: 6px 12px; ' + activeStyle + '">' + p + '</a>' +
                    '</li>'
                );
            }

            var nextClass = modalPage === totalPages ? 'disabled' : '';
            paginationUl.append(
                '<li class="page-item ' + nextClass + '">' +
                '<a class="page-link modal-page-btn" href="#" data-page="' + (modalPage + 1) + '" style="border-radius: 8px; border: 1px solid #cbd5e1; padding: 6px 12px;"><i class="fa fa-angle-right"></i></a>' +
                '</li>'
            );
        }

        $('#modal-page-size').on('change', function() {
            modalPageSize = parseInt($(this).val(), 10);
            modalPage = 1;
            renderModalTable();
        });

        $('#modal-search').on('keyup input', function() {
            modalSearchQuery = $(this).val();
            modalPage = 1;
            renderModalTable();
        });

        $('.modal-sortable-column').on('click', function() {
            var col = $(this).data('sort');
            if (modalSortColumn === col) {
                modalSortDir = modalSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                modalSortColumn = col;
                modalSortDir = 'asc';
            }

            $('.modal-sort-icon').removeClass('bi-arrow-down bi-arrow-up').addClass('bi-arrow-down-up');

            var activeIcon = $(this).find('.modal-sort-icon');
            activeIcon.removeClass('bi-arrow-down-up');
            if (modalSortDir === 'asc') {
                activeIcon.addClass('bi-arrow-down');
            } else {
                activeIcon.addClass('bi-arrow-up');
            }

            modalPage = 1;
            renderModalTable();
        });

        $(document).on('click', '.modal-page-btn', function(e) {
            e.preventDefault();
            var targetPage = $(this).data('page');
            if (targetPage) {
                modalPage = parseInt(targetPage, 10);
                renderModalTable();
            }
        });
    });
</script>
<script>
    // Tab 1 Date Picker setup
    var start_tab1 = moment().startOf('month');
    var end_tab1 = moment().endOf('month');

    $('#dateRange_tab1').daterangepicker({
        startDate: start_tab1,
        endDate: end_tab1,
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().endOf('month')],
            '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().endOf('month')],
            '9 Bulan Terakhir': [moment().subtract(9, 'month').startOf('month'), moment().endOf('month')],
            'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        }
    }, function(start, end) {
        $('#dateRange_tab1 span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
        $('#start_date_tab1').val(start.format('YYYY-MM-DD'));
        $('#end_date_tab1').val(end.format('YYYY-MM-DD'));
        
        loadTab1Data();
    });

    // Initial: "Semua Periode" = no date filter
    $('#start_date_tab1').val('');
    $('#end_date_tab1').val('');
    $('#dateRange_tab1 span').html('Semua Periode');

    // Tab 2 Date Picker setup
    var start_tab2 = moment().startOf('month');
    var end_tab2 = moment().endOf('month');

    $('#dateRange_tab2').daterangepicker({
        startDate: start_tab2,
        endDate: end_tab2,
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().endOf('month')],
            '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().endOf('month')],
            '9 Bulan Terakhir': [moment().subtract(9, 'month').startOf('month'), moment().endOf('month')],
            'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        }
    }, function(start, end) {
        $('#dateRange_tab2 span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
        $('#start_date_tab2').val(start.format('YYYY-MM-DD'));
        $('#end_date_tab2').val(end.format('YYYY-MM-DD'));
        
        loadTab2Data();
    });

    // Initial: "Semua Periode" = no date filter
    $('#start_date_tab2').val('');
    $('#end_date_tab2').val('');
    $('#dateRange_tab2 span').html('Semua Periode');

    // Event listener for Filter Tahun Ajaran Tab 1 & Tab 2
    $('#academic_year_tab1').on('change', function() {
        // Reset jenis tagihan filter when academic year changes
        $('#bill_type_tab1').val('');
        loadTab1Data();
    });

    // Event listener for Filter Jenis Tagihan Tab 1
    $('#bill_type_tab1').on('change', function() {
        loadTab1Data();
    });

    $('#academic_year_tab2').on('change', function() {
        loadTab2Data();
    });

    $('#filter_outlet_id_tab2').on('change', function() {
        loadTab2Data();
    });

    // Event listener for Filter Periode Tab 1
    $('#period_tab1').on('change', function() {
        var selected = $(this).val();
        var start, end;
        
        if (selected === 'semua_periode') {
            $('#wrapper_date_tab1').hide();
            $('#start_date_tab1').val('');
            $('#end_date_tab1').val('');
            $('#dateRange_tab1 span').html('Semua Periode');
            loadTab1Data();
        } else if (selected === 'hari_ini') {
            $('#wrapper_date_tab1').hide();
            start = moment().startOf('day');
            end = moment().endOf('day');
            updateDatesAndReloadTab1(start, end);
        } else if (selected === 'minggu_ini') {
            $('#wrapper_date_tab1').hide();
            start = moment().startOf('week');
            end = moment().endOf('week');
            updateDatesAndReloadTab1(start, end);
        } else if (selected === 'bulan_ini') {
            $('#wrapper_date_tab1').hide();
            start = moment().startOf('month');
            end = moment().endOf('month');
            updateDatesAndReloadTab1(start, end);
        } else if (selected === 'pilih_sendiri') {
            $('#wrapper_date_tab1').show();
        }
    });

    // Event listener for Filter Periode Tab 2
    $('#period_tab2').on('change', function() {
        var selected = $(this).val();
        var start, end;
        
        if (selected === 'semua_periode') {
            $('#wrapper_date_tab2').hide();
            $('#start_date_tab2').val('');
            $('#end_date_tab2').val('');
            $('#dateRange_tab2 span').html('Semua Periode');
            loadTab2Data();
        } else if (selected === 'bulan_ini') {
            $('#wrapper_date_tab2').hide();
            start = moment().startOf('month');
            end = moment().endOf('month');
            updateDatesAndReloadTab2(start, end);
        } else if (selected === '3_bulan') {
            $('#wrapper_date_tab2').hide();
            start = moment().subtract(3, 'months').startOf('month');
            end = moment().endOf('month');
            updateDatesAndReloadTab2(start, end);
        } else if (selected === '6_bulan') {
            $('#wrapper_date_tab2').hide();
            start = moment().subtract(6, 'months').startOf('month');
            end = moment().endOf('month');
            updateDatesAndReloadTab2(start, end);
        } else if (selected === 'tahun_ini') {
            $('#wrapper_date_tab2').hide();
            start = moment().startOf('year');
            end = moment().endOf('year');
            updateDatesAndReloadTab2(start, end);
        } else if (selected === 'pilih_sendiri') {
            $('#wrapper_date_tab2').show();
        }
    });

    function updateDatesAndReloadTab1(start, end) {
        $('#start_date_tab1').val(start.format('YYYY-MM-DD'));
        $('#end_date_tab1').val(end.format('YYYY-MM-DD'));
        $('#dateRange_tab1 span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
        loadTab1Data();
    }

    function updateDatesAndReloadTab2(start, end) {
        $('#start_date_tab2').val(start.format('YYYY-MM-DD'));
        $('#end_date_tab2').val(end.format('YYYY-MM-DD'));
        $('#dateRange_tab2 span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
        loadTab2Data();
    }

    // Tab 1 Loader
    function loadTab1Data() {
        $('#loading-overlay-tab1').addClass('active');
        var params = { type: 'summary' };
        var sd = $('#start_date_tab1').val();
        var ed = $('#end_date_tab1').val();
        var ay = $('#academic_year_tab1').val();
        var bt = $('#bill_type_tab1').val();
        if (sd) params.start_date = sd;
        if (ed) params.end_date = ed;
        if (ay) params.academic_year_id = ay;
        if (bt) params.bill_type_name = bt;

        axios.get("{{ route('cashflow.index') }}", {
            params: params
        })
        .then(function (response) {
            // Update Card values for Tab 1
            $('#total-payment').text('Rp ' + response.data.total_incomes);
            $('#total-cashflow').text('Rp ' + response.data.total_cashflows);
            
            // Status Pemasukan (Realisasi - Target)
            var diff = response.data.status_pemasukan_diff;
            $('#status-pemasukan-diff').text(response.data.status_pemasukan_diff_formatted);
            if (diff < 0) {
                $('#status-pemasukan-diff').css('color', '#dc2626');
                $('#status-pemasukan-icon-bg').removeClass('bg-light-success bg-light-primary bg-light-info').addClass('bg-light-danger');
                $('#status-pemasukan-icon').removeClass('bi-graph-up bi-percent').addClass('bi-graph-down').css('color', '#dc2626');
                $('#status-pemasukan-desc').text('Defisit Selisih Target');
            } else {
                $('#status-pemasukan-diff').css('color', '#059669');
                $('#status-pemasukan-icon-bg').removeClass('bg-light-danger bg-light-primary bg-light-info').addClass('bg-light-success');
                $('#status-pemasukan-icon').removeClass('bi-graph-down bi-percent').addClass('bi-graph-up').css('color', '#059669');
                $('#status-pemasukan-desc').text('Surplus / Sesuai Target');
            }

            // Persentase Realisasi
            $('#percentage-realisasi').text(response.data.percentage_realisasi);

            // Save global state
            globalCategories = response.data.categories;
            globalAdmins = response.data.active_admins;

            // Breakdown Bills Tbody
            var billsTbody = $('#breakdown-bills-tbody');
            billsTbody.empty();

            // Populate Jenis Tagihan filter dropdown (always repopulate with full list, preserve selection)
            if (response.data.all_bill_type_names && response.data.all_bill_type_names.length > 0) {
                var btSelect = $('#bill_type_tab1');
                var prevVal = btSelect.val();
                btSelect.find('option:not(:first)').remove();
                response.data.all_bill_type_names.forEach(function(name) {
                    btSelect.append('<option value="' + name + '">' + name + '</option>');
                });
                btSelect.val(prevVal);
            }

            if (!response.data.breakdown_bills || response.data.breakdown_bills.length === 0) {
                billsTbody.append('<tr><td colspan="3" class="text-center text-muted py-4">Tidak ada data breakdown pembayaran lunas</td></tr>');
            } else {
                response.data.breakdown_bills.forEach(function(item) {
                    billsTbody.append('<tr><td>' + item.name + '</td><td class="text-end text-primary">' + item.target_formatted + '</td><td class="text-end text-emerald-600">' + item.total_formatted + '</td></tr>');
                });
            }

            // Breakdown Detail Bills Tbody
            var detailTbody = $('#breakdown-detail-bills-tbody');
            detailTbody.empty();
            if (!response.data.breakdown_detail_bills || response.data.breakdown_detail_bills.length === 0) {
                detailTbody.append('<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data detil breakdown</td></tr>');
            } else {
                response.data.breakdown_detail_bills.forEach(function(item) {
                    detailTbody.append(
                        '<tr>' +
                        '<td>' + item.name + '</td>' +
                        '<td class="text-end text-primary">' + item.target_formatted + '</td>' +
                        '<td class="text-end text-emerald-600">' + item.total_formatted + '</td>' +
                        '<td class="text-end text-primary" style="opacity: 0.85;">' + item.paid_cash_formatted + '</td>' +
                        '<td class="text-end text-success" style="opacity: 0.85;">' + item.paid_balance_formatted + '</td>' +
                        '<td class="text-end text-info" style="opacity: 0.85;">' + item.paid_transfer_formatted + '</td>' +
                        '</tr>'
                    );
                });
            }

            // Filter tables according to search query if any
            filterBreakdownTables();

            // Breakdown Sources
            $('#source-tunai-amount').text('Rp ' + response.data.breakdown_sources.tunai);
            $('#source-saldo-amount').text('Rp ' + response.data.breakdown_sources.saldo);
            $('#source-transfer-amount').text('Rp ' + response.data.breakdown_sources.transfer);

            // Compute percentages
            var parseVal = function(str) { return Number(str.replace(/\D/g, '')); };
            var tunaiVal = parseVal(response.data.breakdown_sources.tunai);
            var saldoVal = parseVal(response.data.breakdown_sources.saldo);
            var transferVal = parseVal(response.data.breakdown_sources.transfer);
            var totalSum = tunaiVal + saldoVal + transferVal;

            var tunaiPct = totalSum > 0 ? (tunaiVal / totalSum * 100).toFixed(1) : 0;
            var saldoPct = totalSum > 0 ? (saldoVal / totalSum * 100).toFixed(1) : 0;
            var transferPct = totalSum > 0 ? (transferVal / totalSum * 100).toFixed(1) : 0;

            $('#source-tunai-bar').css('width', tunaiPct + '%');
            $('#source-saldo-bar').css('width', saldoPct + '%');
            $('#source-transfer-bar').css('width', transferPct + '%');
        })
        .catch(function (error) {
            console.error("Error loading Tab 1 data:", error);
        })
        .finally(function () {
            $('#loading-overlay-tab1').removeClass('active');
        });
    }

    // Tab 2 Loader
    function loadTab2Data() {
        $('#loading-overlay-tab2').addClass('active');
        var params = { type: 'summary' };
        var sd = $('#start_date_tab2').val();
        var ed = $('#end_date_tab2').val();
        var ay = $('#academic_year_tab2').val();
        var ot = $('#filter_outlet_id_tab2').val();
        if (sd) params.start_date = sd;
        if (ed) params.end_date = ed;
        if (ay) params.academic_year_id = ay;
        if (ot) params.outlet_id = ot;

        axios.get("{{ route('cashflow.index') }}", {
            params: params
        })
        .then(function (response) {
            // Update Card values for Tab 2
            $('#total-pemasukan-tab2').text(response.data.workflow_stats.total_piket_cash);
            $('#mutasi-bendahara-tab2').text(response.data.workflow_stats.total_handed_bendahara);
            $('#mutasi-yayasan-tab2').text(response.data.workflow_stats.total_handed_yayasan);

            // Save global state
            globalCategories = response.data.categories;
            globalAdmins = response.data.active_admins;

            // Visual Pipeline
            $('#pipe-piket-cash').text(response.data.workflow_stats.total_piket_cash);
            $('#pipe-bendahara-cash').text(response.data.workflow_stats.total_handed_bendahara);
            $('#pipe-yayasan-cash').text(response.data.workflow_stats.total_handed_yayasan);

            // Tracing Piket Officers Tbody
            var piketTbody = $('#piket-officers-tbody');
            piketTbody.empty();
            if (!response.data.piket_officers || response.data.piket_officers.length === 0) {
                piketTbody.append('<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada aktivitas piket tunai pada periode ini</td></tr>');
            } else {
                response.data.piket_officers.forEach(function(off) {
                    var actionBtn = '';
                    if (off.cash_in_hand > 0) {
                        actionBtn = '<button class="btn btn-sm btn-light-success btn-piket-serah font-weight-bold" style="border-radius: 10px;" data-id="'+off.id+'" data-cash="'+off.cash_in_hand+'"><i class="bi bi-arrow-up-right"></i> Serahkan Dana</button>';
                    } else {
                        actionBtn = '<span class="badge bg-light-success text-success" style="border-radius: 8px;"><i class="bi bi-check-all"></i> Semua Diserahkan</span>';
                    }

                    piketTbody.append(
                        '<tr>' +
                        '<td>' + off.name + '</td>' +
                        '<td class="text-center"><a href="#" class="btn-detail-transaksi text-primary text-decoration-none font-weight-bold" style="color: #2563EB !important;" data-id="'+off.id+'" data-name="'+off.name+'">' + off.total_txs + ' Transaksi</a></td>' +
                        '<td class="text-end">' + off.total_collected_formatted + '</td>' +
                        '<td class="text-end text-success">' + off.handed_over_formatted + '</td>' +
                        '<td class="text-end text-danger">' + off.cash_in_hand_formatted + '</td>' +
                        '<td class="text-center">' + actionBtn + '</td>' +
                        '</tr>'
                    );
                });
            }

            // Reload DataTable data
            if ($.fn.DataTable.isDataTable('#table-cashflow')) {
                $('#table-cashflow').DataTable().ajax.reload();
            }
        })
        .catch(function (error) {
            console.error("Error loading Tab 2 data:", error);
        })
        .finally(function () {
            $('#loading-overlay-tab2').removeClass('active');
        });
    }

    // Filter tables according to search query
    function filterBreakdownTables() {
        var query = $('#search-jenis-tagihan').val().toLowerCase().trim();
        
        // Saring tabel pertama: Breakdown per Jenis Tagihan
        $('#breakdown-bills-tbody tr').each(function() {
            var row = $(this);
            if (row.find('td').length < 3) {
                return;
            }
            var name = row.find('td:first-child').text().toLowerCase();
            if (name.indexOf(query) > -1) {
                row.show();
            } else {
                row.hide();
            }
        });

        // Saring tabel kedua secara otomatis (Sinkronisasi): Detil Breakdown per Jenis Tagihan
        $('#breakdown-detail-bills-tbody tr').each(function() {
            var row = $(this);
            if (row.find('td').length < 6) {
                return;
            }
            var name = row.find('td:first-child').text().toLowerCase();
            if (name.indexOf(query) > -1) {
                row.show();
            } else {
                row.hide();
            }
        });
    }

    // Bind event listener ke input pencarian
    $(document).ready(function() {
        $('#search-jenis-tagihan').on('keyup input', function() {
            filterBreakdownTables();
        });
    });

    // Event listener for tab transitions
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === "#tab_pemasukan") {
            loadTab1Data();
        } else if (target === "#tab_mutasi") {
            loadTab2Data();
        }
    });

    // Initial default load
    $(document).ready(function() {
        loadTab1Data();
    });
</script>
@endpush