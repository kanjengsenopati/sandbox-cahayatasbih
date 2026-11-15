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
                    <button type="button" id="btn-toolbar-audit" class="btn btn-sm btn-primary fw-bolder">
                        <i class="fas fa-sync-alt me-1 fs-7 text-white" id="icon-btn-audit"></i> 
                        <span id="text-btn-audit">Jalankan Ulang Audit & Analisis AI</span>
                    </button>
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
                                    <div class="d-flex flex-column flex-grow-1">
                                        <span class="text-slate-400 fw-bold fs-7 uppercase" style="font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em;">Jumlah Siswa</span>
                                        <span id="summary-students-local" class="text-slate-800 fw-bolder fs-3" style="color: #1e293b;">
                                            <span class="placeholder-glow"><span class="placeholder col-6"></span></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center fs-7 text-muted">
                                    <span id="summary-students-master">
                                        Database Lama: <span class="placeholder-glow"><span class="placeholder col-3"></span></span>
                                    </span>
                                    <span id="summary-students-diff">
                                        <span class="placeholder-glow"><span class="placeholder col-4"></span></span>
                                    </span>
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
                                    <div class="d-flex flex-column flex-grow-1">
                                        <span class="text-slate-400 fw-bold fs-7 uppercase" style="font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em;">Total Saldo Santri</span>
                                        <span id="summary-saldo-local" class="text-emerald-600 fw-bolder fs-3" style="color: #10B981;">
                                            <span class="placeholder-glow"><span class="placeholder col-7"></span></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center fs-7 text-muted">
                                    <span id="summary-saldo-master">
                                        Database Lama: <span class="placeholder-glow"><span class="placeholder col-4"></span></span>
                                    </span>
                                    <span id="summary-saldo-diff">
                                        <span class="placeholder-glow"><span class="placeholder col-4"></span></span>
                                    </span>
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
                                    <div class="d-flex flex-column flex-grow-1">
                                        <span class="text-slate-400 fw-bold fs-7 uppercase" style="font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em;">Total Tagihan</span>
                                        <span id="summary-bills-local" class="text-slate-800 fw-bolder fs-3" style="color: #1e293b;">
                                            <span class="placeholder-glow"><span class="placeholder col-6"></span></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center fs-7 text-muted">
                                    <span id="summary-bills-master">
                                        Database Lama: <span class="placeholder-glow"><span class="placeholder col-3"></span></span>
                                    </span>
                                    <span id="summary-bills-diff">
                                        <span class="placeholder-glow"><span class="placeholder col-4"></span></span>
                                    </span>
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
                    <div class="card-body p-6 pt-2" id="comparison-table-container">
                        <!-- Skeleton Loader for Table -->
                        <div class="p-4">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="placeholder-glow w-50">
                                    <span class="placeholder col-8 py-3 rounded"></span>
                                </div>
                                <div class="placeholder-glow w-25 text-end">
                                    <span class="placeholder col-6 py-3 rounded"></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr class="bg-light">
                                            <th style="width: 5%;"><span class="placeholder col-6"></span></th>
                                            <th style="width: 25%;"><span class="placeholder col-8"></span></th>
                                            <th style="width: 15%;"><span class="placeholder col-6"></span></th>
                                            <th style="width: 20%;"><span class="placeholder col-7"></span></th>
                                            <th style="width: 20%;"><span class="placeholder col-7"></span></th>
                                            <th style="width: 15%;"><span class="placeholder col-6"></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 0; $i < 4; $i++)
                                            <tr>
                                                <td><span class="placeholder-glow"><span class="placeholder col-12"></span></span></td>
                                                <td><span class="placeholder-glow"><span class="placeholder col-9 mb-1"></span><br><span class="placeholder col-6"></span></span></td>
                                                <td><span class="placeholder-glow"><span class="placeholder col-8"></span></span></td>
                                                <td><span class="placeholder-glow"><span class="placeholder col-7"></span></span></td>
                                                <td><span class="placeholder-glow"><span class="placeholder col-7"></span></span></td>
                                                <td><span class="placeholder-glow"><span class="placeholder col-6"></span></span></td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Integrity Diagnostics Card -->
                <div class="card card-flush shadow-sm mb-6" style="border-radius: 24px; background: #ffffff;">
                    <div class="card-header border-0 pt-6 px-6 bg-transparent">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-slate-800 fs-5" style="color: #1e293b;">Eksekusi Script Diagnostik Integritas</h3>
                            <span class="text-muted mt-1 fw-bold fs-7">Hasil eksekusi naskah pemeriksaan integritas basis data lokal &amp; penyimpanan.</span>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" id="btn-run-scripts-now" class="btn btn-sm btn-light-primary fw-bolder">
                                <i class="fas fa-play me-1"></i> <span id="text-btn-scripts">Jalankan Pindai Integritas</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-6 pt-2" id="scripts-container">
                        <div class="alert bg-light-primary border border-primary d-flex align-items-center justify-content-between p-5 rounded-[16px] flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-primary fs-1 me-4"></i>
                                <div class="d-flex flex-column">
                                    <h5 class="mb-1 text-dark">Naskah Diagnostik Integritas</h5>
                                    <span class="text-slate-600 fs-7">Pemeriksaan integritas tagihan, timestamp anomali, dan duplikasi data lokal siap dijalankan on-demand.</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary fw-bold px-4" onclick="document.getElementById('btn-run-scripts-now').click()">
                                <i class="fas fa-play me-1 text-white"></i> Mulai Pindai Sekarang
                            </button>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scriptsUrl = '{{ route("admin.audit.diagnostics.scripts") }}';

            // Load Diagnostic Integrity Scripts
            function loadScripts(isRefresh = false) {
                const container = document.getElementById('scripts-container');
                if (!container) return;

                if (isRefresh) {
                    container.innerHTML = `
                        <div class="d-flex align-items-center py-4 text-primary">
                            <div class="spinner-border spinner-border-sm me-3 text-primary" role="status"></div>
                            <span class="fw-bold fs-7">Menjalankan seluruh naskah diagnostik sistem...</span>
                        </div>
                    `;
                }

                const url = scriptsUrl + (isRefresh ? '?refresh=1' : '');

                fetch(url)
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (data && data.html) {
                            container.innerHTML = data.html;
                        }
                    })
                    .catch(err => {
                        console.error('Error loading scripts:', err);
                        container.innerHTML = `
                            <div class="alert bg-light-danger border border-danger d-flex align-items-center p-5 rounded-[16px]">
                                <i class="fas fa-exclamation-triangle text-danger fs-1 me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-dark">Gagal Memuat Hasil Script</h4>
                                    <span class="text-slate-600 fs-7">Naskah diagnostik gagal dijalankan atau timeout.</span>
                                    <button type="button" class="btn btn-sm btn-danger mt-3 w-fit" onclick="loadScripts(true)">
                                        <i class="fas fa-redo me-1"></i> Coba Lagi
                                    </button>
                                </div>
                            </div>
                        `;
                    });
            }

            // Manual On-Demand Scripts Scan Button
            const btnRunScriptsNow = document.getElementById('btn-run-scripts-now');
            if (btnRunScriptsNow) {
                btnRunScriptsNow.addEventListener('click', function () {
                    const text = document.getElementById('text-btn-scripts');
                    if (text) text.textContent = 'Memindai...';
                    btnRunScriptsNow.disabled = true;
                    loadScripts(true);
                    setTimeout(() => {
                        if (text) text.textContent = 'Pindai Ulang';
                        btnRunScriptsNow.disabled = false;
                    }, 3000);
                });
            }

            // Toolbar Refresh Button
            const btnToolbarAudit = document.getElementById('btn-toolbar-audit');
            if (btnToolbarAudit) {
                btnToolbarAudit.addEventListener('click', function () {
                    const icon = document.getElementById('icon-btn-audit');
                    const text = document.getElementById('text-btn-audit');

                    if (icon) icon.classList.add('fa-spin');
                    if (text) text.textContent = 'Memindai Integritas...';
                    btnToolbarAudit.disabled = true;

                    loadScripts(true);

                    setTimeout(() => {
                        if (icon) icon.classList.remove('fa-spin');
                        if (text) text.textContent = 'Jalankan Pindai Integritas';
                        btnToolbarAudit.disabled = false;
                    }, 2500);
                });
            }

            // Initial load
            loadScripts(false);
        });
    </script>
@endsection
