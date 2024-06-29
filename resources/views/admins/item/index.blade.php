@extends('layouts.master', ['title' => 'Data Barang'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Barang</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('item.index') }}" class="text-muted text-hover-primary">Barang</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List Barang</li>
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
            <div class="card">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-beetween gap-5">
                        <div class="mb-0">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalImport">
                                <i class="fa fa-upload"></i> Import Data
                            </button>
                        </div>
                        <div class="mb-0">

                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">
                        <div>

                        </div>
                        <div>
                            <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission"
                                href="{{ route('item.create') }}"><i class="fa fa-plus"></i> Barang</a>
                        </div>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-item" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th class="min-w-100px" style="width: 22%">Kategori</th>
                                    <th class="min-w-100px" style="width: 22%">Kode Barang</th>
                                    <th class="min-w-100px" style="width: 22%">Nama Barang</th>
                                    <th class="min-w-100px" style="width: 22%">Harga</th>
                                    <th class="min-w-100px" style="width: 22%">Stok</th>
                                    <th class="min-w-100px" style="width: 22%">Status</th>
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
            <!--end::Card-->
            <!--begin::Modals-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('item.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Data Barang</h5>
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
                        <a href="assets\media\template\import\Template Import Data Barang.xlsx"
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
            var table = $('#table-item').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('item.index') }}",
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
                        data: 'category_item.name',
                        name: 'category_item.name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'code',
                        name: 'code',
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
                    data: 'selling_price',
                    name: 'selling_price',
                    render: function (data, type, row, meta) {
                    var sellingPriceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data);
                    var priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.price);
                    var profitFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.profit);
                    
                    return `<small><i>Harga Jual: ${sellingPriceFormatted}
                            <hr>
                            Harga Beli: ${priceFormatted}
                            <hr>
                            Laba: ${profitFormatted}
                            <hr>
                        </i></small>`;
                    }
                    },
                    {
                        data: 'stock',
                        name: 'stock',
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
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                ]
            });

        })
</script>
@endpush