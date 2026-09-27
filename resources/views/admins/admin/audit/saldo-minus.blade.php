@extends('layouts.master', ['title' => 'Audit Saldo Minus'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Audit Saldo Minus Santri</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.audit.sync') }}" class="text-muted text-hover-primary">Audit dan Sinkron</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Daftar & Forensik Saldo Minus</li>
                </ul>
            </div>
            <!--end::Page title-->

            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.audit.saldo-minus', ['refresh' => 1]) }}"
                   class="btn btn-sm btn-light-primary fw-bolder">
                    <i class="fas fa-sync-alt me-1 fs-7"></i> Refresh Ringkasan
                </a>
                <a href="{{ route('admin.audit.saldo-minus.export') }}"
                   class="btn btn-sm btn-primary fw-bolder">
                    <i class="fas fa-file-excel me-1 fs-7"></i> Export CSV / Excel
                </a>
            </div>
            <!--end::Actions-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">

            <!--begin::Alert Card: Ringkasan Forensik-->
            <div class="card mb-6 border border-primary border-dashed bg-light-primary rounded-3">
                <div class="card-body p-5">
                    <div class="d-flex align-items-start">
                        <div class="symbol symbol-45px symbol-circle bg-primary text-white d-flex align-items-center justify-content-center me-4 mt-1">
                            <i class="fas fa-shield-alt text-white fs-2"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h4 class="text-gray-900 fw-bolder mb-0">Fakta Audit Forensik & Akar Masalah Saldo Minus</h4>
                                <span class="badge badge-light-success fw-bolder fs-8 px-3 py-1">
                                    <i class="fas fa-lock me-1 text-success"></i> Sistem Baru Sudah Diproteksi (Atomic Guard Aktif)
                                </span>
                            </div>
                            <p class="text-gray-700 fs-7 mb-2">
                                Saldo minus santri di database lama terjadi terutama akibat <strong>Autodebit Tagihan SPP / Bulanan Pesantren</strong> yang mengeksekusi pelunasan tanpa mengecek kecukupan saldo (saldo Rp 0 dipaksa potong hingga jutaan rupiah), serta belanja kasir kantin lama tanpa batasan kasbon. Di aplikasi HP orang tua, kartu saldo dinonaktifkan sehingga orang tua tidak melihat angka minus.
                            </p>
                            <div class="d-flex flex-wrap gap-3 fs-8 text-gray-600">
                                <span><i class="fas fa-database text-primary me-1"></i> Sumber Audit: <strong class="text-primary">{{ $connName }}</strong></span>
                                <span><i class="fas fa-users text-danger me-1"></i> Total Korban Minus: <strong class="text-danger">{{ number_format($summary['total_count']) }} Santri</strong></span>
                                <span><i class="fas fa-file-invoice text-warning me-1"></i> Korban Autodebit SPP: <strong class="text-warning">{{ number_format($summary['spp_victims_count']) }} Santri ({{ round(($summary['spp_victims_count'] / max(1, $summary['total_count'])) * 100) }}%)</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Alert Card-->

            <!--begin::KPI Stats Cards-->
            <div class="row g-4 mb-6">
                <!--Card 1: Total Santri Minus-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-danger">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Total Santri Bersaldo Minus</div>
                                    <div class="text-danger fs-2x fw-bolder font-mono">{{ number_format($summary['total_count']) }}</div>
                                    <div class="text-muted fs-8 mt-1">Santri tercatat saldo defisit</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-danger">
                                    <span class="symbol-label">
                                        <i class="fas fa-user-minus text-danger fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Card 2: Total Defisit Kumulatif-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Total Akumulasi Defisit</div>
                                    <div class="text-warning fs-2x fw-bolder font-mono">Rp {{ number_format($summary['total_deficit'], 0, ',', '.') }}</div>
                                    <div class="text-muted fs-8 mt-1">Total saldo di bawah nol</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-warning">
                                    <span class="symbol-label">
                                        <i class="fas fa-wallet text-warning fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Card 3: Korban Pemotongan SPP-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Penyedot SPP Bulanan</div>
                                    <div class="text-primary fs-2x fw-bolder font-mono">{{ number_format($summary['spp_victims_count']) }}</div>
                                    <div class="text-muted fs-8 mt-1">{{ round(($summary['spp_victims_count'] / max(1, $summary['total_count'])) * 100) }}% akibat autodebit SPP</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-primary">
                                    <span class="symbol-label">
                                        <i class="fas fa-file-invoice-dollar text-primary fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Card 4: Kasbon Kantin Murni-->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-start border-4 border-success">
                        <div class="card-body p-5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold fs-7 mb-1">Jajan Kantin Murni</div>
                                    <div class="text-success fs-2x fw-bolder font-mono">{{ number_format($summary['pos_only_count']) }}</div>
                                    <div class="text-muted fs-8 mt-1">Hanya belanja receh tanpa SPP</div>
                                </div>
                                <div class="symbol symbol-50px symbol-light-success">
                                    <span class="symbol-label">
                                        <i class="fas fa-shopping-cart text-success fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::KPI Stats Cards-->

            <!--begin::Card Table-->
            <div class="card shadow-sm border-0 mb-6">
                <!--begin::Card Header with Filters-->
                <div class="card-header border-0 pt-6 pb-4">
                    <div class="card-title w-100 d-flex flex-column">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light-danger fw-bolder me-2 px-3 py-1.5 fs-7">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i> {{ number_format($summary['total_count']) }} Santri
                                </span>
                                <h3 class="fw-bolder text-gray-800 m-0 fs-5">Tabel Data Lengkap Saldo Minus & Log Bukti</h3>
                            </div>
                        </div>

                        <!--begin::Filter Bar-->
                        <div class="d-flex flex-wrap gap-3 align-items-end p-4 bg-light rounded-3 border border-gray-200">
                            <!--Filter Lembaga-->
                            <div style="min-width: 170px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Lembaga</label>
                                <select id="filter_school" class="form-select form-select-sm filter-control">
                                    <option value="">Semua Lembaga</option>
                                    @foreach($schools as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!--Filter Kelas-->
                            <div style="min-width: 150px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Kelas</label>
                                <select id="filter_classroom" class="form-select form-select-sm filter-control">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classrooms as $cls)
                                        <option value="{{ $cls->id }}" data-school="{{ $cls->school_id }}">{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!--Filter Rentang Minus-->
                            <div style="min-width: 170px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Kategori Nominal Minus</label>
                                <select id="filter_minus_range" class="form-select form-select-sm filter-control">
                                    <option value="">Semua Kategori</option>
                                    <option value="ringan">Minus Ringan (&lt; Rp 50.000)</option>
                                    <option value="sedang">Minus Sedang (Rp 50rb - 200rb)</option>
                                    <option value="berat">Minus Berat (&gt; Rp 200.000)</option>
                                </select>
                            </div>

                            <!--Filter Search Box-->
                            <div class="flex-grow-1" style="min-width: 200px;">
                                <label class="form-label fs-8 fw-bolder text-gray-700 mb-1">Cari Nama / NIS</label>
                                <div class="position-relative d-flex align-items-center filter-search-box" style="cursor: text;">
                                    <span class="position-absolute ms-3 text-gray-400" style="pointer-events: none;">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           id="search_keyword" 
                                           class="form-control form-control-sm form-control-solid ps-9" 
                                           placeholder="Ketik Nama Santri / NIS..." />
                                </div>
                            </div>

                            <!--Action Buttons-->
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-apply-filter" class="btn btn-sm btn-primary d-flex align-items-center">
                                    <i class="fas fa-filter me-1 fs-7"></i> Tampilkan
                                </button>
                                <button type="button" id="btn-reset-filter" class="btn btn-sm btn-light d-flex align-items-center text-gray-700 border">
                                    <i class="fas fa-undo me-1 fs-7"></i> Reset
                                </button>
                            </div>
                        </div>
                        <!--end::Filter Bar-->
                    </div>
                </div>
                <!--end::Card Header-->

                <!--begin::Card Body-->
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-saldo-minus" class="table table-row-bordered table-row-dashed align-middle gy-4 gs-4 border rounded">
                            <thead class="bg-light fw-bolder fs-7 text-gray-700 text-uppercase gs-0">
                                <tr>
                                    <th class="w-40px text-center">No</th>
                                    <th>Nama Siswa & NIS</th>
                                    <th>Lembaga</th>
                                    <th>Kelas</th>
                                    <th>Riwayat Saldo</th>
                                    <th>Tanggal Transaksi</th>
                                    <th class="text-end">Minus Berapa</th>
                                    <th>Log Kenapa Bisa Minus</th>
                                    <th class="text-center w-120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600 fs-7">
                                <!-- DataTables will populate here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Card Body-->
            </div>
            <!--end::Card Table-->

        </div>
    </div>
    <!--end::Post-->
</div>

<!--begin::Modal Detail Log Kronologis-->
<div class="modal fade" id="modal-log-timeline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-6 px-6">
                <div>
                    <h3 class="modal-title fw-bolder text-gray-900 fs-4 mb-1">
                        <i class="fas fa-history text-primary me-2"></i> Log Kronologis Bukti Mutasi Saldo
                    </h3>
                    <div class="text-muted fs-7">
                        Santri: <strong class="text-dark" id="modal-student-name">-</strong> | 
                        NIS: <strong class="text-dark" id="modal-student-nis">-</strong> | 
                        Kelas: <strong class="text-dark" id="modal-student-class">-</strong>
                    </div>
                </div>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-4"></i>
                </div>
            </div>

            <div class="modal-body px-6 py-5">
                <!--Loading Spinner-->
                <div id="modal-loading" class="text-center py-10">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div class="text-muted fs-7 fw-bold">Memuat rekap jejak audit forensik...</div>
                </div>

                <!--Content Container-->
                <div id="modal-content" class="d-none">
                    <!--Trigger Box: Pertama Kali Anjlok ke Minus-->
                    <div id="box-first-negative" class="p-4 mb-4 rounded-3 border border-danger border-dashed bg-light-danger d-none">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-exclamation-circle text-danger fs-3 me-2"></i>
                            <h5 class="fw-bolder text-danger mb-0">Pemicu Utama Saldo Anjlok ke Minus:</h5>
                        </div>
                        <div class="ms-6 fs-7 text-gray-800" id="text-first-negative">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!--Summary Stats Bar-->
                    <div class="row g-3 mb-5 text-center">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted fs-8 fw-bold">Saldo Akhir Tercatat</div>
                                <div class="text-danger fw-bolder fs-6 font-mono" id="stat-final-saldo">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted fs-8 fw-bold">Total Potong Tagihan SPP</div>
                                <div class="text-danger fw-bolder fs-6 font-mono" id="stat-bill-deductions">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted fs-8 fw-bold">Total Belanja Jajan POS</div>
                                <div class="text-warning fw-bolder fs-6 font-mono" id="stat-pos-deductions">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted fs-8 fw-bold">Total Top Up Masuk</div>
                                <div class="text-success fw-bolder fs-6 font-mono" id="stat-topup">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <!--Timeline Table-->
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-row-bordered align-middle gs-3 gy-2 fs-8">
                            <thead class="bg-dark text-white sticky-top">
                                <tr>
                                    <th class="w-40px text-center">No</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Tipe</th>
                                    <th class="text-end">Nominal</th>
                                    <th>Keterangan / Transaksi</th>
                                    <th class="text-end">Saldo Sebelum</th>
                                    <th class="text-end">Saldo Sesudah</th>
                                </tr>
                            </thead>
                            <tbody id="modal-timeline-body">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-6 pb-5">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal Detail Log Kronologis-->

<style>
    .filter-control {
        cursor: pointer !important;
        height: 38px !important;
        min-height: 38px !important;
        border: 1px solid #e4e6ef !important;
        border-radius: 0.475rem !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    .filter-control:focus {
        border-color: #009ef7 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.15) !important;
    }
    #search_keyword {
        height: 38px !important;
        min-height: 38px !important;
        border: 1px solid #e4e6ef !important;
        border-radius: 0.475rem !important;
        background-color: #ffffff !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    #search_keyword:focus {
        border-color: #009ef7 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.15) !important;
        outline: none !important;
    }
    #btn-apply-filter, #btn-reset-filter {
        height: 38px !important;
        min-height: 38px !important;
    }
    .font-mono {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
    }
    .pulse-danger {
        animation: pulseDanger 1.8s infinite;
    }
    @keyframes pulseDanger {
        0% { box-shadow: 0 0 0 0 rgba(241, 65, 108, 0.6); }
        70% { box-shadow: 0 0 0 8px rgba(241, 65, 108, 0); }
        100% { box-shadow: 0 0 0 0 rgba(241, 65, 108, 0); }
    }
</style>
@endsection

@push('js')
<script>
    var saldoMinusTable;

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    $(document).ready(function() {
        // Initialize DataTables
        saldoMinusTable = $('#table-saldo-minus').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('admin.audit.saldo-minus.data') }}",
                data: function(d) {
                    d.school_id = $('#filter_school').val();
                    d.classroom_id = $('#filter_classroom').val();
                    d.minus_range = $('#filter_minus_range').val();
                    d.search_keyword = $('#search_keyword').val();
                }
            },
            columns: [
                { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center font-mono' },
                { data: 'student', name: 'student', orderable: true, searchable: true },
                { data: 'school', name: 'school', orderable: true, searchable: true },
                { data: 'classroom', name: 'classroom', orderable: true, searchable: true },
                { data: 'saldo_status', name: 'saldo_status', orderable: false, searchable: false },
                { data: 'last_trans_date', name: 'last_trans_date', orderable: true, searchable: false, className: 'font-mono fs-8' },
                { data: 'minus_amount', name: 'minus_amount', orderable: true, searchable: false },
                { data: 'root_cause', name: 'root_cause', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[6, 'asc']], // Default order by minus_amount asc (most negative first)
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                emptyTable: "Tidak ada santri dengan saldo minus",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ santri minus",
                infoEmpty: "Menampilkan 0 data",
                lengthMenu: "Tampilkan _MENU_ santri",
                loadingRecords: "Memuat...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: "Pencarian:",
                zeroRecords: "Data santri minus tidak ditemukan"
            }
        });

        // Filter trigger
        $('#btn-apply-filter').on('click', function(e) {
            e.preventDefault();
            saldoMinusTable.ajax.reload();
        });

        // Search on Enter key
        $('#search_keyword').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                saldoMinusTable.ajax.reload();
            }
        });

        // Search container click
        $('.filter-search-box').on('click', function() {
            $('#search_keyword').focus();
        });

        // Reset Filter
        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#filter_school').val('');
            $('#filter_classroom').val('');
            $('#filter_minus_range').val('');
            $('#search_keyword').val('');
            saldoMinusTable.ajax.reload();
        });

        // School change filter for classrooms
        $('#filter_school').on('change', function() {
            var schoolId = $(this).val();
            $('#filter_classroom option').each(function() {
                var clsSchool = $(this).data('school');
                if (!schoolId || !clsSchool || clsSchool == schoolId || $(this).val() === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#filter_classroom').val('');
            saldoMinusTable.ajax.reload();
        });

        // Modal View Logs
        $(document).on('click', '.btn-view-logs', function(e) {
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            var name = btn.data('name');
            var nis = btn.data('nis');
            var className = btn.data('class');

            $('#modal-student-name').text(name);
            $('#modal-student-nis').text(nis);
            $('#modal-student-class').text(className);

            $('#modal-loading').removeClass('d-none');
            $('#modal-content').addClass('d-none');
            $('#modal-log-timeline').modal('show');

            $.ajax({
                url: "{{ url('admin/audit/saldo-minus') }}/" + id + "/logs",
                type: "GET",
                success: function(res) {
                    $('#modal-loading').addClass('d-none');
                    $('#modal-content').removeClass('d-none');

                    var summary = res.summary;
                    $('#stat-final-saldo').text('- Rp ' + formatNumber(Math.abs(summary.final_saldo)));
                    $('#stat-bill-deductions').text('Rp ' + formatNumber(summary.total_bill_deductions));
                    $('#stat-pos-deductions').text('Rp ' + formatNumber(summary.total_pos_deductions));
                    $('#stat-topup').text('Rp ' + formatNumber(summary.total_in));

                    // First negative event
                    if (summary.first_negative_event) {
                        var fn = summary.first_negative_event;
                        $('#box-first-negative').removeClass('d-none');
                        $('#text-first-negative').html(
                            'Pada tanggal <strong>' + fn.date + '</strong>, saldo santri sebesar <strong class="text-success">Rp ' + formatNumber(fn.prev) + '</strong> ' +
                            'dipotong sebesar <strong class="text-danger">Rp ' + formatNumber(fn.amount) + '</strong> (' + fn.desc + '). ' +
                            'Karena sistem lama tidak memvalidasi kecukupan saldo, saldo santri seketika anjlok menjadi <strong class="text-danger">Rp ' + formatNumber(fn.after) + '</strong>.'
                        );
                    } else {
                        $('#box-first-negative').addClass('d-none');
                    }

                    // Populate timeline table
                    var tbody = $('#modal-timeline-body');
                    tbody.empty();

                    if (res.timeline && res.timeline.length > 0) {
                        $.each(res.timeline, function(i, item) {
                            var typeBadge = item.type === 'IN' 
                                ? '<span class="badge badge-light-success fw-bold">TOPUP (IN)</span>'
                                : '<span class="badge badge-light-danger fw-bold">POTONG (OUT)</span>';

                            var prevClass = item.prev_balance < 0 ? 'text-danger font-mono' : 'text-gray-700 font-mono';
                            var afterClass = item.balance_after < 0 ? 'text-danger fw-bold font-mono' : 'text-success fw-bold font-mono';

                            var rowClass = item.is_first_negative ? 'bg-light-danger border-danger border-start border-4' : '';
                            var indicator = item.is_first_negative ? '<span class="badge badge-danger ms-1 pulse-danger fs-9">Titik Awal Minus!</span>' : '';

                            var row = `
                                <tr class="${rowClass}">
                                    <td class="text-center font-mono">${item.index}</td>
                                    <td class="font-mono text-nowrap">${item.created_at}</td>
                                    <td>${typeBadge}</td>
                                    <td class="text-end font-mono fw-bold">Rp ${formatNumber(item.amount)}</td>
                                    <td>${item.description} ${indicator}</td>
                                    <td class="text-end ${prevClass}">Rp ${formatNumber(item.prev_balance)}</td>
                                    <td class="text-end ${afterClass}">Rp ${formatNumber(item.balance_after)}</td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada riwayat mutasi saldo ditemukan.</td></tr>');
                    }
                },
                error: function(err) {
                    $('#modal-loading').addClass('d-none');
                    $('#modal-content').removeClass('d-none');
                    $('#modal-timeline-body').html('<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat log data mutasi.</td></tr>');
                }
            });
        });
    });
</script>
@endpush
