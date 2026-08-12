@extends('layouts.master', ['title' => 'Data Tarif Pembayaran'])

@push('css')
<style>
    .class-grid-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 4px 0;
    }
    .class-grade-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .class-badge-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 38px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
        border: 2px solid transparent !important;
        user-select: none;
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        cursor: pointer;
    }
    .class-badge-chip:hover {
        transform: translateY(-2px);
        background-color: #d1fae5 !important;
        color: #047857 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }
    .class-badge-chip.active {
        background-color: #10b981 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.4) !important;
        position: relative;
    }
    .btn-light-success:hover i,
    .btn-light-danger:hover i {
        color: #ffffff !important;
    }
</style>
@endpush

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
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                        <span class="badge badge-light-primary fw-bolder" title="Pos Bayar">
                            <i class="fas fa-school text-primary me-1"></i> {{ $billType->billItem?->name ?? 'Pos Bayar' }}
                        </span>
                        <span class="badge badge-light-info fw-bolder" title="Nama Pembayaran">
                            <i class="fas fa-file-alt text-info me-1"></i> {{ $billType->name }}
                        </span>
                        <span class="badge badge-light-success fw-bolder" title="Tipe Pembayaran">
                            <i class="fas fa-clock text-success me-1"></i> {{ $billType->type == 'MONTHLY' ? 'Bulanan' : 'Bebas' }}{{ $billType->payment_input_type == 'FREE' ? ' (Cicilan)' : ' (Fix)' }}
                        </span>
                    </div>
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
                        <div class="min-w-225px">
                            <select class="form-select form-select-solid form-select-sm" data-control="select2" multiple="multiple" data-hide-search="true" id="filter_academic_year" data-placeholder="Semua Tahun Ajaran">
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ in_array($year->id, $academicYearIds ?? []) ? 'selected' : '' }}>{{ $year->name }}</option>
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
                                        <th class="min-w-180px">Kelas</th>
                                        <th class="min-w-150px">Status Wali Santri</th>
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
                                            @php
                                                $schoolNames = $rate->paymentRateClassrooms
                                                    ->map(fn($prc) => $prc->classroom?->school?->name)
                                                    ->filter()
                                                    ->unique();
                                            @endphp
                                            @if($schoolNames->count() > 1)
                                                <span class="badge badge-light-warning fw-bolder" title="Peringatan: Tarif ini berisi kelas dari beberapa sekolah sekaligus">
                                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i> Multi-Sekolah
                                                </span>
                                                <span class="text-gray-600 fs-8 d-block mt-1">
                                                    {{ $schoolNames->implode(', ') }}
                                                </span>
                                            @else
                                                <span class="text-gray-800 fw-bold d-block fs-6">
                                                    {{ $schoolNames->first() ?? '-' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                             @php
                                                 $sortedClassrooms = $rate->paymentRateClassrooms
                                                     ->filter(fn($prc) => !empty($prc->classroom?->name))
                                                     ->sortBy(fn($prc) => $prc->classroom->name, SORT_NATURAL)
                                                     ->groupBy(function($prc) {
                                                         preg_match('/^\d+/', trim($prc->classroom->name), $matches);
                                                         return $matches[0] ?? 'Lainnya';
                                                     });
                                             @endphp

                                             <div class="class-grid-container">
                                                 @foreach($sortedClassrooms as $gradeLevel => $prClassrooms)
                                                     <div class="class-grade-row">
                                                         @foreach($prClassrooms as $prClassroom)
                                                             <span class="badge class-badge-chip cursor-pointer"
                                                                   data-classroom-id="{{ $prClassroom->classroom_id }}"
                                                                   data-classroom-name="{{ $prClassroom->classroom?->name }}"
                                                                   data-rate-id="{{ $rate->id }}"
                                                                   title="Klik untuk memfilter & memuat santri kelas {{ $prClassroom->classroom?->name }} saja">
                                                                 {{ $prClassroom->classroom?->name }}
                                                             </span>
                                                         @endforeach
                                                     </div>
                                                 @endforeach
                                             </div>
                                            @if($rate->gender)
                                                <span class="badge badge-light-primary fw-bolder m-1">
                                                    {{ $rate->gender == 'L' ? 'Putra' : 'Putri' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rate->jamaah_status)
                                                <span class="badge fw-bolder px-3 py-2" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff;">
                                                    {{ collect(explode(',', $rate->jamaah_status))->map(function($status) {
                                                        return match($status) {
                                                            'JAMAAH' => 'Jamaah',
                                                            'NON_JAMAAH' => 'Non Jamaah',
                                                            'MUKIMIN' => 'Mukimin',
                                                            default => $status
                                                        };
                                                    })->implode(', ') }}
                                                </span>
                                            @else
                                                <span class="badge badge-light-secondary fw-bold">Semua Status</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $totalMasterAmount = ($billType->type == 'MONTHLY' || $rate->paymentRateItems->count() > 0)
                                                    ? ($rate->paymentRateItems->count() > 0 ? $rate->paymentRateItems->sum('amount') : $rate->amount * 12)
                                                    : $rate->amount;
                                            @endphp
                                            <span class="badge badge-light-success fs-7 fw-bolder">Rp. {{ number_format($totalMasterAmount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <form action="{{ route('payment-rate.generate', $rate->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Generate / Sinkronkan tagihan untuk tarif ini?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-{{ $rate->bills_exists ? 'success' : 'danger' }} btn-icon hover-scale" title="Generate / Sinkronkan Tagihan">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                </form>
                                                @include('components.action.edit', ['action' => route('payment-rate.edit', $rate->id), 'name' => 'Jenis Bayar'])
                                                @include('components.action.delete', ['action' => route('payment-rate.destroy', $rate->id), 'id' => $rate->id, 'name' => 'Jenis Bayar'])
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Expandable Detail Row --}}
                                    <tr class="detail-row" id="detail-{{ $rate->id }}" style="display: none;">
                                        <td colspan="7" class="p-0 border-0">
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
                                        <th class="min-w-180px">Nama Siswa</th>
                                        <th class="min-w-150px">Status Wali Santri</th>
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
                                            @php
                                                $transferSchoolNames = $rate->paymentRateStudents
                                                    ->map(fn($prs) => $prs->student?->classroom?->school?->name)
                                                    ->filter()
                                                    ->unique();
                                            @endphp
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                {{ $transferSchoolNames->implode(', ') ?: '-' }}
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
                                            @if($rate->gender)
                                                <div class="mt-2">
                                                    <span class="badge badge-light-primary fw-bolder me-1">
                                                        {{ $rate->gender == 'L' ? 'Putra' : 'Putri' }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rate->jamaah_status)
                                                <span class="badge fw-bolder px-3 py-2" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff;">
                                                    {{ collect(explode(',', $rate->jamaah_status))->map(function($status) {
                                                        return match($status) {
                                                            'JAMAAH' => 'Jamaah',
                                                            'NON_JAMAAH' => 'Non Jamaah',
                                                            'MUKIMIN' => 'Mukimin',
                                                            default => $status
                                                        };
                                                    })->implode(', ') }}
                                                </span>
                                            @else
                                                <span class="badge badge-light-secondary fw-bold">Semua Status</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $totalTransferMasterAmount = ($billType->type == 'MONTHLY' || $rate->paymentRateItems->count() > 0)
                                                    ? ($rate->paymentRateItems->count() > 0 ? $rate->paymentRateItems->sum('amount') : $rate->amount * 12)
                                                    : $rate->amount;
                                            @endphp
                                            <span class="badge badge-light-success fs-7 fw-bolder">Rp. {{ number_format($totalTransferMasterAmount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <form action="{{ route('payment-rate.generate', $rate->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Generate / Sinkronkan tagihan untuk tarif susulan ini?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-{{ $rate->bills_exists ? 'success' : 'danger' }} btn-icon hover-scale" title="Generate / Sinkronkan Tagihan Susulan">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                </form>
                                                @include('components.action.edit', ['action' => route('payment-rate.edit', $rate->id), 'name' => 'Jenis Bayar'])
                                                @include('components.action.delete', ['action' => route('payment-rate.destroy', $rate->id), 'id' => $rate->id, 'name' => 'Jenis Bayar'])
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Expandable Detail Row --}}
                                    <tr class="detail-row" id="detail-{{ $rate->id }}" style="display: none;">
                                        <td colspan="7" class="p-0 border-0">
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
                                        <td colspan="7" class="text-center py-10">
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
        // Handle Filter Change (Multi-select)
        $('#filter_academic_year').on('change', function() {
            var selectedYears = $(this).val();
            var url = new URL(window.location.href);
            if (selectedYears && selectedYears.length > 0) {
                url.searchParams.set('academic_year_id', selectedYears.join(','));
            } else {
                url.searchParams.set('academic_year_id', 'all');
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

        // Interactive Class Badge Chip Filter Click Handler
        $(document).on('click', '.class-badge-chip', function(e) {
            e.stopPropagation();
            var chip = $(this);
            var rateId = chip.data('rate-id');
            var classroomId = chip.data('classroom-id');
            var classroomName = chip.data('classroom-name');
            var rateRow = chip.closest('.rate-row');
            var detailRow = $('#detail-' + rateId);
            var icon = rateRow.find('.toggle-detail i');

            var wasActive = chip.hasClass('active');
            rateRow.find('.class-badge-chip').removeClass('active');

            var filterClassId = null;
            if (!wasActive) {
                chip.addClass('active');
                filterClassId = classroomId;
            }

            if (!detailRow.is(':visible')) {
                detailRow.slideDown(200);
                icon.css('transform', 'rotate(90deg)');
            }

            var contentDiv = detailRow.find('.detail-content');
            contentDiv.removeData('tbl-state');
            var targetText = filterClassId ? ('kelas ' + (classroomName || '')) : 'semua kelas';
            contentDiv.html('<div class="text-center py-5"><div class="spinner-border text-success spinner-border-sm" role="status"></div><span class="text-muted ms-2 fs-7">Memuat data ' + targetText + '...</span></div>');

            loadDetailData(rateId, filterClassId);
        });

        function loadDetailData(rateId, classroomId, callback) {
            if (typeof classroomId === 'function') {
                callback = classroomId;
                classroomId = null;
            }

            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var spinnerEl = detailRow.find('.detail-spinner');
            var countEl = detailRow.find('.detail-count');

            spinnerEl.show();

            var reqData = { type: 'bill' };
            if (classroomId && classroomId !== 'null') {
                reqData.classroom_id = classroomId;
            }

            $.ajax({
                url: "{{ route('payment-rate.show', '') }}/" + rateId,
                type: 'GET',
                data: reqData,
                dataType: 'json',
                success: function(response) {
                    var students = response.data || [];
                    spinnerEl.hide();

                    var rateRow = $('.rate-row[data-rate-id="' + rateId + '"]');
                    var activeChip = rateRow.find('.class-badge-chip.active');
                    var classLabel = activeChip.length > 0 ? ' (Kelas ' + activeChip.data('classroom-name') + ')' : '';

                    countEl.text(students.length + ' Santri' + classLabel);

                    if (students.length === 0) {
                        contentDiv.html('<div class="text-center py-4 text-muted"><i class="fas fa-inbox fs-2 mb-2 d-block"></i>Tidak ada data santri</div>');
                        loadedPanels[rateId] = true;
                        if (typeof callback === 'function') {
                            callback();
                        }
                        return;
                    }

                    // Preserve state if it already exists
                    var existingState = contentDiv.data('tbl-state');
                    var state = {
                        all: students,
                        filtered: students,
                        page: existingState ? existingState.page : 1,
                        limit: existingState ? existingState.limit : 20,
                        search: existingState ? existingState.search : '',
                        sortCol: existingState ? existingState.sortCol : null,
                        sortDir: existingState ? existingState.sortDir : 'asc'
                    };
                    contentDiv.data('tbl-state', state);

                    // Render table and search/limit shell
                    function getSortIcon(colName, currentState) {
                        if (currentState.sortCol !== colName) {
                            return '<i class="fas fa-sort text-gray-400 ms-1 fs-9"></i>';
                        }
                        return currentState.sortDir === 'asc' 
                            ? '<i class="fas fa-sort-up text-primary ms-1 fs-8"></i>' 
                            : '<i class="fas fa-sort-down text-primary ms-1 fs-8"></i>';
                    }

                    var html = '<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">';
                    html += '  <div class="d-flex align-items-center gap-2">';
                    html += '    <span class="fs-8 fw-bold text-gray-600">Tampilkan:</span>';
                    html += '    <select class="form-select form-select-solid form-select-sm w-100px limit-student" data-rate-id="' + rateId + '">';
                    html += '      <option value="20" ' + (state.limit == 20 ? 'selected' : '') + '>20 Baris</option>';
                    html += '      <option value="30" ' + (state.limit == 30 ? 'selected' : '') + '>30 Baris</option>';
                    html += '      <option value="50" ' + (state.limit == 50 ? 'selected' : '') + '>50 Baris</option>';
                    html += '      <option value="all" ' + (state.limit == 'all' ? 'selected' : '') + '>Semua</option>';
                    html += '    </select>';
                    html += '  </div>';
                    html += '  <div class="d-flex align-items-center gap-2">';
                    html += '    <input type="text" class="form-control form-control-solid form-control-sm w-200px search-student" placeholder="Cari Nama..." value="' + state.search + '" data-rate-id="' + rateId + '">';
                    html += '  </div>';
                    html += '</div>';

                    html += '<div class="table-responsive">';
                    html += '  <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-2 bg-white rounded">';
                    html += '    <thead><tr class="fw-bolder text-muted fs-8 text-uppercase">';
                    html += '      <th style="width: 3%"></th>';
                    html += '      <th class="ps-4" style="width: 5%">No</th>';
                    html += '      <th class="sortable-col cursor-pointer user-select-none" data-col="name" data-rate-id="' + rateId + '">Nama Santri ' + getSortIcon('name', state) + '</th>';
                    html += '      <th class="sortable-col cursor-pointer user-select-none" data-col="classroom" data-rate-id="' + rateId + '">Kelas ' + getSortIcon('classroom', state) + '</th>';
                    html += '      <th class="sortable-col cursor-pointer user-select-none" data-col="total" data-rate-id="' + rateId + '">Total Tagihan ' + getSortIcon('total', state) + '</th>';
                    html += '      <th class="sortable-col cursor-pointer user-select-none" data-col="total_paid" data-rate-id="' + rateId + '">Dibayar ' + getSortIcon('total_paid', state) + '</th>';
                    html += '      <th class="sortable-col cursor-pointer user-select-none" data-col="total_unpaid" data-rate-id="' + rateId + '">Sisa ' + getSortIcon('total_unpaid', state) + '</th>';
                    html += '      <th class="text-center sortable-col cursor-pointer user-select-none" data-col="status" data-rate-id="' + rateId + '">Status ' + getSortIcon('status', state) + '</th>';
                    html += '    </tr></thead>';
                    html += '    <tbody class="student-table-body" data-rate-id="' + rateId + '"></tbody>';
                    html += '  </table>';
                    html += '</div>';

                    html += '<div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2 pagination-container" data-rate-id="' + rateId + '"></div>';

                    contentDiv.html(html);
                    loadedPanels[rateId] = true;

                    // Initial render of student list
                    renderStudentTable(rateId);

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

        function renderStudentTable(rateId) {
            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var state = contentDiv.data('tbl-state');
            if (!state) return;

            // Filter by search string
            state.filtered = state.all.filter(function(s) {
                return (s.name || '').toLowerCase().indexOf(state.search.toLowerCase()) !== -1;
            });

            // Sort column if active
            if (state.sortCol) {
                state.filtered.sort(function(a, b) {
                    var valA, valB;
                    if (state.sortCol === 'status') {
                        valA = (a.status || '').replace(/<[^>]*>?/gm, '').trim();
                        valB = (b.status || '').replace(/<[^>]*>?/gm, '').trim();
                    } else if (state.sortCol === 'name') {
                        valA = (a.name || '').toLowerCase();
                        valB = (b.name || '').toLowerCase();
                    } else if (state.sortCol === 'classroom') {
                        valA = (a.classroom || '').toLowerCase();
                        valB = (b.classroom || '').toLowerCase();
                    } else if (state.sortCol === 'total') {
                        valA = parseFloat((a.total || '0').replace(/[^0-9]/g, '')) || 0;
                        valB = parseFloat((b.total || '0').replace(/[^0-9]/g, '')) || 0;
                    } else if (state.sortCol === 'total_paid') {
                        valA = parseFloat((a.total_paid || '0').replace(/[^0-9]/g, '')) || 0;
                        valB = parseFloat((b.total_paid || '0').replace(/[^0-9]/g, '')) || 0;
                    } else if (state.sortCol === 'total_unpaid') {
                        valA = parseFloat((a.total_unpaid || '0').replace(/[^0-9]/g, '')) || 0;
                        valB = parseFloat((b.total_unpaid || '0').replace(/[^0-9]/g, '')) || 0;
                    } else {
                        valA = a[state.sortCol] || '';
                        valB = b[state.sortCol] || '';
                    }

                    if (valA < valB) return state.sortDir === 'asc' ? -1 : 1;
                    if (valA > valB) return state.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            // Pagination calculation
            var total = state.filtered.length;
            var limit = state.limit === 'all' ? total : parseInt(state.limit);
            var totalPages = limit > 0 ? Math.ceil(total / limit) : 1;

            if (state.page > totalPages) {
                state.page = totalPages || 1;
            }
            if (state.page < 1) {
                state.page = 1;
            }

            var start = (state.page - 1) * limit;
            var end = state.limit === 'all' ? total : Math.min(start + limit, total);
            var slice = state.filtered.slice(start, end);

            var tbody = contentDiv.find('.student-table-body');
            var html = '';

            if (slice.length === 0) {
                html += '<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data santri ditemukan</td></tr>';
            } else {
                slice.forEach(function(s, idx) {
                    var displayIdx = start + idx + 1;
                    html += '<tr class="student-row" data-student-id="' + s.id + '" data-rate-id="' + rateId + '" style="cursor: pointer;">';
                    html += '<td class="text-center toggle-student-detail">';
                    html += '<i class="fas fa-chevron-right text-success fs-8 transition-transform" style="transition: transform 0.15s;"></i>';
                    html += '</td>';
                    html += '<td class="ps-4 text-gray-700">' + displayIdx + '</td>';
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
            }

            tbody.html(html);

            // Render pagination footer
            var pagContainer = contentDiv.find('.pagination-container');
            var pagHtml = '';
            if (total > 0) {
                pagHtml += '<div class="fs-8 text-gray-700 fw-bold">Menampilkan ' + (start + 1) + ' - ' + end + ' dari ' + total + ' santri</div>';
            } else {
                pagHtml += '<div class="fs-8 text-gray-700 fw-bold">Menampilkan 0 dari 0 santri</div>';
            }

            if (totalPages > 1) {
                pagHtml += '<ul class="pagination pagination-outline mb-0">';
                
                // Previous button
                pagHtml += '<li class="page-item previous ' + (state.page === 1 ? 'disabled' : '') + '">';
                pagHtml += '<a href="#" class="page-link py-1 px-3 fs-8 btn-page" data-page="' + (state.page - 1) + '" data-rate-id="' + rateId + '">Sebelumnya</a>';
                pagHtml += '</li>';
                
                // Page numbers
                for (var p = 1; p <= totalPages; p++) {
                    if (p === 1 || p === totalPages || Math.abs(p - state.page) <= 1) {
                        pagHtml += '<li class="page-item ' + (state.page === p ? 'active' : '') + '">';
                        pagHtml += '<a href="#" class="page-link py-1 px-3 fs-8 btn-page" data-page="' + p + '" data-rate-id="' + rateId + '">' + p + '</a>';
                        pagHtml += '</li>';
                    } else if (p === 2 || p === totalPages - 1) {
                        pagHtml += '<li class="page-item disabled"><span class="page-link py-1 px-3 fs-8">...</span></li>';
                    }
                }
                
                // Next button
                pagHtml += '<li class="page-item next ' + (state.page === totalPages ? 'disabled' : '') + '">';
                pagHtml += '<a href="#" class="page-link py-1 px-3 fs-8 btn-page" data-page="' + (state.page + 1) + '" data-rate-id="' + rateId + '">Berikutnya</a>';
                pagHtml += '</li>';
                
                pagHtml += '</ul>';
            }

            pagContainer.html(pagHtml);
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
                            : (b.status === 'PARTIAL' ? 'bg-light-warning border-warning' : 'bg-white border-gray-200');
                        
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
                        
                        // Dynamic Status Badge (Emerald for PAID, Yellow for PARTIAL, Red for UNPAID)
                        var statusStyle = b.status === 'PAID' 
                            ? 'background-color: #10b981 !important; color: #ffffff !important;' 
                            : (b.status === 'PARTIAL' 
                                ? 'background-color: #f59e0b !important; color: #ffffff !important;' 
                                : 'background-color: #dc2626 !important; color: #ffffff !important;');
                        var statusLabel = b.status === 'PAID' ? 'LUNAS' : (b.status === 'PARTIAL' ? 'CICILAN' : 'BELUM LUNAS');
                        html += '<div class="badge fw-bold fs-8 py-1.5 px-3 w-100 mb-2 text-center" style="' + statusStyle + '">' + statusLabel + '</div>';
                        
                        // Nominal
                        var amountColor = b.status === 'PAID' ? 'text-success' : (b.status === 'PARTIAL' ? 'text-warning' : 'text-primary');
                        html += '<div class="fs-5 fw-bolder ' + amountColor + ' text-center mb-2">Rp. ' + new Intl.NumberFormat('id-ID').format(b.amount) + '</div>';
                        
                        // Aligned Bottom Buttons / Status
                        html += '<div class="mt-auto">';
                        if (b.status === 'UNPAID' || b.status === 'PARTIAL') {
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

        // Client-side search student name input handler
        $(document).on('input', '.search-student', function() {
            var rateId = $(this).data('rate-id');
            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var state = contentDiv.data('tbl-state');
            if (state) {
                state.search = $(this).val();
                state.page = 1; // Reset to first page
                renderStudentTable(rateId);
            }
        });

        // Client-side page limit selection change handler
        $(document).on('change', '.limit-student', function() {
            var rateId = $(this).data('rate-id');
            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var state = contentDiv.data('tbl-state');
            if (state) {
                state.limit = $(this).val();
                state.page = 1; // Reset to first page
                renderStudentTable(rateId);
            }
        });

        // Column Sort Click Handler (Sort A/D)
        $(document).on('click', '.sortable-col', function(e) {
            e.preventDefault();
            var col = $(this).data('col');
            var rateId = $(this).data('rate-id');
            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var state = contentDiv.data('tbl-state');

            if (state) {
                if (state.sortCol === col) {
                    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortCol = col;
                    state.sortDir = 'asc';
                }

                // Update Header Icons
                detailRow.find('.sortable-col').each(function() {
                    var c = $(this).data('col');
                    var iconEl = $(this).find('i');
                    if (c === state.sortCol) {
                        iconEl.attr('class', 'fas ' + (state.sortDir === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary') + ' ms-1 fs-8');
                    } else {
                        iconEl.attr('class', 'fas fa-sort text-gray-400 ms-1 fs-9');
                    }
                });

                renderStudentTable(rateId);
            }
        });

        // Client-side page navigation click handler
        $(document).on('click', '.btn-page', function(e) {
            e.preventDefault();
            var rateId = $(this).data('rate-id');
            var page = parseInt($(this).data('page'));
            var detailRow = $('#detail-' + rateId);
            var contentDiv = detailRow.find('.detail-content');
            var state = contentDiv.data('tbl-state');
            if (state && !isNaN(page)) {
                state.page = page;
                renderStudentTable(rateId);
            }
        });

        // Handle single student generate bill button
        $(document).on('click', '.generate-single-student-btn', function(e) {
            e.stopPropagation(); // Stop row toggle event
            var btn = $(this);
            var studentId = btn.data('student-id');
            var rateId = btn.data('rate-id');

            Swal.fire({
                title: 'Generate Tagihan Siswa?',
                text: 'Sistem akan me-generate tagihan secara sinkron dan atomic khusus untuk siswa ini.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Generate',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Proses...');
                    
                    $.ajax({
                        url: "{{ route('payment-rate.generate-student') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            payment_rate_id: rateId,
                            student_id: studentId
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || 'Tagihan berhasil di-generate!'
                            });
                            // Reload detail data for this rate to update student total and status
                            delete loadedPanels[rateId];
                            loadDetailData(rateId);
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="fas fa-sync-alt text-primary me-1"></i>Generate');
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal me-generate tagihan.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: msg
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush