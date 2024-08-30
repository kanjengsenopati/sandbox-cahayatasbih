@extends('layouts.master', ['title' => 'Laporan Transaksi'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-transaction.index') }}" class="text-muted text-hover-primary">Laporan
                            Transaksi</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Transaksi</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card mb-5">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
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
                                        <label class="form-label">UPT</label>
                                        <select name="school_id" class="form-select form-select-sm"
                                            id="filter_school_id">
                                            <option value="">Semua UPT</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Kelas</label>
                                        <select name="classroom_id" class="form-select form-select-sm"
                                            id="filter_classroom_id">
                                            <option value="">Semua Kelas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Tipe</label>
                                        <select name="type_data" class="form-select form-select-sm" id="filter_status">
                                            <option value="">Semua</option>
                                            <option value="BILL">Tagihan</option>
                                            <option value="SALDO">Saldo</option>
                                            <option value="SAVING">Tabungan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Petugas</label>
                                        <select name="admin_id" class="form-select form-select-sm" id="filter_admin"
                                            onchange="reloadTable()">
                                            <option value="">Semua</option>
                                            @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--begin::Export dropdown-->
                                    <button type="button" class="btn btn-sm btn-primary" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="ki-duotone fa fa-caret-down fs-2"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        Export Report
                                    </button>
                                    <!--begin::Menu-->
                                    <div id="kt_datatable_example_export_menu"
                                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4"
                                        data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a type="button" class="menu-link btn-export px-3" data-type="xlsx">
                                                Export as Excel
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a type="button" class="menu-link btn-export px-3" data-type="csv">
                                                Export as CSV
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                    <!--end::Export dropdown-->
                                </div>
                            </form>

                            <div class="d-flex gap-2 mt-4">
                                <div class="card bg-light-primary bg-active-primary flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Penerimaan Pembayaran</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-primary fs-3 fw-bolder" id="total-bill">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <div class="card bg-light-danger bg-active-danger flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Saldo</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-danger fs-3 fw-bolder" id="total-saldo">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <div class="card bg-light-success bg-active-success flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Tabungan</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-success fs-3 fw-bolder" id="total-saving">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            {{-- <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">

                            </div> --}}
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-saldo" class="table align-middle table-row-dashed ">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Tanggal</th>
                                            <th>Nama</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                            <th>Metode Pembayaran</th>
                                            <th>Petugas</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Post-->

        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    var saldoTable;

    $(document).ready(function() {
        // Fetch classroom data on school_id change
        $('#filter_school_id').on('change', function() {
            let school_id = $(this).val();
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: { school_id: school_id },
                success: function(response) {
                    $('#filter_classroom_id').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
                    }
                }
            });
        });

        // Event handlers to reload the table
      $('#filter_school_id, #filter_classroom_id, #filter_status, #filter_admin').on('change', function() {
         reloadTable();
        });

        var start = moment().startOf('month');
        var end = moment().endOf('month');

        // Initialize date range picker
        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().endOf('month')],
                '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().endOf('month')],
                '9 Bulan Terakhir': [moment().subtract(9, 'month').startOf('month'), moment().endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            }
        }, function(start, end) {
            $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
            reloadTable();
        });

        // Set initial values
        $('#start_date').val(start.format('YYYY-MM-DD'));
        $('#end_date').val(end.format('YYYY-MM-DD'));
        $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));

        // Initialize table
        initializeTable();

        // Initial total saldo calculation
        getTotalSaldo();
    });

    function initializeTable() {
        if ($.fn.DataTable.isDataTable('#table-saldo')) {
            $('#table-saldo').DataTable().destroy();
        }
        saldoTable = $('#table-saldo').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report-transaction.index') }}",
                data: function(d) {
                    d.data = 'table';
                    d.school_id = $('#filter_school_id').val();
                    d.classroom_id = $('#filter_classroom_id').val();
                    d.type = $('#filter_status').val();
                    d.admin_id = $('#filter_admin').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
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
                { data: 'date', name: 'date', orderable: true, searchable: true },
                 {
                    data: 'student',
                    name: 'student.name',
                    orderable: true,
                    searchable: true,
                        render: function(data, type, row) {
                            let studentName = data && data.name ? data.name : 'N/A';
                            let classroomName = data && data.classroom && data.classroom.name
                            ? data.classroom.name
                            : '';
                    
                        return classroomName
                            ? studentName + ' (' + classroomName + ')'
                            : studentName;
                    }
                },
                { data: 'type', name: 'type', orderable: true, searchable: true },
                { data: 'amount', name: 'amount', orderable: true, searchable: true },
                { data: 'payment_method', name: 'payment_method', orderable: true, searchable: true },
                { data: 'admin.name', name: 'admin.name', orderable: true, searchable: true },
                { data: 'item', name: 'item', orderable: true, searchable: true }
            ]
        });
    }

    function getTotalSaldo() {
        $.ajax({
            url: "{{ route('report-transaction.index') }}",
            type: "GET",
            dataType: 'json',
            data: {
                data: 'total',
                school_id: $('#filter_school_id').val(),
                classroom_id: $('#filter_classroom_id').val(),
                type: $('#filter_status').val(),
                admin_id: $('#filter_admin').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            },
            success: function(response) {
                $('#total-bill').text('Rp. ' + response.total_bill);
                $('#total-saldo').text('Rp. ' + response.total_saldo);
                $('#total-saving').text('Rp. ' + response.saldo_saving);
            }
        });
    }

    // Function to reload DataTables and get total saldo
    function reloadTable() {
        saldoTable.ajax.reload();
        getTotalSaldo();
    }

    // onclick export button event handler to set the type and submit the form to export and action to route transaction.export
    $('.btn-export').on('click', function() {
        $('#type').val($(this).data('type'));
        $('#form-filter').attr('action', "{{ route('report-transaction.export') }}").submit();
    });
</script>
@endpush