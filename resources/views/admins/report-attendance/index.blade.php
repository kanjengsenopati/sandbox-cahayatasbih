@extends('layouts.master', ['title' => 'Laporan Presensi'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan Presensi</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Laporan Presensi</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            
            <!--begin::Tabs-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_siswa_santri">
                        <i class="fa-solid fa-graduation-cap me-2"></i>Siswa dan Santri
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_karyawan" id="btn-tab-karyawan">
                        <i class="fa-solid fa-user-tie me-2"></i>Karyawan & Staff
                    </a>
                </li>
            </ul>
            <!--end::Tabs-->

            <!--begin::Tab Content-->
            <div class="tab-content" id="reportAttendanceTabContent">
                <!--begin::Tab Siswa dan Santri-->
                <div class="tab-pane fade show active" id="tab_siswa_santri" role="tabpanel">
                    <!--begin::Filter-->
                    <div class="card mb-8">
                        <div class="card-body">
                            <form id="form-filter-siswa" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Sekolah</label>
                                    <select name="school_id" id="filter-siswa-school" class="form-select select2-filter" data-control="select2" data-placeholder="Semua Sekolah">
                                        <option value="">Semua Sekolah</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Kegiatan</label>
                                    <select name="activity" id="filter-siswa-activity" class="form-select" data-placeholder="Semua Kegiatan">
                                        <option value="">Semua Kegiatan</option>
                                        <option value="school">Sekolah (Presensi Harian)</option>
                                        <option value="prayer">Sholat Berjamaah</option>
                                        <option value="kajian">Ngaji Murojaah / Kajian</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Mulai Tanggal</label>
                                    <input type="date" name="start_date" id="filter-siswa-start-date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Sampai Tanggal</label>
                                    <input type="date" name="end_date" id="filter-siswa-end-date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::Filter-->

                    <!--begin::Card Tabel-->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table-report-siswa" class="table table-striped border rounded gy-5 gs-7 align-middle w-100">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                            <th width="3%">No</th>
                                            <th>Nama Santri</th>
                                            <th>Sekolah</th>
                                            <th>Tipe</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th class="text-center">Status</th>
                                            <th>Keterlambatan</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Card Tabel-->
                </div>
                <!--end::Tab Siswa dan Santri-->

                <!--begin::Tab Karyawan-->
                <div class="tab-pane fade" id="tab_karyawan" role="tabpanel">
                    <!--begin::Filter-->
                    <div class="card mb-8">
                        <div class="card-body">
                            <form id="form-filter-karyawan" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Mulai Tanggal</label>
                                    <input type="date" name="start_date" id="filter-karyawan-start-date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sampai Tanggal</label>
                                    <input type="date" name="end_date" id="filter-karyawan-end-date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::Filter-->

                    <!--begin::Card Tabel-->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table-report-karyawan" class="table table-striped border rounded gy-5 gs-7 align-middle w-100">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                            <th width="3%">No</th>
                                            <th>Nama Karyawan</th>
                                            <th>Tipe Akun</th>
                                            <th>Nama Shift</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th class="text-center">Status</th>
                                            <th>Keterlambatan</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Card Tabel-->
                </div>
                <!--end::Tab Karyawan-->
            </div>
            <!--end::Tab Content-->

        </div>
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(() => {
        // Tab Siswa: DataTable
        const tableSiswa = $('#table-report-siswa').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report-attendance.index') }}",
                data: function(d) {
                    d.type = 'siswa_santri';
                    d.school_id = $('#filter-siswa-school').val();
                    d.activity = $('#filter-siswa-activity').val();
                    d.start_date = $('#filter-siswa-start-date').val();
                    d.end_date = $('#filter-siswa-end-date').val();
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                }
            },
            columns: [
                {
                    data: 'id',
                    class: 'text-center',
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                { data: 'student_name' },
                { data: 'school_name' },
                { data: 'activity_type_label', class: 'text-center' },
                { data: 'activity_name' },
                { data: 'check_in_formatted', class: 'text-center' },
                { data: 'check_out_formatted', class: 'text-center' },
                { data: 'status_badge', class: 'text-center' },
                { data: 'late_label', class: 'text-center' }
            ]
        });

        // Trigger reload when filter changes
        $('#filter-siswa-school, #filter-siswa-activity, #filter-siswa-start-date, #filter-siswa-end-date').on('change', function() {
            tableSiswa.ajax.reload();
        });

        // Tab Karyawan: DataTable (Lazy Loaded)
        let tableKaryawan = null;

        $('#btn-tab-karyawan').on('shown.bs.tab', function() {
            if (!tableKaryawan) {
                tableKaryawan = $('#table-report-karyawan').DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('report-attendance.index') }}",
                        data: function(d) {
                            d.type = 'karyawan';
                            d.start_date = $('#filter-karyawan-start-date').val();
                            d.end_date = $('#filter-karyawan-end-date').val();
                        }
                    },
                    language: {
                        paginate: {
                            next: "<i class='fa fa-angle-right'></i>",
                            previous: "<i class='fa fa-angle-left'></i>"
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            class: 'text-center',
                            render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                        },
                        { data: 'employee_name' },
                        { data: 'employee_type', class: 'text-center' },
                        { data: 'activity_name' },
                        { data: 'check_in_formatted', class: 'text-center' },
                        { data: 'check_out_formatted', class: 'text-center' },
                        { data: 'status_badge', class: 'text-center' },
                        { data: 'late_label', class: 'text-center' }
                    ]
                });
            } else {
                tableKaryawan.ajax.reload();
            }
        });

        // Trigger reload for Karyawan when date filters change
        $('#filter-karyawan-start-date, #filter-karyawan-end-date').on('change', function() {
            if (tableKaryawan) {
                tableKaryawan.ajax.reload();
            }
        });
    });
</script>
@endpush
