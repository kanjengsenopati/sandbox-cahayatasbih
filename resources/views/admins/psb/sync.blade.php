@extends('layouts.master', ['title' => 'Sinkronisasi PSB Database Terpisah'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    <i class="fas fa-sync-alt text-primary fs-2 me-2"></i> Sinkronisasi PSB (Database Terpisah)
                </h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('psb.index') }}" class="text-muted text-hover-primary">PSB</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-dark">Pull Data & Integrasi</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            
            {{-- Alert Notifikasi --}}
            @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-6">
                <i class="fas fa-check-circle fs-2 text-success me-4"></i>
                <div class="d-flex flex-column">
                    <h5 class="mb-1 text-success fw-bold">Berhasil!</h5>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-6">
                <i class="fas fa-exclamation-triangle fs-2 text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <h5 class="mb-1 text-danger fw-bold">Perhatian!</h5>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            {{-- Status Koneksi Database PSB --}}
            <div class="card mb-6 shadow-sm">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center me-3 mb-2">
                            <div class="symbol symbol-50px me-4">
                                <span class="symbol-label {{ $connection['status'] ? 'bg-light-success text-success' : 'bg-light-danger text-danger' }}">
                                    <i class="fas fa-database fs-2"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="text-gray-900 fw-bolder mb-1">Status Koneksi Database PSB</h4>
                                <div class="text-muted fs-7">
                                    Target: <code>{{ config('database.connections.psb_mysql.database') }}</code> pada host <code>{{ config('database.connections.psb_mysql.host') }}</code>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-2">
                            @if($connection['status'])
                                <span class="badge badge-success px-4 py-3 fs-7">
                                    <i class="fas fa-check me-1"></i> Terhubung
                                </span>
                                <span class="badge badge-light-primary px-3 py-2 fs-7">
                                    Siap Ditarik: <strong>{{ $connection['unsynced_count'] ?? 0 }}</strong>
                                </span>
                            @else
                                <span class="badge badge-danger px-4 py-3 fs-7">
                                    <i class="fas fa-times me-1"></i> Terputus
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Calon Santri Siap Ditarik dari PSB --}}
            <div class="card mb-8 shadow-sm">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 m-0">
                            <i class="fas fa-inbox text-primary me-2"></i> Calon Santri Diterima di PSB (Belum Ditarik ke SIM)
                        </h3>
                    </div>
                    <div class="card-toolbar">
                        @if($connection['status'] && count($pendingCandidates) > 0)
                        <form action="{{ route('psb.sync.pull') }}" method="POST" onsubmit="return confirm('Tarik seluruh data calon santri yang diterima dari database PSB ke antrean SIM?')">
                            @csrf
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fas fa-cloud-download-alt me-2"></i> Tarik Semua Siswa Diterima ({{ count($pendingCandidates) }})
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="card-body py-4">
                    @if(!$connection['status'])
                        <div class="text-center py-10">
                            <i class="fas fa-database text-muted fs-1 mb-3"></i>
                            <h5 class="text-gray-600">Database PSB belum terhubung</h5>
                            <p class="text-muted fs-7">Pastikan variabel <code>PSB_DB_DATABASE</code> di file <code>.env</code> sudah sesuai.</p>
                        </div>
                    @elseif(count($pendingCandidates) == 0)
                        <div class="text-center py-10">
                            <i class="fas fa-check-double text-success fs-1 mb-3"></i>
                            <h5 class="text-gray-800 fw-bold">Semua Data Sudah Sinkron!</h5>
                            <p class="text-muted fs-7">Tidak ada calon santri berstatus DITERIMA yang menunggu untuk ditarik dari PSB.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                <thead>
                                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                        <th>Kode Reg</th>
                                        <th>Nama Calon Santri</th>
                                        <th>Status Orang Tua</th>
                                        <th>Kontak Wali</th>
                                        <th>Asal Sekolah</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingCandidates as $cand)
                                    <tr>
                                        <td><span class="badge badge-light fw-bold font-monospace">{{ $cand->registration_code }}</span></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bolder">{{ $cand->name }}</span>
                                                <span class="text-muted fs-7">NISN: {{ $cand->nisn ?: '-' }} | {{ $cand->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($cand->is_mdti_member)
                                                <span class="badge badge-light-success fw-bolder">
                                                    <i class="fas fa-star text-success me-1"></i> JAMAAH
                                                </span>
                                                <div class="text-muted fs-8 mt-1">Cabang: {{ $cand->mdti_branch ?: '-' }}</div>
                                            @else
                                                <span class="badge badge-light-secondary fw-bold">UMUM</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-gray-700 fw-bold">{{ $cand->father_name ?: $cand->mother_name }}</div>
                                            <div class="text-muted fs-7"><i class="fab fa-whatsapp text-success me-1"></i> {{ $cand->parent_phone }}</div>
                                        </td>
                                        <td><span class="text-gray-700 fs-7">{{ $cand->origin_school ?: '-' }}</span></td>
                                        <td class="text-end">
                                            <form action="{{ route('psb.sync.pull') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="selected_ids[]" value="{{ $cand->id }}">
                                                <button type="submit" class="btn btn-sm btn-light-primary fw-bold">
                                                    <i class="fas fa-download me-1"></i> Tarik
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card Antrean di SIM (Hasil Tarik Data) --}}
            <div class="card shadow-sm">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bolder text-gray-900 m-0">
                            <i class="fas fa-list-check text-success me-2"></i> Antrean Verifikasi PPDB SIM & Konversi Santri
                        </h3>
                    </div>
                </div>

                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                                <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                    <th>Kode Registrasi</th>
                                    <th>Nama Siswa</th>
                                    <th>Status Orang Tua</th>
                                    <th>Status PPDB</th>
                                    <th>Wali Murid</th>
                                    <th class="text-end">Aksi Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($syncedRegistrations as $reg)
                                <tr>
                                    <td><span class="badge badge-light fw-bold font-monospace">{{ $reg->registration_code }}</span></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold">{{ $reg->name }}</span>
                                            <span class="text-muted fs-7">{{ $reg->city ?: '-' }} | {{ $reg->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($reg->is_mdti_member)
                                            <span class="badge badge-light-success fw-bolder">
                                                <i class="fas fa-star text-success me-1"></i> JAMAAH
                                            </span>
                                            <div class="text-muted fs-8 mt-1">Cabang: {{ $reg->mdti_branch ?: '-' }}</div>
                                        @else
                                            <span class="badge badge-light-secondary fw-bold">UMUM</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg->status == 'ENROLLED')
                                            <span class="badge badge-success fw-bold"><i class="fas fa-user-check me-1"></i> Santri Aktif</span>
                                        @elseif($reg->status == 'ACCEPTED')
                                            <span class="badge badge-primary fw-bold">Diterima (Antrean)</span>
                                        @else
                                            <span class="badge badge-light">{{ $reg->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-bold">{{ $reg->father_name ?: $reg->mother_name }}</span>
                                        <div class="text-muted fs-7">{{ $reg->parent_phone }}</div>
                                    </td>
                                    <td class="text-end">
                                        @if($reg->status != 'ENROLLED')
                                        <button type="button" class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalPromote{{ $reg->id }}">
                                            <i class="fas fa-user-plus me-1"></i> Terima & Buat Santri
                                        </button>

                                        {{-- Modal Konversi Santri --}}
                                        <div class="modal fade" id="modalPromote{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('psb.sync.promote', $reg->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Resmikan Calon Santri Aktif</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <div class="mb-4">
                                                                <label class="form-label text-muted fs-7">Nama Santri:</label>
                                                                <div class="fs-6 fw-bolder text-gray-900">{{ $reg->name }}</div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-label fw-bold">Pilih Unit Sekolah:</label>
                                                                <select name="school_id" class="form-select form-select-solid">
                                                                    <option value="">-- Pilih Sekolah --</option>
                                                                    @foreach($schools as $sch)
                                                                        <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-label fw-bold">Pilih Kelas (Opsional):</label>
                                                                <select name="classroom_id" class="form-select form-select-solid">
                                                                    <option value="">-- Atur Nanti --</option>
                                                                    @foreach($classrooms as $cls)
                                                                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="alert alert-light-info fs-7 p-3">
                                                                Sistem akan otomatis membuat akun login Wali Murid di <code>users</code> dengan nomor telepon <strong>{{ $reg->parent_phone }}</strong> dan data Santri di <code>students</code>.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success fw-bold">Konfirmasi & Simpan Santri</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-muted fs-7"><i class="fas fa-check text-success me-1"></i> Terdaftar</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-muted">Belum ada data pendaftar di antrean SIM.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        {{ $syncedRegistrations->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
