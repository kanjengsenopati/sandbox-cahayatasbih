@extends('layouts.master', ['title' => 'Audit Konsistensi Tagihan & Keuangan'])

@push('css')
<style>
    .audit-card {
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
        border: 1px solid #e2e8f0;
    }
    .audit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .badge-healthy {
        background-color: #10b981;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .badge-warning-custom {
        background-color: #f59e0b;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .issue-count {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-slate-800 mb-1">Audit & Konsistensi Data Tagihan</h3>
            <p class="text-muted mb-0">Mekanisme pemindaian otomatis integritas 6 pilar tagihan, tarif, mass billing, entri transaksi, tagihan hantu, dan mutasi saldo.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bill.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tagihan
            </a>
            <button type="button" class="btn btn-warning" onclick="triggerRepair(true)">
                <i class="fas fa-flask mr-1"></i> Simulasi Dry-Run
            </button>
            <button type="button" class="btn btn-primary" onclick="triggerRepair(false)">
                <i class="fas fa-magic mr-1"></i> Perbaiki Otomatis (Live)
            </button>
        </div>
    </div>

    <!-- 6 Modul Status Cards -->
    <div class="row mb-4">
        @foreach($auditResults as $key => $modul)
            <div class="col-md-4 mb-3">
                <div class="card audit-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                                {{ $modul['name'] }}
                            </span>
                            @if($modul['status'] === 'HEALTHY')
                                <span class="badge-healthy"><i class="fas fa-check-circle mr-1"></i> Sehat</span>
                            @else
                                <span class="badge-warning-custom"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $modul['issues_count'] }} Anomali</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-baseline gap-2 my-2">
                            <span class="issue-count {{ $modul['issues_count'] > 0 ? 'text-warning' : 'text-success' }}">
                                {{ number_format($modul['issues_count'], 0, ',', '.') }}
                            </span>
                            <span class="text-muted" style="font-size: 0.85rem;">isu terdeteksi</span>
                        </div>
                        <hr class="my-2">
                        <div class="small text-muted">
                            @if($key === 'item_1_bill_types')
                                <div>Duplikat Nama: <strong>{{ count($modul['details']['duplicate_groups']) }}</strong> grup</div>
                                <div>Total Jenis Tagihan Aktif: <strong>{{ $modul['total_records'] }}</strong></div>
                            @elseif($key === 'item_2_payment_rates')
                                <div>Tarif Tanpa Item: <strong>{{ count($modul['details']['rates_without_items']) }}</strong></div>
                                <div>Tarif Tanpa Target: <strong>{{ count($modul['details']['rates_without_target']) }}</strong></div>
                            @elseif($key === 'item_3_mass_billing')
                                <div>Siswa Nol Tagihan: <strong>{{ count($modul['details']['students_zero_bills']) }}</strong> orang</div>
                                <div>Total Santri Aktif: <strong>{{ $modul['total_active_students'] }}</strong></div>
                            @elseif($key === 'item_4_transaction_details')
                                <div>Nominal NULL: <strong>{{ number_format($modul['details']['null_or_zero_amounts'], 0, ',', '.') }}</strong></div>
                                <div>Referensi Tagihan Yatim: <strong>{{ number_format($modul['details']['orphan_bill_references'], 0, ',', '.') }}</strong></div>
                            @elseif($key === 'item_5_ghost_bills')
                                <div>UNPAID Siswa Non-Aktif: <strong>{{ number_format($modul['details']['unpaid_bills_on_inactive_students'], 0, ',', '.') }}</strong></div>
                                <div>Tagihan Siswa Terhapus: <strong>{{ number_format($modul['details']['bills_on_deleted_students'], 0, ',', '.') }}</strong></div>
                            @elseif($key === 'item_6_payment_consistency')
                                <div>Overpaid (Lebih Bayar): <strong>{{ count($modul['details']['overpaid_bills']) }}</strong> tagihan</div>
                                <div>Status Lunas Salah: <strong>{{ $modul['details']['false_paid_status'] }}</strong></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Detail Breakdown Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="font-weight-bold mb-0 text-slate-800">Tabel Hasil Audit & Rincian Aksi</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem;">
                    <tr>
                        <th style="width: 25%;">Area / Modul</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 45%;">Rincian Temuan Lapangan</th>
                        <th style="width: 15%; text-align: right;">Aksi Perbaikan</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Item 1 -->
                    <tr>
                        <td class="font-weight-bold">1. Jenis Tagihan</td>
                        <td>
                            @if($auditResults['item_1_bill_types']['status'] === 'HEALTHY')
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-warning">Perlu Ditinjau</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($auditResults['item_1_bill_types']['details']['duplicate_groups']))
                                <ul class="mb-0 pl-3 small text-muted">
                                    @foreach($auditResults['item_1_bill_types']['details']['duplicate_groups'] as $dup)
                                        <li>{{ $dup->name }} ({{ $dup->academic_year_name }}): {{ $dup->count }} record duplikat</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-success small"><i class="fas fa-check mr-1"></i> Tidak ada jenis tagihan duplikat</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('bill-type.index') }}" class="btn btn-sm btn-outline-primary">Atur Tagihan</a>
                        </td>
                    </tr>

                    <!-- Item 2 -->
                    <tr>
                        <td class="font-weight-bold">2. Tarif Pembayaran</td>
                        <td>
                            @if($auditResults['item_2_payment_rates']['status'] === 'HEALTHY')
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-warning">Perlu Ditinjau</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">
                                Ditemukan <strong>{{ count($auditResults['item_2_payment_rates']['details']['rates_without_items']) }}</strong> tarif kosong (0 item bulan) dan <strong>{{ count($auditResults['item_2_payment_rates']['details']['rates_without_target']) }}</strong> tarif tanpa kelas/siswa.
                            </div>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('bill-type.index') }}" class="btn btn-sm btn-outline-primary">Lihat Tarif</a>
                        </td>
                    </tr>

                    <!-- Item 3 -->
                    <tr>
                        <td class="font-weight-bold">3. Mass Billing Siswa</td>
                        <td>
                            @if($auditResults['item_3_mass_billing']['status'] === 'HEALTHY')
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-warning">Perlu Ditinjau</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">
                                Ditemukan <strong>{{ count($auditResults['item_3_mass_billing']['details']['students_zero_bills']) }}</strong> santri aktif yang belum memiliki tagihan sama sekali.
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-outline-secondary" disabled>Sync Massal</button>
                        </td>
                    </tr>

                    <!-- Item 4 -->
                    <tr>
                        <td class="font-weight-bold">4. Entri Data Pembayaran</td>
                        <td>
                            @if($auditResults['item_4_transaction_details']['status'] === 'HEALTHY')
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-warning">Auto-Heal Ready</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">
                                Ditemukan <strong>{{ number_format($auditResults['item_4_transaction_details']['details']['null_or_zero_amounts'], 0, ',', '.') }}</strong> detail transaksi lama tanpa nominal (dapat diisi otomatis dari nominal tagihan).
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-outline-success" onclick="triggerRepair(false, {backfill_details: true})">Backfill Nominal</button>
                        </td>
                    </tr>

                    <!-- Item 5 -->
                    <tr>
                        <td class="font-weight-bold">5. Tagihan Hantu (Ghost Bills)</td>
                        <td>
                            @if($auditResults['item_5_ghost_bills']['status'] === 'HEALTHY')
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-danger">Piutang Menggelembung</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">
                                <strong>{{ number_format($auditResults['item_5_ghost_bills']['details']['unpaid_bills_on_inactive_students'], 0, ',', '.') }}</strong> tagihan UNPAID menggantung pada siswa yang sudah lulus/keluar, dan <strong>{{ number_format($auditResults['item_5_ghost_bills']['details']['bills_on_deleted_students'], 0, ',', '.') }}</strong> pada siswa yang sudah terhapus.
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-outline-danger" onclick="triggerRepair(false, {fix_ghost_inactive: true, fix_ghost_deleted: true})">Arsipkan Tagihan</button>
                        </td>
                    </tr>

                    <!-- Item 6 -->
                    <tr>
                        <td class="font-weight-bold">6. Konsistensi Status & Nominal</td>
                        <td>
                            @if($auditResults['item_6_payment_consistency']['status'] === 'HEALTHY')
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-warning">Overpaid Ditemukan</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">
                                Ditemukan <strong>{{ count($auditResults['item_6_payment_consistency']['details']['overpaid_bills']) }}</strong> tagihan Syahriah Pondok overpaid (paid_amount Rp 400.000 > amount Rp 300.000).
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-outline-success" onclick="triggerRepair(false, {fix_overpaid: true})">Selaraskan (Lunas)</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function triggerRepair(isDryRun, specificOptions = {}) {
    const title = isDryRun ? 'Jalankan Simulasi Dry-Run?' : 'Jalankan Perbaikan Konsistensi Live?';
    const text = isDryRun 
        ? 'Simulasi perbaikan akan memvalidasi proses tanpa mengubah data di database (aman 100%).' 
        : 'Perbaikan otomatis akan menyelaraskan data inkonsistensi yang aman secara transaksional.';

    if (!confirm(`${title}\n\n${text}`)) {
        return;
    }

    const payload = {
        _token: '{{ csrf_token() }}',
        dry_run: isDryRun ? 1 : 0,
        fix_overpaid: specificOptions.fix_overpaid !== undefined ? (specificOptions.fix_overpaid ? 1 : 0) : 1,
        fix_ghost_inactive: specificOptions.fix_ghost_inactive !== undefined ? (specificOptions.fix_ghost_inactive ? 1 : 0) : 0,
        fix_ghost_deleted: specificOptions.fix_ghost_deleted !== undefined ? (specificOptions.fix_ghost_deleted ? 1 : 0) : 0,
        relink_rate_items: specificOptions.relink_rate_items !== undefined ? (specificOptions.relink_rate_items ? 1 : 0) : 1,
        backfill_details: specificOptions.backfill_details !== undefined ? (specificOptions.backfill_details ? 1 : 0) : 1,
    };

    fetch('{{ route("bill.repair-consistency") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            let detailSummary = '';
            for (let k in data.results) {
                detailSummary += `\n- ${data.results[k].action}: ${data.results[k].count} data`;
            }
            alert(`${data.message}\n${detailSummary}`);
            if (!isDryRun) {
                window.location.reload();
            }
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan: ' + err.message);
    });
}
</script>
@endpush
