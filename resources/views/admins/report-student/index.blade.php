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
                        <a href="{{ route('report-student.index') }}" class="text-muted text-hover-primary">Laporan
                            Siswa</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Siswa</li>
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
            <!--begin::Summary Cards-->
            <style>
                .summary-card {
                    border-radius: 24px !important;
                    border: none !important;
                    position: relative;
                    overflow: hidden;
                    transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), box-shadow 0.25s ease;
                    cursor: default;
                }
                .summary-card:hover {
                    transform: translateY(-6px) scale(1.015);
                }
                .summary-card .card-blob {
                    position: absolute;
                    border-radius: 50%;
                    opacity: 0.18;
                    pointer-events: none;
                }
                .summary-card .card-number {
                    font-size: 42px;
                    font-weight: 800;
                    line-height: 1;
                    letter-spacing: -1px;
                    font-family: 'Outfit', sans-serif;
                }
                .summary-card .card-label {
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.12em;
                    text-transform: uppercase;
                    opacity: 0.75;
                }
                .summary-card .card-icon-wrap {
                    width: 52px;
                    height: 52px;
                    border-radius: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 22px;
                    flex-shrink: 0;
                }
                .summary-card .card-sub {
                    font-size: 12px;
                    font-weight: 500;
                    opacity: 0.65;
                    margin-top: 6px;
                }
                /* Card 1 – Total (Blue) */
                .summary-card-total {
                    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%) !important;
                    box-shadow: 0 16px 48px rgba(37,99,235,0.35), 0 4px 12px rgba(37,99,235,0.2);
                    color: #fff;
                }
                .summary-card-total:hover {
                    box-shadow: 0 24px 64px rgba(37,99,235,0.45), 0 8px 24px rgba(37,99,235,0.25);
                }
                /* Card 2 – Putra (Cyan/Sky) */
                .summary-card-putra {
                    background: linear-gradient(135deg, #0c4a6e 0%, #0284c7 60%, #38bdf8 100%) !important;
                    box-shadow: 0 16px 48px rgba(2,132,199,0.35), 0 4px 12px rgba(2,132,199,0.2);
                    color: #fff;
                }
                .summary-card-putra:hover {
                    box-shadow: 0 24px 64px rgba(2,132,199,0.45), 0 8px 24px rgba(2,132,199,0.25);
                }
                /* Card 3 – Putri (Rose/Pink) */
                .summary-card-putri {
                    background: linear-gradient(135deg, #831843 0%, #db2777 60%, #f472b6 100%) !important;
                    box-shadow: 0 16px 48px rgba(219,39,119,0.35), 0 4px 12px rgba(219,39,119,0.2);
                    color: #fff;
                }
                .summary-card-putri:hover {
                    box-shadow: 0 24px 64px rgba(219,39,119,0.45), 0 8px 24px rgba(219,39,119,0.25);
                }
            </style>

            <div class="row row-cols-1 row-cols-md-3 g-5 mb-8 px-5">

                <!-- Card 1: Total Siswa -->
                <div class="col">
                    <div class="card summary-card summary-card-total p-6" style="padding: 28px 28px 24px 28px;">
                        <!-- Decorative blobs -->
                        <div class="card-blob" style="width:180px;height:180px;background:#fff;top:-60px;right:-60px;"></div>
                        <div class="card-blob" style="width:80px;height:80px;background:#fff;bottom:-20px;left:20px;"></div>
                        <!-- Content -->
                        <div style="position:relative;z-index:1;">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div>
                                    <div class="card-label" style="color:rgba(255,255,255,0.75);">TOTAL SISWA</div>
                                </div>
                                <div class="card-icon-wrap" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(8px);">
                                    <i class="fa-solid fa-users" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div id="summary-total" class="card-number" style="color:#fff;">{{ $summary['total'] }}</div>
                            <div class="card-sub" style="color:rgba(255,255,255,0.7);">SMP · MA · Pondok</div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Siswa Putra -->
                <div class="col">
                    <div class="card summary-card summary-card-putra p-6" style="padding: 28px 28px 24px 28px;">
                        <!-- Decorative blobs -->
                        <div class="card-blob" style="width:160px;height:160px;background:#fff;top:-50px;right:-50px;"></div>
                        <div class="card-blob" style="width:70px;height:70px;background:#fff;bottom:-15px;left:30px;"></div>
                        <!-- Content -->
                        <div style="position:relative;z-index:1;">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div>
                                    <div class="card-label" style="color:rgba(255,255,255,0.75);">SISWA PUTRA</div>
                                </div>
                                <div class="card-icon-wrap" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(8px);">
                                    <i class="fa-solid fa-mars" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div id="summary-male" class="card-number" style="color:#fff;">{{ $summary['total_male'] }}</div>
                            <div class="card-sub" style="color:rgba(255,255,255,0.7);">Laki-laki terdaftar</div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Siswa Putri -->
                <div class="col">
                    <div class="card summary-card summary-card-putri p-6" style="padding: 28px 28px 24px 28px;">
                        <!-- Decorative blobs -->
                        <div class="card-blob" style="width:170px;height:170px;background:#fff;top:-55px;right:-55px;"></div>
                        <div class="card-blob" style="width:75px;height:75px;background:#fff;bottom:-18px;left:25px;"></div>
                        <!-- Content -->
                        <div style="position:relative;z-index:1;">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div>
                                    <div class="card-label" style="color:rgba(255,255,255,0.75);">SISWA PUTRI</div>
                                </div>
                                <div class="card-icon-wrap" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(8px);">
                                    <i class="fa-solid fa-venus" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div id="summary-female" class="card-number" style="color:#fff;">{{ $summary['total_female'] }}</div>
                            <div class="card-sub" style="color:rgba(255,255,255,0.7);">Perempuan terdaftar</div>
                        </div>
                    </div>
                </div>

            </div>
            <!--end::Summary Cards-->

            <!--begin::Tabs Navigation-->
            <ul class="nav nav-tabs nav-line-tabs mb-6 fs-6 px-5" id="report_student_tabs" role="tablist" style="border-bottom: 2px solid #e2e8f0;">
                <li class="nav-item">
                    <a class="nav-link active fw-bolder text-active-primary px-4 py-3 cursor-pointer" data-tab="total" role="tab" style="font-family: 'Outfit', sans-serif;">Total Siswa (SMP, MA, Pondok)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3 cursor-pointer" data-tab="active" role="tab" style="font-family: 'Outfit', sans-serif;">Siswa Aktif</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3 cursor-pointer" data-tab="graduated" role="tab" style="font-family: 'Outfit', sans-serif;">Siswa Lulus</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder text-active-primary px-4 py-3 cursor-pointer" data-tab="dropped_out" role="tab" style="font-family: 'Outfit', sans-serif;">Siswa Keluar</a>
                </li>
            </ul>
            <!--end::Tabs Navigation-->
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-end gap-5 flex-sm-row justify-content-between border-0 pt-6 pb-2">
                    <div class="d-flex flex-wrap justify-content-between gap-5">
                        <div class="mb-0">
                             <form action="{{ route('report-student.export') }}" id="form-filter" method="get">
                                  <input type="text" hidden id="type" name="type" required>
                                  <input type="hidden" name="tab" id="tab_hidden" value="total">
                                  <div class="d-flex flex-wrap gap-4 align-items-end">
                                      <div>
                                          <label class="form-label">Tahun Ajaran</label>
                                          <select name="academic_year_id" class="form-select" id="filter_academic_year_id">
                                              <option value="">Pilih Tahun Ajaran</option>
                                              @foreach ($academicYears as $year)
                                              <option value="{{ $year->id }}">{{ $year->name }}</option>
                                              @endforeach
                                          </select>
                                      </div>
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
                                         <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                             <option value="">Pilih Kelas</option>
                                         </select>
                                     </div>
                                     <div>
                                         <label class="form-label">Nama / NIS</label>
                                         <div class="d-flex align-items-center position-relative">
                                             <span class="svg-icon svg-icon-1 position-absolute ms-3">
                                                 <i class="fas fa-search text-gray-400"></i>
                                             </span>
                                             <input type="text" id="filter_student_name" name="student_name" class="form-control form-control-solid ps-9" placeholder="Cari Nama / NIS..." style="width: 200px;" />
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
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>UPT</th>
                                    <th>Tunggakan</th>
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
            <div class="modal fade" id="tunggakanModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]">
                        <div class="modal-header border-0 pb-0 pt-7 px-8 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-slate-400 fw-bold uppercase tracking-widest" style="font-size: 11px;">Rincian Tunggakan</span>
                                <h2 class="modal-title fw-bolder text-slate-900 mt-1" style="font-size: 18px; font-family: 'Outfit', sans-serif;" id="tunggakanModalLabel">Nama Siswa</h2>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-8 py-6" style="max-height: 60vh; overflow-y: auto;">
                            <div id="tunggakan-container" class="row g-4">
                                <!-- Grouped bills will be inserted here dynamically -->
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light-soft px-8 py-5 d-flex justify-content-between align-items-center rounded-bottom-[24px]" style="background: rgba(0, 0, 0, 0.01); border-top: 1px solid #f1f5f9;">
                            <div>
                                <span class="text-slate-400 fw-bold uppercase tracking-widest" style="font-size: 10px;">Total Seluruh Tunggakan</span>
                                <div class="fw-bold text-danger mt-1" style="font-size: 20px;" id="tunggakan-grand-total">Rp 0</div>
                            </div>
                            <button type="button" class="btn btn-secondary rounded-xl px-5" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Modals-->
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
    // onchange school_id get data classrom on school
        $('#filter_school_id').on('change', function() {
            var school_id = $(this).val();
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: {
                    school_id: school_id
                },
                success: function(response) {
                    console.log(response);
                    $('#filter_classroom_id').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name +
                                '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
                    }
                }
            });
        });

       $(document).ready(function() {
    var currentTab = 'total';

    // Tabs Navigation Change
    $('#report_student_tabs a').on('click', function(e) {
        e.preventDefault();
        $('#report_student_tabs a').removeClass('active');
        $(this).addClass('active');
        currentTab = $(this).data('tab');
        
        // Update label of Card 1
        var labels = {
            'total': 'TOTAL SISWA',
            'active': 'SISWA AKTIF',
            'graduated': 'SISWA LULUS',
            'dropped_out': 'SISWA KELUAR'
        };
        $('#summary-total-label').text(labels[currentTab]);
        
        searchData();
    });

    // Inisialisasi DataTables
    var table = $('#table-report-bill').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: "{{ route('report-student.index') }}",
    data: function(d) {
    // Mengambil data filter dari elemen formulir
    d.school_id = $('#filter_school_id').val();
    d.classroom_id = $('#filter_classroom_id').val();
    d.academic_year_id = $('#filter_academic_year_id').val();
    d.student_name = $('#filter_student_name').val();
    d.tab = currentTab;
    }
    },
    drawCallback: function(settings) {
        $('[data-bs-toggle="tooltip"]').tooltip();
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
    { data: 'nis', name: 'students.nis' },
    { data: 'nisn', name: 'students.nisn' },
    { data: 'name', name: 'students.name' },
    { data: 'classroom.name', name: 'classroom.name' },
    { data: 'school.name', name: 'school.name', defaultContent: '-' },
    {
    data: 'unpaid_bills',
    name: 'unpaid_bills',
    orderable: true,
    searchable: false
    }
    ]
    });

    table.on('xhr.dt', function(e, settings, json, xhr) {
        if (json && json.summary) {
            $('#summary-total').text(json.summary.total);
            $('#summary-male').text(json.summary.total_male);
            $('#summary-female').text(json.summary.total_female);
        }
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

    // onchange school_id, classroom_id and academic_year_id reload datatable
    $('#filter_school_id, #filter_classroom_id, #filter_academic_year_id').on('change', function() {
        searchData();
    });

    var reportStudentNameTimer;
    $('#filter_student_name').on('keyup input', function() {
        clearTimeout(reportStudentNameTimer);
        reportStudentNameTimer = setTimeout(function() {
            searchData();
        }, 300);
    });

    // Export Report
    $('.btn-export').on('click', function() {
        var type = $(this).data('type');
        $('#type').val(type);
        $('#tab_hidden').val(currentTab);
        $('#form-filter').submit();
    });

    $(document).on('click', '.btn-show-tunggakan', function() {
        var studentName = $(this).data('name');
        var bills = $(this).data('bills');
        
        $('#tunggakanModalLabel').text(studentName);
        
        // Group bills by type
        var grouped = {};
        var grandTotal = 0;
        
        if (bills && Array.isArray(bills)) {
            bills.forEach(function(bill) {
                if (!grouped[bill.bill_type]) {
                    grouped[bill.bill_type] = {
                        items: [],
                        total: 0
                    };
                }
                grouped[bill.bill_type].items.push(bill);
                grouped[bill.bill_type].total += bill.amount;
                grandTotal += bill.amount;
            });
        }
        
        var html = '';
        Object.keys(grouped).forEach(function(type) {
            var group = grouped[type];
            var itemsHtml = '';
            
            group.items.forEach(function(item) {
                var period = item.month ? item.month + ' ' + item.year : item.year;
                itemsHtml += `
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px dashed #e2e8f0 !important;">
                        <span class="text-slate-600" style="font-size: 13px;">${period}</span>
                        <span class="fw-bold text-slate-800" style="font-size: 13px;">Rp ${formatRupiah(item.amount)}</span>
                    </div>
                `;
            });
            
            html += `
                <div class="col-12 col-md-6">
                    <div class="card p-5 rounded-[16px]" style="border: 1px solid #e2e8f0 !important; background: #fafafa;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bolder text-slate-900" style="font-size: 14px; font-family: 'Outfit', sans-serif;">${type}</span>
                            <span class="fw-bold text-danger" style="font-size: 14px;">Total: Rp ${formatRupiah(group.total)}</span>
                        </div>
                        <div class="d-flex flex-column">
                            ${itemsHtml}
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (html === '') {
            html = '<div class="col-12 text-center py-5 text-slate-400">Tidak ada tunggakan</div>';
        }
        
        $('#tunggakan-container').html(html);
        $('#tunggakan-grand-total').text('Rp ' + formatRupiah(grandTotal));
        
        $('#tunggakanModal').modal('show');
    });
    
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }
    });

</script>
@endpush