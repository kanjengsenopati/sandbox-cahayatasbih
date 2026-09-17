@extends('layouts.master', ['title' => 'Tambah Pembelian', 'sidebar' => 'on'])
@push('css')
<style>
    /* Loader styles */
    #page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        display: none;
    }

    .loader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Typography System wrapper simulation */
    .text-h1 {
        font-size: 22px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }
    .text-h2 {
        font-size: 16px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
    }
    .text-amount {
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #059669 !important;
    }
    .text-label {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        color: #94a3b8 !important;
    }
    .text-body {
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #475569 !important;
    }
    .text-caption {
        font-size: 12px !important;
        font-weight: 400 !important;
        font-style: italic !important;
        color: #94a3b8 !important;
    }

    /* Product Grid Card styles (borderless cards) */
    .product-card {
        border-radius: 16px !important;
        background-color: #ffffff;
        border: none !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        overflow: hidden;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1) !important;
    }
    .product-image {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }
    .product-info {
        padding: 12px !important;
    }
</style>
@endpush
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
                        <a href="{{ route('item.index') }}" class="text-muted text-hover-primary">Transaksi</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Tambah Pembelian</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                @include('layouts.partials.outlet_switcher')
            </div>
            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Form-->
            <form id="form-payment" class="form d-flex flex-column flex-lg-row" method="post"
                action="{{ route('order-item.store', ['mode' => request('mode'), 'outlet_id' => request('outlet_id')]) }}">
                @csrf
                <!--begin::Aside column-->
                <div class="w-100 flex-lg-row-auto w-lg-300px mb-7 me-7 me-lg-10">
                    <!--begin::Order details-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h3>Pembayaran</h3>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <x-alert.alert-validation />
                            <!--begin::Tabs-->
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="santri-tab" data-bs-toggle="tab"
                                        data-bs-target="#santri" type="button" role="tab" aria-controls="santri"
                                        aria-selected="true">Santri</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="umum-tab" data-bs-toggle="tab" data-bs-target="#umum"
                                        type="button" role="tab" aria-controls="umum"
                                        aria-selected="false">Umum</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="santri" role="tabpanel"
                                    aria-labelledby="santri-tab">
                                    <div class="d-flex flex-column gap-6 mt-4">
                                        <!--begin::Input group-->
                                        <div class="fv-row" id="scan-card-group">
                                            <!--begin::Label-->
                                            <label class="form-label">Scan Kartu Santri</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-id-card"></i>
                                                </span>
                                                <input class="form-control form-control-solid" name="scan-card"
                                                    placeholder="Masukkan ID Kartu Santri" type="password"
                                                    id="scan-card" />
                                            </div>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="fv-row" id="student-name-group">
                                            <!--begin::Student Info-->
                                            <label class="form-label">Nama Santri</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" class="form-control form-control-solid"
                                                name="student_name" id="student-name" placeholder="Nama Santri"
                                                disabled />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                        <div class="fv-row d-flex gap-3">
                                            <!--begin::Label-->
                                            <div class="col-6">
                                                <label class="form-label">Saldo</label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="text" class="form-control form-control-solid" name="saldo"
                                                    id="saldo" placeholder="Saldo" disabled />
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Sisa</label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="text" class="form-control form-control-solid"
                                                    name="remaining-saldo" id="remaining-saldo" placeholder="Kembalian"
                                                    disabled />
                                            </div>
                                            <!--end::Input-->
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="umum" role="tabpanel" aria-labelledby="umum-tab">
                                </div>
                            </div>
                            <!--end::Tabs-->

                            <!-- Konten di luar tab -->
                            <div class="d-flex flex-column gap-6 mt-4">
                                <div class="fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">Pembayaran</label>
                                    <input class="form-control" name="payment_method" type="text" id="payment_method"
                                        value="Saldo" readonly>
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">Total Pembayaran</label>
                                    <input class="form-control" type="text" id="total-price" value="Rp. 0"
                                        aria-label="Total Pembayaran" disabled readonly>
                                    <!--end::Input-->
                                </div>
                                @if (Auth::user()->can('Create Pos Kasir'))
                                <div class="d-flex justify-content-center">
                                    <button type="submit" id="btn-bayar" class="btn btn-primary mt-3 w-100">
                                        <span class="indicator-label">Bayar</span>
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Order details-->
                </div>
                <!--end::Aside column-->
                <!--begin::Main column-->
                <div class="d-flex flex-column flex-lg-row-fluid gap-7 gap-lg-10">
                    <!--begin::Order details-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="card-title">
                                        <h2>Keranjang</h2>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <button type="button" class="btn btn-danger btn-sm me-2"
                                            onclick="deleteAllProductFromCart()">
                                            <i class="fas fa-trash"></i> Bersihkan Keranjang
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="refreshProductList()">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <!-- Kiri: Input dan Button Cari Barang -->
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Input untuk Kode Barang -->
                                    <div class="position-relative">
                                        <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                                    rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                                                <path
                                                    d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                    fill="currentColor" />
                                            </svg>
                                        </span>
                                        <input type="text" id="search-product"
                                            class="form-control form-control-solid ps-14"
                                            placeholder="Masukkan Kode Barang" />
                                    </div>
                                    <!-- Button Cari Barang -->
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalListProduct">
                                        <i class="fas fa-search"></i>
                                        Cari Barang
                                    </button>
                                </div>

                                <!-- Kanan: Informasi Kasir -->
                                <div class="d-flex align-items-center">
                                    <!-- Detail Kasir -->
                                    <div class="text-end">
                                        <h5 class="fw-bold mb-1">{{ Auth::user()->name ?? 'Nama Kasir' }}</h5>
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalLihatTransaksi" onclick="fetchTransactionHistory()">
                                            Lihat Transaksi
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Separator -->
                            <div class="separator"></div>

                            <!-- Tabel Produk -->
                            <div class="table-responsive mt-4">
                                <table class="table">
                                    <thead>
                                        <tr class="fw-bold fs-6 text-gray-800">
                                            <th class="min-w-150px">Nama Barang</th>
                                            <th class="min-w-100px">Jumlah</th>
                                            <th class="min-w-100px">Harga</th>
                                            <th class="min-w-100px">Total Harga</th>
                                            <th class="min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="list-product">
                                        <div id="product-loader"
                                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.5); display: none;">
                                            <div class="loader text-center"
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <div>Mohon tunggu ...</div>
                                            </div>
                                        </div>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal untuk Lihat Transaksi -->
                        <div class="modal fade" id="modalLihatTransaksi" tabindex="-1"
                            aria-labelledby="modalLihatTransaksiLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLihatTransaksiLabel">Transaksi Hari Ini
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Loader -->
                                        <div id="transaction-loader" style="display: none; text-align: center;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p>Memuat data transaksi...</p>
                                        </div>
                                        <!-- Tabel Riwayat Transaksi -->
                                        <div class="table-responsive">
                                            <table class="table" id="transaction-table" style="display: none;">
                                                <thead>
                                                    <tr class="fw-bold fs-6 text-gray-800">
                                                        <th>No</th>
                                                        <th class="min-w-100px">Tanggal</th>
                                                        <th class="min-w-100px">Pembeli</th>
                                                        <th class="min-w-100px">Item</th>
                                                        <th class="min-w-100px">Jumlah</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="transaction-table-body">
                                                    <!-- Data akan diisi secara dinamis -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="card-body pt-0">
                            <div class="d-flex flex-column gap-10">
                                <!--begin::Input group-->
                                <div>
                                    <!--begin::Label-->
                                    <label class="form-label">Kasir : {{ Auth::user()->name ?? '' }}</label>
                                    <!--end::Label-->
                                    <!--begin::Search inputs-->
                                    <div class="row mb-3">
                                        <!-- Input for searching by product code -->
                                        <div class="col d-flex align-items-center position-relative mb-n7">
                                            <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none">
                                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546"
                                                        height="2" rx="1" transform="rotate(45 17.0365 15.1223)"
                                                        fill="currentColor" />
                                                    <path
                                                        d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                        fill="currentColor" />
                                                </svg>
                                            </span>
                                            <input type="text" data-kt-ecommerce-edit-order-filter="search"
                                                id="search-product"
                                                class="form-control form-control-solid w-100 w-lg-70 ps-14"
                                                placeholder="Masukkan Kode Barang" />
                                        </div>
                                        <!-- Input for searching by product name -->
                                        <div class="col d-flex align-items-center position-relative mb-n7">
                                            <span class="svg-icon svg-icon-1 position-absolute ms-4">

                                            </span>

                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#modalListProduct">
                                                <i class="fas fa-search"></i>
                                                Cari Barang
                                            </button>
                                        </div>
                                    </div>
                                    <!--end::Search inputs-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator"></div>
                                <!--end::Separator-->
                                <!--begin::Search products-->

                                <!--begin::Table-->
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr class="fw-bold fs-6 text-gray-800">
                                                <th class="min-w-150px">Nama Barang</th>
                                                <th class="min-w-100px">Jumlah</th>
                                                <th class="min-w-100px">Harga</th>
                                                <th class="min-w-100px">Total Harga</th>
                                                <th class="min-w-100px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="list-product">
                                            <div id="product-loader"
                                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.5); display: none;">
                                                <div class="loader text-center"
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <div>Mohon tunggu ...</div>
                                                </div>
                                            </div>
                                            <!--begin::Table row-->
                                            <!--end::Table row-->
                                        </tbody>
                                    </table>
                                </div>
                                <!--end::Table-->
                            </div>
                        </div> --}}
                        <!--end::Card header-->
                    </div>
                    <!--end::Order details-->

                    <!--begin::Card Katalog Produk-->
                    <div class="card card-flush py-4 mt-6" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04);">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div class="card-title">
                                    <h2 class="text-h2 m-0">Katalog Barang</h2>
                                </div>
                                <div class="card-toolbar">
                                    <div class="position-relative">
                                        <span class="svg-icon svg-icon-1 position-absolute ms-4" style="top: 50%; transform: translateY(-50%);">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </span>
                                        <input type="text" id="grid-search-product" class="form-control form-control-solid ps-12 w-200px w-md-250px" placeholder="Cari nama barang..." style="border-radius: 12px;" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <!-- Loader -->
                            <div id="grid-loader" class="text-center py-5" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="text-muted mt-2">Memuat katalog barang...</div>
                            </div>
                            
                            <!-- Grid Container -->
                            <div id="grid-product-list" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 overflow-y-auto pt-3" style="max-height: 450px;">
                                <!-- Diberdayakan secara dinamis via AJAX -->
                            </div>
                        </div>
                    </div>
                    <!--end::Card Katalog Produk-->
                </div>
                <!--end::Main column-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Container-->
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!-- Modal -->
<div class="modal fade" id="modalListProduct" tabindex="-1" aria-labelledby="modalListProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalListProductLabel">List Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-5">
                    <div class="col d-flex align-items-center position-relative mb-n7">
                        <span class="svg-icon svg-icon-1 position-absolute ms-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                    transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                                <path
                                    d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        <input type="text" data-kt-ecommerce-edit-order-filter="search" id="search-product-name"
                            class="form-control form-control-solid w-100 w-lg-70 ps-14"
                            placeholder="Masukkan Nama Barang" />
                    </div>
                </div>
                <div id="info-search-product-name"
                    class="row row-cols-1 row-cols-xl-3 row-cols-md-2 border border-dashed rounded pt-3 pb-1 px-2 mb-5 mh-300px overflow-scroll">
                    <!--begin::Empty message-->
                    <span class="w-100 text-muted">Cari barang berdasarkan nama pada kolom di atas dan enter untuk
                        mencari</span>
                    <!--end::Empty message-->
                </div>
                <div>
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th class="min-w-200px" style="min-width: 25%">Nama Barang</th>
                                <th>Harga</th>
                                <th class="w-25px">Aksi</th>
                            </tr>
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody class="fw-bold text-gray-600" id="list-product-name">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    var defaultImageUrl = "{{ asset('assets/media/logos/logo.png') }}";
    var number = 1;
    var totalPrice = 0;
    var requestMode = "{{ request('mode') }}";
    var requestOutletId = "{{ request('outlet_id') }}";

    window.addEventListener('DOMContentLoaded', function () {
        focusOnFirstInput();
        refreshProductList();
        loadProductCatalog();

        var searchProductInput = document.getElementById('search-product');
        if (searchProductInput) {
            searchProductInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
            e.preventDefault(); // Prevent the default action (form submission)
            searchProductByCode(e);
            }
            });
        }

        var gridSearchInput = document.getElementById('grid-search-product');
        if (gridSearchInput) {
            var debounceTimeout = null;
            gridSearchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(function() {
                    loadProductCatalog(e.target.value);
                }, 300);
            });
        }
    });

    function focusOnFirstInput() {
        var searchProductInput = document.getElementById('search-product');
        if (searchProductInput) {
            searchProductInput.focus();
        }
    }

    $('#modalListProduct').on('shown.bs.modal', function () {
     getListProduct();
    });

    function getListProduct() {
        axios.post("{{ route('item.search-item') }}", {
            mode: requestMode,
            outlet_id: requestOutletId
        }).then(handleListProductResponse)
        .catch(handleError);
    }

    function handleListProductResponse(response) {
        var products = response.data.data;
        var listProduct = document.getElementById('list-product-name');
        listProduct.innerHTML = '';

        if (products && products.length > 0) {
            products.forEach(function (product, index) {
               var tr = document.createElement('tr');
                tr.innerHTML = `
                <div class="d-flex align-items-center" data-kt-ecommerce-edit-order-filter="product"
                    data-kt-ecommerce-edit-order-id="product_${product.id}">
                    <a class="symbol symbol-50px">
                        <span class="symbol-label" style="background-image:url(${product.image || defaultImageUrl})"></span>
                    </a>
                    <div class="ms-5">
                        <a class="text-gray-800 text-hover-primary fs-5 fw-bolder">${product.name}</a>
                        <div class="text-muted fs-7">Stok: ${product.stock}</div>
                    </div>
                </div>`;
                listProduct.appendChild(tr);
                var tdPrice = document.createElement('td');
                tdPrice.textContent = `Rp. ${product.selling_price.toLocaleString('id-ID')}`;
                tr.appendChild(tdPrice);
                
                // Create td for action button
                var tdAction = document.createElement('td');
                var button = document.createElement('button');
                button.classList.add('btn', 'btn-primary');
                button.textContent = 'Pilih';
                button.addEventListener('click', function () {
                addProductToCart(product);
                appendProductToTable(product);
                updateTotalPrice();
                });
                tdAction.appendChild(button);
                tr.appendChild(tdAction);
                
                listProduct.appendChild(tr);
            });
        } else {
            // Display message if no products found
            var tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="3" class="text-center">Tidak ada produk yang ditemukan</td>`;
            listProduct.appendChild(tr);
        }
    }

    function searchProductByCode(e) {
        var search = e.target.value;

        axios.post("{{ route('item.search-item') }}", {
            search: search,
            type: 'CODE',
            mode: requestMode,
            outlet_id: requestOutletId
        }).then(handleProductResponse)
          .catch(handleError);
    }

    function handleProductResponse(response) {
        var product = response.data.data;
        var searchProductInput = document.getElementById('search-product');
        
        if (product === null) {
            showErrorAlert(response.data.message);
            clearInput(searchProductInput);
        } else {
            clearInput(searchProductInput);
            addProductToCart(product);
            appendProductToTable(product);
            updateTotalPrice();
        }
    }

    // add product to cart use axios
    function addProductToCart(product) {
        axios.post("{{ route('order-item.add-to-cart') }}", {
            code: product.code,
            quantity: 1,
            mode: requestMode,
            outlet_id: requestOutletId
        }).then(function (response) {
            // if success, refresh table product #list-product
            refreshProductList();
            // Refresh product grid to show updated stock!
            var searchInput = document.getElementById('grid-search-product');
            loadProductCatalog(searchInput ? searchInput.value : '');
        }).catch(function (error) {
            console.error(error);
        });
    }

    // Load Product Catalog Grid on page load and live search
    function loadProductCatalog(search = '') {
        var gridLoader = document.getElementById('grid-loader');
        var gridContainer = document.getElementById('grid-product-list');
        
        if (gridLoader) gridLoader.style.display = 'block';

        axios.post("{{ route('item.search-item') }}", {
            search: search,
            type: 'NAME',
            mode: requestMode,
            outlet_id: requestOutletId
        }).then(function (response) {
            if (gridLoader) gridLoader.style.display = 'none';
            if (gridContainer) {
                gridContainer.innerHTML = '';
                var products = response.data.data;
                
                if (products && products.length > 0) {
                    products.forEach(function (product) {
                        var cardCol = document.createElement('div');
                        cardCol.className = 'col-6 col-sm-4 col-md-4 col-lg-3';
                        
                        var imageUrl = product.image || defaultImageUrl;
                        var stockBadge = product.stock <= 5 
                            ? `<span class="badge bg-light-danger text-danger fw-bold fs-9">Stok Menipis: ${product.stock}</span>`
                            : `<span class="badge bg-light-success text-success fw-bold fs-9">Stok: ${product.stock}</span>`;
                        
                        var formattedPrice = `Rp. ${product.selling_price.toLocaleString('id-ID')}`;
                        
                        // Escaping product name for JSON.stringify in HTML attribute
                        var escapedProduct = JSON.stringify(product).replace(/"/g, '&quot;');
                        
                        cardCol.innerHTML = `
                            <div class="product-card d-flex flex-column h-100" onclick="addProductFromGrid(${escapedProduct})">
                                <img src="${imageUrl}" class="product-image" alt="${product.name}" />
                                <div class="product-info d-flex flex-column justify-content-between flex-grow-1">
                                    <div class="mb-2">
                                        <div class="text-label mb-1">${product.category_item ? product.category_item.name : 'UMUM'}</div>
                                        <h4 class="text-body fw-bold mb-1 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 36px;">${product.name}</h4>
                                        <div class="mt-1">${stockBadge}</div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                        <span class="text-amount">${formattedPrice}</span>
                                        <button type="button" class="btn btn-icon btn-sm btn-light-primary rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        gridContainer.appendChild(cardCol);
                    });
                } else {
                    gridContainer.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <span class="text-muted">Tidak ada produk yang ditemukan</span>
                        </div>
                    `;
                }
            }
        }).catch(function (error) {
            console.error(error);
            if (gridLoader) gridLoader.style.display = 'none';
        });
    }

    // Function to handle product click from grid
    function addProductFromGrid(product) {
        addProductToCart(product);
    }

    function refreshProductList() {
    // Show loader
    document.getElementById('product-loader').style.display = 'block';
    
    axios.get("{{ route('order-item.get-cart') }}", {
        params: {
            mode: requestMode,
            outlet_id: requestOutletId
        }
    })
    .then(function (response) {
    var products = response.data.data;
    var listProduct = document.getElementById('list-product');
    // Clear existing rows
    listProduct.innerHTML = '';
    
    if (products && products.length > 0) {
    products.forEach(function (product, index) {
    var tr = createTableRow(product);
    listProduct.appendChild(tr);
    });
    } else {
    // Display message if no products found
    var tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="5" class="text-center">Barang Masih Kosong</td>`;
    listProduct.appendChild(tr);
    }
    updateTotalPrice();
    
    // Hide loader
    document.getElementById('product-loader').style.display = 'none';
    }).catch(function (error) {
    console.error(error);
    // Hide loader in case of error
    document.getElementById('product-loader').style.display = 'none';
    });
    }

    function createTableRow(product) {
    var tr = document.createElement('tr');
    tr.innerHTML = `
    <td>
        <div class="d-flex align-items-center" data-kt-ecommerce-edit-order-filter="product"
            data-kt-ecommerce-edit-order-id="product_${product?.id || 'unknown'}">
            <a class="symbol symbol-50px">
                <span class="symbol-label" style="background-image:url(${product?.item?.image || defaultImageUrl})"></span>
            </a>
            <div class="ms-5">
                <a class="text-gray-800 text-hover-primary fs-5 fw-bolder">${product?.item?.name || 'Unknown Product'}</a>
                <div class="text-muted fs-7">Stok: ${product?.item?.stock ?? 'N/A'}</div>
            </div>
        </div>
    </td>
    <td>
        <div class="d-flex justify-content-center align-items-center">
            <a class="btn btn-icon btn-light-primary btn-sm me-2 decrement-btn"
                onclick="updateCartQuantity('${product.id}', Math.max(1, ${product.quantity - 1}))">
                <i class="fas fa-minus"></i>
            </a>
            <span class="quantity">${product.quantity}</span>
            <a class="btn btn-icon btn-light-primary btn-sm ms-2 increment-btn"
                onclick="updateCartQuantity('${product.id}', Math.min(${product.item.stock}, ${product.quantity + 1}))">
                <i class="fas fa-plus"></i>
            </a>
            <input type="hidden" value="${product.id}">
        </div>
    </td>
    <td>Rp. ${product.price.toLocaleString('id-ID')}</td>
    <td>Rp. ${product.total.toLocaleString('id-ID')}</td>
    <td>
        <a class="btn btn-icon btn-light-danger btn-sm" onclick="deleteProductFromCart('${product.id}')">
            <span class="svg-icon svg-icon-3"><i class="fas fa-trash"></i></span>
        </a>
    </td>`;
    return tr;
    }

    function deleteProductFromCart(productId) {
        axios.post("{{ route('order-item.delete-from-cart') }}", {
            id: productId,
            mode: requestMode,
            outlet_id: requestOutletId
        }).then(function (response) {
            refreshProductList();
            var searchInput = document.getElementById('grid-search-product');
            loadProductCatalog(searchInput ? searchInput.value : '');
        }).catch(function (error) {
            console.error(error);
        });
    }

    function updateCartQuantity(productId, quantity) {
        axios.post("{{ route('order-item.update-cart-quantity') }}", {
            id: productId,
            quantity: quantity,
            mode: requestMode,
            outlet_id: requestOutletId
        }).then(function (response) {
            refreshProductList();
            var searchInput = document.getElementById('grid-search-product');
            loadProductCatalog(searchInput ? searchInput.value : '');
        }).catch(function (error) {
            console.error(error);
        });
    }

    function showErrorAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Maaf...',
            text: message,
        });
    }

    function clearInput(inputElement) {
        inputElement.value = '';
    }

    function appendProductToTable(product) {
        var listProduct = document.getElementById('list-product');
        var tr = createTableRow(product);
        listProduct.appendChild(tr);
    }

    function updateTotalPrice() {
        axios.get("{{ route('order-item.get-total-price') }}", {
            params: {
                mode: requestMode,
                outlet_id: requestOutletId
            }
        })
        .then(function (response) {
        var totalPrice = response.data.data;
        // Ensure totalPrice is a number
        totalPrice = Number(totalPrice); // Convert to number if it's a string
        
        // Check if conversion is successful and totalPrice is a valid number
        if (!isNaN(totalPrice)) {
        var formattedTotalPrice = `Rp. ${totalPrice.toLocaleString('id-ID')}`;
        } else {
        console.error('totalPrice is not a valid number');
        var formattedTotalPrice = 'Rp. 0'; // Default or error handling value
        }
        var totalPriceElement = document.getElementById('total-price');
        totalPriceElement.value = formattedTotalPrice;

        var remainingSaldoElement = document.getElementById('remaining-saldo');
        var saldoElement = document.getElementById('saldo');

        // Get the saldo value and remove formatting
        var saldo = parseInt(saldoElement.value.replace(/[Rp.\s]/g, ''));

        if (saldo) {
            var remainingSaldo = saldo - totalPrice;
            remainingSaldoElement.value = `Rp. ${remainingSaldo.toLocaleString('id-ID')}`;
        } else {
            remainingSaldoElement.value = `Rp. 0`;
        }

        // --- Limit Saldo Check ---
        var btnBayar = document.getElementById('btn-bayar');
        var paymentMethod = document.getElementById('payment_method').value;

        if (paymentMethod === 'Saldo') {
            // 1. Validasi Kecukupan Saldo Aktual
            if (!isNaN(saldo) && totalPrice > saldo) {
                totalPriceElement.classList.add('text-danger', 'fw-bold');
                if (btnBayar) btnBayar.disabled = true;
                
                if (!window.isLimitAlertShown) {
                    Swal.fire({
                        icon: 'error',
                        title: 'SALDO TIDAK MENCUKUPI!',
                        html: `Total belanja (<b>Rp. ${totalPrice.toLocaleString('id-ID')}</b>) melebihi saldo santri saat ini (<b>Rp. ${saldo.toLocaleString('id-ID')}</b>).`,
                        confirmButtonText: 'Mengerti'
                    });
                    window.isLimitAlertShown = true;
                }
            } 
            // 2. Validasi Limit Harian (Jika limit aktif dan lolos validasi saldo aktual)
            else if (window.currentStudentLimit > 0 && totalPrice > window.currentStudentRemainingLimit) {
                totalPriceElement.classList.add('text-danger', 'fw-bold');
                if (btnBayar) btnBayar.disabled = true;
                
                if (!window.isLimitAlertShown) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'LIMIT SALDO TERLAMPAUI!',
                        html: `Siswa ini memiliki batas limit belanja: <b>Rp. ${window.currentStudentLimit.toLocaleString('id-ID')}</b> / hari.<br>
                               Telah terpakai hari ini: <b>Rp. ${window.currentStudentTotalThisDay.toLocaleString('id-ID')}</b>.<br>
                               Sisa kuota belanja: <b class="text-danger">Rp. ${window.currentStudentRemainingLimit.toLocaleString('id-ID')}</b>`,
                        confirmButtonText: 'Mengerti'
                    });
                    window.isLimitAlertShown = true;
                }
            } 
            // 3. Kondisi Aman
            else {
                totalPriceElement.classList.remove('text-danger', 'fw-bold');
                if (btnBayar) btnBayar.disabled = false;
                window.isLimitAlertShown = false;
            }
        } else {
            // Jika Umum / Tunai
            totalPriceElement.classList.remove('text-danger', 'fw-bold');
            if (btnBayar) btnBayar.disabled = false;
            window.isLimitAlertShown = false;
        }
        // --------------------------
        })
        .catch(function (error) {
        console.error(error);
        });
    }

    function deleteAllProductFromCart() {
        // Menampilkan SweetAlert konfirmasi sebelum menghapus
        Swal.fire({
        title: 'Yakin ingin menghapus semua barang?',
        text: 'Semua barang yang ada di keranjang akan dihapus dari keranjang!',
        icon: 'warning',
        showCancelButton: true,
        // confirmButtonColor: red
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus semua!',
        cancelButtonText: 'Batal'
        }).then((result) => {
        // Jika pengguna menekan tombol "Ya"
        if (result.isConfirmed) {
        // Mengirim permintaan AJAX untuk menghapus semua barang dari keranjang
        axios.post("{{ route('order-item.delete-all-cart') }}", {
            mode: requestMode,
            outlet_id: requestOutletId
        })
        .then(function (response) {
        // Menjalankan fungsi refreshProductList() setelah penghapusan berhasil
        refreshProductList();
        var searchInput = document.getElementById('grid-search-product');
        loadProductCatalog(searchInput ? searchInput.value : '');
        }).catch(function (error) {
        console.error(error);
        });
        }
        });
    }

    function handleError(error) {
        console.error(error);
    }

</script>
<script>
    function updateProductList(products, listId) {
        var listProduct = document.getElementById(listId);
        listProduct.innerHTML = '';

        if (products && products.length > 0) {
            products.forEach(function (product, index) {
                var tr = document.createElement('tr');

                // Create td for product details
                var tdProduct = document.createElement('td');
                tdProduct.innerHTML = `
                <div class="d-flex align-items-center" data-kt-ecommerce-edit-order-filter="product"
                    data-kt-ecommerce-edit-order-id="product_${product.id}">
                    <a class="symbol symbol-50px">
                        <span class="symbol-label" style="background-image:url(${product.image || defaultImageUrl})"></span>
                    </a>
                    <div class="ms-5">
                        <a class="text-gray-800 text-hover-primary fs-5 fw-bolder">${product.name}</a>
                        <div class="text-muted fs-7">Stok: ${product.stock}</div>
                    </div>
                </div>`;
                tr.appendChild(tdProduct);

                // Create td for price
                var tdPrice = document.createElement('td');
                tdPrice.textContent = `Rp. ${product.selling_price.toLocaleString('id-ID')}`;
                tr.appendChild(tdPrice);

                // Create td for action button
                var tdAction = document.createElement('td');
                var button = document.createElement('button');
                button.classList.add('btn', 'btn-primary');
                button.textContent = 'Pilih';
                button.addEventListener('click', function () {
                    addProductToCart(product);
                    appendProductToTable(product);
                    updateTotalPrice();
                });
                tdAction.appendChild(button);
                tr.appendChild(tdAction);

                listProduct.appendChild(tr);
            });
        } else {
            // Display message if no products found
            var tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="4" class="text-center">Tidak ada produk yang ditemukan</td>`;
            listProduct.appendChild(tr);
        }
    }

    function addToProductList(product, listId) {
        var listProduct = document.getElementById('list-product');
        var tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-gray-800 fw-bolder d-block fs-7">${number}</td>
            <td>
                <div class="d-flex align-items-center" data-kt-ecommerce-edit-order-filter="product"
                    data-kt-ecommerce-edit-order-id="product_${product.id}">
                    <a class="symbol symbol-50px">
                        <span class="symbol-label" style="background-image:url(${product.image})"></span>
                    </a>
                    <div class="ms-5">
                        <a class="text-gray-800 text-hover-primary fs-5 fw-bolder">${product.name}</a>
                        <div class="fw-bold fs-7">Harga: Rp.
                            <span data-kt-ecommerce-edit-order-filter="price">${product.selling_price.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="text-muted fs-7">Stok: ${product.stock}</div>
                    </div>
                </div>
            </td>
            <td>
                <input type="number" class="form-control form-control-solid w-100px" value="1" min="1" max="${product.stock}">
            </td>`;
        
        // Create delete button
        var tdDelete = document.createElement('td');
        var deleteButton = document.createElement('button');
        deleteButton.classList.add('btn', 'btn-icon', 'btn-light-danger', 'btn-sm', 'me-2');
        deleteButton.innerHTML = `<span class="svg-icon svg-icon-3"><i class="fas fa-trash"></i></span>`;
        deleteButton.addEventListener('click', function () {
            tr.remove();
        });
        tdDelete.appendChild(deleteButton);
        tr.appendChild(tdDelete);

        listProduct.appendChild(tr);
    }

    document.addEventListener('DOMContentLoaded', function () {
    var searchProductNameInput = document.getElementById('search-product-name');
    if (searchProductNameInput) {
    searchProductNameInput.addEventListener('change', function (e) {
    var search = e.target.value;
    axios.post("{{ route('item.search-item') }}", {
    search: search,
    type: 'NAME',
    mode: requestMode,
    outlet_id: requestOutletId
    }).then(function (response) {
    updateProductList(response.data.data, 'list-product-name');
    // clear input
    e.target.value = '';
    }).catch(function (error) {
    console.error(error);
    });
    });
    }
    });
</script>
<script>
    // Trigger search on Enter key press
    document.getElementById('scan-card').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var barcode = e.target.value;
            axios.post("{{ route('order-item.search-student') }}", {
                barcode: barcode,
                mode: requestMode,
                outlet_id: requestOutletId
            }).then(function (response) {
                var student = response.data.data;
                if (student) {
                    // Check if card is blocked by Wali
                    if (student.is_blocked) {
                        Swal.fire({
                            icon: 'error',
                            title: 'KARTU DIBLOKIR!',
                            text: 'Maaf, Kartu fisik milik ' + student.name + ' telah diblokir oleh Wali Santri.',
                            confirmButtonColor: '#d33'
                        });
                        e.target.value = '';
                        return;
                    }

                    // Replace name, saldo, and update total price
                    document.getElementById('student-name').value = student.name;
                    document.getElementById('saldo').value = 'Rp. ' + student.saldo.toLocaleString('id-ID');
                    // Add student id to form-payment
                    document.getElementById('form-payment').insertAdjacentHTML('beforeend', `<input type="hidden" name="barcode"
                        value="${student.barcode}">`);
                        
                    // Set global limits for alert logic
                    window.currentStudentLimit = student.effective_daily_limit || 0;
                    window.currentStudentRemainingLimit = student.remaining_limit || 0;
                    window.currentStudentTotalThisDay = student.total_this_day || 0;
                    window.isLimitAlertShown = false;

                    updateTotalPrice();
                    // Clear input
                    e.target.value = '';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Santri tidak ditemukan',
                        text: 'ID Kartu Santri tidak ditemukan'
                    });
                    // Clear input
                    e.target.value = '';
                }
            }).catch(function (error) {
                console.error(error);
            });
        }
    });
</script>
<script>
    document.getElementById('santri-tab').addEventListener('click', function () {
        document.getElementById('scan-card-group').style.display = 'block';
        document.getElementById('student-name-group').style.display = 'block';
        document.getElementById('saldo').closest('.fv-row').style.display = 'block';
        document.getElementById('remaining-saldo').closest('.fv-row').style.display = 'block';
        // set #payment_method value to 'Saldo'
        document.getElementById('payment_method').value = 'Saldo';
        
        window.currentStudentLimit = 0;
        window.currentStudentRemainingLimit = 0;
        window.currentStudentTotalThisDay = 0;
        window.isLimitAlertShown = false;
        updateTotalPrice();
    });

    document.getElementById('umum-tab').addEventListener('click', function () {
        document.getElementById('scan-card-group').style.display = 'none';
        document.getElementById('student-name-group').style.display = 'none';
        document.getElementById('saldo').closest('.fv-row').style.display = 'none';
        document.getElementById('remaining-saldo').closest('.fv-row').style.display = 'none';
        // set #payment_method value to 'Umum'
        document.getElementById('payment_method').value = 'Tunai';
        
        window.currentStudentLimit = 0;
        window.currentStudentRemainingLimit = 0;
        window.currentStudentTotalThisDay = 0;
        window.isLimitAlertShown = false;
        updateTotalPrice();
    });
</script>
<script>
    // Fungsi untuk mengambil data riwayat transaksi
    async function fetchTransactionHistory() {
        const loader = document.getElementById('transaction-loader');
        const table = document.getElementById('transaction-table');
        const tableBody = document.getElementById('transaction-table-body');

        // Tampilkan loader
        loader.style.display = 'block';
        table.style.display = 'none';
        tableBody.innerHTML = ''; // Kosongkan tabel

        try {
        // Panggil API untuk mendapatkan data transaksi
        const response = await axios.get("{{ route('order-item.get-daily-transaction') }}", {
            params: {
                mode: requestMode,
                outlet_id: requestOutletId
            }
        });
        const data = response.data.data;
        
        // Log data ke konsol untuk debug
        console.log(data);
        
        // Periksa jika ada data
        if (data.length > 0) {
        data.forEach(item => {
        // Ambil nama, kuantitas, dan harga dari item terkait
        const itemsDetail = item.items.map(i => `${i.name} (${i.qty} x Rp ${i.price})`).join(', ');
        
        // Isi tabel dengan data
        const row = `
        <tr>
            <td>${item.no}</td>
            <td>${item.paid_at}</td>
            <td>${item.student}</td>
            <td>${itemsDetail}</td>
            <td>${item.pay_amount}</td>
        </tr>
        `;
        tableBody.innerHTML += row;
        });
        } else {
        tableBody.innerHTML = `<tr>
            <td colspan="5" class="text-center">Tidak ada data transaksi</td>
        </tr>`;
        }

            // Tampilkan tabel
            loader.style.display = 'none';
            table.style.display = 'table';
        } catch (error) {
            console.error('Gagal memuat data transaksi:', error);
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data transaksi</td></tr>`;
            loader.style.display = 'none';
            table.style.display = 'table';
        }
    }
</script>
@endpush