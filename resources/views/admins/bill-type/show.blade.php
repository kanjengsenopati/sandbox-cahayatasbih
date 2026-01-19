@extends('layouts.master', ['title' => 'Data Tarif Pembayaran'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">

        <!--begin::Header-->
        <div class="d-flex flex-stack mb-5">
            <!--begin::Title-->
            <div class="d-flex align-items-center">
                <div class="symbol symbol-50px me-3">
                    <span class="symbol-label bg-light-primary">
                        <i class="fas fa-file-invoice text-primary fs-2"></i>
                    </span>
                </div>
                <div>
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        Data Tarif Pembayaran
                    </h1>
                    <span class="text-muted fw-bold fs-7">
                        List Tarif Pembayaran: <span class="text-primary">{{ $billType->name }}</span>
                    </span>
                </div>
            </div>
            <!--end::Title-->

            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                <li class="breadcrumb-item text-muted">
                    <a href="#" class="text-muted text-hover-primary">Tipe Pembayaran</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-300 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-dark">{{ $billType->name }}</li>
            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--end::Header-->

        <!--begin::Card-->
        <div class="card shadow-sm rounded-4 border-0">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1 gap-3">
                        <h3 class="fw-bolder m-0 text-dark">Daftar Tagihan</h3>
                        <span class="text-gray-400">|</span>
                        <div class="w-200px">
                            <select class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" id="filter_academic_year" data-placeholder="Filter Tahun Ajaran">
                                <option value="">Semua Tahun</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->status ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <a href="{{ route('bill-type.index') }}" class="btn btn-light btn-sm rounded-pill hover-scale me-2">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                    <a href="{{ route('payment-rate.create', ['bill_type_id' => $billType->id]) }}" class="btn btn-primary btn-sm rounded-pill hover-scale">
                        <i class="fas fa-plus me-2"></i> Tambah Tagihan
                    </a>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table id="table-bill-type" class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead class="bg-light">
                            <tr class="fw-bolder text-muted fs-7 text-uppercase">
                                <th class="ps-4 min-w-50px rounded-start">No</th>
                                <th class="min-w-150px">Sekolah</th>
                                <th class="min-w-150px">Kelas</th>
                                <th class="min-w-125px">Total Tagihan</th>
                                <th class="text-center min-w-100px rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fw-bold text-gray-600">
                            <!-- Data populated by DataTables -->
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
@endsection

@push('js')
<script>
    $(document).ready(function() {
        var table = $('#table-bill-type').DataTable({
            processing: true,
            serverSide: true,
            ordering: false, // Maintain original simplified ordering
            ajax: {
                url: "{{ route('bill-type.show', $billType->id) }}",
                data: function(d) {
                    d.academic_year_id = $('#filter_academic_year').val();
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "Tidak ada data tersedia",
            },
            dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            columns: [{
                    data: null,
                    sortable: false,
                    searchable: false,
                    className: 'ps-4',
                    render: function(data, type, row, meta) {
                        return '<span class="text-gray-800 fw-bolder">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
                    }
                },
                {
                    data: 'school',
                    name: 'school',
                    render: function(data) {
                        return '<div class="d-flex flex-column"><span class="text-gray-800 fw-bolder mb-1">' + data + '</span></div>';
                    }
                },
                {
                    data: 'classrooms',
                    name: 'classrooms',
                    // Rendered as badges by controller
                },
                {
                    data: 'amount',
                    name: 'amount',
                    render: function(data) {
                        return '<span class="badge badge-light-success fs-7 fw-bolder">' + data + '</span>';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ]
        });

        $('#filter_academic_year').change(function() {
            table.draw();
        });
    });
</script>
@endpush