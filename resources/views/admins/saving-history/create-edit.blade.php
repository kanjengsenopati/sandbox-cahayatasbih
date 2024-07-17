@extends('layouts.master', ['title' => 'Data Riwayat Tabungan'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Riwayat Tabungan</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <a class="breadcrumb-item" href="{{ route('saving-history.index') }}">
                        <li class="text-muted">Riwayat Tabungan</li>
                    </a>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Penyesuaian Tabungan</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-body pt-5">
                            <x-alert.alert-validation />
                            <form id="saving-history"
                                action="{{ request()->routeIs('saving-history.create') ? route('saving-history.store') : route('saving-history.update', @$savingHistory->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label" for="student_id">
                                        <span class="required">Tanggal Transaksi</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tanggal Tabungan"></i>
                                    </label>
                                    <input type="date" name="date" id="date" class="form-control form-control-solid"
                                        placeholder="Pilih Tanggal Tabungan"
                                        value="{{ old('date') ?? @$savingHistory->date }}" required />
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label" for="student_id">
                                        <span class="required">Nama Santri</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Nama Santri"></i>
                                    </label>
                                    <x-form.student-saving :value="@$savingHistory->student_id"
                                        class="form-control form-control-solid" />
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                        <span class="required">Tipe Tabungan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Penyesuaian"></i>
                                    </label>
                                    <select name="type" id="type" class="form-select form-select-solid"
                                        aria-label="Select example">
                                        <option value="IN" {{ @$savingHistory->type == 'IN' ? 'selected' : '' }}>
                                            Setor Tabungan</option>
                                        <option value="OUT" {{ @$savingHistory->type == 'OUT' ? 'selected' : '' }}>
                                            Tarik Tabungan</option>
                                    </select>
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="amount">
                                        <span class="required">Jumlah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Jumlah nominal"></i>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="amount" id="amount"
                                            class="form-control form-control-solid input-money"
                                            placeholder="Masukkan Jumlah Penyesuaian"
                                            value="{{ old('amount') ?? @$savingHistory->amount }}" required />
                                    </div>
                                </div>
                                <div class="separator mb-6"></div>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('saving-history.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('saving-history');
        const amountInput = document.getElementById('amount');
        const submitButton = form.querySelector('[type="submit"]');

        submitButton.addEventListener('click', function (event) {
            event.preventDefault();
            
            setTimeout(function () {
                const rawValue = amountInput.value.replace(/[,.]/g, '');
                amountInput.value = rawValue;

                form.submit();
            }, 2000);
        });
    });
</script>
@endpush