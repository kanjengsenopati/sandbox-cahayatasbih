@extends('layouts.master', ['title' => 'Manajemen Barang'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Manajemen Barang</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('item.index') }}" class="text-muted text-hover-primary">Barang</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Manajemen Barang & Stok</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                @include('layouts.partials.outlet_switcher')
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl px-5">
            <!--begin::Card-->
            <div class="card premium-card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-2 pb-5">
                    <div class="card-title">
                        <!--begin::Tabs Nav-->
                        <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                            @can('Manage Barang')
                            <li class="nav-item">
                                <a class="nav-link active fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_barang">Data Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_kategori">Kategori Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_stok">Inventori Barang</a>
                            </li>
                            @endcan
                        </ul>
                        <!--end::Tabs Nav-->
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="tab-content" id="inventoryTabContent">
                        @can('Manage Barang')
                        <!--begin::Tab Pane Data Barang-->
                        <div class="tab-pane fade show active" id="tab_barang" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="mb-0">
                                    @can('Create Barang')
                                    <x-action.import target="#modalImport" name="Barang" />
                                    @endcan
                                </div>
                                <div class="gap-2 d-flex align-items-end">
                                    @can('Create Barang')
                                    <x-action.create name="Barang" action="{{ route('item.create', ['mode' => request('mode')]) }}" />
                                    @endcan
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="table-item" class="table align-middle table-row-dashed w-100">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-100px">Kode Barang</th>
                                            <th class="min-w-150px">Nama Barang</th>
                                            <th class="min-w-100px">Harga</th>
                                            <th class="min-w-80px">Stok</th>
                                            <th class="min-w-100px">Outlet</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Tab Pane Data Barang-->

                        <!--begin::Tab Pane Kategori-->
                        <div class="tab-pane fade" id="tab_kategori" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div></div>
                                <div class="gap-2 d-flex align-items-end">
                                    @can('Create Barang')
                                    <x-action.create name="Barang" label="Kategori" action="{{ route('category-item.create', ['mode' => request('mode')]) }}" />
                                    @endcan
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="table-category-item" class="table align-middle table-row-dashed w-100">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-150px">Kode</th>
                                            <th>Nama</th>
                                            <th class="min-w-150px">Outlet</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Tab Pane Kategori-->

                        <!--begin::Tab Pane Stok-->
                        <div class="tab-pane fade" id="tab_stok" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div></div>
                                <div class="gap-2 d-flex align-items-end">
                                    @can('Create Barang')
                                    <x-action.create name="Barang" label="Stok" action="{{ route('stock-history.create', ['mode' => request('mode')]) }}" />
                                    @endcan
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="table-stock-history" class="table align-middle table-row-dashed w-100">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-100px">Kategori</th>
                                            <th class="min-w-100px">Kode Barang</th>
                                            <th class="min-w-150px">Nama Barang</th>
                                            <th class="min-w-80px">Jumlah</th>
                                            <th class="min-w-100px">Admin</th>
                                            <th class="min-w-150px">Outlet</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Tab Pane Stok-->
                        @endcan
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Post-->
</div>

<!-- Modal Import Barang -->
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
                        <input class="form-control" type="file" name="file" id="file" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <a href="assets/media/template/import/Template Import Data Barang.xlsx"
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
        // Init DataTable Barang
        var tableItem = $('#table-item').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('item.index', ['mode' => request('mode'), 'outlet_id' => request('outlet_id')]) }}",
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            searchDelay: 500,
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
                    data: 'code',
                    name: 'code',
                    render: function(data) {
                        return data ? data : 'N/A';
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data) {
                        return data ? data : 'N/A';
                    }
                },
                {
                    data: 'selling_price',
                    name: 'selling_price',
                    render: function(data, type, row) {
                        var sellingPriceFormatted = data ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data) : 'N/A';
                        var priceFormatted = row.price ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.price) : 'N/A';
                        var profitFormatted = row.profit ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(row.profit) : 'N/A';
                        
                        return `<small><i>Harga Jual: ${sellingPriceFormatted}
                                <hr class="my-1">
                                Harga Beli: ${priceFormatted}
                                <hr class="my-1">
                                Laba: ${profitFormatted}
                            </i></small>`;
                    }
                },
                {
                    data: 'stock',
                    name: 'stock',
                    render: function(data) {
                        return data !== null ? data : 'N/A';
                    }
                },
                {
                    data: 'outlet',
                    name: 'outlet',
                    render: function(data) {
                        return data ? data : 'N/A';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data ? data : 'N/A';
                    }
                },
            ]
        });

        // Init DataTable Kategori
        var tableCategory = $('#table-category-item').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('category-item.index', ['mode' => request('mode'), 'outlet_id' => request('outlet_id')]) }}",
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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'outlet',
                    name: 'outlet',
                    render: function(data) {
                        return data ? data : 'N/A';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Init DataTable Riwayat Stok
        var tableStock = $('#table-stock-history').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('stock-history.index', ['mode' => request('mode'), 'outlet_id' => request('outlet_id')]) }}",
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
                    data: 'item_category',
                    name: 'item_category'
                },
                {
                    data: 'item.code',
                    name: 'item.code'
                },
                {
                    data: 'item.name',
                    name: 'item.name'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'admin',
                    name: 'admin'
                },
                {
                    data: 'outlet',
                    name: 'outlet',
                    render: function(data) {
                        return data ? data : 'N/A';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Refresh DataTables when clicking tabs to ensure correct formatting and sizing
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if (target === "#tab_barang") {
                tableItem.columns.adjust().draw();
            } else if (target === "#tab_kategori") {
                tableCategory.columns.adjust().draw();
            } else if (target === "#tab_stok") {
                tableStock.columns.adjust().draw();
            }
        });
    });
</script>
@endpush