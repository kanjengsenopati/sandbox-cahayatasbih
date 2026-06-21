@push('css')
<style>
    .card-metric {
        transition: transform 0.2s;
    }
    .card-metric:hover {
        transform: translateY(-5px);
    }
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-soft-emerald {
        background-color: rgba(16, 185, 129, 0.1);
    }
    .text-emerald {
        color: #10B981 !important;
    }
    .bg-soft-blue {
        background-color: rgba(37, 99, 235, 0.1);
    }
    .text-blue {
        color: #2563EB !important;
    }
</style>
@endpush

<div class="row g-5 g-xl-8 mb-5 mb-xl-10">
    <!-- Total Penjualan Hari Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-[24px] border-0 card-metric">
            <div class="card-body d-flex align-items-center">
                <div class="icon-circle bg-soft-emerald me-4">
                    <i class="fas fa-wallet fs-2 text-emerald"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bolder fs-2x text-emerald">Rp {{ number_format($salesToday, 0, ',', '.') }}</span>
                    <span class="fw-bold text-gray-400">Penjualan Hari Ini</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Transaksi Hari Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-[24px] border-0 card-metric">
            <div class="card-body d-flex align-items-center">
                <div class="icon-circle bg-soft-blue me-4">
                    <i class="fas fa-receipt fs-2 text-blue"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bolder fs-2x text-dark">{{ number_format($transactionsToday) }}</span>
                    <span class="fw-bold text-gray-400">Transaksi Hari Ini</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Item Barang -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-[24px] border-0 card-metric">
            <div class="card-body d-flex align-items-center">
                <div class="icon-circle bg-light-warning me-4">
                    <i class="fas fa-boxes-stacked fs-2 text-warning"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bolder fs-2x text-dark">{{ number_format($totalBarang) }}</span>
                    <span class="fw-bold text-gray-400">Total Item Barang</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Karyawan Outlet -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-[24px] border-0 card-metric">
            <div class="card-body d-flex align-items-center">
                <div class="icon-circle bg-light-primary me-4">
                    <i class="fas fa-users fs-2 text-primary"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bolder fs-2x text-dark">{{ number_format($totalKaryawan) }}</span>
                    <span class="fw-bold text-gray-400">Karyawan Outlet</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-5 mb-xl-10">
    <!-- Chart Penjualan -->
    <div class="col-lg-8">
        <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm rounded-[24px] border-0">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Grafik Performa Penjualan</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Analisis nominal penjualan 7 hari terakhir</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="position-relative h-300px">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Terkini -->
    <div class="col-lg-4">
        <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm rounded-[24px] border-0">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Transaksi POS Terkini</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Aktifitas kasir terbaru hari ini</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                    <div class="timeline-label">
                        @foreach($recentTransactions as $tx)
                            <div class="d-flex align-items-center mb-6">
                                <!-- Icon Box -->
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light-success rounded min-w-50px h-50px me-4">
                                    <i class="fas fa-shopping-bag text-success fs-3"></i>
                                </div>
                                
                                <!-- Content -->
                                <div class="d-flex flex-column flex-grow-1">
                                    <span class="text-dark fw-bolder fs-6 mb-1">{{ $tx->student ? $tx->student->name : 'Pelanggan Umum' }}</span>
                                    <span class="text-gray-400 fw-bold fs-8">Kasir: {{ $tx->admins ? $tx->admins->name : '-' }}</span>
                                    <span class="text-emerald fw-bolder fs-7 mt-1">Rp {{ number_format($tx->pay_amount, 0, ',', '.') }}</span>
                                </div>
                                
                                <!-- Time -->
                                <span class="badge badge-light-secondary fs-8 fw-bold">{{ $tx->created_at->format('H:i') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-10">
                        <i class="fas fa-folder-open fs-3x text-gray-300 mb-4"></i>
                        <span class="text-gray-400 fw-bold">Belum ada transaksi hari ini</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($salesChart['labels']) !!},
                datasets: [{
                    label: 'Nominal Penjualan (Rp)',
                    data: {!! json_encode($salesChart['data']) !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
