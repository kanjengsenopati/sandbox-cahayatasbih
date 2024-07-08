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
                    <!--begin::Tabs-->
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_1">Transaksi Santri</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_2">Transaksi Umum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_3">Rekap Laporan</a>
                        </li>
                    </ul>
                    <!--end::Tabs-->

                    <!--begin::Tab Content-->
                    <div class="tab-content" id="myTabContent">
                        <!--begin::Tab Pane 1-->
                        <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-transaksi-santri" class="table align-middle table-row-dashed">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-100px">Kode Pembayaran</th>
                                            <th class="min-w-100px">Santri</th>
                                            <th class="min-w-100px">Kelas</th>
                                            <th class="min-w-100px">Jumlah</th>
                                            <th class="min-w-100px">Kasir</th>
                                            <th class="min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Tab Pane 1-->

                        <!--begin::Tab Pane 2-->
                        <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-transaksi-umum" class="table align-middle table-row-dashed">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-100px">Kode Pembayaran</th>
                                            <th class="min-w-100px">Total</th>
                                            <th class="min-w-100px">Profit</th>
                                            <th class="min-w-100px">Kasir</th>
                                            <th class="min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Tab Pane 2-->

                        <!--begin::Tab Pane 3-->
                        <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-gray-800">REKAP KEUANGAN KOPERASI</span>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form id="dateRangeForm">
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <label for="start_date" class="form-label">Tanggal Mulai:</label>
                                                <input type="date" id="start_date" name="start_date"
                                                    value="{{ request('start_date') }}" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="end_date" class="form-label">Tanggal Akhir:</label>
                                                <input type="date" id="end_date" name="end_date"
                                                    value="{{ request('end_date') }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary">Filter</button>
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
                                                        <i
                                                            class="fas fa-hand-holding-usd fs-1 p-0 text-white"></i>&nbsp;
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
                                                        <i
                                                            class="fas fa-hand-holding-usd fs-1 p-0 text-white"></i>&nbsp;
                                                        <h5 class="card-title text-center text-white">Total Pendapatan
                                                    </div>
                                                    <br>
                                                    <p class="card-text text-center text-white"
                                                        style="font-size: 20px;">Rp. {{
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
                                                        <tr
                                                            class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
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
                            <!--end::Tab Pane 3-->
                        </div>
                        <!--end::Tab Content-->
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
        var tableSantri = $('#table-transaksi-santri').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('order-item-history.index') }}',
                data: function(d) {
                    d.type = 'santri';
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
           columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                    }
                    },
                    {
                    data: 'payment_code',
                    name: 'payment_code'
                    },
                    {
                    data: 'student.name',
                    name: 'student.name'
                    },
                    {
                    data: 'student.classroom.name',
                    name: 'student.classroom.name'
                    },
                    {
                    data: 'pay_amount',
                    name: 'pay_amount'
                    },
                  
                    {
                    data: 'admin',
                    name: 'admin'
                    },
                    {
                    data: 'action',
                    name: 'action'
                    }
              ]
        });

        var tableUmum = $('#table-transaksi-umum').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('order-item-history.index') }}',
                data: function(d) {
                    d.type = 'umum';
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
            columns: [{
            "data": null,
            "sortable": false,
            "searchable": false,
            render: function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
            }
            },
            {
            data: 'payment_code',
            name: 'payment_code'
            },
           
            {
            data: 'pay_amount',
            name: 'pay_amount'
            },
            {
            data: 'profit',
            name: 'profit'
            },
           
            {
            data: 'admin',
            name: 'admin'
            },
            {
            data: 'action',
            name: 'action'
            }
            ]
        });

        var tableTopItems = $('#table-top-items').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: {
        url: '{{ route('order-item-history.index') }}',
        data: function(d) {
        d.type = 'top-items';
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
                columns: [{
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
                },
                ]
                });

      
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