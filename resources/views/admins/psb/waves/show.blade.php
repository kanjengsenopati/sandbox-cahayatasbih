@extends('layouts.master', ['title' => 'Detail Gelombang PSB'])

@section('styles')
    <style>
        /* Wave Header */
        .wave-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            color: white;
            border: none;
        }

        .wave-header-card .wave-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Track Cards */
        .track-card {
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .track-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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

        /* Installment Row */
        .installment-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .installment-row:last-child {
            margin-bottom: 0;
        }

        .installment-row select,
        .installment-row input {
            font-size: 0.875rem;
        }

        /* Total Box */
        .total-box {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 10px;
            padding: 16px;
        }

        /* Modal */
        .modal-xl-custom {
            max-width: 700px;
        }
    </style>
@endsection

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content" x-data="trackManager()">
        <!-- Page Header -->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <a href="{{ route('psb.index') }}" class="btn btn-sm btn-icon btn-light me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        Detail Gelombang PSB
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-5" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Wave Header Card -->
                <div class="card wave-header-card mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                            <div>
                                <h2 class="text-white fw-bold mb-2">{{ $wave->name }}</h2>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span class="wave-badge">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $wave->start_date ? \Carbon\Carbon::parse($wave->start_date)->format('d M Y') : '-' }}
                                        -
                                        {{ $wave->end_date ? \Carbon\Carbon::parse($wave->end_date)->format('d M Y') : '-' }}
                                    </span>
                                    <span class="wave-badge">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        {{ $wave->academicYear->name ?? '-' }}
                                    </span>
                                    @if ($wave->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-pause-circle me-1"></i> Tidak Aktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Track List Section -->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">
                                <i class="fas fa-route me-2 text-primary"></i>
                                Daftar Jalur Pendaftaran
                            </h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" @click="openModal()">
                                <i class="fas fa-plus me-2"></i>
                                Tambah Jalur Baru
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th>Nama Jalur</th>
                                        <th>Unit</th>
                                        <th>Tipe</th>
                                        <th>Kuota</th>
                                        <th>Total Biaya</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-bold text-gray-600">
                                    @forelse($wave->tracks as $track)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $track->name ?? 'Reguler' }}</div>
                                            </td>
                                            <td>{{ $track->school->name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match ($track->registration_type) {
                                                        'JAMAAH' => 'badge-track-jamaah',
                                                        'ALUMNI' => 'badge-track-alumni',
                                                        default => 'badge-track-umum',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} px-3 py-2">
                                                    {{ ucfirst(strtolower($track->registration_type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $track->registrations_count ?? 0 }}</span>
                                                <span class="text-muted">/ {{ $track->quota }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    Rp {{ number_format($track->total_installment ?? 0, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($track->is_open)
                                                    <span class="badge badge-light-success">Dibuka</span>
                                                @else
                                                    <span class="badge badge-light-danger">Ditutup</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-icon btn-light-primary me-1"
                                                    @click="editTrack('{{ $track->id }}')" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form
                                                    action="{{ route('psb.waves.tracks.destroy', [$wave->id, $track->id]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus jalur ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light-danger"
                                                        title="Hapus"
                                                        {{ $track->registrations_count > 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-folder-open fs-2x text-muted mb-3"></i>
                                                    <span class="text-muted fw-semibold">Belum ada jalur pendaftaran</span>
                                                    <button type="button" class="btn btn-sm btn-primary mt-3"
                                                        @click="openModal()">
                                                        <i class="fas fa-plus me-1"></i>
                                                        Tambah Jalur Pertama
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Track Modal -->
        <div class="modal fade" id="trackModal" tabindex="-1" x-ref="trackModal" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl-custom">
                <div class="modal-content">
                    <form :action="formAction" method="POST" id="trackForm">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i class="fas fa-route me-2 text-primary"></i>
                                <span x-text="mode === 'create' ? 'Tambah Jalur Baru' : 'Edit Jalur'"></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Basic Info -->
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold required">Nama Jalur</label>
                                    <input type="text" name="name" class="form-control" x-model="form.name"
                                        placeholder="Contoh: Reguler, Jamaah Khusus" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold required">Unit Pendidikan</label>
                                    <select name="school_id" class="form-select" x-model="form.school_id" required>
                                        <option value="">-- Pilih Unit --</option>
                                        @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}
                                                ({{ $school->type }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold required">Tipe Pendaftaran</label>
                                    <select name="registration_type" class="form-select" x-model="form.registration_type"
                                        required>
                                        <option value="UMUM">Umum (Non-Jamaah)</option>
                                        <option value="JAMAAH">Jamaah MDTI</option>
                                        <option value="ALUMNI">Alumni</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold required">Kuota</label>
                                    <input type="number" name="quota" class="form-control"
                                        x-model.number="form.quota" min="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Biaya Pendaftaran</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="registration_fee" class="form-control"
                                            x-model.number="form.registration_fee" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="separator my-5"></div>

                            <!-- Installment Plan Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                            Skema Cicilan Daftar Ulang
                                        </h6>
                                        <small class="text-muted">Atur jadwal pembayaran biaya masuk</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light-primary"
                                        @click="addInstallment()">
                                        <i class="fas fa-plus me-1"></i>
                                        Tambah Cicilan
                                    </button>
                                </div>

                                <!-- Installment Rows -->
                                <template x-if="form.installments.length === 0">
                                    <div class="text-center py-5 bg-light rounded">
                                        <i class="fas fa-receipt text-muted fs-2x mb-3 d-block"></i>
                                        <span class="text-muted">Belum ada skema cicilan</span>
                                    </div>
                                </template>

                                <template x-for="(item, index) in form.installments" :key="index">
                                    <div class="installment-row">
                                        <div class="flex-grow-1">
                                            <label class="form-label small text-muted mb-1">Bulan</label>
                                            <select :name="'installments[' + index + '][month]'"
                                                class="form-select form-select-sm" x-model.number="item.month">
                                                <option value="1">Januari</option>
                                                <option value="2">Februari</option>
                                                <option value="3">Maret</option>
                                                <option value="4">April</option>
                                                <option value="5">Mei</option>
                                                <option value="6">Juni</option>
                                                <option value="7">Juli</option>
                                                <option value="8">Agustus</option>
                                                <option value="9">September</option>
                                                <option value="10">Oktober</option>
                                                <option value="11">November</option>
                                                <option value="12">Desember</option>
                                            </select>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label small text-muted mb-1">Tahun</label>
                                            <select :name="'installments[' + index + '][year_offset]'"
                                                class="form-select form-select-sm" x-model.number="item.year_offset">
                                                <option value="0">Tahun Masuk</option>
                                                <option value="1">Tahun Kedua</option>
                                                <option value="2">Tahun Ketiga</option>
                                            </select>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-label small text-muted mb-1">Nominal</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" :name="'installments[' + index + '][amount]'"
                                                    class="form-control" x-model.number="item.amount" min="0"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                        <div class="pt-4">
                                            <button type="button" class="btn btn-sm btn-icon btn-light-danger"
                                                @click="removeInstallment(index)" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Total Display -->
                                <template x-if="form.installments.length > 0">
                                    <div class="total-box mt-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Total Biaya Daftar Ulang</span>
                                            <span class="fs-3 fw-bold" x-text="formatRupiah(totalAmount)"></span>
                                        </div>
                                        <small class="opacity-75"
                                            x-text="form.installments.length + ' kali pembayaran'"></small>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                <span x-text="mode === 'create' ? 'Simpan Jalur' : 'Update Jalur'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function trackManager() {
            return {
                modal: null,
                mode: 'create',
                trackId: null,
                waveId: '{{ $wave->id }}',

                form: {
                    name: '',
                    school_id: '',
                    registration_type: 'UMUM',
                    quota: 100,
                    registration_fee: 0,
                    installments: [{
                        month: 7,
                        year_offset: 0,
                        amount: 0
                    }]
                },

                init() {
                    this.modal = new bootstrap.Modal(this.$refs.trackModal);
                },

                get formAction() {
                    if (this.mode === 'create') {
                        return '{{ route('psb.waves.tracks.store', $wave->id) }}';
                    }
                    return '/psb/waves/' + this.waveId + '/tracks/' + this.trackId;
                },

                get totalAmount() {
                    return this.form.installments.reduce((sum, item) => sum + Number(item.amount || 0), 0);
                },

                formatRupiah(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                },

                addInstallment() {
                    this.form.installments.push({
                        month: 1,
                        year_offset: 0,
                        amount: 0
                    });
                },

                removeInstallment(index) {
                    this.form.installments.splice(index, 1);
                },

                resetForm() {
                    this.form = {
                        name: '',
                        school_id: '',
                        registration_type: 'UMUM',
                        quota: 100,
                        registration_fee: 0,
                        installments: [{
                            month: 7,
                            year_offset: 0,
                            amount: 0
                        }]
                    };
                },

                openModal(track = null) {
                    if (track) {
                        this.mode = 'edit';
                        this.trackId = track.id;
                        this.form.name = track.name || '';
                        this.form.school_id = track.school_id || '';
                        this.form.registration_type = track.registration_type || 'UMUM';
                        this.form.quota = track.quota || 0;
                        this.form.registration_fee = track.registration_fee || 0;
                        this.form.installments = track.installment_plan && track.installment_plan.length > 0 ?
                            track.installment_plan :
                            [{
                                month: 7,
                                year_offset: 0,
                                amount: 0
                            }];
                    } else {
                        this.mode = 'create';
                        this.trackId = null;
                        this.resetForm();
                    }
                    this.modal.show();
                },

                async editTrack(trackId) {
                    try {
                        const response = await fetch('/psb/waves/' + this.waveId + '/tracks/' + trackId);
                        const track = await response.json();
                        this.openModal(track);
                    } catch (error) {
                        console.error('Error fetching track:', error);
                        alert('Gagal memuat data jalur');
                    }
                }
            }
        }
    </script>
@endsection
