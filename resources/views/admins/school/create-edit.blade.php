@extends('layouts.master', ['title' => 'Data Wilayah UPT'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Scope Wilayah UPT</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('school.index') }}">
                        <li class="text-muted">
                            Wilayah UPT
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('school.create') ? 'Tambah Wilayah UPT' : 'Edit Wilayah UPT' }}</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">{{
                                    request()->routeIs('school.create') ? 'Tambah Wilayah UPT' : 'Edit Wilayah UPT' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            @php
                                $featuresArray = isset($school) && $school->features ? json_decode($school->features, true) ?? [] : (old('features') ?? []);
                            @endphp
                            <form id="school"
                                action="{{ request()->routeIs('school.create') ? route('school.store') : route('school.update', @$school->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <!--begin::Input group-->
                                <div class="row g-7">
                                <div class="col-md-6 mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Wilayah UPT</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Nama Wilayah UPT"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        placeholder="Masukkan Nama Wilayah UPT" value="{{ @$school->name ?? old('name') }}"
                                        required />
                                    <!--end::Input-->
                                </div>

                                <div class="col-md-6 mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                        <span class="required">Tipe Sekolah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Tipe Sekolah"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="type" id="type" class="form-select form-select-solid"
                                        data-control="select2" data-placeholder="Pilih Tipe Sekolah">
                                        <option></option>
                                        @foreach ($types as $key => $type)
                                        <option value="{{ $key }}" {{ (isset($school) && $school->type == $key) || old('type') == $key ? 'selected' : '' }}>
                                            {{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>

                                 <div class="col-md-6 mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="address">
                                        <span class="required">Alamat Sekolah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Alamat Sekolah"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control form-control-solid" name="address" id="address"
                                        placeholder="Masukkan Alamat Sekolah" rows="3">{{ @$school->address ?? old('address') }}</textarea>
                                    <!--end::Input-->
                                </div>

                                <div class="col-md-6 mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="description">
                                        <span class="required">Keterangan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Keterangan yang tampil di halaman PSB"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control form-control-solid" name="description" id="description"
                                        placeholder="Masukkan Keterangan Sekolah" rows="3">{{ @$school->description ?? old('description') }}</textarea>
                                    <!--end::Input-->
                                </div>

                                <div class="col-md-6 mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="features">
                                        <span>Keunggulan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="List Keunggulan yang tampil di web PSB"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div id="features-container">
                                        @if($featuresArray)
                                        @foreach($featuresArray as $feature)
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-solid" name="features[]" value="{{ $feature }}" placeholder="Masukkan fitur">
                                            <button type="button" class="btn btn-danger remove-feature">Hapus</button>
                                        </div>
                                        @endforeach
                                        @endif
                                        <button type="button" class="btn btn-success mb-2" id="add-feature-btn">Tambah Keunggulan</button>
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <div class="col-md-6 mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="icon_name">
                                        <span>Icon Name</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Icon yang akan digunakan untuk tampil di PSB"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="icon_name" id="icon_name"
                                        placeholder="Masukkan Nama Icon" value="{{ @$school->icon_name ?? old('icon_name') }}" />
                                    <!--end::Input-->
                                </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('school.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('features-container');
    const addBtn = document.getElementById('add-feature-btn');

    addBtn.addEventListener('click', function() {
        const newInputGroup = document.createElement('div');
        newInputGroup.className = 'input-group mb-2';
        newInputGroup.innerHTML = `
            <input type="text" class="form-control form-control-solid" name="features[]" placeholder="Masukkan fitur">
            <button type="button" class="btn btn-danger remove-feature">Hapus</button>
        `;
        container.insertBefore(newInputGroup, addBtn);
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-feature')) {
            e.target.closest('.input-group').remove();
        }
    });
});
</script>
@endsection