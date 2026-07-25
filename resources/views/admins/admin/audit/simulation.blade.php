@extends('layouts.master', ['title' => 'Simulasi Draft Audit Saldo'])

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        <i class="fas fa-vial text-primary me-2 fs-2"></i> Mode Simulasi (Draft Mode) Audit Saldo
                    </h1>
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="#" class="text-muted text-hover-primary">Audit dan Sinkron</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Simulasi Draft Saldo</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form action="{{ route('admin.audit.run-simulation') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-bolder" onclick="return confirm('Jalankan simulasi audit saldo? Prosedur ini aman dan TIDAK mengubah data saldo santri.')">
                            <i class="fas fa-sync-alt me-1 text-dark"></i> Jalankan Simulasi Draft
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-fluid">

                <!-- Audit Navigation Tabs -->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6 fw-bolder border-bottom-0">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary pe-4" href="{{ route('admin.audit.sync') }}">
                            <i class="fas fa-database me-2"></i> Sinkronisasi Master
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary px-4" href="{{ route('admin.audit.diagnostics') }}">
                            <i class="fas fa-chart-line me-2"></i> Diagnostik Komparasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary px-4 active" href="{{ route('admin.audit.simulation') }}">
                            <i class="fas fa-vial me-2 text-warning"></i> Simulasi Draft Saldo (Draft Mode)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary ps-4" href="{{ route('admin.audit.duplicates') }}">
                            <i class="fas fa-users-slash me-2"></i> Siswa Duplikat
                        </a>
                    </li>
                </ul>

                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center p-4 mb-5">
                        <i class="fas fa-check-circle fs-2 text-success me-3"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center p-4 mb-5">
                        <i class="fas fa-exclamation-triangle fs-2 text-danger me-3"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <!-- Notification Banner -->
                <div class="card bg-light-primary border-primary border border-dashed mb-5">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-primary fs-2x me-3"></i>
                            <div>
                                <h5 class="fw-bold mb-1 text-primary">Mode Simulasi Aman (Sandbox Mode)</h5>
                                <p class="text-muted mb-0 fs-7">
                                    Hasil rekalkulasi di bawah adalah **Draft Pratinjau In-Memory**. Tidak ada saldo santri di database live yang diubah sampai Anda menekan tombol <strong>Terapkan Perbaikan</strong> secara manual per santri.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metrics Cards -->
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-dashed bgi-no-repeat bgi-size-contain bgi-position-x-end h-100 p-6 bg-white">
                            <div class="text-muted fw-bold fs-7">Total Ter-Simulasi</div>
                            <div class="text-dark fw-bolder fs-2x my-2">{{ number_format($stats['total']) }}</div>
                            <span class="text-gray-400 fs-8">Santri aktif diproses</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-dashed bgi-no-repeat bgi-size-contain bgi-position-x-end h-100 p-6 bg-white border-warning">
                            <div class="text-warning fw-bold fs-7">Selisih Terdeteksi</div>
                            <div class="text-warning fw-bolder fs-2x my-2">{{ number_format($stats['mismatches']) }}</div>
                            <span class="text-gray-400 fs-8">Siswa dengan diff != 0</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-dashed bgi-no-repeat bgi-size-contain bgi-position-x-end h-100 p-6 bg-white border-danger">
                            <div class="text-danger fw-bold fs-7">Saldo Negatif</div>
                            <div class="text-danger fw-bolder fs-2x my-2">{{ number_format($stats['negatives']) }}</div>
                            <span class="text-gray-400 fs-8">Potensi missing topup</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-dashed bgi-no-repeat bgi-size-contain bgi-position-x-end h-100 p-6 bg-white border-success">
                            <div class="text-success fw-bold fs-7">Perbaikan Diterapkan</div>
                            <div class="text-success fw-bolder fs-2x my-2">{{ number_format($stats['applied']) }}</div>
                            <span class="text-gray-400 fs-8">Telah diverifikasi & applied</span>
                        </div>
                    </div>
                </div>

                <!-- Filter & Data Table -->
                <div class="card card-custom card-sticky">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <form action="{{ route('admin.audit.simulation') }}" method="GET" class="d-flex align-items-center position-relative my-1">
                                <i class="fas fa-search position-absolute ms-4 text-gray-500"></i>
                                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-solid w-250px ps-12" placeholder="Cari nama / NIS santri..." />
                            </form>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th>Santri</th>
                                        <th>Kelas</th>
                                        <th>Saldo Live (DB)</th>
                                        <th>Saldo Rekalkulasi (Draft)</th>
                                        <th>Selisih (Diff)</th>
                                        <th>Diagnosa & Catatan</th>
                                        <th>Status Draft</th>
                                        <th class="text-end">Aksi Perbaikan</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-bold text-gray-600">
                                    @forelse($simulations as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 text-hover-primary fw-bolder">{{ $item->student_name }}</span>
                                                    <span class="text-muted fs-7">NIS: {{ $item->student_nis ?: '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-info">{{ $item->classroom_name ?: '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bolder text-dark">Rp {{ number_format($item->current_saldo, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bolder text-primary">Rp {{ number_format($item->simulated_saldo, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                @if($item->saldo_diff > 0)
                                                    <span class="badge badge-light-success">+Rp {{ number_format($item->saldo_diff, 0, ',', '.') }}</span>
                                                @elseif($item->saldo_diff < 0)
                                                    <span class="badge badge-light-danger">-Rp {{ number_format(abs($item->saldo_diff), 0, ',', '.') }}</span>
                                                @else
                                                    <span class="badge badge-light-secondary">Rp 0</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->issue_type === 'NEGATIVE_BALANCE')
                                                    <span class="badge badge-danger mb-1"><i class="fas fa-exclamation-triangle me-1"></i> SALDO NEGATIF</span>
                                                @elseif($item->issue_type === 'MISMATCH')
                                                    <span class="badge badge-warning mb-1"><i class="fas fa-exclamation-circle me-1"></i> SELISIH DETECTED</span>
                                                @else
                                                    <span class="badge badge-success mb-1"><i class="fas fa-check-circle me-1"></i> KONSISTEN</span>
                                                @endif
                                                <div class="fs-8 text-gray-500">{{ $item->issue_description }}</div>
                                            </td>
                                            <td>
                                                @if($item->status === 'APPLIED')
                                                    <span class="badge badge-success"><i class="fas fa-check me-1"></i> APPLIED</span>
                                                    <div class="fs-8 text-gray-400 mt-1">{{ \Carbon\Carbon::parse($item->applied_at)->format('d/m/Y H:i') }}</div>
                                                @else
                                                    <span class="badge badge-light-warning"><i class="fas fa-clock me-1"></i> DRAFT</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($item->status === 'APPLIED')
                                                    <form action="{{ route('admin.audit.rollback-simulation') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="student_id" value="{{ $item->student_id }}">
                                                        <button type="submit" class="btn btn-sm btn-light-warning fw-bold" onclick="return confirm('Kembalikan (rollback) saldo {{ $item->student_name }} ke kondisi snapshot sebelum perbaikan?')">
                                                            <i class="fas fa-undo me-1"></i> Rollback
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.audit.apply-simulation') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="student_id" value="{{ $item->student_id }}">
                                                        <button type="submit" class="btn btn-sm btn-primary fw-bold" onclick="return confirm('Terapkan perbaikan saldo untuk {{ $item->student_name }}? Snapshot backup saldo awal akan dibuat secara otomatis.')">
                                                            <i class="fas fa-check me-1"></i> Terapkan Fix
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                Belum ada data simulasi draft. Klik tombol <strong>Jalankan Simulasi Draft</strong> untuk memulai analisis.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                {{ $simulations->links() }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--end::Post-->
    </div>
@endsection
