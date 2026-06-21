@extends('layouts.master', ['title' => 'Manajemen Karyawan & Payroll'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Manajemen Karyawan & Payroll</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Pondok Mart (Outlet)</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Karyawan & Payroll</li>
                </ul>
                <!--end::Breadcrumb-->
            </div>
            @include('layouts.partials.outlet_switcher')
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl px-5">
            <!--begin::Card-->
            <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]" style="border-radius: 24px; border: none;">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-2 pb-5">
                    <!--begin::Tabs Nav-->
                    <div class="card-title">
                        <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                            @can('Manage Karyawan')
                            <li class="nav-item">
                                <a class="nav-link active fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_karyawan">Data Karyawan</a>
                            </li>
                            @endcan
                            @can('Manage Shift')
                            <li class="nav-item">
                                <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_shift">Pengaturan Shift</a>
                            </li>
                            @endcan
                            @can('Manage Payroll')
                            <li class="nav-item">
                                <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_payroll" id="btn-tab-payroll">Pengaturan Payroll</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                    <!--end::Tabs Nav-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="tab-content" id="karyawanTabContent">
                        <!--Tab Karyawan-->
                        @can('Manage Karyawan')
                        <div class="tab-pane fade show active" id="tab_karyawan" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="card-title">
                                    <h3 class="text-dark">Data Karyawan</h3>
                                </div>
                                <div class="card-toolbar">
                                    @can('Create Karyawan')
                                    <a href="{{ route('karyawan.create', request()->only(['mode', 'outlet_id'])) }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i> Tambah Karyawan
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-karyawan" class="table align-middle table-row-dashed gy-5 gs-7 w-100">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Nama Karyawan</th>
                                            <th>Kamar</th>
                                            <th>Jabatan</th>
                                            <th>Section</th>
                                            <th>Gaji / Bulan</th>
                                            <th>Gaji / Hari</th>
                                            <th>Hari Kerja</th>
                                            <th>Outlet</th>
                                            <th class="text-center min-w-100px" style="width: 15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        @endcan

                        <!--Tab Shift-->
                        @can('Manage Shift')
                        <div class="tab-pane fade" id="tab_shift" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="card-title">
                                    <h3 class="text-dark">Shift Presensi</h3>
                                </div>
                                <div class="card-toolbar">
                                    <x-action.create name="Shift" action="{{ route('working-shift.create', request()->only(['mode', 'outlet_id'])) }}" />
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="table-working-shift" class="table align-middle table-row-dashed w-100">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Nama Shift</th>
                                            <th>Target Pengguna</th>
                                            <th>Hari Aktif</th>
                                            <th>Jam Masuk</th>
                                            <th>Jam Keluar</th>
                                            <th>Toleransi (Menit)</th>
                                            <th>Status</th>
                                            <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        @endcan

                        <!--Tab Payroll-->
                        @can('Manage Payroll')
                        <div class="tab-pane fade" id="tab_payroll" role="tabpanel">
                            <!--Sub tabs-->
                            <div class="card card-flush shadow-none p-0 m-0">
                                <div class="card-header border-0 p-0 mb-5">
                                    <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                                        <li class="nav-item">
                                            <a class="nav-link active fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_slip_gaji">
                                                <i class="fa-solid fa-file-invoice-dollar me-2"></i>Slip Gaji Karyawan
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_pengaturan_gaji" id="btn-tab-settings">
                                                <i class="fa-solid fa-gears me-2"></i>Pengaturan Gaji Karyawan
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-0">
                                    <div class="tab-content" id="payrollSubTabContent">
                                        <!--Sub Tab Slip Gaji-->
                                        <div class="tab-pane fade show active" id="tab_slip_gaji" role="tabpanel">
                                            <!--Card Pemrosesan-->
                                            <div class="card mb-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100">
                                                <div class="card-header border-0 pt-6">
                                                    <div class="card-title">
                                                        <h2>Kalkulasi Gaji Baru (Drafting)</h2>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <form id="form-process-payroll" class="row g-3 align-items-end">
                                                        @csrf
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Tanggal Mulai Periode</label>
                                                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Tanggal Selesai Periode</label>
                                                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button type="submit" class="btn btn-primary w-100" id="btn-process">
                                                                <i class="fa fa-cogs"></i> Kalkulasi Gaji Karyawan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <!--Card Tabel-->
                                            <div class="card shadow-none p-0">
                                                <div class="card-header border-0 pt-6 px-0">
                                                    <div class="card-title">
                                                        <h2>Daftar Slip Gaji Karyawan</h2>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0 px-0">
                                                    <div class="table-responsive">
                                                        <table id="table-payroll" class="table table-striped border rounded gy-5 gs-7 align-middle w-100">
                                                            <thead>
                                                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                                                    <th width="3%">No</th>
                                                                    <th>Nama Karyawan</th>
                                                                    <th>Periode</th>
                                                                    <th>Gaji Bersih (Net)</th>
                                                                    <th class="text-center">Status</th>
                                                                    <th class="text-center min-w-100px">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--Sub Tab Pengaturan Gaji-->
                                        <div class="tab-pane fade" id="tab_pengaturan_gaji" role="tabpanel">
                                            <div class="card shadow-none p-0">
                                                <div class="card-header border-0 pt-6 px-0">
                                                    <div class="card-title">
                                                        <h2>Daftar Pengaturan Gaji Karyawan</h2>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0 px-0">
                                                    <div class="table-responsive">
                                                        <table id="table-salary-settings" class="table table-striped border rounded gy-5 gs-7 align-middle w-100">
                                                            <thead>
                                                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                                                    <th width="3%">No</th>
                                                                    <th>Nama Karyawan</th>
                                                                    <th>Tipe</th>
                                                                    <th>Jabatan/Role</th>
                                                                    <th>Gaji Pokok</th>
                                                                    <th>Tunjangan Kehadiran</th>
                                                                    <th>Denda Keterlambatan</th>
                                                                    <th class="text-center min-w-100px">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>

<!--begin::Modal Edit Gaji-->
<div class="modal fade" id="modal-edit-salary" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Pengaturan Gaji Karyawan</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="fa-solid fa-xmark fs-2"></i>
                    </span>
                </div>
            </div>
            <form id="form-edit-salary">
                @csrf
                <input type="hidden" name="employee_id" id="edit-employee-id">
                <input type="hidden" name="employee_type" id="edit-employee-type">
                
                <div class="modal-body py-10 px-lg-17">
                    <div class="d-flex flex-column mb-5 fv-row">
                        <label class="fs-6 fw-bold mb-2">Nama Karyawan</label>
                        <input type="text" class="form-control form-control-solid" id="edit-employee-name" readonly />
                    </div>

                    <div class="row g-9 mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Gaji Pokok (Rp)</label>
                            <input type="text" class="form-control form-control-solid format-rupiah" name="base_salary" id="edit-base-salary" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Denda Mangkir / Absen (Rp/Hari)</label>
                            <input type="text" class="form-control form-control-solid format-rupiah" name="absence_penalty" id="edit-absence-penalty" required />
                        </div>
                    </div>

                    <div class="row g-9 mb-5 row-allowance">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Tunjangan Kehadiran (Rp/Hari)</label>
                            <input type="text" class="form-control form-control-solid format-rupiah" name="attendance_allowance" id="edit-attendance-allowance" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Tunjangan Transport (Rp/Hari)</label>
                            <input type="text" class="form-control form-control-solid format-rupiah" name="transport_allowance" id="edit-transport-allowance" required />
                        </div>
                    </div>

                    <div class="row-lateness">
                        <div class="separator separator-dashed my-5"></div>

                        <div class="d-flex flex-stack mb-5">
                            <div class="me-5">
                                <label class="fs-6 fw-bold">Potongan Keterlambatan</label>
                                <div class="fs-7 text-muted">Aktifkan atau nonaktifkan denda keterlambatan per menit</div>
                            </div>
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="toggle-lateness" checked />
                                <span class="form-check-label fw-bold text-muted" id="label-toggle-lateness">Aktif</span>
                            </label>
                        </div>

                        <div id="section-lateness-settings" class="row g-9 mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-bold mb-2">Tipe Potongan</label>
                                <select class="form-select form-select-solid" name="lateness_penalty_type" id="edit-lateness-penalty-type" required>
                                    <option value="fixed">Nominal Tetap (Rp/Menit)</option>
                                    <option value="percentage">Persentase (% Gaji Pokok/Menit)</option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-bold mb-2">Nilai Potongan (per Menit)</label>
                                <input type="text" class="form-control form-control-solid" name="lateness_penalty_value" id="edit-lateness-penalty-value" required />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-salary">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Modal Edit Gaji-->

@endsection

@push('js')
<script>
    $(document).ready(() => {
        // Redraw DataTables saat tab berganti untuk mencegah bug layout
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // 1. DataTable Karyawan
        @can('Manage Karyawan')
        var tableKaryawan = $('#table-karyawan').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('karyawan.index') }}",
                data: function(d) {
                    d.mode = "{{ request('mode') }}";
                    d.outlet_id = "{{ request('outlet_id') }}";
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'employee_name', name: 'admin.name' },
                { data: 'kamar', name: 'kamar' },
                { data: 'jabatan', name: 'jabatan' },
                { data: 'section', name: 'section' },
                { data: 'gaji_bulan_formatted', name: 'gaji_bulan' },
                { data: 'gaji_hari_formatted', name: 'gaji_hari' },
                { data: 'hari_kerja', name: 'hari_kerja' },
                { data: 'outlet_name', name: 'outlet.name' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        @endcan

        // 2. DataTable Shift Presensi
        @can('Manage Shift')
        var tableShift = $('#table-working-shift').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('working-shift.index') }}",
                data: function(d) {
                    d.mode = "{{ request('mode') }}";
                    d.outlet_id = "{{ request('outlet_id') }}";
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'name', name: 'name' },
                { data: 'target_label', name: 'target_type' },
                { data: 'days_label', name: 'days' },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                { data: 'grace_period', name: 'grace_period' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: true, searchable: true },
            ]
        });
        @endcan

        // 3. DataTable Payroll & Pengaturan Gaji
        @can('Manage Payroll')
        const tablePayroll = $('#table-payroll').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('payroll.index') }}",
                data: function(d) {
                    d.mode = "{{ request('mode') }}";
                    d.outlet_id = "{{ request('outlet_id') }}";
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
                { data: 'period', class: 'text-center' },
                { data: 'net_salary_formatted', class: 'text-end' },
                { data: 'status_badge', class: 'text-center' },
                { data: 'btnAction', class: 'text-center' }
            ]
        });

        let tableSalarySettings = null;
        
        $('#btn-tab-settings').on('shown.bs.tab', function() {
            if (!tableSalarySettings) {
                tableSalarySettings = $('#table-salary-settings').DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('payroll.settings') }}",
                        data: function(d) {
                            d.mode = "{{ request('mode') }}";
                            d.outlet_id = "{{ request('outlet_id') }}";
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
                        { data: 'role_label' },
                        { data: 'base_salary_label', class: 'text-end' },
                        { data: 'allowance_label' },
                        { data: 'lateness_penalty_label' },
                        { data: 'btnAction', class: 'text-center' }
                    ]
                });
            } else {
                tableSalarySettings.ajax.reload();
            }
        });

        // Proses Kalkulasi Slip Gaji
        $('#form-process-payroll').on('submit', function (e) {
            e.preventDefault();
            const btn = $('#btn-process');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            let formData = $(this).serializeArray();
            formData.push({ name: 'mode', value: "{{ request('mode') }}" });
            formData.push({ name: 'outlet_id', value: "{{ request('outlet_id') }}" });

            $.ajax({
                url: "{{ route('payroll.process') }}",
                type: "POST",
                data: formData,
                success: (res) => {
                    btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> Kalkulasi Gaji Karyawan');
                    if (res.success) {
                        toastr.success(res.message);
                        tablePayroll.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: (xhr) => {
                    btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> Kalkulasi Gaji Karyawan');
                    toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan sistem.');
                }
            });
        });

        // Fungsi memformat angka menjadi format rupiah (pemisah ribuan titik)
        function formatRupiah(angka) {
            if (!angka && angka !== 0) return '';
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Format otomatis ketika pengguna mengetik data
        $(document).on('input', '.format-rupiah', function() {
            let val = $(this).val().replace(/\./g, ''); // Hapus semua titik dulu
            if (val !== '') {
                $(this).val(formatRupiah(val));
            }
        });

        // Edit Gaji Karyawan (Tampilkan Modal)
        $(document).on('click', '.btn-edit-salary', function() {
            const data = $(this).data();
            $('#edit-employee-id').val(data.id);
            $('#edit-employee-type').val(data.type);
            $('#edit-employee-name').val(data.name);
            $('#form-edit-salary').data('is_karyawan', data.is_karyawan);

            // Format nominal rupiah
            $('#edit-base-salary').val(formatRupiah(data.base_salary));
            $('#edit-absence-penalty').val(formatRupiah(data.absence_penalty));
            $('#edit-attendance-allowance').val(formatRupiah(data.attendance_allowance));
            $('#edit-transport-allowance').val(formatRupiah(data.transport_allowance));

            $('#edit-lateness-penalty-type').val(data.lateness_penalty_type);

            // Sesuaikan format potongan keterlambatan
            if (data.lateness_penalty_type === 'fixed') {
                $('#edit-lateness-penalty-value').addClass('format-rupiah').val(formatRupiah(data.lateness_penalty_value));
            } else {
                $('#edit-lateness-penalty-value').removeClass('format-rupiah').val(data.lateness_penalty_value);
            }

            // Handle toggle denda keterlambatan
            if (parseInt(data.lateness_penalty_value) === 0) {
                $('#toggle-lateness').prop('checked', false);
                $('#section-lateness-settings').hide();
                $('#label-toggle-lateness').text('Tidak Aktif');
            } else {
                $('#toggle-lateness').prop('checked', true);
                $('#section-lateness-settings').show();
                $('#label-toggle-lateness').text('Aktif');
            }

            // Toggles berdasarkan tipe karyawan (is_karyawan)
            if (parseInt(data.is_karyawan) === 1) {
                $('.row-allowance').hide();
                $('.row-lateness').hide();
                $('#edit-attendance-allowance').prop('required', false);
                $('#edit-transport-allowance').prop('required', false);
                $('#edit-lateness-penalty-value').prop('required', false);
                
                $('#edit-absence-penalty').prop('readonly', true).css({
                    'background-color': '#f5f8fa',
                    'color': '#5e6278'
                });
                
                // Hitung otomatis denda absen dari base salary
                let baseSalary = parseInt(data.base_salary) || 0;
                let absencePenalty = Math.floor((baseSalary / 30) / 100) * 100;
                $('#edit-absence-penalty').val(formatRupiah(absencePenalty));
            } else {
                $('.row-allowance').show();
                $('.row-lateness').show();
                $('#edit-attendance-allowance').prop('required', true);
                $('#edit-transport-allowance').prop('required', true);
                $('#edit-lateness-penalty-value').prop('required', true);

                $('#edit-absence-penalty').prop('readonly', false).css({
                    'background-color': '',
                    'color': ''
                });
            }

            $('#modal-edit-salary').modal('show');
        });

        // Hitung otomatis denda mangkir/absen ketika Gaji Pokok diubah (khusus Karyawan Outlet)
        $('#edit-base-salary').on('input', function() {
            let isKaryawan = $('#form-edit-salary').data('is_karyawan');
            if (parseInt(isKaryawan) === 1) {
                let baseSalary = parseInt($(this).val().replace(/\./g, '')) || 0;
                let absencePenalty = Math.floor((baseSalary / 30) / 100) * 100;
                $('#edit-absence-penalty').val(formatRupiah(absencePenalty));
            }
        });

        // Listener perubahan tipe potongan keterlambatan (fixed vs percentage)
        $('#edit-lateness-penalty-type').on('change', function() {
            let type = $(this).val();
            let input = $('#edit-lateness-penalty-value');
            let currentVal = input.val().replace(/\./g, '');
            if (type === 'fixed') {
                input.addClass('format-rupiah');
                if (currentVal !== '') {
                    input.val(formatRupiah(currentVal));
                }
            } else {
                input.removeClass('format-rupiah');
                input.val(currentVal);
            }
        });

        // Toggle Potongan Keterlambatan Change Listener
        $('#toggle-lateness').on('change', function() {
            if ($(this).is(':checked')) {
                $('#section-lateness-settings').slideDown();
                $('#label-toggle-lateness').text('Aktif');
                let input = $('#edit-lateness-penalty-value');
                if (input.val() == 0 || input.val() == '') {
                    if ($('#edit-lateness-penalty-type').val() === 'fixed') {
                        input.addClass('format-rupiah').val(formatRupiah(1000));
                    } else {
                        input.removeClass('format-rupiah').val(1000);
                    }
                }
            } else {
                $('#section-lateness-settings').slideUp();
                $('#label-toggle-lateness').text('Tidak Aktif');
                $('#edit-lateness-penalty-value').val(0); // Zero value
            }
        });

        // Simpan Form Gaji Karyawan
        $('#form-edit-salary').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btn-save-salary');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            // Bersihkan titik sementara untuk serialize agar backend menerima numeric murni
            let originalValues = [];
            $('.format-rupiah').each(function() {
                originalValues.push({
                    el: $(this),
                    val: $(this).val()
                });
                $(this).val($(this).val().replace(/\./g, ''));
            });

            let formData = $(this).serialize();

            // Kembalikan nilai dengan titik segera setelah serialize selesai
            originalValues.forEach(function(item) {
                item.el.val(item.val);
            });

            $.ajax({
                url: "{{ route('payroll.settings.save') }}",
                type: "POST",
                data: formData,
                success: (res) => {
                    btn.prop('disabled', false).text('Simpan');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#modal-edit-salary').modal('hide');
                        if (tableSalarySettings) {
                            tableSalarySettings.ajax.reload();
                        }
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: (xhr) => {
                    btn.prop('disabled', false).text('Simpan');
                    toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan pengaturan gaji.');
                }
            });
        });
        @endcan
    });
</script>
@endpush
