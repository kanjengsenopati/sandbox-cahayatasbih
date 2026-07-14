@extends('layouts.master', ['title' => 'Audit Sistem'])

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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Audit Sistem</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.audit') }}" class="text-muted text-hover-primary">Sistem</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Audit Diagnostik
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--begin::Actions - dinamis per tab-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    {{-- Tombol Tab Diagnostik --}}
                    <a href="{{ route('admin.audit') }}"
                       id="btn-toolbar-audit"
                       class="btn btn-sm btn-primary fw-bolder"
                       style="display:none;">
                        <i class="fas fa-sync-alt me-1 fs-7"></i> Jalankan Ulang Audit
                    </a>
                    {{-- Tombol Tab Sinkronisasi --}}
                    <div id="btn-toolbar-sync" style="display:none;">
                        <button type="submit" form="sync-db-form" class="btn btn-sm btn-primary fw-bolder" id="btn-sync-submit">
                            <i class="fas fa-database me-1 fs-7"></i> Sinkronkan Sekarang
                        </button>
                    </div>
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
                @include('admins.partials.tabs-aplikasi')

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

                <!-- Tab Navigation -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-body py-0 px-0">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder px-6" id="audit-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary py-5 me-6 {{ session('success') || session('error') ? 'active' : 'active' }}"
                                   id="tab-sync-trigger"
                                   data-bs-toggle="tab"
                                   href="#tab-sync"
                                   role="tab"
                                   aria-controls="tab-sync"
                                   aria-selected="true">
                                    <i class="fas fa-database me-2 fs-6"></i>
                                    Sinkronisasi Database
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary py-5 me-6"
                                   id="tab-diagnostik-trigger"
                                   data-bs-toggle="tab"
                                   href="#tab-diagnostik"
                                   role="tab"
                                   aria-controls="tab-diagnostik"
                                   aria-selected="false">
                                    <i class="fas fa-stethoscope me-2 fs-6"></i>
                                    Diagnostik Sistem
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="audit-tabs-content">

                    {{-- ===== TAB 1: SINKRONISASI DB ===== --}}
                    <div class="tab-pane fade show active" id="tab-sync" role="tabpanel" aria-labelledby="tab-sync-trigger">
                        <form action="{{ route('admin.sync-master') }}" method="POST" id="sync-db-form">
                            @csrf
                            <div class="card card-flush shadow-sm mb-6">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title flex-column">
                                    <h3 class="card-label fw-bolder text-dark">Sinkronisasi Database Master</h3>
                                    <span class="text-muted mt-1 fw-bold fs-7">
                                        Menyinkronkan data transaksi harian (30 hari terakhir) secara inkremental dari
                                        <code>cahayatasbihdb</code> ke <code>aplikasidb</code>.
                                    </span>
                                </div>
                            </div>
                            <div class="card-body py-4">

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
                                                            &nbsp;({{ $syncStatus['duration'] }})
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
                                                            <span class="text-gray-600 fs-7">{{ $log->duration ?? '-' }}</span>
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

                    {{-- ===== TAB 2: DIAGNOSTIK SISTEM ===== --}}
                    <div class="tab-pane fade" id="tab-diagnostik" role="tabpanel" aria-labelledby="tab-diagnostik-trigger">
                        <div class="card card-flush shadow-sm mb-6">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title flex-column">
                                    <h3 class="card-label fw-bolder text-dark">Dashboard Diagnostik Sistem</h3>
                                    <span class="text-muted mt-1 fw-bold fs-7">Menjalankan naskah pemeriksaan integritas basis data &amp; penyimpanan secara real-time.</span>
                                </div>
                            </div>
                            <div class="card-body py-4">
                                <div class="row g-5">
                                    @php
                                        $descriptions = [
                                            'audit_ghost_timestamps.php' => 'Memvalidasi timestamp & anomali data pada tagihan yang terhapus.',
                                            'cleanup_ghost_bills.php' => 'Membersihkan tagihan yatim yang tidak terhubung dengan tipe tagihan aktif.',
                                            'find_ghost_bills.php' => 'Mendeteksi keberadaan tagihan tanpa relasi tipe tagihan.',
                                            'find_duplicate_bill_types.php' => 'Memindai duplikasi tipe tagihan di sistem.',
                                            'check_image.php' => 'Memeriksa keberadaan fisik berkas bukti pembayaran di storage.',
                                            'check_avatars.php' => 'Mendeteksi foto avatar santri yang terdaftar tetapi file fisiknya hilang.',
                                            'check_bills.php' => 'Pemeriksaan integritas relasi tabel tagihan secara menyeluruh.',
                                        ];
                                    @endphp

                                    @foreach ($results as $script => $data)
                                        <div class="col-12">
                                            <div class="card border border-dashed border-gray-300 card-bordered p-6 mb-2">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                    <div class="d-flex align-items-center">
                                                        <!-- Icon Indicator -->
                                                        <div class="me-4">
                                                            @if ($data['status'] === 0)
                                                                <span class="svg-icon svg-icon-2hx svg-icon-success">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                        <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                                                        <path d="M10.4343 12.4343L8.75 10.75C8.33579 10.3358 7.66421 10.3358 7.25 10.75C6.83579 11.1642 6.83579 11.8358 7.25 12.25L9.69289 14.6929C10.0834 15.0834 10.7166 15.0834 11.1071 14.6929L16.75 9.05C17.1642 8.63579 17.1642 7.96421 16.75 7.55C16.3358 7.13579 15.6642 7.13579 15.25 7.55L10.4343 12.4343Z" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            @else
                                                                <span class="svg-icon svg-icon-2hx svg-icon-danger">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                        <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                                                        <rect x="11" y="14" width="2" height="2" rx="1" fill="currentColor"></rect>
                                                                        <rect x="11" y="7" width="2" height="5" rx="1" fill="currentColor"></rect>
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <!-- Script info -->
                                                        <div>
                                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                                <span class="fs-6 fw-bold text-dark me-2">{{ $script }}</span>
                                                                @if ($data['status'] === 0)
                                                                    <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1">CLEAN (0)</span>
                                                                @elseif ($data['status'] === -1)
                                                                    <span class="badge badge-light-warning fw-bolder fs-8 px-2 py-1">MISSING (-1)</span>
                                                                @else
                                                                    <span class="badge badge-light-danger fw-bolder fs-8 px-2 py-1">WARNING ({{ $data['status'] }})</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ $descriptions[$script] ?? 'Pemeriksaan diagnostik kustom.' }}</div>
                                                        </div>
                                                    </div>
                                                    <!-- Action Toggle -->
                                                    <div>
                                                        <button class="btn btn-sm btn-light btn-active-light-primary fw-bolder"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapse-{{ Str::slug($script) }}">
                                                            <i class="fas fa-terminal me-1"></i> Lihat Log Output
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Expandable output logs -->
                                                <div class="collapse mt-4" id="collapse-{{ Str::slug($script) }}">
                                                    <div class="rounded bg-gray-100 p-5 font-monospace text-dark overflow-auto fs-7" style="max-height: 350px; white-space: pre-wrap;">
                                                        @if (empty($data['output']))
                                                            <span class="text-muted fst-italic">Naskah tidak menghasilkan log output apapun.</span>
                                                        @else
                                                            {!! e($data['output']) !!}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- end::Tab Content --}}

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            var tabSync        = document.getElementById('tab-sync-trigger');
            var tabDiagnostik  = document.getElementById('tab-diagnostik-trigger');
            var btnSync        = document.getElementById('btn-toolbar-sync');
            var btnAudit       = document.getElementById('btn-toolbar-audit');

            function showSyncBtn()  { btnSync.style.display = 'block'; btnAudit.style.display = 'none'; }
            function showAuditBtn() { btnAudit.style.display = 'block'; btnSync.style.display = 'none'; }

            // Default: Tab Sync aktif
            showSyncBtn();

            tabSync.addEventListener('shown.bs.tab', showSyncBtn);
            tabDiagnostik.addEventListener('shown.bs.tab', showAuditBtn);

            // Jika redirect dari sync (session flash), tetap di tab sync
            @if (session('success') || session('error'))
                tabSync.click();
            @endif

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
    </script>
@endsection
