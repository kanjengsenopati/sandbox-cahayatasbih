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
            #kt_header, #kt_aside, #kt_footer, .toolbar, .filter-section, .btn-print-action {
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

            <!--begin::Summary Cards-->
            <div class="row g-5 mb-6">
                <!-- Pendapatan Card -->
                <div class="col">
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
                <div class="col">
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
                <div class="col">
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
                <div class="col">
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
                            @if(count($cashExpensesBreakdown) > 0)
                                @foreach($cashExpensesBreakdown as $expense)
                                    <tr>
                                        <td class="indent-1 text-slate-700">Beban Kas - {{ $expense->cashflow_category?->name ?? 'Kategori Lain' }}</td>
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
    </div>
    <!--end::Post-->
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
        });
    </script>
@endpush
