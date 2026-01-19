@extends('layouts.master', ['title' => 'Detail Pendaftaran'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Pendaftaran #{{ $registration->registration_code }}</h1>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('registrations.index') }}" class="btn btn-sm btn-light">Kembali</a>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            
            <!-- Status & Overview -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-body pt-9 pb-0">
                    <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-2">
                                        <h2 class="text-gray-900 fs-2 fw-bolder me-1">{{ $registration->name }}</h2>
                                        <span class="badge badge-light-success fw-bolder size-sm ms-2 px-4 py-1">{{ $registration->status }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
                                        <span class="d-flex align-items-center text-gray-400 me-5 mb-2">
                                            <i class="fas fa-school me-1"></i> {{ $registration->track->school->name ?? '-' }}
                                        </span>
                                        <span class="d-flex align-items-center text-gray-400 me-5 mb-2">
                                            <i class="fas fa-route me-1"></i> {{ $registration->registration_type }}
                                        </span>
                                        <span class="d-flex align-items-center text-gray-400 mb-2">
                                            <i class="fas fa-money-bill me-1"></i> {{ $registration->payment_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biodata Siswa -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header cursor-pointer">
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">Biodata Siswa</h3>
                    </div>
                </div>
                <div class="card-body p-9">
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Nama Lengkap</label>
                        <div class="col-lg-8"><span class="fw-bolder fs-6 text-gray-800">{{ $registration->name }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">NISN / NIK</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->nisn }} / {{ $registration->nik }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">TTL</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->birth_place }}, {{ $registration->birth_date }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Jenis Kelamin</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Kontak Siswa</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800 d-block">{{ $registration->student_phone }}</span>
                            <span class="fw-bold fs-6 text-gray-800">{{ $registration->student_email }}</span>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Address -->
             <div class="card mb-5 mb-xl-10">
                <div class="card-header cursor-pointer">
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">Alamat</h3>
                    </div>
                </div>
                <div class="card-body p-9">
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Alamat Jalan</label>
                        <div class="col-lg-8"><span class="fw-bolder fs-6 text-gray-800">{{ $registration->address_street }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">RT / RW</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->rt }} / {{ $registration->rw }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Desa / Kelurahan</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->village }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Kecamatan</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->district }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Kota / Kab</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->city }} - {{ $registration->postal_code }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Parents -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header cursor-pointer">
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">Data Orang Tua</h3>
                    </div>
                </div>
                <div class="card-body p-9">
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">No. KK</label>
                        <div class="col-lg-8"><span class="fw-bolder fs-6 text-gray-800">{{ $registration->kk_number }}</span></div>
                    </div>
                    <div class="separator separator-dashed my-5"></div>
                    <h5 class="fw-bold text-gray-800 mb-5">Ayah</h5>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Nama Ayah</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->father_name }} ({{ $registration->father_status }})</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">NIK Ayah</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->father_nik }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Pekerjaan</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->father_job }}</span></div>
                    </div>
                    
                    <div class="separator separator-dashed my-5"></div>
                    <h5 class="fw-bold text-gray-800 mb-5">Ibu</h5>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Nama Ibu</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->mother_name }} ({{ $registration->mother_status }})</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">NIK Ibu</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->mother_nik }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Pekerjaan</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->mother_job }}</span></div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Kontak Ortu</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->parent_phone }}</span></div>
                    </div>
                </div>
            </div>

            <!-- MDTI & Extras -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header cursor-pointer">
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">MDTI & Data Lainnya</h3>
                    </div>
                </div>
                <div class="card-body p-9">
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Anggota MDTI</label>
                        <div class="col-lg-8">
                            <span class="badge {{ $registration->is_mdti_member ? 'badge-success' : 'badge-light' }}">
                                {{ $registration->is_mdti_member ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    </div>
                    @if($registration->is_mdti_member)
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Cabang / Group</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->mdti_branch }} / {{ $registration->mdti_group }}</span></div>
                    </div>
                    @endif
                    <div class="separator separator-dashed my-5"></div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Sekolah Asal</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800 d-block">{{ $registration->origin_school }}</span>
                            <span class="text-muted">{{ $registration->origin_school_address }}</span>
                        </div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Riwayat Penyakit</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->medical_history ?? '-' }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Prestasi</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->achievements ?? '-' }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Bantuan Pmrnth.</label>
                        <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800">{{ $registration->gov_assistance ?? '-' }}</span></div>
                    </div>
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Hobi / Cita-cita / Motivasi</label>
                        <div class="col-lg-8">
                            <div class="fw-bold fs-6 text-gray-800">{{ $registration->hobby }}</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ $registration->ambition }}</div>
                            <div class="text-gray-600">{{ $registration->motivation }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Proof -->
            @if($registration->payment_proof)
            <div class="card mb-5 mb-xl-10">
                <div class="card-header cursor-pointer">
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">Bukti Pembayaran</h3>
                    </div>
                </div>
                <div class="card-body p-9">
                    <div class="symbol symbol-150px symbol-lg-200px mb-5">
                        <!-- Adjust path accordingly, assume 'storage/' link -->
                        <img src="{{ asset('storage/' . $registration->payment_proof) }}" alt="Bukti Pembayaran" class="img-fluid rounded" />
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ asset('storage/' . $registration->payment_proof) }}" target="_blank" class="btn btn-sm btn-primary">Lihat Ukuran Penuh</a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
