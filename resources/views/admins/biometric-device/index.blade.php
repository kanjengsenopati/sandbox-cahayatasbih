@extends('layouts.master', ['title' => 'Mesin Biometrik'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Mesin Biometrik</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Pengaturan</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Mesin Biometrik</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            @include('admins.partials.tabs-aplikasi')
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <div class="card-title"></div>
                    <x-action.create name="Biometric" action="{{ route('biometric-device.create') }}" />
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-biometric-device" class="table align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama Perangkat</th>
                                    <th>IP Address</th>
                                    <th>Lokasi</th>
                                    <th>Auth Token</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        var table = $('#table-biometric-device').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('biometric-device.index') }}",
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
                {
                    data: 'device_name',
                    name: 'device_name'
                },
                {
                    data: 'device_ip',
                    name: 'device_ip',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'location',
                    name: 'location'
                },
                {
                    data: 'auth_token',
                    name: 'auth_token',
                    render: function(data) {
                        return '<code>' + data + '</code>';
                    }
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
    });
</script>
@endpush
