@extends('layouts.master', ['title' => 'Data Tarif Pembayaran'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">

        <!--begin::Header-->
        <div class="d-flex flex-stack mb-5">
            <!--begin::Title-->
            <div class="d-flex align-items-center">
                <div class="symbol symbol-50px me-3">
                    <span class="symbol-label bg-light-primary">
                        <i class="fas fa-file-invoice text-primary fs-2"></i>
                    </span>
                </div>
                <div>
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                        Data Tarif Pembayaran
                    </h1>
                    <span class="text-muted fw-bold fs-7">
                        List Tarif Pembayaran: <span class="text-primary">{{ $billType->name }}</span>
                    </span>
                </div>
            </div>
            <!--end::Title-->

            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                <li class="breadcrumb-item text-muted">
                    <a href="#" class="text-muted text-hover-primary">Tipe Pembayaran</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-300 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-dark">{{ $billType->name }}</li>
            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--end::Header-->

        <!--begin::Card-->
        <div class="card shadow-sm rounded-4 border-0">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1 gap-3">
                        <h3 class="fw-bolder m-0 text-dark">Daftar Tagihan</h3>
                        <span class="text-gray-400">|</span>
                        <div class="w-200px">
                            <select class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" id="filter_academic_year" data-placeholder="Filter Tahun Ajaran">
                                <option value="">Semua Tahun</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->status ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <a href="{{ route('bill-type.index') }}" class="btn btn-light btn-sm rounded-pill hover-scale me-2">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                    <a href="{{ route('payment-rate.create', ['bill_type_id' => $billType->id]) }}" class="btn btn-primary btn-sm rounded-pill hover-scale">
                        <i class="fas fa-plus me-2"></i> Tambah Tagihan
                    </a>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                
                <!--begin::Nav Tabs-->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_regular">
                            Siswa Reguler
                            <span class="badge badge-light-success ms-2">{{ $regularRates->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_transfer">
                            Siswa Pindahan
                            <span class="badge badge-light-warning ms-2">{{ $transferRates->count() }}</span>
                        </a>
                    </li>
                </ul>
                <!--end::Nav Tabs-->

                <!--begin::Tab Content-->
                <div class="tab-content" id="myTabContent">
                    
                    <!--begin::Tab Pane Regular-->
                    <div class="tab-pane fade show active" id="tab_regular" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle gs-0 gy-4" id="table_regular">
                                <thead class="bg-light">
                                    <tr class="fw-bolder text-muted fs-7 text-uppercase">
                                        <th style="width: 3%"></th>
                                        <th class="ps-4 min-w-50px">No</th>
                                        <th class="min-w-150px">Sekolah</th>
                                        <th class="min-w-200px">Kelas</th>
                                        <th class="min-w-125px">Total Tagihan</th>
                                        <th class="text-center min-w-100px rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($regularRates as $rate)
                                    <tr class="rate-row" data-rate-id="{{ $rate->id }}" style="cursor: pointer;">
                                        <td class="text-center toggle-detail">
                                            <i class="fas fa-chevron-right text-primary fs-7 transition-transform" style="transition: transform 0.2s;"></i>
                                        </td>
                                        <td class="ps-4">
                                            <span class="text-dark fw-bolder">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                {{ $rate->paymentRateClassrooms->first()?->classroom?->school?->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($rate->paymentRateClassrooms as $prClassroom)
                                                <span class="badge badge-light-success fw-bolder m-1">
                                                    {{ $prClassroom->classroom?->name ?? 'Kelas Dihapus' }}
                                                </span>
                                            @endforeach
                                            @if($rate->gender)
                                                <span class="badge badge-light-primary fw-bolder m-1">
                                                    {{ $rate->gender == 'L' ? 'Putra' : 'Putri' }}
                                                </span>
                                            @endif
                                            @if($rate->jamaah_status)
                                                <span class="badge badge-light-info fw-bolder m-1">
                                                    {{ collect(explode(',', $rate->jamaah_status))->map(function($status) {
                                                        return match($status) {
                                                            'JAMAAH' => 'Jamaah',
                                                            'NON_JAMAAH' => 'Non Jamaah',
                                                            'MUKIMIN' => 'Mukimin',
                                                            'UNKNOWN' => 'Belum Jelas',
                                                            default => $status
                                                        };
                                                    })->implode(', ') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light-success fs-7 fw-bolder">Rp. {{ number_format($rate->amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                @include('components.action.edit', ['action' => route('payment-rate.edit', $rate->id), 'name' => 'Jenis Bayar'])
                                                @include('components.action.delete', ['action' => route('payment-rate.destroy', $rate->id), 'id' => $rate->id, 'name' => 'Jenis Bayar'])
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Expandable Detail Row --}}
                                    <tr class="detail-row" id="detail-{{ $rate->id }}" style="display: none;">
                                        <td colspan="6" class="p-0 border-0">
                                            <div class="bg-light-primary rounded mx-4 my-3 p-4" style="background-color: #f1f3f9;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0 text-primary fw-bold">
                                                        <i class="fas fa-list-ul me-2"></i>Daftar Santri & Tagihan
                                                    </h6>
                                                    <span class="badge badge-light-primary fs-8">
                                                        <i class="fas fa-spinner fa-spin me-1 detail-spinner"></i>
                                                        <span class="detail-count"></span>
                                                    </span>
                                                </div>
                                                <div class="detail-content">
                                                    <div class="text-center py-5">
                                                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                                        <span class="text-muted ms-2 fs-7">Memuat data...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-search fs-1 text-gray-300 mb-4"></i>
                                                <span class="text-muted fw-bold fs-6">Belum ada data tarif reguler.</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Tab Pane Regular-->

                    <!--begin::Tab Pane Transfer-->
                    <div class="tab-pane fade" id="tab_transfer" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle gs-0 gy-4" id="table_transfer">
                                <thead class="bg-light">
                                    <tr class="fw-bolder text-muted fs-7 text-uppercase">
                                        <th style="width: 3%"></th>
                                        <th class="ps-4 min-w-50px">No</th>
                                        <th class="min-w-150px">Sekolah</th>
                                        <th class="min-w-200px">Nama Siswa</th>
                                        <th class="min-w-125px">Total Tagihan</th>
                                        <th class="text-center min-w-100px rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transferRates as $rate)
                                    <tr class="rate-row" data-rate-id="{{ $rate->id }}" style="cursor: pointer;">
                                        <td class="text-center toggle-detail">
                                            <i class="fas fa-chevron-right text-primary fs-7" style="transition: transform 0.2s;"></i>
                                        </td>
                                        <td class="ps-4">
                                            <span class="text-dark fw-bolder">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                {{ $rate->paymentRateStudents->first()?->student?->classroom?->school?->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($rate->paymentRateStudents as $prStudent)
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge badge-light-warning fw-bolder me-2">
                                                        {{ $prStudent->student?->name ?? 'Siswa Dihapus' }}
                                                    </span>
                                                    <span class="text-muted fs-8">({{ $prStudent->student?->nis ?? '-' }})</span>
                                                </div>
                                            @endforeach
                                            @if($rate->gender || $rate->jamaah_status)
                                                <div class="mt-2">
                                                    @if($rate->gender)
                                                        <span class="badge badge-light-primary fw-bolder me-1">
                                                            {{ $rate->gender == 'L' ? 'Putra' : 'Putri' }}
                                                        </span>
                                                    @endif
                                                    @if($rate->jamaah_status)
                                                        <span class="badge badge-light-info fw-bolder">
                                                            {{ collect(explode(',', $rate->jamaah_status))->map(function($status) {
                                                                return match($status) {
                                                                    'JAMAAH' => 'Jamaah',
                                                                    'NON_JAMAAH' => 'Non Jamaah',
                                                                    'MUKIMIN' => 'Mukimin',
                                                                    'UNKNOWN' => 'Belum Jelas',
                                                                    default => $status
                                                                };
                                                            })->implode(', ') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light-success fs-7 fw-bolder">Rp. {{ number_format($rate->amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                @include('components.action.edit', ['action' => route('payment-rate.edit', $rate->id), 'name' => 'Jenis Bayar'])
                                                @include('components.action.delete', ['action' => route('payment-rate.destroy', $rate->id), 'id' => $rate->id, 'name' => 'Jenis Bayar'])
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Expandable Detail Row --}}
                                    <tr class="detail-row" id="detail-{{ $rate->id }}" style="display: none;">
                                        <td colspan="6" class="p-0 border-0">
                                            <div class="bg-light-primary rounded mx-4 my-3 p-4" style="background-color: #f1f3f9;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0 text-primary fw-bold">
                                                        <i class="fas fa-list-ul me-2"></i>Daftar Santri & Tagihan
                                                    </h6>
                                                    <span class="badge badge-light-primary fs-8">
                                                        <i class="fas fa-spinner fa-spin me-1 detail-spinner"></i>
                                                        <span class="detail-count"></span>
                                                    </span>
                                                </div>
                                                <div class="detail-content">
                                                    <div class="text-center py-5">
                                                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                                        <span class="text-muted ms-2 fs-7">Memuat data...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-user-slash fs-1 text-gray-300 mb-4"></i>
                                                <span class="text-muted fw-bold fs-6">Belum ada data tarif susulan/pindahan.</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Tab Pane Transfer-->

                </div>
                <!--end::Tab Content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Container-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Handle Filter Change
        $('#filter_academic_year').change(function() {
            var yearId = $(this).val();
            var url = new URL(window.location.href);
            if(yearId) {
                url.searchParams.set('academic_year_id', yearId);
            } else {
                url.searchParams.delete('academic_year_id');
            }
            window.location.href = url.toString();
        });

        // Expandable Panel Toggle
        var loadedPanels = {};

        $('.rate-row').on('click', function(e) {
            // Jangan toggle jika klik pada tombol aksi (edit, delete)
            if ($(e.target).closest('.d-flex.justify-content-center').length > 0) return;

            var rateId = $(this).data('rate-id');
            var detailRow = $('#detail-' + rateId);
            var icon = $(this).find('.toggle-detail i');

            if (detailRow.is(':visible')) {
                // Tutup panel
                detailRow.slideUp(200);
                icon.css('transform', 'rotate(0deg)');
            } else {
                // Buka panel
                detailRow.slideDown(200);
                icon.css('transform', 'rotate(90deg)');

                // Muat data jika belum pernah dimuat
                if (!loadedPanels[rateId]) {
                    loadDetailData(rateId);
                }
            }
        });

        function loadDetailData(rateId) {
            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var spinnerEl = detailRow.find('.detail-spinner');
            var countEl = detailRow.find('.detail-count');

            $.ajax({
                url: "{{ route('payment-rate.show', '') }}/" + rateId,
                type: 'GET',
                data: { type: 'bill' },
                dataType: 'json',
                success: function(response) {
                    var students = response.data || [];
                    spinnerEl.hide();
                    countEl.text(students.length + ' Santri');

                    if (students.length === 0) {
                        contentDiv.html('<div class="text-center py-4 text-muted"><i class="fas fa-inbox fs-2 mb-2 d-block"></i>Tidak ada data santri</div>');
                        loadedPanels[rateId] = true;
                        return;
                    }

                    var html = '<div class="table-responsive">';
                    html += '<table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-2 bg-white rounded">';
                    html += '<thead><tr class="fw-bolder text-muted fs-8 text-uppercase">';
                    html += '<th class="ps-4" style="width: 5%">No</th>';
                    html += '<th>Nama Santri</th>';
                    html += '<th>Kelas</th>';
                    html += '<th>Total Tagihan</th>';
                    html += '<th>Dibayar</th>';
                    html += '<th>Sisa</th>';
                    html += '<th class="text-center">Status</th>';
                    html += '</tr></thead><tbody>';

                    students.forEach(function(s, idx) {
                        html += '<tr>';
                        html += '<td class="ps-4 text-gray-700">' + (idx + 1) + '</td>';
                        html += '<td class="fw-bold text-gray-800">' + (s.name || '-') + '</td>';
                        html += '<td class="text-gray-600">' + (s.classroom || '-') + '</td>';
                        html += '<td class="text-gray-700">' + (s.total || 'Rp. 0') + '</td>';
                        html += '<td class="text-success fw-bold">' + (s.total_paid || 'Rp. 0') + '</td>';
                        html += '<td class="text-danger fw-bold">' + (s.total_unpaid || 'Rp. 0') + '</td>';
                        html += '<td class="text-center">' + (s.status || '-') + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    contentDiv.html(html);
                    loadedPanels[rateId] = true;
                },
                error: function() {
                    spinnerEl.hide();
                    contentDiv.html('<div class="alert alert-danger py-3 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memuat data santri</div>');
                }
            });
        }
    });
</script>
@endpush