@extends('layouts.master', ['title' => 'Laporan Transaksi POS Multi-Outlet'])
@php
    $isKasir = auth()->user()->isKasir();
@endphp
@section('content')
<style>
    .premium-card {
        border-radius: 24px !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
        border: none !important;
    }
    .safe-padding {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    .nav-tabs-custom {
        border-bottom: 2px solid #f1f5f9;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 12px 20px;
        position: relative;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link.active {
        color: #2563eb;
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #2563eb;
    }
    .stat-badge {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 8px;
    }
    .modal-custom {
        border-radius: 24px !important;
    }
</style>

<div class="content d-flex flex-column flex-column-fluid safe-padding" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar py-5" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <x-text.h1 class="my-1">
                    @if(($mode ?? '') === 'kantin')
                        Laporan POS Kantin
                    @elseif(($mode ?? '') === 'outlet')
                        Laporan POS Outlet
                    @else
                        Laporan POS Bisnis
                    @endif
                </x-text.h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="#" class="text-muted text-hover-primary">Laporan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        @if(($mode ?? '') === 'kantin')
                            Kantin / Koperasi
                        @elseif(($mode ?? '') === 'outlet')
                            POS Outlet
                        @else
                            POS Bisnis Multi-Outlet
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-fluid px-0">

            <!-- Navigasi Tab Utama (Khusus Laporan POS Bisnis) -->
            @if(($mode ?? '') === 'bisnis' && !$isKasir)
            <ul class="nav nav-tabs nav-tabs-custom mb-6" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions-pane" type="button" role="tab" aria-controls="transactions-pane" aria-selected="true">
                        <i class="fa-solid fa-list-check me-2"></i> Laporan Transaksi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats-pane" type="button" role="tab" aria-controls="stats-pane" aria-selected="false">
                        <i class="fa-solid fa-chart-column me-2"></i> Statistik & Ringkasan
                    </button>
                </li>
                @canany(['Manage Laporan Pos Multi Outlet', 'Manage Laporan Pos Kasir'])
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="handover-tab" data-bs-toggle="tab" data-bs-target="#handover-pane" type="button" role="tab" aria-controls="handover-pane" aria-selected="false">
                        <i class="fa-solid fa-handshake-angle me-2"></i> Serah Terima Dana
                    </button>
                </li>
                @endcanany
            </ul>
            @endif

            <!-- Isi Tab Utama -->
            <div class="tab-content" id="reportTabsContent">

                <!-- TAB 1: LAPORAN TRANSAKSI -->
                <div class="tab-pane fade show active" id="transactions-pane" role="tabpanel" aria-labelledby="transactions-tab">
                    
                    <!-- GRID REKAP HARI INI, MINGGU INI, BULAN INI (Hanya Tampil di Mode POS Bisnis) -->
                    @if(($mode ?? '') === 'bisnis' && !$isKasir)
                    <div class="row g-6 mb-6">
                        <!-- Hari Ini -->
                        <div class="col-md-4">
                            <div class="card premium-card bg-white p-6">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Hari Ini</x-text.label>
                                    <span class="badge bg-light-primary text-primary px-3 py-1 fw-bold rounded-pill" id="today-count-badge">{{ $rekapWaktu['today_count'] }} Transaksi</span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Total Omzet</x-text.caption>
                                    <div class="d-flex align-items-baseline">
                                        <x-text.amount id="today-sales-text">Rp {{ number_format($rekapWaktu['today_sales'], 0, ',', '.') }}</x-text.amount>
                                    </div>
                                </div>
                                <div>
                                    <x-text.caption class="text-slate-400">Estimasi Keuntungan</x-text.caption>
                                    <div class="text-slate-800 fw-bold fs-6" id="today-profit-text">Rp {{ number_format($rekapWaktu['today_profit'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Minggu Ini -->
                        <div class="col-md-4">
                            <div class="card premium-card bg-white p-6">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Minggu Ini</x-text.label>
                                    <span class="badge bg-light-success text-success px-3 py-1 fw-bold rounded-pill" id="week-count-badge">{{ $rekapWaktu['week_count'] }} Transaksi</span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Total Omzet</x-text.caption>
                                    <div class="d-flex align-items-baseline">
                                        <x-text.amount id="week-sales-text">Rp {{ number_format($rekapWaktu['week_sales'], 0, ',', '.') }}</x-text.amount>
                                    </div>
                                </div>
                                <div>
                                    <x-text.caption class="text-slate-400">Estimasi Keuntungan</x-text.caption>
                                    <div class="text-slate-800 fw-bold fs-6" id="week-profit-text">Rp {{ number_format($rekapWaktu['week_profit'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulan Ini -->
                        <div class="col-md-4">
                            <div class="card premium-card bg-white p-6">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Bulan Ini</x-text.label>
                                    <span class="badge bg-light-warning text-warning px-3 py-1 fw-bold rounded-pill" id="month-count-badge">{{ $rekapWaktu['month_count'] }} Transaksi</span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Total Omzet</x-text.caption>
                                    <div class="d-flex align-items-baseline">
                                        <x-text.amount id="month-sales-text">Rp {{ number_format($rekapWaktu['month_sales'], 0, ',', '.') }}</x-text.amount>
                                    </div>
                                </div>
                                <div>
                                    <x-text.caption class="text-slate-400">Estimasi Keuntungan</x-text.caption>
                                    <div class="text-slate-800 fw-bold fs-6" id="month-profit-text">Rp {{ number_format($rekapWaktu['month_profit'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- CARD UTAMA: FILTER DAN TABEL TRANSAKSI -->
                    <div class="card premium-card mb-5">
                        <div class="card-header border-0 pt-6 px-8">
                            <div class="card-title">
                                <x-text.h2>Daftar Riwayat Transaksi</x-text.h2>
                            </div>
                            <div class="card-toolbar flex-wrap gap-4">
                                <form action="#" id="form-filter" method="get">
                                    <div class="d-flex flex-wrap gap-4 align-items-end">
                                        @if(($mode ?? '') !== 'bisnis')
                                        <div>
                                            <x-text.caption class="text-slate-500 d-block mb-1">Periode Transaksi</x-text.caption>
                                            <div class="btn-group btn-group-sm" role="group" id="quick-period-group">
                                                <button type="button" class="btn btn-sm btn-primary btn-period active" data-period="all">Semua</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-period" data-period="today">Hari Ini</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-period" data-period="week">Minggu Ini</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-period" data-period="month">Bulan Ini</button>
                                            </div>
                                            <input type="hidden" id="filter_period" name="period" value="all">
                                        </div>
                                        @else
                                            <input type="hidden" id="filter_period" name="period" value="">
                                        @endif

                                        <div>
                                            <x-text.caption class="text-slate-500 d-block mb-1">Filter Tanggal</x-text.caption>
                                            <div id="dateRange" class="d-flex align-items-center justify-content-between" style="background: #fff; cursor: pointer; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 12px;">
                                                <i class="fa-solid fa-calendar-days text-slate-400 me-2"></i>
                                                <span class="fs-7 fw-bold text-slate-700"></span> <b class="caret ms-2 text-slate-400"></b>
                                            </div>
                                            <input type="text" id="start_date" name="start_date" hidden>
                                            <input type="text" id="end_date" name="end_date" hidden>
                                        </div>

                                        @if(($mode ?? '') === 'bisnis')
                                        <div>
                                            <x-text.caption class="text-slate-500 d-block mb-1">Opsi Mode</x-text.caption>
                                            <select name="mode_filter" class="form-select form-select-solid rounded-3 fs-7" id="filter_mode_filter" style="width: 170px; border: 1px solid #cbd5e1; height: 38px;">
                                                <option value="all">Semua Unit</option>
                                                <option value="kantin">Kantin / Koperasi</option>
                                                <option value="outlet">Outlet Non-Koperasi</option>
                                            </select>
                                        </div>
                                        @endif

                                        <div>
                                            <x-text.caption class="text-slate-500 d-block mb-1">Status</x-text.caption>
                                            <select name="status" class="form-select form-select-solid rounded-3 fs-7" id="filter_status" style="width: 140px; border: 1px solid #cbd5e1; height: 38px;">
                                                <option value="">Semua Status</option>
                                                <option value="SUCCESS">Sukses</option>
                                                <option value="PENDING">Pending</option>
                                                <option value="FAILED">Gagal</option>
                                            </select>
                                        </div>

                                        @if(($mode ?? '') === 'bisnis' && (!$hasOutletRestriction || count($outlets) > 1))
                                        <div>
                                            <x-text.caption class="text-slate-500 d-block mb-1">Outlet</x-text.caption>
                                            <select name="outlet_id" class="form-select form-select-solid rounded-3 fs-7" id="filter_outlet_id" style="width: 180px; border: 1px solid #cbd5e1; height: 38px;">
                                                <option value="">Semua Outlet</option>
                                                @foreach ($outlets as $outlet)
                                                <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @else
                                            <input type="hidden" id="filter_outlet_id" value="{{ request('outlet_id') ?? ($outlets->first()->id ?? '') }}">
                                        @endif
                                    </div>
                                </form>

                                @if(!$isKasir)
                                <div class="d-flex gap-2">
                                    <div class="card bg-light-primary border-0 p-3 d-flex flex-row align-items-center gap-3">
                                        <i class="fa-solid fa-money-bill-trend-up text-primary fs-4"></i>
                                        <div>
                                            <div class="fs-8 text-slate-500 fw-bold">Omzet Filter</div>
                                            <div class="fs-6 fw-bolder text-primary" id="total-filtered-sales">Rp 0</div>
                                        </div>
                                    </div>
                                    <div class="card bg-light-success border-0 p-3 d-flex flex-row align-items-center gap-3">
                                        <i class="fa-solid fa-chart-line text-success fs-4"></i>
                                        <div>
                                            <div class="fs-8 text-slate-500 fw-bold">Profit Filter</div>
                                            <div class="fs-6 fw-bolder text-success" id="total-filtered-profit">Rp 0</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="card-body pt-0 px-8 pb-8">
                            <div class="table-responsive mt-6">
                                <table id="table-transactions" class="table align-middle table-row-dashed fs-7 gy-5">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Invoice</th>
                                            <th>Waktu</th>
                                            <th>Outlet</th>
                                            <th>Kasir</th>
                                            <th>Pembeli</th>
                                            <th>Item Belanja</th>
                                            <th>Total Omzet</th>
                                            @if(!$isKasir)
                                            <th>Profit</th>
                                            @endif
                                            <th>Status</th>
                                            <th class="text-center" style="width: 10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-semibold"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB STATISTIK & RINGKASAN -->
                @if(!$isKasir)
                <div class="tab-pane fade" id="stats-pane" role="tabpanel" aria-labelledby="stats-tab">
                    <!-- GRID REKAP TOTAL -->
                    <div class="row g-6 mb-6">
                        <!-- Total Produk -->
                        <div class="col-md-3">
                            <div class="card premium-card bg-white p-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0,0,0,0.04);">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Total Produk</x-text.label>
                                    <span class="badge bg-light-primary text-primary px-3 py-2 fw-bold rounded-pill"><i class="fa-solid fa-box text-primary fs-7"></i></span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Jumlah Produk Aktif</x-text.caption>
                                    <div class="fs-2 fw-bold text-slate-800" id="total-products-count">{{ number_format($totalProduct, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Transaksi -->
                        <div class="col-md-3">
                            <div class="card premium-card bg-white p-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0,0,0,0.04);">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Total Transaksi</x-text.label>
                                    <span class="badge bg-light-success text-success px-3 py-2 fw-bold rounded-pill"><i class="fa-solid fa-cash-register text-success fs-7"></i></span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Transaksi Sukses</x-text.caption>
                                    <div class="fs-2 fw-bold text-slate-800" id="total-transactions-count">{{ number_format($totalTransaction, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Penjualan -->
                        <div class="col-md-3">
                            <div class="card premium-card bg-white p-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0,0,0,0.04);">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Total Omzet</x-text.label>
                                    <span class="badge bg-light-danger text-danger px-3 py-2 fw-bold rounded-pill"><i class="fa-solid fa-hand-holding-usd text-danger fs-7"></i></span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Total Nilai Penjualan</x-text.caption>
                                    <x-text.amount id="total-sales-count">Rp {{ number_format($totalSales, 0, ',', '.') }}</x-text.amount>
                                </div>
                            </div>
                        </div>

                        <!-- Total Pendapatan -->
                        <div class="col-md-3">
                            <div class="card premium-card bg-white p-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0,0,0,0.04);">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.label class="text-slate-400">Total Keuntungan</x-text.label>
                                    <span class="badge bg-light-warning text-warning px-3 py-2 fw-bold rounded-pill"><i class="fa-solid fa-coins text-warning fs-7"></i></span>
                                </div>
                                <div class="mb-2">
                                    <x-text.caption class="text-slate-400">Estimasi Laba Bersih</x-text.caption>
                                    <x-text.amount id="total-profit-count">Rp {{ number_format($totalIncome, 0, ',', '.') }}</x-text.amount>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GRAFIK DAN PRODUK TERLARIS -->
                    <div class="row g-6 mb-6">
                        <div class="col-lg-8">
                            <div class="card premium-card bg-white p-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0,0,0,0.04);">
                                <div id="chart-container" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card premium-card bg-white p-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0,0,0,0.04); min-height: 448px;">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.h2>Produk Terlaris (Top 10)</x-text.h2>
                                </div>
                                <div class="table-responsive">
                                    <table id="table-top-items" class="table align-middle table-row-dashed fs-7 gy-4">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0">
                                                <th style="width: 10%">No</th>
                                                <th>Nama Produk</th>
                                                <th class="text-end">Terjual</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @canany(['Manage Laporan Pos Multi Outlet', 'Manage Laporan Pos Kasir'])
                <!-- TAB 2: SERAH TERIMA DANA -->
                <div class="tab-pane fade" id="handover-pane" role="tabpanel" aria-labelledby="handover-tab">
                    
                    <div class="row g-6 mb-6">
                        <!-- PANEL RINGKASAN OUTLET & TOMBOL AKSI -->
                        <div class="col-md-5">
                            <div class="card premium-card bg-white p-6 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.h2>Dana Belum Diserahkan</x-text.h2>
                                    @canany(['Create Laporan Pos Multi Outlet', 'Manage Laporan Pos Kasir'])
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-add-handover">
                                        <i class="fa-solid fa-file-invoice-dollar fs-6"></i> Catat Serah Terima
                                    </button>
                                    @endcanany
                                </div>
                                <x-text.body class="text-slate-500 mb-6">Berikut ringkasan total omzet nontunai (Saldo Santri) yang belum diserahterimakan ke pemilik masing-masing outlet. Aliran dana ini bersumber dari deposit Transaksi Topup Saldo masing-masing santri.</x-text.body>
                                
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-7 gy-4">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0">
                                                <th>Outlet</th>
                                                <th class="text-end">Omzet Saldo Santri</th>
                                                <th class="text-end">Belum Diserahkan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($outletsSummary as $otSum)
                                                <tr>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-slate-800 fs-7">{{ $otSum['name'] }}</div>
                                                        <div class="text-slate-400 fs-8">Kode: {{ $otSum['code'] }}</div>
                                                    </td>
                                                    <td class="text-end fw-semibold text-slate-600">Rp {{ number_format($otSum['total_sales'], 0, ',', '.') }}</td>
                                                    <td class="text-end">
                                                        @if ($otSum['pending_amount'] > 0)
                                                            <span class="badge bg-light-warning text-warning px-3 py-2 fw-bolder">Rp {{ number_format($otSum['pending_amount'], 0, ',', '.') }}</span>
                                                        @else
                                                            <span class="badge bg-light-success text-success px-3 py-2 fw-bolder">Lunas (Rp 0)</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 text-slate-400 italic">Data outlet tidak ditemukan</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL TABEL RIWAYAT SERAH TERIMA -->
                        <div class="col-md-7">
                            <div class="card premium-card bg-white p-6 h-100">
                                <div class="card-header border-0 p-0 mb-4">
                                    <div class="card-title">
                                        <x-text.h2>Riwayat Serah Terima Dana</x-text.h2>
                                    </div>
                                    <div class="card-toolbar">
                                        @if(!$hasOutletRestriction || count($outlets) > 1)
                                        <select class="form-select form-select-solid rounded-3 fs-7" id="handover_filter_outlet_id" style="width: 180px; border: 1px solid #cbd5e1; height: 34px;">
                                            <option value="">Semua Outlet</option>
                                            @foreach ($outlets as $outlet)
                                            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table id="table-handovers" class="table align-middle table-row-dashed fs-7 gy-4">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0">
                                                <th style="width: 5%">No</th>
                                                <th>Tanggal</th>
                                                <th>Outlet</th>
                                                <th>Penerima</th>
                                                <th>Nominal</th>
                                                <th>Bukti</th>
                                                <th>Diserahkan Oleh</th>
                                                <th class="text-center" style="width: 10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                @endcanany

            </div>

        </div>
    </div>
    <!--end::Post-->
</div>

@canany(['Create Laporan Pos Multi Outlet', 'Manage Laporan Pos Kasir'])
<!-- MODAL TAMBAH SERAH TERIMA DANA -->
<div class="modal fade" id="modal-add-handover" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content premium-card modal-custom p-6">
            <div class="modal-header border-0 pb-0">
                <x-text.h1>Catat Serah Terima Dana</x-text.h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark fs-4"></i>
                </div>
            </div>
            
            <form action="{{ route('outlet-handover.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-6">
                    <x-alert.alert-validation />
                    
                    <!-- Pilihan Outlet Pengirim -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-700 fs-7">Outlet Pengirim (Sumber)</label>
                        <select name="outlet_id" id="handover_form_outlet_id" class="form-select form-select-solid rounded-3" required>
                            <option value="">-- Pilih Outlet Pengirim --</option>
                            @foreach ($outlets as $outlet)
                                @php
                                    $isMain = in_array(strtoupper($outlet->code), ['KPR', 'KOPERASI']) || strtoupper($outlet->name) === 'KOPERASI';
                                @endphp
                                <option value="{{ $outlet->id }}" @if($isMain) selected @endif>{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilihan Outlet Penerima -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-700 fs-7">Outlet Penerima</label>
                        <select name="recipient_outlet_id" id="handover_form_recipient_outlet_id" class="form-select form-select-solid rounded-3" required>
                            <option value="">-- Pilih Outlet Penerima --</option>
                            @foreach ($outlets as $outlet)
                                @php
                                    $isMain = in_array(strtoupper($outlet->code), ['KPR', 'KOPERASI']) || strtoupper($outlet->name) === 'KOPERASI';
                                @endphp
                                @if(!$isMain)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Jumlah Nominal Dana -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-700 fs-7">Nominal Serah Terima (Rp)</label>
                        <input type="text" id="handover_form_amount_display" class="form-control form-control-solid rounded-3 input-money" placeholder="0" required>
                        <input type="hidden" name="amount" id="handover_form_amount_real">
                        <span class="fs-8 text-muted italic d-block mt-1" id="handover_suggestion_text">Pilih outlet penerima untuk melihat rekomendasi nominal.</span>
                    </div>

                    <!-- Penerima Dana -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-700 fs-7">Penerima Dana (Staff/Kasir)</label>
                        <select name="recipient_id" class="form-select form-select-solid rounded-3" required>
                            <option value="">-- Pilih Penerima --</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Serah Terima -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-700 fs-7">Tanggal Serah Terima</label>
                        <input type="date" name="handover_date" class="form-control form-control-solid rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <!-- Unggah Bukti Bayar -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-700 fs-7">Unggah Bukti Transfer / Pembayaran</label>
                        <input type="file" name="evidence" class="form-control form-control-solid rounded-3" accept="image/*">
                        <span class="fs-9 text-slate-400 d-block mt-1">Format file: jpeg, png, jpg, gif, svg (Maks. 2MB)</span>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="mb-0">
                        <label class="form-label fw-bold text-slate-700 fs-7">Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control form-control-solid rounded-3" rows="3" placeholder="Tambahkan keterangan tambahan jika ada..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 justify-content-end gap-3">
                    <button type="button" class="btn btn-light rounded-pill px-5" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanany

@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/11.4.0/highcharts.js"></script>
<script>
    var transactionTable;
    var handoverTable;
    var posChart;
    var tableTopItems;

    $(document).ready(function() {
        // Tentukan tanggal awal filter
        var start = moment().startOf('month');
        var end = moment().endOf('month');

        // Set nilai ke input hidden
        $('#start_date').val(start.format('YYYY-MM-DD'));
        $('#end_date').val(end.format('YYYY-MM-DD'));

        // Initialize Date Range Picker
        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
            }
        }, function(start, end) {
            $('#dateRange span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
            reloadTransactions();
        });

        // Set display teks awal
        $('#dateRange span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));

        // Load Tables
        initializeTransactionTable();
        if ($('#table-handovers').length) {
            initializeHandoverTable();
        }

        // Initialize Top Items table
        @if(!$isKasir)
        tableTopItems = $('#table-top-items').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: '{{ route('pos-transaction.index') }}',
                data: function(d) {
                    d.type = 'top-items';
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.outlet_id = $('#filter_outlet_id').val();
                }
            },
            columns: [
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'name', name: 'name' },
                { 
                    data: 'total_transaction', 
                    name: 'total_transaction',
                    className: 'text-end fw-bold text-slate-700'
                }
            ]
        });

        // Initialize Highcharts Chart
        var categories = @json($chartIncomesCategories);
        var omzet = @json($chartCashierOmzet);
        var profit = @json($chartCashierProfit);

        var options = {
            chart: {
                type: 'column',
                style: {
                    fontFamily: 'Inter, sans-serif'
                }
            },
            title: {
                text: 'Grafik Omzet dan Profit POS',
                align: 'left',
                style: {
                    fontWeight: 'bold',
                    color: '#1e293b'
                }
            },
            xAxis: {
                categories: categories,
                crosshair: true,
                labels: {
                    style: {
                        color: '#64748b'
                    }
                }
            },
            yAxis: [{
                title: {
                    text: 'Omzet (Rp)',
                    style: {
                        color: '#2563eb'
                    }
                },
                labels: {
                    formatter: function() {
                        return 'Rp ' + this.value.toLocaleString('id-ID');
                    },
                    style: {
                        color: '#64748b'
                    }
                }
            }, {
                title: {
                    text: 'Profit (Rp)',
                    style: {
                        color: '#10b981'
                    }
                },
                labels: {
                    formatter: function() {
                        return 'Rp ' + this.value.toLocaleString('id-ID');
                    },
                    style: {
                        color: '#64748b'
                    }
                },
                opposite: true
            }],
            tooltip: {
                shared: true,
                useHTML: true,
                formatter: function() {
                    var s = '<b>' + this.x + '</b><br/>';
                    $.each(this.points, function(i, point) {
                        s += '<span style="color:' + point.color + '">\u25CF</span> ' + point.series.name + ': <b>Rp ' + point.y.toLocaleString('id-ID') + '</b><br/>';
                    });
                    return s;
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    borderRadius: 4
                }
            },
            series: [{
                name: 'Omzet',
                data: omzet,
                color: '#2563eb'
            }, {
                name: 'Profit',
                data: profit,
                color: '#10b981',
                yAxis: 1
            }],
            credits: {
                enabled: false
            }
        };

        posChart = Highcharts.chart('chart-container', options);
        @endif

        // Load Initial Dynamic Summary
        @if(!$isKasir)
        fetchFilteredSummary();
        @endif

        $('#filter_status, #filter_outlet_id').on('change', function() {
            reloadTransactions();
            if ($('#stats-pane').hasClass('show') || $('#stats-pane').hasClass('active')) {
                const url = new URL(window.location.href);
                url.searchParams.set('outlet_id', $('#filter_outlet_id').val());
                window.location.href = url.toString();
            }
        });

        $('#handover_filter_outlet_id').on('change', function() {
            if (handoverTable) {
                handoverTable.ajax.reload();
            }
        });

        // Deteksi pergantian outlet penerima pada form serah terima dana untuk hitung sisa nominal secara dinamis
        $('#handover_form_recipient_outlet_id').on('change', function() {
            var outletId = $(this).val();
            if (outletId) {
                $('#handover_suggestion_text').html('<i class="fas fa-spinner fa-spin me-1"></i> Menghitung sisa dana...');
                $.ajax({
                    url: "{{ route('outlet-handover.pending-amount', ':id') }}".replace(':id', outletId),
                    type: "GET",
                    success: function(response) {
                        if (response.status === 'success') {
                            var pending = response.pending_amount;
                            // Set dynamic recommendation
                            $('#handover_form_amount_display').val(pending.toLocaleString('id-ID'));
                            $('#handover_form_amount_real').val(pending);
                            $('#handover_suggestion_text').html('Sisa dana outlet penerima yang belum diserahkan: <strong>Rp ' + response.pending_amount_formatted + '</strong>');
                        }
                    },
                    error: function() {
                        $('#handover_suggestion_text').text('Gagal mengambil data sisa dana outlet.');
                    }
                });
            } else {
                $('#handover_form_amount_display').val('0');
                $('#handover_form_amount_real').val('0');
                $('#handover_suggestion_text').text('Pilih outlet penerima untuk melihat rekomendasi nominal.');
            }
        });

        // Sinkronisasi input money kustom ke input real hidden saat user mengetik
        $('#handover_form_amount_display').on('keyup', function() {
            var displayVal = $(this).val();
            var numericVal = displayVal.replace(/[.,]/g, '') || 0;
            $('#handover_form_amount_real').val(numericVal);
        });

        // Event listener untuk tombol filter periode cepat (Hari Ini, Minggu Ini, Bulan Ini)
        $('.btn-period').on('click', function() {
            $('.btn-period').removeClass('active btn-primary').addClass('btn-outline-primary');
            $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
            var period = $(this).data('period');
            $('#filter_period').val(period);

            if (period !== 'all') {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#dateRange span').html('Filter Periode Cepat');
            } else {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#dateRange span').html('Semua Tanggal');
            }

            reloadTransactions();
        });

        $('#filter_mode_filter').on('change', function() {
            reloadTransactions();
        });

        // Pastikan form menyinkronkan nominal sebelum submit
        $('#modal-add-handover form').on('submit', function() {
            var displayVal = $('#handover_form_amount_display').val();
            var numericVal = displayVal.replace(/[.,]/g, '') || 0;
            $('#handover_form_amount_real').val(numericVal);
        });
    });

    function initializeTransactionTable() {
        transactionTable = $('#table-transactions').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searchDelay: 300,
            ajax: {
                url: "{{ route('pos-transaction.index') }}",
                data: function(d) {
                    d.data = 'table';
                    d.mode = '{{ $mode ?? "bisnis" }}';
                    d.period = $('#filter_period').val();
                    d.mode_filter = $('#filter_mode_filter').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.status = $('#filter_status').val();
                    d.outlet_id = $('#filter_outlet_id').val();
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
                { data: 'payment_code', name: 'payment_code' },
                { data: 'date', name: 'date' },
                { data: 'outlet', name: 'outlet' },
                { data: 'admin', name: 'admin' },
                { data: 'student', name: 'student' },
                { data: 'details', name: 'details' },
                { data: 'pay_amount', name: 'pay_amount' },
                @if(!$isKasir)
                { data: 'profit', name: 'profit' },
                @endif
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                processing: '<div class="d-flex align-items-center justify-content-center h-100"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>'
            }
        });
    }

    function initializeHandoverTable() {
        handoverTable = $('#table-handovers').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searchDelay: 300,
            ajax: {
                url: "{{ route('outlet-handover.index') }}",
                data: function(d) {
                    d.outlet_id = $('#handover_filter_outlet_id').val() || $('#filter_outlet_id').val();
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
                { data: 'date', name: 'handover_date' },
                { data: 'outlet', name: 'outlet.name' },
                { data: 'recipient', name: 'recipient_name' },
                { data: 'amount', name: 'amount' },
                { data: 'evidence', name: 'evidence', orderable: false, searchable: false },
                { data: 'creator', name: 'creator.name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }

    function fetchFilteredSummary() {
        $.ajax({
            url: "{{ route('pos-transaction.index') }}",
            type: "GET",
            dataType: 'json',
            data: {
                data: 'total',
                mode: '{{ $mode ?? "bisnis" }}',
                period: $('#filter_period').val(),
                mode_filter: $('#filter_mode_filter').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                status: $('#filter_status').val(),
                outlet_id: $('#filter_outlet_id').val()
            },
            success: function(response) {
                // Update rekap filter di sebelah kanan form
                $('#total-filtered-sales').text(response.total_sales);
                $('#total-filtered-profit').text(response.total_profit);

                // Update 3 card rekap utama secara dinamis berdasarkan filter outlet
                $('#today-sales-text').text(response.today_sales);
                $('#today-profit-text').text(response.today_profit);
                $('#today-count-badge').text(response.today_count + ' Transaksi');

                $('#week-sales-text').text(response.week_sales);
                $('#week-profit-text').text(response.week_profit);
                $('#week-count-badge').text(response.week_count + ' Transaksi');

                $('#month-sales-text').text(response.month_sales);
                $('#month-profit-text').text(response.month_profit);
                $('#month-count-badge').text(response.month_count + ' Transaksi');

                // Update 4 card rekap utama di tab Statistik & Ringkasan
                $('#total-products-count').text(response.total_products);
                $('#total-transactions-count').text(response.total_transactions);
                $('#total-sales-count').text(response.total_sales);
                $('#total-profit-count').text(response.total_profit);

                // Update Highcharts Chart secara dinamis
                if (typeof posChart !== 'undefined' && posChart) {
                    posChart.update({
                        xAxis: {
                            categories: response.chart_categories
                        },
                        series: [{
                            data: response.chart_omzet
                        }, {
                            data: response.chart_profit
                        }]
                    });
                }
            }
        });
    }

    function reloadTransactions() {
        transactionTable.ajax.reload();
        @if(!$isKasir)
        if (typeof tableTopItems !== 'undefined' && tableTopItems) {
            tableTopItems.ajax.reload();
        }
        fetchFilteredSummary();
        @endif
    }
</script>
@endpush