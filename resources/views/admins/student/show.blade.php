@extends('layouts.master', ['title' => 'Detail Siswa'])

@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <a class="breadcrumb-item text-muted" href="{{ route('student.index') }}">Data Siswa</a>
                    </li>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Detail Siswa</li>
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">Detail Siswa</h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <div class="mb-5 hover-scroll-x">
                                <div class="d-grid">
                                    <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 active"
                                                data-bs-toggle="tab" href="#kt_tab_pane_1">
                                                Informasi Siswa
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_2">
                                                Tagihan Santri
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_3">
                                                Riwayat Saldo
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_4">
                                                Riwayat Tabungan
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_5">
                                                Tahfidz
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                                    <div class="d-flex align-items-start gap-5 px-4 pt-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                                            <img class="img_profile"
                                                src="{{ $student->avatar_url ?? asset('assets/media/avatars/default.png') }}"
                                                onerror="this.src='{{ asset('assets/media/avatars/default.png') }}'" alt="Avatar User">
                                            <span class="badge badge-secondary">
                                                Kelas {{ $student->classroom->name ?? 'Belum diatur' }}</span>
                                            @can ('Edit Santri')
                                            <a href="{{ route('student.edit', $student->id) }}"
                                                class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit Siswa</a>
                                            @endcan
                                        </div>
                                        <div class="ms-5 ps-3 w-100">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="profile table">
                                                        <tr>
                                                            <td class="grey" style="width: 40%">Nama</td>
                                                            <td class="pe-2" style="width: 5%">:</td>
                                                            <td>{{ $student->name ?? 'Belum diatur' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Nama Panggilan</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->nickname ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Tempat, Tanggal Lahir</td>
                                                            <td class="pe-2">:</td>
                                                            <td>
                                                                {{ $student->born_place ?? 'Belum diatur' }},
                                                                @if(!empty($student->birth_date))
                                                                    {{ \Carbon\Carbon::parse($student->birth_date)->format('d-M-Y') }}
                                                                @else
                                                                    Belum diatur
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">NIS</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->nis ?? 'Belum diatur' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">NISN</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->nisn ?? 'Belum diatur' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Saldo</td>
                                                            <td class="pe-2">:</td>
                                                            <td class="text-success fw-bold">Rp. {{ number_format($student->saldo, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Tabungan</td>
                                                            <td class="pe-2">:</td>
                                                            <td class="text-success fw-bold">Rp. {{ number_format($student->saving, 0, ',', '.') }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="profile table">
                                                        <tr>
                                                            <td class="grey" style="width: 45%">Jenis Kelamin</td>
                                                            <td class="pe-2" style="width: 5%">:</td>
                                                            <td>{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Wali Siswa</td>
                                                            <td class="pe-2">:</td>
                                                            <td>
                                                                {{ $student->user->name ?? 'Belum diatur' }}
                                                                @if($student->user && $student->user->jamaah_status)
                                                                    @php
                                                                        $status = $student->user->jamaah_status;
                                                                        $badgeClass = match($status) {
                                                                            'JAMAAH' => 'badge-light-success',
                                                                            'NON_JAMAAH' => 'badge-light-danger',
                                                                            'MUKIMIN' => 'badge-light-primary',
                                                                            default => 'badge-light-danger'
                                                                        };
                                                                        $statusLabel = match($status) {
                                                                            'JAMAAH' => 'Jamaah',
                                                                            'NON_JAMAAH' => 'Non Jamaah',
                                                                            'MUKIMIN' => 'Mukimin',
                                                                            default => 'Non Jamaah'
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $badgeClass }} fw-bolder ms-2 px-2 py-1">
                                                                        {{ $statusLabel }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">UPT</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->classroom?->school?->name ?? 'Belum diatur' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Alamat</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->address ?? 'Belum diatur' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Kota / Kabupaten</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->city ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Provinsi</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->province ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Penanggung Jawab / Ustadz Kamar</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->asramaHost->name ?? ($student->asrama?->hostAdmin?->name ?? '-') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="grey">Nama Kamar</td>
                                                            <td class="pe-2">:</td>
                                                            <td>{{ $student->asrama_name ?? ($student->asrama?->name ?? '-') }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-student-bill" class="table align-middle table-row-dashed">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th class="min-w-70px">Tahun Ajaran</th>
                                                    <th class="min-w-125px">Item Pembayaran</th>
                                                    <th class="min-w-125px">Total Tagihan</th>
                                                    <th class="min-w-125px">Dibayar</th>
                                                    <th class="min-w-125px">Sisa Tagihan</th>
                                                    <th class="text-center min-w-70px" style="width: 22%">
                                                        Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-saldo-history" class="table align-middle table-row-dashed">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th class="min-w-100px" style="width: 22%">Siswa</th>
                                                    <th class="min-w-100px" style="width: 22%">Jumlah</th>
                                                    <th class="min-w-100px" style="width: 22%">Status</th>
                                                    <th class="min-w-100px" style="width: 22%">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_4" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-saving-history" class="table align-middle table-row-dashed">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th class="min-w-100px" style="width: 10%">Tanggal</th>
                                                    <th class="min-w-100px" style="width: 22%">Siswa</th>
                                                    <th class="min-w-100px" style="width: 22%">Jumlah</th>
                                                    <th class="min-w-100px" style="width: 22%">Status</th>
                                                    <th class="min-w-100px" style="width: 22%">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-tahfidz" class="table align-middle table-row-dashed">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th width="3%">No</th>
                                                    <th>Tanggal</th>
                                                    <th>Santri</th>
                                                    <th>Jumlah Halaman</th>
                                                    <th>Keterangan</th>
                                                    <th>Feedback</th>
                                                    <th>Link Video</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-bold"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
@endsection

@push('js')
<script>
    $(document).ready(() => {
        // Silence default browser alert popups for DataTables errors
        $.fn.dataTable.ext.errMode = 'none';

        let tables = {};

        // Log DataTables errors to console instead of showing alert()
        $(document).on('error.dt', function(e, settings, techNote, message) {
            console.error('DataTables error: ', message);
        });

        function initBillTable() {
            if (tables['bill']) return;
            tables['bill'] = $('#table-student-bill').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('student.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'bill'
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
                    responsivePriority: -1,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'academic_year.name', name: 'academic_year.name' },
                { data: 'name', name: 'name' },
                { data: 'total', name: 'total' },
                { data: 'total_paid', name: 'total_paid' },
                { data: 'total_unpaid', name: 'total_unpaid' },
                { 
                    data: 'status', 
                    name: 'status',
                    responsivePriority: -1,
                }
                ]
            });
        }

        function initSaldoTable() {
            if (tables['saldo']) return;
            tables['saldo'] = $('#table-saldo-history').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('student.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'saldo'
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
                    responsivePriority: -1,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'student.name', name: 'student.name' },
                { data: 'amount', name: 'amount' },
                { data: 'status', name: 'status' },
                { data: 'description', name: 'description' },
                ]
            });
        }

        function initSavingTable() {
            if (tables['saving']) return;
            tables['saving'] = $('#table-saving-history').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('student.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'saving'
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
                    responsivePriority: -1,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'date', name: 'date' },
                { data: 'student.name', name: 'student.name' },
                { data: 'amount', name: 'amount' },
                { data: 'status', name: 'status' },
                { data: 'description', name: 'description' },
                ]
            });
        }

        function initTahfidzTable() {
            if (tables['tahfidz']) return;
            tables['tahfidz'] = $('#table-tahfidz').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('student.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'tahfidz'
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
                    responsivePriority: -1,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'deposit_date',
                    name: 'deposit_date'
                },
                {
                    data: 'student.name',
                    name: 'student.name'
                },
                {
                    data: 'number_of_pages',
                    name: 'number_of_pages'
                },
                {
                    data: 'note',
                    name: 'note'
                },
                {
                    data: 'feedback',
                    name: 'feedback'
                },
                {
                    data: 'link',
                    name: 'link'
                },
                ]
            });
        }

        // Lazy initialize DataTables when tabs are shown
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            let target = $(e.target).attr("href");
            if (target === "#kt_tab_pane_2") {
                initBillTable();
                if (tables['bill']) {
                    tables['bill'].columns.adjust().responsive.recalc();
                }
            } else if (target === "#kt_tab_pane_3") {
                initSaldoTable();
                if (tables['saldo']) {
                    tables['saldo'].columns.adjust().responsive.recalc();
                }
            } else if (target === "#kt_tab_pane_4") {
                initSavingTable();
                if (tables['saving']) {
                    tables['saving'].columns.adjust().responsive.recalc();
                }
            } else if (target === "#kt_tab_pane_5") {
                initTahfidzTable();
                if (tables['tahfidz']) {
                    tables['tahfidz'].columns.adjust().responsive.recalc();
                }
            }
        });
    });
</script>
@endpush