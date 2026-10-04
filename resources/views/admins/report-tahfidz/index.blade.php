@extends('layouts.master', ['title' => 'Laporan Tahfidz'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-tahfidz.index') }}" class="text-muted text-hover-primary">Laporan
                            Tahfidz</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Tahfidz</li>
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
                        {{-- <div class="mb-0">
                            <label class="form-label">Filter Tanggal</label>
                            <div class="d-flex
                                                gap-4 align-items-end">
                                <div id="dateRange" class="pull-right"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;float: top;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                    <span></span> <b class="caret"></b>
                                </div>
                            </div>
                        </div> --}}
                        <div class="mb-0">
                            <form action="{{ route('report-tahfidz.export') }}" id="form-filter" method="get">
                                <input type="text" hidden id="type" name="type" required>
                                <div class="d-flex flex-wrap gap-4 align-items-end">
                                    <div>
                                        <label class="form-label">UPT</label>
                                        <select name="school_id" class="form-select" id="filter_school_id">
                                            <option value="">Pilih Pendidikan</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Kelas</label>
                                        <div class="dropdown" id="report_tahfidz_classroom_dropdown_container" style="position: relative !important;">
                                            <input type="hidden" name="classroom_id" id="filter_classroom_id" value="">
                                            <button class="btn btn-light border bg-white fs-7 d-flex justify-content-between align-items-center" type="button" id="filter_classroom_btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-width: 140px; height: 38px; cursor: pointer;">
                                                <span id="filter_classroom_btn_text" class="text-truncate me-2" style="pointer-events: none;">Semua Kelas</span>
                                                <i class="fas fa-chevron-down fs-8 text-gray-500 filter-classroom-arrow" style="pointer-events: none; transition: transform 0.2s ease;"></i>
                                            </button>
                                            <div class="dropdown-menu p-3 shadow-lg border-0" style="min-width: 260px; width: 500px; max-width: calc(100vw - 32px); max-height: 420px; overflow-y: auto; border-radius: 16px; position: absolute !important; top: 100% !important; right: 0 !important; left: auto !important; margin-top: 6px !important; z-index: 9999 !important;" aria-labelledby="filter_classroom_btn" id="classroom_mega_menu">
                                                <div class="text-muted fs-7 p-3 text-center">Pilih Pendidikan terlebih dahulu</div>
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
                        </div>
                    </div>
                    <div class="mt-4 gap-2 d-flex justify-content-beetween align-items-end">

                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body">
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        {{-- <h3 class="text-dark">Sekolah</h3> --}}
                    </div>
                    <div class="">
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-report-bill" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Tanggal</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Jumlah Halaman</th>
                                    <th>Keterangan</th>
                                    <th>Feedback</th>
                                    <th>Link</th>
                                    {{-- <th class="text-center min-w-100px" style="width: 22%">Aksi</th> --}}
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
            <!--begin::Modals-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    function renderTahfidzClassroomMegaMenu(classrooms) {
        const container = $('#classroom_mega_menu');
        container.empty();

        if (!classrooms || classrooms.length === 0) {
            container.html('<div class="text-muted fs-7 p-3 text-center">Tidak ada kelas tersedia</div>');
            return;
        }

        const groups = {};
        classrooms.forEach(c => {
            let match = (c.name || '').match(/^(\d+)/);
            let key = match ? match[1] : 'Lainnya';
            if (!groups[key]) groups[key] = [];
            groups[key].push(c);
        });

        const resetBtn = $('<button type="button" class="btn btn-sm btn-light-primary w-100 fw-bold mb-2 classroom-item text-center rounded-2 py-1 fs-8" data-id="" data-name="Semua Kelas" style="cursor: pointer;"><i class="fas fa-layer-group me-1" style="pointer-events: none;"></i><span style="pointer-events: none;">Semua Kelas</span></button>');
        container.append(resetBtn);

        const sortedKeys = Object.keys(groups).sort((a,b) => {
            let numA = parseInt(a);
            let numB = parseInt(b);
            if (isNaN(numA)) return 1;
            if (isNaN(numB)) return -1;
            return numA - numB;
        });

        const colCount = sortedKeys.length;
        let colClass = 'col-12';
        let menuWidth = '150px';
        if (colCount === 2) {
            colClass = 'col-6';
            menuWidth = '230px';
        } else if (colCount === 3) {
            colClass = 'col-4';
            menuWidth = '310px';
        } else if (colCount >= 4) {
            colClass = 'col-3';
            menuWidth = '400px';
        }
        container.css({ 'width': menuWidth, 'min-width': menuWidth, 'padding': '10px' });

        const row = $('<div class="row g-1"></div>');
        sortedKeys.forEach(key => {
            const col = $(`<div class="${colClass}"></div>`);
            const headerTitle = isNaN(parseInt(key)) ? key : 'Kelas ' + key;
            col.append(`<h6 class="dropdown-header text-uppercase text-muted fw-bolder px-1 mb-1 fs-9 border-bottom pb-1">${headerTitle}</h6>`);
            const list = $('<div class="d-flex flex-column" style="gap: 3px;"></div>');
            groups[key].forEach(c => {
                list.append(`<button type="button" class="btn btn-sm btn-light btn-active-light-primary text-start w-100 px-2 rounded-2 classroom-item fs-8 fw-semibold d-flex align-items-center justify-content-between text-truncate" data-id="${c.id}" data-name="${c.name}" style="cursor: pointer; transition: all 0.15s ease-in-out; min-height: 26px; padding-top: 3px; padding-bottom: 3px;">
                    <span class="text-truncate" style="pointer-events: none;">${c.name}</span>
                    <i class="fas fa-check text-primary fs-9 d-none class-check-icon" style="pointer-events: none;"></i>
                </button>`);
            });
            col.append(list);
            row.append(col);
        });
        container.append(row);
    }

    $(document).on('click', '#classroom_mega_menu .classroom-item', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');

        $('#filter_classroom_id').val(id);
        if (id) {
            $('#filter_classroom_btn_text').html(`<i class="fas fa-chalkboard-user me-1 text-primary"></i> <span class="fw-bold">${name}</span>`);
        } else {
            $('#filter_classroom_btn_text').text('Semua Kelas');
        }

        $('#classroom_mega_menu .class-check-icon').addClass('d-none');
        $('#classroom_mega_menu .classroom-item').removeClass('active btn-primary text-white').addClass('btn-light');
        if (id) {
            $(this).addClass('active btn-primary text-white').removeClass('btn-light');
            $(this).find('.class-check-icon').removeClass('d-none');
        }

        const dropdownEl = document.getElementById('classroom_mega_menu');
        if (dropdownEl) {
            const bsDropdown = bootstrap.Dropdown.getInstance(document.getElementById('filter_classroom_btn'));
            if (bsDropdown) bsDropdown.hide();
        }

        searchData();
    });

    $('#report_tahfidz_classroom_dropdown_container').on('show.bs.dropdown', function () {
        const $btn = $('#filter_classroom_btn');
        const $menu = $('#classroom_mega_menu');
        const btnOffset = $btn.offset();
        const menuWidth = $menu.outerWidth() || 500;
        const winWidth = $(window).width();
        if (btnOffset && (btnOffset.left + menuWidth > winWidth - 20)) {
            $menu.css({ 'left': 'auto', 'right': '0' });
        } else {
            $menu.css({ 'left': '0', 'right': 'auto' });
        }
    });

    // onchange school_id get data classrom on school
    $('#filter_school_id').on('change', function() {
        var school_id = $(this).val();
        $('#filter_classroom_id').val('');
        $('#filter_classroom_btn_text').text('Semua Kelas');

        if (!school_id) {
            $('#classroom_mega_menu').html('<div class="text-muted fs-7 p-3 text-center">Pilih Pendidikan terlebih dahulu</div>');
            searchData();
            return;
        }

        $.ajax({
            url: "{{ route('report-bill.get-classroom') }}",
            type: "GET",
            data: {
                school_id: school_id
            },
            success: function(response) {
                renderTahfidzClassroomMegaMenu(response.data || []);
            }
        });

        searchData();
    });

       $(document).ready(function() {
    // Inisialisasi DataTables
    var table = $('#table-report-bill').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: "{{ route('report-tahfidz.index') }}",
    data: function(d) {
    // Mengambil data filter dari elemen formulir
    d.school_id = $('#filter_school_id').val();
    d.classroom_id = $('#filter_classroom_id').val();
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
        data: 'deposit_date',
        name: 'deposit_date'
    },
    { data: 'student.nis', name: 'student.nis' },
    { data: 'student.name', name: 'student.name',
        responsivePriority: -1,
    },
    { data: 'number_of_pages', name: 'number_of_pages' },
    { data: 'note', name: 'note' },
    { data: 'feedback', name: 'feedback' },
    { data: 'link', name: 'link',
        responsivePriority: -1,
     },
    ]
    });
    
    // Fungsi untuk memperbarui data tabel saat melakukan pencarian
    function searchData() {
        table.ajax.reload();
    }
    
    // Event saat tombol "Tampilkan" ditekan
    $('#btn_tampilkan').click(function() {
    searchData();
    });
    
    // Event saat formulir filter disubmit
    $('#filter_form').submit(function(event) {
    event.preventDefault(); // Mencegah aksi default saat submit
    searchData();
    });

    // onchange school_id and classroom_id reload datatable
    $('#filter_school_id, #filter_classroom_id').on('change', function() {
        searchData();
    });

    // Export Report
    $('.btn-export').on('click', function() {
        var type = $(this).data('type');
        $('#type').val(type);
        $('#school_id').val($('#filter_school_id').val());
        $('#classroom_id').val($('#filter_classroom_id').val());
        $('#form-filter').submit();
    });
    });

</script>
@endpush