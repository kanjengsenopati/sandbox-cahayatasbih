@extends('layouts.master', ['title' => 'Data Pembayaran'])
@push('css')
<style>
    :root {
        --pakrt-primary: #2563eb;
        --pakrt-success: #10b981;
        --pakrt-error: #dc2626;
        --pakrt-slate-900: #0f172a;
        --pakrt-slate-800: #1e293b;
        --pakrt-slate-700: #334155;
        --pakrt-slate-600: #475569;
        --pakrt-slate-500: #64748b;
        --pakrt-slate-400: #94a3b8;
    }

    .text-slate-900 { color: var(--pakrt-slate-900) !important; }
    .text-slate-800 { color: var(--pakrt-slate-800) !important; }
    .text-slate-700 { color: var(--pakrt-slate-700) !important; }
    .text-slate-600 { color: var(--pakrt-slate-600) !important; }
    .text-slate-500 { color: var(--pakrt-slate-500) !important; }
    .text-slate-400 { color: var(--pakrt-slate-400) !important; }
    .text-emerald-600 { color: var(--pakrt-success) !important; }
    .text-amber-600 { color: #d97706 !important; }

    .badge-success { background-color: var(--pakrt-success) !important; color: white !important; }
    .badge-danger { background-color: var(--pakrt-error) !important; color: white !important; }
    .badge-primary { background-color: var(--pakrt-primary) !important; color: white !important; }

    .card-information {
        background-color: #ffffff;
        padding: 24px;
        border-radius: 24px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.04);
        border: none;
    }

    .card-information .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .card-information .info-item:last-child {
        margin-bottom: 0;
    }

    .card-information .info-label {
        width: 140px;
        flex-shrink: 0;
        font-weight: 700;
        color: var(--pakrt-slate-500) !important;
    }

    .card-information .info-colon {
        width: 20px;
        flex-shrink: 0;
        color: var(--pakrt-slate-500) !important;
    }

    .card-information .info-value {
        flex-grow: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    @media (max-width: 576px) {
        .card-information .info-label {
            width: 100px;
        }
    }

    /* Premium Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 30px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1;
        border: 1px solid transparent;
    }

    .status-badge-active {
        background-color: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
    }

    .status-badge-inactive, .status-badge-dropped-out {
        background-color: rgba(220, 38, 38, 0.1) !important;
        color: #dc2626 !important;
        border-color: rgba(220, 38, 38, 0.2) !important;
    }

    .status-badge-graduated {
        background-color: rgba(37, 99, 235, 0.1) !important;
        color: #2563eb !important;
        border-color: rgba(37, 99, 235, 0.2) !important;
    }

    .status-badge-transferred {
        background-color: rgba(217, 119, 6, 0.1) !important;
        color: #d97706 !important;
        border-color: rgba(217, 119, 6, 0.2) !important;
    }

    .btn-custom-purple {
        background-color: #8A4FFF;
        border-color: #8A4FFF;
        color: white;
        padding: 10px 20px;
        font-size: 13px;
        border-radius: 25px;
    }

    .btn-custom-purple.btn-sm {
        padding: 6px 16px !important;
        font-size: 11px !important;
        border-radius: 20px !important;
    }

    .btn-custom-purple:hover {
        background-color: #7A3FEF;
        border-color: #7A3FEF;
        color: white;
    }

    @media (min-width: 992px) {
        .border-lg-end {
            border-right: 1px solid #eff2f5 !important;
        }
    }

    .wrapper,
    #kt_content,
    #kt_post {
        padding-right: 0 !important;
        margin-right: 0 !important;
    }

    #kt_content_container,
    #kt_content_container .col-xl-12,
    .tab-content,
    .tab-pane,
    #kt_contacts_main {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
    }
</style>

@endpush
@section('content')
<!--begin::Content-->
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Pembayaran</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('bill.index') }}">
                        <li class="text-slate-500">
                            Data Pembayaran
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        Pembayaran Siswa
                    </li>
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
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->

                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#pembayaran_tunai">Pembayaran
                                Tunai</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#pembayaran_transfer">Pembayaran Transfer</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#arsip_riwayat">Arsip Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#import_pembayaran">Import Pembayaran</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="pembayaran_tunai" role="tabpanel">
                            <!-- Pembayaran Tunai Content -->
                            <div>
                                <div class="card card-flush h-lg-100" id="kt_contacts_main">
                                    <div class="card-body pt-5">
                                        <form action="{{ route('bill.index') }}" method="GET" id="filter-form">
                                            <!-- Unit Pendidikan -->
                                            <div class="row mb-4">
                                                <label class="col-md-3 col-form-label fw-bold fs-6 required" for="school_id">
                                                    Unit Pendidikan
                                                </label>
                                                <div class="col-md-9">
                                                    <select name="school_id" class="form-select form-select-solid" id="school_id">
                                                        <option value="">Pilih Unit Pendidikan</option>
                                                        @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}" {{ request('school_id')==$school->id ? 'selected' : '' }}>
                                                            {{ $school->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Tahun Ajaran -->
                                            <div class="row mb-4">
                                                <label class="col-md-3 col-form-label fw-bold fs-6" for="academic_year_id">
                                                    Tahun Ajaran
                                                </label>
                                                <div class="col-md-9">
                                                    <select name="academic_year_id" id="academic_year_id" class="form-select form-select-solid">
                                                        <option value="">Semua Tahun Ajaran</option>
                                                        @foreach ($academicYears as $year)
                                                            @php
                                                                if (isset($student)) {
                                                                    $startYear = $year->getStartYearSafe();
                                                                    if ($startYear !== null && $student->getEntryYear() > $startYear) {
                                                                        continue;
                                                                    }
                                                                }
                                                            @endphp
                                                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                                {{ $year->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Siswa & Button -->
                                            <div class="row mb-4">
                                                <label class="col-md-3 col-form-label fw-bold fs-6 required" for="student_id">
                                                    NIS/NISN/Nama
                                                </label>
                                                <div class="col-md-9">
                                                    <select name="student_id" id="student_id" class="form-select form-select-solid">
                                                        @if(request('student_id') && isset($student))
                                                            <option value="{{ $student->id }}" selected>
                                                                {{ $student->nis ? $student->nis . ' - ' : '' }}{{ $student->name }} - {{ $student->classroom->name ?? '' }}
                                                            </option>
                                                        @else
                                                            <option value="">Pilih Siswa</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="separator mb-6"></div>
                                    <div class="d-flex justify-content-end"></div>

                                    @if ($student ?? false)
                                    @php
                                        $filteredYearId = request('academic_year_id');
                                        $activeYear = $academicYears->where('is_active', true)->first();
                                        
                                        if ($filteredYearId) {
                                            $filteredYear = $academicYears->where('id', $filteredYearId)->first();
                                            $displayYearName = $filteredYear ? $filteredYear->name : 'Semua Tahun Ajaran';
                                            
                                            $history = $student->classroomHistories->where('academic_year_id', $filteredYearId)->first();
                                            if ($history && $history->classroom) {
                                                $displayClassName = $history->classroom->name;
                                            } else {
                                                // Jika tidak ada di history, dan tahun ajaran yang difilter adalah tahun ajaran aktif,
                                                // gunakan kelas aktif siswa saat ini
                                                if ($activeYear && $filteredYearId == $activeYear->id && $student->classroom) {
                                                    $displayClassName = $student->classroom->name;
                                                } else {
                                                    $displayClassName = '-';
                                                }
                                            }
                                        } else {
                                            $displayYearName = $activeYear ? $activeYear->name : 'Semua Tahun Ajaran';
                                            $displayClassName = $student->classroom->name ?? '-';
                                        }
                                    @endphp
                                    <div class="card-body pt-3">
                                        <div class="card-information">
                                            <div class="row align-items-center g-5">
                                                <!-- Left Side: Student Info -->
                                                <div class="col-lg-6 col-12 border-lg-end pe-lg-5">
                                                    <div class="info-item">
                                                        <span class="info-label">Tahun Ajaran</span>
                                                        <span class="info-colon">:</span>
                                                        <span class="info-value">
                                                            <span class="text-slate-800 fw-bold">
                                                                {{ $displayYearName }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">NIS</span>
                                                        <span class="info-colon">:</span>
                                                        <span class="info-value">
                                                            <span class="text-slate-700 fw-semibold">{{ @$student->nis ?? '' }}</span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Nama</span>
                                                        <span class="info-colon">:</span>
                                                        <span class="info-value">
                                                            <span class="text-slate-900 fw-bold">{{ @$student->name ?? '' }}</span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Kelas</span>
                                                        <span class="info-colon">:</span>
                                                        <span class="info-value">
                                                            <span class="text-slate-700 fw-semibold">{{ $displayClassName }}</span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Status</span>
                                                        <span class="info-colon">:</span>
                                                        <span class="info-value">
                                                            @php
                                                                $statusClass = match(@$student->status) {
                                                                    'ACTIVE' => 'status-badge-active',
                                                                    'INACTIVE' => 'status-badge-inactive',
                                                                    'GRADUATED' => 'status-badge-graduated',
                                                                    'TRANSFERRED' => 'status-badge-transferred',
                                                                    'DROPPED_OUT' => 'status-badge-dropped-out',
                                                                    default => 'status-badge-active',
                                                                };
                                                            @endphp
                                                            <span class="status-badge {{ $statusClass }}">
                                                                {{ @$student->translatedStatus() ?? '' }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Right Side: Billing Cards (Total, Terbayar, Sisa Tagihan) -->
                                                <div class="col-lg-6 col-12 ps-lg-5">
                                                    @php
                                                        $totalBill = (isset($billMonth) ? $billMonth->sum('total_bill') : 0) + (isset($billOthers) ? $billOthers->sum('total_bill') : 0);
                                                        $totalPaid = (isset($billMonth) ? $billMonth->sum('total_paid') : 0) + (isset($billOthers) ? $billOthers->sum('total_paid') : 0);
                                                        $totalUnpaid = (isset($billMonth) ? $billMonth->sum('total_unpaid') : 0) + (isset($billOthers) ? $billOthers->sum('total_unpaid') : 0);
                                                    @endphp
                                                    
                                                    <!-- Row 1: Total & Terbayar -->
                                                    <div class="row g-4 mb-4">
                                                        <div class="col-6">
                                                            <div class="p-4 bg-light-primary border border-primary border-opacity-10 d-flex flex-column justify-content-between h-100" style="border-radius: 16px;">
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <span class="text-slate-500 fs-8 fw-boldest text-uppercase tracking-wider">Total Tagihan</span>
                                                                    <i class="fas fa-file-invoice-dollar text-primary fs-4"></i>
                                                                </div>
                                                                <span class="fs-4 fw-boldest text-slate-900">Rp {{ number_format($totalBill, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="p-4 bg-light-success border border-success border-opacity-10 d-flex flex-column justify-content-between h-100" style="border-radius: 16px;">
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <span class="text-slate-500 fs-8 fw-boldest text-uppercase tracking-wider">Tagihan Terbayar</span>
                                                                    <i class="fas fa-check-circle text-success fs-4"></i>
                                                                </div>
                                                                <span class="fs-4 fw-boldest text-emerald-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Row 2: Sisa Tagihan -->
                                                    <div class="row g-4">
                                                        <div class="col-12">
                                                            <div class="p-4 bg-light-danger border border-danger border-opacity-10 d-flex flex-column justify-content-between" style="border-radius: 16px;">
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <span class="text-slate-500 fs-8 fw-boldest text-uppercase tracking-wider">Sisa Tagihan</span>
                                                                    <i class="fas fa-exclamation-circle text-danger fs-3"></i>
                                                                </div>
                                                                <span class="fs-3 fw-boldest text-danger">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="separator mb-6"></div>
                                        <div class="d-flex justify-content-end"></div>
                                    </div>
                                    <div id="kt_accordion_1" class="accordion accordion-flush mx-5">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="kt_accordion_1_header_1">
                                                <button class="accordion-button fs-4 fw-boldest text-slate-800" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#kt_accordion_1_body_1"
                                                    aria-expanded="true" aria-controls="kt_accordion_1_body_1">
                                                    Fitur Kilat
                                                </button>
                                            </h2>
                                            <div id="kt_accordion_1_body_1" class="accordion-collapse collapse show"
                                                aria-labelledby="kt_accordion_1_header_1"
                                                data-bs-parent="#kt_accordion_1">
                                                <div class="accordion-body">

                                                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6 fw-boldest">
                                                        <li class="nav-item">
                                                            <a class="nav-link active text-slate-800" data-bs-toggle="tab"
                                                                href="#kt_tab_pane_4">Bulanan</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link text-slate-600" data-bs-toggle="tab"
                                                                href="#kt_tab_pane_5">Lainnya</a>
                                                        </li>
                                                        <div
                                                            class="d-flex justify-content-end align-items-center mb-3 ms-auto">
                                                            <input type="checkbox" id="select-all">
                                                            <label for="select-all" class="ms-2 mb-0">Bayar
                                                                Semua</label>
                                                            <!-- Tempatkan tombol "Bayar" di lokasi yang sesuai -->
                                                            @if (Auth::user()->can('Edit Tagihan'))
                                                            <button class="btn btn-primary modal-pay ms-2"
                                                                data-bs-toggle="modal" data-bs-target="#paymentModal"
                                                                style="min-width: 100px;">Bayar</button>
                                                            @endif
                                                        </div>
                                                    </ul>

                                                    <div class="tab-content" id="myTabContent">
                                                        <div class="tab-pane fade show active" id="kt_tab_pane_4"
                                                            role="tabpanel">
                                                            <div class="py-3">
                                                                @include('admins.bill.table.body-kilat')
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                                                            <div class="py-3">
                                                                @include('admins.bill.table.body-lainnya')
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="separator mb-6"></div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="kt_accordion_1_header_1">
                                                <button class="accordion-button fs-4 fw-boldest text-slate-800" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#accordion-tagihan-bulanan" aria-expanded="true"
                                                    aria-controls="accordion-tagihan-bulanan">
                                                    Tagihan Bulanan
                                                </button>
                                            </h2>
                                            <div id="accordion-tagihan-bulanan" class="accordion-collapse collapse show"
                                                aria-labelledby="kt_accordion_1_header_1"
                                                data-bs-parent="#kt_accordion_1">
                                                <div class="accordion-body">
                                                    <div class="table-responsive">
                                                        <table id="table-bill-monthly"
                                                            class="table align-middle table-row-dashed ">
                                                            <thead>
                                                                <tr class="text-start text-slate-500 fw-boldest fs-7 text-uppercase gs-0">
                                                                    <th style="width: 5%">No</th>
                                                                    <th class="min-w-70px">Tahun Ajaran</th>
                                                                    <th class="min-w-125px">Item Pembayaran</th>
                                                                    <th class="min-w-125px">Total Tagihan</th>
                                                                    <th class="min-w-125px">Dibayar</th>
                                                                    <th class="min-w-125px">Sisa Tagihan</th>
                                                                    <th class="text-center" style="width: 22%">Status</th>
                                                                    <th class="text-center min-w-150px">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-slate-700 fw-bold">
                                                                @foreach ($billMonth as $monthly)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ @$monthly->academicYear->name }}</td>
                                                                    <td>{{ @$monthly->name }}</td>
                                                                    <td class="text-slate-900 fw-bolder">Rp {{ number_format(@$monthly->total_bill, 0, ',', '.') }}</td>
                                                                    <td class="text-emerald-600 fw-bolder">Rp {{ number_format(@$monthly->total_paid, 0, ',', '.') }}</td>
                                                                    <td class="text-danger fw-bolder">Rp {{ number_format(@$monthly->total_unpaid, 0, ',', '.') }}</td>
                                                                    <td class="text-center">
                                                                        <span class="badge badge-{{ @$monthly->total_unpaid == 0 ? 'success' : 'danger' }} fw-bold px-3 py-1">
                                                                            {{ @$monthly->total_unpaid == 0 ? 'Lunas' : 'Belum Lunas' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <a href="{{ route('bill.summary-bill', ['bill_type_id' => $monthly->id, 'student_id' => $student->id]) }}"
                                                                            class="btn btn-custom-purple btn-sm">
                                                                            <i class="bi bi-file-text me-2"></i>
                                                                            Lihat Rincian
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="separator mb-6"></div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="kt_accordion_1_header_1">
                                                <button class="accordion-button fs-4 fw-boldest text-slate-800" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#accordion-bill-other"
                                                    aria-expanded="true" aria-controls="accordion-bill-other">
                                                    Tagihan Lainnya
                                                </button>
                                            </h2>
                                            <div id="accordion-bill-other" class="accordion-collapse collapse show"
                                                aria-labelledby="kt_accordion_1_header_1"
                                                data-bs-parent="#kt_accordion_1">
                                                <div class="accordion-body">
                                                    <div class="table-responsive">
                                                        <table id="table-bill-monthly"
                                                            class="table align-middle table-row-dashed ">
                                                            <thead>
                                                                <tr class="text-start text-slate-500 fw-boldest fs-7 text-uppercase gs-0">
                                                                    <th style="width: 5%">No</th>
                                                                    <th class="min-w-70px">Tahun Ajaran</th>
                                                                    <th class="min-w-125px">Item Pembayaran</th>
                                                                    <th class="min-w-125px">Total Tagihan</th>
                                                                    <th class="min-w-125px">Dibayar</th>
                                                                    <th class="min-w-125px">Sisa Tagihan</th>
                                                                    <th class="text-center min-w-70px" style="width: 22%">Status</th>
                                                                    <th class="text-center min-w-150px">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-slate-700 fw-bold">
                                                                @foreach ($billOthers as $other)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ @$other->academicYear->name }}</td>
                                                                    <td>{{ @$other->name }}</td>
                                                                    <td class="text-slate-900 fw-bolder">Rp {{ number_format(@$other->total_bill, 0, ',', '.') }}</td>
                                                                    <td class="text-emerald-600 fw-bolder">Rp {{ number_format(@$other->total_paid, 0, ',', '.') }}</td>
                                                                    <td class="text-danger fw-bolder">Rp {{ number_format(@$other->total_unpaid, 0, ',', '.') }}</td>
                                                                    <td class="text-center">
                                                                        <span class="badge badge-{{ @$other->total_unpaid == 0 ? 'success' : 'danger' }} fw-bold px-3 py-1">
                                                                            {{ @$other->total_unpaid == 0 ? 'Lunas' : 'Belum Lunas' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <a href="{{ route('bill.summary-bill', ['bill_type_id' => $other->id, 'student_id' => $student->id]) }}"
                                                                            class="btn btn-custom-purple btn-sm">
                                                                            <i class="bi bi-file-text me-2"></i>
                                                                            Lihat Rincian
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pembayaran_transfer" role="tabpanel">
                            <!-- Pembayaran Transfer Content -->
                            <!-- Add your content for Pembayaran Transfer here -->
                            @include('admins.bill.transfer-tab.index')
                        </div>
                        <div class="tab-pane fade" id="arsip_riwayat" role="tabpanel">
                            <!-- Arsip Riwayat Content -->
                            @include('admins.bill.transfer-tab.archive')
                        </div>
                        <div class="tab-pane fade" id="import_pembayaran" role="tabpanel">
                            <!-- Import Pembayaran Content -->
                            @include('admins.bill.import-tab.index')
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Contacts-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Contacts App- Add New Contact-->
    </div>
    <!--end::Container-->
</div>
<!-- Modal untuk semua pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
            <form action="{{ route('bill.store') }}" method="post" id="form-multi-payment">
                @csrf
                <div class="modal-header border-0 bg-light px-5 py-3">
                    <h5 class="modal-title fw-bold text-slate-800" id="paymentModalLabel">Informasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5 bg-white">
                    <!-- Prominent Total Amount Banner (Compact) -->
                    <div class="text-center bg-light-success rounded-3 p-4 mb-4 border border-success border-opacity-10">
                        <span class="text-slate-500 fs-7 fw-bold text-uppercase tracking-wider mb-1 d-block">Total Pembayaran Tagihan</span>
                        <span class="text-emerald-600 fw-boldest fs-2hx" id="total-amount">Rp 0</span>
                    </div>

                    <!-- Payment Options side-by-side -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <span class="fw-bold text-slate-700 fs-6 d-block mb-2">Tipe Pembayaran</span>
                            <div class="card shadow-none border border-gray-200" style="border-radius: 16px;">
                                <div class="card-body p-3">
                                    <select class="form-select form-select-solid" id="payment-option" required>
                                        <option value="LUNAS">Lunas</option>
                                        <option value="ANGSUR">Angsur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="fw-bold text-slate-700 fs-6 d-block mb-2">Metode Pembayaran</span>
                            <div class="card shadow-none border border-gray-200" style="border-radius: 16px;">
                                <div class="card-body p-3">
                                    <select class="form-select form-select-solid" name="payment_method" id="payment-method" required>
                                        <option value="">Pilih Metode Pembayaran</option>
                                        <option value="BALANCE">Saldo</option>
                                        <option value="CASH">Tunai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details (2 Columns - Scrollable & Compact) -->
                    <div class="mb-2">
                        <span class="fw-bold text-slate-700 fs-6 d-block mb-2">Rincian Pembayaran</span>
                        <div style="max-height: 200px; overflow-y: auto; overflow-x: hidden; padding-right: 4px;">
                            <div class="row g-2" id="payment-details">
                                <!-- Informasi pembayaran akan ditambahkan di sini -->
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="student_id" id="student-id" value="{{ @$student->id }}">
                </div>
                <div class="modal-footer border-0 bg-light px-5 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-6">Bayar Sekarang</button>
                </div>
            </form>
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
<!--end::Post-->
@endsection
@push('js')
<script>
    $(document).ready(function () {
        // on click modal-pay button to show modal and load data
        $(document).on('click', '.modal-pay', function () {
            var url = $(this).data('url');
            var modal = $('#modal-pay');

            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    modal.find('.modal-body').html(data);
                    modal.modal('show');
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Function to fetch student data based on selected school
        function fetchStudentData(selectedId = null) {
            var school_id = $('#school_id').val();
            if (school_id) {
                $.ajax({
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    delay: 300,
                    data: {
                        search: '', // Assuming you need a default search term
                        data_type: "STUDENT_BY_SCHOOL",
                        school_id: school_id
                    },
                    success: function (data) {
                        var results = $.map(data, function (item) {
                            let displayText = (item.nis ? item.nis + ' - ' : '') +
                                item.name + ' - ' +
                                (item.classroom?.name ? item.classroom.name : '');
                            
                            if (item.status === 'DROPPED_OUT') {
                                displayText += ' (KELUAR - Ada Tunggakan)';
                            }
                            
                            return {
                                text: displayText,
                                id: item.id
                            };
                        });
                        
                        // Menambahkan opsi "Pilih Siswa" di bagian atas list
                        results.unshift({ id: '', text: 'Pilih Siswa' });
                        
                        $('#student_id').empty().select2({
                            data: results,
                            cache: true,
                            templateResult: function (state) {
                                if (!state.id) {
                                    return state.text;
                                }
                                if (state.text.indexOf('(KELUAR') !== -1) {
                                    let cleanText = state.text.replace(' (KELUAR - Ada Tunggakan)', '');
                                    return $('<span>' + cleanText + ' <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 3px 6px; border-radius: 4px; font-weight: bold; display: inline-block; vertical-align: middle; line-height: 1;">KELUAR - Ada Tunggakan</span></span>');
                                }
                                return state.text;
                            },
                            templateSelection: function (state) {
                                if (!state.id) {
                                    return state.text;
                                }
                                if (state.text.indexOf('(KELUAR') !== -1) {
                                    let cleanText = state.text.replace(' (KELUAR - Ada Tunggakan)', '');
                                    return $('<span>' + cleanText + ' <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 3px 6px; border-radius: 4px; font-weight: bold; display: inline-block; vertical-align: middle; line-height: 1;">KELUAR - Ada Tunggakan</span></span>');
                                }
                                return state.text;
                            }
                        });

                        // Set nilai siswa terpilih jika ada di request
                        if (selectedId) {
                            $('#student_id').val(selectedId).trigger('change', [true]);
                        }
                    },
                    cache: true
                });
            } else {
                $('#student_id').empty();
            }
        }
        
        // Bind the change event to the fetchStudentData function
        $('#school_id').change(function() {
            fetchStudentData();
        });
        
        // Auto-submit form ketika siswa dipilih
        $('#student_id').change(function(e, isProgrammatic) {
            if (isProgrammatic) {
                return;
            }
            if ($(this).val()) {
                $(this).closest('form').submit();
            }
        });

        // Auto-submit form ketika tahun ajaran diubah dan siswa sudah dipilih
        $('#academic_year_id').change(function() {
            if ($('#student_id').val()) {
                $(this).closest('form').submit();
            }
        });
        
        // Tampilkan loading overlay saat filter-form disubmit
        $('#filter-form').on('submit', function() {
            Swal.fire({
                title: 'Mohon Tunggu',
                text: 'Sedang memuat data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
        
        // Call the function on page load
        var initialStudentId = "{{ request('student_id') }}";
        fetchStudentData(initialStudentId);
    });
</script>
<script>
    $(document).ready(() => {
            var table = $('#table-transfer').DataTable({
                ordering: true,
                sortable: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('bill.index') }}",
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
                        }
                    },
                     {
                        data: 'student.name',
                        name: 'student.name',
                        orderable: false,
                    },
                  
                    {
                        data: 'pay_amount',
                        name: 'pay_amount'
                    },
                    {
                        data: 'unique_payment',
                        name: 'unique_payment'
                    },
                    {
                        data: 'bank_recipient',
                        name: 'bank_recipient',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'proof',
                        name: 'proof',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            var archiveTable = $('#table-archive').DataTable({
                ordering: true,
                sortable: true,
                processing: true,
                serverSide: true,
                pageLength: 20,
                lengthMenu: [20, 30, 40],
                ajax: {
                    url: "{{ route('bill.index') }}",
                    data: function(d) {
                        d.tab = 'archive';
                        d.start_date = $('#archive-start-date').val();
                        d.end_date = $('#archive-end-date').val();
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
                columns: [
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'student.name',
                        name: 'student.name',
                        orderable: false,
                    },
                    {
                        data: 'pay_amount',
                        name: 'pay_amount'
                    },
                    {
                        data: 'unique_payment',
                        name: 'unique_payment'
                    },
                    {
                        data: 'bank_recipient',
                        name: 'bank_recipient',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'proof',
                        name: 'proof',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'officer',
                        name: 'officer',
                        orderable: false
                    },
                    {
                        data: 'updated_at_formatted',
                        name: 'updated_at',
                        orderable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#archive-btn-filter').click(function() {
                archiveTable.ajax.reload();
            });

            $('#archive-btn-reset').click(function() {
                $('#archive-start-date').val('');
                $('#archive-end-date').val('');
                archiveTable.ajax.reload();
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
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        axios.delete(`{{ url('bill') }}/${id}`, {
                            data: {
                                _token: '{{ csrf_token() }}'
                            }
                        })
                        .then((response) => {
                            if (response.data.code == '200') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.data.message
                                });
                                archiveTable.ajax.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.data.message
                                });
                            }
                        })
                        .catch((error) => {
                            console.error('Error deleting archive:', error);
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

            // Adjust columns on tab switch
            $('a[href="#arsip_riwayat"]').on('shown.bs.tab', function (e) {
                archiveTable.columns.adjust().draw();
            });

            // Click handler for viewing proof images in a modal
            $(document).on('click', '.view-proof-image', function() {
                var src = $(this).data('src');
                $('#imagePreviewSrc').attr('src', src);
                $('#imagePreviewModal').modal('show');
            });

        });
</script>
@endpush