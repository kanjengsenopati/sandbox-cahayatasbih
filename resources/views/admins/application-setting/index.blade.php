@extends('layouts.master', ['title' => 'Setting Aplikasi'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Setting Aplikasi</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('application-setting.index') }}">
                        <li class="breadcrumb-item text-muted">Setting Aplikasi</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        <span class="text-muted fw-bolder fs-7">Edit Setting</span>
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
                        <div class="card-body pt-7">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form action="{{ route('application-setting.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                {{-- add icon kontak aplikasi --}}
                                <div class="row mb-6">
                                    <div class="col-6">
                                        <a href="{{ route('contact.index') }}" class="btn btn-sm btn-light-primary">
                                            <i class="fas fa-user-circle fs-1 text-primary"></i>Data Kontak Aplikasi
                                        </a>
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="payment_fee">
                                            <span class="required">Fee Xendit</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Fee Pembayaran"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control form-control-solid"
                                                id="payment_fee" name="payment_fee" placeholder="Masukkan Fee Xendit"
                                                value="{{ @$applicationSetting->payment_fee ?? old('payment_fee') }}"
                                                required />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="bill_fee">
                                            <span class="required">Fee Tagihan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Fee Pembayaran"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control form-control-solid" id="bill_fee"
                                                name="bill_fee" placeholder="Masukkan Fee Tagihan"
                                                value="{{ @$applicationSetting->bill_fee ?? old('bill_fee') }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="saldo_fee">
                                            <span class="required">Fee Saldo</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Fee Pembayaran"></i>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control form-control-solid" id="saldo_fee"
                                                name="saldo_fee" placeholder="Masukkan Fee Saldo"
                                                value="{{ @$applicationSetting->saldo_fee ?? old('saldo_fee') }}"
                                                required />
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="payment_expire_time">
                                            <span class="required">Waktu Kadaluarsa Pembayaran</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Waktu Kadaluarsa Pembayaran"></i>
                                        </label>
                                        <input type="text" placeholder="hh:mm"
                                            class="form-control form-control-solid time" id="payment_expire_time"
                                            name="payment_expire_time"
                                            placeholder="Masukkan Waktu Kadaluarsa Pembayaran"
                                            value="{{ @$applicationSetting->payment_expire_time ?? old('payment_expire_time') }}"
                                            required />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="target_month">
                                            <span class="required">Target Bulanan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Target Bulanan"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money"
                                                id="target_month" name="target_month"
                                                placeholder="Masukkan Target Pembayaran Bulanan"
                                                value="{{ @$applicationSetting->target_month ?? old('target_month') }}"
                                                required />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="target_year">
                                            <span class="required">Target Tahunan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Target Tahunan"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money"
                                                id="target_year" name="target_year"
                                                placeholder="Masukkan Target Pembayaran Tahunan"
                                                value="{{ @$applicationSetting->target_year ?? old('target_year') }}"
                                                required />
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="link_whatsapp">
                                            <span class="required">Link Whatsapp Gateway</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Link Whatsapp Gateway"></i>
                                        </label>
                                        <input type="url" class="form-control form-control-solid" id="link_whatsapp"
                                            name="link_whatsapp" placeholder="Masukkan Link Whatsapp Gateway"
                                            value="{{ @$applicationSetting->link_whatsapp ?? old('link_whatsapp') }}"
                                            required />
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="number_whatsapp">
                                            <span class="required">Nomor Whatsapp</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nomor Whatsapp Gateway"></i>
                                        </label>
                                        <input type="number" class="form-control form-control-solid"
                                            id="number_whatsapp" name="number_whatsapp"
                                            placeholder="Masukkan Nomor Whatsapp Gateway"
                                            value="{{ @$applicationSetting->number_whatsapp ?? old('number_whatsapp') }}"
                                            required />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="api_key">
                                            <span class="required">Status Device ID</span>
                                            {{-- add info text with color red or green --}}
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Status Device ID"></i>
                                        </label>
                                        {{-- no input only info --}}
                                        <div class="form-control form-control-solid">
                                            <span
                                                class="badge badge-{{ @$applicationSetting->whatsapp_status ? 'success' : 'danger' }}">
                                                {{ @$applicationSetting->whatsapp_status ? 'Aktif' : 'Tidak Aktif'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="device_id">
                                            <span class="required">Device ID</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan device id yang diperolah dari aplikasi whatsapp gateway"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="device_id"
                                            name="device_id" placeholder="Masukkan Device ID"
                                            value="{{ @$applicationSetting->device_id ?? old('device_id') }}"
                                            required />
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <!--end::Input-->
                                    </div>
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <div class="fv-row mb-6">
                                        <x-form.image-upload label="Background Kartu Siswa" name="student_card_image"
                                            :value="@$applicationSetting->student_card_image ?? null" />
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6">
                                </div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->

                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    @if (Auth::user()->can('Edit Pengaturan Aplikasi'))
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                    @endif
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
    $('.time').mask('00:00', {
        reverse: true
        });
</script>
@endpush