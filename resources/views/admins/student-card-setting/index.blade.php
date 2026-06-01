@extends('layouts.master', ['title' => 'Manajemen & Cetak Kartu'])
@push('css')
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
@endpush
@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Manajemen & Cetak Kartu</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <a class="breadcrumb-item" href="{{ route('student-card-setting.index') }}">
                        <li class="breadcrumb-item text-muted">Pengaturan</li>
                    </a>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-dark">
                        <span class="text-muted fw-bolder fs-7">Manajemen & Cetak Kartu</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <style>
                text-h1, .text-h1 { display: block; font-size: 22px; font-weight: 700; color: #0f172a; } /* Slate-900 */
                text-h2, .text-h2 { display: block; font-size: 16px; font-weight: 600; color: #1e293b; } /* Slate-800 */
                text-amount, .text-amount { display: inline-block; font-size: 18px; font-weight: 700; color: #059669; } /* Emerald-600 */
                text-label, .text-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; } /* Slate-400 */
                text-body, .text-body { display: block; font-size: 14px; font-weight: 500; color: #475569; } /* Slate-600 */
                text-caption, .text-caption { display: block; font-size: 12px; font-style: italic; color: #94a3b8; } /* Slate-400 */
                
                .rounded-24px {
                    border-radius: 24px !important;
                }
                .shadow-premium {
                    box-shadow: 0 8px 30px rgba(0,0,0,0.04) !important;
                }
                .hover-scale {
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                .hover-scale:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 12px 40px rgba(0,0,0,0.06) !important;
                }
            </style>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-24px shadow-premium border-0" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-24px shadow-premium border-0" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!--begin::Tabs Navigation-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" id="cardTabs">
                <li class="nav-item">
                    <a class="nav-link active fw-bolder" data-bs-toggle="tab" href="#tab_templates">
                        <i class="fa-solid fa-folder-open me-2 text-primary"></i>Manajemen Template
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder" data-bs-toggle="tab" href="#tab_cetak">
                        <i class="fa-solid fa-print me-2 text-primary"></i>Cetak Kartu
                    </a>
                </li>
            </ul>

            <!--begin::Tab Content-->
            <div class="tab-content" id="cardTabContent">

                {{-- ===================== TAB 1: MANAJEMEN TEMPLATE ===================== --}}
                <div class="tab-pane fade show active" id="tab_templates" role="tabpanel">
                    <div class="card card-flush border-0 shadow-premium rounded-24px mb-5">
                        <div class="card-header border-0 pt-6 pb-2 d-flex justify-content-between align-items-center">
                            <text-h2 class="text-h2 mb-0">Daftar Desain Template Kartu</text-h2>
                            <button type="button" class="btn btn-sm btn-primary rounded-xl" data-bs-toggle="modal" data-bs-target="#modalCreateTemplate">
                                <i class="fa-solid fa-plus me-2"></i>Buat Template
                            </button>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                            <th>Nama Template</th>
                                            <th>Tipe Kartu</th>
                                            <th>Tahun Ajaran</th>
                                            <th>Persyaratan Tunggakan (Ujian)</th>
                                            <th>Status</th>
                                            <th class="text-end min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold">
                                        @forelse($templates as $t)
                                            <tr class="hover-scale">
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 text-hover-primary mb-1">{{ $t->name }}</span>
                                                        <span class="text-muted fs-7">ID: {{ $t->id }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($t->type === 'student_card')
                                                        <span class="badge bg-light-primary text-primary px-3 py-1 rounded" style="background-color: rgba(37, 99, 235, 0.1);">
                                                            Kartu Santri (Non-Tunai)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light-info text-info px-3 py-1 rounded" style="background-color: rgba(14, 165, 233, 0.1);">
                                                            Kartu Ujian
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $t->academicYear->name ?? '-' }}</td>
                                                <td>
                                                    @if($t->type === 'exam_card' && !empty($t->exam_bill_requirements))
                                                        @php
                                                            $reqNames = $billTypes->whereIn('id', $t->exam_bill_requirements)->map(function ($bt) {
                                                                $unit = $bt->billItem->name ?? '';
                                                                $year = $bt->academicYear->name ?? '';
                                                                $suffix = array_filter([$unit, $year]);
                                                                return $bt->name . (!empty($suffix) ? ' (' . implode(' - ', $suffix) . ')' : '');
                                                            })->toArray();
                                                        @endphp
                                                        <span class="text-slate-600 fs-7">{{ implode(', ', $reqNames) }}</span>
                                                    @elseif($t->type === 'exam_card')
                                                        <span class="text-muted fs-7 italic">Tanpa syarat (Bisa dicetak bebas)</span>
                                                    @else
                                                        <span class="text-muted fs-7">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($t->is_active)
                                                        <span class="badge bg-light-success text-emerald-600 px-3 py-1 rounded" style="background-color: rgba(16, 185, 129, 0.1);">
                                                            Aktif (Utama)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-slate-400 px-3 py-1 rounded" style="background-color: rgba(241, 245, 249, 1);">
                                                            Arsip / Inaktif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Desain Layout -->
                                                        <a href="{{ route('student-card-setting.design', $t->id) }}" class="btn btn-sm btn-icon btn-light-primary hover-scale" title="Desain Layout" style="border-radius: 8px; width: 32px; height: 32px;">
                                                            <i class="fa-solid fa-palette fs-7"></i>
                                                        </a>

                                                        <!-- Toggle Active Status -->
                                                        <form action="{{ route('student-card-setting.toggle-active', $t->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-icon btn-light-success hover-scale" title="Aktifkan/Matikan" style="border-radius: 8px; width: 32px; height: 32px;">
                                                                <i class="fa-solid fa-power-off fs-7"></i>
                                                            </button>
                                                        </form>

                                                        <!-- Edit Metadata Button -->
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-warning hover-scale btn-edit-template" title="Ubah Metadata"
                                                            data-id="{{ $t->id }}"
                                                            data-name="{{ $t->name }}"
                                                            data-type="{{ $t->type }}"
                                                            data-year="{{ $t->academic_year_id }}"
                                                            data-active="{{ $t->is_active }}"
                                                            data-reqs="{{ json_encode($t->exam_bill_requirements ?? []) }}"
                                                            data-bs-toggle="modal" data-bs-target="#modalEditTemplate"
                                                            style="border-radius: 8px; width: 32px; height: 32px;">
                                                            <i class="fa-solid fa-pen fs-7"></i>
                                                        </button>

                                                        <!-- Delete -->
                                                        <form action="{{ route('student-card-setting.destroy-template', $t->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger hover-scale" title="Hapus Template" style="border-radius: 8px; width: 32px; height: 32px;">
                                                                <i class="fa-solid fa-trash fs-7"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-10 text-muted">Belum ada template kartu terdaftar</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================== TAB 2: CETAK KARTU ===================== --}}
                <div class="tab-pane fade" id="tab_cetak" role="tabpanel">
                    <form action="{{ route('student-card-setting.print') }}" method="POST" target="_blank" id="printForm">
                        @csrf
                        
                        <div class="row g-5">
                            {{-- Filter Column --}}
                            <div class="col-lg-12">
                                <div class="card card-flush border-0 shadow-premium rounded-24px mb-5">
                                    <div class="card-body">
                                        <div class="row g-3 align-items-end">
                                            <!-- Pilihan Template -->
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Pilih Desain Template</label>
                                                <select name="template_id" id="printTemplateSelect" class="form-select rounded-xl shadow-premium border-gray-200">
                                                    @foreach($templates as $t)
                                                        <option value="{{ $t->id }}" data-type="{{ $t->type }}" {{ $t->is_active ? 'selected' : '' }}>
                                                            {{ $t->name }} ({{ $t->type === 'student_card' ? 'Santri' : 'Ujian' }}) {{ $t->is_active ? '[Aktif]' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Pilihan Layout Kertas -->
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold">Layout Kertas</label>
                                                <select name="print_layout" class="form-select rounded-xl shadow-premium border-gray-200">
                                                    <option value="pvc">PVC Card (Landscape)</option>
                                                    <option value="a4_1x1">A4 1x1 (Satuan)</option>
                                                    <option value="a4_2x2">A4 2x2 (4 Kartu)</option>
                                                    <option value="a4_2x3">A4 2x3 (6 Kartu)</option>
                                                    <option value="a4_2x4" selected>A4 2x4 (8 Kartu)</option>
                                                    <option value="a4_2x5">A4 2x5 (10 Kartu)</option>
                                                </select>
                                            </div>

                                            <!-- Filter Sekolah -->
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold">Sekolah / UPT</label>
                                                <select id="filterSchool" class="form-select rounded-xl shadow-premium border-gray-200">
                                                    <option value="">Semua Sekolah</option>
                                                    @foreach($schools as $sc)
                                                        <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Filter Kelas -->
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold">Kelas</label>
                                                <select id="filterClassroom" class="form-select rounded-xl shadow-premium border-gray-200">
                                                    <option value="">Semua Kelas</option>
                                                </select>
                                            </div>

                                            <!-- Batas Baris -->
                                            <div class="col-md-1">
                                                <label class="form-label fw-bold">Limit</label>
                                                <select id="rowLimit" class="form-select rounded-xl shadow-premium border-gray-200">
                                                    <option value="10" selected>10</option>
                                                    <option value="20">20</option>
                                                    <option value="40">40</option>
                                                </select>
                                            </div>

                                            <!-- Button Filter Trigger -->
                                            <div class="col-md-2">
                                                <button type="button" id="btnFilter" class="btn btn-primary w-100 rounded-xl">
                                                    <i class="fa-solid fa-search me-1"></i>Cari
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Student List Table Column --}}
                            <div class="col-lg-12">
                                <div class="card card-flush border-0 shadow-premium rounded-24px">
                                    <div class="card-header border-0 pt-6 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <text-h2 class="text-h2 mb-0">Daftar Santri untuk Dicetak</text-h2>
                                            <span class="badge bg-light-primary text-primary px-3 py-1 rounded" style="background-color: rgba(37, 99, 235, 0.1);">
                                                Terpilih: <span id="selectedCount" class="fw-bold">0</span> Santri
                                            </span>
                                        </div>
                                        
                                        <div class="card-toolbar d-flex align-items-center gap-3">
                                            <!-- Pilihan Filter Lunas Khusus Kartu Ujian -->
                                            <div class="form-check form-switch me-3 d-none" id="eligibleFilterContainer">
                                                <input class="form-check-input" type="checkbox" id="checkFilterEligible" />
                                                <label class="form-check-label fw-bold text-slate-700" for="checkFilterEligible">Hanya Santri Lunas</label>
                                            </div>

                                            <!-- Input Pencarian -->
                                            <div class="position-relative">
                                                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-4 text-slate-400"></i>
                                                <input type="text" id="searchStudent" class="form-control rounded-xl shadow-premium border-gray-200 ps-10 py-2 fs-7" placeholder="Cari Nama / NIS..." style="width: 220px;" />
                                            </div>

                                            <!-- Tombol Cetak Massal -->
                                            <button type="submit" class="btn btn-success rounded-xl hover-scale">
                                                <i class="fa-solid fa-print me-2"></i>Cetak Kartu Massal
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                                <thead>
                                                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                                        <th class="w-10px pe-2">
                                                            <input type="checkbox" id="checkAll" class="form-check-input" />
                                                        </th>
                                                        <th class="sortable cursor-pointer" data-column="name">Nama Lengkap <i class="fa-solid fa-sort fs-9 ms-1 text-slate-400" id="sort-icon-name"></i></th>
                                                        <th class="sortable cursor-pointer" data-column="nis">NIS <i class="fa-solid fa-sort fs-9 ms-1 text-slate-400" id="sort-icon-nis"></i></th>
                                                        <th class="sortable cursor-pointer" data-column="classroom">Kelas <i class="fa-solid fa-sort fs-9 ms-1 text-slate-400" id="sort-icon-classroom"></i></th>
                                                        <th class="sortable cursor-pointer" data-column="school">Sekolah / UPT <i class="fa-solid fa-sort fs-9 ms-1 text-slate-400" id="sort-icon-school"></i></th>
                                                        <th class="d-none" id="colHeaderBilling">Status Pembayaran</th>
                                                        <th class="sortable cursor-pointer" data-column="print_count">Riwayat Cetak <i class="fa-solid fa-sort fs-9 ms-1 text-slate-400" id="sort-icon-print_count"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentTableBody" class="text-gray-600 fw-bold">
                                                    <tr>
                                                        <td colspan="6" class="text-center py-10"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Pagination --}}
                                        <div class="d-flex flex-stack flex-wrap pt-10">
                                            <div id="paginationInfo" class="fs-6 text-gray-700">Menampilkan 0 sampai 0 dari 0 santri</div>
                                            <ul id="paginationControls" class="pagination d-flex gap-2"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
    <!--end::Post-->
</div>

{{-- ===================== MODALS DEFINITION ===================== --}}

<!-- Modal 1: Create Template -->
<div class="modal fade" id="modalCreateTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-24px border-0 shadow-premium">
            <div class="modal-header border-0 pb-0 pt-7 px-8">
                <text-h2 class="text-h2 mb-0">Buat Template Kartu Baru</text-h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('student-card-setting.store-template') }}" method="POST">
                @csrf
                <div class="modal-body px-8 py-5">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-xl border-gray-200" placeholder="Contoh: Template Ujian Ganjil 2026" required />
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tipe Kartu <span class="text-danger">*</span></label>
                        <select name="type" class="form-select rounded-xl border-gray-200 select-template-type" data-target="create">
                            @if($canSantri)
                                <option value="student_card">Kartu Santri (Non-Tunai)</option>
                            @endif
                            @if($canUjian)
                                <option value="exam_card">Kartu Ujian</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select rounded-xl border-gray-200">
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>{{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Checklist Persyaratan Tagihan (Hanya Ujian) -->
                    <div class="mb-4 d-none section-bill-reqs-create">
                        <label class="form-label fw-bold d-block">Syarat Pelunasan Tagihan (Historis)</label>
                        <small class="text-muted d-block mb-3">Santri wajib melunasi jenis tagihan berikut untuk mendapatkan lencana Lunas (Bebas tunggakan historis).</small>
                        <div style="max-height: 150px; overflow-y: auto;" class="p-3 border rounded-xl bg-light">
                            @foreach($billTypes as $bt)
                                <div class="form-check form-check-custom form-check-solid mb-2">
                                    <input class="form-check-input" type="checkbox" name="exam_bill_requirements[]" value="{{ $bt->id }}" id="req_c_{{ $bt->id }}" />
                                    <label class="form-check-label fw-bold text-slate-700" for="req_c_{{ $bt->id }}">
                                        {{ $bt->name }} ({{ $bt->billItem->name ?? 'Semua Unit' }} - {{ $bt->academicYear->name ?? 'Semua Tahun' }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2 pt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_active" checked />
                        <label class="form-check-label fw-bold text-slate-700" for="create_active">Aktifkan sebagai Utama</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-7 px-8 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-xl" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl">Buat Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Template Metadata -->
<div class="modal fade" id="modalEditTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-24px border-0 shadow-premium">
            <div class="modal-header border-0 pb-0 pt-7 px-8">
                <text-h2 class="text-h2 mb-0">Ubah Metadata Template</text-h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formEditTemplate">
                @csrf
                @method('PUT')
                <div class="modal-body px-8 py-5">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-xl border-gray-200" required />
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tipe Kartu <span class="text-danger">*</span></label>
                        <select name="type" id="edit_type" class="form-select rounded-xl border-gray-200 select-template-type" data-target="edit">
                            @if($canSantri)
                                <option value="student_card">Kartu Santri (Non-Tunai)</option>
                            @endif
                            @if($canUjian)
                                <option value="exam_card">Kartu Ujian</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tahun Ajaran</label>
                        <select name="academic_year_id" id="edit_year" class="form-select rounded-xl border-gray-200">
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Checklist Persyaratan Tagihan (Hanya Ujian) -->
                    <div class="mb-4 d-none section-bill-reqs-edit">
                        <label class="form-label fw-bold d-block">Syarat Pelunasan Tagihan (Historis)</label>
                        <small class="text-muted d-block mb-3">Santri wajib melunasi jenis tagihan berikut untuk mendapatkan lencana Lunas (Bebas tunggakan historis).</small>
                        <div style="max-height: 150px; overflow-y: auto;" class="p-3 border rounded-xl bg-light">
                            @foreach($billTypes as $bt)
                                <div class="form-check form-check-custom form-check-solid mb-2">
                                    <input class="form-check-input" type="checkbox" name="exam_bill_requirements[]" value="{{ $bt->id }}" id="req_e_{{ $bt->id }}" />
                                    <label class="form-check-label fw-bold text-slate-700" for="req_e_{{ $bt->id }}">
                                        {{ $bt->name }} ({{ $bt->billItem->name ?? 'Semua Unit' }} - {{ $bt->academicYear->name ?? 'Semua Tahun' }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2 pt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active" />
                        <label class="form-check-label fw-bold text-slate-700" for="edit_active">Aktifkan sebagai Utama</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-7 px-8 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-xl" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--end::Content-->
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Tampilkan Syarat Pembayaran berdasarkan Pilihan Tipe Kartu ──
    function toggleBillRequirement(target, type) {
        var container = document.querySelector('.section-bill-reqs-' + target);
        if (container) {
            if (type === 'exam_card') {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
                // Uncheck all billing checkboxes to prevent dirty saves
                container.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                    cb.checked = false;
                });
            }
        }
    }

    // Bind event create
    var selectCreate = document.querySelector('select[name="type"].select-template-type');
    if (selectCreate) {
        selectCreate.addEventListener('change', function() {
            toggleBillRequirement('create', this.value);
        });
        // Initial call
        toggleBillRequirement('create', selectCreate.value);
    }

    // Bind event edit
    var selectEdit = document.querySelector('select#edit_type.select-template-type');
    if (selectEdit) {
        selectEdit.addEventListener('change', function() {
            toggleBillRequirement('edit', this.value);
        });
    }

    // ── Edit Metadata Template Modal Handler ──
    document.querySelectorAll('.btn-edit-template').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var name = this.dataset.name;
            var type = this.dataset.type;
            var year = this.dataset.year;
            var active = this.dataset.active === '1' || this.dataset.active === 'true';
            var reqs = JSON.parse(this.dataset.reqs || '[]');

            // Set Form Action
            document.getElementById('formEditTemplate').action = '{{ url("student-card-setting/templates") }}/' + id;

            // Set Inputs
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_year').value = year || '';
            document.getElementById('edit_active').checked = active;

            // Toggle bill options visibility
            toggleBillRequirement('edit', type);

            // Populate Reqs Checkboxes
            var editContainer = document.querySelector('.section-bill-reqs-edit');
            if (editContainer) {
                editContainer.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                    cb.checked = reqs.includes(cb.value);
                });
            }
        });
    });

    // ── Classroom Loader ──
    function loadClassrooms(schoolId) {
        var classSelect = document.getElementById('filterClassroom');
        classSelect.innerHTML = '<option value="">Semua Kelas</option>';
        if (schoolId) {
            return fetch('{{ url("student/school") }}/' + schoolId)
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP status ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    data.forEach(function(c) {
                        classSelect.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
                    });
                })
                .catch(function(err) {
                    console.error('Gagal memuat data kelas:', err);
                });
        }
        return Promise.resolve();
    }

    // ── Cetak Tab AJAX Loader & Arrears Enforcement ──
    let currentPage = 1;
    let cetakTabInitialized = false;
    let sortColumn = 'name';
    let sortDirection = 'asc';
    let filterEligibleOnly = false;

    function fetchStudents(page = 1) {
        currentPage = page;
        var params = new URLSearchParams();
        var templateId = document.getElementById('printTemplateSelect').value;
        var schoolId = document.getElementById('filterSchool').value;
        var classroomId = document.getElementById('filterClassroom').value;
        var query = document.getElementById('searchStudent').value;
        var limit = document.getElementById('rowLimit').value;

        if (templateId) params.append('template_id', templateId);
        if (schoolId) params.append('school_id', schoolId);
        if (classroomId) params.append('classroom_id', classroomId);
        if (query) params.append('q', query);
        params.append('limit', limit);
        params.append('page', page);
        params.append('sort_by', sortColumn);
        params.append('sort_dir', sortDirection);

        // Cari tahu tipe template saat ini
        var selectedOpt = document.getElementById('printTemplateSelect').selectedOptions[0];
        var isExamCard = selectedOpt ? selectedOpt.dataset.type === 'exam_card' : false;

        var tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-10"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td></tr>';

        // Tampilkan/Sembunyikan kolom status pembayaran
        var eligibleContainer = document.getElementById('eligibleFilterContainer');
        var colHeaderBilling = document.getElementById('colHeaderBilling');
        
        if (isExamCard) {
            eligibleContainer.classList.remove('d-none');
            colHeaderBilling.classList.remove('d-none');
        } else {
            eligibleContainer.classList.add('d-none');
            colHeaderBilling.classList.add('d-none');
        }

        fetch('{{ route("student-card-setting.get-students") }}?' + params.toString())
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP status ' + r.status);
                return r.json();
            })
            .then(function(response) {
                var students = response.data || [];
                
                // Filter lunas jika checkFilterEligible aktif
                if (isExamCard && filterEligibleOnly) {
                    students = students.filter(function(s) { return s.is_eligible; });
                }

                if (students.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-10">Tidak ada data santri ditemukan</td></tr>';
                    renderPagination(response);
                    return;
                }

                var html = '';
                students.forEach(function(s) {
                    var printHistoryHtml = '';
                    if (s.print_count > 0) {
                        printHistoryHtml = '<span class="badge bg-light-success text-emerald-600 fw-bold px-3 py-1 rounded" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981;">' + s.print_count + 'x Dicetak</span>';
                        if (s.last_printed_at) {
                            var byText = s.last_printed_by ? ' oleh ' + s.last_printed_by : '';
                            printHistoryHtml += '<div class="text-slate-400 fst-italic mt-1" style="font-size: 11px;">Terakhir: ' + s.last_printed_at + byText + '</div>';
                        }
                    } else {
                        printHistoryHtml = '<span class="badge bg-light text-muted px-3 py-1 rounded" style="background-color: rgba(243, 244, 246, 1); color: #9ca3af;">Belum Pernah</span>';
                    }

                    // Kolom Pembayaran / Validasi
                    var billingColHtml = '';
                    var arrearsClass = '';
                    if (isExamCard) {
                        if (s.is_eligible) {
                            billingColHtml = '<span class="badge bg-light-success text-emerald-600 fw-bold px-3 py-1 rounded" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-circle-check text-emerald-600 me-1"></i>Lunas</span>';
                        } else {
                            billingColHtml = '<span class="badge bg-light-danger text-red-600 fw-bold px-3 py-1 rounded status-unpaid-indicator" style="background-color: rgba(220, 38, 38, 0.1); color: #dc2626;"><i class="fa-solid fa-triangle-exclamation text-red-600 me-1"></i>Tunggakan: ' + s.unpaid_bills.join(', ') + '</span>';
                            arrearsClass = 'data-has-arrears="true"';
                        }
                    }

                    html += '<tr>';
                    html += '<td class="ps-4"><input type="checkbox" class="form-check-input student-check" name="student_ids[]" value="' + s.id + '" ' + arrearsClass + ' /></td>';
                    html += '<td class="fw-bold text-slate-800">' + s.name + '</td>';
                    html += '<td>' + (s.nis || '-') + '</td>';
                    html += '<td>' + s.classroom + '</td>';
                    html += '<td>' + s.school + '</td>';
                    if (isExamCard) {
                        html += '<td>' + billingColHtml + '</td>';
                    }
                    html += '<td>' + printHistoryHtml + '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
                updateSelectedCount();
                renderPagination(response);
            })
            .catch(function(err) {
                console.error('Gagal memuat data santri:', err);
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-10"><i class="fa-solid fa-triangle-exclamation me-2"></i>Gagal memuat data. Periksa koneksi atau coba refresh halaman.<br><small class="text-muted">' + err.message + '</small></td></tr>';
            });
    }

    function renderPagination(pagination) {
        var info = document.getElementById('paginationInfo');
        var controls = document.getElementById('paginationControls');

        if (!pagination.total || pagination.total === 0) {
            info.innerHTML = 'Menampilkan 0 sampai 0 dari 0 santri';
            controls.innerHTML = '';
            return;
        }

        info.innerHTML = 'Menampilkan ' + pagination.from + ' sampai ' + pagination.to + ' dari ' + pagination.total + ' santri';

        var html = '';
        if (pagination.current_page > 1) {
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + (pagination.current_page - 1) + '"><i class="fa-solid fa-angle-left"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-sm btn-light px-3 py-1 rounded text-muted" disabled><i class="fa-solid fa-angle-left"></i></button>';
        }

        var startPage = Math.max(1, pagination.current_page - 2);
        var endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        if (startPage > 1) {
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="1">1</button>';
            if (startPage > 2) {
                html += '<span class="text-muted align-self-center px-1">...</span>';
            }
        }

        for (var p = startPage; p <= endPage; p++) {
            if (p === pagination.current_page) {
                html += '<button type="button" class="btn btn-sm btn-primary px-3 py-1 rounded fw-bold" style="background-color: #2563eb;">' + p + '</button>';
            } else {
                html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + p + '">' + p + '</button>';
            }
        }

        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                html += '<span class="text-muted align-self-center px-1">...</span>';
            }
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + pagination.last_page + '">' + pagination.last_page + '</button>';
        }

        if (pagination.current_page < pagination.last_page) {
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + (pagination.current_page + 1) + '"><i class="fa-solid fa-angle-right"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-sm btn-light px-3 py-1 rounded text-muted" disabled><i class="fa-solid fa-angle-right"></i></button>';
        }

        controls.innerHTML = html;

        controls.querySelectorAll('[data-page]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var p = parseInt(this.getAttribute('data-page'));
                fetchStudents(p);
            });
        });
    }

    var searchTimeout = null;
    document.getElementById('searchStudent').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            fetchStudents(1);
        }, 300);
    });

    document.getElementById('filterSchool').addEventListener('change', function() {
        loadClassrooms(this.value).then(function() {
            fetchStudents(1);
        });
    });

    document.getElementById('filterClassroom').addEventListener('change', function() {
        fetchStudents(1);
    });

    document.getElementById('rowLimit').addEventListener('change', function() {
        fetchStudents(1);
    });

    document.getElementById('btnFilter').addEventListener('click', function() {
        fetchStudents(1);
    });

    document.getElementById('printTemplateSelect').addEventListener('change', function() {
        fetchStudents(1);
    });

    // Toggle filter lunas
    document.getElementById('checkFilterEligible').addEventListener('change', function() {
        filterEligibleOnly = this.checked;
        fetchStudents(1);
    });

    document.querySelectorAll('th.sortable').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = this.getAttribute('data-column');
            if (sortColumn === col) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = col;
                sortDirection = 'asc';
            }
            updateSortIcons();
            fetchStudents(1);
        });
    });

    function updateSortIcons() {
        document.querySelectorAll('th.sortable i').forEach(function(icon) {
            icon.className = 'fa-solid fa-sort fs-9 ms-1 text-slate-400';
        });
        var activeIcon = document.getElementById('sort-icon-' + sortColumn);
        if (activeIcon) {
            if (sortDirection === 'asc') {
                activeIcon.className = 'fa-solid fa-sort-up fs-9 ms-1 text-primary';
            } else {
                activeIcon.className = 'fa-solid fa-sort-down fs-9 ms-1 text-primary';
            }
        }
    }

    function initCetakTab() {
        if (cetakTabInitialized) return;
        cetakTabInitialized = true;
        updateSortIcons();

        var initialSchoolId = document.getElementById('filterSchool').value;
        if (initialSchoolId) {
            loadClassrooms(initialSchoolId).then(function() {
                fetchStudents(1);
            });
        } else {
            fetchStudents(1);
        }
    }

    var cetakTabLink = document.querySelector('a[href="#tab_cetak"]');
    if (cetakTabLink) {
        cetakTabLink.addEventListener('shown.bs.tab', function() {
            initCetakTab();
        });
        cetakTabLink.addEventListener('click', function() {
            setTimeout(initCetakTab, 150);
        });
        if (cetakTabLink.classList.contains('active') || window.location.hash === '#tab_cetak') {
            initCetakTab();
        }
    }

    // Check all checkboxes
    document.getElementById('checkAll').addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.student-check').forEach(function(cb) { cb.checked = checked; });
        updateSelectedCount();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('student-check')) updateSelectedCount();
    });

    function updateSelectedCount() {
        var count = document.querySelectorAll('.student-check:checked').length;
        document.getElementById('selectedCount').textContent = count;
    }

    // ── Non-Blocking Print Interception with Confirmation Modal ──
    document.getElementById('printForm').addEventListener('submit', function(e) {
        var selectedChecks = document.querySelectorAll('.student-check:checked');
        if (selectedChecks.length === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Belum Ada Santri Terpilih',
                text: 'Pilih minimal 1 santri untuk dicetak.',
                icon: 'warning',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        // Cek apakah ada santri terpilih yang memiliki tunggakan
        var hasArrears = false;
        selectedChecks.forEach(function(cb) {
            if (cb.hasAttribute('data-has-arrears')) {
                hasArrears = true;
            }
        });

        if (hasArrears) {
            e.preventDefault(); // Tahan submit form
            
            Swal.fire({
                title: 'Konfirmasi Tunggakan Pembayaran',
                text: 'Beberapa santri yang Anda pilih masih memiliki tunggakan pembayaran. Apakah Anda yakin ingin melanjutkan cetak kartu ujian untuk santri tersebut?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981', // Emerald-600
                cancelButtonColor: '#dc2626',  // Red-600
                confirmButtonText: 'Lanjut Cetak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Bypass submit dengan mensubmit programmatically
                    var form = document.getElementById('printForm');
                    
                    // Kita buat input hidden sementara untuk memicu pengiriman asli agar tidak kena handler ini lagi
                    var bypassInput = document.createElement('input');
                    bypassInput.type = 'hidden';
                    bypassInput.name = 'bypass_arrears';
                    bypassInput.value = '1';
                    form.appendChild(bypassInput);
                    
                    form.submit();
                    
                    // Bersihkan input bypass sesudahnya
                    setTimeout(function() { bypassInput.remove(); }, 500);
                }
            });
        }
    });
});
</script>
@endpush
