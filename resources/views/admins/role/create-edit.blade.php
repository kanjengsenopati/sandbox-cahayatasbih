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
                                    <label class="fs-6 fw-bold form-label" for="permissions">
                                        <span class="required">Pilih Permission Yang Akan Diberikan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Select the permissions for this role"></i>
                                    </label>

                                    <table class="table table-striped border rounded gy-5 gs-7">
                                        <thead>
                                            <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                <th>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input module-checkbox" type="checkbox"
                                                            data-module="all">
                                                    </div>
                                                </th>
                                                <th>Module</th>
                                                <th>Manage</th>
                                                <th>Create</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $modules = ['Role', 'Admin', 'Santri', 'Wali Santri', 'Sekolah', 'Bank',
                                            'Informasi', 'Metode Pembayaran', 'Menu Aplikasi', 'Kontak Bantuan',
                                            'Barang','Saldo Santri' ];
                                            @endphp

                                            @foreach ($modules as $module)
                                            <tr>
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input module-checkbox" type="checkbox"
                                                            data-module="{{ str_replace(' ', '', $module) }}">
                                                    </div>
                                                </td>
                                                <td>{{ ucfirst($module) }}</td>
                                                <td>
                                                    @if (in_array('Manage ' . $module, (array) $permissions))
                                                    @php
                                                    $manageKey = array_search('Manage ' . $module, $permissions);
                                                    @endphp
                                                    <div class="form-check form-check-inline">
                                                        <input
                                                            class="form-check-input permission-checkbox isscheck_{{ str_replace(' ', '', $module) }}"
                                                            type="checkbox" name="permissions[]"
                                                            value="{{ $manageKey }}" id="permission{{ $manageKey }}"
                                                            @if(in_array($manageKey, (array) $permissionValue)) checked
                                                            @endif>
                                                    </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (in_array('Create ' . $module, (array) $permissions))
                                                    @php
                                                    $createKey = array_search('Create ' . $module, $permissions);
                                                    @endphp
                                                    <div class="form-check form-check-inline">
                                                        <input
                                                            class="form-check-input permission-checkbox isscheck_{{ str_replace(' ', '', $module) }}"
                                                            type="checkbox" name="permissions[]"
                                                            value="{{ $createKey }}" id="permission{{ $createKey }}"
                                                            @if(in_array($createKey, (array) $permissionValue)) checked
                                                            @endif>
                                                    </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (in_array('Edit ' . $module, (array) $permissions))
                                                    @php
                                                    $editKey = array_search('Edit ' . $module, $permissions);
                                                    @endphp
                                                    <div class="form-check form-check-inline">
                                                        <input
                                                            class="form-check-input permission-checkbox isscheck_{{ str_replace(' ', '', $module) }}"
                                                            type="checkbox" name="permissions[]" value="{{ $editKey }}"
                                                            id="permission{{ $editKey }}" @if(in_array($editKey, (array)
                                                            $permissionValue)) checked @endif>
                                                    </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (in_array('Delete ' . $module, (array) $permissions))
                                                    @php
                                                    $deleteKey = array_search('Delete ' . $module, $permissions);
                                                    @endphp
                                                    <div class="form-check form-check-inline">
                                                        <input
                                                            class="form-check-input permission-checkbox isscheck_{{ str_replace(' ', '', $module) }}"
                                                            type="checkbox" name="permissions[]"
                                                            value="{{ $deleteKey }}" id="permission{{ $deleteKey }}"
                                                            @if(in_array($deleteKey, (array) $permissionValue)) checked
                                                            @endif>
                                                    </div>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
        $('.module-checkbox').on('change', function() {
            var module = $(this).data('module');
            if (module === 'all') {
                $('.permission-checkbox').prop('checked', this.checked);
            } else {
                $('.permission-checkbox.isscheck_' + module).prop('checked', this.checked);
                var allChecked = $('.permission-checkbox.isscheck_' + module).length === $('.permission-checkbox.isscheck_' + module + ':checked').length;
                $('.module-checkbox[data-module="' + module + '"]').prop('checked', allChecked);
            }
        });

        $('.permission-checkbox').on('change', function() {
            var module = $(this).data('module');
            var allChecked = $('.permission-checkbox.isscheck_' + module).length === $('.permission-checkbox.isscheck_' + module + ':checked').length;
            $('.module-checkbox[data-module="' + module + '"]').prop('checked', allChecked);
        });
    });
</script>
@endpush