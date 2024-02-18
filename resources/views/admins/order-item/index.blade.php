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
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Form-->
            <form id="kt_ecommerce_edit_order_form" class="form d-flex flex-column flex-lg-row"
                data-kt-redirect="../../demo1/dist/apps/ecommerce/sales/listing.html">
                <!--begin::Aside column-->
                <div class="w-100 flex-lg-row-auto w-lg-300px mb-7 me-7 me-lg-10">
                    <!--begin::Order details-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Ringkasan</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="d-flex flex-column gap-10">
                                <!--begin::Input group-->
                                <div class="fv-row">
                                    <!--begin::Label-->
                                    <!--end::Label-->
                                    <!--begin::Auto-generated ID-->
                                    <div class="fw-bolder fs-3">Total Harga : Rp.
                                        <span id="total-price">0</span>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">Scan Kartu Santri</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="barcode"
                                        placeholder="Masukkan ID Kartu Santri" type="password" id="scan-card" />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row">
                                    <!--begin::Student Info-->
                                    <div id="student-info" class="fw-bold fs-5 mt-2">
                                    </div>
                                    <!--end::Student Info-->
                                </div>
                            </div>
                        </div>
                        <!--end::Card header-->
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
                            <div class="card-title">
                                <h2>List Barang</h2>
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
                                    <div class="row mb-5">
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
                                                Pilih Barang
                                            </button>
                                        </div>
                                    </div>
                                    <!--end::Search inputs-->
                                    <!--begin::Selected products-->
                                    <div class="row row-cols-1 row-cols-xl-3 row-cols-md-2 border border-dashed rounded pt-3 pb-1 px-2 mb-5 mh-300px overflow-scroll"
                                        id="kt_ecommerce_edit_order_selected_products">
                                        <!--begin::Empty message-->
                                        <span class="w-100 text-muted">Cari barang berdasarkan kode pada kolom di
                                            atas</span>
                                        <!--end::Empty message-->
                                    </div>
                                    <!--end::Selected products-->
                                    <!--begin::Total price-->
                                    {{-- <div class="fw-bolder fs-4">Total Harga : Rp.
                                        <span id="total-price">0</span>
                                    </div> --}}
                                    <!--end::Total price-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator"></div>
                                <!--end::Separator-->
                                <!--begin::Search products-->
                                {{-- <div class="d-flex align-items-center position-relative mb-n7">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
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
                                    <!--end::Svg Icon-->
                                    <input type="text" data-kt-ecommerce-edit-order-filter="search" id="search-product"
                                        class="form-control form-control-solid w-100 w-lg-50 ps-14"
                                        placeholder="Masukkan Kode Barang" />
                                </div> --}}
                                <!--end::Search products-->
                                <!--begin::Table-->
                                <table class="table align-middle table-row-dashed fs-6 gy-5" id="list-product">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="w-25px pe-2">No</th>
                                            <th class="min-w-200px" style="min-width: 25%">Product</th>
                                            <th>Total</th>
                                            <th class="w-25px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="fw-bold text-gray-600">
                                        <!--begin::Table row-->
                                        <!--begin::Table row-->
                                        <!--end::Table row-->
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                        </div>
                        <!--end::Card header-->
                    </div>
                    <!--end::Order details-->
                    <div class="d-flex justify-content-end">
                        <!--begin::Button-->
                        <a href="../../demo1/dist/apps/ecommerce/catalog/products.html"
                            id="kt_ecommerce_edit_order_cancel" class="btn btn-light me-5">Cancel</a>
                        <!--end::Button-->
                        <!--begin::Button-->
                        <button type="submit" id="kt_ecommerce_edit_order_submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <!--end::Button-->
                    </div>
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
                    <span class="w-100 text-muted">Cari barang berdasarkan nama pada kolom di atas</span>
                    <!--end::Empty message-->
                </div>
                <div>
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th class="w-25px pe-2">No</th>
                                <th class="min-w-200px" style="min-width: 25%">Product</th>
                                <th>Harga</th>
                                <th class="w-25px">Aksi</th>
                            </tr>
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody class="fw-bold text-gray-600" id="list-product-name">
                            <!--begin::Table row-->
                            <!--begin::Table row-->
                            <!--end::Table row-->
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
            appendProductToTable(product);
            updateTotalPrice(product.selling_price, 1);
        }
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

    function createTableRow(product) {
        var tr = document.createElement('tr');

        // Create td for number
        var tdNo = document.createElement('td');
        tdNo.innerHTML = `<span class="text-gray-800 fw-bolder d-block fs-7">${number}</span>`;
        tr.appendChild(tdNo);

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
                    <div class="fw-bold fs-7">Harga: Rp. 
                        <span data-kt-ecommerce-edit-order-filter="price">
                            ${product.selling_price.toLocaleString('id-ID')}
                        </span>
                    </div>
                    <div class="text-muted fs-7">Stok: ${product.stock}</div>
                </div>
            </div>`;
        tr.appendChild(tdProduct);

        // Create td for total quantity
        var tdTotal = document.createElement('td');
        tdTotal.innerHTML = `
            <input type="number" class="form-control form-control-solid w-100px" value="1" min="1" max="${product.stock}" onchange="updateTotalPrice(${product.selling_price}, this.value)">`;
        tr.appendChild(tdTotal);

        // Create td for delete button
        var tdDelete = document.createElement('td');
        var deleteButton = document.createElement('button');
        deleteButton.classList.add('btn', 'btn-icon', 'btn-light-danger', 'btn-sm', 'me-2');
        deleteButton.innerHTML = `<span class="svg-icon svg-icon-3"><i class="fas fa-trash"></i></span>`;
        deleteButton.addEventListener('click', function () {
            tr.remove();
            number--;
            updateTotalPrice(-product.selling_price, -1);
        });
        tdDelete.appendChild(deleteButton);
        tr.appendChild(tdDelete);

        number++;

        return tr;
    }

    function updateTotalPrice(price, quantity) {
        totalPrice += price * quantity;
        var totalPriceElement = document.getElementById('total-price');
        totalPriceElement.textContent = totalPrice.toLocaleString('id-ID');
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

                    // Create td for number
                var tdNo = document.createElement('td');
                tdNo.innerHTML = `<span class="text-gray-800 fw-bolder d-block fs-7">${number}</span>`;
                tr.appendChild(tdNo);

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
                    addToProductList(product, listId);
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
            number--;
        });
        tdDelete.appendChild(deleteButton);
        tr.appendChild(tdDelete);

        listProduct.appendChild(tr);

        number++;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var searchProductNameInput = document.getElementById('search-product-name');
        if (searchProductNameInput) {
            searchProductNameInput.addEventListener('input', function (e) {
                var search = e.target.value;
                axios.post("{{ route('item.search-item') }}", {
                    search: search,
                    type: 'NAME'
                }).then(function (response) {
                    updateProductList(response.data.data, 'list-product-name');
                }).catch(function (error) {
                    console.error(error);
                });
            });
        }
    });
</script>
<script>
    // jika #scan-card diinputkan maka cari student berdasarkan id card
    document.getElementById('scan-card').addEventListener('input', function (e) {
        var barcode = e.target.value;
        axios.post("{{ route('order-item.search-student') }}", {
            barcode: barcode
        }).then(function (response) {
            var student = response.data.data;
            if (student) {
                console.log(student);
                var studentInfo = document.getElementById('student-info');
               studentInfo.innerHTML = `
                <div>
                    <span class=fs-3">Santri:</span>
                    <span class="fw-bold fs-5">${student.name}</span>
                </div>
                <div>
                    <span class=fs-3">NISN:</span>
                    <span class="fw-bold fs-5">${student.nisn}</span>
                </div>
                <div>
                    <span class=fs-3">Kelas:</span>
                    <span class="fw-bold fs-5">${student.classroom.name}</span>
                </div>
                `;

                // Clear input
                e.target.value = '';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Santri tidak ditemukan',
                    text: 'ID Kartu Santri tidak ditemukan'
                });
            }
        }).catch(function (error) {
            console.error(error);
        });
    });
</script>
@endpush