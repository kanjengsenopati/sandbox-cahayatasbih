@extends('layouts.master', ['title' => 'Laporan Biaya Aplikasi'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan Biaya Aplikasi</h1>
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
                                    <i class="fas fa-calendar-day fs-1 p-0 text-white">
                                    </i>&nbsp;
                                    <h5 class="card-title text-center text-white">Hari Ini</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                    number_format($app_fee['today']) ??
                                    0 }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-calendar-week fs-1 p-0 text-white">
                                    </i>&nbsp;
                                    <h5 class="card-title text-center text-white">Minggu Ini</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                    number_format($app_fee['week']) ??
                                    0 }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-calendar-alt fs-1 p-0 text-white"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Bulan Ini</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                    number_format($app_fee['month']) ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-calendar fs-1 p-0 text-white"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Tahun Ini</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">Rp. {{
                                    number_format($app_fee['year']) ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">Riwayat Biaya Aplikasi</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-report-app-fee" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Pembayaran</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
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
<script>
    $(document).ready(() => {
            var table = $('#table-report-app-fee').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('report-app-fee.index') }}",
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
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'payment_code',
                        name: 'payment_code'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'app_fee',
                        name: 'app_fee'
                    },
                   
                ]
            });

        })
</script>
@endpush