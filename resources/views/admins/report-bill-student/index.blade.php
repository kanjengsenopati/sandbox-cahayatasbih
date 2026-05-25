@extends('layouts.master', ['title' => 'Laporan Tagihan Siswa'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-bill-student.index') }}" class="text-muted text-hover-primary">Laporan Tagihan Santri</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-dark">Data Tagihan</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <div class="card mb-5">
                <div class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-beetween gap-5">
                        <div class="mb-0">
                            <form action="#" id="form-filter" method="get">
                                <input type="text" hidden id="type" name="type" required>
                                <div class="d-flex flex-wrap gap-4 align-items-end">
                                    <div>
                                        <label class="form-label">Filter Tanggal</label>
                                        <div class="d-flex gap-4 align-items-end">
                                            <div id="dateRange" class="pull-right"
                                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                                <span></span> <b class="caret"></b>
                                            </div>
                                            <input type="text" id="start_date" name="start_date" hidden>
                                            <input type="text" id="end_date" name="end_date" hidden>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">Tahun Ajaran</label>
                                        <select name="academic_year_id" class="form-select form-select-sm" id="filter_academic_year_id">
                                            <option value="">Semua Tahun Ajaran</option>
                                            @foreach ($academicYears as $ay)
                                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Lembaga</label>
                                        <select name="school_id" class="form-select form-select-sm" id="filter_school_id">
                                            <option value="">Semua Lembaga</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Kelas</label>
                                        <select name="classroom_id" class="form-select form-select-sm" id="filter_classroom_id">
                                            <option value="">Semua Kelas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Tagihan</label>
                                        <select name="bill_type_id[]" class="form-select form-select-sm" id="filter_tipe_tagihan" multiple="multiple">
                                            @foreach ($billTypes as $billType)
                                            <option value="{{ $billType->id }}">{{ $billType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select form-select-sm" id="filter_status">
                                            <option value="">Semua</option>
                                            <option value="PAID">Lunas</option>
                                            <option value="UNPAID">Belum Lunas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="button" id="btn-wa-blast" class="btn btn-success btn-sm">
                                            <i class="fab fa-whatsapp"></i> Kirim WA Blast Tagihan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    {{-- Tabs Navigation --}}
                    <div class="d-flex bg-light rounded p-2 mb-5" style="width: fit-content;">
                        <ul class="nav nav-pills nav-pills-custom" id="reportTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active px-4 py-2 fw-bolder text-gray-600 me-2" id="data-tagihan-tab" data-bs-toggle="pill" href="#data-tagihan" role="tab" aria-controls="data-tagihan" aria-selected="true" style="transition: all 0.3s; border-radius: 8px;">
                                    <i class="fas fa-list me-2"></i>Data Tagihan
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link px-4 py-2 fw-bolder text-gray-600" id="rekap-santri-tab" data-bs-toggle="pill" href="#rekap-santri" role="tab" aria-controls="rekap-santri" aria-selected="false" style="transition: all 0.3s; border-radius: 8px;">
                                    <i class="fas fa-chart-bar me-2"></i>Rekap Per-Santri
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <style>
                        .nav-pills-custom .nav-link.active {
                            background-color: #2563eb;
                            color: white !important;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                        }
                        .nav-pills-custom .nav-link:not(.active):hover {
                            background-color: #e4e6ef;
                        }
                        /* Badge overrides for PakRT Premium colors */
                        .badge.badge-light-success {
                            background-color: rgba(16, 185, 129, 0.1) !important;
                            color: #10b981 !important;
                            font-weight: 700;
                        }
                        .badge.badge-light-danger {
                            background-color: rgba(220, 38, 38, 0.1) !important;
                            color: #dc2626 !important;
                            font-weight: 700;
                        }
                        .badge.badge-light-primary {
                            background-color: rgba(37, 99, 235, 0.1) !important;
                            color: #2563eb !important;
                            font-weight: 700;
                        }
                        .badge.badge-light-warning {
                            background-color: rgba(245, 158, 11, 0.1) !important;
                            color: #d97706 !important;
                            font-weight: 700;
                        }
                        .badge.badge-light-info {
                            background-color: rgba(6, 182, 212, 0.1) !important;
                            color: #0891b2 !important;
                            font-weight: 700;
                        }
                        @media (max-width: 767.98px) {
                            .table-responsive {
                                border: none !important;
                            }
                            /* Ensure table child row td has proper padding and doesn't cause overflow */
                            #table-rekap tbody tr.child td {
                                padding: 8px 12px !important;
                                background-color: #f8fafc !important;
                            }
                            .nested-wrapper {
                                width: 100% !important;
                                margin: 0 !important;
                                padding: 4px 0 !important;
                            }
                        }
                    </style>

                    <div class="tab-content" id="reportTabContent">
                        {{-- TAB 1: Data Tagihan --}}
                        <div class="tab-pane fade show active" id="data-tagihan" role="tabpanel" aria-labelledby="data-tagihan-tab">
                            {{-- Summary Cards --}}
                            <div class="d-flex flex-wrap gap-2 mt-4 mb-4" style="border: 1px solid #e0e0e0; padding: 16px; border-radius: 8px;">
                                <div class="card bg-light-warning bg-active-danger flex-grow-1">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3"><i class="fas fa-bullseye text-danger fs-2"></i></div>
                                        <div>
                                            <div class="fw-bolder fs-5 text-gray-800">Target Pemasukan</div>
                                            <div class="text-danger fs-3 fw-bolder" id="target-revenue">Rp. 0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card bg-light-primary bg-active-primary flex-grow-1">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3"><i class="fas fa-check-circle text-primary fs-2"></i></div>
                                        <div>
                                            <div class="fw-bolder fs-5 text-gray-800">Lunas</div>
                                            <div class="text-primary fs-3 fw-bolder" id="total-paid">Rp. 0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card bg-light-danger bg-active-danger flex-grow-1">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3"><i class="fas fa-times-circle text-danger fs-2"></i></div>
                                        <div>
                                            <div class="fw-bolder fs-5 text-gray-800">Belum Lunas</div>
                                            <div class="text-danger fs-3 fw-bolder" id="total-unpaid">Rp. 0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- DataTable --}}
                            <div class="table-responsive">
                                <table id="table-saldo" class="table table-striped border rounded gy-5 gs-7">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                            <th style="width: 5%">No</th>
                                            <th>Tagihan</th>
                                            <th>Periode</th>
                                            <th>Santri</th>
                                            <th>Notifikasi</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 2: Rekap Per-Santri --}}
                        <div class="tab-pane fade" id="rekap-santri" role="tabpanel" aria-labelledby="rekap-santri-tab">
                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <button type="button" id="btn-export-xlsx" class="btn btn-primary btn-sm">
                                    <i class="fas fa-file-excel me-2"></i>Export Rekap Santri
                                </button>
                                <button type="button" id="btn-share-report" class="btn btn-info btn-sm">
                                    <i class="fas fa-share-alt me-2"></i>Share Report
                                </button>
                            </div>

                            {{-- Summary Cards Rekap - Styled with PakRT guidelines --}}
                            <div class="d-flex flex-wrap gap-3 mt-4 mb-4" style="border: none; padding: 0;">
                                <div class="card flex-grow-1 border-0" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); background-color: rgba(37, 99, 235, 0.05); min-width: 200px;">
                                    <div class="card-body d-flex align-items-center p-6">
                                        <div class="me-4" style="background-color: rgba(37, 99, 235, 0.1); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-users text-primary fs-4" style="color: #2563eb !important;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold uppercase tracking-widest text-slate-400 mb-1" style="font-size: 11px; color: #94a3b8;">Total Santri</div>
                                            <div class="fs-3 fw-bolder" id="rekap-total-students" style="color: #0f172a;">0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card flex-grow-1 border-0" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); background-color: rgba(245, 158, 11, 0.05); min-width: 200px;">
                                    <div class="card-body d-flex align-items-center p-6">
                                        <div class="me-4" style="background-color: rgba(245, 158, 11, 0.1); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-bullseye text-warning fs-4" style="color: #f59e0b !important;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold uppercase tracking-widest text-slate-400 mb-1" style="font-size: 11px; color: #94a3b8;">Total Tagihan</div>
                                            <div class="fs-3 fw-bolder text-warning" id="rekap-total-amount" style="color: #d97706 !important;">Rp. 0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card flex-grow-1 border-0" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); background-color: rgba(16, 185, 129, 0.05); min-width: 200px;">
                                    <div class="card-body d-flex align-items-center p-6">
                                        <div class="me-4" style="background-color: rgba(16, 185, 129, 0.1); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-check-circle text-success fs-4" style="color: #10b981 !important;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold uppercase tracking-widest text-slate-400 mb-1" style="font-size: 11px; color: #94a3b8;">Total Lunas</div>
                                            <div class="fs-3 fw-bolder text-success" id="rekap-total-paid" style="color: #10b981 !important;">Rp. 0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card flex-grow-1 border-0" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); background-color: rgba(220, 38, 38, 0.05); min-width: 200px;">
                                    <div class="card-body d-flex align-items-center p-6">
                                        <div class="me-4" style="background-color: rgba(220, 38, 38, 0.1); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-exclamation-circle text-danger fs-4" style="color: #dc2626 !important;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold uppercase tracking-widest text-slate-400 mb-1" style="font-size: 11px; color: #94a3b8;">Belum Lunas</div>
                                            <div class="fs-3 fw-bolder text-danger" id="rekap-total-unpaid" style="color: #dc2626 !important;">Rp. 0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>{{-- DataTable Rekap --}}
                            <div class="table-responsive">
                                <table id="table-rekap" class="table table-striped border rounded gy-5 gs-7">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                            <th style="width: 20px;">#</th>
                                            <th>No.</th>
                                            <th>Santri</th>
                                            <th class="d-none d-md-table-cell">Jml Tagihan</th>
                                            <th class="d-none d-md-table-cell">Total Tagihan</th>
                                            <th class="d-none d-md-table-cell">Total Lunas</th>
                                            <th class="d-none d-md-table-cell">Belum Lunas</th>
                                            <th class="d-none d-md-table-cell">Tunggakan</th>
                                            <th class="d-none d-md-table-cell">Realisasi</th>
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
@endsection

@push('js')
<script>
var saldoTable, rekapTable;
var rekapInitialized = false;

$(document).ready(function() {
    // School -> Classroom cascade
    $('#filter_school_id').on('change', function() {
        $.ajax({
            url: "{{ route('report-bill.get-classroom') }}",
            type: "GET",
            data: { school_id: $(this).val() },
            success: function(response) {
                $('#filter_classroom_id').empty().append('<option value="">Semua Kelas</option>');
                $.each(response.data, function(key, value) {
                    $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            }
        });
    });

    // Filter change handlers
    $('#filter_school_id, #filter_classroom_id, #filter_tipe_tagihan, #filter_status, #filter_academic_year_id').on('change', function() {
        reloadAllTables();
    });

    // Date range picker
    var start = moment().startOf('month');
    var end = moment().endOf('month');

    $('#dateRange').daterangepicker({
        startDate: start, endDate: end,
        ranges: {
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().endOf('month')],
            '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().endOf('month')],
            'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        }
    }, function(s, e) {
        $('#dateRange span').html(s.format('D/MM/YYYY') + ' - ' + e.format('D/MM/YYYY'));
        $('#start_date').val(s.format('YYYY-MM-DD'));
        $('#end_date').val(e.format('YYYY-MM-DD'));
        reloadAllTables();
    });

    $('#start_date').val(start.format('YYYY-MM-DD'));
    $('#end_date').val(end.format('YYYY-MM-DD'));
    $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));

    // Select2
    $('#filter_tipe_tagihan').select2({ placeholder: 'Pilih tagihan', allowClear: true });

    // Init Tab 1
    initializeTable();
    getTotalSaldo();

    // Lazy-load Tab 2 on first click
    $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
        if (e.target.id === 'rekap-santri-tab' && !rekapInitialized) {
            initializeRekapTable();
            getRekapTotal();
            rekapInitialized = true;
        }
    });
});

function getFilterData() {
    return {
        school_id: $('#filter_school_id').val(),
        classroom_id: $('#filter_classroom_id').val(),
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        bill_type_id: $('#filter_tipe_tagihan').val(),
        status: $('#filter_status').val(),
        academic_year_id: $('#filter_academic_year_id').val(),
    };
}

// ===================== TAB 1 =====================
function initializeTable() {
    if ($.fn.DataTable.isDataTable('#table-saldo')) { $('#table-saldo').DataTable().destroy(); }
    saldoTable = $('#table-saldo').DataTable({
        processing: true, serverSide: true, responsive: true, searchDelay: 500,
        ajax: {
            url: "{{ route('report-bill-student.index') }}",
            data: function(d) { Object.assign(d, getFilterData()); d.data = 'table'; }
        },
        columns: [
            { data: null, sortable: false, searchable: false, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { data: 'bill_type', name: 'bill_type' },
            { data: 'period', name: 'period' },
            { data: 'student', name: 'student' },
            { data: 'notification', name: 'notification' },
            { data: 'amount', name: 'amount' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false, responsivePriority: -1 }
        ]
    });
}

function getTotalSaldo() {
    var params = getFilterData();
    params.data = 'total';
    $.ajax({
        url: "{{ route('report-bill-student.index') }}", type: "GET", dataType: 'json', data: params,
        success: function(r) {
            $('#total-paid').text('Rp. ' + r.total_paid);
            $('#total-unpaid').text('Rp. ' + r.total_unpaid);
            $('#target-revenue').text('Rp. ' + r.target_revenue);
        }
    });
}

// ===================== TAB 2 =====================
function initializeRekapTable() {
    if ($.fn.DataTable.isDataTable('#table-rekap')) { $('#table-rekap').DataTable().destroy(); }
    rekapTable = $('#table-rekap').DataTable({
        processing: true, serverSide: true, responsive: true, searchDelay: 500,
        ajax: {
            url: "{{ route('report-bill-student.index') }}",
            data: function(d) { Object.assign(d, getFilterData()); d.data = 'rekap_table'; }
        },
        columns: [
            {
                className: 'details-control text-center cursor-pointer',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: '<i class="fas fa-chevron-right fs-5 text-primary"></i>'
            },
            { data: null, sortable: false, searchable: false, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { data: 'student', name: 'student' },
            { data: 'bill_count_display', name: 'bill_count', orderable: true, searchable: false, className: 'd-none d-md-table-cell' },
            { data: 'total_bill_display', name: 'total_bill', orderable: true, searchable: false, className: 'd-none d-md-table-cell' },
            { data: 'total_paid_display', name: 'total_paid', orderable: true, searchable: false, className: 'd-none d-md-table-cell' },
            { data: 'total_unpaid_display', name: 'total_unpaid', orderable: true, searchable: false, className: 'd-none d-md-table-cell' },
            { data: 'current_due_display', name: 'current_due_amount', orderable: true, searchable: false, className: 'd-none d-md-table-cell' },
            { data: 'percentage', name: 'percentage', orderable: false, searchable: false, className: 'd-none d-md-table-cell' }
        ]
    });

    // Add event listener for opening and closing details
    $('#table-rekap tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = rekapTable.row(tr);
        var icon = $(this).find('i');

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            // Open this row
            icon.removeClass('fa-chevron-right').addClass('fa-spinner fa-spin'); // Loading state
            
            var params = getFilterData();
            params.data = 'rekap_detail';
            params.student_id = row.data().id;

            $.ajax({
                url: "{{ route('report-bill-student.index') }}",
                type: "GET",
                dataType: 'json',
                data: params,
                success: function(data) {
                    var html = '<div class="nested-wrapper" style="width: 100%; box-sizing: border-box;">' +
                        '<div class="d-flex align-items-center justify-content-between mb-3 px-2">' +
                            '<span class="text-slate-800 fw-bold fs-7" style="color: #1e293b; font-size: 13px;">Rincian Tagihan</span>' +
                            '<span class="badge fs-9 px-2 py-1 fw-bold" style="background-color: rgba(37, 99, 235, 0.1); color: #2563eb; border-radius: 20px;">' + data.length + ' Item</span>' +
                        '</div>';

                    // Desktop Table Layout
                    html += '<div class="table-responsive d-none d-md-block">' +
                        '<table class="table table-sm table-row-dashed fs-7 gy-3 align-middle" style="width: auto; min-width: 60%;">' +
                            '<thead>' +
                                '<tr class="text-start text-gray-500 fw-boldest fs-8 text-uppercase">' +
                                    '<th class="px-3">Tagihan</th>' +
                                    '<th class="px-3">Tahun Ajaran</th>' +
                                    '<th class="px-3">Periode</th>' +
                                    '<th class="px-3">Nominal</th>' +
                                    '<th class="px-3 text-center">Status</th>' +
                                    '<th class="px-3 text-center">Aksi</th>' +
                                </tr>' +
                            '</thead>' +
                            '<tbody>';

                    // Mobile Card List Layout (Clean, high density, proper visibility, action button included)
                    var mobileHtml = '<div class="d-flex flex-column gap-2 d-md-none" style="width: 100%; box-sizing: border-box;">';

                    if(data.length === 0) {
                        html += '<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada rincian data.</td></tr>';
                        mobileHtml += '<div class="text-center text-muted py-4">Tidak ada rincian data.</div>';
                    } else {
                        data.forEach(function(item) {
                            // Row for desktop table
                            html += '<tr>' +
                                '<td class="px-3 align-middle">' + item.bill_type + '</td>' +
                                '<td class="px-3 align-middle">' + item.academic_year + '</td>' +
                                '<td class="px-3 align-middle">' + item.period + '</td>' +
                                '<td class="px-3 fw-bold align-middle">' + item.amount + '</td>' +
                                '<td class="px-3 text-center align-middle">' + item.status + '</td>' +
                                '<td class="px-3 text-center align-middle">' + (item.action ? item.action : '-') + '</td>' +
                            '</tr>';

                            // Card for mobile list (optimised layout)
                            var actionHtml = item.action ? '<div class="flex items-center gap-2">' + item.action + '</div>' : '';
                            
                            mobileHtml += '<div class="card p-3 border-0" style="border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); background: white; width: 100%; box-sizing: border-box;">' +
                                '<div class="d-flex justify-content-between align-items-start gap-2 mb-2">' +
                                    '<div class="d-flex flex-column" style="max-width: 60%;">' +
                                        '<span class="text-slate-800 fw-bold fs-7" style="line-height: 1.3; color: #1e293b; font-size: 13px;">' + item.bill_type + '</span>' +
                                        '<span class="text-slate-400 fs-9 mt-1" style="color: #94a3b8; font-size: 10px;"><i class="far fa-calendar-alt me-1"></i>T.A ' + item.academic_year + '</span>' +
                                    '</div>' +
                                    '<div class="d-flex flex-column align-items-end gap-1.5" style="max-width: 40%;">' +
                                        '<div>' + item.status + '</div>' +
                                        (actionHtml ? '<div style="margin-top: 2px;">' + actionHtml + '</div>' : '') +
                                    '</div>' +
                                '</div>' +
                                '<div class="border-top border-slate-100 my-2" style="border-top: 1px solid #f1f5f9;"></div>' +
                                '<div class="d-flex justify-content-between align-items-center mt-2">' +
                                    '<div class="d-flex flex-column">' +
                                        '<span class="text-slate-400 uppercase tracking-wider fs-9 fw-bold" style="color: #94a3b8; font-size: 8px;">Periode</span>' +
                                        '<span class="text-slate-600 fs-8 fw-medium" style="color: #475569;">' + item.period + '</span>' +
                                    '</div>' +
                                    '<div class="text-end d-flex flex-column">' +
                                        '<span class="text-slate-400 uppercase tracking-wider fs-9 fw-bold" style="color: #94a3b8; font-size: 8px;">Nominal</span>' +
                                        '<span class="fs-7 fw-boldest text-slate-800" style="color: #0f172a; font-weight: 800; font-size: 13px; white-space: nowrap;">' + item.amount + '</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                        });
                    }

                    html += '</tbody></table></div>';
                    mobileHtml += '</div>';
                    
                    html += mobileHtml + '</div>';
                    row.child(html).show();
                    tr.addClass('shown');
                    icon.removeClass('fa-spinner fa-spin').addClass('fa-chevron-down');
                },
                error: function() {
                    icon.removeClass('fa-spinner fa-spin').addClass('fa-chevron-right');
                    Swal.fire('Error', 'Gagal memuat detail tagihan', 'error');
                }
            });
        }
    });
}

function getRekapTotal() {
    var params = getFilterData();
    params.data = 'rekap_total';
    $.ajax({
        url: "{{ route('report-bill-student.index') }}", type: "GET", dataType: 'json', data: params,
        success: function(r) {
            $('#rekap-total-students').text(r.total_students + ' Santri');
            $('#rekap-total-amount').text('Rp. ' + r.total_amount);
            $('#rekap-total-paid').text('Rp. ' + r.total_paid);
            $('#rekap-total-unpaid').text('Rp. ' + r.total_unpaid);
        }
    });
}

// ===================== RELOAD =====================
function reloadAllTables() {
    if (saldoTable) { saldoTable.ajax.reload(); }
    getTotalSaldo();
    if (rekapInitialized && rekapTable) { rekapTable.ajax.reload(); getRekapTotal(); }
}
</script>

{{-- WA Blast Script --}}
<script>
document.getElementById('btn-wa-blast').addEventListener('click', function () {
    const data = {
        school_id: document.getElementById('filter_school_id').value,
        classroom_id: document.getElementById('filter_classroom_id').value,
        bill_type_id: Array.from(document.getElementById('filter_tipe_tagihan').selectedOptions).map(o => o.value),
        status: document.getElementById('filter_status').value,
        start_date: document.getElementById('start_date').value,
        end_date: document.getElementById('end_date').value,
    };

    Swal.fire({
        title: 'Konfirmasi', text: 'Apakah Anda yakin ingin mengirim WA Blast Tagihan?',
        icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Kirim', cancelButtonText: 'Batal', reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Mohon Tunggu', text: 'WA Blast Sedang Dikirim...', icon: 'info', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            axios.post('/send-bill-whatsapp-notification', data, {
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            }).then(response => {
                Swal.fire({ icon: response.data.success ? 'success' : 'error', title: response.data.success ? 'Berhasil' : 'Gagal', text: response.data.message });
            }).catch(error => {
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: 'Terjadi kesalahan saat mengirim WA Blast.' });
            });
        }
    });
});

// Export XLSX
document.getElementById('btn-export-xlsx').addEventListener('click', function() {
    const params = new URLSearchParams(getFilterData()).toString();
    window.location.href = "{{ route('report-bill-student.export') }}?" + params;
});

// Share Report
document.getElementById('btn-share-report').addEventListener('click', function() {
    Swal.fire({
        title: 'Membagikan Laporan',
        html: `
            <div class="text-start mt-3">
                <p class="mb-4">Apakah Anda ingin membuat link publik untuk filter saat ini?</p>
                
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="swal-shorten-url" checked>
                    <label class="form-check-label fw-bold text-gray-800" for="swal-shorten-url">Perpendek URL</label>
                </div>
                
                <div id="swal-custom-url-container" class="mb-3" style="transition: opacity 0.2s ease;">
                    <label class="form-label fw-bold text-gray-700 mb-1" for="swal-custom-url">Custom URL (Opsional)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-slate-500" style="border-right: none;">{{ url('s') }}/</span>
                        <input type="text" class="form-control" id="swal-custom-url" placeholder="misal: tagihan-mei" style="border-left: none;">
                    </div>
                    <small class="text-muted d-block mt-1 fs-8">Kosongkan untuk menggunakan 8 karakter acak. Hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).</small>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Buat Link',
        cancelButtonText: 'Batal',
        didOpen: () => {
            const shortenSwitch = document.getElementById('swal-shorten-url');
            const customUrlContainer = document.getElementById('swal-custom-url-container');
            const customUrlInput = document.getElementById('swal-custom-url');

            shortenSwitch.addEventListener('change', function() {
                if (this.checked) {
                    customUrlContainer.style.opacity = '1';
                    customUrlInput.removeAttribute('disabled');
                } else {
                    customUrlContainer.style.opacity = '0.5';
                    customUrlInput.setAttribute('disabled', 'disabled');
                    customUrlInput.value = '';
                }
            });
        },
        preConfirm: () => {
            const shorten = document.getElementById('swal-shorten-url').checked;
            const customCode = document.getElementById('swal-custom-url').value;
            
            return { shorten, customCode };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { shorten, customCode } = result.value;

            Swal.fire({
                title: 'Mohon Tunggu',
                text: 'Sedang membuat link...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const params = getFilterData();
            params.shorten = shorten ? 1 : 0;
            params.custom_code = customCode;

            axios.get("{{ route('report-bill-student.share') }}", {
                params: params
            }).then(response => {
                Swal.fire({
                    icon: 'success',
                    title: 'Link Berhasil Dibuat',
                    html: `
                        <div class="mt-3">
                            <input type="text" class="form-control mb-3 text-center" id="share-url" value="${response.data.url}" readonly>
                            <button class="btn btn-primary btn-sm" onclick="copyShareUrl()">
                                <i class="fas fa-copy me-2"></i>Salin Link
                            </button>
                        </div>
                    `,
                });
            }).catch(error => {
                let errorMsg = 'Gagal membuat link share';
                if (error.response && error.response.data && error.response.data.message) {
                    errorMsg = error.response.data.message;
                }
                Swal.fire('Gagal', errorMsg, 'error');
            });
        }
    });
});

function copyShareUrl() {
    const copyText = document.getElementById("share-url");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    toastr.success('Link berhasil disalin!');
}
</script>
@endpush