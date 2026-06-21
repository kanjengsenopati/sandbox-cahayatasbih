@extends('layouts.master', ['title' => 'Data Karyawan'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Karyawan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Pondok Mart (Outlet)</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Data Karyawan</li>
                </ul>
                <!--end::Breadcrumb-->
            </div>
            @include('layouts.partials.outlet_switcher')
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl px-5">
            <!--begin::Card-->
            <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]" style="border-radius: 24px; border: none;">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="text-dark">Data Karyawan</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('karyawan.create', request()->only(['mode', 'outlet_id'])) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Karyawan
                        </a>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-karyawan" class="table align-middle table-row-dashed gy-5 gs-7">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama Karyawan</th>
                                    <th>Kamar</th>
                                    <th>Jabatan</th>
                                    <th>Section</th>
                                    <th>Gaji / Bulan</th>
                                    <th>Gaji / Hari</th>
                                    <th>Hari Kerja</th>
                                    <th>Outlet</th>
                                    <th class="text-center min-w-100px" style="width: 15%">Aksi</th>
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
<script>
    $(document).ready(() => {
        var table = $('#table-karyawan').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('karyawan.index') }}",
                data: function(d) {
                    d.mode = "{{ request('mode') }}";
                    d.outlet_id = "{{ request('outlet_id') }}";
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'employee_name', name: 'admin.name' },
                { data: 'kamar', name: 'kamar' },
                { data: 'jabatan', name: 'jabatan' },
                { data: 'section', name: 'section' },
                { data: 'gaji_bulan_formatted', name: 'gaji_bulan' },
                { data: 'gaji_hari_formatted', name: 'gaji_hari' },
                { data: 'hari_kerja', name: 'hari_kerja' },
                { data: 'outlet_name', name: 'outlet.name' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
    });
</script>
@endpush
