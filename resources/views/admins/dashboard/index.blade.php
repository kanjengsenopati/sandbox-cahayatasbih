@extends('layouts.master', ['title' => 'Dashboard'])
@push('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
<!--begin::Container-->
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Dashboard </h1>
                <!--end::Title-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <!--end::Row-->
    <!--begin::Row-->
    <div class="row gy-5 g-xl-10 mt-8 mx-4">
        <div class="col-xl-12">

            <div class="card mb-4">
                <div class="row gx-3 gy-3">
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-user-group fs-1 p-0 text-white"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Wali Santri</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $data['total_parents'] ??
                                    0 }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-users fs-1 p-0 text-white"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Siswa Aktif</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $data['total_students'] ??
                                    0 }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-users fs-1 p-0 text-white"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Santri Aktif</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $totalSantriAktif ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-users fs-1 p-0 text-white"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Santri Keluar</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $totalSantriKeluar ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">REKAP KEUANGAN PEMBAYARAN</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-money-bill-wave fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Hari Ini</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                        number_format($incomes['today'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-coins fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Bulan Ini</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                        number_format($incomes['month'], 0, ',', '.') }}</p>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $incomes['incomePercentageMonth'] ?? 0 }}%;"
                                            aria-valuenow="{{ $incomes['incomePercentageMonth'] ?? 0 }}"
                                            aria-valuemin="0" aria-valuemax="100">{{
                                            $incomes['incomePercentageMonth'] ?? 0 }}%
                                        </div>
                                    </div>
                                    <p class="text-center mt-3">{{
                                        $incomes['incomePercentageMonth'] ?? 0 }}% dari target bulanan (Rp. {{
                                        number_format($incomes['targetMonth'], 0, ',', '.') }})
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-money-check-alt fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Tahun Ini</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                        number_format($incomes['year'], 0, ',', '.') }}</p>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: {{
                                            $incomes['incomePercentageYear'] ?? 0 }}%;"
                                            aria-valuenow="{{ $incomes['incomePercentageYear'] ?? 0 }}"
                                            aria-valuemin="0" aria-valuemax="100">{{
                                            $incomes['incomePercentageYear'] ?? 0 }}%
                                        </div>
                                    </div>
                                    <p class="text-center mt-3">{{
                                        $incomes['incomePercentageYear'] ?? 0 }}% dari target tahunan (Rp. {{
                                        number_format($incomes['targetYear'], 0, ',', '.') }})
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-hand-holding-usd fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Total</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center text-white" style="font-size: 20px;">Rp. {{
                                        number_format($incomes['total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">REKAP KEUANGAN KOPERASI</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-money-bill-wave fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Hari Ini</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                        number_format($incomesCashier['today'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-coins fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Bulan Ini</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                        number_format($incomesCashier['month'], 0, ',', '.') }}</p>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-money-check-alt fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Tahun Ini</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                        number_format($incomesCashier['year'], 0, ',', '.') }}</p>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-hand-holding-usd fs-1 p-0 text-white"></i>&nbsp;
                                        <h5 class="card-title text-center text-white">Total</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center text-white" style="font-size: 20px;">Rp. {{
                                        number_format($incomesCashier['total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div id="revenue-bill" style="width: 800px; height: 400px;"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div id="container" style="width: 800px; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Row-->
</div>
<!--end::revenue-bill-->
@endsection
@push('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
    var categories = @json($chartIncomesCategories);
    var smp = @json($chartIncomesSmp);
    var ma = @json($chartIncomesMa);
    var pondok = @json($chartIncomesPondok);

    // Konfigurasi grafik batang
    var options = {
        chart: {
            type: 'bar' // Tipe grafik batang
        },
        title: {
            text: 'Grafik Pemasukan Pembayaran' // Judul grafik
        },
        xAxis: {
           categories: categories, // Kategori sumbu x (bulan)
            title: {
                text: 'Bulan' // Label sumbu x
            }
        },
        yAxis: {
            title: {
                text: 'Nominal (Rp)' // Label sumbu y
            }
        },
        legend: {
            align: 'right', // Penempatan legenda di samping kanan
            verticalAlign: 'middle', // Penempatan legenda di tengah
            layout: 'vertical' // Tata letak legenda vertikal
        },
        plotOptions: {
            series: {
                stacking: 'normal' // Mengaktifkan tumpukan data
            }
        },
        series: [
            { name: 'SMP', data: smp }, // Data pemasukan SMP
            { name: 'MA', data: ma }, // Data pemasukan MA
            { name: 'Pondok', data: pondok } // Data pemasukan Pondok
        ]
    };

    // Membuat grafik dengan menggunakan Highcharts
    Highcharts.chart('revenue-bill', options);
</script>
<script>
    // Data omzet dan profit koperasi untuk setiap bulan
    var omzet = @json($chartCashierOmzet);
    var profit = @json($chartCashierProfit);
    console.log(omzet);
    console.log(profit);

    // Konfigurasi grafik omzet dan profit koperasi
    var options = {
        chart: {
            type: 'column' // Tipe grafik kolom
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