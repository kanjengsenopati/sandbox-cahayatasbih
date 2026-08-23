@extends('layouts.master', ['title' => 'Data Riwayat Saldo'])

@push('css')
<style>
    /* PakRT Custom Nav Tabs - Full Hitbox & Isolated Stacking Context */
    .saldo-nav-tabs {
        position: relative;
        z-index: 20;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid #e2e8f0;
        padding: 0;
        margin-bottom: 1.5rem;
    }
    .saldo-nav-tabs .nav-item {
        position: relative;
        z-index: 21;
        margin-bottom: -2px;
    }
    .saldo-nav-tabs .nav-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 22px;
        font-weight: 600;
        font-size: 14px;
        color: #64748b;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease-in-out;
        min-height: 48px;
    }
    .saldo-nav-tabs .nav-link:hover {
        color: #2563eb;
        background: rgba(37, 99, 235, 0.05);
        border-bottom-color: #93c5fd;
    }
    .saldo-nav-tabs .nav-link.active {
        color: #2563eb;
        font-weight: 700;
        background: rgba(37, 99, 235, 0.08);
        border-bottom: 3px solid #2563eb;
    }
    .saldo-nav-tabs .nav-link:focus,
    .saldo-nav-tabs .nav-link:active {
        outline: none;
        box-shadow: none;
    }
    #myTabContent {
        position: relative;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Saldo Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('saldo-history.index') }}" class="text-dark text-hover-primary">Data Saldo
                            Siswa</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List Riwayat Saldo</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <a href="{{ route('saldo-bank.index') }}" class="btn btn-sm btn-primary me-2">
                            <i class="fa fa-gear"></i> Setting List Bank
                        </a>
                        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#importSaldoModal">
                            <i class="fa fa-upload"></i> Import Saldo
                        </a>
                    </div>
                    <div>
                        <!-- Button moved to tab -->
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Tabs-->
                    <ul class="nav nav-tabs saldo-nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="top-up-saldo-tab" data-bs-toggle="tab" href="#top-up-saldo"
                                role="tab" aria-controls="top-up-saldo" aria-selected="true">
                                <i class="fas fa-money-bill-wave me-2 fs-7"></i> Top Up Saldo
                            </a>
                        </li>
                        @if(Auth::user()->can('Create Saldo Santri') || Auth::user()->can('Manage Saldo Santri') || (method_exists(Auth::user(), 'isKoordinatorCahayaMart') && Auth::user()->isKoordinatorCahayaMart()))
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="penyesuaian-saldo-tab" data-bs-toggle="tab" href="#penyesuaian-saldo"
                                role="tab" aria-controls="penyesuaian-saldo" aria-selected="false">
                                <i class="fas fa-sliders-h me-2 fs-7"></i> Penyesuaian Saldo
                            </a>
                        </li>
                        @endif
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="arsip-topup-saldo-tab" data-bs-toggle="tab" href="#arsip-topup-saldo"
                                role="tab" aria-controls="arsip-topup-saldo" aria-selected="false">
                                <i class="fas fa-archive me-2 fs-7"></i> Arsip Topup Saldo
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="saldo-history-tab" data-bs-toggle="tab" href="#saldo-history"
                                role="tab" aria-controls="saldo-history" aria-selected="false">
                                <i class="fas fa-history me-2 fs-7"></i> Riwayat Saldo
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <!-- 1. TOP UP SALDO TAB PANE -->
                        <div class="tab-pane fade show active" id="top-up-saldo" role="tabpanel" aria-labelledby="top-up-saldo-tab">
                            <!--begin::Filters Card Toolbar-->
                            <div class="card bg-light-subtle rounded-[20px] p-4 border border-gray-200 mb-4 shadow-sm">
                                <!-- Row 1: Header Title & Status Badge -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 pb-3 border-bottom border-gray-200">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-money-bill-wave text-warning fs-4"></i>
                                        <h4 class="text-slate-800 fw-bolder mb-0 fs-5">Antrean Verifikasi Top Up</h4>
                                    </div>
                                    <span class="badge badge-light-warning text-dark fw-bold fs-8 px-3 py-2 rounded-pill">
                                        <i class="fas fa-clock text-warning me-1"></i> Menunggu Konfirmasi Petugas
                                    </span>
                                </div>

                                <!-- Row 2: Filter Controls Grid -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="fs-7 fw-bold text-gray-700 mb-0">Lembaga:</label>
                                            <select id="topup-school-id" class="form-select form-select-solid form-select-sm rounded-pill" style="width: 140px;">
                                                <option value="">Semua</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="fs-7 fw-bold text-gray-700 mb-0">Kelas:</label>
                                            <select id="topup-classroom-id" class="form-select form-select-solid form-select-sm rounded-pill" style="width: 140px;">
                                                <option value="">Semua Kelas</option>
                                                @foreach($classrooms as $cls)
                                                    <option value="{{ $cls->id }}" data-school="{{ $cls->school_id }}">{{ $cls->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="fs-7 fw-bold text-gray-700 mb-0">Cari:</label>
                                            <div class="position-relative">
                                                <input type="text" id="topup-search-name" class="form-control form-control-solid form-control-sm rounded-pill ps-8" placeholder="Nama Siswa / NIS..." style="width: 180px;">
                                                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-gray-400 fs-8"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button id="topup-btn-filter" class="btn btn-primary btn-sm rounded-pill px-4 shadow-xs"><i class="fas fa-filter me-1"></i> Filter</button>
                                        <button id="topup-btn-reset" class="btn btn-light btn-sm rounded-pill px-4 border"><i class="fas fa-undo me-1"></i> Reset</button>
                                    </div>
                                </div>
                            </div>
                            <!--end::Filters Card Toolbar-->
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-transfer" class="table align-middle table-row-dashed fs-7 gy-3" style="width: 100%;">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 4%">No</th>
                                            <th style="width: 20%">Siswa</th>
                                            <th style="width: 13%">Nominal</th>
                                            <th style="width: 9%">Kode Unik</th>
                                            <th style="width: 20%">Bank Tujuan</th>
                                            <th style="width: 10%">Bukti Transfer</th>
                                            <th style="width: 12%">Status</th>
                                            <th class="text-center min-w-100px" style="width: 12%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>

                        <!-- 2. PENYESUAIAN SALDO TAB PANE -->
                        @if(Auth::user()->can('Create Saldo Santri') || Auth::user()->can('Manage Saldo Santri') || (method_exists(Auth::user(), 'isKoordinatorCahayaMart') && Auth::user()->isKoordinatorCahayaMart()))
                        <div class="tab-pane fade" id="penyesuaian-saldo" role="tabpanel" aria-labelledby="penyesuaian-saldo-tab">
                            <!--begin::Card Header-->
                            <div class="card-header border-0 pt-4 pb-4 px-0">
                                <div class="card-title d-flex align-items-center gap-3 flex-wrap">
                                    <div class="d-flex align-items-center position-relative me-2">
                                        <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </span>
                                        <input type="text" id="custom-search" class="form-control form-control-solid w-250px ps-12 fs-7" placeholder="Cari santri (min. 3 huruf)..." />
                                    </div>
            
                                    <div class="w-200px">
                                        <select id="filter-classroom" class="form-select form-select-solid fs-7">
                                            <option value="">Semua Kelas</option>
                                            @foreach($classrooms as $cls)
                                                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="card-toolbar d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-danger btn-sm rounded-[24px] fw-bold d-none shadow-sm" id="btn-batch-reset-zero" onclick="executeBatchResetZero()">
                                        <i class="fas fa-undo me-1"></i> Reset Saldo Ke Rp 0 <span class="badge badge-circle badge-white ms-1 text-danger" id="selected-zero-count">0</span>
                                    </button>
                                    <span class="badge badge-light-primary fs-7 px-4 py-3 rounded-pill">
                                        <i class="fas fa-info-circle text-primary me-1"></i> Update Saldo Langsung pada Tabel
                                    </span>
                                </div>
                            </div>
                            <!--end::Card Header-->
            
                            <!--begin::Card Body-->
                            <div class="card-body pt-0 px-0 pb-6">
                                <x-alert.alert-validation />
                                
                                <div class="table-responsive">
                                    <table id="table-adjust-saldo" class="table align-middle table-row-dashed fs-6 gy-4" style="width: 100%">
                                        <thead>
                                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-200">
                                                <th style="width: 3%" class="text-center pe-0">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" id="check-all-students" />
                                                    </div>
                                                </th>
                                                <th style="width: 4%">No</th>
                                                <th style="width: 12%">NIS</th>
                                                <th style="width: 22%">Nama</th>
                                                <th style="width: 10%">Kelas</th>
                                                <th style="width: 14%">Saldo Awal</th>
                                                <th style="width: 14%">Saldo Sekarang</th>
                                                <th class="text-center" style="width: 21%">Aksi Update Inline</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-700 fw-bold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <!--end::Card Body-->
                        </div>
                        @endif

                        <!-- 3. ARSIP TOPUP SALDO TAB PANE -->
                        <div class="tab-pane fade" id="arsip-topup-saldo" role="tabpanel" aria-labelledby="arsip-topup-saldo-tab">
                            @include('admins.saldo-history.transfer-tab.archive')
                        </div>

                        <!-- 4. RIWAYAT SALDO TAB PANE -->
                        <div class="tab-pane fade" id="saldo-history" role="tabpanel" aria-labelledby="saldo-history-tab">
                            <!--begin::Filters Card Toolbar-->
                            <div class="card bg-light-subtle rounded-[20px] p-4 border border-gray-200 mb-4 shadow-sm">
                                <!-- Row 1: Header Title & Period Presets -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 pb-3 border-bottom border-gray-200">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-history text-primary fs-4"></i>
                                        <h4 class="text-slate-800 fw-bolder mb-0 fs-5">Riwayat Mutasi Saldo</h4>
                                    </div>
                                    <!--begin::Period Presets-->
                                    <div class="btn-group btn-group-sm" role="group" id="history-period-group">
                                        <button type="button" class="btn btn-light-primary history-period-btn rounded-start-pill px-3" data-period="today">Hari Ini</button>
                                        <button type="button" class="btn btn-primary history-period-btn active px-3" data-period="week">7 Hari Terakhir</button>
                                        <button type="button" class="btn btn-light-primary history-period-btn px-3" data-period="month">Bulan Ini</button>
                                        <button type="button" class="btn btn-light-primary history-period-btn rounded-end-pill px-3" data-period="custom">Cari Sendiri</button>
                                    </div>
                                    <!--end::Period Presets-->
                                </div>

                                <!-- Row 2: Filter Controls Grid -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="fs-7 fw-bold text-gray-700 mb-0">Lembaga:</label>
                                            <select id="saldo-history-school-id" class="form-select form-select-solid form-select-sm rounded-pill" style="width: 140px;">
                                                <option value="">Semua</option>
                                                @foreach ($schools as $school)
                                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="fs-7 fw-bold text-gray-700 mb-0">Kelas:</label>
                                            <div class="dropdown">
                                                <button class="btn btn-light form-select-sm dropdown-toggle text-start rounded-pill" style="width: 140px; background-color: #f5f8fa; border-color: #f5f8fa; color: #5e6278;" type="button" id="saldo_history_classroom_btn" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                                    Semua
                                                </button>
                                                <input type="hidden" id="saldo-history-classroom-id" value="">
                                                <div class="dropdown-menu p-4 shadow" style="min-width: 400px; max-height: 400px; overflow-y: auto;" aria-labelledby="saldo_history_classroom_btn" id="saldo_history_classroom_mega_menu">
                                                    <div class="text-muted fs-7 mb-2">Pilih Lembaga terlebih dahulu</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="fs-7 fw-bold text-gray-700 mb-0">Cari:</label>
                                            <div class="position-relative">
                                                <input type="text" id="saldo-history-search-name" class="form-control form-control-solid form-control-sm rounded-pill ps-8" placeholder="Nama Siswa / NIS..." style="width: 160px;">
                                                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-gray-400 fs-8"></i>
                                            </div>
                                        </div>
                                        <!--begin::Custom Date Range Container-->
                                        <div class="d-flex align-items-center gap-2" id="history-custom-date-container">
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="fs-7 fw-bold text-gray-700 mb-0">Mulai:</label>
                                                <input type="date" id="saldo-history-start-date" class="form-control form-control-solid form-control-sm rounded-pill" value="{{ now()->subDays(6)->format('Y-m-d') }}" style="width: 135px;">
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="fs-7 fw-bold text-gray-700 mb-0">Selesai:</label>
                                                <input type="date" id="saldo-history-end-date" class="form-control form-control-solid form-control-sm rounded-pill" value="{{ now()->format('Y-m-d') }}" style="width: 135px;">
                                            </div>
                                        </div>
                                        <!--end::Custom Date Range Container-->
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button id="saldo-history-btn-filter" class="btn btn-primary btn-sm rounded-pill px-4 shadow-xs"><i class="fas fa-filter me-1"></i> Filter</button>
                                        <button id="saldo-history-btn-reset" class="btn btn-light btn-sm rounded-pill px-4 border"><i class="fas fa-undo me-1"></i> Reset</button>
                                        <button id="saldo-history-btn-recalculate" class="btn btn-warning btn-sm text-dark fw-bold rounded-pill px-3 shadow-xs" title="Perbaiki & Sinkronkan Urutan Saldo"><i class="fas fa-sync-alt me-1"></i> Rekalkulasi</button>
                                    </div>
                                </div>
                            </div>
                            <!--end::Filters Card Toolbar-->
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-saldo-history" class="table align-middle table-row-dashed" style="width: 100%;">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Tanggal</th>
                                            <th class="min-w-100px" style="width: 22%">Siswa</th>
                                            <th class="min-w-100px" style="width: 22%">Jumlah</th>
                                            <th class="min-w-100px" style="width: 22%">Status</th>
                                            <th>Saldo Awal</th>
                                            <th>Saldo Akhir</th>
                                            <th class="min-w-100px" style="width: 22%">Keterangan</th>
                                            <th class="text-center min-w-100px" style="width: 10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                    </div>
                    <!--end::Tabs-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="importSaldoModal" tabindex="-1" aria-labelledby="importSaldoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importSaldoModalLabel">Import Data Saldo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('saldo-history.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="saldoFile" class="form-label">Pilih File Saldo</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx" required>
                        <div class="form-text">Hanya file XLSX yang sesuai dengan template yang diperbolehkan.</div>
                    </div>
                    <div class="mb-3">
                        <h6>Template Import Data</h6>
                        <p>Silakan unduh template berikut untuk mengimpor data saldo:</p>
                        <a href="{{ asset('assets\media\template\import\Template Data Import Saldo.xlsx') }}"
                            class="btn btn-sm btn-secondary" download>Unduh
                            Template</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal View Bukti Transfer -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
            <div class="modal-header border-0 bg-light px-6 py-4">
                <h5 class="modal-title fw-bold text-slate-800" id="imagePreviewModalLabel">Detail Bukti Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-6 bg-white">
                <img id="imagePreviewSrc" src="" class="img-fluid rounded-3 shadow-sm" alt="Bukti Transfer" style="max-height: 70vh; object-fit: contain; border: 1px solid #e2e8f0;">
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    // Global helper to refresh all saldo DataTables synchronously
    function reloadAllSaldoTables() {
        if ($.fn.DataTable.isDataTable('#table-transfer')) {
            $('#table-transfer').DataTable().ajax.reload(null, false);
        }
        if ($.fn.DataTable.isDataTable('#table-adjust-saldo')) {
            $('#table-adjust-saldo').DataTable().ajax.reload(null, false);
        }
        if ($.fn.DataTable.isDataTable('#table-archive')) {
            $('#table-archive').DataTable().ajax.reload(null, false);
        }
        if ($.fn.DataTable.isDataTable('#table-saldo-history')) {
            $('#table-saldo-history').DataTable().ajax.reload(null, false);
        }
    }

    // Helper functions for currency formatting & recalculation
    function formatRupiahVal(number) {
        var val = parseInt(number) || 0;
        return val.toLocaleString('id-ID');
    }

    function parseAmountStr(str) {
        if (!str) return 0;
        var clean = str.toString().replace(/[^0-9]/g, '');
        return parseInt(clean) || 0;
    }

    function onAmountKeyUp(inputElem, rowId) {
        var rawVal = parseAmountStr($(inputElem).val());
        if (rawVal > 0) {
            $(inputElem).val(rawVal.toLocaleString('id-ID'));
        } else {
            $(inputElem).val('');
        }
        recalculateSaldo(rowId);
    }

    function recalculateSaldo(rowId) {
        var baseSaldo = parseInt($('#saldo-awal-' + rowId).attr('data-saldo')) || 0;
        var type = $('#type-' + rowId).val();
        var amount = parseAmountStr($('#amount-' + rowId).val());
        
        var newSaldo = baseSaldo;
        if (type === 'IN') {
            newSaldo = baseSaldo + amount;
        } else if (type === 'WITHDRAW') {
            newSaldo = baseSaldo - amount;
        }

        var badgeElem = $('#saldo-sekarang-' + rowId);
        badgeElem.text('Rp ' + formatRupiahVal(newSaldo));

        if (newSaldo < 0) {
            badgeElem.attr('class', 'badge bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1');
        } else {
            badgeElem.attr('class', 'badge bg-light-success text-success fw-bolder fs-7 mb-1');
        }
    }

    // Topup status updater handlers
    function updateStatus(status, id) {
        const noteTextarea = document.getElementById(`note-${id}`);
        if (!noteTextarea) return;
        if (status == 'REJECTED') {
            if (noteTextarea.tagName.toLowerCase() === 'input') {
                const textarea = document.createElement('textarea');
                textarea.className = 'form-control form-control-sm mt-2';
                textarea.name = 'note';
                textarea.id = `note-${id}`;
                textarea.placeholder = 'Alasan penolakan...';
                textarea.rows = 2;
                textarea.value = noteTextarea.value;
                noteTextarea.replaceWith(textarea);
            }
        } else {
            if (noteTextarea.tagName.toLowerCase() === 'textarea') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'note';
                input.id = `note-${id}`;
                input.value = noteTextarea.value;
                noteTextarea.replaceWith(input);
            }
        }
    }

    function saveStatus(id) {
        const statusElem = document.getElementById(`status-${id}`);
        const noteElem = document.getElementById(`note-${id}`);
        const status = statusElem ? statusElem.value : '';
        const note = noteElem ? noteElem.value : '';

        if (!status) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Status',
                text: 'Silakan pilih status verifikasi terlebih dahulu.'
            });
            return;
        }

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        axios.post(`{{ url('saldo-history/status-payment') }}/${id}`, {
            status: status,
            note: note,
            _token: '{{ csrf_token() }}'
        })
        .then((response) => {
            if (response.data.code == '200' || response.data.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.data.message || 'Status transaksi berhasil diperbarui.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.data.message || 'Terjadi kendala saat memperbarui status.'
                });
            }
            reloadAllSaldoTables();
        })
        .catch((error) => {
            var msg = 'Terjadi kesalahan saat menyimpan data';
            if (error.response && error.response.data && error.response.data.message) {
                msg = error.response.data.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: msg
            });
        });
    }

    // Inline Saldo Adjustment
    function submitInlineSaldo(rowId) {
        var amountStr = $('#amount-' + rowId).val();
        var amount = parseAmountStr(amountStr);
        var type = $('#type-' + rowId).val();
        var desc = $('#desc-' + rowId).val();
        var btn = $('#btn-save-' + rowId);

        if (amount <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan jumlah nominal penyesuaian yang valid terlebih dahulu.',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' }
            });
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        axios.post("{{ route('saldo-history.store') }}", {
            student_id: rowId,
            type: type,
            amount: amount,
            description: desc,
            _token: "{{ csrf_token() }}"
        }, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Update');

            var resData = response.data;
            if (resData && (resData.code == 200 || resData.code == '200' || resData.new_saldo !== undefined)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: resData.message || 'Saldo santri berhasil diperbarui.',
                    timer: 2000,
                    showConfirmButton: false
                });

                var newSaldo = resData.new_saldo !== undefined ? resData.new_saldo : 0;
                var awalBadge = $('#saldo-awal-' + rowId);
                awalBadge.attr('data-saldo', newSaldo).text('Rp ' + formatRupiahVal(newSaldo));
                if (newSaldo < 0) {
                    awalBadge.attr('class', 'badge bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1');
                } else {
                    awalBadge.attr('class', 'badge bg-light-primary text-primary fw-bolder fs-7 mb-1');
                }

                if (resData.updated_date && resData.updated_time) {
                    $('#date-awal-' + rowId).text(resData.updated_date);
                    $('#time-awal-' + rowId).text(resData.updated_time);
                    $('#date-sekarang-' + rowId).text(resData.updated_date);
                    $('#time-sekarang-' + rowId).text(resData.updated_time);
                }
                
                $('#amount-' + rowId).val('');
                $('#desc-' + rowId).val('');
                recalculateSaldo(rowId);
                
                // Synchronize tables
                reloadAllSaldoTables();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: (resData && resData.message) ? resData.message : 'Terjadi kesalahan saat menyimpan penyesuaian saldo.'
                });
            }
        })
        .catch(function(error) {
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Update');
            var msg = 'Terjadi kesalahan pada server saat memperbarui saldo.';
            if (error.response && error.response.data && error.response.data.message) {
                msg = error.response.data.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Gagal Update Saldo',
                text: msg
            });
        });
    }

    // Batch Reset Saldo
    function updateBatchButtonState() {
        var checkedCount = $('.student-select-checkbox:checked').length;
        var btn = $('#btn-batch-reset-zero');
        var counter = $('#selected-zero-count');

        if (checkedCount > 0) {
            btn.removeClass('d-none');
            counter.text(checkedCount);
        } else {
            btn.addClass('d-none');
            counter.text('0');
        }
    }

    function executeBatchResetZero() {
        var selectedBoxes = $('.student-select-checkbox:checked');
        var selectedIds = [];

        selectedBoxes.each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih minimal satu santri yang ingin di-reset saldonya ke Rp 0.',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' }
            });
            return;
        }

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            html: 'Sistem akan me-reset saldo dari <b>' + selectedIds.length + ' santri terpilih</b> menjadi <b>Rp 0</b>. Aksi ini akan mencatat riwayat penyesuaian otomatis.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset Ke Rp 0!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger rounded-[24px]',
                cancelButton: 'btn btn-light rounded-[24px]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Reset Saldo...',
                    text: 'Mohon tunggu sebentar, penyesuaian saldo sedang dilakukan.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                axios.post("{{ route('saldo-history.batch-reset-zero') }}", {
                    student_ids: selectedIds,
                    _token: "{{ csrf_token() }}"
                }, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    var resData = response.data;
                    if (resData && (resData.code == 200 || resData.code == '200')) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Reset!',
                            text: resData.message || 'Saldo santri terpilih berhasil di-reset menjadi Rp 0.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#check-all-students').prop('checked', false);
                        updateBatchButtonState();
                        reloadAllSaldoTables();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: (resData && resData.message) ? resData.message : 'Terjadi kesalahan saat me-reset saldo.'
                        });
                    }
                })
                .catch(function(error) {
                    var msg = 'Terjadi kesalahan pada server saat me-reset saldo.';
                    if (error.response && error.response.data && error.response.data.message) {
                        msg = error.response.data.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Reset Saldo',
                        text: msg
                    });
                });
            }
        });
    }

    // MAIN DOCUMENT READY EXECUTION
    $(document).ready(function() {
        // ==========================================
        // 1. TOP UP SALDO DATATABLE
        // ==========================================
        var topupTable = $('#table-transfer').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: "{{ route('saldo-history.index') }}",
                data: function(d) {
                    d.type = 'topup';
                    d.school_id = $('#topup-school-id').val();
                    d.classroom_id = $('#topup-classroom-id').val();
                    d.search_name = $('#topup-search-name').val();
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                loadingRecords: "Memuat data...",
                processing: "Sedang memproses...",
                emptyTable: "Tidak ada antrean verifikasi top up saldo"
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'student.name',
                    name: 'student.name',
                    defaultContent: '-',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted fs-7">-</span>';
                        let className = (row.student && row.student.classroom) ? row.student.classroom.name : '';
                        let badge = className ? `<span class="badge badge-light-primary fw-bold ms-1" style="font-size: 10px; padding: 3px 6px;">${className}</span>` : '';
                        let nis = (row.student && (row.student.nis || row.student.nisn)) ? `<span class="text-muted fs-8">NIS: ${row.student.nis || row.student.nisn}</span>` : '';
                        return `<div class="d-flex flex-column align-items-start">
                            <span class="text-gray-800 fw-bolder mb-1">${data}</span>
                            <div class="d-flex align-items-center gap-1">${badge} ${nis}</div>
                        </div>`;
                    }
                },
                {
                    data: 'pay_amount',
                    name: 'pay_amount',
                    defaultContent: 'Rp 0'
                },
                {
                    data: 'unique_payment',
                    name: 'unique_payment',
                    defaultContent: '-'
                },
                {
                    data: 'bank_recipient',
                    name: 'bank_recipient',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'proof',
                    name: 'proof',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    defaultContent: '-',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        });

        $('#topup-school-id').on('change', function() {
            var schoolId = $(this).val();
            $('#topup-classroom-id option').each(function() {
                var clsSchool = $(this).data('school');
                if (!schoolId || !clsSchool || clsSchool == schoolId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#topup-classroom-id').val('');
            topupTable.ajax.reload();
        });

        $('#topup-classroom-id').on('change', function() {
            topupTable.ajax.reload();
        });

        $('#topup-btn-filter').on('click', function() {
            topupTable.ajax.reload();
        });

        $('#topup-btn-reset').on('click', function() {
            $('#topup-school-id').val('');
            $('#topup-classroom-id').val('').find('option').show();
            $('#topup-search-name').val('');
            topupTable.ajax.reload();
        });

        $('#topup-search-name').on('keyup', function(e) {
            if (e.keyCode === 13) {
                topupTable.ajax.reload();
            }
        });

        // ==========================================
        // 2. PENYESUAIAN SALDO DATATABLE
        // ==========================================
        var adjustTable = null;
        if ($('#table-adjust-saldo').length) {
            adjustTable = $('#table-adjust-saldo').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                ordering: true,
                order: [],
                ajax: {
                    url: "{{ route('saldo-history.index') }}",
                    data: function(d) {
                        d.type = 'adjust';
                        d.classroom_id = $('#filter-classroom').val();
                    }
                },
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'></i>",
                        previous: "<i class='fa fa-angle-left'></i>"
                    },
                    loadingRecords: "Memuat data...",
                    processing: "Sedang memproses...",
                    emptyTable: "Tidak ada data santri ditemukan"
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        sortable: false,
                        searchable: false,
                        className: 'text-center pe-0',
                        render: function(data, type, row) {
                            return `<div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input student-select-checkbox" type="checkbox" value="${data}" data-saldo="${row.saldo || 0}" data-name="${row.name || ''}" />
                                    </div>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'nis',
                        name: 'nis',
                        defaultContent: '-',
                        orderable: true,
                        sortable: true,
                        searchable: true,
                        render: function(data) {
                            return `<span class="fw-bold text-gray-700 fs-7">${data ? data : '-'}</span>`;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        defaultContent: '-',
                        orderable: true,
                        sortable: true,
                        searchable: true,
                        render: function(data, type, row) {
                            var avatar = row.avatar_url ? row.avatar_url : '{{ asset("assets/media/avatars/default.png") }}';
                            var statusText = row.translated_status || row.status || 'Aktif';
                            var badgeClass = 'bg-light-success text-success';

                            if (row.status === 'INACTIVE') {
                                badgeClass = 'bg-light-danger text-danger';
                            } else if (row.status === 'GRADUATED') {
                                badgeClass = 'bg-light-warning text-warning';
                            } else if (row.status === 'TRANSFERRED') {
                                badgeClass = 'bg-light-info text-info';
                            } else if (row.status === 'DROPPED_OUT') {
                                badgeClass = 'bg-light-secondary text-secondary';
                            }

                            return `
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-35px me-3">
                                        <img src="${avatar}" alt="${data || ''}" style="object-fit: cover;" />
                                    </div>
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="text-gray-800 text-hover-primary fw-bolder fs-6 mb-1">${data || '-'}</span>
                                        <span class="badge ${badgeClass} fs-8 px-2 py-1">${statusText}</span>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'classroom',
                        name: 'classroom',
                        defaultContent: '-',
                        orderable: true,
                        sortable: true,
                        searchable: true,
                        render: function(data) {
                            return `<span class="badge badge-light-dark fs-7">${data || 'Belum ada kelas'}</span>`;
                        }
                    },
                    {
                        data: 'saldo',
                        name: 'saldo',
                        defaultContent: 0,
                        orderable: true,
                        sortable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            var val = parseInt(data) || 0;
                            var formatted = formatRupiahVal(val);
                            var badgeClass = val < 0 
                                ? 'bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1' 
                                : 'bg-success text-white fw-bolder px-3 py-2 fs-7 mb-1';
                            var dateInfo = row.last_saldo_update_date || '-';
                            var timeInfo = row.last_saldo_update_time || '-';

                            return `
                                <div class="d-flex flex-column align-items-start">
                                    <span class="badge ${badgeClass}" id="saldo-awal-${row.id}" data-saldo="${val}">Rp ${formatted}</span>
                                    <span class="text-slate-400 fst-italic mt-1" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="date-awal-${row.id}">${dateInfo}</span>
                                    <span class="text-slate-400 fst-italic" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="time-awal-${row.id}">${timeInfo}</span>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'saldo',
                        name: 'saldo_sekarang',
                        defaultContent: 0,
                        orderable: true,
                        sortable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            var val = parseInt(data) || 0;
                            var formatted = formatRupiahVal(val);
                            var badgeClass = val < 0 
                                ? 'bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1' 
                                : 'bg-success text-white fw-bolder px-3 py-2 fs-7 mb-1';
                            var dateInfo = row.last_saldo_update_date || '-';
                            var timeInfo = row.last_saldo_update_time || '-';

                            return `
                                <div class="d-flex flex-column align-items-start">
                                    <span class="badge ${badgeClass}" id="saldo-sekarang-${row.id}">Rp ${formatted}</span>
                                    <span class="text-slate-400 fst-italic mt-1" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="date-sekarang-${row.id}">${dateInfo}</span>
                                    <span class="text-slate-400 fst-italic" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="time-sekarang-${row.id}">${timeInfo}</span>
                                </div>
                            `;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        sortable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <select class="form-select form-select-sm form-select-solid type-select fs-7" id="type-${row.id}" style="width: 105px;" onchange="recalculateSaldo('${row.id}')">
                                        <option value="IN">+ TopUp</option>
                                        <option value="WITHDRAW">- Tarik</option>
                                    </select>
                                    <div class="input-group input-group-sm" style="width: 140px;">
                                        <span class="input-group-text bg-light text-gray-600 border-0 fs-7 px-2">Rp</span>
                                        <input type="text" class="form-control form-control-sm form-control-solid amount-input fs-7 px-2" id="amount-${row.id}" placeholder="0" onkeyup="onAmountKeyUp(this, '${row.id}')">
                                    </div>
                                    <input type="text" class="form-control form-control-sm form-control-solid desc-input fs-7" id="desc-${row.id}" placeholder="Keterangan..." style="width: 130px;">
                                    <button type="button" class="btn btn-sm btn-primary px-3 py-2 fs-7 btn-save-inline" id="btn-save-${row.id}" onclick="submitInlineSaldo('${row.id}')">
                                        <i class="fas fa-check me-1"></i> Update
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $('#filter-classroom').on('change', function() {
                adjustTable.ajax.reload();
            });

            var searchTimer;
            $('#custom-search').on('keyup input', function() {
                clearTimeout(searchTimer);
                var val = $(this).val().trim();
                searchTimer = setTimeout(function() {
                    if (val.length >= 3) {
                        adjustTable.search(val).draw();
                    } else if (val.length === 0) {
                        adjustTable.search('').draw();
                    }
                }, 300);
            });
        }

        // Multi-select Checkbox Handler
        $(document).on('change', '#check-all-students', function() {
            var isChecked = $(this).is(':checked');
            $('.student-select-checkbox').prop('checked', isChecked);
            updateBatchButtonState();
        });

        $(document).on('change', '.student-select-checkbox', function() {
            var allCount = $('.student-select-checkbox').length;
            var checkedCount = $('.student-select-checkbox:checked').length;
            $('#check-all-students').prop('checked', allCount > 0 && allCount === checkedCount);
            updateBatchButtonState();
        });

        var archiveTable = $('#table-archive').DataTable({
            ordering: true,
            sortable: true,
            processing: true,
            serverSide: true,
            deferRender: true,
            pageLength: 20,
            lengthMenu: [20, 30, 40, 50],
            ajax: {
                url: "{{ route('saldo-history.index') }}",
                data: function(d) {
                    d.type = 'archive';
                    d.school_id = $('#archive-school-id').val();
                    d.classroom_id = $('#archive-classroom-id').val();
                    d.search_name = $('#archive-search-name').val();
                    d.start_date = $('#archive-start-date').val();
                    d.end_date = $('#archive-end-date').val();
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                loadingRecords: "Memuat data...",
                processing: "Sedang memproses...",
                emptyTable: "Tidak ada arsip riwayat top up"
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'student.name',
                    name: 'student.name',
                    defaultContent: '-',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted fs-7">-</span>';
                        let className = (row.student && row.student.classroom) ? row.student.classroom.name : '';
                        let badge = className ? `<span class="badge badge-light-primary fw-bold ms-1" style="font-size: 10px; padding: 3px 6px;">${className}</span>` : '';
                        let nis = (row.student && (row.student.nis || row.student.nisn)) ? `<span class="text-muted fs-8">NIS: ${row.student.nis || row.student.nisn}</span>` : '';
                        return `<div class="d-flex flex-column align-items-start">
                            <span class="text-gray-800 fw-bolder mb-1">${data}</span>
                            <div class="d-flex align-items-center gap-1">${badge} ${nis}</div>
                        </div>`;
                    }
                },
                {
                    data: 'pay_amount',
                    name: 'pay_amount',
                    defaultContent: 'Rp 0'
                },
                {
                    data: 'unique_payment',
                    name: 'unique_payment',
                    defaultContent: '-'
                },
                {
                    data: 'bank_recipient',
                    name: 'bank_recipient',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'proof',
                    name: 'proof',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    defaultContent: '-',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'officer',
                    name: 'officer',
                    defaultContent: '-',
                    orderable: false
                },
                {
                    data: 'updated_at_formatted',
                    name: 'updated_at',
                    defaultContent: '-',
                    orderable: true
                },
                {
                    data: 'action',
                    name: 'action',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        });

        $('#archive-school-id').on('change', function() {
            var schoolId = $(this).val();
            $('#archive-classroom-id option').each(function() {
                var clsSchool = $(this).data('school');
                if (!schoolId || !clsSchool || clsSchool == schoolId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#archive-classroom-id').val('');
            archiveTable.ajax.reload();
        });

        $('#archive-classroom-id').on('change', function() {
            archiveTable.ajax.reload();
        });

        // Helper to compute ISO date string (YYYY-MM-DD) in local time
        function getLocalIsoDate(d) {
            var year = d.getFullYear();
            var month = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }

        function getPresetDates(period) {
            var today = new Date();
            var endStr = getLocalIsoDate(today);
            var startStr = endStr;

            if (period === 'today') {
                startStr = endStr;
            } else if (period === 'week') {
                var d = new Date();
                d.setDate(d.getDate() - 6);
                startStr = getLocalIsoDate(d);
            } else if (period === 'month') {
                var d = new Date(today.getFullYear(), today.getMonth(), 1);
                startStr = getLocalIsoDate(d);
            }
            return { start: startStr, end: endStr };
        }

        // Period button handlers for Archive
        $(document).on('click', '.archive-period-btn', function() {
            var period = $(this).data('period');
            $('.archive-period-btn').removeClass('btn-primary active').addClass('btn-light-primary');
            $(this).removeClass('btn-light-primary').addClass('btn-primary active');

            if (period === 'custom') {
                $('#archive-start-date').focus();
            } else {
                var dates = getPresetDates(period);
                $('#archive-start-date').val(dates.start);
                $('#archive-end-date').val(dates.end);
                archiveTable.ajax.reload();
            }
        });

        $('#archive-btn-filter').on('click', function() {
            $('.archive-period-btn').removeClass('btn-primary active').addClass('btn-light-primary');
            $('.archive-period-btn[data-period="custom"]').removeClass('btn-light-primary').addClass('btn-primary active');
            archiveTable.ajax.reload();
        });

        $('#archive-btn-reset').on('click', function() {
            $('#archive-school-id').val('');
            $('#archive-classroom-id').val('').find('option').show();
            $('#archive-search-name').val('');
            var dates = getPresetDates('week');
            $('#archive-start-date').val(dates.start);
            $('#archive-end-date').val(dates.end);
            $('.archive-period-btn').removeClass('btn-primary active').addClass('btn-light-primary');
            $('.archive-period-btn[data-period="week"]').removeClass('btn-light-primary').addClass('btn-primary active');
            archiveTable.ajax.reload();
        });

        $('#archive-search-name').on('keyup', function(e) {
            if (e.keyCode === 13) {
                archiveTable.ajax.reload();
            }
        });

        $(document).on('click', '.delete-archive-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Arsip riwayat ini akan disembunyikan. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Harap tunggu',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    axios.delete(`{{ url('saldo-history') }}/${id}`, {
                        data: { _token: '{{ csrf_token() }}' }
                    })
                    .then((response) => {
                        if (response.data.code == '200') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            reloadAllSaldoTables();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.data.message
                            });
                        }
                    })
                    .catch((error) => {
                        var msg = 'Terjadi kesalahan saat menghapus arsip';
                        if (error.response && error.response.data && error.response.data.message) {
                            msg = error.response.data.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    });
                }
            });
        });

        // ==========================================
        // 4. RIWAYAT SALDO DATATABLE
        // ==========================================
        var historyTable = $('#table-saldo-history').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: "{{ route('saldo-history.index') }}",
                data: function(d) {
                    d.type = 'saldo';
                    d.school_id = $('#saldo-history-school-id').val();
                    d.classroom_id = $('#saldo-history-classroom-id').val();
                    d.search_name = $('#saldo-history-search-name').val();
                    d.start_date = $('#saldo-history-start-date').val();
                    d.end_date = $('#saldo-history-end-date').val();
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                loadingRecords: "Memuat data...",
                processing: "Sedang memproses...",
                emptyTable: "Tidak ada riwayat mutasi saldo ditemukan"
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'student.name',
                    name: 'student.name',
                    defaultContent: '-',
                    orderable: false,
                    searchable: true,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted fs-7">-</span>';
                        let className = (row.student && row.student.classroom) ? row.student.classroom.name : 'Unknown';
                        let badge = className ? `<span class="badge badge-light-primary fw-bold ms-1" style="font-size: 10px; padding: 3px 6px;">${className}</span>` : '';
                        let nis = (row.student && (row.student.nis || row.student.nisn)) ? `<span class="text-muted fs-8">NIS: ${row.student.nis || row.student.nisn}</span>` : '';
                        return `
                            <div class="d-flex flex-column align-items-start">
                                <span class="text-gray-800 fw-bolder mb-1">${data}</span>
                                <div class="d-flex align-items-center gap-1">${badge} ${nis}</div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'amount',
                    name: 'amount',
                    defaultContent: 'Rp 0',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'balance_before',
                    name: 'balance_before',
                    defaultContent: 'Rp 0',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'balance_after',
                    name: 'balance_after',
                    defaultContent: 'Rp 0',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'description',
                    name: 'description',
                    defaultContent: '-',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'action',
                    name: 'action',
                    defaultContent: '-',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        });

        // Period button handlers for Saldo History
        $(document).on('click', '.history-period-btn', function() {
            var period = $(this).data('period');
            $('.history-period-btn').removeClass('btn-primary active').addClass('btn-light-primary');
            $(this).removeClass('btn-light-primary').addClass('btn-primary active');

            if (period === 'custom') {
                $('#saldo-history-start-date').focus();
            } else {
                var dates = getPresetDates(period);
                $('#saldo-history-start-date').val(dates.start);
                $('#saldo-history-end-date').val(dates.end);
                historyTable.ajax.reload();
            }
        });

        $('#saldo-history-btn-filter').on('click', function() {
            $('.history-period-btn').removeClass('btn-primary active').addClass('btn-light-primary');
            $('.history-period-btn[data-period="custom"]').removeClass('btn-light-primary').addClass('btn-primary active');
            historyTable.ajax.reload();
        });

        $('#saldo-history-btn-reset').on('click', function() {
            $('#saldo-history-school-id').val('');
            $('#saldo-history-classroom-id').val('');
            $('#saldo_history_classroom_btn').text('Semua');
            $('#saldo-history-search-name').val('');
            var dates = getPresetDates('week');
            $('#saldo-history-start-date').val(dates.start);
            $('#saldo-history-end-date').val(dates.end);
            $('.history-period-btn').removeClass('btn-primary active').addClass('btn-light-primary');
            $('.history-period-btn[data-period="week"]').removeClass('btn-light-primary').addClass('btn-primary active');
            historyTable.ajax.reload();
        });
        
        const allHistoryClassrooms = @json($classrooms);

        function renderHistoryClassroomMegaMenu(schoolId) {
            const container = $('#saldo_history_classroom_mega_menu');
            container.empty();

            if (!schoolId) {
                container.html('<div class="text-muted fs-7 mb-2">Pilih Lembaga terlebih dahulu</div>');
                return;
            }

            const filteredClasses = allHistoryClassrooms.filter(c => c.school_id == schoolId);
            if (filteredClasses.length === 0) {
                container.html('<div class="text-muted fs-7 mb-2">Tidak ada kelas ditemukan</div>');
                return;
            }

            const groups = {};
            filteredClasses.forEach(c => {
                let match = c.name.match(/^(\d+)/);
                let key = match ? match[1] : 'Lainnya';
                if (!groups[key]) groups[key] = [];
                groups[key].push(c);
            });

            const row = $('<div class="row g-2"></div>');
            
            container.append($('<a href="#" class="dropdown-item fw-bold text-primary mb-3 history-classroom-item" data-id="" data-name="Semua">Semua</a>'));

            Object.keys(groups).sort((a,b) => parseInt(a) - parseInt(b)).forEach(key => {
                const col = $('<div class="col-4"></div>');
                col.append(`<h6 class="dropdown-header text-uppercase text-muted fw-bolder">Kelas ${key}</h6>`);
                groups[key].forEach(c => {
                    col.append(`<a class="dropdown-item history-classroom-item" href="#" data-id="${c.id}" data-name="${c.name}">${c.name}</a>`);
                });
                row.append(col);
            });

            container.append(row);
        }

        $('#saldo-history-school-id').on('change', function() {
            $('#saldo-history-classroom-id').val('');
            $('#saldo_history_classroom_btn').text('Semua');
            renderHistoryClassroomMegaMenu($(this).val());
        });

        $(document).on('click', '.history-classroom-item', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#saldo-history-classroom-id').val(id);
            $('#saldo_history_classroom_btn').text(name);
            $('#saldo_history_classroom_btn').dropdown('toggle');
        });

        $('#saldo-history-btn-recalculate').on('click', function() {
            Swal.fire({
                title: 'Rekalkulasi Saldo?',
                text: 'Proses ini akan mengurutkan & memperhitungkan ulang seluruh running balance riwayat mutasi saldo santri secara presisi kronologis.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Rekalkulasi Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Rekalkulasi...',
                        text: 'Mohon tunggu sejenak',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    $.ajax({
                        url: "{{ route('saldo-history.recalculate') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire('Berhasil!', res.message || 'Rekalkulasi saldo selesai.', 'success');
                            reloadAllSaldoTables();
                        },
                        error: function(err) {
                            Swal.fire('Gagal!', (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });

        $('#saldo-history-search-name').on('keyup', function(e) {
            if (e.keyCode === 13) {
                historyTable.ajax.reload();
            }
        });

        $(document).on('click', '.delete-history-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Riwayat saldo ini akan dihapus permanen dan saldo siswa akan disesuaikan kembali!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Harap tunggu',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    axios.delete(`{{ url('saldo-history/record') }}/${id}`, {
                        data: { _token: '{{ csrf_token() }}' }
                    })
                    .then((response) => {
                        if (response.data.code == '200') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            reloadAllSaldoTables();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.data.message
                            });
                        }
                    })
                    .catch((error) => {
                        var msg = 'Terjadi kesalahan saat menghapus riwayat';
                        if (error.response && error.response.data && error.response.data.message) {
                            msg = error.response.data.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    });
                }
            });
        });

        // ==========================================
        // 5. UNIFIED TAB SWITCHING & COLUMN ADJUSTMENT
        // ==========================================
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href");
            
            // Seamless URL hash sync
            if (history.replaceState) {
                history.replaceState(null, null, target);
            }

            // Immediately adjust all visible tables geometry
            function adjustVisibleTables() {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }
            adjustVisibleTables();
            setTimeout(adjustVisibleTables, 100);

            // Refresh table upon activating tab for real-time consistency
            if (target === '#top-up-saldo' && topupTable) {
                topupTable.ajax.reload(null, false);
            } else if (target === '#penyesuaian-saldo' && adjustTable) {
                adjustTable.ajax.reload(null, false);
            } else if (target === '#arsip-topup-saldo' && archiveTable) {
                archiveTable.ajax.reload(null, false);
            } else if (target === '#saldo-history' && historyTable) {
                historyTable.ajax.reload(null, false);
            }
        });

        // Activate tab from URL hash or query param (?tab=...)
        var hash = window.location.hash;
        var urlParams = new URLSearchParams(window.location.search);
        var tabParam = urlParams.get('tab');

        if (tabParam) {
            var targetTab = '#' + tabParam.replace('#', '');
            var tabTrigger = $(`a[href="${targetTab}"]`);
            if (tabTrigger.length) {
                tabTrigger.tab('show');
            }
        } else if (hash) {
            var tabTrigger = $(`a[href="${hash}"]`);
            if (tabTrigger.length) {
                tabTrigger.tab('show');
            }
        }

        // Click handler for viewing proof images in a modal
        $(document).on('click', '.view-proof-image', function() {
            var src = $(this).data('src');
            $('#imagePreviewSrc').attr('src', src);
            $('#imagePreviewModal').modal('show');
        });
    });
</script>
@endpush