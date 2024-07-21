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
                    <form id="filter_form" method="GET">
                        <div class="row g-3">
                            <div>
                                <label class="form-label">Filter Tanggal</label>
                                <div class="d-flex gap-4 align-items-end">
                                    <div id="dateRange" class="pull-right"
                                        style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                        <span></span> <b class="caret"></b>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_school_id" class="form-label">UPT</label>
                                <select name="school_id" class="form-select" id="filter_school_id">
                                    <option value="">Semua UPT</option>
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_classroom_id" class="form-label">Kelas</label>
                                <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                    <option value="">Semua Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_academic_year_id" class="form-label">Tahun Ajaran</label>
                                <select name="academic_year_id" class="form-select" id="filter_academic_year_id">
                                    <option value="">Semua Tahun Ajaran</option>
                                    @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex gap-2 mt-4">
                        <div class="card bg-light-primary bg-active-primary flex-grow-1">
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Label-->
                                <div class="fw-bolder fs-5 text-gray-800">Target Pemasukkan</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="text-primary fs-3 fw-bolder" id="total">Rp. 0</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Body-->
                        </div>

                        <div class="card bg-light-success bg-active-success flex-grow-1">
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Label-->
                                <div class="fw-bolder fs-5 text-gray-800">Realisasi Pemasukkan</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="text-success fs-3 fw-bolder" id="total_paid">Rp. 0</div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="0" id="progress-bar"
                                        aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar-label" id="progress-bar-label">0%</div>
                                    </div>
                                </div>
                                <p class="text-center mt-3">
                                <div class="text-danger" id="total_unpaid">Rp. 0</div>
                                </p>
                                <!--end::Stats-->
                            </div>
                            <!--end::Body-->
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

                    <!-- Add Button for WA Blast -->
                    {{-- <div class="text-center mt-3">
                        <button class="btn btn-success" id="send-blast-notif"><i class="bi bi-whatsapp"></i>
                            <span class="indicator-label" id="buttonText">Kirim
                                Notif
                                Tagihan WA</span>
                            <span class="indicator-progress d-none">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span></button>
                    </div> --}}
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latenet/momentjs/latest/moment.min.js">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function() {
    // Inisialisasi DataTable
    var table = initializeTable();

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
                { data: 'academic_year.name', name: 'academic_year.name' },
                { data: 'type', name: 'type' },
                { data: 'total_bill', name: 'total_bill' },
                { data: 'student_count', name: 'student_count' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }

    function reloadTable(start_date = '', end_date = '') {
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
                $('#total').text('Rp. ' + response.total);
                $('#total_paid').text('Rp. ' + response.total_paid);
                $('#progress-bar').css('width', response.realisasion_percentage);
                $('#progress-bar-label').text(response.realisasion_percentage);
                $('#total_unpaid').text('Belum Lunas: Rp. ' + response.total_unpaid);

            }
        });
    }

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

    // Mengirim notifikasi tagihan
    // $('#send-blast-notif').on('click', function() {
    //     var button = $(this);
    //     changeButtonText(button, true);

    //     var data = {
    //         _token: "{{ csrf_token() }}",
    //         school_id: $('#filter_school_id').val(),
    //         classroom_id: $('#filter_classroom_id').val(),
    //         academic_year_id: $('#filter_academic_year_id').val(),
    //         bill_type_id: $('#filter_bill_type_id').val(),
    //         status: $('#filter_status').val()
    //     };

    //     $.ajax({
    //         url: "{{ route('report-bill.send-bill-notification') }}",
    //         type: "POST",
    //         data: data,
    //         success: function(response) {
    //             if (response.code === 200) {
    //                 toastr.success("Berhasil mengirim notifikasi tagihan ke WhatsApp");
    //             } else {
    //                 toastr.error(response.message);
    //             }
    //         },
    //         error: function(xhr, status, error) {
    //             toastr.error("Terjadi kesalahan saat mengirim notifikasi: " + error);
    //         },
    //         complete: function() {
    //             changeButtonText(button, false);
    //         }
    //     });
    // });

    // function changeButtonText(button, sending) {
    //     var buttonText = button.find('.indicator-label');
    //     var indicatorProgress = button.find('.indicator-progress');

    //     if (sending) {
    //         buttonText.text('Mengirim Notifikasi...');
    //         button.attr('disabled', true);
    //         indicatorProgress.removeClass('d-none');
    //     } else {
    //         buttonText.text('Kirim Notif Tagihan WA');
    //         button.removeAttr('disabled');
    //         indicatorProgress.addClass('d-none');
    //     }
    // }

    // Inisialisasi date range picker
    $('#dateRange').daterangepicker({
        startDate: moment().subtract(365, 'days'),
        endDate: moment(),
        ranges: {
            'Hari Ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()]
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
</script>
@endpush