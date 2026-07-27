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
        .btn-accordion-chevron i {
            transition: transform 0.2s ease-in-out !important;
        }
        .transition-all {
            transition: all 0.2s ease-in-out !important;
        }
    </style>
@endpush

@section('content')
    <div class="content d-flex flex-column flex-column-fluid px-5" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
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
                                <a class="nav-link @if($activeTab == 'grade-promotion') active @endif" data-bs-toggle="tab" href="#tab-grade-promotion" role="tab">Migrasi Siswa</a>
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
                                 <!-- Accordion Menu for UPT / Sekolah -->
                                 <div class="accordion mb-8" id="school-accordion">
                                     @foreach ($schools as $school)
                                         <div class="accordion-item mb-5 border-0 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]" style="border-radius: 24px; border: none; background: transparent;">
                                             <!-- Accordion Header Card -->
                                             <div class="card border-0 rounded-[24px]" style="background-color: #ffffff; border-radius: 24px; border: none;">
                                                 <div class="card-body p-6 d-flex align-items-center justify-content-between position-relative cursor-pointer btn-toggle-accordion collapsed" data-bs-toggle="collapse" data-bs-target="#school-collapse-{{ $school->id }}" aria-expanded="false" data-school-id="{{ $school->id }}" data-school-name="{{ $school->name }}">
                                                     <!-- Left Info -->
                                                     <div class="d-flex align-items-center gap-4 pe-20 text-truncate">
                                                         <span class="badge bg-light-primary text-primary px-3 py-1 rounded-pill fs-8 fw-bolder">{{ $school->type }}</span>
                                                         <div class="text-truncate">
                                                             <h3 class="fw-bolder text-dark mb-1 fs-5 text-truncate">{{ $school->name }}</h3>
                                                             <div class="d-flex align-items-center gap-4 text-gray-500 fs-7 text-truncate">
                                                                 <span class="text-truncate"><i class="fa-solid fa-location-dot text-slate-400 me-1"></i>{{ $school->address ?? '-' }}</span>
                                                                 <span class="text-truncate"><i class="fa-solid fa-users-gear text-slate-400 me-1"></i>
                                                                     @php
                                                                         $admins = $school->adminSchool->map(fn($as) => $as->admin)->filter();
                                                                     @endphp
                                                                     @if ($admins->count() > 0)
                                                                         {{ $admins->pluck('name')->implode(', ') }}
                                                                     @else
                                                                         <span class="text-muted italic fs-8">Belum ada user ditugaskan</span>
                                                                     @endif
                                                                 </span>
                                                             </div>
                                                         </div>
                                                     </div>
                                                     
                                                     <!-- Right Action Cluster & Toggle Chevron -->
                                                     <div class="d-flex align-items-center gap-3" onclick="event.stopPropagation();">
                                                         <!-- Assign Users -->
                                                         <button type="button" class="btn btn-icon btn-light-info btn-sm rounded-circle w-30px h-30px btn-assign-user" data-id="{{ $school->id }}" data-name="{{ $school->name }}" data-users="{{ json_encode($school->adminSchool->pluck('admin_id')->toArray()) }}" title="Tugaskan User">
                                                             <i class="fa-solid fa-user-gear text-info fs-6"></i>
                                                         </button>

                                                         <!-- Edit -->
                                                         @can('Edit Sekolah')
                                                         <a href="{{ route('school.edit', $school->id) }}" class="btn btn-icon btn-light-warning btn-sm rounded-circle w-30px h-30px" title="Edit">
                                                             <i class="fa-solid fa-pencil text-warning fs-6"></i>
                                                         </a>
                                                         @endcan

                                                         <!-- Delete -->
                                                         @can('Delete Sekolah')
                                                         <div>
                                                             <a data-id="form-school-{{ $school->id }}" type="button" class="btn-delete btn btn-icon btn-light-danger btn-sm rounded-circle w-30px h-30px" title="Hapus">
                                                                 <i class="fa-solid fa-trash text-danger fs-6" data-id="form-school-{{ $school->id }}"></i>
                                                             </a>
                                                             <form id="form-school-{{ $school->id }}" action="{{ route('school.destroy', $school->id) }}" method="post" style="display: none;">
                                                                 @csrf
                                                                 @method('delete')
                                                             </form>
                                                         </div>
                                                         @endcan
                                                         
                                                         <!-- Chevron Toggle Icon -->
                                                         <button type="button" class="btn btn-icon btn-light btn-sm rounded-circle w-30px h-30px btn-accordion-chevron ms-2" data-bs-toggle="collapse" data-bs-target="#school-collapse-{{ $school->id }}">
                                                             <i class="fa-solid fa-chevron-down text-gray-500 fs-6 transition-all duration-200"></i>
                                                         </button>
                                                     </div>
                                                 </div>
                                             </div>
                                             
                                             <!-- Accordion Body -->
                                             <div id="school-collapse-{{ $school->id }}" class="accordion-collapse collapse" data-bs-parent="#school-accordion">
                                                 <div class="accordion-body p-6 border-top border-gray-100" style="background-color: #fafafa; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px;">
                                                     <div class="d-flex align-items-center justify-content-between mb-6">
                                                         <h4 class="fw-bolder text-dark mb-0 fs-6">Daftar Kelas UPT</h4>
                                                         @can('Manage Sekolah')
                                                         <a href="{{ route('classroom.create', ['school' => $school->id]) }}" class="btn btn-primary btn-sm rounded-pill px-4">
                                                             <i class="fa-solid fa-plus me-1 fs-7"></i> Tambah Kelas
                                                         </a>
                                                         @endcan
                                                     </div>
                                                     
                                                     <!-- Loading Indicator -->
                                                     <div class="classrooms-loading text-center py-5 d-none">
                                                         <div class="spinner-border text-primary" role="status">
                                                             <span class="visually-hidden">Memuat...</span>
                                                         </div>
                                                     </div>

                                                     <!-- Empty State -->
                                                     <div class="classrooms-empty text-center py-5 d-none text-muted">
                                                         Belum ada kelas yang terdaftar untuk sekolah ini.
                                                     </div>

                                                     <!-- Prominent Classroom Cards Grid -->
                                                     <div class="classrooms-list-container row row-cols-2 row-cols-md-6 g-5">
                                                         <!-- Loaded dynamically -->
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     @endforeach
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

                            <!--begin::Tab Migrasi Siswa-->
                            @can('Manage Kenaikan Kelas')
                            <div class="tab-pane fade @if($activeTab == 'grade-promotion') show active @endif" id="tab-grade-promotion" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <x-text.h2>Proses Migrasi Siswa (Pindah Kelas & Kenaikan Kelas)</x-text.h2>
                                    <span id="selected_count_badge" class="badge bg-light-info text-info fw-bolder fs-7 px-4 py-2 border border-info rounded-pill">0 Siswa Terpilih</span>
                                </div>
                                
                                <form action="{{ route('grade-promotion.store') }}" method="post" id="form-grade-promotion">
                                    @csrf
                                    <x-alert.alert-validation />

                                    <!-- Selection Box for Migration Type -->
                                    <div class="card bg-light-primary border border-primary border-dashed p-4 mb-6" style="border-radius: 16px;">
                                        <x-text.label class="d-block mb-3 text-primary fw-bolder fs-7">PILIH JENIS MIGRASI SISWA :</x-text.label>
                                        <div class="d-flex flex-wrap gap-6 align-items-center">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input me-2" type="radio" name="migration_type" value="transfer" id="migration_type_transfer" checked />
                                                <label class="form-check-label fw-bold text-gray-800 cursor-pointer" for="migration_type_transfer">
                                                    <i class="fa-solid fa-right-left me-1 text-primary"></i> <strong>Pindah Kelas</strong> <span class="text-muted fs-8">(Plotting / Penyebaran dalam Tahun Ajaran yang Sama)</span>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input me-2" type="radio" name="migration_type" value="promotion" id="migration_type_promotion" />
                                                <label class="form-check-label fw-bold text-gray-800 cursor-pointer" for="migration_type_promotion">
                                                    <i class="fa-solid fa-graduation-cap me-1 text-success"></i> <strong>Kenaikan Kelas</strong> <span class="text-muted fs-8">(Promosi Kenaikan Tingkat ke Tahun Ajaran Baru)</span>
                                                </label>
                                            </div>
                                        </div>
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
                                            <x-text.body class="fw-bold" id="label_target_classroom">Pindah Ke Kelas :</x-text.body>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="new_classroom_id" id="filter_new_classroom" class="form-select" required>
                                                <option value="">Pilih Kelas Baru</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="academic_year_id" id="filter_academic_year_id" class="form-select" required>
                                                <option value="">Pilih Tahun Ajaran</option>
                                                @foreach ($academicYears as $academicYear)
                                                <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if (Auth::user()->can('Create Kenaikan Kelas'))
                                        <div class="col-md-auto">
                                            <button type="submit" class="btn btn-primary" id="btn_change_classroom">
                                                <i class="fa-solid fa-paper-plane me-2"></i> <span id="btn_change_label">Proses Pindah Kelas</span>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @endcan
                            <!--end::Tab Migrasi Siswa-->

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

    <!-- Modal Assign User -->
    <div class="modal fade" id="modal_assign_school" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content rounded-[24px]" style="border-radius: 24px;">
                <form action="" method="POST" id="form_assign_school">
                    @csrf
                    <div class="modal-header">
                        <h2 class="fw-bolder" id="modal_title">Assign User</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <span class="svg-icon svg-icon-1">
                                <i class="fa-solid fa-xmark fs-4"></i>
                            </span>
                        </div>
                    </div>
                    <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                <span class="required">Pilih Pengguna</span>
                            </label>
                            <select name="admin_ids[]" class="form-select form-select-solid" id="admin_select" data-control="select2" data-dropdown-parent="#modal_assign_school" data-placeholder="Pilih pengguna..." data-allow-clear="true" multiple="multiple">
                                @foreach($allAdmins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn_submit">
                            <span class="indicator-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
            // -------------------- Tab 1: School Accordion & Classrooms --------------------
            function loadClassrooms(schoolId, schoolName) {
                var $panel = $('#school-collapse-' + schoolId);
                var $loading = $panel.find('.classrooms-loading');
                var $empty = $panel.find('.classrooms-empty');
                var $container = $panel.find('.classrooms-list-container');

                $loading.removeClass('d-none');
                $empty.addClass('d-none');
                $container.empty();
                
                $.ajax({
                    url: `/school/${schoolId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $loading.addClass('d-none');
                        
                        var classrooms = response.data;
                        if (classrooms.length > 0) {
                            $.each(classrooms, function(index, classroom) {
                                var editUrl = `/classroom/${classroom.id}/edit`;
                                var deleteFormId = `form-classroom-${classroom.id}`;
                                var deleteUrl = `/classroom/${classroom.id}`;
                                var studentsCount = classroom.students_count || 0;
                                
                                var cardHtml = `
                                    <div class="col">
                                        <div class="card h-100 border-0 position-relative shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover-elevate-up" style="background-color: #ffffff; border-radius: 24px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important; border: none; min-height: 140px; transition: all 0.3s ease;">
                                            <!-- Top-Right Action Cluster -->
                                            <div class="position-absolute top-0 end-0 p-3 d-flex align-items-center gap-2" style="z-index: 10;" onclick="event.stopPropagation();">
                                                @can('Edit Sekolah')
                                                <a href="${editUrl}" class="btn btn-icon btn-light-warning btn-sm rounded-circle w-28px h-28px d-flex align-items-center justify-content-center transition-all" title="Edit" style="background-color: rgba(245, 158, 11, 0.1); color: #f59e0b; border: none; border-radius: 50%;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                                </a>
                                                @endcan
                                                @can('Delete Sekolah')
                                                <div>
                                                    <a data-id="${deleteFormId}" type="button" class="btn-delete btn btn-icon btn-light-danger btn-sm rounded-circle w-28px h-28px d-flex align-items-center justify-content-center transition-all" title="Hapus" style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; border-radius: 50%;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                    </a>
                                                    <form id="${deleteFormId}" action="${deleteUrl}" method="post" style="display: none;">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                    </form>
                                                </div>
                                                @endcan
                                            </div>
                                            
                                            <!-- Card Body -->
                                            <div class="card-body p-5 d-flex flex-column justify-content-between h-100">
                                                <!-- Overline Label -->
                                                <div>
                                                    <span class="typography-label d-block text-slate-400" style="font-size: 10px; font-weight: bold; letter-spacing: 0.05em;">KELAS</span>
                                                </div>
                                                
                                                <!-- Prominent Class Name -->
                                                <div class="my-3 text-center">
                                                    <span style="font-family: 'Outfit', sans-serif; font-size: 2.2rem; font-weight: 700; color: #0f172a; line-height: 1;">${classroom.name}</span>
                                                </div>
                                                
                                                <!-- Bottom Section: Badge & Count -->
                                                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-gray-100" style="margin-top: auto;">
                                                    <span class="badge d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill" style="font-size: 10px; font-weight: bold; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                                                        <i class="fa-solid fa-circle-check" style="font-size: 8px; color: #10b981;"></i> Aktif
                                                    </span>
                                                    <span class="text-slate-500 fw-semibold" style="font-size: 11px;">
                                                        <i class="fa-solid fa-users text-slate-400 me-1" style="font-size: 11px;"></i> ${studentsCount} Siswa
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $container.append(cardHtml);
                            });
                        } else {
                            $empty.removeClass('d-none');
                        }
                    },
                    error: function() {
                        $loading.addClass('d-none');
                        toastr.error('Gagal memuat data kelas');
                    }
                });
            }

            // Bind Bootstrap Collapse Events
            $(document).on('show.bs.collapse', '.accordion-collapse', function () {
                var $collapse = $(this);
                var schoolId = $collapse.attr('id').replace('school-collapse-', '');
                var $header = $(`.btn-toggle-accordion[data-school-id="${schoolId}"]`);
                var schoolName = $header.data('school-name');
                
                loadClassrooms(schoolId, schoolName);
                
                // Animate chevron
                $header.find('.btn-accordion-chevron i').css('transform', 'rotate(180deg)');
            });

            $(document).on('hide.bs.collapse', '.accordion-collapse', function () {
                var $collapse = $(this);
                var schoolId = $collapse.attr('id').replace('school-collapse-', '');
                var $header = $(`.btn-toggle-accordion[data-school-id="${schoolId}"]`);
                
                // Animate chevron back
                $header.find('.btn-accordion-chevron i').css('transform', 'rotate(0deg)');
            });

            // Assign User action
            $(document).on('click', '.btn-assign-user', function(e) {
                e.stopPropagation(); // Prevent accordion collapse trigger
                const schoolId = $(this).data('id');
                const schoolName = $(this).data('name');
                const users = $(this).data('users'); // Array of IDs

                $('#modal_title').text('Tugaskan Pengguna ke Wilayah UPT: ' + schoolName);
                $('#form_assign_school').attr('action', `/school/${schoolId}/assign`);

                // Clear and set values in select2
                $('#admin_select').val(users).trigger('change');

                $('#modal_assign_school').modal('show');
            });

            // Auto-expand panel on load if school_id parameter is present in URL
            const urlParams = new URLSearchParams(window.location.search);
            const schoolIdParam = urlParams.get('school_id');
            if (schoolIdParam) {
                // Activate school tab if not active
                $('a[href="#tab-school"]').tab('show');
                
                var $collapse = $('#school-collapse-' + schoolIdParam);
                if ($collapse.length > 0) {
                    $collapse.collapse('show');
                    
                    // Smooth scroll to UPT panel
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: $collapse.parent().offset().top - 150
                        }, 300);
                    }, 500);
                }
            }

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

            // -------------------- Tab 5: Migrasi Siswa --------------------
            @can('Manage Kenaikan Kelas')
            var tablePromotion;
            let selectedStudentIds = new Set();

            function updateSelectedCounter() {
                const count = selectedStudentIds.size;
                $('#selected_count_badge').text(count + ' Siswa Terpilih');
            }

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
                        drawCallback: function() {
                            // Re-apply checked state for selected student IDs across pages
                            $('#table-grade-promotion input[name="student_ids_page[]"]').each(function() {
                                if (selectedStudentIds.has($(this).val())) {
                                    $(this).prop('checked', true);
                                } else {
                                    $(this).prop('checked', false);
                                }
                            });

                            // Update header select all status
                            const allPageChecked = $('#table-grade-promotion input[name="student_ids_page[]"]').length > 0 &&
                                $('#table-grade-promotion input[name="student_ids_page[]"]:checked').length === $('#table-grade-promotion input[name="student_ids_page[]"]').length;
                            $('#select_all').prop('checked', allPageChecked);

                            updateSelectedCounter();
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
                                    return `<input type="checkbox" class="checkbox-student" name="student_ids_page[]" value="${data}">`;
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

            // Individual checkbox toggle
            $(document).on('change', '.checkbox-student', function() {
                const val = $(this).val();
                if ($(this).is(':checked')) {
                    selectedStudentIds.add(val);
                } else {
                    selectedStudentIds.delete(val);
                }
                updateSelectedCounter();
            });

            // Select All visible rows toggle
            $('#select_all').on('click', function() {
                const isChecked = $(this).is(':checked');
                $('#table-grade-promotion input[name="student_ids_page[]"]').each(function() {
                    $(this).prop('checked', isChecked);
                    const val = $(this).val();
                    if (isChecked) {
                        selectedStudentIds.add(val);
                    } else {
                        selectedStudentIds.delete(val);
                    }
                });
                updateSelectedCounter();
            });

            // Dynamic Migration Type Mode Switcher
            $('input[name="migration_type"]').on('change', function() {
                const mode = $(this).val();
                if (mode === 'transfer') {
                    $('#label_target_classroom').text('Pindah Ke Kelas :');
                    $('#btn_change_label').text('Proses Pindah Kelas');
                    $('#btn_change_classroom i').attr('class', 'fa-solid fa-paper-plane me-2');
                } else {
                    $('#label_target_classroom').text('Naik Ke Kelas :');
                    $('#btn_change_label').text('Proses Kenaikan Kelas');
                    $('#btn_change_classroom i').attr('class', 'fa-solid fa-graduation-cap me-2');
                }
            });

            // Watch filters
            $('#filter_school_id').on('change', function() {
                var val = $(this).val();
                selectedStudentIds.clear();
                updateSelectedCounter();
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
                selectedStudentIds.clear();
                updateSelectedCounter();
                searchPromotion();
            });

            // Form Submit Guard & Payload Injector
            $('#form-grade-promotion').on('submit', function(e) {
                // Remove pre-existing hidden student_ids inputs
                $(this).find('input[name="student_ids[]"]').remove();

                if (selectedStudentIds.size === 0) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Silakan pilih setidaknya satu siswa untuk dipindahkan!');
                    } else {
                        alert('Silakan pilih setidaknya satu siswa untuk dipindahkan!');
                    }
                    return false;
                }

                if (!$('#filter_new_classroom').val()) {
                    e.preventDefault();
                    toastr.error('Silakan pilih Kelas Tujuan!');
                    return false;
                }

                if (!$('#filter_academic_year_id').val()) {
                    e.preventDefault();
                    toastr.error('Silakan pilih Tahun Ajaran Target!');
                    return false;
                }

                // Inject selected student IDs into form
                selectedStudentIds.forEach(function(studentId) {
                    $('#form-grade-promotion').append(`<input type="hidden" name="student_ids[]" value="${studentId}">`);
                });

                return true;
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
