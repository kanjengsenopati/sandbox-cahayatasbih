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

            <!--begin::Modal Edit Barcode-->
            <div class="modal fade" id="modalEditBarcode" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-500px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="fw-bolder modal-title"><i class="fas fa-barcode text-primary me-2"></i>Edit Barcode Santri</h3>
                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                                <i class="fa fa-times"></i>
                            </div>
                        </div>
                        <form id="formEditBarcode">
                            @csrf
                            <input type="hidden" id="edit_student_id">
                            <div class="modal-body py-4 px-lg-8">
                                <!-- Student Info Preview -->
                                <div class="d-flex align-items-center p-3 mb-4 rounded bg-light-primary border border-primary border-dashed">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label bg-primary text-white fw-bold"><i class="fa fa-user"></i></span>
                                    </div>
                                    <div>
                                        <div class="fw-bolder text-gray-900 fs-6" id="edit_student_name">-</div>
                                        <div class="text-muted fs-8">NIS: <span id="edit_student_nis">-</span></div>
                                    </div>
                                </div>

                                <!-- Current Barcode -->
                                <div class="mb-3">
                                    <label class="form-label text-muted fs-8 text-uppercase fw-bold">Barcode Saat Ini</label>
                                    <div class="p-2 bg-light rounded border font-monospace fw-bold text-gray-700" id="edit_current_barcode">-</div>
                                </div>

                                <!-- Previous Barcode (if any) -->
                                <div class="mb-3 d-none" id="container_prev_barcode">
                                    <label class="form-label text-muted fs-8 text-uppercase fw-bold">Barcode Sebelumnya (State Sebelum Diubah)</label>
                                    <div class="p-2 bg-light-warning rounded border border-warning font-monospace text-gray-800" id="edit_previous_barcode">-</div>
                                </div>

                                <!-- New Barcode Input -->
                                <div class="mb-3">
                                    <label for="edit_input_barcode" class="form-label fw-bold required">Barcode Baru</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace" id="edit_input_barcode" name="barcode" placeholder="Ketik atau scan barcode baru..." required autocomplete="off">
                                        <button type="button" class="btn btn-secondary" id="btn_generate_modal_barcode" title="Generate Otomatis Barcode Acak Unik">
                                            <i class="fas fa-magic me-1"></i> Acak
                                        </button>
                                    </div>
                                    <div class="form-text text-muted fs-8">Setiap barcode santri wajib unik (minimal 5 karakter, maksimal 64 karakter).</div>
                                </div>

                                <!-- Checkbox Download PNG -->
                                <div class="form-check form-check-custom form-check-solid mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit_download_png" checked />
                                    <label class="form-check-label text-gray-800 fw-semibold fs-7" for="edit_download_png">
                                        Unduh file gambar barcode (.png) setelah berhasil disimpan
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary" id="btn_save_barcode">
                                    <span class="indicator-label"><i class="fa fa-save me-1"></i> Simpan Barcode</span>
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
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.get('{{ route('student-barcode.generate-unique') }}', function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Acak');
                if (res.barcode) {
                    $('#edit_input_barcode').val(res.barcode);
                }
            }).fail(function() {
                btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Acak');
                Swal.fire('Error', 'Gagal menghasilkan barcode otomatis.', 'error');
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
                title: 'Rollback Barcode?',
                html: 'Apakah Anda yakin ingin mengembalikan barcode santri <strong>' + studentName + '</strong> ke barcode sebelumnya: <br><br><span class="badge badge-light-warning fs-5 font-monospace text-dark p-2">' + prevBarcode + '</span>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-undo me-1"></i> Ya, Kembalikan!',
                cancelButtonText: 'Batal'
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