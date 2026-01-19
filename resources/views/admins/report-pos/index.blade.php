@extends('layouts.master', ['title' => 'Data Riwayat Pembelian', 'sidebar' => 'on'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Pembelian</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('order-item-history.index') }}" class="text-dark text-hover-primary">Riwayat
                            Pembelian</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Pembelian</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Content-->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder text-gray-800">REKAP KEUANGAN KOPERASI</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="dateRangeForm">
                                <div class="row mb-4">
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
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-lg-3 col-md-6 py-2">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center">
                                                <i class="fas fa-box fs-1 p-0 text-white"></i>&nbsp;
                                                <h5 class="card-title text-center text-white">Total Produk</h5>
                                            </div>
                                            <br>
                                            <p class="card-text text-center" style="font-size: 20px;">
                                                {{ $incomesCashier['totalProduct'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 py-2">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center">
                                                <i class="fas fa-cash-register fs-1 p-0 text-white"></i>&nbsp;
                                                <h5 class="card-title text-center text-white">Total Transaksi
                                                </h5>
                                            </div>
                                            <br>
                                            <p class="card-text text-center" style="font-size: 20px;">
                                                {{ $incomesCashier['totalTransaction'] ?? 0 }}</p>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 py-2">
                                    <div class="card bg-danger text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center">
                                                <i class="fas fa-hand-holding-usd fs-1 p-0 text-white"></i>&nbsp;
                                                <h5 class="card-title text-center text-white">Total Penjualan
                                            </div>
                                            <br>
                                            <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                                number_format($incomesCashier['totalSales'], 0, ',', '.') }}</p>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 py-2">
                                    <div class="card bg-warning text-dark h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center">
                                                <i class="fas fa-hand-holding-usd fs-1 p-0 text-white"></i>&nbsp;
                                                <h5 class="card-title text-center text-white">Total Pendapatan
                                            </div>
                                            <br>
                                            <p class="card-text text-center text-white" style="font-size: 20px;">Rp. {{
                                                number_format($incomesCashier['totalIncome'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8 col-md-12 mb-4">
                                    <div id="container" style="width: 100%; height: 650px;"></div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <h4 class="mb-3">Produk Terlaris</h4>
                                    <div class="table-responsive">
                                        <table id="table-top-items" class="table align-middle table-row-dashed">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th>Nama Produk</th>
                                                    <th>Total Penjualan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Content-->
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
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
    $(document).ready(() => {
        // Initialize the DataTable
        var tableTopItems = $('#table-top-items').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('report-pos.index') }}', // Ensure this route is correct
                data: function(d) {
                    // Send the start_date and end_date as part of the request
                    d.type = 'top-items';
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing..."
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
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'total_transaction',
                    name: 'total_transaction'
                }
            ]
        });

        // Initialize the date range picker
        var start = moment().startOf('month');
        var end = moment().endOf('month');
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
            reloadTable(); // Reload the table after the date range is selected
        });

        // Set initial values for date range
        $('#start_date').val(start.format('YYYY-MM-DD'));
        $('#end_date').val(end.format('YYYY-MM-DD'));
        $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));

        // Function to reload the DataTable with the updated filters
        function reloadTable() {
            tableTopItems.ajax.reload(); // Reload the table data with the new date range
        }
    });
</script>
<script>
    var categories = @json($chartIncomesCategories);
// Data omzet dan profit koperasi untuk setiap bulan
var omzet = @json($chartCashierOmzet);
var profit = @json($chartCashierProfit);


// Konfigurasi grafik omzet dan profit koperasi
var options = {
    chart: {
        type: 'column' // Tipe grafik kolom
    },
    responsive: {
    rules: [{
    condition: {
    maxWidth: 500
    },
    chartOptions: {
    legend: {
    enabled: false
    },
    yAxis: {
    title: {
    text: null
    }
    }
    }
    }]

    },
    title: {
        text: 'Grafik Omzet dan Profit Koperasi' // Judul grafik
    },
    xAxis: {
    categories: categories, // Kategori sumbu x (bulan)
    title: {
    text: 'Bulan' // Label sumbu x
    }
    },
    yAxis: [{
        title: {
            text: 'Omzet (Rp)' // Label sumbu y untuk omzet
        }
    }, {
        title: {
            text: 'Profit (Rp)' // Label sumbu y untuk profit
        },
        opposite: true // Menampilkan sumbu y profit di sebelah kanan
    }],
    legend: {
        align: 'right', // Penempatan legenda di samping kanan
        verticalAlign: 'middle', // Penempatan legenda di tengah
        layout: 'vertical' // Tata letak legenda vertikal
    },
   series: [
{ name: 'Omzet', data: omzet }, // Data omzet
{ name: 'Profit', data: profit } // Data profit
]
};

// Membuat grafik dengan menggunakan Highcharts
Highcharts.chart('container', options);
</script>
@endpush