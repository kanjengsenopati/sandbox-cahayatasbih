@extends('layouts.master', ['title' => 'Diagnostik Sistem'])

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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Diagnostik Sistem</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="#" class="text-muted text-hover-primary">Audit dan Sinkron</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Diagnostik Sistem & Audit
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('admin.audit.diagnostics', ['refresh' => 1]) }}"
                       id="btn-toolbar-audit"
                       class="btn btn-sm btn-primary fw-bolder">
                        <i class="fas fa-sync-alt me-1 fs-7 text-white"></i> Jalankan Ulang Audit & Analisis AI
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
                    <div class="alert alert-success d-flex align-items-center p-5 mb-6" style="border-radius: 16px;">
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

                <!-- Summary Cards Row -->
                <div class="row g-6 mb-6">
                    <!-- Students Count Summary -->
                    <div class="col-md-4">
                        <div class="card card-flush shadow-sm h-100" style="border-radius: 24px; background: #ffffff;">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="fas fa-user-graduate text-primary fs-4"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-slate-400 fw-bold fs-7 uppercase" style="font-size: 11px; text-transform: uppercase; color: #94a3b8; tracking-widest: 0.1em;">Jumlah Siswa</span>
                                        <span class="text-slate-800 fw-bolder fs-3" style="color: #1e293b;">{{ number_format($comparison['summaries']['students']['local']) }} Santri</span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center fs-7 text-muted">
                                    <span>Database Lama: <strong>{{ number_format($comparison['summaries']['students']['master']) }}</strong></span>
                                    @if ($comparison['summaries']['students']['diff'] != 0)
                                        <span class="badge {{ $comparison['summaries']['students']['diff'] > 0 ? 'badge-light-danger' : 'badge-light-success' }} fw-bold">
                                            {{ $comparison['summaries']['students']['diff'] > 0 ? '+' : '' }}{{ $comparison['summaries']['students']['diff'] }} Selisih
                                        </span>
                                    @else
                                        <span class="badge badge-light-success fw-bold">Sinkron</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Saldo Summary -->
                    <div class="col-md-4">
                        <div class="card card-flush shadow-sm h-100" style="border-radius: 24px; background: #ffffff;">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-success">
                                            <i class="fas fa-wallet text-emerald-600 fs-4" style="color: #10B981;"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-slate-400 fw-bold fs-7 uppercase" style="font-size: 11px; text-transform: uppercase; color: #94a3b8; tracking-widest: 0.1em;">Total Saldo Santri</span>
                                        <span class="text-emerald-600 fw-bolder fs-3" style="color: #10B981;">Rp {{ number_format($comparison['summaries']['saldo']['local'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center fs-7 text-muted">
                                    <span>Database Lama: <strong>Rp {{ number_format($comparison['summaries']['saldo']['master'], 0, ',', '.') }}</strong></span>
                                    @if ($comparison['summaries']['saldo']['diff'] != 0)
                                        <span class="badge {{ $comparison['summaries']['saldo']['diff'] > 0 ? 'badge-light-danger' : 'badge-light-success' }} fw-bold">
                                            Rp {{ number_format($comparison['summaries']['saldo']['diff'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="badge badge-light-success fw-bold">Sinkron</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tagihan Summary -->
                    <div class="col-md-4">
                        <div class="card card-flush shadow-sm h-100" style="border-radius: 24px; background: #ffffff;">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="fas fa-file-invoice text-warning fs-4"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-slate-400 fw-bold fs-7 uppercase" style="font-size: 11px; text-transform: uppercase; color: #94a3b8; tracking-widest: 0.1em;">Total Tagihan</span>
                                        <span class="text-slate-800 fw-bolder fs-3" style="color: #1e293b;">{{ number_format($comparison['summaries']['bills']['local']) }} Tagihan</span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center fs-7 text-muted">
                                    <span>Database Lama: <strong>{{ number_format($comparison['summaries']['bills']['master']) }}</strong></span>
                                    @if ($comparison['summaries']['bills']['diff'] != 0)
                                        <span class="badge {{ $comparison['summaries']['bills']['diff'] > 0 ? 'badge-light-danger' : 'badge-light-success' }} fw-bold">
                                            {{ $comparison['summaries']['bills']['diff'] > 0 ? '+' : '' }}{{ $comparison['summaries']['bills']['diff'] }} Selisih
                                        </span>
                                    @else
                                        <span class="badge badge-light-success fw-bold">Sinkron</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Insight Card (Async AJAX Loading) -->
                <div class="card card-flush shadow-sm mb-6" style="border-radius: 24px; background: linear-gradient(to right, #fdf8ff, #f9f0ff); border: 1px solid #ebd5ff;">
                    <div class="card-body p-6">
                        <div class="d-flex align-items-center mb-4 justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-purple-100">
                                        <i class="fas fa-magic text-purple-600 fs-4"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <h3 class="card-label fw-bolder text-purple-800 fs-5 mb-0">AI Insight & Analisis Auditor</h3>
                                    <span class="text-purple-400 fs-7">Analisis kecocokan data real-time berbasis AI</span>
                                </div>
                            </div>
                            <span class="badge bg-purple-600 text-white font-semibold px-3 py-1 text-uppercase fs-8" style="background-color: #8b5cf6;">Gemini Powered</span>
                        </div>
                        <div id="ai-insight-container" class="fs-6 text-slate-700 leading-relaxed ps-2">
                            <div class="d-flex align-items-center py-4 text-purple-600">
                                <div class="spinner-border spinner-border-sm me-3 text-purple-600" role="status"></div>
                                <span class="fw-bold fs-7">Sedang memuat analisis AI Insight otomatis...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Discrepancy Table -->
                <div class="card card-flush shadow-sm mb-6" style="border-radius: 24px; background: #ffffff;">
                    <div class="card-header border-0 pt-6 px-6">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-slate-800 fs-5" style="color: #1e293b;">Detil Audit & Perbandingan Siswa</h3>
                            <span class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">
                                Daftar santri yang memiliki perbedaan data UPT, kelas, tahun ajaran, saldo, tabungan, tagihan, atau transaksi antara data awal dan master lama.
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-6 pt-2">
                        @if (empty($comparison['discrepancies']))
                            <div class="alert bg-light-success border border-success d-flex align-items-center p-5 rounded-[16px]">
                                <i class="fas fa-check-circle text-success fs-1 me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-dark">Integritas 100% Cocok</h4>
                                    <span class="text-slate-600 fs-7">Seluruh data siswa, UPT, kelas, saldo, dan tagihan telah ter-inkorporasi dan sinkron sepenuhnya dengan Database Master.</span>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle gs-4 gy-4 border-gray-200">
                                    <thead>
                                        <tr class="fw-bolder text-muted bg-light text-center">
                                            <th class="ps-4 text-start min-w-150px">Siswa & NIS</th>
                                            <th class="min-w-120px">Kategori Properti</th>
                                            <th class="min-w-180px">Log Data Awal (Lokal)</th>
                                            <th class="min-w-180px">Database Lama (Master)</th>
                                            <th class="min-w-180px">Hasil Sinkronisasi (Target)</th>
                                            <th class="min-w-80px">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comparison['discrepancies'] as $index => $item)
                                            <!-- Row 1: UPT/Lembaga -->
                                            <tr style="border-top: 2px solid #cbd5e1;">
                                                <td rowspan="7" class="ps-4 text-start align-top bg-light-light">
                                                    <div class="fw-bolder text-slate-800 fs-6">{{ $item['name'] }}</div>
                                                    <div class="text-muted font-monospace fs-7 mt-1">NIS: {{ $item['nis'] }}</div>
                                                    
                                                    @if (!$item['local']['exists'])
                                                        <div class="mt-3"><span class="badge badge-light-warning">Baru (Belum Ada)</span></div>
                                                    @else
                                                        <div class="mt-3"><span class="badge badge-light-danger">Butuh Sinkron</span></div>
                                                    @endif
                                                </td>
                                                <td class="fw-semibold text-gray-700 fs-7">UPT/Lembaga</td>
                                                <td class="fs-7 text-center {{ $item['local']['school'] !== $item['master']['school'] ? 'bg-light-danger text-danger fw-bold' : '' }}">{{ $item['local']['school'] }}</td>
                                                <td class="fs-7 text-center">{{ $item['master']['school'] }}</td>
                                                <td class="fs-7 text-center fw-bold text-primary">{{ $item['sync_result']['school'] }}</td>
                                                <td class="text-center">
                                                    @if($item['local']['school'] !== $item['master']['school'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Row 2: Kelas -->
                                            <tr>
                                                <td class="fw-semibold text-gray-700 fs-7">Kelas</td>
                                                <td class="fs-7 text-center {{ $item['local']['class'] !== $item['master']['class'] ? 'bg-light-danger text-danger fw-bold' : '' }}">{{ $item['local']['class'] }}</td>
                                                <td class="fs-7 text-center">{{ $item['master']['class'] }}</td>
                                                <td class="fs-7 text-center fw-bold text-primary">{{ $item['sync_result']['class'] }}</td>
                                                <td class="text-center">
                                                    @if($item['local']['class'] !== $item['master']['class'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Row 3: Tahun Ajaran -->
                                            <tr>
                                                <td class="fw-semibold text-gray-700 fs-7">Tahun Ajaran</td>
                                                <td class="fs-7 text-center {{ $item['local']['academic_year'] !== $item['master']['academic_year'] ? 'bg-light-danger text-danger fw-bold' : '' }}">{{ $item['local']['academic_year'] }}</td>
                                                <td class="fs-7 text-center">{{ $item['master']['academic_year'] }}</td>
                                                <td class="fs-7 text-center fw-bold text-primary">{{ $item['sync_result']['academic_year'] }}</td>
                                                <td class="text-center">
                                                    @if($item['local']['academic_year'] !== $item['master']['academic_year'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Row 4: Saldo -->
                                            <tr>
                                                <td class="fw-semibold text-gray-700 fs-7">Saldo Utama</td>
                                                <td class="fs-7 text-center {{ $item['local']['saldo'] != $item['master']['saldo'] ? 'bg-light-danger text-danger fw-bold' : '' }}">Rp {{ number_format($item['local']['saldo'], 0, ',', '.') }}</td>
                                                <td class="fs-7 text-center">Rp {{ number_format($item['master']['saldo'], 0, ',', '.') }}</td>
                                                <td class="fs-7 text-center fw-bold text-emerald-600" style="color: #10B981;">Rp {{ number_format($item['sync_result']['saldo'], 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if($item['local']['saldo'] != $item['master']['saldo'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Row 5: Tabungan -->
                                            <tr>
                                                <td class="fw-semibold text-gray-700 fs-7">Tabungan</td>
                                                <td class="fs-7 text-center {{ $item['local']['saving'] != $item['master']['saving'] ? 'bg-light-danger text-danger fw-bold' : '' }}">Rp {{ number_format($item['local']['saving'], 0, ',', '.') }}</td>
                                                <td class="fs-7 text-center">Rp {{ number_format($item['master']['saving'], 0, ',', '.') }}</td>
                                                <td class="fs-7 text-center fw-bold text-emerald-600" style="color: #10B981;">Rp {{ number_format($item['sync_result']['saving'], 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if($item['local']['saving'] != $item['master']['saving'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Row 6: Tagihan -->
                                            <tr>
                                                <td class="fw-semibold text-gray-700 fs-7">Jumlah Tagihan</td>
                                                <td class="fs-7 text-center {{ $item['local']['bills_count'] != $item['master']['bills_count'] ? 'bg-light-danger text-danger fw-bold' : '' }}">
                                                    {{ $item['local']['bills_count'] }} Tagihan<br>
                                                    <span class="text-muted font-monospace" style="font-size: 10px;">(Rp {{ number_format($item['local']['bills_total'], 0, ',', '.') }})</span>
                                                </td>
                                                <td class="fs-7 text-center">
                                                    {{ $item['master']['bills_count'] }} Tagihan<br>
                                                    <span class="text-muted font-monospace" style="font-size: 10px;">(Rp {{ number_format($item['master']['bills_total'], 0, ',', '.') }})</span>
                                                </td>
                                                <td class="fs-7 text-center fw-bold text-primary">
                                                    {{ $item['sync_result']['bills_count'] }} Tagihan<br>
                                                    <span class="text-muted font-monospace" style="font-size: 10px;">(Rp {{ number_format($item['sync_result']['bills_total'], 0, ',', '.') }})</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($item['local']['bills_count'] != $item['master']['bills_count'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Row 7: Transaksi -->
                                            <tr>
                                                <td class="fw-semibold text-gray-700 fs-7">Transaksi Saldo</td>
                                                <td class="fs-7 text-center {{ $item['local']['tx_count'] != $item['master']['tx_count'] ? 'bg-light-danger text-danger fw-bold' : '' }}">{{ $item['local']['tx_count'] }} Log</td>
                                                <td class="fs-7 text-center">{{ $item['master']['tx_count'] }} Log</td>
                                                <td class="fs-7 text-center fw-bold text-primary">{{ $item['sync_result']['tx_count'] }} Log</td>
                                                <td class="text-center">
                                                    @if($item['local']['tx_count'] != $item['master']['tx_count'])
                                                        <span class="badge badge-light-danger fs-9">Berubah</span>
                                                    @else
                                                        <span class="badge badge-light-success fs-9">Sama</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Diagnostic Scripts Collapse Section (Legacy Audit) -->
                <div class="card card-flush shadow-sm mb-6" style="border-radius: 24px; background: #ffffff;">
                    <div class="card-header border-0 pt-6 px-6">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-slate-800 fs-5" style="color: #1e293b;">Eksekusi Script Diagnostik Integritas</h3>
                            <span class="text-muted mt-1 fw-bold fs-7">Hasil eksekusi naskah pemeriksaan integritas basis data lokal &amp; penyimpanan.</span>
                        </div>
                    </div>
                    <div class="card-body p-6 pt-2">
                        <div class="row g-5">
                            @php
                                $descriptions = [
                                    'audit_ghost_timestamps.php' => 'Memvalidasi timestamp & anomali data pada tagihan yang terhapus.',
                                    'cleanup_ghost_bills.php' => 'Membersihkan tagihan yatim yang tidak terhubung dengan tipe tagihan aktif.',
                                    'find_ghost_bills.php' => 'Mendeteksi keberadaan tagihan tanpa relasi tipe tagihan.',
                                    'find_duplicate_bill_types.php' => 'Memindai duplikasi tipe tagihan di sistem.',
                                    'find_duplicate_students.php' => 'Mendeteksi profil siswa dengan nama ganda.',
                                    'check_image.php' => 'Memeriksa keberadaan fisik berkas bukti pembayaran di storage.',
                                    'check_avatars.php' => 'Mendeteksi foto avatar santri yang terdaftar tetapi file fisiknya hilang.',
                                    'check_bills.php' => 'Pemeriksaan integritas relasi tabel tagihan secara menyeluruh.',
                                ];
                            @endphp

                            @foreach ($results as $script => $data)
                                <div class="col-12">
                                    <div class="card border border-dashed border-gray-300 card-bordered p-5 mb-2" style="border-radius: 16px;">
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
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>

    <!-- Async AJAX script for loading AI Insight without blocking HTTP response -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('{{ route("admin.audit.diagnostics.ai-insight") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('ai-insight-container');
                    if (data && data.html) {
                        container.innerHTML = data.html;
                    } else {
                        container.innerHTML = '<span class="text-muted fs-7">Tidak ada rekomendasi AI Insight.</span>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching AI Insight:', error);
                    const container = document.getElementById('ai-insight-container');
                    container.innerHTML = '<span class="text-danger fs-7"><i class="fas fa-exclamation-circle me-1"></i> Gagal memuat AI Insight secara otomatis.</span>';
                });
        });
    </script>
@endsection
