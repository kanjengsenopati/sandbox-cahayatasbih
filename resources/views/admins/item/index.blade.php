@extends('layouts.master', ['title' => request('mode') === 'outlet' ? 'Manajemen Barang Outlet' : 'Manajemen Barang Kantin'])
@section('content')
@php
    $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
    $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
    if (request('mode') === 'outlet') {
        $modalOutlets = \App\Models\Outlet::where('is_active', 1)->where('id', '!=', $koperasiId)->get();
        $categories = \App\Models\CategoryItem::where(function($q) use ($koperasiId) {
            $q->where('outlet_id', '!=', $koperasiId)->orWhereNull('outlet_id');
        })->get();
    } else {
        $modalOutlets = \App\Models\Outlet::where('is_active', 1)->where('id', $koperasiId)->get();
        $categories = \App\Models\CategoryItem::where('outlet_id', $koperasiId)->get();
    }
    $modalItems = \App\Models\Item::when(auth()->user()->outlet_id, function($q) {
        $q->where('outlet_id', auth()->user()->outlet_id);
    })->when(!auth()->user()->outlet_id, function($q) use ($koperasiId) {
        if (request('mode') === 'outlet') {
            if (request()->filled('outlet_id')) {
                $q->where('outlet_id', request('outlet_id'));
            } else {
                $q->where('outlet_id', '!=', $koperasiId);
            }
        } else {
            $q->where('outlet_id', $koperasiId);
        }
    })->get();
@endphp

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    @if(request('mode') === 'outlet')
                        Manajemen Barang & Inventori Outlet
                    @else
                        Manajemen Barang & Inventori Kantin
                    @endif
                </h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('item.index', ['mode' => request('mode')]) }}" class="text-muted text-hover-primary">Barang</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        @if(request('mode') === 'outlet')
                            Barang & Stok Outlet
                        @else
                            Barang & Stok Kantin / Koperasi
                        @endif
                    </li>
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
                            @if(auth()->user()->can('Manage Barang') || auth()->user()->can('View Barang') || auth()->user()->isKasirOutlet())
                            <li class="nav-item">
                                <a class="nav-link active fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_barang">Data Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_kategori">Kategori Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bolder text-active-primary" data-bs-toggle="tab" href="#tab_stok">Inventori Barang</a>
                            </li>
                            @endif
                        </ul>
                        <!--end::Tabs Nav-->
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="tab-content" id="inventoryTabContent">
                        @if(auth()->user()->can('Manage Barang') || auth()->user()->can('View Barang') || auth()->user()->isKasirOutlet())
                        <!--begin::Tab Pane Data Barang-->
                        <div class="tab-pane fade show active" id="tab_barang" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="mb-0"></div>
                                <div class="gap-2 d-flex align-items-end">
                                    @if(auth()->user()->can('Create Barang') || auth()->user()->can('Manage Barang') || auth()->user()->isKasirOutlet())
                                    <button type="button" class="btn btn-primary btn-sm btn-add-item">
                                        <i class="fa fa-plus me-1"></i> Barang
                                    </button>
                                    @endif
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
                                    @if(auth()->user()->can('Create Barang') || auth()->user()->can('Manage Barang') || auth()->user()->isKasirOutlet())
                                    <button type="button" class="btn btn-primary btn-sm btn-add-category">
                                        <i class="fa fa-plus me-1"></i> Kategori
                                    </button>
                                    @endif
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
                                    @if(auth()->user()->can('Create Barang') || auth()->user()->can('Manage Barang') || auth()->user()->isKasirOutlet())
                                    <button type="button" class="btn btn-primary btn-sm btn-add-stock">
                                        <i class="fa fa-plus me-1"></i> Stok
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="table-stock-history" class="table align-middle table-row-dashed w-100">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-100px">Kode Barang</th>
                                            <th class="min-w-150px">Nama Barang</th>
                                            <th class="min-w-80px">Stok Saat Ini</th>
                                            <th class="min-w-100px">Stok Awal</th>
                                            <th class="min-w-100px">Admin</th>
                                            <th class="min-w-120px">Outlet</th>
                                            <th class="min-w-150px">Catatan / Alasan</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Tab Pane Stok-->
                        @endif
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

<!-- Modal Form Barang (Wide Modal xl - Compact 3 Column) -->
<div class="modal fade" id="modalItemForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-24">
            <form id="formItemModal" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div id="methodItemPut"></div>
                <div class="modal-header py-3 px-5 border-0">
                    <h5 class="modal-title fw-bolder fs-4" id="modalItemTitle">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2 px-5">
                    <div class="row g-3 align-items-start">
                        <!-- Column 1: Left - Foto Barang -->
                        <div class="col-md-3 text-center border-end pe-md-4 mb-3 mb-md-0">
                            <label class="form-label fs-7 fw-bold d-block text-muted text-uppercase tracking-wider mb-2">Foto Barang</label>
                            <div class="image-input image-input-outline my-2" data-kt-image-input="true">
                                <div class="image-input-wrapper w-140px h-140px rounded-16 shadow-sm" id="modal_item_image_preview" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')"></div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" title="Ubah Foto">
                                    <i class="bi bi-pencil-fill fs-7"></i>
                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                </label>
                            </div>
                            <small class="text-muted fs-8 d-block mt-1">Format: JPG, PNG (Maks 2MB)</small>
                        </div>

                        <!-- Column 2: Middle Inputs -->
                        <div class="col-md-4">
                            @if(!auth()->user()->outlet_id)
                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1 required" for="modal_item_outlet_id">Pilih Outlet</label>
                                <select name="outlet_id" id="modal_item_outlet_id" class="form-select form-select-solid form-select-sm" required>
                                    <option value="">Pilih Outlet...</option>
                                    @foreach($modalOutlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                                <input type="hidden" name="outlet_id" id="modal_item_outlet_id" value="{{ auth()->user()->outlet_id }}">
                            @endif

                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1 required" for="modal_item_category_id">Kategori Barang</label>
                                <select name="category_item_id" id="modal_item_category_id" class="form-select form-select-solid form-select-sm" required>
                                    <option value="">Pilih Kategori...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1 required" for="modal_item_name">Nama Barang</label>
                                <input type="text" name="name" id="modal_item_name" class="form-control form-control-solid form-control-sm" placeholder="Masukkan Nama Barang" required />
                            </div>

                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1 required" for="modal_item_selling_price">Harga Jual</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="text" name="selling_price" id="modal_item_selling_price" class="form-control form-control-solid input-money-modal" placeholder="Masukkan Harga Jual" required />
                                </div>
                            </div>
                        </div>

                        <!-- Column 3: Right Inputs -->
                        <div class="col-md-5">
                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1 required" for="modal_item_code">Kode Barang</label>
                                <input type="text" name="code" id="modal_item_code" class="form-control form-control-solid form-control-sm" placeholder="Masukkan Kode Barang" required />
                            </div>

                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1" for="modal_item_stock">Stok Barang</label>
                                <input type="text" id="modal_item_stock" class="form-control form-control-solid form-control-sm bg-light-secondary text-gray-700 fw-bold" value="0" readonly disabled />
                                <small class="text-muted fs-8 d-block mt-1">* Stok diisi & dikelola khusus via tab <b>Inventori Barang</b></small>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1 required" for="modal_item_price">Harga Beli</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="text" name="price" id="modal_item_price" class="form-control form-control-solid input-money-modal" placeholder="Masukkan Harga Beli" required />
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold mb-1" for="modal_item_profit">Keuntungan (Laba)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text fw-bold text-success">Rp</span>
                                    <input type="text" name="profit" id="modal_item_profit" class="form-control form-control-solid text-success fw-bold" placeholder="Keuntungan" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-5 border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Form Kategori Barang (Wide Modal lg - Compact) -->
<div class="modal fade" id="modalCategoryForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-24">
            <form id="formCategoryModal" method="POST" action="">
                @csrf
                <div id="methodCategoryPut"></div>
                <div class="modal-header py-3 px-5 border-0">
                    <h5 class="modal-title fw-bolder fs-4" id="modalCategoryTitle">Tambah Kategori Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3 px-5">
                    @if(!auth()->user()->outlet_id)
                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_cat_outlet_id">Pilih Outlet</label>
                        <select name="outlet_id" id="modal_cat_outlet_id" class="form-select form-select-solid form-select-sm" required>
                            <option value="">Pilih Outlet...</option>
                            @foreach($modalOutlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="outlet_id" id="modal_cat_outlet_id" value="{{ auth()->user()->outlet_id }}">
                    @endif

                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_cat_name">Nama Kategori</label>
                        <input type="text" name="name" id="modal_cat_name" class="form-control form-control-solid form-control-sm" placeholder="Nama Kategori" required />
                    </div>

                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_cat_code">Kode Kategori</label>
                        <input type="text" name="code" id="modal_cat_code" class="form-control form-control-solid form-control-sm" placeholder="Kode Kategori (Contoh: 01)" required />
                    </div>
                </div>
                <div class="modal-footer py-2 px-5 border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Form Stok (Wide/Compact Modal lg) -->
<div class="modal fade" id="modalStockForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-24">
            <form id="formStockModal" method="POST" action="">
                @csrf
                <div id="methodStockPut"></div>
                <div class="modal-header py-3 px-5 border-0">
                    <h5 class="modal-title fw-bolder fs-4" id="modalStockTitle">Tambah Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3 px-5">
                    @if(!auth()->user()->outlet_id)
                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_stock_outlet_id">Pilih Outlet</label>
                        <select name="outlet_id" id="modal_stock_outlet_id" class="form-select form-select-solid form-select-sm" required>
                            <option value="">Pilih Outlet...</option>
                            @foreach($modalOutlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="outlet_id" id="modal_stock_outlet_id" value="{{ auth()->user()->outlet_id }}">
                    @endif

                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_stock_type">Tipe Stok</label>
                        <select name="type" id="modal_stock_type" class="form-select form-select-solid form-select-sm" required>
                            <option value="IN">Stok Masuk (+)</option>
                            <option value="OUT">Stok Keluar (-)</option>
                            @if(request('mode') === 'outlet')
                                <option value="ADJUSTMENT">⚖️ Stok Opname (Physical Count / Koreksi)</option>
                            @endif
                        </select>
                    </div>

                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_stock_item_id">Nama Barang</label>
                        <select name="item_id" id="modal_stock_item_id" class="form-select form-select-solid form-select-sm" required>
                            <option value="">Pilih Barang...</option>
                            @foreach($modalItems as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} (Kode: {{ $item->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label required mb-1" for="modal_stock_quantity">Jumlah Stok</label>
                        <input type="number" name="quantity" id="modal_stock_quantity" class="form-control form-control-solid form-control-sm" placeholder="Jumlah" required />
                    </div>

                    <div class="fv-row mb-3">
                        <label class="fs-7 fw-bold form-label mb-1" for="modal_stock_notes">Catatan / Alasan Koreksi (Opsional)</label>
                        <input type="text" name="notes" id="modal_stock_notes" class="form-control form-control-solid form-control-sm" placeholder="Contoh: Stok Opname Fisik / Barang Expired" />
                    </div>
                </div>
                <div class="modal-footer py-2 px-5 border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(() => {
        // Calculate profit dynamically inside modal
        $(document).on('keyup change', '.input-money-modal', function() {
            var sellingPrice = parseInt($('#modal_item_selling_price').val().replace(/\D/g, ''), 10) || 0;
            var buyingPrice = parseInt($('#modal_item_price').val().replace(/\D/g, ''), 10) || 0;
            var profit = sellingPrice - buyingPrice;
            $('#modal_item_profit').val(profit > 0 ? profit.toLocaleString('id-ID') : 0);
        });

        // Click event: Add Item Modal
        $(document).on('click', '.btn-add-item', function() {
            $('#formItemModal')[0].reset();
            $('#methodItemPut').html('');
            $('#modalItemTitle').text('Tambah Barang');
            $('#formItemModal').attr('action', "{{ route('item.store', ['mode' => request('mode')]) }}");
            $('#modal_item_stock').val('0 (Stok Awal)');
            $('#modal_item_image_preview').css('background-image', "url('{{ asset('assets/media/svg/avatars/blank.svg') }}')");
            $('#modalItemForm').modal('show');
        });

        // Click event: Edit Item Modal (from tableItem or tableStock)
        $(document).on('click', '.btn-edit-item', function() {
            var btn = $(this);
            $('#formItemModal')[0].reset();
            $('#methodItemPut').html('<input type="hidden" name="_method" value="PUT">');
            $('#modalItemTitle').text('Edit Barang');
            $('#formItemModal').attr('action', btn.data('action'));
            
            $('#modal_item_name').val(btn.data('name'));
            $('#modal_item_code').val(btn.data('code'));
            $('#modal_item_category_id').val(btn.data('category_item_id'));
            $('#modal_item_price').val(btn.data('price'));
            $('#modal_item_selling_price').val(btn.data('selling_price'));
            $('#modal_item_profit').val(btn.data('profit'));
            $('#modal_item_stock').val(btn.data('stock'));
            $('#modal_item_outlet_id').val(btn.data('outlet_id'));
            
            if (btn.data('image')) {
                $('#modal_item_image_preview').css('background-image', "url('" + btn.data('image') + "')");
            } else {
                $('#modal_item_image_preview').css('background-image', "url('{{ asset('assets/media/svg/avatars/blank.svg') }}')");
            }
            
            $('#modalItemForm').modal('show');
        });

        // Click event: Add Category Modal
        $(document).on('click', '.btn-add-category', function() {
            $('#formCategoryModal')[0].reset();
            $('#methodCategoryPut').html('');
            $('#modalCategoryTitle').text('Tambah Kategori Barang');
            $('#formCategoryModal').attr('action', "{{ route('category-item.store', ['mode' => request('mode')]) }}");
            $('#modalCategoryForm').modal('show');
        });

        // Click event: Edit Category Modal
        $(document).on('click', '.btn-edit-category', function() {
            var btn = $(this);
            $('#formCategoryModal')[0].reset();
            $('#methodCategoryPut').html('<input type="hidden" name="_method" value="PUT">');
            $('#modalCategoryTitle').text('Edit Kategori Barang');
            $('#formCategoryModal').attr('action', btn.data('action'));
            
            $('#modal_cat_name').val(btn.data('name'));
            $('#modal_cat_code').val(btn.data('code'));
            $('#modal_cat_outlet_id').val(btn.data('outlet_id'));
            
            $('#modalCategoryForm').modal('show');
        });

        // Click event: Add Stock Modal
        $(document).on('click', '.btn-add-stock', function() {
            $('#formStockModal')[0].reset();
            $('#methodStockPut').html('');
            $('#modalStockTitle').text('Tambah Stok');
            $('#formStockModal').attr('action', "{{ route('stock-history.store', ['mode' => request('mode')]) }}");
            $('#modalStockForm').modal('show');
        });

        // Click event: Add Stock for specific Item
        $(document).on('click', '.btn-add-stock-item', function() {
            var btn = $(this);
            $('#formStockModal')[0].reset();
            $('#methodStockPut').html('');
            $('#modalStockTitle').text('Tambah / Opname Stok - ' + btn.data('item_name'));
            $('#formStockModal').attr('action', "{{ route('stock-history.store', ['mode' => request('mode')]) }}");
            $('#modal_stock_item_id').val(btn.data('item_id'));
            if (btn.data('outlet_id')) {
                $('#modal_stock_outlet_id').val(btn.data('outlet_id'));
            }
            $('#modalStockForm').modal('show');
        });

        // Click event: Edit Stock Modal
        $(document).on('click', '.btn-edit-stock', function() {
            var btn = $(this);
            $('#formStockModal')[0].reset();
            $('#methodStockPut').html('<input type="hidden" name="_method" value="PUT">');
            $('#modalStockTitle').text('Edit Stok');
            $('#formStockModal').attr('action', btn.data('action'));
            
            $('#modal_stock_type').val(btn.data('type'));
            $('#modal_stock_item_id').val(btn.data('item_id'));
            $('#modal_stock_quantity').val(btn.data('quantity'));
            $('#modal_stock_outlet_id').val(btn.data('outlet_id'));
            
            $('#modalStockForm').modal('show');
        });

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
                    render: function(data, type, row) {
                        var nameText = data ? data : 'N/A';
                        var fallbackImg = "{{ asset('assets/media/svg/avatars/blank.svg') }}";
                        var imgUrl = (row && row.image) ? "{{ asset('') }}" + row.image.replace(/^\//, '') : fallbackImg;

                        return `
                            <div class="d-flex flex-column align-items-start gap-1 py-1">
                                <span class="fw-bolder text-gray-800 fs-6 mb-1">${nameText}</span>
                                <div class="symbol symbol-45px rounded-12 overflow-hidden shadow-sm border border-gray-200">
                                    <img src="${imgUrl}" alt="${nameText}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 10px;" onerror="this.onerror=null;this.src='${fallbackImg}';" />
                                </div>
                            </div>
                        `;
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
                    render: function(data, type, row) {
                        var nameText = data ? data : 'N/A';
                        var fallbackImg = "{{ asset('assets/media/svg/avatars/blank.svg') }}";
                        var imgUrl = (row && row.image) ? "{{ asset('') }}" + row.image.replace(/^\//, '') : fallbackImg;

                        return `
                            <div class="d-flex flex-column align-items-start gap-1 py-1">
                                <span class="fw-bolder text-gray-800 fs-6 mb-1">${nameText}</span>
                                <div class="symbol symbol-45px rounded-12 overflow-hidden shadow-sm border border-gray-200">
                                    <img src="${imgUrl}" alt="${nameText}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 10px;" onerror="this.onerror=null;this.src='${fallbackImg}';" />
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'current_stock',
                    name: 'current_stock',
                    render: function(data) {
                        return `<span class="fw-bold fs-6 text-gray-800">${data !== undefined ? data : 0}</span>`;
                    }
                },
                {
                    data: 'initial_stock',
                    name: 'initial_stock',
                    render: function(data) {
                        return `<span class="text-gray-600 fw-bold fs-7">${data ? data : 'Stok Awal: 0'}</span>`;
                    }
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
                    data: 'notes',
                    name: 'notes',
                    render: function(data) {
                        return data ? data : '-';
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