@extends('layouts.master', ['title' => 'PPDB - Sekolah'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">PPDB</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('ppdb.index') }}" class="text-muted text-hover-primary">Data PPDB</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">PPDB</li>
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

            <!--end::Card-->
            <!--begin::Modals-->

            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="text-dark">Data PPDB</h3>
                    </div>
                    <div class="">
                        <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission"
                            href="{{ route('ppdb.create') }}">+
                            PPDB</a>
                        <!--end::Primary button-->
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-ppdb" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Jalur PPDB</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Sekolah</th>
                                    <th>Kouta</th>
                                    <th>Tgl Mulai</th>
                                    <th>Tgl Selesai</th>
                                    <th>Status</th>
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

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        var tablePPdbType = $('#table-ppdb').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('ppdb.index') }}",
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'></i>",
                    "previous": "<i class='fa fa-angle-left'></i>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'image',
                    name: 'image',
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'ppdb_type.name'
                },
                {
                    data: 'academic_year.name'
                },
                {
                    data: 'school.name'
                },
                {
                    data: 'capacity',
                    name: 'capacity'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true
                },
            ]
        });

        // var tablePPdb = $('#table-ppdb').DataTable({
        //     ordering: false,
        //     processing: true,
        //     serverSide: true,
        //     ajax: "{{ route('ppdb.index') }}",
        //     language: {
        //         "paginate": {
        //             "next": "<i class='fa fa-angle-right'></i>",
        //             "previous": "<i class='fa fa-angle-left'></i>"
        //         },
        //         "loadingRecords": "Loading...",
        //         "processing": "Processing...",
        //     },
        //     columns: [{
        //             "data": null,
        //             "sortable": false,
        //             "searchable": false,
        //             render: function(data, type, row, meta) {
        //                 return meta.row + meta.settings._iDisplayStart + 1;
        //             }
        //         },
        //         {
        //             data: 'name',
        //             name: 'name'
        //         },
        //         {
        //             data: 'action',
        //             name: 'action',
        //             orderable: true,
        //             searchable: true
        //         },
        //     ]
        // });
    });
</script>
@endpush