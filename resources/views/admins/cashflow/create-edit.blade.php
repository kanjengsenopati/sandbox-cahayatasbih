@extends('layouts.master', ['title' => 'Data Arus Kas'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Arus Kas</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('cashflow.index') }}">
                        <li class="text-muted">
                            Arus Kas
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
                        {{ request()->routeIs('cashflow.create') ? 'Tambah Arus Kas' : 'Edit Arus Kas'
                        }}
                    </li>
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
                            <form id="Arus Kas"
                                action="{{ request()->routeIs('cashflow.create') ? route('cashflow.store') : route('cashflow.update', @$cashflow->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                <!--begin::Input group (responsive columns)-->
                                <div class="row g-7">
                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="cash_flow_category_id">
                                            <span class="required">Kategori</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Kategori"></i>
                                        </label>
                                        <select class="form-select form-select-solid" name="cash_flow_category_id"
                                            id="cash_flow_category_id">
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ @$cashflow->cash_flow_category_id
                                                == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="type">
                                            <span class="required">Tipe</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Tipe Arus Kas"></i>
                                        </label>
                                        <select class="form-select form-select-solid" name="type" id="type" required>
                                            <option value="">Pilih Tipe</option>
                                            <option value="INCOME" {{ @$cashflow->type == 'INCOME' ? 'selected' : ''
                                                }}>Pemasukan
                                            </option>
                                            <option value="EXPENSE" {{ @$cashflow->type == 'EXPENSE' ? 'selected' : ''
                                                }}>Pengeluaran
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="receiver_id">
                                            <span class="required">Penerima</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Penerima"></i>
                                        </label>
                                        <select class="form-select form-select-solid" name="receiver_id"
                                            id="receiver_id" required>
                                            <option value="">Pilih Penerima</option>
                                            @foreach ($admins as $admin)
                                            <option value="{{ $admin->id }}" {{ @$cashflow->receiver_id == $admin->id
                                                ? 'selected' : '' }}>
                                                {{ $admin->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="amount">
                                            <span class="required">Nominal</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nominal"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money"
                                                name="amount" id="amount" value="{{ @$cashflow->amount }}"
                                                placeholder="Masukkan Nominal" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="date">
                                            <span class="required">Tanggal</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Tanggal"></i>
                                        </label>
                                        <input type="date" class="form-control form-control-solid" name="date" id="date"
                                            value="{{ @$cashflow->date }}" placeholder="Masukkan Tanggal" required />
                                    </div>
                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="payment_method">
                                            <span class="required">Metode Pembayaran</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Metode Pembayaran"></i>
                                        </label>
                                        <select class="form-select form-select-solid" name="payment_method"
                                            id="payment_method" required>
                                            <option value="">Pilih Metode Pembayaran</option>
                                            <option value="CASH" {{ @$cashflow->payment_method == 'CASH' ? 'selected' :
                                                ''
                                                }}>Tunai
                                            </option>
                                            <option value="TRANSFER" {{ @$cashflow->payment_method == 'TRANSFER' ?
                                                'selected' : '' }}>Transfer
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 fv-row mb-7">
                                        <label class="fs-6 fw-bold form-label mt-3" for="description">
                                            <span class="">Deskripsi</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Deskripsi"></i>
                                        </label>
                                        <textarea class="form-control form-control-solid" name="description" rows="5"
                                            id="description"
                                            placeholder="Masukkan Deskripsi">{{ @$cashflow->description }}</textarea>
                                    </div>


                                    <div class="col-md-6 fv-row mb-6">
                                        <x-form.image-upload label="Bukti Pembayaran" maxSize="2MB"
                                            name="proof_of_payment" :value="@$cashflow->proof_of_payment ?? null"
                                            nullable="1" />
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('cashflow.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
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
    // Initialize Select2 for the "Penerima" select
        $(document).ready(function() {
            $('#receiver_id').select2({
                placeholder: 'Pilih Penerima',
                allowClear: true
            });
        });
</script>
@endpush