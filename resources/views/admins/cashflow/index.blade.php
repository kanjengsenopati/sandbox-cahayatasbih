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
                <div class="tab-pane fade show active" id="tab_pemasukan" role="tabpanel">
                    <!--begin::Filters ABOVE Cards-->
                    <div class="premium-card p-6 mb-6">
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

                        <!-- Dana Mutasi Bendahara -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Dana Mutasi Bendahara</x-text.label>
                                    <div class="bg-light-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-arrow-down-up text-warning fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="total-mutasi-bendahara" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Dana Diserahkan ke Bendahara</x-text.caption>
                            </div>
                        </div>

                        <!-- Mutasi Ke Pengurus Yayasan -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Mutasi Ke Pengurus Yayasan</x-text.label>
                                    <div class="bg-light-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-bank text-danger fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount id="total-mutasi-yayasan" class="d-block">Rp 0</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Dana Diterima Pengurus Yayasan</x-text.caption>
                            </div>
                        </div>
                    </div>
                    <!--end::Cards-->

                    <!--begin::BI Grid Breakdown-->
                    <div class="row mb-6 g-5">
                        <!-- Breakdown Pemasukan per Jenis Tagihan -->
                        <div class="col-12 col-md-8" style="width: 70%; flex: 0 0 70%; max-width: 70%;">
                            <div class="premium-card">
                                <div class="card-header border-0 pt-6">
                                    <span class="typography-h2">Breakdown per Jenis Tagihan</span>
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
                </div>
                <!--end::Tab 1-->

                <!--begin::Tab 2: Alur & Mutasi Kas Internal-->
                <div class="tab-pane fade" id="tab_mutasi" role="tabpanel">
                    <!--begin::Filters ABOVE Cards-->
                    <div class="premium-card p-6 mb-6">
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
        loadTab1Data();
    });

    $('#academic_year_tab2').on('change', function() {
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
        var params = { type: 'summary' };
        var sd = $('#start_date_tab1').val();
        var ed = $('#end_date_tab1').val();
        var ay = $('#academic_year_tab1').val();
        if (sd) params.start_date = sd;
        if (ed) params.end_date = ed;
        if (ay) params.academic_year_id = ay;

        axios.get("{{ route('cashflow.index') }}", {
            params: params
        })
        .then(function (response) {
            // Update Card values for Tab 1
            $('#total-payment').text('Rp ' + response.data.total_incomes);
            $('#total-cashflow').text('Rp ' + response.data.total_cashflows);
            $('#total-mutasi-bendahara').text(response.data.workflow_stats.total_handed_bendahara);
            $('#total-mutasi-yayasan').text(response.data.workflow_stats.total_handed_yayasan);

            // Save global state
            globalCategories = response.data.categories;
            globalAdmins = response.data.active_admins;

            // Breakdown Bills Tbody
            var billsTbody = $('#breakdown-bills-tbody');
            billsTbody.empty();
            if (!response.data.breakdown_bills || response.data.breakdown_bills.length === 0) {
                billsTbody.append('<tr><td colspan="3" class="text-center text-muted py-4">Tidak ada data breakdown pembayaran lunas</td></tr>');
            } else {
                response.data.breakdown_bills.forEach(function(item) {
                    billsTbody.append('<tr><td>' + item.name + '</td><td class="text-end text-primary">' + item.target_formatted + '</td><td class="text-end text-emerald-600">' + item.total_formatted + '</td></tr>');
                });
            }

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
        });
    }

    // Tab 2 Loader
    function loadTab2Data() {
        var params = { type: 'summary' };
        var sd = $('#start_date_tab2').val();
        var ed = $('#end_date_tab2').val();
        var ay = $('#academic_year_tab2').val();
        if (sd) params.start_date = sd;
        if (ed) params.end_date = ed;
        if (ay) params.academic_year_id = ay;

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
                        '<td class="text-center">' + off.total_txs + ' Transaksi</td>' +
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
        });
    }

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