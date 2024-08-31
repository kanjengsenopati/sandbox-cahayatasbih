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
                    <a class="breadcrumb-item" href="{{ route('bill-item.index') }}">
                        <li class="text-muted">Pos Bayar</li>
                    </a>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('payment-rate.create') ? 'Tambah Pos Bayar' : 'Edit Pos Bayar' }}
                    </li>
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
                                        <div class="d-flex flex-center flex-equal fw-row text-nowrap order-1 order-xxl-2 me-4"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover"
                                            title="Nama Jenis Pembayaran">
                                            <span class="fs-2x fw-bolder text-gray-800">{{ @$billType->name }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end flex-equal order-3 fw-row"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover">
                                            <div class="fs-6 fw-bolder text-gray-700 text-nowrap">Tahun Ajaran : {{
                                                @$billType->academicYear->name }}</div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end flex-equal order-3 fw-row"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover">
                                            <div class="fs-6 fw-bolder text-gray-700 text-nowrap">Tipe Bayar : {{
                                                @$billType->type == "MONTHLY" ? 'Bulanan' : 'Bebas' }}</div>
                                        </div>
                                    </div>
                                    @if ($billType->type == "MONTHLY")
                                    <div class="mb-0">
                                        <div class="mb-0"></div>
                                        <div class="separator separator-dashed my-10"></div>
                                        <div class="mb-0">
                                            <label class="form-label fs-6 fw-bolder text-gray-700">Tarif Setiap Bulan
                                                Sama</label>
                                            <div class="input-group mb-5">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control form-control-solid input-money"
                                                    name="price" id="setPrice" value="{{ @$paymentRate->amount }}" />
                                            </div>
                                            <input type="hidden" name="bill_type_id" value="{{ @$billType->id }}">
                                            <div class="alert alert-primary d-flex align-items-center p-5 mt-10 mb-10">
                                                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span
                                                        class="path1"></span><span class="path2"></span></i>
                                                <div class="d-flex flex-column">
                                                    <h4 class="mb-1 text-dark">Info</h4>
                                                    <span>Masukkan Nilai Harga pada kolom diatas dan Tekan Enter untuk
                                                        menentukan harga setiap bulan</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row gx-10 mb-5">
                                            <div class="col-lg-12">
                                                <label class="form-label fs-6 fw-bolder text-gray-700 mb-3">Tarif Setiap
                                                    Bulan Tidak Sama</label>
                                                @php
                                                setlocale(LC_TIME, 'id_ID');
                                                $startMonth = 7; // Juli
                                                $year = date('Y'); // Tahun saat ini
                                                @endphp
                                                @for ($i = 0; $i < 12; $i++) @php $month=($startMonth + $i - 1) % 12 +
                                                    1; $monthName=Carbon\Carbon::createFromDate(null, $month, 1)->
                                                    translatedFormat('F');
                                                    $displayYear=$month>= 7 ? $year : $year + 1;
                                                    @endphp
                                                    <div class="row mb-5">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="bulan_{{ $month }}"
                                                                class="form-label fs-6 text-gray-700">{{ $monthName
                                                                }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="text"
                                                                    class="form-control form-control-solid input-money"
                                                                    name="bulan_{{ $month }}" id="bulan_{{ $month }}"
                                                                    placeholder="{{ $monthName }}"
                                                                    value="{{ @$paymentRate->paymentRateItems->where('month', $month)->first()->amount }}" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="tahun_{{ $month }}"
                                                                class="form-label fs-6 text-gray-700">Tahun</label>
                                                            <div class="input-group">
                                                                <input type="text"
                                                                    class="form-control form-control-solid input-year"
                                                                    name="tahun_{{ $month }}" id="tahun_{{ $month }}"
                                                                    placeholder="{{ $displayYear }}"
                                                                    value="{{ $displayYear }}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endfor
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="mb-0 mt-4">
                                        <label class="form-label fs-5 text-gray-700">Tarif </label>
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
                                                <span>Masukkan Bulan (Bisa Lebih dari 1) dan Tahun untuk menentukan
                                                    Tagihan</span>
                                                <!--end::Content-->
                                            </div>
                                            <!--end::Wrapper-->
                                        </div>
                                        <!--end::Alert-->

                                    </div>
                                    <!--end::Notes-->
                                    <!--begin::Row-->
                                    <div class="row gx-10 mb-5">
                                        <!-- Col for Month -->
                                        <div class="col-lg-6">
                                            <!-- Input group for Month -->
                                            <div class="mb-5">
                                                <label for="month" class="form-label fs-6 text-gray-700">Bulan</label>
                                                <select name="months[]" id="month" class="form-select form-select-solid"
                                                    data-control="select2" data-allow-clear="true" multiple="multiple"
                                                    required>
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
                                                    @foreach ($indonesianMonths as $key => $month)
                                                    <option value="{{ $key + 1 }}">{{ $month }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- End Input group for Month -->
                                        </div>
                                        <!-- End Col for Month -->

                                        <!-- Col for Year -->
                                        <div class="col-lg-6">
                                            <!-- Input group for Year -->
                                            <div class="mb-5">
                                                <label for="year" class="form-label fs-6 text-gray-700">Tahun</label>
                                                <input type="text" name="year" id="year"
                                                    class="form-control form-control-solid" placeholder="Masukkan Tahun"
                                                    value="{{ date('Y') }}" required>
                                            </div>
                                            <!-- End Input group for Year -->
                                        </div>
                                        <!-- End Col for Year -->
                                    </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end::Content-->
                    <!--begin::Sidebar-->
                    <div class="flex-lg-auto min-w-lg-300px">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card body-->
                            <div class="card-body p-10">
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="form-label fw-bolder fs-6 text-gray-700">Sekolah</label>
                                    <select name="school" class="form-select form-select-solid mb-3" id="school_id"
                                        data-control="select2" aria-placeholder="Pilih Sekolah">
                                        <option value="" selected>Pilih Sekolah</option>
                                        @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-10">
                                    <label class="form-label fw-bolder fs-6 text-gray-700">Kelas</label>
                                    @if (request()->routeIs('payment-rate.create'))
                                    <select name="classrooms[]" class="form-select form-select-solid mb-3"
                                        id="classroom_id" data-control="select2" data-allow-clear="true"
                                        aria-placeholder="Pilih Kelas" multiple="multiple" required>
                                    </select>
                                    @else
                                    <select name="classrooms[]" class="form-select form-select-solid mb-3"
                                        id="classroom_id" data-control="select2" data-allow-clear="true"
                                        aria-placeholder="Pilih Kelas" multiple="multiple" required>
                                        @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}" @if (in_array($classroom->id,
                                            $paymentRate?->paymentRateClassrooms?->pluck('classroom_id')->toArray()))
                                            selected
                                            @endif>
                                            {{ $classroom->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                                <div class="separator separator-dashed mb-8"></div>
                                <div class="mb-0">
                                    <button type="submit" class="btn btn-primary w-100" id="kt_invoice_submit_button">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Sidebar-->
                </div>
                <!--end::Layout-->
            </form>
        </div>
    </div>
</div>
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