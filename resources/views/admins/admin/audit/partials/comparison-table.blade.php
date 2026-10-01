<!-- Search Box & Bulk Actions Toolbar -->
<form id="form-search-comparison" class="mb-6">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
        <div class="d-flex align-items-center gap-2 flex-grow-1 max-w-400px">
            <div class="position-relative w-100">
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-400"></i>
                <input type="text" id="input-search-term" name="search" class="form-control form-control-solid ps-12" placeholder="Cari Nama atau NIS Siswa..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary fw-bold px-4">
                <i class="fas fa-search me-1"></i> Cari
            </button>
            @if(request()->filled('search'))
                <button type="button" id="btn-reset-search" class="btn btn-light btn-active-light-primary fw-bold px-3">
                    Reset
                </button>
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
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <span class="badge badge-light-primary fs-7 px-3 py-2 fw-bold">
                    Total Discrepancies: {{ $comparison['total_discrepancies'] }} Siswa
                </span>
            </div>
            <button type="submit" class="btn btn-success fw-bold text-white d-none shadow-sm" id="btn-sync-selected" style="background-color: #10B981; border: none;">
                <i class="fas fa-sync-alt me-1 text-white"></i> Sinkronkan <span id="selected-count">0</span> Siswa Terpilih
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle gs-4 gy-4 border-gray-200">
                <thead>
                    <tr class="fw-bolder text-muted bg-light text-center">
                        <th class="w-40px text-center">
                            <div class="form-check form-check-custom form-check-solid justify-content-center">
                                <input class="form-check-input" type="checkbox" id="check-all-students">
                            </div>
                        </th>
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
                            <td class="fw-semibold text-gray-700 fs-7">Rekap Tagihan</td>
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
        <div class="my-2 pagination-container">
            {!! $comparison['discrepancies']->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif
