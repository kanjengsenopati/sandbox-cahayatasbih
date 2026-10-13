@push('css')
<style>
    /* ==========================================================================
       PAKRT NATIVE VITE BI DASHBOARD ARCHITECTURE
       ========================================================================== */
    .bi-card {
        border-radius: 24px !important;
        box-shadow: 0 8px 30px rgb(0, 0, 0, 0.04) !important;
        border: 0 !important;
        background: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .bi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 36px rgb(0, 0, 0, 0.06) !important;
    }
    .bi-glass-bar {
        border-radius: 24px !important;
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 8px 30px rgb(0, 0, 0, 0.04) !important;
        border: 0 !important;
    }

    /* Typography System Rules */
    .typography-h1 {
        font-size: 22px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.3;
    }
    .typography-h2 {
        font-size: 16px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        line-height: 1.35;
        margin-bottom: 0;
    }
    .typography-amount {
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #10B981 !important; /* Emerald-600 */
        line-height: 1.25;
        white-space: nowrap !important;
    }
    .typography-amount.amount-lg {
        font-size: clamp(17px, 1.35vw, 21px) !important;
        white-space: nowrap !important;
    }
    .typography-amount.amount-primary {
        color: #2563EB !important; /* Blue-600 */
    }
    .typography-amount.amount-error {
        color: #DC2626 !important; /* Red-600 */
    }
    .typography-label {
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.02em !important;
        color: #334155 !important; /* Slate-700: Darker gray & prominent */
        line-height: 1.3;
    }
    .typography-body {
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #475569 !important; /* Slate-600 */
        margin-bottom: 0;
    }
    .typography-caption {
        font-size: 12px !important;
        font-weight: 400 !important;
        font-style: italic !important;
        color: #94a3b8 !important; /* Slate-400 */
    }

    /* Semantic Tints (bg-opacity-10) */
    .tint-primary {
        background-color: rgba(37, 99, 235, 0.1) !important;
        color: #2563EB !important;
    }
    .tint-emerald {
        background-color: rgba(16, 185, 129, 0.1) !important;
        color: #10B981 !important;
    }
    .tint-red {
        background-color: rgba(220, 38, 38, 0.1) !important;
        color: #DC2626 !important;
    }
    .tint-slate {
        background-color: rgba(148, 163, 184, 0.12) !important;
        color: #475569 !important;
    }
    .tint-warning {
        background-color: rgba(245, 158, 11, 0.1) !important;
        color: #d97706 !important;
    }

    /* Top-Right Action Cluster */
    .action-cluster {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .action-icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(148, 163, 184, 0.1);
        color: #94a3b8;
        transition: all 0.2s ease;
        text-decoration: none;
        flex-shrink: 0;
    }
    .action-icon-btn:hover {
        background: rgba(37, 99, 235, 0.1);
        color: #2563EB;
    }

    /* Switcher Pills */
    .bi-period-btn {
        border: 0;
        background: transparent;
        padding: 5px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        transition: all 0.2s ease;
    }
    .bi-period-btn.active {
        background: #2563EB;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    /* Compact Table & Scrollable Feed */
    .bi-table th {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: #64748b !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .bi-table td {
        padding-top: 11px !important;
        padding-bottom: 11px !important;
        border-bottom: 1px dashed #f1f5f9 !important;
        vertical-align: middle;
    }
    .bi-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }
    .bi-feed-scroll {
        max-height: 410px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .bi-feed-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .bi-feed-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .bi-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
    }

    /* Expandable Financial & Guardian Intelligence Panel */
    .bi-expand-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        padding: 2.5px 8px;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .bi-expand-btn:hover, .bi-expand-btn[aria-expanded="true"] {
        background: rgba(37, 99, 235, 0.08);
        border-color: rgba(37, 99, 235, 0.2);
        color: #2563EB;
    }
    .bi-expand-btn .bi-chevron-icon {
        transition: transform 0.2s ease;
    }
    .bi-expand-btn[aria-expanded="true"] .bi-chevron-icon {
        transform: rotate(180deg);
    }
    .bi-expand-row > td {
        padding: 0 !important;
        border-bottom: 1px dashed #f1f5f9 !important;
    }
    .bi-nested-panel {
        background: #f8fafc;
        border-radius: 20px;
        border: 1px dashed #cbd5e1;
        padding: 16px 18px;
        margin: 6px 4px 14px 4px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }
    .bi-nested-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 12px 14px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }
    .bi-nested-table th {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #64748b !important;
        padding: 6px 10px !important;
        border-bottom: 1px solid #e2e8f0 !important;
        background: #f8fafc;
    }
    .bi-nested-table td {
        font-size: 12px !important;
        padding: 7px 10px !important;
        border-bottom: 1px dashed #f1f5f9 !important;
        background: #ffffff;
    }
    .bi-nested-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }
</style>
@endpush

@php
    $summary = $biSummary ?? [
        'target_pembayaran' => 0,
        'realisasi_pembayaran' => 0,
        'sisa_tunggakan' => 0,
        'realisasi_percentage' => 0,
        'total_bill_items' => 0,
        'paid_bill_items' => 0,
        'partial_bill_items' => 0,
        'unpaid_bill_items' => 0,
        'pembayaran_hari_ini' => 0,
        'pembayaran_bulan_ini' => 0,
        'saldo_masuk_hari_ini' => 0,
        'saldo_masuk_bulan_ini' => 0,
        'saldo_masuk_12_bulan' => 0,
        'saldo_mengendap' => 0,
        'tabungan_masuk_bulan_ini' => 0,
        'tabungan_mengendap' => 0,
    ];
@endphp

<!-- =====================================================================
     MOBILE FILTER BAR (Visible on Mobile/Tablet where Toolbar is hidden)
     ===================================================================== -->
<div class="d-lg-none bi-glass-bar p-4 mb-5">
    <form action="{{ route('dashboard') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 mb-1 d-flex justify-content-between align-items-center">
            <x-text.h2>Executive BI Dashboard</x-text.h2>
            <span class="badge tint-primary rounded-pill px-3 py-1">TA {{ $activeAcademicYearName ?? '-' }}</span>
        </div>
        @if(isset($schools) && $schools->count() > 1)
            <div class="col-6">
                <select name="school_id" onchange="this.form.submit()" class="form-select form-select-sm border-0 bg-light fw-bold" style="border-radius: 12px;">
                    <option value="all">Semua Unit</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" {{ ($selectedSchoolId ?? null) === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(isset($academicYears) && $academicYears->count() > 0)
            <div class="{{ (isset($schools) && $schools->count() > 1) ? 'col-6' : 'col-12' }}">
                <select name="academic_year_id" onchange="this.form.submit()" class="form-select form-select-sm border-0 bg-light fw-bold" style="border-radius: 12px;">
                    <option value="all" {{ ($selectedAcademicYearId ?? '') === 'all' ? 'selected' : '' }}>Semua TA</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ ($selectedAcademicYearId ?? '') === $ay->id ? 'selected' : '' }}>TA {{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </form>
</div>

<!-- =====================================================================
     SECTION 1A: EXECUTIVE FINANCIAL BI SUMMARY CARDS (4 PRIMARY CARDS)
     ===================================================================== -->
<div class="row g-4 mb-4">
    <!-- Card 1: Target Pembayaran Tagihan -->
    <div class="col-xl-3 col-md-6">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <x-text.label class="text-nowrap">TARGET PEMBAYARAN TAGIHAN</x-text.label>
                        <div class="action-cluster">
                            <a href="{{ route('report-bill.index') }}" class="action-icon-btn" title="Laporan Tagihan">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-1 mb-3">
                        <x-text.caption class="mb-0 text-nowrap">Periode TA {{ $activeAcademicYearName ?? '-' }}</x-text.caption>
                        <span class="badge tint-primary rounded-pill px-2.5 py-1 fw-bold text-nowrap" style="font-size: 11px;">
                            {{ number_format($summary['total_bill_items']) }} Tagihan
                        </span>
                    </div>
                </div>
                <div>
                    <div class="mb-2">
                        <x-text.amount class="amount-lg amount-primary text-nowrap d-block">Rp {{ number_format($summary['target_pembayaran'], 0, ',', '.') }}</x-text.amount>
                    </div>
                    <div class="w-100 rounded-pill mb-2" style="height: 6px; background-color: #f1f5f9;">
                        <div class="rounded-pill" style="height: 6px; width: {{ min(100, $summary['realisasi_percentage']) }}%; background-color: #2563EB;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <x-text.caption class="text-nowrap">Lunas: <strong class="text-dark">{{ number_format($summary['paid_bill_items']) }} item</strong></x-text.caption>
                        <x-text.caption class="text-nowrap">Cicilan: <strong class="text-dark">{{ number_format($summary['partial_bill_items']) }} item</strong></x-text.caption>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Realisasi Pembayaran Masuk -->
    <div class="col-xl-3 col-md-6">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <x-text.label class="text-nowrap">REALISASI PEMBAYARAN</x-text.label>
                        <div class="action-cluster">
                            <a href="{{ route('report-transaction.index') }}" class="action-icon-btn" title="Laporan Transaksi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-1 mb-3">
                        <x-text.caption class="mb-0 text-nowrap">Akumulasi Tagihan Terbayar</x-text.caption>
                        <span class="badge tint-emerald rounded-pill px-2.5 py-1 fw-bold text-nowrap" style="font-size: 11px;">
                            {{ $summary['realisasi_percentage'] }}% Tercapai
                        </span>
                    </div>
                </div>
                <div>
                    <div class="mb-2">
                        <x-text.amount class="amount-lg text-nowrap d-block">Rp {{ number_format($summary['realisasi_pembayaran'], 0, ',', '.') }}</x-text.amount>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 pt-2" style="border-top: 1px dashed #f1f5f9;">
                        <x-text.caption class="text-nowrap">Hari Ini: <strong class="text-dark">Rp {{ number_format($summary['pembayaran_hari_ini'], 0, ',', '.') }}</strong></x-text.caption>
                        <x-text.caption class="text-nowrap">Bulan Ini: <strong class="text-dark">Rp {{ number_format($summary['pembayaran_bulan_ini'], 0, ',', '.') }}</strong></x-text.caption>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Sisa Piutang / Tunggakan -->
    <div class="col-xl-3 col-md-6">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <x-text.label class="text-nowrap">SISA TUNGGAKAN TAGIHAN</x-text.label>
                        <div class="action-cluster">
                            <a href="{{ route('report-bill-student.index') }}" class="action-icon-btn" title="Rincian Tunggakan Santri">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-1 mb-3">
                        <x-text.caption class="mb-0 text-nowrap">Piutang Santri Belum Lunas</x-text.caption>
                        <span class="badge tint-red rounded-pill px-2.5 py-1 fw-bold text-nowrap" style="font-size: 11px;">
                            {{ max(0, round(100 - $summary['realisasi_percentage'], 1)) }}% Outstanding
                        </span>
                    </div>
                </div>
                <div>
                    <div class="mb-2">
                        <x-text.amount class="amount-lg amount-error text-nowrap d-block">Rp {{ number_format($summary['sisa_tunggakan'], 0, ',', '.') }}</x-text.amount>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 pt-2" style="border-top: 1px dashed #f1f5f9;">
                        <x-text.caption class="text-nowrap">Belum Bayar: <strong style="color: #DC2626;">{{ number_format($summary['unpaid_bill_items']) }} item</strong></x-text.caption>
                        <x-text.caption class="text-nowrap">Angsuran: <strong class="text-dark">{{ number_format($summary['partial_bill_items']) }} item</strong></x-text.caption>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Saldo Masuk (Top-Up) & Dompet Santri -->
    <div class="col-xl-3 col-md-6">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <x-text.label class="text-nowrap">SALDO MASUK & DOMPET</x-text.label>
                        <div class="action-cluster">
                            <a href="{{ route('report-saldo.index') }}" class="action-icon-btn" title="Laporan Saldo Santri">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-1 mb-3">
                        <x-text.caption class="mb-0 text-nowrap">Top-Up & Saldo Aktif</x-text.caption>
                        <span class="badge tint-emerald rounded-pill px-2.5 py-1 fw-bold text-nowrap" style="font-size: 11px;">Top-Up Bulan Ini</span>
                    </div>
                </div>
                <div>
                    <div class="mb-2">
                        <x-text.amount class="amount-lg text-nowrap d-block">Rp {{ number_format($summary['saldo_masuk_bulan_ini'], 0, ',', '.') }}</x-text.amount>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 pt-2" style="border-top: 1px dashed #f1f5f9;">
                        <x-text.caption class="text-nowrap">Hari Ini: <strong style="color: #10B981;">Rp {{ number_format($summary['saldo_masuk_hari_ini'], 0, ',', '.') }}</strong></x-text.caption>
                        <x-text.caption class="text-nowrap">Saldo Aktif: <strong class="text-dark">Rp {{ number_format($summary['saldo_mengendap'], 0, ',', '.') }}</strong></x-text.caption>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     SECTION 1B: ECOSYSTEM & DEMOGRAPHIC COMPACT STRIP (4 CARDS)
     ===================================================================== -->
<div class="row g-4 mb-5">
    <!-- Santri Aktif -->
    <div class="col-xl-3 col-6">
        <div class="card bi-card">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <x-text.label>SANTRI AKTIF</x-text.label>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <x-text.h1 class="mb-0">{{ number_format($totalSantri) }}</x-text.h1>
                        <x-text.caption>L: {{ number_format($genderRatio['l'] ?? 0) }} • P: {{ number_format($genderRatio['p'] ?? 0) }}</x-text.caption>
                    </div>
                </div>
                <a href="{{ route('student.index') }}" class="action-icon-btn tint-primary" title="Data Santri">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Wali Santri -->
    <div class="col-xl-3 col-6">
        <div class="card bi-card h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <x-text.label class="text-nowrap">WALI SANTRI AKTIF</x-text.label>
                        <div class="action-cluster">
                            <button type="button" class="action-icon-btn border-0" data-bs-toggle="modal" data-bs-target="#modalWaliSantriAudit" title="Audit & Rincian Status Wali Santri">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            </button>
                            <a href="{{ route('user.index') }}" class="action-icon-btn tint-emerald" title="Data Wali Santri">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <x-text.h1 class="mb-0">{{ number_format($totalWali) }}</x-text.h1>
                        <x-text.caption class="text-nowrap">{{ $loginActivity['wali_count'] ?? 0 }} login hari ini</x-text.caption>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-1 mt-2 pt-2" style="border-top: 1px dashed #f1f5f9;">
                    <a href="{{ route('user.index', ['status' => 'ACTIVE']) }}" class="badge rounded-pill px-2 py-0.5 fw-bold text-decoration-none" style="background-color: rgba(16, 185, 129, 0.1); color: #10B981; font-size: 10px;" title="Wali Aktif yang pernah login ke PWA">
                        {{ number_format($loginActivity['wali_ever'] ?? 0) }} Login
                    </a>
                    <a href="{{ route('user.index', ['status' => 'INACTIVE']) }}" class="badge rounded-pill px-2 py-0.5 fw-bold text-decoration-none" style="background-color: rgba(245, 158, 11, 0.1); color: #D97706; font-size: 10px;" title="Wali Aktif yang belum pernah login (Hanya Terdaftar)">
                        {{ number_format($loginActivity['wali_never'] ?? 0) }} Terdaftar
                    </a>
                    @if(($loginActivity['wali_inactive'] ?? 0) > 0)
                        <button type="button" class="badge rounded-pill px-2 py-0.5 fw-bold border-0 text-decoration-none" style="background-color: rgba(220, 38, 38, 0.08); color: #DC2626; font-size: 10px;" data-bs-toggle="modal" data-bs-target="#modalWaliSantriAudit" title="{{ $loginActivity['wali_inactive'] }} Wali yang seluruh anaknya sudah keluar/lulus/pindah">
                            +{{ number_format($loginActivity['wali_inactive']) }} Out
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- User Backoffice & Guru -->
    <div class="col-xl-3 col-6">
        <div class="card bi-card">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <x-text.label>USER BACKOFFICE & STAFF</x-text.label>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <x-text.h1 class="mb-0">{{ number_format($totalStaff) }}</x-text.h1>
                        <x-text.caption>{{ $loginActivity['staff_count'] ?? 0 }} login hari ini</x-text.caption>
                    </div>
                </div>
                <a href="{{ route('admin.index') }}" class="action-icon-btn tint-primary" title="Data Admin & Staff">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Total Kelas / Rombel -->
    <div class="col-xl-3 col-6">
        <div class="card bi-card">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <x-text.label>TOTAL ROMBEL & TABUNGAN</x-text.label>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <x-text.h1 class="mb-0">{{ number_format($totalKelas) }} <span style="font-size: 13px; font-weight: 600; color: #64748b;">Kelas</span></x-text.h1>
                        <x-text.caption>Tabungan: Rp {{ number_format($summary['tabungan_mengendap'], 0, ',', '.') }}</x-text.caption>
                    </div>
                </div>
                <a href="{{ route('academic.index') }}" class="action-icon-btn tint-slate" title="Manajemen Akademik">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     SECTION 2: MODERN BI CHARTS (TARGET PEMBAYARAN & TREND SALDO MASUK)
     ===================================================================== -->
<div class="row g-4 mb-5">
    <!-- Chart 1: Trend Target Pembayaran vs Realisasi (Bulanan Jul - Jun) -->
    <div class="col-xl-7">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
                    <div>
                        <x-text.label>ANALISIS CAPAIAN TAGIHAN BULANAN</x-text.label>
                        <x-text.h2 class="mt-1">Trend Target Pembayaran vs Realisasi (Jul – Jun)</x-text.h2>
                        <x-text.caption>Perbandingan target tagihan, nominal lunas, dan kurva persentase capaian</x-text.caption>
                    </div>
                    <div class="action-cluster">
                        <span class="badge tint-primary rounded-pill px-3 py-1.5 fw-bold" style="font-size: 11px;">
                            Target: Rp {{ number_format($summary['target_pembayaran'], 0, ',', '.') }}
                        </span>
                        <span class="badge tint-emerald rounded-pill px-3 py-1.5 fw-bold" style="font-size: 11px;">
                            Realisasi: Rp {{ number_format($summary['realisasi_pembayaran'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="position-relative" style="height: 310px;">
                    <canvas id="biBillTrendChart"></canvas>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2" style="border-top: 1px dashed #f1f5f9;">
                    <div class="d-flex align-items-center gap-4">
                        <span class="d-inline-flex align-items-center gap-1.5">
                            <span style="width: 10px; height: 10px; border-radius: 3px; background: rgba(37, 99, 235, 0.25); border: 1.5px solid #2563EB; display: inline-block;"></span>
                            <x-text.caption class="fst-normal fw-bold text-dark ms-1">Target Tagihan</x-text.caption>
                        </span>
                        <span class="d-inline-flex align-items-center gap-1.5">
                            <span style="width: 10px; height: 10px; border-radius: 3px; background: #10B981; display: inline-block;"></span>
                            <x-text.caption class="fst-normal fw-bold text-dark ms-1">Realisasi Masuk</x-text.caption>
                        </span>
                        <span class="d-inline-flex align-items-center gap-1.5">
                            <span style="width: 14px; height: 3px; border-radius: 2px; background: #2563EB; display: inline-block;"></span>
                            <x-text.caption class="fst-normal fw-bold text-dark ms-1">% Capaian</x-text.caption>
                        </span>
                    </div>
                    <x-text.caption>Rasio Global: <strong style="color: #10B981;">{{ $summary['realisasi_percentage'] }}%</strong></x-text.caption>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Trend Saldo Masuk & Arus Pembayaran (30 Hari / 12 Bulan) -->
    <div class="col-xl-5">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
                    <div>
                        <x-text.label>CASH INFLOW VELOCITY</x-text.label>
                        <x-text.h2 class="mt-1">Trend Saldo Masuk & Pembayaran</x-text.h2>
                        <x-text.caption id="inflowChartSubtitle">Pergerakan kas masuk harian (30 hari terakhir)</x-text.caption>
                    </div>
                    <div class="action-cluster p-1 rounded-3" style="background: #f1f5f9;">
                        <button type="button" class="bi-period-btn active" id="btnInflow30d" onclick="switchInflowChart('daily_30')">30 Hari</button>
                        <button type="button" class="bi-period-btn" id="btnInflow12m" onclick="switchInflowChart('monthly_12')">12 Bulan</button>
                    </div>
                </div>

                <div class="position-relative" style="height: 310px;">
                    <canvas id="biInflowTrendChart"></canvas>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2" style="border-top: 1px dashed #f1f5f9;">
                    <div>
                        <x-text.label class="d-block">TOTAL SALDO MASUK (12 BLN)</x-text.label>
                        <x-text.amount>Rp {{ number_format($summary['saldo_masuk_12_bulan'], 0, ',', '.') }}</x-text.amount>
                    </div>
                    <div class="text-end">
                        <x-text.label class="d-block">TABUNGAN MASUK (BLN INI)</x-text.label>
                        <x-text.amount class="amount-primary">Rp {{ number_format($summary['tabungan_masuk_bulan_ini'], 0, ',', '.') }}</x-text.amount>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     SECTION 3: 10 AKTIVITAS PEMBAYARAN TERKINI & UNIT REALIZATION
     ===================================================================== -->
<div class="row g-4 mb-5">
    <!-- Left Column: 10 Aktivitas Pembayaran Terkini -->
    <div class="col-xl-8">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
                    <div>
                        <x-text.label>REAL-TIME TRANSACTION FEED</x-text.label>
                        <x-text.h2 class="mt-1">10 Aktivitas Pembayaran Terkini</x-text.h2>
                        <x-text.caption>Transaksi pembayaran tagihan, top-up saldo, dan tabungan yang telah terverifikasi lunas</x-text.caption>
                    </div>
                    <div class="action-cluster">
                        <a href="{{ route('report-transaction.index') }}" class="btn btn-sm fw-bold px-3 py-1.5" style="border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: #2563EB; font-size: 12px;">
                            Lihat Semua Transaksi
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table bi-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-2">Santri & Unit</th>
                                <th>Kategori & Item</th>
                                <th>Metode & Petugas</th>
                                <th class="text-end pe-2 text-nowrap" style="min-width: 170px;">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments ?? [] as $tx)
                                @php
                                    $studentName = $tx->student->name ?? 'Santri';
                                    $studentNis = $tx->student->nis ?? '-';
                                    $className = $tx->student?->classroom?->name ?? '-';
                                    $schoolName = $tx->student?->classroom?->school?->name ?? '-';
                                    $txTime = $tx->paid_at ? \Carbon\Carbon::parse($tx->paid_at) : $tx->created_at;

                                    $typeBadgeClass = 'tint-primary';
                                    $typeLabel = 'Tagihan';
                                    $itemSummary = $tx->payment_code;

                                    if ($tx->type === \App\Models\Transaction::TYPE_BILL) {
                                        $typeBadgeClass = 'tint-primary';
                                        $typeLabel = 'Tagihan';
                                        $billNames = $tx->transactionDetails
                                            ->map(fn($d) => $d->bill?->billType?->name)
                                            ->filter()
                                            ->unique()
                                            ->values();
                                        if ($billNames->isNotEmpty()) {
                                            $itemSummary = $billNames->take(2)->implode(', ') . ($billNames->count() > 2 ? ' +' . ($billNames->count() - 2) : '');
                                        }
                                    } elseif ($tx->type === \App\Models\Transaction::TYPE_SALDO) {
                                        $typeBadgeClass = 'tint-emerald';
                                        $typeLabel = 'Top-Up Saldo';
                                        $itemSummary = 'Deposit Dompet Santri';
                                    } elseif ($tx->type === \App\Models\Transaction::TYPE_SAVING) {
                                        $typeBadgeClass = 'tint-slate';
                                        $typeLabel = 'Tabungan';
                                        $itemSummary = 'Setoran Tabungan';
                                    }

                                    $fin = $tx->financial_profile ?? [
                                        'unpaid_amount' => 0,
                                        'unpaid_count' => 0,
                                        'topup_amount' => 0,
                                        'topup_count' => 0,
                                        'capability_status' => 'BEBAS_TUNGGAKAN',
                                        'capability_label' => 'Bebas Tunggakan',
                                        'capability_badge_class' => 'tint-emerald',
                                        'capability_ratio' => null,
                                        'unpaid_items' => collect([]),
                                    ];

                                    $waliUser = $tx->student?->user;
                                    $waliName = $waliUser?->name;
                                    $waliPhone = $waliUser?->phone;
                                    $waliAvatar = $waliUser?->avatar_url;
                                    $waliJamaah = $waliUser?->jamaah_status;
                                    $studentSaldo = (int) ($tx->student?->saldo ?? 0);

                                    $waLink = null;
                                    if ($waliPhone) {
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $waliPhone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                        if (strlen($cleanPhone) >= 9) {
                                            $waLink = 'https://wa.me/' . $cleanPhone;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-2">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="d-flex align-items-center justify-content-center fw-bold tint-primary mt-1" style="width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0; font-size: 14px;">
                                                {{ strtoupper(substr($studentName, 0, 1)) }}
                                            </div>
                                            <div>
                                                <x-text.body class="fw-bold text-dark">{{ \Illuminate\Support\Str::limit($studentName, 26) }}</x-text.body>
                                                <x-text.caption class="d-block fst-normal">{{ $studentNis }} • {{ $className }} ({{ $schoolName }})</x-text.caption>
                                                <button type="button" 
                                                        class="bi-expand-btn mt-1.5" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#expand-fin-{{ $tx->id }}" 
                                                        aria-expanded="false" 
                                                        aria-controls="expand-fin-{{ $tx->id }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                    <span>Finansial & Wali</span>
                                                    @if($fin['unpaid_amount'] > 0)
                                                        <span class="badge rounded-pill bg-danger text-white px-1.5 py-0.5 ms-1" style="font-size: 9px; font-weight: 700;">
                                                            Tunggakan: Rp {{ number_format($fin['unpaid_amount'], 0, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill bg-success text-white px-1.5 py-0.5 ms-1" style="font-size: 9px; font-weight: 700;">
                                                            Lunas
                                                        </span>
                                                    @endif
                                                    <svg class="bi-chevron-icon ms-0.5" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $typeBadgeClass }} rounded-pill px-2.5 py-1 fw-bold mb-1" style="font-size: 10px;">
                                            {{ $typeLabel }}
                                        </span>
                                        <x-text.caption class="d-block fst-normal text-dark fw-semibold">
                                            {{ \Illuminate\Support\Str::limit($itemSummary, 32) }}
                                        </x-text.caption>
                                    </td>
                                    <td>
                                        <x-text.body class="fw-semibold text-dark" style="font-size: 13px !important;">
                                            {{ $tx->paymentMethod->name ?? 'Manual / Tunai' }}
                                        </x-text.body>
                                        <x-text.caption class="d-block">
                                            Oleh: {{ $tx->admin->name ?? 'Sistem / Wali' }}
                                        </x-text.caption>
                                        <x-text.caption class="d-block fst-normal mt-0.5" style="font-size: 11px !important; color: #64748b !important;">
                                            {{ $txTime ? $txTime->translatedFormat('d M Y, H:i') : '-' }}{{ $txTime ? ' • ' . $txTime->diffForHumans() : '' }}
                                        </x-text.caption>
                                    </td>
                                    <td class="text-end pe-2 text-nowrap" style="min-width: 170px;">
                                        <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                            <x-text.amount class="text-nowrap">Rp {{ number_format($tx->pay_amount, 0, ',', '.') }}</x-text.amount>
                                            <a href="{{ route('transaction.invoice', $tx->id) }}" target="_blank" class="action-icon-btn" title="Cetak Kwitansi / Invoice">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="bi-expand-row">
                                    <td colspan="4">
                                        <div class="collapse" id="expand-fin-{{ $tx->id }}">
                                            <div class="bi-nested-panel">
                                                <!-- Row 1: Profile Wali & Quick WhatsApp Link -->
                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-3" style="border-bottom: 1px dashed #e2e8f0;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="d-flex align-items-center justify-content-center fw-bold tint-slate" style="width: 38px; height: 38px; border-radius: 12px; font-size: 13px; flex-shrink: 0; overflow: hidden;">
                                                            @if($waliAvatar)
                                                                <img src="{{ $waliAvatar }}" alt="Wali" style="width: 100%; height: 100%; object-fit: cover;">
                                                            @else
                                                                {{ strtoupper(substr($waliName ?? 'W', 0, 1)) }}
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <x-text.label class="mb-0">Wali / Orang Tua Santri:</x-text.label>
                                                                @if($waliJamaah === 'JAMAAH')
                                                                    <span class="badge tint-emerald rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">Jamaah</span>
                                                                @elseif($waliJamaah === 'NON_JAMAAH')
                                                                    <span class="badge tint-red rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">Non Jamaah</span>
                                                                @elseif($waliJamaah === 'MUKIMIN')
                                                                    <span class="badge tint-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">Mukimin</span>
                                                                @endif
                                                            </div>
                                                            <x-text.h2 class="mb-0 mt-0.5 {{ $waliName ? 'text-dark' : 'text-muted fst-italic' }}" style="font-size: 14.5px !important;">
                                                                {{ $waliName ?? 'Belum Ditautkan ke Akun Wali' }}
                                                            </x-text.h2>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($waliPhone)
                                                            <span class="badge bg-light text-secondary rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 11px;">
                                                                <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                                                {{ $waliPhone }}
                                                            </span>
                                                            @if($waLink)
                                                                <a href="{{ $waLink }}" target="_blank" class="btn btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-bold rounded-pill" style="font-size: 11px; background-color: rgba(16, 185, 129, 0.1); color: #10B981;">
                                                                    <span>Chat WA</span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                                                </a>
                                                            @endif
                                                        @else
                                                            <x-text.caption class="mb-0 text-muted">Nomor kontak belum terdaftar</x-text.caption>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Row 2: 3 Cards Komparasi Kemampuan Bayar -->
                                                <div class="row g-3 mb-3">
                                                    <!-- Card A: Total Unpaid Bills -->
                                                    <div class="col-md-4">
                                                        <div class="bi-nested-card h-100 d-flex flex-column justify-content-between">
                                                            <div>
                                                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                                    <x-text.label>TAGIHAN JATUH TEMPO (S.D. BULAN INI)</x-text.label>
                                                                    <span class="badge {{ $fin['unpaid_amount'] > 0 ? 'tint-red' : 'tint-emerald' }} rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">
                                                                        {{ $fin['unpaid_count'] }} Item
                                                                    </span>
                                                                </div>
                                                                <x-text.amount class="{{ $fin['unpaid_amount'] > 0 ? 'amount-error' : 'text-emerald' }} d-block" style="font-size: 16px !important;">
                                                                    Rp {{ number_format($fin['unpaid_amount'], 0, ',', '.') }}
                                                                </x-text.amount>
                                                            </div>
                                                            <x-text.caption class="mt-2 text-truncate">
                                                                Kewajiban jatuh tempo s.d. {{ Carbon\Carbon::now()->translatedFormat('F Y') }}
                                                            </x-text.caption>
                                                        </div>
                                                    </div>

                                                    <!-- Card B: Akumulasi Topup 30 Hari -->
                                                    <div class="col-md-4">
                                                        <div class="bi-nested-card h-100 d-flex flex-column justify-content-between">
                                                            <div>
                                                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                                    <x-text.label>TOP-UP SALDO (30 HARI)</x-text.label>
                                                                    <span class="badge tint-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">
                                                                        {{ $fin['topup_count'] }}x Top-Up
                                                                    </span>
                                                                </div>
                                                                <x-text.amount class="amount-primary d-block" style="font-size: 16px !important;">
                                                                    Rp {{ number_format($fin['topup_amount'], 0, ',', '.') }}
                                                                </x-text.amount>
                                                            </div>
                                                            <x-text.caption class="mt-2 text-truncate">
                                                                Arus mutasi deposit dompet santri
                                                            </x-text.caption>
                                                        </div>
                                                    </div>

                                                    <!-- Card C: Analisis Kemampuan Bayar & Saldo Aktif -->
                                                    <div class="col-md-4">
                                                        <div class="bi-nested-card h-100 d-flex flex-column justify-content-between">
                                                            <div>
                                                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                                    <x-text.label>KOMPARASI KEMAMPUAN</x-text.label>
                                                                    <span class="badge {{ $fin['capability_badge_class'] }} rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">
                                                                        {{ $fin['capability_label'] }}
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex align-items-baseline gap-2">
                                                                    <x-text.amount class="text-dark d-block" style="font-size: 15px !important;">
                                                                        Saldo: Rp {{ number_format($studentSaldo, 0, ',', '.') }}
                                                                    </x-text.amount>
                                                                </div>
                                                            </div>
                                                            <x-text.caption class="mt-2">
                                                                @if($fin['unpaid_amount'] === 0)
                                                                    Likuiditas aman, tidak ada tunggakan jatuh tempo
                                                                @elseif($fin['capability_ratio'] !== null)
                                                                    Volume top-up = <strong>{{ $fin['capability_ratio'] }}%</strong> dari tagihan jatuh tempo
                                                                @else
                                                                    Perlu atensi, tanpa mutasi top-up 30 hari
                                                                @endif
                                                            </x-text.caption>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Row 3: Mini-Tabel Rincian Tagihan Belum Lunas -->
                                                <div class="bg-white rounded-3 p-3" style="border: 1px solid #f1f5f9;">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <x-text.label style="font-size: 11px !important;">RINCIAN POS TAGIHAN JATUH TEMPO (S.D. BULAN INI)</x-text.label>
                                                        <a href="{{ route('report-bill.index', ['student_id' => $tx->student_id]) }}" class="text-primary fw-bold" style="font-size: 11px; text-decoration: none;">
                                                            Lihat Rekap Lengkap &rarr;
                                                        </a>
                                                    </div>
                                                    @if(!empty($fin['unpaid_items']) && count($fin['unpaid_items']) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table bi-nested-table mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="ps-2">Item Tagihan</th>
                                                                        <th>Periode</th>
                                                                        <th>Status</th>
                                                                        <th class="text-end pe-2">Sisa Tagihan</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($fin['unpaid_items'] as $item)
                                                                        <tr>
                                                                            <td class="ps-2 fw-semibold text-dark">{{ $item['name'] }}</td>
                                                                            <td class="text-muted">{{ $item['period'] ?: '-' }}</td>
                                                                            <td>
                                                                                <span class="badge {{ $item['status'] === 'PARTIAL' ? 'tint-warning' : 'tint-red' }} rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10px;">
                                                                                    {{ $item['status'] === 'PARTIAL' ? 'Cicilan' : 'Belum Lunas' }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-end pe-2 fw-bold text-danger">
                                                                                Rp {{ number_format($item['remaining'], 0, ',', '.') }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-3">
                                                            <svg class="text-success mb-1" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                            <x-text.caption class="d-block fst-normal text-success fw-bold">
                                                                Seluruh tagihan santri yang jatuh tempo s.d. bulan ini telah terbayar lunas.
                                                            </x-text.caption>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8">
                                        <x-text.body>Belum ada aktivitas pembayaran terverifikasi.</x-text.body>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Performa Unit Pendidikan (UPT) & Komposisi Gender -->
    <div class="col-xl-4">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div>
                            <x-text.label>UNIT PERFORMANCE & DEMOGRAPHY</x-text.label>
                            <x-text.h2 class="mt-1">Realisasi & Populasi per Unit</x-text.h2>
                            <x-text.caption>Capaian pembayaran & sebaran santri per lembaga</x-text.caption>
                        </div>
                    </div>

                    <!-- Per-School BI List -->
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($schoolData as $school)
                            <div class="p-3.5 rounded-4" style="background: #f8fafc; border-radius: 16px; padding: 14px;">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge tint-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                            {{ $school->name }}
                                        </span>
                                        <x-text.caption class="fst-normal fw-semibold">
                                            {{ $school->total_students }} Santri ({{ $school->total_classes }} Kls)
                                        </x-text.caption>
                                    </div>
                                    <span class="badge {{ ($school->realisasi_pct ?? 0) >= 70 ? 'tint-emerald' : 'tint-primary' }} rounded-pill px-2 py-1 fw-bold" style="font-size: 11px;">
                                        {{ $school->realisasi_pct ?? 0 }}%
                                    </span>
                                </div>

                                <div class="w-100 rounded-pill my-2" style="height: 6px; background: #e2e8f0;">
                                    <div class="rounded-pill" style="height: 6px; width: {{ min(100, $school->realisasi_pct ?? 0) }}%; background: #10B981;"></div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <x-text.caption class="fst-normal">
                                        Lunas: <strong style="color: #10B981;">Rp {{ number_format($school->paid_bill ?? 0, 0, ',', '.') }}</strong>
                                    </x-text.caption>
                                    <x-text.caption class="fst-normal">
                                        Target: <strong class="text-dark">Rp {{ number_format($school->target_bill ?? 0, 0, ',', '.') }}</strong>
                                    </x-text.caption>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <!-- Compact Payment Channel Ratio Doughnut (Aplikasi Approval vs Entry Langsung) -->
                    <div class="pt-3 mb-3" style="border-top: 1px dashed #f1f5f9;">
                        <div class="row align-items-center g-2">
                            <div class="col-5">
                                <div class="position-relative" style="height: 115px;">
                                    <canvas id="paymentChannelChart"></canvas>
                                </div>
                            </div>
                            <div class="col-7">
                                <x-text.label class="d-block mb-2">KANAL PEMBAYARAN TAGIHAN</x-text.label>
                                <div class="d-flex justify-content-between align-items-center mb-0.5">
                                    <span class="d-flex align-items-center gap-2">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #2563EB; display: inline-block; flex-shrink: 0;"></span>
                                        <x-text.body style="font-size: 13px !important;">Aplikasi (Approval)</x-text.body>
                                    </span>
                                    <x-text.body class="fw-bold text-dark" style="font-size: 12.5px !important;">{{ number_format($paymentChannelRatio['app_count'] ?? 0) }} Tx ({{ $paymentChannelRatio['app_pct'] ?? 0 }}%)</x-text.body>
                                </div>
                                <x-text.caption class="d-block mb-2 ps-4 fst-normal">
                                    Rp {{ number_format($paymentChannelRatio['app_amount'] ?? 0, 0, ',', '.') }}
                                </x-text.caption>

                                <div class="d-flex justify-content-between align-items-center mb-0.5">
                                    <span class="d-flex align-items-center gap-2">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block; flex-shrink: 0;"></span>
                                        <x-text.body style="font-size: 13px !important;">Entry Langsung</x-text.body>
                                    </span>
                                    <x-text.body class="fw-bold text-dark" style="font-size: 12.5px !important;">{{ number_format($paymentChannelRatio['direct_count'] ?? 0) }} Tx ({{ $paymentChannelRatio['direct_pct'] ?? 0 }}%)</x-text.body>
                                </div>
                                <x-text.caption class="d-block ps-4 fst-normal">
                                    Rp {{ number_format($paymentChannelRatio['direct_amount'] ?? 0, 0, ',', '.') }}
                                </x-text.caption>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Gender Ratio Doughnut -->
                    <div class="pt-3" style="border-top: 1px dashed #f1f5f9;">
                        <div class="row align-items-center g-2">
                            <div class="col-5">
                                <div class="position-relative" style="height: 115px;">
                                    <canvas id="genderChart"></canvas>
                                </div>
                            </div>
                            <div class="col-7">
                                <x-text.label class="d-block mb-2">KOMPOSISI SANTRI</x-text.label>
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="d-flex align-items-center gap-2">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #2563EB; display: inline-block;"></span>
                                        <x-text.body style="font-size: 13px !important;">Putra (L)</x-text.body>
                                    </span>
                                    <x-text.body class="fw-bold text-dark">{{ number_format($genderRatio['l'] ?? 0) }}</x-text.body>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="d-flex align-items-center gap-2">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block;"></span>
                                        <x-text.body style="font-size: 13px !important;">Putri (P)</x-text.body>
                                    </span>
                                    <x-text.body class="fw-bold text-dark">{{ number_format($genderRatio['p'] ?? 0) }}</x-text.body>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     SECTION 4: BACKOFFICE ACCESS & ACTIVITY TELEMETRY + PWA ADOPTION (2 COLUMNS)
     (RESTRICTED TO SUPER ADMIN ONLY)
     ===================================================================== -->
@php
    $canViewSuperAdminTelemetry = ($isSuperAdmin ?? false)
        || (auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin());
@endphp
@if($canViewSuperAdminTelemetry)
@php
    $waliPwaChartAll = $loginActivity['wali_pwa_chart']['all'] ?? [
        'total' => $loginActivity['wali_total'] ?? 0,
        'total_pct' => 100,
        'login' => $loginActivity['wali_ever'] ?? 0,
        'login_pct' => 0,
        'not_login' => ($loginActivity['wali_total'] ?? 0) - ($loginActivity['wali_ever'] ?? 0),
        'not_login_pct' => 0,
    ];
    $activeSchoolFilterName = isset($schools, $selectedSchoolId) && $selectedSchoolId
        ? ($schools->firstWhere('id', $selectedSchoolId)->name ?? 'Unit Terpilih')
        : 'Semua Unit';
@endphp
<div class="row g-4 mb-5">
    <!-- Kolom Kiri (Lebar 8 Kolom): Backoffice Access Telemetry & Live Log Activity -->
    <div class="col-xl-8 col-lg-12">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
                    <div>
                        <x-text.label>BACKOFFICE ACCESS & ACTIVITY TELEMETRY</x-text.label>
                        <x-text.h2 class="mt-1">User Backoffice Online & Log Activity</x-text.h2>
                        <x-text.caption>Monitoring petugas yang sedang online aktif & jejak audit aktivitas sistem secara real-time</x-text.caption>
                    </div>
                    <div class="action-cluster flex-wrap">
                        <span class="badge tint-emerald rounded-pill px-2.5 py-1.5 fw-bold text-nowrap d-inline-flex align-items-center gap-1.5" style="font-size: 10.5px;">
                            <span class="rounded-circle" style="width: 7px; height: 7px; background-color: #10B981; display: inline-block;"></span>
                            Sedang Online: {{ $loginActivity['staff_online_count'] ?? 0 }} / {{ $loginActivity['staff_total'] ?? 0 }} Staff
                        </span>
                        <span class="badge tint-primary rounded-pill px-2.5 py-1.5 fw-bold text-nowrap" style="font-size: 10.5px;">
                            Log Hari Ini: {{ number_format($loginActivity['staff_activity_today'] ?? 0, 0, ',', '.') }} Aksi
                        </span>
                        @if(Route::has('report-audit.index'))
                            <a href="{{ route('report-audit.index') }}" class="btn btn-sm fw-bold px-3 py-1.5 text-nowrap" style="border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: #2563EB; font-size: 11.5px;">
                                Audit Log
                            </a>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Sub-Kolom Kiri: User Backoffice Sedang Online -->
                    <div class="col-lg-5">
                        <!-- Progress Strip User Online -->
                        <div class="p-3 rounded-4 mb-3" style="background: #f8fafc; border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                <x-text.caption class="fst-normal fw-bold text-dark">User Sedang Online: {{ $loginActivity['staff_online_count'] ?? 0 }} Staff</x-text.caption>
                                <x-text.caption class="fst-normal">Hari Ini: <strong>{{ $loginActivity['staff_count'] ?? 0 }} Staff ({{ $loginActivity['staff_percentage'] ?? 0 }}%)</strong></x-text.caption>
                            </div>
                            <div class="w-100 rounded-pill" style="height: 6px; background: #e2e8f0;">
                                <div class="rounded-pill" style="height: 6px; width: {{ min(100, max(($loginActivity['staff_online_count'] ?? 0) > 0 ? 8 : 0, $loginActivity['staff_online_percentage'] ?? 0)) }}%; background: #10B981;"></div>
                            </div>
                        </div>

                        <!-- Currently Online Backoffice Users Feed -->
                        <div class="bi-feed-scroll">
                            @forelse($recentStaffLogins ?? [] as $staff)
                                <div class="d-flex align-items-start justify-content-between gap-2 py-2.5" style="border-bottom: 1px dashed #f1f5f9;">
                                    <div class="d-flex align-items-start gap-2.5 min-w-0">
                                        <div class="position-relative flex-shrink-0">
                                            <img src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" class="bi-avatar" onerror="this.src='{{ asset('assets/media/avatars/blank.png') }}'">
                                            <span class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white"
                                                  style="width: 10px; height: 10px; background-color: #10B981;" title="Sedang Online"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <x-text.body class="fw-bold text-dark d-block text-truncate">{{ $staff->name }}</x-text.body>
                                            <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                                                <span class="badge tint-primary rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 700;">
                                                    {{ $staff->role_name }}
                                                </span>
                                                @if($staff->school)
                                                    <x-text.caption class="fst-normal">• {{ $staff->school->name }}</x-text.caption>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="badge tint-emerald rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                                            <span class="rounded-circle" style="width: 6px; height: 6px; background-color: #10B981; display: inline-block;"></span>
                                            ONLINE
                                        </span>
                                        <x-text.caption class="d-block mt-1 text-nowrap">
                                            Aktif {{ $staff->last_login_at ? $staff->last_login_at->diffForHumans() : 'baru saja' }}
                                        </x-text.caption>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <x-text.body>Tidak ada User Backoffice yang sedang online saat ini.</x-text.body>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Sub-Kolom Kanan: Live Log Activity User Backoffice -->
                    <div class="col-lg-7">
                        <!-- Summary Strip Log Activity -->
                        <div class="p-3 rounded-4 mb-3" style="background: #f8fafc; border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                <x-text.caption class="fst-normal fw-bold text-dark">Live Audit Trail (Log Activity)</x-text.caption>
                                <x-text.caption class="fst-normal">7 Hari Terakhir: <strong>{{ number_format($loginActivity['staff_activity_7d'] ?? 0, 0, ',', '.') }} Aktivitas</strong></x-text.caption>
                            </div>
                            <div class="d-flex align-items-center gap-1 w-100 rounded-pill overflow-hidden" style="height: 6px; background: #e2e8f0;">
                                <div style="height: 6px; width: 45%; background: #10B981;"></div>
                                <div style="height: 6px; width: 40%; background: #2563EB;"></div>
                                <div style="height: 6px; width: 15%; background: #94a3b8;"></div>
                            </div>
                        </div>

                        <!-- Recent 15 Backoffice Activity Logs Feed -->
                        <div class="bi-feed-scroll">
                            @forelse($recentStaffActivities ?? [] as $act)
                                @php
                                    $causer = $act->causer;
                                    $causerName = $causer->name ?? 'User Backoffice';
                                    $causerRole = $causer ? $causer->role_name : 'System';
                                    $causerAvatar = $causer->avatar_url ?? asset('assets/media/avatars/blank.png');
                                    $actTime = $act->created_at;
                                    $isActToday = $actTime && $actTime->isToday();

                                    $humanLog = \App\Traits\LogActivityTrait::humanizeActivity($act);
                                    $eventBadgeClass = $humanLog['badge_class'] ?? 'tint-primary';
                                    $eventLabel = $humanLog['badge_label'] ?? 'AKTIVITAS';
                                    $humanDescription = $humanLog['description'] ?? $act->description;

                                    $device = method_exists($act, 'getExtraProperty') ? ($act->getExtraProperty('device') ?? 'Desktop') : 'Desktop';
                                    $ip = method_exists($act, 'getExtraProperty') ? $act->getExtraProperty('ip') : null;
                                @endphp
                                <div class="d-flex align-items-start justify-content-between gap-2 py-2.5" style="border-bottom: 1px dashed #f1f5f9;">
                                    <div class="d-flex align-items-start gap-2.5 min-w-0">
                                        <div class="position-relative flex-shrink-0 mt-0.5">
                                            <img src="{{ $causerAvatar }}" alt="{{ $causerName }}" class="bi-avatar" onerror="this.src='{{ asset('assets/media/avatars/blank.png') }}'">
                                            <span class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white"
                                                  style="width: 10px; height: 10px; background-color: {{ $isActToday ? '#10B981' : '#94a3b8' }};"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="d-flex flex-wrap align-items-center gap-1.5">
                                                <x-text.body class="fw-bold text-dark mb-0">{{ $causerName }}</x-text.body>
                                                <span class="badge tint-primary rounded-pill px-2 py-0.5" style="font-size: 9.5px; font-weight: 700;">
                                                    {{ $causerRole }}
                                                </span>
                                                <span class="badge {{ $eventBadgeClass }} rounded-pill px-2 py-0.5" style="font-size: 9.5px; font-weight: 700;">
                                                    {{ $eventLabel }}
                                                </span>
                                            </div>
                                            <x-text.caption class="d-block fst-normal text-dark fw-semibold mt-1" style="line-height: 1.35;">
                                                {{ \Illuminate\Support\Str::limit($humanDescription, 115) }}
                                            </x-text.caption>
                                            <x-text.caption class="d-block fst-normal mt-0.5" style="font-size: 11px !important; color: #64748b !important;">
                                                {{ $device }}{{ $ip ? ' • IP: ' . $ip : '' }}
                                            </x-text.caption>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="badge {{ $isActToday ? 'tint-emerald' : 'tint-slate' }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 10px;">
                                            {{ $actTime ? $actTime->diffForHumans() : '-' }}
                                        </span>
                                        <x-text.caption class="d-block mt-1 text-nowrap">
                                            {{ $actTime ? $actTime->translatedFormat('d M Y, H:i') : '-' }}
                                        </x-text.caption>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <x-text.body>Belum ada riwayat log aktivitas User Backoffice.</x-text.body>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan (Lebar 4 Kolom): Bar Chart Statistik Akses PWA Wali Santri -->
    <div class="col-xl-4 col-lg-12">
        <div class="card bi-card h-100">
            <div class="card-body p-5 pb-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                        <div>
                            <x-text.label>PWA ADOPTION ANALYTICS</x-text.label>
                            <x-text.h2 class="mt-1">Statistik Akses PWA Wali</x-text.h2>
                            <x-text.caption id="waliPwaChartSubtitle">Proporsi Wali Santri pernah login vs belum login ke PWA</x-text.caption>
                        </div>
                        <div class="action-cluster">
                            <button type="button" id="btnWaliPwaAll" class="bi-pill-btn active" onclick="switchWaliPwaChart('all')" style="padding: 4px 10px; font-size: 11px;">Semua</button>
                            <button type="button" id="btnWaliPwa30d" class="bi-pill-btn" onclick="switchWaliPwaChart('30d')" style="padding: 4px 10px; font-size: 11px;">30H</button>
                            <button type="button" id="btnWaliPwa7d" class="bi-pill-btn" onclick="switchWaliPwaChart('7d')" style="padding: 4px 10px; font-size: 11px;">7H</button>
                            <button type="button" id="btnWaliPwaToday" class="bi-pill-btn" onclick="switchWaliPwaChart('today')" style="padding: 4px 10px; font-size: 11px;">Hari Ini</button>
                        </div>
                    </div>

                    <!-- Filter Scope Strip -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded-4 mb-3" style="background: #f8fafc; border-radius: 14px;">
                        <x-text.caption class="fst-normal fw-semibold text-dark">
                            Filter Unit: <strong style="color: #2563EB;">{{ $activeSchoolFilterName }}</strong>
                        </x-text.caption>
                        <span id="waliPwaPeriodBadge" class="badge tint-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 10px;">
                            Semua Waktu
                        </span>
                    </div>

                    <!-- Bar Chart Container -->
                    <div class="position-relative w-100 mb-3" style="height: 225px;">
                        <canvas id="waliPwaBarChart"></canvas>
                    </div>
                </div>

                <!-- Summary Breakdown (Angka & Prosentase) -->
                <div class="d-flex flex-column gap-2 pt-2" style="border-top: 1px dashed #e2e8f0;">
                    <!-- Row 1: Total Wali Santri -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded-3" style="background: #f8fafc; border-radius: 14px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background: #2563EB;"></span>
                            <x-text.body class="fw-bold text-dark mb-0" style="font-size: 13px;">Total Wali Santri</x-text.body>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="waliStatTotalCount" class="fw-bold text-dark" style="font-size: 14px;">{{ number_format($waliPwaChartAll['total'], 0, ',', '.') }}</span>
                            <span id="waliStatTotalPct" class="badge tint-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10.5px;">{{ $waliPwaChartAll['total_pct'] }}%</span>
                        </div>
                    </div>

                    <!-- Row 2: Wali Santri Login PWA -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded-3" style="background: #f8fafc; border-radius: 14px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background: #10B981;"></span>
                            <x-text.body class="fw-bold text-dark mb-0" style="font-size: 13px;">Wali Santri Login PWA</x-text.body>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="waliStatLoginCount" class="fw-bold" style="font-size: 14px; color: #10B981;">{{ number_format($waliPwaChartAll['login'], 0, ',', '.') }}</span>
                            <span id="waliStatLoginPct" class="badge tint-emerald rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10.5px;">{{ $waliPwaChartAll['login_pct'] }}%</span>
                        </div>
                    </div>

                    <!-- Row 3: Wali Santri Tidak Login PWA -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded-3" style="background: #f8fafc; border-radius: 14px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background: #DC2626;"></span>
                            <x-text.body class="fw-bold text-dark mb-0" style="font-size: 13px;">Wali Santri Tidak Login</x-text.body>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="waliStatNotLoginCount" class="fw-bold" style="font-size: 14px; color: #DC2626;">{{ number_format($waliPwaChartAll['not_login'], 0, ',', '.') }}</span>
                            <span id="waliStatNotLoginPct" class="badge tint-red rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10.5px;">{{ $waliPwaChartAll['not_login_pct'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- =====================================================================
     SECTION 5: AGENDA & KEGIATAN MENDATANG
     ===================================================================== -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card bi-card">
            <div class="card-body p-5 pb-4">
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div>
                        <x-text.label>ACADEMIC & BOARDING CALENDAR</x-text.label>
                        <x-text.h2 class="mt-1">Agenda & Kegiatan Mendatang</x-text.h2>
                    </div>
                    <div class="action-cluster">
                        <a href="{{ route('schedule.index') }}" class="action-icon-btn" title="Kelola Jadwal & Agenda">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </a>
                    </div>
                </div>

                @if(isset($upcomingSchedules) && $upcomingSchedules->count() > 0)
                    <div class="row g-3">
                        @foreach($upcomingSchedules as $schedule)
                            <div class="col-xl-4 col-md-6">
                                <div class="d-flex align-items-center p-3 rounded-4 h-100" style="background: #f8fafc; border-radius: 16px;">
                                    <div class="d-flex flex-column align-items-center justify-content-center tint-primary me-3" style="min-width: 54px; height: 54px; border-radius: 14px;">
                                        <span class="fw-bolder lh-1" style="font-size: 18px;">{{ $schedule->date->format('d') }}</span>
                                        <span class="fw-bold lh-1 mt-1" style="font-size: 10px;">{{ \Illuminate\Support\Str::upper($schedule->date->translatedFormat('M')) }}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <x-text.body class="fw-bold text-dark mb-0.5">{{ $schedule->name }}</x-text.body>
                                        <x-text.caption class="d-block mb-1">{{ \Illuminate\Support\Str::limit($schedule->description, 48) }}</x-text.caption>
                                        @if($schedule->type == 'SCHOOL' && $schedule->school)
                                            <span class="badge tint-primary rounded-pill px-2 py-0.5" style="font-size: 10px;">{{ $schedule->school->name }}</span>
                                        @else
                                            <span class="badge tint-emerald rounded-pill px-2 py-0.5" style="font-size: 10px;">Umum / Pondok</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <x-text.body>Belum ada agenda mendatang yang terjadwal.</x-text.body>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     MODAL AUDIT STATUS & DISTRIBUSI WALI SANTRI (PAKRT NATIVE VITE)
     ===================================================================== -->
<div class="modal fade" id="modalWaliSantriAudit" tabindex="-1" aria-labelledby="modalWaliSantriAuditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 p-2" style="border-radius: 24px; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08); background: #ffffff;">
            <div class="modal-header border-0 pb-0 px-5 pt-4 d-flex align-items-center justify-content-between">
                <div>
                    <x-text.label>AUDIT BI DATA INTEGRITY</x-text.label>
                    <x-text.h1 id="modalWaliSantriAuditLabel" class="mt-1 mb-0" style="font-size: 20px;">
                        Distribusi & Status Akun Wali Santri
                    </x-text.h1>
                    <x-text.caption class="d-block mt-0.5">
                        Rekonsiliasi data antara akun pengguna terdaftar, status keaktifan santri, dan aktivitas login portal.
                    </x-text.caption>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="border-radius: 10px;"></button>
            </div>

            <div class="modal-body px-5 py-4">
                <!-- Segmented Progress Bar -->
                @php
                    $registeredTotal = max(1, $loginActivity['wali_registered_total'] ?? ($totalWali + ($loginActivity['wali_inactive'] ?? 0) + ($loginActivity['wali_orphan'] ?? 0)));
                    $pctActive = round(($totalWali / $registeredTotal) * 100, 1);
                    $pctInactive = round((($loginActivity['wali_inactive'] ?? 0) / $registeredTotal) * 100, 1);
                    $pctOrphan = max(0, round(100 - $pctActive - $pctInactive, 1));
                @endphp
                <div class="p-4 rounded-4 mb-4" style="background: #f8fafc; border-radius: 20px;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <x-text.label>TOTAL AKUN TERDAFTAR DI DATABASE</x-text.label>
                        <span class="badge tint-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 12px;">
                            {{ number_format($loginActivity['wali_registered_total'] ?? $registeredTotal) }} Total Akun
                        </span>
                    </div>
                    <div class="progress w-100 mb-2" style="height: 10px; border-radius: 10px; background-color: #e2e8f0; overflow: hidden;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $pctActive }}%; background-color: #10B981;" title="Santri Aktif: {{ $pctActive }}%"></div>
                        <div class="progress-bar" role="progressbar" style="width: {{ $pctInactive }}%; background-color: #DC2626;" title="Santri Non-Aktif/Out: {{ $pctInactive }}%"></div>
                        <div class="progress-bar" role="progressbar" style="width: {{ $pctOrphan }}%; background-color: #94a3b8;" title="Tanpa Santri / Orphan: {{ $pctOrphan }}%"></div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #10B981;"></span>
                            <x-text.caption class="mb-0">Santri Aktif: <strong class="text-dark">{{ number_format($totalWali) }}</strong> ({{ $pctActive }}%)</x-text.caption>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #DC2626;"></span>
                            <x-text.caption class="mb-0">Santri Out/Alumni: <strong class="text-dark">{{ number_format($loginActivity['wali_inactive'] ?? 0) }}</strong> ({{ $pctInactive }}%)</x-text.caption>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #94a3b8;"></span>
                            <x-text.caption class="mb-0">Tanpa Santri: <strong class="text-dark">{{ number_format($loginActivity['wali_orphan'] ?? 0) }}</strong> ({{ $pctOrphan }}%)</x-text.caption>
                        </div>
                    </div>
                </div>

                <!-- 3 Pillars Cards Grid -->
                <div class="row g-3">
                    <!-- Pilar 1: Wali Santri Aktif Riil -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 p-3" style="border-radius: 18px; background: #ffffff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: rgba(16, 185, 129, 0.1); color: #10B981; font-size: 11px;">
                                    Santri Aktif
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <x-text.amount class="amount-lg d-block mb-1" style="color: #10B981 !important;">
                                {{ number_format($totalWali) }} Wali
                            </x-text.amount>
                            <x-text.caption class="d-block mb-3" style="font-size: 11.5px; line-height: 1.35;">
                                Memiliki minimal satu santri berstatus <strong>ACTIVE</strong> di unit pondok.
                            </x-text.caption>
                            
                            <div class="pt-2 mb-3" style="border-top: 1px dashed #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Pernah Login PWA</x-text.caption>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">{{ number_format($loginActivity['wali_ever'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Hanya Terdaftar</x-text.caption>
                                    <span class="fw-bold" style="color: #D97706; font-size: 12px;">{{ number_format($loginActivity['wali_never'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Login 7 Hari Terakhir</x-text.caption>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">{{ number_format($loginActivity['wali_7d'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <x-text.caption>Login Hari Ini</x-text.caption>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">{{ number_format($loginActivity['wali_count'] ?? 0) }}</span>
                                </div>
                            </div>

                            <a href="{{ route('user.index', ['status' => 'ACTIVE']) }}" class="btn btn-sm w-100 fw-bold mt-auto" style="border-radius: 12px; background-color: rgba(16, 185, 129, 0.1); color: #10B981;">
                                Buka Data Wali Aktif
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 2: Wali Santri Out / Alumni -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 p-3" style="border-radius: 18px; background: #ffffff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border: 1px solid rgba(220, 38, 38, 0.2) !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: rgba(220, 38, 38, 0.1); color: #DC2626; font-size: 11px;">
                                    Santri Non-Aktif
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </div>
                            <x-text.amount class="amount-lg d-block mb-1" style="color: #DC2626 !important;">
                                {{ number_format($loginActivity['wali_inactive'] ?? 0) }} Wali
                            </x-text.amount>
                            <x-text.caption class="d-block mb-3" style="font-size: 11.5px; line-height: 1.35;">
                                Seluruh santrinya sudah tidak aktif (lulus, keluar/DO, atau pindah).
                            </x-text.caption>

                            <div class="pt-2 mb-3" style="border-top: 1px dashed #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Santri Lulus (Alumni)</x-text.caption>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">{{ number_format($loginActivity['wali_alumni'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Santri Keluar (DO)</x-text.caption>
                                    <span class="fw-bold" style="color: #DC2626; font-size: 12px;">{{ number_format($loginActivity['wali_dropped_out'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Santri Pindah (Mutasi)</x-text.caption>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">{{ number_format($loginActivity['wali_transferred'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <x-text.caption>Arsip Keuangan</x-text.caption>
                                    <span class="badge tint-slate px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;">Tersimpan</span>
                                </div>
                            </div>

                            <a href="{{ route('student.index') }}" class="btn btn-sm w-100 fw-bold mt-auto" style="border-radius: 12px; background-color: rgba(220, 38, 38, 0.1); color: #DC2626;">
                                Kelola Data Santri
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 3: Akun Tanpa Santri (Orphan) -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 p-3" style="border-radius: 18px; background: #ffffff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: rgba(148, 163, 184, 0.15); color: #475569; font-size: 11px;">
                                    Tanpa Santri
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="23" y2="14"/><line x1="23" y1="8" x2="17" y2="14"/></svg>
                            </div>
                            <x-text.amount class="amount-lg d-block mb-1" style="color: #64748b !important;">
                                {{ number_format($loginActivity['wali_orphan'] ?? 0) }} Akun
                            </x-text.amount>
                            <x-text.caption class="d-block mb-3" style="font-size: 11.5px; line-height: 1.35;">
                                Akun terdaftar yang belum/tidak memiliki tautan data santri aktif maupun alumni.
                            </x-text.caption>

                            <div class="pt-2 mb-3" style="border-top: 1px dashed #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Sumber Akun</x-text.caption>
                                    <span class="badge tint-slate px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;">PPDB / Manual</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Status Akses</x-text.caption>
                                    <span class="text-dark fw-bold" style="font-size: 12px;">is_active = 1</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <x-text.caption>Tautan Siswa</x-text.caption>
                                    <span class="text-muted fw-bold" style="font-size: 12px;">0 Santri</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <x-text.caption>Rekomendasi</x-text.caption>
                                    <span class="badge tint-primary px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;">Verifikasi</span>
                                </div>
                            </div>

                            <a href="{{ route('user.index') }}" class="btn btn-sm w-100 fw-bold mt-auto" style="border-radius: 12px; background-color: #f1f5f9; color: #475569;">
                                Kelola Akun Pengguna
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 px-5 pb-4 pt-2 d-flex justify-content-between align-items-center">
                <x-text.caption class="text-muted mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Data metrik Executive BI kini terisolasi akurat hanya untuk santri yang berstatus aktif.
                </x-text.caption>
                <button type="button" class="btn btn-sm btn-dark px-4 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 12px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const biBillTrendData = {!! json_encode($billTrendChart ?? ['labels' => [], 'target' => [], 'realisasi' => [], 'tunggakan' => [], 'percentage' => []]) !!};
    const biInflowData = {!! json_encode($inflowTrendChart ?? ['daily_30' => ['labels' => [], 'saldo_in' => [], 'bill_paid' => [], 'saving_in' => []], 'monthly_12' => ['labels' => [], 'saldo_in' => [], 'bill_paid' => [], 'saving_in' => []]]) !!};
    const biWaliPwaData = {!! json_encode($canViewSuperAdminTelemetry ? ($loginActivity['wali_pwa_chart'] ?? [
        'all' => ['total' => 0, 'total_pct' => 0, 'login' => 0, 'login_pct' => 0, 'not_login' => 0, 'not_login_pct' => 0],
        '30d' => ['total' => 0, 'total_pct' => 0, 'login' => 0, 'login_pct' => 0, 'not_login' => 0, 'not_login_pct' => 0],
        '7d' => ['total' => 0, 'total_pct' => 0, 'login' => 0, 'login_pct' => 0, 'not_login' => 0, 'not_login_pct' => 0],
        'today' => ['total' => 0, 'total_pct' => 0, 'login' => 0, 'login_pct' => 0, 'not_login' => 0, 'not_login_pct' => 0],
    ]) : []) !!};
    let biInflowChartInstance = null;
    let biWaliPwaChartInstance = null;
    let currentWaliPwaMode = 'all';

    function formatRupiahCompact(value) {
        const num = Number(value) || 0;
        if (num >= 1000000000) return 'Rp ' + (num / 1000000000).toFixed(1).replace('.0', '') + ' M';
        if (num >= 1000000) return 'Rp ' + (num / 1000000).toFixed(1).replace('.0', '') + ' Jt';
        if (num >= 1000) return 'Rp ' + (num / 1000).toFixed(0) + ' Rb';
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Chart Target vs Realisasi Pembayaran Bulanan
        const billCtxEl = document.getElementById('biBillTrendChart');
        if (billCtxEl) {
            const billCtx = billCtxEl.getContext('2d');
            new Chart(billCtx, {
                type: 'bar',
                data: {
                    labels: biBillTrendData.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Target Tagihan (Rp)',
                            data: biBillTrendData.target,
                            backgroundColor: 'rgba(37, 99, 235, 0.16)',
                            borderColor: '#2563EB',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            yAxisID: 'y',
                            order: 2
                        },
                        {
                            type: 'bar',
                            label: 'Realisasi Terbayar (Rp)',
                            data: biBillTrendData.realisasi,
                            backgroundColor: '#10B981',
                            borderRadius: 6,
                            yAxisID: 'y',
                            order: 1
                        },
                        {
                            type: 'line',
                            label: 'Capaian (%)',
                            data: biBillTrendData.percentage,
                            borderColor: '#2563EB',
                            backgroundColor: '#2563EB',
                            borderWidth: 2.5,
                            pointRadius: 3.5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2563EB',
                            pointBorderWidth: 2,
                            tension: 0.35,
                            yAxisID: 'y1',
                            order: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.yAxisID === 'y1') {
                                        return ' ' + context.dataset.label + ': ' + context.parsed.y + '%';
                                    }
                                    return ' ' + context.dataset.label + ': Rp ' + Number(context.parsed.y || 0).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter', size: 11, weight: '600' }, color: '#64748b' }
                        },
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            grid: { color: 'rgba(148, 163, 184, 0.12)', drawBorder: false },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#64748b',
                                callback: function(value) { return formatRupiahCompact(value); }
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            max: 100,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#2563EB',
                                callback: function(value) { return value + '%'; }
                            }
                        }
                    }
                }
            });
        }

        // 2. Chart Trend Saldo Masuk & Pembayaran
        const inflowCtxEl = document.getElementById('biInflowTrendChart');
        if (inflowCtxEl) {
            const inflowCtx = inflowCtxEl.getContext('2d');
            const emeraldGrad = inflowCtx.createLinearGradient(0, 0, 0, 280);
            emeraldGrad.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
            emeraldGrad.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

            const blueGrad = inflowCtx.createLinearGradient(0, 0, 0, 280);
            blueGrad.addColorStop(0, 'rgba(37, 99, 235, 0.22)');
            blueGrad.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

            const initialSeries = biInflowData.daily_30 || { labels: [], saldo_in: [], bill_paid: [] };

            biInflowChartInstance = new Chart(inflowCtx, {
                type: 'line',
                data: {
                    labels: initialSeries.labels,
                    datasets: [
                        {
                            label: 'Saldo Masuk / Top-Up (Rp)',
                            data: initialSeries.saldo_in,
                            borderColor: '#10B981',
                            backgroundColor: emeraldGrad,
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'Pembayaran Tagihan (Rp)',
                            data: initialSeries.bill_paid,
                            borderColor: '#2563EB',
                            backgroundColor: blueGrad,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { family: 'Inter', size: 11, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': Rp ' + Number(context.parsed.y || 0).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxTicksLimit: 8, font: { family: 'Inter', size: 10.5 }, color: '#64748b' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.12)', drawBorder: false },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#64748b',
                                callback: function(value) { return formatRupiahCompact(value); }
                            }
                        }
                    }
                }
            });
        }

        // 3. Chart Kanal Pembayaran Tagihan (Aplikasi Approval vs Entry Langsung)
        const paymentChannelCtxEl = document.getElementById('paymentChannelChart');
        if (paymentChannelCtxEl) {
            const appAmt = {{ $paymentChannelRatio['app_amount'] ?? 0 }};
            const directAmt = {{ $paymentChannelRatio['direct_amount'] ?? 0 }};
            const appCnt = {{ $paymentChannelRatio['app_count'] ?? 0 }};
            const directCnt = {{ $paymentChannelRatio['direct_count'] ?? 0 }};
            const appPct = {{ $paymentChannelRatio['app_pct'] ?? 0 }};
            const directPct = {{ $paymentChannelRatio['direct_pct'] ?? 0 }};
            const hasChannelData = (appAmt + directAmt) > 0;

            new Chart(paymentChannelCtxEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: hasChannelData ? ['Aplikasi (Approval)', 'Entry Langsung'] : ['Belum Ada Data'],
                    datasets: [{
                        data: hasChannelData ? [appAmt, directAmt] : [1],
                        backgroundColor: hasChannelData ? ['#2563EB', '#10B981'] : ['#e2e8f0'],
                        borderWidth: 0,
                        hoverOffset: hasChannelData ? 4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: hasChannelData,
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const cnt = idx === 0 ? appCnt : directCnt;
                                    const pct = idx === 0 ? appPct : directPct;
                                    const amt = Number(context.parsed || 0).toLocaleString('id-ID');
                                    return ' ' + context.label + ': Rp ' + amt + ' (' + cnt + ' Tx • ' + pct + '%)';
                                }
                            }
                        }
                    },
                    cutout: '72%'
                }
            });
        }

        // 4. Chart Komposisi Putra vs Putri
        const genderCtxEl = document.getElementById('genderChart');
        if (genderCtxEl) {
            new Chart(genderCtxEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Putra', 'Putri'],
                    datasets: [{
                        data: [{{ $genderRatio['l'] ?? 0 }}, {{ $genderRatio['p'] ?? 0 }}],
                        backgroundColor: ['#2563EB', '#10B981'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '72%'
                }
            });
        }

        // 5. Bar Chart Statistik Akses PWA Wali Santri (Total, Login, Tidak Login)
        const waliPwaCtxEl = document.getElementById('waliPwaBarChart');
        if (waliPwaCtxEl) {
            const initialWali = biWaliPwaData.all || { total: 0, total_pct: 0, login: 0, login_pct: 0, not_login: 0, not_login_pct: 0 };

            const waliBarDataLabelPlugin = {
                id: 'waliBarDataLabelPlugin',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    const activeData = biWaliPwaData[currentWaliPwaMode] || initialWali;
                    const pcts = [activeData.total_pct, activeData.login_pct, activeData.not_login_pct];
                    const colors = ['#1e293b', '#059669', '#dc2626'];
                    const meta = chart.getDatasetMeta(0);

                    ctx.save();
                    ctx.font = '700 11px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    meta.data.forEach((bar, index) => {
                        const rawVal = Number(chart.data.datasets[0].data[index] || 0);
                        const pctVal = pcts[index] ?? 0;
                        const labelText = rawVal.toLocaleString('id-ID') + ' (' + pctVal + '%)';
                        ctx.fillStyle = colors[index] || '#0f172a';
                        ctx.fillText(labelText, bar.x, bar.y - 6);
                    });
                    ctx.restore();
                }
            };

            biWaliPwaChartInstance = new Chart(waliPwaCtxEl.getContext('2d'), {
                type: 'bar',
                plugins: [waliBarDataLabelPlugin],
                data: {
                    labels: ['Total Wali', 'Login PWA', 'Tidak Login'],
                    datasets: [{
                        label: 'Jumlah Wali Santri',
                        data: [initialWali.total, initialWali.login, initialWali.not_login],
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.88)',
                            'rgba(16, 185, 129, 0.88)',
                            'rgba(220, 38, 38, 0.88)'
                        ],
                        borderColor: ['#2563EB', '#10B981', '#DC2626'],
                        borderWidth: 1.5,
                        borderRadius: 10,
                        maxBarThickness: 54
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 26,
                            right: 8,
                            left: 4,
                            bottom: 0
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    const activeData = biWaliPwaData[currentWaliPwaMode] || initialWali;
                                    const pcts = [activeData.total_pct, activeData.login_pct, activeData.not_login_pct];
                                    const val = Number(context.parsed.y || 0).toLocaleString('id-ID');
                                    const pct = pcts[context.dataIndex] ?? 0;
                                    return ' ' + context.label + ': ' + val + ' Wali (' + pct + '%)';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Inter', size: 11.5, weight: '700' },
                                color: '#334155'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.12)', drawBorder: false },
                            ticks: {
                                precision: 0,
                                font: { family: 'Inter', size: 10.5 },
                                color: '#64748b',
                                callback: function(value) {
                                    return Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    function switchWaliPwaChart(mode) {
        if (!biWaliPwaChartInstance || !biWaliPwaData[mode]) return;
        currentWaliPwaMode = mode;
        const metric = biWaliPwaData[mode];

        biWaliPwaChartInstance.data.datasets[0].data = [metric.total, metric.login, metric.not_login];
        biWaliPwaChartInstance.update();

        const modeMap = {
            all: {
                btnId: 'btnWaliPwaAll',
                badge: 'Semua Waktu',
                subtitle: 'Proporsi Wali Santri pernah login vs belum login ke PWA'
            },
            '30d': {
                btnId: 'btnWaliPwa30d',
                badge: '30 Hari Terakhir',
                subtitle: 'Proporsi Wali Santri aktif login PWA dalam 30 hari terakhir'
            },
            '7d': {
                btnId: 'btnWaliPwa7d',
                badge: '7 Hari Terakhir',
                subtitle: 'Proporsi Wali Santri aktif login PWA dalam 7 hari terakhir'
            },
            today: {
                btnId: 'btnWaliPwaToday',
                badge: 'Hari Ini',
                subtitle: 'Proporsi Wali Santri aktif login PWA pada hari ini'
            }
        };

        ['btnWaliPwaAll', 'btnWaliPwa30d', 'btnWaliPwa7d', 'btnWaliPwaToday'].forEach(id => {
            document.getElementById(id)?.classList.remove('active');
        });
        const cfg = modeMap[mode] || modeMap.all;
        document.getElementById(cfg.btnId)?.classList.add('active');

        const badgeEl = document.getElementById('waliPwaPeriodBadge');
        if (badgeEl) badgeEl.textContent = cfg.badge;

        const subtitleEl = document.getElementById('waliPwaChartSubtitle');
        if (subtitleEl) subtitleEl.textContent = cfg.subtitle;

        const totalCntEl = document.getElementById('waliStatTotalCount');
        const totalPctEl = document.getElementById('waliStatTotalPct');
        const loginCntEl = document.getElementById('waliStatLoginCount');
        const loginPctEl = document.getElementById('waliStatLoginPct');
        const notLoginCntEl = document.getElementById('waliStatNotLoginCount');
        const notLoginPctEl = document.getElementById('waliStatNotLoginPct');

        if (totalCntEl) totalCntEl.textContent = Number(metric.total || 0).toLocaleString('id-ID');
        if (totalPctEl) totalPctEl.textContent = (metric.total_pct ?? 0) + '%';
        if (loginCntEl) loginCntEl.textContent = Number(metric.login || 0).toLocaleString('id-ID');
        if (loginPctEl) loginPctEl.textContent = (metric.login_pct ?? 0) + '%';
        if (notLoginCntEl) notLoginCntEl.textContent = Number(metric.not_login || 0).toLocaleString('id-ID');
        if (notLoginPctEl) notLoginPctEl.textContent = (metric.not_login_pct ?? 0) + '%';
    }

    function switchInflowChart(mode) {
        if (!biInflowChartInstance || !biInflowData[mode]) return;
        const series = biInflowData[mode];
        biInflowChartInstance.data.labels = series.labels;
        biInflowChartInstance.data.datasets[0].data = series.saldo_in;
        biInflowChartInstance.data.datasets[1].data = series.bill_paid;
        biInflowChartInstance.update();

        const btn30d = document.getElementById('btnInflow30d');
        const btn12m = document.getElementById('btnInflow12m');
        const subtitle = document.getElementById('inflowChartSubtitle');

        if (mode === 'daily_30') {
            btn30d?.classList.add('active');
            btn12m?.classList.remove('active');
            if (subtitle) subtitle.textContent = 'Pergerakan kas masuk harian (30 hari terakhir)';
        } else {
            btn12m?.classList.add('active');
            btn30d?.classList.remove('active');
            if (subtitle) subtitle.textContent = 'Pergerakan kas masuk bulanan (12 bulan terakhir)';
        }
    }
</script>
@endpush
