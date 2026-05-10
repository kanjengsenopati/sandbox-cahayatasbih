<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Tagihan Santri | {{ config('app.name') }}</title>
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
 
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
 
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 
    <style>
        /* Legacy Theme Colors */
        :root {
            --kt-primary: #8a63d2;
            --kt-primary-light: #f4f1fa;
        }
 
        body {
            background-color: #f5f8fa;
            font-family: 'Poppins', sans-serif;
        }
 
        .card {
            border: none;
            box-shadow: 0 0 20px 0 rgba(76, 87, 125, 0.02);
            border-radius: 0.475rem;
        }
 
        .table.dataTable thead th {
            color: #b5b5c3 !important;
            text-transform: uppercase;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: -0.01rem;
            border-bottom: 1px border-dashed #eff2f5;
        }
 
        .table.dataTable tbody td {
            border-bottom: 1px border-dashed #eff2f5;
            padding: 1rem 0.75rem;
        }
 
        .details-control {
            cursor: pointer;
            text-align: center;
        }
 
        .student-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }
 
        .nested-table {
            background-color: #f9f9f9;
            border-radius: 8px;
            margin: 10px 0 10px 50px;
            padding: 15px;
            border: 1px solid #eee;
        }
 
        .badge-light-success {
            background-color: #e8fff3;
            color: #50cd89;
        }
 
        .badge-light-danger {
            background-color: #fff5f8;
            color: #f1416c;
        }
 
        .header-title {
            background-color: white;
            padding: 20px 0;
            border-bottom: 1px solid #eff2f5;
            margin-bottom: 30px;
        }
    </style>
</head>
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed aside-enabled aside-fixed">
 
<div class="header-title">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="text-dark fw-bolder fs-3 mb-0">Laporan Pembayaran Santri</h1>
                <div class="text-muted fs-7 fw-bold mt-1">
                    {{ $academicYear ? 'Tahun Ajaran ' . $academicYear->name : 'Semua Tahun Ajaran' }}
                </div>
            </div>
            <div>
                <img src="{{ url('assets/media/logos/logo.png') }}" class="h-40px" alt="">
            </div>
        </div>
    </div>
</div>
 
<div class="container mb-10">
    <!-- Stats Row -->
    <div class="row g-5 mb-8">
        <div class="col-md-4">
            <div class="card p-6 border-start border-primary border-4">
                <div class="text-muted fw-boldest fs-7 uppercase">TOTAL TAGIHAN</div>
                <div class="text-dark fs-2 fw-bolder mt-1">Rp {{ number_format($totals['total_amount'], 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-6 border-start border-success border-4">
                <div class="text-muted fw-boldest fs-7 uppercase">TOTAL TERBAYAR</div>
                <div class="text-success fs-2 fw-bolder mt-1">Rp {{ number_format($totals['total_paid'], 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-6 border-start border-danger border-4">
                <div class="text-muted fw-boldest fs-7 uppercase">SISA TAGIHAN</div>
                <div class="text-danger fs-2 fw-bolder mt-1">Rp {{ number_format($totals['total_unpaid'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
 
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <span class="svg-icon svg-icon-1 position-absolute ms-4">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" data-kt-report-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari Santri..." />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="report_table">
                <thead>
                    <tr class="text-start text-gray-800 fw-boldest fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2"></th>
                        <th class="min-w-50px">NO</th>
                        <th class="min-w-200px">SANTRI</th>
                        <th class="text-end min-w-100px">TOTAL</th>
                        <th class="text-end min-w-100px text-success">LUNAS</th>
                        <th class="text-end min-w-100px text-danger">SISA</th>
                        <th class="text-end min-w-150px">REALISASI</th>
                    </tr>
                </thead>
                <tbody class="fw-bold text-gray-600">
                    @foreach($data as $index => $row)
                    @php
                        $avatar = $row->avatar;
                        if ($avatar) {
                            if (str_starts_with($avatar, 'http')) {
                                $avatarUrl = $avatar;
                            } elseif (str_starts_with($avatar, 'storage/') || str_starts_with($avatar, 'assets/')) {
                                $avatarUrl = url($avatar);
                            } else {
                                $avatarUrl = url('storage/images/avatar/' . $avatar);
                            }
                        } else {
                            $avatarUrl = url('assets/media/avatars/default.png');
                        }
                        
                        $pct = $row->total_bill == 0 ? 0 : ($row->total_paid / $row->total_bill) * 100;
                        $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                    @endphp
                    <tr data-student-id="{{ $row->id }}">
                        <td class="details-control">
                            <i class="fas fa-chevron-right text-primary"></i>
                        </td>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $avatarUrl }}" class="student-avatar" alt="">
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ $row->name }}</span>
                                    <span class="text-muted fs-7">{{ $row->classroom_name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">Rp {{ number_format($row->total_bill, 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($row->total_paid, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">
                            {{ $row->total_unpaid > 0 ? 'Rp ' . number_format($row->total_unpaid, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-column w-100 me-2">
                                <div class="d-flex flex-stack mb-2">
                                    <span class="text-muted me-2 fs-7 fw-bold">{{ round($pct) }}%</span>
                                </div>
                                <div class="progress h-6px w-100">
                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
 
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
 
<script>
    "use strict";
 
    var KTReportTable = function () {
        var table = document.getElementById('report_table');
        var datatable;
 
        var monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
 
        var format = function (d, breakdown) {
            var html = '<div class="nested-table">' +
                '<h6 class="fw-bolder mb-3">Rincian Item Tagihan</h6>' +
                '<table class="table table-sm table-row-dashed fs-7 gy-3">' +
                '<thead><tr class="text-start text-gray-800 fw-boldest fs-8 text-uppercase">' +
                '<th class="w-20px ps-3">NO</th>' +
                '<th>Nama Tagihan</th>' +
                '<th>Tahun Ajaran</th>' +
                '<th>Bulan & Tahun</th>' +
                '<th class="text-end">Nominal</th>' +
                '<th class="text-center">Status</th>' +
                '</tr></thead>' +
                '<tbody>';
 
            if (breakdown && breakdown.length > 0) {
                breakdown.forEach(function(bill, index) {
                    var statusBadge = bill.status === "PAID" 
                        ? '<span class="badge badge-light-success fs-9 px-3 py-1">Lunas</span>' 
                        : '<span class="badge badge-light-danger fs-9 px-3 py-1">Belum Lunas</span>';
                    
                    // Group months by year for display
                    var monthYearStr = "";
                    var yearGroups = {};
                    bill.months.forEach(function(m) {
                        if (!yearGroups[m.year]) yearGroups[m.year] = [];
                        yearGroups[m.year].push(monthNames[m.month]);
                    });

                    var formattedGroups = [];
                    for (var year in yearGroups) {
                        formattedGroups.push(yearGroups[year].join(", ") + " " + year);
                    }
                    monthYearStr = formattedGroups.join("; ");
                    
                    html += '<tr>' +
                        '<td class="ps-3 text-muted">' + (index + 1) + '</td>' +
                        '<td class="fw-boldest text-gray-800">' + bill.bill_type_name + '</td>' +
                        '<td>' + bill.academic_year + '</td>' +
                        '<td>' + monthYearStr + '</td>' +
                        '<td class="text-end fw-boldest text-dark">Rp ' + new Intl.NumberFormat('id-ID').format(bill.amount) + '</td>' +
                        '<td class="text-center">' + statusBadge + '</td>' +
                        '</tr>';
                });
            } else {
                html += '<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada rincian data.</td></tr>';
            }
 
            html += '</tbody></table></div>';
            return html;
        };
 
        var initDatatable = function () {
            datatable = $(table).DataTable({
                "info": false,
                'order': [],
                'pageLength': 10,
                'lengthMenu': [10, 15, 25, 20], // As requested: 10 default, 15, 25, 20
                'columnDefs': [
                    { orderable: false, targets: 0 },
                ]
            });
 
            // Expand all rows by default as requested
            datatable.rows().every(function () {
                var row = this;
                var studentId = $(row.node()).data('student-id');
                var breakdown = window.pivotedData[studentId];
                row.child(format(row.data(), breakdown)).show();
                $(row.node()).addClass('shown');
                $(row.node()).find('.details-control i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
            });
 
            // Handle search
            const filterSearch = document.querySelector('[data-kt-report-table-filter="search"]');
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
 
            // Click listener for details control
            $('#report_table tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = datatable.row(tr);
                var studentId = tr.data('student-id');
                var breakdown = window.pivotedData[studentId];
 
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
                } else {
                    row.child(format(row.data(), breakdown)).show();
                    tr.addClass('shown');
                    $(this).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
            });
        };
 
        return {
            init: function () {
                if (!table) { return; }
                initDatatable();
            }
        };
    }();
 
    // Data for nested table
    window.pivotedData = @json($pivotedData);
 
    $(document).ready(function() {
        KTReportTable.init();
    });
</script>
</body>
</html>
