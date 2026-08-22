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
            action="{{ isset($paymentRate) ? route('payment-rate.update', $paymentRate->id) : route('payment-rate.store') }}"
            method="POST">
            @csrf
            @if (isset($paymentRate))
                @method('PUT')
            @endif

            @php
                $isMatrixMode = !isset($paymentRate) && $billType->use_wali_filter && $billType->use_alumni_filter;
            @endphp

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
                                <!-- Regular Option (Siswa Umum) -->
                                <div class="col-md-6">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-stack text-start p-6 {{ (!isset($paymentRate) || @$paymentRate->type == 'REGULAR') ? 'active' : '' }} {{ isset($paymentRate) ? 'disabled opacity-50 pe-none' : '' }}" data-kt-button="true">
                                        <div class="d-flex align-items-center me-2">
                                            <div class="form-check form-check-custom form-check-solid form-check-primary me-6">
                                                <input class="form-check-input" type="radio" name="type" value="REGULAR" {{ (!isset($paymentRate) || @$paymentRate->type == 'REGULAR') ? 'checked' : '' }} {{ isset($paymentRate) ? 'disabled' : '' }} />
                                            </div>
                                            <div class="flex-grow-1">
                                                <h2 class="d-flex align-items-center fs-3 fw-bolder text-gray-900 mb-1">
                                                    Siswa Umum
                                                </h2>
                                                <div class="fs-7 fw-normal text-gray-600" style="color: #5e6278 !important;">
                                                    Tarif untuk satu kelas penuh / kriteria umum
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <!-- Transfer Option (Siswa Khusus) -->
                                <div class="col-md-6">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-warning d-flex flex-stack text-start p-6 {{ (@$paymentRate->type == 'TRANSFER') ? 'active' : '' }} {{ isset($paymentRate) ? 'disabled opacity-50 pe-none' : '' }}" data-kt-button="true">
                                        <div class="d-flex align-items-center me-2">
                                            <div class="form-check form-check-custom form-check-solid form-check-warning me-6">
                                                <input class="form-check-input" type="radio" name="type" value="TRANSFER" {{ (@$paymentRate->type == 'TRANSFER') ? 'checked' : '' }} {{ isset($paymentRate) ? 'disabled' : '' }} />
                                            </div>
                                            <div class="flex-grow-1">
                                                <h2 class="d-flex align-items-center fs-3 fw-bolder text-gray-900 mb-1">
                                                    Siswa Khusus
                                                </h2>
                                                <div class="fs-7 fw-normal text-gray-600" style="color: #5e6278 !important;">
                                                    Tarif khusus / custom untuk siswa tertentu
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

                            <!-- ========================================== -->
                            <!-- SECTION 1: SISWA UMUM (REGULAR) WRAPPER    -->
                            <!-- ========================================== -->
                            <div id="regular_nominal_wrapper">
                                @if($isMatrixMode)
                                    <!-- MATRIX PRICING UI -->
                                    <input type="hidden" name="is_matrix" value="1">
                                    <div class="mb-10">
                                        <div class="alert alert-primary d-flex align-items-center p-5 mb-10">
                                            <i class="ki-duotone ki-information fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            <div class="d-flex flex-column">
                                                <h4 class="mb-1 text-primary">Matriks Tarif Pintar</h4>
                                                <span>Sistem mendeteksi bahwa tagihan ini menggunakan filter Alumni & Wali. Silakan isi matriks di bawah ini, sistem akan otomatis men-generate 4 tarif terpisah.</span>
                                            </div>
                                        </div>

                                        <div class="table-responsive border rounded">
                                            <table class="table table-row-bordered table-striped align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="fw-bold text-gray-700 px-3 w-25">Status Alumni</th>
                                                        <th class="fw-bold text-gray-700 px-3 w-25">Status Wali</th>
                                                        <th class="fw-bold text-gray-700 px-3 min-w-200px">Nominal Tarif (Rp)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Row 1: Jamaah -->
                                                    <tr>
                                                        <td rowspan="3" class="align-middle fw-bold text-dark bg-white border-bottom px-3">Khusus Siswa Baru<br><span class="text-muted fs-7 fw-normal">(Non-Alumni)</span></td>
                                                        <td class="bg-white px-3">Jamaah</td>
                                                        <td class="bg-white px-3">
                                                            <div class="input-group input-group-sm input-group-solid">
                                                                <span class="input-group-text border-0">Rp</span>
                                                                <input type="text" class="form-control form-control-sm form-control-solid input-money" name="matrix_price[NON_ALUMNI][JAMAAH]" placeholder="0" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Row 2: Non Jamaah -->
                                                    <tr>
                                                        <td class="bg-white px-3">Non Jamaah</td>
                                                        <td class="bg-white px-3">
                                                            <div class="input-group input-group-sm input-group-solid">
                                                                <span class="input-group-text border-0">Rp</span>
                                                                <input type="text" class="form-control form-control-sm form-control-solid input-money" name="matrix_price[NON_ALUMNI][NON_JAMAAH]" placeholder="0" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Row 3: Mukimin -->
                                                    <tr>
                                                        <td class="bg-white border-bottom px-3">Mukimin</td>
                                                        <td class="bg-white border-bottom px-3">
                                                            <div class="input-group input-group-sm input-group-solid">
                                                                <span class="input-group-text border-0">Rp</span>
                                                                <input type="text" class="form-control form-control-sm form-control-solid input-money" name="matrix_price[NON_ALUMNI][MUKIMIN]" placeholder="0" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Row 4: Alumni -->
                                                    <tr>
                                                        <td class="fw-bold text-primary bg-light-primary border-bottom px-3">Khusus Alumni<br><span class="text-muted fs-7 fw-normal">(Lulusan SMP -> MA)</span></td>
                                                        <td class="text-muted fst-italic bg-light-primary border-bottom px-3">Semua Status Wali</td>
                                                        <td class="bg-light-primary border-bottom px-3">
                                                            <div class="input-group input-group-sm input-group-solid border border-primary">
                                                                <span class="input-group-text border-0 bg-transparent text-primary">Rp</span>
                                                                <input type="text" class="form-control form-control-sm form-control-solid input-money bg-transparent" name="matrix_price[ALUMNI_SMP_MA][ALL]" placeholder="0" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Hide Months / Year if it's FREE TYPE -->
                                    @if ($billType->type != "MONTHLY")
                                        <div class="row mt-8">
                                            <div class="col-lg-6">
                                                <div class="mb-5">
                                                    <label for="month" class="form-label fs-6 fw-bold text-gray-700">Berlaku Untuk Bulan</label>
                                                    <select name="months[]" class="form-select form-select-solid"
                                                        data-control="select2" data-placeholder="Pilih Bulan..." multiple="multiple">
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
                                                    <input type="number" name="year"
                                                        class="form-control form-control-solid" placeholder="Tahun"
                                                        value="{{ $billType->academicYear->start_year ?? date('Y') }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    @if ($billType->type == "MONTHLY")
                                        <!-- MONTHLY TYPE STANDARD -->
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
                                        $startYear = $billType->academicYear->start_year ?? date('Y');
                                        $endYear = $billType->academicYear->end_year ?? ($startYear + 1);
                                        @endphp

                                        <div class="row">
                                            @for ($i = 0; $i < 12; $i++)
                                                @php
                                                $month=($startMonth + $i - 1) % 12 + 1;
                                                $monthName=\Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F');
                                                $defaultYear = $month >= 7 ? $startYear : $endYear;
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
                                                                    value="{{ $displayYear }}" placeholder="Tahun" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                    @else
                                        <!-- FREE TYPE STANDARD -->
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
                                                        data-control="select2" data-placeholder="Pilih Bulan..." multiple="multiple">
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
                                                        value="{{ $billType->academicYear->start_year ?? date('Y') }}">
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
                                @endif
                            </div>
                            <!-- END SECTION 1: SISWA UMUM WRAPPER -->

                            <!-- ========================================== -->
                            <!-- SECTION 2: SISWA KHUSUS (TRANSFER) WRAPPER -->
                            <!-- ========================================== -->
                            <div id="transfer_nominal_wrapper" class="d-none">
                                <div class="mb-5">
                                    <div class="alert alert-warning d-flex align-items-center p-5 mb-6" style="background-color: #fff8dd; border: 1px dashed #ffc700;">
                                        <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <div class="d-flex flex-column text-gray-800">
                                            <h4 class="mb-1 text-gray-900 fw-bolder">Konfigurasi Tarif Siswa Khusus</h4>
                                            <span class="text-gray-700 fs-7">Buat tarif khusus/custom per kelompok santri (misal: Santri Ndalem, Santri Khidmah, Santri Kuliah, Beasiswa, atau Siswa Susulan). Tentukan nama status khusus, nominal rupiah, dan pilih santri yang ditargetkan.</span>
                                        </div>
                                    </div>

                                    @if(isset($paymentRate) && $paymentRate->type == 'TRANSFER')
                                        <!-- EDIT MODE FOR EXISTING TRANSFER RATE -->
                                        <div class="p-6 bg-light rounded-4 border mb-4 shadow-sm" style="background-color: #fdfdfd; border: 1px solid #e4e6ef !important;">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <label class="form-label fs-6 fw-bolder text-gray-800">Nama Jenis Status / Tagihan Khusus <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-solid" id="transfer_edit_name" name="transfer_edit_name" value="{{ $paymentRate->name }}" placeholder="Misal: Santri Khidmat / Ndalem" required />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fs-6 fw-bolder text-gray-800">Nominal Tarif (Rp) <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-solid">
                                                        <span class="input-group-text border-0">Rp</span>
                                                        <input type="text" class="form-control form-control-solid input-money" name="price" value="{{ number_format($paymentRate->amount ?? 0, 0, ',', '.') }}" placeholder="0" required />
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fs-6 fw-bolder text-gray-800 m-0">
                                                            <i class="fas fa-users text-primary me-2"></i>Daftar Siswa Terdaftar & Tambah Siswa Susulan <span class="text-danger">*</span>
                                                        </label>
                                                        <span class="badge badge-light-primary fw-bolder fs-8" id="edit_students_count_badge">
                                                            {{ $paymentRate->paymentRateStudents->count() }} Santri
                                                        </span>
                                                    </div>
                                                    <div class="form-text mb-3 text-muted">
                                                        Ketik nama atau NIS santri pada kolom di bawah untuk mencari dan <strong>menambahkan santri susulan</strong>, atau klik tanda silang (<i class="fas fa-times text-danger fs-8"></i>) untuk menghapus santri dari tarif khusus ini.
                                                    </div>
                                                    
                                                    <select class="form-select form-select-solid transfer-student-edit-select" id="transfer_edit_students" name="students[]" data-placeholder="Ketik untuk mencari dan menambahkan santri susulan..." multiple="multiple" required>
                                                        @foreach($paymentRate->paymentRateStudents as $prStudent)
                                                            @if($prStudent->student)
                                                                <option value="{{ $prStudent->student_id }}" 
                                                                        selected 
                                                                        data-name="{{ $prStudent->student->name }}"
                                                                        data-nis="{{ $prStudent->student->nis ?? '-' }}"
                                                                        data-classroom="{{ $prStudent->student->classroom?->name ?? '-' }}"
                                                                        data-school="{{ $prStudent->student->classroom?->school?->name ?? '-' }}">
                                                                    {{ $prStudent->student->name }} [{{ $prStudent->student->nis ?? '-' }}] - {{ $prStudent->student->classroom?->name ?? '-' }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- CREATE MODE: DYNAMIC REPEATER FOR SPECIAL STUDENTS -->
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <label class="form-label fs-5 fw-bolder text-gray-800 m-0">
                                                <i class="fas fa-layer-group text-primary me-2"></i>Daftar Jenis Siswa Khusus & Tarif
                                            </label>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="addSiswaKhususRow()">
                                                <i class="fas fa-plus me-1"></i> Tambah Status / Tarif Khusus
                                            </button>
                                        </div>

                                        <div id="siswa_khusus_repeater">
                                            <!-- Dynamically added rows will appear here via JS -->
                                        </div>
                                    @endif

                                    <!-- MONTHLY 12-MONTH SWITCHES (FOR MONTHLY BILL TYPE) -->
                                    @if ($billType->type == "MONTHLY" && !isset($paymentRate))
                                        <div class="mt-8 border-top pt-6">
                                            <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Bulan Tagihan Aktif untuk Siswa Khusus</label>
                                            <div class="form-text mb-4">
                                                Secara default tagihan berlaku 12 bulan penuh. Hilangkan centang jika Anda ingin menonaktifkan tagihan di bulan tertentu.
                                            </div>
                                            <div class="row">
                                                @php
                                                $monthlyList = [
                                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni'
                                                ];
                                                @endphp
                                                @foreach($monthlyList as $mKey => $mName)
                                                <div class="col-md-3 col-6 mb-3">
                                                    <div class="form-check form-switch form-check-custom form-check-solid form-check-success">
                                                        <input class="form-check-input" type="checkbox" name="active_months[]" value="{{ $mKey }}" checked />
                                                        <label class="form-check-label fw-bold">
                                                            {{ $mName }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!-- END SECTION 2: SISWA KHUSUS WRAPPER -->

                        </div>
                    </div>
                    <!--end::Nominal Card-->
                </div>
                <!--end::Left Column-->

                <!--begin::Right Column (Target & Actions)-->
                <div class="col-lg-4">
                    
                    <!-- ========================================== -->
                    <!-- CARD TARGET REGULAR (SISWA UMUM)           -->
                    <!-- ========================================== -->
                    <div class="card shadow-sm rounded-4 border-0 mb-5" id="target_card_regular">
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
                                @php
                                    $selectedSchoolId = null;
                                    $isLocked = false;
                                    if (isset($paymentRate)) {
                                        if ($paymentRate->type == 'REGULAR' && $paymentRate->paymentRateClassrooms->isNotEmpty()) {
                                            $selectedSchoolId = $paymentRate->paymentRateClassrooms->first()->classroom->school_id ?? null;
                                        } elseif ($paymentRate->type == 'TRANSFER' && $paymentRate->paymentRateStudents->isNotEmpty()) {
                                            $selectedSchoolId = $paymentRate->paymentRateStudents->first()->student->classroom->school_id ?? null;
                                        }
                                        $isLocked = true;
                                    } else {
                                        if (request()->has('school_id') && request()->get('school_id')) {
                                            $selectedSchoolId = request()->get('school_id');
                                            $isLocked = true;
                                        } elseif (isset($schools) && $schools->count() === 1) {
                                            $selectedSchoolId = $schools->first()->id;
                                            $isLocked = true;
                                        } elseif (auth()->user()?->school_id) {
                                            $selectedSchoolId = auth()->user()->school_id;
                                            $isLocked = true;
                                        }
                                    }
                                @endphp
                                <select name="school_id" class="form-select form-select-solid {{ ($isLocked) ? 'bg-light' : '' }}" id="school_id"
                                    data-control="select2" data-placeholder="Pilih Sekolah" {{ ($isLocked) ? 'disabled' : '' }}>
                                    <option></option>
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" {{ ($selectedSchoolId == $school->id) ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @if($isLocked && $selectedSchoolId)
                                    <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">
                                @endif
                            </div>

                            <!-- FILTER WRAPPER (FOR SISWA UMUM ONLY) -->
                            <div id="filter_status_wrapper">
                                <!-- Status Wali -->
                                @if($billType->use_wali_filter && !$isMatrixMode)
                                <div class="mb-5">
                                    <label class="form-label fw-bold fs-6 text-gray-700">Status Wali</label>
                                    <select name="jamaah_status[]" class="form-select form-select-solid {{ isset($paymentRate) ? 'bg-light' : '' }}" id="jamaah_status"
                                        data-control="select2" data-placeholder="Semua Status (Jamaah, Non Jamaah, Mukimin)" data-allow-clear="true" multiple="multiple" {{ isset($paymentRate) ? 'disabled' : '' }}>
                                        @php
                                            $jamaahValues = isset($paymentRate) ? explode(',', $paymentRate->jamaah_status) : [];
                                        @endphp
                                        <option value="JAMAAH" {{ in_array('JAMAAH', $jamaahValues) ? 'selected' : '' }}>Jamaah</option>
                                        <option value="NON_JAMAAH" {{ in_array('NON_JAMAAH', $jamaahValues) ? 'selected' : '' }}>Non Jamaah</option>
                                        <option value="MUKIMIN" {{ in_array('MUKIMIN', $jamaahValues) ? 'selected' : '' }}>Mukimin</option>
                                    </select>
                                    @if(isset($paymentRate))
                                        <input type="hidden" name="jamaah_status" value="{{ $paymentRate->jamaah_status }}">
                                    @endif
                                </div>
                                @endif

                                <!-- Gender -->
                                @if($billType->use_gender_filter)
                                <div class="mb-5">
                                    <label class="form-label fw-bold fs-6 text-gray-700">Jenis Kelamin</label>
                                    <select name="gender[]" class="form-select form-select-solid {{ isset($paymentRate) ? 'bg-light' : '' }}" id="gender"
                                        data-control="select2" data-placeholder="Semua Gender (Putra & Putri)" data-allow-clear="true" multiple="multiple" {{ isset($paymentRate) ? 'disabled' : '' }}>
                                        @php
                                            $genderValues = isset($paymentRate) ? explode(',', $paymentRate->gender) : [];
                                        @endphp
                                        <option value="L" {{ in_array('L', $genderValues) ? 'selected' : '' }}>Laki-laki (Santri Putra)</option>
                                        <option value="P" {{ in_array('P', $genderValues) ? 'selected' : '' }}>Perempuan (Santri Putri)</option>
                                    </select>
                                    @if(isset($paymentRate))
                                        <input type="hidden" name="gender" value="{{ $paymentRate->gender }}">
                                    @endif
                                </div>
                                @endif

                                <!-- Status Alumni -->
                                @if($billType->use_alumni_filter && !$isMatrixMode)
                                <div class="mb-5">
                                    <label class="form-label fw-bold fs-6 text-gray-700">Status Alumni</label>
                                    <select name="alumni_status[]" class="form-select form-select-solid {{ isset($paymentRate) ? 'bg-light' : '' }}" id="alumni_status"
                                        data-control="select2" data-placeholder="Semua Status (Alumni & Non-Alumni)" data-allow-clear="true" multiple="multiple" {{ isset($paymentRate) ? 'disabled' : '' }}>
                                        @php
                                            $alumniValues = isset($paymentRate) ? explode(',', $paymentRate->alumni_status) : [];
                                        @endphp
                                        <option value="ALUMNI_SMP_MA" {{ in_array('ALUMNI_SMP_MA', $alumniValues) ? 'selected' : '' }}>Khusus Alumni (Lulusan SMP -> MA)</option>
                                        <option value="NON_ALUMNI" {{ in_array('NON_ALUMNI', $alumniValues) ? 'selected' : '' }}>Khusus Siswa Baru (Non-Alumni)</option>
                                    </select>
                                    @if(isset($paymentRate))
                                        <input type="hidden" name="alumni_status" value="{{ $paymentRate->alumni_status }}">
                                    @endif
                                </div>
                                @endif
                            </div>

                            @if(isset($paymentRate))
                                <!-- EDIT MODE TARGET INFO -->
                                <div class="mb-5">
                                    <label class="form-label fw-bold fs-6 text-gray-700">
                                        {{ $paymentRate->type == 'REGULAR' ? 'Kelas Terdaftar' : 'Siswa Terdaftar' }}
                                    </label>
                                    @if($paymentRate->type == 'REGULAR')
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($paymentRate->paymentRateClassrooms as $prClassroom)
                                                <span class="badge badge-light-primary p-2 fs-7">{{ $prClassroom->classroom?->name ?? 'Kelas Dihapus' }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <!-- CREATE MODE: KELAS TARGET (SISWA UMUM) -->
                                <div class="mb-5" id="classroom_wrapper">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-bold fs-6 text-gray-700 mb-0">Kelas</label>
                                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-8" id="btn-select-all-classrooms">
                                            Select All
                                        </button>
                                    </div>
                                    <div class="border rounded p-4 bg-light-body" style="max-height: 250px; overflow-y: auto;">
                                        <div class="row row-cols-3 g-2" id="classroom_grid_container">
                                            @if(isset($classrooms))
                                            @foreach ($classrooms as $classroom)
                                            @php
                                                $isCreated = isset($existingClassroomIds) && in_array($classroom->id, $existingClassroomIds);
                                            @endphp
                                            <div class="col">
                                                <label class="btn btn-outline btn-outline-dashed btn-outline-default d-flex align-items-center justify-content-start p-2 w-100 h-100 {{ $isCreated ? 'pe-none bg-light-secondary opacity-75 border-gray-300' : 'cursor-pointer' }} text-start" style="border-radius: 8px;">
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm me-2">
                                                        <input class="form-check-input classroom-checkbox" type="checkbox" name="classrooms[]" value="{{ $classroom->id }}" {{ $isCreated ? 'checked disabled' : '' }} />
                                                    </div>
                                                    <span class="fs-7 fw-bold text-gray-800">{{ $classroom->name }}</span>
                                                    @if($isCreated)
                                                        <span class="ms-auto me-1 text-danger" title="Kelas ini sudah dibuatkan tarif"><i class="fas fa-lock fs-8"></i></span>
                                                    @endif
                                                </label>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">
                                        Pilih satu atau lebih kelas yang akan dikenakan tarif ini.
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                    <!--end::Target Card Regular-->

                    <!-- ========================================== -->
                    <!-- CARD DAFTAR SANTRI TERPILIH (SISWA KHUSUS) -->
                    <!-- ========================================== -->
                    <div class="card shadow-sm rounded-4 border-0 mb-5 d-none" id="selected_students_card">
                        <div class="card-header border-0 pt-6 d-flex justify-content-between align-items-center">
                            <h3 class="card-title fw-bolder text-dark m-0 fs-5">
                                <i class="fas fa-user-check text-primary me-2"></i>Daftar Santri Terpilih
                            </h3>
                            <span class="badge badge-light-primary fw-bolder fs-7" id="selected_students_count_badge">0 Santri</span>
                        </div>
                        <div class="card-body pt-2 px-4">
                            <div id="empty_selected_students_notice" class="text-center py-8 text-muted d-none">
                                <div class="symbol symbol-50px symbol-circle bg-light-primary mb-3 mx-auto d-flex align-items-center justify-content-center">
                                    <i class="fas fa-users fs-2 text-primary"></i>
                                </div>
                                <div class="fw-bold fs-7 text-gray-700">Belum ada santri yang dipilih</div>
                                <div class="fs-8 text-muted mt-1">Cari dan pilih santri pada kolom di sebelah kiri untuk melihat daftar urutan santri di sini.</div>
                            </div>

                            <div class="table-responsive" id="selected_students_table_container" style="max-height: 420px; overflow-y: auto;">
                                <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-2 mb-0">
                                    <thead>
                                        <tr class="fw-bolder text-gray-600 fs-8 text-uppercase bg-light">
                                            <th class="w-30px text-center ps-2 rounded-start">No</th>
                                            <th>Nama Santri</th>
                                            <th class="text-center">Kelas</th>
                                            <th class="text-end pe-2 rounded-end">Jenis Khusus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected_students_tbody">
                                        <!-- Dynamically populated rows from selected students in repeater or edit select -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Selected Students Card-->

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
    let khususRowCounter = 0;

    // Cache of student details selected across all rows and edit mode
    const studentDetailCache = {};

    function renderSelectedStudentsTable() {
        const allStudents = [];

        // CASE 1: EDIT MODE FOR EXISTING TRANSFER RATE
        if ($('#transfer_edit_students').length > 0) {
            const categoryName = $('#transfer_edit_name').val() || 'Siswa Khusus';
            const selectedData = $('#transfer_edit_students').select2('data') || [];

            selectedData.forEach(function(item) {
                const cached = studentDetailCache[item.id] || {};
                const className = item.classroom_name || cached.classroom_name || $(item.element).data('classroom') || '-';
                const studentName = item.name || cached.name || $(item.element).data('name') || (item.text ? item.text.split('[')[0].trim() : 'Santri');
                const studentNis = item.nis || cached.nis || $(item.element).data('nis') || (item.text && item.text.match(/\[(.*?)\]/) ? item.text.match(/\[(.*?)\]/)[1] : '-');

                allStudents.push({
                    id: item.id,
                    name: studentName,
                    nis: studentNis,
                    classroom: className,
                    categoryName: categoryName
                });
            });
        } else {
            // CASE 2: CREATE MODE WITH DYNAMIC REPEATER
            $('.siswa-khusus-row').each(function(rowIndex) {
                const rowName = $(this).find('input[name^="transfer_names"]').val() || ('Jenis Khusus #' + (rowIndex + 1));
                const selectEl = $(this).find('.transfer-student-select');
                const selectedData = selectEl.select2('data') || [];

                selectedData.forEach(function(item) {
                    // Look up in cache or extract from Select2 item
                    const cached = studentDetailCache[item.id] || {};
                    const className = item.classroom_name || cached.classroom_name || '-';
                    const studentName = item.name || cached.name || (item.text ? item.text.split('[')[0].trim() : 'Santri');
                    const studentNis = item.nis || cached.nis || (item.text && item.text.match(/\[(.*?)\]/) ? item.text.match(/\[(.*?)\]/)[1] : '-');

                    allStudents.push({
                        id: item.id,
                        name: studentName,
                        nis: studentNis,
                        classroom: className,
                        categoryName: rowName
                    });
                });
            });
        }

        const count = allStudents.length;
        $('#selected_students_count_badge').text(count + ' Santri');
        if ($('#edit_students_count_badge').length > 0) {
            $('#edit_students_count_badge').text(count + ' Santri');
        }

        if (count === 0) {
            $('#empty_selected_students_notice').removeClass('d-none');
            $('#selected_students_table_container').addClass('d-none');
            $('#selected_students_tbody').empty();
        } else {
            $('#empty_selected_students_notice').addClass('d-none');
            $('#selected_students_table_container').removeClass('d-none');

            let tbodyHtml = '';
            allStudents.forEach(function(student, idx) {
                const nisBadge = (student.nis && student.nis !== '-') ? `<span class="text-muted fs-8 d-block">${student.nis}</span>` : '';
                const classBadge = (student.classroom && student.classroom !== '-') 
                    ? `<span class="badge badge-light-info fw-bold fs-8">${student.classroom}</span>`
                    : `<span class="badge badge-light-secondary text-muted fs-8">-</span>`;
                
                tbodyHtml += `
                    <tr>
                        <td class="text-center fw-bold text-gray-700 ps-2 fs-7">${idx + 1}</td>
                        <td>
                            <span class="fw-bolder text-gray-800 fs-7">${student.name}</span>
                            ${nisBadge}
                        </td>
                        <td class="text-center">
                            ${classBadge}
                        </td>
                        <td class="text-end pe-2">
                            <span class="badge badge-light-warning fw-bolder fs-8">${student.categoryName}</span>
                        </td>
                    </tr>
                `;
            });
            $('#selected_students_tbody').html(tbodyHtml);
        }
    }

    function addSiswaKhususRow() {
        const index = khususRowCounter++;
        const rowHtml = `
            <div class="row align-items-center mb-4 siswa-khusus-row bg-light p-4 rounded border position-relative">
                <button type="button" class="btn btn-icon btn-sm btn-light-danger position-absolute" style="top: -10px; right: -10px; width: 26px; height: 26px; border-radius: 50%;" onclick="removeSiswaKhususRow(this)" title="Hapus baris ini">
                    <i class="fas fa-times"></i>
                </button>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label fs-7 fw-bolder text-gray-700">Nama Jenis Status / Tarif Khusus <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-solid form-control-sm row-category-name" name="transfer_names[${index}]" placeholder="Misal: Santri Kuliah / Ndalem / Beasiswa" required />
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fs-7 fw-bolder text-gray-700">Nominal Tarif (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm input-group-solid">
                        <span class="input-group-text border-0">Rp</span>
                        <input type="text" class="form-control form-control-solid form-control-sm input-money" name="transfer_prices[${index}]" placeholder="0" required />
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fs-7 fw-bolder text-gray-700">Pilih Siswa Target <span class="text-danger">*</span></label>
                    <select class="form-select form-select-solid form-select-sm transfer-student-select" name="transfer_students[${index}][]" data-placeholder="Ketik untuk mencari nama siswa..." multiple="multiple" required>
                    </select>
                </div>
            </div>
        `;
        
        $("#siswa_khusus_repeater").append(rowHtml);
        const $newRow = $("#siswa_khusus_repeater .siswa-khusus-row").last();
        
        // Initialize Mask Money
        $newRow.find('.input-money').mask('000.000.000.000.000', {reverse: true});
        
        // Initialize Remote Select2
        const $select = $newRow.find('.transfer-student-select');
        $select.select2({
            ajax: {
                url: "{{ route('select2') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { 
                        data_type: 'STUDENT', 
                        school_id: $('#school_id').val(),
                        q: params.term, 
                        page: params.page 
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return { 
                        results: $.map(data, function (item) { 
                            // Cache details
                            studentDetailCache[item.id] = {
                                id: item.id,
                                name: item.name,
                                nis: item.nis,
                                classroom_name: item.classroom_name,
                                gender: item.gender
                            };

                            return { 
                                id: item.id,
                                name: item.name,
                                nis: item.nis,
                                classroom_name: item.classroom_name,
                                gender: item.gender,
                                text: item.text 
                            }; 
                        }), 
                        pagination: { more: (params.page * 30) < (data.total_count || 0) } 
                    };
                }
            },
            minimumInputLength: 1,
            language: {
                inputTooShort: function() {
                    return "Ketik nama santri...";
                },
                searching: function() {
                    return "Mencari data santri...";
                },
                noResults: function() {
                    return "Santri tidak ditemukan";
                }
            }
        });

        // Trigger table update when selection changes
        $select.on('change select2:select select2:unselect', function() {
            renderSelectedStudentsTable();
        });

        // Trigger table update when category name changes
        $newRow.find('.row-category-name').on('input keyup change', function() {
            renderSelectedStudentsTable();
        });
    }

    function removeSiswaKhususRow(btn) {
        if ($('.siswa-khusus-row').length <= 1) {
            Swal.fire({
                text: "Minimal harus ada 1 jenis tagihan khusus.",
                icon: "info",
                buttonsStyling: false,
                confirmButtonText: "Mengerti",
                customClass: { confirmButton: "btn btn-primary" }
            });
            return;
        }
        $(btn).closest('.siswa-khusus-row').remove();
        renderSelectedStudentsTable();
    }

    $(document).ready(function() {

        // ========================================================
        // INITIALIZE EDIT MODE SISWA KHUSUS SELECT2
        // ========================================================
        if ($('#transfer_edit_students').length > 0) {
            // Preload student cache from existing selected options
            $('#transfer_edit_students option:selected').each(function() {
                const sId = $(this).val();
                studentDetailCache[sId] = {
                    id: sId,
                    name: $(this).data('name') || $(this).text().split('[')[0].trim(),
                    nis: $(this).data('nis') || ($(this).text().match(/\[(.*?)\]/) ? $(this).text().match(/\[(.*?)\]/)[1] : '-'),
                    classroom_name: $(this).data('classroom') || '-',
                    school_name: $(this).data('school') || '-'
                };
            });

            // Initialize AJAX Select2 for Edit Mode
            $('#transfer_edit_students').select2({
                ajax: {
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { 
                            data_type: 'STUDENT', 
                            school_id: $('#school_id').val(),
                            q: params.term, 
                            page: params.page 
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return { 
                            results: $.map(data, function (item) { 
                                studentDetailCache[item.id] = {
                                    id: item.id,
                                    name: item.name,
                                    nis: item.nis,
                                    classroom_name: item.classroom_name,
                                    gender: item.gender
                                };

                                return { 
                                    id: item.id,
                                    name: item.name,
                                    nis: item.nis,
                                    classroom_name: item.classroom_name,
                                    gender: item.gender,
                                    text: item.text 
                                }; 
                            }), 
                            pagination: { more: (params.page * 30) < (data.total_count || 0) } 
                        };
                    }
                },
                minimumInputLength: 1,
                language: {
                    inputTooShort: function() {
                        return "Ketik nama atau NIS santri...";
                    },
                    searching: function() {
                        return "Mencari data santri...";
                    },
                    noResults: function() {
                        return "Santri tidak ditemukan";
                    }
                }
            });

            // Listen to student changes in edit mode
            $('#transfer_edit_students').on('change select2:select select2:unselect', function() {
                renderSelectedStudentsTable();
            });

            // Listen to name changes in edit mode
            $('#transfer_edit_name').on('input keyup change', function() {
                renderSelectedStudentsTable();
            });
        }

        // Function to fetch classrooms dynamically
        function fetchClassrooms() {
            var school_id = $('#school_id').val();
            var gridContainer = $('#classroom_grid_container');
            var billTypeId = $('input[name="bill_type_id"]').val();
            var gender = $('#gender').val();
            var jamaah_status = $('#jamaah_status').val();
            var alumni_status = $('#alumni_status').val();
            var is_matrix = $('input[name="is_matrix"]').val() || 0;

            if (!school_id) {
                gridContainer.empty();
                return;
            }

            axios.get("{{ route('payment-rate.get-classroom') }}", {
                    params: { 
                        school_id: school_id,
                        bill_type_id: billTypeId,
                        gender: gender,
                        jamaah_status: jamaah_status,
                        alumni_status: alumni_status,
                        is_matrix: is_matrix
                    }
                })
                .then(function(response) {
                    gridContainer.empty();
                    if (response.data.length > 0) {
                        $.each(response.data, function(key, value) {
                            var isCreated = value.is_already_created;
                            var cardClass = isCreated ? "btn btn-outline btn-outline-dashed btn-outline-default d-flex align-items-center justify-content-start p-2 w-100 h-100 pe-none bg-light-secondary opacity-75 text-start border-gray-300" : "btn btn-outline btn-outline-dashed btn-outline-default d-flex align-items-center justify-content-start p-2 w-100 h-100 cursor-pointer text-start";
                            var checkAttr = isCreated ? "checked disabled" : "";
                            var badgeHtml = isCreated ? `<span class="ms-auto me-1 text-danger" title="Kelas ini sudah dibuatkan tarif"><i class="fas fa-lock fs-8"></i></span>` : "";

                            var cardHtml = `
                                <div class="col">
                                    <label class="${cardClass}" style="border-radius: 8px;">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm me-2">
                                            <input class="form-check-input classroom-checkbox" type="checkbox" name="classrooms[]" value="${value.id}" ${checkAttr} />
                                        </div>
                                        <span class="fs-7 fw-bold text-gray-800">${value.name}</span>
                                        ${badgeHtml}
                                    </label>
                                </div>
                            `;
                            gridContainer.append(cardHtml);
                        });
                        updateSelectAllButtonText();
                    }
                })
                .catch(function(error) { console.error(error); });
        }

        // Handle School Change
        $('#school_id').on('change', function() {
            fetchClassrooms();
        });

        // Handle Filter Changes
        $('#gender, #jamaah_status, #alumni_status').on('change', function() {
            fetchClassrooms();
        });

        // ========================================================
        // TAB / RADIO TOGGLE (SISWA UMUM VS SISWA KHUSUS)
        // ========================================================
        function toggleCategoryType(type) {
            if (type === 'REGULAR') {
                // Tampilkan UI Siswa Umum
                $('#regular_nominal_wrapper').removeClass('d-none');
                $('#target_card_regular').removeClass('d-none');
                
                // Sembunyikan UI Siswa Khusus
                $('#transfer_nominal_wrapper').addClass('d-none');
                $('#selected_students_card').addClass('d-none');
                $('#type_helper').text('Tarif akan diterapkan untuk semua siswa dalam kelas yang dipilih.');
            } else {
                // Sembunyikan UI Siswa Umum
                $('#regular_nominal_wrapper').addClass('d-none');
                $('#target_card_regular').addClass('d-none');
                
                // Tampilkan UI Siswa Khusus
                $('#transfer_nominal_wrapper').removeClass('d-none');
                $('#selected_students_card').removeClass('d-none');
                $('#type_helper').text('Tarif hanya akan diterapkan untuk siswa tertentu yang dipilih pada daftar di bawah.');
                
                // Auto create initial row if empty in create mode
                if ($('#siswa_khusus_repeater .siswa-khusus-row').length === 0 && !$('#transfer_edit_students').length) {
                    addSiswaKhususRow();
                }

                renderSelectedStudentsTable();
            }
        }

        $('input[name="type"]').on('change', function() {
            toggleCategoryType($(this).val());
        });

        // Initial Trigger
        var initialType = $('input[name="type"]:checked').val() || $('input[name="type"]').val() || 'REGULAR';
        toggleCategoryType(initialType);

        function updateSelectAllButtonText() {
            var checkboxes = $('.classroom-checkbox:not(:disabled)');
            var checkedCount = checkboxes.filter(':checked').length;
            var totalCount = checkboxes.length;

            if (totalCount > 0 && checkedCount === totalCount) {
                $('#btn-select-all-classrooms').text('Deselect All');
            } else {
                $('#btn-select-all-classrooms').text('Select All');
            }
        }

        // Select All Classrooms Button
        $('#btn-select-all-classrooms').click(function() {
            var checkboxes = $('.classroom-checkbox:not(:disabled)');
            if (checkboxes.length === 0) return;

            var checkedCount = checkboxes.filter(':checked').length;
            var totalCount = checkboxes.length;

            if (checkedCount === totalCount) {
                checkboxes.prop('checked', false);
                $(this).text('Select All');
            } else {
                checkboxes.prop('checked', true);
                $(this).text('Deselect All');
            }
        });

        $(document).on('change', '.classroom-checkbox', function() {
            updateSelectAllButtonText();
        });

        // Auto-Fill Months for Monthly Standard Price
        $('#setPrice').on('change keyup', function() {
            var price = $(this).val();
            $('[id^="bulan_"]').val(price);
        });

        // Currency Formatting Helpers
        function formatThousandString(val) {
            if (!val) return '';
            var clean = val.toString().replace(/[^0-9]/g, '');
            if (!clean) return '';
            return parseInt(clean, 10).toLocaleString('id-ID');
        }

        $(document).on('input keyup', '.input-money, #setPrice, [name^="bulan_"]', function() {
            var raw = $(this).val();
            $(this).val(formatThousandString(raw));
        });

        $('.input-money, #setPrice, [name^="bulan_"]').each(function() {
            var val = $(this).val();
            if (val && val !== '0') {
                $(this).val(formatThousandString(val));
            }
        });

        // Form Submit Handler & Validation
        $('#payment-rate-form').on('submit', function(e) {
            var currentType = $('input[name="type"]:checked').val() || $('input[name="type"]').val() || 'REGULAR';
            
            if (currentType === 'REGULAR') {
                var isMatrix = $('input[name="is_matrix"]').val() || 0;
                var isMonthly = $('input[id^="bulan_"]').length > 0;
                var isValid = false;

                if (isMatrix == 1) {
                    isValid = true;
                } else if (isMonthly) {
                    $('input[id^="bulan_"]').each(function() {
                        var val = $(this).val().replace(/\./g, '');
                        if (val && parseInt(val) > 0) {
                            isValid = true;
                            return false;
                        }
                    });
                } else {
                    var val = $('#setPrice').val().replace(/\./g, '');
                    if (val && parseInt(val) > 0) {
                        isValid = true;
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        text: "Harap isi minimal 1 rincian tarif (Bulan atau Total Tagihan).",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti",
                        customClass: { confirmButton: "btn btn-primary" }
                    });
                    return false;
                }

                // Check classrooms selected in create mode
                if ($('.classroom-checkbox').length > 0 && $('.classroom-checkbox:checked').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        text: "Harap pilih minimal 1 kelas target untuk Siswa Umum.",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti",
                        customClass: { confirmButton: "btn btn-primary" }
                    });
                    return false;
                }
            } else {
                // SISWA KHUSUS VALIDATION
                if ($('#transfer_edit_students').length > 0) {
                    var selectedStudents = $('#transfer_edit_students').val();
                    if (!selectedStudents || selectedStudents.length === 0) {
                        e.preventDefault();
                        Swal.fire({
                            text: "Harap pilih minimal 1 santri target untuk tarif khusus ini.",
                            icon: "warning",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, Mengerti",
                            customClass: { confirmButton: "btn btn-primary" }
                        });
                        return false;
                    }
                } else {
                    if ($('.siswa-khusus-row').length === 0) {
                        e.preventDefault();
                        Swal.fire({
                            text: "Harap tambahkan minimal 1 baris tarif Siswa Khusus.",
                            icon: "warning",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, Mengerti",
                            customClass: { confirmButton: "btn btn-primary" }
                        });
                        return false;
                    }
                }
            }

            // Clean thousand separator before submitting
            $('.input-money, #setPrice, [name^="bulan_"]').each(function() {
                var rawVal = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(rawVal);
            });
        });

        // Trigger school change if selected on load
        if ($('#school_id').val()) {
            $('#school_id').trigger('change');
        }
    });
</script>
@endpush