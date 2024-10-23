@extends('layouts.master', ['title' => 'Data Riwayat Saldo'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Saldo Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('saldo-history.index') }}" class="text-dark text-hover-primary">Data Saldo
                            Siswa</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List Riwayat Saldo</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <a href="{{ route('saldo-bank.index') }}" class="btn btn-sm btn-primary me-2">
                            <i class="fa fa-gear"></i> Setting List Bank
                        </a>
                        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#importSaldoModal">
                            <i class="fa fa-upload"></i> Import Saldo
                        </a>
                    </div>
                    <div>
                        <x-action.create name="Saldo Santri" label="Penyesuaian Saldo"
                            action="{{ route('saldo-history.create') }}" />
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Tabs-->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">

                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="top-up-saldo-tab" data-bs-toggle="tab" href="#top-up-saldo"
                                role="tab" aria-controls="top-up-saldo" aria-selected="false">Top Up Saldo</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="saldo-history-tab" data-bs-toggle="tab" href="#saldo-history"
                                role="tab" aria-controls="saldo-history" aria-selected="true">Riwayat Saldo</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade " id="saldo-history" role="tabpanel"
                            aria-labelledby="saldo-history-tab">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-saldo-history" class="table align-middle table-row-dashed ">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Tanggal</th>
                                            <th class="min-w-100px" style="width: 22%">Siswa</th>
                                            <th class="min-w-100px" style="width: 22%">Jumlah</th>
                                            <th class="min-w-100px" style="width: 22%">Status</th>
                                            <th class="min-w-100px" style="width: 22%">Keterangan</th>
                                            {{-- <th class="text-center min-w-100px" style="width: 22%">Aksi</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        <div class="tab-pane fade show active" id="top-up-saldo" role="tabpanel"
                            aria-labelledby="top-up-saldo-tab">
                            <!--begin::Top Up Form-->
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table id="table-transfer" class="table align-middle table-row-dashed ">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Siswa</th>
                                            <th>Jumlah Pembayaran</th>
                                            <th>Kode Unik</th>
                                            <th>Bukti Transfer</th>
                                            <th>Status</th>
                                            <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold"></tbody>
                                </table>
                            </div>
                            <!--end::Table-->

                            <!--end::Top Up Form-->
                        </div>
                    </div>
                    <!--end::Tabs-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="importSaldoModal" tabindex="-1" aria-labelledby="importSaldoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importSaldoModalLabel">Import Data Saldo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('saldo-history.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="saldoFile" class="form-label">Pilih File Saldo</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx" required>
                        <div class="form-text">Hanya file XLSX yang sesuai dengan template yang diperbolehkan.</div>
                    </div>
                    <div class="mb-3">
                        <h6>Template Import Data</h6>
                        <p>Silakan unduh template berikut untuk mengimpor data saldo:</p>
                        <a href="{{ asset('assets\media\template\import\Template Data Import Saldo.xlsx') }}"
                            class="btn btn-sm btn-secondary" download>Unduh
                            Template</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
            var table = $('#table-saldo-history').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('saldo-history.index') }}",
                    data: function(d) {
                        d.type = 'saldo';
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
                        data: 'date',
                        name: 'date',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                        }
                    },
                    {
                        data: 'student.name',
                        name: 'student.name',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                        }
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                         return data ? data : 'N/A'; // Null handler
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                         return data ? data : 'N/A'; // Null handler
                        }
                    },
                ]
            });
    })
</script>
<script>
    $(document).ready(() => {
            var table = $('#table-transfer').DataTable({
                ordering: true,
                sortable: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('saldo-history.index') }}",
                    data: function(d) {
                        d.type = 'topup';
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
                        data: 'student.name',
                        name: 'student.name',
                        orderable: false,
                    },
                  
                    {
                        data: 'pay_amount',
                        name: 'pay_amount'
                    },
                    {
                        data: 'unique_payment',
                        name: 'unique_payment'
                    },
                    {
                        data: 'proof',
                        name: 'proof',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

        })
</script>
<script>
    function updateStatus(status, id) {
        // Show note textarea if status is "Ditolak"
        const noteTextarea = document.getElementById(`note-${id}`);
        if (status == 'REJECTED') {
        if (noteTextarea.tagName.toLowerCase() === 'input') {
        const textarea = document.createElement('textarea');
        textarea.className = 'form-control mt-2';
        textarea.name = 'note';
        textarea.id = `note-${id}`;
        textarea.placeholder = 'Note';
        textarea.value = noteTextarea.value;
        noteTextarea.replaceWith(textarea);
    }
    } else {
        if (noteTextarea.tagName.toLowerCase() === 'textarea') {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'note';
        input.id = `note-${id}`;
        input.value = noteTextarea.value;
        noteTextarea.replaceWith(input);
        }
     }
    }

    function saveStatus(id) {
    const status = document.getElementById(`status-${id}`).value;
    const note = document.getElementById(`note-${id}`).value || '';

    // Tampilkan loader menggunakan SweetAlert
    Swal.fire({
        title: 'Menyimpan...',
        text: 'Harap tunggu',
        allowOutsideClick: false,
        didOpen: () => {
        Swal.showLoading();
        }
    });

    // Simpan menggunakan axios ke route saldo-history.update
    axios.post(`{{ url('saldo-history/status-payment/') }}/${id}`, {
        status: status,
        note: note,
        _token: '{{ csrf_token() }}' // Pastikan Anda menyertakan CSRF token
    })
    .then((response) => {
        // Tampilkan pesan sukses menggunakan SweetAlert sesuai response dari server
        if (response.data.code == '200') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response.data.message
            });
        }
        // reload data table
        $('#table-transfer').DataTable().ajax.reload();
    })
    .catch((error) => {
    // Tampilkan pesan error menggunakan SweetAlert
        Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Terjadi kesalahan saat menyimpan data'
        });
        });
    }
</script>
@endpush