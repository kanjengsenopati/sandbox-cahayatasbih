@extends('layouts.master', ['title' => 'Data PPDB'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data PPDB</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('ppdb.index') }}">
                        <li class="text-muted">
                            PPDB
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
                        {{ request()->routeIs('ppdb.create') ? 'Tambah PPDB' : 'Edit PPDB' }}</li>
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
                                    request()->routeIs('ppdb.create') ? 'Tambah PPDB' : 'Edit PPDB' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="ppdb"
                                action="{{ request()->routeIs('ppdb.create') ? route('ppdb.store') : route('ppdb.update', @$ppdb->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <!--begin::Input group-->
                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama PPDB</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Nama PPDB"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" name="name" class="form-control form-control-solid"
                                        placeholder="Masukkan Nama PPDB" value="{{ old('name', @$ppdb->name) }}" />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="ppdb_type_id">
                                        <span class="required">Tipe Jalur PPDB</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Nama PPDB"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="ppdb_type_id" id="ppdb_type_id" class="form-select form-select-solid"
                                        data-control="select2" data-placeholder="Pilih Tipe Jalur PPDB">
                                        <option value="">Pilih Tipe Jalur PPDB</option>
                                        @foreach ($ppdbTypes as $ppdbType)
                                        <option value="{{ $ppdbType->id }}" {{ old('ppdb_type_id', @$ppdb->ppdb_type_id)
                                            == $ppdbType->id ? 'selected' : '' }}>
                                            {{ $ppdbType->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="academic_year_id">
                                        <span class="required">Tahun Ajaran</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tahun Ajaran"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-form.academic-year name="academic_year_id" id="academic_year_id"
                                        :value="@$ppdb->academic_year_id ?? null " />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="academic_year_id">
                                        <span class="required">Sekolah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Sekolah"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="school_id" id="school_id" class="form-select form-select-solid"
                                        data-control="select2" data-placeholder="Pilih Sekolah">
                                        <option value="">Pilih Sekolah</option>
                                        @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id', @$ppdb->school_id) ==
                                            $school->id
                                            ? 'selected' : '' }}>
                                            {{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="capacity">
                                        <span class="required">Kouta PPDB</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tahun Ajaran"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="number" name="capacity" class="form-control form-control-solid"
                                        placeholder="Masukkan Kouta PPDB"
                                        value="{{ old('capacity', @$ppdb->capacity) }}" />
                                    <!--end::Input-->
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="start_date">
                                            <span class="required">Tanggal Mulai</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Tanggal Mulai"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="date" name="start_date" class="form-control form-control-solid"
                                            value="{{ old('start_date', @$ppdb->start_date) }}" />
                                        <!--end::Input-->
                                    </div>

                                    <div class="col-md-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="end_date">
                                            <span class="required">Tanggal Selesai</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Tanggal Selesai"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="date" name="end_date" class="form-control form-control-solid"
                                            value="{{ old('end_date', @$ppdb->end_date) }}" />
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                        <span class="required">Status</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tahun Ajaran"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="is_active" id="is_active" class="form-select form-select-solid"
                                        data-placeholder="Pilih Status">
                                        <option value="">Pilih Status</option>
                                        <option value="1" {{ old('is_active', @$ppdb->is_active) == 1 ? 'selected' : ''
                                            }}>
                                            Aktif</option>
                                        <option value="0" {{ old('is_active', @$ppdb->is_active) == 0 ? 'selected' : ''
                                            }}>
                                            Tidak Aktif</option>
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="decsription">
                                        <span class="required">Deskripsi</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Deskripsi"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea name="description" class="form-control form-control-solid"
                                        placeholder="Masukkan Deskripsi">{{ old('description', @$ppdb->description) }}</textarea>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <x-form.image-upload label="Foto" name="image" :value="@$ppdb->image" />
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('ppdb.index') }}">
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
@endsection
@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/classic/ckeditor.js"></script>
<script>
    // add ckeditor on description
        ClassicEditor.create(document.querySelector('textarea[name="description"]'))
            .then(editor => {
                console.log(editor);
            })
            .catch(error => {
                console.error(error);
            });
</script>
@endpush