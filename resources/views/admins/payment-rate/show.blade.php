@extends('layouts.master', ['title' => 'Detail Tarif Pembayaran'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Tarif Pembayaran</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('bill-type.index') }}" class="text-muted text-hover-primary">Jenis
                            Pembayaran</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Detail Tarif Pembayaran</li>
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
                        <h3 class="text-dark">Detail Tarif Pembayaran</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-report-bill" class="table align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th class="min-w-125px">Item Pembayaran</th>
                                    <th class="min-w-125px">Total Tagihan</th>
                                    <th class="min-w-125px">Dibayar</th>
                                    <th class="min-w-125px">Sisa Tagihan</th>
                                    <th class="text-center min-w-70px" style="width: 22%">Status</th>
                                    <th class="text-center" style="width: 10%">Aksi</th>
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
    url: "{{ route('payment-rate.show', $paymentRate->id) }}",
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
    { data: 'total', name: 'total' },
    { data: 'total_paid', name: 'total_paid' },
    { data: 'total_unpaid', name: 'total_unpaid' },
    { data: 'status', name: 'status', responsivePriority: -1 },
    { data: 'action', name: 'action', responsivePriority: -1 }
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
    url: "{{ route('payment-rate.show', $paymentRate->id) }}",
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
</script>
@endpush