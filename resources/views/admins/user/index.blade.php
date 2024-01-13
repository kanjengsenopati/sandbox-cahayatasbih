@extends('layouts.master', ['title' => 'Data User'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Daftar User</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('user.index') }}" class="text-muted text-hover-primary">User</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List User</li>
                    <!--end::Item-->

                </ul>
                <!--end::Breadcrumb-->
            </div>
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
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-beetween gap-5">
                        <div class="mb-0">
                            <label class="form-label">Filter Tanggal</label>
                            <div class="d-flex
                                gap-4 align-items-end">
                                <div id="dateRange" class="pull-right"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                    <span></span> <b class="caret"></b>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            {{-- <form action="{{route('user.export_excel')}}" id="form-filter" method="get">
                                <input type="text" hidden id="start_date" name="start_date" required>
                                <input type="text" hidden id="end_date" name="end_date" required>
                                <input type="text" hidden id="type" name="type" required>
                                <div class="d-flex flex-wrap gap-4 align-items-end">
                                    <div>
                                        <label class="form-label">Export Tanggal</label>
                                        <div id="dateRangeExport" class="pull-right"
                                            style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                            <span></span> <b class="caret"></b>
                                        </div>
                                    </div>
                                    <!--begin::Export dropdown-->
                                    <button type="button" class="btn btn-sm btn-primary" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="ki-duotone fa fa-caret-down fs-2"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        Export Report
                                    </button>
                                    <!--begin::Menu-->
                                    <div id="kt_datatable_example_export_menu"
                                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4"
                                        data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a type="button" class="menu-link btn-export px-3" data-type="xlsx">
                                                Export as Excel
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a type="button" class="menu-link btn-export px-3" data-type="csv">
                                                Export as CSV
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                    <!--end::Export dropdown-->
                                </div>
                            </form> --}}
                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">
                        <div>
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#kt_modal_1">+
                                Bulk Add Point
                            </button>
                        </div>
                        <div>
                            <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission"
                                href="{{ route('user.create') }}">+ User</a>
                        </div>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-user" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true"
                                                data-kt-check-target="#table-user .form-check-input" value="1" />
                                        </div>
                                    </th>
                                    <th width="3%">No</th>
                                    <th class="w-10px pe-2">Avatar</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No HP</th>
                                    <th>Gender</th>
                                    <th>Saldo</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-100px">Aksi</th>
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
{{-- <form action="{{route('user_point.add_bulk')}}" id="form_add_point_bulk" method="post">
    @csrf
    <div class="modal fade" tabindex="-1" id="kt_modal_1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Bulk Add Point</h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <input type="text" name="point" id="point-bulk" class="form-control form-control-solid"
                        placeholder="Poin" min="1" value="1" />
                    <input type="text" id="user_ids" name="user_ids" hidden>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btn_add_point_bulk" class="btn btn-primary">Add Point</button>
                </div>
            </div>
        </div>
    </div>
</form> --}}
@endsection
@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(() => {
            table();
            const container = document.querySelector('#table-user');
            const toolbarBase = document.querySelector('[data-kt-docs-table-toolbar="base"]');
            const toolbarSelected = document.querySelector('[data-kt-docs-table-toolbar="selected"]');
            const selectedCount = document.querySelector('[data-kt-docs-table-select="selected_count"]');

            // Select refreshed checkbox DOM elements
            const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');

            // Detect checkboxes state & count
            let checkedState = false;
            let count = 0;
            let userIds = [];

            // Count checked boxes
            allCheckboxes.forEach(c => {
                if (c.checked) {
                    checkedState = true;
                    count++;
                }
            });

            // Toggle toolbars
            if (checkedState) {
                selectedCount.innerHTML = count;
                toolbarBase.classList.add('d-none');
                toolbarSelected.classList.remove('d-none');
            } else {
                toolbarBase.classList.remove('d-none');
                toolbarSelected.classList.add('d-none');
            }

        })


    const table = (start_date = '', end_date = '') => {
        var table = $('#table-user').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('user.index') }}",
                    data: {
                        start_date,
                        end_date
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
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            return `
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" data-user_id="${row.id}" />
                                </div>`;
                        }
                    },
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, {
                        data: 'avatar',
                        name: 'avatar',
                        render: function(data, type, row) {
                            if (data == null) {
                                return `<span class="symbol-label fs-2x fw-bold text-primary bg-light-primary">${row.name.charAt(0)}</span>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px w-50px rounded-circle" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        responsivePriority: -1,
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'gender',
                        name: 'gender',
                        render: function(data, type, row) {
                            let badgeClass = '';
                            let label = '';

                            if (data == 'L') {
                                badgeClass = 'badge-light-primary';
                                label = 'Laki-laki';
                            } else if (data == 'P') {
                                badgeClass = 'badge-light-danger';
                                label = 'Perempuan';
                            } else {
                                badgeClass = 'badge-light-warning';
                                label = 'Tidak diketahui';
                            }

                            return `<span class="badge ${badgeClass}">${label}</span>`;
                        },
                    },
                    {
                        data: 'saldo',
                        name: 'saldo'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        responsivePriority: -1,
                        render: function(data, type, row) {
                            let badgeClass = '';
                            let label = '';
                            if (data == true) {
                                badgeClass = 'badge-light-success';
                                label = 'Aktif';
                            } else {
                                badgeClass = 'badge-light-danger';
                                label = 'Nonaktif';
                            }
                            return `<span class="badge ${badgeClass}">${label}</span>`;
                        },
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true,
                        responsivePriority: -1,
                    },
                ]
            });
    }
    $('#dateRange').change(function(){
        table()
    })
    $(function() {
        var start = moment().subtract(60, 'days');
        var end = moment();

        function callback(start, end) {
            $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            var start = start.format('YYYY-MM-DD');
            var end = end.format('YYYY-MM-DD');
            table(start,end);
        }

        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
            }
        }, callback);
        callback(start, end);

        function callbackExport(start, end) {
            $('#dateRangeExport span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            var start = start.format('YYYY-MM-DD');
            var end = end.format('YYYY-MM-DD');
            $("#start_date").val(start);
            $("#end_date").val(end);
        }
        $('#dateRangeExport').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
            }
        }, callbackExport);
        callbackExport(start, end);
    });

    $(document).on('click', '.btn-export', function(e) {
        var type = e.target.dataset.type;
        $("#type").val(type);
        $("#form-filter").submit();
    });
    $(document).on('click', '#btn_add_point_bulk', function(e) {
            e.preventDefault();
            $('#user_ids').val()
            var allVals = [];
            let url = $(this).data('url');
            $(".form-check-input:checked").each(function() {
                allVals.push($(this).attr('data-user_id'));
            });
            var join_selected_values = allVals.join(",");
            $('#user_ids').val(join_selected_values);
            if(allVals.length <=0)
            {
                Swal.fire(
                    'Opps',
                    'Silakan pilih user terlebih dahulu !!!',
                    'warning'
                )
            }else{
                Swal.fire({
                title: 'Confirmation ?',
                text: 'Anda yakin akan menambahkan '+$("#point-bulk").val()+' point untuk '+allVals.length+' user terpilih ???',
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: 'success',
                cancelButtonColor: 'primary',
                confirmButtonText: 'Tambahkan',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed){
                    $('#form_add_point_bulk').submit();
                }else{
                    return false;
                }
            });

            }

    });
</script>
@endpush