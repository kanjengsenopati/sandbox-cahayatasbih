@extends('layouts.master', ['title' => 'Sinkronisasi Database'])

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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Sinkronisasi Database</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="#" class="text-muted text-hover-primary">Audit dan Sinkron</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Sinkronisasi Database
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <span class="badge badge-light-success fw-bolder fs-7 px-3 py-2 border border-success border-opacity-25">
                        <i class="fas fa-signal text-success me-1"></i> Connected to Cloud Master DB (103.193.179.146)
                    </span>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">

                <!-- Session Alerts -->
                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center p-5 mb-6">
                        <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                <path d="M10.4343 12.4343L8.75 10.75C8.33579 10.3358 7.66421 10.3358 7.25 10.75C6.83579 11.1642 6.83579 11.8358 7.25 12.25L9.69289 14.6929C10.0834 15.0834 10.7166 15.0834 11.1071 14.6929L16.75 9.05C17.1642 8.63579 17.1642 7.96421 16.75 7.55C16.3358 7.13579 15.6642 7.13579 15.25 7.55L10.4343 12.4343Z" fill="currentColor"></path>
                            </svg>
                        </span>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Sukses</h4>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center p-5 mb-6">
                        <span class="svg-icon svg-icon-2hx svg-icon-danger me-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                <rect x="11" y="14" width="2" height="2" rx="1" fill="currentColor"></rect>
                                <rect x="11" y="7" width="2" height="5" rx="1" fill="currentColor"></rect>
                            </svg>
                        </span>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Gagal</h4>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Primary Redesigned Feature Tabs Navigation -->
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-bolder mb-6 gap-2" id="syncMainTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 active d-flex align-items-center gap-2" id="tab-verified-link" data-bs-toggle="tab" href="#tab-verified-pane" role="tab" onclick="fetchMasterDiff('students')">
                            <i class="fas fa-search-plus text-primary fs-3"></i>
                            1. Verifikasi Data Aplikasi Lama (Selective Ingestion)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 d-flex align-items-center gap-2" id="tab-fullsync-link" data-bs-toggle="tab" href="#tab-fullsync-pane" role="tab">
                            <i class="fas fa-database text-primary fs-3"></i>
                            2. Sinkronisasi Full Database (Otomatis Inkremental)
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="syncMainTabsContent">
                    <!-- Tab 1 Pane: Verified Ingestion -->
                    <div class="tab-pane fade show active" id="tab-verified-pane" role="tabpanel">
                        <!-- Master Data Pull & Verification Preview Panel -->
                        <div class="card card-flush shadow-sm mb-6 border-0 rounded-[24px]" id="preview-master-card" style="display: block;">
                            <div class="card-header border-0 pt-6 bg-light-primary rounded-top-[24px]">
                                <div class="card-title flex-column">
                                    <h3 class="card-label fw-bolder text-primary d-flex align-items-center gap-2">
                                        <i class="fas fa-search-plus text-primary fs-2"></i> Preview & Verifikasi Diff Data Aplikasi Lama
                                    </h3>
                                    <span class="text-muted mt-1 fw-bold fs-7">
                                        Menampilkan perbandingan presisi data Aplikasi Lama (<code>cahayatasbihdb</code>) vs Lokal (<code>aplikasidb</code>) tanpa menimpa data otomatis.
                                    </span>
                                </div>
                                <div class="card-toolbar d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="select-diff-limit" class="fs-8 fw-bolder text-gray-600 mb-0 d-none d-md-inline">Cakupan Data:</label>
                                        <select id="select-diff-limit" class="form-select form-select-sm fw-bold border-primary text-primary style-slim-select" style="width: auto;" onchange="fetchMasterDiff(document.getElementById('current-merge-module').value)">
                                            <option value="50">50 Record (Preview Cepat)</option>
                                            <option value="100">100 Record</option>
                                            <option value="500">500 Record</option>
                                            <option value="1500" selected>1.185+ Record (Semua Siswa Aktif Master)</option>
                                        </select>
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline btn-outline-primary active btn-mod-tab" onclick="fetchMasterDiff('students', this)">Siswa</button>
                                        <button type="button" class="btn btn-outline btn-outline-primary btn-mod-tab" onclick="fetchMasterDiff('classrooms', this)">Kelas</button>
                                        <button type="button" class="btn btn-outline btn-outline-primary btn-mod-tab" onclick="fetchMasterDiff('schools', this)">Sekolah</button>
                                        <button type="button" class="btn btn-outline btn-outline-primary btn-mod-tab" onclick="fetchMasterDiff('academic_years', this)">Tahun Ajaran</button>
                                        <button type="button" class="btn btn-outline btn-outline-primary btn-mod-tab" onclick="fetchMasterDiff('bill_types', this)">Jenis Tagihan</button>
                                        <button type="button" class="btn btn-outline btn-outline-success btn-mod-tab" onclick="fetchMasterDiff('saldo', this)"><i class="fas fa-wallet me-1 fs-8"></i> Verifikasi Saldo</button>
                                        <button type="button" class="btn btn-outline btn-outline-danger btn-mod-tab" onclick="fetchMasterDiff('billing_status', this)"><i class="fas fa-file-invoice-dollar me-1 fs-8"></i> Status Tagihan</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-4">
                                <!-- Filter Container per Sekolah & Kelas (Hanya Tampil saat Modul Status Tagihan Aktif) -->
                                <div class="row g-3 mb-4 d-none p-3 bg-light-danger rounded-3 border border-danger border-opacity-25" id="billing-filter-container">
                                    <div class="col-md-6">
                                        <label class="fs-8 fw-bolder text-gray-700 mb-1"><i class="fas fa-school text-danger me-1"></i> Filter Sekolah:</label>
                                        <select class="form-select form-select-sm fw-bold border-danger style-slim-select" id="select-filter-school" onchange="onSchoolFilterChange()">
                                            <option value="">Semua Sekolah</option>
                                            @foreach($schools ?? [] as $sch)
                                                <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fs-8 fw-bolder text-gray-700 mb-1"><i class="fas fa-chalkboard-teacher text-danger me-1"></i> Filter Kelas:</label>
                                        <select class="form-select form-select-sm fw-bold border-danger style-slim-select" id="select-filter-classroom" onchange="fetchMasterDiff(document.getElementById('current-merge-module').value)">
                                            <option value="">Semua Kelas</option>
                                            @foreach($classrooms ?? [] as $cls)
                                                <option value="{{ $cls->id }}" data-school="{{ $cls->school_id }}">{{ $cls->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- Status Counter Summary Badges (Clickable Filters) -->
                                <div class="d-flex align-items-center gap-2 mb-4 p-3 bg-light rounded-3 flex-wrap" id="diff-status-badges">
                                    <span class="badge badge-light-success fw-bolder fs-7 px-3 py-2 cursor-pointer filter-badge" id="cnt-new" onclick="filterDiffTable('NEW_RECORD', this)" style="cursor: pointer; transition: all 0.2s;" title="Klik untuk memfilter Aplikasi Lama Baru">🟢 Aplikasi Lama Baru: 0</span>
                                    <span class="badge badge-light-warning fw-bolder fs-7 px-3 py-2 cursor-pointer filter-badge" id="cnt-update" onclick="filterDiffTable('UPDATE_REQUIRED', this)" style="cursor: pointer; transition: all 0.2s;" title="Klik untuk memfilter Butuh Update">🟡 Butuh Update: 0</span>
                                    <span class="badge badge-light-info fw-bolder fs-7 px-3 py-2 cursor-pointer filter-badge" id="cnt-match" onclick="filterDiffTable('EXACT_MATCH', this)" style="cursor: pointer; transition: all 0.2s;" title="Klik untuk memfilter 100% Identik">🔵 100% Identik: 0</span>
                                    <span class="badge badge-light-danger fw-bolder fs-7 px-3 py-2 cursor-pointer filter-badge" id="cnt-conflict" onclick="filterDiffTable('CONFLICT', this)" style="cursor: pointer; transition: all 0.2s;" title="Klik untuk memfilter Konflik Mapping">🔴 Konflik Mapping: 0</span>
                                    <button type="button" class="btn btn-xs btn-light-secondary border border-gray-300 fs-8 fw-bold ms-auto d-none" id="btn-reset-diff-filter" onclick="filterDiffTable('ALL', null)">
                                        <i class="fas fa-undo me-1"></i> Reset Filter (<span id="txt-active-filter-label">Semua</span>)
                                    </button>
                                </div>

                                <form action="{{ route('admin.audit.confirm-merge-master') }}" method="POST" id="form-confirm-merge">
                                    @csrf
                                    <input type="hidden" name="module" id="current-merge-module" value="students">

                                    <div class="table-responsive style-slim-scroll" style="max-height: 380px;">
                                        <table class="table table-hover table-striped align-middle table-row-dashed fs-7 gy-3" id="tbl-diff-preview">
                                            <thead class="bg-light sticky-top">
                                                <tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">
                                                    <th class="w-40px px-3">
                                                        <input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)">
                                                    </th>
                                                    <th>ID / Code</th>
                                                    <th>Nama Record</th>
                                                    <th>Status Mapping</th>
                                                    <th>Perbandingan Kolom (Aplikasi Lama ➔ Lokal)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-diff-preview">
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">
                                                        <i class="fas fa-spinner fa-spin me-2"></i> Memuat analisis perbandingan...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between pt-4 mt-3 border-top">
                                        <span class="text-muted fs-7" id="txt-selected-count">0 record terpilih untuk di-merge</span>
                                        <button type="submit" class="btn btn-sm btn-success fw-bolder" id="btn-submit-merge" disabled>
                                            <i class="fas fa-check-circle me-1 text-white"></i> Konfirmasi & Terapkan Data Terverifikasi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2 Pane: Full Database Sync -->
                    <div class="tab-pane fade" id="tab-fullsync-pane" role="tabpanel">
                        <!-- Sync Form Card -->
                        <form action="{{ route('admin.sync-master') }}" method="POST" id="sync-db-form">
                            @csrf
                            <div class="card card-flush shadow-sm mb-6 border-0 rounded-[24px]">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title flex-column">
                                        <h3 class="card-label fw-bolder text-dark">Sinkronisasi Database Aplikasi Lama (Full Sync)</h3>
                                        <span class="text-muted mt-1 fw-bold fs-7">
                                            Menyinkronkan data transaksi harian (30 hari terakhir) secara inkremental dari
                                            <code>cahayatasbihdb</code> ke <code>aplikasidb</code>.
                                        </span>
                                    </div>
                                    <div class="card-toolbar">
                                        <button type="submit" class="btn btn-sm btn-primary fw-bolder" id="btn-sync-submit">
                                            <i class="fas fa-database me-1 fs-7 text-white"></i> Sinkronkan Sekarang
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body py-4">rd-body py-4">

                            <!-- Status Banner -->
                            <div class="bg-light-primary rounded p-5 mb-6">
                                <div class="d-flex flex-stack flex-wrap gap-2">
                                    <div class="d-flex align-items-center me-3">
                                        <div class="me-4">
                                            <i class="fas fa-history text-primary fs-1"></i>
                                        </div>
                                        <div class="flex-column">
                                            <span class="text-gray-800 fw-bold fs-6">Status Sinkronisasi Terakhir</span>
                                            <div class="text-muted fs-7 mt-1">
                                                @if (isset($syncStatus) && $syncStatus)
                                                    Mulai: {{ \Carbon\Carbon::parse($syncStatus['started_at'])->format('d-M-Y H:i:s') }}
                                                    @if ($syncStatus['finished_at'])
                                                        &nbsp;|&nbsp; Selesai: {{ \Carbon\Carbon::parse($syncStatus['finished_at'])->format('d-M-Y H:i:s') }}
                                                        &nbsp;({{ $syncStatus['duration'] }} detik)
                                                    @endif
                                                @else
                                                    Belum pernah dijalankan.
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        @if (!isset($syncStatus) || !$syncStatus)
                                            <span class="badge badge-light-warning fw-bolder fs-7 px-4 py-2">NEVER RUN</span>
                                        @elseif ($syncStatus['status'] === 'success')
                                            <span class="badge badge-light-success fw-bolder fs-7 px-4 py-2">
                                                <i class="fas fa-check me-1"></i> SUCCESS
                                            </span>
                                        @elseif ($syncStatus['status'] === 'running')
                                            <span class="badge badge-light-primary fw-bolder fs-7 px-4 py-2">
                                                <i class="fas fa-spinner fa-spin me-1"></i> RUNNING
                                            </span>
                                        @else
                                            <span class="badge badge-light-danger fw-bolder fs-7 px-4 py-2">
                                                <i class="fas fa-times me-1"></i> FAILED
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Error message -->
                            @if (isset($syncStatus) && $syncStatus && $syncStatus['status'] === 'failed' && !empty($syncStatus['error']))
                                <div class="alert bg-light-danger border border-danger d-flex flex-column p-5 mb-6">
                                    <h5 class="mb-1 text-danger fw-bold">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Pesan Error Sinkronisasi:
                                    </h5>
                                    <code class="fs-7 text-dark">{{ $syncStatus['error'] }}</code>
                                </div>
                            @endif

                            <!-- Selection Card for Tables/Modules -->
                            <div class="card border border-dashed border-gray-300 card-bordered mb-6" style="border-radius: 16px;">
                                <div class="card-header border-0 pt-5 min-h-auto">
                                    <div class="card-title flex-column">
                                        <h5 class="fw-bolder text-gray-800 fs-5 mb-1">Pilih Modul / Tabel yang Disinkronkan</h5>
                                        <span class="text-muted fs-7">Centang modul yang ingin Anda sinkronkan. Kosongkan modul jika ingin dilewati.</span>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="sync-check-all-tables" checked />
                                            <label class="form-check-label fw-bold text-gray-700 fs-7 ms-2" for="sync-check-all-tables">Pilih Semua Tabel</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-3 pb-6">
                                    <div class="row row-cols-1 row-cols-md-3 g-4">
                                        <!-- Group 1: Sekolah & UPT -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-school me-2 text-slate-400"></i>Sekolah & UPT</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="school" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="schools" data-group="school" checked id="tbl-schools" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-schools">schools (Sekolah / UPT)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="outlets" data-group="school" checked id="tbl-outlets" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-outlets">outlets (Outlet Mart)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="admin_outlets" data-group="school" checked id="tbl-admin-outlets" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-admin-outlets">admin_outlets (Admin Outlet)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Group 2: Produk & Stok -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-boxes me-2 text-slate-400"></i>Produk & Stok Mart</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="stock" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="category_items" data-group="stock" checked id="tbl-category-items" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-category-items">category_items (Kategori)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="items" data-group="stock" checked id="tbl-items" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-items">items (Data Produk)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="stock_histories" data-group="stock" checked id="tbl-stock-histories" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-stock-histories">stock_histories (Riwayat Stok)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Group 3: Akademik & Rekening -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-university me-2 text-slate-400"></i>Akademik & Bank</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="academic" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="academic_years" data-group="academic" checked id="tbl-academic-years" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-academic-years">academic_years (Tahun Ajaran)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="bill_items" data-group="academic" checked id="tbl-bill-items" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-bill-items">bill_items (Item Bayar)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="bill_types" data-group="academic" checked id="tbl-bill-types" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-bill-types">bill_types (Tipe Tagihan)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="banks" data-group="academic" checked id="tbl-banks" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-banks">banks (Rekening Bank)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="bill_type_banks" data-group="academic" checked id="tbl-bill-type-banks" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-bill-type-banks">bill_type_banks (Map Rekening)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="topup_banks" data-group="academic" checked id="tbl-topup-banks" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-topup-banks">topup_banks (Rekening Topup)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Group 4: Tarif Tagihan -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-file-invoice-dollar me-2 text-slate-400"></i>Tarif Tagihan</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="rates" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="payment_rates" data-group="rates" checked id="tbl-payment-rates" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-payment-rates">payment_rates (Tarif Pembayaran)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="payment_rate_classrooms" data-group="rates" checked id="tbl-pr-classrooms" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-pr-classrooms">payment_rate_classrooms</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="payment_rate_students" data-group="rates" checked id="tbl-pr-students" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-pr-students">payment_rate_students</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="payment_rate_items" data-group="rates" checked id="tbl-pr-items" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-pr-items">payment_rate_items</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Group 5: Siswa & Kelas -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-users me-2 text-slate-400"></i>Siswa & Kelas</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="students" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="students" data-group="students" checked id="tbl-students" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-students">students (Data Siswa)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="student_classroom_histories" data-group="students" checked id="tbl-student-histories" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-student-histories">classroom_histories</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Group 6: Tagihan & Transaksi -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-receipt me-2 text-slate-400"></i>Tagihan & Transaksi</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="transactions" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="bills" data-group="transactions" checked id="tbl-bills" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-bills">bills (Tagihan Siswa)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="transaction_proofs" data-group="transactions" checked id="tbl-tx-proofs" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-tx-proofs">transaction_proofs (Bukti Trf)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="transactions" data-group="transactions" checked id="tbl-transactions" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-transactions">transactions (Transaksi)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="transaction_details" data-group="transactions" checked id="tbl-tx-details" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-tx-details">transaction_details (Rincian)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="saldo_histories" data-group="transactions" checked id="tbl-saldo-histories" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-saldo-histories">saldo_histories (Riwayat Saldo)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="saving_histories" data-group="transactions" checked id="tbl-saving-histories" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-saving-histories">saving_histories (Tabungan)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Group 7: Kasir & Buku Kas -->
                                        <div class="col">
                                            <div class="border rounded-[12px] p-4 bg-light-body h-100" style="border: 1px dashed rgba(0,0,0,0.1) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="fw-bolder text-dark fs-7"><i class="fas fa-cash-register me-2 text-slate-400"></i>Kasir & Buku Kas</span>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input group-checkbox" type="checkbox" data-group="pos" checked />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 ps-2">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="point_of_sale_carts" data-group="pos" checked id="tbl-pos-carts" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-pos-carts">point_of_sale_carts (Keranjang)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="point_of_sale_transactions" data-group="pos" checked id="tbl-pos-tx" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-pos-tx">point_of_sale_transactions</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="point_of_sale_transaction_details" data-group="pos" checked id="tbl-pos-details" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-pos-details">point_of_sale_details</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="cash_flow_categories" data-group="pos" checked id="tbl-cf-categories" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-cf-categories">cash_flow_categories</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="cash_flows" data-group="pos" checked id="tbl-cash-flows" />
                                                        <label class="form-check-label text-gray-700 fs-7" for="tbl-cash-flows">cash_flows (Buku Kas)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Per-table report -->
                            @if (isset($syncStatus) && $syncStatus && !empty($syncStatus['report']))
                                <div class="separator separator-dashed mb-4"></div>
                                <h5 class="fw-bolder text-dark mb-4">Laporan Per Tabel</h5>
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                        <thead>
                                            <tr class="fw-bolder text-muted bg-light">
                                                <th class="min-w-200px ps-4 rounded-start">Nama Tabel</th>
                                                <th class="min-w-100px text-center">Status</th>
                                                <th class="min-w-150px text-end pe-4 rounded-end">Data Disinkronkan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($syncStatus['report'] as $table => $info)
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="text-dark fw-bolder fs-6">{{ $table }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($info['status'] === 'success')
                                                            <span class="badge badge-light-success fw-bolder fs-8">SUCCESS</span>
                                                        @elseif ($info['status'] === 'skipped')
                                                            <span class="badge badge-light-warning fw-bolder fs-8">SKIPPED</span>
                                                        @else
                                                            <span class="badge badge-light-danger fw-bolder fs-8">FAILED</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                         <span class="text-dark fw-bold fs-6">
                                                             {{ isset($info['rows_synced']) ? number_format($info['rows_synced']) . ' baris' : '-' }}
                                                         </span>
                                                         @if (isset($info['min_date']) && $info['min_date'])
                                                             <div class="text-muted fs-8 mt-1">
                                                                 <i class="far fa-calendar-alt me-1 fs-9 text-slate-400"></i>
                                                                 {{ \Carbon\Carbon::parse($info['min_date'])->format('d M Y') }} 
                                                                 s/d 
                                                                 {{ \Carbon\Carbon::parse($info['max_date'])->format('d M Y') }}
                                                             </div>
                                                         @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-10">
                                    <i class="fas fa-database text-muted fs-3x mb-4"></i>
                                    <p class="text-muted fw-bold fs-6">Belum ada laporan sinkronisasi.</p>
                                    <p class="text-muted fs-7">Klik tombol <strong>Sinkronkan Sekarang</strong> di kanan atas untuk memulai.</p>
                                </div>
                            @endif

                            <!-- Sync History List -->
                            <div class="separator separator-dashed my-8"></div>
                            <h5 class="fw-bolder text-dark mb-4">Riwayat Sinkronisasi (10 Terakhir)</h5>
                            @if (isset($syncHistory) && $syncHistory->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bolder text-muted bg-light">
                                                <th class="ps-4 rounded-start">Waktu Mulai</th>
                                                <th>Waktu Selesai</th>
                                                <th>Durasi</th>
                                                <th class="text-center">Status</th>
                                                <th class="min-w-100px text-end pe-4 rounded-end">Total Baris Sync</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($syncHistory as $log)
                                                @php
                                                    $totalRows = 0;
                                                    if (is_array($log->report)) {
                                                        foreach ($log->report as $tReport) {
                                                            $totalRows += $tReport['rows_synced'] ?? 0;
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="text-dark fw-bold fs-6">{{ $log->started_at->format('d M Y H:i:s') }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-gray-800 fs-6">{{ $log->finished_at ? $log->finished_at->format('d M Y H:i:s') : '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-gray-600 fs-7">{{ $log->duration ? $log->duration . ' detik' : '-' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($log->status === 'success')
                                                            <span class="badge badge-light-success fw-bolder fs-8">SUCCESS</span>
                                                        @elseif ($log->status === 'running')
                                                            <span class="badge badge-light-primary fw-bolder fs-8">RUNNING</span>
                                                        @else
                                                            <span class="badge badge-light-danger fw-bolder fs-8">FAILED</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <span class="text-dark fw-bolder fs-6">{{ number_format($totalRows) }} baris</span>
                                                        @if ($log->error)
                                                            <div class="text-danger fs-8 mt-1" title="{{ $log->error }}">
                                                                <i class="fas fa-exclamation-circle text-danger me-1"></i>
                                                                {{ Str::limit($log->error, 30) }}
                                                             </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <p class="text-muted fs-7">Belum ada riwayat pencatatan sinkronisasi.</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </form>
                    </div>
                    <!--end::Tab 2 Pane-->
                </div>
                <!--end::syncMainTabsContent-->

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto fetch students diff on Tab 1 initial load
            if (typeof fetchMasterDiff === 'function') {
                fetchMasterDiff('students');
            }

            // Loading state saat form sync disubmit
            var form = document.getElementById('sync-db-form');
            if (form) {
                form.addEventListener('submit', function () {
                    var btn = document.getElementById('btn-sync-submit');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sinkronisasi Berjalan...';
                    }
                });
            }

            // Check All Table logic
            var checkAll = document.getElementById('sync-check-all-tables');
            var tableCheckboxes = document.querySelectorAll('.table-checkbox');
            var groupCheckboxes = document.querySelectorAll('.group-checkbox');

            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    var isChecked = this.checked;
                    tableCheckboxes.forEach(function (cb) {
                        cb.checked = isChecked;
                    });
                    groupCheckboxes.forEach(function (cb) {
                        cb.checked = isChecked;
                    });
                });
            }

            // Group Checkbox logic
            groupCheckboxes.forEach(function (gCb) {
                gCb.addEventListener('change', function () {
                    var group = this.getAttribute('data-group');
                    var isChecked = this.checked;
                    document.querySelectorAll('.table-checkbox[data-group="' + group + '"]').forEach(function (tCb) {
                        tCb.checked = isChecked;
                    });
                    updateMasterCheckbox();
                });
            });

            // Table Checkbox logic
            tableCheckboxes.forEach(function (tCb) {
                tCb.addEventListener('change', function () {
                    var group = this.getAttribute('data-group');
                    var groupCb = document.querySelector('.group-checkbox[data-group="' + group + '"]');
                    if (groupCb) {
                        var siblings = document.querySelectorAll('.table-checkbox[data-group="' + group + '"]');
                        var allSiblingsChecked = Array.from(siblings).every(function (cb) {
                            return cb.checked;
                        });
                        var anySiblingChecked = Array.from(siblings).some(function (cb) {
                            return cb.checked;
                        });
                        groupCb.checked = allSiblingsChecked;
                        groupCb.indeterminate = anySiblingChecked && !allSiblingsChecked;
                    }
                    updateMasterCheckbox();
                });
            });

            function updateMasterCheckbox() {
                if (checkAll) {
                    var allChecked = Array.from(tableCheckboxes).every(function (cb) {
                        return cb.checked;
                    });
                    var anyChecked = Array.from(tableCheckboxes).some(function (cb) {
                        return cb.checked;
                    });
                    checkAll.checked = allChecked;
                    checkAll.indeterminate = anyChecked && !allChecked;
                }
            }
        });

        function toggleMasterPreview() {
            var card = document.getElementById('preview-master-card');
            if (card.style.display === 'none' || card.style.display === '') {
                card.style.display = 'block';
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                fetchMasterDiff('students');
            } else {
                card.style.display = 'none';
            }
        }

        var currentActiveFilter = 'ALL';

        function filterDiffTable(status, badgeEl) {
            if (badgeEl && currentActiveFilter === status) {
                status = 'ALL';
                badgeEl = null;
            }
            currentActiveFilter = status;

            document.querySelectorAll('.filter-badge').forEach(function(el) {
                el.classList.remove('border', 'border-2', 'border-dark', 'shadow-sm');
                el.style.opacity = (status === 'ALL') ? '1' : '0.4';
            });

            var resetBtn = document.getElementById('btn-reset-diff-filter');
            var filterLabel = document.getElementById('txt-active-filter-label');

            if (badgeEl && status !== 'ALL') {
                badgeEl.style.opacity = '1';
                badgeEl.classList.add('border', 'border-2', 'border-dark', 'shadow-sm');
                if (resetBtn) resetBtn.classList.remove('d-none');
                if (filterLabel) filterLabel.innerText = badgeEl.innerText.split(':')[0].replace(/^[^\s]+\s*/, '');
            } else {
                if (resetBtn) resetBtn.classList.add('d-none');
            }

            var rows = document.querySelectorAll('#tbody-diff-preview tr[data-status]');
            var visibleCount = 0;

            rows.forEach(function(row) {
                var rowStatus = row.getAttribute('data-status');
                if (status === 'ALL' || rowStatus === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            var emptyMsg = document.getElementById('tr-filter-empty-msg');
            if (visibleCount === 0 && rows.length > 0) {
                if (!emptyMsg) {
                    var tbody = document.getElementById('tbody-diff-preview');
                    tbody.insertAdjacentHTML('beforeend', '<tr id="tr-filter-empty-msg"><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-filter me-2"></i> Tidak ada data dengan status filter ini.</td></tr>');
                }
            } else if (emptyMsg) {
                emptyMsg.remove();
            }

            updateMergeButtonState();
        }

        function onSchoolFilterChange() {
            var schoolId = document.getElementById('select-filter-school').value;
            var classSelect = document.getElementById('select-filter-classroom');
            var options = classSelect.querySelectorAll('option');

            classSelect.value = '';
            options.forEach(function(opt) {
                if (!opt.value) {
                    opt.style.display = '';
                    return;
                }
                var optSchool = opt.getAttribute('data-school');
                if (!schoolId || optSchool === schoolId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });

            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }

        function fetchMasterDiff(module, btnEl) {
            if (btnEl) {
                document.querySelectorAll('.btn-mod-tab').forEach(function(b) { b.classList.remove('active'); });
                btnEl.classList.add('active');
            }
            document.getElementById('current-merge-module').value = module;

            // Toggle filter container visibility
            var filterBox = document.getElementById('billing-filter-container');
            if (filterBox) {
                if (module === 'billing_status') {
                    filterBox.classList.remove('d-none');
                } else {
                    filterBox.classList.add('d-none');
                }
            }

            // Reset active filter
            filterDiffTable('ALL', null);

            var limitSelect = document.getElementById('select-diff-limit');
            var limitVal = limitSelect ? parseInt(limitSelect.value) : 50;

            var schoolId = document.getElementById('select-filter-school') ? document.getElementById('select-filter-school').value : '';
            var classroomId = document.getElementById('select-filter-classroom') ? document.getElementById('select-filter-classroom').value : '';

            var tbody = document.getElementById('tbody-diff-preview');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Memuat analisis perbandingan module ' + module + ' (' + (limitVal >= 1000 ? 'Semua Data Master' : limitVal + ' Record') + ')...</td></tr>';

            axios.post('{{ route("admin.audit.preview-pull-master") }}', {
                module: module,
                limit: limitVal,
                school_id: schoolId,
                classroom_id: classroomId
            }).then(function(res) {
                var data = res.data;
                var summary = data.status_summary || {};

                document.getElementById('cnt-new').innerText = '🟢 Aplikasi Lama Baru: ' + (summary.new_count || 0);
                document.getElementById('cnt-update').innerText = '🟡 Butuh Update: ' + (summary.update_count || 0);
                document.getElementById('cnt-match').innerText = '🔵 100% Identik: ' + (summary.match_count || 0);
                document.getElementById('cnt-conflict').innerText = '🔴 Konflik Mapping: ' + (summary.conflict_count || 0);

                var items = data.items || [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada data ditemukan untuk module ini.</td></tr>';
                    return;
                }

                var html = '';
                items.forEach(function(item) {
                    var badgeClass = 'bg-light-info text-info';
                    var badgeLabel = '🔵 100% IDENTIK';

                    if (item.status === 'NEW_RECORD') {
                        badgeClass = 'bg-light-success text-success';
                        badgeLabel = '🟢 APLIKASI LAMA BARU';
                    } else if (item.status === 'UPDATE_REQUIRED') {
                        badgeClass = 'bg-light-warning text-warning';
                        badgeLabel = '🟡 BUTUH UPDATE';
                    } else if (item.status === 'CONFLICT') {
                        badgeClass = 'bg-light-danger text-danger';
                        badgeLabel = '🔴 KONFLIK';
                    }

                    var diffHtml = '';
                    if (item.diffs && Object.keys(item.diffs).length > 0) {
                        diffHtml = '<ul class="mb-0 ps-3 fs-8" style="color: #374151; font-weight: 500;">';
                        for (var k in item.diffs) {
                            if (typeof item.diffs[k] === 'object') {
                                diffHtml += '<li class="my-1"><code class="text-primary fw-bolder px-1 py-0.5 bg-light-primary rounded" style="font-size: 11px;">' + k + '</code>: Aplikasi Lama (<span class="fw-bolder text-dark bg-light-warning text-warning px-1.5 py-0.5 rounded border border-warning border-opacity-25">"' + (item.diffs[k].master||'-') + '"</span>) vs Lokal (<span class="fw-bolder text-gray-800 bg-light px-1.5 py-0.5 rounded border border-gray-300">"' + (item.diffs[k].local||'-') + '"</span>)</li>';
                            } else {
                                diffHtml += '<li class="my-1"><span class="text-danger fw-bold">' + item.diffs[k] + '</span></li>';
                            }
                        }
                        diffHtml += '</ul>';
                    } else {
                        diffHtml = '<span class="fw-semibold fs-8" style="color: #4b5563;">Data aplikasi lama dan lokal presisi identik</span>';
                    }

                    var isCheckable = (item.status !== 'EXACT_MATCH');
                    var checkAttr = isCheckable ? 'checked' : 'disabled';

                    html += '<tr data-status="' + item.status + '">';
                    html += '<td class="px-3"><input class="form-check-input chk-diff-item" type="checkbox" name="selected_ids[]" value="' + item.id + '" ' + checkAttr + ' onchange="updateMergeButtonState()"></td>';
                    html += '<td class="fw-bold fs-7"><code>' + (item.code_or_nis || item.id) + '</code></td>';
                    html += '<td class="fw-bolder text-dark">' + item.name + '</td>';
                    html += '<td><span class="badge ' + badgeClass + ' fw-bolder fs-8 px-2 py-1">' + badgeLabel + '</span></td>';
                    html += '<td>' + diffHtml + '</td>';
                    html += '</tr>';
                });

                tbody.innerHTML = html;
                updateMergeButtonState();
            }).catch(function(err) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Gagal memuat data diff: ' + (err.message || 'Error Server') + '</td></tr>';
            });
        }

        function toggleAllDiffCheckboxes(masterCb) {
            var items = document.querySelectorAll('.chk-diff-item:not(:disabled)');
            items.forEach(function(cb) {
                cb.checked = masterCb.checked;
            });
            updateMergeButtonState();
        }

        function updateMergeButtonState() {
            var selected = document.querySelectorAll('.chk-diff-item:checked');
            var btn = document.getElementById('btn-submit-merge');
            var txt = document.getElementById('txt-selected-count');

            if (txt) {
                txt.innerText = selected.length + ' record terverifikasi terpilih untuk di-merge';
            }

            if (btn) {
                btn.disabled = (selected.length === 0);
            }
        }
    </script>
@endsection
