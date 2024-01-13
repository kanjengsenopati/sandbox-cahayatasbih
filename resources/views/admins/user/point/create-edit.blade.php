@extends('layouts.master', ['title' => 'Data Poin User'])
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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Poin User</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->

                        <!--end::Item-->
                        <!--begin::Item-->
                        <a class="breadcrumb-item" href="{{ route('user-point.index') }}">
                            <li class="breadcrumb-item text-muted">Poin User</li>
                        </a>
                        <!--end::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-dark">
                            {{ request()->routeIs('user-point.create') ? 'Tambah Poin User' : 'Edit Poin User' }}</li>
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
                                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">{{ request()->routeIs('user-point.create') ? 'Tambah Poin User' : 'Edit Poin User' }}
                                    </h1>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-5">
                                <!--begin::Form-->
                                <x-alert.alert-validation />
                                <form class="form"
                                    action="{{ request()->routeIs('user-point.create') ? route('user-point.store') : route('user-point.update', @$user_point->id) }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <x-form.put-method />
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="user_id">
                                            <span class="required">Nama Pengguna</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilihlah Nama Pengguna"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select name="user_id" id="user_id"
                                            class="form-select form-select-solid select2-hidden-accessible"
                                            data-control="select2" data-placeholder="Pilih Pengguna" tabindex="-1"
                                            aria-hidden="true">
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ @$user_point->user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} - {{ $user->phone }}</option>
                                            @endforeach
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="type">
                                            <span class="required">Tipe Poin</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilihlah Tipe Penambahan atau Pengurangan Poin"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select name="type" id="type"
                                            class="form-select form-select-solid select2-hidden-accessible"
                                            data-control="select2" data-hide-search="true"
                                            data-placeholder="Pilih Tipe Poin" tabindex="-1" aria-hidden="true">
                                            <option value="ADD" {{ @$user_point->type == 'ADD' ? 'selected' : '' }}>
                                                Penambahan Poin</option>
                                            <option value="REDUCE" {{ @$user_point->type == 'REDUCE' ? 'selected' : '' }}>
                                                Pengurangan Poin</option>
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="point">
                                            <span class="required">Jumlah Poin</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Jumlah Poin"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" class="form-control form-control-solid" id="point" name="point"
                                            value="{{ old('point', @$user_point->point) }}" />
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Separator-->
                                    <div class="separator mb-6"></div>
                                    <!--end::Separator-->
                                    <!--begin::Action buttons-->
                                    <div class="d-flex justify-content-end">
                                        <!--begin::Button-->
                                        <a href="{{ route('user-point.index') }}">
                                            <button type="button" data-kt-contacts-type="cancel"
                                                class="btn btn-sm btn-secondary me-3">Cancel</button>
                                        </a>
                                        <!--end::Button-->
                                        <!--begin::Button-->
                                        <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                            <span class="indicator-label">Save</span>
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
