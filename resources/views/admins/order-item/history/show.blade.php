@extends('layouts.master', ['title' => 'Detail Transaksi'])
@section('content')
<!--begin::Content-->
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Transaksi</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('order-item-history.index') }}">
                        <li class="breadcrumb-item text-muted">Transaksi</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark"><a href="{{ route('order-item-history.show', $order->id) }}"
                            class="text-dark">Detail
                            Transaksi</a></li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Navbar-->
            <div class="card mb-5 mb-xl-10">
                <div class="card-body pt-9 pb-0">
                    <!--begin::Details-->
                    <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                        <!-- Content Details -->
                    </div>
                    <!--end::Details-->
                    <!--begin::Navs-->
                    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder">
                        <!-- Nav items -->
                        <li class="nav-item mt-2">
                            <a class="nav-link text-active-primary ms-0 me-10 py-5 active"
                                href="#ringkasanTabContent">Ringkasan</a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link text-active-primary ms-0 me-10 py-5" href="#listPembelianTabContent">List
                                Pembelian</a>
                        </li>
                    </ul>
                    <!--end::Navs-->
                </div>
            </div>
            <!--end::Navbar-->

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Ringkasan Tab Content -->
                <div class="tab-pane fade show active" id="ringkasanTabContent">
                    <!--begin::Basic info-->
                    <div class="row g-5 g-xl-8">
                        <!-- Content Cards -->
                        <div class="col-6">
                            <!-- Card informasi transaksi -->
                            <div class="card" style="height: 100%;">
                                <!-- Card header -->
                                <div class="card-header">
                                    <!-- Card title -->
                                    <h3 class="card-title fw-bolder">Transaksi</h3>
                                    <!-- Card title -->
                                </div>
                                <!-- Card body -->
                                <div class="card-body pt-9 pb-0">
                                    <!-- Details -->
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Nomor Transaksi</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Status</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Total Pembayaran</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Kasir</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Profit</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order->payment_code ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                @if ($order->status == 'PENDING')
                                                <span class="badge badge-light-primary me-2">Menunggu
                                                    Pembayaran</span>
                                                @elseif ($order->status == 'SUCCESS')
                                                <span class="badge badge-light-success me-2">Pembayaran
                                                    Berhasil</span>
                                                @elseif ($order->status == 'FAILED')
                                                <span class="badge badge-light-danger me-2">Pembayaran Gagal</span>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>Rp {{ number_format($order->pay_amount, 0, ',', '.') ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order?->admins?->name ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>Rp {{ number_format($order->profit, 0, ',', '.') ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Details -->
                                </div>
                                <!-- End Card body -->
                            </div>
                            <!-- End Card informasi transaksi -->
                        </div>
                        @if ($order->type == 'SANTRI')
                        <div class="col-6">
                            <!-- Card informasi siswa -->
                            <div class="card" style="height: 100%;">
                                <!-- Card header -->
                                <div class="card-header">
                                    <!-- Card title -->
                                    <h3 class="card-title fw-bolder">Santri</h3>
                                    <!-- Card title -->
                                </div>
                                <!-- Card body -->
                                <div class="card-body pt-9 pb-0">
                                    <!-- Details -->
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">NIS</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Nama</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Kelas</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Sekolah</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p class="fw-bold fs fs-6">Jenis Kelamin</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order->student->nis ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order->student?->name ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order->student?->classroom->name ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order->student?->classroom?->school->name ?? '' }}</p>
                                            </div>
                                            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                                <p>{{ $order->student?->gender == 'L' ? 'Laki-Laki' : 'Perempuan' ??
                                                    ''
                                                    }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Details -->
                                </div>
                                <!-- End Card body -->
                            </div>
                            <!-- End Card informasi siswa -->
                        </div>
                        @endif
                        <!-- End Card informasi siswa -->
                    </div>
                    <!--end::Basic info-->
                </div>
                <!-- End Ringkasan Tab Content -->

                <!-- List Pembelian Tab Content -->
                <div class="tab-pane fade" id="listPembelianTabContent">
                    <!-- List Pembelian Table -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title fw-bolder">List Pembelian</h3>
                            <div class="table-responsive">
                                <table id="table-student" class="table table-striped border rounded gy-5 gs-7">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                            <th style="width: 3%">No</th>
                                            <th>Nama</th>
                                            <th>Qty</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- End List Pembelian Table -->
                </div>
                <!-- End List Pembelian Tab Content -->
            </div>
            <!-- End Tab Content -->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->
    <!--end::Wrapper-->
    @endsection
    @push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
        var tabTriggerList = document.querySelectorAll('.nav-link');
        tabTriggerList.forEach(function (tabTrigger) {
            tabTrigger.addEventListener('click', function (event) {
                event.preventDefault();
                var tabPane = document.querySelector(event.target.getAttribute('href'));
                var activeTabPane = document.querySelector('.tab-pane.show.active');
                var activeNav = document.querySelector('.nav-link.active');
                activeTabPane.classList.remove('show', 'active');
                activeNav.classList.remove('active');
                tabPane.classList.add('show', 'active');
                tabTrigger.classList.add('active');
            });
        });
    });
    </script>
    <script>
        $(document).ready(() => {
            // Initialize DataTable
            var table = $('#table-student').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('order-item-history.show', $order->id) }}',
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'></i>",
                        "previous": "<i class='fa fa-angle-left'></i>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
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
                        data: 'item.name',
                        name: 'item.name',
                        render: function(data, type, row) {
                            return data ? data : 'Belum diisi';
                        }
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        render: function(data, type, row) {
                            return data ? data : 'Belum diisi';
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                    },
                    {
                        data: 'total',
                        name: 'total',
                        render: function(data, type, row) {
                            return data ? 'Rp ' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") : 'Belum diisi';
                        }
                    }
                ]
            });
        });
    </script>
    @endpush