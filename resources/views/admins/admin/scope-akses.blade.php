@extends('layouts.master', ['title' => 'Scope Akses Aplikasi'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Scope Akses Aplikasi</h1>
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
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Scope Akses Aplikasi</li>
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
        <div id="kt_content_container" class="container-xxl px-5">
            @include('admins.partials.tabs-akses')
            <!--begin::Card-->
            <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]" style="border-radius: 24px; border: none;">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title d-flex align-items-center gap-3">
                        <h3 class="text-dark m-0">Scope Akses Aplikasi</h3>
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1 ms-4">
                            <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" id="search-scope-akses" class="form-control form-control-solid w-250px ps-12 fs-7" placeholder="Cari Scope / Pengguna..." />
                        </div>
                        <!--end::Search-->
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-scope-akses" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                    <th width="5%">No</th>
                                    <th>Scope Akses</th>
                                    <th>Pengguna Terpilih</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scopes as $index => $scope)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold fs-6">{{ $scope['name'] }}</span>
                                    </td>
                                    <td>
                                        @if($scope['users']->count() > 0)
                                            @foreach($scope['users'] as $user)
                                                <span class="badge m-1" style="background-color: #8b5cf6; color: white;">{{ $user->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted italic" style="font-size: 11px;">Belum ada user yang ditugaskan</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-icon btn-active-light-primary w-30px h-30px btn-assign" 
                                            data-id="{{ $scope['id'] }}" 
                                            data-name="{{ $scope['name'] }}" 
                                            data-users="{{ json_encode($scope['users']->pluck('id')) }}">
                                            <i class="fa-solid fa-user-gear text-primary fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
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
<div class="modal fade" id="modal_assign_scope" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded-[24px]" style="border-radius: 24px;">
            <form action="" method="POST" id="form_assign_scope">
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
                        <select name="admin_ids[]" class="form-select form-select-solid" id="admin_select" data-control="select2" data-dropdown-parent="#modal_assign_scope" data-placeholder="Pilih pengguna..." data-allow-clear="true" multiple="multiple">
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
    $(document).ready(function() {
        $('#search-scope-akses').on('keyup input', function() {
            var value = $(this).val().toLowerCase();
            $('#table-scope-akses tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        $('.btn-assign').on('click', function() {
            const scopeId = $(this).data('id');
            const scopeName = $(this).data('name');
            const users = $(this).data('users'); // Array of IDs

            $('#modal_title').text('Tugaskan Pengguna ke Scope: ' + scopeName);
            $('#form_assign_scope').attr('action', `/admin/scope-akses/${scopeId}/assign`);

            // Clear and set values in select2
            $('#admin_select').val(users).trigger('change');

            $('#modal_assign_scope').modal('show');
        });
    });
</script>
@endpush
