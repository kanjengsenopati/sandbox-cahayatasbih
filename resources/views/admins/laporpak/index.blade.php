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

            <!-- HEADER NATIVE BOOTSTRAP 5 / METRONIC TABS (PROPER SPLIT) -->
            <div class="mb-6 border-bottom border-gray-200">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-bolder" id="laporPakAdminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link py-3 px-5 border-bottom-3 me-2 active" 
                           id="tab-daftar-pengaduan" 
                           data-bs-toggle="tab" 
                           href="#kt_tab_pane_daftar_pengaduan" 
                           role="tab" 
                           aria-controls="kt_tab_pane_daftar_pengaduan" 
                           aria-selected="true">
                            <i class="bi bi-card-list me-2 fs-4"></i> Tab 1: Daftar Pengaduan
                            <span class="badge badge-light-primary ms-2">{{ $stats['total'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link py-3 px-5 border-bottom-3" 
                           id="tab-pengaturan-ringkasan" 
                           data-bs-toggle="tab" 
                           href="#kt_tab_pane_pengaturan_ringkasan" 
                           role="tab" 
                           aria-controls="kt_tab_pane_pengaturan_ringkasan" 
                           aria-selected="false">
                            <i class="bi bi-gear-fill me-2 fs-4"></i> Tab 2: Pengaturan & Ringkasan
                        </a>
                    </li>
                </ul>
            </div>

            <!-- CONTAINER TAB CONTENT BOOTSTRAP 5 -->
            <div class="tab-content" id="laporPakTabContent">

                <!-- TAB 1: DAFTAR PENGADUAN LAPOR PAK -->
                <div class="tab-pane fade show active" id="kt_tab_pane_daftar_pengaduan" role="tabpanel" aria-labelledby="tab-daftar-pengaduan">
                    <div class="card shadow-sm border border-slate-200">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title flex-column">
                                <h3 class="fw-bolder fs-3 text-dark">Daftar Pengaduan Lapor Pak</h3>
                                <span class="text-muted fs-7">Kelola, update status tindak lanjut, & catatan untuk wali santri</span>
                            </div>
                            
                            <!-- Filter Toolbar 1 Baris Sejajar -->
                            <div class="card-toolbar w-100 mt-4 mt-md-0">
                                <form action="{{ route('admin.laporpak.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap w-100 justify-content-md-end">
                                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? 'created_at' }}">
                                    <input type="hidden" name="sort_dir" value="{{ $sortDir ?? 'desc' }}">

                                    <!-- 1. Status Filter -->
                                    <select name="status" class="form-select form-select-sm form-select-solid w-150px" onchange="this.form.submit()">
                                        <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>Semua Status</option>
                                        @foreach($milestones as $ms)
                                            <option value="{{ $ms }}" {{ $statusFilter == $ms ? 'selected' : '' }}>{{ $ms }}</option>
                                        @endforeach
                                    </select>

                                    <!-- 2. Category Filter -->
                                    <select name="category" class="form-select form-select-sm form-select-solid w-170px" onchange="this.form.submit()">
                                        <option value="all" {{ $categoryFilter == 'all' ? 'selected' : '' }}>Semua Kendala</option>
                                        @foreach(\App\Http\Controllers\Admin\LaporPakAdminController::KENDALA_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ $categoryFilter == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>

                                    <!-- 3. Search Input -->
                                    <div class="d-flex align-items-center position-relative w-200px">
                                        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm form-control-solid ps-9" placeholder="Cari Wali/Siswa..." />
                                        <i class="bi bi-search position-absolute ms-3 text-gray-400"></i>
                                    </div>

                                    <!-- 4. Action Buttons -->
                                    <button type="submit" class="btn btn-sm btn-light-primary text-nowrap">Filter</button>
                                    @if($statusFilter != 'all' || $categoryFilter != 'all' || !empty($search) || ($sortBy ?? 'created_at') != 'created_at' || ($sortDir ?? 'desc') != 'desc')
                                        <a href="{{ route('admin.laporpak.index') }}" class="btn btn-sm btn-light-danger text-nowrap">Reset</a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-7 gy-3">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-8 text-uppercase gs-0">
                                            <th style="width: 4%">No</th>

                                            <!-- SORTABLE HEADER: NAMA WALI -->
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'parent_name', 'sort_dir' => ($sortBy == 'parent_name' && $sortDir == 'asc') ? 'desc' : 'asc']) }}" 
                                                   class="text-gray-400 text-hover-primary text-uppercase fw-bolder fs-8 d-inline-flex align-items-center">
                                                    Nama Wali
                                                    @if(($sortBy ?? '') == 'parent_name')
                                                        <i class="bi bi-arrow-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                                                    @else
                                                        <i class="bi bi-arrow-down-up text-gray-400 ms-1 fs-9 opacity-50"></i>
                                                    @endif
                                                </a>
                                            </th>

                                            <!-- SORTABLE HEADER: NAMA SISWA -->
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'student_name', 'sort_dir' => ($sortBy == 'student_name' && $sortDir == 'asc') ? 'desc' : 'asc']) }}" 
                                                   class="text-gray-400 text-hover-primary text-uppercase fw-bolder fs-8 d-inline-flex align-items-center">
                                                    Nama Siswa
                                                    @if(($sortBy ?? '') == 'student_name')
                                                        <i class="bi bi-arrow-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                                                    @else
                                                        <i class="bi bi-arrow-down-up text-gray-400 ms-1 fs-9 opacity-50"></i>
                                                    @endif
                                                </a>
                                            </th>

                                            <!-- SORTABLE HEADER: JENIS KENDALA -->
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'kendala', 'sort_dir' => ($sortBy == 'kendala' && $sortDir == 'asc') ? 'desc' : 'asc']) }}" 
                                                   class="text-gray-400 text-hover-primary text-uppercase fw-bolder fs-8 d-inline-flex align-items-center">
                                                    Jenis Kendala
                                                    @if(($sortBy ?? '') == 'kendala')
                                                        <i class="bi bi-arrow-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                                                    @else
                                                        <i class="bi bi-arrow-down-up text-gray-400 ms-1 fs-9 opacity-50"></i>
                                                    @endif
                                                </a>
                                            </th>

                                            <!-- SORTABLE HEADER: WAKTU LAPOR -->
                                            <th>
                                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_dir' => ($sortBy == 'created_at' && $sortDir == 'asc') ? 'desc' : 'asc']) }}" 
                                                   class="text-gray-400 text-hover-primary text-uppercase fw-bolder fs-8 d-inline-flex align-items-center">
                                                    Waktu Lapor
                                                    @if(($sortBy ?? 'created_at') == 'created_at')
                                                        <i class="bi bi-arrow-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                                                    @else
                                                        <i class="bi bi-arrow-down-up text-gray-400 ms-1 fs-9 opacity-50"></i>
                                                    @endif
                                                </a>
                                            </th>

                                            <th class="text-center min-w-180px">Status & Catatan Petugas</th>
                                            <th class="text-end min-w-120px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold">
                                        @forelse($reports as $index => $rep)
                                            @php
                                                $phoneFormatted = preg_replace('/[^0-9]/', '', $rep->parent_phone);
                                                if (str_starts_with($phoneFormatted, '0')) {
                                                    $phoneFormatted = '62' . substr($phoneFormatted, 1);
                                                }
                                                $waMessage = rawurlencode("Assalamu'alaikum Bapak/Ibu {$rep->parent_name}, menindaklanjuti pengaduan Lapor Pak untuk santri {$rep->student_name} mengenai [{$rep->kendala}]. Status saat ini: {$rep->status}.");
                                                
                                                $statusBgClass = match($rep->status) {
                                                    'Selesai', 'Teratasi' => 'bg-light-success text-success border-success',
                                                    'Sedang Ditangani' => 'bg-light-primary text-primary border-primary',
                                                    'Diterima' => 'bg-light-warning text-warning border-warning',
                                                    default => 'bg-light-secondary text-gray-700',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="text-center fw-bolder text-gray-400">
                                                    {{ ($reports->firstItem() ?? 1) + $index }}
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
                                                    <!-- FORM UPDATE STATUS & CATATAN HIGH CONTRAST -->
                                                    <form action="{{ route('admin.laporpak.update-status', $rep->id) }}" method="POST" class="d-inline-block w-100">
                                                        @csrf
                                                        <select 
                                                            name="status" 
                                                            onchange="this.form.submit()" 
                                                            class="form-select form-select-sm fw-bolder fs-8 py-1.5 px-2 rounded-2 shadow-xs border-0 {{ $statusBgClass }}"
                                                        >
                                                            <option value="Laporan Masuk" {{ $rep->status == 'Laporan Masuk' || $rep->status == 'Kendala' ? 'selected' : '' }} style="color: #1e293b; background-color: #ffffff; font-weight: 600;">1. Laporan Masuk</option>
                                                            <option value="Diterima" {{ $rep->status == 'Diterima' ? 'selected' : '' }} style="color: #1e293b; background-color: #ffffff; font-weight: 600;">2. Diterima</option>
                                                            <option value="Sedang Ditangani" {{ $rep->status == 'Sedang Ditangani' ? 'selected' : '' }} style="color: #1e293b; background-color: #ffffff; font-weight: 600;">3. Sedang Ditangani</option>
                                                            <option value="Selesai" {{ $rep->status == 'Selesai' || $rep->status == 'Teratasi' ? 'selected' : '' }} style="color: #1e293b; background-color: #ffffff; font-weight: 600;">4. Selesai</option>
                                                        </select>

                                                        <!-- TOMBOL BUKA MODAL CATATAN / INPUT CATATAN RINGKAS -->
                                                        <div class="mt-1">
                                                            <button type="button" class="btn btn-link btn-color-gray-500 btn-active-color-primary p-0 fs-8 fw-bold" data-bs-toggle="modal" data-bs-target="#noteModal{{ $rep->id }}">
                                                                <i class="bi bi-pencil-square me-1"></i> {{ $rep->admin_note ? 'Edit Catatan' : '+ Tambah Catatan' }}
                                                            </button>
                                                        </div>
                                                    </form>

                                                    <!-- MODAL EDIT CATATAN PETUGAS -->
                                                    <div class="modal fade" id="noteModal{{ $rep->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form action="{{ route('admin.laporpak.update-status', $rep->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title fw-bolder">Update Status & Catatan Petugas</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body text-start">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Status Tindak Lanjut</label>
                                                                            <select name="status" class="form-select form-select-solid">
                                                                                <option value="Laporan Masuk" {{ $rep->status == 'Laporan Masuk' ? 'selected' : '' }}>1. Laporan Masuk</option>
                                                                                <option value="Diterima" {{ $rep->status == 'Diterima' ? 'selected' : '' }}>2. Diterima</option>
                                                                                <option value="Sedang Ditangani" {{ $rep->status == 'Sedang Ditangani' ? 'selected' : '' }}>3. Sedang Ditangani</option>
                                                                                <option value="Selesai" {{ $rep->status == 'Selesai' ? 'selected' : '' }}>4. Selesai</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Catatan Petugas (Tampil di UI Publik Wali)</label>
                                                                            <textarea name="admin_note" rows="3" class="form-control form-control-solid" placeholder="Contoh: Laporan sedang diverifikasi oleh Tim IT Pesantren. Estimasi selesai hari ini.">{{ $rep->admin_note }}</textarea>
                                                                            <span class="text-muted fs-8">Catatan ini akan langsung terlihat oleh wali santri di menu Pantau Tindak Lanjut.</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                                        <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                                        @if(!empty($phoneFormatted))
                                                            <a href="https://wa.me/{{ $phoneFormatted }}?text={{ $waMessage }}" target="_blank" class="btn btn-sm btn-light-success px-2.5 py-1.5 text-success" title="Chat WA Wali">
                                                                <i class="bi bi-whatsapp"></i> WA
                                                            </a>
                                                        @endif

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

                <!-- TAB 2: PENGATURAN PERIODE AKSES & RINGKASAN STATISTIK -->
                <div class="tab-pane fade" id="kt_tab_pane_pengaturan_ringkasan" role="tabpanel" aria-labelledby="tab-pengaturan-ringkasan">
                    <!-- CARD 1: PENGATURAN PERIODE AKSES FORM LAPOR PAK -->
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

                    <!-- CARD 2: SUMMARY CARDS & STATISTIK 4 MILESTONE -->
                    <div class="row g-5 mb-7">
                        <!-- Card 1: Total Pengaduan -->
                        <div class="col-md-3">
                            <div class="card card-flush h-100 shadow-sm border border-slate-200">
                                <div class="card-header pt-4">
                                    <div class="card-title d-flex flex-column">
                                        <span class="fs-2hx fw-bolder text-dark me-2 lh-1 font-mono">{{ $stats['total'] }}</span>
                                        <span class="text-gray-400 pt-1 fw-bold fs-7">Total Pengaduan</span>
                                    </div>
                                </div>
                                <div class="card-body pt-0 d-flex align-items-end">
                                    <span class="badge badge-light-primary fw-bolder px-3 py-1.5 fs-8">
                                        <i class="bi bi-inbox me-1"></i> {{ $stats['masukCount'] }} Laporan Masuk
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Status Progress 4 Milestone Breakdown -->
                        <div class="col-md-5">
                            <div class="card card-flush h-100 shadow-sm border border-slate-200">
                                <div class="card-header pt-4">
                                    <h3 class="card-title fw-bolder text-dark fs-6">Milestone Progress Status</h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-2 text-center">
                                        <div class="col-3">
                                            <div class="bg-light-secondary rounded p-2 border border-dashed border-gray-300">
                                                <span class="fs-6 fw-bolder text-gray-700 font-mono block">{{ $stats['masukCount'] }}</span>
                                                <span class="text-gray-500 fs-9 font-bold block">1. Masuk</span>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-light-warning rounded p-2 border border-dashed border-warning">
                                                <span class="fs-6 fw-bolder text-warning font-mono block">{{ $stats['diterimaCount'] }}</span>
                                                <span class="text-warning fs-9 font-bold block">2. Diterima</span>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-light-primary rounded p-2 border border-dashed border-primary">
                                                <span class="fs-6 fw-bolder text-primary font-mono block">{{ $stats['ditanganiCount'] }}</span>
                                                <span class="text-primary fs-9 font-bold block">3. Ditangani</span>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-light-success rounded p-2 border border-dashed border-success">
                                                <span class="fs-6 fw-bolder text-success font-mono block">{{ $stats['selesaiCount'] }}</span>
                                                <span class="text-success fs-9 font-bold block">4. Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Breakdown Kategori -->
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
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
