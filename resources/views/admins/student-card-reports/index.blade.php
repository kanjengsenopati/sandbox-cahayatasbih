@extends('layouts.master', ['title' => 'Monitoring Lapor Kartu Santri'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Monitoring Kendala Kartu Santri</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student.index') }}" class="text-muted text-hover-primary">Data Santri</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student-barcode.index') }}" class="text-muted text-hover-primary">Barcode Santri</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-dark">Laporan Kartu</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('student-barcode.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-barcode me-1"></i> Kelola Barcode Santri
                </a>
                <a href="{{ route('student-card-setting.index') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-print me-1"></i> Desain & Cetak Kartu
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">

            <!--begin::Stats Summary Cards-->
            <div class="row g-5 g-xl-6 mb-6">
                <!-- Total Menunggu -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-light-danger border border-danger border-dashed">
                        <div class="card-body my-1 py-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-danger fw-bolder fs-7 text-uppercase ls-1">Menunggu Cetak</span>
                                    <div class="text-gray-900 fw-bolder fs-2hx mt-1">{{ number_format($counts['total_pending'] ?? 0) }}</div>
                                </div>
                                <div class="symbol symbol-45px bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center rounded">
                                    <i class="fa fa-clock fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rusak -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-light-warning border border-warning border-dashed">
                        <div class="card-body my-1 py-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-warning fw-bolder fs-7 text-uppercase ls-1">Kartu Rusak</span>
                                    <div class="text-gray-900 fw-bolder fs-2hx mt-1">{{ number_format($counts['rusak'] ?? 0) }}</div>
                                </div>
                                <div class="symbol symbol-45px bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center rounded">
                                    <i class="fa fa-heart-broken fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gagal Transaksi -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-light-info border border-info border-dashed">
                        <div class="card-body my-1 py-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-info fw-bolder fs-7 text-uppercase ls-1">Tidak Bisa Transaksi</span>
                                    <div class="text-gray-900 fw-bolder fs-2hx mt-1">{{ number_format($counts['tidak_bisa_transaksi'] ?? 0) }}</div>
                                </div>
                                <div class="symbol symbol-45px bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center rounded">
                                    <i class="fa fa-times-circle fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hilang / Selesai -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-light-success border border-success border-dashed">
                        <div class="card-body my-1 py-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-success fw-bolder fs-7 text-uppercase ls-1">Selesai Dicetak</span>
                                    <div class="text-gray-900 fw-bolder fs-2hx mt-1">{{ number_format($counts['completed'] ?? 0) }}</div>
                                </div>
                                <div class="symbol symbol-45px bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center rounded">
                                    <i class="fa fa-check-double fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Stats Summary Cards-->

            <!--begin::Card Main Table-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-4 pb-4">
                    <!-- Filters -->
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1">Status</label>
                            <select id="filter_status" class="form-select form-select-sm form-select-solid w-150px">
                                <option value="pending" selected>Menunggu Cetak</option>
                                <option value="completed">Selesai Dicetak</option>
                                <option value="">Semua Status</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1">Jenis Kendala</label>
                            <select id="filter_issue_type" class="form-select form-select-sm form-select-solid w-160px">
                                <option value="">Semua Kendala</option>
                                <option value="rusak">Kartu Rusak</option>
                                <option value="tidak_bisa_transaksi">Tidak Bisa Transaksi</option>
                                <option value="hilang">Kartu Hilang</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1">UPT</label>
                            <select id="filter_school" class="form-select form-select-sm form-select-solid w-160px">
                                <option value="">Semua UPT</option>
                                @foreach ($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1">Pencarian</label>
                            <input type="text" id="filter_search" class="form-control form-control-sm form-control-solid w-200px" placeholder="Nama / NIS santri..." />
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="btn-bulk-complete" class="btn btn-success btn-sm d-none">
                            <i class="fa fa-check-circle me-1"></i> Tandai Selesai Terpilih (<span id="bulk_complete_count">0</span>)
                        </button>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-reports" class="table table-striped table-row-bordered gy-5 gs-7 align-middle border rounded">
                            <thead>
                                <tr class="fw-bolder fs-7 text-gray-800 text-uppercase border-bottom border-gray-200">
                                    <th width="3%" class="text-center">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                            <input class="form-check-input" type="checkbox" id="check-all-reports" />
                                        </div>
                                    </th>
                                    <th width="10%">Tanggal Lapor</th>
                                    <th>Santri (Nama & NIS)</th>
                                    <th>Barcode</th>
                                    <th>Kendala</th>
                                    <th>Pelapor & Catatan</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card Main Table-->

            <!--begin::Modal Edit Barcode Shortcut-->
            <div class="modal fade" id="modalEditBarcode" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-500px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="fw-bolder modal-title"><i class="fas fa-barcode text-primary me-2"></i>Edit / Ganti Barcode Santri</h3>
                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                                <i class="fa fa-times"></i>
                            </div>
                        </div>
                        <form id="formEditBarcode">
                            @csrf
                            <input type="hidden" id="edit_student_id">
                            <div class="modal-body py-4 px-lg-8">
                                <div class="d-flex align-items-center p-3 mb-4 rounded bg-light-primary border border-primary border-dashed">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label bg-primary text-white fw-bold"><i class="fa fa-user"></i></span>
                                    </div>
                                    <div>
                                        <div class="fw-bolder text-gray-900 fs-6" id="edit_student_name">-</div>
                                        <div class="text-muted fs-8">NIS: <span id="edit_student_nis">-</span></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted fs-8 text-uppercase fw-bold">Barcode Saat Ini</label>
                                    <div class="p-2 bg-light rounded border font-monospace fw-bold text-gray-700" id="edit_current_barcode">-</div>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_input_barcode" class="form-label fw-bold required">Barcode Baru</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace" id="edit_input_barcode" name="barcode" placeholder="Ketik atau scan barcode..." required autocomplete="off">
                                        <button type="button" class="btn btn-secondary" id="btn_generate_modal_barcode" title="Generate Otomatis Barcode Acak Unik">
                                            <i class="fas fa-magic me-1"></i> Acak
                                        </button>
                                    </div>
                                    <div class="form-text text-muted fs-8">Barcode wajib unik across santri.</div>
                                </div>

                                <div class="form-check form-check-custom form-check-solid mt-3">
                                    <input class="form-check-input" type="checkbox" id="edit_download_png" />
                                    <label class="form-check-label text-gray-800 fw-semibold fs-7" for="edit_download_png">
                                        Unduh file barcode (.png) setelah disimpan
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary" id="btn_save_barcode">
                                    <span class="indicator-label"><i class="fa fa-save me-1"></i> Simpan</span>
                                    <span class="indicator-progress d-none">Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--end::Modal Edit Barcode Shortcut-->

        </div>
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        var table = $('#table-reports').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: '{{ route('student-card-reports.index') }}',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.issue_type = $('#filter_issue_type').val();
                    d.school_id = $('#filter_school').val();
                    d.search_name = $('#filter_search').val();
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'student_info', name: 'student_info' },
                { data: 'barcode', name: 'barcode' },
                { data: 'issue_type', name: 'issue_type' },
                { data: 'reporter_info', name: 'reporter_info' },
                { data: 'status', name: 'status' },
                { data: 'action', orderable: false, searchable: false }
            ],
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                processing: "Memuat antrean laporan kartu..."
            }
        });

        // Filter triggers
        $('#filter_status, #filter_issue_type, #filter_school').on('change', function() {
            table.ajax.reload();
        });

        var searchTimer;
        $('#filter_search').on('keyup input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                table.ajax.reload();
            }, 400);
        });

        // Checkbox handling
        $('#check-all-reports').on('click', function() {
            var checked = this.checked;
            $('.report-checkbox:not(:disabled)').prop('checked', checked);
            toggleBulkButton();
        });

        $(document).on('click', '.report-checkbox', function() {
            var allChecked = $('.report-checkbox:not(:disabled):checked').length === $('.report-checkbox:not(:disabled)').length;
            $('#check-all-reports').prop('checked', allChecked);
            toggleBulkButton();
        });

        table.on('draw', function() {
            $('#check-all-reports').prop('checked', false);
            toggleBulkButton();
        });

        function toggleBulkButton() {
            var count = $('.report-checkbox:checked').length;
            if (count > 0) {
                $('#btn-bulk-complete').removeClass('d-none');
                $('#bulk_complete_count').text(count);
            } else {
                $('#btn-bulk-complete').addClass('d-none');
            }
        }

        // Tandai Selesai Tunggal
        $(document).on('click', '.btn-complete-report', function() {
            var url = $(this).data('url');
            var name = $(this).data('name');

            Swal.fire({
                title: 'Tandai Selesai?',
                text: 'Konfirmasi bahwa kartu santri ' + name + ' telah berhasil dicetak ulang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#50cd89',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Selesai!',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil!', res.message, 'success');
                    }).fail(function() {
                        Swal.fire('Error', 'Gagal memperbarui status laporan.', 'error');
                    });
                }
            });
        });

        // Tandai Selesai Massal
        $('#btn-bulk-complete').on('click', function() {
            var ids = [];
            $('.report-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) return;

            Swal.fire({
                title: 'Tandai Selesai Massal?',
                text: 'Menandai ' + ids.length + ' kartu terpilih telah selesai dicetak ulang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#50cd89',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tandai Selesai!',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post('{{ route('student-card-reports.bulk-complete') }}', {
                        _token: '{{ csrf_token() }}',
                        ids: ids
                    }, function(res) {
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil!', res.message, 'success');
                    }).fail(function() {
                        Swal.fire('Error', 'Gagal memperbarui laporan massal.', 'error');
                    });
                }
            });
        });

        // Hapus Laporan
        $(document).on('click', '.btn-delete-report', function() {
            var url = $(this).data('url');

            Swal.fire({
                title: 'Hapus Laporan?',
                text: 'Laporan kendala kartu ini akan dihapus dari antrean.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus!', res.message, 'success');
                        },
                        error: function() {
                            Swal.fire('Error', 'Gagal menghapus laporan.', 'error');
                        }
                    });
                }
            });
        });

        // Edit Barcode Shortcut Modal
        $(document).on('click', '.btn-edit-barcode', function() {
            var studentId = $(this).data('id');
            var studentName = $(this).data('name');
            var studentNis = $(this).data('nis');
            var currentBarcode = $(this).data('barcode');

            $('#edit_student_id').val(studentId);
            $('#edit_student_name').text(studentName);
            $('#edit_student_nis').text(studentNis);
            $('#edit_current_barcode').text(currentBarcode || 'Belum ada');
            $('#edit_input_barcode').val(currentBarcode || '');

            $('#modalEditBarcode').modal('show');
        });

        $('#btn_generate_modal_barcode').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.get('{{ route('student-barcode.generate-unique') }}', function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Acak');
                if (res.barcode) {
                    $('#edit_input_barcode').val(res.barcode);
                }
            });
        });

        $('#formEditBarcode').on('submit', function(e) {
            e.preventDefault();
            var studentId = $('#edit_student_id').val();
            var newBarcode = $('#edit_input_barcode').val();
            var downloadPng = $('#edit_download_png').is(':checked');
            var updateUrl = '{{ route('student-barcode.update-barcode', ':id') }}'.replace(':id', studentId);

            $.post(updateUrl, {
                _token: '{{ csrf_token() }}',
                barcode: newBarcode
            }, function(res) {
                $('#modalEditBarcode').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire('Berhasil!', res.message, 'success');
                if (downloadPng && res.download_url) {
                    window.location.href = res.download_url;
                }
            }).fail(function(xhr) {
                var msg = 'Gagal menyimpan barcode.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Validasi Gagal', msg, 'error');
            });
        });
    });
</script>
@endpush
