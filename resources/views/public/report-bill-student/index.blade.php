<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Tagihan Santri | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 
    <style>
        :root {
            --pakrt-primary: #2563eb;
            --pakrt-success: #10b981;
            --pakrt-error: #dc2626;
            --pakrt-slate-900: #0f172a;
            --pakrt-slate-800: #1e293b;
            --pakrt-slate-700: #334155;
            --pakrt-slate-600: #475569;
            --pakrt-slate-500: #64748b;
            --pakrt-slate-400: #94a3b8;
        }
 
        body {
            background-color: #f8fafc;
            font-family: 'Poppins', sans-serif;
            color: var(--pakrt-slate-700);
        }
 
        .header-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 60px 0 120px;
            color: white;
            text-align: center;
        }
 
        .main-container {
            margin-top: -80px;
            padding-bottom: 60px;
        }
 
        .premium-card {
            background: white;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            overflow: hidden;
        }
 
        .stat-card {
            border-radius: 20px;
            padding: 20px;
            height: 100%;
            transition: transform 0.2s;
            border: 1px solid rgba(0,0,0,0.05);
        }
 
        .stat-card:hover {
            transform: translateY(-5px);
        }
 
        .text-slate-900 { color: var(--pakrt-slate-900) !important; }
        .text-slate-800 { color: var(--pakrt-slate-800) !important; }
        .text-slate-600 { color: var(--pakrt-slate-600) !important; }
        .text-slate-500 { color: var(--pakrt-slate-500) !important; }
        .text-emerald-600 { color: var(--pakrt-success) !important; }
 
        .bg-emerald-50 { background-color: #ecfdf5; }
        .bg-blue-50 { background-color: #eff6ff; }
        .bg-rose-50 { background-color: #fff1f2; }
 
        .badge-success { background-color: var(--pakrt-success) !important; color: white !important; }
        .badge-danger { background-color: var(--pakrt-error) !important; color: white !important; }
 
        .table thead th {
            background-color: #f8fafc;
            color: var(--pakrt-slate-500);
            text-uppercase: true;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 700;
            border: none;
            padding: 15px 20px;
        }
 
        .table tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
 
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #f1f5f9;
        }
 
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
 
<div class="header-gradient">
    <div class="container">
        <h1 class="fw-boldest mb-2">Rekap Pembayaran Santri</h1>
        <p class="fs-5 opacity-75">
            {{ $academicYear ? 'Tahun Ajaran ' . $academicYear->name : 'Semua Tahun Ajaran' }}
        </p>
    </div>
</div>
 
<div class="container main-container">
    <div class="premium-card">
        <div class="p-8">
            <!-- Stats Row -->
            <div class="row g-6 mb-8">
                <div class="col-md-4">
                    <div class="stat-card bg-blue-50">
                        <div class="d-flex align-items-center mb-3">
                            <div class="symbol symbol-40px me-3">
                                <span class="symbol-label bg-white text-primary">
                                    <i class="fas fa-file-invoice-dollar fs-4"></i>
                                </span>
                            </div>
                            <span class="text-slate-600 fw-bold fs-7 uppercase tracking-wider">TOTAL TAGIHAN</span>
                        </div>
                        <div class="text-slate-900 fs-2 fw-boldest">Rp {{ number_format($totals['total_amount'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-emerald-50">
                        <div class="d-flex align-items-center mb-3">
                            <div class="symbol symbol-40px me-3">
                                <span class="symbol-label bg-white text-success">
                                    <i class="fas fa-check-circle fs-4"></i>
                                </span>
                            </div>
                            <span class="text-slate-600 fw-bold fs-7 uppercase tracking-wider">TOTAL TERBAYAR</span>
                        </div>
                        <div class="text-emerald-600 fs-2 fw-boldest">Rp {{ number_format($totals['total_paid'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-rose-50">
                        <div class="d-flex align-items-center mb-3">
                            <div class="symbol symbol-40px me-3">
                                <span class="symbol-label bg-white text-danger">
                                    <i class="fas fa-exclamation-circle fs-4"></i>
                                </span>
                            </div>
                            <span class="text-slate-600 fw-bold fs-7 uppercase tracking-wider">SISA TAGIHAN</span>
                        </div>
                        <div class="text-danger fs-2 fw-boldest">Rp {{ number_format($totals['total_unpaid'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
 
            <!-- Table Row -->
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>SANTRI</th>
                            <th class="text-center">JML TAGIHAN</th>
                            <th>TOTAL TAGIHAN</th>
                            <th>TERBAYAR</th>
                            <th>SISA</th>
                            <th style="width: 200px;">REALISASI</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 fw-bold">
                        @foreach($data as $row)
                        @php
                            $pct = $row->total_bill == 0 ? 0 : ($row->total_paid / $row->total_bill) * 100;
                            $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                            
                            $avatar = $row->avatar;
                            if ($avatar) {
                                if (!str_starts_with($avatar, 'http') && !str_starts_with($avatar, 'storage/') && !str_starts_with($avatar, 'assets/')) {
                                    $avatarUrl = asset('storage/images/avatar/' . $avatar);
                                } else {
                                    $avatarUrl = asset($avatar);
                                }
                            } else {
                                $avatarUrl = asset('assets/media/avatars/default.png');
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $avatarUrl }}" class="student-avatar me-3" alt="">
                                    <div class="d-flex flex-column">
                                        <span class="text-slate-900 fw-boldest fs-6">{{ $row->name }}</span>
                                        <span class="text-slate-400 fs-8">{{ $row->classroom_name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light-primary text-primary fw-boldest fs-8 px-3 py-1">
                                    {{ $row->bill_count }} Tagihan
                                </span>
                            </td>
                            <td>Rp {{ number_format($row->total_bill, 0, ',', '.') }}</td>
                            <td class="text-emerald-600">Rp {{ number_format($row->total_paid, 0, ',', '.') }}</td>
                            <td class="text-danger">
                                @if($row->total_unpaid > 0)
                                    Rp {{ number_format($row->total_unpaid, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar bg-{{ $color }}" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-{{ $color }} fw-boldest fs-7" style="min-width: 40px;">{{ round($pct) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-light p-6 text-center border-top">
            <span class="text-slate-400 fs-8">Laporan ini dibuat otomatis oleh sistem pada {{ date('d F Y H:i') }}</span>
        </div>
    </div>
</div>
 
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
</body>
</html>
