@extends('layouts.master', ['title' => 'Laporan Audit Log'])

@push('css')
    <style>
        .premium-card {
            border-radius: 24px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
            border: none !important;
            background: #ffffff !important;
            transition: all 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.06) !important;
        }
        .typography-h1 {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: #0f172a !important; /* Slate-900 */
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .typography-h2 {
            font-size: 16px !important;
            font-weight: 600 !important;
            color: #1e293b !important; /* Slate-800 */
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .typography-label {
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            color: #94a3b8 !important; /* Slate-400 */
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .typography-body {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #475569 !important; /* Slate-600 */
            font-family: 'Inter', sans-serif !important;
        }
        .typography-caption {
            font-size: 12px !important;
            font-weight: 400 !important;
            font-style: italic !important;
            color: #94a3b8 !important; /* Slate-400 */
            font-family: 'Inter', sans-serif !important;
        }
    </style>
@endpush

@section('content')
    <div class="content d-flex flex-column flex-column-fluid px-5" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <!--begin::Container-->
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-0">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                    data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                    class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <!--begin::Title-->
                    <x-text.h1 class="my-1">Laporan Audit Log</x-text.h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Laporan</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Audit Log
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl px-0">
                <!--begin::Card-->
                <div class="card premium-card mb-5 p-5">
                    <!--begin::Card header-->
                    <div class="card-header d-flex align-items-center justify-content-between border-0 p-0 mb-5">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <x-text.h2>Riwayat Log Aktivitas Pengguna</x-text.h2>
                        </div>
                        <!--end::Card title-->

                        <!--begin::Actions-->
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light-primary btn-export" data-type="xlsx">
                                <i class="fa-solid fa-file-excel me-2"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-light-secondary btn-export" data-type="csv">
                                <i class="fa-solid fa-file-csv me-2"></i> Export CSV
                            </button>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body p-0">
                        <!--begin::Filters-->
                        <div class="row g-5 mb-6 align-items-end">
                            <div class="col-md-4">
                                <x-text.label class="d-block mb-2">Filter Rentang Tanggal</x-text.label>
                                <div id="dateRange" class="form-control d-flex align-items-center justify-content-between cursor-pointer"
                                    style="background: #fff; border: 1px solid #e4e6ef; height: 38px; border-radius: 8px;">
                                    <div>
                                        <i class="fa-solid fa-calendar text-muted me-2"></i>
                                        <span></span>
                                    </div>
                                    <b class="caret"></b>
                                </div>
                                <input type="hidden" id="start_date">
                                <input type="hidden" id="end_date">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-reset" class="btn btn-sm btn-light w-100" style="height: 38px;">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                        <!--end::Filters-->

                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table id="table-audit-log" class="table table-striped border rounded gy-5 gs-7 w-100">
                                <thead>
                                    <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                        <th width="5%">No</th>
                                        <th>Nama</th>
                                        <th>Peran (Role)</th>
                                        <th>Aksi (Activity)</th>
                                        <th>Timestamp</th>
                                        <th>Device / IP</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                $('#start_date').val(start.format('YYYY-MM-DD'));
                $('#end_date').val(end.format('YYYY-MM-DD'));
            }

            $('#dateRange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal',
                    customRangeLabel: 'Rentang Kustom',
                }
            }, cb);

            // Set default text empty or let it trigger initial load
            cb(start, end);

            var table = $('#table-audit-log').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('report-audit.index') }}",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function (data) {
                            return `<div class="typography-body">${data}</div>`;
                        }
                    },
                    {
                        data: 'role',
                        name: 'role',
                        render: function (data) {
                            let badgeClass = 'bg-light-primary text-primary';
                            if (data === 'Super Admin') badgeClass = 'bg-light-danger text-danger';
                            else if (data === '-') badgeClass = 'bg-light-secondary text-dark';
                            return `<span class="badge ${badgeClass} fs-7 fw-bold">${data}</span>`;
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function (data) {
                            return `<span class="typography-body">${data}</span>`;
                        }
                    },
                    {
                        data: 'date',
                        name: 'created_at',
                        render: function (data) {
                            return `<span class="typography-caption">${data}</span>`;
                        }
                    },
                    {
                        data: 'device',
                        name: 'device',
                    }
                ],
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'></i>",
                        previous: "<i class='fa fa-angle-left'></i>"
                    },
                    loadingRecords: "Memuat...",
                    processing: "Memproses..."
                }
            });

            // Reload table on date filter changes
            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                cb(picker.startDate, picker.endDate);
                table.draw();
            });

            $('#btn-reset').click(function () {
                cb(start, end);
                table.draw();
            });

            // Handle exports
            $('.btn-export').click(function () {
                let exportType = $(this).data('type');
                let startDate = $('#start_date').val();
                let endDate = $('#end_date').val();
                let url = "{{ route('report-audit.export') }}?type=" + exportType + "&start_date=" + startDate + "&end_date=" + endDate;
                window.open(url, '_blank');
            });
        });
    </script>
@endpush
