@extends('layouts.master', ['title' => 'Dashboard POS'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Dashboard POS</h1>
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
        <div class="card">
            <div class="card-body pt-0">
                <div class="row gy-5 g-xl-10">
                    <div class="col-sm-6 col-xl-3 mb-xl-10">
                        <div class="card h-lg-150">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                <div class="m-0">
                                    <i class="fa fa-chart-line fs-2hx text-gray-600"></i>
                                </div>
                                <div class="flex-column mt-7 mb-2">
                                    <div class="m-0">
                                        <span class="fw-semibold fs-6 text-gray-700">Total Transaksi</span>
                                    </div>
                                    <span
                                        class="badge badge-light-primary fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{
                                        $statistic['totalTransaction'] ?? 0 }}</span>
                                </div>
                                <p>Dari <span class="badge badge-light-warning fs-base">{{
                                        number_format($statistic['totalStudent']) ?? 0 }}</span> Santri</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 mb-xl-10">
                        <div class="card h-lg-150">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                <div class="m-0">
                                    <i class="fa fa-chart-line fs-2hx text-gray-600"></i>
                                </div>
                                <div class="flex-column mt-7 mb-2">
                                    <div class="m-0">
                                        <span class="fw-semibold fs-6 text-gray-700">Total Pendapatan</span>
                                    </div>
                                    <span
                                        class="badge badge-light-primary fw-semibold fs-2x text-gray-800 lh-1 ls-n2 mt-2">Rp.
                                        {{
                                        number_format($statistic['totalProfit']) ?? 0 }}</span>
                                </div>
                                <p>Dari <span class="badge badge-light-warning fs-base">{{
                                        number_format($statistic['totalStudent']) ?? 0 }}</span> Santri</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 mb-xl-10">
                        <div class="card h-lg-150">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                <div class="m-0">
                                    <i class="fa fa-chart-line fs-2hx text-gray-600"></i>
                                </div>
                                <div class="flex-column mt-7 mb-2">
                                    <div class="m-0">
                                        <span class="fw-semibold fs-6 text-gray-700">Total Produk Terjual</span>
                                    </div>
                                    <span
                                        class="badge badge-light-primary fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{
                                        number_format($statistic['totalSellingProduct']) ?? 0 }}</span>
                                </div>
                                <p>Dari <span class="badge badge-light-warning fs-base">{{
                                        $statistic['totalItemAvailable'] ?? 0 }}</span> Barang
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 mb-xl-10">
                        <div class="card h-lg-150">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                <div class="m-0">
                                    <i class="fa fa-chart-line fs-2hx text-gray-600"></i>
                                </div>
                                <div class="flex-column mt-7 mb-2">
                                    <div class="m-0">
                                        <span class="fw-semibold fs-6 text-gray-700">Produk Tersedia</span>
                                    </div>
                                    <span
                                        class="badge badge-light-primary fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{
                                        $statistic['totalItemAvailable'] ?? 0 }}</span>
                                </div>
                                <p>Dari <span class="badge badge-light-warning fs-base">{{
                                        $statistic['totalItemAvailable'] ?? 0 }}</span> Barang
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">Produk Terlaris</span>
                    </h3>
                </div>
                <div class="table-responsive">
                    <table id="datatable-item-best-seller"
                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th style="width: 5%">No</th>
                                <th>Nama Produk</th>
                                <th style="width: 20%">Kategori</th>
                                <th style="width: 15%">Harga</th>
                                <th style="width: 10%">Terjual</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-dark fw-semibold"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--begin::Col-->

    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
            var table = $('#datatable-item-best-seller').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('pos.dashboard') }}",
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
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'total_selling',
                        name: 'total_selling'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                ]
            });

        })
</script>
@endpush