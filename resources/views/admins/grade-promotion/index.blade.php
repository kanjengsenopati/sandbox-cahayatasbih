@extends('layouts.master', ['title' => 'Kenaikan Kelas'])
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
                        <a href="{{ route('student.index') }}" class="text-muted text-hover-primary">Data
                            Siswa</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Kenaikan Kelas</li>
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
                        <div class="mb-0">
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
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">

                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <!--end::Card body-->
            </div>
            <!--end::Card-->

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">

                    </div>
                    <div class="">
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <form action="{{ route('grade-promotion.store') }}" method="post">
                        @csrf
                        <x-alert.alert-validation />
                        <div class="table-responsive">
                            <table id="table-grade-promotion" class="table align-middle table-row-dashed ">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                        <th style="align-content: center">Pilih Semua<br><input type="checkbox"
                                                id="select_all"></th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold"></tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                        <div class="row align-items-center mt-2">
                            <div class="col-md-auto">
                                <label class="form-label me-3">Pindah / Naik Ke Kelas</label>
                            </div>
                            <div class="col-md-auto">
                                <select name="new_classroom_id" id="filter_new_classroom" class="form-select">
                                    <option value="">Pilih Kelas Baru</option>
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <select name="academic_year_id" id="filter_academic_year_id" class="form-select">
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if (Auth::user()->can('Create Kenaikan Kelas'))
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary" id="btn_change_classroom">Pindah
                                    Kelas</button>
                            </div>
                            @endif
                        </div>
                    </form>
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
<script>
    // Function to get classrooms by school id
    function getClassroomBySchoolId(schoolId) {
    $.ajax({
    url: "{{ route('report-bill.get-classroom') }}",
    type: "GET",
    data: {
    school_id: schoolId
    },
    success: function(response) {
    $('#filter_classroom_id').empty();
    $('#filter_new_classroom').empty();
    if (response.data.length > 0) {
    $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
    $.each(response.data, function(key, value) {
    $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
    $('#filter_new_classroom').append('<option value="' + value.id + '">' + value.name + '</option>');
    });
    } else {
    $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
    }
    }
    });
    }
    
    $(document).ready(function() {

    // load kelas jika upt ada yang dipilih
    var schoolId = $('#filter_school_id').val();
    if (schoolId) {
    getClassroomBySchoolId(schoolId);
    }
    // Initialize DataTables
    var table;
    
    function initializeDataTable() {
    table = $('#table-grade-promotion').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: "{{ route('grade-promotion.index') }}",
    data: function(d) {
    // Get filter data from form elements
    d.school_id = $('#filter_school_id').val();
    d.classroom_id = $('#filter_classroom_id').val();
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
    data: 'nis',
    name: 'nis',
    orderable: true,
    searchable: true
    },
    {
    data: 'name',
    name: 'name',
    orderable: true,
    searchable: true
    },
    {
    data: 'classroom',
    name: 'classroom',
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
    // add checkbox input
    data: 'id',
    name: 'id',
    orderable: false,
    searchable: false,
    render: function(data, type, row) {
    return `<input type="checkbox" name="student_ids[]" value="${data}">`;
    }
    },
    ]
    });
    }
    
    // Function to refresh table data when searching
    function searchData() {
    if ($.fn.DataTable.isDataTable('#table-grade-promotion')) {
    table.ajax.reload();
    } else {
    initializeDataTable();
    }
    }
    
    // Onchange school_id get data classroom on school
    $('#filter_school_id').on('change', function() {
    var schoolId = $(this).val();
    if (schoolId) {
    getClassroomBySchoolId(schoolId); // Call function to get classrooms
    searchData(); // Load DataTable when school_id is selected
    } else {
    $('#filter_classroom_id').empty(); // Clear classroom options if school_id is not selected
    $('#filter_new_classroom').empty();
    if ($.fn.DataTable.isDataTable('#table-grade-promotion')) {
    table.destroy(); // Destroy DataTable if school_id is not selected
    }
    }
    });
    
    // onchange school_id and classroom_id reload datatable
    $('#filter_classroom_id').on('change', function() {
    searchData();
    });
    
    // Event when filter form is submitted
    $('#filter_form').submit(function(event) {
    event.preventDefault(); // Prevent default action when submit
    searchData();
    });
    
    // on click select all checkbox
    $('#select_all').on('click', function() {
    if ($(this).is(':checked')) {
    $('input[name="student_ids[]"]').prop('checked', true);
    } else {
    $('input[name="student_ids[]"]').prop('checked', false);
    }
    });
    });
</script>
@endpush