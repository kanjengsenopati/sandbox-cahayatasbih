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
        </div>
        <!--end::Post-->
    </div>
@endsection
