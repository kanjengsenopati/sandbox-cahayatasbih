@extends('layouts.master', ['title' => 'Data User'])

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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data User</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->

                        <!--end::Item-->
                        <!--begin::Item-->
                        <a class="breadcrumb-item" href="{{ route('user.index') }}">
                            <li class="breadcrumb-item text-muted">User</li>
                        </a>
                        <!--end::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-dark">Detail User</li>
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
                                        Detail User</h1>
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
                                                    Information User
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                    data-bs-toggle="tab" href="#kt_tab_pane_2">
                                                    Run History
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
                                                    data-bs-toggle="tab" href="#kt_tab_pane_5">
                                                    Reward Claim History
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
                                                    src="{{ $user->avatar ? asset($user->avatar) : asset('/assets/media/avatars/default.png') }}"
                                                    alt="Avatar User">
                                                <span class="badge badge-secondary"><img style="width: 16px"
                                                        src="{{ asset($user->level->badge) }}" alt="">
                                                    Level {{ $user->level->name }}</span>
                                            </div>
                                            <div class="ms-5 ps-3 w-100">
                                                <!-- <div class=""> -->
                                                <table class="profile table">
                                                    <tr>
                                                        <td class="grey" width="20%">Name</td>
                                                        <td class="pe-3"></td>
                                                        <td>{{ $user->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Email</td>
                                                        <td></td>
                                                        <td>{{ $user->email }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Phone</td>
                                                        <td></td>
                                                        <td>+{{ $user->phone }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Bio</td>
                                                        <td></td>
                                                        <td>{{ $user->bio ?? 'Belum diatur' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Total Point</td>
                                                        <td></td>
                                                        <td><span class="badge badge-success">{{ $user->point }}
                                                                Point</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Total Step</td>
                                                        <td></td>
                                                        <td><span class="badge badge-info">{{ $user->total_step }}
                                                                Langkah</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Total Calory</td>
                                                        <td></td>
                                                        <td><span class="badge badge-primary">{{ $user->total_calories }}
                                                                KCal</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Total Distance</td>
                                                        <td></td>
                                                        <td><span class="badge badge-warning">{{ $user->total_range }}
                                                                Km</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Total Xp</td>
                                                        <td></td>
                                                        <td><span class="badge badge-danger">{{ $user->experience_point }}
                                                                Xp</span>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Gender</td>
                                                        <td></td>
                                                        <td>{{ $user->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Birth</td>
                                                        <td></td>
                                                        <td>{{ $user->birth ?? 'Belum Ditambahkan' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Height</td>
                                                        <td></td>
                                                        <td>{{ $user->height ?? 'Belum Ditambahkan' }} Cm</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grey" width="20%">Weight</td>
                                                        <td></td>
                                                        <td>{{ $user->weight ?? 'Belum Ditambahkan' }} Kg</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
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
                                            <table id="table-challenge" class="table table-striped border rounded gy-5 gs-7">
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
                    url: "{{ route('user.show', $user->id) }}",
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
                    url: "{{ route('user.show', $user->id) }}",
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
                    url: "{{ route('user.show', $user->id) }}",
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

            var tableReward = $('#table-reward').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $user->id) }}",
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
