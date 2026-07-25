@extends('layouts.master', ['title' => 'Modul Admin Lapor Pak'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!-- Toolbar -->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Modul Kelola Lapor Pak</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">Backoffice</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-dark">Lapor Pak</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('public.laporpak.index') }}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Form Publik
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center p-5 mb-7 rounded-3">
                    <i class="bi bi-check-circle-fill fs-2x text-success me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success font-bold">Berhasil!</h4>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- ROW 1: PENGATURAN PERIODE AKSES FORM LAPOR PAK -->
            <div class="card mb-7 shadow-sm border border-slate-200">
                <div class="card-header bg-light-primary border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-4 text-dark">Pengaturan Periode Akses Form Publik</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Atur status aktif & jendela periode buka/tutup pengaduan publik wali santri</span>
                    </h3>
                    <div class="card-toolbar">
                        @if($setting->is_active)
                            <span class="badge badge-light-success fs-7 fw-bolder px-3 py-2">Status: AKSES DIBUKA</span>
                        @else
                            <span class="badge badge-light-danger fs-7 fw-bolder px-3 py-2">Status: DITUTUP</span>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.laporpak.setting') }}" method="POST">
                        @csrf
                        <div class="row g-5">
                            <div class="col-md-3">
                                <label class="form-label fw-bolder text-dark">Sakelar Status Form</label>
                                <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                    <input class="form-check-input h-30px w-50px" type="checkbox" name="is_active" value="1" id="is_active_switch" {{ $setting->is_active ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold text-gray-700 ms-3" for="is_active_switch">
                                        Aktifkan Akses Form Publik
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bolder text-dark">Waktu Buka (Mulai)</label>
                                <input type="datetime-local" name="start_datetime" class="form-control form-control-solid" value="{{ $setting->start_datetime ? $setting->start_datetime->format('Y-m-d\TH:i') : '' }}">
                                <span class="text-muted fs-8">Kosongkan jika dibuka tanpa batasan awal</span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bolder text-dark">Waktu Tutup (Selesai)</label>
                                <input type="datetime-local" name="end_datetime" class="form-control form-control-solid" value="{{ $setting->end_datetime ? $setting->end_datetime->format('Y-m-d\TH:i') : '' }}">
                                <span class="text-muted fs-8">Kosongkan jika dibuka tanpa batasan akhir</span>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bolder">
                                    <i class="bi bi-save me-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bolder text-dark">Pesan Penutupan Form (Tampil saat form ditutup/di luar periode)</label>
                                <textarea name="closed_message" rows="2" class="form-control form-control-solid" placeholder="Contoh: Form pengaduan Lapor Pak saat ini belum dibuka. Periode berikutnya dibuka pada tanggal 1 s/d 5 tiap bulan.">{{ $setting->closed_message }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ROW 2: SUMMARY CARDS & STATISTIK -->
            <div class="row g-5 mb-7">
                <!-- Card 1: Total Pengaduan -->
                <div class="col-md-4">
                    <div class="card card-flush h-100 shadow-sm border border-slate-200">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bolder text-dark me-2 lh-1 font-mono">{{ $stats['total'] }}</span>
                                <span class="text-gray-400 pt-1 fw-bold fs-7">Total Pengaduan Masuk</span>
                            </div>
                        </div>
                        <div class="card-body pt-0 d-flex align-items-end">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <span class="badge badge-light-danger fw-bolder px-3 py-2">
                                    <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> {{ $stats['kendalaCount'] }} Pending Kendala
                                </span>
                                <span class="badge badge-light-success fw-bolder px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1 text-success"></i> {{ $stats['teratasiCount'] }} Teratasi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Status Progress Breakdown -->
                <div class="col-md-4">
                    <div class="card card-flush h-100 shadow-sm border border-slate-200">
                        <div class="card-header pt-5">
                            <h3 class="card-title fw-bolder text-dark fs-5">Rasio Penanganan</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between mb-2 fs-7 fw-bold">
                                    <span class="text-danger">Kendala ({{ $stats['kendalaCount'] }})</span>
                                    <span class="text-success">Teratasi ({{ $stats['teratasiCount'] }})</span>
                                </div>
                                <div class="progress h-8px bg-light-danger rounded">
                                    @php
                                        $teratasiPercent = $stats['total'] > 0 ? round(($stats['teratasiCount'] / $stats['total']) * 100) : 0;
                                    @endphp
                                    <div class="progress-bar bg-success rounded" role="progressbar" style="width: {{ $teratasiPercent }}%" aria-valuenow="{{ $teratasiPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="text-muted fs-8 mt-2 italic text-center">{{ $teratasiPercent }}% Keluhan telah berhasil diselesaikan</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Breakdown Jenis Kendala -->
                <div class="col-md-4">
                    <div class="card card-flush h-100 shadow-sm border border-slate-200">
                        <div class="card-header pt-4">
                            <h3 class="card-title fw-bolder text-dark fs-6">Distribusi Kategori Kendala</h3>
                        </div>
                        <div class="card-body pt-0 max-h-125px overflow-auto">
                            @foreach($stats['catBreakdown'] as $cat)
                                <div class="d-flex justify-content-between align-items-center mb-1 fs-8">
                                    <span class="text-gray-600 fw-bold">{{ $cat['category'] }}</span>
                                    <span class="badge badge-light-primary fw-bolder">{{ $cat['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 3: TABEL KELOLA DATA PENGADUAN -->
            <div class="card shadow-sm border border-slate-200">
                <div class="card-header border-0 pt-6">
                    <div class="card-title flex-column">
                        <h3 class="fw-bolder fs-3 text-dark">Daftar Pengaduan Lapor Pak</h3>
                        <span class="text-muted fs-7">Kelola & tindak lanjuti laporan pengaduan wali santri</span>
                    </div>
                    
                    <!-- Filter Toolbar -->
                    <div class="card-toolbar gap-3 flex-wrap">
                        <!-- Filter Form -->
                        <form action="{{ route('admin.laporpak.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
                            <!-- Status Filter -->
                            <select name="status" class="form-select form-select-sm form-select-solid w-150px" onchange="this.form.submit()">
                                <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="Kendala" {{ $statusFilter == 'Kendala' ? 'selected' : '' }}>Kendala</option>
                                <option value="Teratasi" {{ $statusFilter == 'Teratasi' ? 'selected' : '' }}>Teratasi</option>
                            </select>

                            <!-- Category Filter -->
                            <select name="category" class="form-select form-select-sm form-select-solid w-180px" onchange="this.form.submit()">
                                <option value="all" {{ $categoryFilter == 'all' ? 'selected' : '' }}>Semua Kendala</option>
                                @foreach(\App\Http\Controllers\Admin\LaporPakAdminController::KENDALA_OPTIONS as $opt)
                                    <option value="{{ $opt }}" {{ $categoryFilter == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>

                            <!-- Search Input -->
                            <div class="d-flex align-items-center position-relative">
                                <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm form-control-solid w-200px ps-9" placeholder="Cari Wali/Siswa..." />
                                <i class="bi bi-search position-absolute ms-3 text-gray-400"></i>
                            </div>

                            <button type="submit" class="btn btn-sm btn-light-primary">Filter</button>
                            @if($statusFilter != 'all' || $categoryFilter != 'all' || !empty($search))
                                <a href="{{ route('admin.laporpak.index') }}" class="btn btn-sm btn-light-danger">Reset</a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-3">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-8 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama Wali</th>
                                    <th>Nama Siswa</th>
                                    <th>Jenis Kendala</th>
                                    <th>Waktu Lapor</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end min-w-180px">Aksi Kelola</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold">
                                @forelse($reports as $index => $rep)
                                    @php
                                        // Formatter nomor WhatsApp ke format internasional 628xxx untuk direct chat link
                                        $phoneFormatted = preg_replace('/[^0-9]/', '', $rep->parent_phone);
                                        if (str_starts_with($phoneFormatted, '0')) {
                                            $phoneFormatted = '62' . substr($phoneFormatted, 1);
                                        }
                                        $waMessage = rawurlencode("Assalamu'alaikum Bapak/Ibu {$rep->parent_name}, menindaklanjuti pengaduan Lapor Pak untuk santri {$rep->student_name} mengenai [{$rep->kendala}].");
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bolder text-gray-400">
                                            {{ $reports->firstItem() + $index }}
                                        </td>
                                        <td>
                                            <div class="fw-bolder text-gray-800 fs-7">{{ $rep->parent_name }}</div>
                                            <div class="text-muted font-mono fs-8">{{ $rep->parent_phone }}</div>
                                            @if($rep->is_parent_updated)
                                                <span class="badge badge-light-info fs-9 py-0.5 px-1.5 font-bold">Diupdate Wali</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bolder text-gray-800 fs-7">{{ $rep->student_name }}</div>
                                            <div class="text-muted fs-8">{{ $rep->school }} — {{ $rep->class_name }}</div>
                                        </td>
                                        <td class="max-w-250px">
                                            <span class="badge badge-light-primary fw-bold mb-1">{{ $rep->kendala }}</span>
                                            @if($rep->keterangan)
                                                <div class="text-gray-600 fs-8 leading-tight line-clamp-2 bg-light p-2 rounded">
                                                    {{ $rep->keterangan }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="font-mono text-gray-500 fs-8 whitespace-nowrap">
                                            {{ $rep->created_at ? $rep->created_at->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($rep->status === 'Kendala')
                                                <span class="badge badge-light-danger fs-8 fw-bolder px-3 py-1.5">
                                                    <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Kendala
                                                </span>
                                            @else
                                                <span class="badge badge-light-success fs-8 fw-bolder px-3 py-1.5">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i> Teratasi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                <!-- Direct WhatsApp Button -->
                                                @if(!empty($phoneFormatted))
                                                    <a href="https://wa.me/{{ $phoneFormatted }}?text={{ $waMessage }}" target="_blank" class="btn btn-sm btn-light-success px-2.5 py-1.5 text-success" title="Chat WA Wali">
                                                        <i class="bi bi-whatsapp"></i> WA
                                                    </a>
                                                @endif

                                                <!-- Toggle Status Button -->
                                                <form action="{{ route('admin.laporpak.toggle-status', $rep->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $rep->status === 'Kendala' ? 'btn-success' : 'btn-light-warning' }} px-3 py-1.5 fw-bold fs-8">
                                                        {{ $rep->status === 'Kendala' ? 'Selesaikan' : 'Tandai Kendala' }}
                                                    </button>
                                                </form>

                                                <!-- Delete Button -->
                                                <form action="{{ route('admin.laporpak.destroy', $rep->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan pengaduan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="Hapus Laporan">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colSpan="7" class="text-center py-10 text-muted">
                                            <i class="bi bi-inbox fs-2x d-block mb-2"></i>
                                            Belum ada laporan pengaduan yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end pt-4">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
