@extends('layouts.master', ['title' => 'Data Riwayat Saldo'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Riwayat Saldo</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('saldo-history.index') }}">
                        <li class="text-muted">
                            Riwayat Saldo
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Penyesuaian Saldo</li>
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

                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="saldo-history"
                                action="{{ request()->routeIs('saldo-history.create') ? route('saldo-history.store') : route('saldo-history.update', @$stockHistory->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label" for="student_id">
                                        <span class="required">Nama Santri</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Nama Santri"></i>
                                    </label>
                                    <x-form.student-saldo :value="@$saldoHistory->student_id"
                                        class="form-control form-control-solid" />
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                        <span class="required">Tipe Penyesuaian</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Penyesuaian"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="type" id="type" class="form-select form-select-solid"
                                        aria-label="Select example">
                                        <option value="IN" {{ @$saldoHistory->type == 'IN' ? 'selected' : '' }}>
                                            Saldo Masuk</option>
                                        <option value="OUT" {{ @$saldoHistory->type == 'OUT' ? 'selected' : '' }}>
                                            Saldo Keluar</option>
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="amount">
                                        <span class="required">Jumlah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Jumlah Penyesuaian"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="amount" id="amount"
                                            class="form-control form-control-solid input-money"
                                            placeholder="Masukkan Jumlah Penyesuaian"
                                            value="{{ old('amount') ?? @$stockHistory->amount }}" required />
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('saldo-history.index') }}">
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('saldo-history');
        const amountInput = document.getElementById('amount');
        const submitButton = form.querySelector('[type="submit"]');

        submitButton.addEventListener('click', function (event) {
            // Remove thousands separator from the amount input
            const rawValue = amountInput.value.replace(/,/g, '');
            amountInput.value = rawValue;

            // Allow form submission to proceed
        });
    });
</script>
@endpush