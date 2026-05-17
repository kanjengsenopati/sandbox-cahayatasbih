@extends('layouts.master', ['title' => 'Kelola Data Asrama'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Kelola Data Asrama</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('asrama.index') }}" class="text-muted text-hover-primary">Data Asrama</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Daftar Asrama</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                        <i class="fa fa-check-circle fa-2x text-success"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-dark">Berhasil</h4>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <span class="svg-icon svg-icon-2hx svg-icon-danger me-4">
                        <i class="fa fa-exclamation-triangle fa-2x text-danger"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-dark">Gagal</h4>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Master Data Asrama Pondok</h2>
                    </div>
                    <div class="card-toolbar">
                        <x-action.create name="Asrama" label="Tambah Asrama" action="{{ route('asrama.create') }}" />
                    </div>
                </div>
                <!--end::Card header-->
                
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-asrama" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th width="3%">No</th>
                                    <th>Nama Asrama</th>
                                    <th>Asatidz Pengampu</th>
                                    <th>No WhatsApp Asatidz</th>
                                    <th>Jumlah Santri Binaan</th>
                                    <th class="text-center min-w-100px">Aksi</th>
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
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script>
    function format(data) {
        if (!data.students || data.students.length === 0) {
            return '<div class="p-4 text-center text-muted">Tidak ada santri binaan di asrama ini.</div>';
        }

        var html = '<div class="p-5 bg-light rounded-3 shadow-xs border border-gray-200 my-2 ms-4">';
        html += '<h5 class="fw-bolder mb-3 text-primary"><i class="fa fa-users me-2"></i>Daftar Santri Binaan (' + data.students.length + ' Santri)</h5>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0 bg-white border rounded">';
        html += '<thead>';
        html += '<tr class="fw-bolder fs-7 text-gray-800 border-bottom border-gray-200 bg-light-primary gs-4">';
        html += '<th class="ps-4" width="5%">No</th>';
        html += '<th width="20%">NISN</th>';
        html += '<th width="45%">Nama Santri</th>';
        html += '<th width="20%">Kelas</th>';
        html += '<th width="10%" class="text-center pe-4">Gender</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        $.each(data.students, function(index, student) {
            var classroomName = student.classroom ? student.classroom.name : '<span class="text-muted italic">Tanpa Kelas</span>';
            var genderBadge = student.gender === 'L' 
                ? '<span class="badge badge-light-info fw-bold">Laki-laki</span>' 
                : '<span class="badge badge-light-danger fw-bold">Perempuan</span>';
            
            html += '<tr class="gs-4">';
            html += '<td class="ps-4 fw-bold">' + (index + 1) + '</td>';
            html += '<td class="font-monospace text-gray-600">' + (student.nisn || '-') + '</td>';
            html += '<td class="fw-bolder text-gray-800">' + student.name + '</td>';
            html += '<td>' + classroomName + '</td>';
            html += '<td class="text-center pe-4">' + genderBadge + '</td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        return html;
    }

    $(document).ready(() => {
        var table = $('#table-asrama').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('asrama.index') }}",
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                loadingRecords: "Loading...",
                processing: "Processing..."
            },
            columns: [
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                    searchable: true,
                },
                {
                    data: 'host_name',
                    name: 'hostAdmin.name',
                    orderable: true,
                    searchable: true,
                },
                {
                    data: 'host_phone',
                    name: 'hostAdmin.phone',
                    orderable: true,
                    searchable: true,
                },
                {
                    data: 'student_count',
                    name: 'students_count',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'btnAction',
                    name: 'btnAction',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                },
            ]
        });

        // Add event listener for opening and closing details
        $('#table-asrama tbody').on('click', '.btn-toggle-students', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var chevron = $(this).find('.btn-chevron');

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
                chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });
    });
</script>
@endpush
