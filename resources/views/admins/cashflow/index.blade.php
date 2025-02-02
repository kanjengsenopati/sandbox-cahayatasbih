@extends('layouts.master', ['title' => 'Data Arus Kas'])

@push('css')
<!-- Include Lightbox2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Arus Kas</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('cashflow.index') }}" class="text-muted text-hover-primary">Arus Kas</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Arus Kas</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Cards-->
            <div class="row mb-5">
                <!-- Penerimaan Pembayaran -->
                <div class="col-md-3">
                    <div class="card bg-success text-white" style="height: 200px;">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="bi bi-wallet2 fs-3 text-white mb-3"></i> <!-- White icon on success background -->
                            <div>
                                <h5 class="card-title text-white mb-2">Penerimaan Pembayaran</h5>
                                <p class="card-text fs-2" id="total-payment">Rp {{ number_format($totalIncomes, 0, ',',
                                    '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pengeluaran -->
                <div class="col-md-3">
                    <div class="card bg-danger text-white" style="height: 200px;">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="bi bi-credit-card fs-3 text-white mb-3"></i>
                            <!-- White icon on danger background -->
                            <div>
                                <h5 class="card-title text-white mb-2">Pengeluaran</h5>
                                <p class="card-text fs-2" id="total-expenses">Rp {{ number_format($totalExpenses, 0,
                                    ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sisa Saldo -->
                <div class="col-md-3">
                    <div class="card bg-primary text-white" style="height: 200px;">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="bi bi-bank fs-3 text-white mb-3"></i> <!-- White icon on primary background -->
                            <div>
                                <h5 class="card-title text-white mb-2">Sisa Saldo</h5>
                                <p class="card-text fs-2" id="remaining-balance">Rp {{ number_format($remainingBalances,
                                    0, ',',
                                    '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Arus Kas -->
                <div class="col-md-3">
                    <div class="card bg-info text-white" style="height: 200px;">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="bi bi-currency-exchange fs-3 text-white mb-3"></i>
                            <!-- White icon on info background -->
                            <div>
                                <h5 class="card-title text-white mb-2">Target Arus Kas</h5>
                                <p class="card-text fs-2" id="total-cashflow">Rp {{ number_format($totalCashflows, 0,
                                    ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Cards-->

            <!--begin::Card-->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <div class="card-title"></div>
                    <x-action.create name="Arus Kas" action="{{ route('cashflow.create') }}" />
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-cashflow" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th style="width: 5%">Tanggal</th>
                                    <th style="width: 10%">Kode</th>
                                    <th style="width: 10%">Tipe</th>
                                    <th style="width: 10%">Kategori</th>
                                    <th style="width: 30%">Dari/Kepada</th>
                                    <th style="width: 10%">Jumlah</th>
                                    <th style="width: 10%">Status</th>
                                    <th style="width: 10%">Keterangan</th>
                                    <th style="width: 10%">Bukti Pembayaran</th>
                                    <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Post-->
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Alasan Penolakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="reject_cashflow_id" name="cashflow_id">
                    <div class="mb-3">
                        <label for="status_reason" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" id="status_reason" name="status_reason" required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
<script>
    $(document).ready(() => {
        var table = $('#table-cashflow').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            responsive: true,
            ajax: "{{ route('cashflow.index') }}",
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
                    },
                    responsivePriority: -1
                },
               {
                    data: 'date',
                    name: 'date',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'payment_code',
                    name: 'payment_code',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'type',
                    name: 'type',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'category',
                    name: 'category',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'from_to',
                    name: 'from_to',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
                {
                    data: 'description',
                    name: 'description',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'proof',
                    name: 'proof',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1
                },
            ]
        });
    })
</script>

<script>
    $(document).ready(function() {
        // Approve action
        $(document).on('click', '.approve-btn', function() {
            var cashflowId = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda akan menyetujui arus kas ini!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terima!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('/cashflow/approve/' + cashflowId, {
                            _token: "{{ csrf_token() }}",
                            status: 'APPROVED'
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.data.message,
                            confirmButtonText: 'Ok'
                        });
                        location.reload(); 
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan!',
                            text: 'Tidak dapat menyetujui Arus Kas.',
                            confirmButtonText: 'Ok'
                        });
                    }
                }
            });
        });

        // Reject action
        $(document).on('click', '.reject-btn', function() {
            var cashflowId = $(this).data('id');
            $('#reject_cashflow_id').val(cashflowId);
        });

        // Reject form submission
        $('#rejectForm').on('submit', async function(e) {
            e.preventDefault();
            var cashflowId = $('#reject_cashflow_id').val();
            var statusReason = $('#status_reason').val();
            try {
                const response = await axios.post('/cashflow/reject/' + cashflowId, {
                    _token: "{{ csrf_token() }}",
                    status: 'REJECTED',
                    reason: statusReason
                });
                Swal.fire({
                    icon: 'success',
                    title: 'Ditolak!',
                    text: response.data.message,
                    confirmButtonText: 'Ok'
                });
                location.reload();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan!',
                    text: 'Tidak dapat menolak Arus Kas.',
                    confirmButtonText: 'Ok'
                });
            }
        });
    });
</script>
@endpush