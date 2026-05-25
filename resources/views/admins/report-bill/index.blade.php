@extends('layouts.master', ['title' => 'Laporan Tagihan'])
@section('content')
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-bill.index') }}" class="text-muted text-hover-primary">Laporan
                            Tagihan</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Tagihan</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card mb-5">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="text-dark">Laporan Tagihan Pembayaran</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body">
                    <form id="filter_form" method="GET" class="mb-5">
                        <div class="row align-items-end g-3 p-4 bg-white rounded shadow-sm border border-gray-200">
                            <div class="col-md-3">
                                <label class="form-label text-muted fs-7 fw-bold mb-2 text-uppercase ls-1">Range Tanggal</label>
                                <div class="position-relative">
                                    <div id="dateRange" class="form-control form-control-solid d-flex align-items-center justify-content-between cursor-pointer border-0 bg-light-primary">
                                        <span class="text-gray-600 fw-bold fs-6 ps-1">Loading...</span>
                                        <i class="fa fa-calendar text-primary ms-2 fs-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_school_id" class="form-label text-muted fs-7 fw-bold mb-2 text-uppercase ls-1">Unit Pendidikan (UPT)</label>
                                <select name="school_id" class="form-select form-select-solid border-0 bg-light" id="filter_school_id">
                                    <option value="">Semua UPT</option>
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_classroom_id" class="form-label text-muted fs-7 fw-bold mb-2 text-uppercase ls-1">Kelas</label>
                                <select name="classroom_id" class="form-select form-select-solid border-0 bg-light" id="filter_classroom_id">
                                    <option value="">Semua Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_academic_year_id" class="form-label text-muted fs-7 fw-bold mb-2 text-uppercase ls-1">Tahun Ajaran</label>
                                <select name="academic_year_id" class="form-select form-select-solid border-0 bg-light" id="filter_academic_year_id">
                                    <option value="">Semua Perioede</option>
                                    @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Summary Cards -->
                    <div class="row g-4">
                        <!-- Target Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #F3F0FF 0%, #FFFFFF 100%); border-radius: 12px;">
                                <div class="card-body p-5">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column">
                                            <span class="text-uppercase tracking-wider fw-bold text-gray-500 fs-8 ls-1 mb-1">Target Pemasukkan</span>
                                            <span class="fw-bolder fs-2x text-dark tracking-tight" id="total">Rp 0</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm" style="width: 48px; height: 48px;">
                                            <i class="fa fa-bullseye fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-light-primary text-primary px-3 py-2 rounded-pill fs-7 fw-bolder">
                                            Goal
                                        </div>
                                        <span class="text-muted ms-3 fs-7">Total seluruh tagihan tercatat</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Realization Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #F0FFF4 0%, #FFFFFF 100%); border-radius: 12px;">
                                <div class="card-body p-5">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex flex-column">
                                            <span class="text-uppercase tracking-wider fw-bold text-gray-500 fs-8 ls-1 mb-1">Realisasi Pemasukkan</span>
                                            <span class="fw-bolder fs-2x text-dark tracking-tight" id="total_paid">Rp 0</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm" style="width: 48px; height: 48px;">
                                            <i class="fa fa-wallet fs-2 text-success"></i>
                                        </div>
                                    </div>

                                    <!-- Progress Section -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-gray-600 fw-bold fs-7">Progress</span>
                                            <span class="fw-bold fs-7 text-dark" id="progress-bar-label">0%</span>
                                        </div>
                                        <div class="progress h-10px w-100 rounded-pill bg-light-success">
                                            <div class="progress-bar rounded-pill" role="progressbar" id="progress-bar"
                                                style="width: 0%; transition: width 0.6s ease;"
                                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-top border-gray-100 d-flex align-items-center">
                                        <span class="text-muted fs-7 me-2">Sisa Tagihan:</span>
                                        <span class="text-danger fw-bolder fs-6 bg-light-danger px-2 py-1 rounded" id="total_unpaid">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        {{-- <h3 class="text-dark">Sekolah</h3> --}}
                    </div>
                    <div class="">
                        {{-- <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission"
                            href="{{ route('report-bill.create') }}">+ Sekolah</a> --}}
                        <!--end::Primary button-->
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-report-bill" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama</th>
                                    <th>Tahun Akademik</th>
                                    <th>Tipe</th>
                                    <th>Total Tagihan</th>
                                    <th>Total Santri</th>
                                    <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function() {
    // Inisialisasi DataTable
    // var table = initializeTable();

    $('#filter_school_id').on('change', function() {
         var school_id = $(this).val();
         $.ajax({
        url: "{{ route('report-bill.get-classroom') }}",
        type: "GET",
        data: { school_id: school_id },
          success: function(response) {
          $('#filter_classroom_id').empty();
            if (response.data.length > 0) {
                  $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
            $.each(response.data, function(key, value) {
                 $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
            } else {
                 $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
            }
            }
         });

        reloadTable();
    });

    // handle filter change
    $('#filter_classroom_id, #filter_academic_year_id').on('change', function() {
        reloadTable();
    });
    
    // Inisialisasi date range picker
    $('#dateRange').daterangepicker({
        startDate: moment().subtract(365, 'days'),
        endDate: moment(),
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '9 Bulan Terakhir': [moment().subtract(9, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
        }
    }, function(start, end) {
        $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
        reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });

    // Panggilan awal untuk date range picker
    var start = moment().subtract(365, 'days');
    var end = moment();
    $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
    reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
});

    function initializeTable(start_date = '', end_date = '') {
    return $('#table-report-bill').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: "{{ route('report-bill.index') }}",
    data: function(d) {
    d.type = 'bill';
    d.school_id = $('#filter_school_id').val();
    d.classroom_id = $('#filter_classroom_id').val();
    d.academic_year_id = $('#filter_academic_year_id').val();
    d.start_date = start_date;
    d.end_date = end_date;
    }
    },
    language: {
    processing: "Sedang memproses data, Silahkan ditunggu..."
    },
    lengthMenu: [
    [10, 25, 50, 100, -1],
    [10, 25, 50, 100, "Semua"]
    ],
    columns: [
    { data: null, sortable: false, searchable: false, render: function(data, type, row, meta) {
    return meta.row + meta.settings._iDisplayStart + 1;
    }},
    { data: 'name', name: 'name' },
    { data: 'academic_year', name: 'academic_year' },
    { data: 'type', name: 'type' },
    { data: 'total_bill', name: 'total_bill' },
    { data: 'student_count', name: 'student_count' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
    ]
    });
    }
    
    function reloadTable(start_date = '', end_date = '') {
    var table = $('#table-report-bill').DataTable();
    table.destroy();
    table = initializeTable(start_date, end_date);
    getTotalBill(start_date, end_date);
    }
    
    function getTotalBill(start_date = '', end_date = '') {
        $.ajax({
            url: "{{ route('report-bill.index') }}",
            type: "GET",
            dataType: 'json',
            data: {
                type: 'total',
                school_id: $('#filter_school_id').val(),
                classroom_id: $('#filter_classroom_id').val(),
                academic_year_id: $('#filter_academic_year_id').val(),
                start_date: start_date,
                end_date: end_date
            },
            success: function(response) {
                // Formatting values
                $('#total').text('Rp. ' + response.total);
                $('#total_paid').text('Rp. ' + response.total_paid);
                
                // Progress Bar Logic
                let percentage = parseFloat(response.realisasion_percentage);
                let percentageText = response.realisasion_percentage_text;
                
                $('#progress-bar').css('width', percentage + '%');
                $('#progress-bar-label').text(percentageText);
                
                // Progress Bar Color
                let progressBar = $('#progress-bar');
                progressBar.removeClass('bg-warning bg-success bg-primary bg-danger');
                
                if (percentage < 50) {
                    progressBar.addClass('bg-warning'); // Orange/Yellow
                } else if (percentage > 80) {
                    progressBar.addClass('bg-success'); // Green
                } else {
                    progressBar.addClass('bg-primary'); // Default (Purple/Blue)
                }

                $('#total_unpaid').text('Rp. ' + response.total_unpaid);
            }
        });
    }
</script>
@endpush