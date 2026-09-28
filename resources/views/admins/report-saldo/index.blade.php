@extends('layouts.master', ['title' => 'Laporan Transaksi Saldo'])

@push('css')
<style>
    #filter_classroom_btn {
        min-width: 180px !important;
        height: 42px !important;
        min-height: 42px !important;
        background-color: #f5f8fa !important;
        border: 1px solid #f5f8fa !important;
        border-radius: 0.475rem !important;
        color: #5e6278 !important;
        cursor: pointer !important;
        user-select: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0 16px !important;
        transition: all 0.2s ease-in-out !important;
    }
    #filter_classroom_btn:hover {
        background-color: #eef3f7 !important;
        border-color: #eef3f7 !important;
        color: #181c32 !important;
    }
    #classroom_mega_menu:not(.show) {
        display: none !important;
    }
    #classroom_mega_menu.show {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
</style>
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan Transaksi Saldo</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-saldo.index') }}" class="text-muted text-hover-primary">Laporan Transaksi Saldo</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Transaksi</li>
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
            <div class="card mb-5">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-beetween gap-5">
                        <div class="mb-0">
                            <form action="{{ route('report-saldo.export') }}" id="form-filter" method="get">
                                <input type="text" hidden id="type" name="type" required>
                                <div class="d-flex flex-wrap gap-4 align-items-end">
                                    <div>
                                        <label class="form-label">Filter Tanggal</label>
                                        <div class="d-flex gap-4 align-items-end">
                                            <div id="dateRange" class="pull-right"
                                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                                <span></span> <b class="caret"></b>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                         <label class="form-label">Outlet</label>
                                         <select name="outlet_id" class="form-select" id="filter_outlet_id">
                                             <option value="">Semua Outlet</option>
                                             @foreach ($outlets as $outlet)
                                             <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                             @endforeach
                                         </select>
                                     </div>
                                     <div>
                                          <label class="form-label">Lembaga / UPT</label>
                                          <select name="school_id" class="form-select" id="filter_school_id">
                                              <option value="">Semua Lembaga</option>
                                              @foreach ($schools as $school)
                                              <option value="{{ $school->id }}">{{ $school->name }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                      <div>
                                          <label class="form-label">Kelas</label>
                                          <div class="dropdown" id="report_saldo_classroom_dropdown_container" style="position: relative !important;">
                                              <input type="hidden" name="classroom_id" id="filter_classroom_id" value="">
                                              <button class="btn btn-light fs-7" type="button" id="filter_classroom_btn" aria-expanded="false">
                                                  <span id="filter_classroom_btn_text" class="text-truncate me-2" style="pointer-events: none;">Semua Kelas</span>
                                                  <i class="fas fa-chevron-down fs-8 text-gray-500 filter-classroom-arrow" style="pointer-events: none; transition: transform 0.2s ease;"></i>
                                              </button>
                                              <div class="dropdown-menu p-4 shadow" style="min-width: 520px; width: 620px; max-width: 95vw; max-height: 420px; overflow-y: auto; position: absolute !important; top: 100% !important; left: 0 !important; margin-top: 6px !important; z-index: 9999 !important;" aria-labelledby="filter_classroom_btn" id="classroom_mega_menu">
                                                  <div class="text-muted fs-7 mb-2">Pilih Lembaga terlebih dahulu</div>
                                              </div>
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
                            </form>
                            {{-- add 3 card total topup saldo, pengurangan saldo, dan saldo tersedia --}}

                            <div class="d-flex gap-2 mt-4">
                                <div class="card bg-light-primary bg-active-primary flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Topup Saldo</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-primary fs-3 fw-bolder" id="total-topup">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <div class="card bg-light-danger bg-active-danger flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Total Pengurangan Saldo</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-danger fs-3 fw-bolder" id="total-pengurangan">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <div class="card bg-light-success bg-active-success flex-grow-1">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Label-->
                                        <div class="fw-bolder fs-5 text-gray-800">Saldo Tersedia</div>
                                        <!--end::Label-->
                                        <!--begin::Stats-->
                                        <div class="text-success fs-3 fw-bolder" id="saldo-tersedia">Rp. 0</div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="card-body pt-0">
                        <!--begin::Search Box-->
                        <div class="d-flex justify-content-end mb-4">
                            <div class="d-flex align-items-center position-relative">
                                <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                    <i class="fas fa-search text-gray-400"></i>
                                </span>
                                <input type="text" id="custom-search-student" class="form-control form-control-solid w-250px ps-12 fs-7" placeholder="Cari Nama Siswa / NIS..." />
                            </div>
                        </div>
                        <!--end::Search Box-->
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table id="table-saldo" class="table align-middle table-row-dashed ">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                         <th style="width: 5%">No</th>
                                         <th>Tanggal</th>
                                         <th>NIS</th>
                                         <th>Nama Siswa</th>
                                         <th>Outlet</th>
                                         <th>Nominal</th>
                                         <th>Status</th>
                                         <th>Saldo Awal</th>
                                         <th>Saldo Akhir</th>
                                         <th>Keterangan</th>
                                         @if (Auth::user()->can('Delete Laporan Saldo Santri'))
                                         <th style="width: 10%">Aksi</th>
                                         @endif
                                     </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold"></tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->

</div>
</div>
</div>
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>
<script>
    $(document).ready(function() {
        let table = initializeTable();
        getTotalSaldo();

        function initializeTable(start_date = '', end_date = '') {
            return $('#table-saldo').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('report-saldo.index') }}",
                    data: function(d) {
                        d.type = 'table';
                        d.outlet_id = $('#filter_outlet_id').val();
                        d.school_id = $('#filter_school_id').val();
                        d.classroom_id = $('#filter_classroom_id').val();
                        d.start_date = start_date;
                        d.end_date = end_date;
                    }
                },
                columns: [
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'date',
                        name: 'date',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Date';
                        }
                    },
                    {
                        data: 'student.nis',
                        name: 'student.nis',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No NIS';
                        }
                    },
                    {
                        data: 'student.name',
                        name: 'student.name',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                            var name = data ? data : 'Unknown Student';
                            var className = (row.student && row.student.classroom && row.student.classroom.name) ? row.student.classroom.name : '-';
                            return `<div class="d-flex flex-column text-start">
                                        <span class="fw-bolder">${name}</span>
                                        <span class="badge badge-light-primary fw-bolder mt-1 align-self-start">${className}</span>
                                    </div>`;
                        }
                    },
                    {
                        data: 'outlet.name',
                        name: 'outlet.name',
                        defaultContent: '-',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Amount';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Status';
                        }
                    },
                    {
                        data: 'balance_before',
                        name: 'balance_before',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'balance_after',
                        name: 'balance_after',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        orderable: true,
                        searchable: true,
                        render: function(data) {
                            return data ? data : 'No Description';
                        }
                    },
                    @if (Auth::user()->can('Delete Laporan Saldo Santri'))
                    {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    }
                    @endif
                ],
                order: [[1, 'desc']]
            });
        }

        function getTotalSaldo(start_date = '', end_date = '') {
            $.ajax({
                url: "{{ route('report-saldo.index') }}",
                type: "GET",
                dataType: 'json',
                data: {
                    type: 'total',
                    outlet_id: $('#filter_outlet_id').val(),
                    school_id: $('#filter_school_id').val(),
                    classroom_id: $('#filter_classroom_id').val(),
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    $('#total-topup').text('Rp. ' + response.total_topup);
                    $('#total-pengurangan').text('Rp. ' + response.total_pengurangan);
                    $('#saldo-tersedia').text('Rp. ' + response.saldo_tersedia);
                }
            });
        }

        function reloadTable(start_date = '', end_date = '') {
            table.destroy();
            table = initializeTable(start_date, end_date);
            getTotalSaldo(start_date, end_date);
        }

        $('#btn_tampilkan').click(function() {
            reloadTable();
        });

        $('#filter_form').submit(function(event) {
            event.preventDefault();
            reloadTable();
        });

        $('#filter_outlet_id, #filter_school_id').on('change', function() {
            if ($(this).attr('id') === 'filter_school_id') {
                $('#filter_classroom_id').val('');
                $('#filter_classroom_btn').text('Semua Kelas');
                renderClassroomMegaMenu($(this).val());
            }
            reloadTable();
        });

        const allClassrooms = @json($classrooms);

        function renderClassroomMegaMenu(schoolId) {
            const container = $('#classroom_mega_menu');
            container.empty();

            if (!schoolId) {
                container.html('<div class="text-muted fs-7 mb-2">Pilih Lembaga terlebih dahulu</div>');
                return;
            }

            const filteredClasses = allClassrooms.filter(c => c.school_id == schoolId);
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

            const sortedKeys = Object.keys(groups).sort((a,b) => {
                let numA = parseInt(a);
                let numB = parseInt(b);
                if (isNaN(numA)) return 1;
                if (isNaN(numB)) return -1;
                return numA - numB;
            });

            const row = $('<div class="row g-2 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4"></div>');
            
            container.append($('<button type="button" class="btn btn-sm btn-light-primary w-100 fw-bold mb-3 classroom-item text-center rounded-2 py-2" data-id="" data-name="Semua Kelas" style="cursor: pointer;"><i class="fas fa-layer-group me-1" style="pointer-events: none;"></i><span style="pointer-events: none;">Semua Kelas</span></button>'));

            sortedKeys.forEach(key => {
                const col = $('<div class="col"></div>');
                const headerTitle = isNaN(parseInt(key)) ? key : 'Kelas ' + key;
                col.append(`<h6 class="dropdown-header text-uppercase text-muted fw-bolder px-1 mb-2 fs-8 border-bottom pb-1">${headerTitle}</h6>`);
                const list = $('<div class="d-flex flex-column gap-1"></div>');
                groups[key].forEach(c => {
                    list.append(`<button type="button" class="btn btn-sm btn-light btn-active-light-primary text-start w-100 py-1.5 px-2 mb-1 rounded-2 classroom-item fs-8 fw-semibold d-flex align-items-center justify-content-between text-truncate" data-id="${c.id}" data-name="${c.name}" style="cursor: pointer; transition: all 0.15s ease-in-out; min-height: 32px;">
                        <span class="text-truncate" style="pointer-events: none;">${c.name}</span>
                        <i class="fas fa-check text-white fs-9 d-none class-check-icon" style="pointer-events: none;"></i>
                    </button>`);
                });
                col.append(list);
                row.append(col);
            });

            container.append(row);
        }

        // Full hitbox toggle handler for classroom dropdown button
        $(document).on('click', '#filter_classroom_btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            var $menu = $('#classroom_mega_menu');
            var isShown = $menu.hasClass('show');

            if (isShown) {
                $menu.removeClass('show');
                $btn.removeClass('show').attr('aria-expanded', 'false');
                $btn.find('.filter-classroom-arrow').css('transform', 'rotate(0deg)');
            } else {
                $('.dropdown-menu.show').not($menu).removeClass('show');
                $('.dropdown-toggle[aria-expanded="true"]').not($btn).removeClass('show').attr('aria-expanded', 'false');
                $menu.addClass('show');
                $btn.addClass('show').attr('aria-expanded', 'true');
                $btn.find('.filter-classroom-arrow').css('transform', 'rotate(180deg)');
            }
        });

        // Prevent clicks inside dropdown menu from closing it prematurely
        $(document).on('click', '#classroom_mega_menu', function(e) {
            e.stopPropagation();
        });

        // Close when clicking anywhere outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#report_saldo_classroom_dropdown_container').length) {
                $('#classroom_mega_menu').removeClass('show');
                $('#filter_classroom_btn').removeClass('show').attr('aria-expanded', 'false');
                $('#filter_classroom_btn .filter-classroom-arrow').css('transform', 'rotate(0deg)');
            }
        });

        $(document).on('click', '.classroom-item', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#filter_classroom_id').val(id);
            if (id) {
                $('#filter_classroom_btn_text').html(`<i class="fas fa-chalkboard-user me-1 text-primary"></i> <span class="fw-bold">${name}</span>`);
            } else {
                $('#filter_classroom_btn_text').text(name);
            }

            $('.classroom-item').removeClass('active bg-primary text-white').addClass('btn-light text-slate-700');
            $('.classroom-item .class-check-icon').addClass('d-none');
            $(this).addClass('active bg-primary text-white').removeClass('btn-light text-slate-700');
            $(this).find('.class-check-icon').removeClass('d-none');

            $('#classroom_mega_menu').removeClass('show');
            $('#filter_classroom_btn').removeClass('show').attr('aria-expanded', 'false');
            $('#filter_classroom_btn .filter-classroom-arrow').css('transform', 'rotate(0deg)');

            reloadTable();
        });

        var searchTimer;
        $('#custom-search-student').on('keyup input', function() {
            clearTimeout(searchTimer);
            var val = $(this).val().trim();
            searchTimer = setTimeout(function() {
                table.search(val).draw();
            }, 300);
        });

        $('#dateRange').daterangepicker({
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
            }
        }, function(start, end) {
            $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });

        var start = moment().startOf('month');
        var end = moment().endOf('month');
        $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
        reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });
</script>
@endpush