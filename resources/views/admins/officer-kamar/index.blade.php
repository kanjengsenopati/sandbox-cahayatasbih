@extends('layouts.master', ['title' => 'Data Kamar & Ustadz'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Kamar & Penanggung Jawab / Ustadz</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('officer.index') }}" class="text-muted text-hover-primary">Master Data</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Data Kamar & Ustadz</li>
                </ul>
            </div>
            <!--end::Page title-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            
            <!--begin::Nav Line Tabs-->
            <div class="bg-white rounded-[24px] p-4 mb-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-4 px-6 {{ $activeTab === 'officer' ? 'active' : '' }}" 
                           data-bs-toggle="tab" href="#kt_tab_officer" id="tab_link_officer" role="tab">
                            <i class="fa-solid fa-user-tie fs-4 me-2 text-primary"></i> Petugas / Ustadz / Penanggung Jawab
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-4 px-6 {{ $activeTab === 'kamar' ? 'active' : '' }}" 
                           data-bs-toggle="tab" href="#kt_tab_kamar" id="tab_link_kamar" role="tab">
                            <i class="fa-solid fa-bed fs-4 me-2 text-primary"></i> Data Kamar & Santri
                        </a>
                    </li>
                </ul>
            </div>
            <!--end::Nav Line Tabs-->

            <!--begin::Tab Content-->
            <div class="tab-content" id="kt_tab_content">
                <!--begin::Tab 1: Officer / Ustadz-->
                <div class="tab-pane fade {{ $activeTab === 'officer' ? 'show active' : '' }}" id="kt_tab_officer" role="tabpanel">
                    <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px;">
                        <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                            <div class="card-title">
                                <h3 class="fw-bolder text-gray-800 fs-4 mb-0"><i class="fa-solid fa-users-gear text-primary me-2"></i>Daftar Petugas & Ustadz Penanggung Jawab</h3>
                            </div>
                            @can('Create Petugas')
                                <x-action.create name="Petugas" label="Tambah Ustadz / Petugas" action="{{ route('officer.create') }}" />
                            @endcan
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="table-officer" class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th style="width: 12%">Foto</th>
                                            <th style="width: 20%">Nama</th>
                                            <th style="width: 15%">Jabatan</th>
                                            <th style="width: 23%">Tugas / Deskripsi</th>
                                            <th style="width: 15%">No WA</th>
                                            <th style="width: 10%">Status</th>
                                            <th class="text-center min-w-100px" style="width: 10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Tab 1-->

                <!--begin::Tab 2: Kamar & Santri-->
                <div class="tab-pane fade {{ $activeTab === 'kamar' ? 'show active' : '' }}" id="kt_tab_kamar" role="tabpanel">
                    <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px;">
                        <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                            <div class="card-title">
                                <h3 class="fw-bolder text-gray-800 fs-4 mb-0"><i class="fa-solid fa-house-chimney text-primary me-2"></i>Master Data Kamar Santri</h3>
                            </div>
                            @can('Create Asrama')
                                <x-action.create name="Asrama" label="Tambah Kamar" action="{{ route('asrama.create') }}" />
                            @endcan
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="table-asrama" class="table table-striped border rounded gy-5 gs-7">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                            <th width="5%">No</th>
                                            <th>Nama Kamar</th>
                                            <th>Penanggung Jawab Kamar</th>
                                            <th>No Whatsapp</th>
                                            <th>Jumlah Santri</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Tab 2-->
            </div>
            <!--end::Tab Content-->

        </div>
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    // Format child row showing list of Santri residing in each Kamar
    function formatSantriList(data) {
        if (!data.students || data.students.length === 0) {
            return '<div class="p-4 text-center text-muted italic fs-7">Belum ada santri yang dialokasikan di kamar ini.</div>';
        }

        var html = '<div class="p-5 bg-light rounded-3 shadow-xs border border-gray-200 my-2 ms-4" style="border-radius: 16px;">';
        html += '<h5 class="fw-bolder mb-3 text-primary"><i class="fa fa-users me-2"></i>Daftar Santri Penghuni Kamar ' + data.name + ' (' + data.students.length + ' Santri)</h5>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0 bg-white border rounded">';
        html += '<thead>';
        html += '<tr class="fw-bolder fs-7 text-gray-800 border-bottom border-gray-200 bg-light-primary gs-4">';
        html += '<th class="ps-4" width="5%">No</th>';
        html += '<th width="25%">Nama Kamar</th>';
        html += '<th width="50%">Nama Santri</th>';
        html += '<th width="20%">Kelas</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        $.each(data.students, function(index, student) {
            var classroomName = student.classroom ? student.classroom.name : '<span class="text-muted italic">Tanpa Kelas</span>';
            
            html += '<tr class="gs-4">';
            html += '<td class="ps-4 fw-bold">' + (index + 1) + '</td>';
            html += '<td>' + data.name + '</td>';
            html += '<td class="fw-bolder text-gray-800">' + student.name + '</td>';
            html += '<td>' + classroomName + '</td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        return html;
    }

    $(document).ready(() => {
        // Initialize Officer DataTable
        var tableOfficer = $('#table-officer').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('officer.index') }}",
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'>",
                    previous: "<i class='fa fa-angle-left'>"
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
                { data: 'photo', name: 'photo' },
                { data: 'user.name', name: 'user.name', searchable: true, defaultContent: '-' },
                { data: 'position', name: 'position', searchable: true },
                { data: 'duty', name: 'duty', searchable: true },
                { data: 'phone', name: 'phone', searchable: true },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' }
            ]
        });

        // Initialize Kamar DataTable
        var tableKamar = $('#table-asrama').DataTable({
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
                { data: 'name', name: 'name', orderable: true, searchable: true },
                { data: 'host_name', name: 'hostAdmin.name', orderable: true, searchable: true },
                { data: 'host_phone', name: 'hostAdmin.phone', orderable: true, searchable: true },
                { data: 'student_count', name: 'students_count', orderable: false, searchable: false },
                { data: 'btnAction', name: 'btnAction', className: 'text-center', orderable: false, searchable: false, responsivePriority: -1 }
            ]
        });

        // Expand/Collapse child row showing Santri list in Kamar DataTable
        $('#table-asrama tbody').on('click', '.btn-toggle-students', function () {
            var tr = $(this).closest('tr');
            var row = tableKamar.row(tr);
            var chevron = $(this).find('.btn-chevron');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                row.child(formatSantriList(row.data())).show();
                tr.addClass('shown');
                chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

        // Update URL state on Tab Switch
        $('#tab_link_officer').on('click', function() {
            history.pushState(null, '', "{{ route('officer.index') }}");
        });
        $('#tab_link_kamar').on('click', function() {
            history.pushState(null, '', "{{ route('asrama.index') }}");
        });
    });
</script>
@endpush
