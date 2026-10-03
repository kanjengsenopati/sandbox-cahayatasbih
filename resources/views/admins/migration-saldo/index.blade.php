@extends('layouts.master', ['title' => 'Konfirmasi Penerimaan Migrasi Saldo'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    <i class="fas fa-file-import text-primary fs-2 me-3"></i>
                    Konfirmasi Penerimaan Migrasi Saldo
                </h1>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                @if ($latestBatch && $latestBatch->status === 'PENDING')
                <form action="{{ route('admin.migration-saldo.reject', $latestBatch->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Apakah Anda yakin ingin menolak / membatalkan paket migrasi ini?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light-danger fw-bolder">
                        <i class="fas fa-times me-1"></i> Tolak / Batalkan
                    </button>
                </form>

                <form action="{{ route('admin.migration-saldo.apply', $latestBatch->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('PERHATIAN: Saldo seluruh santri di Aplikasi Baru akan diperbarui dan disamakan 100% dengan saldo Aplikasi Lama.\n\nTotal Saldo: Rp {{ number_format($summary['total_old_saldo'] ?? 0, 0, ',', '.') }}\n\nLanjutkan terapkan saldo?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success fw-bolder">
                        <i class="fas fa-check-circle me-1"></i> Terima & Terapkan Saldo
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid px-5" id="kt_post">
        <div id="kt_content_container" class="container-xxl">

            @if (session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-6 rounded">
                <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Berhasil</h4>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-6 rounded">
                <i class="fas fa-exclamation-triangle fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Gagal</h4>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @if (session('info'))
            <div class="alert alert-info d-flex align-items-center p-5 mb-6 rounded">
                <i class="fas fa-info-circle fs-2hx text-info me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-info">Informasi</h4>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
            @endif

            @if ($latestBatch && $latestBatch->status === 'PENDING')
                <!--begin::Banner Status Pending-->
                <div class="alert alert-primary d-flex align-items-center p-5 mb-6 rounded border border-primary border-dashed">
                    <i class="fas fa-bell fs-2hx text-primary me-4"></i>
                    <div class="d-flex flex-column flex-grow-1">
                        <h4 class="mb-1 text-primary">Paket Migrasi Saldo Masuk Menunggu Persetujuan!</h4>
                        <span class="fs-7">
                            Data dikirim dari Aplikasi Lama oleh <strong>{{ $latestBatch->sent_by }}</strong> pada 
                            <strong>{{ $latestBatch->sent_at ? $latestBatch->sent_at->translatedFormat('d F Y H:i:s') : '-' }}</strong>.
                            Periksa tabel perbandingan santri di bawah. Jika data sudah cocok, klik tombol <strong>"Terima & Terapkan Saldo"</strong> di pojok kanan atas.
                        </span>
                    </div>
                </div>
                <!--end::Banner Status Pending-->

                <!--begin::Summary Cards-->
                <div class="row g-5 mb-6">
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-flush h-100 bg-white border">
                            <div class="card-body p-6 text-center">
                                <span class="text-muted fw-bold fs-7">TOTAL SANTRI</span>
                                <div class="fs-2hx fw-bolder text-primary mt-2">
                                    {{ number_format($summary['total_students'] ?? 0, 0, ',', '.') }}
                                </div>
                                <span class="text-muted fs-8">Santri dalam Paket Migrasi</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-flush h-100 bg-light-success border border-success border-dashed">
                            <div class="card-body p-6 text-center">
                                <span class="text-success fw-bold fs-7">SALDO DARI APLIKASI LAMA (ACUAN)</span>
                                <div class="fs-2hx fw-bolder text-success mt-2">
                                    Rp {{ number_format($summary['total_old_saldo'] ?? 0, 0, ',', '.') }}
                                </div>
                                <span class="text-muted fs-8">Total Nilai yang Akan Diterapkan</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-flush h-100 bg-white border">
                            <div class="card-body p-6 text-center">
                                <span class="text-muted fw-bold fs-7">SALDO APLIKASI BARU SAAT INI</span>
                                <div class="fs-2hx fw-bolder text-gray-800 mt-2">
                                    Rp {{ number_format($summary['total_local_saldo'] ?? 0, 0, ',', '.') }}
                                </div>
                                <span class="text-muted fs-8">Sebelum Ditimpa</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-flush h-100 bg-light-warning border border-warning border-dashed">
                            <div class="card-body p-6 text-center">
                                <span class="text-warning fw-bold fs-7">SELISIH TOTAL</span>
                                <div class="fs-2hx fw-bolder text-warning mt-2">
                                    Rp {{ number_format($summary['total_diff'] ?? 0, 0, ',', '.') }}
                                </div>
                                <span class="text-muted fs-8">Akan Disesuaikan</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Summary Cards-->

                <!--begin::Table Card-->
                <div class="card card-flush border">
                    <div class="card-header pt-6">
                        <div class="card-title">
                            <h3 class="fw-bolder text-gray-800">Rincian Perbandingan Seluruh Santri</h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="table-migration-items">
                                <thead>
                                    <tr class="fw-bolder text-muted bg-light">
                                        <th class="ps-4 min-w-50px">NO</th>
                                        <th class="min-w-120px">NIS</th>
                                        <th class="min-w-200px">NAMA SANTRI</th>
                                        <th class="min-w-100px">KELAS</th>
                                        <th class="min-w-140px text-end text-success">SALDO APP LAMA (ACUAN)</th>
                                        <th class="min-w-140px text-end">SALDO APP BARU SAAT INI</th>
                                        <th class="min-w-140px text-end">SELISIH</th>
                                        <th class="min-w-100px text-center pe-4">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Table Card-->

            @else
                <!--begin::State Tidak Ada Batch Pending-->
                <div class="card card-flush border p-10 text-center mb-6">
                    <div class="card-body">
                        <div class="symbol symbol-70px mb-5">
                            <span class="symbol-label bg-light-success text-success">
                                <i class="fas fa-check-double fs-2tx"></i>
                            </span>
                        </div>
                        <h2 class="fw-bolder text-gray-800 mb-2">Tidak Ada Paket Migrasi yang Menunggu Konfirmasi</h2>
                        <p class="text-muted fs-6 mb-6">
                            @if ($latestBatch && $latestBatch->status === 'APPLIED')
                                Paket migrasi terakhir telah <strong>berhasil diterapkan</strong> pada {{ $latestBatch->applied_at ? $latestBatch->applied_at->translatedFormat('d F Y H:i') : '-' }} oleh {{ $latestBatch->applied_by }}.
                            @else
                                Belum ada pengiriman data migrasi dari Aplikasi Lama. Silakan buka menu <strong>Setting Aplikasi</strong> di Aplikasi Lama lalu klik tombol <strong>"Kirim Data Saldo ke Aplikasi Baru"</strong>.
                            @endif
                        </p>
                    </div>
                </div>
                <!--end::State-->
            @endif

            @if ($historyBatches && $historyBatches->isNotEmpty())
            <!--begin::Riwayat Batch Terdahulu-->
            <div class="card card-flush border mt-8">
                <div class="card-header pt-6">
                    <div class="card-title">
                        <h4 class="fw-bolder text-gray-800">Riwayat Pengiriman Migrasi Terdahulu</h4>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3 fs-7">
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th>WAKTU KIRIM</th>
                                    <th>PENGIRIM</th>
                                    <th>TOTAL SANTRI</th>
                                    <th class="text-end">TOTAL SALDO</th>
                                    <th>STATUS</th>
                                    <th>WAKTU EKSEKUSI</th>
                                    <th>CATATAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historyBatches as $hb)
                                <tr>
                                    <td>{{ $hb->sent_at ? $hb->sent_at->translatedFormat('d M Y H:i') : '-' }}</td>
                                    <td>{{ $hb->sent_by ?? '-' }}</td>
                                    <td>{{ number_format($hb->total_students, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($hb->total_saldo, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($hb->status === 'APPLIED')
                                            <span class="badge badge-light-success">Diterapkan</span>
                                        @elseif ($hb->status === 'REJECTED')
                                            <span class="badge badge-light-danger">Ditolak</span>
                                        @elseif ($hb->status === 'SUPERSEDED')
                                            <span class="badge badge-light-secondary">Digantikan</span>
                                        @else
                                            <span class="badge badge-light-warning">{{ $hb->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $hb->applied_at ? $hb->applied_at->translatedFormat('d M Y H:i') : '-' }}</td>
                                    <td class="text-muted fs-8">{{ $hb->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Riwayat Batch Terdahulu-->
            @endif

        </div>
    </div>
    <!--end::Post-->

</div>
@endsection

@push('js')
@if ($latestBatch && $latestBatch->status === 'PENDING')
<script>
    $(document).ready(function() {
        $('#table-migration-items').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.migration-saldo.datatable', $latestBatch->id) }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4' },
                { data: 'nis', name: 'nis' },
                { data: 'name', name: 'name', className: 'fw-bold' },
                { data: 'classroom', name: 'classroom' },
                { data: 'old_saldo', name: 'old_saldo', className: 'text-end fw-bold text-success' },
                { data: 'current_local_saldo', name: 'current_local_saldo', className: 'text-end' },
                { data: 'diff_saldo', name: 'diff_saldo', className: 'text-end' },
                { data: 'status', name: 'status', className: 'text-center pe-4' }
            ],
            language: {
                search: "Cari Santri / NIS:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ santri",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });
    });
</script>
@endif
@endpush
