@extends('layouts.master', ['title' => 'Data Siswa'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('student.index') }}">
                        <li class="breadcrumb-item text-muted">Siswa</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('student.create') ? 'Tambah Siswa' : 'Edit Siswa' }}</li>
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
                            <form id="student" action="{{ request()->routeIs('student.create') ? route('student.store')
                                 : route('student.update', $student->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="user_id">
                                        <span class="required">Nama Wali Siswa</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Nama Wali Siswa"></i>
                                    </label>
                                    <x-form.user :value="@$student->user_id" class="form-control form-control-solid" />
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="name">
                                        <span class="required">Nama Siswa</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="masukkan nama siswa"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="name" name="name"
                                        placeholder="Masukkan Nama Siswa" value="{{ @$student->name ?? old('name') }}"
                                        required />
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="gender">
                                        <span class="required">Jenis Kelamin</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Jenis Kelamin Siswa"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="gender" class="form-select form-select-solid" id="gender"
                                        data-control="select2" data-placeholder="Pilih Jenis Kelamin"
                                        data-allow-clear="true" data-hide-search="true">
                                        <option value="L" {{ @$user->gender == 'L' ? 'selected' : '' }}> Laki-Laki
                                        </option>
                                        <option value="P" {{ @$user->gender == 'P' ? 'selected' : '' }}> Perempuan
                                        </option>
                                    </select>
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="born_place">
                                        <span class="required">Tempat Lahir</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Tempat Lahir"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="born_place"
                                        name="born_place" placeholder="Masukkan Tempat Lahir"
                                        value="{{ @$student->born_place ?? old('born_place') }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="birth_date">
                                        <span class="required">Tanggal Lahir</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Tanggal Lahir Siswa"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="date" class="form-control form-control-solid" id="birth_date"
                                        name="birth_date" placeholder="Masukkan Tanggal Lahir Siswa"
                                        value="{{ @$student->birth_date ?? old('birth_date') }}" required />
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
                                    <a href="{{ route('student.index') }}">
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
        $('#student').validate({
       rules: {
        user_id: {
        required: true,
        },
        name: {
        required: true,
        maxlength: 255,
        },
        gender: {
        required: true,
        },
        born_place: {
        required: true,
        maxlength: 255,
        },
        birth_date: {
        required: true,
        },
        },
        messages: {
        user_id: {
        required: "Wali Siswa harus dipilih",
        },
        name: {
        required: "Nama Siswa harus diisi",
        maxlength: "Nama maksimal 255 karakter",
        },
        gender: {
        required: "Jenis Kelamin harus diisi",
        },
        born_place: {
        required: "Tempat Lahir harus diisi",
        maxlength: "Tempat Lahir maksimal 255 karakter",
        },
        birth_date: {
        required: "Tanggal Lahir harus diisi",
        },
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
        error.addClass("invalid-feedback");
        element.closest(".fv-row").append(error);
        },
        });
    });
</script>
@endpush