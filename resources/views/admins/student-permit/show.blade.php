@extends('layouts.master', ['title' => 'Detail Perizinan Santri'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Perizinan Santri</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student-permit.index') }}" class="text-muted text-hover-primary">Perizinan Santri</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Detail Izin</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                        <i class="fa fa-check-circle fa-2x text-success"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-dark">Berhasil</h4>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <span class="svg-icon svg-icon-2hx svg-icon-danger me-4">
                        <i class="fa fa-exclamation-triangle fa-2x text-danger"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-dark">Gagal</h4>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!--begin::Layout-->
            <div class="d-flex flex-column flex-lg-row">
                <!--begin::Content-->
                <div class="flex-lg-row-fluid me-lg-15">
                    <!--begin::Card-->
                    <div class="card card-flush mb-6 mb-xl-9">
                        <div class="card-header pt-9">
                            <div class="card-title flex-column">
                                <span class="badge badge-lg @if($permit->status === 'pending') badge-warning @elseif($permit->status === 'approved' || $permit->status === 'out') badge-primary @elseif($permit->status === 'returned') badge-success @else badge-danger @endif mb-3 uppercase font-weight-bold">
                                    {{ strtoupper($permit->status) }}
                                </span>
                                <h2>Pengajuan Izin: {{ ucwords(str_replace('_', ' ', $permit->permit_type)) }}</h2>
                            </div>
                        </div>
                        
                        <div class="card-body pt-5">
                            <div class="d-flex flex-wrap flex-stack mb-10 border-bottom pb-5">
                                <div class="d-flex align-items-center me-5 my-2">
                                    <div class="symbol symbol-50px symbol-circle me-3">
                                        <div class="symbol-label fs-2 bg-light-primary text-primary font-weight-bold">
                                            {{ strtoupper(substr($permit->student->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="#" class="fs-4 text-gray-800 text-hover-primary fw-bolder">{{ $permit->student->name }}</a>
                                        <span class="text-muted fw-bold fs-7">NISN: {{ $permit->student->nisn }} · Kelas: {{ $permit->student->classroom_name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-7">
                                <label class="col-lg-4 fw-bold text-muted">Asrama / Host Pembina</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-gray-800">{{ $permit->student->asrama_name ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="row mb-7">
                                <label class="col-lg-4 fw-bold text-muted">Nama Wali Santri (Pengaju)</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-gray-800">{{ $permit->user->name ?? '-' }} ({{ $permit->user->phone ?? '-' }})</span>
                                </div>
                            </div>

                            <div class="row mb-7">
                                <label class="col-lg-4 fw-bold text-muted">Alasan / Keperluan Izin</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-gray-800 bg-light p-3 rounded d-block">{{ $permit->reason }}</span>
                                </div>
                            </div>

                            <div class="row mb-7">
                                <label class="col-lg-4 fw-bold text-muted">Rencana Waktu Keluar</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-gray-800">
                                        <i class="fa fa-calendar-alt text-primary me-2"></i>
                                        {{ date('d-m-Y H:i', strtotime($permit->planned_exit_date)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-7">
                                <label class="col-lg-4 fw-bold text-muted">Rencana Waktu Kembali (Tenggat)</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-gray-800">
                                        <i class="fa fa-calendar-check text-warning me-2"></i>
                                        {{ date('d-m-Y H:i', strtotime($permit->planned_return_date)) }}
                                    </span>
                                </div>
                            </div>

                            @if($permit->actual_return_date)
                            <div class="row mb-7">
                                <label class="col-lg-4 fw-bold text-muted text-success">Realisasi Kembali</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-success">
                                        <i class="fa fa-check text-success me-2"></i>
                                        {{ date('d-m-Y H:i', strtotime($permit->actual_return_date)) }}
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if($permit->exit_escort_name)
                            <div class="row mb-7 border-top pt-5">
                                <label class="col-lg-4 fw-bold text-muted">Penjemput / Pendamping</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-gray-800">{{ $permit->exit_escort_name }} (Hubungan: {{ $permit->exit_escort_relation }})</span>
                                </div>
                            </div>
                            @endif

                            @if($permit->rejection_reason)
                            <div class="row mb-7 border-top pt-5">
                                <label class="col-lg-4 fw-bold text-danger">Alasan Penolakan</label>
                                <div class="col-lg-8">
                                    <span class="fw-bolder fs-6 text-danger bg-light-danger p-3 rounded d-block">{{ $permit->rejection_reason }}</span>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="card-footer border-top pt-5 d-flex gap-3">
                            <a href="{{ route('student-permit.index') }}" class="btn btn-light">Kembali</a>

                            @if($permit->status === 'pending')
                                <form action="{{ route('student-permit.update', $permit->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success">Setujui Pengajuan</button>
                                </form>

                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal_reject">
                                    Tolak Pengajuan
                                </button>
                            @endif

                            @if($permit->status === 'approved' || $permit->status === 'out')
                                <form action="{{ route('student-permit.update', $permit->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="return">
                                    <button type="submit" class="btn btn-primary">Nyatakan Kembali ke Pondok</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Layout-->
        </div>
    </div>
    <!--end::Post-->
</div>

<!-- Reject Modal -->
<div class="modal fade" id="modal_reject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Pengajuan Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('student-permit.update', $permit->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="reject">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label font-weight-bold">Alasan Penolakan</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required placeholder="Tulis alasan penolakan agar wali santri memahami keputusannya..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Tolak Izin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
