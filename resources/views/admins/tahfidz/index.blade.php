@extends('layouts.master', ['title' => 'Data Tahfidz'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Tahfidz</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('tahfidz.index') }}" class="text-muted text-hover-primary">Tahfidz</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List Tahfidz</li>
                    <!--end::Item-->

                </ul>
                <!--end::Breadcrumb-->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-beetween gap-5">
                        <div class="mb-0">

                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">
                        <x-action.create name="Tahfidz" action="{{ route('tahfidz.create') }}" />
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-tahfidz" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th width="3%">No</th>
                                    <th>Tanggal</th>
                                    <th>Santri</th>
                                    <th>Jumlah Halaman</th>
                                    <th>Keterangan</th>
                                    <th>Feedback</th>
                                    <th>Link Video</th>
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
            <!--begin::Modals-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')

<script>
    $(document).ready(() => {
    var table = $('#table-tahfidz').DataTable({
    ordering: false,
    processing: true,
    serverSide: true,
    ajax: "{{ route('tahfidz.index') }}",
    language: {
    "paginate": {
    "next": "<i class='fa fa-angle-right'>",
        "previous": "<i class='fa fa-angle-left'>"
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
                data: 'deposit_date',
                name: 'deposit_date'
            },
            {
                data: 'student.name',
                name: 'student.name'
            },
            {
                data: 'number_of_pages',
                name: 'number_of_pages'
            },
            {
                data: 'note',
                name: 'note'
            },
            {
                data: 'feedback',
                name: 'feedback'
            },
            {
                data: 'link',
                name: 'link'
            },
            {
            data: 'action',
            name: 'action',
            orderable: true,
            searchable: true,
            responsivePriority: -1,
            },
            ]
            });
                
                })
</script>
@endpush