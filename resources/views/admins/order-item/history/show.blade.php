@extends('layouts.master', ['title' => 'Detail Transaksi'])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                    data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                    class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Transaksi</h1>
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <a class="breadcrumb-item" href="{{ route('order-item-history.index') }}">
                            <li class="breadcrumb-item text-muted">Transaksi</li>
                        </a>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Detail Transaksi</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">

                <!-- Header Card dengan Status -->
                <div class="card mb-5 shadow-sm border-0">
                    <div class="card-body p-6">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <h2 class="fw-bold text-dark mb-0 me-3">{{ $order->payment_code ?? '-' }}</h2>
                                    @if ($order->status == 'PENDING')
                                        <span class="badge badge-light-warning px-4 py-2 fs-6">
                                            <i class="fas fa-clock me-1"></i> Menunggu Pembayaran
                                        </span>
                                    @elseif ($order->status == 'SUCCESS')
                                        <span class="badge badge-light-success px-4 py-2 fs-6">
                                            <i class="fas fa-check-circle me-1"></i> Pembayaran Berhasil
                                        </span>
                                    @elseif ($order->status == 'FAILED')
                                        <span class="badge badge-light-danger px-4 py-2 fs-6">
                                            <i class="fas fa-times-circle me-1"></i> Pembayaran Gagal
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted fs-7">
                                    <i
                                        class="fas fa-calendar-alt me-2"></i>{{ $order->created_at ? $order->created_at->format('d M Y, H:i') : '-' }}
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="fs-7 text-muted mb-1">Total Pembayaran</div>
                                <div class="fs-2x fw-bold text-primary">Rp
                                    {{ number_format($order->pay_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-5">
                    <!-- Left Column - Transaction & Student Info -->
                    <div class="col-lg-8">
                        <!-- Transaction Details Card -->
                        <div class="card shadow-sm border-0 mb-5">
                            <div class="card-header bg-light border-0 py-4">
                                <h3 class="card-title fw-bold m-0">
                                    <i class="fas fa-receipt text-primary me-2"></i>Informasi Transaksi
                                </h3>
                            </div>
                            <div class="card-body p-6">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="symbol symbol-40px me-3">
                                                <span class="symbol-label bg-light-primary">
                                                    <i class="fas fa-hashtag text-primary"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-muted fs-7 mb-1">Nomor Transaksi</div>
                                                <div class="fw-bold fs-6">{{ $order->payment_code ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="symbol symbol-40px me-3">
                                                <span class="symbol-label bg-light-info">
                                                    @if ($order?->admins?->avatar)
                                                        <img src="{{ asset($order->admins->avatar) }}" alt="avatar"
                                                            class="img-fluid rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <i class="fas fa-user text-info"></i>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-muted fs-7 mb-1">Kasir</div>
                                                <div class="fw-bold fs-6">{{ $order?->admins?->name ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="symbol symbol-40px me-3">
                                                <span class="symbol-label bg-light-success">
                                                    <i class="fas fa-chart-line text-success"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-muted fs-7 mb-1">Profit</div>
                                                <div class="fw-bold fs-6 text-success">Rp
                                                    {{ number_format($order->profit, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="symbol symbol-40px me-3">
                                                <span class="symbol-label bg-light-warning">
                                                    <i class="fas fa-tag text-warning"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-muted fs-7 mb-1">Tipe Transaksi</div>
                                                <div class="fw-bold fs-6">{{ $order->type ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($order->type == 'SANTRI' && $order->student)
                            <!-- Student Info Card -->
                            <div class="card shadow-sm border-0 mb-5">
                                <div class="card-header bg-light border-0 py-4">
                                    <h3 class="card-title fw-bold m-0">
                                        <i class="fas fa-user-graduate text-primary me-2"></i>Informasi Santri
                                    </h3>
                                </div>
                                <div class="card-body p-6">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start mb-4">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="fas fa-id-card text-primary"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-muted fs-7 mb-1">NIS</div>
                                                    <div class="fw-bold fs-6">{{ $order->student->nis ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start mb-4">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-info">
                                                        <i class="fas fa-user text-info"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-muted fs-7 mb-1">Nama Lengkap</div>
                                                    <div class="fw-bold fs-6">{{ $order->student?->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start mb-4">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-success">
                                                        <i class="fas fa-chalkboard text-success"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-muted fs-7 mb-1">Kelas</div>
                                                    <div class="fw-bold fs-6">
                                                        {{ $order->student?->classroom->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start mb-4">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-warning">
                                                        <i class="fas fa-school text-warning"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-muted fs-7 mb-1">Sekolah</div>
                                                    <div class="fw-bold fs-6">
                                                        {{ $order->student?->classroom?->school->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-danger">
                                                        <i class="fas fa-venus-mars text-danger"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-muted fs-7 mb-1">Jenis Kelamin</div>
                                                    <div class="fw-bold fs-6">
                                                        {{ $order->student?->gender == 'L' ? 'Laki-Laki' : 'Perempuan' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Purchase List Card -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light border-0 py-4">
                                <h3 class="card-title fw-bold m-0">
                                    <i class="fas fa-shopping-cart text-primary me-2"></i>Daftar Pembelian
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="table-student"
                                        class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3 mb-0">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-6 rounded-start" style="width: 50px">No</th>
                                                <th>Nama Item</th>
                                                <th class="text-center" style="width: 100px">Qty</th>
                                                <th class="text-end" style="width: 150px">Harga</th>
                                                <th class="text-end pe-6 rounded-end" style="width: 150px">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="col-lg-4">
                        <!-- Summary Card -->
                        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                            <div class="card-header bg-primary border-0 py-4">
                                <h3 class="card-title fw-bold text-white m-0">
                                    <i class="fas fa-calculator me-2"></i>Ringkasan Pembayaran
                                </h3>
                            </div>
                            <div class="card-body p-6">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom border-gray-300">
                                    <span class="text-muted fs-6">Subtotal</span>
                                    <span class="fw-bold fs-5">Rp
                                        {{ number_format($order->pay_amount - ($order->tax ?? 0), 0, ',', '.') }}</span>
                                </div>
                                @if (isset($order->tax) && $order->tax > 0)
                                    <div
                                        class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom border-gray-300">
                                        <span class="text-muted fs-6">Pajak</span>
                                        <span class="fw-bold fs-5">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div
                                    class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom border-gray-300">
                                    <span class="text-muted fs-6">Profit</span>
                                    <span class="fw-bold fs-5 text-success">Rp
                                        {{ number_format($order->profit, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-4">Total</span>
                                    <span class="fw-bolder fs-2 text-primary">Rp
                                        {{ number_format($order->pay_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="card-footer border-0 bg-light py-4">
                                <a href="{{ route('order-item-history.index') }}" class="btn btn-light-primary w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                                </a>
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
            var table = $('#table-student').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('order-item-history.show', $order->id) }}',
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'></i>",
                        "previous": "<i class='fa fa-angle-left'></i>"
                    },
                    "loadingRecords": "Memuat data...",
                    "processing": "Memproses...",
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "zeroRecords": "Data tidak ditemukan",
                    "emptyTable": "Tidak ada data yang tersedia"
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        "className": "ps-6",
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'item.name',
                        name: 'item.name',
                        render: function(data, type, row) {
                            return `<div class="d-flex align-items-center">
                                <div class="symbol symbol-35px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="fas fa-box text-primary fs-6"></i>
                                    </span>
                                </div>
                                <div class="fw-bold">${data ?? '-'}</div>
                            </div>`;
                        }
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `<span class="badge badge-light-primary fs-7 fw-bold">${data ?? 0}</span>`;
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return data ? '<span class="fw-semibold">Rp ' + data.toString().replace(
                                /\B(?=(\d{3})+(?!\d))/g, ".") + '</span>' : '-';
                        }
                    },
                    {
                        data: 'total_price',
                        name: 'total_price',
                        className: 'text-end pe-6',
                        render: function(data, type, row) {
                            return data ? '<span class="fw-bold text-dark">Rp ' + data.toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '</span>' : '-';
                        }
                    }
                ]
            });
        });
    </script>
@endpush
