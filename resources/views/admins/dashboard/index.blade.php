@extends('layouts.master', ['title' => 'Dashboard'])
@push('css')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
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
            <div class="col-xl-8">
                <div class="chart">
                    <div id="chart-line" class="chart-canvas" height="350px"></div>
                </div>
            </div>
            <!--begin::Col-->
            <div class="col-xl-4">
                <!--begin::List widget 11-->
                <div class="card card-flush h-xl-100">
                    {{-- add image vector here --}}
                    <div class="card-body pt-2">
                        <div class="d-flex flex-stack">
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <img src="{{ asset('assets/media/illustrations/dashboard/statistik1.jpg') }}"
                                    class="mw-100 mh-200px" alt="">
                            </div>
                        </div>
                    </div>
                    <!--begin::Header-->
                    <div class="card-header pt-7 mb-3">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder text-gray-800">Statistik</span>
                            <span class="text-gray-400 mt-1 fw-bold fs-6">{{ date('d F Y') }}</span>
                        </h3>

                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-4">
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">

                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-user fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Admin</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Admin yang
                                        terdaftar</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span class="text-gray-800 fw-bolder fs-6 d-block">{{ $statistics['total_admin'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-briefcase fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Klien</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Klien yang
                                        terdaftar
                                    </span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span class="text-gray-800 fw-bolder fs-6 d-block">{{ $statistics['total_client'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-download fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total Download
                                    </a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Total Download
                                        Etika dan SOP</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span
                                    class="text-gray-800 fw-bolder fs-6 d-block">{{ $statistics['total_download'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                    </div>

                    <!--end::Body-->
                </div>
                <!--end::List widget 11-->

            </div>
            <!--end::Col-->
            <!--begin::Col-->

            <!--end::Col-->
        </div>
        <!--end::Row-->
        <div class="row gy-5 g-xl-10 mt-8 mx-4">
            <div class="col-xl-8">
                <div class="card">

                    <!--begin::Card header-->
                    <div class="card-header d-flex align-items-center justify-content-start border-0 pt-6">
                        <!--begin::Card title-->
                        <span class="card-title fw-bolder text-dark fs-3">Pelaporan WBS Terbaru</span>
                    </div>
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table id="table-user" class="table table-striped border rounded gy-5 gs-7">
                                <thead>
                                    <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                        <th width="3%">No</th>
                                        <th width="20%">Kode Pelaporan</th>
                                        <th width="20%">Kategori</th>
                                        <th>Status</th>
                                        <th>Pelapor</th>
                                        <th width="10%" class="text-center"> Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                </div>
            </div>
            <!--begin::Col-->
            <div class="col-xl-4">
                <!--begin::List widget 11-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Header-->

                    <div class="card-header pt-7 mb-3">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder text-gray-800">Statistik WBS </span>
                            <span class="text-gray-400 mt-1 fw-bold fs-6"></span>
                        </h3>

                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-4">
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">

                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-envelope-open-text fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Diterima</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan yang
                                        diterima</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span class="text-gray-800 fw-bolder fs-6 d-block">{{ $wbs_statistics['pending'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-user-check fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Verifikasi </a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan yang
                                        diverifikasi
                                    </span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span
                                    class="text-gray-800 fw-bolder fs-6 d-block">{{ $wbs_statistics['verification'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-magnifying-glass-arrow-right fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Investigasi </a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan yang
                                        diinvestigasi</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span
                                    class="text-gray-800 fw-bolder fs-6 d-block">{{ $wbs_statistics['investigation'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->

                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-solid fa-rotate fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Dalam
                                        Proses </a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan Dalam
                                        Proses
                                    </span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span
                                    class="text-gray-800 fw-bolder fs-6 d-block">{{ $wbs_statistics['in_progress'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-circle-check fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Tindakan Selesai</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Tindakan Korekftif
                                        Selesai
                                    </span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span
                                    class="text-gray-800 fw-bolder fs-6 d-block">{{ $wbs_statistics['completed'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-envelope-circle-check fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                        Selesai</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Laporan Selesai
                                    </span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span
                                    class="text-gray-800 fw-bolder fs-6 d-block">{{ $wbs_statistics['finished'] }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                    </div>

                    <!--end::Body-->
                </div>
                <!--end::List widget 11-->

            </div>
            <!--end::Col-->
            <!--begin::Col-->

            <!--end::Col-->
        </div>
    </div>
    <!--end::Container-->
@endsection
@push('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        var labels = <?php echo json_encode($labels, JSON_NUMERIC_CHECK); ?>;
        var chart_etika = <?php echo json_encode($etika_values, JSON_NUMERIC_CHECK); ?>;
        var chart_wbs = <?php echo json_encode($wbs_values, JSON_NUMERIC_CHECK); ?>;
        var chart_article = <?php echo json_encode($article_values, JSON_NUMERIC_CHECK); ?>;

        Highcharts.chart('chart-line', {
            title: {
                text: 'Statistik Bulan Ini'
            },
            yAxis: {
                title: {
                    text: 'Total Statistik Bulan Ini'
                }
            },
            xAxis: {
                categories: labels,
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            plotOptions: {
                series: {
                    label: {
                        connectorAllowed: true
                    },
                }
            },
            series: [{
                    name: 'Etika',
                    data: chart_etika,
                    color: '#1BC5BD'
                },
                {
                    name: 'WBS',
                    data: chart_wbs,
                    color: '#F64E60'
                },
                {
                    name: 'Artikel',
                    data: chart_article,
                    color: '#FFA800'
                }
            ],
        });

        var table = $('#table-user').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('dashboard') }}",
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
                    responsivePriority: -1,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'reporting_code',
                    name: 'reporting_code',
                    responsivePriority: 0
                },
                {
                    data: 'category',
                    name: 'category',
                    responsivePriority: 1
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'reporter',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: 2
                },
            ]
        });
    </script>
@endpush
