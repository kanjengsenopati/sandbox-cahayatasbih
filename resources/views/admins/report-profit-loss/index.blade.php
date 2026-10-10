@extends('layouts.master', ['title' => 'Laporan Arus Keuangan & Rugi Laba'])

@push('css')
    <style>
        .premium-card {
            border-radius: 24px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
            border: none !important;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.06) !important;
        }
        .typography-h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-h2 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-amount {
            font-size: 18px;
            font-weight: 700;
            color: #059669; /* Emerald 600 */
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-amount.text-danger {
            color: #dc2626 !important; /* Red 600 */
        }
        .typography-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .typography-body {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            font-family: 'Inter', sans-serif;
        }
        .typography-caption {
            font-size: 12px;
            font-weight: 400;
            font-style: italic;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
        }
        
        /* Table Styles */
        .pl-table th {
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0 !important;
        }
        .pl-table td {
            font-size: 14px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .pl-table tr.total-row td {
            font-weight: 700 !important;
            background-color: #f8fafc;
            border-top: 1px solid #cbd5e1 !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        .pl-table tr.main-total-row td {
            font-weight: 800 !important;
            font-size: 15px !important;
            background-color: #f1f5f9;
            border-top: 2px solid #94a3b8 !important;
            border-bottom: 2px solid #94a3b8 !important;
        }
        
        .indent-1 {
            padding-left: 2.5rem !important;
        }
        
        /* Print styling */
        @media print {
            #kt_header, #kt_aside, #kt_footer, .toolbar, .filter-section, .btn-print-action, .nav-tabs {
                display: none !important;
            }
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
            }
            #tab_transaksi, #tab_handover {
                display: none !important;
            }
            .wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }
            .content {
                padding: 0 !important;
            }
            .premium-card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                margin-bottom: 0 !important;
            }
            body {
                background: #ffffff !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid px-5" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <x-text.h1>Arus Keuangan & Rugi Laba</x-text.h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Real Movement & Rugi Laba</li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                @include('layouts.partials.outlet_switcher')
                <button onclick="window.print()" class="btn btn-sm btn-light-primary btn-print-action d-flex align-items-center gap-2" style="border-radius: 12px;">
                    <i class="bi bi-printer fs-6"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Filters-->
            <div class="premium-card p-6 mb-6 filter-section">
                <form action="{{ route('report-profit-loss.index') }}" method="GET" class="row g-4 align-items-end">
                    <input type="hidden" name="mode" value="{{ request('mode') }}">
                    <!-- Outlet Filter -->
                    <div class="col-md-4">
                        <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Pilih Outlet</label>
                        <select name="outlet_id" id="filter_outlet_id" class="form-select" style="border-radius: 12px; background-color: #fff; border: 1px solid #ccc; padding: 7px 14px; color: #475569; font-weight: 500;">
                            @if(!$hasOutletRestriction)
                                <option value="">Semua Outlet</option>
                            @endif
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-5">
                        <label class="form-label mb-1 fw-bold text-gray-700 fs-7">Rentang Tanggal</label>
                        <div class="d-flex gap-2 align-items-center">
                            <div id="dateRange" style="background: #fff; cursor: pointer; padding: 7px 14px; border: 1px solid #ccc; border-radius: 12px; color: #475569; font-weight: 500; width: 100%; display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="bi bi-calendar3 me-2"></i></span>
                                <b class="caret"></b>
                            </div>
                            <input type="hidden" id="start_date" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; padding: 9px 14px;">
                            <i class="bi bi-filter-circle fs-5"></i> Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
            <!--end::Filters-->

            <!--begin::Tabs Navigation-->
            <ul class="nav nav-tabs nav-line-tabs mb-6 fs-6 filter-section" role="tablist" style="border-bottom: 2px solid #e2e8f0;">
                <li class="nav-item">
                    <a class="nav-link active fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_report" role="tab" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-graph-up me-2"></i> Ringkasan Rugi Laba & Real Arus Kas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_handover" role="tab" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-arrow-left-right me-2"></i> Serah Terima Dana (Handover)
                        @if($totalPendingHandover > 0)
                            <span class="badge badge-warning text-dark ms-2 px-2 py-1 fs-9 rounded-pill">Pending</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_transaksi" role="tab" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-wallet2 me-2"></i> Catat Beban Operasional
                    </a>
                </li>
            </ul>
            <!--end::Tabs Navigation-->

            <!--begin::Tab Content-->
            <div class="tab-content">
                <!-- ==========================================
                     TAB 1: LAPORAN RUGI LABA & REAL ARUS KAS
                     ========================================== -->
                <div class="tab-pane fade show active" id="tab_report" role="tabpanel">
                    <!--begin::Summary Cards-->
                    <div class="row g-5 mb-6">
                        <!-- Card 1: Omzet POS Log Sistem -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Total Omzet POS (Log Sistem)</x-text.label>
                                    <div class="bg-light-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-receipt-cutoff text-primary fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount style="color: #2563eb !important;">Rp {{ number_format($posSalesTotal, 0, ',', '.') }}</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">
                                    Saldo Santri: <strong>Rp {{ number_format($posSantriSalesTotal, 0, ',', '.') }}</strong><br>
                                    Kasir Tunai: <strong>Rp {{ number_format($posUmumSalesTotal, 0, ',', '.') }}</strong>
                                </x-text.caption>
                            </div>
                        </div>

                        <!-- Card 2: Kas Riil Diterima (Real Handover) -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Kas Riil Handover Diterima</x-text.label>
                                    <div class="bg-light-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-cash-stack text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount class="text-success">Rp {{ number_format($totalRealHandover, 0, ',', '.') }}</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">
                                    Saldo Koperasi: <strong>Rp {{ number_format($santriHandoverTotal, 0, ',', '.') }}</strong><br>
                                    Setor Kasir: <strong>Rp {{ number_format($cashierHandoverTotal, 0, ',', '.') }}</strong>
                                </x-text.caption>
                            </div>
                        </div>

                        <!-- Card 3: Pending Handover (Belum Diserahkan) -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Pending Belum Handover</x-text.label>
                                    <div class="bg-light-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount class="{{ $totalPendingHandover > 0 ? 'text-warning' : '' }}" style="{{ $totalPendingHandover > 0 ? 'color: #d97706 !important;' : '' }}">
                                        Rp {{ number_format($totalPendingHandover, 0, ',', '.') }}
                                    </x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">
                                    Pending Koperasi: <strong>Rp {{ number_format($pendingSantriHandover, 0, ',', '.') }}</strong><br>
                                    Pending Kasir: <strong>Rp {{ number_format($pendingCashierHandover, 0, ',', '.') }}</strong>
                                </x-text.caption>
                            </div>
                        </div>

                        <!-- Card 4: Laba Bersih Operasional -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Laba Bersih Operasional</x-text.label>
                                    <div class="{{ $netProfit >= 0 ? 'bg-light-success' : 'bg-light-danger' }} rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi {{ $netProfit >= 0 ? 'bi-shield-check text-success' : 'bi-shield-exclamation text-danger' }} fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount class="{{ $netProfit >= 0 ? '' : 'text-danger' }}">
                                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                                    </x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">
                                    HPP: <strong>Rp {{ number_format($totalHpp, 0, ',', '.') }}</strong> | Beban: <strong>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</strong>
                                </x-text.caption>
                            </div>
                        </div>
                    </div>
                    <!--end::Summary Cards-->

                    <!--begin::Reconciliation Box (Real Movement Arus Kas)-->
                    <div class="premium-card p-8 mb-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div>
                                <x-text.h2>Rekonsiliasi Real Movement Arus Keuangan</x-text.h2>
                                <x-text.caption class="text-muted d-block mt-1">
                                    Membandingkan riwayat transaksi log sistem POS dengan fisik dana riil yang sudah diserahterimakan (berbukti setor/transfer).
                                </x-text.caption>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-add-handover-koperasi" style="border-radius: 12px;">
                                    <i class="bi bi-bank fs-6"></i> + Handover Saldo Koperasi
                                </button>
                                <button type="button" class="btn btn-sm btn-light-warning text-dark d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-add-handover-cashier" style="border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-cash fs-6 text-warning"></i> + Setor Kasir Tunai
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle pl-table table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-800 fw-bold fs-7 text-uppercase">
                                        <th style="width: 32%;">Aliran Arus Kas</th>
                                        <th style="width: 17%;" class="text-end">Log Transaksi Sistem</th>
                                        <th style="width: 17%;" class="text-end">Sudah Handover (Real)</th>
                                        <th style="width: 17%;" class="text-end">Pending (Belum Serah)</th>
                                        <th style="width: 17%;" class="text-center">Status Rekonsiliasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Row 1: Saldo Santri Koperasi ➔ Outlet -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-light-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="bi bi-credit-card text-primary fs-5"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-gray-800 d-block fs-7">Saldo Belanja Santri (Koperasi ➔ Outlet)</span>
                                                    <small class="text-muted">Pintu masuk saldo di Koperasi, dicairkan ke Outlet</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-7">
                                            Rp {{ number_format($posSantriSalesTotal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-success fs-7">
                                            Rp {{ number_format($santriHandoverTotal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold {{ $pendingSantriHandover > 0 ? 'text-warning' : 'text-muted' }} fs-7">
                                            Rp {{ number_format($pendingSantriHandover, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if($pendingSantriHandover <= 0)
                                                <span class="badge badge-light-success fw-bold px-3 py-2 text-success" style="border-radius: 8px;">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i> Tuntas / Settled
                                                </span>
                                            @else
                                                <span class="badge badge-light-warning fw-bold px-3 py-2 text-warning" style="border-radius: 8px;">
                                                    <i class="bi bi-exclamation-circle-fill text-warning me-1"></i> Pending Koperasi
                                                </span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Row 2: Kas Tunai Kasir ➔ Pengelola -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-light-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="bi bi-cash-coin text-warning fs-5"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-gray-800 d-block fs-7">Setoran Kasir Tunai (Kasir ➔ Manajemen)</span>
                                                    <small class="text-muted">Kas fisik dari transaksi umum di laci kasir (Yogo, Khurotun, dll)</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-7">
                                            Rp {{ number_format($posUmumSalesTotal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-success fs-7">
                                            Rp {{ number_format($cashierHandoverTotal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold {{ $pendingCashierHandover > 0 ? 'text-danger' : 'text-muted' }} fs-7">
                                            Rp {{ number_format($pendingCashierHandover, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if($pendingCashierHandover <= 0)
                                                <span class="badge badge-light-success fw-bold px-3 py-2 text-success" style="border-radius: 8px;">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i> Tuntas / Settled
                                                </span>
                                            @else
                                                <span class="badge badge-light-danger fw-bold px-3 py-2 text-danger" style="border-radius: 8px;">
                                                    <i class="bi bi-clock-history text-danger me-1"></i> Kas di Tangan Kasir
                                                </span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Total Rekonsiliasi Row -->
                                    <tr class="main-total-row">
                                        <td class="text-dark fw-bold">TOTAL REKONSILIASI KEUANGAN</td>
                                        <td class="text-end fw-bold text-dark">Rp {{ number_format($posSalesTotal, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($totalRealHandover, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold {{ $totalPendingHandover > 0 ? 'text-danger' : 'text-dark' }}">
                                            Rp {{ number_format($totalPendingHandover, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if($totalPendingHandover <= 0)
                                                <span class="badge badge-success px-3 py-2 fw-bold" style="border-radius: 8px;">100% Tuntas</span>
                                            @else
                                                <span class="badge badge-warning text-dark px-3 py-2 fw-bold" style="border-radius: 8px;">Ada Pending Kas</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Reconciliation Box-->

                    <!--begin::Profit Loss Statement-->
                    <div class="premium-card p-8">
                        <div class="d-flex align-items-center justify-content-between mb-6">
                            <div>
                                <x-text.h2>Laporan Rugi Laba Komprehensif</x-text.h2>
                                <x-text.caption class="text-muted d-block mt-1">
                                    Outlet: <strong>{{ $selectedOutlet ? $selectedOutlet->name : 'Semua Outlet' }}</strong> | 
                                    Periode: <strong>{{ Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
                                </x-text.caption>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle pl-table table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-800 fw-bold fs-7 text-uppercase">
                                        <th style="width: 70%;">Deskripsi Akun</th>
                                        <th class="text-end" style="width: 30%;">Nominal (IDR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- PENDAPATAN -->
                                    <tr>
                                        <td class="fw-bold"><x-text.label class="text-dark">1. Pendapatan Operasional</x-text.label></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="indent-1 text-slate-700">Pendapatan Penjualan POS (Kasir)</td>
                                        <td class="text-end fw-bold text-slate-800">Rp {{ number_format($posSalesTotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @if(count($cashIncomesBreakdown) > 0)
                                        @foreach($cashIncomesBreakdown as $income)
                                            <tr>
                                                <td class="indent-1 text-slate-700">Pemasukan Kas - {{ $income->cashflow_category?->name ?? 'Kategori Lain' }}</td>
                                                <td class="text-end fw-bold text-slate-800">Rp {{ number_format($income->total, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr class="total-row">
                                        <td class="text-dark fw-bold">Total Pendapatan Operasional</td>
                                        <td class="text-end fw-bold text-dark">Rp {{ number_format($totalRevenues, 0, ',', '.') }}</td>
                                    </tr>

                                    <!-- HARGA POKOK PENJUALAN -->
                                    <tr>
                                        <td class="fw-bold pt-6"><x-text.label class="text-dark">2. Harga Pokok Penjualan (HPP)</x-text.label></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="indent-1 text-slate-700">HPP Penjualan POS (Beban Pokok Barang)</td>
                                        <td class="text-end fw-bold text-slate-800">Rp {{ number_format($posCostTotal, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td class="text-dark fw-bold">Total Beban Pokok Penjualan (HPP)</td>
                                        <td class="text-end fw-bold text-dark">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                                    </tr>

                                    <!-- LABA KOTOR -->
                                    <tr class="main-total-row">
                                        <td class="text-slate-900 fw-bold">LABA KOTOR (Gross Profit)</td>
                                        <td class="text-end fw-bold text-slate-900">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                                    </tr>

                                    <!-- BEBAN OPERASIONAL -->
                                    <tr>
                                        <td class="fw-bold pt-6"><x-text.label class="text-dark">3. Beban Operasional</x-text.label></td>
                                        <td></td>
                                    </tr>
                                    @if(count($cashExpensesBreakdownFormatted) > 0)
                                        @foreach($cashExpensesBreakdownFormatted as $expense)
                                            <tr>
                                                <td class="indent-1 text-slate-700">Beban Kas - {{ $expense->category_name }}</td>
                                                <td class="text-end fw-bold text-slate-800">Rp {{ number_format($expense->total, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="indent-1 text-muted italic">Tidak ada beban operasional tercatat</td>
                                            <td class="text-end text-muted">-</td>
                                        </tr>
                                    @endif
                                    <tr class="total-row">
                                        <td class="text-dark fw-bold">Total Beban Operasional</td>
                                        <td class="text-end fw-bold text-dark">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                                    </tr>

                                    <!-- LABA/RUGI BERSIH -->
                                    <tr class="main-total-row">
                                        <td class="text-slate-900 fw-bold">LABA / RUGI BERSIH OPERASIONAL (Net Profit)</td>
                                        <td class="text-end fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}" style="color: {{ $netProfit >= 0 ? '#059669' : '#dc2626' }} !important;">
                                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Profit Loss Statement-->
                </div>

                <!-- ==========================================
                     TAB 2: SERAH TERIMA DANA (HANDOVER MOVEMENT)
                     ========================================== -->
                <div class="tab-pane fade" id="tab_handover" role="tabpanel">
                    <div class="premium-card p-8">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-6">
                            <div>
                                <x-text.h2>Riwayat Serah Terima Dana (Handover)</x-text.h2>
                                <x-text.caption class="text-muted d-block mt-1">
                                    Catatan perpindahan dana kas nyata antara Koperasi, Kasir, dan Pengelola Outlet beserta bukti transfer/setoran.
                                </x-text.caption>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-add-handover-koperasi" style="border-radius: 12px;">
                                    <i class="bi bi-bank fs-6"></i> + Handover Saldo Koperasi ➔ Outlet
                                </button>
                                <button type="button" class="btn btn-light-warning text-dark d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-add-handover-cashier" style="border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-cash fs-6 text-warning"></i> + Setor Kasir Tunai (Shift Closing)
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle pl-table table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-800 fw-bold fs-7 text-uppercase">
                                        <th style="width: 5%">No</th>
                                        <th style="width: 12%">Tanggal</th>
                                        <th style="width: 16%">Jenis Handover</th>
                                        <th style="width: 15%">Sumber / Kasir</th>
                                        <th style="width: 15%">Penerima</th>
                                        <th style="width: 12%" class="text-end">Nominal Log</th>
                                        <th style="width: 13%" class="text-end">Nominal Riil</th>
                                        <th style="width: 8%" class="text-center">Bukti</th>
                                        <th style="width: 4%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($handoversList) > 0)
                                        @foreach($handoversList as $index => $handover)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ Carbon\Carbon::parse($handover->handover_date)->translatedFormat('d M Y') }}</td>
                                                <td>
                                                    @if($handover->handover_type === App\Models\OutletHandover::TYPE_CASHIER_TO_MANAGEMENT)
                                                        <span class="badge badge-light-warning fw-bold px-3 py-2 text-warning" style="border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.2);">
                                                            <i class="fas fa-cash-register me-1 fs-8 text-warning"></i> Setor Kasir Tunai
                                                        </span>
                                                    @else
                                                        <span class="badge badge-light-primary fw-bold px-3 py-2 text-primary" style="border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.2);">
                                                            <i class="fas fa-university me-1 fs-8 text-primary"></i> Saldo Koperasi ➔ Outlet
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($handover->handover_type === App\Models\OutletHandover::TYPE_CASHIER_TO_MANAGEMENT)
                                                        <strong>{{ $handover->cashier?->name ?? 'Kasir' }}</strong>
                                                        <br><small class="text-muted">{{ $handover->outlet?->name ?? '-' }}</small>
                                                    @else
                                                        <strong>{{ $handover->outlet?->name ?? 'Koperasi' }}</strong>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $handover->recipient?->name ?? $handover->recipient_name }}</strong>
                                                    <br><small class="text-muted">({{ $handover->recipientOutlet?->name ?? '-' }})</small>
                                                </td>
                                                <td class="text-end text-muted fw-semibold">
                                                    Rp {{ number_format($handover->system_amount ?: $handover->amount, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end fw-bold text-success">
                                                    Rp {{ number_format($handover->amount, 0, ',', '.') }}
                                                    @if($handover->discrepancy != 0)
                                                        <br>
                                                        @if($handover->discrepancy > 0)
                                                            <small class="text-success fw-bold">(+Rp {{ number_format($handover->discrepancy, 0, ',', '.') }})</small>
                                                        @else
                                                            <small class="text-danger fw-bold">(-Rp {{ number_format(abs($handover->discrepancy), 0, ',', '.') }})</small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($handover->evidence_path)
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-primary rounded-circle img-proof-zoom" data-src="{{ asset($handover->evidence_path) }}" title="Lihat Bukti Transfer / Setoran">
                                                            <i class="bi bi-image fs-6"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted fst-italic fs-8">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('outlet-handover.destroy', $handover->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat serah terima dana ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-icon btn-light-danger btn-sm rounded-circle" title="Hapus Riwayat">
                                                            <i class="bi bi-trash-fill fs-7"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-8">
                                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                                                Belum ada riwayat serah terima dana (handover) dalam periode dan outlet ini.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                     TAB 3: CATAT BEBAN OPERASIONAL
                     ========================================== -->
                <div class="tab-pane fade" id="tab_transaksi" role="tabpanel">
                    <div class="row g-6">
                        <!-- Form Entry Pengeluaran (Left Side) -->
                        <div class="col-lg-4 col-md-5">
                            <div class="premium-card p-6" style="border-radius: 24px;">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.h2>Input Pengeluaran Operasional</x-text.h2>
                                </div>
                                <hr class="text-slate-200 mb-4" />

                                <form action="{{ route('report-profit-loss.store-expense') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <!-- Outlet Target -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Outlet <span class="text-danger">*</span></label>
                                        <select name="outlet_id" id="form_outlet_id" class="form-select" style="border-radius: 12px;" required>
                                            <option value="">Pilih Outlet</option>
                                            @foreach($outlets as $outlet)
                                                <option value="{{ $outlet->id }}" {{ (request('outlet_id') == $outlet->id || count($outlets) == 1) ? 'selected' : '' }}>
                                                    {{ $outlet->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Kategori Pengeluaran -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Kategori Pengeluaran <span class="text-danger">*</span></label>
                                        <select name="cash_flow_category_id" id="cash_flow_category_id" class="form-select" style="border-radius: 12px;" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach($expenseCategories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Custom Sub-Category (Conditional) -->
                                    <div class="mb-4" id="custom_category_wrapper" style="display: none;">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Nama Kategori Lainnya <span class="text-danger">*</span></label>
                                        <input type="text" name="custom_category" id="custom_category" class="form-control" placeholder="Contoh: Beli ATK Kasir, Plastik..." style="border-radius: 12px;">
                                    </div>

                                    <!-- Nominal Pengeluaran -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Nominal (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text fw-bold" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">Rp</span>
                                            <input type="text" name="amount" id="form_amount_display" class="form-control" placeholder="0" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;" required>
                                        </div>
                                    </div>

                                    <!-- Tanggal Transaksi -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" style="border-radius: 12px;" required>
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Keterangan / Catatan</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi rinci pengeluaran..." style="border-radius: 12px;"></textarea>
                                    </div>

                                    <!-- Bukti Pembayaran -->
                                    <div class="mb-6">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Bukti Pembayaran / Nota (Foto/PDF)</label>
                                        <input type="file" name="proof_of_payment" class="form-control" accept="image/*,application/pdf" style="border-radius: 12px;">
                                        <x-text.caption class="text-muted mt-1 d-block">Maksimal 2MB (jpg, png, pdf)</x-text.caption>
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px;">
                                        <i class="bi bi-save-fill"></i> Simpan Transaksi
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- List Operational Expenses (Right Side) -->
                        <div class="col-lg-8 col-md-7">
                            <div class="premium-card p-6" style="border-radius: 24px;">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <x-text.h2>Daftar Pengeluaran Operasional</x-text.h2>
                                    <x-text.caption class="text-muted">
                                        Total Terfilter: <strong>{{ count($expensesList) }} Transaksi</strong>
                                    </x-text.caption>
                                </div>
                                <hr class="text-slate-200 mb-4" />

                                <div class="table-responsive">
                                    <table class="table align-middle pl-table table-row-dashed">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th style="width: 5%">No</th>
                                                <th style="width: 15%">Tanggal</th>
                                                <th style="width: 20%">Kategori</th>
                                                <th style="width: 18%">Nominal</th>
                                                <th style="width: 22%">Keterangan</th>
                                                <th style="width: 10%">Bukti</th>
                                                <th class="text-center" style="width: 10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-bold">
                                            @if(count($expensesList) > 0)
                                                @foreach($expensesList as $index => $expense)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ Carbon\Carbon::parse($expense->date)->translatedFormat('d M Y') }}</td>
                                                        <td>
                                                            <span class="badge bg-light-primary text-primary px-3 py-2 rounded-pill" style="font-weight: 600;">
                                                                {{ $expense->display_category_name }}
                                                            </span>
                                                        </td>
                                                        <td class="text-danger">
                                                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                                        </td>
                                                        <td class="typography-body fs-7" style="max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            {{ $expense->display_description ?? '-' }}
                                                        </td>
                                                        <td>
                                                            @if($expense->proof_of_payment)
                                                                <button type="button" class="btn btn-sm btn-icon btn-light-primary rounded-circle img-proof-zoom" data-src="{{ asset($expense->proof_of_payment) }}" title="Lihat Bukti Nota">
                                                                    <i class="bi bi-image fs-6"></i>
                                                                </button>
                                                            @else
                                                                <span class="text-muted fst-italic fs-7" style="font-weight: normal;">No file</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <form action="{{ route('report-profit-loss.destroy-expense', [$expense->id, 'mode' => request('mode'), 'outlet_id' => request('outlet_id')]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi pengeluaran ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-icon btn-light-danger btn-sm rounded-circle" title="Hapus Transaksi">
                                                                    <i class="bi bi-trash-fill fs-6"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-6">Tidak ada transaksi pengeluaran dalam periode & outlet ini</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Tab Content-->
        </div>
    </div>
    <!--end::Post-->
</div>

<!-- ============================================================
     MODAL 1: HANDOVER SALDO KOPERASI ➔ OUTLET
     ============================================================ -->
<div class="modal fade" id="modal-add-handover-koperasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: none;">
            <div class="modal-header border-0 pb-0 px-8 pt-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-bank fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold" style="font-family: 'Outfit', sans-serif;">Handover Saldo Koperasi ➔ Outlet</h4>
                        <span class="text-muted fs-7">Transfer pencairan saldo belanja santri dari rekening Koperasi ke Outlet penerima.</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('outlet-handover.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="handover_type" value="{{ App\Models\OutletHandover::TYPE_KOPERASI_TO_OUTLET }}">
                <input type="hidden" name="outlet_id" value="{{ $koperasiOutlet ? $koperasiOutlet->id : '' }}">

                <div class="modal-body px-8 py-6">
                    <div class="row g-4">
                        <!-- Outlet Penerima -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Outlet Penerima Dana <span class="text-danger">*</span></label>
                            <select name="recipient_outlet_id" id="koperasi_handover_recipient_outlet_id" class="form-select" style="border-radius: 12px;" required>
                                <option value="">Pilih Outlet Penerima</option>
                                @foreach($childOutlets as $child)
                                    <option value="{{ $child->id }}" {{ ($defaultRecipientOutletId == $child->id) ? 'selected' : '' }}>
                                        {{ $child->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1" id="koperasi_suggestion_text">
                                @if($defaultPendingSantriLog > 0)
                                    Sisa dana pending log saat ini: <strong class="text-success">Rp {{ number_format($defaultPendingSantriLog, 0, ',', '.') }}</strong>
                                @else
                                    Pilih outlet penerima untuk melihat sisa dana pending.
                                @endif
                            </small>
                        </div>

                        <!-- Penerima (Gus Maulana Rifqi default + Kasir Outlet) -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Nama Pengelola / Penerima <span class="text-danger">*</span></label>
                            <select name="recipient_id" class="form-select" style="border-radius: 12px;" required>
                                @foreach($pengelolaPenerimaList as $adm)
                                    <option value="{{ $adm->id }}" {{ ($defaultPenerimaId == $adm->id) ? 'selected' : '' }}>
                                        {{ $adm->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Handover -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Tanggal Transfer Handover <span class="text-danger">*</span></label>
                            <input type="date" name="handover_date" class="form-control" value="{{ date('Y-m-d') }}" style="border-radius: 12px;" required>
                        </div>

                        <!-- Nominal Handover / Serah Terima -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Nominal Serah Terima (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">Rp</span>
                                <input type="text" name="amount" id="koperasi_handover_amount_display" class="form-control" value="{{ number_format($defaultPendingSantriLog, 0, ',', '.') }}" placeholder="0" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;" required>
                            </div>
                            <input type="hidden" name="system_amount" id="koperasi_handover_system_amount" value="{{ $defaultPendingSantriLog }}">
                            <small class="text-muted fs-8 mt-1 d-block">Default otomatis terisi dari log sistem pending, dapat disesuaikan manual.</small>
                        </div>

                        <!-- Bukti Transfer -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-gray-700 fs-7">Upload Bukti Transfer Bank (M-Banking / Struk) <span class="text-danger">*</span></label>
                            <input type="file" name="evidence" class="form-control" accept="image/*,application/pdf" style="border-radius: 12px;" required>
                            <span class="text-muted fs-8 d-block mt-1">Bukti fisik mutlak diperlukan untuk audit real movement kas.</span>
                        </div>

                        <!-- Catatan -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-gray-700 fs-7">Catatan / Referensi</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pencairan saldo belanja santri periode 1-7 Okt 2026..." style="border-radius: 12px;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-8 pb-8 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius: 12px;">
                        <i class="bi bi-check-circle-fill"></i> Simpan Handover Saldo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 2: HANDOVER SETOR KASIR TUNAI (SHIFT CLOSING)
     ============================================================ -->
<div class="modal fade" id="modal-add-handover-cashier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: none;">
            <div class="modal-header border-0 pb-0 px-8 pt-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold" style="font-family: 'Outfit', sans-serif;">Setor Kasir Tunai (Shift Closing)</h4>
                        <span class="text-muted fs-7">Penyerahan fisik uang tunai dari kasir umum (Yogo, Khurotun, dsb) ke Pengelola/Bendahara.</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('outlet-handover.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="handover_type" value="{{ App\Models\OutletHandover::TYPE_CASHIER_TO_MANAGEMENT }}">

                <div class="modal-body px-8 py-6">
                    <div class="row g-4">
                        <!-- Outlet Kasir -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Outlet Tempat Kasir Bertugas <span class="text-danger">*</span></label>
                            <select name="outlet_id" id="cashier_handover_outlet_id" class="form-select" style="border-radius: 12px;" required>
                                <option value="">Pilih Outlet</option>
                                @foreach($allOutlets as $otl)
                                    <option value="{{ $otl->id }}" {{ (request('outlet_id') == $otl->id || (count($allOutlets) == 1)) ? 'selected' : '' }}>
                                        {{ $otl->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kasir yang Menyerahkan (Khusus Role Kasir) -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Kasir yang Menyerahkan Kas <span class="text-danger">*</span></label>
                            <select name="cashier_id" id="cashier_handover_cashier_id" class="form-select" style="border-radius: 12px;" required>
                                <option value="">Pilih Kasir</option>
                                @foreach($kasirList as $kasir)
                                    <option value="{{ $kasir->id }}" {{ ($defaultCashierId == $kasir->id) ? 'selected' : '' }}>
                                        {{ $kasir->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1" id="cashier_suggestion_text">
                                @if($defaultPendingCashierLog > 0)
                                    Sisa kas fisik belum disetor kasir: <strong class="text-warning">Rp {{ number_format($defaultPendingCashierLog, 0, ',', '.') }}</strong>
                                @else
                                    Pilih kasir untuk mengecek sisa kas yang belum disetor.
                                @endif
                            </small>
                        </div>

                        <!-- Penerima (Gus Maulana Rifqi default + Kasir Outlet) -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Bendahara / Pengelola Penerima <span class="text-danger">*</span></label>
                            <select name="recipient_id" class="form-select" style="border-radius: 12px;" required>
                                @foreach($pengelolaPenerimaList as $adm)
                                    <option value="{{ $adm->id }}" {{ ($defaultPenerimaId == $adm->id) ? 'selected' : '' }}>
                                        {{ $adm->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Serah Terima -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Tanggal Serah Terima Kas <span class="text-danger">*</span></label>
                            <input type="date" name="handover_date" class="form-control" value="{{ date('Y-m-d') }}" style="border-radius: 12px;" required>
                        </div>

                        <!-- Nominal Log Sistem -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Total Log Penjualan Kasir (Rp)</label>
                            <input type="text" id="cashier_system_amount_display" class="form-control bg-light" readonly value="Rp {{ number_format($defaultCashierSales, 0, ',', '.') }}" style="border-radius: 12px;">
                            <input type="hidden" name="system_amount" id="cashier_system_amount_real" value="{{ $defaultCashierSales }}">
                        </div>

                        <!-- Nominal Serah Terima -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-700 fs-7">Nominal Serah Terima (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">Rp</span>
                                <input type="text" name="amount" id="cashier_handover_amount_display" class="form-control" value="{{ number_format($defaultPendingCashierLog, 0, ',', '.') }}" placeholder="0" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;" required>
                            </div>
                            <small class="text-muted fs-8 mt-1 d-block">Default otomatis terisi dari log kasir, dapat disesuaikan jika ada selisih uang fisik.</small>
                        </div>

                        <!-- Bukti Serah Terima -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-gray-700 fs-7">Upload Bukti Serah Terima / Foto Fisik Uang & Tanda Terima <span class="text-danger">*</span></label>
                            <input type="file" name="evidence" class="form-control" accept="image/*,application/pdf" style="border-radius: 12px;" required>
                            <span class="text-muted fs-8 d-block mt-1">Lampirkan foto fisik serah terima uang cash atau form tanda terima shift.</span>
                        </div>

                        <!-- Catatan -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-gray-700 fs-7">Catatan / Shift</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Tutup kasir shift pagi, uang fisik pas sesuai log..." style="border-radius: 12px;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-8 pb-8 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold d-flex align-items-center gap-2" style="border-radius: 12px;">
                        <i class="bi bi-check-circle-fill text-dark"></i> Simpan Setoran Kasir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Zoom Bukti Pembayaran / Transfer -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true" style="border-radius: 24px;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content premium-shadow" style="border-radius: 24px; border: none;">
            <div class="modal-header border-0 pb-0 px-6 pt-6">
                <h5 class="modal-title fw-bold" style="font-family: 'Outfit', sans-serif;">Detail Bukti Serah Terima / Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-6">
                <img id="zoom_image_target" src="" alt="Bukti" class="img-fluid rounded-[16px] shadow-sm" style="max-height: 70vh; object-fit: contain;" />
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var start = moment('{{ $startDate }}');
            var end = moment('{{ $endDate }}');

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
            });

            // Set initial date range text
            $('#dateRange span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));

            // Initialize Select2 if available
            if ($('#filter_outlet_id').length) {
                $('#filter_outlet_id').select2({
                    placeholder: 'Semua Outlet',
                    allowClear: true
                });
            }

            // Input Rupiah Formatter Helper
            function formatRupiah(val) {
                var number_string = val.replace(/[^,\d]/g, '').toString(),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            }

            $('#form_amount_display, #koperasi_handover_amount_display, #cashier_handover_amount_display').on('keyup', function() {
                $(this).val(formatRupiah($(this).val()));
            });

            // AJAX: Hitung Pending Saldo Koperasi ➔ Outlet
            $('#koperasi_handover_recipient_outlet_id').on('change', function() {
                var recipientId = $(this).val();
                var startDate = $('#start_date').val() || '';
                var endDate = $('#end_date').val() || '';

                if (recipientId) {
                    $('#koperasi_suggestion_text').html('<i class="fas fa-spinner fa-spin me-1"></i> Menghitung sisa dana saldo santri...');
                    var ajaxUrl = "{{ route('outlet-handover.pending-amount', ':id') }}".replace(':id', recipientId) 
                        + "?type=KOPERASI" 
                        + (startDate ? "&start_date=" + encodeURIComponent(startDate) : "") 
                        + (endDate ? "&end_date=" + encodeURIComponent(endDate) : "");

                    $.ajax({
                        url: ajaxUrl,
                        type: "GET",
                        success: function(res) {
                            if (res.status === 'success') {
                                $('#koperasi_handover_amount_display').val(formatRupiah(res.pending_amount.toString()));
                                $('#koperasi_handover_system_amount').val(res.system_amount);
                                $('#koperasi_suggestion_text').html('Sisa dana pending log saat ini: <strong class="text-success">Rp ' + res.pending_amount_formatted + '</strong>');
                            }
                        },
                        error: function() {
                            $('#koperasi_suggestion_text').text('Gagal memuat sisa dana.');
                        }
                    });
                } else {
                    $('#koperasi_suggestion_text').text('Pilih outlet penerima untuk melihat sisa dana pending.');
                    $('#koperasi_handover_amount_display').val('0');
                }
            });

            // AJAX: Hitung Pending Kasir Tunai
            $('#cashier_handover_cashier_id').on('change', function() {
                var cashierId = $(this).val();
                var outletId = $('#cashier_handover_outlet_id').val() || '{{ $koperasiOutlet ? $koperasiOutlet->id : "" }}';
                var startDate = $('#start_date').val() || '';
                var endDate = $('#end_date').val() || '';

                if (cashierId) {
                    $('#cashier_suggestion_text').html('<i class="fas fa-spinner fa-spin me-1"></i> Menghitung total penjualan tunai kasir...');
                    var ajaxUrl = "{{ route('outlet-handover.pending-amount', ':id') }}".replace(':id', outletId) 
                        + "?type=CASHIER&cashier_id=" + cashierId 
                        + (startDate ? "&start_date=" + encodeURIComponent(startDate) : "") 
                        + (endDate ? "&end_date=" + encodeURIComponent(endDate) : "");

                    $.ajax({
                        url: ajaxUrl,
                        type: "GET",
                        success: function(res) {
                            if (res.status === 'success') {
                                $('#cashier_system_amount_display').val('Rp ' + formatRupiah(res.system_amount.toString()));
                                $('#cashier_system_amount_real').val(res.system_amount);
                                $('#cashier_handover_amount_display').val(formatRupiah(res.pending_amount.toString()));
                                $('#cashier_suggestion_text').html('Sisa kas fisik belum disetor kasir: <strong class="text-warning">Rp ' + res.pending_amount_formatted + '</strong> (Log: Rp ' + formatRupiah(res.system_amount.toString()) + ')');
                            }
                        },
                        error: function() {
                            $('#cashier_suggestion_text').text('Gagal memuat data kasir.');
                        }
                    });
                } else {
                    $('#cashier_suggestion_text').text('Pilih kasir untuk mengecek sisa kas yang belum disetor.');
                    $('#cashier_system_amount_display').val('Rp 0');
                    $('#cashier_system_amount_real').val('0');
                    $('#cashier_handover_amount_display').val('0');
                }
            });

            // Modal Shown Triggers for instant auto-fill & refresh
            $('#modal-add-handover-koperasi').on('shown.bs.modal', function() {
                var recipientId = $('#koperasi_handover_recipient_outlet_id').val();
                var currentVal = $('#koperasi_handover_amount_display').val();
                if (recipientId && (!currentVal || currentVal === '0')) {
                    $('#koperasi_handover_recipient_outlet_id').trigger('change');
                }
            });

            $('#modal-add-handover-cashier').on('shown.bs.modal', function() {
                var cashierId = $('#cashier_handover_cashier_id').val();
                var currentVal = $('#cashier_handover_amount_display').val();
                if (cashierId && (!currentVal || currentVal === '0')) {
                    $('#cashier_handover_cashier_id').trigger('change');
                }
            });

            // Custom Category Field Show/Hide Logic
            function toggleCustomCategoryField() {
                var selectedText = $('#cash_flow_category_id option:selected').text().trim();
                if (selectedText === 'Lainnya') {
                    $('#custom_category_wrapper').slideDown();
                    $('#custom_category').attr('required', true);
                } else {
                    $('#custom_category_wrapper').slideUp();
                    $('#custom_category').removeAttr('required').val('');
                }
            }

            $('#cash_flow_category_id').on('change', function() {
                toggleCustomCategoryField();
            });
            toggleCustomCategoryField();

            // Modal Zoom Trigger for image thumbnails
            $(document).on('click', '.img-proof-zoom', function() {
                var imgSrc = $(this).data('src');
                $('#zoom_image_target').attr('src', imgSrc);
                var zoomModal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
                zoomModal.show();
            });

            // Auto-switch tabs if session flash indicates specific action
            @if(session('success') && (str_contains(session('success'), 'pengeluaran') || str_contains(session('success'), 'Pengeluaran')))
                var tabTriggerEl = document.querySelector('a[href="#tab_transaksi"]');
                if (tabTriggerEl) {
                    var tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            @elseif(session('success') && (str_contains(session('success'), 'Handover') || str_contains(session('success'), 'serah terima')))
                var tabTriggerEl = document.querySelector('a[href="#tab_handover"]');
                if (tabTriggerEl) {
                    var tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            @endif
        });
    </script>
@endpush
