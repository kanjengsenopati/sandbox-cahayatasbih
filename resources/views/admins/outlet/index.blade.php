@extends('layouts.master', ['title' => 'Scope Pondok Mart'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Scope Pondok Mart</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.index') }}" class="text-muted text-hover-primary">Pengguna</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Scope Pondok Mart</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl px-5">
            @include('admins.partials.tabs-akses')
            <!--begin::Card-->
            <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]" style="border-radius: 24px; border: none;">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="text-dark">Scope Pondok Mart</h3>
                    </div>
                    <x-action.create name="Outlet" label="Pondok Mart" action="{{ route('outlet.create') }}" />
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-outlet" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama Pondok Mart</th>
                                    <th>Pengguna Terpilih</th>
                                    <th>Kode</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-100px" style="width: 25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>

<!-- Modal Assign User -->
<div class="modal fade" id="modal_assign_outlet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded-[24px]" style="border-radius: 24px;">
            <form action="" method="POST" id="form_assign_outlet">
                @csrf
                <div class="modal-header">
                    <h2 class="fw-bolder" id="modal_title">Assign User</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <i class="fa-solid fa-xmark fs-4"></i>
                        </span>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                            <span class="required">Pilih Pengguna</span>
                        </label>
                        <select name="admin_ids[]" class="form-select form-select-solid" id="admin_select" data-control="select2" data-dropdown-parent="#modal_assign_outlet" data-placeholder="Pilih pengguna..." data-allow-clear="true" multiple="multiple">
                            @foreach($allAdmins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer text-center justify-content-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(() => {
            var table = $('#table-outlet').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('outlet.index') }}",
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 2 }, // Pengguna
                    { responsivePriority: 4, targets: 5 }, // Status
                    { responsivePriority: 5, targets: 6 }, // Aksi
                ],
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'users',
                        name: 'users',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                ]
            });

            // Assign User action
            $(document).on('click', '.btn-assign-user', function() {
                const outletId = $(this).data('id');
                const outletName = $(this).data('name');
                const users = $(this).data('users'); // Array of IDs

                $('#modal_title').text('Tugaskan Pengguna ke Pondok Mart: ' + outletName);
                $('#form_assign_outlet').attr('action', `/outlet/${outletId}/assign`);

                // Clear and set values in select2
                $('#admin_select').val(users).trigger('change');

                $('#modal_assign_outlet').modal('show');
            });
        })
</script>
@endpush
