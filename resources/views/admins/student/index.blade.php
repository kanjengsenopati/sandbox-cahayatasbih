@extends('layouts.master', ['title' => 'Data Siswa'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Daftar Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student.index') }}" class="text-muted text-hover-primary">Siswa</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List Siswa</li>
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
                            <label class="form-label fw-bold">Kelas</label>
                            <div class="dropdown" id="student_classroom_dropdown_container" style="position: relative !important;">
                                <input type="hidden" id="filter_class" value="">
                                <button class="btn btn-light border bg-white fs-7 d-flex justify-content-between align-items-center" type="button" id="filter_class_btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-width: 150px; height: 42px; cursor: pointer;">
                                    <span id="filter_class_btn_text" class="text-truncate me-2" style="pointer-events: none;">Semua Kelas</span>
                                    <i class="fas fa-chevron-down fs-8 text-gray-500 filter-class-arrow" style="pointer-events: none; transition: transform 0.2s ease;"></i>
                                </button>
                                <div class="dropdown-menu p-3 shadow-lg border-0" style="min-width: 260px; width: 500px; max-width: calc(100vw - 32px); max-height: 420px; overflow-y: auto; border-radius: 16px; position: absolute !important; top: 100% !important; margin-top: 6px !important; z-index: 9999 !important;" aria-labelledby="filter_class_btn" id="student_classroom_mega_menu">
                                    <div class="text-muted fs-7 p-3 text-center">Pilih UPT terlebih dahulu</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="filter_status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="filter_status">
                                <option value="">Semua Status</option>
                                <option value="ACTIVE">Aktif</option>
                                <option value="INACTIVE">Tidak Aktif</option>
                                <option value="GRADUATED">Lulus</option>
                                <option value="DROPPED_OUT">Drop Out</option>
                                <option value="TRANSFERRED">Pindah</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_name" class="form-label fw-bold">Cari Nama / NIS</label>
                            <div class="d-flex align-items-center position-relative">
                                <span class="svg-icon svg-icon-1 position-absolute ms-3">
                                    <i class="fas fa-search text-gray-400"></i>
                                </span>
                                <input type="text" id="filter_name" class="form-control form-control-solid ps-9" placeholder="Cari Nama / NIS..." style="width: 200px;" />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-end">
                        {{-- <div class="me-sm-3 mb-3 mb-sm-0"> --}}
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-bulk-update-substatus" class="btn btn-warning btn-sm d-none" data-bs-toggle="modal" data-bs-target="#modalBulkUpdateSubStatus">
                                    <i class="fa fa-edit me-2"></i> Ubah Status PPTQ
                                </button>
                                <button type="button" id="btn-bulk-delete-student" class="btn btn-danger btn-sm d-none">
                                    <i class="fa fa-trash me-2"></i> Hapus Terpilih
                                </button>
                                <a href="{{ route('student-barcode.index') }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-print me-2"></i>
                                    Barcode Santri</a>
                                <x-action.import target="#modalImport" name="Santri" />
                                {{--
                            </div> --}}
                            <x-action.create name="Santri" action="{{ route('student.create') }}" />
                        </div>
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
                                    <th width="3%">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" id="check-all-student">
                                        </div>
                                    </th>
                                    <th style="width: 3%">No</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Wali Siswa</th>
                                    <th>UPT</th>
                                    <th>Saldo</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-100px">Aksi</th>
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
            <!--begin::Modals-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>

<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.import-preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Data Santri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel</label>
                        <input class="form-control" type="file" name="file" id="file">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <a href="assets\media\template\import\Template Import Data Santri.xlsx"
                            class="btn btn-light-primary"><i class="fa fa-download"></i> Template</a>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Siswa (Wide Modal) -->
<div class="modal fade" id="modalDetailSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" id="modalDetailSiswaContent">
            <div class="p-10 text-center text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Memuat Detail Siswa...
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Siswa (Wide Modal) -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" id="modalEditSiswaContent">
            <div class="p-10 text-center text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Memuat Form Edit Siswa...
            </div>
        </div>
    </div>
</div>

<!-- Modal Bulk Update Sub Status PPTQ -->
<div class="modal fade" id="modalBulkUpdateSubStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Status Santri PPTQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <label class="form-label fw-bold">Pilih Status Baru</label>
                    <select id="bulk_sub_status_id" class="form-select form-select-solid">
                        <option value="">Pilih Status</option>
                        @foreach($studentSubStatuses ?? [] as $subStatus)
                        <option value="{{ $subStatus->id }}">{{ $subStatus->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info">
                    Ini akan memperbarui status <strong id="bulk_sub_status_count">0</strong> santri terpilih.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btn-submit-bulk-substatus" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        // Initialize DataTable
        var table = $('#table-student').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            searchDelay: 500,
            ajax: {
                url: '{{ route('student.index') }}',
                data: function(d) {
                    d.school_id = $('#filter_school').val();
                    d.classroom_id = $('#filter_class').val();
                    d.status = $('#filter_status').val();
                    d.search_name = $('#filter_name').val();
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
                    data: 'id',
                    name: 'id',
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid">' +
                            '<input class="form-check-input student-checkbox" type="checkbox" value="' + data + '">' +
                            '</div>';
                    }
                },
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
                    data: 'student',
                    name: 'student',
                    orderable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'parent',
                    name: 'parent',
                    orderable: false,
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'Belum ada';
                    },
                },
                {
                    data: 'school',
                    name: 'school',
                },
                {
                    data: 'saldo',
                    name: 'saldo',
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });

        function renderStudentClassroomMegaMenu(classes) {
            const container = $('#student_classroom_mega_menu');
            container.empty();

            if (!classes || classes.length === 0) {
                container.html('<div class="text-muted fs-7 p-3 text-center">Tidak ada kelas ditemukan</div>');
                return;
            }

            const groups = {};
            classes.forEach(c => {
                let match = (c.name || '').match(/^(\d+)/);
                let key = match ? match[1] : 'Lainnya';
                if (!groups[key]) groups[key] = [];
                groups[key].push(c);
            });

            const sortedKeys = Object.keys(groups).sort((a, b) => {
                const na = parseInt(a), nb = parseInt(b);
                if (isNaN(na) && isNaN(nb)) return a.localeCompare(b);
                if (isNaN(na)) return 1;
                if (isNaN(nb)) return -1;
                return na - nb;
            });

            const colCount = sortedKeys.length;
            let colClass = 'col-12';
            let menuWidth = '260px';
            if (colCount === 2) {
                colClass = 'col-6';
                menuWidth = '380px';
            } else if (colCount === 3) {
                colClass = 'col-4';
                menuWidth = '500px';
            } else if (colCount >= 4) {
                colClass = 'col-3';
                menuWidth = '620px';
            }
            container.css({ 'width': menuWidth });

            const resetBtn = $('<button type="button" class="btn btn-sm btn-light-primary w-100 fw-bold mb-3 student-classroom-item text-center rounded-2 py-2" data-id="" data-name="Semua Kelas" style="cursor: pointer;"><i class="fas fa-layer-group me-1" style="pointer-events: none;"></i><span style="pointer-events: none;">Semua Kelas</span></button>');
            container.append(resetBtn);

            const row = $('<div class="row g-2"></div>');
            sortedKeys.forEach(key => {
                const col = $(`<div class="${colClass}"></div>`);
                const headerTitle = isNaN(parseInt(key)) ? key : 'Kelas ' + key;
                col.append(`<h6 class="dropdown-header text-uppercase text-muted fw-bolder px-1 mb-2 fs-9 border-bottom pb-1">${headerTitle}</h6>`);
                const list = $('<div class="d-flex flex-column gap-1"></div>');
                groups[key].forEach(c => {
                    list.append(`<button type="button" class="btn btn-sm btn-light btn-active-light-primary text-start w-100 py-1.5 px-2 mb-1 rounded-2 student-classroom-item fs-8 fw-semibold d-flex align-items-center justify-content-between text-truncate" data-id="${c.id}" data-name="${c.name}" style="cursor: pointer; transition: all 0.15s ease-in-out; min-height: 30px;">
                        <span class="text-truncate" style="pointer-events: none;">${c.name}</span>
                        <i class="fas fa-check text-primary fs-9 d-none class-check-icon" style="pointer-events: none;"></i>
                    </button>`);
                });
                col.append(list);
                row.append(col);
            });
            container.append(row);
        }

        $(document).on('click', '#student_classroom_mega_menu .student-classroom-item', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#filter_class').val(id);
            if (id) {
                $('#filter_class_btn_text').html(`<i class="fas fa-chalkboard-user me-1 text-primary"></i> <span class="fw-bold">${name}</span>`);
            } else {
                $('#filter_class_btn_text').text('Semua Kelas');
            }

            $('#student_classroom_mega_menu .class-check-icon').addClass('d-none');
            $('#student_classroom_mega_menu .student-classroom-item').removeClass('active btn-primary text-white').addClass('btn-light');
            if (id) {
                $(this).addClass('active btn-primary text-white').removeClass('btn-light');
                $(this).find('.class-check-icon').removeClass('d-none');
            }

            const dropdownEl = document.getElementById('student_classroom_mega_menu');
            if (dropdownEl) {
                const bsDropdown = bootstrap.Dropdown.getInstance(document.getElementById('filter_class_btn'));
                if (bsDropdown) bsDropdown.hide();
            }

            table.ajax.reload();
        });

        $('#student_classroom_dropdown_container').on('show.bs.dropdown', function () {
            const $btn = $('#filter_class_btn');
            const $menu = $('#student_classroom_mega_menu');
            const btnOffset = $btn.offset();
            const menuWidth = $menu.outerWidth() || 500;
            const winWidth = $(window).width();
            if (btnOffset && (btnOffset.left + menuWidth > winWidth - 20)) {
                $menu.css({ 'left': 'auto', 'right': '0' });
            } else {
                $menu.css({ 'left': '0', 'right': 'auto' });
            }
        });

        // Populate filter_class on school change
        $('#filter_school').on('change', function() {
            var school_id = $(this).val();
            $('#filter_class').val('');
            $('#filter_class_btn_text').text('Semua Kelas');

            if (!school_id) {
                $('#student_classroom_mega_menu').html('<div class="text-muted fs-7 p-3 text-center">Pilih UPT terlebih dahulu</div>');
                table.ajax.reload();
                return;
            }

            var url = "{{ route('student.get-classroom', ':id') }}".replace(':id', school_id);
            $.get(url, function(data) {
                renderStudentClassroomMegaMenu(data || []);
                table.ajax.reload();
            });
        });

        // Reload DataTable on filter change
        $('#filter_status').on('change', function() {
            table.ajax.reload();
        });

        var filterStudentNameTimer;
        $('#filter_name').on('keyup input', function() {
            clearTimeout(filterStudentNameTimer);
            filterStudentNameTimer = setTimeout(function() {
                table.ajax.reload();
            }, 300);
        });

        // Select / Deselect All Checkboxes
        $('#check-all-student').on('click', function() {
            var checked = this.checked;
            $('.student-checkbox').each(function() {
                this.checked = checked;
            });
            toggleBulkDeleteButton();
        });

        // Individual Checkbox Click
        $('#table-student').on('click', '.student-checkbox', function() {
            var allChecked = $('.student-checkbox:checked').length === $('.student-checkbox').length;
            $('#check-all-student').prop('checked', allChecked);
            toggleBulkDeleteButton();
        });

        // Reset check all on DataTable draw/reload
        table.on('draw', function() {
            $('#check-all-student').prop('checked', false);
            toggleBulkDeleteButton();
        });

        function toggleBulkDeleteButton() {
            var checkedCount = $('.student-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#btn-bulk-delete-student').removeClass('d-none');
                $('#btn-bulk-update-substatus').removeClass('d-none');
                $('#bulk_sub_status_count').text(checkedCount);
            } else {
                $('#btn-bulk-delete-student').addClass('d-none');
                $('#btn-bulk-update-substatus').addClass('d-none');
            }
        }

        // Bulk Delete Button Click
        $('#btn-bulk-delete-student').on('click', function() {
            var selectedIds = [];
            $('.student-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Menghapus " + selectedIds.length + " data santri terpilih secara massal (soft delete)?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('student.bulk-delete') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: selectedIds
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Terhapus!',
                                    response.message,
                                    'success'
                                );
                                table.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message || 'Terjadi kesalahan saat menghapus data.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            var errMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                            Swal.fire(
                                'Gagal!',
                                errMsg,
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Handle Detail Siswa Click (Wide Modal)
        $(document).on('click', '.btn-detail-student', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var modalContent = $('#modalDetailSiswaContent');

            modalContent.html('<div class="p-10 text-center text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Memuat Detail Siswa...</div>');
            var detailModal = new bootstrap.Modal(document.getElementById('modalDetailSiswa'));
            detailModal.show();

            $.get(url, function(response) {
                if (response.html) {
                    modalContent.html(response.html);
                } else {
                    modalContent.html('<div class="p-6 text-danger text-center">Gagal memuat detail siswa.</div>');
                }
            }).fail(function() {
                modalContent.html('<div class="p-6 text-danger text-center">Terjadi kesalahan saat memuat data.</div>');
            });
        });

        // Handle Edit Siswa Click (Wide Modal)
        $(document).on('click', '.btn-edit-student, .btn-open-edit-modal', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = $(this).data('url') || "{{ route('student.edit', ':id') }}".replace(':id', id);
            var modalContent = $('#modalEditSiswaContent');

            // Hide detail modal if open
            var detailModalEl = document.getElementById('modalDetailSiswa');
            var detailModalInst = bootstrap.Modal.getInstance(detailModalEl);
            if (detailModalInst) {
                detailModalInst.hide();
            }

            modalContent.html('<div class="p-10 text-center text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Memuat Form Edit Siswa...</div>');
            var editModal = new bootstrap.Modal(document.getElementById('modalEditSiswa'));
            editModal.show();

            $.get(url, function(response) {
                if (response.html) {
                    modalContent.html(response.html);
                } else {
                    modalContent.html('<div class="p-6 text-danger text-center">Gagal memuat form edit.</div>');
                }
            }).fail(function() {
                modalContent.html('<div class="p-6 text-danger text-center">Terjadi kesalahan saat memuat form.</div>');
            });
        });

        // Handle Form Submit for Edit Siswa Modal
        $(document).on('submit', '#form-edit-student-modal', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btn-save-edit-student');
            var formData = new FormData(this);

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Perubahan');
                    if (response.success) {
                        var editModalEl = document.getElementById('modalEditSiswa');
                        var editModalInst = bootstrap.Modal.getInstance(editModalEl);
                        if (editModalInst) {
                            editModalInst.hide();
                        }
                        Swal.fire({
                            text: response.message || "Data Siswa berhasil diperbarui.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, Mengerti",
                            customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
                        });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            text: response.error || "Gagal memperbarui data siswa.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, Mengerti",
                            customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Perubahan');
                    var msg = "Terjadi kesalahan saat menyimpan data.";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        html: msg,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti",
                        customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
                    });
                }
            });
        });

        // Bulk Update Sub Status Submit
        $('#btn-submit-bulk-substatus').on('click', function() {
            var selectedIds = [];
            $('.student-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            var subStatusId = $('#bulk_sub_status_id').val();

            if (selectedIds.length === 0) {
                Swal.fire("Peringatan", "Pilih minimal satu santri", "warning");
                return;
            }

            if (!subStatusId) {
                Swal.fire("Peringatan", "Pilih status baru terlebih dahulu", "warning");
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: '{{ route('student.bulk-update-sub-status') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: selectedIds,
                    sub_status_id: subStatusId
                },
                success: function(response) {
                    btn.prop('disabled', false).text('Simpan');
                    $('#modalBulkUpdateSubStatus').modal('hide');
                    if (response.success) {
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, Mengerti",
                            customClass: { confirmButton: "btn btn-primary rounded-[24px]" }
                        });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire("Gagal", response.message, "error");
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Simpan');
                    Swal.fire("Gagal", "Terjadi kesalahan server", "error");
                }
            });
        });
    });
</script>
@endpush