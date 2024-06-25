@extends('layouts.master', ['title' => 'Data Pembayaran'])
@push('css')
<style>
    .card-information {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-information .mb-3 {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-information .fw-bold {
        width: 30%;
    }

    .card-information span {
        width: 60%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .card-information .fw-bold {
            width: 40%;
        }

        .card-information span {
            width: 50%;
        }
    }

    .d-flex.align-items-center>*:not(:last-child) {
        margin-right: 10px;
    }
</style>

@endpush
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Pembayaran</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('bill.index') }}">
                        <li class="text-muted">
                            Data Pembayaran
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        Pembayaran Siswa
                    </li>
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
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->

                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#pembayaran_tunai">Pembayaran
                                Tunai</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#pembayaran_transfer">Pembayaran Transfer</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="pembayaran_tunai" role="tabpanel">
                            <!-- Pembayaran Tunai Content -->
                            <div>
                                <div class="card card-flush h-lg-100" id="kt_contacts_main">
                                    <div class="card-body pt-5">
                                        <form action="{{ route('bill.index') }}" method="GET">
                                            <div class="fv-row mb-7 d-flex align-items-center">
                                                <label class="fs-6 fw-bold form-label mt-3 me-3 col-2" for="school_id">
                                                    <span class="required">Unit Pendidikan</span>
                                                </label>
                                                <select name="school_id" name="school_id"
                                                    class="form-control form-control-solid flex-grow-1" id="school_id">
                                                    <option value="">Pilih Unit Pendidikan</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" {{ request('school_id')==$school->
                                                        id ?
                                                        'selected' : '' }}>
                                                        {{ $school->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="fv-row mb-7 d-flex align-items-center">
                                                <label class="fs-6 fw-bold form-label mt-3 me-3 col-2" for="name">
                                                    <span class="required">NIS/NISN/Nama</span>
                                                </label>
                                                <select name="student_id" id="student_id"
                                                    class="form-select form-select-solid" id="student_id">
                                                    <option value="">Pilih Siswa</option>
                                                </select>
                                                <button id="btn-cari" class="btn btn-sm btn-primary ms-3"
                                                    style="height: 40px; width: 100px;" type="submit">
                                                    <span class="indicator-label" id="buttonText">Tampilkan</span>
                                                    <span class="indicator-progress d-none">Please wait...
                                                        <span
                                                            class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="separator mb-6"></div>
                                    <div class="d-flex justify-content-end"></div>

                                    @if ($student ?? false)
                                    <div class="card-body pt-3">
                                        <div class="card-information">
                                            <div class="mb-3">
                                                <span class="fw-bold text-muted">Tahun Ajaran</span>
                                                :&nbsp;
                                                <span><b>Semua Tahun Ajaran</b></span>
                                            </div>
                                            <div class="mb-3">
                                                <span class="fw-bold text-muted">NIS</span>
                                                :&nbsp;
                                                <span>{{ @$student->nis ?? '' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <span class="fw-bold text-muted">Nama</span>
                                                :&nbsp;
                                                <span>{{ @$student->name ?? '' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <span class="fw-bold text-muted">Kelas</span>
                                                :&nbsp;
                                                <span>{{ @$student->classroom->name ?? '' }}</span>
                                            </div>
                                        </div>
                                        <div class="separator mb-6"></div>
                                        <div class="d-flex justify-content-end"></div>
                                    </div>
                                    <div id="kt_accordion_1" class="accordion accordion-flush mx-5">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="kt_accordion_1_header_1">
                                                <button class="accordion-button fs-4 fw-semibold" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#kt_accordion_1_body_1"
                                                    aria-expanded="true" aria-controls="kt_accordion_1_body_1">
                                                    Fitur Kilat
                                                </button>
                                            </h2>
                                            <div id="kt_accordion_1_body_1" class="accordion-collapse collapse show"
                                                aria-labelledby="kt_accordion_1_header_1"
                                                data-bs-parent="#kt_accordion_1">
                                                <div class="accordion-body">

                                                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab"
                                                                href="#kt_tab_pane_4">Bulanan</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab"
                                                                href="#kt_tab_pane_5">Lainnya</a>
                                                        </li>
                                                        <div
                                                            class="d-flex justify-content-end align-items-center mb-3 ms-auto">
                                                            <input type="checkbox" id="select-all">
                                                            <label for="select-all" class="ms-2 mb-0">Bayar
                                                                Semua</label>
                                                            <!-- Tempatkan tombol "Bayar" di lokasi yang sesuai -->
                                                            <button class="btn btn-primary modal-pay ms-2"
                                                                data-bs-toggle="modal" data-bs-target="#paymentModal"
                                                                style="min-width: 100px;">Bayar</button>
                                                        </div>
                                                    </ul>

                                                    <div class="tab-content" id="myTabContent">
                                                        <div class="tab-pane fade show active" id="kt_tab_pane_4"
                                                            role="tabpanel">
                                                            <div class="table-responsive">
                                                                <table class="table  gy-7 gs-7">
                                                                    <thead>
                                                                        @include('admins.bill.table.header-kilat')
                                                                    </thead>
                                                                    <tbody>
                                                                        @include('admins.bill.table.body-kilat')
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                                                            <div class="table-responsive">
                                                                <table class="table gy-7 gs-7">
                                                                    <thead>
                                                                        @include('admins.bill.table.header-kilat')
                                                                    </thead>
                                                                    <tbody>
                                                                        @include('admins.bill.table.body-lainnya')
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="separator mb-6"></div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="kt_accordion_1_header_1">
                                                <button class="accordion-button fs-4 fw-semibold" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#accordion-tagihan-bulanan" aria-expanded="true"
                                                    aria-controls="accordion-tagihan-bulanan">
                                                    Tagihan Bulanan
                                                </button>
                                            </h2>
                                            <div id="accordion-tagihan-bulanan" class="accordion-collapse collapse show"
                                                aria-labelledby="kt_accordion_1_header_1"
                                                data-bs-parent="#kt_accordion_1">
                                                <div class="accordion-body">
                                                    <div class="table-responsive">
                                                        <table id="table-bill-monthly"
                                                            class="table align-middle table-row-dashed ">
                                                            <thead>
                                                                <tr
                                                                    class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                                    <th style="width: 5%">No</th>
                                                                    <th class="min-w-125px">Tahun Ajaran</th>
                                                                    <th class="min-w-125px">Jenis Pembayaran</th>
                                                                    <th class="min-w-125px">Jumlah</th>
                                                                    <th class="min-w-125px">Dibayar</th>
                                                                    <th class="text-center min-w-100px"
                                                                        style="width: 22%">
                                                                        Status</th>
                                                                    <th class="text-center min-w-100px"
                                                                        style="width: 22%">Aksi
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-gray-600 fw-bold">
                                                                @foreach ($billMonth as $monthly)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ @$monthly->academicYear->name }}</td>
                                                                    <td>{{ @$monthly->name }}</td>
                                                                    <td>Rp {{ number_format(@$monthly->total_unpaid, 0,
                                                                        ',',
                                                                        '.') }}</td>
                                                                    <td>Rp {{ number_format(@$monthly->total_paid, 0,
                                                                        ',', '.')
                                                                        }}</td>
                                                                    <td><span
                                                                            class="d-flex text-center bg-{{ @$monthly->total_unpaid == 0 ?
                                                                                                 'success' : 'danger' }} text-white px-3 py-1 rounded-1">{{
                                                                            @$monthly->total_unpaid == 0 ? 'Lunas' :
                                                                            'Belum
                                                                            Lunas' }}</span></td>
                                                                    <td class="text-center">
                                                                        <x-action.show
                                                                            :action="route('bill.summary-bill', ['bill_type_id' => $monthly->id, 'student_id' => $student->id])"
                                                                            label="Bayar" />
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="separator mb-6"></div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="kt_accordion_1_header_1">
                                                <button class="accordion-button fs-4 fw-semibold" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#accordion-bill-other"
                                                    aria-expanded="true" aria-controls="accordion-bill-other">
                                                    Tagihan Lainnya
                                                </button>
                                            </h2>
                                            <div id="accordion-bill-other" class="accordion-collapse collapse show"
                                                aria-labelledby="kt_accordion_1_header_1"
                                                data-bs-parent="#kt_accordion_1">
                                                <div class="accordion-body">
                                                    <div class="table-responsive">
                                                        <table id="table-bill-monthly"
                                                            class="table align-middle table-row-dashed ">
                                                            <thead>
                                                                <tr
                                                                    class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                                    <th style="width: 5%">No</th>
                                                                    <th class="min-w-125px">Tahun Ajaran</th>
                                                                    <th class="min-w-125px">Jenis Pembayaran</th>
                                                                    <th class="min-w-125px">Jumlah</th>
                                                                    <th class="min-w-125px">Dibayar</th>
                                                                    <th class="text-center min-w-100px"
                                                                        style="width: 22%">
                                                                        Status</th>
                                                                    <th class="text-center min-w-100px"
                                                                        style="width: 22%">Aksi
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-gray-600 fw-bold">
                                                                @foreach ($billOthers as $other)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ @$other->academicYear->name }}</td>
                                                                    <td>{{ @$other->name }}</td>
                                                                    <td>Rp {{ number_format(@$other->total_unpaid, 0,
                                                                        ',', '.')
                                                                        }}</td>
                                                                    <td>Rp {{ number_format(@$other->total_paid, 0, ',',
                                                                        '.') }}
                                                                    </td>
                                                                    <td><span
                                                                            class="d-flex text-center bg-{{ @$other->total_unpaid == 0 ?
                                                                                                     'success' : 'danger' }} text-white px-3 py-1 rounded-1">{{
                                                                            @$other->total_unpaid == 0 ? 'Lunas' :
                                                                            'Belum Lunas'
                                                                            }}</span></td>
                                                                    <td class="text-center">
                                                                        <x-action.show
                                                                            :action="route('bill.summary-bill', ['bill_type_id' => $other->id, 'student_id' => $student->id])"
                                                                            label="Bayar" />
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pembayaran_transfer" role="tabpanel">
                            <!-- Pembayaran Transfer Content -->
                            <!-- Add your content for Pembayaran Transfer here -->
                            @include('admins.bill.transfer-tab.index')
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
<!-- Modal untuk semua pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bill.store') }}" method="post" id="form-multi-payment">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Informasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-2">
                        <div class="mb-1">
                            <span class="fw-bold fs-1" id="total-amount"></span>
                        </div>
                        <div class="mb-1">
                            <span class="fw-bold text-muted">Total Pembayaran Tagihan</span>
                        </div>
                    </div>
                    <span class="fw-bold text-muted">Metode Pembayaran</span>
                    <div class="card payment-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <select class="form-select" name="payment_method" id="payment-method" required>
                                    <option value="">Pilih Metode Pembayaran</option>
                                    <option value="BALANCE">Saldo</option>
                                    <option value="CASH">Tunai</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <span class="fw-bold text-muted">Rincian Pembayaran</span>
                    <div id="payment-details">
                        <!-- Informasi pembayaran akan ditambahkan di sini -->
                    </div>
                    <input type="hidden" name="student_id" id="student-id" value="{{ @$student->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Bayar Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Post-->
@endsection
@push('js')
<script>
    $(document).ready(function () {
        function changeButtonText(button) {
            var buttonText = button.find('.indicator-label');
            var indicatorProgress = button.find('.indicator-progress');

            if (buttonText.text() === 'Cari') {
                buttonText.text('Mencari...');
                indicatorProgress.removeClass('d-none');
            } else {
                buttonText.text('Cari');
                indicatorProgress.addClass('d-none');
            }
        }

        // on click modal-pay button to show modal and load data
        $(document).on('click', '.modal-pay', function () {
            var url = $(this).data('url');
            var modal = $('#modal-pay');

            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    modal.find('.modal-body').html(data);
                    modal.modal('show');
                }
            });
        });

        
    });
</script>
<script>
    $(document).ready(function() {
    // Function to fetch student data based on selected school
    function fetchStudentData() {
    var school_id = $('#school_id').val();
    if (school_id) {
    $.ajax({
    url: "{{ route('select2') }}",
    dataType: 'json',
    delay: 300,
    data: {
    search: '', // Assuming you need a default search term
    data_type: "STUDENT_BY_SCHOOL",
    school_id: school_id
    },
    success: function (data) {
    var results = $.map(data, function (item) {
    let displayText = (item.nis ? item.nis + ' - ' : '') +
    item.name + ' - ' +
    (item.classroom?.name ? item.classroom.name : '');
    return {
    text: displayText,
    id: item.id
    };
    });
    
    $('#student_id').empty().select2({
    data: results,
    cache: true
    });
    },
    cache: true
    });
    } else {
    $('#student_id').empty();
    }
    }
    
    // Bind the change event to the fetchStudentData function
    $('#school_id').change(fetchStudentData);
    
    // Call the function on page load
    fetchStudentData();
    });
</script>
<script>
    $(document).ready(() => {
            var table = $('#table-transfer').DataTable({
                ordering: true,
                sortable: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('bill.index') }}",
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
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                     {
                        data: 'student.name',
                        name: 'student.name',
                        orderable: false,
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'pay_amount',
                        name: 'pay_amount'
                    },
                    {
                        data: 'unique_payment',
                        name: 'unique_payment'
                    },
                    {
                        data: 'proof',
                        name: 'proof',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

        })
</script>
@endpush