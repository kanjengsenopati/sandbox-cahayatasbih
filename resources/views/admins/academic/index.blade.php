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
        /* 3-Column Dropdown Class Picker Button Styles & Contrast Fixes */
        .btn-select-current-class,
        .btn-select-target-class {
            color: #2563eb !important;
            background-color: #f1f5f9 !important;
            border: 1px solid transparent !important;
            transition: all 0.15s ease-in-out !important;
        }
        .btn-select-current-class *,
        .btn-select-target-class * {
            color: inherit !important;
        }
        .btn-select-current-class:hover,
        .btn-select-target-class:hover,
        .btn-select-current-class:focus,
        .btn-select-target-class:focus,
        .btn-select-current-class.active,
        .btn-select-target-class.active {
            color: #ffffff !important;
            background-color: #2563eb !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
        }
        .btn-select-current-class:hover *,
        .btn-select-target-class:hover *,
        .btn-select-current-class:focus *,
        .btn-select-target-class:focus *,
        .btn-select-current-class.active *,
        .btn-select-target-class.active * {
            color: #ffffff !important;
        }
        .btn-select-current-class.disabled,
        .btn-select-target-class.disabled {
            color: #94a3b8 !important;
            background-color: #f8fafc !important;
            opacity: 0.55 !important;
            box-shadow: none !important;
            cursor: not-allowed !important;
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
                                            <tr class="text-start text-slate-700 fw-bolder fs-7 text-uppercase gs-0">
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
                                <form action="{{ route('grade-promotion.store') }}" method="post" id="form-grade-promotion">
                                    @csrf
                                    <input type="hidden" name="migration_type" id="input_migration_type" value="transfer">
                                    <x-alert.alert-validation />

                                    <!-- Sub-Tab Navigation Pills for Migration Type -->
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-5 gap-3">
                                        <ul class="nav nav-pills nav-pills-custom gap-2" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active fw-bolder px-5 py-3 rounded-pill btn-subtab-migration shadow-sm" id="subtab_transfer" data-type="transfer" type="button" style="transition: all 0.2s ease;">
                                                    <i class="fa-solid fa-right-left me-2"></i> Pindah Kelas <span class="fs-8 opacity-75 fw-normal">(Plotting / Tahun Ajaran Sama)</span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link fw-bolder px-5 py-3 rounded-pill text-gray-700 bg-light btn-subtab-migration" id="subtab_promotion" data-type="promotion" type="button" style="transition: all 0.2s ease;">
                                                    <i class="fa-solid fa-graduation-cap me-2"></i> Kenaikan Kelas <span class="fs-8 opacity-75 fw-normal">(Promosi / Tahun Ajaran Baru)</span>
                                                </button>
                                            </li>
                                        </ul>
                                        <span id="selected_count_badge" class="badge bg-light-info text-info fw-bolder fs-7 px-4 py-2 border border-info rounded-pill">0 Siswa Terpilih</span>
                                    </div>

                                    <!-- Warning Banner if Next Academic Year is missing -->
                                    <div id="ay_warning_banner" class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center p-4 mb-5 d-none" style="border-radius: 14px;">
                                        <i class="fa-solid fa-triangle-exclamation fs-2 text-warning me-4"></i>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bolder text-gray-900 fs-6">Tahun Ajaran Berikutnya Belum Dibuat</span>
                                            <span class="text-gray-700 fs-7">Tahun Ajaran Baru (berikutnya) belum tersedia di sistem. Silakan buat Tahun Ajaran Baru terlebih dahulu di Tab <strong>'Tahun Ajaran'</strong> untuk memproses Kenaikan Kelas.</span>
                                        </div>
                                    </div>

                                    <!-- Top Control Bar Card -->
                                    <div class="card p-5 mb-6 shadow-sm border-0" style="border-radius: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                        <div class="row g-4 align-items-end">
                                            <div class="col-lg-3 col-md-6">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;">UPT / Pendidikan</label>
                                                <select name="school_id" class="form-select bg-white" id="filter_school_id">
                                                    <option value="">Pilih Pendidikan</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-6 dropdown">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;">Kelas Saat Ini</label>
                                                <input type="hidden" name="classroom_id" id="filter_classroom_id">
                                                
                                                <button type="button" class="form-select bg-white d-flex align-items-center justify-content-between text-start w-100" id="btn_trigger_current_classroom" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" disabled style="height: 42px; border-radius: 10px;">
                                                    <span id="label_selected_current_classroom" class="text-muted fs-7">Pilih Kelas</span>
                                                </button>
                                                
                                                <div class="dropdown-menu p-3 shadow-lg border-0" id="dropdown_menu_current_classroom" style="width: 360px; max-width: 95vw; border-radius: 16px; z-index: 1050; background-color: #ffffff;">
                                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                                        <span class="fw-bolder fs-8 text-gray-800"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i>Pilih Kelas Saat Ini</span>
                                                        <span class="badge bg-light-primary text-primary fs-9 fw-bold px-2 py-1 rounded-pill">3 Kolom</span>
                                                    </div>
                                                    
                                                    <div class="row g-2" id="container_3col_current_classrooms">
                                                        <!-- Column 1 -->
                                                        <div class="col-4 border-end pe-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="badge bg-light-primary text-primary fw-bolder px-1 py-1 fs-9 rounded-pill w-100 text-center" id="col1_current_title">Kelas 7</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 overflow-auto pe-1" id="col1_current_class_list" style="max-height: 200px;">
                                                                <span class="text-muted fs-9 italic text-center py-2">Pilih UPT</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Column 2 -->
                                                        <div class="col-4 border-end px-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="badge bg-light-info text-info fw-bolder px-1 py-1 fs-9 rounded-pill w-100 text-center" id="col2_current_title">Kelas 8</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 overflow-auto pe-1" id="col2_current_class_list" style="max-height: 200px;">
                                                                <span class="text-muted fs-9 italic text-center py-2">Pilih UPT</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Column 3 -->
                                                        <div class="col-4 ps-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="badge bg-light-success text-success fw-bolder px-1 py-1 fs-9 rounded-pill w-100 text-center" id="col3_current_title">Kelas 9</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 overflow-auto pe-1" id="col3_current_class_list" style="max-height: 200px;">
                                                                <span class="text-muted fs-9 italic text-center py-2">Pilih UPT</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-6 dropdown">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;" id="label_target_classroom">Kelas Tujuan</label>
                                                <input type="hidden" name="new_classroom_id" id="filter_new_classroom" required>
                                                
                                                <button type="button" class="form-select bg-white d-flex align-items-center justify-content-between text-start w-100" id="btn_trigger_target_classroom" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" disabled style="height: 42px; border-radius: 10px;">
                                                    <span id="label_selected_target_classroom" class="text-muted fs-7">Pilih Kelas Tujuan</span>
                                                </button>
                                                
                                                <div class="dropdown-menu p-3 shadow-lg border-0" id="dropdown_menu_target_classroom" style="width: 360px; max-width: 95vw; border-radius: 16px; z-index: 1050; background-color: #ffffff;">
                                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                                        <span class="fw-bolder fs-8 text-gray-800"><i class="fa-solid fa-layer-group me-1 text-primary"></i>Pilih Kelas Tujuan</span>
                                                        <span class="badge bg-light-primary text-primary fs-9 fw-bold px-2 py-1 rounded-pill" id="target_class_mode_badge">3 Kolom</span>
                                                    </div>
                                                    
                                                    <div class="row g-2" id="container_3col_classrooms">
                                                        <!-- Column 1 -->
                                                        <div class="col-4 border-end pe-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="badge bg-light-primary text-primary fw-bolder px-1 py-1 fs-9 rounded-pill w-100 text-center" id="col1_title">Kelas 7</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 overflow-auto pe-1" id="col1_class_list" style="max-height: 200px;">
                                                                <span class="text-muted fs-9 italic text-center py-2">Pilih kelas</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Column 2 -->
                                                        <div class="col-4 border-end px-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="badge bg-light-info text-info fw-bolder px-1 py-1 fs-9 rounded-pill w-100 text-center" id="col2_title">Kelas 8</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 overflow-auto pe-1" id="col2_class_list" style="max-height: 200px;">
                                                                <span class="text-muted fs-9 italic text-center py-2">Pilih kelas</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Column 3 -->
                                                        <div class="col-4 ps-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="badge bg-light-success text-success fw-bolder px-1 py-1 fs-9 rounded-pill w-100 text-center" id="col3_title">Kelas 9</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 overflow-auto pe-1" id="col3_class_list" style="max-height: 200px;">
                                                                <span class="text-muted fs-9 italic text-center py-2">Pilih kelas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;" id="label_target_academic_year">Tahun Ajaran Target</label>
                                                <select name="academic_year_id" id="filter_academic_year_id" class="form-select bg-white" required>
                                                    <option value="">Pilih Tahun Ajaran</option>
                                                    @foreach ($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if (Auth::user()->can('Create Kenaikan Kelas'))
                                            <div class="col-lg-2 col-md-12">
                                                <button type="submit" class="btn btn-primary w-100 py-3" id="btn_change_classroom">
                                                    <i class="fa-solid fa-paper-plane me-1"></i> <span id="btn_change_label">Proses Pindah</span>
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Table Area -->
                                    <div class="table-responsive">
                                        <table id="table-grade-promotion" class="table align-middle table-row-dashed w-100">
                                            <thead>
                                                <tr class="text-start text-slate-700 fw-bolder fs-7 text-uppercase gs-0">
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
                                </form>
                            </div>
                            @endcan
                            <!--end::Tab Migrasi Siswa-->

                            <!--begin::Tab Student Graduation-->
                            @can('Manage Kelulusan Santri')
                            <div class="tab-pane fade @if($activeTab == 'student-graduation') show active @endif" id="tab-student-graduation" role="tabpanel">
                                <form action="{{ route('student-graduation.store') }}" method="POST" id="form-student-graduation">
                                    @csrf
                                    <x-alert.alert-validation />

                                    <!-- Top Control Bar Card for Graduation -->
                                    <div class="card p-5 mb-6 shadow-sm border-0" style="border-radius: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                        <div class="row g-4 align-items-end">
                                            <div class="col-lg-3 col-md-6">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;">UPT / Pendidikan</label>
                                                <select name="school_id" class="form-select bg-white" id="filter_school_id_grad">
                                                    <option value="">Pilih Pendidikan</option>
                                                    @foreach ($schoolsGraduation as $school)
                                                    <option value="{{ $school->id }}" data-type="{{ $school->type }}">{{ $school->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-3 col-md-6">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;">Kelas Akhir</label>
                                                <select name="classroom_id" class="form-select bg-white" id="filter_classroom_id_grad">
                                                    <option value="">Pilih Kelas Akhir</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3 col-md-6">
                                                <label class="d-block mb-2 fw-bold" style="font-size: 12px; color: #334155; letter-spacing: -0.01em; text-transform: none;">Opsi Kelulusan</label>
                                                <select name="graduation_option" id="filter_graduation_option" class="form-select bg-white" required>
                                                    <option value="lanjut_studi" selected>🎓 Lanjut Studi (Ke UPT Berikutnya & Kelas Transit)</option>
                                                    <option value="keluar">🚪 Keluar / Lulus Murni</option>
                                                </select>
                                            </div>
                                            @if (Auth::user()->can('Create Kelulusan Santri'))
                                            <div class="col-lg-3 col-md-12">
                                                <button type="submit" class="btn btn-primary w-100 py-3" id="btn_process_graduation">
                                                    <i class="fa-solid fa-user-graduate me-1"></i> <span id="btn_grad_label">Proses Kelulusan Siswa</span>
                                                </button>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Dynamic Destination Helper Badge -->
                                        <div class="mt-4 pt-3 border-top d-flex align-items-center gap-2" id="graduation_destination_container">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                            <span class="fs-7 fw-bold text-gray-700" id="graduation_destination_badge">SMP $\rightarrow$ Otomatis Lanjut ke MA (Kelas 10-Transit) | MA $\rightarrow$ Otomatis Lanjut ke Pondok (Kelas Pondok-Transit)</span>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="table-student-graduation" class="table align-middle table-row-dashed w-100">
                                            <thead>
                                                <tr class="text-start text-slate-700 fw-bolder fs-7 text-uppercase gs-0">
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

    <!-- Modal Wide Data Table Detail Tunggakan -->
    <div class="modal fade" id="modal_unpaid_bills_detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-[24px]" style="border-radius: 24px;">
                <div class="modal-header pb-3 border-0">
                    <div>
                        <h3 class="fw-bolder text-gray-900 mb-1" id="modal_unpaid_student_name">Detail Tunggakan Siswa</h3>
                        <span class="text-gray-600 fs-7 fw-bold" id="modal_unpaid_student_nis">NIS: -</span>
                    </div>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark fs-4"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 my-2">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed table-striped w-100">
                            <thead>
                                <tr class="text-start text-slate-700 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th style="width: 35%">Nama Tagihan & Tahun Ajaran</th>
                                    <th style="width: 40%">Bulan Menunggak</th>
                                    <th style="width: 20%" class="text-end">Nominal Total</th>
                                </tr>
                            </thead>
                            <tbody id="unpaid_bills_tbody" class="fw-bold text-gray-800">
                            </tbody>
                            <tfoot>
                                <tr class="border-top border-2 border-gray-300">
                                    <td colspan="3" class="text-end fw-bolder text-dark fs-6">Total Tunggakan Keseluruhan:</td>
                                    <td class="text-end fw-bolder text-danger fs-5" id="unpaid_bills_total">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-6">
                    <button type="button" class="btn btn-light-primary fw-bold px-6" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var currentSchoolClassrooms = [];
        var activeAY = @json($activeAcademicYear);
        var nextAY = @json($nextAcademicYear);

        function updateAcademicYearOptions() {
            var mode = $('#input_migration_type').val() || 'transfer';
            var $aySelect = $('#filter_academic_year_id');
            $aySelect.empty();
            $('#ay_warning_banner').addClass('d-none');
            $('#btn_change_classroom').prop('disabled', false);

            if (mode === 'transfer') {
                // Mode Pindah Kelas (Plotting): HANYA TAHUN AJARAN AKTIF SAAT INI
                if (activeAY) {
                    $aySelect.append('<option value="' + activeAY.id + '" selected>' + activeAY.name + '</option>');
                    $aySelect.prop('disabled', false);
                } else {
                    $aySelect.append('<option value="">Belum ada Tahun Ajaran Aktif</option>').prop('disabled', true);
                    $('#btn_change_classroom').prop('disabled', true);
                }
            } else {
                // Mode Kenaikan Kelas (Promosi): HANYA TAHUN AJARAN BERIKUTNYA
                if (nextAY) {
                    $aySelect.append('<option value="' + nextAY.id + '" selected>' + nextAY.name + '</option>');
                    $aySelect.prop('disabled', false);
                } else {
                    $aySelect.append('<option value="">Tahun Ajaran Berikutnya Belum Dibuat</option>').prop('disabled', true);
                    $('#ay_warning_banner').removeClass('d-none');
                    $('#btn_change_classroom').prop('disabled', true);
                }
            }
        }

        function extractClassLevel(className) {
            if (!className) return null;
            var str = className.trim();
            
            var digitMatch = str.match(/^(10|11|12|[789])/i);
            if (digitMatch) {
                return parseInt(digitMatch[0]);
            }
            
            var upper = str.toUpperCase();
            if (upper.startsWith('VII')) return 7;
            if (upper.startsWith('VIII')) return 8;
            if (upper.startsWith('IX')) return 9;
            if (upper.startsWith('XII')) return 12;
            if (upper.startsWith('XI')) return 11;
            if (upper.startsWith('X')) return 10;

            return null;
        }

        function updateTargetClassroomOptions() {
            var mode = $('#input_migration_type').val() || 'transfer';
            var selectedCurrentClassId = $('#filter_classroom_id').val();
            
            // Reset target classroom selection
            $('#filter_new_classroom').val('');
            $('#label_selected_target_classroom').text('Pilih Kelas Tujuan').removeClass('text-gray-900 fw-bolder').addClass('text-muted');
            $('#col1_class_list, #col2_class_list, #col3_class_list').empty();

            if (!selectedCurrentClassId || !currentSchoolClassrooms || currentSchoolClassrooms.length === 0) {
                $('#btn_trigger_target_classroom').prop('disabled', true);
                $('#col1_class_list, #col2_class_list, #col3_class_list').html('<span class="text-muted fs-8 italic text-center py-3">Pilih kelas saat ini</span>');
                return;
            }

            var selectedClass = currentSchoolClassrooms.find(function(c) { return c.id == selectedCurrentClassId; });
            if (!selectedClass) {
                $('#btn_trigger_target_classroom').prop('disabled', true);
                $('#col1_class_list, #col2_class_list, #col3_class_list').html('<span class="text-muted fs-8 italic text-center py-3">Pilih kelas saat ini</span>');
                return;
            }

            var currentLevel = extractClassLevel(selectedClass.name);

            if (mode === 'transfer') {
                $('#label_target_classroom').text('Kelas Tujuan');
                $('#target_class_mode_badge').text('Mode Pindah Kelas');
            } else {
                $('#label_target_classroom').text('Kelas Tujuan (Kenaikan Kelas)');
                $('#target_class_mode_badge').text('Mode Kenaikan Kelas');
            }

            // Determine level layout (SMP: 7, 8, 9 vs MA: 10, 11, 12 or dynamic)
            var levelsSet = new Set();
            $.each(currentSchoolClassrooms, function(i, cls) {
                var lvl = extractClassLevel(cls.name);
                if (lvl !== null) levelsSet.add(lvl);
            });
            var sortedLevels = Array.from(levelsSet).sort(function(a, b) { return a - b; });

            var level1 = 7, level2 = 8, level3 = 9; // Default SMP
            if (sortedLevels.some(function(l) { return l >= 10; })) {
                // MA / High School levels
                level1 = 10; level2 = 11; level3 = 12;
            } else if (sortedLevels.length > 0) {
                level1 = sortedLevels[0] || 7;
                level2 = sortedLevels[1] || (level1 + 1);
                level3 = sortedLevels[2] || (level2 + 1);
            }

            $('#col1_title').text('Kelas ' + level1);
            $('#col2_title').text('Kelas ' + level2);
            $('#col3_title').text('Kelas ' + level3);

            var countCol1 = 0, countCol2 = 0, countCol3 = 0;
            var totalSelectable = 0;

            $.each(currentSchoolClassrooms, function(index, cls) {
                var clsLevel = extractClassLevel(cls.name);
                var isCurrent = (cls.id == selectedCurrentClassId);

                var isSelectable = false;
                if (mode === 'transfer') {
                    // Mode Pindah Kelas: Boleh ke semua kelas kecuali kelas yang sedang dipilih
                    if (!isCurrent) {
                        isSelectable = true;
                    }
                } else {
                    // Mode Kenaikan Kelas: Harus 1 level di atasnya
                    if (currentLevel !== null && clsLevel !== null) {
                        if (clsLevel === (currentLevel + 1)) {
                            isSelectable = true;
                        }
                    } else if (!isCurrent) {
                        isSelectable = true;
                    }
                }

                var btnClass = '';
                if (isCurrent) {
                    btnClass = 'btn-light-warning text-warning border border-warning border-opacity-25 opacity-75 disabled';
                } else if (isSelectable) {
                    btnClass = 'btn-light-primary text-primary hover-elevate-up';
                } else {
                    btnClass = 'btn-light text-muted opacity-40 disabled';
                }

                var disabledAttr = (isSelectable && !isCurrent) ? '' : 'disabled';

                var itemHtml = `
                    <button type="button" class="btn btn-sm ${btnClass} text-center justify-content-center py-1 px-1 btn-select-target-class rounded-2 mb-1 w-100 fs-8 fw-bolder d-flex align-items-center" data-id="${cls.id}" data-name="${cls.name}" ${disabledAttr}>
                        <span>${cls.name}</span>
                        ${isCurrent ? '<i class="fa-solid fa-user-lock ms-1 fs-9" title="Kelas Saat Ini"></i>' : ''}
                    </button>
                `;

                if (clsLevel === level1) {
                    $('#col1_class_list').append(itemHtml);
                    countCol1++;
                } else if (clsLevel === level2) {
                    $('#col2_class_list').append(itemHtml);
                    countCol2++;
                } else if (clsLevel === level3) {
                    $('#col3_class_list').append(itemHtml);
                    countCol3++;
                } else {
                    // Fallback placement for non-standard level names
                    if (clsLevel !== null && clsLevel < level1) {
                        $('#col1_class_list').append(itemHtml);
                        countCol1++;
                    } else if (clsLevel !== null && clsLevel > level3) {
                        $('#col3_class_list').append(itemHtml);
                        countCol3++;
                    } else {
                        $('#col1_class_list').append(itemHtml);
                        countCol1++;
                    }
                }

                if (isSelectable && !isCurrent) {
                    totalSelectable++;
                }
            });

            if (countCol1 === 0) $('#col1_class_list').html('<span class="text-muted fs-8 italic text-center py-2">- Kosong -</span>');
            if (countCol2 === 0) $('#col2_class_list').html('<span class="text-muted fs-8 italic text-center py-2">- Kosong -</span>');
            if (countCol3 === 0) $('#col3_class_list').html('<span class="text-muted fs-8 italic text-center py-2">- Kosong -</span>');

            if (totalSelectable > 0 || currentSchoolClassrooms.length > 0) {
                $('#btn_trigger_target_classroom').prop('disabled', false);
            } else {
                $('#btn_trigger_target_classroom').prop('disabled', true);
            }
        }

        function renderCurrentClassroomOptions() {
            $('#col1_current_class_list, #col2_current_class_list, #col3_current_class_list').empty();

            if (!currentSchoolClassrooms || currentSchoolClassrooms.length === 0) {
                $('#btn_trigger_current_classroom').prop('disabled', true);
                $('#col1_current_class_list, #col2_current_class_list, #col3_current_class_list').html('<span class="text-muted fs-9 italic text-center py-2">Tidak ada kelas</span>');
                return;
            }

            // Determine level layout (SMP: 7, 8, 9 vs MA: 10, 11, 12 or dynamic)
            var levelsSet = new Set();
            $.each(currentSchoolClassrooms, function(i, cls) {
                var lvl = extractClassLevel(cls.name);
                if (lvl !== null) levelsSet.add(lvl);
            });
            var sortedLevels = Array.from(levelsSet).sort(function(a, b) { return a - b; });

            var level1 = 7, level2 = 8, level3 = 9; // Default SMP
            if (sortedLevels.some(function(l) { return l >= 10; })) {
                // MA / High School levels
                level1 = 10; level2 = 11; level3 = 12;
            } else if (sortedLevels.length > 0) {
                level1 = sortedLevels[0] || 7;
                level2 = sortedLevels[1] || (level1 + 1);
                level3 = sortedLevels[2] || (level2 + 1);
            }

            $('#col1_current_title').text('Kelas ' + level1);
            $('#col2_current_title').text('Kelas ' + level2);
            $('#col3_current_title').text('Kelas ' + level3);

            var countCol1 = 0, countCol2 = 0, countCol3 = 0;
            var selectedCurrentId = $('#filter_classroom_id').val();

            $.each(currentSchoolClassrooms, function(index, cls) {
                var clsLevel = extractClassLevel(cls.name);
                var isSelected = (cls.id == selectedCurrentId);

                var btnClass = isSelected ? 'active bg-primary text-white shadow-xs' : 'btn-light-primary text-primary hover-elevate-up';

                var itemHtml = `
                    <button type="button" class="btn btn-sm ${btnClass} text-center justify-content-center py-1 px-1 btn-select-current-class rounded-2 mb-1 w-100 fs-8 fw-bolder d-flex align-items-center" data-id="${cls.id}" data-name="${cls.name}">
                        <span>${cls.name}</span>
                    </button>
                `;

                if (clsLevel === level1) {
                    $('#col1_current_class_list').append(itemHtml);
                    countCol1++;
                } else if (clsLevel === level2) {
                    $('#col2_current_class_list').append(itemHtml);
                    countCol2++;
                } else if (clsLevel === level3) {
                    $('#col3_current_class_list').append(itemHtml);
                    countCol3++;
                } else {
                    if (clsLevel !== null && clsLevel < level1) {
                        $('#col1_current_class_list').append(itemHtml);
                        countCol1++;
                    } else if (clsLevel !== null && clsLevel > level3) {
                        $('#col3_current_class_list').append(itemHtml);
                        countCol3++;
                    } else {
                        $('#col1_current_class_list').append(itemHtml);
                        countCol1++;
                    }
                }
            });

            if (countCol1 === 0) $('#col1_current_class_list').html('<span class="text-muted fs-8 italic text-center py-2">- Kosong -</span>');
            if (countCol2 === 0) $('#col2_current_class_list').html('<span class="text-muted fs-8 italic text-center py-2">- Kosong -</span>');
            if (countCol3 === 0) $('#col3_current_class_list').html('<span class="text-muted fs-8 italic text-center py-2">- Kosong -</span>');

            $('#btn_trigger_current_classroom').prop('disabled', false);
        }

        // Relational classroom filters for Grade Promotion
        function getClassroomBySchoolId(schoolId) {
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: { school_id: schoolId },
                success: function(response) {
                    $('#filter_classroom_id').val('');
                    $('#label_selected_current_classroom').text('Pilih Kelas').removeClass('text-gray-900 fw-bolder').addClass('text-muted');
                    $('#btn_trigger_current_classroom').prop('disabled', true);
                    $('#col1_current_class_list, #col2_current_class_list, #col3_current_class_list').empty();

                    $('#filter_new_classroom').val('');
                    $('#label_selected_target_classroom').text('Pilih Kelas Tujuan').removeClass('text-gray-900 fw-bolder').addClass('text-muted');
                    $('#btn_trigger_target_classroom').prop('disabled', true);
                    $('#col1_class_list, #col2_class_list, #col3_class_list').html('<span class="text-muted fs-8 italic text-center py-3">Pilih kelas saat ini</span>');

                    currentSchoolClassrooms = response.data || [];

                    if (currentSchoolClassrooms.length > 0) {
                        renderCurrentClassroomOptions();
                    } else {
                        $('#col1_current_class_list, #col2_current_class_list, #col3_current_class_list').html('<span class="text-muted fs-9 italic text-center py-2">- Tidak ada -</span>');
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

            // Sub-Tab Migration Mode Button Click Handler
            $('.btn-subtab-migration').on('click', function() {
                $('.btn-subtab-migration').removeClass('active shadow-sm text-white').addClass('text-gray-700 bg-light');
                $(this).addClass('active shadow-sm text-white').removeClass('text-gray-700 bg-light');

                const mode = $(this).data('type');
                $('#input_migration_type').val(mode);

                if (mode === 'transfer') {
                    $('#btn_change_label').text('Proses Pindah');
                    $('#btn_change_classroom i').attr('class', 'fa-solid fa-paper-plane me-1');
                } else {
                    $('#btn_change_label').text('Proses Kenaikan');
                    $('#btn_change_classroom i').attr('class', 'fa-solid fa-graduation-cap me-1');
                }

                updateTargetClassroomOptions();
                updateAcademicYearOptions();
            });

            // Initial load of Academic Year Options
            updateAcademicYearOptions();

            // Selection handler for 3-Column Current Classroom Picker items
            $(document).on('click', '.btn-select-current-class', function(e) {
                e.preventDefault();
                if ($(this).prop('disabled')) return;

                var classId = $(this).data('id');
                var className = $(this).data('name');

                $('#filter_classroom_id').val(classId);
                $('#label_selected_current_classroom')
                    .html(`<i class="fa-solid fa-circle-check text-success me-1"></i><span class="text-gray-900 fw-bolder">${className}</span>`)
                    .removeClass('text-muted');

                $('.btn-select-current-class').removeClass('active bg-primary text-white shadow-xs').addClass('btn-light-primary text-primary');
                $(this).addClass('active bg-primary text-white shadow-xs').removeClass('btn-light-primary text-primary');

                // Hide Bootstrap Dropdown Menu
                var $triggerBtn = $('#btn_trigger_current_classroom');
                if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                    var dropdownInstance = bootstrap.Dropdown.getInstance($triggerBtn[0]) || new bootstrap.Dropdown($triggerBtn[0]);
                    if (dropdownInstance) dropdownInstance.hide();
                } else if ($.fn.dropdown) {
                    $triggerBtn.dropdown('hide');
                }

                selectedStudentIds.clear();
                updateSelectedCounter();
                updateTargetClassroomOptions();
                searchPromotion();
            });

            // Selection handler for 3-Column Target Classroom Picker items
            $(document).on('click', '.btn-select-target-class', function(e) {
                e.preventDefault();
                if ($(this).prop('disabled')) return;

                var classId = $(this).data('id');
                var className = $(this).data('name');

                $('#filter_new_classroom').val(classId);
                $('#label_selected_target_classroom')
                    .html(`<i class="fa-solid fa-circle-check text-success me-1"></i><span class="text-gray-900 fw-bolder">${className}</span>`)
                    .removeClass('text-muted');

                $('.btn-select-target-class').removeClass('active bg-primary text-white shadow-xs').addClass('btn-light-primary text-primary');
                $(this).addClass('active bg-primary text-white shadow-xs').removeClass('btn-light-primary text-primary');

                // Hide Bootstrap Dropdown Menu
                var $triggerBtn = $('#btn_trigger_target_classroom');
                if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                    var dropdownInstance = bootstrap.Dropdown.getInstance($triggerBtn[0]) || new bootstrap.Dropdown($triggerBtn[0]);
                    if (dropdownInstance) dropdownInstance.hide();
                } else if ($.fn.dropdown) {
                    $triggerBtn.dropdown('hide');
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
                    $('#filter_classroom_id').val('');
                    $('#label_selected_current_classroom').text('Pilih Kelas').removeClass('text-gray-900 fw-bolder').addClass('text-muted');
                    $('#btn_trigger_current_classroom').prop('disabled', true);
                    $('#col1_current_class_list, #col2_current_class_list, #col3_current_class_list').html('<span class="text-muted fs-8 italic text-center py-3">Pilih UPT</span>');

                    $('#filter_new_classroom').val('');
                    $('#label_selected_target_classroom').text('Pilih Kelas Tujuan').removeClass('text-gray-900 fw-bolder').addClass('text-muted');
                    $('#btn_trigger_target_classroom').prop('disabled', true);
                    $('#col1_class_list, #col2_class_list, #col3_class_list').html('<span class="text-muted fs-8 italic text-center py-3">Pilih kelas saat ini</span>');
                    currentSchoolClassrooms = [];
                    if ($.fn.DataTable.isDataTable('#table-grade-promotion')) {
                        tablePromotion.destroy();
                        $('#table-grade-promotion tbody').empty();
                    }
                }
            });

            $('#filter_classroom_id').on('change', function() {
                selectedStudentIds.clear();
                updateSelectedCounter();
                updateTargetClassroomOptions();
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

            // Wide Data Table Modal Click Handler for Unpaid Bills Details (Grouped Layout)
            $(document).on('click', '.btn-unpaid-details', function() {
                var studentName = $(this).data('student-name') || 'Siswa';
                var studentNis = $(this).data('student-nis') || '-';
                var rawBills = $(this).attr('data-bills');
                var bills = [];

                try {
                    bills = typeof rawBills === 'string' ? JSON.parse(rawBills) : rawBills;
                } catch(e) {
                    console.error('Failed to parse bills JSON', e);
                }

                $('#modal_unpaid_student_name').text('Detail Tunggakan: ' + studentName);
                $('#modal_unpaid_student_nis').text('NIS: ' + studentNis);

                var $tbody = $('#unpaid_bills_tbody');
                $tbody.empty();

                var totalGrandAmount = 0;

                if (bills && bills.length > 0) {
                    // Group bills by bill name & academic year
                    var groupedBills = {};

                    $.each(bills, function(index, bill) {
                        var name = bill.name || 'Tagihan';
                        var ay = bill.academic_year || '-';
                        var key = name + '___' + ay;

                        if (!groupedBills[key]) {
                            groupedBills[key] = {
                                name: name,
                                academic_year: ay,
                                months: [],
                                total_amount: 0
                            };
                        }

                        if (bill.month) {
                            groupedBills[key].months.push(bill.month);
                        }
                        var amount = parseFloat(bill.amount) || 0;
                        groupedBills[key].total_amount += amount;
                        totalGrandAmount += amount;
                    });

                    var no = 1;
                    $.each(groupedBills, function(key, group) {
                        var monthBadgesHtml = '';
                        if (group.months.length > 0) {
                            monthBadgesHtml = '<div class="d-grid gap-2" style="grid-template-columns: repeat(3, minmax(0, 1fr)); max-width: 360px;">';
                            $.each(group.months, function(i, monthName) {
                                monthBadgesHtml += `<span class="badge bg-light-primary text-primary fs-8 fw-bold text-center px-2 py-1 border border-primary border-opacity-10">${monthName}</span>`;
                            });
                            monthBadgesHtml += '</div>';
                        } else {
                            monthBadgesHtml = '<span class="text-gray-400 fs-8 fw-normal">-</span>';
                        }

                        var formattedTotal = 'Rp ' + group.total_amount.toLocaleString('id-ID');

                        var rowHtml = `<tr>
                            <td class="align-top pt-4">${no}</td>
                            <td class="align-top pt-4">
                                <div class="fw-bolder text-gray-900 fs-6 mb-1">${group.name}</div>
                                <span class="badge bg-light-primary text-primary border border-primary border-opacity-25 fs-7 fw-bolder px-3 py-1 mt-1 shadow-xs"><i class="fa-solid fa-calendar-days me-1 fs-8 text-primary"></i> TA ${group.academic_year}</span>
                            </td>
                            <td class="align-top pt-4">${monthBadgesHtml}</td>
                            <td class="align-top pt-4 text-end text-danger fw-bolder fs-6">${formattedTotal}</td>
                        </tr>`;

                        $tbody.append(rowHtml);
                        no++;
                    });
                } else {
                    $tbody.append('<tr><td colspan="4" class="text-center text-gray-500 py-4">Tidak ada detail tunggakan</td></tr>');
                }

                var formattedGrandTotal = 'Rp ' + totalGrandAmount.toLocaleString('id-ID');
                $('#unpaid_bills_total').text(formattedGrandTotal);

                $('#modal_unpaid_bills_detail').modal('show');
            });

            // Dynamic Destination Badge Helper for Graduation
            function updateGraduationDestinationBadge() {
                var schoolOption = $('#filter_school_id_grad option:selected');
                var schoolType = schoolOption.data('type') || '';
                var option = $('#filter_graduation_option').val();

                if (option === 'keluar') {
                    $('#graduation_destination_badge').text('Siswa berstatus LULUS / KELUAR. Tunggakan masa lalu tetap tersimpan utuh sampai dilunasi, dan tidak akan ditagih tagihan baru.');
                } else {
                    if (schoolType === 'SMP') {
                        $('#graduation_destination_badge').text('SMP ➔ Otomatis Lanjut ke MA (Di-plot ke Kelas 10-Transit). Status tetap AKTIF & NIS Lokal Permanen.');
                    } else if (schoolType === 'MA') {
                        $('#graduation_destination_badge').text('MA ➔ Otomatis Lanjut ke Pondok (Di-plot ke Kelas Pondok-Transit). Status tetap AKTIF & NIS Lokal Permanen.');
                    } else {
                        $('#graduation_destination_badge').text('SMP ➔ Otomatis Lanjut ke MA (Kelas 10-Transit) | MA ➔ Otomatis Lanjut ke Pondok (Kelas Pondok-Transit). Status tetap AKTIF & NIS Lokal Permanen.');
                    }
                }
            }

            $('#filter_school_id_grad, #filter_graduation_option').on('change', function() {
                updateGraduationDestinationBadge();
            });

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
            updateGraduationDestinationBadge();
            @endcan
        });
    </script>
@endpush
