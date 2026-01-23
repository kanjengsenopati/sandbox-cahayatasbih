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
                            <table class="table table-hover align-middle gs-0 gy-4">
                                <thead class="bg-light">
                                    <tr class="fw-bolder text-muted fs-7 text-uppercase">
                                        <th class="ps-4 min-w-50px rounded-start">No</th>
                                        <th class="min-w-150px">Sekolah</th>
                                        <th class="min-w-200px">Kelas</th>
                                        <th class="min-w-125px">Total Tagihan</th>
                                        <th class="text-center min-w-100px rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($regularRates as $rate)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-dark fw-bolder">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                {{ $rate->paymentRateClassrooms->first()->classroom->school->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($rate->paymentRateClassrooms as $prClassroom)
                                                <span class="badge badge-light-success fw-bolder m-1">
                                                    {{ $prClassroom->classroom->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge badge-light-success fs-7 fw-bolder">Rp. {{ number_format($rate->amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                @include('components.action.edit', ['action' => route('payment-rate.edit', $rate->id), 'name' => 'Jenis Bayar'])
                                                @include('components.action.show', ['action' => route('payment-rate.show', $rate->id)])
                                                @include('components.action.delete', ['action' => route('payment-rate.destroy', $rate->id), 'id' => $rate->id, 'name' => 'Jenis Bayar'])
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10">
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
                            <table class="table table-hover align-middle gs-0 gy-4">
                                <thead class="bg-light">
                                    <tr class="fw-bolder text-muted fs-7 text-uppercase">
                                        <th class="ps-4 min-w-50px rounded-start">No</th>
                                        <th class="min-w-150px">Sekolah</th>
                                        <th class="min-w-200px">Nama Siswa</th>
                                        <th class="min-w-125px">Total Tagihan</th>
                                        <th class="text-center min-w-100px rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transferRates as $rate)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-dark fw-bolder">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">
                                                {{ $rate->paymentRateStudents->first()->student->classroom->school->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($rate->paymentRateStudents as $prStudent)
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge badge-light-warning fw-bolder me-2">
                                                        {{ $prStudent->student->name }}
                                                    </span>
                                                    <span class="text-muted fs-8">({{ $prStudent->student->nis ?? '-' }})</span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge badge-light-success fs-7 fw-bolder">Rp. {{ number_format($rate->amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                @include('components.action.edit', ['action' => route('payment-rate.edit', $rate->id), 'name' => 'Jenis Bayar'])
                                                @include('components.action.show', ['action' => route('payment-rate.show', $rate->id)])
                                                @include('components.action.delete', ['action' => route('payment-rate.destroy', $rate->id), 'id' => $rate->id, 'name' => 'Jenis Bayar'])
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10">
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

        // Initialize tooltips/other components if needed (Metronic usually handles this globally)
    });
</script>
@endpush