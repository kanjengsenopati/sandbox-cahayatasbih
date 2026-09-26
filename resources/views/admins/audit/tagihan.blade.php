@extends('layouts.master', ['title' => 'Tagihan Pembayaran - Audit & Sinkron'])

@push('css')
<style>
    .nav-custom {
        border-bottom: 2px solid #eff2f5;
    }
    
    .nav-custom .nav-link {
        color: #7e8299;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    
    .nav-custom .nav-link:hover {
        color: #2563eb;
    }
    
    .nav-custom .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }
</style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Tagihan Pembayaran</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">Audit dan Sinkron</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Tagihan Pembayaran</li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('bill.audit-consistency') }}" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-shield-check me-1"></i> Audit Konsistensi Tagihan
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="card card-flush">
                <div class="card-header pt-7">
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6 w-100">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#arsip_riwayat">Arsip Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#import_pembayaran">Import Pembayaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#audit_anomaly_data">Audit Anomaly Data</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-0">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="arsip_riwayat" role="tabpanel">
                            @include('admins.bill.transfer-tab.archive')
                        </div>
                        <div class="tab-pane fade" id="import_pembayaran" role="tabpanel">
                            @include('admins.bill.import-tab.index')
                        </div>
                        <div class="tab-pane fade" id="audit_anomaly_data" role="tabpanel">
                            <div class="alert alert-primary d-flex align-items-center justify-content-between p-5 mb-5 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe;">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-primary text-white">
                                            <i class="bi bi-shield-check fs-2"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <h5 class="mb-1 text-slate-800 fw-bolder">Audit & Konsistensi Finansial (6 Item Kritis)</h5>
                                        <span class="text-slate-600 fs-7">Deteksi jenis tagihan duplikat, tarif kosong, siswa tanpa tagihan, ghost billing, dan inkonsistensi status tagihan. Dilengkapi fitur safe auto-repair.</span>
                                    </div>
                                </div>
                                <a href="{{ route('bill.audit-consistency') }}" class="btn btn-primary fw-bold text-nowrap ms-4 px-5">
                                    <i class="bi bi-speedometer2 me-2"></i> Buka Dashboard Konsistensi
                                </a>
                            </div>

                            <div class="card card-flush">
                                <div class="card-body p-0">
                                    <div class="p-0 border-0 bg-white m-0" style="width: 100%; min-height: 800px;">
                                        <iframe src="{{ url('audit-vps-data') }}" style="width: 100%; height: 800px; border: none; border-radius: 12px; background: #fff;" title="Audit Anomaly Data" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bukti Pembayaran -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Bukti Pembayaran</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body text-center pt-0 pb-15">
                <img id="imagePreviewSrc" src="" alt="Bukti Pembayaran" class="img-fluid rounded" />
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(() => {
        var archiveTable = $('#table-archive').DataTable({
            ordering: true,
            sortable: true,
            processing: true,
            serverSide: true,
            pageLength: 20,
            ajax: "{{ route('audit.tagihan') }}?tab=archive",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'student', name: 'student' },
                { data: 'pay_amount', name: 'pay_amount' },
                { data: 'kode_unik', name: 'kode_unik' },
                { data: 'bank_recipient', name: 'bank_recipient', orderable: false },
                { data: 'proof', name: 'proof' },
                { data: 'status', name: 'status' },
                { data: 'officer', name: 'officer', orderable: false },
                { data: 'date', name: 'date', orderable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[8, 'desc']], // Urutkan berdasarkan tanggal terbaru
        });

        // Delete Archive handler
        $(document).on('click', '.btn-delete-archive', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Arsip riwayat ini akan disembunyikan. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, sembunyikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/admin/transaction/" + id + "/hide-archive",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT'
                        },
                        success: function(response) {
                            Swal.fire('Berhasil!', 'Arsip telah disembunyikan.', 'success');
                            archiveTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'Gagal menyembunyikan arsip', 'error');
                        }
                    });
                }
            });
        });

        $('a[href="#arsip_riwayat"]').on('shown.bs.tab', function (e) {
            archiveTable.columns.adjust().draw();
        });

        $(document).on('click', '.view-proof-image', function() {
            var src = $(this).data('src');
            $('#imagePreviewSrc').attr('src', src);
            $('#imagePreviewModal').modal('show');
        });
    });
</script>
@endpush
