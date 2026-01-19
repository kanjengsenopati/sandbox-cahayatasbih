@extends('layouts.master', ['title' => 'Data Barang'])
@section('content')
<!--begin::Content-->
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

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('item.index') }}">
                        <li class="text-muted">
                            Barang
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('item.create') ? 'Tambah Barang' : 'Edit Barang' }}</li>
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

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-5 g-lg-7 justify-content-center">
                <!--begin::Content-->
                <div class="col-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header pt-7 d-flex align-items-center justify-content-center">
                            <!--begin::Card title-->
                            <div class="card-title">
                                {{-- <div class="animate__animated animate__bounceInDown">
                                    <img src="https://img.freepik.com/free-vector/empty-concept-illustration_114360-1233.jpg?w=740&t=st=1705259211~exp=1705259811~hmac=f2907f9954f384beef7b38ff0c03ce8350bd63a41e67dfef7bff363943528a62"
                                        alt="Category Item Image" class="img-fluid rounded"
                                        style="max-width: 200px; height: auto;">
                                </div> --}}
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="item"
                                action="{{ request()->routeIs('item.create') ? route('item.store') : route('item.update', @$item->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <div class="col-12">
                                    <div class="d-flex justify-content-center">
                                        <x-form.image-upload label="Foto Barang (Jika Ada)" name="image"
                                            :value="@$item->image ?? null" />
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <!-- Category Item -->
                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="category_item_id">Kategori
                                                Barang</label>
                                            <x-form.category-item :value="@$item->category_item_id"
                                                class="form-control form-control-solid" />
                                        </div>
                                        <!-- Kode Barang -->
                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="name">Nama Barang</label>
                                            <input type="text" name="name" id="name"
                                                class="form-control form-control-solid"
                                                placeholder="Masukkan Nama Barang"
                                                value="{{ old('name') ?? @$item->name }}" required />

                                        </div>
                                        <!-- Nama Barang -->
                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="selling_price">Harga
                                                Jual</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" name="selling_price" id="selling_price"
                                                    class="form-control form-control-solid input-money"
                                                    placeholder="Masukkan Harga Jual"
                                                    value="{{ old('selling_price') ?? @$item->selling_price }}"
                                                    required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Harga Beli -->
                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="code">Kode Barang</label>
                                            <input type="text" name="code" id="code"
                                                class="form-control form-control-solid"
                                                placeholder="Masukkan Kode Barang"
                                                value="{{ old('code') ?? @$item->code }}" required />

                                        </div>
                                        <!-- Harga Jual -->
                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="stock">Stok Barang</label>
                                            <input type="text" name="stock" id="stock"
                                                class="form-control form-control-solid"
                                                placeholder="Masukkan Stok Barang"
                                                value="{{ old('stock') ?? @$item->stock }}" required />

                                        </div>
                                        <!-- Keuntungan -->
                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="price">Harga Beli</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" name="price" id="price"
                                                    class="form-control form-control-solid input-money"
                                                    placeholder="Masukkan Harga Beli"
                                                    value="{{ old('price') ?? @$item->price }}" required />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fs-6 fw-bold" for="profit">Keuntungan</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" name="profit" id="profit"
                                                    class="form-control form-control-solid input-money"
                                                    placeholder="Keuntungan"
                                                    value="{{ old('profit') ?? @$item->profit }}" readonly />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <!-- Action buttons -->
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('item.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Contacts-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Contacts App- Add New Contact-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(".input-money").on('keyup', function() {
        var sellingPrice = parseInt($('#selling_price').val().replace(/\D/g, ''), 10) || 0;
        var buyingPrice = parseInt($('#price').val().replace(/\D/g, ''), 10) || 0;
        
        // Calculate profit
        var profit = sellingPrice - buyingPrice;

        // Display profit
        $('#profit').val(profit > 0 ? profit.toLocaleString() : 0);
    });
</script>
@endpush