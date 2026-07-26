@extends('layouts.master', ['title' => 'Sync Saldo & Riwayat'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    
    <!-- Toolbar -->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <x-text.h1>Sinkronisasi Saldo & Riwayat (Preview)</x-text.h1>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm btn-light-primary fw-bolder rounded-[24px]" id="btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </button>
                <form action="{{ route('admin.audit.advanced-sync.execute') }}" method="POST" id="form-execute-sync" class="d-none">
                    @csrf
                    <input type="hidden" name="preview_id" id="execute_preview_id">
                    <button type="button" class="btn btn-sm btn-primary fw-bolder rounded-[24px]" id="btn-execute-sync">
                        <i class="fas fa-link me-1"></i> Gabungkan Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Post -->
    <div class="post d-flex flex-column-fluid px-5" id="kt_post">
        <div id="kt_content_container" class="container-xxl">

            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center p-5 mb-6 rounded-[24px]">
                    <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Sukses</h4>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center p-5 mb-6 rounded-[24px]">
                    <i class="fas fa-exclamation-triangle fs-2hx text-danger me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Gagal</h4>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- FILTER CARD -->
            <div class="card mb-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" id="filter-card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <x-text.h2>Filter Sinkronisasi</x-text.h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    <form id="form-preview" onsubmit="event.preventDefault(); loadPreview();">
                        <div class="row g-5">
                            <div class="col-md-3">
                                <x-text.label>Tanggal Mulai</x-text.label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-solid rounded-[12px]" required>
                            </div>
                            <div class="col-md-3">
                                <x-text.label>Tanggal Selesai</x-text.label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-solid rounded-[12px]" required>
                            </div>
                            <div class="col-md-3">
                                <x-text.label>Sekolah / UPT</x-text.label>
                                <select name="school_id" id="school_id" class="form-select form-select-solid rounded-[12px]" data-control="select2" data-placeholder="Pilih Sekolah (Opsional)">
                                    <option value=""></option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <x-text.label>Kelas</x-text.label>
                                <select name="classroom_id" id="classroom_id" class="form-select form-select-solid rounded-[12px]" data-control="select2" data-placeholder="Pilih Kelas (Opsional)">
                                    <option value=""></option>
                                    @foreach($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->school->name ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <x-text.label>Cari Siswa (NIS/Nama)</x-text.label>
                                <input type="text" name="search" id="search" class="form-control form-control-solid rounded-[12px]" placeholder="Masukkan NIS atau Nama (Opsional)">
                            </div>
                            <div class="col-md-6 d-flex align-items-end justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-[24px]" id="btn-generate-preview">
                                    <i class="fas fa-search me-1"></i> Buat Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PREVIEW CARD -->
            <div class="card rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0 d-none" id="preview-card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title flex-column">
                        <x-text.h2>Hasil Pratinjau (Preview)</x-text.h2>
                        <x-text.body>Data di bawah belum disimpan ke database lokal. Periksa dengan teliti sebelum menekan tombol Gabungkan Data.</x-text.body>
                    </div>
                </div>
                <div class="card-body py-4 pb-8">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="table-preview">
                            <thead>
                                <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                    <th>Siswa</th>
                                    <th>Kelas/UPT</th>
                                    <th>Saldo Lokal Awal</th>
                                    <th>Riwayat Tertunda</th>
                                    <th>Saldo Master</th>
                                    <th>Estimasi Saldo Akhir</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold">
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    var previewTable = null;

    function loadPreview() {
        var btn = $('#btn-generate-preview');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');
        
        var filters = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            school_id: $('#school_id').val(),
            classroom_id: $('#classroom_id').val(),
            search: $('#search').val(),
            _token: '{{ csrf_token() }}'
        };

        if (previewTable) {
            previewTable.destroy();
        }

        $('#preview-card').removeClass('d-none');
        $('#form-execute-sync').addClass('d-none'); // Sembunyikan tombol eksekusi saat loading
        
        previewTable = $('#table-preview').DataTable({
            processing: true,
            serverSide: false, // We load all data in one request since it's a preview
            ajax: {
                url: "{{ route('admin.audit.advanced-sync.preview') }}",
                type: 'POST',
                data: filters,
                dataSrc: function ( json ) {
                    btn.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Buat Preview');
                    
                    if(json.preview_id) {
                        $('#execute_preview_id').val(json.preview_id);
                        $('#form-execute-sync').removeClass('d-none'); // Tampilkan tombol eksekusi
                    } else {
                        Swal.fire({
                            text: "Tidak ada data riwayat baru yang ditemukan untuk filter tersebut.",
                            icon: "info",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, Mengerti",
                            customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
                        });
                    }

                    return json.data;
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Buat Preview');
                    Swal.fire({
                        text: "Terjadi kesalahan saat memproses data.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti",
                        customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
                    });
                }
            },
            columns: [
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<div class="d-flex flex-column">' +
                               '<span class="text-gray-800 font-medium text-[14px]">' + row.name + '</span>' +
                               '<span class="text-slate-400 text-[12px] italic">' + row.nis + '</span>' +
                               '</div>';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<div class="d-flex flex-column">' +
                               '<span class="text-gray-800 text-[14px]">' + row.classroom + '</span>' +
                               '<span class="text-slate-400 text-[12px]">' + row.school + '</span>' +
                               '</div>';
                    }
                },
                {
                    data: 'current_local_saldo',
                    render: function(data, type, row) {
                        return '<span class="text-slate-600 font-medium">Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>';
                    }
                },
                {
                    data: 'new_histories_count',
                    render: function(data, type, row) {
                        let totalIn = new Intl.NumberFormat('id-ID').format(row.total_in_added);
                        let totalOut = new Intl.NumberFormat('id-ID').format(row.total_out_added);
                        return '<div class="d-flex flex-column gap-1">' +
                               '<span class="badge badge-light-primary fw-bolder">' + data + ' Transaksi</span>' +
                               '<span class="text-[12px] text-emerald-600">+ Rp ' + totalIn + '</span>' +
                               '<span class="text-[12px] text-red-600">- Rp ' + totalOut + '</span>' +
                               '</div>';
                    }
                },
                {
                    data: 'master_saldo',
                    render: function(data, type, row) {
                        return '<span class="text-slate-600 font-medium">Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>';
                    }
                },
                {
                    data: 'simulated_saldo',
                    render: function(data, type, row) {
                        return '<span class="text-[18px] font-bold text-emerald-600">Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>';
                    }
                },
                {
                    data: 'conflict_status',
                    render: function(data, type, row) {
                        if (data === 'OK') {
                            return '<span class="badge badge-light-success px-3 py-2">OK</span>';
                        }
                        return '<span class="badge badge-light-warning px-3 py-2">CONFLICT MERGED</span>';
                    }
                }
            ]
        });
    }

    // Execute Sync Action
    $('#btn-execute-sync').on('click', function(e) {
        e.preventDefault();
        var form = $('#form-execute-sync');
        
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            html: 'Sistem akan menggabungkan <b>semua transaksi dari master</b> dan menghitung ulang saldo lokal. Aksi ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Gabungkan Data!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: "btn btn-primary rounded-[24px]",
                cancelButton: "btn btn-light rounded-[24px]"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading screen
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu, proses sinkronisasi sedang berjalan.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                form.submit();
            }
        });
    });

    $('#btn-back').on('click', function() {
        window.location.href = "{{ route('admin.audit.sync') }}";
    });
</script>
@endsection
