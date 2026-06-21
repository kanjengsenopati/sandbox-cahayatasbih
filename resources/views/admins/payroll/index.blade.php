@extends('layouts.master', ['title' => 'Data Penggajian Karyawan'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Penggajian Karyawan</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('payroll.index') }}" class="text-muted text-hover-primary">Payroll</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Daftar Slip Gaji</li>
                </ul>
            </div>
            @include('layouts.partials.outlet_switcher')
        </div>
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            
            <!--begin::Tabs-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
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
            <!--end::Tabs-->

            <!--begin::Tab Content-->
            <div class="tab-content" id="payrollTabContent">
                <!--begin::Tab Slip Gaji-->
                <div class="tab-pane fade show active" id="tab_slip_gaji" role="tabpanel">
                    <!--begin::Card Pemrosesan-->
                    <div class="card mb-8">
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
                    <!--end::Card Pemrosesan-->

                    <!--begin::Card Tabel-->
                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h2>Daftar Slip Gaji Karyawan</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="table-payroll" class="table table-striped border rounded gy-5 gs-7 align-middle">
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
                    <!--end::Card Tabel-->
                </div>
                <!--end::Tab Slip Gaji-->

                <!--begin::Tab Pengaturan Gaji-->
                <div class="tab-pane fade" id="tab_pengaturan_gaji" role="tabpanel">
                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h2>Daftar Pengaturan Gaji Karyawan</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
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
                <!--end::Tab Pengaturan Gaji-->
            </div>
            <!--end::Tab Content-->

        </div>
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

                    <div class="row g-9 mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Tunjangan Kehadiran (Rp/Hari)</label>
                            <input type="text" class="form-control form-control-solid format-rupiah" name="attendance_allowance" id="edit-attendance-allowance" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Tunjangan Transport (Rp/Hari)</label>
                            <input type="text" class="form-control form-control-solid format-rupiah" name="transport_allowance" id="edit-transport-allowance" required />
                        </div>
                    </div>

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
        // Tab 1: DataTable Slip Gaji
        const table = $('#table-payroll').DataTable({
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

        // Tab 2: DataTable Pengaturan Gaji
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
                        table.ajax.reload();
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

            // Format nominal rupiah
            $('#edit-base-salary').val(formatRupiah(data.base_salary));
            $('#edit-attendance-allowance').val(formatRupiah(data.attendance_allowance));
            $('#edit-transport-allowance').val(formatRupiah(data.transport_allowance));
            $('#edit-absence-penalty').val(formatRupiah(data.absence_penalty));

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

            $('#modal-edit-salary').modal('show');
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
    });
</script>
@endpush
