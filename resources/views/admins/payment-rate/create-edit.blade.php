@extends('layouts.master', ['title' => 'Tarif Pembayaran'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Tarif Pembayaran</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('bill-item.index') }}">
                        <li class="text-muted">
                            Pos Bayar
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
                        {{ request()->routeIs('payment-rate.create') ? 'Tambah Pos Bayar' : 'Edit Pos Bayar' }}</li>
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
        <div id="kt_content_container" class="container-xxl">
            <form id="payment-rate"
                action="{{ request()->routeIs('payment-rate.create') ? route('payment-rate.store') : route('payment-rate.update', @$paymentRate->id) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <x-form.put-method />
                <!--begin::Layout-->
                <div class="d-flex flex-column flex-lg-row">
                    <!--begin::Content-->
                    <div class="flex-lg-row-fluid mb-10 mb-lg-0 me-lg-7 me-xl-10">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card body-->
                            <div class="card-body p-12">
                                <!--begin::Form-->
                                <form action="" id="kt_invoice_form">
                                    <!--begin::Wrapper-->
                                    <div class="d-flex flex-column align-items-start flex-xxl-row">
                                        <!--begin::Input group-->
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="d-flex flex-center flex-equal fw-row text-nowrap order-1 order-xxl-2 me-4"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover"
                                            title="Nama Jenis Pembayaran">
                                            <span class="fs-2x fw-bolder text-gray-800">{{ @$billType->name }}</span>
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="d-flex align-items-center justify-content-end flex-equal order-3 fw-row"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover">
                                            <!--begin::Date-->
                                            <div class="fs-6 fw-bolder text-gray-700 text-nowrap">Tahun Ajaran : {{
                                                @$billType->academicYear->name }}</div>
                                            <!--end::Date-->
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end flex-equal order-3 fw-row"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover">
                                            <!--begin::Date-->
                                            <div class="fs-6 fw-bolder text-gray-700 text-nowrap">Tipe Bayar : {{
                                                @$billType->type == "MONTHLY" ? 'Bulanan' : 'Bebas' }}</div>
                                            <!--end::Date-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <!--end::Top-->
                                    <!--begin::Separator-->
                                    @if ($billType->type == "MONTHLY")
                                    <div class="mb-0">
                                        <!--begin::Notes-->
                                        <div class="mb-0">
                                        </div>
                                        <div class="separator separator-dashed my-10"></div>
                                        <div class="mb-0">
                                            <label class="form-label fs-6 fw-bolder text-gray-700">Tarif Setiap Bulan
                                                Sama</label>
                                            <div class="input-group mb-5">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control form-control-solid input-money"
                                                    name="price" id="setPrice" value="{{ @$billType->price }}" />
                                            </div>
                                            <input type="hidden" name="bill_type_id" value="{{ @$billType->id }}">
                                            <div class="alert alert-primary d-flex align-items-center p-5 mt-10 mb-10">
                                                <!--begin::Icon-->
                                                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span
                                                        class="path1"></span><span class="path2"></span></i>
                                                <!--end::Icon-->

                                                <!--begin::Wrapper-->
                                                <div class="d-flex flex-column">
                                                    <!--begin::Title-->
                                                    <h4 class="mb-1 text-dark">Info</h4>
                                                    <!--end::Title-->

                                                    <!--begin::Content-->
                                                    <span>Masukkan Nilai Harga pada kolom diatas dan Tekan Enter untuk
                                                        menentukan harga setiap bulan</span>
                                                    <!--end::Content-->
                                                </div>
                                                <!--end::Wrapper-->
                                            </div>
                                            <!--end::Alert-->

                                        </div>
                                        <!--end::Notes-->
                                        <!--begin::Row-->
                                        <div class="row gx-10 mb-5">
                                            <!--begin::Col-->
                                            <div class="col-lg-12">
                                                <label class="form-label fs-6 fw-bolder text-gray-700 mb-3">Tarif Setiap
                                                    Bulan Tidak Sama</label>
                                                <!--begin::Input group-->
                                                @for ($i = 1; $i <= 12; $i++) <div class="mb-5">
                                                    <label for="bulan_{{ $i }}" class="form-label fs-6 text-gray-700">{{
                                                        date('F', mktime(0, 0, 0, $i, 1)) }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text"
                                                            class="form-control form-control-solid input-money"
                                                            name="{{ $i }}" id="bulan_{{ $i }}"
                                                            placeholder="{{ date('F', mktime(0, 0, 0, $i, 1)) }}" />
                                                    </div>
                                            </div>
                                            @endfor
                                            <!--end::Input group-->
                                        </div>
                                    </div>
                                    <!--end::Col-->
                                    @else
                                    <div class="mb-0">
                                        <!--begin::Notes-->
                                        <div class="mb-0">
                                        </div>
                                        <div class="separator separator-dashed my-10"></div>
                                        <div class="mb-0">
                                            <label class="form-label fs-6 fw-bolder text-gray-700">Tarif </label>
                                            <div class="input-group mb-5">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control form-control-solid input-money"
                                                    name="price" id="setPrice" value="{{ @$billType->price }}" />
                                            </div>
                                            <input type="hidden" name="bill_type_id" value="{{ @$billType->id }}">
                                            <div class="alert alert-primary d-flex align-items-center p-5 mt-10 mb-10">
                                                <!--begin::Icon-->
                                                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span
                                                        class="path1"></span><span class="path2"></span></i>
                                                <!--end::Icon-->

                                                <!--begin::Wrapper-->
                                                <div class="d-flex flex-column">
                                                    <!--begin::Title-->
                                                    <h4 class="mb-1 text-dark">Info</h4>
                                                    <!--end::Title-->

                                                    <!--begin::Content-->
                                                    <span>Masukkan Nilai Harga pada kolom diatas dan Tekan Enter untuk
                                                        menentukan harga setiap bulan</span>
                                                    <!--end::Content-->
                                                </div>
                                                <!--end::Wrapper-->
                                            </div>
                                            <!--end::Alert-->

                                        </div>
                                        <!--end::Notes-->
                                        <!--begin::Row-->
                                        <div class="row gx-10 mb-5">
                                            <!--begin::Col-->
                                            <div class="col-lg-12">
                                                <!--begin::Input group-->
                                                <div class="mb-5">
                                                    <label for="month"
                                                        class="form-label fs-6 text-gray-700">Bulan</label>
                                                    <select name="month" id="month"
                                                        class="form-select form-select-solid" required>
                                                        <option value="" selected>Pilih Bulan</option>
                                                        @php
                                                        $indonesianMonths = [
                                                        'Januari',
                                                        'Februari',
                                                        'Maret',
                                                        'April',
                                                        'Mei',
                                                        'Juni',
                                                        'Juli',
                                                        'Agustus',
                                                        'September',
                                                        'Oktober',
                                                        'November',
                                                        'Desember',
                                                        ];
                                                        @endphp
                                                        @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">{{
                                                            $indonesianMonths[$i - 1] }}</option>
                                                            @endfor
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                            </div>
                                        </div>
                                        <!--end::Col-->
                                        @endif
                                    </div>
                                    <!--end::Wrapper-->
                                </form>
                                <!--end::Form-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Content-->
                    <!--begin::Sidebar-->
                    <div class="flex-lg-auto min-w-lg-300px">
                        <!--begin::Card-->
                        <div class="card" data-kt-sticky="true" data-kt-sticky-name="invoice"
                            data-kt-sticky-offset="{default: false, lg: '200px'}"
                            data-kt-sticky-width="{lg: '250px', lg: '300px'}" data-kt-sticky-left="auto"
                            data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                            <!--begin::Card body-->
                            <div class="card-body p-10">
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-bolder fs-6 text-gray-700">Sekolah</label>
                                    <!--end::Label-->
                                    <!--begin::Select-->
                                    <select name="school" class="form-select form-select-solid mb-3" id="school_id"
                                        data-control="select2" aria-placeholder="Pilih Sekolah" required>
                                        <option value="" selected>Pilih Sekolah</option>
                                        @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">
                                            {{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Select-->
                                </div>

                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-bolder fs-6 text-gray-700">Kelas</label>
                                    <!--end::Label-->
                                    <!--begin::Select-->
                                    <select name="classrooms[]" class="form-select form-select-solid mb-3"
                                        id="classroom_id" data-control="select2" data-allow-clear="true"
                                        aria-placeholder="Pilih Kelas" multiple="multiple" required>
                                    </select>
                                    <!--end::Select-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed mb-8"></div>
                                <!--begin::Actions-->
                                <div class="mb-0">
                                    <button type="submit" class="btn btn-primary w-100" id="kt_invoice_submit_button">
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen016.svg-->
                                        <span class="svg-icon svg-icon-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z"
                                                    fill="currentColor" />
                                                <path opacity="0.3"
                                                    d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z"
                                                    fill="currentColor" />
                                            </svg>
                                        </span>
                                        <!--end::Svg Icon-->Send Invoice
                                    </button>
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
            </form>
            <!--end::Sidebar-->
        </div>
        <!--end::Layout-->
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
    $(document).ready(function(){
        $('#school_id').on('change', function(){
            var school_id = $(this).val();
            axios.get('{{ route('payment-rate.get-classroom') }}', {
                params: {
                    school_id: school_id
                }
            })
            .then(function (response) {
                // handle success
                $('#classroom_id').empty();
                if (response.data.length > 0) {
                    // Jika data kelas ada
                    $.each(response.data, function (key, value) {
                        $('#classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                } else {
                    // Jika data kelas kosong
                    $('#classroom_id').append('<option value="" disabled selected>Maaf, Kelas Tidak Ada</option>');
                }
            })
            .catch(function (error) {
                // handle error
                console.log(error);
            });
        });

    // if #setPrice change sett all month price value
    $('#setPrice').on('change', function(){
        var price = $(this).val();
        for (let i = 1; i <= 12; i++) {
            $('#bulan_'+i).val(price);
        }
    });
    });
</script>
@endpush