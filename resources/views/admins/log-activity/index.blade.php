@extends('layouts.master', ['title' => 'Activity Log'])
@push('css')
    <script>
        .modal - dialog {
            max - width: fit - content;
            margin: 1.75 rem auto;
        }
    </script>
@endpush
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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Daftar Log Activity</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('log-activity.index') }}" class="text-muted text-hover-primary">Log Activity</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            List Log Activity
                        </li>

                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Filter menu-->
                    <!--begin::Primary button-->

                    <!--end::Primary button-->
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h3 class="text-dark">Log Activity</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table id="table-log-activity" class="table table-striped border rounded gy-5 gs-7">
                                <thead>
                                    <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                        <th width="3%">No</th>
                                        <th>Nama</th>
                                        <th>LOG</th>
                                        <th>EVENT</th>
                                        <th>ACTION AT</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
                <!--begin::Modals-->

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Wrapper-->

    <div class="modal fade" tabindex="-1" id="modal-popout">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Detail Log Activity</h3>

                    <!--begin::Close-->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped border rounded gy-5 gs-7" id="list-previous-data" width="100%">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - New Card-->
@endsection
@push('js')
    <script>
        $(document).ready(() => {
            var table = $('#table-log-activity').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('log-activity.index') }}",
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'>",
                        previous: "<i class='fa fa-angle-left'>"
                    },
                    loadingRecords: "Loading...",
                    processing: "Processing..."
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
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'description',
                        name: 'description',
                    },
                    {
                        data: 'event',
                        name: 'event',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },
                ]
            });
        });

        function see_previous_data(id) {
            $.ajax({
                url: "{{ route('log-activity.show', ':id') }}".replace(':id', id),
                method: 'get',
                dataType: 'html',
                success: function(data) {
                    $('#modal-popout').modal('show');
                    $('#list-previous-data').html(data);
                }
            });
        }
    </script>
@endpush
