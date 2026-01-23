@extends('layouts.master', ['title' => 'Detail Transaksi'])

@section('content')
    <!-- MAIN CONTENT (WEB VIEW) -->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Transaksi</h1>
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted"> <a href="{{ route('order-item-history.index') }}" class="text-muted text-hover-primary">Riwayat Penjualan</a> </li>
                        <li class="breadcrumb-item"> <span class="bullet bg-gray-300 w-5px h-2px"></span> </li>
                        <li class="breadcrumb-item text-dark">Invoice #{{ $order->payment_code }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                
                <div class="row g-5 g-xl-8">
                    <!-- LEFT COLUMN: Rincian Pesanan -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 mb-5">
                            <!-- Header Card -->
                            <div class="card-header border-0 pt-6 pb-0">
                                <h3 class="card-title fw-bolder text-dark fs-2">
                                    Invoice <span class="text-muted fw-bold ms-2 fs-4">#{{ $order->payment_code }}</span>
                                </h3>
                                <div class="card-toolbar">
                                    @if ($order->status == 'SUCCESS')
                                        <span class="badge badge-light-success fs-7 fw-bolder px-3 py-2">LUNAS / PAID</span>
                                    @elseif ($order->status == 'PENDING')
                                        <span class="badge badge-light-warning fs-7 fw-bolder px-3 py-2">PENDING</span>
                                    @else
                                        <span class="badge badge-light-danger fs-7 fw-bolder px-3 py-2">GAGAL</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Body Card: Table Items -->
                            <div class="card-body py-4">
                                <div class="table-responsive">
                                    <table id="table-student" class="table table-hover align-middle gs-0 gy-4">
                                        <thead class="border-bottom border-gray-200 fs-7 fw-bolder bg-light">
                                            <tr class="text-start text-muted text-uppercase gs-0">
                                                <th class="ps-4 min-w-50px rounded-start">No</th>
                                                <th class="min-w-150px">Produk</th>
                                                <th class="min-w-50px text-center">Qty</th>
                                                <th class="min-w-100px text-end">Harga</th>
                                                <th class="min-w-100px text-end pe-4 rounded-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fs-6 fw-bold text-gray-700">
                                            <!-- Data Loaded by DataTables via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Footer Card: Summary -->
                            <div class="card-footer bg-light border-0 rounded-bottom-4 p-5">
                                <div class="d-flex justify-content-end">
                                    <div class="d-flex flex-column w-md-300px">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted fw-bold">Subtotal</span>
                                            <span class="text-dark fw-bolder">Rp {{ number_format($order->pay_amount - ($order->tax ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                        @if(isset($order->profit))
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted fw-bold">Profit (Est.)</span>
                                            <span class="text-success fw-bolder">Rp {{ number_format($order->profit, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="separator separator-dashed border-gray-300 mb-3"></div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-dark fw-bold fs-4">Grand Total</span>
                                            <span class="text-primary fw-bolder fs-2tx">Rp {{ number_format($order->pay_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Konteks & Aksi -->
                    <div class="col-lg-4">
                         <!-- Card 3: Actions -->
                        <div class="card border-0 shadow-sm rounded-4 mb-5 ">
                            <div class="card-body p-5">
                                <a href="{{ route('order-item-history.print', $order->id) }}" target="_blank" class="btn btn-primary w-100 mb-3 fs-5 fw-bold btn-block hover-scale">
                                    <i class="fas fa-print me-2"></i> Cetak Struk (PDF)
                                </a>
                                <a href="{{ route('order-item-history.index') }}" class="btn btn-outline btn-outline-secondary w-100 fw-bold btn-block">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </a>
                            </div>
                        </div>

                        
                        <!-- Card 1: Profil Santri (Jika Ada) -->
                        @if ($order->type == 'SANTRI' && $order->student)
                        <div class="card border-0 shadow-sm rounded-4 mb-5 border-start border-4 border-primary">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-center mb-4">
                                    <div class="symbol symbol-75px symbol-circle mb-3">
                                        <div class="symbol-label fs-2 fw-bold bg-light-primary text-primary">
                                            {{ substr($order->student->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <h3 class="fw-bolder text-dark mb-1">{{ $order->student->name }}</h3>
                                    <span class="text-muted fw-bold">{{ $order->student->nis ?? '-' }}</span>
                                </div>
                                
                                <div class="d-flex flex-stack mb-2 bg-light rounded p-3">
                                    <span class="text-muted fw-bold">Kelas</span>
                                    <span class="badge badge-light-primary fw-bolder fs-7">{{ $order->student->classroom->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex flex-stack bg-light rounded p-3">
                                    <span class="text-muted fw-bold">Sekolah</span>
                                    <span class="text-gray-800 fw-bold fs-7 text-end">{{ $order->student->classroom->school->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        @else
                         <div class="card border-0 shadow-sm rounded-4 mb-5 border-start border-4 border-success">
                            <div class="card-body p-5">
                                <div class="d-flex flex-column align-items-center mb-4">
                                     <div class="symbol symbol-75px symbol-circle mb-3">
                                        <div class="symbol-label fs-2 fw-bold bg-light-success text-success">
                                            <i class="fas fa-users fs-1"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bolder text-dark mb-1">Pelanggan Umum</h3>
                                    <span class="text-muted fw-bold">Non-Santri (Guest)</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Card 2: Informasi Kasir & Waktu -->
                        <div class="card border-0 shadow-sm rounded-4 mb-5">
                            <div class="card-header border-0 min-h-50px pt-4">
                                <h3 class="card-title fw-bold text-gray-700 fs-5">Informasi Order</h3>
                            </div>
                            <div class="card-body pt-2 pb-5">
                                <!-- Kasir -->
                                <div class="d-flex align-items-center mb-4">
                                    <div class="symbol symbol-35px me-3">
                                        <div class="symbol-label bg-light-info">
                                            <i class="fas fa-user-tie text-info"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted fw-bold fs-7">Kasir</span>
                                        <span class="text-dark fw-bolder">{{ $order->admins->name ?? 'Admin' }}</span>
                                    </div>
                                </div>
                                <!-- Waktu -->
                                <div class="d-flex align-items-center mb-4">
                                    <div class="symbol symbol-35px me-3">
                                        <div class="symbol-label bg-light-warning">
                                            <i class="fas fa-clock text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted fw-bold fs-7">Waktu Transaksi</span>
                                        <span class="text-dark fw-bolder">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                            </div>
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
        // DATATABLE INIT
        var table = $('#table-student').DataTable({
            destroy: true,
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('order-item-history.show', $order->id) }}',
            },
            dom: 't', 
            paging: false, 
            language: {
                "zeroRecords": "Data tidak ditemukan",
                "emptyTable": "Tidak ada item pembelian",
                "processing": '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><span class="ms-2">Memuat data...</span></div>'
            },
            columns: [
                {
                    data: null,
                    sortable: false,
                    className: "ps-4",
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'item.name',
                    name: 'item.name',
                    render: function(data, type, row) {
                        return `<div class="d-flex flex-column">
                                    <span class="fw-bolder text-dark fs-6">${data ?? '-'}</span>
                                    <span class="text-muted fs-8 fw-bold">${row.item.category_item ? row.item.category_item.name : '-'}</span>
                                </div>`;
                    }
                },
                {
                    data: 'quantity',
                    name: 'quantity',
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="fw-bold text-gray-700">${data}</span>`;
                    }
                },
                {
                    data: 'price',
                    name: 'price',
                    className: 'text-end',
                    render: function(data) {
                        return `<span class="text-gray-600 fw-bold">Rp ${new Intl.NumberFormat('id-ID').format(data)}</span>`;
                    }
                },
                {
                    data: 'total',
                    name: 'total',
                    className: 'text-end pe-4',
                    render: function(data) {
                        return `<span class="text-dark fw-bolder">Rp ${new Intl.NumberFormat('id-ID').format(data)}</span>`;
                    }
                }
            ]
        });
    });
</script>
@endpush
