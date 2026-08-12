@extends('layouts.master', ['title' => 'Data Jenis Bayar'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Jenis Bayar</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a class="text-muted text-hover-primary">Jenis Bayar</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">List Jenis Bayar</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            @include('admins.partials.tabs-keuangan')
            <!--begin::Card-->
            <div class="card card-flush shadow-sm">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <h3>Filter Data</h3>
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <x-action.create name="Jenis Bayar" action="{{ route('bill-type.create') }}" />
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Filters-->
                    <div class="row g-5 mb-5 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-bold">Tahun Ajaran:</label>
                            <select class="form-select form-select-solid" id="filter_academic_year" data-placeholder="Pilih Tahun Ajaran">
                                <option value="">Semua Tahun</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-bold">Tipe Pembayaran:</label>
                            <select class="form-select form-select-solid" id="filter_type" data-placeholder="Pilih Tipe">
                                <option value="">Semua Tipe</option>
                                <option value="MONTHLY">Bulanan</option>
                                <option value="OTHER">Bebas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-6 fw-bold">Pos Bayar (UPT):</label>
                            <select class="form-select form-select-solid" id="filter_bill_item" data-placeholder="Pilih Pos Bayar">
                                <option value="">Semua Pos Bayar</option>
                                @foreach($billItems as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!--end::Filters-->

                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-bill-type" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Pos Bayar</th>
                                    <th>Nama Pembayaran</th>
                                    <th>Tipe</th>
                                    <th>Tahun</th>
                                    <th>Tarif Pembayaran</th>
                                    <th>Bank Pembayaran</th>
                                    <th class="text-center min-w-100px" style="width: 15%">Aksi</th>
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
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(() => {
        var table = $('#table-bill-type').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('bill-type.index') }}",
                data: function(d) {
                    d.academic_year_id = $('#filter_academic_year').val();
                    d.type = $('#filter_type').val();
                    d.bill_item_id = $('#filter_bill_item').val();
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
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'bill_item',
                    name: 'bill_item',
                    orderable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'academic_year.name',
                    name: 'academic_year.name',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'payment_rates',
                    name: 'payment_rates',
                    orderable: false
                },
                {
                    data: 'bank',
                    name: 'bank',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ],
            drawCallback: function() {
                // Re-initialize tooltips or other plugins if needed after table draw
                // KTMenu.createInstances(); // Example if using Metronic menus
            }
        });

        // Filter event listeners
        $('#filter_academic_year, #filter_type, #filter_bill_item').on('change', function() {
            table.draw();
        });

        // Reset Filter
        $('#btn-reset').on('click', function() {
            $('#filter_academic_year').val('').trigger('change');
            $('#filter_type').val('').trigger('change');
            $('#filter_bill_item').val('').trigger('change');
        });

        // Toggle Visibility Event Listener
        $(document).on('click', '.btn-toggle-visibility', function(e) {
            e.preventDefault();
            var btn = $(this);
            var actionUrl = btn.data('action');
            var isVisible = btn.data('visible');
            var actionText = isVisible ? 'menyembunyikan' : 'menampilkan';
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan " + actionText + " jenis bayar ini dari UI Entry Pembayaran dan PWA Wali Santri.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: actionUrl,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.code === 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Terjadi kesalahan pada sistem.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush