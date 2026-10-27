<div class="nested-breakdown-wrapper p-4 my-2" style="background: #f8fafc; border-radius: 20px; border: 1px solid #e2e8f0;">
    <!-- Header Ringkasan Siswa -->
    <div class="row g-3 align-items-center mb-3">
        <div class="col-12 col-md-5">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-40px me-3">
                    <span class="symbol-label bg-light-primary text-primary fw-bolder fs-5">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <h5 class="text-dark fw-bolder mb-0 fs-6">{{ $student->name }}</h5>
                    <div class="text-muted fs-8">
                        <span>NIS: {{ $student->nis ?? '-' }}</span> &bull; 
                        <span>Kelas: {{ $student->classroom->name ?? '-' }}</span> &bull;
                        <span>Wali: {{ $student->user->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-7">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                <!-- Saldo Card -->
                <div class="px-3 py-2 bg-white rounded-2 border border-gray-200 d-flex flex-column text-end">
                    <span class="text-muted uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.05em;">SALDO SANTRI</span>
                    <span class="fw-bolder fs-7 text-success">Rp {{ number_format($student->saldo ?? 0, 0, ',', '.') }}</span>
                </div>
                <!-- Total Tagihan -->
                <div class="px-3 py-2 bg-white rounded-2 border border-gray-200 d-flex flex-column text-end">
                    <span class="text-muted uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.05em;">TOTAL TAGIHAN</span>
                    <span class="fw-bolder fs-7 text-dark">Rp {{ number_format($summary['total_bill'], 0, ',', '.') }}</span>
                </div>
                <!-- Terbayar -->
                <div class="px-3 py-2 bg-white rounded-2 border border-gray-200 d-flex flex-column text-end">
                    <span class="text-muted uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.05em;">TERBAYAR</span>
                    <span class="fw-bolder fs-7 text-primary">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</span>
                </div>
                <!-- Tanggungan -->
                <div class="px-3 py-2 bg-white rounded-2 border border-gray-200 d-flex flex-column text-end">
                    <span class="text-muted uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.05em;">TANGGUNGAN</span>
                    <span class="fw-bolder fs-7 text-danger">Rp {{ number_format($summary['total_current_due'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Tagihan Bulanan / Item -->
    <div class="table-responsive bg-white rounded-3 shadow-xs border border-gray-100 overflow-hidden">
        <table class="table table-sm table-striped align-middle fs-7 mb-0">
            <thead class="bg-light">
                <tr class="text-uppercase fw-bold text-gray-500" style="font-size: 11px;">
                    <th class="ps-4" style="width: 40px;">No</th>
                    <th class="min-w-140px">Periode</th>
                    <th class="min-w-120px text-end">Nominal</th>
                    <th class="min-w-120px text-end">Dibayar</th>
                    <th class="min-w-120px text-end">Tanggungan</th>
                    <th class="min-w-140px">Metode Bayar</th>
                    <th class="min-w-100px text-center pe-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $item)
                <tr>
                    <td class="ps-4 text-muted fw-bold">{{ $idx + 1 }}</td>
                    <td class="fw-bold text-dark">{{ $item['month_name'] }}</td>
                    <td class="text-end fw-bold text-dark">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                    <td class="text-end fw-bold {{ $item['paid'] > 0 ? 'text-primary' : 'text-muted' }}">
                        Rp {{ number_format($item['paid'], 0, ',', '.') }}
                    </td>
                    <td class="text-end fw-bold {{ $item['due'] > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $item['due'] > 0 ? 'Rp ' . number_format($item['due'], 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        @if($item['is_paid'])
                            <span class="badge badge-light-success fs-9 fw-semibold">
                                {{ $item['payment_method'] }}
                                @if($item['paid_date'])
                                    ({{ $item['paid_date'] }})
                                @endif
                            </span>
                        @else
                            <span class="text-muted fs-8">-</span>
                        @endif
                    </td>
                    <td class="text-center pe-4">
                        @if($item['is_paid'])
                            <span class="badge badge-light-success fw-bolder px-2 py-1 fs-9">LUNAS</span>
                        @elseif($item['is_overdue'])
                            <span class="badge badge-light-danger fw-bolder px-2 py-1 fs-9 text-danger">MELEWATI TEMPO</span>
                        @else
                            <span class="badge badge-light-warning fw-bolder px-2 py-1 fs-9 text-warning">BELUM LUNAS</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Tidak ada data tagihan ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
