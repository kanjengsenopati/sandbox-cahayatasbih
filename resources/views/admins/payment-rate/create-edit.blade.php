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

                    <!--begin::Category Card-->
                    <!--begin::Category Card-->
                    @if(isset($paymentRate))
                    <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                        <i class="ki-duotone ki-shield-cross fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Mode Edit Terbatas</h4>
                            <span>Perhatian: Kategori Tarif dan Sekolah tidak dapat diubah untuk menjaga konsistensi data. Jika ingin mengubahnya, silakan hapus dan buat baru.</span>
                        </div>
                    </div>
                    @endif

                    <div class="card shadow-sm rounded-4 border-0 mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bolder text-dark">Kategori Tarif</h3>
                        </div>
                        <div class="card-body pt-0">
                            <!-- Hidden Input for Edit Mode -->
                            @if(isset($paymentRate))
                                <input type="hidden" name="type" value="{{ $paymentRate->type }}">
                            @endif

                            <div class="row g-5" data-kt-buttons="true" data-kt-buttons-target="[data-kt-button='true']">
                                <!-- Regular Option -->
                                <div class="col-md-6">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-stack text-start p-6 {{ (!isset($paymentRate) || @$paymentRate->type == 'REGULAR') ? 'active' : '' }} {{ isset($paymentRate) ? 'disabled opacity-50 pe-none' : '' }}" data-kt-button="true">
                                        <div class="d-flex align-items-center me-2">
                                            <div class="form-check form-check-custom form-check-solid form-check-primary me-6">
                                                <input class="form-check-input" type="radio" name="type" value="REGULAR" {{ (!isset($paymentRate) || @$paymentRate->type == 'REGULAR') ? 'checked' : '' }} {{ isset($paymentRate) ? 'disabled' : '' }} />
                                            </div>
                                            <div class="flex-grow-1">
                                                <h2 class="d-flex align-items-center fs-3 fw-bolder flex-wrap">
                                                    Siswa Reguler
                                                </h2>
                                                <div class="fw-bold opacity-50">
                                                    Tarif untuk satu kelas penuh
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <!-- Transfer Option -->
                                <div class="col-md-6">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-warning d-flex flex-stack text-start p-6 {{ (@$paymentRate->type == 'TRANSFER') ? 'active' : '' }} {{ isset($paymentRate) ? 'disabled opacity-50 pe-none' : '' }}" data-kt-button="true">
                                        <div class="d-flex align-items-center me-2">
                                            <div class="form-check form-check-custom form-check-solid form-check-warning me-6">
                                                <input class="form-check-input" type="radio" name="type" value="TRANSFER" {{ (@$paymentRate->type == 'TRANSFER') ? 'checked' : '' }} {{ isset($paymentRate) ? 'disabled' : '' }} />
                                            </div>
                                            <div class="flex-grow-1">
                                                <h2 class="d-flex align-items-center fs-3 fw-bolder flex-wrap">
                                                    Siswa Pindahan
                                                </h2>
                                                <div class="fw-bold opacity-50">
                                                    Tarif untuk siswa spesifik
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="form-text mt-5" id="type_helper">
                                {{ (!isset($paymentRate) || @$paymentRate->type == 'REGULAR') ? 'Tarif akan diterapkan untuk semua siswa dalam kelas yang dipilih.' : 'Tarif hanya akan diterapkan untuk siswa tertentu yang dipilih.' }}
                            </div>
                        </div>
                    </div>
                    <!--end::Category Card-->

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
                            // Use academic year start_year/end_year if available, else default to current logic
                            $startYear = $billType->academicYear->start_year ?? date('Y');
                            $endYear = $billType->academicYear->end_year ?? ($startYear + 1);
                            @endphp

                            <div class="row">
                                @for ($i = 0; $i < 12; $i++)
                                    @php
                                    $month=($startMonth + $i - 1) % 12 + 1;
                                    $monthName=\Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F');
                                    
                                    // Logic: Jul-Dec uses Start Year, Jan-Jun uses End Year
                                    $defaultYear = $month >= 7 ? $startYear : $endYear;
                                    
                                    // Handle edit values (if modifying existing payment rate item)
                                    $existingItem = isset($paymentRate->paymentRateItems) 
                                        ? $paymentRate->paymentRateItems->where('month', $month)->first() 
                                        : null;
                                        
                                    $existingAmount = $existingItem->amount ?? '';
                                    $displayYear = $existingItem->year ?? $defaultYear;
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
                                                    <input type="number" class="form-control form-control-sm form-control-solid input-year text-center"
                                                        name="tahun_{{ $month }}" id="tahun_{{ $month }}"
                                                        value="{{ $displayYear }}" placeholder="Tahun" required />
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
                                            value="{{ $billType->academicYear->start_year ?? date('Y') }}" required>
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
                            
                            <!-- Alert Edit Mode -->
                            @if(isset($paymentRate))
                            <div class="alert alert-warning d-flex align-items-center p-4 mb-5">
                                <i class="ki-duotone ki-information fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-7">Mode Edit hanya untuk perubahan nominal. Untuk menambah/menghapus target, silakan buat baru atau hapus tarif ini.</span>
                                </div>
                            </div>
                            @endif

                            <!-- Sekolah -->
                            <div class="mb-5">
                                <label class="form-label fw-bold fs-6 text-gray-700">Sekolah</label>
                                <select name="school_id" class="form-select form-select-solid {{ isset($paymentRate) ? 'bg-light' : '' }}" id="school_id"
                                    data-control="select2" data-placeholder="Pilih Sekolah" {{ isset($paymentRate) ? 'disabled' : '' }}>
                                    <option></option>
                                    @php
                                        $selectedSchoolId = null;
                                        if (isset($paymentRate)) {
                                            if ($paymentRate->type == 'REGULAR' && $paymentRate->paymentRateClassrooms->isNotEmpty()) {
                                                $selectedSchoolId = $paymentRate->paymentRateClassrooms->first()->classroom->school_id ?? null;
                                            } elseif ($paymentRate->type == 'TRANSFER' && $paymentRate->paymentRateStudents->isNotEmpty()) {
                                                $selectedSchoolId = $paymentRate->paymentRateStudents->first()->student->classroom->school_id ?? null;
                                            }
                                        }
                                    @endphp
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" {{ ($selectedSchoolId == $school->id) ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @if(isset($paymentRate))
                                    <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">
                                @endif
                            </div>

                            <!-- EDIT MODE: READ-ONLY TARGETS -->
                            @if(isset($paymentRate))
                                <div class="mb-5 bg-light rounded p-4 border border-dashed border-gray-300">
                                    <label class="form-label fw-bold fs-6 text-gray-700 mb-2">Target Terpilih (Read-Only)</label>
                                    
                                    @if($paymentRate->type == 'REGULAR')
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($paymentRate->paymentRateClassrooms as $prClass)
                                                <span class="badge badge-primary fs-7">{{ $prClass->classroom->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($paymentRate->paymentRateStudents as $prStudent)
                                                <div class="d-flex align-items-center bg-white p-2 rounded border">
                                                    <div class="symbol symbol-30px me-3">
                                                        <div class="symbol-label fs-6 fw-bold bg-light-warning text-warning">
                                                            {{ substr($prStudent->student->name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-gray-800 fs-7">{{ $prStudent->student->name }}</span>
                                                        <span class="text-muted fs-8">{{ $prStudent->student->nis ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            
                            @else
                                <!-- CREATE MODE: INPUT TARGETS -->
                                <!-- Kelas Wrapper -->
                                <div class="mb-5" id="classroom_wrapper">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold fs-6 text-gray-700 mb-0">Kelas</label>
                                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-8" id="btn-select-all-classrooms">
                                            Select All
                                        </button>
                                    </div>
                                    <select name="classrooms[]" class="form-select form-select-solid"
                                        id="classroom_id" data-control="select2" data-close-on-select="false"
                                        data-placeholder="Ketik untuk mencari kelas..." data-allow-clear="true"
                                        multiple="multiple">
                                        @if(isset($classrooms))
                                        @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">
                                            {{ $classroom->name }}
                                        </option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <div class="form-text mt-2">
                                        Pilih satu atau lebih kelas yang akan dikenakan tarif ini.
                                    </div>
                                </div>

                                <!-- Student Wrapper -->
                                <div class="mb-5 d-none" id="student_wrapper">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold fs-6 text-gray-700 mb-0">Siswa</label>
                                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-8" id="btn-select-all-students">
                                            Select All
                                        </button>
                                    </div>
                                    <select name="students[]" class="form-select form-select-solid"
                                        id="student_id" data-control="select2" data-close-on-select="false"
                                        data-placeholder="Ketik untuk mencari siswa..." data-allow-clear="true"
                                        multiple="multiple">
                                    </select>
                                    <div class="form-text mt-2">
                                        Pilih siswa spesifik untuk tarif ini.
                                    </div>
                                </div>
                            @endif

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
        // 1. Handle School Change -> Fetch Classrooms AND Students
        $('#school_id').on('change', function() {
            var school_id = $(this).val();
            var classroomSelect = $('#classroom_id');
            var studentSelect = $('#student_id');

            // Clear current options
            classroomSelect.empty().trigger('change');
            studentSelect.empty().trigger('change');

            if (school_id) {
                // Fetch Classrooms
                axios.get("{{ route('payment-rate.get-classroom') }}", {
                        params: { school_id: school_id }
                    })
                    .then(function(response) {
                        if (response.data.length > 0) {
                            $.each(response.data, function(key, value) {
                                var newOption = new Option(value.name, value.id, false, false);
                                classroomSelect.append(newOption);
                            });
                            classroomSelect.trigger('change');
                        }
                    })
                    .catch(function(error) { console.error(error); });
                
                // Fetch Students
                var billTypeId = $('input[name="bill_type_id"]').val();
                axios.get("{{ route('payment-rate.get-student') }}", {
                        params: { 
                            school_id: school_id,
                            bill_type_id: billTypeId
                        }
                    })
                    .then(function(response) {
                         if (response.data.length > 0) {
                            $.each(response.data, function(key, value) {
                                var text = value.name + ' - ' + (value.nis ?? '');
                                var newOption = new Option(text, value.id, false, false);
                                studentSelect.append(newOption);
                            });
                            studentSelect.trigger('change');
                        }
                    })
                    .catch(function(error) { console.error(error); });
            }
        });

        // Toggle Classroom / Student based on Category Type
        $('input[name="type"]').on('change', function() {
            var type = $(this).val();
            if (type === 'REGULAR') {
                $('#classroom_wrapper').removeClass('d-none');
                $('#student_wrapper').addClass('d-none');
                $('#type_helper').text('Tarif akan diterapkan untuk semua siswa dalam kelas yang dipilih.');
                $('#classroom_id').prop('required', true);
                $('#student_id').prop('required', false);
            } else {
                 $('#classroom_wrapper').addClass('d-none');
                 $('#student_wrapper').removeClass('d-none');
                 $('#type_helper').text('Tarif hanya akan diterapkan untuk siswa tertentu yang dipilih.');
                 $('#classroom_id').prop('required', false);
                 $('#student_id').prop('required', true);
            }
        });

        // Trigger change on load to set initial state
        var initialType = $('input[name="type"]:checked').val();
        if (initialType) {
            $('input[name="type"][value="' + initialType + '"]').trigger('change');
        }

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

        // 4. Form Validation
        $('#payment-rate-form').on('submit', function(e) {
            // Check based on inputs availability
            var isMonthly = $('input[id^="bulan_"]').length > 0;
            var isValid = false;

            if (isMonthly) {
                // Check if at least one month has a value
                $('input[id^="bulan_"]').each(function() {
                    var val = $(this).val().replace(/\./g, ''); // Remove currency formatting if any
                    if (val && parseInt(val) > 0) {
                        isValid = true;
                        return false; // Break loop
                    }
                });
            } else {
                // Check if single price has value (Bebas Type)
                var val = $('#setPrice').val().replace(/\./g, '');
                if (val && parseInt(val) > 0) {
                    isValid = true;
                }
            }

            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    text: "Harap isi minimal 1 rincian pembayaran (Bulan atau Total Tagihan).",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, Mengerti",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
                return false;
            }
        });
    });
</script>
@endpush