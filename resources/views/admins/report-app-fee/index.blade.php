@extends('layouts.master', ['title' => 'Laporan Biaya Aplikasi'])
@push('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
<!--begin::Container-->
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan Biaya Aplikasi</h1>
                <!--end::Title-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <!--end::Row-->
    <!--begin::Row-->
    <div class="row gy-5 g-xl-10 mt-8 mx-4">
        <div class="col-xl-12">

            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                        class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                        class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">UPT:</label>
                    <select name="school" id="school" class="form-select">
                        <option value="">Semua UPT</option>
                        @foreach ($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card mb-4">
                    <div class="row gx-3 gy-3">
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-calendar-day fs-1 p-0 text-white">
                                        </i>&nbsp;
                                        <h5 class="card-title text-center text-white">Total</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;" id="total">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-money-bill fs-1 p-0 text-white">
                                        </i>&nbsp;
                                        <h5 class="card-title text-center text-white">Tagihan</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;" id="bill">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-money-check-alt fs-1 p-0 text-white"></i>
                                        &nbsp;
                                        <h5 class="card-title text-center text-white">Saldo</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;" id="saldo">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 py-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <i class="fas fa-piggy-bank fs-1 p-0 text-white"></i>
                                        &nbsp;
                                        <h5 class="card-title text-center text-white">Tabungan</h5>
                                    </div>
                                    <br>
                                    <p class="card-text text-center" style="font-size: 20px;" id="saving">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder text-gray-800">Riwayat Biaya Aplikasi</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table id="table-report-app-fee" class="table align-middle table-row-dashed ">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th>Tanggal</th>
                                        <th>Kode Pembayaran</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::revenue-bill-->
    @endsection
    @push('js')
    <script>
        $(document).ready(() => {
        const startDate = "{{ request('start_date', '') }}" || moment().startOf('year').format('YYYY-MM-DD');
        const endDate = "{{ request('end_date', '') }}" || moment().format('YYYY-MM-DD');
        
        $('#start_date').val(startDate);
        $('#end_date').val(endDate);
        
        // Define fetchSummaryData function first
        const fetchSummaryData = () => {
            $.ajax({
                url: "{{ route('report-app-fee.index') }}",
                type: 'GET',
                data: {
                    type: 'summary',
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    school: $('#school').val()
                },
                success: (data) => {
                    updateSummaryCards(data);
                },
                error: (error) => {
                    console.error('Error fetching summary data:', error);
                }
            });
        };

        const updateSummaryCards = (data) => {
            $('#total').text(`Rp. ${data.total || 0}`);
            $('#bill').text(`Rp. ${data.bill || 0}`);
            $('#saldo').text(`Rp. ${data.saldo || 0}`);
            $('#saving').text(`Rp. ${data.saving || 0}`);
        };

        var table = $('#table-report-app-fee').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('report-app-fee.index') }}",
                data: function(d) {
                    d.type = 'app_fee';
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.school = $('#school').val();
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
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'date', name: 'date' },
                { data: 'payment_code', name: 'payment_code' },
                { data: 'type', name: 'type' },
                { data: 'app_fee', name: 'app_fee' },
            ]
        });

        $('#start_date, #end_date').on('change', function() {
            table.ajax.reload();
            fetchSummaryData();
        });

        $('#school').on('change', function() {
            table.ajax.reload();
            fetchSummaryData();
        });

        // Initialize statistics
        fetchSummaryData();
    });
    </script>
    @endpush