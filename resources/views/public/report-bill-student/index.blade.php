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
            --pakrt-radius: 24px;
        }
 
        body {
            background-color: #f8fafc;
            font-family: 'Poppins', sans-serif;
            color: var(--pakrt-slate-700);
            -webkit-font-smoothing: antialiased;
        }
 
        .header-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px 0 100px;
            color: white;
            text-align: center;
        }
 
        .main-container {
            margin-top: -60px;
            padding-bottom: 60px;
        }
 
        .premium-card {
            background: white;
            border-radius: var(--pakrt-radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            overflow: hidden;
        }
 
        .stat-card {
            border-radius: 20px;
            padding: 20px;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.02);
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
 
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }
 
        .search-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto 24px;
        }
 
        .search-input {
            border-radius: 16px;
            padding: 12px 20px 12px 45px;
            border: 1px solid #e2e8f0;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
 
        .search-input:focus {
            outline: none;
            border-color: var(--pakrt-primary);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }
 
        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pakrt-slate-400);
        }
 
        /* Table Styles */
        .table-responsive {
            border-radius: 16px;
        }
 
        .table thead th {
            background-color: #f8fafc;
            color: var(--pakrt-slate-500);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 700;
            border: none;
            padding: 18px 15px;
            white-space: nowrap;
        }
 
        .table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
 
        .table tbody tr:hover {
            background-color: #fcfdfe;
        }
 
        /* Mobile Experience */
        @media (max-width: 991px) {
            .table-desktop {
                display: none;
            }
            
            .mobile-card-container {
                display: block;
            }
 
            .mobile-student-card {
                background: white;
                border-radius: 20px;
                padding: 20px;
                margin-bottom: 16px;
                border: 1px solid #f1f5f9;
                box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            }
 
            .mobile-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px dashed #f1f5f9;
            }
 
            .mobile-row:last-child {
                border-bottom: none;
            }
 
            .mobile-label {
                font-size: 12px;
                color: var(--pakrt-slate-500);
                font-weight: 600;
            }
 
            .mobile-value {
                font-size: 13px;
                font-weight: 700;
                color: var(--pakrt-slate-800);
            }
        }
 
        @media (min-width: 992px) {
            .mobile-card-container {
                display: none;
            }
        }
 
        .student-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
 
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
        }
 
        .amount-positive { color: var(--pakrt-success); font-weight: 700; }
        .amount-negative { color: var(--pakrt-error); font-weight: 700; }
        .amount-zero { color: var(--pakrt-slate-300); font-weight: 400; }
 
        .progress-compact {
            height: 6px;
            border-radius: 3px;
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>
 
<div class="header-gradient">
    <div class="container">
        <h1 class="fw-boldest mb-2 text-white">Rekap Pembayaran Santri</h1>
        <p class="fs-6 text-white opacity-75">
            {{ $academicYear ? 'Tahun Ajaran ' . $academicYear->name : 'Semua Tahun Ajaran' }}
        </p>
    </div>
</div>
 
<div class="container main-container">
    <!-- Search Bar -->
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="studentSearch" class="search-input" placeholder="Cari nama santri...">
    </div>
 
    <div class="premium-card p-6 p-lg-8">
        <!-- Stats Row -->
        <div class="row g-4 mb-8">
            <div class="col-6 col-lg-4">
                <div class="stat-card bg-blue-50">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-file-invoice-dollar text-primary me-2 fs-7"></i>
                        <span class="text-slate-500 fw-bold fs-9 tracking-widest uppercase">TAGIHAN</span>
                    </div>
                    <div class="text-slate-900 fs-4 fs-lg-3 fw-boldest">Rp {{ number_format($totals['total_amount'], 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat-card bg-emerald-50">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2 fs-7"></i>
                        <span class="text-slate-500 fw-bold fs-9 tracking-widest uppercase">TERBAYAR</span>
                    </div>
                    <div class="text-emerald-600 fs-4 fs-lg-3 fw-boldest">Rp {{ number_format($totals['total_paid'], 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="stat-card bg-rose-50">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exclamation-circle text-danger me-2 fs-7"></i>
                        <span class="text-slate-500 fw-bold fs-9 tracking-widest uppercase">SISA</span>
                    </div>
                    <div class="text-danger fs-4 fs-lg-3 fw-boldest">Rp {{ number_format($totals['total_unpaid'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
 
        <!-- Desktop Table -->
        <div class="table-responsive table-desktop">
            <table class="table align-middle" id="reportTable">
                <thead>
                    <tr>
                        <th class="ps-4">NO</th>
                        <th>SANTRI</th>
                        @foreach($billTypes as $type)
                        <th class="text-center">{{ $type->name }}</th>
                        @endforeach
                        <th>TOTAL</th>
                        <th>TERBAYAR</th>
                        <th>SISA</th>
                        <th class="pe-4">REALISASI</th>
                    </tr>
                </thead>
                <tbody class="fw-bold">
                    @foreach($data as $index => $row)
                    <tr class="student-row" data-name="{{ strtolower($row->name) }}">
                        <td class="ps-4 text-slate-400 fs-8">{{ $index + 1 }}</td>
                        <td>
                            <div class="student-info">
                                @php
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
                                <img src="{{ $avatarUrl }}" class="student-avatar" alt="">
                                <div>
                                    <div class="text-slate-900 fs-7 fw-boldest mb-0">{{ $row->name }}</div>
                                    <div class="text-slate-400 fs-9">{{ $row->classroom_name }}</div>
                                </div>
                            </div>
                        </td>
                        @foreach($billTypes as $type)
                        @php
                            $amt = $pivotedData[$row->id][$type->id] ?? 0;
                        @endphp
                        <td class="text-center fs-8 {{ $amt > 0 ? 'text-slate-700' : 'text-slate-200' }}">
                            {{ $amt > 0 ? 'Rp ' . number_format($amt, 0, ',', '.') : '-' }}
                        </td>
                        @endforeach
                        <td class="fs-7">Rp {{ number_format($row->total_bill, 0, ',', '.') }}</td>
                        <td class="fs-7 text-emerald-600">Rp {{ number_format($row->total_paid, 0, ',', '.') }}</td>
                        <td class="fs-7 text-danger">
                            {{ $row->total_unpaid > 0 ? 'Rp ' . number_format($row->total_unpaid, 0, ',', '.') : '-' }}
                        </td>
                        <td class="pe-4">
                            @php
                                $pct = $row->total_bill == 0 ? 0 : ($row->total_paid / $row->total_bill) * 100;
                                $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress progress-compact flex-grow-1">
                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-{{ $color }} fs-9 fw-boldest">{{ round($pct) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
 
        <!-- Mobile Cards -->
        <div class="mobile-card-container">
            @foreach($data as $index => $row)
            <div class="mobile-student-card student-row" data-name="{{ strtolower($row->name) }}">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $avatarUrl }}" class="student-avatar" alt="">
                    <div>
                        <div class="text-slate-900 fs-6 fw-boldest">{{ $row->name }}</div>
                        <div class="text-slate-400 fs-8">{{ $row->classroom_name }}</div>
                    </div>
                    <div class="ms-auto text-slate-300 fs-9 fw-bold">#{{ $index + 1 }}</div>
                </div>
                
                <div class="mobile-details mb-4">
                    @foreach($billTypes as $type)
                    @php $amt = $pivotedData[$row->id][$type->id] ?? 0; @endphp
                    @if($amt > 0)
                    <div class="mobile-row">
                        <span class="mobile-label">{{ $type->name }}</span>
                        <span class="mobile-value">Rp {{ number_format($amt, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
 
                <div class="bg-light rounded-4 p-4">
                    <div class="mobile-row border-0 py-1">
                        <span class="mobile-label">TOTAL</span>
                        <span class="mobile-value">Rp {{ number_format($row->total_bill, 0, ',', '.') }}</span>
                    </div>
                    <div class="mobile-row border-0 py-1">
                        <span class="mobile-label">TERBAYAR</span>
                        <span class="mobile-value text-emerald-600">Rp {{ number_format($row->total_paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="mobile-row border-0 py-1">
                        <span class="mobile-label">SISA</span>
                        <span class="mobile-value text-danger">Rp {{ number_format($row->total_unpaid, 0, ',', '.') }}</span>
                    </div>
                    
                    @php
                        $pct = $row->total_bill == 0 ? 0 : ($row->total_paid / $row->total_bill) * 100;
                        $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fs-9 fw-boldest text-slate-400 uppercase tracking-widest">REALISASI</span>
                            <span class="text-{{ $color }} fs-8 fw-boldest">{{ round($pct) }}%</span>
                        </div>
                        <div class="progress progress-compact">
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
 
        <div class="bg-light p-6 text-center border-top mt-8">
            <p class="text-slate-400 fs-9 mb-0">Laporan ini dibuat otomatis oleh sistem pada {{ date('d F Y H:i') }}</p>
            <p class="text-slate-300 fs-10 mt-1">Sistem Informasi Cahaya Tasbih</p>
        </div>
    </div>
</div>
 
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
 
<script>
    // Search Functionality
    document.getElementById('studentSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');
        
        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            if (name.includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
