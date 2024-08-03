@extends('layouts.master', ['title' => 'Detail Laporan Tagihan'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan Tagihan Pembayaran</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-bill.index') }}" class="text-muted text-hover-primary">Laporan
                            Tagihan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Detail {{ $billType->name ?? '' }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <div class="card mb-5">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <div class="card-title">
                        <h4 class="text-dark">{{ $billType->name ?? '' }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filter_form" method="GET">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_school_id" class="form-label">UPT</label>
                                <select name="school_id" class="form-select" id="filter_school_id">
                                    <option value="">Semua UPT</option>
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_classroom_id" class="form-label">Kelas</label>
                                <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                    <option value="">Semua Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-select" id="status">
                                    <option value="">Semua Status</option>
                                    <option value="PAID">Lunas</option>
                                    <option value="UNPAID">Belum Lunas</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex gap-2 mt-4">
                        <div class="card bg-light-primary bg-active-primary flex-grow-1">
                            <div class="card-body">
                                <div class="fw-bolder fs-5 text-gray-800">Target Pemasukkan</div>
                                <div class="text-primary fs-3 fw-bolder" id="total">Rp. 0</div>
                            </div>
                        </div>

                        <div class="card bg-light-success bg-active-success flex-grow-1">
                            <div class="card-body">
                                <div class="fw-bolder fs-5 text-gray-800">Realisasi Pemasukkan</div>
                                <div class="text-success fs-3 fw-bolder" id="total_paid">Rp. 0</div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="0" id="progress-bar"
                                        aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar-label" id="progress-bar-label">0%</div>
                                    </div>
                                </div>
                                <p class="text-center mt-3">
                                <div class="text-danger" id="total_unpaid">Rp. 0</div>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <div class="card-title"></div>
                    <div class=""></div>
                </div>
                <div class="card-body pt-0">
                    {{-- add button and select all to send bill whatsapp notification --}}
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            {{-- tambahkan checkbutton select all --}}
                            <div class="form-check form-check-sm">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">Pilih Semua</label>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="send-bill-whatsapp">Kirim
                                Notifikasi
                                WhatsApp</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-report-bill" class="table align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th class="min-w-125px">Item Pembayaran</th>
                                    <th class="min-w-125px">Total Tagihan</th>
                                    <th class="min-w-125px">Dibayar</th>
                                    <th class="min-w-125px">Sisa Tagihan</th>
                                    <th class="text-center min-w-70px" style="width: 22%">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function() {
        var table = initializeTable();

        function initializeTable(start_date = '', end_date = '') {
            return $('#table-report-bill').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('report-bill.show', $billType->id) }}",
                    data: function(d) {
                        d.type = 'bill';
                        d.school_id = $('#filter_school_id').val();
                        d.classroom_id = $('#filter_classroom_id').val();
                        d.status = $('#status').val();
                    }
                },
                language: {
                    processing: "Sedang memproses data, Silahkan ditunggu..."
                },
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Semua"]
                ],
                columns: [
                    { data: null, sortable: false, searchable: false, render: function(data, type, row, meta) {
                        return '<input type="checkbox" class="select-row">';
                    }},
                    { data: null, sortable: false, searchable: false, render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }},
                    { data: 'name', name: 'name' },
                    { data: 'total', name: 'total' },
                    { data: 'total_paid', name: 'total_paid' },
                    { data: 'total_unpaid', name: 'total_unpaid' },
                    { data: 'status', name: 'status', responsivePriority: -1 }
                ]
            });
        }

        function reloadTable(start_date = '', end_date = '') {
            table.destroy();
            table = initializeTable(start_date, end_date);
            getTotalBill(start_date, end_date);
        }

        function getTotalBill(start_date = '', end_date = '') {
            $.ajax({
                url: "{{ route('report-bill.show', $billType->id) }}",
                type: "GET",
                dataType: 'json',
                data: {
                    type: 'total',
                    school_id: $('#filter_school_id').val(),
                    classroom_id: $('#filter_classroom_id').val(),
                    status: $('#status').val(),
                },
                success: function(response) {
                    $('#total').text('Rp. ' + response.total);
                    $('#total_paid').text('Rp. ' + response.total_paid);
                    $('#progress-bar').css('width', response.realisasion_percentage);
                    $('#progress-bar-label').text(response.realisasion_percentage);
                    $('#total_unpaid').text('Belum Lunas: Rp. ' + response.total_unpaid);
                }
            });
        }

        $('#filter_school_id').on('change', function() {
            var school_id = $(this).val();
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: { school_id: school_id },
                success: function(response) {
                    $('#filter_classroom_id').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append('<option value="">Data tidak ditemukan</option>');
                    }
                }
            });
        });

        $('#filter_school_id, #filter_classroom_id, #status').on('change', function() {
            reloadTable();
        });

        $('#select-all').on('click', function() {
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        $('#table-report-bill tbody').on('change', 'input[type="checkbox"]', function() {
            if (!this.checked) {
                var el = $('#select-all').get(0);
                if (el && el.checked && ('indeterminate' in el)) {
                    el.indeterminate = true;
                }
            }
        });

        getTotalBill();
    });
</script>
@endpush