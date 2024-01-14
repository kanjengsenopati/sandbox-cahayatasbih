@extends('layouts.master', ['title' => 'Data Tahfidz'])
@push('css')

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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Tahfidz</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('tahfidz.index') }}">
                        <li class="breadcrumb-item text-muted">Tahfidz</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('tahfidz.create') ? 'Tambah Tahfidz' : 'Edit Tahfidz' }}</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->

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

                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="tahfidz" action="{{ request()->routeIs('tahfidz.create') ? route('tahfidz.store')
                                 : route('tahfidz.update', $tahfidz->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="student_id">
                                        <span class="required">Nama Santri</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Nama Santri"></i>
                                    </label>
                                    <x-form.student :value="@$tahfidz->student_id"
                                        class="form-control form-control-solid" />
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="deposit_date">
                                        <span class="required">Tanggal Setoran</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Tanggal Setoran"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="date" class="form-control form-control-solid" id="deposit_date"
                                        name="deposit_date" placeholder="Masukkan Tanggal Setoran"
                                        value="{{ @$tahfidz->deposit_date ?? old('deposit_date') }}" required />
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="number_of_pages">
                                        <span class="required">Jumlah Halaman</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Jumlah Halaman Setoran Tahfidz"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-solid"
                                            placeholder="Masukkan Jumlah Halaman Setoran Tahfidz"
                                            value="{{ @$tahfidz->number_of_pages ?? old('number_of_pages') }}"
                                            name="number_of_pages" id="number_of_pages" required />
                                        <span class="input-group-text">Halaman</span>
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="note">
                                        <span class="required">Keterangan Setoran</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Keterangan Yang Menjelaskan Setoran Tahfidz"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control form-control-solid" rows="3" id="note"
                                        name="note">{{ @$tahfidz->note ?? old('note') }}</textarea>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="feedback">
                                        <span class="required">Feedback Pengajar</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Feedback Pengajar"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control form-control-solid" rows="3" id="feedback"
                                        name="feedback">{{ @$tahfidz->feedback ?? old('feedback') }}</textarea>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="link">
                                        <span class="required">Link Video (Opsional)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Link Video yang telah diupload di Youtube"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="link" name="link"
                                        placeholder="Masukkan Link Video"
                                        value="{{ @$tahfidz->link ?? old('link') }}" />
                                    <!--end::Input-->
                                </div>

                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6">
                                </div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('tahfidz.index') }}">
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
                            <!--end::Form-->
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
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(document).ready(function () {
        $('#tahfidz').validate({
            rules: {
                student_id: {
                    required: true,
                },
                deposit_date: {
                    required: true,
                },
                number_of_pages: {
                    required: true,
                },
                note: {
                    required: true,
                },
                feedback: {
                    required: true,
                },
            },
            messages: {
                student_id: {
                    required: "Nama Santri tidak boleh kosong",
                },
                deposit_date: {
                    required: "Tanggal Setoran tidak boleh kosong",
                },
                number_of_pages: {
                    required: "Jumlah Halaman tidak boleh kosong",
                },
                note: {
                    required: "Keterangan Setoran tidak boleh kosong",
                },
                feedback: {
                    required: "Feedback Pengajar tidak boleh kosong",
                },
            },
            submitHandler: function (form) {
                form.submit();
            }
        });
    });

</script>
@endpush