@extends('layouts.master', ['title' => 'Admin'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Admin</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('admin.index') }}">
                        <li class="breadcrumb-item text-muted">Data Admin</li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('admin.create') ? 'Tambah Admin' : 'Edit Admin' }}</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
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
                                    {{ request()->routeIs('admin.create') ? 'Tambah Admin' : 'Edit Admin' }}</h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="admin" action="{{ request()->routeIs('admin.create') ? route('admin.store') :
                                 route('admin.update', @$admin->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="email">
                                        <span class="required">Email </span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Email"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="email" class="form-control form-control-solid" id="email"
                                        placeholder="Contoh: admin@gmail.com" name="email"
                                        value="{{ @$admin->email ?? old('email') }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Nama </span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="masukkan nama"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="name"
                                        placeholder="Contoh: Admin" value="{{ @$admin->name ?? old('name') }}"
                                        required />
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="password">
                                        <span class="required">Password</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Password"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="position-relative">
                                        <input type="password" class="form-control form-control-solid" id="password"
                                            placeholder="{{ route('admin.create') ? 'Contoh: CahayaTasbih123' : 'Kosongkan jika tidak ingin mengubah password'  }}"
                                            name="password" value="{{ old('password') }}" />
                                        <span
                                            class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                            data-kt-password-meter-control="visibility">
                                            <i class="bi bi-eye-slash fs-2"></i>
                                            <i class="bi bi-eye fs-2 d-none"></i>
                                        </span>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="password_confirmation">
                                        <span class="required">Konfirmasi Password</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Konfirmasi Password"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="position-relative">
                                        <input type="password" class="form-control form-control-solid"
                                            id="password_confirmation" name="password_confirmation"
                                            placeholder="Konfirmasi Password Harus Sama Dengan Password"
                                            value="{{ old('password_confirmation') }}" />
                                        <span
                                            class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                            data-kt-password-meter-control="visibility">
                                            <i class="bi bi-eye-slash fs-2"></i>
                                            <i class="bi bi-eye fs-2 d-none"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="role_id">
                                        <span class="required">Role</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Role Akses yang dimiliki admin"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="role_id" class="form-select form-select-solid" id="role_id"
                                        data-control="select2" data-placeholder="Select option" data-allow-clear="true"
                                        data-hide-search="true">
                                        <option value="">--Pilih Role--</option>
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id')==$role->id || @$admin->role_id
                                            == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="school">
                                        <span class="required">Divisi Admin (Jika Bukan Super Admin)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Unit Pendidikan Admin"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->

                                    <select name="admin_schools[]" class="form-select form-select-solid mb-3"
                                        id="select2" data-control="select2" data-allow-clear="true" multiple="multiple"
                                        required>
                                        @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" @if (in_array(@$school->id,
                                            @$adminSchools)) selected @endif>
                                            {{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <x-form.image-upload label="Avatar" maxSize="2MB" name="avatar"
                                        :value="@$admin->avatar ?? null" nullable='1' />
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6">
                                    <input type="hidden" name="id" value="{{ @$admin->id }}">
                                </div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('admin.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-sm btn-secondary me-3">Cancel</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Mohon Tunggu...
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
        $('#admin').validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 255,
                },
                email: {
                    required: true,
                    maxlength: 255,
                    email: true,
                },
                password: {
                    maxlength: 255,
                    minlength: 8,
                },
                password_confirmation: {
                    maxlength: 255,
                    minlength: 8,
                    equalTo: "#password",
                },
                role_id: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Nama harus diisi",
                    maxlength: "Nama maksimal 255 karakter",
                },
                email: {
                    required: "Email harus diisi",
                    maxlength: "Email maksimal 255 karakter",
                    email: "Email tidak valid",
                },
                password: {
                    maxlength: "Password maksimal 255 karakter",
                },
                password_confirmation: {
                    maxlength: "Konfirmasi Password maksimal 255 karakter",
                    equalTo: "Konfirmasi Password tidak sama dengan Password",
                },
                role_id: {
                    required: "Role harus diisi",
                },
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".fv-row").append(error);
            },
        });

        // add show password toggle in textbox
        $("body").on("click", "[data-kt-password-meter-control='visibility']", function () {
            var input = $(this).closest(".position-relative").find("input");
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });

    });
</script>
@endpush