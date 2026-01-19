@extends('layouts.master', ['title' => 'Detail Pendaftaran PSB'])

@push('css')
    <style>
        /* PSB Detail Page Styles */
        .psb-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            color: white;
            border: none;
        }

        .psb-header-card .registration-code {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Data Cards */
        .data-card {
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: box-shadow 0.2s ease;
        }

        .data-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .data-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f3f5;
            padding: 1rem 1.25rem;
        }

        .data-card .card-header h5 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1f2937;
        }

        .data-card .card-body {
            padding: 1.25rem;
        }

        /* Data Row */
        .data-row {
            display: flex;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            width: 40%;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .data-value {
            width: 60%;
            font-weight: 500;
            color: #1f2937;
            font-size: 0.875rem;
        }

        /* Status Card (Right Column) */
        .status-card {
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .status-card .big-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
        }

        .big-status-badge.draft {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        .big-status-badge.submitted {
            background-color: #fef3c7;
            color: #d97706;
        }

        .big-status-badge.verified {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .big-status-badge.accepted {
            background-color: #d1fae5;
            color: #059669;
        }

        .big-status-badge.rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }

        /* Timeline */
        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 8px 0;
        }

        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #8a63d2;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .timeline-dot.secondary {
            background: #d1d5db;
        }

        .timeline-content {
            flex-grow: 1;
        }

        .timeline-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .timeline-text {
            font-size: 0.875rem;
            color: #374151;
        }

        /* KTA Verification Box */
        .kta-verification-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 1.25rem;
        }

        .kta-verification-box.pending {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #f59e0b;
        }

        .kta-image-preview {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .kta-image-preview:hover {
            transform: scale(1.02);
        }

        /* Alumni Verified Box */
        .alumni-verified-box {
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border: 2px solid #8b5cf6;
            border-radius: 12px;
            padding: 1.25rem;
        }

        /* Action Buttons */
        .btn-accept {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            padding: 12px 32px;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-reject {
            border: 2px solid #ef4444;
            color: #ef4444;
            background: transparent;
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .btn-reject:hover {
            background: #ef4444;
            color: white;
        }

        /* WhatsApp Button */
        .btn-whatsapp {
            background: #25d366;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .btn-whatsapp:hover {
            background: #1da851;
            color: white;
        }

        /* Accordion Style Data Sections */
        .accordion-data .accordion-button {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.95rem;
            color: #1f2937;
            border-radius: 10px !important;
            padding: 1rem 1.25rem;
        }

        .accordion-data .accordion-button:not(.collapsed) {
            background: #f1f5f9;
            color: #1e40af;
        }

        .accordion-data .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-data .accordion-body {
            padding: 1.25rem;
        }

        /* Responsive */
        @media (max-width: 992px) {

            .data-label,
            .data-value {
                width: 50%;
            }
        }

        @media (max-width: 768px) {
            .data-row {
                flex-direction: column;
                gap: 4px;
            }

            .data-label,
            .data-value {
                width: 100%;
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
                    <a href="{{ route('psb.index') }}" class="btn btn-sm btn-icon btn-light me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        Detail Pendaftaran PSB
                    </h1>
                </div>
            </div>
        </div>

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-5" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Header Card with Santri Info -->
                <div class="card psb-header-card mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                            <div class="d-flex align-items-center gap-4">
                                <!-- Avatar -->
                                <div class="symbol symbol-70px">
                                    <div class="symbol-label fs-1 fw-bold {{ $registration->gender === 'P' ? 'bg-light-danger text-danger' : 'bg-light-primary text-primary' }}"
                                        style="border-radius: 16px;">
                                        {{ strtoupper(substr($registration->name ?? 'X', 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <h2 class="mb-1 text-white fw-bold">{{ $registration->name ?? '-' }}</h2>
                                    <div class="d-flex flex-wrap gap-3 align-items-center">
                                        <span class="registration-code">
                                            <i class="fas fa-hashtag me-1"></i>
                                            {{ $registration->registration_code ?? 'N/A' }}
                                        </span>
                                        <span class="d-flex align-items-center text-white-50 small">
                                            <i class="fas fa-school me-1"></i>
                                            {{ $registration->track->school->name ?? '-' }}
                                        </span>
                                        <span class="d-flex align-items-center text-white-50 small">
                                            <i class="fas fa-route me-1"></i>
                                            Jalur {{ ucfirst(strtolower($registration->track->registration_type ?? '-')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content: 2 Column Layout -->
                <div class="row g-5">

                    <!-- LEFT COLUMN: Data Informasi -->
                    <div class="col-lg-8">

                        <!-- Accordion Data Sections -->
                        <div class="accordion accordion-data" id="santriDataAccordion">

                            <!-- Biodata Santri -->
                            <div class="accordion-item data-card mb-4">
                                <h2 class="accordion-header" id="headingBiodata">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseBiodata" aria-expanded="true">
                                        <i class="fas fa-user-graduate me-3 text-primary"></i>
                                        Biodata Santri
                                    </button>
                                </h2>
                                <div id="collapseBiodata" class="accordion-collapse collapse show"
                                    aria-labelledby="headingBiodata">
                                    <div class="accordion-body">
                                        <div class="data-row">
                                            <span class="data-label">Nama Lengkap</span>
                                            <span class="data-value">{{ $registration->name ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">NISN</span>
                                            <span class="data-value">{{ $registration->nisn ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">NIK</span>
                                            <span class="data-value">{{ $registration->nik ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Tempat, Tanggal Lahir</span>
                                            <span class="data-value">
                                                {{ $registration->birth_place ?? '-' }},
                                                {{ $registration->birth_date ? \Carbon\Carbon::parse($registration->birth_date)->format('d F Y') : '-' }}
                                            </span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Jenis Kelamin</span>
                                            <span class="data-value">
                                                @if ($registration->gender === 'L')
                                                    <i class="fas fa-mars text-primary me-1"></i> Laki-laki
                                                @elseif($registration->gender === 'P')
                                                    <i class="fas fa-venus text-danger me-1"></i> Perempuan
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Asal Sekolah</span>
                                            <span class="data-value">
                                                {{ $registration->origin_school ?? '-' }}
                                                @if ($registration->origin_school_address)
                                                    <br><small
                                                        class="text-muted">{{ $registration->origin_school_address }}</small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Wali / Orang Tua -->
                            <div class="accordion-item data-card mb-4">
                                <h2 class="accordion-header" id="headingWali">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseWali" aria-expanded="true">
                                        <i class="fas fa-users me-3 text-success"></i>
                                        Data Wali / Orang Tua
                                    </button>
                                </h2>
                                <div id="collapseWali" class="accordion-collapse collapse show"
                                    aria-labelledby="headingWali">
                                    <div class="accordion-body">
                                        <h6 class="fw-bold text-gray-700 mb-3">
                                            <i class="fas fa-male me-2"></i>Ayah
                                        </h6>
                                        <div class="data-row">
                                            <span class="data-label">Nama Ayah</span>
                                            <span class="data-value">{{ $registration->father_name ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">NIK Ayah</span>
                                            <span class="data-value">{{ $registration->father_nik ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Status</span>
                                            <span class="data-value">{{ $registration->father_status ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Pekerjaan</span>
                                            <span class="data-value">{{ $registration->father_job ?? '-' }}</span>
                                        </div>

                                        <div class="separator my-4"></div>

                                        <h6 class="fw-bold text-gray-700 mb-3">
                                            <i class="fas fa-female me-2"></i>Ibu
                                        </h6>
                                        <div class="data-row">
                                            <span class="data-label">Nama Ibu</span>
                                            <span class="data-value">{{ $registration->mother_name ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">NIK Ibu</span>
                                            <span class="data-value">{{ $registration->mother_nik ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Status</span>
                                            <span class="data-value">{{ $registration->mother_status ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Pekerjaan</span>
                                            <span class="data-value">{{ $registration->mother_job ?? '-' }}</span>
                                        </div>

                                        <div class="separator my-4"></div>

                                        <div class="data-row">
                                            <span class="data-label">No. WhatsApp Wali</span>
                                            <span class="data-value">
                                                {{ $registration->parent_phone ?? '-' }}
                                                @if ($registration->parent_phone)
                                                    @php
                                                        $phone = preg_replace(
                                                            '/[^0-9]/',
                                                            '',
                                                            $registration->parent_phone,
                                                        );
                                                        if (substr($phone, 0, 1) === '0') {
                                                            $phone = '62' . substr($phone, 1);
                                                        }
                                                    @endphp
                                                    <a href="https://wa.me/{{ $phone }}" target="_blank"
                                                        class="btn btn-whatsapp btn-sm ms-2">
                                                        <i class="fab fa-whatsapp me-1"></i> Chat
                                                    </a>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">No. Kartu Keluarga</span>
                                            <span class="data-value">{{ $registration->kk_number ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="accordion-item data-card mb-4">
                                <h2 class="accordion-header" id="headingAlamat">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseAlamat" aria-expanded="false">
                                        <i class="fas fa-map-marker-alt me-3 text-danger"></i>
                                        Alamat Lengkap
                                    </button>
                                </h2>
                                <div id="collapseAlamat" class="accordion-collapse collapse"
                                    aria-labelledby="headingAlamat">
                                    <div class="accordion-body">
                                        <div class="data-row">
                                            <span class="data-label">Alamat Jalan</span>
                                            <span class="data-value">{{ $registration->address_street ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">RT / RW</span>
                                            <span class="data-value">RT {{ $registration->rt ?? '-' }} / RW
                                                {{ $registration->rw ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Desa / Kelurahan</span>
                                            <span class="data-value">{{ $registration->village ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Kecamatan</span>
                                            <span class="data-value">{{ $registration->district ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Kota / Kabupaten</span>
                                            <span class="data-value">{{ $registration->city ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Kode Pos</span>
                                            <span class="data-value">{{ $registration->postal_code ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Tambahan -->
                            <div class="accordion-item data-card mb-4">
                                <h2 class="accordion-header" id="headingTambahan">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTambahan" aria-expanded="false">
                                        <i class="fas fa-info-circle me-3 text-info"></i>
                                        Data Tambahan
                                    </button>
                                </h2>
                                <div id="collapseTambahan" class="accordion-collapse collapse"
                                    aria-labelledby="headingTambahan">
                                    <div class="accordion-body">
                                        <div class="data-row">
                                            <span class="data-label">Prestasi</span>
                                            <span class="data-value">{{ $registration->achievements ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Riwayat Penyakit</span>
                                            <span class="data-value">{{ $registration->medical_history ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Bantuan Pemerintah</span>
                                            <span class="data-value">{{ $registration->gov_assistance ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Hobi</span>
                                            <span class="data-value">{{ $registration->hobby ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Cita-cita</span>
                                            <span class="data-value">{{ $registration->ambition ?? '-' }}</span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Motivasi Masuk Pondok</span>
                                            <span class="data-value">{{ $registration->motivation ?? '-' }}</span>
                                        </div>
                                        @if ($registration->is_mdti_member)
                                            <div class="separator my-4"></div>
                                            <h6 class="fw-bold text-gray-700 mb-3">
                                                <i class="fas fa-mosque me-2 text-success"></i>Data Jamaah MDTI
                                            </h6>
                                            <div class="data-row">
                                                <span class="data-label">Cabang MDTI</span>
                                                <span class="data-value">{{ $registration->mdti_branch ?? '-' }}</span>
                                            </div>
                                            <div class="data-row">
                                                <span class="data-label">Kelompok</span>
                                                <span class="data-value">{{ $registration->mdti_group ?? '-' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Bukti Pembayaran -->
                            @if ($registration->payment_proof)
                                <div class="accordion-item data-card mb-4">
                                    <h2 class="accordion-header" id="headingPayment">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapsePayment"
                                            aria-expanded="false">
                                            <i class="fas fa-receipt me-3 text-warning"></i>
                                            Bukti Pembayaran
                                        </button>
                                    </h2>
                                    <div id="collapsePayment" class="accordion-collapse collapse"
                                        aria-labelledby="headingPayment">
                                        <div class="accordion-body text-center">
                                            <img src="{{ asset('storage/' . $registration->payment_proof) }}"
                                                alt="Bukti Pembayaran" class="img-fluid rounded mb-3"
                                                style="max-height: 300px; cursor: pointer;"
                                                onclick="window.open(this.src, '_blank')">
                                            <div>
                                                <a href="{{ asset('storage/' . $registration->payment_proof) }}"
                                                    target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> Buka Ukuran Penuh
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Verification Cockpit -->
                    <div class="col-lg-4">

                        <!-- Status Card -->
                        <div class="card status-card mb-5">
                            <div class="card-header border-0 py-4">
                                <h5 class="card-title m-0 fw-bold">Status Pendaftaran</h5>
                            </div>
                            <div class="card-body pt-0">
                                <!-- Big Status Badge -->
                                @php
                                    $status = strtoupper($registration->status ?? 'DRAFT');
                                    $statusClass = match ($status) {
                                        'SUBMITTED' => 'submitted',
                                        'VERIFIED' => 'verified',
                                        'ACCEPTED' => 'accepted',
                                        'REJECTED' => 'rejected',
                                        default => 'draft',
                                    };
                                    $statusLabel = match ($status) {
                                        'SUBMITTED' => 'Menunggu Verifikasi',
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
                                <div class="text-center mb-5">
                                    <span class="big-status-badge {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }}"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <!-- Timeline -->
                                <div class="timeline-wrapper">
                                    <div class="timeline-item">
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-date">
                                                {{ $registration->created_at ? $registration->created_at->format('d M Y, H:i') : '-' }}
                                            </div>
                                            <div class="timeline-text">Pendaftaran dibuat</div>
                                        </div>
                                    </div>
                                    @if (in_array($status, ['SUBMITTED', 'VERIFIED', 'ACCEPTED', 'REJECTED']))
                                        <div class="timeline-item">
                                            <div class="timeline-dot {{ $status === 'SUBMITTED' ? '' : 'secondary' }}">
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-date">
                                                    {{ $registration->updated_at ? $registration->updated_at->format('d M Y, H:i') : '-' }}
                                                </div>
                                                <div class="timeline-text">Data disubmit oleh wali</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if (in_array($status, ['ACCEPTED', 'REJECTED']))
                                        <div class="timeline-item">
                                            <div class="timeline-dot"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-date">
                                                    {{ $registration->updated_at ? $registration->updated_at->format('d M Y, H:i') : '-' }}
                                                </div>
                                                <div class="timeline-text">
                                                    {{ $status === 'ACCEPTED' ? 'Diterima oleh Admin' : 'Ditolak oleh Admin' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($registration->admin_note)
                                    <div class="alert {{ $status === 'REJECTED' ? 'alert-danger' : 'alert-info' }} mt-4">
                                        <strong>Catatan Admin:</strong><br>
                                        {{ $registration->admin_note }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Special Verification Box: JAMAAH -->
                        @if ($registration->track && $registration->track->registration_type === 'JAMAAH')
                            <div class="card mb-5">
                                <div class="card-header border-0 py-4">
                                    <h5 class="card-title m-0 fw-bold">
                                        <i class="fas fa-id-card me-2 text-success"></i>
                                        Verifikasi KTA MDTI
                                    </h5>
                                </div>
                                <div class="card-body pt-0">
                                    @if ($ktaData && $ktaData['kta_image_path'])
                                        <!-- KTA Image Preview -->
                                        <div class="text-center mb-4">
                                            <img src="{{ asset('storage/' . $ktaData['kta_image_path']) }}"
                                                alt="Bukti KTA MDTI" class="kta-image-preview"
                                                onclick="window.open(this.src, '_blank')">
                                        </div>

                                        <!-- Verification Status -->
                                        @if ($ktaData['status'] === 'VERIFIED')
                                            <div class="kta-verification-box">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle bg-success p-2">
                                                        <i class="fas fa-check text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-success">KTA Valid</div>
                                                        <small class="text-muted">Sudah diverifikasi</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="kta-verification-box pending">
                                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle bg-warning p-2">
                                                            <i class="fas fa-clock text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-warning">Belum Verifikasi</div>
                                                            <small class="text-muted">KTA perlu dicek</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <form action="{{ route('psb.verify-kta', $registration->user_id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fas fa-check me-2"></i>
                                                        Verifikasi KTA Sekarang
                                                    </button>
                                                </form>
                                            </div>
                                        @endif

                                        @if ($ktaData['member_branch'] || $ktaData['member_group'])
                                            <div class="mt-4">
                                                <div class="data-row">
                                                    <span class="data-label">Cabang</span>
                                                    <span class="data-value">{{ $ktaData['member_branch'] ?? '-' }}</span>
                                                </div>
                                                <div class="data-row">
                                                    <span class="data-label">Kelompok</span>
                                                    <span class="data-value">{{ $ktaData['member_group'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-image text-muted fs-2x mb-3 d-block"></i>
                                            <div class="text-muted">Belum ada bukti KTA yang diupload</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Special Box: ALUMNI -->
                        @if ($isAlumniTrack)
                            <div class="card mb-5">
                                <div class="card-body">
                                    <div class="alumni-verified-box">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle p-3"
                                                style="background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);">
                                                <i class="fas fa-user-graduate text-white fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold" style="color: #6d28d9;">Verified Alumni</div>
                                                <small class="text-muted">Data diambil dari Database Alumni
                                                    (Auto-Verified)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Decision Actions -->
                        @if (!in_array($registration->status, ['ACCEPTED', 'REJECTED']))
                            <div class="card">
                                <div class="card-header border-0 py-4">
                                    <h5 class="card-title m-0 fw-bold">
                                        <i class="fas fa-gavel me-2 text-primary"></i>
                                        Keputusan Seleksi
                                    </h5>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="d-grid gap-3">
                                        <!-- Accept Button -->
                                        <button type="button" class="btn btn-accept btn-lg" data-bs-toggle="modal"
                                            data-bs-target="#acceptModal">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Terima Santri
                                        </button>

                                        <!-- Reject Button -->
                                        <button type="button" class="btn btn-reject" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal">
                                            <i class="fas fa-times-circle me-2"></i>
                                            Tolak Pendaftaran
                                        </button>
                                    </div>

                                    <div class="separator my-4"></div>

                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Keputusan ini bersifat final
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Accept Modal -->
    <div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('psb.update-status', $registration->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="ACCEPTED">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="acceptModalLabel">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Terima Calon Santri
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success-light bg-light-success mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-success me-3 fs-4"></i>
                                <div>
                                    <strong>{{ $registration->name }}</strong> akan diterima sebagai santri baru.
                                    Data akan otomatis dimigrasikan ke sistem.
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold required">Pilih Kelas</label>
                            <select name="classroom_id" class="form-select" id="classroomSelect" required>
                                <option value="">-- Klik di sini untuk memuat kelas --</option>
                            </select>
                            <div class="form-text">
                                Kelas akan dimuat dari:
                                <strong>{{ $registration->track->school->name ?? 'Tidak ada sekolah' }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (Opsional)</label>
                            <textarea name="admin_note" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Ya, Terima Santri
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('psb.update-status', $registration->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="REJECTED">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="rejectModalLabel">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            Tolak Pendaftaran
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger-light bg-light-danger mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle text-danger me-3 fs-4"></i>
                                <div>
                                    Anda akan menolak pendaftaran <strong>{{ $registration->name }}</strong>.
                                    Tindakan ini tidak dapat dibatalkan.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold required">Alasan Penolakan</label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Tuliskan alasan penolakan..." required></textarea>
                            <div class="form-text">Alasan ini akan ditampilkan kepada pendaftar</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> Ya, Tolak Pendaftaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const acceptModal = document.getElementById('acceptModal');
            const classroomSelect = document.getElementById('classroomSelect');
            const schoolId = '{{ $registration->track->school_id ?? '' }}';

            // Function to load classrooms
            function loadClassrooms() {
                if (!schoolId) {
                    classroomSelect.innerHTML = '<option value="">-- Tidak ada data sekolah --</option>';
                    console.warn('School ID tidak tersedia');
                    return;
                }

                // Show loading state
                classroomSelect.innerHTML = '<option value="">Memuat kelas...</option>';
                classroomSelect.disabled = true;

                fetch('{{ url('/psb/classrooms') }}/' + schoolId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        classroomSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                        classroomSelect.disabled = false;

                        if (data.length === 0) {
                            classroomSelect.innerHTML =
                                '<option value="">-- Tidak ada kelas tersedia --</option>';
                            return;
                        }

                        data.forEach(classroom => {
                            const option = document.createElement('option');
                            option.value = classroom.id;
                            option.textContent = classroom.name;
                            classroomSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading classrooms:', error);
                        classroomSelect.innerHTML = '<option value="">-- Gagal memuat kelas --</option>';
                        classroomSelect.disabled = false;
                    });
            }

            // Load classrooms when modal is opened
            if (acceptModal) {
                acceptModal.addEventListener('shown.bs.modal', function() {
                    if (classroomSelect.options.length <= 1) {
                        loadClassrooms();
                    }
                });
            }
        });
    </script>
@endpush
