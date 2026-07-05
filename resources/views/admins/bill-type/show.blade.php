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
                    <input type="hidden" id="edit_rate_id">
                    <input type="hidden" id="edit_student_id">
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Nominal Tagihan</label>
                        <input type="text" class="form-control" id="edit_amount" required>
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

        function loadDetailData(rateId, callback) {
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
                        if (typeof callback === 'function') {
                            callback();
                        }
                        return;
                    }

                    var html = '<div class="table-responsive">';
                    html += '<table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-2 bg-white rounded">';
                    html += '<thead><tr class="fw-bolder text-muted fs-8 text-uppercase">';
                    html += '<th style="width: 3%"></th>';
                    html += '<th class="ps-4" style="width: 5%">No</th>';
                    html += '<th>Nama Santri</th>';
                    html += '<th>Kelas</th>';
                    html += '<th>Total Tagihan</th>';
                    html += '<th>Dibayar</th>';
                    html += '<th>Sisa</th>';
                    html += '<th class="text-center">Status</th>';
                    html += '</tr></thead><tbody>';

                    students.forEach(function(s, idx) {
                        html += '<tr class="student-row" data-student-id="' + s.id + '" data-rate-id="' + rateId + '" style="cursor: pointer;">';
                        html += '<td class="text-center toggle-student-detail">';
                        html += '<i class="fas fa-chevron-right text-success fs-8 transition-transform" style="transition: transform 0.15s;"></i>';
                        html += '</td>';
                        html += '<td class="ps-4 text-gray-700">' + (idx + 1) + '</td>';
                        html += '<td class="fw-bold text-gray-800">' + (s.name || '-') + '</td>';
                        html += '<td class="text-gray-600">' + (s.classroom || '-') + '</td>';
                        html += '<td class="text-gray-700">' + (s.total || 'Rp. 0') + '</td>';
                        html += '<td class="text-success fw-bold">' + (s.total_paid || 'Rp. 0') + '</td>';
                        html += '<td class="text-danger fw-bold">' + (s.total_unpaid || 'Rp. 0') + '</td>';
                        html += '<td class="text-center">' + (s.status || '-') + '</td>';
                        html += '</tr>';

                        // Detail row for student
                        html += '<tr class="student-detail-row" id="student-detail-' + s.id + '-' + rateId + '" style="display: none;">';
                        html += '<td colspan="8" class="p-0 border-0">';
                        html += '<div class="bg-light rounded mx-4 my-2 p-3" style="background-color: #f8f9fa; border: 1px dashed #e4e6ef;">';
                        
                        // Redesigned Header with multi-select actions
                        html += '<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">';
                        html += '<h6 class="mb-0 text-success fw-bold fs-7">';
                        html += '<i class="fas fa-receipt me-2"></i>Rincian Tagihan Bulanan';
                        html += '</h6>';
                        html += '<div class="d-flex align-items-center gap-3 select-actions-container-' + s.id + '-' + rateId + '" style="display: none !important;">';
                        html += '<div class="form-check form-check-custom form-check-solid form-check-sm">';
                        html += '<input class="form-check-input select-all-bills" type="checkbox" data-student-id="' + s.id + '" data-rate-id="' + rateId + '" id="check-all-' + s.id + '-' + rateId + '" />';
                        html += '<label class="form-check-label fs-8 text-gray-700 cursor-pointer" for="check-all-' + s.id + '-' + rateId + '">';
                        html += 'Pilih Semua';
                        html += '</label>';
                        html += '</div>';
                        html += '<button type="button" class="btn btn-sm btn-light-danger px-3 py-1 fs-9 mass-delete-btn" data-student-id="' + s.id + '" data-rate-id="' + rateId + '">';
                        html += '<i class="bi bi-trash fs-9 me-1"></i>Hapus Terpilih (<span class="selected-count">0</span>)';
                        html += '</button>';
                        html += '</div>';
                        html += '</div>';
                        
                        html += '<div class="student-bill-content">';
                        html += '<div class="text-center py-3">';
                        html += '<div class="spinner-border text-success spinner-border-sm" role="status"></div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    contentDiv.html(html);
                    loadedPanels[rateId] = true;

                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function() {
                    spinnerEl.hide();
                    contentDiv.html('<div class="alert alert-danger py-3 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memuat data santri</div>');
                }
            });
        }

        // Expandable Student Panel Toggle
        $(document).on('click', '.student-row', function(e) {
            // Jangan toggle jika klik pada tombol aksi (edit, delete)
            if ($(e.target).closest('button').length > 0 || $(e.target).closest('.btn').length > 0) return;

            var studentId = $(this).data('student-id');
            var rateId = $(this).data('rate-id');
            var detailRow = $('#student-detail-' + studentId + '-' + rateId);
            var icon = $(this).find('.toggle-student-detail i');

            if (detailRow.is(':visible')) {
                detailRow.slideUp(150);
                icon.css('transform', 'rotate(0deg)');
            } else {
                detailRow.slideDown(150);
                icon.css('transform', 'rotate(90deg)');
                loadStudentBills(studentId, rateId);
            }
        });

        function loadStudentBills(studentId, rateId) {
            var detailRow = $('#student-detail-' + studentId + '-' + rateId);
            var contentDiv = detailRow.find('.student-bill-content');
            var selectActionsContainer = $('.select-actions-container-' + studentId + '-' + rateId);
            var billTypeId = '{{ $billType->id }}';

            $.ajax({
                url: "{{ route('payment-rate.get-bill-details') }}",
                type: 'GET',
                data: {
                    student_id: studentId,
                    bill_type_id: billTypeId,
                    payment_rate_id: rateId
                },
                dataType: 'json',
                success: function(response) {
                    var bills = response.bills || [];
                    if (bills.length === 0) {
                        contentDiv.html('<div class="text-center py-4 text-muted"><i class="fas fa-inbox fs-2 mb-2 d-block"></i>Tidak ada data tagihan</div>');
                        selectActionsContainer.hide();
                        return;
                    }

                    // Count unpaid bills to decide if we show select actions
                    var unpaidBills = bills.filter(function(b) { return b.status === 'UNPAID'; });
                    if (unpaidBills.length > 0) {
                        selectActionsContainer.css('display', 'flex');
                        // Reset "Pilih Semua" checkbox & count
                        var checkAll = $('#check-all-' + studentId + '-' + rateId);
                        checkAll.prop('checked', false);
                        selectActionsContainer.find('.selected-count').text(0);
                    } else {
                        selectActionsContainer.hide();
                    }

                    var html = '<div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-6">';

                    bills.forEach(function(b, idx) {
                        html += '<div class="col">';
                        
                        var cardStyle = b.status === 'PAID' 
                            ? 'bg-light-success border-success' 
                            : 'bg-white border-gray-200';
                        
                        // Refined to p-3, min-height 145px, d-flex flex-column to be compact and eliminate blank gaps
                        html += '<div class="card h-100 border ' + cardStyle + ' shadow-sm rounded-4 position-relative p-3 d-flex flex-column" style="min-height: 145px; transition: transform 0.2s, box-shadow 0.2s;">';
                        
                        // Checkbox for selection (Only for UNPAID bills, positioned at top-left with m-2)
                        if (b.status === 'UNPAID') {
                            html += '<div class="position-absolute top-0 start-0 m-2">';
                            html += '<div class="form-check form-check-custom form-check-solid form-check-sm">';
                            html += '<input class="form-check-input select-bill-checkbox" type="checkbox" value="' + b.id + '" data-rate-id="' + rateId + '" data-student-id="' + studentId + '" />';
                            html += '</div>';
                            html += '</div>';
                        }

                        // Prominent Month & Year Badge (Centered mt-2 to avoid checkbox overlap)
                        html += '<div class="badge badge-light-primary fw-bolder text-uppercase fs-8 py-1.5 px-3 w-100 mb-1.5 text-center mt-2">' + (b.translated_month || '-') + ' ' + b.year + '</div>';
                        
                        // Solid Status Badge (Red for Unpaid, Emerald for Paid)
                        var statusStyle = b.status === 'PAID' 
                            ? 'background-color: #10b981 !important; color: #ffffff !important;' 
                            : 'background-color: #dc2626 !important; color: #ffffff !important;';
                        var statusLabel = b.status === 'PAID' ? 'LUNAS' : 'BELUM LUNAS';
                        html += '<div class="badge fw-bold fs-8 py-1.5 px-3 w-100 mb-2 text-center" style="' + statusStyle + '">' + statusLabel + '</div>';
                        
                        // Nominal
                        var amountColor = b.status === 'PAID' ? 'text-success' : 'text-primary';
                        html += '<div class="fs-5 fw-bolder ' + amountColor + ' text-center mb-2">Rp. ' + new Intl.NumberFormat('id-ID').format(b.amount) + '</div>';
                        
                        // Aligned Bottom Buttons / Status
                        html += '<div class="mt-auto">';
                        if (b.status === 'UNPAID') {
                            html += '<div class="d-flex gap-2 w-100">';
                            html += '<button type="button" class="btn btn-sm btn-light-primary w-50 edit-bill-btn py-1 fs-8 d-flex align-items-center justify-content-center" data-bill-id="' + b.id + '" data-amount="' + b.amount + '" data-rate-id="' + rateId + '" data-student-id="' + studentId + '" title="Edit">';
                            html += '<i class="bi bi-pencil-square me-1 fs-8"></i>Edit';
                            html += '</button>';
                            html += '<button type="button" class="btn btn-sm btn-light-danger w-50 delete-bill-btn py-1 fs-8 d-flex align-items-center justify-content-center" data-bill-id="' + b.id + '" data-rate-id="' + rateId + '" data-student-id="' + studentId + '" title="Hapus">';
                            html += '<i class="bi bi-trash me-1 fs-8"></i>Hapus';
                            html += '</button>';
                            html += '</div>';
                        } else {
                            html += '<div class="d-flex justify-content-center align-items-center text-success py-1 fw-bold fs-7">';
                            html += '<i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>Lunas';
                            html += '</div>';
                        }
                        html += '</div>'; // End Bottom aligned section

                        html += '</div>'; // End Card
                        html += '</div>'; // End Col
                    });

                    html += '</div>'; // End Row
                    contentDiv.html(html);
                },
                error: function() {
                    contentDiv.html('<div class="alert alert-danger py-2 mb-0 fs-8"><i class="fas fa-exclamation-triangle me-1"></i>Gagal memuat tagihan</div>');
                }
            });
        }

        function formatNumber(num) {
            return num.toString().replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        $('#edit_amount').on('input', function() {
            this.value = formatNumber(this.value);
        });

        // Edit Bill Button click
        $(document).on('click', '.edit-bill-btn', function(e) {
            e.stopPropagation();
            var billId = $(this).data('bill-id');
            var amount = $(this).data('amount');
            var rateId = $(this).data('rate-id');
            var studentId = $(this).data('student-id');

            $('#edit_bill_id').val(billId);
            $('#edit_amount').val(formatNumber(amount));
            $('#edit_rate_id').val(rateId);
            $('#edit_student_id').val(studentId);
            $('#editBillModal').modal('show');
        });

        // Save Bill Button click
        $('#saveBillBtn').click(function() {
            var billId = $('#edit_bill_id').val();
            var amount = $('#edit_amount').val().replace(/\./g, '');
            var rateId = $('#edit_rate_id').val();
            var studentId = $('#edit_student_id').val();

            if (!amount) {
                Swal.fire('Error', 'Nominal tidak boleh kosong', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('payment-rate.update-bill') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    bill_id: billId,
                    amount: amount
                },
                beforeSend: function() {
                    $('#saveBillBtn').attr('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    $('#editBillModal').modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    
                    // Refresh student list and expand the edited student again
                    loadDetailData(rateId, function() {
                        var studentRow = $('.student-row[data-student-id="' + studentId + '"]');
                        studentRow.trigger('click');
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                },
                complete: function() {
                    $('#saveBillBtn').attr('disabled', false).text('Simpan');
                }
            });
        });

        // Delete Bill Button click
        $(document).on('click', '.delete-bill-btn', function(e) {
            e.stopPropagation();
            var billId = $(this).data('bill-id');
            var rateId = $(this).data('rate-id');
            var studentId = $(this).data('student-id');

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
                            
                            // Refresh student list and expand the edited student again
                            loadDetailData(rateId, function() {
                                var studentRow = $('.student-row[data-student-id="' + studentId + '"]');
                                studentRow.trigger('click');
                            });
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
        });

        // Check All Checkbox
        $(document).on('change', '.select-all-bills', function() {
            var studentId = $(this).data('student-id');
            var rateId = $(this).data('rate-id');
            var isChecked = $(this).is(':checked');
            var container = $('.select-actions-container-' + studentId + '-' + rateId);
            
            // Find all unpaid checkboxes in the student's detail panel
            var checkboxes = $('#student-detail-' + studentId + '-' + rateId + ' .select-bill-checkbox');
            checkboxes.prop('checked', isChecked);
            
            // Update selected count
            var count = isChecked ? checkboxes.length : 0;
            container.find('.selected-count').text(count);
        });

        // Individual Checkbox Change
        $(document).on('change', '.select-bill-checkbox', function() {
            var studentId = $(this).data('student-id');
            var rateId = $(this).data('rate-id');
            var container = $('.select-actions-container-' + studentId + '-' + rateId);
            
            var checkboxes = $('#student-detail-' + studentId + '-' + rateId + ' .select-bill-checkbox');
            var checkedCount = checkboxes.filter(':checked').length;
            
            // Update Hapus Terpilih count
            container.find('.selected-count').text(checkedCount);
            
            // Update "Pilih Semua" checkbox status
            var checkAll = $('#check-all-' + studentId + '-' + rateId);
            checkAll.prop('checked', checkedCount === checkboxes.length);
        });

        // Mass Delete Button Click
        $(document).on('click', '.mass-delete-btn', function() {
            var studentId = $(this).data('student-id');
            var rateId = $(this).data('rate-id');
            
            // Gather all selected bill IDs
            var checkedCheckbox = $('#student-detail-' + studentId + '-' + rateId + ' .select-bill-checkbox:checked');
            var billIds = [];
            checkedCheckbox.each(function() {
                billIds.push($(this).val());
            });

            if (billIds.length === 0) {
                Swal.fire('Peringatan', 'Silakan pilih setidaknya satu tagihan untuk dihapus', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Hapus Massal',
                text: 'Apakah Anda yakin ingin menghapus ' + billIds.length + ' tagihan terpilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('payment-rate.delete-bills-mass') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            bill_ids: billIds
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menghapus tagihan...',
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
                                text: response.message || 'Tagihan terpilih berhasil dihapus',
                                timer: 2000
                            });
                            
                            // Refresh student list and expand the edited student again
                            loadDetailData(rateId, function() {
                                var studentRow = $('.student-row[data-student-id="' + studentId + '"]');
                                studentRow.trigger('click');
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus tagihan'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush