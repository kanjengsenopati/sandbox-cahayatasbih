@extends('layouts.master', ['title' => 'Data Jenis Bayar'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Jenis Bayar</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('bill-type.index') }}">
                        <li class="text-muted">
                            Jenis Bayar
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
                        {{ request()->routeIs('bill-type.create') ? 'Tambah Jenis Bayar' : 'Edit Jenis Bayar' }}</li>
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
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="bill-type"
                                action="{{ (request()->routeIs('bill-type.create') || empty(@$billType->id)) ? route('bill-type.store') : route('bill-type.update', $billType->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <!--begin::Input group-->
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Step A: POS (Lembaga / UPT) -->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="bill_item_id">
                                                <span class="required">A. POS (Lembaga / UPT)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Pos Bayar Dasar / UPT Lembaga (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <x-form.bill-item :value="@$billType->bill_item_id"
                                                class="form-control form-control-solid" />
                                            <!--end::Input-->
                                        </div>

                                        <!-- Step B: Tahun Ajaran -->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="academic_year_id">
                                                <span class="required">B. Tahun Ajaran</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Tahun Ajaran (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <x-form.academic-year :value="@$billType->academic_year_id"
                                                class="form-control form-control-solid" />
                                            <!--end::Input-->
                                        </div>

                                        <!-- Step C: Nama Pembayaran -->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="name_select">
                                                <span class="required">C. Nama Pembayaran</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Nama Pembayaran (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <select name="name_select" id="name_select" class="form-select form-select-solid" required>
                                                <option value="">Pilih Nama Pembayaran</option>
                                                @foreach ($paymentNames as $pName)
                                                    <option value="{{ $pName }}" {{ (old('name_select') ?? @$billType->name) == $pName ? 'selected' : '' }}>
                                                        {{ $pName }}
                                                    </option>
                                                @endforeach
                                                <option value="Lainnya" {{ old('name_select') == 'Lainnya' || (@$billType && !in_array($billType->name, $paymentNames)) ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                        </div>

                                        <div class="fv-row mb-7" id="name_custom_container" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="name_custom">
                                                <span class="required">Jenis Baru (Nama Pembayaran Custom)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan nama jenis bayar custom (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" name="name_custom" id="name_custom" class="form-control form-control-solid"
                                                placeholder="Masukkan Jenis baru (contoh: Pendaftaran / Seragam)"
                                                value="{{ old('name_custom') ?? (@$billType && !in_array($billType->name, $paymentNames) ? $billType->name : '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <!-- Step D: Tipe Pembayaran -->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="type">
                                                <span class="required">D. Tipe Pembayaran</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Tipe Pembayaran Bulanan / Bebas (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <select name="type" id="type" class="form-select form-select-solid" required>
                                                <option value="">Pilih Tipe Pembayaran</option>
                                                <option value="MONTHLY" {{ old('type')=='MONTHLY' ? 'selected' : (@$billType->
                                                    type == 'MONTHLY' ? 'selected' : '') }}>Bulanan</option>
                                                <option value="OTHER" {{ old('type')=='OTHER' ? 'selected' : (@$billType->
                                                    type == 'OTHER' ? 'selected' : '') }}>Bebas (Insidental / Sekali Bayar)</option>
                                            </select>
                                        </div>

                                        <!-- Step E: Metode Input Nominal -->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="payment_input_type">
                                                <span class="required">E. Metode Input Nominal</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih metode input nominal Fix / Bebas (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <select name="payment_input_type" id="payment_input_type" class="form-select form-select-solid" required>
                                                <option value="FIXED" {{ (old('payment_input_type') ?? @$billType->payment_input_type) == 'FIXED' ? 'selected' : '' }}>Fix Amount (Nominal Tetap)</option>
                                                <option value="FREE" {{ (old('payment_input_type') ?? @$billType->payment_input_type) == 'FREE' ? 'selected' : '' }}>Nominal Bebas (Cicilan)</option>
                                            </select>
                                        </div>

                                        <!-- Step F: Bank Pembayaran -->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="billTypeBank">
                                                <span class="required">F. Bank Pembayaran</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Bank Pembayaran (Wajib)"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <select name="bank_ids[]" class="form-select form-select-solid mb-3" id="select2"
                                                data-control="select2" data-allow-clear="true" multiple="multiple" required>
                                                @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" @if (in_array(@$bank->id,
                                                    @$bankValue)) selected @endif>
                                                    {{ $bank->name ?? '' }} - {{ $bank->account_number ?? '' }} -
                                                    {{ $bank->account_name ?? '' }}</option>
                                                @endforeach
                                            </select>
                                            <div class="d-flex gap-3">
                                                <input type="checkbox" id="select-all">
                                                <label style="font-size: 14px;" class="cursor-pointer" for="select-all">Select
                                                    All</label>
                                            </div>
                                            <!--end::Input-->
                                        </div>

                                        <!-- Step G: Extra Checked Filter untuk Tipe Pembayaran Tagihan Custom -->
                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label mt-3">G. Fitur Extra Checked Filter (Tagihan Custom)</label>
                                            <div class="d-flex flex-column gap-3 mt-2">
                                                <div class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="use_wali_filter" id="use_wali_filter" value="1" 
                                                        {{ (old('use_wali_filter') ?? @$billType->use_wali_filter) ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-gray-700 cursor-pointer" for="use_wali_filter">
                                                        Aktifkan Filter Status Wali Siswa (Jamaah / Non Jamaah)
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="use_gender_filter" id="use_gender_filter" value="1" 
                                                        {{ (old('use_gender_filter') ?? @$billType->use_gender_filter) ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-gray-700 cursor-pointer" for="use_gender_filter">
                                                        Aktifkan Filter Jenis Kelamin (Santri Putra / Santri Putri)
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="use_custom_filter" id="use_custom_filter" value="1" 
                                                        {{ (old('use_custom_filter') ?? @$billType->use_custom_filter) ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-gray-700 cursor-pointer" for="use_custom_filter">
                                                        Aktifkan Filter Tagihan Custom (Contoh: Pendaftaran / Seragam / Insidental)
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="use_alumni_filter" id="use_alumni_filter" value="1" 
                                                        {{ (old('use_alumni_filter') ?? @$billType->use_alumni_filter) ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-gray-700 cursor-pointer" for="use_alumni_filter">
                                                        Aktifkan Filter Khusus Alumni (Lulusan SMP lanjut MA)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('bill-type.index') }}">
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
    $(document).ready(function() {
        $("#select2").select2();

        $("#select-all").click(function() {
            if ($("#select-all").is(':checked')) {
                $("#select2").find('option').prop("selected", true).trigger('change');
            } else {
                $("#select2").find('option').prop("selected", false).trigger('change');
            }
        });

        $('#select2').on('change', function() {
            let selected = $(this).val();
            if (!selected || selected.length === 0) {
                $("#select-all").prop('checked', false);
            }
        });

        function toggleCustomNameInput() {
            if ($('#name_select').val() === 'Lainnya') {
                $('#name_custom_container').show();
                $('#name_custom').attr('required', true);
            } else {
                $('#name_custom_container').hide();
                $('#name_custom').removeAttr('required');
            }
        }

        $('#name_select').on('change', toggleCustomNameInput);
        toggleCustomNameInput(); // run on load
    });
</script>
@endpush