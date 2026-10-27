@extends('layouts.master', ['title' => 'Detail Laporan Tagihan'])

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!-- Main Container with Top Padding to avoid Navbar overlap -->
        <div class="container-xxl" id="kt_content_container">

            <!-- Header Section (Title & Breadcrumb) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-6">
                <div class="d-flex flex-column">
                    <h1 class="text-dark fw-bolder fs-2 mb-2">
                        {{ $billType->formatted_name ?? 'Laporan Tagihan' }}
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('report-bill.index') }}" class="text-muted text-hover-primary">Laporan</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Detail</li>
                    </ul>
                </div>
                <!-- Optional: Top Actions can go here if needed in future -->
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm border-0 mb-8 rounded-3">
                <div class="card-body py-4">
                    <form id="filter_form">
                        <div class="row g-4 align-items-end">
                            <!-- Periode -->
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fs-7 fw-bolder text-uppercase text-gray-500 mb-2">Periode</label>
                                <div class="input-group input-group-solid border rounded-3 overflow-hidden">
                                    <span class="input-group-text border-0 bg-white"><i
                                            class="fa fa-calendar-alt text-primary"></i></span>
                                    <div id="dateRange" class="form-control form-control-solid bg-white border-0 fw-bold"
                                        style="cursor: pointer;">
                                        <span>Loading...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cari Nama Siswa -->
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fs-7 fw-bolder text-uppercase text-gray-500 mb-2">Cari Nama Siswa</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 translate-middle-y ms-3 text-gray-400">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <input type="text" id="search_student_name"
                                        class="form-control form-control-solid bg-white border ps-10"
                                        placeholder="Cari Nama Siswa..." autocomplete="off">
                                </div>
                            </div>

                            <!-- Classroom -->
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fs-7 fw-bolder text-uppercase text-gray-500 mb-2">Kelas</label>
                                <div class="dropdown" id="report_bill_classroom_dropdown_container" style="position: relative !important;">
                                    <input type="hidden" name="classroom_id" id="filter_classroom_id" value="">
                                    <button class="btn btn-light border bg-white fs-7 d-flex justify-content-between align-items-center w-100"
                                        type="button" id="filter_classroom_btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" style="min-width: 140px; height: 42px; cursor: pointer;">
                                        <span id="filter_classroom_btn_text" class="text-truncate me-2" style="pointer-events: none;">Semua Kelas</span>
                                        <i class="fas fa-chevron-down fs-8 text-gray-500 filter-classroom-arrow" style="pointer-events: none; transition: transform 0.2s ease;"></i>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-lg border-0" id="classroom_mega_menu"
                                        style="min-width: 320px; width: 330px; max-width: calc(100vw - 32px); max-height: 420px; overflow-y: auto; border-radius: 16px; position: absolute !important; top: 100% !important; margin-top: 6px !important; z-index: 9999 !important;"
                                        aria-labelledby="filter_classroom_btn">
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fs-7 fw-bolder text-uppercase text-gray-500 mb-2">Status</label>
                                <select name="class_id" class="form-select form-select-solid bg-white border"
                                    id="status">
                                    <option value="">Semua Status</option>
                                    <option value="PAID">Lunas</option>
                                    <option value="UNPAID">Belum Lunas</option>
                                </select>
                            </div>

                            <!-- Actions Buttons (Full width on mobile, auto on desktop) -->
                            <div class="col-12 d-flex flex-column flex-md-row justify-content-end gap-2 mt-4 mt-xl-0">
                                <button type="button"
                                    class="btn btn-light-primary border border-primary fw-bold w-100 w-md-auto"
                                    id="btn-export">
                                    <i class="fa fa-file-excel me-2"></i> Export
                                </button>
                                <button type="button" class="btn btn-success fw-bold w-100 w-md-auto" id="send-wa">
                                    <i class="fab fa-whatsapp me-2 fs-4"></i> Kirim WA
                                    <span id="spinner" class="spinner-border spinner-border-sm ms-2"
                                        style="display: none;" role="status"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards Section -->
            <div class="row g-4 mb-8">
                <!-- Target Card -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100 position-relative overflow-hidden bg-white">
                        <div class="position-absolute top-0 start-0 bottom-0 bg-primary" style="width: 4px;"></div>
                        <div class="card-body p-5 ps-8 d-flex flex-column justify-content-center">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="fa fa-bullseye text-primary fs-3"></i>
                                    </span>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fs-7 text-uppercase fw-bold text-gray-500 spacing-1 text-truncate">Target
                                        Pemasukkan</div>
                                    <div class="fs-2x fw-bolder text-dark" id="total">Rp 0</div>
                                </div>
                            </div>
                            <div class="text-muted fs-7">
                                Total potensi pendapatan.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Realization Card -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100 position-relative overflow-hidden bg-white">
                        <div class="position-absolute top-0 start-0 bottom-0 bg-success" style="width: 4px;"></div>
                        <div class="card-body p-5 ps-8">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="overflow-hidden">
                                        <div class="fs-7 text-uppercase fw-bold text-gray-500 spacing-1 text-truncate">
                                            Realisasi</div>
                                        <div class="fs-2x fw-bolder text-dark" id="total_paid">Rp 0</div>
                                    </div>
                                    <div class="symbol symbol-45px ms-2">
                                        <span class="symbol-label bg-light-success">
                                            <i class="fa fa-wallet text-success fs-3"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="d-flex flex-column mt-2">
                                    <div class="d-flex justify-content-between align-items-end mb-1">
                                        <span class="fw-bold text-gray-600 fs-7">Progress</span>
                                        <span class="fw-bolder text-dark fs-7" id="progress-bar-label">0%</span>
                                    </div>
                                    <div class="progress h-8px w-100 rounded-pill bg-light">
                                        <div class="progress-bar bg-success rounded-pill" role="progressbar"
                                            id="progress-bar" style="width: 0%; transition: width 1s ease;"
                                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Due Card -->
                <div class="col-12 col-md-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100 position-relative overflow-hidden bg-white">
                        <div class="position-absolute top-0 start-0 bottom-0 bg-danger" style="width: 4px;"></div>
                        <div class="card-body p-5 ps-8 d-flex flex-column justify-content-center">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-light-danger">
                                        <i class="fa fa-exclamation-triangle text-danger fs-3"></i>
                                    </span>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fs-7 text-uppercase fw-bold text-gray-500 spacing-1 text-truncate">
                                        Tanggungan Berjalan</div>
                                    <div class="fs-2x fw-bolder text-danger" id="total_current_due">Rp 0</div>
                                </div>
                            </div>
                            <div class="text-muted fs-7">
                                Total tagihan nunggak hingga saat ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-report-bill"
                            class="table align-middle table-row-dashed table-striped gs-6 gy-5 m-0 first-last-col-padding">
                            <thead class="border-bottom border-gray-200 fs-7 fw-bolder">
                                <tr class="text-start text-gray-400 text-uppercase gs-0">
                                    <th class="ps-4" style="width: 5%">No</th>
                                    <th class="min-w-150px">Nama Siswa</th>
                                    <th class="min-w-100px">Kelas</th>
                                    <th class="min-w-125px">Total Tagihan</th>
                                    <th class="min-w-125px">Dibayar</th>
                                    <th class="min-w-100px text-danger">Tanggungan</th>
                                    <th class="min-w-100px text-gray-500">Total Sisa Tagihan</th>
                                    <th class="text-center min-w-70px" style="width: 15%">Status</th>
                                    <th class="text-center pe-4" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bottom Spacer -->
            <div class="mb-10"></div>
        </div>

        <!-- Modal Detail Tagihan Siswa (Wide Modal) -->
        <div class="modal fade" id="modal-rincian" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 1200px;">
                <div class="modal-content rounded-[24px] border-0 shadow-lg overflow-hidden bg-white">
                    <div class="modal-header border-0 pb-0 pt-6 px-8 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="modal-title fw-bolder text-dark fs-3 mb-1">Rincian Tagihan Siswa</h3>
                            <p class="text-muted fs-7 mb-0" id="modal_student_subtitle">Memuat data rincian tagihan...</p>
                        </div>
                        <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-primary rounded-circle" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times fs-4"></i>
                        </button>
                    </div>
                    <div class="modal-body py-6 px-8" id="modal_detail_bill_content">
                        <div class="text-center py-12">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                            <div class="mt-3 text-gray-600 fw-semibold fs-6">Memuat rincian tagihan...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script>
        $(document).ready(function() {
            // Classroom Mega Menu Grouping (3 Kolom untuk Kelas 10, 11, 12)
            const rawClassrooms = @json($classrooms ?? []);

            function renderBillClassroomMegaMenu(classes) {
                const container = $('#classroom_mega_menu');
                container.empty();

                if (!classes || classes.length === 0) {
                    container.html('<div class="text-muted fs-7 p-3 text-center">Tidak ada kelas ditemukan</div>');
                    return;
                }

                const groups = {};
                classes.forEach(c => {
                    let match = (c.name || '').match(/^(\d+)/);
                    let key = match ? match[1] : 'Lainnya';
                    if (!groups[key]) groups[key] = [];
                    groups[key].push(c);
                });

                const sortedKeys = Object.keys(groups).sort((a, b) => {
                    const na = parseInt(a), nb = parseInt(b);
                    if (isNaN(na) && isNaN(nb)) return a.localeCompare(b);
                    if (isNaN(na)) return 1;
                    if (isNaN(nb)) return -1;
                    return na - nb;
                });

                const colCount = sortedKeys.length;
                let colClass = 'col-12';
                let menuWidth = '160px';
                if (colCount === 2) {
                    colClass = 'col-6';
                    menuWidth = '240px';
                } else if (colCount === 3) {
                    colClass = 'col-4';
                    menuWidth = '330px';
                } else if (colCount >= 4) {
                    colClass = 'col-3';
                    menuWidth = '420px';
                }
                container.css({ 'width': menuWidth, 'min-width': menuWidth, 'padding': '10px' });

                const resetBtn = $('<button type="button" class="btn btn-sm btn-light-primary w-100 fw-bold mb-2 classroom-item text-center rounded-2 py-1 fs-8" data-id="" data-name="Semua Kelas" style="cursor: pointer;"><i class="fas fa-layer-group me-1" style="pointer-events: none;"></i><span style="pointer-events: none;">Semua Kelas</span></button>');
                container.append(resetBtn);

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

            renderBillClassroomMegaMenu(rawClassrooms);

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

                reloadTable();
            });

            $('#report_bill_classroom_dropdown_container').on('show.bs.dropdown', function () {
                const $btn = $('#filter_classroom_btn');
                const $menu = $('#classroom_mega_menu');
                const btnOffset = $btn.offset();
                const menuWidth = $menu.outerWidth() || 330;
                const winWidth = $(window).width();
                if (btnOffset && (btnOffset.left + menuWidth > winWidth - 20)) {
                    $menu.css({ 'left': 'auto', 'right': '0' });
                } else {
                    $menu.css({ 'left': '0', 'right': 'auto' });
                }
            });

            // Event listeners for filters
            $('#status').on('change', function() {
                reloadTable();
            });

            // Live search nama siswa with debounce
            let searchTimeout;
            $('#search_student_name').on('keyup', function() {
                clearTimeout(searchTimeout);
                let val = $(this).val();
                searchTimeout = setTimeout(function() {
                    $('#table-report-bill').DataTable().search(val).draw();
                }, 300);
            });

            // Click handler for Detail button to open wide modal
            $(document).on('click', '.btn-detail-bill', function(e) {
                e.preventDefault();
                const studentId = $(this).data('student-id');
                const billTypeId = $(this).data('bill-type-id');
                const studentName = $(this).data('student-name');

                $('#modal_student_subtitle').text('Data rincian tagihan untuk ' + studentName);
                $('#modal_detail_bill_content').html(`
                    <div class="text-center py-12">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                        <div class="mt-3 text-gray-600 fw-semibold fs-6">Memuat rincian tagihan ${studentName}...</div>
                    </div>
                `);
                $('#modal-rincian').modal('show');

                $.ajax({
                    url: "{{ route('bill.summary-bill') }}",
                    type: "GET",
                    data: {
                        student_id: studentId,
                        bill_type_id: billTypeId
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#modal_detail_bill_content').html(response);
                    },
                    error: function(xhr) {
                        let msg = 'Gagal memuat rincian tagihan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#modal_detail_bill_content').html(`
                            <div class="alert alert-danger d-flex align-items-center p-5 rounded-3 mb-0">
                                <i class="fas fa-exclamation-circle fs-2 me-3 text-danger"></i>
                                <div class="d-flex flex-column">
                                    <h5 class="mb-1 text-danger">Terjadi Kesalahan</h5>
                                    <span>${msg}</span>
                                </div>
                            </div>
                        `);
                    }
                });
            });

            // Initial call to fetch totals
            // getTotalBill();

            $('#send-wa').on('click', function() {
                const button = $('#send-wa');
                const originalText = 'Kirim <i class="bi bi-whatsapp"></i>';

                // Change button text and show the spinner
                button.html(
                    'Mengirim Notif Tagihan... <span id="spinner" class="spinner-border spinner-border-sm" role="status"></span>'
                );
                $('#spinner').show();

                // Simulate the delay or call your AJAX request
                setTimeout(() => {
                    // Your AJAX request
                    $.ajax({
                        url: "{{ route('report-bill.send-wa', $billType->id) }}",
                        type: "GET",
                        success: function(response) {
                            if (response.code == 200) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }

                            // Hide the spinner and reset the button text after the request is done
                            $('#spinner').hide();
                            button.html(originalText);
                        },
                        error: function() {
                            // Handle errors and reset the button
                            toastr.error('Error occurred while sending notification.');
                            $('#spinner').hide();
                            button.html(originalText);
                        }
                    });
                }, 2000); // Adjust this delay if needed
            });

            $('#btn-export').on('click', function(e) {
                e.preventDefault();
                let classroom_id = $('#filter_classroom_id').val();
                let status = $('#status').val();
                let student_name = $('#search_student_name').val();
                let start_date = $('#dateRange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                let end_date = $('#dateRange').data('daterangepicker').endDate.format('YYYY-MM-DD');

                let url = "{{ route('report-bill.export', $billType->id) }}";
                let params = new URLSearchParams({
                    start_date: start_date,
                    end_date: end_date,
                    classroom_id: classroom_id,
                    status: status,
                    student_name: student_name
                });

                window.location.href = url + "?" + params.toString();
            });

            // Logic Tahun Ajaran (Academic Year)
            // Jika bulan >= Juli (6), maka Tahun Ajaran = Tahun ini s/d Tahun Depan
            // Jika bulan < Juli (6), maka Tahun Ajaran = Tahun Lalu s/d Tahun Ini
            var currentMonth = moment().month(); // 0 = Jan, 11 = Dec
            var startAcademicYear, endAcademicYear;

            if (currentMonth >= 6) { // Juli ke atas
                startAcademicYear = moment().month(6).startOf('month'); // 1 Juli
                endAcademicYear = moment().add(1, 'year').month(5).endOf('month'); // 30 Juni
            } else { // Januari - Juni
                startAcademicYear = moment().subtract(1, 'year').month(6).startOf('month');
                endAcademicYear = moment().month(5).endOf('month');
            }

            var startLastAcademicYear = startAcademicYear.clone().subtract(1, 'year');
            var endLastAcademicYear = endAcademicYear.clone().subtract(1, 'year');

            // Default State: Semua Periode (Range Lebar)
            var startDefault = moment().subtract(10, 'year').startOf('year');
            var endDefault = moment().add(5, 'year').endOf('year');

            function cb(start, end, label) {
                // Logic Label: Jika range sama dengan default "Semua Periode", tampilkan teks statis
                if (label === 'Semua Periode' || (start.isSame(startDefault, 'day') && end.isSame(endDefault,
                        'day'))) {
                    $('#dateRange span').html('Semua Periode');
                } else {
                    $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                }
                reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }

            $('#dateRange').daterangepicker({
                startDate: startDefault,
                endDate: endDefault,
                ranges: {
                    'Semua Periode': [startDefault, endDefault],
                    'Tahun Ajaran Ini': [startAcademicYear, endAcademicYear],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    '3 Bulan Terakhir': [moment().subtract(2, 'month').startOf('month'), moment().endOf(
                        'month')],
                    '6 Bulan Terakhir': [moment().subtract(5, 'month').startOf('month'), moment().endOf(
                        'month')],
                    'Tahun Ajaran Lalu': [startLastAcademicYear, endLastAcademicYear]
                },
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal',
                    fromLabel: 'Dari',
                    toLabel: 'Sampai',
                    customRangeLabel: 'Kustom',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                }
            }, cb);

            // Initial Call
            cb(startDefault, endDefault, 'Semua Periode');

        });

        function initializeTable(start_date = '', end_date = '') {
            return $('#table-report-bill').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                ordering: true,
                ajax: {
                    url: "{{ route('report-bill.show', $billType->id) }}",
                    data: function(d) {
                        d.type = 'bill';
                        d.classroom_id = $('#filter_classroom_id').val();
                        d.status = $('#status').val();
                        d.start_date = start_date;
                        d.end_date = end_date;
                    }
                },
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Semua"]
                ],
                columns: [{
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'classroom_name',
                        name: 'classrooms.name'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid'
                    },
                    {
                        data: 'current_due',
                        name: 'current_due'
                    },
                    {
                        data: 'future_bill',
                        name: 'future_bill'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        responsivePriority: -1
                    },
                    {
                        data: 'action',
                        name: 'action',
                        responsivePriority: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }

        // Function to reload DataTable
        function reloadTable(start_date = '', end_date = '') {
            var table = $('#table-report-bill').DataTable();
            table.destroy(); // Destroy DataTable
            table = initializeTable(start_date, end_date); // Reinitialize DataTable
            let currentSearch = $('#search_student_name').val();
            if (currentSearch) {
                table.search(currentSearch).draw();
            }
            getTotalBill(start_date, end_date); // Fetch totals
        }

        // Function to get total bill information
        function getTotalBill(start_date = '', end_date = '') {
            $.ajax({
                url: "{{ route('report-bill.show', $billType->id) }}",
                type: "GET",
                dataType: 'json',
                data: {
                    type: 'total',
                    classroom_id: $('#filter_classroom_id').val(),
                    status: $('#status').val(),
                    start_date: start_date, // Kirim parameter tanggal
                    end_date: end_date // Kirim parameter tanggal
                },
                success: function(response) {
                    $('#total').text('Rp. ' + response.total);
                    $('#total_paid').text('Rp. ' + response.total_paid);

                    // Progress Bar Logic Fix: Logic Percentage Float
                    let percentage = parseFloat(response.realisasion_percentage);
                    let percentageText = response.realisasion_percentage_text;

                    $('#progress-bar').css('width', percentage + '%');
                    $('#progress-bar-label').text(percentageText);

                    // Progress Bar Color Logic
                    let progressBar = $('#progress-bar');
                    progressBar.removeClass('bg-warning bg-success bg-primary');

                    if (percentage < 50) {
                        progressBar.addClass('bg-warning'); // Orange
                    } else if (percentage > 80) {
                        progressBar.addClass('bg-success'); // Green
                    } else {
                        progressBar.addClass('bg-primary'); // Default Purple
                    }

                    $('#total_unpaid').text(response.total_unpaid);
                    $('#total_current_due').text("Rp. " + response.total_current_due);
                }
            });
        }
    </script>
@endpush
