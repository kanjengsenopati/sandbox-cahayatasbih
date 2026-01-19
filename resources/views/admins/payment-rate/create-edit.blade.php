@extends('layouts.master', ['title' => 'Tarif Pembayaran'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">

        <!--begin::Page Title-->
        <div class="d-flex flex-stack mb-5">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-50px me-3">
                    <span class="symbol-label bg-light-primary">
                        <i class="fas fa-money-check-alt text-primary fs-2"></i>
                    </span>
                </div>
                <div>
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        {{ request()->routeIs('payment-rate.create') ? 'Tambah Tarif Pembayaran' : 'Edit Tarif Pembayaran' }}
                    </h1>
                    <span class="text-muted fw-bold fs-7">
                        Konfigurasi tarif untuk: <span class="text-primary">{{ @$billType->name }}</span>
                    </span>
                </div>
            </div>

            <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('bill-type.index') }}" class="text-muted text-hover-primary">Jenis Bayar</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-300 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-dark">
                    {{ request()->routeIs('payment-rate.create') ? 'Tambah' : 'Edit' }}
                </li>
            </ul>
        </div>
        <!--end::Page Title-->

        <form id="payment-rate-form"
            action="{{ request()->routeIs('payment-rate.create') ? route('payment-rate.store') : route('payment-rate.update', @$paymentRate->id) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <x-form.put-method />

            <div class="row g-5">
                <!--begin::Left Column (Main Configuration)-->
                <div class="col-lg-8">
                    <!--begin::Info Card-->
                    <div class="card shadow-sm rounded-4 border-0 mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bolder text-dark">Informasi Dasar</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-wrap gap-5">
                                <div class="d-flex align-items-center bg-light-info rounded p-3 flex-grow-1">
                                    <i class="fas fa-file-invoice fs-1 text-info me-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-600 fs-7">Nama Pembayaran</span>
                                        <span class="fw-bolder text-dark fs-5">{{ @$billType->name }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center bg-light-warning rounded p-3 flex-grow-1">
                                    <i class="fas fa-calendar-alt fs-1 text-warning me-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-600 fs-7">Tahun Ajaran</span>
                                        <span class="fw-bolder text-dark fs-5">{{ @$billType->academicYear->name }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center bg-light-success rounded p-3 flex-grow-1">
                                    <i class="fas fa-tag fs-1 text-success me-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-600 fs-7">Tipe</span>
                                        <span class="fw-bolder text-dark fs-5">
                                            {{ @$billType->type == "MONTHLY" ? 'Bulanan (SPP)' : 'Bebas (Sekali Bayar)' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Info Card-->

                    <!--begin::Nominal Card-->
                    <div class="card shadow-sm rounded-4 border-0 mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bolder text-dark">Konfigurasi Nominal</h3>
                        </div>
                        <div class="card-body pt-0">
                            <!-- Hidden Bill Type ID -->
                            <input type="hidden" name="bill_type_id" value="{{ @$billType->id }}">

                            @if ($billType->type == "MONTHLY")
                            <!-- MONTHLY TYPE -->
                            <div class="mb-8">
                                <label class="form-label fs-6 fw-bolder text-gray-700">Tarif Bulanan Standar</label>
                                <div class="input-group input-group-solid mb-2">
                                    <span class="input-group-text border-0 ms-2">Rp</span>
                                    <input type="text" class="form-control form-control-solid input-money ps-2"
                                        name="price" id="setPrice" value="{{ @$paymentRate->amount ?? 0 }}" placeholder="0" />
                                </div>
                                <div class="form-text text-muted">
                                    Masukkan nominal di sini lalu tekan <strong>Enter</strong> atau klik di luar untuk otomatis mengisi semua bulan di bawah.
                                </div>
                            </div>

                            <div class="separator separator-dashed my-6"></div>

                            <h4 class="fw-bolder text-gray-800 mb-4">Rincian Per Bulan</h4>
                            <div class="alert alert-dismissible bg-light-primary border border-primary border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
                                <i class="ki-duotone ki-information-2 fs-2hx text-primary me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <h5 class="mb-1">Fleksibilitas Tarif</h5>
                                    <span>Anda dapat mengubah nominal untuk bulan tertentu jika berbeda (misal: ada potongan di bulan Juli).</span>
                                </div>
                            </div>

                            @php
                            setlocale(LC_TIME, 'id_ID');
                            $startMonth = 7; // Juli
                            $year = date('Y');
                            @endphp

                            <div class="row">
                                @for ($i = 0; $i < 12; $i++)
                                    @php
                                    $month=($startMonth + $i - 1) % 12 + 1;
                                    $monthName=\Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F');
                                    $displayYear = $month >= 7 ? $year : $year + 1;
                                    // Handle edit values
                                    $existingAmount = isset($paymentRate->paymentRateItems)
                                    ? ($paymentRate->paymentRateItems->where('month', $month)->first()->amount ?? '')
                                    : '';
                                    @endphp
                                    <div class="col-md-6 mb-4">
                                        <div class="bg-light rounded p-4 border border-gray-200">
                                            <label class="form-label fs-6 fw-bold text-gray-800 mb-2">{{ $monthName }}</label>
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <div class="input-group input-group-sm input-group-solid">
                                                        <span class="input-group-text border-0">Rp</span>
                                                        <input type="text" class="form-control form-control-solid input-money"
                                                            name="bulan_{{ $month }}" id="bulan_{{ $month }}"
                                                            value="{{ $existingAmount }}" placeholder="Nominal" />
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <input type="text" class="form-control form-control-sm form-control-solid input-year text-center"
                                                        name="tahun_{{ $month }}" id="tahun_{{ $month }}"
                                                        value="{{ $displayYear }}" readonly />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                            </div>

                            @else
                            <!-- FREE TYPE -->
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-bold text-dark mb-2">Total Tagihan</label>
                                <div class="input-group input-group-solid input-group-lg">
                                    <span class="input-group-text border-0">Rp</span>
                                    <input type="text" class="form-control form-control-solid input-money fs-3 fw-bolder"
                                        name="price" id="setPrice" value="{{ @$paymentRate->amount ?? 0 }}" placeholder="0" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-5">
                                        <label for="month" class="form-label fs-6 fw-bold text-gray-700">Berlaku Untuk Bulan</label>
                                        <select name="months[]" id="month" class="form-select form-select-solid"
                                            data-control="select2" data-placeholder="Pilih Bulan..." multiple="multiple" required>
                                            @php
                                            $indonesianMonths = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                            @endphp
                                            @foreach ($indonesianMonths as $key => $monthName)
                                            <option value="{{ $key + 1 }}">{{ $monthName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-5">
                                        <label for="year" class="form-label fs-6 fw-bold text-gray-700">Tahun</label>
                                        <input type="number" name="year" id="year"
                                            class="form-control form-control-solid" placeholder="Tahun"
                                            value="{{ date('Y') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center p-5 mt-4">
                                <i class="ki-duotone ki-shield-tick fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-dark">Informasi</h4>
                                    <span>Tagihan tipe Bebas ini akan digenerate sekali sesuai nominal yang diinputkan.</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Left Column-->

                <!--begin::Right Column (Target & Actions)-->
                <div class="col-lg-4">
                    <!--begin::Target Card-->
                    <div class="card shadow-sm rounded-4 border-0 mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bolder text-dark">Target Pembayaran</h3>
                        </div>
                        <div class="card-body pt-0">
                            <!-- Sekolah -->
                            <div class="mb-5">
                                <label class="form-label fw-bold fs-6 text-gray-700">Sekolah</label>
                                <select name="school_id" class="form-select form-select-solid" id="school_id"
                                    data-control="select2" data-placeholder="Pilih Sekolah">
                                    <option></option>
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" {{ (isset($paymentRate) && $paymentRate->paymentRateClassrooms->first()->classroom->school_id == $school->id) ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Kelas -->
                            <div class="mb-5">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold fs-6 text-gray-700 mb-0">Kelas</label>
                                    <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-8" id="btn-select-all-classrooms">
                                        Select All
                                    </button>
                                </div>
                                <select name="classrooms[]" class="form-select form-select-solid"
                                    id="classroom_id" data-control="select2" data-close-on-select="false"
                                    data-placeholder="Ketik untuk mencari kelas..." data-allow-clear="true"
                                    multiple="multiple" required>
                                    @if(isset($classrooms))
                                    @foreach ($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}"
                                        @if (in_array($classroom->id, $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray())) selected @endif>
                                        {{ $classroom->name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="form-text mt-2">
                                    Pilih satu atau lebih kelas yang akan dikenakan tarif ini.
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Target Card-->

                    <!--begin::Action Card-->
                    <div class="card shadow-sm rounded-4 border-0">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 py-3 fs-4 fw-bolder hover-scale" id="kt_invoice_submit_button">
                                <i class="fas fa-save me-2"></i> Simpan Tarif
                            </button>
                            <a href="{{ route('bill-type.index') }}" class="btn btn-light w-100 py-3 mt-3 fs-6 fw-bold">
                                Batal
                            </a>
                        </div>
                    </div>
                    <!--end::Action Card-->
                </div>
                <!--end::Right Column-->
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {

        // 1. Handle School Change -> Fetch Classrooms
        $('#school_id').on('change', function() {
            var school_id = $(this).val();
            var classroomSelect = $('#classroom_id');

            // Clear current options
            classroomSelect.empty().trigger('change');

            if (school_id) {
                axios.get("{{ route('payment-rate.get-classroom') }}", {
                        params: {
                            school_id: school_id
                        }
                    })
                    .then(function(response) {
                        if (response.data.length > 0) {
                            $.each(response.data, function(key, value) {
                                var newOption = new Option(value.name, value.id, false, false);
                                classroomSelect.append(newOption);
                            });
                            classroomSelect.trigger('change');
                        } else {
                            // Optional: notify no classes found
                        }
                    })
                    .catch(function(error) {
                        console.error(error);
                        Swal.fire({
                            text: "Gagal mengambil data kelas.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    });
            }
        });

        // 2. Handle 'Select All' Classrooms
        $('#btn-select-all-classrooms').click(function() {
            var select = $('#classroom_id');
            if (select.find('option').length === 0) {
                return;
            }

            var allValues = [];
            select.find('option').each(function() {
                allValues.push($(this).val());
            });

            // Check if all are currently selected
            var currentSelection = select.val() || [];
            if (currentSelection.length === allValues.length) {
                // Deselect all
                select.val(null).trigger('change');
                $(this).text('Select All');
            } else {
                // Select all
                select.val(allValues).trigger('change');
                $(this).text('Deselect All');
            }
        });

        // Toggle button text based on selection
        $('#classroom_id').on('change', function() {
            var select = $(this);
            var totalOptions = select.find('option').length;
            var selectedOptions = (select.val() || []).length;

            if (totalOptions > 0 && totalOptions === selectedOptions) {
                $('#btn-select-all-classrooms').text('Deselect All');
            } else {
                $('#btn-select-all-classrooms').text('Select All');
            }
        });

        // 3. Handle Auto-Fill Months when Main Price changes
        $('#setPrice').on('change keyup', function() {
            var price = $(this).val();
            // Loop through inputs with id pattern 'bulan_X'
            $('[id^="bulan_"]').val(price);
        });
    });
</script>
@endpush