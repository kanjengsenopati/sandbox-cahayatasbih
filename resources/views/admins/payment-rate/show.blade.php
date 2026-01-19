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
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">



            <!--begin::Card-->
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <i class="bi bi-search fs-2"></i>
                            </span>
                            <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari Santri..." id="search_student" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('bill-type.show', $paymentRate->bill_type_id) }}" class="btn btn-light btn-active-light-primary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Filters-->
                    <div class="d-flex flex-wrap gap-5 mb-5 align-items-end">
                        <div class="w-100 w-md-200px">
                            <label class="form-label fs-6 fw-bold">Sekolah:</label>
                            <select class="form-select form-select-solid" id="filter_school_id" data-control="select2" data-placeholder="Pilih Sekolah">
                                <option value="">Semua Sekolah</option>
                                @foreach (\App\Models\School::all() as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-100 w-md-200px">
                            <label class="form-label fs-6 fw-bold">Kelas:</label>
                            <select class="form-select form-select-solid" id="filter_classroom_id" data-control="select2" data-placeholder="Pilih Kelas">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>

                    </div>
                    <!--end::Filters-->

                    <!--begin::Mass Delete Actions-->
                    <div class="d-flex align-items-center mb-4 p-3 bg-light-danger rounded border border-danger border-dashed" id="mass-delete-section" style="display: none !important;">
                        <span class="fs-6 fw-bold text-danger me-4">
                            <i class="bi bi-check-square-fill me-2 fs-4"></i>
                            <span id="selected-count">0</span> Tagihan Dipilih
                        </span>
                        <button type="button" class="btn btn-danger btn-sm" id="delete-selected-bills">
                            <i class="bi bi-trash"></i> Hapus Terpilih
                        </button>
                        <button type="button" class="btn btn-light btn-sm ms-2" id="cancel-selection">
                            Batal
                        </button>
                    </div>
                    <!--end::Mass Delete Actions-->

                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-report-bill" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 3%"></th>
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

<!-- Edit Bill Modal -->
<div class="modal fade" id="editBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBillForm">
                    <input type="hidden" id="edit_bill_id">
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Nominal Tagihan</label>
                        <input type="number" class="form-control" id="edit_amount" required min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveBillBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize DataTable
        var table = initializeTable();

        // Initialize Select2
        $('#filter_school_id').select2({
            allowClear: true,
            placeholder: "Pilih Sekolah"
        });
        $('#filter_classroom_id').select2({
            allowClear: true,
            placeholder: "Pilih Kelas"
        });

        // HandleSchool Change
        $('#filter_school_id').on('change', function() {
            var schoolId = $(this).val();
            $('#filter_classroom_id').empty().append('<option value="">Semua Kelas</option>');

            if (!schoolId) {
                reloadTable();
                return;
            }

            $.ajax({
                url: "{{ route('payment-rate.get-classroom') }}",
                type: 'GET',
                data: {
                    school_id: schoolId
                },
                success: function(response) {
                    $.each(response, function(key, value) {
                        $('#filter_classroom_id').append('<option value="' + value.id +
                            '">' + value.name + '</option>');
                    });
                }
            });

            reloadTable();
        });

        // handle filter change
        $('#filter_classroom_id').on('change', function() {
            reloadTable();
        });

        // Search Filter
        $('#search_student').on('keyup', function() {
            var table = $('#table-report-bill').DataTable();
            table.search(this.value).draw();
        });



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

        // Save Bill Logic
        $('#saveBillBtn').click(function() {
            var billId = $('#edit_bill_id').val();
            var amount = $('#edit_amount').val();

            if (!amount) {
                Swal.fire('Error', 'Nominal tidak boleh kosong', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('payment-rate.update-bill') }}",
                type: 'POST',
                data: {
                    bill_id: billId,
                    amount: amount
                },
                beforeSend: function() {
                    $('#saveBillBtn').attr('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    $('#editBillModal').modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    reloadTable();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Terjadi kesalahan', 'error');
                },
                complete: function() {
                    $('#saveBillBtn').attr('disabled', false).text('Simpan');
                }
            });
        });
    });

    function initializeTable() {
        return $('#table-report-bill').DataTable({
            processing: true,
            serverSide: true,
            order: [],
            ajax: {
                url: "{{ route('payment-rate.show', $paymentRate->id) }}",
                data: function(d) {
                    d.type = 'bill';
                    d.school_id = $('#filter_school_id').val();
                    d.classroom_id = $('#filter_classroom_id').val();
                    d.academic_year_id = $('#filter_academic_year_id').val();
                }
            },
            language: {
                processing: "Sedang memproses data...",
                zeroRecords: "Tidak ada data ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
            },
            dom: 'tr<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
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
                    className: 'text-center',
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
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            drawCallback: function() {
                attachRowExpandListeners(this.api());
            }
        });
    }

    function reloadTable() {
        var table = $('#table-report-bill').DataTable();
        table.destroy();

        initializeTable();
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

                row.child(
                    '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                ).show();
                tr.addClass('shown');
                icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');

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

        let html = '<div class="p-4 bg-light-primary rounded" style="background-color: #f8f9fa;">';
        html += '<div class="d-flex justify-content-between align-items-center mb-3">';
        html += '<h5 class="mb-0 text-primary fw-bold"><i class="bi bi-receipt me-2"></i> Rincian Tagihan - ' + studentData.name +
            '</h5>';
        html += '<label class="form-check-label cursor-pointer">';
        html += '<input type="checkbox" class="form-check-input select-all-bills me-2" data-student-id="' + studentData
            .id + '">';
        html += '<span class="text-gray-600 fw-bold">Pilih Semua</span>';
        html += '</label>';
        html += '</div>';

        html += '<div class="table-responsive">';
        html += '<table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3 bg-white rounded">';
        html += '<thead class="fw-bolder text-muted bg-light">';
        html += '<tr>';
        html += '<th width="5%" class="text-center ps-4">';
        html += '<input type="checkbox" class="form-check-input select-all-nested" disabled style="opacity:0">';
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
            html += '<td class="text-center ps-4">';
            if (bill.status === 'UNPAID') {
                html += '<input type="checkbox" class="form-check-input bill-checkbox" value="' + bill.id +
                    '">';
            }
            html += '</td>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + (bill.translated_month || '-') + '</td>';
            html += '<td>' + bill.year + '</td>';
            html += '<td class="fw-bold text-gray-700">Rp. ' + new Intl.NumberFormat('id-ID').format(bill.amount) + '</td>';
            html += '<td>' + bill.status_badge + '</td>';
            html += '<td class="text-center">';
            if (bill.status === 'UNPAID') {
                // Edit Button
                html += '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 edit-bill-btn" ' +
                    'data-bill-id="' + bill.id + '" ' +
                    'data-amount="' + bill.amount + '" ' +
                    'title="Edit Tagihan">';
                html += '<i class="bi bi-pencil-square fs-5"></i>';
                html += '</button>';

                // Delete Button
                html += '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-bill" data-bill-id="' + bill
                    .id + '" title="Hapus Tagihan">';
                html += '<i class="bi bi-trash fs-5"></i>';
                html += '</button>';
            } else {
                html += '<i class="bi bi-check-circle-fill text-success fs-3"></i>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        setTimeout(() => {
            $('.delete-bill').off('click').on('click', function() {
                const billId = $(this).data('bill-id');
                deleteSingleBill(billId);
            });

            // Initializing Edit Button Listener
            $('.edit-bill-btn').off('click').on('click', function() {
                var billId = $(this).data('bill-id');
                var amount = $(this).data('amount');

                $('#edit_bill_id').val(billId);
                $('#edit_amount').val(amount);
                $('#editBillModal').modal('show');
            });

            $('.select-all-bills').off('change').on('change', function() {
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
</script>
@endpush