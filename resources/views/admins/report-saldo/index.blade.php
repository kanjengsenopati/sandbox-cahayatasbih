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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan Transaksi</h1>
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
                            <form action="{{ route('report-saldo.export') }}" id="form-filter" method="get">
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
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">UPT</label>
                                        <select name="school_id" class="form-select" id="filter_school_id">
                                            <option value="">Semua UPT</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Kelas</label>
                                        <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                            <option value="">Semua Kelas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select" id="filter_status">
                                            <option value="">Semua</option>
                                            <option value="SUCCESS">Berhasil</option>
                                            <option value="PENDING">Pending</option>
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
                            {{-- add 3 card total topup saldo, pengurangan saldo, dan saldo tersedia --}}

                            <div class="d-flex gap-2 mt-4">
                                <div class="card bg-light-primary bg-active-primary flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Topup Saldo</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-primary fs-3 fw-bolder" id="total-topup">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <div class="card bg-light-danger bg-active-danger flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Pengurangan Saldo</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-danger fs-3 fw-bolder" id="total-pengurangan">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <div class="card bg-light-success bg-active-success flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Saldo Tersedia</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-success fs-3 fw-bolder" id="saldo-tersedia">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            {{-- <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">

                            </div> --}}
                            <!--end::Card title-->
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table id="table-saldo" class="table align-middle table-row-dashed ">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th>Tanggal</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Nominal</th>
                                        <th>Status</th>
                                        <th>Saldo Awal</th>
                                        <th>Saldo Akhir</th>
                                        <th>Keterangan</th>
                                        @if (Auth::user()->can('Delete Laporan Saldo Santri'))
                                        <th style="width: 10%">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold"></tbody>
                            </table>
                        </div>
                        <!--end::Table-->
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
</div>
</div>
@endsection
@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latenet/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function() {
        let table = initializeTable();
        getTotalSaldo();

        function initializeTable(start_date = '', end_date = '') {
            return $('#table-saldo').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('report-saldo.index') }}",
                    data: function(d) {
                        d.type = 'table';
                        d.school_id = $('#filter_school_id').val();
                        d.classroom_id = $('#filter_classroom_id').val();
                        d.status = $('#filter_status').val();
                        d.start_date = start_date;
                        d.end_date = end_date;
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
                    {
                        data: 'date',
                        name: 'date',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Date';
                        }
                    },
                    {
                        data: 'student.nis',
                        name: 'student.nis',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No NIS';
                        }
                    },
                    {
                        data: 'student.name',
                        name: 'student.name',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'Unknown Student';
                        }
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Amount';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Status';
                        }
                    },
                    {
                        data: 'balance_before',
                        name: 'balance_before',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'balance_after',
                        name: 'balance_after',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Description';
                        }
                    },
                    @if (Auth::user()->can('Delete Laporan Saldo Santri'))
                    {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    }
                    @endif
                ]
            });
        }

        function getTotalSaldo(start_date = '', end_date = '') {
            $.ajax({
                url: "{{ route('report-saldo.index') }}",
                type: "GET",
                dataType: 'json',
                data: {
                    type: 'total',
                    school_id: $('#filter_school_id').val(),
                    classroom_id: $('#filter_classroom_id').val(),
                    status: $('#filter_status').val(),
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    $('#total-topup').text('Rp. ' + response.total_topup);
                    $('#total-pengurangan').text('Rp. ' + response.total_pengurangan);
                    $('#saldo-tersedia').text('Rp. ' + response.saldo_tersedia);
                }
            });
        }

        function reloadTable(start_date = '', end_date = '') {
            table.destroy();
            table = initializeTable(start_date, end_date);
            getTotalSaldo(start_date, end_date);
        }

        $('#filter_school_id').on('change', function() {
            var school_id = $(this).val();
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

        $('#btn_tampilkan').click(function() {
            reloadTable();
        });

        $('#filter_form').submit(function(event) {
            event.preventDefault();
            reloadTable();
        });

        $('#filter_school_id, #filter_classroom_id, #filter_status').on('change', function() {
            reloadTable();
        });

        $('#dateRange').daterangepicker({
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
            }
        }, function(start, end) {
            $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });

        var start = moment().startOf('month');
        var end = moment().endOf('month');
        $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
        reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });
</script>
@endpush