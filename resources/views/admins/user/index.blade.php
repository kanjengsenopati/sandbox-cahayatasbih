@extends('layouts.master', ['title' => 'Data User'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Daftar User</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('user.index') }}" class="text-muted text-hover-primary">User</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List User</li>
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
                        <x-action.import target="#modalImport" name="Wali Santri" />
                        <div class="mb-0">

                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">

                        <x-action.create name="Wali Santri" action="{{ route('user.create') }}" />
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-user" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th width="3%">No</th>
                                    <th class="w-10px pe-2">Avatar</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No HP</th>
                                    <th>Gender</th>
                                    <th>Status</th>
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
<!--end::Content-->
<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Data Wali Santri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel</label>
                        <input class="form-control" type="file" name="file" id="file">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <a href="assets\media\template\import\Template Import Data Wali Santri.xlsx"
                            class="btn btn-light-primary"><i class="fa fa-download"></i> Template</a>
                    </div>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        var table = $('#table-user').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            searchable: true,
            ajax: "{{ route('user.index') }}",
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
                    data: 'avatar',
                    name: 'avatar',
                    render: function(data, type, row) {
                        if (data == null) {
                            return `<span class="symbol-label fs-2x fw-bold text-primary bg-light-primary">${row.name ? row.name.charAt(0) : 'N/A'}</span>`;
                        } else {
                            return `<img src="${data}" alt="image" class="h-50px w-50px rounded-circle" />`;
                        }
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                    }
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                    }
                },
                {
                    data: 'gender',
                    name: 'gender',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        let label = '';
                        
                        if (data == 'L') {
                            badgeClass = 'badge-light-primary';
                            label = 'Laki-laki';
                        } else if (data == 'P') {
                            badgeClass = 'badge-light-danger';
                            label = 'Perempuan';
                        } else {
                            badgeClass = 'badge-light-warning';
                            label = 'Tidak diketahui';
                        }
                        
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    },
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    searchable: false,
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        let badgeClass = '';
                        let label = '';
                        if (data == true) {
                            badgeClass = 'badge-light-success';
                            label = 'Aktif';
                        } else {
                            badgeClass = 'badge-light-danger';
                            label = 'Nonaktif';
                        }
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    },
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'No actions available'; // Null handler
                    }
                },
            ]
        });
    })
</script>
@endpush