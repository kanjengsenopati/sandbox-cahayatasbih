@extends('layouts.master', ['title' => 'Data User'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data User</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('user.index') }}">
                        <li class="breadcrumb-item text-muted">Wali Santri</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('user.create') ? 'Tambah Wali Santri' : 'Edit Wali Santri' }}</li>
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
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="user"
                                action="{{ request()->routeIs('user.create') ? route('user.store') : route('user.update', @$user->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="name">
                                                <span class="required">Nama</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan Nama"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" class="form-control form-control-solid" id="name" name="name"
                                                placeholder="Masukkan Nama Wali Santri"
                                                value="{{ @$user->name ?? old('name') }}" />
                                            <!--end::Input-->
                                        </div>
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="email">
                                                <span class="required">Email</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="masukkan email anda"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="email" class="form-control form-control-solid" id="email" name="email"
                                                placeholder="Masukkan Email Wali Santri"
                                                value="{{ @$user->email ?? old('email') }}" />
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
                                                    placeholder="{{ route('user.create') ? 'Contoh: User123' : 'Kosongkan jika tidak ingin mengubah password'  }}"
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
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="gender">
                                                <span class="required">Jenis Kelamin</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Jenis Kelamin User"></i>
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
                                            <label class="fs-6 fw-bold form-label" for="phone">
                                                <span class="required">No Handphone (Format: 08xxxx)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan No Handphone"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" class="form-control form-control-solid" id="phone" name="phone"
                                                placeholder="Format: 08xxxx" value="{{ @$user->phone ?? old('phone') }}" />
                                            <!--end::Input-->
                                        </div>
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="jamaah_status">
                                                <span class="required">Status Jamaah</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Status Keanggotaan Jamaah Wali Santri"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <select name="jamaah_status" class="form-select form-select-solid" id="jamaah_status"
                                                data-control="select2" data-placeholder="Pilih Status Jamaah"
                                                data-hide-search="true" required>
                                                <option value="UNKNOWN" {{ (old('jamaah_status') ?? @$user->jamaah_status) == 'UNKNOWN' ? 'selected' : '' }}>Belum Jelas (Default)</option>
                                                <option value="JAMAAH" {{ (old('jamaah_status') ?? @$user->jamaah_status) == 'JAMAAH' ? 'selected' : '' }}>Jamaah</option>
                                                <option value="NON_JAMAAH" {{ (old('jamaah_status') ?? @$user->jamaah_status) == 'NON_JAMAAH' ? 'selected' : '' }}>Non Jamaah</option>
                                                <option value="MUKIMIN" {{ (old('jamaah_status') ?? @$user->jamaah_status) == 'MUKIMIN' ? 'selected' : '' }}>Mukimin</option>
                                            </select>
                                            <!--end::Input-->
                                        </div>
                                        <div class="fv-row mb-6">
                                            <x-form.image-upload label="Avatar" name="avatar" :value="@$user->avatar ?? null" />
                                        </div>
                                    </div>
                                </div>

                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6">
                                    <input type="hidden" name="id" value="{{ @$user->id }}">
                                </div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('user.index') }}">
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
        $('#user').validate({
       rules: {
        name: {
        required: true,
        maxlength: 255,
        },
        email: {
        required: true,
        maxlength: 255,
        email : true,
        },
        gender: {
        required: true,
        },
        phone: {
        required: true,
        maxlength: 255,
        },
        jamaah_status: {
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
        required: "Password harus diisi",
        maxlength: "Password maksimal 255 karakter",
        },
        password_confirmation: {
        required: "Konfirmasi Password harus diisi",
        equalTo: "Konfirmasi Password tidak sama dengan Password",
        },
        gender: {
        required: "Jenis Kelamin harus diisi",
        },
        phone: {
        required: "No Handphone harus diisi",
        maxlength: "No Handphone maksimal 255 karakter",
        },
        jamaah_status: {
        required: "Status Jamaah harus diisi",
        },
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
        error.addClass("invalid-feedback");
        element.closest(".fv-row").append(error);
        },
        submitHandler: function (form) {
            var name = $('#name').val();
            var phone = $('#phone').val();
            var userId = $('input[name="id"]').val();
            var submitButton = $(form).find('[data-kt-contacts-type="submit"]');

            // Disable submit button and show loading state
            submitButton.attr('disabled', true);
            submitButton.find('.indicator-label').hide();
            submitButton.find('.indicator-progress').show();

            $.ajax({
                url: '{{ route("user.check-duplicate") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: name,
                    phone: phone,
                    id: userId
                },
                success: function(response) {
                    if (response.duplicate) {
                        // Reset submit button state to allow user reaction
                        submitButton.removeAttr('disabled');
                        submitButton.find('.indicator-label').show();
                        submitButton.find('.indicator-progress').hide();

                        Swal.fire({
                            title: 'Data Duplikat Terdeteksi',
                            text: response.message + '. Apakah Anda yakin tetap ingin menyimpan data ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Tetap Simpan',
                            cancelButtonText: 'Batal',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-active-light'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Re-enable loading state and submit the form directly
                                submitButton.attr('disabled', true);
                                submitButton.find('.indicator-label').hide();
                                submitButton.find('.indicator-progress').show();
                                form.submit();
                            }
                        });
                    } else {
                        form.submit();
                    }
                },
                error: function() {
                    // Fallback: if AJAX fails, just submit the form
                    form.submit();
                }
            });
        }
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