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
                        @if(Auth::user()->can('Create Saldo Santri') || Auth::user()->can('Manage Saldo Santri') || Auth::user()->isKoordinatorCahayaMart())
                            <a class="btn btn-sm btn-primary" href="{{ route('saldo-history.create') }}">
                                <i class="fas fa-plus me-1"></i> Penyesuaian Saldo
                            </a>
                        @endif
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
                            <a class="nav-link" id="arsip-topup-saldo-tab" data-bs-toggle="tab" href="#arsip-topup-saldo"
                                role="tab" aria-controls="arsip-topup-saldo" aria-selected="false">Arsip Topup Saldo</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="saldo-history-tab" data-bs-toggle="tab" href="#saldo-history"
                                role="tab" aria-controls="saldo-history" aria-selected="true">Riwayat Saldo</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade " id="saldo-history" role="tabpanel"
                            aria-labelledby="saldo-history-tab">
                            <!--begin::Filters-->
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 my-4">
                                <h4 class="text-dark fw-bolder mb-0">Riwayat Mutasi Saldo</h4>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fs-7 fw-bold text-gray-700 mb-0">Cari Siswa:</label>
                                        <input type="text" id="saldo-history-search-name" class="form-control form-control-solid form-control-sm" placeholder="Nama Siswa / NIS..." style="width: 180px;">
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fs-7 fw-bold text-gray-700 mb-0">Lembaga:</label>
                                        <select id="saldo-history-school-id" class="form-select form-select-solid form-select-sm" style="width: 150px;">
                                            <option value="">Semua</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fs-7 fw-bold text-gray-700 mb-0">Kelas:</label>
                                        <div class="dropdown">
                                            <button class="btn btn-light form-select-sm dropdown-toggle text-start" style="width: 150px; background-color: #f5f8fa; border-color: #f5f8fa; color: #5e6278;" type="button" id="saldo_history_classroom_btn" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                                Semua
                                            </button>
                                            <input type="hidden" id="saldo-history-classroom-id" value="">
                                            <div class="dropdown-menu p-4 shadow" style="min-width: 400px; max-height: 400px; overflow-y: auto;" aria-labelledby="saldo_history_classroom_btn" id="saldo_history_classroom_mega_menu">
                                                <div class="text-muted fs-7 mb-2">Pilih Lembaga terlebih dahulu</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fs-7 fw-bold text-gray-700 mb-0">Mulai:</label>
                                        <input type="date" id="saldo-history-start-date" class="form-control form-control-solid form-control-sm" style="width: 150px;">
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fs-7 fw-bold text-gray-700 mb-0">Selesai:</label>
                                        <input type="date" id="saldo-history-end-date" class="form-control form-control-solid form-control-sm" style="width: 150px;">
                                    </div>
                                    <button id="saldo-history-btn-filter" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                                    <button id="saldo-history-btn-reset" class="btn btn-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</button>
                                    <button id="saldo-history-btn-recalculate" class="btn btn-warning btn-sm text-dark fw-bold ms-1" title="Perbaiki & Sinkronkan Urutan Saldo"><i class="fas fa-sync-alt me-1"></i> Rekalkulasi Saldo</button>
                                </div>
                            </div>
                            <!--end::Filters-->
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
                                            <th>Saldo Awal</th>
                                            <th>Saldo Akhir</th>
                                            <th class="min-w-100px" style="width: 22%">Keterangan</th>
                                            <th class="text-center min-w-100px" style="width: 10%">Aksi</th>
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
                                            <th>Nominal</th>
                                            <th>Kode Unik</th>
                                            <th>Bank Tujuan</th>
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
                        <div class="tab-pane fade" id="arsip-topup-saldo" role="tabpanel"
                            aria-labelledby="arsip-topup-saldo-tab">
                            @include('admins.saldo-history.transfer-tab.archive')
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
<!-- Modal View Bukti Transfer -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
            <div class="modal-header border-0 bg-light px-6 py-4">
                <h5 class="modal-title fw-bold text-slate-800" id="imagePreviewModalLabel">Detail Bukti Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-6 bg-white">
                <img id="imagePreviewSrc" src="" class="img-fluid rounded-3 shadow-sm" alt="Bukti Transfer" style="max-height: 70vh; object-fit: contain; border: 1px solid #e2e8f0;">
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
                        d.school_id = $('#saldo-history-school-id').val();
                        d.classroom_id = $('#saldo-history-classroom-id').val();
                        d.search_name = $('#saldo-history-search-name').val();
                        d.start_date = $('#saldo-history-start-date').val();
                        d.end_date = $('#saldo-history-end-date').val();
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading..."
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
                            if (!data) return 'N/A';
                            let className = row.student && row.student.classroom ? row.student.classroom.name : 'Unknown';
                            return `
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bolder mb-1">${data}</span>
                                    <span class="badge badge-light-primary fw-bold" style="width: fit-content; font-size: 10px; padding: 4px 6px;">${className}</span>
                                </div>
                            `;
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
                        data: 'balance_before',
                        name: 'balance_before'
                    },
                    {
                        data: 'balance_after',
                        name: 'balance_after'
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
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            $('#saldo-history-btn-filter').off('click').on('click', function() {
                table.ajax.reload();
            });

            $('#saldo-history-btn-reset').off('click').on('click', function() {
                $('#saldo-history-school-id').val('');
                $('#saldo-history-classroom-id').val('');
                $('#saldo_history_classroom_btn').text('Semua');
                $('#saldo-history-search-name').val('');
                $('#saldo-history-start-date').val('');
                $('#saldo-history-end-date').val('');
                table.ajax.reload();
            });
            
            const allHistoryClassrooms = @json($classrooms);

            function renderHistoryClassroomMegaMenu(schoolId) {
                const container = $('#saldo_history_classroom_mega_menu');
                container.empty();

                if (!schoolId) {
                    container.html('<div class="text-muted fs-7 mb-2">Pilih Lembaga terlebih dahulu</div>');
                    return;
                }

                const filteredClasses = allHistoryClassrooms.filter(c => c.school_id == schoolId);
                if (filteredClasses.length === 0) {
                    container.html('<div class="text-muted fs-7 mb-2">Tidak ada kelas ditemukan</div>');
                    return;
                }

                const groups = {};
                filteredClasses.forEach(c => {
                    let match = c.name.match(/^(\d+)/);
                    let key = match ? match[1] : 'Lainnya';
                    if (!groups[key]) groups[key] = [];
                    groups[key].push(c);
                });

                const row = $('<div class="row g-2"></div>');
                
                container.append($('<a href="#" class="dropdown-item fw-bold text-primary mb-3 history-classroom-item" data-id="" data-name="Semua">Semua</a>'));

                Object.keys(groups).sort((a,b) => parseInt(a) - parseInt(b)).forEach(key => {
                    const col = $('<div class="col-4"></div>');
                    col.append(`<h6 class="dropdown-header text-uppercase text-muted fw-bolder">Kelas ${key}</h6>`);
                    groups[key].forEach(c => {
                        col.append(`<a class="dropdown-item history-classroom-item" href="#" data-id="${c.id}" data-name="${c.name}">${c.name}</a>`);
                    });
                    row.append(col);
                });

                container.append(row);
            }

            $('#saldo-history-school-id').on('change', function() {
                $('#saldo-history-classroom-id').val('');
                $('#saldo_history_classroom_btn').text('Semua');
                renderHistoryClassroomMegaMenu($(this).val());
            });

            $(document).on('click', '.history-classroom-item', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#saldo-history-classroom-id').val(id);
                $('#saldo_history_classroom_btn').text(name);
                $('#saldo_history_classroom_btn').dropdown('toggle'); // close dropdown manually
            });

            $('#saldo-history-btn-recalculate').off('click').on('click', function() {
                Swal.fire({
                    title: 'Rekalkulasi Saldo?',
                    text: 'Proses ini akan mengurutkan & memperhitungkan ulang seluruh running balance riwayat mutasi saldo santri secara presisi kronologis.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Rekalkulasi Sekarang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Rekalkulasi...',
                            text: 'Mohon tunggu sejenak',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        $.ajax({
                            url: "{{ route('saldo-history.recalculate') }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                Swal.fire('Berhasil!', res.message || 'Rekalkulasi saldo selesai.', 'success');
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire('Gagal!', (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan.', 'error');
                            }
                        });
                    }
                });
            });

            $('#saldo-history-search-name').off('keyup').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    table.ajax.reload();
                }
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
                    "loadingRecords": "Loading..."
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
                        data: 'bank_recipient',
                        name: 'bank_recipient',
                        orderable: false,
                        searchable: false
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
<script>
    $(document).ready(() => {
        var archiveTable = $('#table-archive').DataTable({
            ordering: true,
            sortable: true,
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [20, 30, 40],
            ajax: {
                url: "{{ route('saldo-history.index') }}",
                data: function(d) {
                    d.type = 'archive';
                    d.search_name = $('#archive-search-name').val();
                    d.start_date = $('#archive-start-date').val();
                    d.end_date = $('#archive-end-date').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading..."
            },
            columns: [
                {
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
                    data: 'bank_recipient',
                    name: 'bank_recipient',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'proof',
                    name: 'proof',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'officer',
                    name: 'officer',
                    orderable: false
                },
                {
                    data: 'updated_at_formatted',
                    name: 'updated_at',
                    orderable: true
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#archive-btn-filter').click(function() {
            archiveTable.ajax.reload();
        });

        $('#archive-btn-reset').click(function() {
            $('#archive-search-name').val('');
            $('#archive-start-date').val('');
            $('#archive-end-date').val('');
            archiveTable.ajax.reload();
        });

        $('#archive-search-name').keyup(function(e) {
            if (e.keyCode === 13) {
                archiveTable.ajax.reload();
            }
        });

        $(document).on('click', '.delete-archive-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Arsip riwayat ini akan disembunyikan. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Harap tunggu',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    axios.delete(`{{ url('saldo-history') }}/${id}`, {
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    })
                    .then((response) => {
                        if (response.data.code == '200') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message
                            });
                            archiveTable.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.data.message
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Error deleting archive:', error);
                        var msg = 'Terjadi kesalahan saat menghapus arsip';
                        if (error.response && error.response.data && error.response.data.message) {
                            msg = error.response.data.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    });
                }
            });
        });

        $(document).on('click', '.delete-history-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Riwayat saldo ini akan dihapus permanen dan saldo siswa akan disesuaikan kembali!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Harap tunggu',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    axios.delete(`{{ url('saldo-history/record') }}/${id}`, {
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    })
                    .then((response) => {
                        if (response.data.code == '200') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message
                            });
                            $('#table-saldo-history').DataTable().ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.data.message
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Error deleting history:', error);
                        var msg = 'Terjadi kesalahan saat menghapus riwayat';
                        if (error.response && error.response.data && error.response.data.message) {
                            msg = error.response.data.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    });
                }
            });
        });

        // Adjust columns on tab switch
        $('a[href="#arsip-topup-saldo"]').on('shown.bs.tab', function (e) {
            archiveTable.columns.adjust().draw();
        });

        // Click handler for viewing proof images in a modal
        $(document).on('click', '.view-proof-image', function() {
            var src = $(this).data('src');
            $('#imagePreviewSrc').attr('src', src);
            $('#imagePreviewModal').modal('show');
        });
    });
</script>
@endpush