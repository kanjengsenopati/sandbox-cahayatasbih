@extends('layouts.master', ['title' => 'Laporan Arus Kas Mart'])

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
    .summary-card-smooth {
        border-radius: 24px !important;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent !important;
    }
    .summary-card-smooth:hover {
        transform: translateY(-3px);
    }
    .summary-card-total {
        background: linear-gradient(135deg, #eff6ff 0%, #f8faff 50%, #ffffff 100%) !important;
        border-color: rgba(37, 99, 235, 0.18) !important;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.07), 0 8px 10px -6px rgba(37, 99, 235, 0.04) !important;
    }
    .summary-card-outlet {
        background: linear-gradient(135deg, #fffbeb 0%, #fefdf7 50%, #ffffff 100%) !important;
        border-color: rgba(245, 158, 11, 0.2) !important;
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.07), 0 8px 10px -6px rgba(245, 158, 11, 0.04) !important;
    }
    .summary-card-koperasi {
        background: linear-gradient(135deg, #ecfdf5 0%, #f7fdfa 50%, #ffffff 100%) !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.07), 0 8px 10px -6px rgba(16, 185, 129, 0.04) !important;
    }
    .summary-card-count {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%) !important;
        border-color: rgba(100, 116, 139, 0.15) !important;
        box-shadow: 0 10px 25px -5px rgba(100, 116, 139, 0.07) !important;
    }
    .summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .summary-watermark {
        position: absolute;
        right: -8px;
        bottom: -12px;
        font-size: 56px;
        opacity: 0.04;
        pointer-events: none;
        line-height: 1;
    }
    .pos-item-stack {
        line-height: 1.25;
    }
    .btn-source-filter.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }
</style>

<div class="content d-flex flex-column flex-column-fluid safe-padding" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar py-5" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <x-text.h1 class="my-1">Arus Kas Mart</x-text.h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Laporan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Arus Kas Mart (Belanja Siswa via Saldo)</li>
                </ul>
            </div>

            <!-- Top-Right Action Cluster -->
            <div class="d-flex items-center gap-2">
                <button type="button" class="btn btn-sm btn-light-primary fw-bold" id="btn-refresh" title="Segarkan Data">
                    <i class="fa-solid fa-arrows-rotate me-1 fs-7"></i> Refresh
                </button>
                <button type="button" class="btn btn-sm btn-success fw-bold" id="btn-export-excel" title="Download Excel">
                    <i class="fa-solid fa-file-excel me-1 fs-7"></i> Export Excel
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-fluid px-0">

            @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-6 rounded-3">
                <i class="fa-solid fa-triangle-exclamation fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <h5 class="mb-1 text-danger">Peringatan</h5>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            <!-- Scope Notice for Restricted Users -->
            @if($isRestricted)
            <div class="alert alert-light-warning d-flex align-items-center p-4 mb-6 rounded-3" style="border: 1px solid rgba(245, 158, 11, 0.3);">
                <i class="fa-solid fa-shield-halved fs-3 text-warning me-3"></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-gray-800 fs-7">Scope Hak Akses Terkunci (Outlet Assigned):</span>
                    <span class="text-muted fs-8">
                        Anda terhubung dengan hak akses outlet: 
                        @foreach($outlets as $o)
                            <strong class="text-warning">{{ $o->name }}</strong>{{ !$loop->last ? ', ' : '' }}
                        @endforeach.
                        Data transaksi yang ditampilkan dikhususkan untuk unit outlet penugasan Anda guna rekonsiliasi penagihan saldo ke Bank Saldo (Pondok Mart).
                    </span>
                </div>
            </div>
            @endif

            <!-- GRID SUMMARY CARDS -->
            <div class="row g-4 mb-5">
                <!-- 1. Total Belanja Saldo -->
                <div class="col-md-3">
                    <div class="card premium-card summary-card-smooth summary-card-total px-5 py-3 h-100">
                        <i class="fa-solid fa-wallet summary-watermark text-primary"></i>
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <div class="d-flex flex-column">
                                <x-text.label class="text-slate-400 lh-sm mb-0.5">Total Belanja Saldo</x-text.label>
                                <x-text.amount id="stat-total-amount" class="fs-2 text-primary lh-1 mt-0.5">Rp 0</x-text.amount>
                            </div>
                            <div class="summary-icon bg-primary text-white shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-money-bill-wave fs-6 text-white"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                            <x-text.caption class="text-slate-500" id="stat-total-count">0 Transaksi</x-text.caption>
                            <span class="badge bg-light-primary text-primary px-2 py-0.5 fs-9 fw-bold rounded-pill">Total Terfilter</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Tagihan Outlet ke Bank Saldo -->
                <div class="col-md-3">
                    <div class="card premium-card summary-card-smooth summary-card-outlet px-5 py-3 h-100">
                        <i class="fa-solid fa-shop summary-watermark text-warning"></i>
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <div class="d-flex flex-column">
                                <x-text.label class="text-slate-400 lh-sm mb-0.5">Tagihan Saldo Outlet</x-text.label>
                                <x-text.amount id="stat-outlet-amount" class="fs-2 lh-1 mt-0.5" style="color: #d97706 !important;">Rp 0</x-text.amount>
                            </div>
                            <div class="summary-icon bg-warning text-white shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;">
                                <i class="fa-solid fa-file-invoice-dollar fs-6 text-white"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                            <x-text.caption class="text-slate-500" id="stat-outlet-count">0 Transaksi</x-text.caption>
                            <span class="badge bg-light-warning text-warning px-2 py-0.5 fs-9 fw-bold rounded-pill">Ditagihkan ke Bank</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Belanja di Pondok Mart / Koperasi -->
                <div class="col-md-3">
                    <div class="card premium-card summary-card-smooth summary-card-koperasi px-5 py-3 h-100">
                        <i class="fa-solid fa-store summary-watermark text-success"></i>
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <div class="d-flex flex-column">
                                <x-text.label class="text-slate-400 lh-sm mb-0.5">Pondok Mart / Koperasi</x-text.label>
                                <x-text.amount id="stat-koperasi-amount" class="fs-2 lh-1 mt-0.5" style="color: #10b981 !important;">Rp 0</x-text.amount>
                            </div>
                            <div class="summary-icon bg-success text-white shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                                <i class="fa-solid fa-building-columns fs-6 text-white"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                            <x-text.caption class="text-slate-500" id="stat-koperasi-count">0 Transaksi</x-text.caption>
                            <span class="badge bg-light-success text-success px-2 py-0.5 fs-9 fw-bold rounded-pill">Bank Saldo Utama</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Rata-rata Belanja -->
                <div class="col-md-3">
                    <div class="card premium-card summary-card-smooth summary-card-count px-5 py-3 h-100">
                        <i class="fa-solid fa-receipt summary-watermark text-slate-400"></i>
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <div class="d-flex flex-column">
                                <x-text.label class="text-slate-400 lh-sm mb-0.5">Rata-rata Belanja</x-text.label>
                                <x-text.amount id="stat-avg-amount" class="fs-2 text-dark lh-1 mt-0.5">Rp 0</x-text.amount>
                            </div>
                            <div class="summary-icon bg-secondary text-gray-700 shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-calculator fs-6 text-gray-700"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1 pt-1">
                            <x-text.caption class="text-slate-500">Nominal per Siswa</x-text.caption>
                            <span class="badge bg-light-secondary text-gray-700 px-2 py-0.5 fs-9 fw-bold rounded-pill">Per Struk</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER CARD -->
            <div class="card premium-card bg-white px-5 py-4 mb-5">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                    
                    <!-- Filter Sumber Transaksi (Pondok Mart / Koperasi vs Outlet) -->
                    <div class="d-flex flex-column gap-2">
                        <x-text.label class="text-slate-400">Sumber Transaksi</x-text.label>
                        @if(!$isRestricted)
                        <div class="btn-group" role="group" id="source-filter-group">
                            <button type="button" class="btn btn-sm btn-outline btn-outline-primary btn-source-filter active" data-source="all">
                                <i class="fa-solid fa-layer-group me-1 fs-8"></i> Semua Unit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline btn-outline-primary btn-source-filter" data-source="koperasi">
                                <i class="fa-solid fa-store me-1 fs-8"></i> Pondok Mart / Koperasi
                            </button>
                            <button type="button" class="btn btn-sm btn-outline btn-outline-primary btn-source-filter" data-source="outlet">
                                <i class="fa-solid fa-shop me-1 fs-8"></i> Unit Outlet
                            </button>
                        </div>
                        @else
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light-warning text-warning fw-bold px-3 py-2 fs-7" style="border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px;">
                                <i class="fa-solid fa-lock me-1 fs-8"></i> Scope Khusus Outlet
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Filter Rentang Tanggal -->
                    <div class="d-flex flex-column gap-2 flex-grow-1" style="min-width: 240px; max-width: 340px;">
                        <x-text.label class="text-slate-400">Rentang Tanggal</x-text.label>
                        <div class="position-relative cursor-pointer" id="filter-daterange-wrapper">
                            <input class="form-control form-control-sm form-control-solid fw-bold ps-9 cursor-pointer" placeholder="Pilih Periode Tanggal" value="Semua Tanggal" id="filter-daterange" readonly style="cursor: pointer !important;" />
                            <i class="fa-solid fa-calendar-days position-absolute top-50 translate-middle-y ms-3 text-muted fs-7" style="pointer-events: none;"></i>
                        </div>
                    </div>

                    <!-- Quick Period Buttons -->
                    <div class="d-flex flex-column gap-2">
                        <x-text.label class="text-slate-400">Preset Periode</x-text.label>
                        <div class="btn-group" role="group" id="preset-period-group">
                            <button type="button" class="btn btn-sm btn-light btn-period" data-period="today">Hari Ini</button>
                            <button type="button" class="btn btn-sm btn-light btn-period" data-period="week">Minggu Ini</button>
                            <button type="button" class="btn btn-sm btn-light btn-period" data-period="month">Bulan Ini</button>
                            <button type="button" class="btn btn-sm btn-primary text-white btn-period active" data-period="all">Semua</button>
                        </div>
                    </div>

                    <!-- Tombol Reset -->
                    <div class="d-flex flex-column justify-content-end gap-2">
                        <x-text.label class="text-transparent d-none d-lg-block">-</x-text.label>
                        <button type="button" class="btn btn-sm btn-light-danger fw-bold" id="btn-reset-filter" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left me-1 fs-8"></i> Reset
                        </button>
                    </div>

                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card premium-card bg-white p-6">
                <div class="card-header border-0 px-0 pt-0 pb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <x-text.h2>Riwayat Transaksi Belanja Siswa (Saldo)</x-text.h2>
                        <x-text.caption class="text-slate-400">Menampilkan rekap seluruh transaksi belanja siswa menggunakan metode pembayaran saldo pada unit yang Anda kelola</x-text.caption>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-7 gy-3 gs-4" id="table-mart-cashflow">
                        <thead>
                            <tr class="text-start text-muted fw-bolder fs-8 text-uppercase gs-0" style="background-color: #f8fafc; border-radius: 12px;">
                                <th class="w-40px ps-4">No</th>
                                <th class="min-w-120px">No. Transaksi</th>
                                <th class="min-w-140px">Tanggal & Waktu</th>
                                <th class="min-w-180px">Identitas Siswa</th>
                                <th class="min-w-160px">Unit / Outlet</th>
                                <th class="min-w-200px">Rincian Belanja</th>
                                <th class="min-w-130px">Nominal Belanja</th>
                                <th class="min-w-90px text-center">Status</th>
                                <th class="min-w-80px text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                            <!-- Populated via AJAX DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        let currentSource = '{{ $defaultSource }}';
        let currentPeriod = 'all';
        let startDate = '';
        let endDate = '';
        const isRestricted = {{ $isRestricted ? 'true' : 'false' }};

        function setActivePeriodBtn(period) {
            $('.btn-period').removeClass('active btn-primary text-white').addClass('btn-light');
            if (period) {
                $('.btn-period[data-period="' + period + '"]').removeClass('btn-light').addClass('active btn-primary text-white');
            }
        }

        function applyCustomDateRange(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');

            if (start.isSame(end, 'day')) {
                $('#filter-daterange').val(start.format('DD MMM YYYY'));
            } else {
                $('#filter-daterange').val(startDate + ' s/d ' + endDate);
            }

            // Sinkronisasi highlight tombol Preset Periode jika rentang tanggal cocok
            if (start.isSame(moment(), 'day') && end.isSame(moment(), 'day')) {
                currentPeriod = 'today';
                setActivePeriodBtn('today');
            } else if (start.isSame(moment().startOf('isoWeek'), 'day') && end.isSame(moment().endOf('isoWeek'), 'day')) {
                currentPeriod = 'week';
                setActivePeriodBtn('week');
            } else if (start.isSame(moment().startOf('month'), 'day') && end.isSame(moment().endOf('month'), 'day')) {
                currentPeriod = 'month';
                setActivePeriodBtn('month');
            } else {
                currentPeriod = 'custom';
                setActivePeriodBtn(null);
            }

            loadSummary();
            table.ajax.reload();
        }

        // Inisialisasi Date Range Picker
        $('#filter-daterange').daterangepicker({
            autoUpdateInput: false,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            showDropdowns: true,
            alwaysShowCalendars: true,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                'Minggu Ini': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
            },
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' s/d ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Semua Tanggal',
                customRangeLabel: 'Pilih Rentang Bebas',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                firstDay: 1
            }
        }, function(start, end) {
            applyCustomDateRange(start, end);
        });

        $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
            applyCustomDateRange(picker.startDate, picker.endDate);
        });

        $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
            $('#filter-daterange').val('Semua Tanggal');
            startDate = '';
            endDate = '';
            currentPeriod = 'all';
            setActivePeriodBtn('all');
            loadSummary();
            table.ajax.reload();
        });

        // Filter Sumber Transaksi (Pondok Mart / Koperasi vs Outlet)
        $('.btn-source-filter').on('click', function() {
            $('.btn-source-filter').removeClass('active');
            $(this).addClass('active');
            currentSource = $(this).data('source');

            loadSummary();
            table.ajax.reload();
        });

        // Quick Period Buttons
        $('.btn-period').on('click', function() {
            const period = $(this).data('period');
            currentPeriod = period;
            setActivePeriodBtn(period);

            const picker = $('#filter-daterange').data('daterangepicker');

            if (period === 'today') {
                const today = moment();
                startDate = today.format('YYYY-MM-DD');
                endDate = today.format('YYYY-MM-DD');
                $('#filter-daterange').val(today.format('DD MMM YYYY'));
                if (picker) {
                    picker.setStartDate(today);
                    picker.setEndDate(today);
                }
            } else if (period === 'week') {
                const startWeek = moment().startOf('isoWeek');
                const endWeek = moment().endOf('isoWeek');
                startDate = startWeek.format('YYYY-MM-DD');
                endDate = endWeek.format('YYYY-MM-DD');
                $('#filter-daterange').val(startDate + ' s/d ' + endDate);
                if (picker) {
                    picker.setStartDate(startWeek);
                    picker.setEndDate(endWeek);
                }
            } else if (period === 'month') {
                const startMonth = moment().startOf('month');
                const endMonth = moment().endOf('month');
                startDate = startMonth.format('YYYY-MM-DD');
                endDate = endMonth.format('YYYY-MM-DD');
                $('#filter-daterange').val(startDate + ' s/d ' + endDate);
                if (picker) {
                    picker.setStartDate(startMonth);
                    picker.setEndDate(endMonth);
                }
            } else {
                startDate = '';
                endDate = '';
                $('#filter-daterange').val('Semua Tanggal');
            }

            loadSummary();
            table.ajax.reload();
        });

        // Reset Filter Button
        $('#btn-reset-filter').on('click', function() {
            currentSource = isRestricted ? 'outlet' : 'all';
            currentPeriod = 'all';
            startDate = '';
            endDate = '';

            $('#filter-daterange').val('Semua Tanggal');

            $('.btn-source-filter').removeClass('active');
            $('.btn-source-filter[data-source="' + currentSource + '"]').addClass('active');

            setActivePeriodBtn('all');

            loadSummary();
            table.ajax.reload();
        });

        // Refresh Button
        $('#btn-refresh').on('click', function() {
            loadSummary();
            table.ajax.reload();
        });

        // DataTables Inisialisasi
        const table = $('#table-mart-cashflow').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report-mart-cashflow.index') }}",
                data: function(d) {
                    d.source = currentSource;
                    d.period = currentPeriod;
                    d.start_date = startDate;
                    d.end_date = endDate;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4' },
                { data: 'payment_code', name: 'payment_code' },
                { data: 'date', name: 'created_at' },
                { data: 'student', name: 'student.name' },
                { data: 'outlet', name: 'outlet.name' },
                { data: 'details', name: 'details', orderable: false, searchable: false },
                { data: 'pay_amount', name: 'pay_amount' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-4' }
            ],
            order: [[2, 'desc']],
            pageLength: 25,
            language: {
                search: "Cari Siswa/No. Trx:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ transaksi",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Tidak ada transaksi belanja siswa ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });

        // Fungsi Load Summary Cards
        function loadSummary() {
            $.ajax({
                url: "{{ route('report-mart-cashflow.index') }}",
                type: "GET",
                data: {
                    data: 'summary',
                    source: currentSource,
                    period: currentPeriod,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(res) {
                    $('#stat-total-amount').text(res.total_amount || 'Rp 0');
                    $('#stat-total-count').text(res.total_count || '0 Transaksi');
                    $('#stat-outlet-amount').text(res.outlet_amount || 'Rp 0');
                    $('#stat-outlet-count').text(res.outlet_count || '0 Transaksi');
                    $('#stat-koperasi-amount').text(res.koperasi_amount || 'Rp 0');
                    $('#stat-koperasi-count').text(res.koperasi_count || '0 Transaksi');
                    $('#stat-avg-amount').text(res.avg_amount || 'Rp 0');
                },
                error: function() {
                    console.error('Gagal memuat ringkasan arus kas mart');
                }
            });
        }

        // Export Excel Action
        $('#btn-export-excel').on('click', function() {
            const params = new URLSearchParams({
                source: currentSource,
                period: currentPeriod,
                start_date: startDate,
                end_date: endDate
            });
            window.location.href = "{{ route('report-mart-cashflow.export') }}?" + params.toString();
        });

        // Initial Load
        loadSummary();
    });
</script>
@endpush
