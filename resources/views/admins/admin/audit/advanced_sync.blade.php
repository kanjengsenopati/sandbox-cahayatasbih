@extends('layouts.master', ['title' => 'Sync Saldo dari Aplikasi Lama'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    
    <!-- Toolbar -->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <x-text.h1>Sync Saldo dari Aplikasi Lama</x-text.h1>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm btn-light-primary fw-bolder rounded-[24px]" id="btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </button>
                <form action="{{ route('admin.audit.advanced-sync.execute') }}" method="POST" id="form-execute-sync" class="d-none">
                    @csrf
                    <input type="hidden" name="preview_id" id="execute_preview_id">
                    <div id="selected-students-container"></div>
                    <button type="button" class="btn btn-sm btn-primary fw-bolder rounded-[24px]" id="btn-execute-sync">
                        <i class="fas fa-sync me-1"></i> Sync Saldo <span id="selected-count" class="badge badge-circle badge-white ms-2 d-none text-primary">0</span>
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
                                <x-text.label>Periode Filter</x-text.label>
                                <select id="period_filter" class="form-select form-select-solid rounded-[12px]" data-control="select2" data-hide-search="true">
                                    <option value="today">Hari Ini</option>
                                    <option value="this_week">Minggu Ini</option>
                                    <option value="this_month" selected>Bulan Ini</option>
                                    <option value="last_30_days">30 Hari Terakhir</option>
                                    <option value="custom">Pilih Sendiri</option>
                                </select>
                            </div>
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
                                <select name="school_id" id="school_id" class="form-select form-select-solid rounded-[12px]" data-control="select2" data-placeholder="Semua Sekolah / UPT">
                                    <option value="">Semua Sekolah / UPT</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <x-text.label>Kelas</x-text.label>
                                <select name="classroom_id" id="classroom_id" class="form-select form-select-solid rounded-[12px]" data-control="select2" data-placeholder="Semua Kelas">
                                    <option value="">Semua Kelas</option>
                                      @foreach($classrooms as $classroom)
                                          <option value="{{ $classroom->id }}" data-school-id="{{ $classroom->school_id }}">{{ $classroom->name }} ({{ $classroom->school->name ?? '-' }})</option>
                                      @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <x-text.label>Cari Siswa (NIS/Nama)</x-text.label>
                                <input type="text" name="search" id="search" class="form-control form-control-solid rounded-[12px]" placeholder="Masukkan NIS atau Nama (Opsional)">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary rounded-[24px] w-100" id="btn-generate-preview">
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
                        <x-text.body>Data di bawah belum disimpan. Saldo aplikasi lama akan menjadi <strong>sumber kebenaran tunggal (Single Source of Truth)</strong>. Tekan Sync Saldo untuk menimpa saldo lokal.</x-text.body>
                    </div>
                </div>
                <div class="card-body py-4 pb-8">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="table-preview">
                            <thead>
                                <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#table-preview .row-checkbox" id="check-all" />
                                        </div>
                                    </th>
                                    <th class="w-20px">No</th>
                                    <th>Siswa</th>
                                    <th>Kelas/UPT</th>
                                    <th>
                                        Saldo Lokal (Baru)
                                        <i class="fas fa-question-circle ms-1 fs-7 text-primary cursor-pointer" data-bs-toggle="tooltip" title="Saldo siswa saat ini di aplikasi lokal yang sedang Anda gunakan"></i>
                                    </th>
                                    <th>Total TX Master</th>
                                    <th>
                                        Saldo Master (Lama)
                                        <i class="fas fa-question-circle ms-1 fs-7 text-primary cursor-pointer" data-bs-toggle="tooltip" title="Saldo siswa yang tercatat di database server lama (master) — ini yang akan menjadi saldo akhir setelah sync"></i>
                                    </th>
                                    <th>Selisih</th>
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

<!-- History Modal -->
<div class="modal fade" tabindex="-1" id="historyModal">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-[24px] shadow-lg border-0">
            <div class="modal-header border-0 pt-8 px-8">
                <h3 class="modal-title">
                    <x-text.h2>Riwayat Transaksi Tertunda</x-text.h2>
                    <div class="text-muted fs-7 fw-normal mt-1" id="history-modal-subtitle">Detail transaksi dari server master</div>
                </h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times fs-2"></i>
                </div>
            </div>
            <div class="modal-body px-8 py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Waktu</th>
                                <th>Tipe</th>
                                <th>Penggunaan</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="history-modal-body" class="text-gray-600 fw-bold">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pb-8 px-8 justify-content-end">
                <button type="button" class="btn btn-light rounded-[24px]" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    var previewTable = null;

    // Filter UPT -> Kelas Cascade & Period Helper
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        function setPeriodDates(period) {
            var today = new Date();
            
            function formatDate(d) {
                var year = d.getFullYear();
                var month = String(d.getMonth() + 1).padStart(2, '0');
                var day = String(d.getDate()).padStart(2, '0');
                return year + '-' + month + '-' + day;
            }

            var start, end;
            
            if (period === 'today') {
                start = formatDate(today);
                end = formatDate(today);
            } else if (period === 'this_week') {
                var dayOfWeek = today.getDay();
                var distanceToMonday = (dayOfWeek === 0 ? -6 : 1 - dayOfWeek);
                var monday = new Date(today);
                monday.setDate(today.getDate() + distanceToMonday);
                
                start = formatDate(monday);
                end = formatDate(today);
            } else if (period === 'this_month') {
                var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                start = formatDate(firstDay);
                end = formatDate(today);
            } else if (period === 'last_30_days') {
                var thirtyDaysAgo = new Date(today);
                thirtyDaysAgo.setDate(today.getDate() - 30);
                start = formatDate(thirtyDaysAgo);
                end = formatDate(today);
            } else if (period === 'custom') {
                $('#start_date, #end_date').prop('readonly', false).removeClass('bg-light');
                return;
            }

            if (start && end) {
                $('#start_date').val(start);
                $('#end_date').val(end);
                $('#start_date, #end_date').prop('readonly', true).addClass('bg-light');
            }
        }

        $('#period_filter').on('change', function() {
            setPeriodDates($(this).val());
        });

        setPeriodDates($('#period_filter').val() || 'this_month');

        $('#school_id').on('change', function() {
            var schoolId = $(this).val();
            var classroomSelect = $('#classroom_id');
            
            if (!classroomSelect.data('options')) {
                classroomSelect.data('options', classroomSelect.find('option').clone());
            }
            
            var options = classroomSelect.data('options');
            classroomSelect.empty();
            
            if (schoolId) {
                var filteredOptions = options.filter(function() {
                    var val = $(this).val();
                    if (val === '') return true;
                    return $(this).data('school-id') == schoolId;
                });
                classroomSelect.append(filteredOptions);
            } else {
                classroomSelect.append(options);
            }
            
            classroomSelect.val(classroomSelect.val()).trigger('change.select2'); // Keep selected value if valid, else reset
        });

        // Trigger on load to apply initial filter if browser autocomplete filled it
        setTimeout(function() {
            if ($('#school_id').val()) {
                $('#school_id').trigger('change');
            }
        }, 100);
    });

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
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid">' +
                               '<input class="form-check-input row-checkbox" type="checkbox" value="' + row.student_id + '" />' +
                               '</div>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
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
                        let html = '<div class="d-flex flex-column align-items-start gap-1">' +
                                   '<span class="text-slate-600 font-medium">Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>';
                        if (row.local_saldo_date && row.local_saldo_time) {
                            html += '<div class="badge d-inline-flex flex-column align-items-start py-1 px-2.5 mt-1" style="font-size: 70%; border-radius: 8px; line-height: 1.35; width: fit-content; background-color: #EFF6FF; border: 1px solid #BFDBFE;">' +
                                    '<span class="fw-bold" style="color: #1D4ED8 !important;">' + row.local_saldo_date + '</span>' +
                                    '<span class="fw-semibold" style="color: #2563EB !important;">' + row.local_saldo_time + '</span>' +
                                    '</div>';
                        }
                        html += '</div>';
                        return html;
                    }
                },
                {
                    data: 'master_histories_count',
                    render: function(data, type, row) {
                        window.previewHistories = window.previewHistories || {};
                        window.previewHistories[row.student_id] = {
                            name: row.name,
                            histories: row.histories_to_insert
                        };

                        return '<span class="badge badge-light-primary fw-bolder cursor-pointer history-hover-trigger" data-student-id="' + row.student_id + '">' + data + ' Transaksi <i class="fas fa-eye ms-1 text-primary fs-8"></i></span>';
                    }
                },
                {
                    data: 'master_saldo',
                    render: function(data, type, row) {
                        let html = '<div class="d-flex flex-column align-items-start gap-1">' +
                                   '<span class="text-[18px] font-bold text-emerald-600">Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>';
                        if (row.master_saldo_date && row.master_saldo_time) {
                            html += '<div class="badge d-inline-flex flex-column align-items-start py-1 px-2.5 mt-1" style="font-size: 70%; border-radius: 8px; line-height: 1.35; width: fit-content; background-color: #ECFDF5; border: 1px solid #A7F3D0;">' +
                                    '<span class="fw-bold" style="color: #047857 !important;">' + row.master_saldo_date + '</span>' +
                                    '<span class="fw-semibold" style="color: #059669 !important;">' + row.master_saldo_time + '</span>' +
                                    '</div>';
                        }
                        html += '</div>';
                        return html;
                    }
                },
                {
                    data: 'saldo_difference',
                    render: function(data, type, row) {
                        if (data === 0) {
                            return '<span class="text-slate-400 font-medium">—</span>';
                        }
                        let color = data > 0 ? 'text-emerald-600' : 'text-red-600';
                        let sign = data > 0 ? '+' : '';
                        return '<span class="font-bold ' + color + '">' + sign + 'Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>';
                    }
                },
                {
                    data: 'conflict_status',
                    render: function(data, type, row) {
                        if (data === 'OK') {
                            return '<span class="badge badge-light-success px-3 py-2 fw-bolder" style="border-radius: 8px;">SYNCED</span>';
                        }
                        if (data === 'NEW') {
                            return '<span class="badge px-3 py-2 fw-bolder" style="background-color: #2563EB; color: #ffffff; border-radius: 8px; letter-spacing: 0.02em;">NEW</span>';
                        }
                        return '<span class="badge px-3 py-2 fw-bolder" style="background-color: #F59E0B; color: #ffffff; border-radius: 8px; letter-spacing: 0.02em;">NEEDS SYNC</span>';
                    }
                }
            ]
        });
    }

    // Execute Sync Action
    $('#btn-execute-sync').on('click', function(e) {
        e.preventDefault();
        var form = $('#form-execute-sync');
        var checkedBoxes = $('.row-checkbox:checked');
        var container = $('#selected-students-container');
        
        container.empty();
        
        if (checkedBoxes.length === 0) {
            Swal.fire({
                title: 'Tidak Ada Siswa Terpilih',
                text: 'Harap centang minimal satu siswa yang ingin Anda gabungkan datanya. Gunakan checkbox di sebelah kiri nama siswa.',
                icon: 'warning',
                buttonsStyling: false,
                confirmButtonText: "Ok, Mengerti",
                customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
            });
            return;
        }

        checkedBoxes.each(function() {
            container.append('<input type="hidden" name="selected_students[]" value="' + $(this).val() + '">');
        });
        
        Swal.fire({
            title: 'Sync Saldo dari Aplikasi Lama?',
            html: 'Saldo lokal untuk <b>' + checkedBoxes.length + ' siswa terpilih</b> akan <strong>ditimpa</strong> dengan saldo dari aplikasi lama (master). Riwayat transaksi lokal yang tidak ada di master akan dihapus. Aksi ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sync Saldo!',
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
                    text: 'Mohon tunggu, proses sinkronisasi saldo sedang berjalan.',
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

    // Checkbox count logic
    $(document).on('change', '.row-checkbox, #check-all', function() {
        var count = $('.row-checkbox:checked').length;
        var badge = $('#selected-count');
        if (count > 0) {
            badge.removeClass('d-none').text(count);
        } else {
            badge.addClass('d-none');
        }
    });

    // History Modal Trigger Logic (Click & Hover support with defensive data handling)
    var historyModalTimeout;
    var historyModalInstance = null;

    document.getElementById('historyModal').addEventListener('hidden.bs.modal', function () {
        historyModalInstance = null; // reset instance on hidden
    });

    function showHistoryModal(studentId, immediate) {
        var data = window.previewHistories ? window.previewHistories[studentId] : null;
        if (!data || !data.histories) return;

        clearTimeout(historyModalTimeout);

        $('#history-modal-subtitle').text('Siswa: ' + data.name);
        var bodyHtml = '';
        
        var historiesList = Array.isArray(data.histories) ? data.histories : Object.values(data.histories || {});

        historiesList.forEach(function(h) {
            var color = (h.type === 'IN' || h.type === 'UNBLOCKED') ? 'success' : 'danger';
            var sign = (h.type === 'IN' || h.type === 'UNBLOCKED') ? '+' : '-';
            
            bodyHtml += '<tr>';
            bodyHtml += '<td>' + new Date(h.created_at).toLocaleString('id-ID') + '</td>';
            bodyHtml += '<td><span class="badge badge-light-' + color + '">' + h.type + '</span></td>';
            bodyHtml += '<td>' + (h.usage || '-') + '</td>';
            bodyHtml += '<td class="text-' + color + '">' + sign + ' Rp ' + new Intl.NumberFormat('id-ID').format(h.amount) + '</td>';
            bodyHtml += '<td><div style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="' + (h.description || '-') + '">' + (h.description || '-') + '</div></td>';
            bodyHtml += '</tr>';
        });
        
        if (historiesList.length === 0) {
            bodyHtml = '<tr><td colspan="5" class="text-center text-muted">Tidak ada riwayat</td></tr>';
        }

        $('#history-modal-body').html(bodyHtml);

        var triggerShow = function() {
            if (!historyModalInstance) {
                historyModalInstance = new bootstrap.Modal(document.getElementById('historyModal'));
            }
            historyModalInstance.show();
        };

        if (immediate) {
            triggerShow();
        } else {
            historyModalTimeout = setTimeout(triggerShow, 300);
        }
    }

    $(document).on('click', '.history-hover-trigger', function(e) {
        e.preventDefault();
        var studentId = $(this).data('student-id');
        showHistoryModal(studentId, true);
    });

    $(document).on('mouseenter', '.history-hover-trigger', function() {
        var studentId = $(this).data('student-id');
        showHistoryModal(studentId, false);
    });

    $(document).on('mouseleave', '.history-hover-trigger', function() {
        clearTimeout(historyModalTimeout);
    });

    $('#btn-back').on('click', function() {
        window.location.href = "{{ route('admin.audit.sync') }}";
    });
</script>
@endpush
@endsection
