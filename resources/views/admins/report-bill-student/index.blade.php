@extends('layouts.master', ['title' => 'Laporan Tagihan Siswa'])
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
                        <a href="{{ route('report-bill-student.index') }}" class="text-muted text-hover-primary">Laporan
                            Tagihan Santri</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Tagihan</li>
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
                                        <label class="form-label">Lembaga</label>
                                        <select name="school_id" class="form-select form-select-sm"
                                            id="filter_school_id">
                                            <option value="">Semua Lembaga</option>
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
                                        <label class="form-label">Tagihan</label>
                                        <select name="bill_type_id[]" class="form-select form-select-sm"
                                            id="filter_tipe_tagihan" multiple="multiple">
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
                                </div>
                            </form>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <!-- Card for "Target Pemasukan" (Revenue Target) -->
                                <div class="card bg-light-warning bg-active-warning flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-bullseye text-warning fs-2"></i>
                                        </div>
                                        <div>
                                            <!--begin::Label-->
                                            <div class="fw-bolder fs-5 text-gray-800">Target Pemasukan</div>
                                            <!--end::Label-->
                                            <!--begin::Stats-->
                                            <div class="text-warning fs-3 fw-bolder" id="target-revenue">Rp. 0</div>
                                            <!--end::Stats-->
                                        </div>
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <!-- Card for "Lunas" (Paid) -->
                                <div class="card bg-light-primary bg-active-primary flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-check-circle text-primary fs-2"></i>
                                        </div>
                                        <div>
                                            <!--begin::Label-->
                                            <div class="fw-bolder fs-5 text-gray-800">Lunas</div>
                                            <!--end::Label-->
                                            <!--begin::Stats-->
                                            <div class="text-primary fs-3 fw-bolder" id="total-paid">Rp. 0</div>
                                            <!--end::Stats-->
                                        </div>
                                    </div>
                                    <!--end::Body-->
                                </div>

                                <!-- Card for "Belum Lunas" (Unpaid) -->
                                <div class="card bg-light-danger bg-active-danger flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-times-circle text-danger fs-2"></i>
                                        </div>
                                        <div>
                                            <!--begin::Label-->
                                            <div class="fw-bolder fs-5 text-gray-800">Belum Lunas</div>
                                            <!--end::Label-->
                                            <!--begin::Stats-->
                                            <div class="text-danger fs-3 fw-bolder" id="total-unpaid">Rp. 0</div>
                                            <!--end::Stats-->
                                        </div>
                                    </div>
                                    <!--end::Body-->
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-saldo" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                    <th style="width: 5%">No</th>
                                    <th>Tagihan</th>
                                    <th>Periode</th>
                                    <th>Santri</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
       $(document).ready(function() {
        $('#filter_school_id, #filter_classroom_id, #filter_admin, #filter_tipe_tagihan, #filter_status').on('change',
            function() {
                reloadTable();
            });
        });

        var start = moment().startOf('month');
        var end = moment().endOf('month');

        // Initialize date range picker
        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
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
            responsive: true,
            orderable: true,
            searchDelay: 300,
            ajax: {
                url: "{{ route('report-bill-student.index') }}",
                data: function(d) {
                    d.data = 'table';
                    d.school_id = $('#filter_school_id').val();
                    d.classroom_id = $('#filter_classroom_id').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.bill_type_id = $('#filter_tipe_tagihan').val();
                    d.status = $('#filter_status').val();
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
                { data: 'bill_type', name: 'bill_type', orderable: true, searchable: true },
                { data: 'period', name: 'period', orderable: true, searchable: true },
                { data: 'student', name: 'student', orderable: true, searchable: true },
                { data: 'amount', name: 'amount', orderable: true, searchable: true },
                { data: 'status', name: 'status', orderable: true, searchable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false,
                responsivePriority: -1
                }
            ]
        });
    }

    function getTotalSaldo() {
        $.ajax({
            url: "{{ route('report-bill-student.index') }}",
            type: "GET",
            dataType: 'json',
            data: {
                data: 'total',
                school_id: $('#filter_school_id').val(),
                classroom_id: $('#filter_classroom_id').val(),
                admin_id: $('#filter_admin').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                bill_type_id: $('#filter_tipe_tagihan').val(),
                status: $('#filter_status').val(),
            },
            success: function(response) {
                $('#total-paid').text('Rp. ' + response.total_paid);
                $('#total-unpaid').text('Rp. ' + response.total_unpaid);
                $('#target-revenue').text('Rp. ' + response.target_revenue);
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
<script>
    $(document).ready(function() {
        // Menginisialisasi Select2 pada elemen select
        $('#filter_tipe_tagihan').select2({
            placeholder: 'Pilih tagihan', // Placeholder
            allowClear: true // Menambahkan opsi untuk menghapus pilihan
        });
    });
</script>
@endpush