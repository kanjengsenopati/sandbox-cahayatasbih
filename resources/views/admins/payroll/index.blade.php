@extends('layouts.master', ['title' => 'Data Penggajian Karyawan'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Penggajian Karyawan</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('payroll.index') }}" class="text-muted text-hover-primary">Payroll</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Daftar Slip Gaji</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            
            <!--begin::Card Pemrosesan-->
            <div class="card mb-8">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Kalkulasi Gaji Baru (Drafting)</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form id="form-process-payroll" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Mulai Periode</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Selesai Periode</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100" id="btn-process">
                                <i class="fa fa-cogs"></i> Kalkulasi Gaji Karyawan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Card Pemrosesan-->

            <!--begin::Card Tabel-->
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Daftar Slip Gaji Karyawan</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-payroll" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th width="3%">No</th>
                                    <th>Nama Karyawan</th>
                                    <th>Periode</th>
                                    <th>Gaji Bersih (Net)</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Card Tabel-->

        </div>
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(() => {
        const table = $('#table-payroll').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('payroll.index') }}",
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                }
            },
            columns: [
                {
                    data: 'id',
                    class: 'text-center',
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                { data: 'employee_name' },
                { data: 'period', class: 'text-center' },
                { data: 'net_salary_formatted', class: 'text-end' },
                { data: 'status_badge', class: 'text-center' },
                { data: 'btnAction', class: 'text-center' }
            ]
        });

        $('#form-process-payroll').on('submit', function (e) {
            e.preventDefault();
            const btn = $('#btn-process');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: "{{ route('payroll.process') }}",
                type: "POST",
                data: $(this).serialize(),
                success: (res) => {
                    btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> Kalkulasi Gaji Karyawan');
                    if (res.success) {
                        toastr.success(res.message);
                        table.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: (xhr) => {
                    btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> Kalkulasi Gaji Karyawan');
                    toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan sistem.');
                }
            });
        });
    });
</script>
@endpush
