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
                action="{{ route('order-item.store') }}">
                @csrf
                <!--begin::Aside column-->
                <div class="w-100 flex-lg-row-auto w-lg-300px mb-7 me-7 me-lg-10">
                    <!--begin::Order details-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Pembayaran</h2>
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
                                                <input class="form-control form-control-solid" name="barcode"
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
                                    <input class="form-control" name="payment-method" type="text" id="payment-method"
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
                                <div class="d-flex justify-content-center">
                                    <button type="submit" id="btn-bayar" class="btn btn-primary mt-3 w-100">
                                        <span class="indicator-label">Bayar</span>
                                    </button>
                                </div>
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
                                    <!--begin::Selected products-->
                                    {{-- <div
                                        class="row row-cols-1 row-cols-xl-3 row-cols-md-2 border border-dashed rounded pt-3 pb-1 px-2 mb-5 mh-300px overflow-scroll"
                                        id="kt_ecommerce_edit_order_selected_products">
                                        <!--begin::Empty message-->
                                        <span class="w-100 text-muted">Cari barang berdasarkan kode pada kolom di
                                            atas</span>
                                        <!--end::Empty message-->
                                    </div> --}}
                                    <!--end::Selected products-->
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
                        </div>
                        <!--end::Card header-->
                    </div>
                    <!--end::Order details-->
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
    var number = 1;
    var totalPrice = 0;

    window.addEventListener('DOMContentLoaded', function () {
        focusOnFirstInput();
        refreshProductList();

        var searchProductInput = document.getElementById('search-product');
        if (searchProductInput) {
            searchProductInput.addEventListener('input', searchProductByCode);
        }
    });

    function focusOnFirstInput() {
        var searchProductInput = document.getElementById('search-product');
        if (searchProductInput) {
            searchProductInput.focus();
        }
    }

    function searchProductByCode(e) {
        var search = e.target.value;

        axios.post("{{ route('item.search-item') }}", {
            search: search,
            type: 'CODE'
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
            quantity: 1
        }).then(function (response) {
            // if success, refresh table product #list-product
            refreshProductList();
        }).catch(function (error) {
            console.error(error);
        });
    }

    function refreshProductList() {
    // Show loader
    document.getElementById('product-loader').style.display = 'block';
    
    axios.get("{{ route('order-item.get-cart') }}")
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
            data-kt-ecommerce-edit-order-id="product_${product.id}">
            <a class="symbol symbol-50px">
                <span class="symbol-label" style="background-image:url(${product.item.image})"></span>
            </a>
            <div class="ms-5">
                <a class="text-gray-800 text-hover-primary fs-5 fw-bolder">${product.item.name}</a>
                <div class="text-muted fs-7">Stok: ${product.item.stock}</div>
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
    id: productId
    }).then(function (response) {
    refreshProductList();
    }).catch(function (error) {
    console.error(error);
    });
    }

    function updateCartQuantity(productId, quantity) {
        axios.post("{{ route('order-item.update-cart-quantity') }}", {
            id: productId,
            quantity: quantity
        }).then(function (response) {
            refreshProductList();
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
        axios.get("{{ route('order-item.get-total-price') }}")
        .then(function (response) {
        var totalPrice = response.data.data;
        var formattedTotalPrice = `Rp. ${totalPrice.toLocaleString('id-ID')}`;
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
        axios.post("{{ route('order-item.delete-all-cart') }}")
        .then(function (response) {
        // Menjalankan fungsi refreshProductList() setelah penghapusan berhasil
        refreshProductList();
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
                            <span class="symbol-label" style="background-image:url(${product.image})"></span>
                        </a>
                        <div class="ms-5">
                            <a class="text-gray-800 text-hover-primary fs-5 fw-bolder">${product.name}</a>
                            <div class="text-muted fs-7">Stok: ${product.stock}</div>
                        </div>
                    </div>`;
                tr.appendChild(tdProduct);

                // Create td for price
                var tdPrice = document.createElement('td');
                tdPrice.textContent = `Rp. ${product.price.toLocaleString('id-ID')}`;
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
                            <span data-kt-ecommerce-edit-order-filter="price">${product.price.toLocaleString('id-ID')}</span>
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
    type: 'NAME'
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
    // jika #scan-card diinputkan maka cari student berdasarkan id card
    document.getElementById('scan-card').onchange = function (e) {
    var barcode = e.target.value;
    axios.post("{{ route('order-item.search-student') }}", {
    barcode: barcode
    }).then(function (response) {
    var student = response.data.data;
    if (student) {
    // replace name, saldo, and count saldo - total price
    document.getElementById('student-name').value = student.name;
    document.getElementById('saldo').value = 'Rp. ' + student.saldo.toLocaleString('id-ID');
    // add student id to form form-payment
    document.getElementById('form-payment').insertAdjacentHTML('beforeend', `<input type="hidden" name="student_id"
        value="${student.id}">`);
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
    };

</script>
<script>
    document.getElementById('santri-tab').addEventListener('click', function () {
        document.getElementById('scan-card-group').style.display = 'block';
        document.getElementById('student-name-group').style.display = 'block';
        document.getElementById('saldo').closest('.fv-row').style.display = 'block';
        document.getElementById('remaining-saldo').closest('.fv-row').style.display = 'block';
        // set #payment-method value to 'Saldo'
        document.getElementById('payment-method').value = 'Saldo';
    });

    document.getElementById('umum-tab').addEventListener('click', function () {
        document.getElementById('scan-card-group').style.display = 'none';
        document.getElementById('student-name-group').style.display = 'none';
        document.getElementById('saldo').closest('.fv-row').style.display = 'none';
        document.getElementById('remaining-saldo').closest('.fv-row').style.display = 'none';
        // set #payment-method value to 'Umum'
        document.getElementById('payment-method').value = 'Tunai';
    });
</script>
@endpush