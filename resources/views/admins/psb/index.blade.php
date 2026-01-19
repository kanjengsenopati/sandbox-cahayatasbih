@extends('layouts.master', ['title' => 'Manajemen PSB'])

@push('css')
    <style>
        /* PSB Module Custom Styles */
        .psb-stat-card {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .psb-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .psb-stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .psb-stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .psb-stat-card .stat-label {
            font-size: 0.85rem;
            color: #7e8299;
        }

        /* Track Type Badges */
        .badge-track-umum {
            background-color: #f1f3f5;
            color: #495057;
        }

        .badge-track-jamaah {
            background-color: #e6fcf5;
            color: #0ca678;
        }

        .badge-track-alumni {
            background-color: #f3f0ff;
            color: #7c3aed;
        }

        /* Status Badges */
        .badge-status-draft {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .badge-status-submitted {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-status-verified {
            background-color: #cce5ff;
            color: #004085;
        }

        .badge-status-accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Payment Status Badges */
        .badge-payment-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-payment-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-payment-free {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* Modern Table Styling */
        .psb-table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #7e8299;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        .psb-table tbody td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        .psb-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .psb-table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Santri Info Cell */
        .santri-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .santri-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #8a63d2 0%, #6366f1 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .santri-avatar.female {
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%);
        }

        .santri-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .santri-gender {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        /* Filter Card */
        .filter-card {
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .filter-select {
            border-radius: 8px !important;
            border-color: #e9ecef !important;
            font-size: 0.875rem;
        }

        .search-input {
            border-radius: 8px !important;
            border-color: #e9ecef !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stat-cards-row {
                gap: 1rem;
            }

            .psb-stat-card .stat-value {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!-- Page Header -->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        <i class="fas fa-user-graduate me-3 text-primary"></i>
                        Manajemen PSB (Penerimaan Santri Baru)
                    </h1>
                </div>
            </div>
        </div>

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">

                <!-- Statistics Cards -->
                <div class="row g-4 mb-6 stat-cards-row">
                    <!-- Total Pendaftar -->
                    <div class="col-6 col-lg-3">
                        <div class="card psb-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-4">
                                <div class="stat-icon" style="background-color: #ede9fe;">
                                    <i class="fas fa-users text-purple fs-4" style="color: #7c3aed;"></i>
                                </div>
                                <div>
                                    <div class="stat-value">{{ number_format($stats['total']) }}</div>
                                    <div class="stat-label">Total Pendaftar</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menunggu Verifikasi -->
                    <div class="col-6 col-lg-3">
                        <div class="card psb-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-4">
                                <div class="stat-icon" style="background-color: #fef3c7;">
                                    <i class="fas fa-clock fs-4" style="color: #d97706;"></i>
                                </div>
                                <div>
                                    <div class="stat-value">{{ number_format($stats['pending']) }}</div>
                                    <div class="stat-label">Menunggu Verifikasi</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Santri Diterima -->
                    <div class="col-6 col-lg-3">
                        <div class="card psb-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-4">
                                <div class="stat-icon" style="background-color: #d1fae5;">
                                    <i class="fas fa-check-circle fs-4" style="color: #059669;"></i>
                                </div>
                                <div>
                                    <div class="stat-value">{{ number_format($stats['accepted']) }}</div>
                                    <div class="stat-label">Santri Diterima</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ditolak -->
                    <div class="col-6 col-lg-3">
                        <div class="card psb-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-4">
                                <div class="stat-icon" style="background-color: #fee2e2;">
                                    <i class="fas fa-times-circle fs-4" style="color: #dc2626;"></i>
                                </div>
                                <div>
                                    <div class="stat-value">{{ number_format($stats['rejected']) }}</div>
                                    <div class="stat-label">Ditolak</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card filter-card mb-5">
                    <div class="card-body py-4">
                        <form action="{{ route('psb.index') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <!-- Search Input -->
                                <div class="col-12 col-lg-3">
                                    <label class="form-label text-muted small">Cari Pendaftar</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" name="search"
                                            class="form-control search-input border-start-0 ps-0"
                                            placeholder="Nama, Kode, atau No. HP Wali..." value="{{ request('search') }}">
                                    </div>
                                </div>

                                <!-- Academic Year Filter -->
                                <div class="col-6 col-lg-2">
                                    <label class="form-label text-muted small">Tahun Ajaran</label>
                                    <select name="academic_year" class="form-select filter-select">
                                        <option value="">Semua Tahun</option>
                                        @foreach ($academicYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ request('academic_year') == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- School Level Filter -->
                                <div class="col-6 col-lg-2">
                                    <label class="form-label text-muted small">Unit Pendidikan</label>
                                    <select name="school_level" class="form-select filter-select">
                                        <option value="">Semua Unit</option>
                                        <option value="SMP" {{ request('school_level') == 'SMP' ? 'selected' : '' }}>SMP
                                        </option>
                                        <option value="MA" {{ request('school_level') == 'MA' ? 'selected' : '' }}>MA
                                        </option>
                                    </select>
                                </div>

                                <!-- Track Type Filter -->
                                <div class="col-6 col-lg-2">
                                    <label class="form-label text-muted small">Jalur Pendaftaran</label>
                                    <select name="track_type" class="form-select filter-select">
                                        <option value="">Semua Jalur</option>
                                        @foreach ($trackTypes as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ request('track_type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="col-6 col-lg-2">
                                    <label class="form-label text-muted small">Status Seleksi</label>
                                    <select name="status" class="form-select filter-select">
                                        <option value="">Semua Status</option>
                                        @foreach ($statuses as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ request('status') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter Buttons -->
                                <div class="col-12 col-lg-1">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                        <a href="{{ route('psb.index') }}" class="btn btn-light">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Registration Table -->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Daftar Pendaftaran PSB</h3>
                        </div>
                        <div class="card-toolbar">
                            <span class="badge badge-light-primary fs-7">
                                {{ $registrations->total() }} Data
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed psb-table">
                                <thead>
                                    <tr>
                                        <th>Info Pendaftaran</th>
                                        <th>Calon Santri</th>
                                        <th>Jalur & Unit</th>
                                        <th>Status Seleksi</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registrations as $reg)
                                        <tr>
                                            <!-- Info Pendaftaran -->
                                            <td>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $reg->registration_code ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        {{ $reg->created_at ? $reg->created_at->format('d M Y') : '-' }}
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Calon Santri -->
                                            <td>
                                                <div class="santri-cell">
                                                    <div class="santri-avatar {{ $reg->gender === 'P' ? 'female' : '' }}">
                                                        {{ strtoupper(substr($reg->name ?? 'X', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="santri-name">{{ $reg->name ?? '-' }}</div>
                                                        <div class="santri-gender">
                                                            {{ $reg->gender === 'L' ? 'Laki-laki' : ($reg->gender === 'P' ? 'Perempuan' : '-') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Jalur & Unit -->
                                            <td>
                                                @php
                                                    $trackType = $reg->track->registration_type ?? 'UMUM';
                                                    $schoolType = $reg->track->school->type ?? '-';
                                                    $badgeClass = match ($trackType) {
                                                        'JAMAAH' => 'badge-track-jamaah',
                                                        'ALUMNI' => 'badge-track-alumni',
                                                        default => 'badge-track-umum',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} px-3 py-2 fw-semibold">
                                                    {{ $schoolType }} - {{ ucfirst(strtolower($trackType)) }}
                                                </span>
                                            </td>

                                            <!-- Status Seleksi -->
                                            <td>
                                                @php
                                                    $status = strtoupper($reg->status ?? 'DRAFT');
                                                    $statusBadge = match ($status) {
                                                        'SUBMITTED' => 'badge-status-submitted',
                                                        'VERIFIED' => 'badge-status-verified',
                                                        'ACCEPTED' => 'badge-status-accepted',
                                                        'REJECTED' => 'badge-status-rejected',
                                                        default => 'badge-status-draft',
                                                    };
                                                    $statusLabel = match ($status) {
                                                        'SUBMITTED' => 'Menunggu',
                                                        'VERIFIED' => 'Terverifikasi',
                                                        'ACCEPTED' => 'Diterima',
                                                        'REJECTED' => 'Ditolak',
                                                        default => 'Draft',
                                                    };
                                                    $statusIcon = match ($status) {
                                                        'SUBMITTED' => 'fa-hourglass-half',
                                                        'VERIFIED' => 'fa-clipboard-check',
                                                        'ACCEPTED' => 'fa-check-circle',
                                                        'REJECTED' => 'fa-times-circle',
                                                        default => 'fa-edit',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusBadge }} px-3 py-2">
                                                    <i class="fas {{ $statusIcon }} me-1"></i>
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>

                                            <!-- Aksi -->
                                            <td class="text-end">
                                                <a href="{{ route('psb.show', $reg->id) }}"
                                                    class="btn btn-sm btn-icon btn-light-primary" data-bs-toggle="tooltip"
                                                    title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-folder-open fs-2x text-muted mb-3"></i>
                                                    <span class="text-muted fw-semibold">Tidak ada data pendaftaran</span>
                                                    @if (request()->hasAny(['search', 'academic_year', 'school_level', 'track_type', 'status']))
                                                        <a href="{{ route('psb.index') }}"
                                                            class="btn btn-sm btn-light mt-3">
                                                            Reset Filter
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5">
                            <div class="text-muted small">
                                Menampilkan {{ $registrations->firstItem() ?? 0 }} - {{ $registrations->lastItem() ?? 0 }}
                                dari {{ $registrations->total() }} data
                            </div>
                            <div>
                                {{ $registrations->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
