@extends('layouts.master', ['title' => 'Laporan Rugi Laba Outlet'])

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
            #tab_transaksi {
                display: none !important; /* Do not print entry form */
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
                <x-text.h1>Laporan Rugi Laba Outlet</x-text.h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Rugi Laba Outlet</li>
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
                    <a class="nav-link active fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_report" role="tab" style="font-family: 'Outfit', sans-serif;">Laporan Rugi Laba</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3" data-bs-toggle="tab" href="#tab_transaksi" role="tab" style="font-family: 'Outfit', sans-serif;">Catat Pengeluaran</a>
                </li>
            </ul>
            <!--end::Tabs Navigation-->

            <!--begin::Tab Content-->
            <div class="tab-content">
                <!-- Tab Laporan Rugi Laba -->
                <div class="tab-pane fade show active" id="tab_report" role="tabpanel">
                    <!--begin::Summary Cards-->
                    <div class="row g-5 mb-6">
                        <!-- Pendapatan Card -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Total Pendapatan</x-text.label>
                                    <div class="bg-light-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount>Rp {{ number_format($totalRevenues, 0, ',', '.') }}</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">POS Sales + Incomes</x-text.caption>
                            </div>
                        </div>

                        <!-- HPP Card -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Harga Pokok Penjualan (HPP)</x-text.label>
                                    <div class="bg-light-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-box-seam text-warning fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount class="text-warning" style="color: #d97706 !important;">Rp {{ number_format($totalHpp, 0, ',', '.') }}</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Beban Pokok POS</x-text.caption>
                            </div>
                        </div>

                        <!-- Beban Operasional Card -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Beban Operasional</x-text.label>
                                    <div class="bg-light-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-wallet2 text-danger fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount class="text-danger">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Pengeluaran Operasional</x-text.caption>
                            </div>
                        </div>

                        <!-- Laba/Rugi Bersih Card -->
                        <div class="col-md-3">
                            <div class="premium-card p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <x-text.label>Laba / Rugi Bersih</x-text.label>
                                    <div class="{{ $netProfit >= 0 ? 'bg-light-success' : 'bg-light-danger' }} rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi {{ $netProfit >= 0 ? 'bi-shield-check text-success' : 'bi-shield-exclamation text-danger' }} fs-4"></i>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <x-text.amount class="{{ $netProfit >= 0 ? '' : 'text-danger' }}">
                                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                                    </x-text.amount>
                                </div>
                                <x-text.caption class="text-muted d-block">Hasil Bersih Setelah Pajak & HPP</x-text.caption>
                            </div>
                        </div>
                    </div>
                    <!--end::Summary Cards-->

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
                                        <td class="fw-bold"><x-text.label class="text-dark">1. Pendapatan</x-text.label></td>
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
                                        <td class="indent-1 text-slate-700">HPP Penjualan POS (Kasir)</td>
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

                <!-- Tab Transaksi (Entri Pengeluaran) -->
                <div class="tab-pane fade" id="tab_transaksi" role="tabpanel">
                    <div class="row g-6">
                        <!-- Form Input Pengeluaran (Left Side) -->
                        <div class="col-lg-4 col-md-5">
                            <div class="premium-card p-6" style="border-radius: 24px;">
                                <x-text.h2 class="mb-4">Catat Pengeluaran Baru</x-text.h2>
                                <hr class="text-slate-200 mb-4" />
                                
                                <form action="{{ route('report-profit-loss.store-expense', ['mode' => request('mode'), 'outlet_id' => request('outlet_id')]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <!-- Outlet Selector -->
                                    @if(auth()->user()->outlet_id)
                                        <input type="hidden" name="outlet_id" value="{{ auth()->user()->outlet_id }}" />
                                    @else
                                        <div class="mb-4">
                                            <label class="form-label fw-bold text-gray-700 fs-7 required">Pilih Outlet</label>
                                            <select name="outlet_id" id="form_outlet_id" class="form-select" required style="border-radius: 12px;">
                                                <option value="" disabled selected>Pilih Outlet</option>
                                                @foreach($outlets as $outlet)
                                                    <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                                        {{ $outlet->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <!-- Kategori Pengeluaran -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7 required">Kategori</label>
                                        <select name="cash_flow_category_id" id="cash_flow_category_id" class="form-select" required style="border-radius: 12px;">
                                            <option value="" disabled selected>Pilih Kategori</option>
                                            @foreach($expenseCategories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Free Text Custom Category (untuk Lainnya) -->
                                    <div class="mb-4" id="custom_category_wrapper" style="display: none;">
                                        <label class="form-label fw-bold text-gray-700 fs-7 required">Nama Kategori Pengeluaran</label>
                                        <input type="text" name="custom_category" id="custom_category" class="form-control" placeholder="Contoh: Pembelian Air Mineral" style="border-radius: 12px;" />
                                    </div>

                                    <!-- Nominal Pengeluaran -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7 required">Nominal (Rp)</label>
                                        <input type="text" name="amount" class="form-control input-money" required placeholder="Contoh: 100.000" style="border-radius: 12px;" />
                                    </div>

                                    <!-- Tanggal Transaksi -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7 required">Tanggal</label>
                                        <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}" style="border-radius: 12px;" />
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Keterangan / Deskripsi</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi mengenai pengeluaran..." style="border-radius: 12px;"></textarea>
                                    </div>

                                    <!-- Upload Bukti Bayar -->
                                    <div class="mb-6">
                                        <label class="form-label fw-bold text-gray-700 fs-7">Bukti Pembayaran (Optional)</label>
                                        <input type="file" name="proof_of_payment" class="form-control" accept="image/*,application/pdf" style="border-radius: 12px;" />
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
                                                                <img src="{{ asset($expense->proof_of_payment) }}" 
                                                                     data-src="{{ asset($expense->proof_of_payment) }}"
                                                                     alt="Bukti" 
                                                                     class="img-thumbnail img-proof-zoom" 
                                                                     style="cursor: pointer; width: 45px; height: 45px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;" />
                                                            @else
                                                                <span class="text-muted italic fs-7" style="font-weight: normal;">No file</span>
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

<!-- Modal Zoom Bukti Pembayaran -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true" style="border-radius: 24px;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content premium-shadow" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="font-family: 'Outfit', sans-serif;">Detail Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-8">
                <img id="zoom_image_target" src="" alt="Bukti Pembayaran" class="img-fluid rounded-[16px] shadow-sm" style="max-height: 70vh; object-fit: contain;" />
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
            if ($('#form_outlet_id').length) {
                $('#form_outlet_id').select2({
                    placeholder: 'Pilih Outlet',
                    allowClear: true
                });
            }
            if ($('#cash_flow_category_id').length) {
                $('#cash_flow_category_id').select2({
                    placeholder: 'Pilih Kategori',
                    allowClear: true
                });
            }

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

            // Event listener for category select box
            $('#cash_flow_category_id').on('change', function() {
                toggleCustomCategoryField();
            });

            // Initial toggle check on load
            toggleCustomCategoryField();

            // Modal Zoom Trigger for image thumbnails
            $('.img-proof-zoom').on('click', function() {
                var imgSrc = $(this).data('src');
                $('#zoom_image_target').attr('src', imgSrc);
                var zoomModal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
                zoomModal.show();
            });

            // Switch to Transaksi tab if redirecting back from store/delete expense
            @if(session('success') && (str_contains(session('success'), 'pengeluaran') || str_contains(session('success'), 'Pengeluaran')))
                var tabTriggerEl = document.querySelector('a[href="#tab_transaksi"]');
                if (tabTriggerEl) {
                    var tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            @endif
        });
    </script>
@endpush
