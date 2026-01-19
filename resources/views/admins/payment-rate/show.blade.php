@extends('layouts.master', ['title' => 'Detail Tarif Pembayaran'])
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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Tarif Pembayaran</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('bill-type.index') }}" class="text-muted text-hover-primary">Jenis
                                Pembayaran</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-dark">Detail Tarif Pembayaran</li>
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
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h3 class="text-dark">Detail Tarif Pembayaran</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Mass Delete Actions-->
                        <div class="d-flex align-items-center mb-4" id="mass-delete-section"
                            style="display: none !important;">
                            <button type="button" class="btn btn-danger btn-sm" id="delete-selected-bills">
                                <i class="bi bi-trash"></i> Hapus Terpilih (<span id="selected-count">0</span>)
                            </button>
                            <button type="button" class="btn btn-light btn-sm ms-2" id="cancel-selection">
                                Batal
                            </button>
                        </div>
                        <!--end::Mass Delete Actions-->

                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table id="table-report-bill" class="table align-middle table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 3%">
                                            <i class="bi bi-chevron-down text-primary fs-5" title="Expand/Collapse"></i>
                                        </th>
                                        <th style="width: 5%">No</th>
                                        <th class="min-w-125px">Nama Santri</th>
                                        <th class="min-w-100px">Kelas</th>
                                        <th class="min-w-125px">Total Tagihan</th>
                                        <th class="min-w-125px">Dibayar</th>
                                        <th class="min-w-125px">Sisa Tagihan</th>
                                        <th class="text-center min-w-70px" style="width: 15%">Status</th>
                                        <th class="text-center" style="width: 10%">Aksi</th>
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

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
@endsection
@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latenet/momentjs/latest/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            // var table = initializeTable();

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
                            $('#filter_classroom_id').append(
                                '<option value="">Semua Kelas</option>');
                            $.each(response.data, function(key, value) {
                                $('#filter_classroom_id').append('<option value="' +
                                    value.id + '">' + value.name + '</option>');
                            });
                        } else {
                            $('#filter_classroom_id').append(
                                '<option value="">Tidak ada kelas</option>');
                        }
                    }
                });

                reloadTable();
            });

            // handle filter change
            $('#filter_classroom_id, #filter_academic_year_id').on('change', function() {
                reloadTable();
            });

            // Inisialisasi date range picker
            $('#dateRange').daterangepicker({
                startDate: moment().subtract(365, 'days'),
                endDate: moment(),
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().subtract(
                        1, 'month').endOf('month')],
                    '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().subtract(
                        1, 'month').endOf('month')],
                    '9 Bulan Terakhir': [moment().subtract(9, 'month').startOf('month'), moment().subtract(
                        1, 'month').endOf('month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                }
            }, function(start, end) {
                $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            });

            // Panggilan awal untuk date range picker
            var start = moment().subtract(365, 'days');
            var end = moment();
            $('#dateRange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            reloadTable(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));

            // Mass delete functionality
            let selectedBills = [];

            $(document).on('change', '.bill-checkbox', function() {
                const billId = $(this).val();
                if ($(this).is(':checked')) {
                    selectedBills.push(billId);
                } else {
                    selectedBills = selectedBills.filter(id => id !== billId);
                }
                updateMassDeleteUI();
            });

            function updateMassDeleteUI() {
                const count = selectedBills.length;
                $('#selected-count').text(count);
                if (count > 0) {
                    $('#mass-delete-section').show();
                } else {
                    $('#mass-delete-section').hide();
                }
            }

            $('#cancel-selection').on('click', function() {
                $('.bill-checkbox').prop('checked', false);
                selectedBills = [];
                updateMassDeleteUI();
            });

            $('#delete-selected-bills').on('click', function() {
                if (selectedBills.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak ada tagihan yang dipilih',
                        text: 'Silakan pilih tagihan terlebih dahulu.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus ${selectedBills.length} tagihan yang dipilih?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteBillsMass(selectedBills);
                    }
                });
            });

            window.deleteBillsMass = function(billIds) {
                $.ajax({
                    url: "{{ route('payment-rate.delete-bills-mass') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        bill_ids: billIds
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Sedang memproses penghapusan tagihan',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Tagihan berhasil dihapus',
                            timer: 2000
                        });
                        selectedBills = [];
                        updateMassDeleteUI();
                        reloadTable();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menghapus tagihan'
                        });
                    }
                });
            };
        });

        function initializeTable(start_date = '', end_date = '') {
            return $('#table-report-bill').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('payment-rate.show', $paymentRate->id) }}",
                    data: function(d) {
                        d.type = 'bill';
                        d.school_id = $('#filter_school_id').val();
                        d.classroom_id = $('#filter_classroom_id').val();
                        d.academic_year_id = $('#filter_academic_year_id').val();
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
                        className: 'details-control text-center',
                        orderable: false,
                        searchable: false,
                        render: function() {
                            return '<i class="bi bi-chevron-right text-primary fs-4 cursor-pointer" style="cursor: pointer;"></i>';
                        }
                    },
                    {
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
                        data: 'classroom',
                        name: 'classroom'
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
                        data: 'total_unpaid',
                        name: 'total_unpaid'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        responsivePriority: -1
                    },
                    {
                        data: 'action',
                        name: 'action',
                        responsivePriority: -1
                    }
                ]
            });
        }

        function reloadTable(start_date = '', end_date = '') {
            var table = $('#table-report-bill').DataTable();
            table.destroy();
            table = initializeTable(start_date, end_date);
            getTotalBill(start_date, end_date);
            attachRowExpandListeners(table);
        }

        function attachRowExpandListeners(table) {
            $('#table-report-bill tbody').off('click', 'td.details-control');
            $('#table-report-bill tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this).find('i');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('bi-chevron-down').addClass('bi-chevron-right');
                } else {
                    var studentId = row.data().id;
                    var billTypeId = '{{ $paymentRate->bill_type_id }}';
                    var paymentRateId = '{{ $paymentRate->id }}';

                    // Show loading
                    row.child(
                        '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                        ).show();
                    tr.addClass('shown');
                    icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');

                    // Fetch bill details
                    $.ajax({
                        url: "{{ route('payment-rate.get-bill-details') }}",
                        type: 'GET',
                        data: {
                            student_id: studentId,
                            bill_type_id: billTypeId,
                            payment_rate_id: paymentRateId
                        },
                        success: function(response) {
                            row.child(formatBillDetails(response.bills, row.data())).show();
                        },
                        error: function() {
                            row.child(
                                '<div class="alert alert-danger m-3">Gagal memuat data tagihan</div>'
                                ).show();
                        }
                    });
                }
            });
        }

        function formatBillDetails(bills, studentData) {
            if (!bills || bills.length === 0) {
                return '<div class="alert alert-info m-3">Tidak ada data tagihan</div>';
            }

            let html = '<div class="p-4 bg-light-primary" style="background-color: #f8f9fa;">';
            html += '<div class="d-flex justify-content-between align-items-center mb-3">';
            html += '<h5 class="mb-0"><i class="bi bi-receipt text-primary"></i> Rincian Tagihan - ' + studentData.name +
                '</h5>';
            html += '<label class="form-check-label">';
            html += '<input type="checkbox" class="form-check-input select-all-bills me-2" data-student-id="' + studentData
                .id + '">';
            html += '<span class="text-muted">Pilih Semua</span>';
            html += '</label>';
            html += '</div>';

            html += '<div class="table-responsive">';
            html += '<table class="table table-bordered table-hover table-sm bg-white">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th width="5%" class="text-center">';
            html += '<input type="checkbox" class="form-check-input select-all-nested" disabled>';
            html += '</th>';
            html += '<th width="5%">No</th>';
            html += '<th>Bulan</th>';
            html += '<th>Tahun</th>';
            html += '<th>Nominal</th>';
            html += '<th width="15%">Status</th>';
            html += '<th width="15%" class="text-center">Aksi</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            bills.forEach((bill, index) => {
                html += '<tr>';
                html += '<td class="text-center">';
                if (bill.status === 'UNPAID') {
                    html += '<input type="checkbox" class="form-check-input bill-checkbox" value="' + bill.id +
                    '">';
                }
                html += '</td>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td>' + bill.translated_month + '</td>';
                html += '<td>' + bill.year + '</td>';
                html += '<td>Rp. ' + new Intl.NumberFormat('id-ID').format(bill.amount) + '</td>';
                html += '<td>' + bill.status_badge + '</td>';
                html += '<td class="text-center">';
                if (bill.status === 'UNPAID') {
                    html += '<button type="button" class="btn btn-danger btn-sm delete-bill" data-bill-id="' + bill
                        .id + '">';
                    html += '<i class="bi bi-trash"></i> Hapus';
                    html += '</button>';
                } else {
                    html += '<span class="badge bg-success">Lunas</span>';
                }
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';

            // Attach delete handlers after rendering
            setTimeout(() => {
                $('.delete-bill').on('click', function() {
                    const billId = $(this).data('bill-id');
                    deleteSingleBill(billId);
                });

                $('.select-all-bills').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $(this).closest('.p-4').find('.bill-checkbox').prop('checked', isChecked).trigger(
                        'change');
                });
            }, 100);

            return html;
        }

        function deleteSingleBill(billId) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus tagihan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('payment-rate.delete-bill') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            bill_id: billId
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menghapus...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Tagihan berhasil dihapus',
                                timer: 2000
                            });
                            reloadTable();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        }

        function getTotalBill(start_date = '', end_date = '') {
            $.ajax({
                url: "{{ route('payment-rate.show', $paymentRate->id) }}",
                type: "GET",
                dataType: 'json',
                data: {
                    type: 'total',
                    school_id: $('#filter_school_id').val(),
                    classroom_id: $('#filter_classroom_id').val(),
                    academic_year_id: $('#filter_academic_year_id').val(),
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    $('#total').text('Rp. ' + response.total);
                    $('#total_paid').text('Rp. ' + response.total_paid);
                    $('#progress-bar').css('width', response.realisasion_percentage);
                    $('#progress-bar-label').text(response.realisasion_percentage);
                    $('#total_unpaid').text('Belum Lunas: Rp. ' + response.total_unpaid);

                }
            });
        }
    </script>
@endpush
