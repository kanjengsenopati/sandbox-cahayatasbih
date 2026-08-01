@extends('layouts.master', ['title' => 'Data User'])
@push('css')
<style>
    .card.mb-5, .custom-card-migration {
        border-radius: 24px !important;
        box-shadow: 0 8px 30px rgba(0,0,0,0.04) !important;
        border: none !important;
    }
    .nav-line-tabs .nav-item .nav-link {
        color: #64748b !important;
        font-weight: 600;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.5rem;
    }
    .nav-line-tabs .nav-item .nav-link.active, .nav-line-tabs .nav-item .nav-link:hover {
        color: #2563EB !important;
        border-bottom-color: #2563EB !important;
    }
</style>
@endpush
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Wali Santri
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-end ms-4"></span>
                    <!--end::Separator-->
                </h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('user.index') }}" class="text-muted text-hover-primary">Wali</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Wali Santri</li>
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
            @if(session('import_skipped'))
            <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-6 mb-5" style="border-radius: 24px;">
                <!--begin::Icon-->
                <span class="position-absolute top-0 end-0 m-3">
                    <button type="button" class="btn btn-icon btn-sm btn-active-light-warning" data-bs-dismiss="alert">
                        <i class="fas fa-times text-warning fs-4"></i>
                    </button>
                </span>
                <span class="svg-icon svg-icon-2hx svg-icon-warning me-4 mb-5 mb-sm-0">
                    <i class="fas fa-exclamation-triangle fs-1 text-warning"></i>
                </span>
                <!--end::Icon-->

                <!--begin::Wrapper-->
                <div class="d-flex flex-column pe-0 pe-sm-10 w-100">
                    <!--begin::Title-->
                    <h4 class="fw-bold text-warning mb-2">Beberapa Baris Dilewati (Data Duplikat)</h4>
                    <!--end::Title-->
                    <!--begin::Content-->
                    <span class="text-gray-700 fs-6 mb-4">
                        Proses impor mendeteksi beberapa data ganda (nomor WA/HP sudah terdaftar). Baris-baris berikut <strong>dilewati otomatis</strong> untuk mencegah kerusakan data:
                    </span>
                    <div class="table-responsive">
                        <table class="table table-sm table-row-dashed table-row-gray-300 align-middle">
                            <thead>
                                <tr class="fw-bolder text-warning fs-7 text-uppercase gs-0">
                                    <th>Baris Excel</th>
                                    <th>Nama</th>
                                    <th>No WA (Phone)</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold fs-7">
                                @foreach(session('import_skipped') as $skipped)
                                    <tr>
                                        <td>Baris {{ $skipped['row'] }}</td>
                                        <td>{{ $skipped['name'] }}</td>
                                        <td><code>{{ $skipped['phone'] }}</code></td>
                                        <td><span class="badge badge-light-danger">{{ $skipped['reason'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Wrapper-->
            </div>
            @endif

            <!--begin::Tabs-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6 fw-bold">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_daftar_wali">Daftar Wali Santri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_migrasi_status">Migrasi Status Massal</a>
                </li>
            </ul>
            <!--end::Tabs-->

            <!--begin::Tab Content-->
            <div class="tab-content" id="userTabContent">
                <!--begin::Tab Pane 1-->
                <div class="tab-pane fade show active" id="kt_tab_daftar_wali" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card mb-5">
                        <!--begin::Card header-->
                        <div
                            class="card-header d-flex flex-column flex-sm-row align-items-end justify-content-between border-0 pt-6">
                            <!-- Filter Section -->
                            <div class="d-flex flex-wrap gap-4 align-items-end mb-4 mb-sm-0">
                                <form action="#" id="form-filter" method="get">
                                    <input type="text" hidden id="type" name="type" required>
                                    <div class="d-flex flex-wrap gap-4 align-items-end">
                                         <div>
                                             <label class="form-label">Status</label>
                                             <select name="status" class="form-select form-select-sm" id="filter_status">
                                                 <option value="">Semua</option>
                                                 <option value="ACTIVE">Aktif</option>
                                                 <option value="INACTIVE">Tidak Aktif</option>
                                                 <option value="VERIFICATION">Butuh Verifikasi</option>
                                             </select>
                                         </div>
                                         <div>
                                             <label class="form-label">Nama / No. HP</label>
                                             <div class="d-flex align-items-center position-relative">
                                                 <span class="svg-icon svg-icon-1 position-absolute ms-3">
                                                     <i class="fas fa-search text-gray-400"></i>
                                                 </span>
                                                 <input type="text" id="filter_name" name="search_name" class="form-control form-control-sm form-control-solid ps-9" placeholder="Cari Nama / No. HP..." style="width: 220px;" />
                                             </div>
                                         </div>
                                     </div>
                                 </form>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex flex-wrap gap-4 align-items-end">
                                <button type="button" id="btn-bulk-delete-user" class="btn btn-danger btn-sm d-none">
                                    <i class="fa fa-trash me-2"></i> Hapus Terpilih
                                </button>
                                <x-action.create name="Wali Santri" action="{{ route('user.create') }}" />
                            </div>

                            <!-- Stats Cards -->
                            <div class="d-flex flex-wrap gap-4 mt-4 w-100">
                                <!-- Card for "Wali Santri Aktif" -->
                                <div class="card bg-light-success flex-grow-1">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-user-check text-success fs-2"></i> <!-- Ikon untuk aktif -->
                                        </div>
                                        <div>
                                            <div class="fw-bolder fs-5 text-gray-800">Wali Santri Aktif</div>
                                            <div class="text-success fs-3 fw-bolder" id="active-parents">0</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card for "Wali Santri Tidak Aktif" -->
                                <div class="card bg-light-danger flex-grow-1">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-user-times text-danger fs-2"></i> <!-- Ikon untuk tidak aktif -->
                                        </div>
                                        <div>
                                            <div class="fw-bolder fs-5 text-gray-800">Wali Santri Tidak Aktif</div>
                                            <div class="text-danger fs-3 fw-bolder" id="inactive-parents">0</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card for "Wali Santri Butuh Verifikasi" -->
                                <div class="card bg-light-warning flex-grow-1">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-user-shield text-warning fs-2"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bolder fs-5 text-gray-800">Wali Santri Butuh Verifikasi</div>
                                            <div class="text-warning fs-3 fw-bolder" id="verification-parents">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-user" class="table table-striped border rounded gy-5 gs-7">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                            <th width="3%">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" id="check-all-user">
                                                </div>
                                            </th>
                                            <th width="3%">No</th>
                                            <th>Nama</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Status</th>
                                            <th>Status Jamaah</th>
                                            <th>Akses</th>
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
                </div>
                <!--end::Tab Pane 1-->

                <!--begin::Tab Pane 2-->
                <div class="tab-pane fade" id="kt_tab_migrasi_status" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card mb-5 custom-card-migration">
                        <!--begin::Card header-->
                        <div class="card-header d-flex flex-column flex-sm-row align-items-center justify-content-between border-0 pt-6">
                            <!-- Filter Section -->
                            <div class="d-flex flex-wrap gap-4 align-items-center mb-4 mb-sm-0">
                                <div>
                                    <label class="form-label text-slate-600 fw-bold">Filter Status Asal</label>
                                    <select name="migration_origin_status" class="form-select form-select-sm" id="filter_migration_origin_status">
                                        <option value="" selected>Semua</option>
                                        <option value="JAMAAH">Jamaah</option>
                                        <option value="NON_JAMAAH">Non Jamaah</option>
                                        <option value="MUKIMIN">Mukimin</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Action Section -->
                            <div class="d-flex flex-wrap gap-4 align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label text-slate-600 fw-bold mb-0 me-2">Status Target:</label>
                                    <select name="migration_target_status" class="form-select form-select-sm w-150px" id="migration_target_status">
                                        <option value="JAMAAH" selected>Jamaah</option>
                                        <option value="NON_JAMAAH">Non Jamaah</option>
                                        <option value="MUKIMIN">Mukimin</option>
                                    </select>
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-apply-bulk-migration">
                                        <i class="fas fa-check me-1"></i> Terapkan Semua
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!--end::Card header-->
                        
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-user-migration" class="table table-striped border rounded gy-5 gs-7 align-middle w-100">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                            <th width="3%">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" id="check-all-migration">
                                                </div>
                                            </th>
                                            <th width="5%">No</th>
                                            <th>Nama</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Status</th>
                                            <th>Status Jamaah</th>
                                            <th>Akses</th>
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
                </div>
                <!--end::Tab Pane 2-->
            </div>
            <!--end::Tab Content-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->

<!-- Modal Reset Password Success -->
<div class="modal fade" id="modalResetPasswordSuccess" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-450px">
        <div class="modal-content" style="border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0 pt-6 px-6">
                <h5 class="modal-title fw-bolder text-dark">Informasi Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-6 px-6">
                <div class="mb-4">
                    <div class="symbol symbol-60px symbol-circle bg-light-success d-inline-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px;">
                        <i class="fa fa-check-circle fs-1 text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bolder text-gray-800 mb-3" id="resetPasswordUserName">Wali Santri</h4>
                <div class="alert alert-custom bg-light-success p-4 rounded-3 text-success fw-bolder fs-5 mb-0" style="border: 1px dashed #10B981;">
                    Berhasil Reset Password 12345678
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-6 px-6 justify-content-center">
                <button type="button" class="btn btn-primary px-8" style="border-radius: 12px;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        var table = $('#table-user').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            searchable: true,
            ajax: {
                url: '{{ route('user.index') }}',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.type = 'table';
                    d.search_name = $('#filter_name').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
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
                            '<input class="form-check-input user-checkbox" type="checkbox" value="' + data + '">' +
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
                    data: 'name',
                    name: 'name',
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        if (!data) return 'N/A';
                        try {
                            var date = new Date(data);
                            var day = String(date.getDate()).padStart(2, '0');
                            var months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
                            var month = months[date.getMonth()];
                            var year = date.getFullYear();
                            return day + '-' + month + '-' + year;
                        } catch (e) {
                            return data;
                        }
                    }
                },
                {
                    data: 'gender',
                    name: 'gender',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        let label = '';
                        
                        if (data == 'L') {
                            badgeClass = 'badge-light-primary';
                            label = 'Laki-laki';
                        } else if (data == 'P') {
                            badgeClass = 'badge-light-danger';
                            label = 'Perempuan';
                        } else {
                            badgeClass = 'badge-light-warning';
                            label = 'Tidak diketahui';
                        }
                        
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    },
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'jamaah_status',
                    name: 'jamaah_status',
                },
                {
                    data: 'last_login',
                    name: 'last_login',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'No actions available'; // Null handler
                    }
                },
            ]
        });

        $('#filter_status').on('change', function() {
            table.ajax.reload();
        });

        var filterUserNameTimer;
        $('#filter_name').on('keyup input', function() {
            clearTimeout(filterUserNameTimer);
            filterUserNameTimer = setTimeout(function() {
                table.ajax.reload();
            }, 300);
        });

        // Select / Deselect All User Checkboxes
        $('#check-all-user').on('click', function() {
            var checked = this.checked;
            $('.user-checkbox').each(function() {
                this.checked = checked;
            });
            toggleUserBulkDeleteButton();
        });

        // Individual User Checkbox Click
        $('#table-user').on('click', '.user-checkbox', function() {
            var allChecked = $('.user-checkbox:checked').length === $('.user-checkbox').length;
            $('#check-all-user').prop('checked', allChecked);
            toggleUserBulkDeleteButton();
        });

        // Reset check all on DataTable draw/reload
        table.on('draw', function() {
            $('#check-all-user').prop('checked', false);
            toggleUserBulkDeleteButton();
        });

        function toggleUserBulkDeleteButton() {
            var checkedCount = $('.user-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#btn-bulk-delete-user').removeClass('d-none');
            } else {
                $('#btn-bulk-delete-user').addClass('d-none');
            }
        }

        // Bulk Delete User Button Click
        $('#btn-bulk-delete-user').on('click', function() {
            var selectedIds = [];
            $('.user-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Menghapus " + selectedIds.length + " data wali santri terpilih secara massal (soft delete)?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('user.bulk-delete') }}',
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

        // Initialize Migration Datatable
        var migrationTable = $('#table-user-migration').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            searchable: true,
            ajax: {
                url: '{{ route('user.index') }}',
                data: function(d) {
                    d.status = '';
                    d.jamaah_status = $('#filter_migration_origin_status').val();
                    d.type = 'table';
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    data: 'id',
                    sortable: false,
                    searchable: false,
                    width: '3%',
                    render: function(data, type, row) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input migration-checkbox" type="checkbox" value="${data}">
                            </div>
                        `;
                    }
                },
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    width: '5%',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'N/A';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        if (!data) return 'N/A';
                        try {
                            var date = new Date(data);
                            var day = String(date.getDate()).padStart(2, '0');
                            var months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
                            var month = months[date.getMonth()];
                            var year = date.getFullYear();
                            return day + '-' + month + '-' + year;
                        } catch (e) {
                            return data;
                        }
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'jamaah_status',
                    name: 'jamaah_status',
                },
                {
                    data: 'last_login',
                    name: 'last_login',
                }
            ]
        });

        $('#filter_migration_origin_status').on('change', function() {
            migrationTable.ajax.reload();
            $('#check-all-migration').prop('checked', false);
        });

        // Check All checkbox behavior
        $('#check-all-migration').on('change', function() {
            $('.migration-checkbox').prop('checked', this.checked);
        });

        // Individual checkbox change behavior
        $(document).on('change', '.migration-checkbox', function() {
            var allCheckboxes = $('.migration-checkbox');
            var checkedCheckboxes = $('.migration-checkbox:checked');
            if (allCheckboxes.length > 0 && checkedCheckboxes.length === allCheckboxes.length) {
                $('#check-all-migration').prop('checked', true);
            } else {
                $('#check-all-migration').prop('checked', false);
            }
        });

        // Apply Bulk Migration button click
        $('#btn-apply-bulk-migration').on('click', function() {
            var selectedIds = [];
            $('.migration-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih minimal satu Wali Santri terlebih dahulu.',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                return;
            }

            var targetStatus = $('#migration_target_status').val();
            var targetStatusLabel = $('#migration_target_status option:selected').text();

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Mengubah status keanggotaan ${selectedIds.length} Wali Santri menjadi "${targetStatusLabel}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terapkan!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-active-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('user.bulk-update-status') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: selectedIds,
                            jamaah_status: targetStatus
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                });
                                // Reload tables
                                table.ajax.reload();
                                migrationTable.ajax.reload();
                                // Reset select all checkbox
                                $('#check-all-migration').prop('checked', false);
                                // Refresh active/inactive counters
                                refreshCounters();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message || 'Terjadi kesalahan saat memproses data.',
                                    customClass: {
                                        confirmButton: 'btn btn-danger'
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            var errMsg = 'Terjadi kesalahan sistem.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: errMsg,
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    });
                }
            });
        });

         // Handler for Verifikasi button click
         $(document).on('click', '.btn-verify', function() {
             var url = $(this).data('url');
             Swal.fire({
                 title: 'Verifikasi Wali Santri?',
                 text: "Apakah Anda yakin ingin menyetujui data Wali Santri ini?",
                 icon: 'question',
                 showCancelButton: true,
                 confirmButtonColor: '#10B981',
                 cancelButtonColor: '#3085d6',
                 confirmButtonText: 'Ya, Verifikasi!',
                 cancelButtonText: 'Batal',
                 customClass: {
                     confirmButton: 'btn btn-success',
                     cancelButton: 'btn btn-secondary'
                 }
             }).then((result) => {
                 if (result.isConfirmed) {
                     $.ajax({
                         url: url,
                         type: 'POST',
                         data: {
                             _token: '{{ csrf_token() }}'
                         },
                         success: function(response) {
                             if (response.status === 'success') {
                                 Swal.fire({
                                     icon: 'success',
                                     title: 'Berhasil',
                                     text: response.message,
                                     customClass: {
                                         confirmButton: 'btn btn-success'
                                     }
                                 });
                                 table.ajax.reload();
                                 refreshCounters();
                             } else {
                                 Swal.fire({
                                     icon: 'error',
                                     title: 'Gagal',
                                     text: response.message,
                                     customClass: {
                                         confirmButton: 'btn btn-danger'
                                     }
                                 });
                             }
                         },
                         error: function(xhr) {
                             var errMsg = 'Terjadi kesalahan saat memproses verifikasi.';
                             if (xhr.responseJSON && xhr.responseJSON.message) {
                                 errMsg = xhr.responseJSON.message;
                             }
                             Swal.fire({
                                 icon: 'error',
                                 title: 'Gagal',
                                 text: errMsg,
                                 customClass: {
                                     confirmButton: 'btn btn-danger'
                                 }
                             });
                         }
                     });
                 }
             });
         });

         // Handler for Reset Password button click
         $(document).on('click', '.btn-reset-password', function() {
             var url = $(this).data('url');
             var userName = $(this).data('name') || 'Wali Santri';

             Swal.fire({
                 title: 'Reset Password?',
                 text: "Apakah Anda yakin ingin mereset password untuk Wali Santri '" + userName + "'?",
                 icon: 'warning',
                 showCancelButton: true,
                 confirmButtonColor: '#f59e0b',
                 cancelButtonColor: '#64748b',
                 confirmButtonText: 'Ya, Reset Password!',
                 cancelButtonText: 'Batal',
                 customClass: {
                     confirmButton: 'btn btn-warning text-white',
                     cancelButton: 'btn btn-secondary'
                 }
             }).then((result) => {
                 if (result.isConfirmed) {
                     $.ajax({
                         url: url,
                         type: 'POST',
                         data: {
                             _token: '{{ csrf_token() }}'
                         },
                         success: function(response) {
                             if (response.status === 'success') {
                                 $('#resetPasswordUserName').text(response.user_name || userName);
                                 $('#modalResetPasswordSuccess').modal('show');
                                 table.ajax.reload();
                             } else {
                                 Swal.fire({
                                     icon: 'error',
                                     title: 'Gagal',
                                     text: response.message || 'Gagal mereset password.',
                                     customClass: {
                                         confirmButton: 'btn btn-danger'
                                     }
                                 });
                             }
                         },
                         error: function(xhr) {
                             var errMsg = 'Terjadi kesalahan saat mereset password.';
                             if (xhr.responseJSON && xhr.responseJSON.message) {
                                 errMsg = xhr.responseJSON.message;
                             }
                             Swal.fire({
                                 icon: 'error',
                                 title: 'Gagal',
                                 text: errMsg,
                                 customClass: {
                                     confirmButton: 'btn btn-danger'
                                 }
                             });
                         }
                     });
                 }
             });
         });

         // Function to refresh statistic counters
         function refreshCounters() {
             $.ajax({
                 url: '{{ route('user.index') }}',
                 type: 'GET',
                 data: {
                     type: 'statistic'
                 },
                 success: function(response) {
                     $('#active-parents').text(response.active);
                     $('#inactive-parents').text(response.inactive);
                     $('#verification-parents').text(response.verification);
                 }
             });
         }

        // Initial fetch of counters
        refreshCounters();
    });
</script>
@endpush