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

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center p-5 mb-6" style="border-radius: 16px;">
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

                <!-- Detailed Discrepancy Table Card -->
                <div class="card card-flush shadow-sm mb-6" style="border-radius: 24px; background: #ffffff;">
                    <div class="card-header border-0 pt-6 px-6 bg-transparent">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-slate-800 fs-5" style="color: #1e293b;">Detil Audit & Perbandingan Siswa</h3>
                            <span class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">
                                Menampilkan rekor siswa bermasalah (default 10 rekor per halaman).
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-6 pt-2">
                        
                        <!-- Search Box & Bulk Actions Toolbar -->
                        <form action="{{ route('admin.audit.diagnostics') }}" method="GET" class="mb-6">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 max-w-400px">
                                    <div class="position-relative w-100">
                                        <i class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-400"></i>
                                        <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari Nama atau NIS Siswa..." value="{{ request('search') }}">
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold px-4">
                                        <i class="fas fa-search me-1"></i> Cari
                                    </button>
                                    @if(request()->filled('search'))
                                        <a href="{{ route('admin.audit.diagnostics') }}" class="btn btn-light btn-active-light-primary fw-bold px-3">
                                            Reset
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        @if ($comparison['discrepancies']->isEmpty())
                            <div class="alert bg-light-success border border-success d-flex align-items-center p-5 rounded-[16px]">
                                <i class="fas fa-check-circle text-success fs-1 me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-dark">Tidak Ada Rekor Bermasalah</h4>
                                    <span class="text-slate-600 fs-7">
                                        @if(request()->filled('search'))
                                            Tidak ditemukan siswa bermasalah dengan kata kunci "{{ request('search') }}".
                                        @else
                                            Seluruh data siswa, UPT, kelas, saldo, dan tagihan telah ter-inkorporasi dan sinkron sepenuhnya dengan Database Master.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('admin.audit.sync-selected-students') }}" method="POST" id="form-sync-selected">
                                @csrf
                                
                                <div class="d-flex align-items-center justify-content-between mb-4 bg-light-primary p-4 rounded-[12px]">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" id="check-all-students">
                                            <label class="form-check-label fw-bold text-gray-700 fs-7" for="check-all-students">
                                                Pilih Semua di Halaman Ini
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-sync-selected" class="btn btn-sm btn-success fw-bold text-white px-4 d-none" style="background-color: #10B981; border: none;">
                                        <i class="fas fa-sync-alt me-2 text-white"></i> Sinkronkan Siswa Terpilih (<span id="selected-count">0</span>)
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle gs-4 gy-4 border-gray-200">
                                        <thead>
                                            <tr class="fw-bolder text-muted bg-light text-center">
                                                <th class="w-40px text-center">#</th>
                                                <th class="ps-4 text-start min-w-150px">Siswa & NIS</th>
                                                <th class="min-w-120px">Kategori Properti</th>
                                                <th class="min-w-180px">Log Data Awal (Lokal)</th>
                                                <th class="min-w-180px">Database Lama (Master)</th>
                                                <th class="min-w-180px">Hasil Sinkronisasi (Target)</th>
                                                <th class="min-w-80px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($comparison['discrepancies'] as $index => $item)
                                                <!-- Row 1: UPT/Lembaga -->
                                                <tr style="border-top: 2px solid #cbd5e1;">
                                                    <td rowspan="7" class="text-center align-top bg-light-light pt-4">
                                                        <div class="form-check form-check-custom form-check-solid justify-content-center">
                                                            <input class="form-check-input student-select-checkbox" type="checkbox" name="student_ids[]" value="{{ $item['id'] }}">
                                                        </div>
                                                    </td>
                                                    <td rowspan="7" class="ps-4 text-start align-top bg-light-light pt-4">
                                                        <div class="fw-bolder text-slate-800 fs-6">{{ $item['name'] }}</div>
                                                        <div class="text-muted font-monospace fs-7 mt-1">NIS: {{ $item['nis'] }}</div>
                                                        
                                                        @if (!$item['local']['exists'])
                                                            <div class="mt-3"><span class="badge badge-light-warning">Baru (Belum Ada)</span></div>
                                                        @else
                                                            <div class="mt-3"><span class="badge badge-light-danger">Butuh Sinkron</span></div>
                                                        @endif

                                                        <div class="mt-4">
                                                            <button type="button" class="btn btn-sm btn-light-primary fw-bolder btn-detail-modal py-1 px-3" data-json="{{ json_encode($item) }}">
                                                                <i class="fas fa-eye me-1"></i> Lihat Detil
                                                            </button>
                                                        </div>
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
                            </form>

                            <!-- Pagination Controls -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap my-4">
                                <div class="text-gray-600 fs-7 my-2">
                                    Menampilkan {{ $comparison['discrepancies']->firstItem() ?? 0 }} sampai {{ $comparison['discrepancies']->lastItem() ?? 0 }} dari {{ $comparison['discrepancies']->total() }} siswa bermasalah.
                                </div>
                                <div class="my-2">
                                    {!! $comparison['discrepancies']->appends(request()->query())->links('pagination::bootstrap-4') !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Diagnostic Scripts Collapse Section (Legacy Audit) -->
                <div class="card card-flush shadow-sm mb-6" style="border-radius: 24px; background: #ffffff;">
                    <div class="card-header border-0 pt-6 px-6 bg-transparent">
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

    <!-- Modal Detil Perubahan Data Siswa -->
    <div class="modal fade" id="modalStudentDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 24px;">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h3 class="modal-title fw-bolder text-slate-800" id="modal-student-name">Detil Perubahan Data Siswa</h3>
                        <span class="text-muted fs-7" id="modal-student-nis">NIS: -</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-6">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle gs-4 gy-4 border-gray-200">
                            <thead>
                                <tr class="fw-bolder text-muted bg-light text-center">
                                    <th>Properti</th>
                                    <th>Data Awal (Lokal)</th>
                                    <th>Database Lama (Master)</th>
                                    <th>Target Hasil Sinkron</th>
                                </tr>
                            </thead>
                            <tbody id="modal-detail-tbody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Tutup</button>
                    <form action="{{ route('admin.audit.sync-selected-students') }}" method="POST" id="form-modal-single-sync">
                        @csrf
                        <input type="hidden" name="student_ids[]" id="modal-student-id-input">
                        <button type="submit" class="btn btn-success fw-bold text-white" style="background-color: #10B981; border: none;">
                            <i class="fas fa-sync-alt me-1 text-white"></i> Sinkronkan Siswa Ini
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Async AJAX loading for AI Insight
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

            // Check All Students Checkbox handler
            const checkAll = document.getElementById('check-all-students');
            const rowCheckboxes = document.querySelectorAll('.student-select-checkbox');
            const btnSyncSelected = document.getElementById('btn-sync-selected');
            const selectedCountSpan = document.getElementById('selected-count');

            function updateSelectedCount() {
                const checked = document.querySelectorAll('.student-select-checkbox:checked');
                const count = checked.length;
                selectedCountSpan.textContent = count;
                if (count > 0) {
                    btnSyncSelected.classList.remove('d-none');
                } else {
                    btnSyncSelected.classList.add('d-none');
                }
            }

            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(cb => cb.checked = checkAll.checked);
                    updateSelectedCount();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    if (!this.checked && checkAll) {
                        checkAll.checked = false;
                    }
                    updateSelectedCount();
                });
            });

            // Detail Modal Click Handler
            document.querySelectorAll('.btn-detail-modal').forEach(btn => {
                btn.addEventListener('click', function () {
                    const data = JSON.parse(this.getAttribute('data-json'));
                    
                    document.getElementById('modal-student-name').textContent = 'Detil Perubahan: ' + data.name;
                    document.getElementById('modal-student-nis').textContent = 'NIS: ' + (data.nis || '-');
                    document.getElementById('modal-student-id-input').value = data.id;

                    const props = [
                        { label: 'UPT / Lembaga', key: 'school', type: 'text' },
                        { label: 'Kelas', key: 'class', type: 'text' },
                        { label: 'Tahun Ajaran', key: 'academic_year', type: 'text' },
                        { label: 'Saldo Utama', key: 'saldo', type: 'currency' },
                        { label: 'Tabungan', key: 'saving', type: 'currency' },
                        { label: 'Jumlah Tagihan', key: 'bills_count', type: 'count' },
                        { label: 'Transaksi Saldo', key: 'tx_count', type: 'count' }
                    ];

                    let html = '';
                    props.forEach(p => {
                        let localVal = data.local[p.key];
                        let masterVal = data.master[p.key];
                        let syncVal = data.sync_result[p.key];

                        if (p.type === 'currency') {
                            localVal = 'Rp ' + Number(localVal).toLocaleString('id-ID');
                            masterVal = 'Rp ' + Number(masterVal).toLocaleString('id-ID');
                            syncVal = 'Rp ' + Number(syncVal).toLocaleString('id-ID');
                        } else if (p.type === 'count') {
                            localVal = localVal + ' Rekor';
                            masterVal = masterVal + ' Rekor';
                            syncVal = syncVal + ' Rekor';
                        }

                        const isDiff = data.local[p.key] != data.master[p.key];
                        const diffClass = isDiff ? 'bg-light-danger text-danger fw-bold' : '';

                        html += `
                            <tr>
                                <td class="fw-bold text-gray-700">${p.label}</td>
                                <td class="text-center ${diffClass}">${localVal}</td>
                                <td class="text-center">${masterVal}</td>
                                <td class="text-center fw-bold text-emerald-600" style="color: #10B981;">${syncVal}</td>
                            </tr>
                        `;
                    });

                    document.getElementById('modal-detail-tbody').innerHTML = html;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalStudentDetail'));
                    modal.show();
                });
            });
        });
    </script>
@endsection
