@extends('layouts.master', ['title' => 'Audit Sistem'])

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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Audit Sistem</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.audit') }}" class="text-muted text-hover-primary">Sistem</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Audit Diagnostik
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('admin.audit') }}" class="btn btn-sm btn-primary fw-bolder">
                        <i class="fas fa-sync-alt me-1 fs-7"></i> Jalankan Ulang Audit
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                
                <!-- Session Alerts -->
                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center p-5 mb-6">
                        <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                <path d="M10.4343 12.4343L8.75 10.75C8.33579 10.3358 7.66421 10.3358 7.25 10.75C6.83579 11.1642 6.83579 11.8358 7.25 12.25L9.69289 14.6929C10.0834 15.0834 10.7166 15.0834 11.1071 14.6929L16.75 9.05C17.1642 8.63579 17.1642 7.96421 16.75 7.55C16.3358 7.13579 15.6642 7.13579 15.25 7.55L10.4343 12.4343Z" fill="currentColor"></path>
                            </svg>
                        </span>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Sukses</h4>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center p-5 mb-6">
                        <span class="svg-icon svg-icon-2hx svg-icon-danger me-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                <rect x="11" y="14" width="2" height="2" rx="1" fill="currentColor"></rect>
                                <rect x="11" y="7" width="2" height="5" rx="1" fill="currentColor"></rect>
                            </svg>
                        </span>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Gagal</h4>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Database Sync Card -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-dark">Sinkronisasi Database Master</h3>
                            <span class="text-muted mt-1 fw-bold fs-7">Menyinkronkan data transaksi harian (30 hari terakhir) secara inkremental dari cahayatasbihdb ke aplikasidb.</span>
                        </div>
                        <div class="card-toolbar">
                            <form action="{{ route('admin.sync-master') }}" method="POST" id="sync-db-form">
                                @csrf
                                <button type="submit" class="btn btn-primary fw-bolder" id="btn-sync-submit">
                                    <i class="fas fa-database me-2"></i> Sinkronkan Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <div class="bg-light-primary rounded p-5 mb-4">
                            <div class="d-flex flex-stack flex-wrap gap-2">
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-4">
                                        <i class="fas fa-history text-primary fs-1"></i>
                                    </div>
                                    <div class="flex-column">
                                        <span class="text-gray-800 fw-bold fs-6 block">Status Sinkronisasi Terakhir</span>
                                        <div class="text-muted fs-7 mt-1">
                                            @if (isset($syncStatus) && $syncStatus)
                                                Mulai: {{ $syncStatus['started_at'] }} 
                                                @if ($syncStatus['finished_at'])
                                                    | Selesai: {{ $syncStatus['finished_at'] }} ({{ $syncStatus['duration'] }})
                                                @endif
                                            @else
                                                Belum pernah dijalankan.
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    @if (!isset($syncStatus) || !$syncStatus)
                                        <span class="badge badge-light-warning fw-bolder fs-7 px-4 py-2">NEVER RUN</span>
                                    @elseif ($syncStatus['status'] === 'success')
                                        <span class="badge badge-light-success fw-bolder fs-7 px-4 py-2">SUCCESS</span>
                                    @elseif ($syncStatus['status'] === 'running')
                                        <span class="badge badge-light-primary fw-bolder fs-7 px-4 py-2">RUNNING</span>
                                    @else
                                        <span class="badge badge-light-danger fw-bolder fs-7 px-4 py-2">FAILED</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if (isset($syncStatus) && $syncStatus && $syncStatus['status'] === 'failed' && $syncStatus['error'])
                            <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column p-5 mb-4">
                                <h4 class="mb-1 text-danger font-bold">Pesan Error Sinkronisasi:</h4>
                                <span class="font-mono fs-7">{{ $syncStatus['error'] }}</span>
                            </div>
                        @endif

                        @if (isset($syncStatus) && $syncStatus && !empty($syncStatus['report']))
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <thead>
                                        <tr class="fw-bolder text-muted">
                                            <th class="min-w-200px">Nama Tabel</th>
                                            <th class="min-w-100px text-center">Status</th>
                                            <th class="min-w-150px text-end">Jumlah Data Disinkronkan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($syncStatus['report'] as $table => $info)
                                            <tr>
                                                <td>
                                                    <span class="text-dark fw-bolder fs-6">{{ $table }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($info['status'] === 'success')
                                                        <span class="badge badge-light-success fw-bolder fs-8">SUCCESS</span>
                                                    @elseif ($info['status'] === 'skipped')
                                                        <span class="badge badge-light-warning fw-bolder fs-8">SKIPPED</span>
                                                    @else
                                                        <span class="badge badge-light-danger fw-bolder fs-8">FAILED</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-dark fw-bold fs-6">
                                                        {{ isset($info['rows_synced']) ? number_format($info['rows_synced']) . ' baris' : '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-dark">Dashboard Diagnostik Sistem</h3>
                            <span class="text-muted mt-1 fw-bold fs-7">Menjalankan naskah pemeriksaan integritas basis data & penyimpanan secara real-time.</span>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <div class="row g-5">
                            @php
                                $descriptions = [
                                    'audit_ghost_timestamps.php' => 'Memvalidasi timestamp & anomali data pada tagihan yang terhapus.',
                                    'cleanup_ghost_bills.php' => 'Membersihkan tagihan yatim yang tidak terhubung dengan tipe tagihan aktif.',
                                    'find_ghost_bills.php' => 'Mendeteksi keberadaan tagihan tanpa relasi tipe tagihan.',
                                    'find_duplicate_bill_types.php' => 'Memindai duplikasi tipe tagihan di sistem.',
                                    'check_image.php' => 'Memeriksa keberadaan fisik berkas bukti pembayaran di storage.',
                                    'check_avatars.php' => 'Mendeteksi foto avatar santri yang terdaftar tetapi file fisiknya hilang.',
                                    'check_bills.php' => 'Pemeriksaan integritas relasi tabel tagihan secara menyeluruh.',
                                ];
                            @endphp

                            @foreach ($results as $script => $data)
                                <div class="col-12">
                                    <div class="card border border-dashed border-gray-300 card-bordered p-6 mb-2">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center">
                                                <!-- Icon Indicator -->
                                                <div class="me-4">
                                                    @if ($data['status'] === 0)
                                                        <span class="svg-icon svg-icon-2hx svg-icon-success">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                                                <path d="M10.4343 12.4343L8.75 10.75C8.33579 10.3358 7.66421 10.3358 7.25 10.75C6.83579 11.1642 6.83579 11.8358 7.25 12.25L9.69289 14.6929C10.0834 15.0834 10.7166 15.0834 11.1071 14.6929L16.75 9.05C17.1642 8.63579 17.1642 7.96421 16.75 7.55C16.3358 7.13579 15.6642 7.13579 15.25 7.55L10.4343 12.4343Z" fill="currentColor"></path>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <span class="svg-icon svg-icon-2hx svg-icon-danger">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                                                <rect x="11" y="14" width="2" height="2" rx="1" fill="currentColor"></rect>
                                                                <rect x="11" y="7" width="2" height="5" rx="1" fill="currentColor"></rect>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Script info -->
                                                <div>
                                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                                        <span class="fs-6 fw-bold text-dark me-2">{{ $script }}</span>
                                                        @if ($data['status'] === 0)
                                                            <span class="badge badge-light-success fw-bolder fs-8 px-2.5 py-1">CLEAN (0)</span>
                                                        @elseif ($data['status'] === -1)
                                                            <span class="badge badge-light-warning fw-bolder fs-8 px-2.5 py-1">MISSING (-1)</span>
                                                        @else
                                                            <span class="badge badge-light-danger fw-bolder fs-8 px-2.5 py-1">WARNING ({{ $data['status'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted fs-7 mt-1">{{ $descriptions[$script] ?? 'Pemeriksaan diagnostik kustom.' }}</div>
                                                </div>
                                            </div>
                                            <!-- Action Toggle -->
                                            <div>
                                                <button class="btn btn-sm btn-light btn-active-light-primary fw-bolder" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#collapse-{{ Str::slug($script) }}">
                                                    Lihat Log Output
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Expandable output logs -->
                                        <div class="collapse mt-4" id="collapse-{{ Str::slug($script) }}">
                                            <div class="rounded bg-gray-100 p-5 font-mono text-dark overflow-auto fs-7" style="max-height: 350px; white-space: pre-wrap;">
                                                @if (empty($data['output']))
                                                    <span class="text-muted italic">Naskah tidak menghasilkan log output apapun.</span>
                                                @else
                                                    {!! e($data['output']) !!}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
            <!--end::Container-->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('sync-db-form');
                if (form) {
                    form.addEventListener('submit', function() {
                        var btn = document.getElementById('btn-sync-submit');
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sinkronisasi Sedang Berjalan...';
                        }
                    });
                }
            });
        </script>
    </div>
@endsection
