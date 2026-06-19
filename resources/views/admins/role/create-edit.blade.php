@extends('layouts.master', ['title' => 'Data Role'])
@section('content')
<!--begin::Content-->
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Role</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('role.index') }}" class="text-muted text-hover-primary">Role</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('role.create') ? 'Tambah Role' : 'Edit Role' }}
                    </li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ request()->routeIs('role.create') ? 'Tambah Role' : 'Edit Role' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form"
                                action="{{ request()->routeIs('role.create') ? route('role.store') : route('role.update', $role->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="name">
                                        <span class="required">Nama Role</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Nama Role Yang akan digunakan"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        value="{{ @$role->name ?? old('name') }}" />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-7">
                                    <div class="d-flex justify-content-between align-items-center mb-5">
                                        <label class="fs-6 fw-bold form-label mb-0" for="permissions">
                                            <span class="required">Pilih Permission Yang Akan Diberikan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Select the permissions for this role"></i>
                                        </label>
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input module-checkbox" type="checkbox"
                                                data-module="all" id="select_all_permissions">
                                            <label class="form-check-label fw-bold text-gray-700 fs-7" for="select_all_permissions">Pilih Semua</label>
                                        </div>
                                    </div>

                                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                        @php
                                        $modules = ['Role', 'Admin', 'Santri', 'Wali Santri',
                                        'Sekolah', 'Bank', 'Outlet',
                                        'Informasi', 'Metode Pembayaran', 'Menu Aplikasi', 'Kontak Bantuan',
                                        'Barang','Saldo Santri', 'Tabungan Santri', 'Jadwal', 'Tahfidz',
                                        'Pos Kasir', 'Tagihan', 'Status Tagihan', 'Perilaku Santri',
                                        'Prestasi Santri', 'Nilai Santri', 'Perizinan', 'Asrama',
                                        'PPDB', 'Mata Pelajaran', 'Tahun Ajaran', 'Semester', 'Kenaikan Kelas',
                                        'Pengaturan Aplikasi', 'Item Bayar', 'Jenis Bayar', 'Payroll', 'Laporan Presensi', 'Shift', 'Laporan Pos Kasir',
                                        'Laporan Pos Multi Outlet',
                                        'Laporan Rugi Laba',
                                        'Laporan Tagihan',
                                        'Laporan Santri', 'Laporan Tahfidz', 'Laporan Perilaku Siswa',
                                        'Laporan Saldo Santri', 'Laporan Fee Aplikasi', 'Laporan Transaksi',
                                        'Kelulusan Santri', 'Kategori Arus Kas', 'Arus Kas', 'Laporan Arus Kas',
                                        'Gelombang PPDB', 'Kartu Santri', 'Kartu Ujian', 'Petugas', 'Biometric'
                                        ];
                                        @endphp

                                        @foreach ($modules as $module)
                                        @php
                                            $moduleKey = str_replace(' ', '', $module);
                                            $managePerm = $module === 'Payroll' ? 'Manage Payroll' : 'Manage ' . $module;
                                            $createPerm = $module === 'Payroll' ? 'Create Payroll' : 'Create ' . $module;
                                            $editPerm   = $module === 'Payroll' ? 'Approve Payroll' : 'Edit ' . $module;
                                            $deletePerm = $module === 'Payroll' ? 'Pay Payroll' : 'Delete ' . $module;
                                        @endphp
                                        <div class="col">
                                            <div class="card h-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; border: 1px solid #f1f1f4; background: #ffffff;">
                                                <div class="card-body p-5">
                                                    <!-- Header Card: Module Name + Card Select All -->
                                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                                        <span class="fs-6 fw-bolder text-gray-800">{{ ucfirst($module) }}</span>
                                                        <div class="form-check form-check-custom form-check-solid">
                                                            <input class="form-check-input module-checkbox" type="checkbox"
                                                                data-module="{{ $moduleKey }}" id="select_module_{{ $moduleKey }}">
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- CRUD Checkboxes stacked vertically -->
                                                    <div class="d-flex flex-column gap-2 mt-3">
                                                        <!-- Read Checkbox -->
                                                        @if (in_array($managePerm, (array) $permissions))
                                                            @php $manageKey = array_search($managePerm, $permissions); @endphp
                                                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                                <input class="form-check-input permission-checkbox isscheck_{{ $moduleKey }}"
                                                                    type="checkbox" name="permissions[]" data-module="{{ $moduleKey }}"
                                                                    value="{{ $manageKey }}" id="permission{{ $manageKey }}"
                                                                    @if(in_array($manageKey, (array) $permissionValue)) checked @endif>
                                                                <label class="form-check-label text-gray-600 fs-7" for="permission{{ $manageKey }}">Read</label>
                                                            </div>
                                                        @endif

                                                        <!-- Create Checkbox -->
                                                        @if (in_array($createPerm, (array) $permissions))
                                                            @php $createKey = array_search($createPerm, $permissions); @endphp
                                                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                                <input class="form-check-input permission-checkbox isscheck_{{ $moduleKey }}"
                                                                    type="checkbox" name="permissions[]" data-module="{{ $moduleKey }}"
                                                                    value="{{ $createKey }}" id="permission{{ $createKey }}"
                                                                    @if(in_array($createKey, (array) $permissionValue)) checked @endif>
                                                                <label class="form-check-label text-gray-600 fs-7" for="permission{{ $createKey }}">Create</label>
                                                            </div>
                                                        @endif

                                                        <!-- Edit Checkbox -->
                                                        @if (in_array($editPerm, (array) $permissions))
                                                            @php $editKey = array_search($editPerm, $permissions); @endphp
                                                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                                <input class="form-check-input permission-checkbox isscheck_{{ $moduleKey }}"
                                                                    type="checkbox" name="permissions[]" data-module="{{ $moduleKey }}"
                                                                    value="{{ $editKey }}" id="permission{{ $editKey }}"
                                                                    @if(in_array($editKey, (array) $permissionValue)) checked @endif>
                                                                <label class="form-check-label text-gray-600 fs-7" for="permission{{ $editKey }}">Edit</label>
                                                            </div>
                                                        @endif

                                                        <!-- Delete Checkbox -->
                                                        @if (in_array($deletePerm, (array) $permissions))
                                                            @php $deleteKey = array_search($deletePerm, $permissions); @endphp
                                                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                                <input class="form-check-input permission-checkbox isscheck_{{ $moduleKey }}"
                                                                    type="checkbox" name="permissions[]" data-module="{{ $moduleKey }}"
                                                                    value="{{ $deleteKey }}" id="permission{{ $deleteKey }}"
                                                                    @if(in_array($deleteKey, (array) $permissionValue)) checked @endif>
                                                                <label class="form-check-label text-gray-600 fs-7" for="permission{{ $deleteKey }}">Delete</label>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!--begin::Actions-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('role.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                    <!--end::Button-->
                                </div>
                                <!--end::Actions-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Contacts-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Contacts App- Add New Contact-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Function to update local module-checkbox based on its permission checkboxes
        function updateModuleCheckbox(module) {
            var total = $('.permission-checkbox.isscheck_' + module).length;
            var checked = $('.permission-checkbox.isscheck_' + module + ':checked').length;
            var moduleCheckbox = $('.module-checkbox[data-module="' + module + '"]');
            if (total > 0 && total === checked) {
                moduleCheckbox.prop('checked', true);
            } else {
                moduleCheckbox.prop('checked', false);
            }
        }

        // Initialize individual module checkboxes on page load
        $('.module-checkbox').each(function() {
            var module = $(this).data('module');
            if (module !== 'all') {
                updateModuleCheckbox(module);
            }
        });

        // Initialize master "all" checkbox on page load
        var totalAll = $('.permission-checkbox').length;
        var checkedAll = $('.permission-checkbox:checked').length;
        if (totalAll > 0 && totalAll === checkedAll) {
            $('.module-checkbox[data-module="all"]').prop('checked', true);
        }

        // Event handler for module/select-all checkboxes
        $('.module-checkbox').on('change', function() {
            var module = $(this).data('module');
            if (module === 'all') {
                $('.permission-checkbox').prop('checked', this.checked);
                $('.module-checkbox').not('[data-module="all"]').prop('checked', this.checked);
            } else {
                $('.permission-checkbox.isscheck_' + module).prop('checked', this.checked);
                updateModuleCheckbox(module);
                
                // Update master "all" checkbox state
                var allChecked = $('.permission-checkbox').length === $('.permission-checkbox:checked').length;
                $('.module-checkbox[data-module="all"]').prop('checked', allChecked);
            }
        });

        // Event handler for individual permission checkboxes
        $('.permission-checkbox').on('change', function() {
            var module = $(this).data('module');
            updateModuleCheckbox(module);
            
            // Update master "all" checkbox state
            var allChecked = $('.permission-checkbox').length === $('.permission-checkbox:checked').length;
            $('.module-checkbox[data-module="all"]').prop('checked', allChecked);
        });
    });
</script>
@endpush