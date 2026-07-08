@extends('layouts.master', ['title' => 'Pratinjau Import Data'])
@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid px-5" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar mb-5" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-0">
            <div class="page-title d-flex align-items-center flex-wrap me-3">
                <x-text.h1>Pratinjau & Verifikasi Import Data</x-text.h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student.index') }}" class="text-muted text-hover-primary">Daftar Siswa</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Pratinjau Import</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid px-0">
            <!-- Summary Cards Row -->
            <div class="row g-5 mb-5">
                <div class="col-md-3">
                    <div class="premium-card d-flex align-items-center justify-content-between">
                        <div>
                            <x-text.label class="d-block mb-1">Total Baris</x-text.label>
                            <span class="fs-2x fw-bold text-slate-800">{{ $summary['total'] }}</span>
                        </div>
                        <div class="symbol symbol-40px bg-light-primary p-2 rounded-3">
                            <i class="fas fa-list-ol text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card d-flex align-items-center justify-content-between">
                        <div>
                            <x-text.label class="d-block mb-1 text-success">Valid & Ready</x-text.label>
                            <span class="fs-2x fw-bold text-emerald-600">{{ $summary['valid'] }}</span>
                        </div>
                        <div class="symbol symbol-40px bg-light-success p-2 rounded-3" style="background-color: rgba(16, 185, 129, 0.1);">
                            <i class="fas fa-check text-emerald-600 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card d-flex align-items-center justify-content-between">
                        <div>
                            <x-text.label class="d-block mb-1 text-info">Wali Baru</x-text.label>
                            <span class="fs-2x fw-bold text-blue-600">{{ $summary['new_wali'] }}</span>
                        </div>
                        <div class="symbol symbol-40px bg-light-info p-2 rounded-3" style="background-color: rgba(37, 99, 235, 0.1);">
                            <i class="fas fa-user-plus text-blue-600 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card d-flex align-items-center justify-content-between">
                        <div>
                            <x-text.label class="d-block mb-1 text-danger">Error</x-text.label>
                            <span class="fs-2x fw-bold text-danger">{{ $summary['errors'] }}</span>
                        </div>
                        <div class="symbol symbol-40px bg-light-danger p-2 rounded-3" style="background-color: rgba(220, 38, 38, 0.1);">
                            <i class="fas fa-exclamation-triangle text-danger fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="premium-card mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <x-text.h2>Detail Baris Data Excel</x-text.h2>
                    <x-text.caption>Menampilkan data siswa dan relasi wali yang diproses</x-text.caption>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle border rounded gy-4 gs-7">
                        <thead>
                            <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                <th width="5%">Baris</th>
                                <th width="15%">Siswa (NIS)</th>
                                <th width="20%">Nama Siswa</th>
                                <th width="10%">Kelas</th>
                                <th width="25%">Wali (No WA)</th>
                                <th width="10%">Status Wali</th>
                                <th width="15%">Hasil Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($previewData as $row)
                                <tr>
                                    <td>{{ $row['row_number'] }}</td>
                                    <td>
                                        <span class="fw-bold">{{ $row['nis'] }}</span>
                                        @if ($row['gender'] == 'L' || $row['gender'] == 'l')
                                            <span class="badge badge-light-primary ms-1">L</span>
                                        @elseif ($row['gender'] == 'P' || $row['gender'] == 'p')
                                            <span class="badge badge-light-danger ms-1">P</span>
                                        @endif
                                    </td>
                                    <td><x-text.body class="fw-bold text-slate-800">{{ $row['name'] }}</x-text.body></td>
                                    <td><span class="badge badge-light-info">{{ $row['class'] }}</span></td>
                                    <td>
                                        <div><x-text.body class="fw-bold mb-0">{{ $row['wali_name'] }}</x-text.body></div>
                                        <div class="text-muted fs-7"><i class="fab fa-whatsapp me-1 text-success"></i>{{ $row['wali_phone'] }}</div>
                                    </td>
                                    <td>
                                        @if ($row['wali_status'] == 'Terdaftar')
                                            <span class="badge badge-light-success text-emerald-600" style="background-color: rgba(16, 185, 129, 0.1);">Wali Aktif</span>
                                        @elseif ($row['wali_status'] == 'Baru')
                                            <span class="badge badge-light-primary text-blue-600" style="background-color: rgba(37, 99, 235, 0.1);">Auto-Create</span>
                                        @else
                                            <span class="badge badge-light-warning">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['status'] == 'READY')
                                            <span class="badge badge-light-success text-emerald-600" style="background-color: rgba(16, 185, 129, 0.1);"><i class="fas fa-check-circle me-1 text-emerald-600"></i> Valid</span>
                                        @else
                                            <span class="badge badge-light-danger text-danger" style="background-color: rgba(220, 38, 38, 0.1);"><i class="fas fa-times-circle me-1 text-danger"></i> Error</span>
                                        @endif

                                        @if (count($row['errors']) > 0)
                                            <div class="text-danger mt-1 fs-7">
                                                @foreach ($row['errors'] as $err)
                                                    <div>• {{ $err }}</div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (count($row['warnings']) > 0)
                                            <div class="text-warning mt-1 fs-7">
                                                @foreach ($row['warnings'] as $wrn)
                                                    <div>• {{ $wrn }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-between align-items-center mt-6 pt-6 border-top">
                    <div>
                        @if ($summary['errors'] > 0)
                            <div class="alert alert-light-warning d-flex align-items-center p-3 rounded-[12px] mb-0" style="border: 1px solid rgba(245, 158, 11, 0.3);">
                                <i class="fas fa-exclamation-triangle text-warning me-3"></i>
                                <span class="fs-7 text-slate-700">Terdapat <strong>{{ $summary['errors'] }} baris bermasalah</strong>. Baris bermasalah akan dilewati secara otomatis saat import.</span>
                            </div>
                        @else
                            <div class="alert alert-light-success d-flex align-items-center p-3 rounded-[12px] mb-0" style="background-color: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                                <i class="fas fa-check-circle text-emerald-600 me-3"></i>
                                <span class="fs-7 text-slate-700">Semua baris data valid dan siap di-import ke database.</span>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-3">
                        <a href="{{ route('student.index') }}" class="btn btn-secondary btn-sm rounded-[12px]">Batal</a>
                        <form action="{{ route('student.import-confirm') }}" method="POST">
                            @csrf
                            <input type="hidden" name="temp_file" value="{{ $tempFile }}">
                            <button type="submit" class="btn btn-primary btn-sm rounded-[12px]" {{ $summary['valid'] == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-cloud-upload-alt me-1"></i> Konfirmasi & Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
@endsection
