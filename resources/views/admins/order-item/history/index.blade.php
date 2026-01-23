@extends('layouts.master', ['title' => 'Dashboard Penjualan', 'sidebar' => 'on'])
@php
    $today = now();
    $periodAliases = [
        'month' => 'this_month',
        'custom' => 'custom',
        'today' => 'today',
        'week' => 'week',
    ];
    $requestedPeriod = request('period');
    $normalizedPeriod = $periodAliases[$requestedPeriod] ?? ($requestedPeriod ?? 'this_month');

    if (request()->filled('start_date') && request()->filled('end_date')) {
        $filterStart = request('start_date');
        $filterEnd = request('end_date');
    } else {
        switch ($normalizedPeriod) {
            case 'last_month':
                $filterStart = $today->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $filterEnd = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_3_months':
                $filterStart = $today->copy()->subMonths(2)->startOfMonth()->format('Y-m-d');
                $filterEnd = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_6_months':
                $filterStart = $today->copy()->subMonths(5)->startOfMonth()->format('Y-m-d');
                $filterEnd = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $filterStart = $today->copy()->startOfYear()->format('Y-m-d');
                $filterEnd = $today->copy()->endOfYear()->format('Y-m-d');
                break;
            case 'today':
                $filterStart = $today->copy()->format('Y-m-d');
                $filterEnd = $today->copy()->format('Y-m-d');
                break;
            case 'week':
                $filterStart = $today->copy()->subDays(6)->format('Y-m-d');
                $filterEnd = $today->copy()->format('Y-m-d');
                break;
            default:
                $filterStart = $today->copy()->startOfMonth()->format('Y-m-d');
                $filterEnd = $today->copy()->endOfMonth()->format('Y-m-d');
                $normalizedPeriod = 'this_month';
                break;
        }
    }

    $filterPeriod = in_array($normalizedPeriod, ['today', 'week']) ? 'custom' : $normalizedPeriod;
@endphp
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar py-5" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <div class="page-title d-flex align-items-center">
                    <h1 class="d-flex text-dark fw-bold fs-2 align-items-center my-1">
                        <i class="fas fa-chart-line text-primary fs-2 me-3"></i>
                        Dashboard Penjualan & Analitik
                    </h1>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-light-primary" id="refreshData">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    {{-- <button class="btn btn-sm btn-primary" id="exportReport">
                        <i class="fas fa-download"></i> Export Report
                    </button> --}}
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid">
            <div id="kt_content_container" class="container-fluid">

                <!--begin::Date Filter & Quick Stats-->
                <div class="card shadow-sm mb-5">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('order-item-history.index') }}">
                            <div class="row align-items-end">
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold text-gray-700">Periode Analisis</label>
                                    <div id="dateRange"
                                        class="form-control form-control-solid d-flex align-items-center justify-content-between"
                                        style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-calendar-alt text-primary"></i>
                                            <span></span>
                                        </div>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                    <input type="hidden" id="start_date" name="start_date" value="{{ $filterStart }}">
                                    <input type="hidden" id="end_date" name="end_date" value="{{ $filterEnd }}">
                                    <input type="hidden" id="period" name="period" value="{{ $filterPeriod }}">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="submit" class="btn btn-primary w-100" id="filterBtn">
                                        <i class="fas fa-filter"></i> Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--end::Date Filter-->

                <!--begin::KPI Cards-->
                <div class="row g-5 mb-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-flush h-100 shadow-sm hover-elevate-up">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-50px me-3 bg-light-primary">
                                        <i class="fas fa-shopping-cart fs-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-600 fw-semibold d-block fs-7">Total Transaksi</span>
                                        <span class="text-gray-800 fw-bold d-block fs-2">
                                            {{ number_format($totalTransaction ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                @if (isset($comparison['transaction']))
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="badge badge-light-{{ $comparison['transaction'] >= 0 ? 'success' : 'danger' }} fs-8">
                                            <i
                                                class="fas fa-arrow-{{ $comparison['transaction'] >= 0 ? 'up' : 'down' }}"></i>
                                            {{ abs($comparison['transaction']) }}%
                                        </span>
                                        <span class="text-gray-600 fs-8 ms-2">vs periode sebelumnya</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-flush h-100 shadow-sm hover-elevate-up">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-50px me-3 bg-light-success">
                                        <i class="fas fa-money-bill-wave fs-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-600 fw-semibold d-block fs-7">Total Penjualan</span>
                                        <span class="text-gray-800 fw-bold d-block fs-2">
                                            Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                @if (isset($comparison['sales']))
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="badge badge-light-{{ $comparison['sales'] >= 0 ? 'success' : 'danger' }} fs-8">
                                            <i class="fas fa-arrow-{{ $comparison['sales'] >= 0 ? 'up' : 'down' }}"></i>
                                            {{ abs($comparison['sales']) }}%
                                        </span>
                                        <span class="text-gray-600 fs-8 ms-2">vs periode sebelumnya</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-flush h-100 shadow-sm hover-elevate-up">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-50px me-3 bg-light-warning">
                                        <i class="fas fa-chart-line fs-2x text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-600 fw-semibold d-block fs-7">Total Profit</span>
                                        <span class="text-gray-800 fw-bold d-block fs-2">
                                            Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                @if (isset($comparison['income']))
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="badge badge-light-{{ $comparison['income'] >= 0 ? 'success' : 'danger' }} fs-8">
                                            <i class="fas fa-arrow-{{ $comparison['income'] >= 0 ? 'up' : 'down' }}"></i>
                                            {{ abs($comparison['income']) }}%
                                        </span>
                                        <span class="text-gray-600 fs-8 ms-2">vs periode sebelumnya</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-flush h-100 shadow-sm hover-elevate-up">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-50px me-3 bg-light-info">
                                        <i class="fas fa-percentage fs-2x text-info"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-600 fw-semibold d-block fs-7">Margin Profit</span>
                                        <span class="text-gray-800 fw-bold d-block fs-2">
                                            {{ number_format($profitMargin ?? 0, 1) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light-info fs-8">
                                        <i class="fas fa-info-circle"></i> Optimal
                                    </span>
                                    <span class="text-gray-600 fs-8 ms-2">Target: 25%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::KPI Cards-->

                <!--begin::Charts Section-->
                <div class="row g-5 mb-5">
                    <!--begin::Sales Chart-->
                    <div class="col-xl-8">
                        <div class="card card-flush h-100 shadow-sm">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800">Trend Penjualan & Profit</span>
                                    <span class="text-gray-600 mt-1 fw-semibold fs-7">Analisis performa bulanan</span>
                                </h3>
                                <div class="card-toolbar">
                                    <button class="btn btn-sm btn-icon btn-light-primary">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div id="salesChart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                    <!--end::Sales Chart-->

                    <!--begin::Top Categories-->
                    <div class="col-xl-4">
                        <div class="card card-flush h-100 shadow-sm">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800">Kategori Terlaris</span>
                                    <span class="text-gray-600 mt-1 fw-semibold fs-7">Top 5 kategori periode ini</span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <div id="topCategoriesChart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                    <!--end::Top Categories-->
                </div>
                <!--end::Charts Section-->

                <!--begin::Insights Cards-->
                <div class="row g-5 mb-5">
                    <div class="col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <i class="fas fa-trophy fs-2x text-warning me-3"></i>
                                    <h3 class="card-title fw-bold text-gray-800 m-0">Best Performer</h3>
                                </div>
                                <div class="separator separator-dashed mb-4"></div>
                                <div class="mb-3">
                                    <span class="text-gray-600 fs-7 fw-semibold">Kasir Terbaik</span>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span
                                            class="text-gray-800 fw-bold fs-5">{{ $insights['bestCashier']['name'] ?? '-' }}</span>
                                        <span
                                            class="badge badge-light-success">{{ $insights['bestCashier']['transactions'] ?? 0 }}
                                            transaksi</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-gray-600 fs-7 fw-semibold">Produk Terlaris</span>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span
                                            class="text-gray-800 fw-bold fs-5">{{ $insights['topProduct']['name'] ?? '-' }}</span>
                                        <span
                                            class="badge badge-light-primary">{{ number_format($insights['topProduct']['sold'] ?? 0) }}
                                            terjual</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-600 fs-7 fw-semibold">Jam Tersibuk</span>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span class="text-gray-800 fw-bold fs-5">{{ $insights['peakHour'] ?? '-' }}</span>
                                        <span class="badge badge-light-info">Peak Time</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <i class="fas fa-users fs-2x text-primary me-3"></i>
                                    <h3 class="card-title fw-bold text-gray-800 m-0">Customer Insights</h3>
                                </div>
                                <div class="separator separator-dashed mb-4"></div>
                                <div class="mb-3">
                                    <span class="text-gray-600 fs-7 fw-semibold">Rata-rata Transaksi</span>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span class="text-gray-800 fw-bold fs-5">Rp
                                            {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</span>
                                        @if (isset($comparison['sales']))
                                            <span
                                                class="text-{{ $comparison['sales'] >= 0 ? 'success' : 'danger' }} fs-8">
                                                <i
                                                    class="fas fa-arrow-{{ $comparison['sales'] >= 0 ? 'up' : 'down' }}"></i>
                                                {{ abs($comparison['sales']) }}%
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-gray-600 fs-7 fw-semibold">Customer Aktif</span>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span
                                            class="text-gray-800 fw-bold fs-5">{{ number_format($insights['activeCustomers'] ?? 0) }}
                                            santri</span>
                                        <span class="text-success fs-8"><i class="fas fa-check-circle"></i> Active</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-600 fs-7 fw-semibold">Repeat Customer Rate</span>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span
                                            class="text-gray-800 fw-bold fs-5">{{ number_format($insights['repeatRate'] ?? 0, 1) }}%</span>
                                        <span class="text-success fs-8"><i class="fas fa-trophy"></i> Excellent</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <i class="fas fa-lightbulb fs-2x text-info me-3"></i>
                                    <h3 class="card-title fw-bold text-gray-800 m-0">Rekomendasi</h3>
                                </div>
                                <div class="separator separator-dashed mb-4"></div>
                                @if (($insights['lowStockItems'] ?? 0) > 0)
                                    <div
                                        class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex flex-column flex-sm-row p-4 mb-3">
                                        <i class="fas fa-exclamation-triangle fs-2x text-danger me-4 mb-3 mb-sm-0"></i>
                                        <div class="d-flex flex-column pe-0 pe-sm-10">
                                            <h5 class="mb-1 text-gray-800">Stok Menipis!</h5>
                                            <span class="text-gray-700 fs-7">{{ $insights['lowStockItems'] }} produk perlu
                                                restock segera</span>
                                        </div>
                                    </div>
                                @endif

                                @if (isset($comparison['sales']) && $comparison['sales'] > 15)
                                    <div
                                        class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row p-4 mb-3">
                                        <i class="fas fa-chart-line fs-2x text-success me-4 mb-3 mb-sm-0"></i>
                                        <div class="d-flex flex-column pe-0 pe-sm-10">
                                            <h5 class="mb-1 text-gray-800">Trend Meningkat</h5>
                                            <span class="text-gray-700 fs-7">Penjualan naik
                                                {{ abs($comparison['sales']) }}% dari periode sebelumnya</span>
                                        </div>
                                    </div>
                                @endif

                                @if (($profitMargin ?? 0) < 20)
                                    <div
                                        class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex flex-column flex-sm-row p-4">
                                        <i class="fas fa-info-circle fs-2x text-warning me-4 mb-3 mb-sm-0"></i>
                                        <div class="d-flex flex-column pe-0 pe-sm-10">
                                            <h5 class="mb-1 text-gray-800">Perhatian</h5>
                                            <span class="text-gray-700 fs-7">Margin profit masih di bawah target 25%</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Insights Cards-->

                <!--begin::Tabs-->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_santri">
                                    <i class="fas fa-user-graduate me-2"></i>Transaksi Santri
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_umum">
                                    <i class="fas fa-users me-2"></i>Transaksi Umum
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_detail">
                                    <i class="fas fa-list-alt me-2"></i>Detail Produk
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <!--begin::Tab Santri-->
                            <div class="tab-pane fade show active" id="kt_tab_santri" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-transaksi-santri"
                                        class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-4 min-w-50px rounded-start">No</th>
                                                <th class="min-w-100px">Tanggal</th>
                                                <th class="min-w-125px">Kode Pembayaran</th>
                                                <th class="min-w-150px">Santri</th>
                                                <th class="min-w-100px">Kelas</th>
                                                <th class="min-w-100px">Jumlah</th>
                                                <th class="min-w-100px">Kasir</th>
                                                <th class="min-w-100px rounded-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!--begin::Tab Umum-->
                            <div class="tab-pane fade" id="kt_tab_umum" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-transaksi-umum"
                                        class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-4 min-w-50px rounded-start">No</th>
                                                <th class="min-w-100px">Tanggal</th>
                                                <th class="min-w-125px">Kode Pembayaran</th>
                                                <th class="min-w-100px">Total</th>
                                                <th class="min-w-100px">Profit</th>
                                                <th class="min-w-100px">Kasir</th>
                                                <th class="min-w-100px rounded-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!--begin::Tab Detail-->
                            <div class="tab-pane fade" id="kt_tab_detail" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-top-items"
                                        class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-4 min-w-50px rounded-start">Ranking</th>
                                                <th class="min-w-200px">Nama Produk</th>
                                                <th class="min-w-100px">Total Penjualan</th>
                                                <th class="min-w-100px rounded-end">Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Tabs-->

                <!--begin::Modal Detail Transaksi-->
                <div class="modal fade" id="modalDetailTransaksi" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h3 class="modal-title text-white">
                                    <i class="fas fa-receipt me-2"></i>Detail Transaksi
                                </h3>
                                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                                    <i class="fas fa-times fs-2x text-white"></i>
                                </div>
                            </div>
                            <div class="modal-body p-0">
                                <!-- Loading State -->
                                <div id="loadingDetail" class="d-flex justify-content-center align-items-center"
                                    style="min-height: 300px;">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status"
                                            style="width: 3rem; height: 3rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-gray-600 mt-3 fw-semibold">Memuat detail transaksi...</p>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div id="contentDetail" style="display: none;">
                                    <!-- Header Info -->
                                    <div class="bg-light-primary p-5 border-bottom">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Kode
                                                        Pembayaran</span>
                                                    <span class="text-gray-800 fw-bold fs-4"
                                                        id="detailPaymentCode">-</span>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Tanggal &
                                                        Waktu</span>
                                                    <span class="text-gray-800 fw-semibold fs-6" id="detailDate">-</span>
                                                </div>
                                                <div id="studentInfo" style="display: none;">
                                                    <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Santri</span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px me-3">
                                                            <div class="symbol-label bg-light-info">
                                                                <i class="fas fa-user-graduate fs-4 text-info"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-800 fw-bold d-block"
                                                                id="detailStudentName">-</span>
                                                            <span class="text-gray-600 fs-7"
                                                                id="detailStudentClass">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-md-end">
                                                    <div class="mb-3">
                                                        <span
                                                            class="text-gray-600 fs-7 fw-semibold d-block mb-1">Kasir</span>
                                                        <span class="text-gray-800 fw-semibold fs-6"
                                                            id="detailCashier">-</span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Total
                                                            Pembayaran</span>
                                                        <span class="text-primary fw-bold fs-2" id="detailTotal">Rp
                                                            0</span>
                                                    </div>
                                                    <div id="profitInfo" style="display: none;">
                                                        <span
                                                            class="text-gray-600 fs-7 fw-semibold d-block mb-1">Profit</span>
                                                        <span class="badge badge-light-success fs-4" id="detailProfit">Rp
                                                            0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Items Table -->
                                    <div class="p-5">
                                        <h4 class="text-gray-800 fw-bold mb-4">
                                            <i class="fas fa-shopping-basket text-primary me-2"></i>Rincian Pembelian
                                        </h4>
                                        <div class="table-responsive">
                                            <table id="tableDetailItems"
                                                class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                                                <thead>
                                                    <tr class="fw-bold text-muted bg-light">
                                                        <th class="ps-4 rounded-start">No</th>
                                                        <th>Nama Item</th>
                                                        <th class="text-center">Kategori</th>
                                                        <th class="text-end">Harga Satuan</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-end rounded-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600"></tbody>
                                                <tfoot>
                                                    <tr class="fw-bold text-gray-800 border-top border-2">
                                                        <td colspan="5" class="text-end pe-4 fs-5">GRAND TOTAL</td>
                                                        <td class="text-end fs-4 text-primary" id="footerTotal">Rp 0</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Summary Cards -->
                                    <div class="bg-light-info p-5 border-top">
                                        <div class="row g-4">
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-3 bg-white">
                                                        <i class="fas fa-box fs-2x text-info"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 fs-8 fw-semibold d-block">Total
                                                            Item</span>
                                                        <span class="text-gray-800 fw-bold fs-3"
                                                            id="summaryTotalItems">0</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-3 bg-white">
                                                        <i class="fas fa-layer-group fs-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 fs-8 fw-semibold d-block">Total
                                                            Quantity</span>
                                                        <span class="text-gray-800 fw-bold fs-3"
                                                            id="summaryTotalQty">0</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-3 bg-white">
                                                        <i class="fas fa-wallet fs-2x text-success"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 fs-8 fw-semibold d-block">Total
                                                            Bayar</span>
                                                        <span class="text-gray-800 fw-bold fs-3" id="summaryTotalPay">Rp
                                                            0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Tutup
                                </button>
                                <button type="button" class="btn btn-primary" id="btnPrintReceipt">
                                    <i class="fas fa-print me-2"></i>Cetak Struk
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Modal Detail Transaksi-->
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .hover-elevate-up {
            transition: all 0.3s ease;
        }

        .hover-elevate-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075) !important;
        }

        .card {
            border: none;
        }

        .card-flush {
            box-shadow: 0 0 20px 0 rgba(76, 87, 125, 0.02);
        }

        #modalDetailTransaksi .modal-content {
            border-radius: 10px;
            overflow: hidden;
        }

        #modalDetailTransaksi .modal-header {
            border-radius: 0;
        }
    </style>
@endpush

@push('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const modalEl = document.getElementById('modalDetailTransaksi');
        const detailModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        // Helper selector scoped ke modal
        const $M = (sel) => $(modalEl).find(sel);

        function resetDetailUI() {
            $M('#loadingDetail').show();
            $M('#contentDetail').hide();

            $M('#detailPaymentCode').text('-');
            $M('#detailDate').text('-');
            $M('#detailCashier').text('-');
            $M('#detailTotal').text('Rp 0');
            $M('#detailProfit').text('Rp 0');

            $M('#studentInfo').hide();
            $M('#profitInfo').hide();

            const $tbody = $M('#tableDetailItems tbody');
            $tbody.empty();

            $M('#footerTotal').text('Rp 0');
            $M('#summaryTotalItems').text('0');
            $M('#summaryTotalQty').text('0');
            $M('#summaryTotalPay').text('Rp 0');
        }

        function formatNumberSafe(n) {
            const v = Number(n);
            return Number.isFinite(v) ? v.toLocaleString('id-ID') : '0';
        }

        function fetchWithTimeout(resource, options = {}) {
            const {
                timeout = 15000
            } = options;
            const controller = new AbortController();
            const id = setTimeout(() => controller.abort(), timeout);
            return fetch(resource, {
                    ...options,
                    signal: controller.signal
                })
                .finally(() => clearTimeout(id));
        }

        async function showDetailTransaction(id, url) {
            // GUARD: kalau id kosong, jangan biarkan modal nyangkut spinner
            if (!id) {
                resetDetailUI();
                detailModal.hide();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada ID transaksi',
                    text: 'Tombol detail tidak membawa data-transaction-id.',
                    confirmButtonColor: '#009EF7'
                });
                return;
            }

            try {
                resetDetailUI();
                detailModal.show();

                const res = await fetchWithTimeout(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    timeout: 15000,
                });

                if (!res.ok) {
                    const text = await res.text().catch(() => '');
                    throw new Error(`HTTP ${res.status}${text ? `: ${text.slice(0, 120)}...` : ''}`);
                }

                const response = await res.json();

                $M('#detailPaymentCode').text(response?.payment_code ?? '-');
                $M('#detailDate').text(response?.date ?? '-');
                $M('#detailCashier').text(response?.cashier ?? '-');
                $M('#detailTotal').text('Rp ' + formatNumberSafe(response?.total ?? 0));

                if (response?.student) {
                    $M('#studentInfo').show();
                    $M('#detailStudentName').text(response.student?.name ?? '-');
                    $M('#detailStudentClass').text(response.student?.classroom ?? '-');
                    $M('#profitInfo').hide();
                } else {
                    $M('#studentInfo').hide();
                    $M('#profitInfo').show();
                    $M('#detailProfit').text('Rp ' + formatNumberSafe(response?.profit ?? 0));
                }

                const items = Array.isArray(response?.items) ? response.items : [];
                const $tbody = $M('#tableDetailItems tbody');
                let totalItems = 0,
                    totalQty = 0,
                    grandTotal = 0;

                items.forEach((it, i) => {
                    const qty = Number(it?.quantity) || 0;
                    const price = Number(it?.price) || 0;
                    const line = Number(it?.total_price) || qty * price;

                    totalItems++;
                    totalQty += qty;
                    grandTotal += line;

                    $tbody.append(`
          <tr>
            <td class="ps-4">${i + 1}</td>
            <td>
              <div class="d-flex align-items-center">
                <div class="symbol symbol-45px me-3">
                  <div class="symbol-label bg-light-primary">
                    <i class="fas fa-box text-primary fs-3"></i>
                  </div>
                </div>
                <div>
                  <span class="text-gray-800 fw-bold d-block">${it?.name ?? '-'}</span>
                  <span class="text-gray-600 fs-8">${it?.code ?? '-'}</span>
                </div>
              </div>
            </td>
            <td class="text-center">
              <span class="badge badge-light-info">${it?.category ?? '-'}</span>
            </td>
            <td class="text-end">Rp ${formatNumberSafe(price)}</td>
            <td class="text-center">
              <span class="badge badge-light-primary fs-6">${qty}</span>
            </td>
            <td class="text-end fw-bold text-gray-800">Rp ${formatNumberSafe(line)}</td>
          </tr>
        `);
                });

                $M('#footerTotal').text('Rp ' + formatNumberSafe(grandTotal));
                $M('#summaryTotalItems').text(String(totalItems));
                $M('#summaryTotalQty').text(String(totalQty));
                $M('#summaryTotalPay').text('Rp ' + formatNumberSafe(grandTotal));

                $M('#contentDetail').show();
            } catch (err) {
                console.error('Detail error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat detail',
                    text: ('' + err).slice(0, 200),
                    confirmButtonColor: '#009EF7'
                });
                detailModal.hide();
            } finally {
                $M('#loadingDetail').hide();
            }
        }

        // Delegate klik. Pastikan class dan atributnya sesuai (lihat poin #1).
        $(document).on('click', '.btn-detail-transaction', function(e) {
            e.preventDefault();
            const url = $(this).data('detail-url'); // <- pakai ini
            const id = $(this).data('transaction-id');
            showDetailTransaction(id, url);
        });

        // Reset UI setiap modal mau dibuka
        modalEl.addEventListener('show.bs.modal', resetDetailUI);
    </script>

    <script>
        $(document).ready(() => {
            const presetMap = {
                'Bulan Ini': 'this_month',
                'Bulan Kemarin': 'last_month',
                '3 Bulan Terakhir': 'last_3_months',
                '6 Bulan Terakhir': 'last_6_months',
                'Tahun Ini': 'this_year'
            };

            const initialStart = moment('{{ $filterStart }}', 'YYYY-MM-DD');
            const initialEnd = moment('{{ $filterEnd }}', 'YYYY-MM-DD');

            function updateDateRangeDisplay(startDate, endDate) {
                $('#dateRange span').html(startDate.format('D/MM/YYYY') + ' - ' + endDate.format('D/MM/YYYY'));
            }

            updateDateRangeDisplay(initialStart, initialEnd);

            $('#dateRange').daterangepicker({
                startDate: initialStart,
                endDate: initialEnd,
                autoUpdateInput: false,
                alwaysShowCalendars: true,
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal',
                    customRangeLabel: 'Rentang Lainnya'
                },
                ranges: {
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    '3 Bulan Terakhir': [moment().subtract(2, 'months').startOf('month'), moment().endOf(
                        'month')],
                    '6 Bulan Terakhir': [moment().subtract(5, 'months').startOf('month'), moment().endOf(
                        'month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
                }
            }, function(start, end) {
                updateDateRangeDisplay(start, end);
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                const label = picker.chosenLabel;
                $('#period').val(presetMap[label] || 'custom');

                updateDateRangeDisplay(picker.startDate, picker.endDate);
            });

            // Get current filter params
            function getFilterParams() {
                return {
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    period: $('#period').val()
                };
            }

            // DataTables initialization
            var tableSantri = $('#table-transaksi-santri').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('order-item-history.index') }}',
                    data: function(d) {
                        d.type = 'santri';
                        $.extend(d, getFilterParams());
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Memuat data...",
                    "processing": "Memproses...",
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "zeroRecords": "Tidak ada data yang ditemukan"
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
                deferLoading: 0, // Prevent initial load
                ajax: {
                    url: '{{ route('order-item-history.index') }}',
                    data: function(d) {
                        d.type = 'umum';
                        $.extend(d, getFilterParams());
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Memuat data...",
                    "processing": "Memproses...",
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "zeroRecords": "Tidak ada data yang ditemukan"
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
                deferLoading: 0, // Prevent initial load
                ajax: {
                    url: '{{ route('order-item-history.index') }}',
                    data: function(d) {
                        d.type = 'top-items';
                        $.extend(d, getFilterParams());
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Memuat data...",
                    "processing": "Memproses...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            var rank = meta.row + meta.settings._iDisplayStart + 1;
                            var badgeClass = rank === 1 ? 'badge-warning' : rank === 2 ?
                                'badge-light-warning' : 'badge-light-primary';
                            return '<span class="badge ' + badgeClass + ' fs-5">#' + rank +
                                '</span>';
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'total_transaction',
                        name: 'total_transaction',
                        render: function(data) {
                            return '<span class="badge badge-light-success">' + data +
                                ' transaksi</span>';
                        }
                    },
                    {
                        data: 'revenue',
                        name: 'revenue'
                    }
                ]
            });

            // Trigger load on tab click
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("href"); // activated tab
                if (target === '#kt_tab_umum') {
                    tableUmum.draw();
                } else if (target === '#kt_tab_detail') {
                    tableTopItems.draw();
                }
            });

            // Sales & Profit Chart
            var categories = @json($chartIncomesCategories ?? []);
            var omzet = @json($chartCashierOmzet ?? []);
            var profit = @json($chartCashierProfit ?? []);

            Highcharts.chart('salesChart', {
                chart: {
                    type: 'areaspline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories,
                    labels: {
                        style: {
                            color: '#7E8299'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Rupiah (Rp)',
                        style: {
                            color: '#7E8299'
                        }
                    },
                    labels: {
                        style: {
                            color: '#7E8299'
                        },
                        formatter: function() {
                            return 'Rp ' + (this.value / 1000000).toFixed(1) + 'jt';
                        }
                    }
                },
                legend: {
                    align: 'left',
                    verticalAlign: 'top',
                    itemStyle: {
                        color: '#7E8299',
                        fontWeight: 'normal'
                    }
                },
                tooltip: {
                    shared: true,
                    valuePrefix: 'Rp ',
                    formatter: function() {
                        var s = '<b>' + this.x + '</b>';
                        this.points.forEach(function(point) {
                            s += '<br/>' + point.series.name + ': Rp ' +
                                point.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        });
                        return s;
                    }
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.1,
                        marker: {
                            radius: 4
                        }
                    }
                },
                series: [{
                    name: 'Omzet',
                    data: omzet,
                    color: '#009EF7'
                }, {
                    name: 'Profit',
                    data: profit,
                    color: '#50CD89'
                }],
                credits: {
                    enabled: false
                }
            });

            // Top Categories Pie Chart
            var topCategories = @json($topCategories ?? []);

            Highcharts.chart('topCategoriesChart', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '<b>{point.percentage:.1f}%</b><br/>Total: Rp {point.revenue:,.0f}'
                },
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f}%',
                            style: {
                                color: '#7E8299',
                                fontSize: '11px'
                            }
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    name: 'Kategori',
                    colorByPoint: true,
                    data: topCategories.map(function(cat, index) {
                        var colors = ['#009EF7', '#50CD89', '#FFC700', '#F1416C',
                            '#7239EA'
                        ];
                        return {
                            name: cat.name,
                            y: parseFloat(cat.total_sales),
                            revenue: parseFloat(cat.total_revenue),
                            color: colors[index % colors.length]
                        };
                    })
                }],
                credits: {
                    enabled: false
                }
            });

            // Print receipt handler
            $('#btnPrintReceipt').on('click', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Mencetak Struk',
                    text: 'Fitur cetak struk akan segera tersedia',
                    confirmButtonColor: '#009EF7'
                });
            });

            // Refresh data handler
            $('#refreshData').on('click', function() {
                var btn = $(this);
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Refresh');
                btn.prop('disabled', true);

                // Reload all datatables
                tableSantri.ajax.reload();
                tableUmum.ajax.reload();
                tableTopItems.ajax.reload();

                // Reload page to refresh statistics
                setTimeout(() => {
                    location.reload();
                }, 1000);
            });
        });
    </script>
@endpush
