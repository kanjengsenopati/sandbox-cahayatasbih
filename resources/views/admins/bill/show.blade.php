@extends('layouts.master', ['title' => 'Konfirmasi Pembayaran'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    Verifikasi Pembayaran</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('bill.index') }}" class="text-muted text-hover-primary">Pembayaran</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        <span class="text-muted text-hover-primary">Konfirmasi Pembayaran</span>
                    </li>
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">

            </div>
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
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    #{{ @$transaction?->payment_code ?? '' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--end::Card body-->
                        <div class="card-body pt-5">
                            <div class="text-center">
                                <img src="{{ asset('assets/media/svg/approval/approval.svg') }}" alt="" class="w-50">
                            </div>
                            <form class="form" action="{{ route('bill.update', ['bill' => $transaction->id]) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="name">
                                        <span class="required">Status</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Status Yang Sesuai Dengan Pembayaran"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="status" class="form-select form-select-solid" id="status"
                                        data-control="select2" data-placeholder="Pilih Status" data-hide-search="true">
                                        <option value="">Pilih Status</option>
                                        <option value="PENDING_PAYMENT" {{ @$transaction->status == 'PENDING_PAYMENT' ?
                                            'selected' :
                                            '' }}>
                                            Menunggu Pembayaran </option>
                                        <option value="PENDING_CONFIRMATION" {{ @$transaction->status ==
                                            'PENDING_CONFIRMATION' ? 'selected'
                                            : '' }}>
                                            Menunggu Konfirmasi Petugas </option>
                                        <option value="PAID" {{ @$transaction->status == 'PAID' ? 'selected'
                                            : '' }}>Pembayaran Diterima</option>
                                        <option value="REJECTED" {{ @$transaction->status == 'REJECTED' ? 'selected'
                                            : '' }}>Ditolak</option>
                                    </select>
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6" id="reason" style="display: none">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="name">
                                        <span class="required">Alasan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Alasan"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea name="note" class="form-control form-control-solid" rows="3"
                                        placeholder="Masukkan Alasan"> {{ @$transaction->note }}</textarea>
                                    <!--end::Input-->
                                </div>

                                <!--end::Form-->
                                <div class="accordion accordion-flush" id="accordionFlushExample">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-headingOne">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                                                aria-expanded="false" aria-controls="flush-collapseOne">
                                                Data Pembayaran
                                            </button>
                                        </h2>
                                        <div id="flush-collapseOne" class="accordion-collapse collapse"
                                            aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <div class="row fv-row mb-6">
                                                    <div class="col-12 col-md-6">
                                                        <!--begin::Label-->
                                                        <label class="fs-6 fw-bold form-label">
                                                            <span class="required">Kode Pembayaran</span>
                                                            <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                                data-bs-toggle="tooltip" title="Kode Pembayaran"></i>
                                                        </label>
                                                        <!--end::Label-->
                                                        <!--begin::Value-->
                                                        <div class="form-control form-control-solid">
                                                            {{ @$transaction->payment_code }}
                                                        </div>
                                                        <!--end::Value-->
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <!--begin::Label-->
                                                        <label class="fs-6 fw-bold form-label">
                                                            <span class="required">Siswa</span>
                                                            <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                                data-bs-toggle="tooltip" title="Nama Siswa"></i>
                                                        </label>
                                                        <!--end::Label-->
                                                        <!--begin::Value-->
                                                        <div class="form-control form-control-solid">
                                                            {{ @$transaction?->student?->name }}
                                                        </div>
                                                        <!--end::Value-->
                                                    </div>
                                                </div>

                                                <div class="row fv-row mb-6">
                                                    <div class="col-12 col-md-6">
                                                        <!--begin::Label-->
                                                        <label class="fs-6 fw-bold form-label">
                                                            <span class="required">Total Pembayaran</span>
                                                            <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                                data-bs-toggle="tooltip" title="Total Pembayaran"></i>
                                                        </label>
                                                        <!--end::Label-->
                                                        <!--begin::Value-->
                                                        <div class="form-control form-control-solid">
                                                            Rp. {{ number_format(@$transaction->pay_amount) }}
                                                        </div>
                                                        <!--end::Value-->
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <!--begin::Label-->
                                                        <label class="fs-6 fw-bold form-label">
                                                            <span class="required">Kode Pembayaran</span>
                                                        </label>
                                                        <!--end::Label-->
                                                        <!--begin::Value-->
                                                        <div class="form-control form-control-solid">
                                                            {{ @$transaction->unique_payment }}
                                                        </div>
                                                        <!--end::Value-->
                                                    </div>
                                                </div>

                                                <div class="row fv-row mb-6">
                                                    <div class="col-12 col-md-6">
                                                        <!--begin::Label-->
                                                        <label class="fs-6 fw-bold form-label">
                                                            <span class="required">Biaya Admin Aplikasi</span>
                                                        </label>
                                                        <!--end::Label-->
                                                        <!--begin::Value-->
                                                        <div class="form-control form-control-solid">
                                                            Rp. {{ number_format($transaction->app_fee) }}
                                                        </div>
                                                        <!--end::Value-->
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <!--begin::Label-->
                                                        <label class="fs-6 fw-bold form-label">
                                                            <span class="required">Waktu Kadaluarsa</span>
                                                        </label>
                                                        <!--end::Label-->
                                                        <!--begin::Value-->
                                                        <div class="form-control form-control-solid">
                                                            {{ date('d F Y H:i', strtotime($transaction->expiry_time))
                                                            }}
                                                        </div>
                                                        <!--end::Value-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('bill.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                    <!--end::Button-->
                                </div>
                                <!--end::Action buttons-->
                            </form>
                        </div>
                    </div>
                    <!--end::Contacts-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Contacts App- Add New Contact-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(document).ready(function() {
            var status = $('#status').val();
            toggleReasonField(status);

            $('#status').change(function() {
                var status = $(this).val();
                toggleReasonField(status);
            });

            function toggleReasonField(status) {
                if (status == 'REJECTED' || status == 'REVISION') {
                    $('#reason').show();
                } else {
                    $('#reason').hide();
                }
            }
        });
</script>
@endpush