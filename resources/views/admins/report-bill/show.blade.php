@extends('layouts.master', ['title' => 'Detail Laporan Tagihan'])

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!-- Main Container with Top Padding to avoid Navbar overlap -->
        <div class="container-xxl" id="kt_content_container">

            <!-- Header Section (Title & Breadcrumb) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-6">
                <div class="d-flex flex-column">
                    <h1 class="text-dark fw-bolder fs-2 mb-2">
                        {{ $billType->name ?? 'Laporan Tagihan' }}
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

                            <!-- UPT / School -->
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fs-7 fw-bolder text-uppercase text-gray-500 mb-2">UPT</label>
                                <select name="school_id" class="form-select form-select-solid bg-white border"
                                    id="filter_school_id">
                                    <option value="">Semua UPT</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Classroom -->
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fs-7 fw-bolder text-uppercase text-gray-500 mb-2">Kelas</label>
                                <select name="classroom_id" class="form-select form-select-solid bg-white border"
                                    id="filter_classroom_id">
                                    <option value="">Semua Kelas</option>
                                </select>
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
                                    <th class="min-w-150px">Item Pembayaran</th>
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
    </div>
@endsection
@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize the table on document ready
            // var table = initializeTable();

            // Function to initialize DataTable


            // Event listeners for filters
            $('#filter_school_id, #filter_classroom_id, #status').on('change', function() {
                reloadTable();
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
                let school_id = $('#filter_school_id').val();
                let classroom_id = $('#filter_classroom_id').val();
                let status = $('#status').val();
                let start_date = $('#dateRange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                let end_date = $('#dateRange').data('daterangepicker').endDate.format('YYYY-MM-DD');

                let url = "{{ route('report-bill.export', $billType->id) }}";
                let params = new URLSearchParams({
                    start_date: start_date,
                    end_date: end_date,
                    school_id: school_id,
                    classroom_id: classroom_id,
                    status: status
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
                        d.school_id = $('#filter_school_id').val();
                        d.classroom_id = $('#filter_classroom_id').val();
                        d.status = $('#status').val();
                        d.start_date = start_date;
                        d.end_date = end_date;
                    }
                },
                language: {
                    processing: "Sedang memproses data, Silahkan ditunggu..."
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
                    school_id: $('#filter_school_id').val(),
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

        // Event listener for school filter change
        $('#filter_school_id').on('change', function() {
            var school_id = $(this).val();
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: {
                    school_id: school_id
                },
                success: function(response) {
                    $('#filter_classroom_id').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id +
                                '">' + value.name + '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append(
                            '<option value="">Data tidak ditemukan</option>');
                    }
                }
            });
        });
    </script>
@endpush
