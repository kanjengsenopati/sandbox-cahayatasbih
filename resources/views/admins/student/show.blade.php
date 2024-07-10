@extends('layouts.master', ['title' => 'Detail Siswa'])

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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('student.index') }}">
                        <li class="breadcrumb-item text-muted">Data Siswa</li>
                    </a>
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
                                <!--begin::Svg Icon | path: icons/duotune/communication/com005.svg-->
                                <span class="svg-icon svg-icon-1 me-2">
                                    {{-- add online icon admin --}}

                                </span>
                                <!--end::Svg Icon-->
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    Detail Siswa
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
                                        {{-- <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_2">
                                                Activity History
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_3">
                                                Challenge History
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_4">
                                                Point History
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_6">
                                                XP History
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_5">
                                                Reward Claim History
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                                    <div class="d-flex align-items-start gap-5 px-4 pt-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                                            <img class="img_profile"
                                                src="{{ $student->avatar ? asset($student->avatar) : asset('/assets/media/avatars/default.png') }}"
                                                alt="Avatar User">
                                            <span class="badge badge-secondary">
                                                Kelas {{ $student->classroom->name ?? 'Belum diatur' }}</span>
                                            {{-- add button direct to edit student --}}
                                            @can ('Edit Santri')
                                            <a href="{{ route('student.edit', $student->id) }}"
                                                class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit Siswa</a>
                                            @endcan
                                        </div>
                                        <div class="ms-5 ps-3 w-100">
                                            <!-- <div class=""> -->
                                            <table class="profile table">
                                                <tr>
                                                    <td class="grey" width="20%">Nama</td>
                                                    <td class="pe-3"></td>
                                                    <td>{{ $student->name ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Tempat, Tanggal Lahir</td>
                                                    <td></td>
                                                    <td>{{ $student->born_place ?? 'Belum diatur' }},
                                                        {{ $student->birth_date ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">NIS</td>
                                                    <td></td>
                                                    <td>+{{ $student->nis ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">NISN</td>
                                                    <td></td>
                                                    <td>{{ $student->nisn ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Jenis Kelamin</td>
                                                    <td></td>
                                                    <td>{{ $student->gender == 'L' ?
                                                        'Laki-laki' : 'Perempuan' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Wali Siswa</td>
                                                    <td></td>
                                                    <td>{{ $student->user->name ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">UPT</td>
                                                    <td></td>
                                                    <td>{{ $student->classroom?->school?->name ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Alamat</td>
                                                    <td></td>
                                                    <td>{{ $student->address ?? 'Belum diatur' }}</td>
                                                </tr>
                                                {{-- <tr>
                                                    <td class="grey" width="20%">Total Xp</td>
                                                    <td></td>
                                                    <td><span class="badge badge-danger">{{ $student->experience_point
                                                            }}
                                                            Xp</span>
                                                </tr> --}}
                                                {{-- <tr>
                                                    <td class="grey" width="20%">Gender</td>
                                                    <td></td>
                                                    <td>{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Birth</td>
                                                    <td></td>
                                                    <td>{{ $student->birth ?? 'Belum Ditambahkan' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Height</td>
                                                    <td></td>
                                                    <td>{{ $student->height ? $student->height . ' Cm' : 'Belum
                                                        Ditambahkan'
                                                        }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Weight</td>
                                                    <td></td>
                                                    <td>{{ $student->weight ? $student->weight . ' Kg' : 'Belum
                                                        Ditambahkan'
                                                        }}</td>
                                                </tr> --}}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-run" class="table table-striped border rounded gy-5 gs-7">
                                            <thead>
                                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                    <th width="3%">No</th>
                                                    <th class="w-10px pe-2 nowrap">Distance (Km)</th>
                                                    <th>Duration</th>
                                                    <th>Step</th>
                                                    <th class="nowrap">Calory (Kcal)</th>
                                                    <th class="nowrap">Pace (Duration/Km)</th>
                                                    <th>Start</th>
                                                    <th>Finish</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-challenge"
                                            class="table table-striped border rounded gy-5 gs-7">
                                            <thead>
                                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                    <th width="3%">No</th>
                                                    <th class="w-10px pe-2 nowrap">Challenge</th>
                                                    <th class="w-10px pe-2 nowrap">Distance (Km)</th>
                                                    <th>Duration</th>
                                                    <th>Step</th>
                                                    <th class="nowrap">Calory (Kcal)</th>
                                                    <th class="nowrap">Pace (Duration/Km)</th>
                                                    <th class="nowrap">Start At</th>
                                                    <th class="nowrap">Finish At</th>
                                                    <th>Status</th>
                                                    <th>Failed Note</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_4" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-poin" class="table table-striped border rounded gy-5 gs-7">
                                            <thead>
                                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                    <th width="3%">No</th>
                                                    <th class="nowrap">History</th>
                                                    <th class="nowrap">Poin (Km)</th>
                                                    <th>Get At</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_6" role="tabpanel">
                                    <div class="d-flex justify-content-end my-5">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#addXp">
                                            Tambah XP
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="table-xp" class="table table-striped border rounded gy-5 gs-7">
                                            <thead>
                                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                    <th width="3%">No</th>
                                                    <th>Jumlah XP</th>
                                                    <th>Status</th>
                                                    <th>Keterangan</th>
                                                    <th>Diperoleh Pada</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-reward" class="table table-striped border rounded gy-5 gs-7">
                                            <thead>
                                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                    <th width="3%">No</th>
                                                    <th class="nowrap">Reward</th>
                                                    <th class="nowrap">Value</th>
                                                    <th>Claimed At</th>
                                                    <th>Expired At</th>
                                                    <th>Used At</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div> --}}
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

<!--end::Modal dialog-->
{{-- </div> --}}
{{-- add modal #addXp --}}
{{-- <div class="modal fade" id="addXp" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content rounded">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-kt-modal-action-type="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--begin::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <!--begin:Form-->
                <form id="kt_modal_bidding_form" class="form" action="" method="POST" enctype="multipart/form-data"
                    autocomplete="off">
                    @csrf
                    <!--begin::Heading-->
                    <div class="mb-13 text-center">
                        <!--begin::Title-->
                        <h1 class="mb-3">Penyesuaian XP</h1>
                        <!--end::Title-->
                        <!--begin::Description-->
                        <div class="text-muted fw-semibold fs-5">Sistem dukungan yang memungkinkan admin
                            memberikan bantuan atau resolusi masalah yang mungkin muncul terkait
                            dengan XP.
                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Heading-->
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">Tipe XP</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Select2-->
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true"
                            data-placeholder="Pilih Tipe Tindakan" name="type">
                            <option value="ADD">Penambahan XP</option>
                            <option value="SUBTRACT">Pengurangan XP</option>
                        </select>
                        <!--end::Select2-->
                    </div>

                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">Mode XP</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Select2-->
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true"
                            data-placeholder="Pilih Mode XP" name="mode">
                            <option value="walk">Jalan Kaki</option>
                            <option value="run">Lari</option>
                            <option value="bike">Bersepeda</option>
                        </select>
                        <!--end::Select2-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">Jumlah XP</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input wrapper-->
                        <input type="number" class="form-control form-control-solid" placeholder="Masukkan Jumlah XP"
                            name="xp" />
                    </div>
                    <input type="hidden" name="user_id" value="{{ $student->id }}">
                    <!--end::Input group-->
                    <!--begin::Notice-->
                    <!--begin::Notice-->
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                        <!--begin::Icon-->
                        <!--end::Icon-->
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-stack flex-grow-1">
                            <!--begin::Content-->
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Peringatan!</h4>
                                <div class="fs-6 text-gray-700">Pastikan jumlah XP yang anda masukkan sudah sesuai
                                    dengan
                                    kebutuhan user.</div>
                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
            </div>
            <!--end::Notice-->
            <!--end::Notice-->
            <!--begin::Actions-->
            <div class="text-center">
                <button type="reset" class="btn btn-light me-3" data-kt-modal-action-type="cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" data-kt-modal-action-type="submit">
                    <span class="indicator-label">Simpan</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
            <!--end::Actions-->
            </form>
            <!--end:Form-->
        </div>
        <!--end::Modal body-->
    </div>
    <!--end::Modal content-->
</div> --}}
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(document).ready(() => {
            var table = $('#table-run').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'history'
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
                        data: 'range',
                        name: 'range'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'step',
                        name: 'step'
                    },
                    {
                        data: 'calory',
                        name: 'calory',
                    },
                    {
                        data: 'pace',
                        name: 'pace',
                    },
                    {
                        data: 'start_at',
                        name: 'start_at',
                    },
                    {
                        data: 'finish_at',
                        name: 'finish_at',
                    },
                ]
            });

            var tableChallenge = $('#table-challenge').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'challenge'
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
                        data: 'challenge',
                        name: 'challenge'
                    },
                    {
                        data: 'range',
                        name: 'range'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'step',
                        name: 'step'
                    },
                    {
                        data: 'calory',
                        name: 'calory',
                    },
                    {
                        data: 'pace',
                        name: 'pace',
                    },
                    {
                        data: 'start_at',
                        name: 'start_at',
                    },
                    {
                        data: 'finish_at',
                        name: 'finish_at',
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    {
                        data: 'failed_note',
                        name: 'failed_note',
                    }
                ]
            });

            var tableReward = $('#table-poin').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'point'
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
                        data: 'note',
                        name: 'note',
                    },
                    {
                        data: 'point',
                        name: 'point'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        responsivePriority: -1,
                    },
                ]
            });

            var tableReward = $('#table-xp').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'xp'
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
                        data: 'xp',
                        name: 'xp',
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        responsivePriority: -1,
                    },
                ]
            });

            var tableReward = $('#table-reward').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $student->id) }}",
                    type: 'GET',
                    data: {
                        type: 'reward'
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
                        data: 'reward',
                        name: 'reward',
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'claimed_at',
                        name: 'claimed_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'expired_at',
                        name: 'expired_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'used_at',
                        name: 'used_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'status',
                        name: 'status',
                        responsivePriority: -1,
                    },
                ]

            });
        })
</script>
@endpush