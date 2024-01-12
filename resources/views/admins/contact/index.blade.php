@extends('layouts.master', ['title' => 'Kontak Perusahaan'])
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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Kontak</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->

                        <!--end::Item-->
                        <!--begin::Item-->
                        <a class="breadcrumb-item">
                            <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}"
                                    class="text-muted text-hover-primary">Dashboard</a></li>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-dark">
                            Kontak Perusahaan</li>
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
                                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">Kontak Perusahaan
                                    </h1>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-5">
                                <!--begin::Form-->
                                <x-alert.alert-validation />
                                <form action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <x-form.put-method />
                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="content">
                                            <span class="required">Email Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Email Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="email" class="form-control form-control-solid" id="email"
                                            placeholder="Masukkan Email Perusahaan" name="email"
                                            value="{{ @$contact->email ?? old('email') }}" required />

                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="phone">
                                            <span class="required">Telepon Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Telepon Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-solid" id="phone"
                                            placeholder="Masukkan Telepon Perusahaan" name="phone"
                                            value="{{ @$contact->phone ?? old('phone') }}" required />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="address">
                                            <span class="required">Alamat Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Alamat Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <textarea name="address" id="address" cols="30" rows="10"
                                        class="form-control form-control-solid" required>{{ @$contact->address ??
                                         old('address') }}</textarea>
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="maps">
                                            <span class="required">Link Maps Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Url Maps Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="url" class="form-control form-control-solid" id="maps"
                                            placeholder="Url Maps Perusahaan Contoh : (https://maps.app.goo.gl/P6nDoLiFGrXdm2xVA)"
                                             name="maps" value="{{ @$contact->maps ?? old('maps') }}" />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="facebook">
                                            <span class="required">Facebook Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Url Facebook Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="url" class="form-control form-control-solid" id="facebook"
                                            placeholder="Masukkan Url Facebook Perusahaan" name="facebook"
                                            value="{{ @$contact->facebook ?? old('facebook') }}" />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="twitter">
                                            <span class="required">Twitter Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Url Twitter Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="url" class="form-control form-control-solid" id="twitter"
                                            placeholder="Masukkan Url Twitter Perusahaan" name="twitter"
                                            value="{{ @$contact->twitter ?? old('twitter') }}" />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="instagram">
                                            <span class="required">Instagram Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Url Instagram Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="url" class="form-control form-control-solid" id="instagram"
                                            placeholder="Masukkan Url Instagram Perusahaan" name="instagram"
                                            value="{{ @$contact->instagram ?? old('instagram') }}" />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="youtube">
                                            <span class="required">Youtube Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Url Youtube Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="url" class="form-control form-control-solid" id="youtube"
                                            placeholder="Masukkan Url Youtube Perusahaan" name="youtube"
                                            value="{{ @$contact->youtube ?? old('youtube') }}" />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-7">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="linkedin">
                                            <span class="required">Linkedin Perusahaan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Url Linkedin Perusahaan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="url" class="form-control form-control-solid" id="linkedin"
                                            placeholder="Masukkan Url Linkedin Perusahaan" name="linkedin"
                                            value="{{ @$contact->linkedin ?? old('linkedin') }}" />
                                        <!--end::Input-->
                                    </div>
                                    <!--begin::Action buttons-->
                                    <div class="d-flex justify-content-end">
                                        <!--begin::Button-->
                                        <button type="submit" data-kt-contacts-type="submit"
                                            class="btn btn-sm btn-primary">
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
