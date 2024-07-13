@extends('layouts.master', ['title' => 'Bayar Tagihan'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!-- Toolbar -->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!-- Page title -->
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Bayar Tagihan</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a class="text-muted text-hover-primary">Data Pembayaran</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Bayar Tagihan</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Post -->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <div class="d-flex flex-column flex-lg-row">
                <!-- Content -->
                <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
                    <!-- Invoice Summary -->
                    <div class="card card-flush pt-5 mb-5 mb-lg-10" style="background-color: #E5E7EB;">
                        <div class="card-header" style="background-color: #D1D5DB;">
                            <div class="card-title">
                                <h2 style="color: #000000;" class="fw-bolder">Ringkasan Tagihan</h2>
                            </div>
                        </div>
                        <div class="separator separator-dashed mb-7" style="border-color: #D1D5DB;"></div>
                        <div class="card-body pt-0 fs-6" style="color: #000000;">
                            <div class="row mb-7">
                                <!-- Student Info -->
                                <div class="col-md-6 mb-7">
                                    <h5 class="mb-3">Informasi Siswa</h5>
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">Nama :</td>
                                                <td>{{ $student->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Saldo :</td>
                                                <td>Rp. {{ number_format($student->saldo ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Kelas :</td>
                                                <td>{{ $student->classroom->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">NISN :</td>
                                                <td>{{ $student->nisn ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Sekolah :</td>
                                                <td>{{ $student->school->name ?? '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Bill Info -->
                                <div class="col-md-6 mb-7">
                                    <h5 class="mb-3">Informasi Tagihan</h5>
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">Nama :</td>
                                                <td>{{ $billType->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Tahun Ajaran :</td>
                                                <td>{{ $billType->academicYear->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Total Tagihan :</td>
                                                <td>Rp. {{ number_format($billType['total_bill'] ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Total Dibayar :</td>
                                                <td>Rp. {{ number_format($billType['total_paid'] ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Sisa Tagihan :</td>
                                                <td>Rp. {{ number_format($billType['total_unpaid'] ?? 0) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="separator separator-dashed mb-7" style="border-color: #D1D5DB;"></div>
                            <a class="btn btn-primary" href="{{ route('bill.index', ['student_id' => $student->id]) }}"
                                style="background-color: #4D0C7A; border-color: #4D0C7A;">Kembali</a>
                        </div>
                    </div>

                    <!-- Bill Payment -->
                    <div class="card card-flush pt-3 mb-5 mb-lg-10">
                        <div class="card-header">
                            <div class="card-title">
                                <h2 class="fw-bolder">Pembayaran Tagihan</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 fw-bold gy-4"
                                    id="kt_subscription_products_table">
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%;">No</th>
                                            <th class="min-w-125px">Bulan</th>
                                            <th class="min-w-125px">Tagihan</th>
                                            <th class="min-w-125px">Metode Pembayaran</th>
                                            <th>Status</th>
                                            <th class="min-w-125px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600">
                                        @foreach (range(1, 12) as $month)
                                        <tr>
                                            <form action="{{ route('bill.store') }}" method="post" class="form-bayar"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <td>{{ $loop->iteration }}</td>
                                                <th class="min-w-125px">
                                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                                </th>
                                                @php
                                                $billForMonth = $bills->where('month', $month)->first();
                                                $amount = $billForMonth ? number_format($billForMonth->amount, 0, ',',
                                                '.') : 0;
                                                @endphp
                                                <input type="hidden" name="bill_ids[]"
                                                    value="{{ $billForMonth->id ?? '' }}">
                                                <input type="hidden" name="pay_amount"
                                                    value="{{ $billForMonth->amount ?? '' }}">
                                                <input type="hidden" name="student_id" value="{{ $student->id ?? '' }}">
                                                <td>Rp {{ $amount ?? '' }}</td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    @if(optional($billForMonth->transactions->first())->paymentMethod?->type
                                                    == 'CASH')
                                                    <span class="badge badge-success">
                                                        @if ($billForMonth?->transactions?->first()?->admin)
                                                        Tunai Melalui {{
                                                        $billForMonth->transactions->first()->admin->name ?? '' }}
                                                        @endif
                                                        @if($billForMonth->transactions?->first()?->paid_at)
                                                        <br>Dibayar {{ $billForMonth->transactions->first()->paid_at }}
                                                        @endif
                                                    </span>
                                                    @elseif(optional($billForMonth->transactions->first())->paymentMethod?->type
                                                    == 'TRANSFER')
                                                    <span class="badge badge-success">
                                                        @if ($billForMonth?->transactions?->first()?->admin)
                                                        Transfer Diverifikasi oleh {{
                                                        $billForMonth->transactions->first()->admin->name ?? '' }}
                                                        @endif
                                                        @if($billForMonth->transactions?->first()?->paid_at)
                                                        <br>Dibayar {{ $billForMonth->transactions->first()->paid_at }}
                                                        @endif
                                                    </span>
                                                    @else
                                                    <span class="badge badge-success">
                                                        {{ $billForMonth->transactions->first()->paymentMethod->name ??
                                                        '' }}
                                                        @if($billForMonth->transactions?->first()?->paid_at)
                                                        <br>Dibayar {{ $billForMonth->transactions->first()->paid_at }}
                                                        @endif
                                                    </span>
                                                    @endif
                                                    @else
                                                    <select class="form-select payment-method-select"
                                                        name="payment_method" required>
                                                        <option value="">Metode Pembayaran</option>
                                                        @if ($student->saldo > $amount)
                                                        <option value="BALANCE">Saldo</option>
                                                        @endif
                                                        <option value="CASH">Tunai</option>
                                                    </select>
                                                    @endif
                                                </td>
                                                <td>{{ $billForMonth ? $billForMonth->translated_status : 'UNPAID' }}
                                                </td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    @elseif ($billForMonth && $billForMonth->status == 'UNPAID' &&
                                                    $billForMonth->transactions->first()?->payment_link)
                                                    <a href="{{ $billForMonth->transactions->first()->payment_link }}"
                                                        class="btn btn-primary">Ke Halaman Pembayaran</a>
                                                    @elseif ($billForMonth)
                                                    <button onclick="disableButton(this)"
                                                        class="btn btn-primary btn-bayar">Bayar</button>
                                                    @endif
                                                </td>
                                            </form>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function disableButton(el) {
        el.disabled = true;
        el.innerHTML = 'Loading...';
        el.form.submit();
    }
</script>
@endpush
{{-- @extends('layouts.master', ['title' => 'Bayar Tagihan'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Bayar Tagihan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a class="text-muted text-hover-primary">Data Pembayaran</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Bayar Tagihan</li>
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
            <!--begin::Layout-->
            <div class="d-flex flex-column flex-lg-row">
                <!--begin::Content-->
                <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
                    <!--begin::Form-->
                    <div class="card card-flush pt-5 mb-5 mb-lg-10" style="background-color: #E5E7EB;">
                        <!--begin::Card header-->
                        <div class="card-header" style="background-color: #D1D5DB;">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2 style="color: #000000;" class="fw-bolder">Ringkasan Tagihan</h2>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <div class="separator separator-dashed mb-7" style="border-color: #D1D5DB;"></div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0 fs-6" style="color: #000000;">
                            <!--begin::Section-->
                            <div class="row mb-7">
                                <!--begin::Col-->
                                <div class="col-md-6">
                                    <!--begin::Input group-->
                                    <div class="mb-7">
                                        <!--begin::Title-->
                                        <h5 class="mb-3">Informasi Siswa</h5>
                                        <!--end::Title-->
                                        <!--begin::Details-->
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold">Nama :</td>
                                                    <td>{{ @$student->name ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Saldo :</td>
                                                    <td>Rp. {{ number_format(@$student->saldo, 0, ',', '.') ?? '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Kelas :</td>
                                                    <td>{{ @$student->classroom->name ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">NISN :</td>
                                                    <td>{{ @$student->nisn ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Sekolah :</td>
                                                    <td>{{ @$student->school->name ?? '' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!--end::Details-->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-7">
                                        <!--begin::Title-->
                                        <h5 class="mb-3">Informasi Tagihan</h5>
                                        <!--end::Title-->
                                        <!--begin::Details-->
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold">Nama :</td>
                                                    <td>{{ @$billType->name ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Tahun Ajaran :</td>
                                                    <td>{{ @$billType->academicYear->name ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Total Tagihan :</td>
                                                    <td>Rp. {{ number_format($billType['total_bill']) ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Total Dibayar :</td>
                                                    <td>Rp. {{ number_format($billType['total_paid']) ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Sisa Tagihan :</td>
                                                    <td>Rp. {{ number_format($billType['total_unpaid']) ?? '' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!--end::Details-->
                                    </div>
                                </div>
                            </div>
                            <!--end::Section-->
                            <!--begin::Seperator-->
                            <div class="separator separator-dashed mb-7" style="border-color: #D1D5DB;"></div>
                            <!--end::Seperator-->
                            <!--begin::Actions-->
                            <div class="mb-0">
                                <a class="btn btn-primary"
                                    href="{{ route('bill.index', ['student_id' => $student->id]) }}"
                                    style="background-color: #4D0C7A; border-color: #4D0C7A;">Kembali</a>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card body-->


                    <!--begin::Pricing-->
                    <div class="card card-flush pt-3 mb-5 mb-lg-10">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2 class="fw-bolder">Pembayaran Tagihan</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table wrapper-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-dashed fs-6 fw-bold gy-4"
                                    id="kt_subscription_products_table">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%;">No</th>
                                            <th class="min-w-125px">Bulan</th>
                                            <th class="min-w-125px">Tagihan</th>
                                            <th class="min-w-125px">Metode Pembayaran</th>
                                            <th>Status</th>
                                            <th class="min-w-125px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="text-gray-600">
                                        @foreach (range(1, 12) as $month)

                                        <tr>
                                            <form action="{{ route('bill.store') }}" method="post" class="form-bayar"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <td>{{ $loop->iteration }}</td>
                                                <th class="min-w-125px">
                                                    {{
                                                    \Carbon\Carbon::create()->month($month)->translatedFormat('F')
                                                    }}
                                                </th>
                                                @php
                                                $billForMonth = $bills->where('month', $month)->first();
                                                $amount = $billForMonth ? number_format($billForMonth->amount, 0,
                                                ',',
                                                '.') : 0;
                                                @endphp
                                                <input type="hidden" name="bill_ids[]"
                                                    value="{{ $billForMonth->id ?? '' }}">
                                                <input type="hidden" name="pay_amount"
                                                    value="{{ $billForMonth->amount ?? '' }}">
                                                <input type="hidden" name="student_id" value="{{ $student->id ?? '' }}">
                                                <td>Rp {{ $amount ?? '' }}</td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID' &&
                                                    optional($billForMonth->transactions?->first())->paymentMethod?->type
                                                    == 'CASH')
                                                    <span class="badge badge-success">
                                                        Tunai Melalui {{
                                                        $billForMonth->transactions?->first()->admin?->name ??
                                                        '' }}
                                                        <br>Dibayar {{
                                                        $billForMonth->transactions?->first()->paid_at
                                                        ?? '' }}</span>
                                                    </span>
                                                    @elseif ($billForMonth && $billForMonth->status == 'PAID' &&
                                                    optional($billForMonth->transactions?->first())->paymentMethod?->type
                                                    == 'TRANSFER')
                                                    <span class="badge badge-success">
                                                        Transfer Diverifikasi oleh {{
                                                        $billForMonth->transactions?->first()->admin?->name ??
                                                        '' }}
                                                        <br>Dibayar {{
                                                        $billForMonth->transactions?->first()->paid_at
                                                        ?? '' }}</span>
                                                    </span>
                                                    @elseif ($billForMonth && $billForMonth->status == 'PAID')
                                                    <span class="badge badge-success">
                                                        {{
                                                        $billForMonth->transactions?->first()->paymentMethod?->name
                                                        ?? ''
                                                        }}
                                                        <br>Dibayar {{
                                                        $billForMonth->transactions?->first()->paid_at
                                                        ?? '' }}</span>
                                                    </span>
                                                    @elseif ($billForMonth)
                                                    <select class="form-select payment-method-select"
                                                        name="payment_method" required>
                                                        <option value="">Metode Pembayaran
                                                        </option>
                                                        @if ($student->saldo > $amount)
                                                        <option value="BALANCE">Saldo</option>
                                                        @endif
                                                        <option value="CASH">Tunai</option>
                                                    </select>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $billForMonth ? $billForMonth->translated_status : 'UNPAID' }}
                                                </td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')

                                                    @elseif ($billForMonth && $billForMonth->status == 'UNPAID' &&
                                                    $billForMonth?->transactions?->first()?->payment_link)
                                                    <a href="{{ $billForMonth?->transactions?->first()?->payment_link }}"
                                                        class="btn btn-primary">Ke Halaman Pembayaran</a>
                                                    @elseif ($billForMonth)
                                                    <button onclick="disableButton(this)"
                                                        class="btn btn-primary btn-bayar">Bayar</button>
                                                    @endif
                                                </td>
                                            </form>
                                        </tr>
                                        @endforeach
                                    </tbody>

                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table wrapper-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Pricing-->

                    <!--end::Form-->
                </div>
            </div>
            <!--end::Layout-->
            <!--begin::Modals-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script>
    // disable button dan submit form dan beri loading
    function disableButton(el) {
        el.disabled = true;
        el.innerHTML = 'Loading...';
        el.form.submit();
    }
</script>
@endpush --}}