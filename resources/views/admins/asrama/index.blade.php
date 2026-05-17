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
                                    <th>Ustadz / Host Pembina</th>
                                    <th>No WhatsApp Host</th>
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
    $(document).ready(() => {
        $('#table-asrama').DataTable({
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
    });
</script>
@endpush
