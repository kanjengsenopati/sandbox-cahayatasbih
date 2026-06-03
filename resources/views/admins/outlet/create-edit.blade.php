@extends('layouts.master', ['title' => 'Data Outlet'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Outlet</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('outlet.index') }}" class="text-muted text-hover-primary">Data Outlet</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('outlet.create') ? 'Tambah Outlet' : 'Edit Outlet' }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <div class="card-title">
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ request()->routeIs('outlet.create') ? 'Tambah Outlet' : 'Edit Outlet' }}
                                </h1>
                            </div>
                        </div>
                        <div class="card-body pt-5">
                            <x-alert.alert-validation />
                            <form id="outlet_form"
                                action="{{ request()->routeIs('outlet.create') ? route('outlet.store') : route('outlet.update', @$outlet->id) }}"
                                method="POST">
                                @csrf
                                <x-form.put-method />

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Nama Outlet</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Masukkan nama outlet"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="name"
                                        placeholder="Contoh: Cahaya Mart Putra" value="{{ @$outlet->name ?? old('name') }}" required />
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Kode Outlet</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Kode unik untuk nota (Maks. 10 karakter)"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="code"
                                        placeholder="Contoh: CHMPA" value="{{ @$outlet->code ?? old('code') }}" required maxlength="10" />
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span>Alamat</span>
                                    </label>
                                    <textarea class="form-control form-control-solid" name="address" rows="3"
                                        placeholder="Contoh: Gedung A Lantai 1">{{ @$outlet->address ?? old('address') }}</textarea>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Status</span>
                                    </label>
                                    <select name="is_active" class="form-select form-select-solid" data-control="select2" data-hide-search="true" required>
                                        <option value="1" {{ (old('is_active') ?? @$outlet->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ (old('is_active') ?? @$outlet->is_active) === 0 ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </div>

                                <div class="separator mb-6">
                                    <input type="hidden" name="id" value="{{ @$outlet->id }}">
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('outlet.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Cancel</button>
                                    </a>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Mohon Tunggu...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function () {
        $('#outlet_form').validate({
            rules: {
                name: { required: true, maxlength: 255 },
                code: { required: true, maxlength: 10 },
                is_active: { required: true }
            },
            messages: {
                name: { required: "Nama harus diisi", maxlength: "Maksimal 255 karakter" },
                code: { required: "Kode harus diisi", maxlength: "Maksimal 10 karakter" },
                is_active: { required: "Status harus dipilih" }
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
