@extends('layouts.master', ['title' => 'Laporan Saldo'])
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
                        <a href="{{ route('report-saldo.index') }}" class="text-muted text-hover-primary">Laporan
                            Saldo</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Saldo</li>
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
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-beetween gap-5">
                        {{-- <div class="mb-0">
                            <label class="form-label">Filter Tanggal</label>
                            <div class="d-flex
                                                gap-4 align-items-end">
                                <div id="dateRange" class="pull-right"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                    <span></span> <b class="caret"></b>
                                </div>
                            </div>
                        </div> --}}
                        <div class="mb-0">
                            <form action="{{ route('report-saldo.export') }}" id="form-filter" method="get">
                                <input type="text" hidden id="type" name="type" required>
                                <div class="d-flex flex-wrap gap-4 align-items-end">
                                    <div>
                                        <label class="form-label">UPT</label>
                                        <select name="school_id" class="form-select" id="filter_school_id">
                                            <option value="">Pilih Pendidikan</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Kelas</label>
                                        <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                            <option value="">Pilih Kelas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select" id="filter_status">
                                            <option value="">Semua</option>
                                            <option value="SUCCESS">Berhasil</option>
                                            <option value="PENDING">Pending</option>
                                        </select>
                                    </div>
                                    <!--begin::Export dropdown-->
                                    <button type="button" class="btn btn-sm btn-primary" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="ki-duotone fa fa-caret-down fs-2"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        Export Report
                                    </button>
                                    <!--begin::Menu-->
                                    <div id="kt_datatable_example_export_menu"
                                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4"
                                        data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a type="button" class="menu-link btn-export px-3" data-type="xlsx">
                                                Export as Excel
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a type="button" class="menu-link btn-export px-3" data-type="csv">
                                                Export as CSV
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                    <!--end::Export dropdown-->
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">

                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body">
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
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-saldo" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    // onchange school_id get data classrom on school
        $('#filter_school_id').on('change', function() {
            var school_id = $(this).val();
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: {
                    school_id: school_id
                },
                success: function(response) {
                    console.log(response);
                    $('#filter_classroom_id').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name +
                                '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
                    }
                }
            });
        });

   $(document).ready(function() {
    // Initialize DataTables
    var table = $('#table-saldo').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: "{{ route('report-saldo.index') }}",
    data: function(d) {
    // Get filter data from form elements
    d.school_id = $('#filter_school_id').val();
    d.classroom_id = $('#filter_classroom_id').val();
    d.status = $('#filter_status').val();
    }
    },
    columns: [
    {
    data: null,
    sortable: false,
    searchable: false,
    render: function(data, type, row, meta) {
    // Render serial number
    return meta.row + meta.settings._iDisplayStart + 1;
    }
    },
    {
    data: 'student.nis',
    name: 'student.nis',
    orderable: true,
    searchable: true
    },
    {
    data: 'student.name',
    name: 'student.name',
    orderable: true,
    searchable: true
    },
    {
    data: 'amount',
    name: 'amount',
    orderable: true,
    searchable: true
    },
    {
    data: 'status',
    name: 'status',
    orderable: true,
    searchable: true
    },
    {
    data: 'description',
    name: 'description',
    orderable: true,
    searchable: true
    }
    ]
    });
    // Fungsi untuk memperbarui data tabel saat melakukan pencarian
    function searchData() {
        table.ajax.reload();
    }
    
    // Event saat tombol "Tampilkan" ditekan
    $('#btn_tampilkan').click(function() {
    searchData();
    });
    
    // Event saat formulir filter disubmit
    $('#filter_form').submit(function(event) {
    event.preventDefault(); // Mencegah aksi default saat submit
    searchData();
    });

    // onchange school_id and classroom_id reload datatable
    $('#filter_school_id, #filter_classroom_id, #filter_status').on('change', function() {
        searchData();
    });

    // Export Report
    $('.btn-export').on('click', function() {
        var type = $(this).data('type');
        $('#type').val(type);
        $('#school_id').val($('#filter_school_id').val());
        $('#classroom_id').val($('#filter_classroom_id').val());
        $('#status').val($('#filter_status').val());
        $('#form-filter').submit();
    });
    });

</script>
@endpush