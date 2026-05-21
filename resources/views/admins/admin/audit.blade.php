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
                <!--begin::Actions - dinamis per tab-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    {{-- Tombol Tab Diagnostik --}}
                    <a href="{{ route('admin.audit') }}"
                       id="btn-toolbar-audit"
                       class="btn btn-sm btn-primary fw-bolder"
                       style="display:none;">
                        <i class="fas fa-sync-alt me-1 fs-7"></i> Jalankan Ulang Audit
                    </a>
                    {{-- Tombol Tab Sinkronisasi --}}
                    <div id="btn-toolbar-sync" style="display:none;">
                        <form action="{{ route('admin.sync-master') }}" method="POST" id="sync-db-form">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary fw-bolder" id="btn-sync-submit">
                                <i class="fas fa-database me-1 fs-7"></i> Sinkronkan Sekarang
                            </button>
                        </form>
                    </div>
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
                @include('admins.partials.tabs-aplikasi')

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

                <!-- Tab Navigation -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-body py-0 px-0">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder px-6" id="audit-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary py-5 me-6 {{ session('success') || session('error') ? 'active' : 'active' }}"
                                   id="tab-sync-trigger"
                                   data-bs-toggle="tab"
                                   href="#tab-sync"
                                   role="tab"
                                   aria-controls="tab-sync"
                                   aria-selected="true">
                                    <i class="fas fa-database me-2 fs-6"></i>
                                    Sinkronisasi Database
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary py-5 me-6"
                                   id="tab-diagnostik-trigger"
                                   data-bs-toggle="tab"
                                   href="#tab-diagnostik"
                                   role="tab"
                                   aria-controls="tab-diagnostik"
                                   aria-selected="false">
                                    <i class="fas fa-stethoscope me-2 fs-6"></i>
                                    Diagnostik Sistem
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="audit-tabs-content">

                    {{-- ===== TAB 1: SINKRONISASI DB ===== --}}
                    <div class="tab-pane fade show active" id="tab-sync" role="tabpanel" aria-labelledby="tab-sync-trigger">
                        <div class="card card-flush shadow-sm mb-6">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title flex-column">
                                    <h3 class="card-label fw-bolder text-dark">Sinkronisasi Database Master</h3>
                                    <span class="text-muted mt-1 fw-bold fs-7">
                                        Menyinkronkan data transaksi harian (30 hari terakhir) secara inkremental dari
                                        <code>cahayatasbihdb</code> ke <code>aplikasidb</code>.
                                    </span>
                                </div>
                            </div>
                            <div class="card-body py-4">

                                <!-- Status Banner -->
                                <div class="bg-light-primary rounded p-5 mb-6">
                                    <div class="d-flex flex-stack flex-wrap gap-2">
                                        <div class="d-flex align-items-center me-3">
                                            <div class="me-4">
                                                <i class="fas fa-history text-primary fs-1"></i>
                                            </div>
                                            <div class="flex-column">
                                                <span class="text-gray-800 fw-bold fs-6">Status Sinkronisasi Terakhir</span>
                                                <div class="text-muted fs-7 mt-1">
                                                    @if (isset($syncStatus) && $syncStatus)
                                                        Mulai: {{ $syncStatus['started_at'] }}
                                                        @if ($syncStatus['finished_at'])
                                                            &nbsp;|&nbsp; Selesai: {{ $syncStatus['finished_at'] }}
                                                            &nbsp;({{ $syncStatus['duration'] }})
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
                                                <span class="badge badge-light-success fw-bolder fs-7 px-4 py-2">
                                                    <i class="fas fa-check me-1"></i> SUCCESS
                                                </span>
                                            @elseif ($syncStatus['status'] === 'running')
                                                <span class="badge badge-light-primary fw-bolder fs-7 px-4 py-2">
                                                    <i class="fas fa-spinner fa-spin me-1"></i> RUNNING
                                                </span>
                                            @else
                                                <span class="badge badge-light-danger fw-bolder fs-7 px-4 py-2">
                                                    <i class="fas fa-times me-1"></i> FAILED
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Error message -->
                                @if (isset($syncStatus) && $syncStatus && $syncStatus['status'] === 'failed' && !empty($syncStatus['error']))
                                    <div class="alert bg-light-danger border border-danger d-flex flex-column p-5 mb-6">
                                        <h5 class="mb-1 text-danger fw-bold">
                                            <i class="fas fa-exclamation-triangle me-2"></i> Pesan Error Sinkronisasi:
                                        </h5>
                                        <code class="fs-7 text-dark">{{ $syncStatus['error'] }}</code>
                                    </div>
                                @endif

                                <!-- Per-table report -->
                                @if (isset($syncStatus) && $syncStatus && !empty($syncStatus['report']))
                                    <div class="separator separator-dashed mb-4"></div>
                                    <h5 class="fw-bolder text-dark mb-4">Laporan Per Tabel</h5>
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                            <thead>
                                                <tr class="fw-bolder text-muted bg-light">
                                                    <th class="min-w-200px ps-4 rounded-start">Nama Tabel</th>
                                                    <th class="min-w-100px text-center">Status</th>
                                                    <th class="min-w-150px text-end pe-4 rounded-end">Data Disinkronkan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($syncStatus['report'] as $table => $info)
                                                    <tr>
                                                        <td class="ps-4">
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
                                                        <td class="text-end pe-4">
                                                             <span class="text-dark fw-bold fs-6">
                                                                 {{ isset($info['rows_synced']) ? number_format($info['rows_synced']) . ' baris' : '-' }}
                                                             </span>
                                                             @if (isset($info['min_date']) && $info['min_date'])
                                                                 <div class="text-muted fs-8 mt-1">
                                                                     <i class="far fa-calendar-alt me-1 fs-9 text-slate-400"></i>
                                                                     {{ \Carbon\Carbon::parse($info['min_date'])->format('d M Y') }} 
                                                                     s/d 
                                                                     {{ \Carbon\Carbon::parse($info['max_date'])->format('d M Y') }}
                                                                 </div>
                                                             @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-10">
                                        <i class="fas fa-database text-muted fs-3x mb-4"></i>
                                        <p class="text-muted fw-bold fs-6">Belum ada laporan sinkronisasi.</p>
                                        <p class="text-muted fs-7">Klik tombol <strong>Sinkronkan Sekarang</strong> di kanan atas untuk memulai.</p>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- ===== TAB 2: DIAGNOSTIK SISTEM ===== --}}
                    <div class="tab-pane fade" id="tab-diagnostik" role="tabpanel" aria-labelledby="tab-diagnostik-trigger">
                        <div class="card card-flush shadow-sm mb-6">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title flex-column">
                                    <h3 class="card-label fw-bolder text-dark">Dashboard Diagnostik Sistem</h3>
                                    <span class="text-muted mt-1 fw-bold fs-7">Menjalankan naskah pemeriksaan integritas basis data &amp; penyimpanan secara real-time.</span>
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
                                                                    <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1">CLEAN (0)</span>
                                                                @elseif ($data['status'] === -1)
                                                                    <span class="badge badge-light-warning fw-bolder fs-8 px-2 py-1">MISSING (-1)</span>
                                                                @else
                                                                    <span class="badge badge-light-danger fw-bolder fs-8 px-2 py-1">WARNING ({{ $data['status'] }})</span>
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
                                                            <i class="fas fa-terminal me-1"></i> Lihat Log Output
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Expandable output logs -->
                                                <div class="collapse mt-4" id="collapse-{{ Str::slug($script) }}">
                                                    <div class="rounded bg-gray-100 p-5 font-monospace text-dark overflow-auto fs-7" style="max-height: 350px; white-space: pre-wrap;">
                                                        @if (empty($data['output']))
                                                            <span class="text-muted fst-italic">Naskah tidak menghasilkan log output apapun.</span>
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

                </div>
                {{-- end::Tab Content --}}

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            var tabSync        = document.getElementById('tab-sync-trigger');
            var tabDiagnostik  = document.getElementById('tab-diagnostik-trigger');
            var btnSync        = document.getElementById('btn-toolbar-sync');
            var btnAudit       = document.getElementById('btn-toolbar-audit');

            function showSyncBtn()  { btnSync.style.display = 'block'; btnAudit.style.display = 'none'; }
            function showAuditBtn() { btnAudit.style.display = 'block'; btnSync.style.display = 'none'; }

            // Default: Tab Sync aktif
            showSyncBtn();

            tabSync.addEventListener('shown.bs.tab', showSyncBtn);
            tabDiagnostik.addEventListener('shown.bs.tab', showAuditBtn);

            // Jika redirect dari sync (session flash), tetap di tab sync
            @if (session('success') || session('error'))
                tabSync.click();
            @endif

            // Loading state saat form sync disubmit
            var form = document.getElementById('sync-db-form');
            if (form) {
                form.addEventListener('submit', function () {
                    var btn = document.getElementById('btn-sync-submit');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sinkronisasi Berjalan...';
                    }
                });
            }
        });
    </script>
@endsection
