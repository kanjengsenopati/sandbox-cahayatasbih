@extends('layouts.master', ['title' => 'Mutasi Pindah Unit'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Setting Lanjut Unit</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">Aplikasi</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Lanjut Unit</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('unit-transfer-config.create') }}" class="btn btn-sm btn-primary">Tambah Jalur Baru</a>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari..." />
                        </div>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_datatable">
                            <thead>
                                <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                    <th>Dari Unit</th>
                                    <th>Ke Unit Tujuan</th>
                                    <th>Masuk Kelas</th>
                                    <th>Tagihan Syarat</th>
                                    <th>Status</th>
                                    <th class="text-end min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    $(document).ready(function() {
        var table = $('#kt_datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('unit-transfer-config.index') }}",
            columns: [
                {data: 'from_school.name', name: 'fromSchool.name', defaultContent: '-'},
                {data: 'to_school.name', name: 'toSchool.name', defaultContent: '-'},
                {data: 'to_classroom.name', name: 'toClassroom.name', defaultContent: '-'},
                {data: 'bill_type.name', name: 'billType.name', defaultContent: '-'},
                {data: 'is_active', name: 'is_active'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('[data-kt-filter="search"]').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>
@endpush
