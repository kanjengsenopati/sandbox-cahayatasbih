@extends('layouts.master', ['title' => 'Akademik'])

@push('css')
    <style>
        .premium-card {
            border-radius: 24px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
            border: none !important;
            background: #ffffff !important;
            transition: all 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.06) !important;
        }
        .typography-h1 {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: #0f172a !important; /* Slate-900 */
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .typography-h2 {
            font-size: 16px !important;
            font-weight: 600 !important;
            color: #1e293b !important; /* Slate-800 */
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .typography-label {
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            color: #94a3b8 !important; /* Slate-400 */
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .typography-body {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #475569 !important; /* Slate-600 */
            font-family: 'Inter', sans-serif !important;
        }
        .typography-caption {
            font-size: 12px !important;
            font-weight: 400 !important;
            font-style: italic !important;
            color: #94a3b8 !important; /* Slate-400 */
            font-family: 'Inter', sans-serif !important;
        }
        /* Custom styles for premium tabs */
        .nav-line-tabs .nav-item .nav-link {
            border-bottom: 3px solid transparent;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            color: #64748b;
            padding: 12px 20px;
            transition: all 0.2s ease;
        }
        .nav-line-tabs .nav-item .nav-link.active,
        .nav-line-tabs .nav-item .nav-link:hover {
            color: #2563eb !important; /* Accent Primary */
            border-bottom: 3px solid #2563eb;
        }
    </style>
@endpush

@section('content')
    <div class="content d-flex flex-column flex-column-fluid px-5" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-0">
                <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <x-text.h1 class="my-1">Modul Akademik</x-text.h1>
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Akademik</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Manajemen Data Akademik</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl px-0">
                <!--begin::Card-->
                <div class="card premium-card mb-5 p-5">
                    <!--begin::Card header-->
                    <div class="card-header border-0 p-0 mb-5">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold" role="tablist">
                            @can('Manage Sekolah')
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab == 'school') active @endif" data-bs-toggle="tab" href="#tab-school" role="tab">UPT / Sekolah</a>
                            </li>
                            @endcan
                            @can('Manage Tahun Ajaran')
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab == 'academic-year') active @endif" data-bs-toggle="tab" href="#tab-academic-year" role="tab">Tahun Ajaran</a>
                            </li>
                            @endcan
                            @can('Manage Semester')
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab == 'semester') active @endif" data-bs-toggle="tab" href="#tab-semester" role="tab">Semester</a>
                            </li>
                            @endcan
                            @can('Manage Mata Pelajaran')
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab == 'study') active @endif" data-bs-toggle="tab" href="#tab-study" role="tab">Mata Pelajaran</a>
                            </li>
                            @endcan
                            @can('Manage Kenaikan Kelas')
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab == 'grade-promotion') active @endif" data-bs-toggle="tab" href="#tab-grade-promotion" role="tab">Kenaikan Kelas</a>
                            </li>
                            @endcan
                            @can('Manage Kelulusan Santri')
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab == 'student-graduation') active @endif" data-bs-toggle="tab" href="#tab-student-graduation" role="tab">Kelulusan Siswa</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body p-0">
                        <div class="tab-content">
                            
                            <!--begin::Tab School-->
                            @can('Manage Sekolah')
                            <div class="tab-pane fade @if($activeTab == 'school') show active @endif" id="tab-school" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Data UPT / Sekolah</x-text.h2>
                                    <x-action.create name="Sekolah" action="{{ route('school.create') }}" />
                                </div>
                                <div class="table-responsive">
                                    <table id="table-school" class="table align-middle table-row-dashed w-100">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                <th style="width: 5%">No</th>
                                                <th>Nama</th>
                                                <th>Tipe</th>
                                                <th>Keterangan</th>
                                                <th>Keunggulan</th>
                                                <th>Alamat</th>
                                                <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-bold"></tbody>
                                    </table>
                                </div>
                            </div>
                            @endcan
                            <!--end::Tab School-->

                            <!--begin::Tab Academic Year-->
                            @can('Manage Tahun Ajaran')
                            <div class="tab-pane fade @if($activeTab == 'academic-year') show active @endif" id="tab-academic-year" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Data Tahun Ajaran</x-text.h2>
                                    <x-action.create name="Tahun Ajaran" action="{{ route('academic-year.create') }}" />
                                </div>
                                <div class="table-responsive">
                                    <table id="table-academic-year" class="table align-middle table-row-dashed w-100">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                <th style="width: 5%">No</th>
                                                <th>Tahun Ajaran</th>
                                                <th>Mulai</th>
                                                <th>Selesai</th>
                                                <th>Status</th>
                                                <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-bold"></tbody>
                                    </table>
                                </div>
                            </div>
                            @endcan
                            <!--end::Tab Academic Year-->

                            <!--begin::Tab Semester-->
                            @can('Manage Semester')
                            <div class="tab-pane fade @if($activeTab == 'semester') show active @endif" id="tab-semester" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Data Semester</x-text.h2>
                                    <x-action.create name="Semester" action="{{ route('semester.create') }}" />
                                </div>
                                <div class="table-responsive">
                                    <table id="table-semester" class="table table-striped border rounded gy-5 gs-7 w-100">
                                        <thead>
                                            <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                                <th width="5%">No</th>
                                                <th>Nama Semester</th>
                                                <th>Urutan</th>
                                                <th class="text-center min-w-100px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            @endcan
                            <!--end::Tab Semester-->

                            <!--begin::Tab Study-->
                            @can('Manage Mata Pelajaran')
                            <div class="tab-pane fade @if($activeTab == 'study') show active @endif" id="tab-study" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Data Mata Pelajaran</x-text.h2>
                                    <x-action.create name="Mata Pelajaran" action="{{ route('study.create') }}" />
                                </div>
                                <div class="table-responsive">
                                    <table id="table-study" class="table table-striped border rounded gy-5 gs-7 w-100">
                                        <thead>
                                            <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                                <th width="5%">No</th>
                                                <th>Mata Pelajaran</th>
                                                <th>KKM</th>
                                                <th class="text-center min-w-100px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            @endcan
                            <!--end::Tab Study-->

                            <!--begin::Tab Grade Promotion-->
                            @can('Manage Kenaikan Kelas')
                            <div class="tab-pane fade @if($activeTab == 'grade-promotion') show active @endif" id="tab-grade-promotion" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Proses Kenaikan Kelas Santri</x-text.h2>
                                </div>
                                
                                <!-- Filters -->
                                <div class="row g-5 mb-6 align-items-end">
                                    <div class="col-md-4">
                                        <x-text.label class="d-block mb-2">UPT / Pendidikan</x-text.label>
                                        <select name="school_id" class="form-select" id="filter_school_id">
                                            <option value="">Pilih Pendidikan</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <x-text.label class="d-block mb-2">Kelas Saat Ini</x-text.label>
                                        <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                            <option value="">Pilih Kelas</option>
                                        </select>
                                    </div>
                                </div>

                                <form action="{{ route('grade-promotion.store') }}" method="post" id="form-grade-promotion">
                                    @csrf
                                    <x-alert.alert-validation />
                                    <div class="table-responsive">
                                        <table id="table-grade-promotion" class="table align-middle table-row-dashed w-100">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th>NIS</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Kelas</th>
                                                    <th>Status</th>
                                                    <th style="align-content: center">Pilih Semua<br><input type="checkbox" id="select_all"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>

                                    <div class="row align-items-center mt-5 g-4">
                                        <div class="col-md-auto">
                                            <x-text.body class="fw-bold">Pindah / Naik Ke Kelas :</x-text.body>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="new_classroom_id" id="filter_new_classroom" class="form-select">
                                                <option value="">Pilih Kelas Baru</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="academic_year_id" id="filter_academic_year_id" class="form-select">
                                                <option value="">Pilih Tahun Ajaran Baru</option>
                                                @foreach ($academicYears as $academicYear)
                                                <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if (Auth::user()->can('Create Kenaikan Kelas'))
                                        <div class="col-md-auto">
                                            <button type="submit" class="btn btn-primary" id="btn_change_classroom">
                                                <i class="fa-solid fa-graduation-cap me-2"></i> Proses Kenaikan Kelas
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @endcan
                            <!--end::Tab Grade Promotion-->

                            <!--begin::Tab Student Graduation-->
                            @can('Manage Kelulusan Santri')
                            <div class="tab-pane fade @if($activeTab == 'student-graduation') show active @endif" id="tab-student-graduation" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Proses Kelulusan Siswa Akhir</x-text.h2>
                                </div>

                                <!-- Filters -->
                                <div class="row g-5 mb-6 align-items-end">
                                    <div class="col-md-4">
                                        <x-text.label class="d-block mb-2">UPT / Pendidikan</x-text.label>
                                        <select name="school_id" class="form-select" id="filter_school_id_grad">
                                            <option value="">Pilih Pendidikan</option>
                                            @foreach ($schoolsGraduation as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <x-text.label class="d-block mb-2">Kelas Akhir</x-text.label>
                                        <select name="classroom_id" class="form-select" id="filter_classroom_id_grad">
                                            <option value="">Pilih Kelas</option>
                                        </select>
                                    </div>
                                </div>

                                <form action="{{ route('student-graduation.store') }}" method="POST" id="form-student-graduation">
                                    @csrf
                                    <x-alert.alert-validation />
                                    <div class="table-responsive">
                                        <table id="table-student-graduation" class="table align-middle table-row-dashed w-100">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th>NIS</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Kelas</th>
                                                    <th>Status</th>
                                                    <th>Tunggakan</th>
                                                    <th style="align-content: center">Pilih Semua<br><input type="checkbox" id="select_all_grad"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>

                                    <div class="row align-items-center mt-5">
                                        @if (Auth::user()->can('Create Kelulusan Santri'))
                                        <div class="col-md-auto">
                                            <button type="submit" class="btn btn-primary" id="btn_process_graduation">
                                                <i class="fa-solid fa-user-graduate me-2"></i> Proses Kelulusan Siswa
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @endcan
                            <!--end::Tab Student Graduation-->

                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
        </div>
        <!--end::Post-->
    </div>
@endsection

@push('js')
    <script>
        // Relational classroom filters for Grade Promotion
        function getClassroomBySchoolId(schoolId) {
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: { school_id: schoolId },
                success: function(response) {
                    $('#filter_classroom_id').empty();
                    $('#filter_new_classroom').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            $('#filter_new_classroom').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
                    }
                }
            });
        }

        // Relational classroom filters for Graduation
        function getClassroomBySchoolIdGrad(schoolId) {
            $.ajax({
                url: "{{ route('student-graduation.get-classroom') }}",
                type: "GET",
                data: { school_id: schoolId },
                success: function(response) {
                    $('#filter_classroom_id_grad').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id_grad').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id_grad').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    } else {
                        $('#filter_classroom_id_grad').append('<option value="">Tidak ada kelas</option>');
                    }
                }
            });
        }

        $(document).ready(function() {
            // -------------------- Tab 1: School --------------------
            @can('Manage Sekolah')
            $('#table-school').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('school.index') }}",
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'></i>",
                        previous: "<i class='fa fa-angle-left'></i>"
                    },
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                },
                columns: [
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type' },
                    { data: 'description', name: 'description' },
                    { data: 'features_display', name: 'features_display' },
                    { data: 'address', name: 'address' },
                    { data: 'action', name: 'action' }
                ]
            });
            @endcan

            // -------------------- Tab 2: Academic Year --------------------
            @can('Manage Tahun Ajaran')
            $('#table-academic-year').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('academic-year.index') }}",
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'></i>",
                        previous: "<i class='fa fa-angle-left'></i>"
                    },
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                },
                columns: [
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'start_year', name: 'start_year' },
                    { data: 'end_year', name: 'end_year' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action' }
                ]
            });
            @endcan

            // -------------------- Tab 3: Semester --------------------
            @can('Manage Semester')
            $('#table-semester').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('semester.index') }}",
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'></i>",
                        previous: "<i class='fa fa-angle-left'></i>"
                    },
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                },
                columns: [
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'order', name: 'order' },
                    { data: 'action', name: 'action' }
                ]
            });
            @endcan

            // -------------------- Tab 4: Study --------------------
            @can('Manage Mata Pelajaran')
            $('#table-study').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('study.index') }}",
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'></i>",
                        previous: "<i class='fa fa-angle-left'></i>"
                    },
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                },
                columns: [
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'kkm', name: 'kkm' },
                    { data: 'action', name: 'action' }
                ]
            });
            @endcan

            // -------------------- Tab 5: Grade Promotion --------------------
            @can('Manage Kenaikan Kelas')
            var tablePromotion;
            function searchPromotion() {
                if ($.fn.DataTable.isDataTable('#table-grade-promotion')) {
                    tablePromotion.ajax.reload();
                } else {
                    tablePromotion = $('#table-grade-promotion').DataTable({
                        processing: true,
                        serverSide: true,
                        ordering: false,
                        ajax: {
                            url: "{{ route('grade-promotion.index') }}",
                            data: function(d) {
                                d.school_id = $('#filter_school_id').val();
                                d.classroom_id = $('#filter_classroom_id').val();
                            }
                        },
                        columns: [
                            {
                                data: null,
                                sortable: false,
                                searchable: false,
                                render: function(data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1;
                                }
                            },
                            { data: 'nis', name: 'nis' },
                            { data: 'name', name: 'name' },
                            { data: 'classroom', name: 'classroom' },
                            { data: 'status', name: 'status' },
                            {
                                data: 'id',
                                name: 'id',
                                orderable: false,
                                searchable: false,
                                render: function(data) {
                                    return `<input type="checkbox" name="student_ids[]" value="${data}">`;
                                }
                            }
                        ],
                        language: {
                            paginate: {
                                next: "<i class='fa fa-angle-right'></i>",
                                previous: "<i class='fa fa-angle-left'></i>"
                            },
                            loadingRecords: "Memuat...",
                            processing: "Memproses..."
                        }
                    });
                }
            }

            // Watch filters
            $('#filter_school_id').on('change', function() {
                var val = $(this).val();
                if (val) {
                    getClassroomBySchoolId(val);
                    searchPromotion();
                } else {
                    $('#filter_classroom_id').empty().append('<option value="">Pilih Kelas</option>');
                    $('#filter_new_classroom').empty().append('<option value="">Pilih Kelas Baru</option>');
                    if ($.fn.DataTable.isDataTable('#table-grade-promotion')) {
                        tablePromotion.destroy();
                        $('#table-grade-promotion tbody').empty();
                    }
                }
            });

            $('#filter_classroom_id').on('change', function() {
                searchPromotion();
            });

            $('#select_all').on('click', function() {
                $('input[name="student_ids[]"]').prop('checked', $(this).is(':checked'));
            });

            // Initial load of classrooms if school pre-selected
            if ($('#filter_school_id').val()) {
                getClassroomBySchoolId($('#filter_school_id').val());
                searchPromotion();
            }
            @endcan

            // -------------------- Tab 6: Student Graduation --------------------
            @can('Manage Kelulusan Santri')
            var tableGraduation;
            function searchGraduation() {
                if ($.fn.DataTable.isDataTable('#table-student-graduation')) {
                    tableGraduation.ajax.reload();
                } else {
                    tableGraduation = $('#table-student-graduation').DataTable({
                        processing: true,
                        serverSide: true,
                        ordering: false,
                        ajax: {
                            url: "{{ route('student-graduation.index') }}",
                            data: function(d) {
                                d.school_id = $('#filter_school_id_grad').val();
                                d.classroom_id = $('#filter_classroom_id_grad').val();
                            }
                        },
                        drawCallback: function() {
                            $('[data-bs-toggle="tooltip"]').tooltip();
                        },
                        columns: [
                            {
                                data: null,
                                sortable: false,
                                searchable: false,
                                render: function(data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1;
                                }
                            },
                            { data: 'nis', name: 'nis' },
                            { data: 'name', name: 'name' },
                            { data: 'classroom', name: 'classroom' },
                            { data: 'status', name: 'status' },
                            { data: 'unpaid_bills', name: 'unpaid_bills' },
                            {
                                data: 'id',
                                name: 'id',
                                orderable: false,
                                searchable: false,
                                render: function(data) {
                                    return `<input type="checkbox" name="student_ids[]" value="${data}">`;
                                }
                            }
                        ],
                        language: {
                            paginate: {
                                next: "<i class='fa fa-angle-right'></i>",
                                previous: "<i class='fa fa-angle-left'></i>"
                            },
                            loadingRecords: "Memuat...",
                            processing: "Memproses..."
                        }
                    });
                }
            }

            // Watch filters for graduation
            $('#filter_school_id_grad').on('change', function() {
                var val = $(this).val();
                if (val) {
                    getClassroomBySchoolIdGrad(val);
                    searchGraduation();
                } else {
                    $('#filter_classroom_id_grad').empty().append('<option value="">Pilih Kelas</option>');
                    if ($.fn.DataTable.isDataTable('#table-student-graduation')) {
                        tableGraduation.destroy();
                        $('#table-student-graduation tbody').empty();
                    }
                }
            });

            $('#filter_classroom_id_grad').on('change', function() {
                searchGraduation();
            });

            $('#select_all_grad').on('click', function() {
                $('#table-student-graduation input[name="student_ids[]"]').prop('checked', $(this).is(':checked'));
            });

            if ($('#filter_school_id_grad').val()) {
                getClassroomBySchoolIdGrad($('#filter_school_id_grad').val());
                searchGraduation();
            }
            @endcan
        });
    </script>
@endpush
