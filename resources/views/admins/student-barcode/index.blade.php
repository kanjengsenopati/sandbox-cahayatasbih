@extends('layouts.master', ['title' => 'Barcode Santri'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Santri</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student.index') }}" class="text-muted text-hover-primary">Data Santri</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Barcode Santri</li>
                    <!--end::Item-->

                </ul>
                <!--end::Breadcrumb-->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-between gap-5">
                        <div class="mb-3">
                            <label for="filter_school" class="form-label fw-bold">UPT</label>
                            <select class="form-select" id="filter_school">
                                <option value="">Semua UPT</option>
                                @foreach ($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_class" class="form-label fw-bold">Kelas</label>
                            <select class="form-select" id="filter_class">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_name" class="form-label fw-bold">Pencarian</label>
                            <input type="text" class="form-control" id="filter_name" placeholder="Ketik nama santri...">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="{{ route('student-card-reports.index') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-id-card me-2"></i>
                            Monitoring Lapor Kartu
                            @if(($pendingReportsCount ?? 0) > 0)
                                <span class="badge bg-danger text-white ms-1">{{ $pendingReportsCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('student-barcode.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-print me-2"></i>
                            Cetak Barcode
                        </a>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-student" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th style="width: 3%">No</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>UPT</th>
                                    <th>Kelas</th>
                                    <th>Barcode</th>
                                    <th class="text-center min-w-150px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

            <!--begin::Premium Modal Styles-->
            <style>
                .pakrt-modal-content {
                    border-radius: 24px !important;
                    border: none !important;
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
                    overflow: hidden;
                }
                .pakrt-modal-header {
                    padding: 22px 24px 16px 24px !important;
                    border-bottom: 1px solid #f1f5f9 !important;
                    background: linear-gradient(180deg, rgba(248, 250, 252, 0.9) 0%, #ffffff 100%);
                }
                .pakrt-student-card {
                    border-radius: 18px;
                    background: linear-gradient(135deg, rgba(37, 99, 235, 0.06) 0%, rgba(16, 185, 129, 0.06) 100%);
                    border: 1px solid rgba(37, 99, 235, 0.12);
                    padding: 14px 16px;
                }
                .pakrt-code-box {
                    border-radius: 14px;
                    padding: 10px 14px;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                    letter-spacing: 0.06em;
                }
                .pakrt-swal-popup {
                    border-radius: 24px !important;
                    padding: 24px !important;
                    box-shadow: 0 12px 40px rgba(15, 23, 42, 0.12) !important;
                }
            </style>
            <!--end::Premium Modal Styles-->

            <!--begin::Modal Edit Barcode-->
            <div class="modal fade" id="modalEditBarcode" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-500px">
                    <div class="modal-content pakrt-modal-content">
                        <div class="modal-header pakrt-modal-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="symbol symbol-40px">
                                    <span class="symbol-label bg-primary bg-opacity-10 text-primary" style="border-radius: 12px;">
                                        <i class="fas fa-barcode fs-4 text-primary"></i>
                                    </span>
                                </div>
                                <div>
                                    <h3 class="fw-bolder text-gray-900 mb-0" style="font-size: 16px;">Edit Barcode Santri</h3>
                                    <div class="text-muted" style="font-size: 12px;">Perbarui atau acak kode unik kartu santri</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-primary rounded-circle" data-bs-dismiss="modal">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <form id="formEditBarcode">
                            @csrf
                            <input type="hidden" id="edit_student_id">
                            <div class="modal-body py-5 px-6">
                                <!-- Student Info Preview -->
                                <div class="d-flex align-items-center mb-5 pakrt-student-card">
                                    <div class="symbol symbol-45px me-3">
                                        <span class="symbol-label bg-primary text-white fw-bold" style="border-radius: 14px;">
                                            <i class="fa fa-user-graduate fs-5 text-white"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-uppercase fw-bold text-primary" style="font-size: 10px; letter-spacing: 0.08em;">Identitas Santri</div>
                                        <div class="fw-bolder text-gray-900" style="font-size: 15px;" id="edit_student_name">-</div>
                                        <div class="text-muted fw-medium" style="font-size: 12px;">NIS: <span class="fw-bold text-gray-700" id="edit_student_nis">-</span></div>
                                    </div>
                                </div>

                                <!-- Current & Previous Barcode Grid -->
                                <div class="row g-3 mb-4">
                                    <div class="col-12" id="col_current_barcode">
                                        <label class="form-label text-muted text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.08em;">Barcode Aktif Saat Ini</label>
                                        <div class="pakrt-code-box bg-light border border-gray-200 fw-bolder text-gray-800 fs-6" id="edit_current_barcode">-</div>
                                    </div>
                                    <div class="col-12 d-none" id="container_prev_barcode">
                                        <label class="form-label text-warning text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.08em;">
                                            <i class="fa fa-history me-1 text-warning"></i>Riwayat Sebelumnya (Tersedia untuk Rollback)
                                        </label>
                                        <div class="pakrt-code-box bg-light-warning border border-warning border-dashed fw-bold text-gray-800 fs-7" id="edit_previous_barcode">-</div>
                                    </div>
                                </div>

                                <!-- New Barcode Input -->
                                <div class="mb-4">
                                    <label for="edit_input_barcode" class="form-label text-uppercase fw-bold text-gray-700 required mb-2" style="font-size: 11px; letter-spacing: 0.08em;">Barcode Baru</label>
                                    <div class="input-group input-group-solid border border-gray-300" style="border-radius: 14px; overflow: hidden;">
                                        <input type="text" class="form-control form-control-solid border-0 font-monospace fw-bold fs-6 ps-4" id="edit_input_barcode" name="barcode" placeholder="Ketik atau scan barcode baru..." required autocomplete="off">
                                        <button type="button" class="btn btn-light-primary fw-bold px-4" id="btn_generate_modal_barcode" title="Generate Otomatis 16 Digit Unik">
                                            <i class="fas fa-magic me-1"></i> Generate 16 Digit
                                        </button>
                                    </div>
                                    <div class="form-text text-muted mt-2" style="font-size: 12px;">
                                        <i class="fa fa-info-circle me-1 text-primary"></i>Format rekomendasi: <strong>16 digit angka</strong> (Code 128C murni) agar pemindaian kasir instan.
                                    </div>
                                </div>

                                <!-- Checkbox Download JPG -->
                                <div class="p-3 rounded-3 bg-light-success bg-opacity-50 border border-success border-opacity-25">
                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                        <input class="form-check-input" type="checkbox" id="edit_download_png" checked />
                                        <label class="form-check-label text-gray-800 fw-semibold fs-7 cursor-pointer" for="edit_download_png">
                                            Otomatis unduh file gambar barcode (<strong>.jpg</strong>) setelah disimpan
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0 pb-5 px-6 justify-content-end gap-2">
                                <button type="button" class="btn btn-light fw-bold px-5" style="border-radius: 12px;" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary fw-bold px-6" style="border-radius: 12px;" id="btn_save_barcode">
                                    <span class="indicator-label"><i class="fa fa-save me-1"></i> Simpan Perubahan</span>
                                    <span class="indicator-progress d-none">Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--end::Modal Edit Barcode-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        // Initialize DataTable
        var table = $('#table-student').DataTable({
            ordering: true,
            order: [[2, 'asc']], // Default order by Name ascending
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('student-barcode.index') }}',
                data: function(d) {
                    d.school_id = $('#filter_school').val();
                    d.classroom_id = $('#filter_class').val();
                    d.status = $('#filter_status').val();
                    d.name = $('#filter_name').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'></i>",
                    "previous": "<i class='fa fa-angle-left'></i>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'nis',
                    name: 'nis',
                    orderable: true,
                    render: function(data, type, row) {
                        return data ? data : 'Belum diisi';
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'school',
                    name: 'school',
                    orderable: false,
                },
                {
                    data: 'classroom',
                    name: 'classroom',
                    orderable: false,
                },
                {
                    data: 'barcode',
                    name: 'barcode',
                    orderable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                },
            ]
        });

        // Populate filter_class on school change
        $('#filter_school').on('change', function() {
            var school_id = $(this).val();
            var url = "{{ route('student.get-classroom', ':id') }}".replace(':id', school_id);

            $.get(url, function(data) {
                $('#filter_class').html('<option value="">Semua Kelas</option>');
                if (data.length > 0) {
                    $.each(data, function(index, value) {
                        $('#filter_class').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        });

        // Reload DataTable on filter change
        $('#filter_school, #filter_class, #filter_status').on('change', function() {
            table.ajax.reload();
        });
        
        // Search by name with delay (debounce) to avoid spamming ajax
        var searchTimeout;
        $('#filter_name').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                table.ajax.reload();
            }, 500);
        });

        // ==================== EDIT BARCODE HANDLER ====================
        $(document).on('click', '.btn-edit-barcode', function() {
            var studentId = $(this).data('id');
            var studentName = $(this).data('name');
            var studentNis = $(this).data('nis');
            var currentBarcode = $(this).data('barcode');
            var prevBarcode = $(this).data('previous');

            $('#edit_student_id').val(studentId);
            $('#edit_student_name').text(studentName);
            $('#edit_student_nis').text(studentNis);
            $('#edit_current_barcode').text(currentBarcode || 'Belum ada');
            $('#edit_input_barcode').val(currentBarcode || '');

            if (prevBarcode) {
                $('#edit_previous_barcode').text(prevBarcode);
                $('#container_prev_barcode').removeClass('d-none');
            } else {
                $('#container_prev_barcode').addClass('d-none');
            }

            $('#modalEditBarcode').modal('show');
            setTimeout(function() {
                $('#edit_input_barcode').focus().select();
            }, 400);
        });

        // Generate Random Unique Barcode in Modal
        $('#btn_generate_modal_barcode').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Memuat...');

            $.get('{{ route('student-barcode.generate-unique') }}', function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Generate 16 Digit');
                if (res.barcode) {
                    $('#edit_input_barcode').val(res.barcode);
                }
            }).fail(function() {
                btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Generate 16 Digit');
                Swal.fire('Error', 'Gagal menghasilkan barcode otomatis.', 'error');
            });
        });

        // ==================== QUICK GENERATE BARCODE HANDLER (MODERN MODAL) ====================
        $(document).on('click', '.btn-quick-generate-barcode', function() {
            var studentId = $(this).data('id');
            var studentName = $(this).data('name');
            var studentNis = $(this).data('nis') || '-';
            var currentBarcode = $(this).data('barcode') || 'Belum ada';
            var actionUrl = $(this).data('url');

            Swal.fire({
                title: '<span class="fw-bolder text-gray-900 fs-4">Generate Barcode Baru?</span>',
                html: `
                    <div class="text-start mt-2">
                        <div class="pakrt-student-card mb-4">
                            <div class="text-uppercase fw-bold text-primary" style="font-size: 10px; letter-spacing: 0.08em;">Santri Terpilih</div>
                            <div class="fw-bolder text-gray-900 fs-6 mt-1">${studentName}</div>
                            <div class="text-muted fs-8">NIS: <strong>${studentNis}</strong></div>
                        </div>
                        <div class="mb-3">
                            <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 10px; letter-spacing: 0.08em;">Barcode Saat Ini (Disimpan ke Rollback)</div>
                            <div class="pakrt-code-box bg-light border border-gray-200 fw-bold text-gray-700 fs-7">${currentBarcode}</div>
                        </div>
                        <div class="p-3 rounded-3 bg-light-info border border-info border-opacity-25 text-gray-700 fs-7">
                            <i class="fas fa-sync-alt text-info me-1"></i> Sistem akan membuat <strong>16 digit barcode unik baru</strong> dan langsung mengunduh gambar <strong>.jpg</strong> siap cetak.
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: '<i class="fas fa-bolt me-1"></i> Ya, Generate & Unduh JPG',
                cancelButtonText: '<span class="text-gray-700 fw-bold">Batal</span>',
                customClass: {
                    popup: 'pakrt-swal-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Membuat Barcode Baru...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        customClass: { popup: 'pakrt-swal-popup' },
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: actionUrl,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            table.ajax.reload(null, false);
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Barcode baru berhasil di-generate.',
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false,
                                customClass: { popup: 'pakrt-swal-popup' }
                            });

                            if (response.download_url) {
                                window.location.href = response.download_url;
                            }
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat men-generate barcode baru.', 'error');
                        }
                    });
                }
            });
        });

        // Submit Form Edit Barcode
        $('#formEditBarcode').on('submit', function(e) {
            e.preventDefault();

            var studentId = $('#edit_student_id').val();
            var newBarcode = $('#edit_input_barcode').val();
            var downloadPng = $('#edit_download_png').is(':checked');
            var submitBtn = $('#btn_save_barcode');

            if (!newBarcode || newBarcode.trim() === '') {
                Swal.fire('Peringatan', 'Barcode tidak boleh kosong.', 'warning');
                return;
            }

            submitBtn.find('.indicator-label').addClass('d-none');
            submitBtn.find('.indicator-progress').removeClass('d-none');
            submitBtn.prop('disabled', true);

            var updateUrl = '{{ route('student-barcode.update-barcode', ':id') }}'.replace(':id', studentId);

            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    barcode: newBarcode
                },
                success: function(response) {
                    submitBtn.find('.indicator-label').removeClass('d-none');
                    submitBtn.find('.indicator-progress').addClass('d-none');
                    submitBtn.prop('disabled', false);

                    $('#modalEditBarcode').modal('hide');
                    table.ajax.reload(null, false);

                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (downloadPng && response.download_url) {
                        window.location.href = response.download_url;
                    }
                },
                error: function(xhr) {
                    submitBtn.find('.indicator-label').removeClass('d-none');
                    submitBtn.find('.indicator-progress').addClass('d-none');
                    submitBtn.prop('disabled', false);

                    var msg = 'Terjadi kesalahan saat memperbarui barcode.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.barcode) {
                        msg = xhr.responseJSON.errors.barcode[0];
                    }

                    Swal.fire('Validasi Gagal', msg, 'error');
                }
            });
        });

        // ==================== ROLLBACK BARCODE HANDLER ====================
        $(document).on('click', '.btn-rollback-barcode', function() {
            var studentId = $(this).data('id');
            var studentName = $(this).data('name');
            var prevBarcode = $(this).data('previous');

            if (!prevBarcode) {
                Swal.fire('Informasi', 'Santri ini belum memiliki riwayat barcode sebelumnya.', 'info');
                return;
            }

            Swal.fire({
                title: '<span class="fw-bolder text-gray-900 fs-4">Rollback Barcode Santri?</span>',
                html: `
                    <div class="text-start mt-2">
                        <div class="pakrt-student-card mb-4">
                            <div class="text-uppercase fw-bold text-primary" style="font-size: 10px; letter-spacing: 0.08em;">Santri Terpilih</div>
                            <div class="fw-bolder text-gray-900 fs-6 mt-1">${studentName}</div>
                        </div>
                        <div class="mb-2">
                            <div class="text-uppercase fw-bold text-warning mb-1" style="font-size: 10px; letter-spacing: 0.08em;">Kembalikan ke Barcode Sebelumnya</div>
                            <div class="pakrt-code-box bg-light-warning border border-warning fw-bolder text-gray-900 fs-6 text-center">${prevBarcode}</div>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: '<i class="fas fa-undo me-1"></i> Ya, Kembalikan!',
                cancelButtonText: '<span class="text-gray-700 fw-bold">Batal</span>',
                customClass: {
                    popup: 'pakrt-swal-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var rollbackUrl = '{{ route('student-barcode.rollback-barcode', ':id') }}'.replace(':id', studentId);

                    Swal.fire({
                        title: 'Memproses Rollback...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: rollbackUrl,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            Swal.fire('Berhasil!', response.message, 'success');
                        },
                        error: function(xhr) {
                            var msg = 'Gagal melakukan rollback barcode.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Gagal Rollback', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush